<?php
$page_title = 'Alamat Pengiriman';
require_once '../config/config.php';

if (!isLoggedIn() || isAdmin()) {
    redirect('login.php');
}

$userId = $_SESSION['user_id'];
$userModel = new User($conn);
$user = $userModel->getById($userId);

$addressModel = new Address($conn);

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $addressId = intval($_GET['delete']);
    if ($addressModel->delete($addressId, $userId)) {
        setFlashMessage('success', 'Alamat berhasil dihapus.');
    } else {
        setFlashMessage('error', 'Gagal menghapus alamat.');
    }
    redirect('customer/addresses.php');
}

if (isset($_GET['default']) && is_numeric($_GET['default'])) {
    $addressId = intval($_GET['default']);
    if ($addressModel->setAsDefault($addressId, $userId)) {
        setFlashMessage('success', 'Alamat berhasil diatur sebagai alamat utama.');
    } else {
        setFlashMessage('error', 'Gagal mengatur alamat utama.');
    }
    redirect('customer/addresses.php');
}

$addresses = $addressModel->getByUserId($userId);

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
                            <a href="addresses.php" class="flex items-center p-3 rounded-lg bg-blue-50 text-blue-700">
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
                <div class="p-6 border-b flex justify-between items-center">
                    <h1 class="text-2xl font-semibold">Alamat Pengiriman</h1>
                    <a href="address-form.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none">
                        <i class="fas fa-plus mr-2"></i> Tambah Alamat Baru
                    </a>
                </div>

                <?php if (count($addresses) > 0): ?>
                    <div class="divide-y">
                        <?php foreach ($addresses as $address): ?>
                            <div class="p-6 hover:bg-gray-50 transition-colors">
                                <div class="flex flex-col md:flex-row md:justify-between md:items-start space-y-4 md:space-y-0">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($address['recipient_name']); ?></h3>
                                            <?php if ($address['is_default']): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Utama
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-sm text-gray-600 mb-1"><?php echo htmlspecialchars($address['phone']); ?></p>
                                        <p class="text-sm text-gray-600 mb-1">
                                            <?php echo htmlspecialchars($address['full_address']); ?>,
                                            <?php echo htmlspecialchars($address['district']); ?>,
                                            <?php echo htmlspecialchars($address['city']); ?>,
                                            <?php echo htmlspecialchars($address['province']); ?>,
                                            <?php echo htmlspecialchars($address['postal_code']); ?>
                                        </p>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <?php if (!$address['is_default']): ?>
                                            <a href="?default=<?php echo $address['id']; ?>" class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                                                Jadikan Utama
                                            </a>
                                        <?php endif; ?>
                                        <a href="address-form.php?id=<?php echo $address['id']; ?>" class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if (count($addresses) > 1): // Hapus hanya jika alamat lebih dari 1 
                                        ?>
                                            <a href="?delete=<?php echo $address['id']; ?>" class="inline-flex items-center px-3 py-1.5 border border-red-300 rounded-md text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none" onclick="return confirm('Anda yakin ingin menghapus alamat ini?')">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="p-6 text-center">
                        <div class="py-8">
                            <div class="text-gray-400 mb-4">
                                <i class="fas fa-map-marker-alt text-5xl"></i>
                            </div>
                            <p class="text-gray-500">Anda belum memiliki alamat pengiriman.</p>
                            <a href="address-form.php" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none">
                                <i class="fas fa-plus mr-2"></i> Tambah Alamat Baru
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>