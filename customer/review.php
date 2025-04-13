<?php
$page_title = 'Beri Ulasan Produk';
require_once '../config/config.php';

if (!isLoggedIn() || isAdmin()) {
    redirect('../login.php');
}

$orderId = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$productId = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

$errors = [];

if (!$orderId && !$productId) {
} elseif (!$orderId) {
    $errors[] = 'ID pesanan tidak valid';
} elseif (!$productId) {
    $errors[] = 'ID produk tidak valid';
}

$userId = $_SESSION['user_id'];
$userModel = new User($conn);
$user = $userModel->getById($userId);

$reviewModel = new Review($conn);
$orderModel = new Order($conn);
$productModel = new Product($conn);

if ($orderId) {
    $order = $orderModel->getById($orderId);

    if (!$order || $order['user_id'] != $userId) {
        $errors[] = 'Pesanan tidak ditemukan atau Anda tidak memiliki akses';
    }

    if (!$errors && $order['status'] !== 'completed') {
        $errors[] = 'Anda hanya dapat memberikan ulasan untuk pesanan yang telah selesai';
    }
}

if ($productId && !$errors) {
    $product = $productModel->getById($productId);

    if (!$product) {
        $errors[] = 'Produk tidak ditemukan';
    } else {
        if ($reviewModel->hasUserReviewed($userId, $productId)) {
            $errors[] = 'Anda sudah memberikan ulasan untuk produk ini';
        }

        if (!$reviewModel->canUserReviewProduct($userId, $productId)) {
            $errors[] = 'Anda hanya dapat memberikan ulasan untuk produk yang telah Anda beli dan terima';
        }
    }
}

if ($errors && ($orderId || $productId)) {
    foreach ($errors as $error) {
        setFlashMessage('error', $error);
    }

    if ($orderId) {
        redirect('customer/order-detail.php?id=' . $orderId);
    } else {
        redirect('customer/orders.php');
    }
}

// Proses form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    $review = isset($_POST['review']) ? sanitize($_POST['review']) : '';
    $productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $orderId = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;

    // Validasi
    if ($rating < 1 || $rating > 5) {
        setFlashMessage('error', 'Rating harus antara 1-5');
    } elseif (empty($review)) {
        setFlashMessage('error', 'Ulasan tidak boleh kosong');
    } elseif (!$productId) {
        setFlashMessage('error', 'ID produk tidak valid');
    } else {
        // Tambah ulasan
        $result = $reviewModel->create([
            'user_id' => $userId,
            'product_id' => $productId,
            'order_id' => $orderId,
            'rating' => $rating,
            'review' => $review
        ]);

        if ($result['success']) {
            setFlashMessage('success', 'Terima kasih! Ulasan Anda telah berhasil ditambahkan');
            redirect('customer/orders.php');
        } else {
            setFlashMessage('error', $result['message']);
        }
    }
}

$reviewableProducts = $reviewModel->getReviewableProducts($userId, $orderId);

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
                            <a href="orders.php" class="flex items-center p-3 rounded-lg bg-blue-50 text-blue-700">
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
                            <a href="wishlist.php" class="flex items-center p-3 rounded-lg hover:bg-gray-100 transition-colors">
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
            <!-- Review Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-semibold">Beri Ulasan Produk</h1>
                    <p class="text-gray-500">Bagikan pengalaman Anda dengan produk yang telah Anda beli</p>
                </div>
                <a href="orders.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                </a>
            </div>

            <?php
            // Tampilkan pesan flash
            $flashMessage = getFlashMessage();
            if ($flashMessage) {
                $alertClass = $flashMessage['type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                echo '<div class="mb-6 p-4 rounded-lg ' . $alertClass . '">' . $flashMessage['message'] . '</div>';
            }
            ?>

            <?php if (empty($reviewableProducts)): ?>
                <div class="bg-yellow-50 p-6 rounded-lg shadow-md mb-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-yellow-500 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-yellow-800">Tidak ada produk untuk diulas</h3>
                            <p class="text-yellow-700 mt-1">Anda hanya dapat memberikan ulasan untuk produk yang telah Anda beli dan pesanannya telah selesai.</p>
                            <div class="mt-3">
                                <a href="orders.php" class="text-sm font-medium text-yellow-800 hover:text-yellow-900">
                                    Lihat Pesanan Saya <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php elseif (isset($product) && !$errors): ?>
                <!-- Form Review untuk Produk Tertentu -->
                <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
                    <div class="p-6 border-b">
                        <h2 class="text-xl font-semibold">Beri Ulasan untuk <?php echo htmlspecialchars($product['name']); ?></h2>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center mb-6">
                            <div class="flex-shrink-0 h-24 w-24">
                                <img class="h-24 w-24 rounded-md object-cover" src="<?php echo !empty($product['image']) ? BASE_URL . 'assets/images/products/' . $product['image'] : BASE_URL . 'assets/images/products/default-product.jpg'; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p class="text-gray-500">Kategori: <?php echo htmlspecialchars($product['category_name']); ?></p>
                                <p class="text-gray-500">Harga: <?php echo formatRupiah($product['price']); ?></p>
                            </div>
                        </div>

                        <form action="review.php" method="POST" class="space-y-6">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <input type="hidden" name="order_id" value="<?php echo $orderId; ?>">

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
                                <div class="flex space-x-2">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <label class="cursor-pointer">
                                            <input type="radio" name="rating" value="<?php echo $i; ?>" class="hidden" required>
                                            <i class="fas fa-star text-2xl text-gray-300 hover:text-yellow-400 peer-checked:text-yellow-400 star-rating" data-rating="<?php echo $i; ?>"></i>
                                        </label>
                                    <?php endfor; ?>
                                </div>
                                <p class="text-sm text-gray-500 mt-1">Klik bintang untuk memberi rating</p>
                            </div>

                            <div>
                                <label for="review" class="block text-sm font-medium text-gray-700 mb-2">Ulasan Anda</label>
                                <textarea id="review" name="review" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" placeholder="Bagikan pengalaman Anda dengan produk ini..." required></textarea>
                            </div>

                            <div class="flex justify-end">
                                <a href="<?php echo $orderId ? 'order-detail.php?id=' . $orderId : 'orders.php'; ?>" class="mr-4 inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                    Batal
                                </a>
                                <button type="submit" name="submit_review" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                                    <i class="fas fa-paper-plane mr-2"></i> Kirim Ulasan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <!-- Daftar Produk yang Bisa Diulas -->
                <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
                    <div class="p-6 border-b">
                        <h2 class="text-xl font-semibold">Produk yang Bisa Anda Ulas</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <?php foreach ($reviewableProducts as $product): ?>
                                <div class="border rounded-lg overflow-hidden shadow-sm flex">
                                    <div class="w-1/3">
                                        <img class="h-full w-full object-cover" src="<?php echo !empty($product['image']) ? BASE_URL . 'assets/images/products/' . $product['image'] : BASE_URL . 'assets/images/products/default-product.jpg'; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    </div>
                                    <div class="w-2/3 p-4 flex flex-col justify-between">
                                        <div>
                                            <h3 class="text-lg font-medium text-gray-900 mb-1"><?php echo htmlspecialchars($product['name']); ?></h3>
                                            <p class="text-sm text-gray-500 mb-2">Pesanan #<?php echo $product['order_id']; ?></p>
                                        </div>
                                        <div>
                                            <?php if ($product['has_reviewed'] > 0): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-medium bg-green-100 text-green-800">
                                                    <i class="fas fa-check-circle mr-1.5"></i> Telah Diulas
                                                </span>
                                            <?php else: ?>
                                                <a href="review.php?order_id=<?php echo $product['order_id']; ?>&product_id=<?php echo $product['id']; ?>" class="inline-flex items-center px-3 py-2 border border-blue-600 rounded-md text-sm font-medium text-blue-600 bg-white hover:bg-blue-50">
                                                    <i class="fas fa-star mr-1.5"></i> Beri Ulasan
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Star rating functionality
        const stars = document.querySelectorAll('.star-rating');
        const ratingInputs = document.querySelectorAll('input[name="rating"]');

        stars.forEach(star => {
            star.addEventListener('click', function() {
                const rating = parseInt(this.getAttribute('data-rating'));

                // Update input value
                ratingInputs.forEach(input => {
                    if (parseInt(input.value) === rating) {
                        input.checked = true;
                    }
                });

                // Update star visuals
                stars.forEach(s => {
                    const starRating = parseInt(s.getAttribute('data-rating'));
                    if (starRating <= rating) {
                        s.classList.remove('text-gray-300');
                        s.classList.add('text-yellow-400');
                    } else {
                        s.classList.remove('text-yellow-400');
                        s.classList.add('text-gray-300');
                    }
                });
            });
        });
    });
</script>

<?php include '../includes/footer.php'; ?>