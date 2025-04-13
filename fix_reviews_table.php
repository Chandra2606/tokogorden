<?php
// Script untuk memperbaiki struktur tabel reviews dan products

// Koneksi ke database
require_once 'config/config.php';

// Aktifkan display errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<pre>";
echo "===== PERBAIKAN STRUKTUR TABEL =====\n\n";

// === FIX UNTUK TABEL REVIEWS ===
echo "MEMERIKSA TABEL REVIEWS...\n";

// Cek apakah kolom order_id sudah ada
$checkSql = "SHOW COLUMNS FROM reviews LIKE 'order_id'";
$checkResult = $conn->query($checkSql);

if ($checkResult->num_rows == 0) {
    // Kolom belum ada, tambahkan
    $alterSql = "ALTER TABLE reviews ADD COLUMN order_id INT(11) NOT NULL DEFAULT 0 AFTER product_id";

    if ($conn->query($alterSql) === TRUE) {
        echo "- Kolom order_id berhasil ditambahkan!\n";
    } else {
        echo "- Error menambahkan kolom order_id: " . $conn->error . "\n";
    }
} else {
    echo "- Kolom order_id sudah ada!\n";
}

// Cek apakah kolom review sudah ada
$checkSql = "SHOW COLUMNS FROM reviews LIKE 'review'";
$checkResult = $conn->query($checkSql);

if ($checkResult->num_rows == 0) {
    // Cek apakah kolom comment ada
    $checkCommentSql = "SHOW COLUMNS FROM reviews LIKE 'comment'";
    $commentResult = $conn->query($checkCommentSql);

    if ($commentResult->num_rows > 0) {
        // Rename kolom comment menjadi review
        $alterSql = "ALTER TABLE reviews CHANGE COLUMN comment review TEXT";
    } else {
        // Tambahkan kolom review baru
        $alterSql = "ALTER TABLE reviews ADD COLUMN review TEXT AFTER rating";
    }

    if ($conn->query($alterSql) === TRUE) {
        echo "- Kolom review berhasil ditambahkan/diubah!\n";
    } else {
        echo "- Error menambahkan/mengubah kolom review: " . $conn->error . "\n";
    }
} else {
    echo "- Kolom review sudah ada!\n";
}

// === FIX UNTUK TABEL PRODUCTS ===
echo "\nMEMERIKSA TABEL PRODUCTS...\n";

// Cek apakah kolom rating sudah ada di tabel products
$checkSql = "SHOW COLUMNS FROM products LIKE 'rating'";
$checkResult = $conn->query($checkSql);

if ($checkResult->num_rows == 0) {
    // Tambahkan kolom rating ke tabel products
    $alterSql = "ALTER TABLE products ADD COLUMN rating DECIMAL(3,1) DEFAULT 0 AFTER description";

    if ($conn->query($alterSql) === TRUE) {
        echo "- Kolom rating berhasil ditambahkan ke tabel products!\n";
    } else {
        echo "- Error menambahkan kolom rating ke tabel products: " . $conn->error . "\n";
    }
} else {
    echo "- Kolom rating sudah ada di tabel products!\n";
}

// Tampilkan struktur tabel untuk verifikasi
echo "\nSTRUKTUR TABEL REVIEWS:\n";
$result = $conn->query("DESCRIBE reviews");
while ($row = $result->fetch_assoc()) {
    echo "- {$row['Field']} - {$row['Type']} - Null: {$row['Null']} - Default: {$row['Default']}\n";
}

echo "\nSTRUKTUR TABEL PRODUCTS (hanya kolom rating):\n";
$result = $conn->query("SHOW COLUMNS FROM products LIKE 'rating'");
if ($row = $result->fetch_assoc()) {
    echo "- {$row['Field']} - {$row['Type']} - Null: {$row['Null']} - Default: {$row['Default']}\n";
} else {
    echo "- Kolom rating tidak ditemukan!\n";
}

echo "\n===== PERBAIKAN SELESAI =====";
echo "</pre>";
