<?php
$page_title = 'Detail Produk';
$show_breadcrumb = true;

require_once 'config/config.php';
require_once 'lib/models/Product.php';
require_once 'lib/models/Category.php';
require_once 'lib/models/Review.php';
require_once 'lib/models/Discount.php';
require_once 'lib/models/Wishlist.php';

if (!isset($_GET['slug']) || empty($_GET['slug'])) {
    redirect('');
}

$slug = sanitize($_GET['slug']);
$productModel = new Product($conn);
$categoryModel = new Category($conn);
$reviewModel = new Review($conn);
$discountModel = new Discount($conn);
$wishlistModel = new Wishlist($conn);

$product = $productModel->getBySlug($slug);

if (!$product) {
    setFlashMessage('error', 'Produk tidak ditemukan');
    redirect('');
}

$category = $categoryModel->getById($product['category_id']);
$breadcrumb_items = [
    [
        'label' => $category['name'],
        'url' => BASE_URL . 'category.php?slug=' . $category['slug']
    ],
    [
        'label' => $product['name']
    ]
];

$isInWishlist = false;
if (isLoggedIn()) {
    $isInWishlist = $wishlistModel->isProductInWishlist($_SESSION['user_id'], $product['id']);
}

$originalPrice = $product['price'];
$discountedPrice = $productModel->getDiscountedPrice($product);
$hasDiscount = ($discountedPrice < $originalPrice);
$discountPercentage = 0;
if ($hasDiscount) {
    $discountPercentage = round(($originalPrice - $discountedPrice) / $originalPrice * 100);
}

$relatedProducts = $productModel->getByCategoryId($product['category_id'], 4);

$reviews = $reviewModel->getProductReviews($product['id']);
$reviewCount = $reviewModel->getTotalProductReviews($product['id']);
$averageRating = $product['rating'] ?? $reviewModel->getAverageRating($product['id']);

$wishlistMessage = '';
if (isset($_GET['action']) && $_GET['action'] === 'add_to_wishlist' && isLoggedIn()) {
    if ($isInWishlist) {
        $wishlistMessage = 'Produk sudah ada di wishlist Anda';
    } else {
        $result = $wishlistModel->add($_SESSION['user_id'], $product['id']);
        if ($result) {
            $isInWishlist = true;
            $wishlistMessage = 'Produk berhasil ditambahkan ke wishlist';
        } else {
            $wishlistMessage = 'Gagal menambahkan produk ke wishlist';
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'remove_from_wishlist' && isLoggedIn()) {
    $result = $wishlistModel->remove($_SESSION['user_id'], $product['id']);
    if ($result) {
        $isInWishlist = false;
        $wishlistMessage = 'Produk berhasil dihapus dari wishlist';
    } else {
        $wishlistMessage = 'Gagal menghapus produk dari wishlist';
    }
}

$cartMessage = '';
if (isset($_GET['action']) && $_GET['action'] === 'add_to_cart') {
    $quantity = isset($_GET['quantity']) ? intval($_GET['quantity']) : 1;

    if ($quantity <= 0) {
        $quantity = 1;
    }

    if ($quantity > $product['stock']) {
        $cartMessage = 'Jumlah melebihi stok yang tersedia';
    } else {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        $productId = $product['id'];
        $found = false;

        for ($i = 0; $i < count($_SESSION['cart']); $i++) {
            if ($_SESSION['cart'][$i]['id'] == $productId) {
                $_SESSION['cart'][$i]['quantity'] += $quantity;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $_SESSION['cart'][] = [
                'id' => $productId,
                'name' => $product['name'],
                'price' => $discountedPrice,
                'original_price' => $originalPrice,
                'quantity' => $quantity,
                'image' => $product['image']
            ];
        }

        $cartMessage = 'Produk berhasil ditambahkan ke keranjang';
    }
}

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <?php if (!empty($wishlistMessage)): ?>
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4">
            <div class="flex items-center">
                <i class="fas fa-info-circle mr-2"></i>
                <span><?php echo $wishlistMessage; ?></span>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($cartMessage)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <span><?php echo $cartMessage; ?></span>
            </div>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow-md overflow-hidden p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Gambar Produk -->
            <div>
                <div class="bg-gray-100 rounded-lg p-4 flex items-center justify-center h-96">
                    <img src="<?php echo !empty($product['image']) ? BASE_URL . 'assets/images/products/' . $product['image'] : BASE_URL . 'assets/images/products/default-product.jpg'; ?>"
                        alt="<?php echo $product['name']; ?>"
                        class="max-h-full max-w-full object-contain">
                </div>
            </div>

            <!-- Detail Produk -->
            <div>
                <div class="mb-2">
                    <a href="<?php echo BASE_URL . 'category.php?slug=' . $category['slug']; ?>" class="text-sm text-blue-600 hover:text-blue-800">
                        <?php echo $category['name']; ?>
                    </a>
                </div>

                <h1 class="text-3xl font-bold text-gray-900 mb-2"><?php echo $product['name']; ?></h1>

                <div class="flex items-center mb-4">
                    <div class="flex items-center">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?php if ($i <= round($averageRating)): ?>
                                <i class="fas fa-star text-yellow-400"></i>
                            <?php else: ?>
                                <i class="far fa-star text-yellow-400"></i>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                    <span class="ml-2 text-gray-600"><?php echo $averageRating; ?> (<?php echo $reviewCount; ?> ulasan)</span>
                </div>

                <div class="mb-4">
                    <?php if ($hasDiscount): ?>
                        <div class="flex items-center mb-1">
                            <span class="text-gray-500 line-through text-lg"><?php echo formatRupiah($originalPrice); ?></span>
                            <span class="ml-2 bg-red-100 text-red-800 text-xs font-semibold px-2 py-1 rounded">-<?php echo $discountPercentage; ?>%</span>
                        </div>
                        <div class="text-2xl font-bold text-red-600"><?php echo formatRupiah($discountedPrice); ?></div>
                    <?php else: ?>
                        <div class="text-2xl font-bold text-gray-900"><?php echo formatRupiah($originalPrice); ?></div>
                    <?php endif; ?>
                </div>

                <div class="border-t border-b border-gray-200 py-4 my-4">
                    <div class="flex items-center mb-2">
                        <span class="w-24 text-gray-600">Stok</span>
                        <span class="font-medium"><?php echo $product['stock']; ?> tersedia</span>
                    </div>
                </div>

                <form action="<?php echo BASE_URL . 'product.php?slug=' . $slug . '&action=add_to_cart'; ?>" method="get" class="mb-6">
                    <input type="hidden" name="slug" value="<?php echo $slug; ?>">
                    <input type="hidden" name="action" value="add_to_cart">

                    <div class="flex items-center mb-4">
                        <span class="w-24 text-gray-600">Jumlah</span>
                        <div class="flex items-center">
                            <button type="button" class="quantity-btn minus bg-gray-200 rounded-l px-3 py-1">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" class="quantity-input w-16 text-center py-1 border-t border-b border-gray-300">
                            <button type="button" class="quantity-btn plus bg-gray-200 rounded-r px-3 py-1">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center space-x-4">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg flex items-center">
                            <i class="fas fa-shopping-cart mr-2"></i> Tambah ke Keranjang
                        </button>

                        <?php if (isLoggedIn()): ?>
                            <?php if ($isInWishlist): ?>
                                <a href="<?php echo BASE_URL . 'product.php?slug=' . $slug . '&action=remove_from_wishlist'; ?>" class="text-red-500 hover:text-red-600">
                                    <i class="fas fa-heart text-xl"></i>
                                </a>
                            <?php else: ?>
                                <a href="<?php echo BASE_URL . 'product.php?slug=' . $slug . '&action=add_to_wishlist'; ?>" class="text-gray-400 hover:text-red-500">
                                    <i class="far fa-heart text-xl"></i>
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </form>

                <div class="mt-6">
                    <h3 class="text-lg font-semibold mb-2">Deskripsi Produk</h3>
                    <div class="text-gray-700 leading-relaxed">
                        <?php echo nl2br($product['description']); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Produk Terkait -->
    <?php if (count($relatedProducts) > 0): ?>
        <div class="mt-12">
            <h2 class="text-2xl font-bold mb-6">Produk Terkait</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php foreach ($relatedProducts as $relatedProduct): ?>
                    <?php if ($relatedProduct['id'] != $product['id']): ?>
                        <div class="bg-white rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-shadow">
                            <a href="<?php echo BASE_URL . 'product.php?slug=' . $relatedProduct['slug']; ?>">
                                <div class="h-48 overflow-hidden relative">
                                    <img src="<?php echo !empty($relatedProduct['image']) ? BASE_URL . 'assets/images/products/' . $relatedProduct['image'] : BASE_URL . 'assets/images/products/default-product.jpg'; ?>"
                                        alt="<?php echo $relatedProduct['name']; ?>"
                                        class="w-full h-full object-cover object-center hover:scale-105 transition-transform">

                                    <?php
                                    $relatedDiscountedPrice = $productModel->getDiscountedPrice($relatedProduct);
                                    if ($relatedDiscountedPrice < $relatedProduct['price']):
                                        $relatedDiscountPercentage = round(($relatedProduct['price'] - $relatedDiscountedPrice) / $relatedProduct['price'] * 100);
                                    ?>
                                        <div class="absolute top-2 left-2 bg-red-600 text-white text-xs font-semibold px-2 py-1 rounded">
                                            -<?php echo $relatedDiscountPercentage; ?>%
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </a>
                            <div class="p-4">
                                <a href="<?php echo BASE_URL . 'category.php?slug=' . $category['slug']; ?>" class="text-xs text-blue-600 hover:text-blue-800">
                                    <?php echo $relatedProduct['category_name']; ?>
                                </a>
                                <a href="<?php echo BASE_URL . 'product.php?slug=' . $relatedProduct['slug']; ?>">
                                    <h3 class="font-semibold mt-1 hover:text-blue-600"><?php echo $relatedProduct['name']; ?></h3>
                                </a>
                                <div class="mt-2">
                                    <?php if ($relatedDiscountedPrice < $relatedProduct['price']): ?>
                                        <span class="text-gray-500 line-through"><?php echo formatRupiah($relatedProduct['price']); ?></span>
                                        <span class="text-red-600 font-semibold ml-1"><?php echo formatRupiah($relatedDiscountedPrice); ?></span>
                                    <?php else: ?>
                                        <span class="font-semibold"><?php echo formatRupiah($relatedProduct['price']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Ulasan Produk -->
    <div class="mt-12">
        <h2 class="text-2xl font-bold mb-6">Ulasan Produk</h2>

        <?php if (count($reviews) > 0): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="text-center">
                        <div class="text-5xl font-bold text-blue-600 mb-2"><?php echo $averageRating; ?></div>
                        <div class="flex items-center justify-center mb-2">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?php if ($i <= round($averageRating)): ?>
                                    <i class="fas fa-star text-yellow-400"></i>
                                <?php else: ?>
                                    <i class="far fa-star text-yellow-400"></i>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                        <div class="text-gray-600"><?php echo $reviewCount; ?> ulasan</div>
                    </div>

                    <div class="md:col-span-3">
                        <div class="space-y-4">
                            <?php foreach ($reviews as $review): ?>
                                <div class="border-b border-gray-200 pb-4 mb-4 last:border-b-0 last:pb-0 last:mb-0">
                                    <div class="flex items-center justify-between mb-2">
                                        <div>
                                            <div class="font-semibold"><?php echo $review['user_name']; ?></div>
                                            <div class="text-sm text-gray-500"><?php echo date('d M Y', strtotime($review['created_at'])); ?></div>
                                        </div>
                                        <div class="flex items-center">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <?php if ($i <= $review['rating']): ?>
                                                    <i class="fas fa-star text-yellow-400"></i>
                                                <?php else: ?>
                                                    <i class="far fa-star text-yellow-400"></i>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <div class="text-gray-700">
                                        <?php
                                        $reviewText = isset($review['review']) ? $review['review'] : (isset($review['comment']) ? $review['comment'] : '');
                                        echo nl2br($reviewText);
                                        ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden p-6 text-center">
                <p class="text-gray-600">Belum ada ulasan untuk produk ini.</p>
                <?php if (isLoggedIn()): ?>
                    <p class="mt-2">
                        <a href="<?php echo BASE_URL . 'review.php?product_id=' . $product['id'] . '&bypass_check=true'; ?>" class="text-blue-600 hover:text-blue-800">
                            Jadilah yang pertama mengulas produk ini
                        </a>
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (isLoggedIn()): ?>
            <div class="mt-6 text-center">
                <a href="<?php echo BASE_URL . 'review.php?product_id=' . $product['id'] . '&bypass_check=true'; ?>" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg inline-flex items-center">
                    <i class="fas fa-star mr-2"></i> Tulis Ulasan
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const quantityInput = document.querySelector('.quantity-input');
        const minusBtn = document.querySelector('.quantity-btn.minus');
        const plusBtn = document.querySelector('.quantity-btn.plus');
        const maxStock = <?php echo $product['stock']; ?>;

        minusBtn.addEventListener('click', function() {
            let value = parseInt(quantityInput.value);
            if (value > 1) {
                quantityInput.value = value - 1;
            }
        });

        plusBtn.addEventListener('click', function() {
            let value = parseInt(quantityInput.value);
            if (value < maxStock) {
                quantityInput.value = value + 1;
            }
        });

        quantityInput.addEventListener('change', function() {
            let value = parseInt(this.value);
            if (isNaN(value) || value < 1) {
                this.value = 1;
            } else if (value > maxStock) {
                this.value = maxStock;
            }
        });
    });
</script>

<?php include 'includes/footer.php'; ?>