<?php
require_once '../../config/config.php';
require_once '../../lib/models/User.php';
require_once '../../lib/models/Order.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = 'ID pengguna tidak valid';
    redirect('admin/users/index.php');
}

$userId = intval($_GET['id']);
$userModel = new User($conn);
$orderModel = new Order($conn);

if ($userId == $_SESSION['user_id']) {
    $_SESSION['error_message'] = 'Anda tidak dapat menghapus akun Anda sendiri';
    redirect('admin/users/index.php');
}

$user = $userModel->getById($userId);
if (!$user) {
    $_SESSION['error_message'] = 'Pengguna tidak ditemukan';
    redirect('admin/users/index.php');
}

$userOrders = $orderModel->getByUserId($userId);
if (!empty($userOrders)) {
    $_SESSION['error_message'] = 'Pengguna tidak dapat dihapus karena memiliki pesanan. Pertimbangkan untuk nonaktifkan akun ini.';
    redirect('admin/users/index.php');
}

if ($userModel->delete($userId)) {
    $_SESSION['success_message'] = 'Pengguna berhasil dihapus';
} else {
    $_SESSION['error_message'] = 'Gagal menghapus pengguna';
}

redirect('admin/users/index.php');
