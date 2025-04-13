<?php
require_once 'config/config.php';
require_once 'lib/models/Wishlist.php';

if (!isLoggedIn()) {
    setFlashMessage('error', 'Silakan login terlebih dahulu untuk menambahkan produk ke wishlist');
    redirect('login.php');
}

if (!isset($_GET['product_id']) || empty($_GET['product_id'])) {
    setFlashMessage('error', 'ID produk tidak valid');
    redirect('products.php');
}

$productId = intval($_GET['product_id']);
$userId = $_SESSION['user_id'];

$wishlistModel = new Wishlist($conn);

$result = $wishlistModel->add($userId, $productId);

if ($result) {
    setFlashMessage('success', 'Produk berhasil ditambahkan ke wishlist');
} else {
    setFlashMessage('error', 'Gagal menambahkan produk ke wishlist');
}

if (isset($_SERVER['HTTP_REFERER'])) {
    $referer = str_replace(BASE_URL, '', $_SERVER['HTTP_REFERER']);
    if (empty($referer)) {
        $referer = 'products.php';
    }
} else {
    $referer = 'products.php';
}
redirect($referer);
