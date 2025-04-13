<?php
$page_title = 'Beri Ulasan Produk';
require_once 'config/config.php';

if (!isLoggedIn()) {
    setFlashMessage('error', 'Silakan login terlebih dahulu untuk memberikan ulasan');
    redirect('login.php');
}

if (isAdmin()) {
    redirect('admin/index.php');
}

$productId = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

if (!$productId) {
    setFlashMessage('error', 'ID produk tidak valid');
    redirect('index.php');
}

$productModel = new Product($conn);
$reviewModel = new Review($conn);
$orderModel = new Order($conn);

$product = $productModel->getById($productId);
if (!$product) {
    setFlashMessage('error', 'Produk tidak ditemukan');
    redirect('index.php');
}

$userId = $_SESSION['user_id'];
$userModel = new User($conn);
$user = $userModel->getById($userId);

if ($reviewModel->hasUserReviewed($userId, $productId)) {
    setFlashMessage('error', 'Anda sudah memberikan ulasan untuk produk ini');
    redirect('product.php?slug=' . $product['slug']);
}


$bypass_check = (isset($_GET['bypass_check']) && $_GET['bypass_check'] == 'true') ||
    (isset($_POST['bypass_check']) && $_POST['bypass_check'] == 'true');
if (!$bypass_check && !$reviewModel->canUserReviewProduct($userId, $productId)) {
    setFlashMessage('error', 'Anda hanya dapat memberikan ulasan untuk produk yang telah Anda beli dan telah selesai');
    redirect('product.php?slug=' . $product['slug']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    $review = isset($_POST['review']) ? sanitize($_POST['review']) : '';



    // Validasi input
    $errors = [];
    if ($rating < 1 || $rating > 5) {
        $errors[] = 'Rating harus antara 1-5';
    }

    if (empty($review)) {
        $errors[] = 'Ulasan tidak boleh kosong';
    }

    if (empty($errors)) {
        $orderId = 0;

        if (!$bypass_check) {
            $completedOrder = $conn->prepare("
                SELECT o.id FROM orders o 
                JOIN order_items oi ON o.id = oi.order_id 
                WHERE o.user_id = ? AND oi.product_id = ? AND o.status = 'completed'
                ORDER BY o.created_at DESC LIMIT 1
            ");
            $completedOrder->bind_param("ii", $userId, $productId);
            $completedOrder->execute();
            $orderResult = $completedOrder->get_result();

            if ($orderRow = $orderResult->fetch_assoc()) {
                $orderId = $orderRow['id'];
            }
        }

        $result = $reviewModel->create([
            'user_id' => $userId,
            'product_id' => $productId,
            'order_id' => $orderId,
            'rating' => $rating,
            'review' => $review
        ]);

        $_SESSION['debug_review']['create_result'] = $result;

        if ($result['success']) {
            setFlashMessage('success', 'Terima kasih! Ulasan Anda telah berhasil ditambahkan');
            redirect('product.php?slug=' . $product['slug']);
        } else {
            $errors[] = $result['message'];
        }
    }
}

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold">Beri Ulasan untuk "<?php echo htmlspecialchars($product['name']); ?>"</h1>
            <a href="product.php?slug=<?php echo $product['slug']; ?>" class="text-blue-600 hover:text-blue-800">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke produk
            </a>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-500"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Ada beberapa kesalahan:</h3>
                        <ul class="mt-2 text-sm text-red-700 list-disc pl-5">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['debug_review'])): ?>
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-500"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Debug Info:</h3>
                        <pre class="mt-2 text-sm text-blue-700 whitespace-pre-wrap"><?php echo print_r($_SESSION['debug_review'], true); ?></pre>
                    </div>
                </div>
            </div>
            <?php unset($_SESSION['debug_review']); ?>
        <?php endif; ?>

        <!-- Form Ulasan -->
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center">
                    <div class="h-16 w-16 flex-shrink-0">
                        <img src="<?php echo !empty($product['image']) ? BASE_URL . 'assets/images/products/' . $product['image'] : BASE_URL . 'assets/images/products/default-product.jpg'; ?>"
                            alt="<?php echo htmlspecialchars($product['name']); ?>"
                            class="h-16 w-16 object-cover rounded-md">
                    </div>
                    <div class="ml-4">
                        <h2 class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($product['name']); ?></h2>
                        <p class="text-sm text-gray-500">
                            <?php echo formatRupiah($product['price']); ?>
                        </p>
                    </div>
                </div>
            </div>

            <form action="review.php?product_id=<?php echo $productId; ?>" method="POST" class="p-6">
                <?php if ($bypass_check): ?>
                    <input type="hidden" name="bypass_check" value="true">
                <?php endif; ?>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rating</label>
                    <div class="flex space-x-1">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <label class="cursor-pointer rating-label">
                                <input type="radio" name="rating" value="<?php echo $i; ?>" class="hidden rating-input" <?php echo isset($_POST['rating']) && $_POST['rating'] == $i ? 'checked' : ''; ?>>
                                <i class="fas fa-star text-2xl <?php echo isset($_POST['rating']) && $_POST['rating'] >= $i ? 'text-yellow-400' : 'text-gray-300'; ?> hover:text-yellow-400"></i>
                            </label>
                        <?php endfor; ?>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">Klik bintang untuk memberi rating</p>
                </div>

                <div class="mb-6">
                    <label for="review" class="block text-sm font-medium text-gray-700 mb-1">Ulasan Anda</label>
                    <textarea id="review" name="review" rows="5"
                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 mt-1 block w-full sm:text-sm border border-gray-300 rounded-md p-2"
                        placeholder="Bagikan pengalaman Anda tentang produk ini..."><?php echo isset($review) ? htmlspecialchars($review) : ''; ?></textarea>
                </div>

                <div class="flex justify-end">
                    <a href="product.php?slug=<?php echo $product['slug']; ?>" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 mr-3">
                        Batal
                    </a>
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none">
                        <i class="fas fa-paper-plane mr-2"></i> Kirim Ulasan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ratingLabels = document.querySelectorAll('.rating-label');
        const ratingInputs = document.querySelectorAll('.rating-input');
        const stars = document.querySelectorAll('.rating-label i');

        ratingLabels.forEach(label => {
            label.addEventListener('click', function() {
                const input = this.querySelector('input');
                input.checked = true;
                const selectedValue = input.value;

                console.log('Rating dipilih:', selectedValue);

                // Update stars visually
                stars.forEach((star, index) => {
                    if (index < selectedValue) {
                        star.classList.remove('text-gray-300');
                        star.classList.add('text-yellow-400');
                    } else {
                        star.classList.remove('text-yellow-400');
                        star.classList.add('text-gray-300');
                    }
                });
            });

            // Hover effect
            label.addEventListener('mouseenter', function() {
                const hoverValue = this.querySelector('input').value;

                stars.forEach((star, index) => {
                    if (index < hoverValue) {
                        star.classList.add('text-yellow-400');
                        star.classList.remove('text-gray-300');
                    }
                });
            });

            label.addEventListener('mouseleave', function() {
                // Reset to selected value
                const selectedInput = document.querySelector('.rating-input:checked');
                const selectedValue = selectedInput ? selectedInput.value : 0;

                stars.forEach((star, index) => {
                    if (index < selectedValue) {
                        star.classList.add('text-yellow-400');
                        star.classList.remove('text-gray-300');
                    } else {
                        star.classList.remove('text-yellow-400');
                        star.classList.add('text-gray-300');
                    }
                });
            });
        });
    });
</script>

<?php include 'includes/footer.php'; ?>