<?php
$page_title = 'Daftar Keinginan';
require_once '../config/config.php';

if (!isLoggedIn() || isAdmin()) {
    redirect('login.php');
}

$userId = $_SESSION['user_id'];
$userModel = new User($conn);
$user = $userModel->getById($userId);

$wishlistModel = new Wishlist($conn);

if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $productId = intval($_GET['remove']);
    if ($wishlistModel->remove($userId, $productId)) {
        setFlashMessage('success', 'Produk berhasil dihapus dari daftar keinginan.');
    } else {
        setFlashMessage('error', 'Gagal menghapus produk dari daftar keinginan.');
    }
    redirect('customer/wishlist.php');
}

$wishlistItems = $wishlistModel->getByUserId($userId);

include '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row gap-8">
        <!-- Sidebar Menu -->
        <div class="w-full md:w-1/4">
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <div class="p-6 bg-blue-600 text-white">
                    <div class="flex items-center space-x-4">
                        <div class="rounded-full bg-white/30 p-3">
                            <i class="fas fa-user text-2xl"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-semibold"><?php echo htmlspecialchars($user['name']); ?></h2>
                            <p class="text-sm opacity-80"><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                    </div>
                </div>
                <nav class="p-4">
                    <ul class="space-y-2">
                        <li>
                            <a href="dashboard.php" class="flex items-center p-3 rounded-lg hover:bg-gray-100 transition-colors">
                                <i class="fas fa-tachometer-alt w-6"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="orders.php" class="flex items-center p-3 rounded-lg hover:bg-gray-100 transition-colors">
                                <i class="fas fa-shopping-bag w-6"></i>
                                <span>Pesanan Saya</span>
                            </a>
                        </li>
                        <li>
                            <a href="addresses.php" class="flex items-center p-3 rounded-lg hover:bg-gray-100 transition-colors">
                                <i class="fas fa-map-marker-alt w-6"></i>
                                <span>Alamat Pengiriman</span>
                            </a>
                        </li>
                        <li>
                            <a href="profile.php" class="flex items-center p-3 rounded-lg hover:bg-gray-100 transition-colors">
                                <i class="fas fa-user-edit w-6"></i>
                                <span>Edit Profil</span>
                            </a>
                        </li>
                        <li>
                            <a href="change-password.php" class="flex items-center p-3 rounded-lg hover:bg-gray-100 transition-colors">
                                <i class="fas fa-lock w-6"></i>
                                <span>Ubah Password</span>
                            </a>
                        </li>
                        <li>
                            <a href="wishlist.php" class="flex items-center p-3 rounded-lg bg-blue-50 text-blue-700">
                                <i class="fas fa-heart w-6"></i>
                                <span>Daftar Keinginan</span>
                            </a>
                        </li>
                        <li>
                            <a href="../logout.php" class="flex items-center p-3 rounded-lg hover:bg-red-50 text-red-600 transition-colors">
                                <i class="fas fa-sign-out-alt w-6"></i>
                                <span>Keluar</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="w-full md:w-3/4">
            <!-- Flashmessage -->
            <?php $flashMessage = getFlashMessage(); ?>
            <?php if ($flashMessage): ?>
                <div class="bg-<?php echo $flashMessage['type'] === 'success' ? 'green' : 'red'; ?>-100 border-l-4 border-<?php echo $flashMessage['type'] === 'success' ? 'green' : 'red'; ?>-500 text-<?php echo $flashMessage['type'] === 'success' ? 'green' : 'red'; ?>-700 p-4 mb-6" role="alert">
                    <p class="font-medium"><?php echo $flashMessage['type'] === 'success' ? 'Sukses!' : 'Error!'; ?></p>
                    <p><?php echo $flashMessage['message']; ?></p>
                </div>
            <?php endif; ?>

            <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
                <div class="p-6 border-b">
                    <h1 class="text-2xl font-semibold">Daftar Keinginan</h1>
                </div>

                <?php if (count($wishlistItems) > 0): ?>
                    <div class="p-0">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($wishlistItems as $item): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-16 w-16">
                                                        <img class="h-16 w-16 rounded-md object-cover" src="<?php echo !empty($item['image']) ? BASE_URL . 'assets/images/products/' . $item['image'] : BASE_URL . 'assets/images/products/default-product.jpg'; ?>" alt="<?php echo $item['product_name']; ?>">
                                                    </div>
                                                    <div class="ml-4">
                                                        <a href="../product.php?slug=<?php echo $item['slug']; ?>" class="text-sm font-medium text-gray-900 hover:text-blue-600">
                                                            <?php echo htmlspecialchars($item['product_name']); ?>
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo formatRupiah($item['price']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php if ($item['stock'] > 0): ?>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        Tersedia
                                                    </span>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        Stok Habis
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex justify-end space-x-2">
                                                    <?php if ($item['stock'] > 0): ?>
                                                        <a href="../cart.php?add=<?php echo $item['product_id']; ?>" class="text-blue-600 hover:text-blue-900">
                                                            <i class="fas fa-shopping-cart"></i> Tambah ke Keranjang
                                                        </a>
                                                    <?php endif; ?>
                                                    <a href="?remove=<?php echo $item['product_id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Anda yakin ingin menghapus produk ini dari daftar keinginan?')">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="p-6 text-center">
                        <div class="py-8">
                            <div class="text-gray-400 mb-4">
                                <i class="fas fa-heart text-5xl"></i>
                            </div>
                            <p class="text-gray-500">Daftar keinginan Anda masih kosong.</p>
                            <a href="../products.php" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none">
                                <i class="fas fa-shopping-cart mr-2"></i> Mulai Belanja
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>