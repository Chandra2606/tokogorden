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

// Periksa apakah produk ada di wishlist
if (!$wishlistModel->isProductInWishlist($userId, $productId)) {
    echo json_encode(['success' => false, 'message' => 'Produk tidak ada di wishlist']);
    exit;
}

// Hapus produk dari wishlist
if ($wishlistModel->remove($userId, $productId)) {
    echo json_encode(['success' => true, 'message' => 'Produk berhasil dihapus dari wishlist']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus produk dari wishlist']);
}
