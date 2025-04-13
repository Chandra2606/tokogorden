<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../lib/models/Category.php';
require_once __DIR__ . '/../lib/models/Wishlist.php';

$categoryModel = new Category($conn);
$categories = $categoryModel->getAll();

$wishlistCount = 0;
if (isLoggedIn()) {
    $wishlistModel = new Wishlist($conn);
    $wishlistCount = $wishlistModel->getCountByUserId($_SESSION['user_id']);
}

$active_page = isset($active_page) ? $active_page : '';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko Gorden - <?php echo isset($page_title) ? $page_title : 'Belanja Gorden Berkualitas'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.10.5/dist/cdn.min.js" defer></script>
    <style>
        .banner-slider {
            transition: transform 0.5s ease-in-out;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen flex flex-col">
    <header class="bg-blue-800 text-white shadow-md">
        <div class="container mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <a href="<?php echo BASE_URL; ?>" class="text-2xl font-bold">Toko Gorden</a>
                    <div class="hidden md:flex space-x-4">
                        <a href="<?php echo BASE_URL; ?>" class="hover:text-blue-200 <?php echo $active_page == 'home' ? 'text-blue-200 font-semibold' : ''; ?>">Beranda</a>
                        <div class="relative group" x-data="{ open: false }">
                            <button @click="open = !open" class="hover:text-blue-200 <?php echo $active_page == 'category' ? 'text-blue-200 font-semibold' : ''; ?>">
                                Kategori <i class="fas fa-chevron-down text-xs ml-1"></i>
                            </button>
                            <div x-show="open" @click.away="open = false" class="absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg z-20 text-gray-800">
                                <?php foreach ($categories as $category): ?>
                                    <a href="<?php echo BASE_URL . 'category.php?slug=' . $category['slug']; ?>" class="block px-4 py-2 hover:bg-blue-100">
                                        <?php echo $category['name']; ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <a href="<?php echo BASE_URL . 'products.php'; ?>" class="hover:text-blue-200 <?php echo $active_page == 'products' ? 'text-blue-200 font-semibold' : ''; ?>">Produk</a>

                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <form action="<?php echo BASE_URL . 'search.php'; ?>" method="GET" class="flex">
                            <input type="text" name="q" placeholder="Cari produk..." class="py-1 px-3 rounded-l text-black text-sm focus:outline-none">
                            <button type="submit" class="bg-blue-600 py-1 px-3 rounded-r hover:bg-blue-700 focus:outline-none">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                    <?php if (isLoggedIn()): ?>
                        <a href="<?php echo BASE_URL . 'customer/wishlist.php'; ?>" class="hover:text-blue-200 relative">
                            <i class="fas fa-heart"></i>
                            <?php if ($wishlistCount > 0): ?>
                                <span class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full text-xs w-5 h-5 flex items-center justify-center">
                                    <?php echo $wishlistCount; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                        <a href="<?php echo BASE_URL . 'cart.php'; ?>" class="hover:text-blue-200">
                            <i class="fas fa-shopping-cart"></i>
                        </a>
                        <div class="relative group" x-data="{ open: false }">
                            <button @click="open = !open" class="hover:text-blue-200">
                                <i class="fas fa-user-circle text-xl"></i>
                            </button>
                            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-20 text-gray-800">
                                <div class="py-2 px-4 border-b border-gray-200">
                                    <p class="font-semibold"><?php echo $_SESSION['user_name']; ?></p>
                                    <p class="text-sm text-gray-600"><?php echo $_SESSION['user_email']; ?></p>
                                </div>
                                <?php if (isAdmin()): ?>
                                    <a href="<?php echo BASE_URL . 'admin/index.php'; ?>" class="block px-4 py-2 hover:bg-blue-100">
                                        <i class="fas fa-tachometer-alt mr-2"></i> Dashboard Admin
                                    </a>
                                <?php else: ?>
                                    <a href="<?php echo BASE_URL . 'customer/dashboard.php'; ?>" class="block px-4 py-2 hover:bg-blue-100">
                                        <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                                    </a>
                                <?php endif; ?>
                                <a href="<?php echo BASE_URL . 'customer/orders.php'; ?>" class="block px-4 py-2 hover:bg-blue-100">
                                    <i class="fas fa-shopping-bag mr-2"></i> Pesanan Saya
                                </a>
                                <a href="<?php echo BASE_URL . 'customer/profile.php'; ?>" class="block px-4 py-2 hover:bg-blue-100">
                                    <i class="fas fa-user-edit mr-2"></i> Edit Profil
                                </a>
                                <a href="<?php echo BASE_URL . 'logout.php'; ?>" class="block px-4 py-2 hover:bg-blue-100 text-red-600">
                                    <i class="fas fa-sign-out-alt mr-2"></i> Keluar
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL . 'login.php'; ?>" class="hover:text-blue-200">
                            <i class="fas fa-sign-in-alt mr-1"></i> Masuk
                        </a>
                        <a href="<?php echo BASE_URL . 'register.php'; ?>" class="hover:text-blue-200">
                            <i class="fas fa-user-plus mr-1"></i> Daftar
                        </a>
                    <?php endif; ?>
                    <button class="md:hidden focus:outline-none" id="menu-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
            <div class="md:hidden hidden mt-3 pb-2" id="mobile-menu">
                <a href="<?php echo BASE_URL; ?>" class="block py-2 hover:text-blue-200 <?php echo $active_page == 'home' ? 'text-blue-200 font-semibold' : ''; ?>">Beranda</a>
                <div class="py-2" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full text-left hover:text-blue-200 <?php echo $active_page == 'category' ? 'text-blue-200 font-semibold' : ''; ?>">
                        Kategori <i class="fas fa-chevron-down text-xs ml-1"></i>
                    </button>
                    <div x-show="open" class="pl-4 mt-1 space-y-2">
                        <?php foreach ($categories as $category): ?>
                            <a href="<?php echo BASE_URL . 'category.php?slug=' . $category['slug']; ?>" class="block py-1 hover:text-blue-200">
                                <?php echo $category['name']; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <a href="<?php echo BASE_URL . 'products.php'; ?>" class="block py-2 hover:text-blue-200 <?php echo $active_page == 'products' ? 'text-blue-200 font-semibold' : ''; ?>">Produk</a>

            </div>
        </div>
    </header>
    <main class="flex-grow"><?php if (isset($show_breadcrumb) && $show_breadcrumb): ?>
            <div class="bg-gray-200 py-2">
                <div class="container mx-auto px-4">
                    <div class="flex items-center text-sm text-gray-600">
                        <a href="<?php echo BASE_URL; ?>" class="hover:text-blue-600">Beranda</a>
                        <?php if (isset($breadcrumb_items) && is_array($breadcrumb_items)): ?>
                            <?php foreach ($breadcrumb_items as $item): ?>
                                <span class="mx-2">/</span>
                                <?php if (isset($item['url'])): ?>
                                    <a href="<?php echo $item['url']; ?>" class="hover:text-blue-600"><?php echo $item['label']; ?></a>
                                <?php else: ?>
                                    <span class="text-gray-800"><?php echo $item['label']; ?></span>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <script>
            document.getElementById('menu-toggle').addEventListener('click', function() {
                document.getElementById('mobile-menu').classList.toggle('hidden');
            });
        </script>
</body>

</html>