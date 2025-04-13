<?php
// Script untuk memperbaiki struktur tabel orders

// Koneksi ke database
require_once 'config/config.php';

// Aktifkan display errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<pre>";
echo "===== PERBAIKAN STRUKTUR TABEL ORDERS =====\n\n";

// Periksa struktur kolom status di tabel orders
$sql = "SHOW COLUMNS FROM orders LIKE 'status'";
$result = $conn->query($sql);

if (!$result) {
    echo "Error mengakses tabel orders: " . $conn->error . "\n";
    exit;
}

if ($result->num_rows == 0) {
    echo "Kolom status tidak ditemukan dalam tabel orders!\n";
    exit;
}

$row = $result->fetch_assoc();
echo "Struktur kolom status saat ini:\n";
echo "- Type: " . $row['Type'] . "\n";
echo "- Null: " . $row['Null'] . "\n";
echo "- Default: " . $row['Default'] . "\n\n";

// Periksa apakah nilai 'completed' sudah ada dalam definisi ENUM
$type = $row['Type'];
if (strpos($type, "'completed'") === false) {
    echo "Nilai 'completed' belum termasuk dalam definisi ENUM.\n";

    // Parsing nilai ENUM saat ini
    if (preg_match("/^enum\((.*)\)$/", $type, $matches)) {
        $enumValues = $matches[1];

        // Tambahkan 'completed' ke daftar nilai ENUM
        $newEnumValues = str_replace("'cancelled'", "'cancelled','completed'", $enumValues);

        // Perbarui definisi kolom status
        $alterSql = "ALTER TABLE orders MODIFY COLUMN status ENUM($newEnumValues) NOT NULL DEFAULT 'pending'";

        echo "Mencoba mengubah struktur kolom status...\n";
        echo "Query: $alterSql\n\n";

        if ($conn->query($alterSql)) {
            echo "Kolom status berhasil diperbarui. Nilai 'completed' telah ditambahkan.\n";
        } else {
            echo "Error mengubah kolom status: " . $conn->error . "\n";
        }
    } else {
        echo "Gagal mengurai definisi ENUM saat ini: $type\n";
    }
} else {
    echo "Nilai 'completed' sudah termasuk dalam definisi ENUM.\n";
}

// Periksa struktur kolom status setelah perubahan
$sql = "SHOW COLUMNS FROM orders LIKE 'status'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

echo "\nStruktur kolom status setelah perubahan:\n";
echo "- Type: " . $row['Type'] . "\n";
echo "- Null: " . $row['Null'] . "\n";
echo "- Default: " . $row['Default'] . "\n";

echo "\n===== PERBAIKAN SELESAI =====";
echo "</pre>";
