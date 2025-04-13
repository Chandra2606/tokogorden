<?php
require_once __DIR__ . '/../../config/config.php';

// Cek jika pengguna belum login atau bukan admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$active_page = isset($active_page) ? $active_page : '';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Toko Gorden - <?php echo isset($page_title) ? $page_title : 'Dashboard'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.10.5/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Custom styling for form inputs */
        input[type="text"],
        input[type="number"],
        input[type="email"],
        input[type="password"],
        input[type="date"],
        input[type="datetime-local"],
        select,
        textarea {
            background-color: #fff;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            padding: 0.5rem 0.75rem;
            width: 100%;
            font-size: 0.875rem;
            line-height: 1.25rem;
            color: #1f2937;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        input[type="date"]:focus,
        input[type="datetime-local"]:focus,
        select:focus,
        textarea:focus {
            border-color: #3b82f6;
            outline: 0;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
        }

        /* Styling for file inputs */
        input[type="file"] {
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            padding: 0.5rem;
            width: 100%;
            font-size: 0.875rem;
            line-height: 1.25rem;
            color: #1f2937;
            cursor: pointer;
        }

        input[type="file"]::-webkit-file-upload-button {
            background-color: #e5e7eb;
            border: 0;
            border-radius: 0.25rem;
            padding: 0.375rem 0.75rem;
            margin-right: 0.5rem;
            color: #4b5563;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.15s ease-in-out;
        }

        input[type="file"]::-webkit-file-upload-button:hover {
            background-color: #d1d5db;
        }

        /* Styling for checkboxes and radio buttons */
        input[type="checkbox"],
        input[type="radio"] {
            width: 1rem;
            height: 1rem;
            color: #3b82f6;
            background-color: #fff;
            border: 1px solid #d1d5db;
            border-radius: 0.25rem;
            margin-right: 0.5rem;
            cursor: pointer;
        }

        input[type="checkbox"]:checked,
        input[type="radio"]:checked {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }

        input[type="checkbox"]:focus,
        input[type="radio"]:focus {
            outline: 0;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
        }

        /* Form label styling */
        label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.25rem;
            display: block;
        }

        /* Error message styling */
        .text-red-600 {
            color: #dc2626;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        /* Disabled input styling */
        input:disabled,
        select:disabled,
        textarea:disabled {
            background-color: #f3f4f6;
            cursor: not-allowed;
            opacity: 0.7;
        }

        /* Helper text styling */
        .form-helper-text {
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }

        /* Button styling */
        button,
        .btn {
            padding: 0.5rem 1rem;
            font-weight: 500;
            border-radius: 0.375rem;
            transition: background-color 0.15s ease-in-out;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen">
    <div class="flex h-screen bg-gray-100">
        <!-- Sidebar -->
        <div class="hidden md:flex md:flex-shrink-0">
            <div class="flex flex-col w-64 bg-gray-800">
                <div class="flex items-center h-16 px-4 bg-gray-900 text-white font-semibold text-lg">
                    <a href="<?php echo BASE_URL . 'admin/index.php'; ?>">Admin Toko Gorden</a>
                </div>
                <div class="flex flex-col flex-1 overflow-y-auto">
                    <nav class="flex-1 px-2 py-4 space-y-1">
                        <a href="<?php echo BASE_URL . 'admin/index.php'; ?>" class="flex items-center px-4 py-2 text-sm font-medium rounded-md <?php echo $active_page == 'dashboard' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?>">
                            <i class="fas fa-tachometer-alt mr-3"></i>
                            Dashboard
                        </a>
                        <a href="<?php echo BASE_URL . 'admin/products/index.php'; ?>" class="flex items-center px-4 py-2 text-sm font-medium rounded-md <?php echo $active_page == 'products' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?>">
                            <i class="fas fa-box mr-3"></i>
                            Produk
                        </a>
                        <a href="<?php echo BASE_URL . 'admin/categories/index.php'; ?>" class="flex items-center px-4 py-2 text-sm font-medium rounded-md <?php echo $active_page == 'categories' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?>">
                            <i class="fas fa-list mr-3"></i>
                            Kategori
                        </a>
                        <a href="<?php echo BASE_URL . 'admin/orders/index.php'; ?>" class="flex items-center px-4 py-2 text-sm font-medium rounded-md <?php echo $active_page == 'orders' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?>">
                            <i class="fas fa-shopping-cart mr-3"></i>
                            Pesanan
                        </a>
                        <a href="<?php echo BASE_URL . 'admin/users/index.php'; ?>" class="flex items-center px-4 py-2 text-sm font-medium rounded-md <?php echo $active_page == 'users' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?>">
                            <i class="fas fa-users mr-3"></i>
                            Pengguna
                        </a>
                        <a href="<?php echo BASE_URL . 'admin/discounts/index.php'; ?>" class="flex items-center px-4 py-2 text-sm font-medium rounded-md <?php echo $active_page == 'discounts' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?>">
                            <i class="fas fa-percent mr-3"></i>
                            Diskon
                        </a>
                        <a href="<?php echo BASE_URL . 'admin/banners/index.php'; ?>" class="flex items-center px-4 py-2 text-sm font-medium rounded-md <?php echo $active_page == 'banners' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?>">
                            <i class="fas fa-image mr-3"></i>
                            Banner
                        </a>
                    </nav>
                </div>
                <div class="p-4 bg-gray-700">
                    <a href="<?php echo BASE_URL . 'logout.php'; ?>" class="flex items-center text-gray-300 hover:text-white">
                        <i class="fas fa-sign-out-alt mr-3"></i>
                        Keluar
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex flex-col flex-1 overflow-hidden">
            <!-- Top Navbar -->
            <header class="flex items-center justify-between p-4 bg-white border-b border-gray-200">
                <div class="flex items-center md:hidden">
                    <button id="sidebar-toggle" class="text-gray-500 focus:outline-none">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
                <div class="flex items-center">
                    <div>
                        <span class="text-gray-800 mr-2"><?php echo $_SESSION['user_name']; ?></span>
                        <span class="text-gray-500">(Admin)</span>
                    </div>
                    <div class="ml-4 relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center text-gray-600 focus:outline-none">
                            <div class="w-8 h-8 rounded-full bg-gray-400 flex items-center justify-center text-white">
                                <i class="fas fa-user"></i>
                            </div>
                            <i class="fas fa-chevron-down ml-1 text-xs"></i>
                        </button>
                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10">
                            <a href="<?php echo BASE_URL; ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-home mr-2"></i> Lihat Toko
                            </a>
                            <a href="<?php echo BASE_URL . 'admin/users/profile.php'; ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-user-edit mr-2"></i> Edit Profil
                            </a>
                            <a href="<?php echo BASE_URL . 'logout.php'; ?>" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                <i class="fas fa-sign-out-alt mr-2"></i> Keluar
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Mobile Sidebar -->
            <div id="mobile-sidebar" class="fixed inset-0 z-40 md:hidden hidden">
                <div class="fixed inset-0 bg-gray-600 bg-opacity-75"></div>
                <div class="fixed inset-y-0 left-0 flex flex-col w-64 bg-gray-800">
                    <div class="flex items-center h-16 px-4 bg-gray-900 text-white font-semibold text-lg">
                        <a href="<?php echo BASE_URL . 'admin/index.php'; ?>">Admin Toko Gorden</a>
                        <button id="close-sidebar" class="ml-auto text-white focus:outline-none">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <nav class="flex-1 px-2 py-4 space-y-1 overflow-y-auto">
                        <a href="<?php echo BASE_URL . 'admin/index.php'; ?>" class="flex items-center px-4 py-2 text-sm font-medium rounded-md <?php echo $active_page == 'dashboard' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?>">
                            <i class="fas fa-tachometer-alt mr-3"></i>
                            Dashboard
                        </a>
                        <a href="<?php echo BASE_URL . 'admin/products/index.php'; ?>" class="flex items-center px-4 py-2 text-sm font-medium rounded-md <?php echo $active_page == 'products' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?>">
                            <i class="fas fa-box mr-3"></i>
                            Produk
                        </a>
                        <a href="<?php echo BASE_URL . 'admin/categories/index.php'; ?>" class="flex items-center px-4 py-2 text-sm font-medium rounded-md <?php echo $active_page == 'categories' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?>">
                            <i class="fas fa-list mr-3"></i>
                            Kategori
                        </a>
                        <a href="<?php echo BASE_URL . 'admin/orders/index.php'; ?>" class="flex items-center px-4 py-2 text-sm font-medium rounded-md <?php echo $active_page == 'orders' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?>">
                            <i class="fas fa-shopping-cart mr-3"></i>
                            Pesanan
                        </a>
                        <a href="<?php echo BASE_URL . 'admin/users/index.php'; ?>" class="flex items-center px-4 py-2 text-sm font-medium rounded-md <?php echo $active_page == 'users' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?>">
                            <i class="fas fa-users mr-3"></i>
                            Pengguna
                        </a>
                        <a href="<?php echo BASE_URL . 'admin/discounts/index.php'; ?>" class="flex items-center px-4 py-2 text-sm font-medium rounded-md <?php echo $active_page == 'discounts' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?>">
                            <i class="fas fa-percent mr-3"></i>
                            Diskon
                        </a>
                        <a href="<?php echo BASE_URL . 'admin/banners/index.php'; ?>" class="flex items-center px-4 py-2 text-sm font-medium rounded-md <?php echo $active_page == 'banners' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?>">
                            <i class="fas fa-image mr-3"></i>
                            Banner
                        </a>
                    </nav>
                    <div class="p-4 bg-gray-700">
                        <a href="<?php echo BASE_URL . 'logout.php'; ?>" class="flex items-center text-gray-300 hover:text-white">
                            <i class="fas fa-sign-out-alt mr-3"></i>
                            Keluar
                        </a>
                    </div>
                </div>
            </div>

            <!-- Page Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                <?php
                $flashMessage = getFlashMessage();
                if ($flashMessage):
                ?>
                    <div class="bg-<?php echo $flashMessage['type'] === 'success' ? 'green' : 'red'; ?>-100 border border-<?php echo $flashMessage['type'] === 'success' ? 'green' : 'red'; ?>-400 text-<?php echo $flashMessage['type'] === 'success' ? 'green' : 'red'; ?>-700 px-4 py-3 rounded mb-4">
                        <div class="flex items-center">
                            <i class="fas fa-<?php echo $flashMessage['type'] === 'success' ? 'check' : 'times'; ?>-circle mr-2"></i>
                            <span><?php echo $flashMessage['message']; ?></span>
                        </div>
                    </div>
                <?php endif; ?>