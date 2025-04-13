<?php
$active_page = 'orders';
$page_title = 'Update Status Pesanan';

require_once '../../config/config.php';
require_once '../../lib/models/Order.php';

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

// Ambil data pesanan
$order = $orderModel->getById($orderId);
if (!$order) {
    $_SESSION['error_message'] = 'Pesanan tidak ditemukan';
    redirect('admin/orders/index.php');
}

// Handle quick actions
if (isset($_GET['quick_action'])) {
    $quickAction = $_GET['quick_action'];

    // Cancel order
    if ($quickAction === 'cancel' && $order['status'] === 'pending') {
        $orderStatusResult = $orderModel->updateStatus($orderId, 'cancelled');

        if ($orderStatusResult) {
            $_SESSION['success_message'] = 'Pesanan berhasil dibatalkan';
            redirect('admin/orders/detail.php?id=' . $orderId);
        } else {
            $_SESSION['error_message'] = 'Gagal membatalkan pesanan';
            redirect('admin/orders/detail.php?id=' . $orderId);
        }
    }

    // Confirm payment
    if ($quickAction === 'confirm_payment' && $order['status'] === 'pending' && $order['payment_status'] === 'unpaid') {
        $paymentStatusResult = $orderModel->updatePaymentStatus($orderId, 'paid');
        $orderStatusResult = $orderModel->updateStatus($orderId, 'processing');

        if ($paymentStatusResult && $orderStatusResult) {
            $_SESSION['success_message'] = 'Pembayaran berhasil dikonfirmasi dan status pesanan diubah menjadi Diproses';
            redirect('admin/orders/detail.php?id=' . $orderId);
        } else {
            $_SESSION['error_message'] = 'Gagal mengkonfirmasi pembayaran';
            redirect('admin/orders/detail.php?id=' . $orderId);
        }
    }
}

$errors = [];

// Proses form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderStatus = sanitize($_POST['order_status']);
    $paymentStatus = sanitize($_POST['payment_status']);

    // Update status pesanan
    $orderStatusResult = $orderModel->updateStatus($orderId, $orderStatus);

    // Update status pembayaran
    $paymentStatusResult = $orderModel->updatePaymentStatus($orderId, $paymentStatus);

    if ($orderStatusResult && $paymentStatusResult) {
        $_SESSION['success_message'] = 'Status pesanan berhasil diperbarui';
        redirect('admin/orders/detail.php?id=' . $orderId);
    } else {
        $errors[] = 'Gagal memperbarui status pesanan';
    }
}

include '../inc/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Update Status Pesanan #<?php echo $orderId; ?></h1>
    <a href="detail.php?id=<?php echo $orderId; ?>" class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded inline-flex items-center">
        <i class="fas fa-arrow-left mr-2"></i> Kembali
    </a>
</div>

<div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
    <div class="p-6">
        <?php if (!empty($errors)): ?>
            <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg">
                <ul class="list-disc pl-4">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="order_status" class="block text-sm font-medium text-gray-700 mb-1">Status Pesanan</label>
                    <select id="order_status" name="order_status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Menunggu Pembayaran</option>
                        <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Diproses</option>
                        <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Dikirim</option>
                        <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Selesai</option>
                        <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Dibatalkan</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Status pesanan saat ini:
                        <span class="font-medium">
                            <?php
                            switch ($order['status']) {
                                case 'pending':
                                    echo 'Menunggu Pembayaran';
                                    break;
                                case 'processing':
                                    echo 'Diproses';
                                    break;
                                case 'shipped':
                                    echo 'Dikirim';
                                    break;
                                case 'delivered':
                                    echo 'Selesai';
                                    break;
                                case 'cancelled':
                                    echo 'Dibatalkan';
                                    break;
                                default:
                                    echo 'Unknown';
                            }
                            ?>
                        </span>
                    </p>
                </div>

                <div>
                    <label for="payment_status" class="block text-sm font-medium text-gray-700 mb-1">Status Pembayaran</label>
                    <select id="payment_status" name="payment_status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="unpaid" <?php echo $order['payment_status'] === 'unpaid' ? 'selected' : ''; ?>>Menunggu</option>
                        <option value="paid" <?php echo $order['payment_status'] === 'paid' ? 'selected' : ''; ?>>Lunas</option>
                        <option value="failed" <?php echo $order['payment_status'] === 'failed' ? 'selected' : ''; ?>>Gagal</option>
                        <option value="refunded" <?php echo $order['payment_status'] === 'refunded' ? 'selected' : ''; ?>>Dikembalikan</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Status pembayaran saat ini:
                        <span class="font-medium">
                            <?php
                            switch ($order['payment_status']) {
                                case 'unpaid':
                                    echo 'Menunggu';
                                    break;
                                case 'paid':
                                    echo 'Lunas';
                                    break;
                                case 'failed':
                                    echo 'Gagal';
                                    break;
                                case 'refunded':
                                    echo 'Dikembalikan';
                                    break;
                                default:
                                    echo 'Unknown';
                            }
                            ?>
                        </span>
                    </p>
                </div>
            </div>

            <div class="mt-6 bg-gray-50 p-4 rounded-md">
                <div class="text-sm text-gray-600 mb-4">
                    <p><strong>Catatan Penting:</strong></p>
                    <ul class="list-disc pl-5 mt-1">
                        <li>Mengubah status pesanan ke "Dibatalkan" tidak akan mengembalikan stok produk secara otomatis.</li>
                        <li>Pastikan status pembayaran diubah ke "Lunas" jika pembayaran telah diterima.</li>
                        <li>Status pesanan "Diproses" berarti pesanan sedang disiapkan untuk pengiriman.</li>
                        <li>Status pesanan "Dikirim" berarti pesanan telah dikirim ke pelanggan.</li>
                        <li>Status pesanan "Selesai" berarti pesanan telah diterima oleh pelanggan.</li>
                    </ul>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <a href="detail.php?id=<?php echo $orderId; ?>" class="mr-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded">
                    Batal
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">
                    Perbarui Status
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../inc/footer.php'; ?>