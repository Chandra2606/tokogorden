<?php
$active_page = 'products';
$page_title = 'Hapus Produk';

require_once '../../config/config.php';
require_once '../../lib/models/Product.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$product_id) {
    $_SESSION['error_message'] = 'ID produk tidak valid';
    redirect('admin/products/index.php');
}

$productModel = new Product($conn);

$product = $productModel->getById($product_id);
if (!$product) {
    $_SESSION['error_message'] = 'Produk tidak ditemukan';
    redirect('admin/products/index.php');
}

if (isset($_POST['confirm_delete']) && $_POST['confirm_delete'] === 'yes') {
    if (!empty($product['image'])) {
        $imagePath = '../../assets/images/products/' . $product['image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    $result = $productModel->delete($product_id);

    if ($result) {
        $_SESSION['success_message'] = 'Produk berhasil dihapus';
    } else {
        $_SESSION['error_message'] = 'Gagal menghapus produk. Silakan coba lagi.';
    }

    redirect('admin/products/index.php');
}

include '../inc/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Hapus Produk</h1>
    <a href="index.php" class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded inline-flex items-center">
        <i class="fas fa-arrow-left mr-2"></i> Kembali
    </a>
</div>

<?php if (!empty($_SESSION['error_message'])): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
        <p><?php echo $_SESSION['error_message']; ?></p>
        <?php unset($_SESSION['error_message']); ?>
    </div>
<?php endif; ?>

<div class="bg-white shadow-md rounded-lg overflow-hidden">
    <div class="p-6">
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-500"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-medium text-yellow-700">Konfirmasi Penghapusan</h3>
                    <p class="mt-2 text-sm text-yellow-600">
                        Anda akan menghapus produk <strong><?php echo htmlspecialchars($product['name']); ?></strong>.
                        Tindakan ini tidak dapat dibatalkan. Semua data terkait produk ini akan dihapus secara permanen.
                    </p>
                </div>
            </div>
        </div>

        <div class="mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-3">Informasi Produk</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600">Nama Produk:</p>
                    <p class="font-medium"><?php echo htmlspecialchars($product['name']); ?></p>
                </div>

                <div>
                    <p class="text-sm text-gray-600">Harga:</p>
                    <p class="font-medium">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></p>
                </div>

                <div>
                    <p class="text-sm text-gray-600">Stok:</p>
                    <p class="font-medium"><?php echo number_format($product['stock'], 0, ',', '.'); ?> unit</p>
                </div>

                <div>
                    <p class="text-sm text-gray-600">Status:</p>
                    <p class="font-medium"><?php echo $product['is_featured'] ? 'Produk Unggulan' : 'Produk Reguler'; ?></p>
                </div>
            </div>

            <?php if (!empty($product['image'])): ?>
                <div class="mt-4">
                    <p class="text-sm text-gray-600 mb-2">Gambar Produk:</p>
                    <img src="../../assets/images/products/<?php echo htmlspecialchars($product['image']); ?>"
                        alt="<?php echo htmlspecialchars($product['name']); ?>"
                        class="w-40 h-40 object-cover border rounded">
                </div>
            <?php endif; ?>
        </div>

        <form action="" method="POST" class="mt-8">
            <input type="hidden" name="confirm_delete" value="yes">

            <div class="flex justify-end space-x-3">
                <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded">
                    Batal
                </a>
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded flex items-center">
                    <i class="fas fa-trash mr-2"></i> Hapus Produk
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../inc/footer.php'; ?>