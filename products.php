<?php
$page_title = 'Semua Produk';
require_once 'config/config.php';

$productModel = new Product($conn);
$categoryModel = new Category($conn);
$wishlistModel = new Wishlist($conn);

$categories = $categoryModel->getAll();

$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$categoryId = isset($_GET['category']) ? intval($_GET['category']) : 0;
$sort = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'newest';

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

if (!empty($search) && $categoryId > 0) {
    $products = $productModel->searchInCategory($search, $categoryId, $limit, $offset);
    $totalProducts = $productModel->countSearchInCategory($search, $categoryId);
} elseif (!empty($search)) {
    $products = $productModel->search($search, $limit, $offset);
    $totalProducts = $productModel->countSearch($search);
} elseif ($categoryId > 0) {
    $products = $productModel->getByCategoryId($categoryId, $limit, $offset);
    $totalProducts = $productModel->countByCategoryId($categoryId);
} else {
    $products = $productModel->getAll($limit, $offset);
    $totalProducts = $productModel->countAll();
}

if ($sort === 'price_low') {
    usort($products, function ($a, $b) {
        return $a['price'] - $b['price'];
    });
} elseif ($sort === 'price_high') {
    usort($products, function ($a, $b) {
        return $b['price'] - $a['price'];
    });
}

$totalPages = ceil($totalProducts / $limit);

$activeCategory = null;
if ($categoryId > 0) {
    $activeCategory = $categoryModel->getById($categoryId);
}

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold mb-2"><?php echo $activeCategory ? htmlspecialchars($activeCategory['name']) : 'Semua Produk'; ?></h1>
        <div class="text-gray-600">
            <?php if ($totalProducts > 0): ?>
                Menampilkan <?php echo $totalProducts; ?> produk
            <?php else: ?>
                Tidak ada produk ditemukan
            <?php endif; ?>
        </div>
    </div>

    <!-- Filter dan Pencarian -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-8">
        <div class="p-4 border-b border-gray-200">
            <form action="" method="GET" class="flex flex-wrap items-end gap-4">
                <div class="w-full md:w-auto mb-4 md:mb-0">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Cari Produk</label>
                    <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Nama produk..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="w-full md:w-auto mb-4 md:mb-0">
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                    <select id="category" name="category" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="0">Semua Kategori</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo $categoryId == $category['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="w-full md:w-auto mb-4 md:mb-0">
                    <label for="sort" class="block text-sm font-medium text-gray-700 mb-1">Urutkan</label>
                    <select id="sort" name="sort" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Terbaru</option>
                        <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Harga: Rendah ke Tinggi</option>
                        <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Harga: Tinggi ke Rendah</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded inline-flex items-center">
                        <i class="fas fa-search mr-2"></i> Filter
                    </button>
                    <?php if (!empty($search) || $categoryId > 0 || $sort !== 'newest'): ?>
                        <a href="products.php" class="ml-2 text-gray-500 hover:text-gray-700">
                            <i class="fas fa-times-circle"></i> Reset
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Daftar Kategori -->
    <div class="flex flex-wrap gap-2 mb-6">
        <a href="products.php" class="px-4 py-2 rounded-full text-sm <?php echo $categoryId === 0 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
            Semua
        </a>
        <?php foreach ($categories as $category): ?>
            <a href="products.php?category=<?php echo $category['id']; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo $sort !== 'newest' ? '&sort=' . $sort : ''; ?>"
                class="px-4 py-2 rounded-full text-sm <?php echo $categoryId === $category['id'] ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                <?php echo htmlspecialchars($category['name']); ?>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if (count($products) > 0): ?>
        <!-- Grid Produk -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($products as $product): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                    <a href="<?php echo BASE_URL; ?>product.php?slug=<?php echo $product['slug']; ?>" class="block">
                        <div class="h-48 overflow-hidden relative">
                            <img src="<?php echo !empty($product['image']) ? BASE_URL . 'assets/images/products/' . $product['image'] : BASE_URL . 'assets/images/products/default-product.jpg'; ?>"
                                alt="<?php echo htmlspecialchars($product['name']); ?>"
                                class="w-full h-full object-cover transition-transform hover:scale-105">

                            <?php if (isLoggedIn()): ?>
                                <?php $isInWishlist = $wishlistModel->isProductInWishlist($_SESSION['user_id'], $product['id']); ?>
                                <a href="<?php echo BASE_URL . ($isInWishlist ? 'wishlist-remove.php?product_id=' : 'wishlist-add.php?product_id='); ?><?php echo $product['id']; ?>"
                                    class="absolute top-2 right-2 p-2 rounded-full bg-white/70 hover:bg-white text-<?php echo $isInWishlist ? 'red' : 'gray'; ?>-600 hover:text-red-600 transition-colors">
                                    <i class="fas fa-heart"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </a>
                    <div class="p-4">
                        <a href="<?php echo BASE_URL; ?>category.php?slug=<?php echo $product['slug']; ?>" class="text-xs text-blue-600 hover:text-blue-800">
                            <?php echo htmlspecialchars($product['category_name']); ?>
                        </a>
                        <a href="<?php echo BASE_URL; ?>product.php?slug=<?php echo $product['slug']; ?>">
                            <h3 class="font-semibold mt-1 hover:text-blue-600"><?php echo htmlspecialchars($product['name']); ?></h3>
                        </a>
                        <div class="mt-2">
                            <?php
                            $discountedPrice = $productModel->getDiscountedPrice($product);
                            if ($discountedPrice < $product['price']):
                            ?>
                                <span class="text-gray-500 line-through"><?php echo formatRupiah($product['price']); ?></span>
                                <span class="text-red-600 font-semibold ml-1"><?php echo formatRupiah($discountedPrice); ?></span>
                            <?php else: ?>
                                <span class="font-semibold"><?php echo formatRupiah($product['price']); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="mt-3">
                            <a href="<?php echo BASE_URL; ?>cart.php?action=add&id=<?php echo $product['id']; ?>" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg inline-flex items-center justify-center w-full">
                                <i class="fas fa-shopping-cart mr-2"></i> Tambah ke Keranjang
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="mt-8 flex justify-center">
                <nav class="inline-flex rounded-md shadow">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo $categoryId > 0 ? '&category=' . $categoryId : ''; ?><?php echo $sort !== 'newest' ? '&sort=' . $sort : ''; ?>"
                            class="relative inline-flex items-center px-4 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-chevron-left mr-1"></i> Sebelumnya
                        </a>
                    <?php endif; ?>

                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    for ($i = $startPage; $i <= $endPage; $i++):
                    ?>
                        <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo $categoryId > 0 ? '&category=' . $categoryId : ''; ?><?php echo $sort !== 'newest' ? '&sort=' . $sort : ''; ?>"
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium <?php echo $i == $page ? 'text-blue-600 bg-blue-50' : 'text-gray-700 hover:bg-gray-50'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo $categoryId > 0 ? '&category=' . $categoryId : ''; ?><?php echo $sort !== 'newest' ? '&sort=' . $sort : ''; ?>"
                            class="relative inline-flex items-center px-4 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Selanjutnya <i class="fas fa-chevron-right ml-1"></i>
                        </a>
                    <?php endif; ?>
                </nav>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <!-- Tidak ada produk -->
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <div class="text-gray-400 mb-4">
                <i class="fas fa-box-open text-6xl"></i>
            </div>
            <h2 class="text-2xl font-semibold mb-2">Tidak ada produk ditemukan</h2>
            <p class="text-gray-600 mb-6">Coba gunakan filter yang berbeda atau reset pencarian.</p>
            <a href="products.php" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg inline-flex items-center">
                <i class="fas fa-redo mr-2"></i> Lihat Semua Produk
            </a>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>