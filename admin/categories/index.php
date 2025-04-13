<?php
$active_page = 'categories';
$page_title = 'Kelola Kategori';

require_once '../../config/config.php';
require_once '../../lib/models/Category.php';
require_once '../../lib/models/Product.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$categoryModel = new Category($conn);
$productModel = new Product($conn);

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Handle search
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Get categories
if (!empty($search)) {
    // Cari kategori berdasarkan nama (asumsi fungsi search ada, jika tidak ada perlu ditambahkan di model)
    $categories = $categoryModel->search($search, $limit, $offset);
    $totalCategories = $categoryModel->countSearch($search);
} else {
    $categories = $categoryModel->getAll($limit, $offset);
    $totalCategories = $categoryModel->countAll();
}

$totalPages = ceil($totalCategories / $limit);

include '../inc/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Kelola Kategori</h1>
    <a href="create.php" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded inline-flex items-center">
        <i class="fas fa-plus mr-2"></i> Tambah Kategori
    </a>
</div>

<div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
    <div class="p-4 border-b border-gray-200">
        <form action="" method="GET" class="flex flex-wrap items-end space-x-4">
            <div class="w-full md:w-auto mb-4 md:mb-0">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Cari Kategori</label>
                <input type="text" id="search" name="search" value="<?php echo $search; ?>" placeholder="Nama kategori..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded inline-flex items-center">
                    <i class="fas fa-search mr-2"></i> Cari
                </button>
                <?php if (!empty($search)): ?>
                    <a href="index.php" class="ml-2 text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times-circle"></i> Reset
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg">
            <?php
            echo $_SESSION['success_message'];
            unset($_SESSION['success_message']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg">
            <?php
            echo $_SESSION['error_message'];
            unset($_SESSION['error_message']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($categories)): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Kategori</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Produk</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($categories as $category): ?>
                        <?php $productCount = $categoryModel->getProductCount($category['id']); ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    <?php echo $category['name']; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">
                                    <?php echo $category['slug']; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <?php echo $productCount; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="edit.php?id=<?php echo $category['id']; ?>" class="text-blue-600 hover:text-blue-900" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?php echo BASE_URL . 'category.php?slug=' . $category['slug']; ?>" target="_blank" class="text-green-600 hover:text-green-900" title="Lihat">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if ($productCount == 0): ?>
                                        <a href="delete.php?id=<?php echo $category['id']; ?>" class="text-red-600 hover:text-red-900"
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus kategori ini?');" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-gray-400 cursor-not-allowed" title="Tidak dapat dihapus karena masih memiliki produk">
                                            <i class="fas fa-trash"></i>
                                        </span>
                                    <?php endif; ?>
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
                        Menampilkan <?php echo ($page - 1) * $limit + 1; ?> sampai <?php echo min($page * $limit, $totalCategories); ?> dari <?php echo $totalCategories; ?> kategori
                    </div>
                    <div class="flex space-x-1">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="px-3 py-1 rounded border border-gray-300 text-sm hover:bg-gray-100">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>

                        <?php
                        $startPage = max(1, $page - 2);
                        $endPage = min($totalPages, $page + 2);

                        for ($i = $startPage; $i <= $endPage; $i++):
                        ?>
                            <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="px-3 py-1 rounded border <?php echo $i == $page ? 'bg-blue-600 text-white' : 'border-gray-300 hover:bg-gray-100 text-gray-700'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="px-3 py-1 rounded border border-gray-300 text-sm hover:bg-gray-100">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="p-6 text-center">
            <p class="text-gray-500">Tidak ada kategori yang ditemukan.</p>
            <?php if (!empty($search)): ?>
                <a href="index.php" class="text-blue-600 hover:text-blue-800 mt-2 inline-block">
                    <i class="fas fa-times-circle mr-1"></i> Reset Filter
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../inc/footer.php'; ?>