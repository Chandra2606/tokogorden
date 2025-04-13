<?php
$active_page = 'categories';
$page_title = 'Edit Kategori';

require_once '../../config/config.php';
require_once '../../lib/models/Category.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$categoryModel = new Category($conn);
$errors = [];

// Cek ID kategori
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = 'ID kategori tidak valid';
    redirect('admin/categories/index.php');
}

$categoryId = intval($_GET['id']);
$category = $categoryModel->getById($categoryId);

if (!$category) {
    $_SESSION['error_message'] = 'Kategori tidak ditemukan';
    redirect('admin/categories/index.php');
}

$name = $category['name'];
$slug = $category['slug'];
$originalSlug = $slug;

// Proses form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi
    $name = trim(sanitize($_POST['name']));
    $slug = isset($_POST['slug']) && !empty($_POST['slug'])
        ? trim(sanitize($_POST['slug']))
        : generateSlug($name);

    // Periksa apakah nama kategori sudah diisi
    if (empty($name)) {
        $errors[] = 'Nama kategori wajib diisi';
    }

    // Periksa apakah slug sudah ada (kecuali untuk kategori ini sendiri)
    if ($slug !== $originalSlug) {
        $existingCategory = $categoryModel->getBySlug($slug);
        if ($existingCategory && $existingCategory['id'] != $categoryId) {
            $errors[] = 'Slug kategori sudah digunakan, silakan gunakan yang lain';
        }
    }

    // Jika tidak ada error, update kategori
    if (empty($errors)) {
        $result = $categoryModel->update($categoryId, $name, $slug);

        if ($result) {
            $_SESSION['success_message'] = 'Kategori berhasil diperbarui';
            redirect('admin/categories/index.php');
        } else {
            $errors[] = 'Gagal memperbarui kategori. Silakan coba lagi';
        }
    }
}

include '../inc/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Edit Kategori</h1>
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

        <form action="" method="POST">
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Kategori <span class="text-red-600">*</span></label>
                <input type="text" id="name" name="name" value="<?php echo $name; ?>" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-gray-500 mt-1">Masukkan nama kategori yang akan ditampilkan</p>
            </div>

            <div class="mb-4">
                <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                <input type="text" id="slug" name="slug" value="<?php echo $slug; ?>"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-gray-500 mt-1">Slug adalah versi URL-friendly dari nama. Biasanya semua huruf kecil dan berisi hanya huruf, angka, dan tanda hubung. Biarkan kosong untuk membuat otomatis dari nama.</p>
            </div>

            <div class="flex justify-end">
                <a href="index.php" class="mr-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded">
                    Batal
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">
                    Perbarui Kategori
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const nameInput = document.getElementById('name');
        const slugInput = document.getElementById('slug');
        const originalName = nameInput.value;
        const originalSlug = slugInput.value;

        // Set auto flag berdasarkan apakah slug sama dengan slug yang dihasilkan dari nama
        if (slugInput.value === generateSlug(nameInput.value)) {
            slugInput.dataset.auto = 'true';
        } else {
            slugInput.dataset.auto = 'false';
        }

        nameInput.addEventListener('input', function() {
            // Hanya update slug jika pengguna belum mengisi slug sendiri atau auto flag true
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