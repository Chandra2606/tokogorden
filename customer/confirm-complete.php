<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);


require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../lib/models/Order.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Anda harus login terlebih dahulu.";
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "ID pesanan tidak valid.";
    header("Location: orders.php");
    exit;
}

$orderId = $_GET['id'];
$userId = $_SESSION['user_id'];

$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$orderModel = new Order($db);

$order = $orderModel->getById($orderId);
if (!$order || $order['user_id'] != $userId) {
    $_SESSION['error'] = "Pesanan tidak ditemukan atau bukan milik Anda.";
    header("Location: orders.php");
    exit;
}

if ($order['status'] != 'delivered') {
    $_SESSION['error'] = "Hanya pesanan yang sudah diterima (delivered) yang dapat dikonfirmasi selesai.";
    header("Location: orders.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['confirm'])) {
        if ($orderModel->markOrderAsCompleted($orderId)) {
            $_SESSION['success'] = "Pesanan berhasil dikonfirmasi selesai. Terima kasih telah berbelanja!";
        } else {
            $_SESSION['error'] = "Gagal mengkonfirmasi pesanan. Silakan coba lagi.";
        }
        header("Location: orders.php");
        exit;
    } else {
        header("Location: orders.php");
        exit;
    }
}

// Include header
$pageTitle = "Konfirmasi Pesanan Selesai";
include_once '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8 max-w-4xl">
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="bg-blue-600 text-white p-6">
            <div class="flex items-center space-x-3">
                <div class="bg-white/20 rounded-full p-2">
                    <i class="fas fa-check-circle text-2xl"></i>
                </div>
                <h1 class="text-2xl font-bold">Konfirmasi Pesanan Selesai</h1>
            </div>
        </div>

        <div class="p-6">
            <div class="mb-6 text-center">
                <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-box-open text-blue-600 text-3xl"></i>
                </div>
                <h2 class="text-xl font-semibold text-gray-800 mb-2">Apakah Anda yakin ingin menyelesaikan pesanan ini?</h2>
                <p class="text-gray-600">Dengan mengkonfirmasi, Anda menyatakan bahwa pesanan #<?php echo $orderId; ?> telah diterima dengan baik dan status akan diubah menjadi 'completed'.</p>
            </div>

            <div class="bg-gray-50 border border-gray-200 rounded-lg p-5 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-3 border-b border-gray-200 pb-2">Detail Pesanan</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-gray-600"><span class="font-medium">ID Pesanan:</span> #<?php echo $orderId; ?></p>
                        <p class="text-gray-600"><span class="font-medium">Tanggal:</span> <?php echo date('d F Y', strtotime($order['created_at'])); ?></p>
                    </div>
                    <div>
                        <p class="text-gray-600"><span class="font-medium">Status:</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Diterima
                            </span>
                        </p>
                        <p class="text-gray-600"><span class="font-medium">Total:</span> <span class="font-semibold text-blue-600"><?php echo formatRupiah($order['total_price']); ?></span></p>
                    </div>
                </div>
            </div>

            <form method="POST" class="space-y-4">
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-600"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                Setelah dikonfirmasi selesai, status pesanan tidak dapat diubah kembali. Pastikan Anda sudah menerima dan memeriksa pesanan dengan baik.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-center space-x-4">
                    <a href="orders.php" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium rounded-md shadow-sm transition-colors flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i> Kembali
                    </a>
                    <button type="submit" name="confirm" class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-md shadow-sm transition-colors flex items-center">
                        <i class="fas fa-check-circle mr-2"></i> Konfirmasi Selesai
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>