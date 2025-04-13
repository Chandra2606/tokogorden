<?php
$active_page = 'categories';
$page_title = 'Tambah Kategori Baru';

require_once '../../config/config.php';
require_once '../../lib/models/Category.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$categoryModel = new Category($conn);

$formData = [
    'name' => '',
    'slug' => '',
    'icon' => '',
    'is_featured' => 0
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'name' => trim($_POST['name'] ?? ''),
        'slug' => trim($_POST['slug'] ?? ''),
        'icon' => trim($_POST['icon'] ?? ''),
        'is_featured' => isset($_POST['is_featured']) ? 1 : 0
    ];

    if (empty($formData['name'])) {
        $errors['name'] = 'Nama kategori wajib diisi';
    } elseif (strlen($formData['name']) > 255) {
        $errors['name'] = 'Nama kategori maksimal 255 karakter';
    }

    if (empty($formData['slug'])) {
        $formData['slug'] = generateSlug($formData['name']);
    } elseif (strlen($formData['slug']) > 255) {
        $errors['slug'] = 'Slug maksimal 255 karakter';
    }

    $existingCategory = $categoryModel->getBySlug($formData['slug']);
    if ($existingCategory) {
        $errors['slug'] = 'Slug sudah digunakan oleh kategori lain';
    }

    if (empty($errors)) {
        $result = $categoryModel->create($formData['name'], $formData['slug']);

        if ($result) {
            setFlashMessage('success', 'Kategori berhasil ditambahkan');
            redirect('admin/categories/index.php');
        } else {
            setFlashMessage('error', 'Gagal menambahkan kategori. Silakan coba lagi.');
        }
    }
}

include '../inc/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Tambah Kategori Baru</h1>
    <a href="index.php" class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded inline-flex items-center">
        <i class="fas fa-arrow-left mr-2"></i> Kembali
    </a>
</div>

<div class="bg-white shadow-md rounded-lg overflow-hidden">
    <div class="p-6">
        <form action="" method="POST">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nama Kategori -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Kategori <span class="text-red-600">*</span></label>
                    <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($formData['name']); ?>"
                        class="w-full rounded-md shadow-sm focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                        required placeholder="Masukkan nama kategori">
                    <?php if (isset($errors['name'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?php echo $errors['name']; ?></p>
                    <?php endif; ?>
                </div>

                <!-- Slug -->
                <div>
                    <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                    <input type="text" name="slug" id="slug" value="<?php echo htmlspecialchars($formData['slug']); ?>"
                        class="w-full rounded-md shadow-sm focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                        placeholder="Slug akan dibuat otomatis" data-auto="true">
                    <p class="mt-1 text-xs text-gray-500 form-helper-text">Slug akan otomatis dibuat dari nama kategori. Biarkan kosong jika auto-generate.</p>
                    <?php if (isset($errors['slug'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?php echo $errors['slug']; ?></p>
                    <?php endif; ?>
                </div>

                <!-- Icon (Font Awesome) -->
                <div>
                    <label for="icon" class="block text-sm font-medium text-gray-700 mb-1">Icon (Font Awesome)</label>
                    <div class="flex items-center">
                        <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500">
                            <i class="fas fa-"></i>
                        </span>
                        <input type="text" name="icon" id="icon" value="<?php echo htmlspecialchars($formData['icon']); ?>"
                            class="rounded-none rounded-r-md w-full shadow-sm focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            placeholder="home, tag, list, dll.">
                    </div>
                    <p class="mt-1 text-xs text-gray-500 form-helper-text">Contoh: 'home' untuk 'fa-home'. Hanya nama icon (tanpa 'fa-').</p>
                </div>

                <!-- Kategori Unggulan -->
                <div>
                    <div class="flex items-center h-full mt-6">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="is_featured" value="1" <?php echo $formData['is_featured'] ? 'checked' : ''; ?>
                                class="rounded text-blue-600 shadow-sm focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-700">Jadikan Kategori Unggulan</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded mr-2">Batal</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">
                    <i class="fas fa-save mr-2"></i> Simpan Kategori
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const nameInput = document.getElementById('name');
        const slugInput = document.getElementById('slug');
        const iconInput = document.getElementById('icon');
        const iconPreview = document.querySelector('.fa-');

        // Preview icon ketika pengguna mengetik
        iconInput.addEventListener('input', function() {
            iconPreview.className = 'fas fa-' + this.value;
        });

        nameInput.addEventListener('input', function() {
            // Hanya update slug jika pengguna belum mengisi slug sendiri
            if (slugInput.value === '' || slugInput.dataset.auto === 'true') {
                slugInput.value = generateSlug(nameInput.value);
                slugInput.dataset.auto = 'true';
            }
        });

        slugInput.addEventListener('input', function() {
            // Jika pengguna mengubah slug, atur flag auto menjadi false
            if (slugInput.value !== generateSlug(nameInput.value)) {
                slugInput.dataset.auto = 'false';
            } else {
                slugInput.dataset.auto = 'true';
            }
        });

        function generateSlug(text) {
            return text.toString().toLowerCase()
                .replace(/\s+/g, '-') // Ganti spasi dengan -
                .replace(/[^\w\-]+/g, '') // Hapus semua karakter non-word
                .replace(/\-\-+/g, '-') // Ganti multiple - dengan single -
                .replace(/^-+/, '') // Trim - dari awal teks
                .replace(/-+$/, ''); // Trim - dari akhir teks
        }
    });
</script>

<?php include '../inc/footer.php'; ?>