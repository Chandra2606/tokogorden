<?php
$page_title = 'Edit Profil';
require_once '../config/config.php';

if (!isLoggedIn() || isAdmin()) {
    redirect('login.php');
}

$userId = $_SESSION['user_id'];
$userModel = new User($conn);
$user = $userModel->getById($userId);

$formData = [
    'name' => $user['name'],
    'email' => $user['email'],
    'phone' => $user['phone'] ?? '',
];

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['name'])) {
        $errors['name'] = 'Nama tidak boleh kosong';
    } else {
        $formData['name'] = sanitize($_POST['name']);
    }

    if (empty($_POST['email'])) {
        $errors['email'] = 'Email tidak boleh kosong';
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Format email tidak valid';
    } else {
        $email = sanitize($_POST['email']);
        if ($email !== $user['email'] && $userModel->isEmailExists($email)) {
            $errors['email'] = 'Email sudah digunakan';
        } else {
            $formData['email'] = $email;
        }
    }

    if (!empty($_POST['phone'])) {
        $phone = sanitize($_POST['phone']);
        if (!preg_match('/^[0-9+\-\s]{8,15}$/', $phone)) {
            $errors['phone'] = 'Format nomor telepon tidak valid';
        } else {
            $formData['phone'] = $phone;
        }
    } else {
        $formData['phone'] = '';
    }

    if (empty($errors)) {
        $userData = [
            'id' => $userId,
            'name' => $formData['name'],
            'email' => $formData['email'],
            'phone' => $formData['phone'],
        ];

        if ($userModel->update($userData)) {
            $success = true;
            $_SESSION['user_name'] = $formData['name'];
            $user = $userModel->getById($userId);
        } else {
            $errors['general'] = 'Gagal memperbarui profil. Silakan coba lagi.';
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
                            <a href="profile.php" class="flex items-center p-3 rounded-lg bg-blue-50 text-blue-700">
                                <i class="fas fa-user-edit w-6"></i>
                                <span>Edit Profil</span>
                            </a>
                        </li>
                        <li>
                            <a href="change-password.php" class="flex items-center p-3 rounded-lg hover:bg-gray-100 transition-colors">
                                <i class="fas fa-lock w-6"></i>
                                <span>Ubah Password</span>
                            </a>
                        </li>
                        <li>
                            <a href="wishlist.php" class="flex items-center p-3 rounded-lg hover:bg-gray-100 transition-colors">
                                <i class="fas fa-heart w-6"></i>
                                <span>Daftar Keinginan</span>
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
                    <h1 class="text-2xl font-semibold">Edit Profil</h1>
                </div>

                <?php if ($success): ?>
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 m-6" role="alert">
                        <p class="font-medium">Sukses!</p>
                        <p>Profil Anda berhasil diperbarui.</p>
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
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-600">*</span></label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($formData['name']); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <?php if (isset($errors['name'])): ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo $errors['name']; ?></p>
                            <?php endif; ?>
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-600">*</span></label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($formData['email']); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <?php if (isset($errors['email'])): ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo $errors['email']; ?></p>
                            <?php endif; ?>
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($formData['phone']); ?>" pattern="[0-9+\-\s]{8,15}" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Format: 08123456789">
                            <?php if (isset($errors['phone'])): ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo $errors['phone']; ?></p>
                            <?php endif; ?>
                            <p class="mt-1 text-xs text-gray-500">Kami memerlukan nomor telepon Anda untuk konfirmasi pesanan dan pengiriman.</p>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-save mr-2"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>