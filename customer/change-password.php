<?php
$page_title = 'Ubah Password';
require_once '../config/config.php';

if (!isLoggedIn() || isAdmin()) {
    redirect('login.php');
}

$userId = $_SESSION['user_id'];
$userModel = new User($conn);
$user = $userModel->getById($userId);

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['current_password'])) {
        $errors['current_password'] = 'Password saat ini tidak boleh kosong';
    } elseif (!password_verify($_POST['current_password'], $user['password'])) {
        $errors['current_password'] = 'Password saat ini tidak valid';
    }

    if (empty($_POST['new_password'])) {
        $errors['new_password'] = 'Password baru tidak boleh kosong';
    } elseif (strlen($_POST['new_password']) < 6) {
        $errors['new_password'] = 'Password baru minimal 6 karakter';
    }

    if (empty($_POST['confirm_password'])) {
        $errors['confirm_password'] = 'Konfirmasi password tidak boleh kosong';
    } elseif ($_POST['new_password'] !== $_POST['confirm_password']) {
        $errors['confirm_password'] = 'Konfirmasi password tidak sesuai dengan password baru';
    }

    if (empty($errors)) {
        $newPasswordHash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

        if ($userModel->updatePassword($userId, $newPasswordHash)) {
            $success = true;
        } else {
            $errors['general'] = 'Gagal memperbarui password. Silakan coba lagi.';
        }
    }
}

include '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row gap-8">
        <!-- Sidebar Menu -->
        <div class="w-full md:w-1/4">
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <div class="p-6 bg-blue-600 text-white">
                    <div class="flex items-center space-x-4">
                        <div class="rounded-full bg-white/30 p-3">
                            <i class="fas fa-user text-2xl"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-semibold"><?php echo htmlspecialchars($user['name']); ?></h2>
                            <p class="text-sm opacity-80"><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                    </div>
                </div>
                <nav class="p-4">
                    <ul class="space-y-2">
                        <li>
                            <a href="dashboard.php" class="flex items-center p-3 rounded-lg hover:bg-gray-100 transition-colors">
                                <i class="fas fa-tachometer-alt w-6"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="orders.php" class="flex items-center p-3 rounded-lg hover:bg-gray-100 transition-colors">
                                <i class="fas fa-shopping-bag w-6"></i>
                                <span>Pesanan Saya</span>
                            </a>
                        </li>
                        <li>
                            <a href="addresses.php" class="flex items-center p-3 rounded-lg hover:bg-gray-100 transition-colors">
                                <i class="fas fa-map-marker-alt w-6"></i>
                                <span>Alamat Pengiriman</span>
                            </a>
                        </li>
                        <li>
                            <a href="profile.php" class="flex items-center p-3 rounded-lg hover:bg-gray-100 transition-colors">
                                <i class="fas fa-user-edit w-6"></i>
                                <span>Edit Profil</span>
                            </a>
                        </li>
                        <li>
                            <a href="change-password.php" class="flex items-center p-3 rounded-lg bg-blue-50 text-blue-700">
                                <i class="fas fa-lock w-6"></i>
                                <span>Ubah Password</span>
                            </a>
                        </li>
                        <li>
                            <a href="../logout.php" class="flex items-center p-3 rounded-lg hover:bg-red-50 text-red-600 transition-colors">
                                <i class="fas fa-sign-out-alt w-6"></i>
                                <span>Keluar</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="w-full md:w-3/4">
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <div class="p-6 border-b">
                    <h1 class="text-2xl font-semibold">Ubah Password</h1>
                </div>

                <?php if ($success): ?>
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 m-6" role="alert">
                        <p class="font-medium">Sukses!</p>
                        <p>Password Anda berhasil diperbarui.</p>
                    </div>
                <?php endif; ?>

                <?php if (isset($errors['general'])): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 m-6" role="alert">
                        <p class="font-medium">Error!</p>
                        <p><?php echo $errors['general']; ?></p>
                    </div>
                <?php endif; ?>

                <div class="p-6">
                    <form action="" method="POST" class="space-y-6">
                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Password Saat Ini <span class="text-red-600">*</span></label>
                            <input type="password" id="current_password" name="current_password" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <?php if (isset($errors['current_password'])): ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo $errors['current_password']; ?></p>
                            <?php endif; ?>
                        </div>

                        <div>
                            <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">Password Baru <span class="text-red-600">*</span></label>
                            <input type="password" id="new_password" name="new_password" required minlength="6" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <?php if (isset($errors['new_password'])): ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo $errors['new_password']; ?></p>
                            <?php endif; ?>
                            <p class="mt-1 text-xs text-gray-500">Minimal 6 karakter</p>
                        </div>

                        <div>
                            <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru <span class="text-red-600">*</span></label>
                            <input type="password" id="confirm_password" name="confirm_password" required minlength="6" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <?php if (isset($errors['confirm_password'])): ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo $errors['confirm_password']; ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-save mr-2"></i> Ubah Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>