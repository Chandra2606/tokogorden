<?php
$page_title = 'Detail Pesanan';
require_once '../config/config.php';

if (!isLoggedIn() || isAdmin()) {
    redirect('login.php');
}

$orderId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$orderId) {
    $_SESSION['error_message'] = 'ID pesanan tidak valid';
    redirect('customer/orders.php');
}

$userId = $_SESSION['user_id'];
$userModel = new User($conn);
$user = $userModel->getById($userId);

$orderModel = new Order($conn);
$order = $orderModel->getById($orderId);

if (!$order || $order['user_id'] != $userId) {
    $_SESSION['error_message'] = 'Pesanan tidak ditemukan atau Anda tidak memiliki akses';
    redirect('customer/orders.php');
}

$orderItems = $orderModel->getOrderItems($orderId);

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
            <!-- Order Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-semibold">Detail Pesanan #<?php echo $order['id']; ?></h1>
                    <p class="text-gray-500">Tanggal: <?php echo date('d F Y, H:i', strtotime($order['created_at'])); ?></p>
                </div>
                <a href="orders.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                </a>
            </div>

            <!-- Order Status -->
            <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
                <div class="p-6 border-b">
                    <h2 class="text-xl font-semibold">Status Pesanan</h2>
                </div>
                <div class="p-6">
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
                    <div class="flex items-center">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?php echo $statusColor; ?>">
                            <?php echo $statusLabel; ?>
                        </span>

                        <?php if ($order['status'] === 'pending'): ?>
                            <span class="ml-3 text-sm text-gray-500">Silakan selesaikan pembayaran Anda untuk memproses pesanan ini.</span>
                        <?php endif; ?>
                    </div>

                    <!-- Progress Timeline -->
                    <?php if ($order['status'] !== 'cancelled'): ?>
                        <div class="mt-6 relative">
                            <?php
                            $allStatuses = ['pending', 'processing', 'shipped', 'delivered', 'completed'];
                            $currentStatusIndex = array_search($order['status'], $allStatuses);
                            ?>

                            <div class="absolute left-0 top-5 w-full h-1 bg-gray-200">
                                <div class="h-full bg-green-500" style="width: <?php echo ($currentStatusIndex / (count($allStatuses) - 1)) * 100; ?>%;"></div>
                            </div>

                            <div class="flex justify-between relative">
                                <?php foreach ($allStatuses as $index => $status): ?>
                                    <?php
                                    $isPassed = $index <= $currentStatusIndex;
                                    $dotColor = $isPassed ? 'bg-green-500' : 'bg-gray-300';
                                    $textColor = $isPassed ? 'text-green-600 font-medium' : 'text-gray-400';
                                    ?>
                                    <div class="flex flex-col items-center">
                                        <div class="w-10 h-10 rounded-full <?php echo $dotColor; ?> flex items-center justify-center text-white">
                                            <?php if ($isPassed): ?>
                                                <i class="fas fa-check"></i>
                                            <?php else: ?>
                                                <?php echo $index + 1; ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-xs mt-2 <?php echo $textColor; ?> text-center">
                                            <?php echo $statusLabels[$status]; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Order Items -->
            <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
                <div class="p-6 border-b">
                    <h2 class="text-xl font-semibold">Produk yang Dipesan</h2>
                </div>
                <div class="p-0">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Diskon</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($orderItems as $item): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <img class="h-10 w-10 rounded-md object-cover" src="<?php echo !empty($item['product_image']) ? BASE_URL . 'assets/images/products/' . $item['product_image'] : BASE_URL . 'assets/images/products/default-product.jpg'; ?>" alt="<?php echo $item['product_name']; ?>">
                                                </div>
                                                <div class="ml-4">
                                                    <a href="../product.php?slug=<?php echo $item['product_slug']; ?>" class="text-sm font-medium text-gray-900 hover:text-blue-600">
                                                        <?php echo $item['product_name']; ?>
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo formatRupiah($item['price']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo $item['quantity']; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php
                                            if ($item['discount_amount'] > 0) {
                                                echo '<span class="text-red-600">-' . formatRupiah($item['discount_amount']) . '</span>';
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?php
                                            $subtotal = $item['price'] * $item['quantity'] - $item['discount_amount'];
                                            echo formatRupiah($subtotal);
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="p-6 border-t">
                        <div class="flex justify-end text-sm">
                            <div class="w-1/2 md:w-1/4">
                                <div class="flex justify-between py-2">
                                    <span class="text-gray-600">Subtotal</span>
                                    <span class="font-medium"><?php
                                                                // Hitung subtotal dari item pesanan
                                                                $subtotal = 0;
                                                                foreach ($orderItems as $item) {
                                                                    $subtotal += $item['price'] * $item['quantity'];
                                                                }
                                                                echo formatRupiah($subtotal);
                                                                ?></span>
                                </div>
                                <?php if (isset($order['shipping_cost']) && $order['shipping_cost'] > 0): ?>
                                    <div class="flex justify-between py-2">
                                        <span class="text-gray-600">Biaya Pengiriman</span>
                                        <span class="font-medium"><?php echo formatRupiah($order['shipping_cost']); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if (isset($order['discount_amount']) && $order['discount_amount'] > 0): ?>
                                    <div class="flex justify-between py-2">
                                        <span class="text-gray-600">Diskon</span>
                                        <span class="font-medium text-red-600">-<?php echo formatRupiah($order['discount_amount']); ?></span>
                                    </div>
                                <?php endif; ?>
                                <div class="flex justify-between py-2 border-t border-gray-200 font-semibold">
                                    <span>Total</span>
                                    <span class="text-blue-600"><?php echo formatRupiah($order['total_price']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shipping Information -->
            <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
                <div class="p-6 border-b">
                    <h2 class="text-xl font-semibold">Informasi Pengiriman</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-900 mb-2">Alamat Pengiriman</h3>
                            <p class="text-sm text-gray-600 whitespace-pre-line"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-900 mb-2">Kontak</h3>
                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($order['phone']); ?></p>
                        </div>
                    </div>
                    <?php if (!empty($order['notes'])): ?>
                        <div class="mt-6">
                            <h3 class="text-sm font-medium text-gray-900 mb-2">Catatan Pesanan</h3>
                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($order['notes']); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-4">
                <?php if ($order['status'] === 'pending'): ?>
                    <button class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none">
                        <i class="fas fa-credit-card mr-2"></i> Bayar Sekarang
                    </button>
                <?php elseif ($order['status'] === 'shipped'): ?>
                    <a href="confirm-order.php?id=<?php echo $order['id']; ?>" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none">
                        <i class="fas fa-check-circle mr-2"></i> Konfirmasi Pesanan Diterima
                    </a>
                <?php elseif ($order['status'] === 'delivered'): ?>
                    <a href="confirm-complete.php?id=<?php echo $order['id']; ?>" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none">
                        <i class="fas fa-check-circle mr-2"></i> Konfirmasi Pesanan Selesai
                    </a>
                <?php elseif ($order['status'] === 'completed'): ?>
                    <a href="review.php?order_id=<?php echo $order['id']; ?>" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none">
                        <i class="fas fa-star mr-2"></i> Beri Ulasan Produk
                    </a>
                <?php endif; ?>

                <a href="orders.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                    <i class="fas fa-shopping-bag mr-2"></i> Lihat Semua Pesanan
                </a>
            </div>

            <?php if (
                $order['status'] === 'shipped' ||
                $order['status'] === 'delivered'
            ) : ?>
                <div class="mb-4">
                    <?php if ($order['status'] === 'shipped') : ?>
                        <a href="confirm-order.php?id=<?= $order['id'] ?>" class="btn btn-success">
                            <i class="fas fa-check mr-2"></i>Konfirmasi Penerimaan
                        </a>
                    <?php elseif ($order['status'] === 'delivered') : ?>
                        <a href="confirm-complete.php?id=<?= $order['id'] ?>" class="btn btn-primary">
                            <i class="fas fa-check-circle mr-2"></i>Pesanan Selesai
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($order['notes']) : ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Catatan</h5>
                    </div>
                    <div class="card-body">
                        <p><?= htmlspecialchars($order['notes']) ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>