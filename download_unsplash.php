<?php
// Buat direktori jika belum ada
$directories = [
    'assets/images/products',
    'assets/images/banners'
];

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
}

// Fungsi untuk download gambar dengan error handling
function downloadImage($url, $destination)
{
    try {
        $image = @file_get_contents($url);
        if ($image === false) {
            echo "Gagal mengunduh dari $url - Mencoba alternatif...<br>";
            return false;
        }
        file_put_contents($destination, $image);
        echo "Berhasil men-download $destination<br>";
        return true;
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "<br>";
        return false;
    }
}

// Daftar gambar produk dan URL tetap (bukan API random)
$productImages = [
    'blackout-premium.jpg' => [
        'https://images.unsplash.com/photo-1583847268964-b28dc8f51f92',
        'https://images.pexels.com/photos/1743227/pexels-photo-1743227.jpeg'
    ],
    'blackout-standar.jpg' => [
        'https://images.unsplash.com/photo-1615800002234-05c4d488696c',
        'https://images.pexels.com/photos/1643383/pexels-photo-1643383.jpeg'
    ],
    'minimalis-polos.jpg' => [
        'https://images.unsplash.com/photo-1616046229478-9901c5536a45',
        'https://images.pexels.com/photos/945688/pexels-photo-945688.jpeg'
    ],
    'minimalis-motif.jpg' => [
        'https://images.unsplash.com/photo-1540730930991-a9286a5f5020',
        'https://images.pexels.com/photos/1571467/pexels-photo-1571467.jpeg'
    ],
    'jendela-kecil.jpg' => [
        'https://images.unsplash.com/photo-1519710164239-da123dc03ef4',
        'https://images.pexels.com/photos/1668860/pexels-photo-1668860.jpeg'
    ],
    'jendela-besar.jpg' => [
        'https://images.unsplash.com/photo-1513694203232-719a280e022f',
        'https://images.pexels.com/photos/276583/pexels-photo-276583.jpeg'
    ],
    'dapur-anti-minyak.jpg' => [
        'https://images.unsplash.com/photo-1556912172-45b7abe8b7e1',
        'https://images.pexels.com/photos/2062426/pexels-photo-2062426.jpeg'
    ],
    'dapur-motif-buah.jpg' => [
        'https://images.unsplash.com/photo-1600566752355-35792bedcfea',
        'https://images.pexels.com/photos/2736139/pexels-photo-2736139.jpeg'
    ],
    'km-anti-air.jpg' => [
        'https://images.unsplash.com/photo-1584622650111-993a426fbf0a',
        'https://images.pexels.com/photos/6958514/pexels-photo-6958514.jpeg'
    ],
    'bracket-minimalis.jpg' => [
        'https://images.unsplash.com/photo-1521291916-c86a3ec62981',
        'https://images.pexels.com/photos/4050318/pexels-photo-4050318.jpeg'
    ]
];

// Daftar gambar banner
$bannerImages = [
    'banner-year-end.jpg' => [
        'https://images.unsplash.com/photo-1618556450994-a6a128ef0d9d',
        'https://images.pexels.com/photos/5980800/pexels-photo-5980800.jpeg'
    ],
    'banner-blackout.jpg' => [
        'https://images.unsplash.com/photo-1548693590-e01ce13c592c',
        'https://images.pexels.com/photos/1435752/pexels-photo-1435752.jpeg'
    ],
    'banner-minimalis.jpg' => [
        'https://images.unsplash.com/photo-1586023492125-27b2c045efd7',
        'https://images.pexels.com/photos/276724/pexels-photo-276724.jpeg'
    ]
];

// Parameter untuk mendapatkan ukuran spesifik dari Unsplash
$productSize = '?ixlib=rb-4.0.3&q=85&fm=jpg&crop=entropy&cs=srgb&w=800&h=800&fit=crop';
$bannerSize = '?ixlib=rb-4.0.3&q=85&fm=jpg&crop=entropy&cs=srgb&w=1200&h=400&fit=crop';

// Download gambar produk
foreach ($productImages as $image => $urls) {
    $destination = "assets/images/products/$image";
    $success = false;

    // Coba URL pertama (Unsplash)
    $success = downloadImage($urls[0] . $productSize, $destination);

    // Jika gagal, coba URL alternatif (Pexels)
    if (!$success && isset($urls[1])) {
        $success = downloadImage($urls[1], $destination);
    }

    // Jika semua gagal, gunakan placeholder
    if (!$success) {
        $placeholderUrl = "https://via.placeholder.com/800x800.jpg/CCCCCC/333333?text=" . urlencode(pathinfo($image, PATHINFO_FILENAME));
        downloadImage($placeholderUrl, $destination);
    }

    // Delay untuk menghindari rate limiting
    sleep(1);
}

// Download gambar banner
foreach ($bannerImages as $image => $urls) {
    $destination = "assets/images/banners/$image";
    $success = false;

    // Coba URL pertama (Unsplash)
    $success = downloadImage($urls[0] . $bannerSize, $destination);

    // Jika gagal, coba URL alternatif (Pexels)
    if (!$success && isset($urls[1])) {
        $success = downloadImage($urls[1], $destination);
    }

    // Jika semua gagal, gunakan placeholder
    if (!$success) {
        $placeholderUrl = "https://via.placeholder.com/1200x400.jpg/3D5A80/FFFFFF?text=" . urlencode(pathinfo($image, PATHINFO_FILENAME));
        downloadImage($placeholderUrl, $destination);
    }

    // Delay untuk menghindari rate limiting
    sleep(1);
}

echo "<h2>Semua gambar berhasil di-download!</h2>";
echo "<p>Jika beberapa gambar tidak sesuai, silahkan jalankan script lagi atau download manual dari sumber berikut:</p>";
echo "<ul>";
echo "<li><a href='https://unsplash.com/s/photos/curtain' target='_blank'>Unsplash - Curtain</a></li>";
echo "<li><a href='https://www.pexels.com/search/curtain/' target='_blank'>Pexels - Curtain</a></li>";
echo "</ul>";
