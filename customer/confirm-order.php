<?php
require_once '../config/config.php';

if (!isLoggedIn() || isAdmin()) {
    redirect('../login.php');
}

$orderId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$orderId) {
    setFlashMessage('error', 'ID pesanan tidak valid');
    redirect('orders.php');
}

$userId = $_SESSION['user_id'];
$orderModel = new Order($conn);
$order = $orderModel->getById($orderId);

if (!$order || $order['user_id'] != $userId) {
    setFlashMessage('error', 'Pesanan tidak ditemukan atau Anda tidak memiliki akses');
    redirect('orders.php');
}

if ($order['status'] !== 'shipped') {
    setFlashMessage('error', 'Pesanan tidak dalam status Dikirim');
    redirect('customer/order-detail.php?id=' . $orderId);
}

// Konfirmasi tombol
if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    $result = $orderModel->updateStatus($orderId, 'delivered');

    if ($result) {
        setFlashMessage('success', 'Pesanan berhasil dikonfirmasi diterima. Jika tidak ada masalah dengan pesanan, harap konfirmasi penyelesaian pesanan.');
    } else {
        setFlashMessage('error', 'Gagal mengubah status pesanan');
    }

    redirect('customer/order-detail.php?id=' . $orderId);
} else {
    include '../includes/header.php';
?>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6">
                <div class="text-center mb-6">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full text-blue-600 mb-4">
                        <i class="fas fa-box-open text-2xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">Konfirmasi Penerimaan</h2>
                    <p class="text-gray-600 mt-2">Apakah Anda sudah menerima pesanan #<?php echo $orderId; ?>?</p>
                </div>

                <div class="bg-blue-50 rounded-lg p-4 mb-6">
                    <p class="text-sm text-blue-800">
                        <i class="fas fa-info-circle mr-2"></i> Dengan mengkonfirmasi penerimaan, Anda menyatakan bahwa pesanan telah diterima dalam kondisi baik.
                    </p>
                </div>

                <div class="flex justify-center space-x-4">
                    <a href="order-detail.php?id=<?php echo $orderId; ?>" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Kembali
                    </a>
                    <a href="confirm-order.php?id=<?php echo $orderId; ?>&confirm=yes" class="px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                        Ya, Saya Sudah Terima
                    </a>
                </div>
            </div>
        </div>
    </div>

<?php
    include '../includes/footer.php';
}
?>