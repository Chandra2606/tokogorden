<?php
$active_page = 'register';
$page_title = 'Pendaftaran Akun';
require_once 'config/config.php';

if (isLoggedIn()) {
    redirect('');
}

// Inisialisasi variabel
$name = '';
$email = '';
$phone = '';
$address = '';
$errors = [];

// Proses form registrasi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi input
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);

    if (empty($name)) {
        $errors['name'] = 'Nama harus diisi';
    }

    if (empty($email)) {
        $errors['email'] = 'Email harus diisi';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Format email tidak valid';
    }

    if (empty($password)) {
        $errors['password'] = 'Password harus diisi';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password minimal 6 karakter';
    }

    if ($password !== $password_confirm) {
        $errors['password_confirm'] = 'Konfirmasi password tidak cocok';
    }

    // Jika tidak ada error, proses registrasi
    if (empty($errors)) {
        require_once 'lib/models/User.php';
        $userModel = new User($conn);

        // Cek apakah email sudah terdaftar
        $existingUser = $userModel->getByEmail($email);
        if ($existingUser) {
            $errors['email'] = 'Email sudah terdaftar, silahkan gunakan email lain';
        } else {
            // Data untuk registrasi
            $userData = [
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'role' => 'customer',
                'phone' => $phone,
                'address' => $address
            ];

            // Proses registrasi
            $userId = $userModel->create($userData);

            if ($userId) {
                // Set session dan redirect
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_role'] = 'customer';

                // Redirect ke dashboard customer
                redirect('customer/dashboard.php');
            } else {
                $errors['register'] = 'Gagal melakukan pendaftaran, silahkan coba lagi';
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-10">
    <div class="max-w-lg mx-auto bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-blue-800 text-white py-4 px-6">
            <h2 class="text-xl font-semibold">Daftar Akun Baru</h2>
        </div>
        <div class="py-6 px-6">
            <?php if (!empty($errors['register'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo $errors['register']; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-4">
                <div>
                    <label for="name" class="block text-gray-700 font-medium mb-1">Nama Lengkap</label>
                    <input type="text" id="name" name="name" value="<?php echo $name; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <?php if (!empty($errors['name'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?php echo $errors['name']; ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="email" class="block text-gray-700 font-medium mb-1">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo $email; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <?php if (!empty($errors['email'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?php echo $errors['email']; ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="password" class="block text-gray-700 font-medium mb-1">Password</label>
                    <input type="password" id="password" name="password" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <?php if (!empty($errors['password'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?php echo $errors['password']; ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="password_confirm" class="block text-gray-700 font-medium mb-1">Konfirmasi Password</label>
                    <input type="password" id="password_confirm" name="password_confirm" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <?php if (!empty($errors['password_confirm'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?php echo $errors['password_confirm']; ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="phone" class="block text-gray-700 font-medium mb-1">Nomor Telepon</label>
                    <input type="text" id="phone" name="phone" value="<?php echo $phone; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label for="address" class="block text-gray-700 font-medium mb-1">Alamat</label>
                    <textarea id="address" name="address" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" rows="3"><?php echo $address; ?></textarea>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="agree" name="agree" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" required>
                    <label for="agree" class="ml-2 block text-sm text-gray-700">Saya setuju dengan <a href="<?php echo BASE_URL . 'terms.php'; ?>" class="text-blue-600 hover:text-blue-800">Syarat & Ketentuan</a></label>
                </div>

                <div>
                    <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                        Daftar
                    </button>
                </div>
            </form>

            <div class="mt-4 text-center">
                <p class="text-gray-600">Sudah memiliki akun? <a href="login.php" class="text-blue-600 hover:text-blue-800">Masuk sekarang</a></p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>