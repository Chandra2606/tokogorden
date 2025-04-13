-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 13 Apr 2025 pada 03.32
-- Versi server: 8.0.30
-- Versi PHP: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tokogorden`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `addresses`
--

CREATE TABLE `addresses` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `recipient_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `province` varchar(100) NOT NULL,
  `city` varchar(100) NOT NULL,
  `district` varchar(100) NOT NULL,
  `postal_code` varchar(10) NOT NULL,
  `full_address` text NOT NULL,
  `is_default` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `addresses`
--

INSERT INTO `addresses` (`id`, `user_id`, `recipient_name`, `phone`, `province`, `city`, `district`, `postal_code`, `full_address`, `is_default`, `created_at`, `updated_at`) VALUES
(1, 2, 'Budi Santoso', '081234567890', 'Riau', 'Batam', 'Utara', '74783', '0', 1, '2025-04-13 00:03:43', '2025-04-13 00:03:43'),
(2, 5, 'Jamal', '0834573648', 'Sumatera Barat', 'Kota Padang', 'Padang Barat', '76835', 'Padang', 1, '2025-04-13 00:17:07', '2025-04-13 00:32:45'),
(3, 7, 'Muklis', '087485789', 'Riau', 'Batam', 'Batam', '84758', '0', 1, '2025-04-13 01:48:15', '2025-04-13 01:48:15');

-- --------------------------------------------------------

--
-- Struktur dari tabel `banners`
--

CREATE TABLE `banners` (
  `id` int NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text,
  `image` varchar(255) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `priority` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `banners`
--

INSERT INTO `banners` (`id`, `title`, `description`, `image`, `link`, `is_active`, `priority`, `created_at`) VALUES
(1, 'Promo Akhir Tahun', 'Dapatkan diskon hingga 15% untuk semua produk gorden', 'banner-year-end.jpg', 'products.php', 1, 1, '2025-04-12 21:52:49'),
(2, 'Koleksi Gorden Blackout', 'Temukan koleksi gorden blackout premium untuk kamar anda', 'banner-blackout.jpg', 'category.php?slug=gorden-blackout', 1, 2, '2025-04-12 21:52:49'),
(3, 'Gorden Minimalis', 'Gorden minimalis untuk tampilan modern', 'banner-minimalis.jpg', 'category.php?slug=gorden-minimalis', 1, 3, '2025-04-12 21:52:49');

-- --------------------------------------------------------

--
-- Struktur dari tabel `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `created_at`) VALUES
(1, 'Gorden Blackout', 'gorden-blackout', '2025-04-12 21:52:48'),
(2, 'Gorden Minimalis', 'gorden-minimalis', '2025-04-12 21:52:48'),
(3, 'Gorden Jendela', 'gorden-jendela', '2025-04-12 21:52:48'),
(4, 'Gorden Dapur', 'gorden-dapur', '2025-04-12 21:52:48'),
(5, 'Gorden Kamar Mandi', 'gorden-kamar-mandi', '2025-04-12 21:52:48'),
(6, 'Aksesoris Gorden', 'aksesoris-gorden', '2025-04-12 21:52:48');

-- --------------------------------------------------------

--
-- Struktur dari tabel `discounts`
--

CREATE TABLE `discounts` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('bundle','product','voucher','time') NOT NULL,
  `value` decimal(5,2) NOT NULL,
  `is_percentage` tinyint(1) DEFAULT '1',
  `min_qty` int DEFAULT NULL,
  `code` varchar(20) DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `discounts`
--

INSERT INTO `discounts` (`id`, `name`, `type`, `value`, `is_percentage`, `min_qty`, `code`, `start_date`, `end_date`, `active`, `created_at`) VALUES
(7, 'APRIL BAHAGIA', 'time', 9.00, 1, NULL, NULL, '2025-04-10 07:14:00', '2025-04-19 07:14:00', 1, '2025-04-13 00:14:48'),
(8, 'New Member', 'voucher', 7.00, 1, NULL, 'MEMBER', NULL, NULL, 1, '2025-04-13 01:47:48');

-- --------------------------------------------------------

--
-- Struktur dari tabel `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `total_price` decimal(12,2) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled','completed') NOT NULL DEFAULT 'pending',
  `payment_status` enum('unpaid','paid') NOT NULL DEFAULT 'unpaid',
  `shipping_address` text,
  `phone` varchar(15) DEFAULT NULL,
  `notes` text,
  `payment_method` varchar(50) DEFAULT 'transfer_bank',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_price`, `status`, `payment_status`, `shipping_address`, `phone`, `notes`, `payment_method`, `created_at`) VALUES
(1, 2, 450000.00, 'delivered', 'paid', 'Jl. Sudirman No. 123, Jakarta', '081234567890', 'Tolong dibungkus rapi', 'transfer_bank', '2025-04-12 21:52:49'),
(2, 3, 625000.00, 'processing', 'paid', 'Jl. Thamrin No. 45, Jakarta', '085678901234', 'Hubungi sebelum pengiriman', 'transfer_bank', '2025-04-12 21:52:49'),
(3, 4, 350000.00, 'pending', 'unpaid', 'Jl. Gatot Subroto No. 67, Jakarta', '082345678901', '', 'transfer_bank', '2025-04-12 21:52:49'),
(4, 2, 121500.00, 'delivered', 'paid', 'Budi Santoso, 0, Utara, Batam, Riau 74783', '081234567890', 'tes', 'transfer_bank', '2025-04-13 00:04:04'),
(5, 5, 839250.00, 'delivered', 'paid', 'Jamal, 0, Padang Barat, Kota Padang, Sumatera Barat 76835', '0834573648', 'TES COD', 'transfer_bank', '2025-04-13 00:17:42'),
(6, 5, 332500.00, 'delivered', 'paid', 'Jamal, 0, Padang Barat, Kota Padang, Sumatera Barat 76835', '0834573648', '', 'transfer_bank', '2025-04-13 00:29:38'),
(7, 7, 790500.00, 'completed', 'paid', 'Muklis, 0, Batam, Batam, Riau 84758', '087485789', 'Tes Pesan', 'transfer_bank', '2025-04-13 01:48:38'),
(8, 7, 338520.00, 'completed', 'paid', 'Muklis, Alamat tidak diisi, Batam, Batam, Riau 84758', '087485789', '', 'transfer_bank', '2025-04-13 02:11:09'),
(9, 7, 350000.00, 'pending', 'unpaid', 'Muklis, Alamat tidak diisi, Batam, Batam, Riau 84758', '087485789', '', 'transfer_bank', '2025-04-13 03:09:03'),
(10, 7, 150000.00, 'pending', 'unpaid', 'Muklis, Alamat tidak diisi, Batam, Batam, Riau 84758', '087485789', '', 'transfer_bank', '2025-04-13 03:13:41'),
(11, 7, 150000.00, 'pending', 'unpaid', 'Muklis, Alamat tidak diisi, Batam, Batam, Riau 84758', '087485789', '', '', '2025-04-13 03:18:39'),
(12, 7, 350000.00, 'pending', 'unpaid', 'Muklis, Alamat tidak diisi, Batam, Batam, Riau 84758', '087485789', '', '', '2025-04-13 03:18:55');

-- --------------------------------------------------------

--
-- Struktur dari tabel `order_items`
--

CREATE TABLE `order_items` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `discount_amount` decimal(12,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`, `discount_amount`, `created_at`) VALUES
(1, 1, 1, 1, 450000.00, 0.00, '2025-04-12 21:52:49'),
(2, 2, 3, 1, 275000.00, 0.00, '2025-04-12 21:52:49'),
(3, 2, 5, 1, 150000.00, 0.00, '2025-04-12 21:52:49'),
(4, 2, 10, 2, 120000.00, 0.00, '2025-04-12 21:52:49'),
(5, 3, 2, 1, 350000.00, 0.00, '2025-04-12 21:52:49'),
(6, 4, 5, 1, 135000.00, NULL, '2025-04-13 00:04:04'),
(7, 5, 3, 1, 250250.00, NULL, '2025-04-13 00:17:42'),
(8, 5, 6, 1, 364000.00, NULL, '2025-04-13 00:17:42'),
(9, 5, 7, 1, 225000.00, NULL, '2025-04-13 00:17:42'),
(10, 6, 2, 1, 332500.00, NULL, '2025-04-13 00:29:38'),
(11, 7, 18, 1, 400000.00, NULL, '2025-04-13 01:48:38'),
(12, 7, 1, 1, 450000.00, NULL, '2025-04-13 01:48:38'),
(13, 8, 6, 1, 364000.00, 36000.00, '2025-04-13 02:11:09'),
(14, 9, 2, 1, 350000.00, 0.00, '2025-04-13 03:09:03'),
(15, 10, 5, 1, 150000.00, 0.00, '2025-04-13 03:13:41'),
(16, 11, 5, 1, 150000.00, 0.00, '2025-04-13 03:18:39'),
(17, 12, 2, 1, 350000.00, 0.00, '2025-04-13 03:18:55');

-- --------------------------------------------------------

--
-- Struktur dari tabel `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `category_id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text,
  `rating` decimal(3,1) DEFAULT '0.0',
  `price` decimal(12,2) NOT NULL,
  `stock` int NOT NULL DEFAULT '0',
  `image` varchar(255) DEFAULT NULL,
  `is_featured` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `description`, `rating`, `price`, `stock`, `image`, `is_featured`, `created_at`) VALUES
(1, 1, 'Gorden Blackout Premium', 'gorden-blackout-premium', 'Gorden blackout premium yang mampu memblokir 99% cahaya dari luar', 4.0, 450000.00, 34, 'blackout-premium.jpg', 1, '2025-04-12 21:52:48'),
(2, 1, 'Gorden Blackout Standar', 'gorden-blackout-standar', 'Gorden blackout standar dengan kualitas terbaik dan harga terjangkau', 0.0, 350000.00, 47, 'blackout-standar.jpg', 1, '2025-04-12 21:52:48'),
(3, 2, 'Gorden Minimalis Polos', 'gorden-minimalis-polos', 'Gorden minimalis polos dengan warna-warna elegan untuk ruang tamu modern', 0.0, 275000.00, 39, 'minimalis-polos.jpg', 0, '2025-04-12 21:52:48'),
(4, 2, 'Gorden Minimalis Motif', 'gorden-minimalis-motif', 'Gorden minimalis dengan motif sederhana yang menambah estetika ruangan', 0.0, 300000.00, 30, 'minimalis-motif.jpg', 0, '2025-04-12 21:52:48'),
(5, 3, 'Gorden Jendela Kecil', 'gorden-jendela-kecil', 'Gorden khusus untuk jendela kecil dengan berbagai motif menarik', 0.0, 150000.00, 57, 'jendela-kecil.jpg', 1, '2025-04-12 21:52:48'),
(6, 3, 'Gorden Jendela Besar', 'gorden-jendela-besar', 'Gorden untuk jendela besar dengan bahan berkualitas dan tahan lama', 4.0, 400000.00, 23, 'jendela-besar.jpg', 0, '2025-04-12 21:52:48'),
(7, 4, 'Gorden Dapur Anti Minyak', 'gorden-dapur-anti-minyak', 'Gorden dapur dengan lapisan anti minyak, mudah dibersihkan', 0.0, 225000.00, 44, 'dapur-anti-minyak.jpg', 0, '2025-04-12 21:52:48'),
(8, 4, 'Gorden Dapur Motif Buah', 'gorden-dapur-motif-buah', 'Gorden dapur dengan motif buah-buahan yang ceria', 0.0, 200000.00, 55, 'dapur-motif-buah.jpg', 1, '2025-04-12 21:52:48'),
(9, 5, 'Gorden Kamar Mandi Anti Air', 'gorden-kamar-mandi-anti-air', 'Gorden kamar mandi dengan bahan anti air dan anti jamur', 0.0, 175000.00, 70, 'km-anti-air.jpg', 1, '2025-04-12 21:52:48'),
(10, 6, 'Bracket Gorden Minimalis', 'bracket-gorden-minimalis', 'Bracket gorden dengan desain minimalis cocok untuk segala ruangan', 0.0, 120000.00, 80, 'bracket-minimalis.jpg', 1, '2025-04-12 21:52:48'),
(18, 3, 'Gorden Jendela Depan', 'gorden-jendela-depan', 'Tes Produk', 0.0, 400000.00, 9, 'product_1744508689_67fb1711997ed.jpeg', 0, '2025-04-13 01:44:49');

-- --------------------------------------------------------

--
-- Struktur dari tabel `product_discounts`
--

CREATE TABLE `product_discounts` (
  `id` int NOT NULL,
  `product_id` int NOT NULL,
  `discount_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `product_discounts`
--

INSERT INTO `product_discounts` (`id`, `product_id`, `discount_id`, `created_at`) VALUES
(11, 3, 7, '2025-04-13 00:15:17'),
(12, 6, 7, '2025-04-13 00:15:17');

-- --------------------------------------------------------

--
-- Struktur dari tabel `reviews`
--

CREATE TABLE `reviews` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `order_id` int NOT NULL DEFAULT '0',
  `rating` int NOT NULL,
  `review` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `product_id`, `order_id`, `rating`, `review`, `created_at`) VALUES
(1, 2, 1, 0, 5, 'Gorden blackoutnya sangat bagus, benar-benar menyerap 99% cahaya. Sangat puas!', '2025-04-12 21:52:49'),
(2, 3, 3, 0, 4, 'Gorden minimalisnya bagus, tapi pengirimannya agak lama.', '2025-04-12 21:52:49'),
(3, 3, 5, 0, 5, 'Cocok untuk jendela kecil di kamar anak saya.', '2025-04-12 21:52:49'),
(4, 3, 10, 0, 4, 'Bracket gordennnya kokoh dan mudah dipasang.', '2025-04-12 21:52:49'),
(8, 5, 6, 0, 4, 'KEREN', '2025-04-13 01:41:33');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','customer') NOT NULL DEFAULT 'customer',
  `phone` varchar(15) DEFAULT NULL,
  `address` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `phone`, `address`, `created_at`) VALUES
(1, 'Administrator', 'admin@tokogorden.com', '$2y$10$xzglBCO80n3oyFStJX716OV2w5Eu9a4IUfa6/URdhS5fcw2vDWAmi', 'admin', NULL, NULL, '2025-04-12 21:46:17'),
(2, 'Budi Santoso', 'budi@example.com', '$2y$10$tqCpOSqH75YQ8XsTQg1VAOi6/c8aywRhNa9ZN0xJ9UEigULKOmXCy', 'customer', '081234567890', 'Jl. Sudirman No. 123, Jakarta', '2025-04-12 21:52:49'),
(3, 'Siti Nuraini', 'siti@example.com', '$2y$10$jmylj1gjriRn85to0Zvxp.0uouHDTC8TVviBQJDLNECyBddcpa4fu', 'customer', '085678901234', 'Jl. Thamrin No. 45, Jakarta', '2025-04-12 21:52:49'),
(4, 'Agus Prasetyo', 'agus@example.com', '$2y$10$0Q.zWpCm5rdL4pqBqK0ZdetayK8EbyEjwc2eXIKlq4muP3zN2yw9y', 'customer', '082345678901', 'Jl. Gatot Subroto No. 67, Jakarta', '2025-04-12 21:52:49'),
(5, 'Jamal', 'jamal@gmail.com', '$2y$10$V6tNctmOOc6wrBh3svdtlOsJlP8OfmyPxG6sbC4iy3ZjfIrQbP8Mq', 'customer', '0834573648', 'Padang', '2025-04-13 00:16:43'),
(7, 'Muklis', 'muklis@gmail.com', '$2y$10$ilicV0C6zpf6oSwBO0peLOOW4yseU4eUS.gFZmPE.8VrDRbyooo5u', 'customer', '087485789', 'Batam', '2025-04-13 01:46:24');

-- --------------------------------------------------------

--
-- Struktur dari tabel `wishlists`
--

CREATE TABLE `wishlists` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `wishlists`
--

INSERT INTO `wishlists` (`id`, `user_id`, `product_id`, `created_at`) VALUES
(3, 3, 2, '2025-04-12 21:52:49'),
(4, 3, 8, '2025-04-12 21:52:49'),
(5, 4, 9, '2025-04-12 21:52:49'),
(6, 1, 8, '2025-04-12 22:12:14');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indeks untuk tabel `discounts`
--
ALTER TABLE `discounts`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indeks untuk tabel `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `category_id` (`category_id`);

--
-- Indeks untuk tabel `product_discounts`
--
ALTER TABLE `product_discounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `discount_id` (`discount_id`);

--
-- Indeks untuk tabel `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeks untuk tabel `wishlists`
--
ALTER TABLE `wishlists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `banners`
--
ALTER TABLE `banners`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `discounts`
--
ALTER TABLE `discounts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT untuk tabel `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT untuk tabel `product_discounts`
--
ALTER TABLE `product_discounts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `wishlists`
--
ALTER TABLE `wishlists`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `product_discounts`
--
ALTER TABLE `product_discounts`
  ADD CONSTRAINT `product_discounts_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_discounts_ibfk_2` FOREIGN KEY (`discount_id`) REFERENCES `discounts` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `wishlists`
--
ALTER TABLE `wishlists`
  ADD CONSTRAINT `wishlists_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlists_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
