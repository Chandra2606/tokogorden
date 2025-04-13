<?php
$active_page = 'discounts';
$page_title = 'Tambah Diskon';

require_once '../../config/config.php';
require_once '../../lib/models/Discount.php';
require_once '../../lib/models/Product.php';

$discountModel = new Discount($conn);
$productModel = new Product($conn);

$products = $productModel->getAll();
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $type = sanitize($_POST['type']);
    $value = floatval($_POST['value']);
    $is_percentage = isset($_POST['is_percentage']) ? 1 : 0;
    $min_qty = !empty($_POST['min_qty']) ? intval($_POST['min_qty']) : null;
    $code = !empty($_POST['code']) ? sanitize($_POST['code']) : null;
    $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
    $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
    $active = isset($_POST['active']) ? 1 : 0;
    $selectedProducts = isset($_POST['products']) ? $_POST['products'] : [];

    if (empty($name)) {
        $errors['name'] = 'Nama diskon harus diisi';
    }

    if (empty($type)) {
        $errors['type'] = 'Tipe diskon harus dipilih';
    }

    if ($value <= 0) {
        $errors['value'] = 'Nilai diskon harus lebih dari 0';
    }

    if ($type === 'bundle' && empty($min_qty)) {
        $errors['min_qty'] = 'Minimal pembelian harus diisi untuk diskon bundel';
    }

    if ($type === 'voucher' && empty($code)) {
        $errors['code'] = 'Kode voucher harus diisi untuk diskon voucher';
    }

    if ($type === 'time' && (empty($start_date) || empty($end_date))) {
        $errors['date'] = 'Tanggal mulai dan berakhir harus diisi untuk diskon waktu';
    }

    if (empty($errors)) {
        $discountData = [
            'name' => $name,
            'type' => $type,
            'value' => $value,
            'is_percentage' => $is_percentage,
            'min_qty' => $min_qty,
            'code' => $code,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'active' => $active
        ];

        $discountId = $discountModel->create($discountData);

        if ($discountId) {
            if (!empty($selectedProducts) && ($type === 'product' || $type === 'bundle')) {
                foreach ($selectedProducts as $productId) {
                    $discountModel->assignToProduct($discountId, $productId);
                }
            }

            $success = true;
        } else {
            $errors['general'] = 'Gagal menambahkan diskon. Silakan coba lagi.';
        }
    }
}

include '../inc/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Tambah Diskon Baru</h1>
    <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded inline-flex items-center">
        <i class="fas fa-arrow-left mr-2"></i> Kembali
    </a>
</div>

<?php if ($success): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            <span>Diskon berhasil ditambahkan!</span>
        </div>
        <div class="mt-2">
            <a href="index.php" class="text-green-700 font-medium underline">Kembali ke daftar diskon</a> atau
            <a href="create.php" class="text-green-700 font-medium underline">tambah diskon lainnya</a>.
        </div>
    </div>
<?php endif; ?>

<?php if (isset($errors['general'])): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <div class="flex items-center">
            <i class="fas fa-times-circle mr-2"></i>
            <span><?php echo $errors['general']; ?></span>
        </div>
    </div>
<?php endif; ?>

<div class="bg-white shadow-md rounded-lg overflow-hidden p-6">
    <form method="POST" action="">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Diskon <span class="text-red-600">*</span></label>
                    <input type="text" id="name" name="name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <?php if (isset($errors['name'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?php echo $errors['name']; ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Tipe Diskon <span class="text-red-600">*</span></label>
                    <select id="type" name="type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">Pilih Tipe Diskon</option>
                        <option value="bundle">Diskon Berdasarkan Pembelian Banyak</option>
                        <option value="product">Diskon Khusus untuk Produk Tertentu</option>
                        <option value="voucher">Diskon Berdasarkan Kode Voucher</option>
                        <option value="time">Diskon Berdasarkan Tanggal Tertentu</option>
                    </select>
                    <?php if (isset($errors['type'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?php echo $errors['type']; ?></p>
                    <?php endif; ?>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="value" class="block text-sm font-medium text-gray-700 mb-1">Nilai Diskon <span class="text-red-600">*</span></label>
                        <input type="number" id="value" name="value" min="0" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <?php if (isset($errors['value'])): ?>
                            <p class="text-red-500 text-sm mt-1"><?php echo $errors['value']; ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="flex items-center mt-8">
                        <input type="checkbox" id="is_percentage" name="is_percentage" value="1" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" checked>
                        <label for="is_percentage" class="ml-2 text-sm text-gray-700">Persen (%)</label>
                    </div>
                </div>

                <div id="bundleFields" class="hidden">
                    <label for="min_qty" class="block text-sm font-medium text-gray-700 mb-1">Minimal Pembelian <span class="text-red-600">*</span></label>
                    <input type="number" id="min_qty" name="min_qty" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <?php if (isset($errors['min_qty'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?php echo $errors['min_qty']; ?></p>
                    <?php endif; ?>
                </div>

                <div id="voucherFields" class="hidden">
                    <label for="code" class="block text-sm font-medium text-gray-700 mb-1">Kode Voucher <span class="text-red-600">*</span></label>
                    <input type="text" id="code" name="code" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <?php if (isset($errors['code'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?php echo $errors['code']; ?></p>
                    <?php endif; ?>
                </div>

                <div id="timeFields" class="hidden space-y-4">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai <span class="text-red-600">*</span></label>
                        <input type="datetime-local" id="start_date" name="start_date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Berakhir <span class="text-red-600">*</span></label>
                        <input type="datetime-local" id="end_date" name="end_date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <?php if (isset($errors['date'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?php echo $errors['date']; ?></p>
                    <?php endif; ?>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="active" name="active" value="1" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" checked>
                    <label for="active" class="ml-2 text-sm text-gray-700">Aktif</label>
                </div>
            </div>

            <div id="productSelectionSection" class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Produk</label>
                <div class="border border-gray-300 rounded-md h-96 overflow-y-auto p-4">
                    <?php if (count($products) > 0): ?>
                        <div class="space-y-2">
                            <?php foreach ($products as $product): ?>
                                <div class="flex items-start">
                                    <input type="checkbox" id="product_<?php echo $product['id']; ?>" name="products[]" value="<?php echo $product['id']; ?>" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mt-1">
                                    <label for="product_<?php echo $product['id']; ?>" class="ml-2 text-sm text-gray-700">
                                        <span class="font-medium"><?php echo $product['name']; ?></span>
                                        <span class="block text-gray-500 text-xs">Kategori: <?php echo $product['category_name']; ?> | Harga: <?php echo formatRupiah($product['price']); ?></span>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 text-sm">Belum ada produk tersedia.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="mt-6">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">
                <i class="fas fa-save mr-2"></i> Simpan Diskon
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('type');
        const bundleFields = document.getElementById('bundleFields');
        const voucherFields = document.getElementById('voucherFields');
        const timeFields = document.getElementById('timeFields');
        const productSelectionSection = document.getElementById('productSelectionSection');

        typeSelect.addEventListener('change', function() {
            bundleFields.classList.add('hidden');
            voucherFields.classList.add('hidden');
            timeFields.classList.add('hidden');
            productSelectionSection.classList.add('hidden');

            switch (this.value) {
                case 'bundle':
                    bundleFields.classList.remove('hidden');
                    productSelectionSection.classList.remove('hidden');
                    break;
                case 'product':
                    productSelectionSection.classList.remove('hidden');
                    break;
                case 'voucher':
                    voucherFields.classList.remove('hidden');
                    break;
                case 'time':
                    timeFields.classList.remove('hidden');
                    break;
            }
        });
    });
</script>

<?php include '../inc/footer.php'; ?>