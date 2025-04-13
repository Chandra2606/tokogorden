<?php
require_once 'config/config.php';

$result = $conn->query("SHOW COLUMNS FROM orders LIKE 'payment_method'");
if ($result->num_rows == 0) {
    $sql = "ALTER TABLE orders ADD COLUMN payment_method VARCHAR(50) DEFAULT 'transfer_bank' AFTER notes";

    if ($conn->query($sql) === TRUE) {
        echo "Kolom payment_method berhasil ditambahkan ke tabel orders!";
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    echo "Kolom payment_method sudah ada di tabel orders.";
}

$conn->close();
