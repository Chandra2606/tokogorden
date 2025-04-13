<?php
$active_page = 'orders';
$page_title = 'Detail Pesanan';

require_once '../../config/config.php';
require_once '../../lib/models/Order.php';
require_once '../../lib/models/Product.php';

// Cek login admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

// Cek ID pesanan
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = 'ID pesanan tidak valid';
    redirect('admin/orders/index.php');
}

$orderId = intval($_GET['id']);
$orderModel = new Order($conn);
$productModel = new Product($conn);

// Ambil data pesanan
$order = $orderModel->getById($orderId);
if (!$order) {
    $_SESSION['error_message'] = 'Pesanan tidak ditemukan';
    redirect('admin/orders/index.php');
}

// Ambil item pesanan
$orderItems = $orderModel->getOrderItems($orderId);

// Status pesanan dalam bahasa indonesia
$orderStatuses = [
    'pending' => 'Menunggu Pembayaran',
    'processing' => 'Diproses',
    'shipped' => 'Dikirim',
    'delivered' => 'Selesai',
    'completed' => 'Telah Selesai',
    'cancelled' => 'Dibatalkan'
];

// Status pembayaran dalam bahasa indonesia
$paymentStatuses = [
    'unpaid' => 'Belum Dibayar',
    'paid' => 'Sudah Dibayar',
    'failed' => 'Gagal',
    'refunded' => 'Dikembalikan'
];

// Status badge colors
$statusColors = [
    'pending' => 'yellow',
    'processing' => 'blue',
    'shipped' => 'indigo',
    'delivered' => 'green',
    'completed' => 'green',
    'cancelled' => 'red',
    'unpaid' => 'yellow',
    'paid' => 'green',
    'failed' => 'red',
    'refunded' => 'gray'
];

// Metode pembayaran
$paymentMethods = [
    'transfer_bank' => 'Transfer Bank',
    'cod' => 'Cash on Delivery (COD)'
];

include '../inc/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Detail Pesanan #<?php echo $orderId; ?></h1>
    <div class="flex space-x-2">
        <a href="update-status.php?id=<?php echo $orderId; ?>" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded inline-flex items-center">
            <i class="fas fa-edit mr-2"></i> Update Status
        </a>
        <a href="index.php" class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded inline-flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Informasi Pesanan -->
    <div class="lg:col-span-2">
        <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold">Informasi Pesanan</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">ID Pesanan</p>
                        <p class="font-medium">#<?php echo $order['id']; ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Tanggal Pemesanan</p>
                        <p class="font-medium"><?php echo date('d F Y, H:i', strtotime($order['created_at'])); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Status Pesanan</p>
                        <p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-<?php echo $statusColors[$order['status']]; ?>-100 text-<?php echo $statusColors[$order['status']]; ?>-800">
                                <?php echo $orderStatuses[$order['status']] ?? $order['status']; ?>
                            </span>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Status Pembayaran</p>
                        <p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-<?php echo $statusColors[$order['payment_status']]; ?>-100 text-<?php echo $statusColors[$order['payment_status']]; ?>-800">
                                <?php echo $paymentStatuses[$order['payment_status']] ?? $order['payment_status']; ?>
                            </span>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Metode Pembayaran</p>
                        <p class="font-medium"><?php echo $paymentMethods[$order['payment_method']] ?? $order['payment_method']; ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Total Pembayaran</p>
                        <p class="font-medium text-lg text-green-600"><?php echo formatRupiah($order['total_price']); ?></p>
                    </div>
                </div>

                <?php if (!empty($order['notes'])): ?>
                    <div class="mt-4 p-4 bg-gray-50 rounded-md">
                        <p class="text-sm text-gray-600 mb-1">Catatan</p>
                        <p><?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Informasi Pelanggan -->
    <div>
        <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold">Informasi Pelanggan</h2>
            </div>
            <div class="p-6">
                <div class="mb-4">
                    <p class="text-sm text-gray-600 mb-1">Nama</p>
                    <p class="font-medium"><?php echo htmlspecialchars($order['user_name']); ?></p>
                </div>
                <div class="mb-4">
                    <p class="text-sm text-gray-600 mb-1">Email</p>
                    <p class="font-medium"><?php echo htmlspecialchars($order['user_email']); ?></p>
                </div>
                <div class="mb-4">
                    <p class="text-sm text-gray-600 mb-1">No. Telepon</p>
                    <p class="font-medium"><?php echo htmlspecialchars($order['phone']); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Alamat Pengiriman</p>
                    <p><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Item Pesanan -->
<div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
    <div class="p-6 border-b border-gray-200">
        <h2 class="text-lg font-semibold">Item Pesanan</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Produk
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Harga
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Jumlah
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Diskon
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Subtotal
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php
                $subtotal = 0;
                $total_discount = 0;

                foreach ($orderItems as $item):
                    $itemSubtotal = $item['price'] * $item['quantity'];
                    $subtotal += $itemSubtotal;
                    $total_discount += $item['discount_amount'] * $item['quantity'];
                ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <img class="h-10 w-10 rounded-md object-cover"
                                        src="<?php echo !empty($item['product_image']) ? BASE_URL . 'assets/images/products/' . $item['product_image'] : BASE_URL . 'assets/images/products/default-product.jpg'; ?>"
                                        alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($item['product_name']); ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        ID: <?php echo $item['product_id']; ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo formatRupiah($item['price']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo $item['quantity']; ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo formatRupiah($item['discount_amount']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo formatRupiah($itemSubtotal); ?></div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="bg-gray-50">
                <tr>
                    <td colspan="3" class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        Subtotal
                    </td>
                    <td colspan="2" class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?php echo formatRupiah($subtotal); ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        Total Diskon
                    </td>
                    <td colspan="2" class="px-6 py-4 whitespace-nowrap text-sm text-red-600">
                        -<?php echo formatRupiah($total_discount); ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        Total
                    </td>
                    <td colspan="2" class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                        <?php echo formatRupiah($order['total_price']); ?>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
    <div class="p-6 border-b border-gray-200">
        <h2 class="text-lg font-semibold">Aksi</h2>
    </div>
    <div class="p-6">
        <div class="flex space-x-2">
            <a href="update-status.php?id=<?php echo $orderId; ?>" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded inline-flex items-center">
                <i class="fas fa-edit mr-2"></i> Update Status
            </a>
            <?php if ($order['status'] === 'pending'): ?>
                <a href="#" onclick="if(confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')) window.location.href='update-status.php?id=<?php echo $orderId; ?>&quick_action=cancel'"
                    class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded inline-flex items-center">
                    <i class="fas fa-times mr-2"></i> Batalkan Pesanan
                </a>
            <?php endif; ?>
            <?php if ($order['status'] === 'pending' && $order['payment_status'] === 'unpaid'): ?>
                <a href="#" onclick="if(confirm('Konfirmasi pembayaran untuk pesanan ini?')) window.location.href='update-status.php?id=<?php echo $orderId; ?>&quick_action=confirm_payment'"
                    class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded inline-flex items-center">
                    <i class="fas fa-check mr-2"></i> Konfirmasi Pembayaran
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../inc/footer.php'; ?>