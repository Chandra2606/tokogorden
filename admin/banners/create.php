<?php
$active_page = 'banners';
$page_title = 'Tambah Banner';

require_once '../../config/config.php';
require_once '../../lib/models/Banner.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$bannerModel = new Banner($conn);
$errors = [];
$formData = [
    'title' => '',
    'description' => '',
    'link' => '',
    'is_active' => 1,
    'priority' => 0
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'title' => trim(sanitize($_POST['title'])),
        'description' => trim(sanitize($_POST['description'])),
        'link' => trim(sanitize($_POST['link'])),
        'is_active' => isset($_POST['is_active']) ? 1 : 0,
        'priority' => isset($_POST['priority']) ? intval($_POST['priority']) : 0
    ];

    if (empty($formData['title'])) {
        $errors[] = 'Judul banner wajib diisi';
    }

    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Gambar banner wajib diunggah';
    } else {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($_FILES['image']['type'], $allowedTypes)) {
            $errors[] = 'Format gambar tidak valid. Gunakan JPG, PNG, GIF, atau WebP.';
        } elseif ($_FILES['image']['size'] > $maxSize) {
            $errors[] = 'Ukuran gambar terlalu besar. Maksimal 5MB.';
        } else {
            $targetDir = '../../assets/images/banners/';
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            $fileName = time() . '_' . basename($_FILES['image']['name']);
            $fileName = preg_replace('/[^a-zA-Z0-9\-\._]/', '', $fileName); // Remove special chars
            $targetFile = $targetDir . $fileName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                $formData['image'] = $fileName;
            } else {
                $errors[] = 'Gagal mengunggah gambar. Silakan coba lagi.';
            }
        }
    }

    if (empty($errors)) {
        $result = $bannerModel->create($formData);

        if ($result) {
            $_SESSION['success_message'] = 'Banner berhasil ditambahkan';
            redirect('admin/banners/index.php');
        } else {
            $errors[] = 'Gagal menambahkan banner. Silakan coba lagi.';
        }
    }
}

include '../inc/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Tambah Banner</h1>
    <a href="index.php" class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded inline-flex items-center">
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

        <form action="" method="POST" enctype="multipart/form-data">
            <div class="mb-4">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Judul Banner <span class="text-red-600">*</span></label>
                <input type="text" id="title" name="title" value="<?php echo $formData['title']; ?>" required
                    class="w-full rounded-md shadow-sm focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    placeholder="Masukkan judul banner">
            </div>

            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea id="description" name="description" rows="3"
                    class="w-full rounded-md shadow-sm focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    placeholder="Masukkan deskripsi banner (opsional)"><?php echo $formData['description']; ?></textarea>
            </div>

            <div class="mb-4">
                <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Gambar Banner <span class="text-red-600">*</span></label>
                <input type="file" id="image" name="image" accept="image/*" required
                    class="w-full rounded-md">
                <p class="text-xs text-gray-500 mt-1 form-helper-text">Format: JPG, PNG, GIF, WebP. Maksimal 5MB. Ukuran yang direkomendasikan: 1200x400 piksel.</p>
            </div>

            <div class="mb-4">
                <label for="link" class="block text-sm font-medium text-gray-700 mb-1">Link (URL)</label>
                <input type="url" id="link" name="link" value="<?php echo $formData['link']; ?>"
                    class="w-full rounded-md shadow-sm focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    placeholder="https://example.com/halaman">
                <p class="text-xs text-gray-500 mt-1 form-helper-text">URL lengkap tempat banner akan mengarah ketika diklik. Contoh: https://example.com/halaman</p>
            </div>

            <div class="mb-4">
                <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">Prioritas</label>
                <input type="number" id="priority" name="priority" value="<?php echo $formData['priority']; ?>" min="0"
                    class="w-full rounded-md shadow-sm focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                <p class="text-xs text-gray-500 mt-1 form-helper-text">Banner dengan nilai prioritas lebih tinggi akan ditampilkan lebih dulu. Default: 0</p>
            </div>

            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" id="is_active" name="is_active" <?php echo $formData['is_active'] ? 'checked' : ''; ?>
                        class="rounded text-blue-600 shadow-sm focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <span class="ml-2 text-sm text-gray-700">Aktifkan Banner</span>
                </label>
            </div>

            <div class="flex justify-end">
                <a href="index.php" class="mr-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded">
                    Batal
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">
                    Simpan Banner
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Preview gambar saat diupload
        const imageInput = document.getElementById('image');

        imageInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const fileSize = this.files[0].size / 1024 / 1024; // in MB

                if (fileSize > 5) {
                    alert('Ukuran file terlalu besar. Maksimal 5MB.');
                    this.value = '';
                    return;
                }

                const reader = new FileReader();

                reader.onload = function(e) {
                    let previewContainer = document.getElementById('image-preview');

                    if (!previewContainer) {
                        previewContainer = document.createElement('div');
                        previewContainer.id = 'image-preview';
                        previewContainer.className = 'mt-2 border rounded p-2';
                        imageInput.parentNode.appendChild(previewContainer);
                    }

                    previewContainer.innerHTML = `
                    <p class="text-sm font-medium mb-1">Preview:</p>
                    <img src="${e.target.result}" class="max-w-full h-auto max-h-60" />
                `;
                }

                reader.readAsDataURL(this.files[0]);
            }
        });
    });
</script>

<?php include '../inc/footer.php'; ?>