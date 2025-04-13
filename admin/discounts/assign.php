<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$active_page = 'discounts';
$page_title = 'Assign Diskon ke Produk';

require_once '../../config/config.php';
require_once '../../lib/models/Discount.php';
require_once '../../lib/models/Product.php';
require_once '../../lib/models/Category.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$discount_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$discount_id) {
    $_SESSION['error_message'] = 'ID diskon tidak valid';
    redirect('admin/discounts/index.php');
}

$discountModel = new Discount($conn);
$productModel = new Product($conn);
$categoryModel = new Category($conn);

$discount = $discountModel->getById($discount_id);
if (!$discount) {
    $_SESSION['error_message'] = 'Diskon tidak ditemukan';
    redirect('admin/discounts/index.php');
}

$assignedProductIds = $discountModel->getAssignedProductIds($discount_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $discountModel->removeAllProductAssignments($discount_id);

    if (isset($_POST['products']) && is_array($_POST['products'])) {
        foreach ($_POST['products'] as $productId) {
            $discountModel->assignToProduct($discount_id, $productId);
        }
    }

    $_SESSION['success_message'] = 'Produk berhasil di-update untuk diskon ' . $discount['name'];
    redirect('admin/discounts/index.php');
}

$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$categoryId = isset($_GET['category']) ? intval($_GET['category']) : 0;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Get products
if (!empty($search)) {
    $products = $productModel->search($search, $limit, $offset);
    $totalProducts = $productModel->countSearch($search);
} elseif ($categoryId > 0) {
    $products = $productModel->getByCategoryId($categoryId, $limit, $offset);
    $totalProducts = $productModel->countByCategoryId($categoryId);
} else {
    $products = $productModel->getAll($limit, $offset);
    $totalProducts = $productModel->countAll();
}

$totalPages = ceil($totalProducts / $limit);

$categories = $categoryModel->getAll();

include '../inc/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Assign Produk ke Diskon "<?php echo $discount['name']; ?>"</h1>
    <a href="index.php" class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded inline-flex items-center">
        <i class="fas fa-arrow-left mr-2"></i> Kembali
    </a>
</div>

<div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
    <div class="p-6 bg-gray-50 border-b border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h2 class="text-lg font-semibold mb-2">Detail Diskon</h2>
                <div class="bg-white p-4 rounded border border-gray-200">
                    <p><span class="font-medium">Nama:</span> <?php echo $discount['name']; ?></p>
                    <p><span class="font-medium">Tipe:</span>
                        <?php
                        $typeLabels = [
                            'product' => 'Diskon Produk',
                            'voucher' => 'Voucher',
                            'bundle' => 'Bundel',
                            'time' => 'Diskon Waktu Tertentu'
                        ];
                        echo isset($typeLabels[$discount['type']]) ? $typeLabels[$discount['type']] : $discount['type'];
                        ?>
                    </p>
                    <p><span class="font-medium">Nilai:</span>
                        <?php
                        if ($discount['is_percentage']) {
                            echo $discount['value'] . '%';
                        } else {
                            echo formatRupiah($discount['value']);
                        }
                        ?>
                    </p>
                    <p><span class="font-medium">Status:</span>
                        <?php if ($discount['active']): ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Aktif
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                Tidak Aktif
                            </span>
                        <?php endif; ?>
                    </p>
                    <?php if ($discount['start_date'] || $discount['end_date']): ?>
                        <p class="mt-2"><span class="font-medium">Periode:</span>
                            <?php
                            if ($discount['start_date'] && $discount['end_date']) {
                                echo date('d/m/Y', strtotime($discount['start_date'])) . ' - ' . date('d/m/Y', strtotime($discount['end_date']));
                            } elseif ($discount['start_date']) {
                                echo 'Mulai ' . date('d/m/Y', strtotime($discount['start_date']));
                            } elseif ($discount['end_date']) {
                                echo 'Sampai ' . date('d/m/Y', strtotime($discount['end_date']));
                            }
                            ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
            <div>
                <h2 class="text-lg font-semibold mb-2">Filter Produk</h2>
                <form action="" method="GET" class="bg-white p-4 rounded border border-gray-200">
                    <input type="hidden" name="id" value="<?php echo $discount_id; ?>">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Cari Produk</label>
                            <input type="text" id="search" name="search" value="<?php echo $search; ?>" placeholder="Nama produk..." class="w-full rounded-md shadow-sm focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        </div>
                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                            <select id="category" name="category" class="w-full rounded-md shadow-sm focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <option value="0">Semua Kategori</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" <?php echo $categoryId == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo $category['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">
                            <i class="fas fa-search mr-2"></i> Filter
                        </button>
                        <?php if (!empty($search) || $categoryId > 0): ?>
                            <a href="assign.php?id=<?php echo $discount_id; ?>" class="ml-2 bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded">
                                <i class="fas fa-times mr-2"></i> Reset
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <form action="" method="POST">
        <div class="p-4 bg-gray-50 border-t border-b border-gray-200">
            <div class="flex justify-between items-center">
                <div>
                    <button type="button" id="selectAll" class="text-blue-600 hover:text-blue-800 mr-4">
                        <i class="fas fa-check-square mr-1"></i> Pilih Semua
                    </button>
                    <button type="button" id="deselectAll" class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-square mr-1"></i> Batalkan Semua
                    </button>
                </div>
                <div>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded">
                        <i class="fas fa-save mr-2"></i> Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>

        <?php if (count($products) > 0): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 p-6">
                <?php foreach ($products as $product): ?>
                    <div class="relative border rounded-lg overflow-hidden hover:shadow-md transition duration-300">
                        <label for="product_<?php echo $product['id']; ?>" class="block cursor-pointer">
                            <div class="absolute top-2 left-2 z-10">
                                <input
                                    type="checkbox"
                                    id="product_<?php echo $product['id']; ?>"
                                    name="products[]"
                                    value="<?php echo $product['id']; ?>"
                                    <?php echo in_array($product['id'], $assignedProductIds) ? 'checked' : ''; ?>
                                    class="form-checkbox h-5 w-5 text-blue-600 rounded focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            </div>
                            <div class="aspect-w-1 aspect-h-1 w-full overflow-hidden bg-gray-200">
                                <img
                                    src="<?php echo !empty($product['image']) ? BASE_URL . 'assets/images/products/' . $product['image'] : BASE_URL . 'assets/images/products/default-product.jpg'; ?>"
                                    alt="<?php echo $product['name']; ?>"
                                    class="w-full h-48 object-cover">
                            </div>
                            <div class="p-4">
                                <h3 class="text-sm font-medium text-gray-900"><?php echo $product['name']; ?></h3>
                                <div class="mt-1 flex justify-between items-center">
                                    <div>
                                        <?php
                                        $discountedPrice = $productModel->getDiscountedPrice($product);
                                        if ($discountedPrice < $product['price']):
                                        ?>
                                            <div class="text-xs text-gray-500 line-through"><?php echo formatRupiah($product['price']); ?></div>
                                            <div class="text-sm text-red-600 font-semibold"><?php echo formatRupiah($discountedPrice); ?></div>
                                        <?php else: ?>
                                            <div class="text-sm text-gray-900"><?php echo formatRupiah($product['price']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <span class="text-xs text-gray-500">Stok: <?php echo $product['stock']; ?></span>
                                    </div>
                                </div>
                                <div class="mt-1 text-xs text-gray-500">Kategori: <?php echo $product['category_name']; ?></div>
                            </div>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPages > 1): ?>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-500">
                            Menampilkan <?php echo ($page - 1) * $limit + 1; ?> sampai <?php echo min($page * $limit, $totalProducts); ?> dari <?php echo $totalProducts; ?> produk
                        </div>
                        <div class="flex space-x-1">
                            <?php if ($page > 1): ?>
                                <a href="?id=<?php echo $discount_id; ?>&page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo $categoryId > 0 ? '&category=' . $categoryId : ''; ?>" class="px-3 py-1 rounded border border-gray-300 text-sm hover:bg-gray-100">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>

                            <?php
                            $startPage = max(1, $page - 2);
                            $endPage = min($totalPages, $page + 2);

                            for ($i = $startPage; $i <= $endPage; $i++):
                            ?>
                                <a href="?id=<?php echo $discount_id; ?>&page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo $categoryId > 0 ? '&category=' . $categoryId : ''; ?>" class="px-3 py-1 rounded border <?php echo $i == $page ? 'bg-blue-600 text-white' : 'border-gray-300 hover:bg-gray-100 text-gray-700'; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($page < $totalPages): ?>
                                <a href="?id=<?php echo $discount_id; ?>&page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo $categoryId > 0 ? '&category=' . $categoryId : ''; ?>" class="px-3 py-1 rounded border border-gray-300 text-sm hover:bg-gray-100">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="p-6 text-center">
                <p class="text-gray-500">Tidak ada produk yang ditemukan.</p>
                <?php if (!empty($search) || $categoryId > 0): ?>
                    <a href="assign.php?id=<?php echo $discount_id; ?>" class="text-blue-600 hover:text-blue-800 mt-2 inline-block">
                        <i class="fas fa-times-circle mr-1"></i> Reset Filter
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllBtn = document.getElementById('selectAll');
        const deselectAllBtn = document.getElementById('deselectAll');
        const checkboxes = document.querySelectorAll('input[name="products[]"]');

        if (selectAllBtn) {
            selectAllBtn.addEventListener('click', function() {
                checkboxes.forEach(checkbox => {
                    checkbox.checked = true;
                });
            });
        }

        if (deselectAllBtn) {
            deselectAllBtn.addEventListener('click', function() {
                checkboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });
            });
        }
    });
</script>

<?php include '../inc/footer.php'; ?>