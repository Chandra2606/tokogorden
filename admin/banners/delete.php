<?php
require_once '../../config/config.php';
require_once '../../lib/models/Banner.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = 'ID banner tidak valid';
    redirect('admin/banners/index.php');
}

$bannerId = intval($_GET['id']);
$bannerModel = new Banner($conn);

$banner = $bannerModel->getById($bannerId);
if (!$banner) {
    $_SESSION['error_message'] = 'Banner tidak ditemukan';
    redirect('admin/banners/index.php');
}

if (!empty($banner['image'])) {
    $imagePath = '../../assets/images/banners/' . $banner['image'];
    if (file_exists($imagePath)) {
        @unlink($imagePath);
    }
}

if ($bannerModel->delete($bannerId)) {
    $_SESSION['success_message'] = 'Banner berhasil dihapus';
} else {
    $_SESSION['error_message'] = 'Gagal menghapus banner';
}

redirect('admin/banners/index.php');
