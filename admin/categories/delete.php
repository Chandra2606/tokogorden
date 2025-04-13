<?php
require_once '../../config/config.php';
require_once '../../lib/models/Category.php';
require_once '../../lib/models/Product.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = 'ID kategori tidak valid';
    redirect('admin/categories/index.php');
}

$categoryId = intval($_GET['id']);
$categoryModel = new Category($conn);
$productModel = new Product($conn);

$category = $categoryModel->getById($categoryId);
if (!$category) {
    $_SESSION['error_message'] = 'Kategori tidak ditemukan';
    redirect('admin/categories/index.php');
}

$productCount = $categoryModel->getProductCount($categoryId);
if ($productCount > 0) {
    $_SESSION['error_message'] = 'Kategori tidak dapat dihapus karena masih memiliki ' . $productCount . ' produk.';
    redirect('admin/categories/index.php');
}

if ($categoryModel->delete($categoryId)) {
    $_SESSION['success_message'] = 'Kategori berhasil dihapus';
} else {
    $_SESSION['error_message'] = 'Gagal menghapus kategori';
}

redirect('admin/categories/index.php');
