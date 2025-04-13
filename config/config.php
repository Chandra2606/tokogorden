<?php
session_start();

define('BASE_URL', 'http://localhost/tokogorden/');
define('BASE_PATH', __DIR__ . '/../');

require_once BASE_PATH . 'config/database.php';

// Include model files
require_once BASE_PATH . 'lib/models/User.php';
require_once BASE_PATH . 'lib/models/Order.php';
require_once BASE_PATH . 'lib/models/Product.php';
require_once BASE_PATH . 'lib/models/Category.php';
require_once BASE_PATH . 'lib/models/Discount.php';
require_once BASE_PATH . 'lib/models/Address.php';
require_once BASE_PATH . 'lib/models/Wishlist.php';
require_once BASE_PATH . 'lib/models/Review.php';

function sanitize($data)
{
    global $conn;
    return mysqli_real_escape_string($conn, htmlspecialchars(trim($data)));
}

function redirect($url)
{
    header('Location: ' . BASE_URL . $url);
    exit;
}

function setFlashMessage($type, $message)
{
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage()
{
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function isAdmin()
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function formatRupiah($angka)
{
    if ($angka === null || !is_numeric($angka)) {
        $angka = 0;
    }

    // Coba gunakan NumberFormatter jika tersedia (lebih modern)
    if (class_exists('NumberFormatter')) {
        $formatter = new NumberFormatter('id_ID', NumberFormatter::CURRENCY);
        return $formatter->formatCurrency($angka, 'IDR');
    }

    // Fallback ke metode lama jika NumberFormatter tidak tersedia
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

function generateSlug($text)
{
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', ' ', $text);
    $text = preg_replace('/\s/', '-', $text);
    return $text;
}
