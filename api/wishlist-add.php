<?php
require_once '../config/config.php';
require_once '../lib/models/Wishlist.php';

header('Content-Type: application/json');

// Pastikan user sudah login
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Silahkan login terlebih dahulu']);
    exit;
}

// Pastikan product_id tersedia
if (!isset($_GET['product_id']) || empty($_GET['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID Produk tidak valid']);
    exit;
}

$productId = (int) $_GET['product_id'];
$userId = $_SESSION['user_id'];

$wishlistModel = new Wishlist($conn);

// Periksa apakah produk sudah ada di wishlist
if ($wishlistModel->isProductInWishlist($userId, $productId)) {
    echo json_encode(['success' => true, 'message' => 'Produk sudah ada di wishlist']);
    exit;
}

// Tambahkan produk ke wishlist
if ($wishlistModel->add($userId, $productId)) {
    echo json_encode(['success' => true, 'message' => 'Produk berhasil ditambahkan ke wishlist']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menambahkan produk ke wishlist']);
}
