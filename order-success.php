<?php
$active_page = 'order-success';
$page_title = 'Pesanan Berhasil';
$show_breadcrumb = true;

require_once 'config/config.php';
require_once 'lib/models/Order.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('customer/orders.php');
}

$order_id = intval($_GET['id']);
$orderModel = new Order($conn);
$order = $orderModel->getById($order_id);

if (!$order || $order['user_id'] != $_SESSION['user_id']) {
    redirect('customer/orders.php');
}

$breadcrumb_items = [
    [
        'label' => 'Keranjang Belanja',
        'url' => 'cart.php'
    ],
    [
        'label' => 'Checkout',
        'url' => 'checkout.php'
    ],
    [
        'label' => 'Pesanan Berhasil'
    ]
];

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6 text-center">
            <div class="mb-6">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full text-green-600 mb-4">
                    <i class="fas fa-check-circle text-4xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Pesanan Berhasil Dibuat!</h1>
                <p class="text-gray-600 mt-2">Terima kasih atas pesanan Anda. Berikut adalah detail pesanan Anda.</p>
            </div>

            <div class="bg-gray-50 rounded-lg p-6 mb-6 text-left">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="text-sm text-gray-500">Nomor Pesanan</div>
                        <div class="font-medium">#<?php echo $order['id']; ?></div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Tanggal Pemesanan</div>
                        <div class="font-medium"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Status Pesanan</div>
                        <div class="font-medium">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Metode Pembayaran</div>
                        <div class="font-medium">
                            <?php
                            $paymentMethods = [
                                'transfer_bank' => 'Transfer Bank',
                                'cod' => 'Cash on Delivery (COD)'
                            ];
                            echo isset($paymentMethods[$order['payment_method']]) ? $paymentMethods[$order['payment_method']] : $order['payment_method'];
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mb-6">
                <div class="text-xl font-bold text-gray-900 mb-2">Total Pembayaran</div>
                <div class="text-3xl font-bold text-green-600"><?php echo formatRupiah($order['total_price']); ?></div>
            </div>

            <?php if (isset($paymentMethods[$order['payment_method']]) && $order['payment_method'] == 'transfer_bank'): ?>
                <div class="bg-blue-50 rounded-lg p-6 mb-6 text-left">
                    <h3 class="text-lg font-semibold text-blue-900 mb-2">Instruksi Pembayaran</h3>
                    <p class="text-blue-800 mb-4">Silakan transfer ke rekening berikut dalam waktu 24 jam:</p>
                    <div class="bg-white p-4 rounded-lg border border-blue-200 mb-4">
                        <div class="mb-2">
                            <div class="text-sm text-gray-500">Bank</div>
                            <div class="font-medium">Bank BCA</div>
                        </div>
                        <div class="mb-2">
                            <div class="text-sm text-gray-500">Nomor Rekening</div>
                            <div class="font-medium">1234567890</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">Atas Nama</div>
                            <div class="font-medium">PT Toko Gorden Indonesia</div>
                        </div>
                    </div>
                    <p class="text-sm text-blue-700">
                        <i class="fas fa-info-circle mr-1"></i> Setelah melakukan pembayaran, silakan konfirmasi melalui WhatsApp di nomor 0812-3456-7890 dengan menyertakan bukti transfer dan nomor pesanan.
                    </p>
                </div>
            <?php elseif (isset($paymentMethods[$order['payment_method']]) && $order['payment_method'] == 'cod'): ?>
                <div class="bg-green-50 rounded-lg p-6 mb-6 text-left">
                    <h3 class="text-lg font-semibold text-green-900 mb-2">Informasi Cash on Delivery (COD)</h3>
                    <div class="bg-white p-4 rounded-lg border border-green-200 mb-4">
                        <div class="flex items-center mb-2">
                            <i class="fas fa-truck text-green-600 mr-2 text-xl"></i>
                            <div>
                                <div class="font-medium">Pesanan Anda Akan Segera Dikirim</div>
                                <div class="text-sm text-gray-600">Pembayaran akan dilakukan saat barang diterima</div>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-money-bill-wave text-green-600 mr-2 text-xl"></i>
                            <div>
                                <div class="font-medium">Siapkan Uang Pas</div>
                                <div class="text-sm text-gray-600">Kurir kami akan menghubungi Anda sebelum pengiriman</div>
                            </div>
                        </div>
                    </div>
                    <p class="text-sm text-green-700">
                        <i class="fas fa-info-circle mr-1"></i> Pastikan alamat dan nomor telepon Anda sudah benar. Kami akan menghubungi Anda untuk mengonfirmasi pengiriman.
                    </p>
                </div>
            <?php endif; ?>

            <div class="flex justify-center space-x-4">
                <a href="customer/orders.php" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg inline-flex items-center">
                    <i class="fas fa-list-alt mr-2"></i> Lihat Pesanan Saya
                </a>
                <a href="products.php" class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-6 rounded-lg inline-flex items-center">
                    <i class="fas fa-shopping-bag mr-2"></i> Lanjut Belanja
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>