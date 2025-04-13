<?php
$page_title = 'Pesanan Saya';
require_once '../config/config.php';

if (!isLoggedIn() || isAdmin()) {
    redirect('login.php');
}

$userId = $_SESSION['user_id'];
$userModel = new User($conn);
$user = $userModel->getById($userId);

$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$orderModel = new Order($conn);

if (!empty($status)) {
    $totalOrders = $orderModel->countByUserIdAndStatus($userId, $status);
    $orders = $orderModel->getByUserIdAndStatus($userId, $status, $limit, $offset);
} else {
    $totalOrders = $orderModel->countByUserId($userId);
    $orders = $orderModel->getByUserId($userId, $limit, $offset);
}

$totalPages = ceil($totalOrders / $limit);

include '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row gap-8">
        <!-- Sidebar Menu -->
        <div class="w-full md:w-1/4">
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <div class="p-6 bg-blue-600 text-white">
                    <div class="flex items-center space-x-4">
                        <div class="rounded-full bg-white/30 p-3">
                            <i class="fas fa-user text-2xl"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-semibold"><?php echo htmlspecialchars($user['name']); ?></h2>
                            <p class="text-sm opacity-80"><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                    </div>
                </div>
                <nav class="p-4">
                    <ul class="space-y-2">
                        <li>
                            <a href="dashboard.php" class="flex items-center p-3 rounded-lg hover:bg-gray-100 transition-colors">
                                <i class="fas fa-tachometer-alt w-6"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="orders.php" class="flex items-center p-3 rounded-lg bg-blue-50 text-blue-700">
                                <i class="fas fa-shopping-bag w-6"></i>
                                <span>Pesanan Saya</span>
                            </a>
                        </li>
                        <li>
                            <a href="addresses.php" class="flex items-center p-3 rounded-lg hover:bg-gray-100 transition-colors">
                                <i class="fas fa-map-marker-alt w-6"></i>
                                <span>Alamat Pengiriman</span>
                            </a>
                        </li>
                        <li>
                            <a href="profile.php" class="flex items-center p-3 rounded-lg hover:bg-gray-100 transition-colors">
                                <i class="fas fa-user-edit w-6"></i>
                                <span>Edit Profil</span>
                            </a>
                        </li>
                        <li>
                            <a href="change-password.php" class="flex items-center p-3 rounded-lg hover:bg-gray-100 transition-colors">
                                <i class="fas fa-lock w-6"></i>
                                <span>Ubah Password</span>
                            </a>
                        </li>
                        <li>
                            <a href="wishlist.php" class="flex items-center p-3 rounded-lg hover:bg-gray-100 transition-colors">
                                <i class="fas fa-heart w-6"></i>
                                <span>Daftar Keinginan</span>
                            </a>
                        </li>
                        <li>
                            <a href="../logout.php" class="flex items-center p-3 rounded-lg hover:bg-red-50 text-red-600 transition-colors">
                                <i class="fas fa-sign-out-alt w-6"></i>
                                <span>Keluar</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="w-full md:w-3/4">
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <div class="p-6 border-b flex justify-between items-center">
                    <h1 class="text-2xl font-semibold">Pesanan Saya</h1>
                </div>

                <!-- Filter -->
                <div class="p-4 bg-gray-50 border-b">
                    <div class="flex flex-wrap gap-2">
                        <a href="orders.php" class="px-4 py-2 rounded-md text-sm font-medium <?php echo empty($status) ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'; ?>">
                            Semua
                        </a>
                        <a href="orders.php?status=pending" class="px-4 py-2 rounded-md text-sm font-medium <?php echo $status === 'pending' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'; ?>">
                            Menunggu Pembayaran
                        </a>
                        <a href="orders.php?status=processing" class="px-4 py-2 rounded-md text-sm font-medium <?php echo $status === 'processing' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'; ?>">
                            Diproses
                        </a>
                        <a href="orders.php?status=shipped" class="px-4 py-2 rounded-md text-sm font-medium <?php echo $status === 'shipped' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'; ?>">
                            Dikirim
                        </a>
                        <a href="orders.php?status=delivered" class="px-4 py-2 rounded-md text-sm font-medium <?php echo $status === 'delivered' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'; ?>">
                            Diterima
                        </a>
                        <a href="orders.php?status=completed" class="px-4 py-2 rounded-md text-sm font-medium <?php echo $status === 'completed' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'; ?>">
                            Selesai
                        </a>
                        <a href="orders.php?status=cancelled" class="px-4 py-2 rounded-md text-sm font-medium <?php echo $status === 'cancelled' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'; ?>">
                            Dibatalkan
                        </a>
                    </div>
                </div>

                <?php if (count($orders) > 0): ?>
                    <div class="divide-y">
                        <?php foreach ($orders as $order): ?>
                            <div class="p-6 hover:bg-gray-50 transition-colors">
                                <div class="flex flex-col md:flex-row md:justify-between md:items-center space-y-4 md:space-y-0">
                                    <div>
                                        <p class="text-gray-500 text-sm">Order #<?php echo $order['id']; ?></p>
                                        <p class="font-semibold mt-1"><?php echo formatRupiah($order['total_price']); ?></p>
                                        <p class="text-sm text-gray-500 mt-1"><?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?></p>
                                    </div>

                                    <div>
                                        <?php
                                        $statusColors = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'processing' => 'bg-blue-100 text-blue-800',
                                            'shipped' => 'bg-indigo-100 text-indigo-800',
                                            'delivered' => 'bg-green-100 text-green-800',
                                            'completed' => 'bg-green-100 text-green-800',
                                            'cancelled' => 'bg-red-100 text-red-800',
                                        ];
                                        $statusLabels = [
                                            'pending' => 'Menunggu Pembayaran',
                                            'processing' => 'Diproses',
                                            'shipped' => 'Dikirim',
                                            'delivered' => 'Terkirim',
                                            'completed' => 'Selesai',
                                            'cancelled' => 'Dibatalkan',
                                        ];
                                        $statusColor = isset($statusColors[$order['status']]) ? $statusColors[$order['status']] : 'bg-gray-100 text-gray-800';
                                        $statusLabel = isset($statusLabels[$order['status']]) ? $statusLabels[$order['status']] : ucfirst($order['status']);
                                        ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $statusColor; ?>">
                                            <?php echo $statusLabel; ?>
                                        </span>
                                    </div>

                                    <div>
                                        <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="btn btn-primary btn-sm">Lihat Detail</a>
                                        <?php if ($order['status'] == 'delivered'): ?>
                                            <a href="confirm-complete.php?id=<?php echo $order['id']; ?>" class="btn btn-success btn-sm">Konfirmasi Selesai</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($totalPages > 1): ?>
                        <!-- Pagination -->
                        <div class="px-6 py-4 bg-gray-50 border-t">
                            <div class="flex justify-between items-center">
                                <div class="text-sm text-gray-500">
                                    Menampilkan <?php echo min(($page - 1) * $limit + 1, $totalOrders); ?> - <?php echo min($page * $limit, $totalOrders); ?> dari <?php echo $totalOrders; ?> pesanan
                                </div>
                                <div class="flex space-x-1">
                                    <?php if ($page > 1): ?>
                                        <a href="?page=<?php echo $page - 1; ?><?php echo !empty($status) ? '&status=' . $status : ''; ?>" class="px-3 py-1 rounded border border-gray-300 text-sm hover:bg-gray-100">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    <?php endif; ?>

                                    <?php
                                    $startPage = max(1, $page - 2);
                                    $endPage = min($totalPages, $page + 2);

                                    for ($i = $startPage; $i <= $endPage; $i++):
                                    ?>
                                        <a href="?page=<?php echo $i; ?><?php echo !empty($status) ? '&status=' . $status : ''; ?>" class="px-3 py-1 rounded border <?php echo $i == $page ? 'bg-blue-600 text-white' : 'border-gray-300 text-gray-700 hover:bg-gray-100'; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php endfor; ?>

                                    <?php if ($page < $totalPages): ?>
                                        <a href="?page=<?php echo $page + 1; ?><?php echo !empty($status) ? '&status=' . $status : ''; ?>" class="px-3 py-1 rounded border border-gray-300 text-sm hover:bg-gray-100">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="p-6 text-center">
                        <div class="py-8">
                            <div class="text-gray-400 mb-4">
                                <i class="fas fa-shopping-bag text-5xl"></i>
                            </div>
                            <?php if (empty($status)): ?>
                                <p class="text-gray-500">Anda belum memiliki pesanan.</p>
                            <?php else: ?>
                                <p class="text-gray-500">Anda belum memiliki pesanan dengan status "<?php echo $statusLabels[$status] ?? ucfirst($status); ?>".</p>
                            <?php endif; ?>
                            <a href="../index.php" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none">
                                <i class="fas fa-shopping-cart mr-2"></i> Mulai Belanja
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>