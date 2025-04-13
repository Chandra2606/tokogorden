<?php
$active_page = 'users';
$page_title = 'Tambah Pengguna';

require_once '../../config/config.php';
require_once '../../lib/models/User.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$userModel = new User($conn);
$errors = [];
$formData = [
    'name' => '',
    'email' => '',
    'role' => 'customer',
    'phone' => '',
    'address' => ''
];

// Proses form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi
    $formData = [
        'name' => trim(sanitize($_POST['name'])),
        'email' => trim(sanitize($_POST['email'])),
        'password' => trim($_POST['password']),
        'confirm_password' => trim($_POST['confirm_password']),
        'role' => trim(sanitize($_POST['role'])),
        'phone' => isset($_POST['phone']) ? trim(sanitize($_POST['phone'])) : '',
        'address' => isset($_POST['address']) ? trim(sanitize($_POST['address'])) : ''
    ];

    // Validasi nama
    if (empty($formData['name'])) {
        $errors[] = 'Nama pengguna wajib diisi';
    }

    // Validasi email
    if (empty($formData['email'])) {
        $errors[] = 'Email wajib diisi';
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid';
    } else {
        // Cek apakah email sudah ada
        $existingUser = $userModel->getByEmail($formData['email']);
        if ($existingUser) {
            $errors[] = 'Email sudah digunakan, silakan gunakan email lain';
        }
    }

    // Validasi password
    if (empty($formData['password'])) {
        $errors[] = 'Password wajib diisi';
    } elseif (strlen($formData['password']) < 6) {
        $errors[] = 'Password minimal 6 karakter';
    } elseif ($formData['password'] !== $formData['confirm_password']) {
        $errors[] = 'Konfirmasi password tidak sesuai';
    }

    // Validasi role
    if (empty($formData['role']) || !in_array($formData['role'], ['admin', 'customer'])) {
        $errors[] = 'Role tidak valid';
    }

    // Jika tidak ada error, simpan pengguna
    if (empty($errors)) {
        // Hapus confirm_password dari formData
        unset($formData['confirm_password']);

        $result = $userModel->create($formData);

        if ($result) {
            $_SESSION['success_message'] = 'Pengguna berhasil ditambahkan';
            redirect('admin/users/index.php');
        } else {
            $errors[] = 'Gagal menambahkan pengguna. Silakan coba lagi.';
        }
    }
}

include '../inc/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Tambah Pengguna</h1>
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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama <span class="text-red-600">*</span></label>
                    <input type="text" id="name" name="name" value="<?php echo $formData['name']; ?>" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-600">*</span></label>
                    <input type="email" id="email" name="email" value="<?php echo $formData['email']; ?>" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-red-600">*</span></label>
                    <input type="password" id="password" name="password" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Minimal 6 karakter</p>
                </div>

                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password <span class="text-red-600">*</span></label>
                    <input type="password" id="confirm_password" name="confirm_password" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role <span class="text-red-600">*</span></label>
                    <select id="role" name="role" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="customer" <?php echo $formData['role'] === 'customer' ? 'selected' : ''; ?>>Customer</option>
                        <option value="admin" <?php echo $formData['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">No. Telepon</label>
                    <input type="text" id="phone" name="phone" value="<?php echo $formData['phone']; ?>"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="md:col-span-2">
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                    <textarea id="address" name="address" rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo $formData['address']; ?></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <a href="index.php" class="mr-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded">
                    Batal
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">
                    Simpan Pengguna
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../inc/footer.php'; ?>