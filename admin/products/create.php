<?php
$active_page = 'products';
$page_title = 'Tambah Produk Baru';

require_once '../../config/config.php';
require_once '../../lib/models/Product.php';
require_once '../../lib/models/Category.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$productModel = new Product($conn);
$categoryModel = new Category($conn);

$categories = $categoryModel->getAll();

$formData = [
    'name' => '',
    'category_id' => '',
    'price' => '',
    'stock' => '',
    'description' => '',
    'is_featured' => 0
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'name' => trim($_POST['name'] ?? ''),
        'category_id' => !empty($_POST['category_id']) ? intval($_POST['category_id']) : null,
        'price' => !empty($_POST['price']) ? str_replace(['.', ','], ['', '.'], $_POST['price']) : '',
        'stock' => !empty($_POST['stock']) ? intval($_POST['stock']) : '',
        'description' => trim($_POST['description'] ?? ''),
        'is_featured' => isset($_POST['is_featured']) ? 1 : 0
    ];

    if (empty($formData['name'])) {
        $errors['name'] = 'Nama produk wajib diisi';
    } elseif (strlen($formData['name']) > 255) {
        $errors['name'] = 'Nama produk maksimal 255 karakter';
    }

    if (empty($formData['category_id'])) {
        $errors['category_id'] = 'Kategori wajib dipilih';
    }

    if (empty($formData['price'])) {
        $errors['price'] = 'Harga wajib diisi';
    } elseif (!is_numeric($formData['price']) || $formData['price'] <= 0) {
        $errors['price'] = 'Harga harus berupa angka positif';
    }

    if ($formData['stock'] === '') {
        $errors['stock'] = 'Stok wajib diisi';
    } elseif (!is_numeric($formData['stock']) || $formData['stock'] < 0) {
        $errors['stock'] = 'Stok harus berupa angka dan tidak boleh negatif';
    }

    $image = '';
    if (!empty($_FILES['image']['name'])) {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $maxFileSize = 2 * 1024 * 1024; // 2MB

        $fileName = $_FILES['image']['name'];
        $fileSize = $_FILES['image']['size'];
        $fileTmpName = $_FILES['image']['tmp_name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($fileExtension, $allowedExtensions)) {
            $errors['image'] = 'Format gambar harus JPG, JPEG, PNG, atau WEBP';
        }

        if ($fileSize > $maxFileSize) {
            $errors['image'] = 'Ukuran gambar maksimal 2MB';
        }

        if (!isset($errors['image'])) {
            $image = 'product_' . time() . '_' . uniqid() . '.' . $fileExtension;
            $uploadPath = '../../assets/images/products/' . $image;

            if (!is_dir(dirname($uploadPath))) {
                mkdir(dirname($uploadPath), 0755, true);
            }

            if (!move_uploaded_file($fileTmpName, $uploadPath)) {
                $errors['image'] = 'Gagal mengunggah gambar. Silakan coba lagi.';
                $image = '';
            }
        }
    }

    if (empty($errors)) {
        $slug = generateSlug($formData['name']);

        $productData = [
            'name' => $formData['name'],
            'slug' => $slug,
            'category_id' => $formData['category_id'],
            'price' => $formData['price'],
            'stock' => $formData['stock'],
            'description' => $formData['description'],
            'image' => $image,
            'is_featured' => $formData['is_featured']
        ];

        $_SESSION['debug_image'] = $image;

        $result = $productModel->create($productData);

        if ($result) {
            $_SESSION['success_message'] = 'Produk berhasil ditambahkan';
            redirect('admin/products/index.php');
        } else {
            $_SESSION['error_message'] = 'Gagal menambahkan produk. Silakan coba lagi.';

            if (!empty($image)) {
                unlink('../../assets/images/products/' . $image);
            }
        }
    }
}

include '../inc/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Tambah Produk Baru</h1>
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
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nama Produk -->
                <div class="col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Produk <span class="text-red-600">*</span></label>
                    <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($formData['name']); ?>"
                        class="w-full rounded-md shadow-sm focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                        required placeholder="Masukkan nama produk">
                    <?php if (isset($errors['name'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?php echo $errors['name']; ?></p>
                    <?php endif; ?>
                </div>

                <!-- Kategori -->
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Kategori <span class="text-red-600">*</span></label>
                    <select name="category_id" id="category_id"
                        class="w-full rounded-md shadow-sm focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                        required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo ($formData['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['category_id'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?php echo $errors['category_id']; ?></p>
                    <?php endif; ?>
                </div>

                <!-- Harga -->
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Harga (Rp) <span class="text-red-600">*</span></label>
                    <input type="text" name="price" id="price" value="<?php echo htmlspecialchars($formData['price']); ?>"
                        class="w-full rounded-md shadow-sm focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                        required placeholder="contoh: 150.000">
                    <?php if (isset($errors['price'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?php echo $errors['price']; ?></p>
                    <?php endif; ?>
                </div>

                <!-- Stok -->
                <div>
                    <label for="stock" class="block text-sm font-medium text-gray-700 mb-1">Stok <span class="text-red-600">*</span></label>
                    <input type="number" name="stock" id="stock" value="<?php echo htmlspecialchars($formData['stock']); ?>"
                        class="w-full rounded-md shadow-sm focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                        required min="0" placeholder="Masukkan jumlah stok">
                    <?php if (isset($errors['stock'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?php echo $errors['stock']; ?></p>
                    <?php endif; ?>
                </div>

                <!-- Gambar -->
                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Gambar Produk</label>
                    <input type="file" name="image" id="image"
                        class="w-full rounded-md">
                    <p class="mt-1 text-xs text-gray-500 form-helper-text">Format: JPG, JPEG, PNG, WEBP. Maksimal 2MB.</p>
                    <?php if (isset($errors['image'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?php echo $errors['image']; ?></p>
                    <?php endif; ?>
                </div>

                <!-- Produk Unggulan -->
                <div>
                    <div class="flex items-center h-full mt-6">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="is_featured" value="1" <?php echo $formData['is_featured'] ? 'checked' : ''; ?>
                                class="rounded text-blue-600 shadow-sm focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-700">Jadikan Produk Unggulan</span>
                        </label>
                    </div>
                </div>

                <!-- Deskripsi -->
                <div class="col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Produk</label>
                    <textarea name="description" id="description" rows="5"
                        class="w-full rounded-md shadow-sm focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                        placeholder="Masukkan deskripsi produk"><?php echo htmlspecialchars($formData['description']); ?></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded mr-2">Batal</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">
                    <i class="fas fa-save mr-2"></i> Simpan Produk
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../inc/footer.php'; ?>