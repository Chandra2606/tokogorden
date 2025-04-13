<?php
$active_page = 'orders';
$page_title = 'Kelola Pesanan';

require_once '../../config/config.php';
require_once '../../lib/models/Order.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$orderModel = new Order($conn);

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Filter berdasarkan status
$status = isset($_GET['status']) && !empty($_GET['status']) ? sanitize($_GET['status']) : null;

// Filter berdasarkan tanggal
$startDate = isset($_GET['start_date']) && !empty($_GET['start_date']) ? sanitize($_GET['start_date']) : null;
$endDate = isset($_GET['end_date']) && !empty($_GET['end_date']) ? sanitize($_GET['end_date']) : null;

// Mendapatkan semua order
$totalOrders = $orderModel->countAll();
$orders = $orderModel->getAll($limit, $offset);

include '../inc/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Kelola Pesanan</h1>
</div>

<div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
    <div class="p-4 border-b border-gray-200">
        <form action="" method="GET" class="flex flex-wrap gap-4">
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status Pesanan</label>
                <select id="status" name="status" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Status</option>
                    <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Menunggu Pembayaran</option>
                    <option value="processing" <?php echo $status === 'processing' ? 'selected' : ''; ?>>Diproses</option>
                    <option value="shipped" <?php echo $status === 'shipped' ? 'selected' : ''; ?>>Dikirim</option>
                    <option value="delivered" <?php echo $status === 'delivered' ? 'selected' : ''; ?>>Selesai</option>
                    <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Dibatalkan</option>
                </select>
            </div>

            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                <input type="date" id="start_date" name="start_date" value="<?php echo $startDate; ?>"
                    class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
                <input type="date" id="end_date" name="end_date" value="<?php echo $endDate; ?>"
                    class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="flex items-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">
                    <i class="fas fa-filter mr-2"></i> Filter
                </button>

                <?php if ($status || $startDate || $endDate): ?>
                    <a href="index.php" class="ml-2 text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times-circle"></i> Reset
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="p-4 text-sm text-green-700 bg-green-100 rounded-lg">
            <?php
            echo $_SESSION['success_message'];
            unset($_SESSION['success_message']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="p-4 text-sm text-red-700 bg-red-100 rounded-lg">
            <?php
            echo $_SESSION['error_message'];
            unset($_SESSION['error_message']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($orders)): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Pesanan</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pelanggan</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Pesanan</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Pembayaran</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">#<?php echo $order['id']; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo $order['user_name']; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo formatRupiah($order['total_price']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                switch ($order['status']) {
                                    case 'pending':
                                        echo '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Menunggu Pembayaran</span>';
                                        break;
                                    case 'processing':
                                        echo '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Diproses</span>';
                                        break;
                                    case 'shipped':
                                        echo '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">Dikirim</span>';
                                        break;
                                    case 'delivered':
                                        echo '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Selesai</span>';
                                        break;
                                    case 'cancelled':
                                        echo '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Dibatalkan</span>';
                                        break;
                                    default:
                                        echo '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Unknown</span>';
                                }
                                ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($order['payment_status'] == 'paid'): ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Lunas</span>
                                <?php elseif ($order['payment_status'] == 'unpaid'): ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Menunggu</span>
                                <?php elseif ($order['payment_status'] == 'failed'): ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Gagal</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500"><?php echo date("d M Y H:i", strtotime($order['created_at'])); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="detail.php?id=<?php echo $order['id']; ?>" class="text-blue-600 hover:text-blue-900" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="update-status.php?id=<?php echo $order['id']; ?>" class="text-green-600 hover:text-green-900" title="Update Status">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalOrders > $limit): ?>
            <div class="px-6 py-4 bg-gray-50">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-500">
                        Menampilkan <?php echo ($page - 1) * $limit + 1; ?> sampai <?php echo min($page * $limit, $totalOrders); ?> dari <?php echo $totalOrders; ?> pesanan
                    </div>
                    <div class="flex space-x-1">
                        <?php
                        $totalPages = ceil($totalOrders / $limit);
                        $startPage = max(1, $page - 2);
                        $endPage = min($totalPages, $page + 2);

                        // Query parameter untuk pagination
                        $queryParams = [];
                        if ($status) $queryParams[] = "status=" . urlencode($status);
                        if ($startDate) $queryParams[] = "start_date=" . urlencode($startDate);
                        if ($endDate) $queryParams[] = "end_date=" . urlencode($endDate);
                        $queryString = !empty($queryParams) ? '&' . implode('&', $queryParams) : '';

                        if ($page > 1):
                        ?>
                            <a href="?page=<?php echo $page - 1 . $queryString; ?>" class="px-3 py-1 rounded border border-gray-300 text-sm hover:bg-gray-100">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>

                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <a href="?page=<?php echo $i . $queryString; ?>" class="px-3 py-1 rounded border <?php echo $i == $page ? 'bg-blue-600 text-white' : 'border-gray-300 hover:bg-gray-100 text-gray-700'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo $page + 1 . $queryString; ?>" class="px-3 py-1 rounded border border-gray-300 text-sm hover:bg-gray-100">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="p-6 text-center">
            <p class="text-gray-500">Tidak ada pesanan yang ditemukan.</p>
            <?php if ($status || $startDate || $endDate): ?>
                <a href="index.php" class="text-blue-600 hover:text-blue-800 mt-2 inline-block">
                    <i class="fas fa-times-circle mr-1"></i> Reset Filter
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../inc/footer.php'; ?>