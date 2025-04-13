<?php
$active_page = 'home';
$page_title = 'Belanja Gorden Berkualitas';
require_once 'config/config.php';

// Load models
require_once 'lib/models/Category.php';
require_once 'lib/models/Product.php';
require_once 'lib/models/Banner.php';
require_once 'lib/models/Discount.php';
require_once 'lib/models/Wishlist.php';

// Instance models
$categoryModel = new Category($conn);
$productModel = new Product($conn);
$bannerModel = new Banner($conn);
$discountModel = new Discount($conn);

// Get data
$banners = $bannerModel->getActive();
$featuredProducts = $productModel->getAll(8);
$discountedProducts = $productModel->getDiscountedProducts();
$categories = $categoryModel->getAll();

// Include header
include 'includes/header.php';
?>

<!-- Banner Slider -->
<div class="relative overflow-hidden bg-gray-100 h-80 md:h-96">
    <div class="flex banner-slider" id="bannerSlider" style="transition: transform 0.5s ease-in-out;">
        <?php foreach ($banners as $banner): ?>
            <div class="min-w-full h-80 md:h-96 bg-cover bg-center" style="background-image: url('<?php echo BASE_URL . 'assets/images/banners/' . $banner['image']; ?>')">
                <div class="container mx-auto px-4 h-full flex items-center">
                    <div class="max-w-lg bg-black bg-opacity-60 p-6 rounded-lg">
                        <h2 class="text-3xl font-bold text-white mb-2"><?php echo $banner['title']; ?></h2>
                        <p class="text-white mb-4"><?php echo $banner['description']; ?></p>
                        <?php if (!empty($banner['link'])): ?>
                            <a href="<?php echo $banner['link']; ?>" class="inline-block px-5 py-2 bg-blue-700 text-white font-semibold rounded hover:bg-blue-800 transition-colors">
                                Selengkapnya
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <!-- Slider Navigation -->
    <?php if (count($banners) > 1): ?>
        <div class="absolute bottom-4 left-0 right-0 flex justify-center space-x-2">
            <?php for ($i = 0; $i < count($banners); $i++): ?>
                <button class="w-3 h-3 rounded-full bg-white bg-opacity-50 banner-indicator" data-index="<?php echo $i; ?>"></button>
            <?php endfor; ?>
        </div>
        <button class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-30 text-white p-2 rounded-full hover:bg-opacity-50" id="prevBtn">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-30 text-white p-2 rounded-full hover:bg-opacity-50" id="nextBtn">
            <i class="fas fa-chevron-right"></i>
        </button>
    <?php endif; ?>
</div>

<!-- Kategori -->
<section class="py-10 bg-white">
    <div class="container mx-auto px-4">
        <h2 class="text-2xl font-bold mb-6 text-center">Kategori Gorden</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <?php foreach ($categories as $category): ?>
                <a href="<?php echo BASE_URL . 'category.php?slug=' . $category['slug']; ?>" class="group">
                    <div class="bg-gray-100 rounded-lg p-6 text-center transition-all hover:shadow-lg">
                        <div class="w-16 h-16 bg-blue-100 text-blue-800 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-blue-800 group-hover:text-white transition-colors">
                            <i class="fas fa-layer-group text-2xl"></i>
                        </div>
                        <h3 class="font-semibold"><?php echo $category['name']; ?></h3>
                        <p class="text-sm text-gray-600 mt-2">
                            <?php
                            $count = $categoryModel->getProductCount($category['id']);
                            echo $count . ' Produk';
                            ?>
                        </p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Produk Diskon -->
<?php if (count($discountedProducts) > 0): ?>
    <section class="py-10 bg-white">
        <div class="container mx-auto px-4">
            <div class="bg-red-600 text-white p-4 rounded-lg mb-6">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-bold flex items-center">
                        <i class="fas fa-bolt mr-2"></i> Promo Spesial!
                    </h2>
                    <a href="<?php echo BASE_URL . 'products.php?discount=1'; ?>" class="text-white bg-red-700 px-3 py-1 rounded hover:bg-red-800 transition-colors">
                        Lihat Semua Diskon <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php foreach ($discountedProducts as $product): ?>
                    <div class="bg-white rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-shadow border border-red-200">
                        <a href="<?php echo BASE_URL . 'product.php?slug=' . $product['slug']; ?>">
                            <div class="h-48 overflow-hidden relative">
                                <img src="<?php echo !empty($product['image']) ? BASE_URL . 'assets/images/products/' . $product['image'] : BASE_URL . 'assets/images/products/default-product.jpg'; ?>"
                                    alt="<?php echo $product['name']; ?>"
                                    class="w-full h-full object-cover object-center hover:scale-105 transition-transform">

                                <div class="absolute top-2 left-2 bg-red-600 text-white text-xs font-semibold px-2 py-1 rounded">
                                    <?php
                                    if ($product['is_percentage']) {
                                        echo $product['value'] . '%';
                                    } else {
                                        echo 'Hemat ' . formatRupiah($product['value']);
                                    }
                                    ?>
                                </div>
                            </div>
                        </a>
                        <div class="p-4">
                            <a href="<?php echo BASE_URL . 'category.php?slug=' . $product['slug']; ?>" class="text-xs text-blue-600 hover:text-blue-800">
                                <?php echo $product['category_name']; ?>
                            </a>
                            <a href="<?php echo BASE_URL . 'product.php?slug=' . $product['slug']; ?>">
                                <h3 class="font-semibold mt-1 hover:text-blue-600"><?php echo $product['name']; ?></h3>
                            </a>
                            <div class="mt-2">
                                <span class="text-gray-500 line-through"><?php echo formatRupiah($product['price']); ?></span>
                                <span class="text-red-600 font-semibold ml-1">
                                    <?php
                                    $discountedPrice = $product['price'];
                                    if ($product['is_percentage']) {
                                        $discountedPrice = $product['price'] - ($product['price'] * $product['value'] / 100);
                                    } else {
                                        $discountedPrice = $product['price'] - $product['value'];
                                    }
                                    if ($discountedPrice < 0) $discountedPrice = 0;
                                    echo formatRupiah($discountedPrice);
                                    ?>
                                </span>
                            </div>
                            <div class="mt-3 flex justify-between items-center">
                                <a href="<?php echo BASE_URL . 'cart.php?action=add&id=' . $product['id']; ?>" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-shopping-cart mr-1"></i> Beli
                                </a>
                                <?php if (isLoggedIn()): ?>
                                    <button class="text-gray-400 hover:text-red-500 wishlist-btn" data-product-id="<?php echo $product['id']; ?>">
                                        <i class="far fa-heart"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- Produk Unggulan -->
<section class="py-10 bg-gray-100">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Produk Terbaru</h2>
            <a href="<?php echo BASE_URL . 'products.php'; ?>" class="text-blue-600 hover:text-blue-800">
                Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($featuredProducts as $product): ?>
                <div class="bg-white rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-shadow">
                    <a href="<?php echo BASE_URL . 'product.php?slug=' . $product['slug']; ?>">
                        <div class="h-48 overflow-hidden relative">
                            <img src="<?php echo !empty($product['image']) ? BASE_URL . 'assets/images/products/' . $product['image'] : BASE_URL . 'assets/images/products/default-product.jpg'; ?>"
                                alt="<?php echo $product['name']; ?>"
                                class="w-full h-full object-cover object-center hover:scale-105 transition-transform">

                            <?php if (isset($product['discount_id'])): ?>
                                <div class="absolute top-2 left-2 bg-red-600 text-white text-xs font-semibold px-2 py-1 rounded">
                                    Diskon
                                </div>
                            <?php endif; ?>
                        </div>
                    </a>
                    <div class="p-4">
                        <a href="<?php echo BASE_URL . 'category.php?slug=' . $product['slug']; ?>" class="text-xs text-blue-600 hover:text-blue-800">
                            <?php echo $product['category_name']; ?>
                        </a>
                        <a href="<?php echo BASE_URL . 'product.php?slug=' . $product['slug']; ?>">
                            <h3 class="font-semibold mt-1 hover:text-blue-600"><?php echo $product['name']; ?></h3>
                        </a>
                        <div class="mt-2">
                            <?php
                            $discountedPrice = $productModel->getDiscountedPrice($product);
                            if ($discountedPrice < $product['price']):
                            ?>
                                <span class="text-gray-500 line-through"><?php echo formatRupiah($product['price']); ?></span>
                                <span class="text-red-600 font-semibold ml-1"><?php echo formatRupiah($discountedPrice); ?></span>
                            <?php else: ?>
                                <span class="font-semibold"><?php echo formatRupiah($product['price']); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="mt-3 flex justify-between items-center">
                            <a href="<?php echo BASE_URL . 'cart.php?action=add&id=' . $product['id']; ?>" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700 transition-colors">
                                <i class="fas fa-shopping-cart mr-1"></i> Beli
                            </a>
                            <?php if (isLoggedIn()): ?>
                                <button class="text-gray-400 hover:text-red-500 wishlist-btn" data-product-id="<?php echo $product['id']; ?>">
                                    <i class="far fa-heart"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>



<!-- Keunggulan Toko -->
<section class="py-10 bg-gray-100">
    <div class="container mx-auto px-4">
        <h2 class="text-2xl font-bold mb-6 text-center">Mengapa Memilih Kami?</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white p-6 rounded-lg shadow-md text-center">
                <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-shipping-fast text-2xl"></i>
                </div>
                <h3 class="font-semibold mb-2">Pengiriman Cepat</h3>
                <p class="text-gray-600 text-sm">Kami mengirimkan pesanan Anda dengan cepat dan aman ke seluruh Indonesia.</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md text-center">
                <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-money-bill-wave text-2xl"></i>
                </div>
                <h3 class="font-semibold mb-2">Harga Terjangkau</h3>
                <p class="text-gray-600 text-sm">Kami menawarkan gorden berkualitas dengan harga yang bersaing di pasaran.</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md text-center">
                <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-gem text-2xl"></i>
                </div>
                <h3 class="font-semibold mb-2">Kualitas Premium</h3>
                <p class="text-gray-600 text-sm">Produk kami dibuat dari bahan berkualitas tinggi dan tahan lama.</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md text-center">
                <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-headset text-2xl"></i>
                </div>
                <h3 class="font-semibold mb-2">Pelayanan 24/7</h3>
                <p class="text-gray-600 text-sm">Tim customer service kami siap membantu Anda kapan saja diperlukan.</p>
            </div>
        </div>
    </div>
</section>

<!-- Testimoni Pelanggan -->
<section class="py-10 bg-white">
    <div class="container mx-auto px-4">
        <h2 class="text-2xl font-bold mb-6 text-center">Apa Kata Pelanggan Kami</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-blue-700 text-white rounded-full flex items-center justify-center mr-4">
                        <span class="font-bold">RS</span>
                    </div>
                    <div>
                        <h3 class="font-semibold">Rina Santika</h3>
                        <div class="flex text-yellow-400">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
                <p class="text-gray-700">"Gordennya sangat bagus dan sesuai dengan ekspektasi. Pengiriman juga cepat dan pelayanannya sangat ramah. Terima kasih Toko Gorden!"</p>
            </div>
            <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-green-700 text-white rounded-full flex items-center justify-center mr-4">
                        <span class="font-bold">BP</span>
                    </div>
                    <div>
                        <h3 class="font-semibold">Budi Prasetyo</h3>
                        <div class="flex text-yellow-400">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                    </div>
                </div>
                <p class="text-gray-700">"Kualitas gorden sangat bagus, warna tidak luntur dan jahitannya rapi. Harga juga terjangkau dibanding toko lain. Puas dengan pembelian di sini."</p>
            </div>
            <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-purple-700 text-white rounded-full flex items-center justify-center mr-4">
                        <span class="font-bold">DM</span>
                    </div>
                    <div>
                        <h3 class="font-semibold">Dewi Maharani</h3>
                        <div class="flex text-yellow-400">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
                <p class="text-gray-700">"Sudah 3 kali beli gorden di sini dan selalu puas. Motifnya bagus-bagus dan up to date. Customer service juga sangat membantu dalam memilih gorden yang sesuai."</p>
            </div>
        </div>
    </div>
</section>

<script>
    // Banner Slider
    let currentSlide = 0;
    const slides = document.querySelectorAll('#bannerSlider > div');
    const indicators = document.querySelectorAll('.banner-indicator');
    const slideCount = slides.length;

    function showSlide(index) {
        if (index >= slideCount) {
            currentSlide = 0;
        } else if (index < 0) {
            currentSlide = slideCount - 1;
        } else {
            currentSlide = index;
        }

        document.getElementById('bannerSlider').style.transform = `translateX(-${currentSlide * 100}%)`;

        // Update indicators
        indicators.forEach((indicator, i) => {
            if (i === currentSlide) {
                indicator.classList.add('bg-white');
                indicator.classList.remove('bg-opacity-50');
            } else {
                indicator.classList.remove('bg-white');
                indicator.classList.add('bg-opacity-50');
            }
        });
    }

    // Add event listeners for indicators
    indicators.forEach((indicator, i) => {
        indicator.addEventListener('click', () => {
            showSlide(i);
        });
    });

    // Auto slide
    let slideInterval = setInterval(() => {
        showSlide(currentSlide + 1);
    }, 5000);

    // Add event listeners for prev/next buttons
    if (document.getElementById('prevBtn')) {
        document.getElementById('prevBtn').addEventListener('click', () => {
            clearInterval(slideInterval);
            showSlide(currentSlide - 1);
            slideInterval = setInterval(() => {
                showSlide(currentSlide + 1);
            }, 5000);
        });
    }

    if (document.getElementById('nextBtn')) {
        document.getElementById('nextBtn').addEventListener('click', () => {
            clearInterval(slideInterval);
            showSlide(currentSlide + 1);
            slideInterval = setInterval(() => {
                showSlide(currentSlide + 1);
            }, 5000);
        });
    }

    // Wishlist functionality
    document.querySelectorAll('.wishlist-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            const icon = this.querySelector('i');

            // Toggle icon class
            if (icon.classList.contains('far')) {
                // Add to wishlist
                fetch(`${window.location.origin}/tokogorden/api/wishlist-add.php?product_id=${productId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            icon.classList.remove('far');
                            icon.classList.add('fas');
                            icon.classList.add('text-red-500');
                        }
                    });
            } else {
                // Remove from wishlist
                fetch(`${window.location.origin}/tokogorden/api/wishlist-remove.php?product_id=${productId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            icon.classList.remove('fas');
                            icon.classList.remove('text-red-500');
                            icon.classList.add('far');
                        }
                    });
            }
        });
    });

    // Init the first slide
    showSlide(0);
</script>

<?php include 'includes/footer.php'; ?>