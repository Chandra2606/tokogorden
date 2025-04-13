<?php
require_once '../../config/config.php';
require_once '../../lib/models/Discount.php';

// Cek login admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

// Cek ID diskon
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = 'ID diskon tidak valid';
    redirect('admin/discounts/index.php');
}

$discount_id = intval($_GET['id']);
$discountModel = new Discount($conn);

// Ambil data diskon untuk konfirmasi
$discount = $discountModel->getById($discount_id);
if (!$discount) {
    $_SESSION['error_message'] = 'Diskon tidak ditemukan';
    redirect('admin/discounts/index.php');
}

// Proses konfirmasi hapus
if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    // Hapus semua relasi dengan produk
    $discountModel->removeAllProductAssignments($discount_id);

    // Hapus diskon
    $result = $discountModel->delete($discount_id);

    if ($result) {
        $_SESSION['success_message'] = 'Diskon berhasil dihapus';
    } else {
        $_SESSION['error_message'] = 'Gagal menghapus diskon';
    }

    redirect('admin/discounts/index.php');
} else {
    // Tampilkan konfirmasi
    include '../inc/header.php';
?>

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Hapus Diskon</h1>
        <a href="index.php" class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded inline-flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
        <div class="p-6">
            <div class="p-4 mb-4 text-sm border-l-4 border-yellow-400 bg-yellow-50 text-yellow-700">
                <p>Anda yakin ingin menghapus diskon berikut?</p>
            </div>

            <div class="mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Nama Diskon:</p>
                        <p class="font-medium"><?php echo htmlspecialchars($discount['name']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Tipe:</p>
                        <p class="font-medium">
                            <?php
                            $typeLabels = [
                                'product' => 'Diskon Produk',
                                'voucher' => 'Voucher',
                                'bundle' => 'Bundel',
                                'time' => 'Diskon Waktu Tertentu'
                            ];
                            echo isset($typeLabels[$discount['type']]) ? $typeLabels[$discount['type']] : $discount['type'];
                            ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Nilai:</p>
                        <p class="font-medium">
                            <?php
                            if ($discount['is_percentage']) {
                                echo $discount['value'] . '%';
                            } else {
                                echo formatRupiah($discount['value']);
                            }
                            ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Status:</p>
                        <p class="font-medium">
                            <?php if ($discount['active']): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Aktif</span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Tidak Aktif</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="p-4 mb-4 text-sm border-l-4 border-red-400 bg-red-50 text-red-700">
                <p><strong>Peringatan:</strong> Tindakan ini tidak dapat dibatalkan. Semua keterkaitan diskon ini dengan produk juga akan dihapus.</p>
            </div>

            <div class="flex justify-end space-x-2">
                <a href="index.php" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded">
                    Batal
                </a>
                <a href="delete.php?id=<?php echo $discount_id; ?>&confirm=yes" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded">
                    Hapus Diskon
                </a>
            </div>
        </div>
    </div>

<?php
    include '../inc/footer.php';
}
?>