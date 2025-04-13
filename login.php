<?php
$active_page = 'login';
$page_title = 'Masuk';
require_once 'config/config.php';

// Jika sudah login, redirect ke halaman utama
if (isLoggedIn()) {
    redirect('');
}

// Inisialisasi variabel
$email = '';
$errors = [];

// Proses form login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi input
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];

    if (empty($email)) {
        $errors['email'] = 'Email harus diisi';
    }

    if (empty($password)) {
        $errors['password'] = 'Password harus diisi';
    }

    // Jika tidak ada error, proses login
    if (empty($errors)) {
        require_once 'lib/models/User.php';
        $userModel = new User($conn);
        $user = $userModel->authenticate($email, $password);

        if ($user) {
            // Set session dan redirect
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];

            // Redirect sesuai role
            if ($user['role'] === 'admin') {
                redirect('admin/index.php');
            } else {
                redirect('customer/dashboard.php');
            }
        } else {
            $errors['login'] = 'Email atau password salah';
        }
    }
}

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-10">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-blue-800 text-white py-4 px-6">
            <h2 class="text-xl font-semibold">Masuk ke Akun Anda</h2>
        </div>
        <div class="py-6 px-6">
            <?php if (!empty($errors['login'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo $errors['login']; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-4">
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

                <div class="flex items-center">
                    <input type="checkbox" id="remember" name="remember" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="remember" class="ml-2 block text-sm text-gray-700">Ingat saya</label>
                </div>

                <div>
                    <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                        Masuk
                    </button>
                </div>
            </form>

            <div class="mt-4 text-center">
                <p class="text-gray-600">Belum memiliki akun? <a href="register.php" class="text-blue-600 hover:text-blue-800">Daftar sekarang</a></p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>