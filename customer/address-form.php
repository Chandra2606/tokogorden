<?php
$page_title = 'Form Alamat Pengiriman';
require_once '../config/config.php';

if (!isLoggedIn() || isAdmin()) {
    redirect('login.php');
}

$userId = $_SESSION['user_id'];
$userModel = new User($conn);
$user = $userModel->getById($userId);

$addressModel = new Address($conn);

$isEdit = false;
$addressId = 0;
$address = [
    'recipient_name' => $user['name'],
    'phone' => $user['phone'] ?? '',
    'province' => '',
    'city' => '',
    'district' => '',
    'postal_code' => '',
    'full_address' => '',
    'is_default' => 0
];

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $addressId = intval($_GET['id']);
    $addressData = $addressModel->getById($addressId);

    if ($addressData && $addressData['user_id'] == $userId) {
        $isEdit = true;
        $address = $addressData;
    } else {
        setFlashMessage('error', 'Alamat tidak ditemukan.');
        redirect('customer/addresses.php');
    }
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['recipient_name'])) {
        $errors['recipient_name'] = 'Nama penerima tidak boleh kosong';
    } else {
        $address['recipient_name'] = sanitize($_POST['recipient_name']);
    }

    if (empty($_POST['phone'])) {
        $errors['phone'] = 'Nomor telepon tidak boleh kosong';
    } elseif (!preg_match('/^[0-9+\-\s]{8,15}$/', $_POST['phone'])) {
        $errors['phone'] = 'Format nomor telepon tidak valid';
    } else {
        $address['phone'] = sanitize($_POST['phone']);
    }

    if (empty($_POST['province'])) {
        $errors['province'] = 'Provinsi tidak boleh kosong';
    } else {
        $address['province'] = sanitize($_POST['province']);
    }

    if (empty($_POST['city'])) {
        $errors['city'] = 'Kota tidak boleh kosong';
    } else {
        $address['city'] = sanitize($_POST['city']);
    }

    if (empty($_POST['district'])) {
        $errors['district'] = 'Kecamatan tidak boleh kosong';
    } else {
        $address['district'] = sanitize($_POST['district']);
    }

    if (empty($_POST['postal_code'])) {
        $errors['postal_code'] = 'Kode pos tidak boleh kosong';
    } elseif (!preg_match('/^[0-9]{5}$/', $_POST['postal_code'])) {
        $errors['postal_code'] = 'Format kode pos tidak valid';
    } else {
        $address['postal_code'] = sanitize($_POST['postal_code']);
    }

    if (empty($_POST['full_address'])) {
        $errors['full_address'] = 'Alamat lengkap tidak boleh kosong';
    } else {
        $address['full_address'] = sanitize($_POST['full_address']);
    }

    $address['is_default'] = isset($_POST['is_default']) ? 1 : 0;

    if (empty($errors)) {
        $address['user_id'] = $userId;

        if ($isEdit) {
            $address['id'] = $addressId;
            if ($addressModel->update($address)) {
                setFlashMessage('success', 'Alamat berhasil diperbarui.');
                redirect('customer/addresses.php');
            } else {
                $errors['general'] = 'Gagal memperbarui alamat. Silakan coba lagi.';
            }
        } else {
            if ($addressModel->create($address)) {
                setFlashMessage('success', 'Alamat berhasil ditambahkan.');
                redirect('customer/addresses.php');
            } else {
                $errors['general'] = 'Gagal menambahkan alamat. Silakan coba lagi.';
            }
        }
    }
}

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
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <div class="p-6 border-b flex justify-between items-center">
                    <h1 class="text-2xl font-semibold"><?php echo $isEdit ? 'Edit Alamat' : 'Tambah Alamat Baru'; ?></h1>
                    <a href="addresses.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                        <i class="fas fa-arrow-left mr-2"></i> Kembali
                    </a>
                </div>

                <?php if (isset($errors['general'])): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 m-6" role="alert">
                        <p class="font-medium">Error!</p>
                        <p><?php echo $errors['general']; ?></p>
                    </div>
                <?php endif; ?>

                <div class="p-6">
                    <form action="" method="POST" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="recipient_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Penerima <span class="text-red-600">*</span></label>
                                <input type="text" id="recipient_name" name="recipient_name" value="<?php echo htmlspecialchars($address['recipient_name']); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <?php if (isset($errors['recipient_name'])): ?>
                                    <p class="mt-1 text-sm text-red-600"><?php echo $errors['recipient_name']; ?></p>
                                <?php endif; ?>
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon <span class="text-red-600">*</span></label>
                                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($address['phone']); ?>" required pattern="[0-9+\-\s]{8,15}" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Format: 08123456789">
                                <?php if (isset($errors['phone'])): ?>
                                    <p class="mt-1 text-sm text-red-600"><?php echo $errors['phone']; ?></p>
                                <?php endif; ?>
                            </div>

                            <div>
                                <label for="province" class="block text-sm font-medium text-gray-700 mb-1">Provinsi <span class="text-red-600">*</span></label>
                                <input type="text" id="province" name="province" value="<?php echo htmlspecialchars($address['province']); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <?php if (isset($errors['province'])): ?>
                                    <p class="mt-1 text-sm text-red-600"><?php echo $errors['province']; ?></p>
                                <?php endif; ?>
                            </div>

                            <div>
                                <label for="city" class="block text-sm font-medium text-gray-700 mb-1">Kota <span class="text-red-600">*</span></label>
                                <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($address['city']); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <?php if (isset($errors['city'])): ?>
                                    <p class="mt-1 text-sm text-red-600"><?php echo $errors['city']; ?></p>
                                <?php endif; ?>
                            </div>

                            <div>
                                <label for="district" class="block text-sm font-medium text-gray-700 mb-1">Kecamatan <span class="text-red-600">*</span></label>
                                <input type="text" id="district" name="district" value="<?php echo htmlspecialchars($address['district']); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <?php if (isset($errors['district'])): ?>
                                    <p class="mt-1 text-sm text-red-600"><?php echo $errors['district']; ?></p>
                                <?php endif; ?>
                            </div>

                            <div>
                                <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-1">Kode Pos <span class="text-red-600">*</span></label>
                                <input type="text" id="postal_code" name="postal_code" value="<?php echo htmlspecialchars($address['postal_code']); ?>" required pattern="[0-9]{5}" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="5 digit angka">
                                <?php if (isset($errors['postal_code'])): ?>
                                    <p class="mt-1 text-sm text-red-600"><?php echo $errors['postal_code']; ?></p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div>
                            <label for="full_address" class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap <span class="text-red-600">*</span></label>
                            <textarea id="full_address" name="full_address" rows="3" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Nama jalan, nomor rumah, rt/rw, dll"><?php echo htmlspecialchars($address['full_address']); ?></textarea>
                            <?php if (isset($errors['full_address'])): ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo $errors['full_address']; ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" id="is_default" name="is_default" <?php echo $address['is_default'] ? 'checked' : ''; ?> class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <label for="is_default" class="ml-2 block text-sm text-gray-700">Jadikan sebagai alamat utama</label>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-save mr-2"></i> <?php echo $isEdit ? 'Simpan Perubahan' : 'Simpan Alamat'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>