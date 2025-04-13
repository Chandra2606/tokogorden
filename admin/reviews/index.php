<?php
$page_title = 'Manajemen Ulasan';
require_once '../../config/config.php';

if (!isLoggedIn() || !isAdmin()) {
    setFlashMessage('error', 'Anda tidak memiliki akses ke halaman ini');
    redirect('login.php');
}

$reviewModel = new Review($conn);
$productModel = new Product($conn);
$userModel = new User($conn);

$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($currentPage - 1) * $limit;

$rating = isset($_GET['rating']) ? (int)$_GET['rating'] : 0;

$searchQuery = isset($_GET['search']) ? sanitize($_GET['search']) : '';

$query = "SELECT r.*, p.name as product_name, p.slug as product_slug, u.name as user_name 
          FROM reviews r
          JOIN products p ON r.product_id = p.id
          JOIN users u ON r.user_id = u.id
          WHERE 1=1";

$countQuery = "SELECT COUNT(*) as total FROM reviews r
               JOIN products p ON r.product_id = p.id
               JOIN users u ON r.user_id = u.id
               WHERE 1=1";

$queryParams = [];
$types = "";

if ($rating > 0) {
    $query .= " AND r.rating = ?";
    $countQuery .= " AND r.rating = ?";
    $queryParams[] = $rating;
    $types .= "i";
}

if (!empty($searchQuery)) {
    $query .= " AND (p.name LIKE ? OR u.name LIKE ?)";
    $countQuery .= " AND (p.name LIKE ? OR u.name LIKE ?)";
    $searchValue = "%$searchQuery%";
    $queryParams[] = $searchValue;
    $queryParams[] = $searchValue;
    $types .= "ss";
}

$query .= " ORDER BY r.created_at DESC LIMIT ?, ?";
$queryParams[] = $offset;
$queryParams[] = $limit;
$types .= "ii";

$stmt = $conn->prepare($query);
if (!empty($queryParams)) {
    $stmt->bind_param($types, ...$queryParams);
}
$stmt->execute();
$reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$countStmt = $conn->prepare($countQuery);
if (!empty($queryParams) && count($queryParams) > 0) {
    $countParams = array_slice($queryParams, 0, count($queryParams) - 2);
    if (count($countParams) > 0) {
        $countTypes = substr($types, 0, strlen($types) - 2);
        $countStmt->bind_param($countTypes, ...$countParams);
    }
}
$countStmt->execute();
$totalReviews = $countStmt->get_result()->fetch_assoc()['total'];

$totalPages = ceil($totalReviews / $limit);

include '../inc/header.php';
?>

<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-semibold">Manajemen Ulasan Produk</h1>
</div>

<!-- Filter -->
<div class="bg-white rounded-lg shadow-md p-4 mb-6">
    <form action="" method="GET" class="flex flex-wrap items-end gap-4">
        <div>
            <label for="rating" class="block text-sm font-medium text-gray-700 mb-1">Rating</label>
            <select id="rating" name="rating" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                <option value="0" <?php echo $rating === 0 ? 'selected' : ''; ?>>Semua Rating</option>
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <option value="<?php echo $i; ?>" <?php echo $rating === $i ? 'selected' : ''; ?>>
                        <?php echo $i; ?> Bintang
                    </option>
                <?php endfor; ?>
            </select>
        </div>
        <div>
            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
            <input type="text" id="search" name="search" value="<?php echo $searchQuery; ?>" placeholder="Cari produk atau pengguna..." class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none">
                <i class="fas fa-search mr-2"></i>
                Filter
            </button>
            <?php if ($rating > 0 || !empty($searchQuery)): ?>
                <a href="index.php" class="ml-2 inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                    <i class="fas fa-times mr-2"></i>
                    Reset
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Daftar Ulasan -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <?php if (count($reviews) > 0): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengguna</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ulasan</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($reviews as $review): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="<?php echo BASE_URL . 'product.php?slug=' . $review['product_slug']; ?>" class="text-blue-600 hover:text-blue-800">
                                    <?php echo htmlspecialchars($review['product_name']); ?>
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php echo htmlspecialchars($review['user_name']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex text-yellow-400">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= $review['rating']): ?>
                                            <i class="fas fa-star"></i>
                                        <?php else: ?>
                                            <i class="far fa-star"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 line-clamp-2">
                                    <?php echo htmlspecialchars($review['review']); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('d/m/Y H:i', strtotime($review['created_at'])); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-500">
                        Menampilkan <?php echo count($reviews); ?> dari <?php echo $totalReviews; ?> ulasan
                    </div>
                    <div class="flex space-x-1">
                        <?php if ($currentPage > 1): ?>
                            <a href="?page=<?php echo $currentPage - 1; ?><?php echo $rating ? '&rating=' . $rating : ''; ?><?php echo $searchQuery ? '&search=' . urlencode($searchQuery) : ''; ?>"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                <i class="fas fa-chevron-left mr-2"></i> Sebelumnya
                            </a>
                        <?php endif; ?>

                        <?php if ($currentPage < $totalPages): ?>
                            <a href="?page=<?php echo $currentPage + 1; ?><?php echo $rating ? '&rating=' . $rating : ''; ?><?php echo $searchQuery ? '&search=' . urlencode($searchQuery) : ''; ?>"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                Selanjutnya <i class="fas fa-chevron-right ml-2"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="p-6 text-center">
            <div class="mb-4">
                <i class="fas fa-comments text-gray-300 text-5xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-1">Tidak ada ulasan</h3>
            <p class="text-gray-500">Belum ada ulasan produk yang tersedia atau sesuai dengan filter yang dipilih.</p>
        </div>
    <?php endif; ?>
</div>

<?php include '../inc/footer.php'; ?>