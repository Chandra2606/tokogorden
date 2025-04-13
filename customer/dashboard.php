<?php
$page_title = 'Dashboard Pelanggan';
require_once '../config/config.php';

require_once '../lib/models/User.php';

if (!isLoggedIn() || isAdmin()) {
    redirect('login.php');
}

$userId = $_SESSION['user_id'];
$userModel = new User($conn);
$user = $userModel->getById($userId);

$orderModel = new Order($conn);
$recentOrders = $orderModel->getByUserId($userId, 5); // 5 pesanan terakhir

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
                            <a href="dashboard.php" class="flex items-center p-3 rounded-lg bg-blue-50 text-blue-700">
                                <i class="fas fa-tachometer-alt w-6"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="orders.php" class="flex items-center p-3 rounded-lg hover:bg-gray-100 transition-colors">
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
            <!-- Greeting & Summary -->
            <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
                <div class="p-6 border-b">
                    <h1 class="text-2xl font-semibold">Selamat datang, <?php echo explode(' ', $user['name'])[0]; ?>!</h1>
                    <p class="text-gray-600 mt-1">Dashboard akun Anda, tempat mengelola pesanan dan informasi akun.</p>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500">Total Pesanan</p>
                                <p class="text-2xl font-semibold"><?php echo $orderModel->countByUserId($userId); ?></p>
                            </div>
                            <div class="rounded-full bg-blue-100 p-3 text-blue-600">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500">Pesanan Selesai</p>
                                <p class="text-2xl font-semibold"><?php echo $orderModel->countByUserIdAndStatus($userId, 'completed'); ?></p>
                            </div>
                            <div class="rounded-full bg-green-100 p-3 text-green-600">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500">Dalam Proses</p>
                                <p class="text-2xl font-semibold"><?php echo $orderModel->countPendingByUserId($userId); ?></p>
                            </div>
                            <div class="rounded-full bg-yellow-100 p-3 text-yellow-600">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
                <div class="p-6 border-b flex justify-between items-center">
                    <h2 class="text-xl font-semibold">Pesanan Terbaru</h2>
                    <a href="orders.php" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>

                <?php if (count($recentOrders) > 0): ?>
                    <div class="divide-y">
                        <?php foreach ($recentOrders as $order): ?>
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
                                        <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                                            <i class="fas fa-eye mr-2"></i> Detail
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="p-6 text-center">
                        <div class="py-8">
                            <div class="text-gray-400 mb-4">
                                <i class="fas fa-shopping-bag text-5xl"></i>
                            </div>
                            <p class="text-gray-500">Anda belum memiliki pesanan.</p>
                            <a href="../index.php" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none">
                                <i class="fas fa-shopping-cart mr-2"></i> Mulai Belanja
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Account Info Card -->
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <div class="p-6 border-b">
                    <h2 class="text-xl font-semibold">Informasi Akun</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Nama Lengkap</p>
                            <p class="font-medium"><?php echo htmlspecialchars($user['name']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Email</p>
                            <p class="font-medium"><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">No. Telepon</p>
                            <p class="font-medium"><?php echo !empty($user['phone']) ? htmlspecialchars($user['phone']) : '<span class="text-gray-400">Belum ada</span>'; ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Terdaftar Sejak</p>
                            <p class="font-medium"><?php echo date('d F Y', strtotime($user['created_at'])); ?></p>
                        </div>
                    </div>
                    <div class="mt-6">
                        <a href="profile.php" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                            <i class="fas fa-user-edit mr-2"></i> Edit Profil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>