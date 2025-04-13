<?php
$active_page = 'products';
$page_title = 'Kelola Produk';

require_once '../../config/config.php';
require_once '../../lib/models/Product.php';
require_once '../../lib/models/Category.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$productModel = new Product($conn);
$categoryModel = new Category($conn);

$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$categoryId = isset($_GET['category']) ? intval($_GET['category']) : 0;

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

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
    <h1 class="text-2xl font-semibold text-gray-900">Kelola Produk</h1>
    <a href="create.php" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded inline-flex items-center">
        <i class="fas fa-plus mr-2"></i> Tambah Produk
    </a>
</div>

<div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
    <div class="p-4 border-b border-gray-200">
        <form action="" method="GET" class="flex flex-wrap items-end space-x-4">
            <div class="w-full md:w-auto mb-4 md:mb-0">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Cari Produk</label>
                <input type="text" id="search" name="search" value="<?php echo $search; ?>" placeholder="Nama produk..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="w-full md:w-auto mb-4 md:mb-0">
                <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                <select id="category" name="category" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="0">Semua Kategori</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>" <?php echo $categoryId == $category['id'] ? 'selected' : ''; ?>>
                            <?php echo $category['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded inline-flex items-center">
                    <i class="fas fa-search mr-2"></i> Filter
                </button>
                <?php if (!empty($search) || $categoryId > 0): ?>
                    <a href="index.php" class="ml-2 text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times-circle"></i> Reset
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <?php if (count($products) > 0): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 flex-shrink-0 mr-3">
                                        <img class="h-10 w-10 rounded-full object-cover" src="<?php echo !empty($product['image']) ? BASE_URL . 'assets/images/products/' . $product['image'] : BASE_URL . 'assets/images/products/default-product.jpg'; ?>" alt="">
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo $product['name']; ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?php echo substr($product['description'], 0, 50) . (strlen($product['description']) > 50 ? '...' : ''); ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo $product['category_name']; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $discountedPrice = $productModel->getDiscountedPrice($product);
                                if ($discountedPrice < $product['price']):
                                ?>
                                    <div class="text-sm text-gray-500 line-through"><?php echo formatRupiah($product['price']); ?></div>
                                    <div class="text-sm text-red-600 font-semibold"><?php echo formatRupiah($discountedPrice); ?></div>
                                <?php else: ?>
                                    <div class="text-sm text-gray-900"><?php echo formatRupiah($product['price']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <?php if ($product['stock'] <= 0): ?>
                                        <span class="text-red-500">Habis</span>
                                    <?php elseif ($product['stock'] < 5): ?>
                                        <span class="text-yellow-500"><?php echo $product['stock']; ?></span>
                                    <?php else: ?>
                                        <?php echo $product['stock']; ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($product['stock'] > 0): ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Tersedia
                                    </span>
                                <?php else: ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Tidak Tersedia
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="edit.php?id=<?php echo $product['id']; ?>" class="text-blue-600 hover:text-blue-900" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?php echo BASE_URL . 'product.php?slug=' . $product['slug']; ?>" target="_blank" class="text-green-600 hover:text-green-900" title="Lihat">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="delete.php?id=<?php echo $product['id']; ?>" class="text-red-600 hover:text-red-900" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="px-6 py-4 bg-gray-50">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-500">
                        Menampilkan <?php echo ($page - 1) * $limit + 1; ?> sampai <?php echo min($page * $limit, $totalProducts); ?> dari <?php echo $totalProducts; ?> produk
                    </div>
                    <div class="flex space-x-1">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo $categoryId > 0 ? '&category=' . $categoryId : ''; ?>" class="px-3 py-1 rounded border border-gray-300 text-sm hover:bg-gray-100">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>

                        <?php
                        $startPage = max(1, $page - 2);
                        $endPage = min($totalPages, $page + 2);

                        for ($i = $startPage; $i <= $endPage; $i++):
                        ?>
                            <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo $categoryId > 0 ? '&category=' . $categoryId : ''; ?>" class="px-3 py-1 rounded border <?php echo $i == $page ? 'bg-blue-600 text-white' : 'border-gray-300 hover:bg-gray-100 text-gray-700'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo $categoryId > 0 ? '&category=' . $categoryId : ''; ?>" class="px-3 py-1 rounded border border-gray-300 text-sm hover:bg-gray-100">
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
                <a href="index.php" class="text-blue-600 hover:text-blue-800 mt-2 inline-block">
                    <i class="fas fa-times-circle mr-1"></i> Reset Filter
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php if (isset($_SESSION['debug_image'])): ?>
    <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4">
        <div class="flex items-center">
            <i class="fas fa-info-circle mr-2"></i>
            <span>Debug - Nama gambar: "<?php echo $_SESSION['debug_image']; ?>"</span>
        </div>
    </div>
    <?php unset($_SESSION['debug_image']); ?>
<?php endif; ?>

<?php include '../inc/footer.php'; ?>