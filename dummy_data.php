<?php
require_once 'config/database.php';

// Pastikan tabel sudah dibuat terlebih dahulu
require_once 'config/init_database.php';

// Buat koneksi baru karena koneksi di init_database.php sudah ditutup
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Data sudah ada di database, kita perlu membersihkan data terlebih dahulu
// PERHATIAN: Ini akan menghapus semua data yang ada!
$tables = [
    "wishlists",
    "reviews",
    "order_items",
    "orders",
    "product_discounts",
    "discounts",
    "products",
    "categories",
    "users"
];

// Nonaktifkan foreign key checks sementara untuk memudahkan penghapusan
$conn->query("SET FOREIGN_KEY_CHECKS = 0");

// Hapus data dari semua tabel
foreach ($tables as $table) {
    // Skip menghapus tabel users jika ingin mempertahankan admin
    if ($table == "users") {
        $conn->query("DELETE FROM users WHERE role != 'admin'");
    } else {
        $conn->query("TRUNCATE TABLE $table");
    }
}

// Aktifkan kembali foreign key checks
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

echo "Data lama berhasil dibersihkan<br>";

// 1. Data Kategori
$categories = [
    ['name' => 'Gorden Blackout', 'slug' => 'gorden-blackout'],
    ['name' => 'Gorden Minimalis', 'slug' => 'gorden-minimalis'],
    ['name' => 'Gorden Jendela', 'slug' => 'gorden-jendela'],
    ['name' => 'Gorden Dapur', 'slug' => 'gorden-dapur'],
    ['name' => 'Gorden Kamar Mandi', 'slug' => 'gorden-kamar-mandi'],
    ['name' => 'Aksesoris Gorden', 'slug' => 'aksesoris-gorden']
];

foreach ($categories as $category) {
    $sql = "INSERT INTO categories (name, slug) VALUES ('{$category['name']}', '{$category['slug']}')";
    if ($conn->query($sql) === TRUE) {
        echo "Kategori '{$category['name']}' berhasil ditambahkan<br>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error . "<br>";
    }
}

// 2. Data Produk
$products = [
    [
        'category_id' => 1,
        'name' => 'Gorden Blackout Premium',
        'slug' => 'gorden-blackout-premium',
        'description' => 'Gorden blackout premium yang mampu memblokir 99% cahaya dari luar',
        'price' => 450000,
        'stock' => 35,
        'image' => 'blackout-premium.jpg'
    ],
    [
        'category_id' => 1,
        'name' => 'Gorden Blackout Standar',
        'slug' => 'gorden-blackout-standar',
        'description' => 'Gorden blackout standar dengan kualitas terbaik dan harga terjangkau',
        'price' => 350000,
        'stock' => 50,
        'image' => 'blackout-standar.jpg'
    ],
    [
        'category_id' => 2,
        'name' => 'Gorden Minimalis Polos',
        'slug' => 'gorden-minimalis-polos',
        'description' => 'Gorden minimalis polos dengan warna-warna elegan untuk ruang tamu modern',
        'price' => 275000,
        'stock' => 40,
        'image' => 'minimalis-polos.jpg'
    ],
    [
        'category_id' => 2,
        'name' => 'Gorden Minimalis Motif',
        'slug' => 'gorden-minimalis-motif',
        'description' => 'Gorden minimalis dengan motif sederhana yang menambah estetika ruangan',
        'price' => 300000,
        'stock' => 30,
        'image' => 'minimalis-motif.jpg'
    ],
    [
        'category_id' => 3,
        'name' => 'Gorden Jendela Kecil',
        'slug' => 'gorden-jendela-kecil',
        'description' => 'Gorden khusus untuk jendela kecil dengan berbagai motif menarik',
        'price' => 150000,
        'stock' => 60,
        'image' => 'jendela-kecil.jpg'
    ],
    [
        'category_id' => 3,
        'name' => 'Gorden Jendela Besar',
        'slug' => 'gorden-jendela-besar',
        'description' => 'Gorden untuk jendela besar dengan bahan berkualitas dan tahan lama',
        'price' => 400000,
        'stock' => 25,
        'image' => 'jendela-besar.jpg'
    ],
    [
        'category_id' => 4,
        'name' => 'Gorden Dapur Anti Minyak',
        'slug' => 'gorden-dapur-anti-minyak',
        'description' => 'Gorden dapur dengan lapisan anti minyak, mudah dibersihkan',
        'price' => 225000,
        'stock' => 45,
        'image' => 'dapur-anti-minyak.jpg'
    ],
    [
        'category_id' => 4,
        'name' => 'Gorden Dapur Motif Buah',
        'slug' => 'gorden-dapur-motif-buah',
        'description' => 'Gorden dapur dengan motif buah-buahan yang ceria',
        'price' => 200000,
        'stock' => 55,
        'image' => 'dapur-motif-buah.jpg'
    ],
    [
        'category_id' => 5,
        'name' => 'Gorden Kamar Mandi Anti Air',
        'slug' => 'gorden-kamar-mandi-anti-air',
        'description' => 'Gorden kamar mandi dengan bahan anti air dan anti jamur',
        'price' => 175000,
        'stock' => 70,
        'image' => 'km-anti-air.jpg'
    ],
    [
        'category_id' => 6,
        'name' => 'Bracket Gorden Minimalis',
        'slug' => 'bracket-gorden-minimalis',
        'description' => 'Bracket gorden dengan desain minimalis cocok untuk segala ruangan',
        'price' => 120000,
        'stock' => 80,
        'image' => 'bracket-minimalis.jpg'
    ]
];

foreach ($products as $product) {
    $sql = "INSERT INTO products (category_id, name, slug, description, price, stock, image) 
            VALUES (
                {$product['category_id']}, 
                '{$product['name']}', 
                '{$product['slug']}', 
                '{$product['description']}', 
                {$product['price']}, 
                {$product['stock']}, 
                '{$product['image']}'
            )";
    if ($conn->query($sql) === TRUE) {
        echo "Produk '{$product['name']}' berhasil ditambahkan<br>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error . "<br>";
    }
}

// 3. Data Diskon
$discounts = [
    [
        'name' => 'Diskon Akhir Tahun',
        'type' => 'time',
        'value' => 15.00,
        'is_percentage' => 1,
        'start_date' => '2023-12-20 00:00:00',
        'end_date' => '2023-12-31 23:59:59',
        'active' => 1
    ],
    [
        'name' => 'Diskon Bundle Gorden',
        'type' => 'bundle',
        'value' => 10.00,
        'is_percentage' => 1,
        'min_qty' => 2,
        'active' => 1
    ],
    [
        'name' => 'Voucher Welcome',
        'type' => 'voucher',
        'value' => 500.00,
        'is_percentage' => 0,
        'code' => 'WELCOME50',
        'active' => 1
    ],
    [
        'name' => 'Diskon Produk Blackout',
        'type' => 'product',
        'value' => 5.00,
        'is_percentage' => 1,
        'active' => 1
    ]
];

foreach ($discounts as $discount) {
    $sql = "INSERT INTO discounts (name, type, value, is_percentage, min_qty, code, start_date, end_date, active) 
            VALUES (
                '{$discount['name']}', 
                '{$discount['type']}', 
                {$discount['value']}, 
                {$discount['is_percentage']}, 
                " . (isset($discount['min_qty']) ? $discount['min_qty'] : "NULL") . ", 
                " . (isset($discount['code']) ? "'{$discount['code']}'" : "NULL") . ", 
                " . (isset($discount['start_date']) ? "'{$discount['start_date']}'" : "NULL") . ", 
                " . (isset($discount['end_date']) ? "'{$discount['end_date']}'" : "NULL") . ", 
                {$discount['active']}
            )";
    $conn->query($sql);
}
echo "Diskon berhasil ditambahkan<br>";

// 4. Hubungkan Produk dengan Diskon
$product_discounts = [
    ['product_id' => 1, 'discount_id' => 4],
    ['product_id' => 2, 'discount_id' => 4],
    ['product_id' => 3, 'discount_id' => 2],
    ['product_id' => 4, 'discount_id' => 2],
    ['product_id' => 5, 'discount_id' => 1],
    ['product_id' => 6, 'discount_id' => 1]
];

foreach ($product_discounts as $pd) {
    $sql = "INSERT INTO product_discounts (product_id, discount_id) 
            VALUES ({$pd['product_id']}, {$pd['discount_id']})";
    $conn->query($sql);
}
echo "Produk-Diskon berhasil ditambahkan<br>";

// 5. Tambah User Customer
$customers = [
    [
        'name' => 'Budi Santoso',
        'email' => 'budi@example.com',
        'password' => password_hash('budi123', PASSWORD_DEFAULT),
        'phone' => '081234567890',
        'address' => 'Jl. Sudirman No. 123, Jakarta'
    ],
    [
        'name' => 'Siti Nuraini',
        'email' => 'siti@example.com',
        'password' => password_hash('siti123', PASSWORD_DEFAULT),
        'phone' => '085678901234',
        'address' => 'Jl. Thamrin No. 45, Jakarta'
    ],
    [
        'name' => 'Agus Prasetyo',
        'email' => 'agus@example.com',
        'password' => password_hash('agus123', PASSWORD_DEFAULT),
        'phone' => '082345678901',
        'address' => 'Jl. Gatot Subroto No. 67, Jakarta'
    ]
];

foreach ($customers as $customer) {
    $sql = "INSERT INTO users (name, email, password, role, phone, address) 
            VALUES (
                '{$customer['name']}', 
                '{$customer['email']}', 
                '{$customer['password']}', 
                'customer', 
                '{$customer['phone']}', 
                '{$customer['address']}'
            )";
    $conn->query($sql);
}
echo "Customer berhasil ditambahkan<br>";

// 6. Data Order
$orders = [
    [
        'user_id' => 2, // Budi
        'total_price' => 450000,
        'status' => 'delivered',
        'payment_status' => 'paid',
        'shipping_address' => 'Jl. Sudirman No. 123, Jakarta',
        'phone' => '081234567890',
        'notes' => 'Tolong dibungkus rapi'
    ],
    [
        'user_id' => 3, // Siti
        'total_price' => 625000,
        'status' => 'processing',
        'payment_status' => 'paid',
        'shipping_address' => 'Jl. Thamrin No. 45, Jakarta',
        'phone' => '085678901234',
        'notes' => 'Hubungi sebelum pengiriman'
    ],
    [
        'user_id' => 4, // Agus
        'total_price' => 350000,
        'status' => 'pending',
        'payment_status' => 'unpaid',
        'shipping_address' => 'Jl. Gatot Subroto No. 67, Jakarta',
        'phone' => '082345678901',
        'notes' => ''
    ]
];

foreach ($orders as $order) {
    $sql = "INSERT INTO orders (user_id, total_price, status, payment_status, shipping_address, phone, notes) 
            VALUES (
                {$order['user_id']}, 
                {$order['total_price']}, 
                '{$order['status']}', 
                '{$order['payment_status']}', 
                '{$order['shipping_address']}', 
                '{$order['phone']}', 
                '{$order['notes']}'
            )";
    $conn->query($sql);
}
echo "Order berhasil ditambahkan<br>";

// 7. Data Order Items
$order_items = [
    ['order_id' => 1, 'product_id' => 1, 'quantity' => 1, 'price' => 450000, 'discount_amount' => 0],
    ['order_id' => 2, 'product_id' => 3, 'quantity' => 1, 'price' => 275000, 'discount_amount' => 0],
    ['order_id' => 2, 'product_id' => 5, 'quantity' => 1, 'price' => 150000, 'discount_amount' => 0],
    ['order_id' => 2, 'product_id' => 10, 'quantity' => 2, 'price' => 120000, 'discount_amount' => 0],
    ['order_id' => 3, 'product_id' => 2, 'quantity' => 1, 'price' => 350000, 'discount_amount' => 0]
];

foreach ($order_items as $item) {
    $sql = "INSERT INTO order_items (order_id, product_id, quantity, price, discount_amount) 
            VALUES (
                {$item['order_id']}, 
                {$item['product_id']}, 
                {$item['quantity']}, 
                {$item['price']}, 
                {$item['discount_amount']}
            )";
    $conn->query($sql);
}
echo "Order Items berhasil ditambahkan<br>";

// 8. Data Review
$reviews = [
    [
        'user_id' => 2, // Budi
        'product_id' => 1,
        'rating' => 5,
        'comment' => 'Gorden blackoutnya sangat bagus, benar-benar menyerap 99% cahaya. Sangat puas!'
    ],
    [
        'user_id' => 3, // Siti
        'product_id' => 3,
        'rating' => 4,
        'comment' => 'Gorden minimalisnya bagus, tapi pengirimannya agak lama.'
    ],
    [
        'user_id' => 3, // Siti
        'product_id' => 5,
        'rating' => 5,
        'comment' => 'Cocok untuk jendela kecil di kamar anak saya.'
    ],
    [
        'user_id' => 3, // Siti
        'product_id' => 10,
        'rating' => 4,
        'comment' => 'Bracket gordennnya kokoh dan mudah dipasang.'
    ]
];

foreach ($reviews as $review) {
    $sql = "INSERT INTO reviews (user_id, product_id, rating, comment) 
            VALUES (
                {$review['user_id']}, 
                {$review['product_id']}, 
                {$review['rating']}, 
                '{$review['comment']}'
            )";
    $conn->query($sql);
}
echo "Review berhasil ditambahkan<br>";

// 9. Data Wishlist
$wishlists = [
    ['user_id' => 2, 'product_id' => 6],
    ['user_id' => 2, 'product_id' => 7],
    ['user_id' => 3, 'product_id' => 2],
    ['user_id' => 3, 'product_id' => 8],
    ['user_id' => 4, 'product_id' => 9]
];

foreach ($wishlists as $wishlist) {
    $sql = "INSERT INTO wishlists (user_id, product_id) 
            VALUES ({$wishlist['user_id']}, {$wishlist['product_id']})";
    $conn->query($sql);
}
echo "Wishlist berhasil ditambahkan<br>";

// 10. Data Banner
$banners = [
    [
        'title' => 'Promo Akhir Tahun',
        'description' => 'Dapatkan diskon hingga 15% untuk semua produk gorden',
        'image' => 'banner-year-end.jpg',
        'link' => 'products.php',
        'is_active' => 1,
        'priority' => 1
    ],
    [
        'title' => 'Koleksi Gorden Blackout',
        'description' => 'Temukan koleksi gorden blackout premium untuk kamar anda',
        'image' => 'banner-blackout.jpg',
        'link' => 'category.php?slug=gorden-blackout',
        'is_active' => 1,
        'priority' => 2
    ],
    [
        'title' => 'Gorden Minimalis',
        'description' => 'Gorden minimalis untuk tampilan modern',
        'image' => 'banner-minimalis.jpg',
        'link' => 'category.php?slug=gorden-minimalis',
        'is_active' => 1,
        'priority' => 3
    ]
];

foreach ($banners as $banner) {
    $sql = "INSERT INTO banners (title, description, image, link, is_active, priority) 
            VALUES (
                '{$banner['title']}', 
                '{$banner['description']}', 
                '{$banner['image']}', 
                '{$banner['link']}', 
                {$banner['is_active']}, 
                {$banner['priority']}
            )";
    $conn->query($sql);
}
echo "Banner berhasil ditambahkan<br>";

echo "<h3>Semua data dummy berhasil ditambahkan!</h3>";
$conn->close();
