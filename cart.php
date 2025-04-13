<?php
$active_page = 'cart';
$page_title = 'Keranjang Belanja';
$show_breadcrumb = true;

require_once 'config/config.php';
require_once 'lib/models/Product.php';
require_once 'lib/models/Discount.php';

$breadcrumb_items = [
    [
        'label' => 'Keranjang Belanja'
    ]
];

$productModel = new Product($conn);
$discountModel = new Discount($conn);

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle actions
$message = '';
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    // Add product to cart
    if ($action === 'add' && isset($_GET['id'])) {
        $productId = intval($_GET['id']);
        $product = $productModel->getById($productId);

        if ($product) {
            $quantity = isset($_GET['quantity']) ? intval($_GET['quantity']) : 1;
            if ($quantity <= 0) $quantity = 1;

            if ($quantity > $product['stock']) {
                $message = 'Jumlah melebihi stok yang tersedia';
            } else {
                $found = false;

                for ($i = 0; $i < count($_SESSION['cart']); $i++) {
                    if (isset($_SESSION['cart'][$i]['id']) && $_SESSION['cart'][$i]['id'] == $productId) {
                        $_SESSION['cart'][$i]['quantity'] += $quantity;
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    // Calculate discounted price
                    $discountedPrice = $productModel->getDiscountedPrice($product);

                    $_SESSION['cart'][] = [
                        'id' => $productId,
                        'name' => $product['name'],
                        'price' => $discountedPrice,
                        'original_price' => $product['price'],
                        'quantity' => $quantity,
                        'image' => $product['image']
                    ];
                }

                $message = 'Produk berhasil ditambahkan ke keranjang';
            }
        }
    }

    // Update cart item quantity
    else if ($action === 'update' && isset($_POST['quantity'])) {
        $quantities = $_POST['quantity'];

        foreach ($quantities as $index => $qty) {
            $qty = intval($qty);
            if ($qty <= 0) {
                // Remove item if quantity is zero or negative
                unset($_SESSION['cart'][$index]);
            } else {
                // Check stock
                $productId = isset($_SESSION['cart'][$index]['id']) ? $_SESSION['cart'][$index]['id'] : 0;
                $product = $productModel->getById($productId);

                if ($product && $qty <= $product['stock']) {
                    $_SESSION['cart'][$index]['quantity'] = $qty;
                }
            }
        }

        // Reindex array
        $_SESSION['cart'] = array_values($_SESSION['cart']);
        $message = 'Keranjang berhasil diperbarui';
    }

    // Remove item from cart
    else if ($action === 'remove' && isset($_GET['index'])) {
        $index = intval($_GET['index']);

        if (isset($_SESSION['cart'][$index])) {
            unset($_SESSION['cart'][$index]);
            $_SESSION['cart'] = array_values($_SESSION['cart']);
            $message = 'Produk berhasil dihapus dari keranjang';
        }
    }

    // Clear cart
    else if ($action === 'clear') {
        $_SESSION['cart'] = [];
        $message = 'Keranjang berhasil dikosongkan';
    }

    // Apply voucher
    else if ($action === 'apply_voucher' && isset($_POST['voucher_code'])) {
        $voucherCode = sanitize($_POST['voucher_code']);
        $voucher = $discountModel->validateVoucherCode($voucherCode);

        if ($voucher) {
            $_SESSION['voucher'] = $voucher;
            $message = 'Voucher berhasil diterapkan';
        } else {
            $_SESSION['voucher'] = null;
            $message = 'Kode voucher tidak valid atau telah kadaluarsa';
        }
    }

    // Remove voucher
    else if ($action === 'remove_voucher') {
        $_SESSION['voucher'] = null;
        $message = 'Voucher berhasil dihapus';
    }
}

// Calculate cart totals
$items = $_SESSION['cart'];
$subtotal = 0;
$discount = 0;
$total = 0;
$voucher = isset($_SESSION['voucher']) ? $_SESSION['voucher'] : null;

// Update cart with latest prices and calculate totals
for ($i = 0; $i < count($items); $i++) {
    if (!isset($items[$i]['id'])) {
        unset($items[$i]);
        continue;
    }

    $product = $productModel->getById($items[$i]['id']);
    if ($product) {
        // Get latest discounted price
        $discountedPrice = $productModel->getDiscountedPrice($product);
        $items[$i]['price'] = $discountedPrice;
        $items[$i]['original_price'] = $product['price'];

        // Calculate discount amount per item
        $items[$i]['discount_amount'] = ($product['price'] - $discountedPrice) * $items[$i]['quantity'];

        // Calculate line total
        $lineTotal = $items[$i]['price'] * $items[$i]['quantity'];
        $subtotal += $lineTotal;
    } else {
        // Product no longer exists, remove from cart
        unset($items[$i]);
    }
}

// Reindex the array after possible removals
$items = array_values($items);

// Apply voucher discount if available
if ($voucher) {
    if (isset($voucher['is_percentage']) && $voucher['is_percentage']) {
        $discount = $subtotal * ($voucher['value'] / 100);
    } else if (isset($voucher['value'])) {
        $discount = $voucher['value'];
    }

    if ($discount > $subtotal) {
        $discount = $subtotal;
    }
}

$total = $subtotal - $discount;

// Update session cart
$_SESSION['cart'] = $items;

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <?php if (!empty($message)): ?>
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4">
            <div class="flex items-center">
                <i class="fas fa-info-circle mr-2"></i>
                <span><?php echo $message; ?></span>
            </div>
        </div>
    <?php endif; ?>

    <h1 class="text-2xl font-bold mb-6">Keranjang Belanja</h1>

    <?php if (empty($items)): ?>
        <div class="bg-white rounded-lg shadow-md p-6 text-center">
            <i class="fas fa-shopping-cart text-gray-400 text-5xl mb-4"></i>
            <p class="text-gray-600 mb-4">Keranjang belanja Anda kosong.</p>
            <a href="<?php echo BASE_URL . 'products.php'; ?>" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded inline-flex items-center">
                <i class="fas fa-shopping-bag mr-2"></i> Mulai Belanja
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-2">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <form action="<?php echo BASE_URL . 'cart.php?action=update'; ?>" method="post">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Produk
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Harga
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Jumlah
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Subtotal
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($items as $index => $item): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="h-16 w-16 flex-shrink-0 mr-4 bg-gray-100 rounded overflow-hidden">
                                                    <img src="<?php echo !empty($item['image']) ? BASE_URL . 'assets/images/products/' . $item['image'] : BASE_URL . 'assets/images/products/default-product.jpg'; ?>" alt="<?php echo isset($item['name']) ? $item['name'] : 'Produk'; ?>" class="h-full w-full object-cover object-center">
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900"><?php echo isset($item['name']) ? $item['name'] : 'Produk tidak diketahui'; ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <?php
                                            $itemPrice = isset($item['price']) ? $item['price'] : 0;
                                            $itemOriginalPrice = isset($item['original_price']) ? $item['original_price'] : 0;
                                            if ($itemPrice < $itemOriginalPrice):
                                            ?>
                                                <div class="text-gray-500 line-through text-xs"><?php echo formatRupiah($itemOriginalPrice); ?></div>
                                                <div class="text-red-600"><?php echo formatRupiah($itemPrice); ?></div>
                                            <?php else: ?>
                                                <div><?php echo formatRupiah($itemPrice); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <input type="number" name="quantity[<?php echo $index; ?>]" value="<?php echo isset($item['quantity']) ? $item['quantity'] : 1; ?>" min="1" class="w-16 px-2 py-1 border border-gray-300 rounded text-center">
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                            <?php echo formatRupiah(isset($item['price']) && isset($item['quantity']) ? $item['price'] * $item['quantity'] : 0); ?>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm">
                                            <a href="<?php echo BASE_URL . 'cart.php?action=remove&index=' . $index; ?>" class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <div class="px-6 py-4 flex justify-between items-center">
                            <a href="<?php echo BASE_URL . 'cart.php?action=clear'; ?>" class="text-red-600 hover:text-red-900">
                                <i class="fas fa-trash mr-1"></i> Kosongkan Keranjang
                            </a>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">
                                <i class="fas fa-sync-alt mr-1"></i> Perbarui Keranjang
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div>
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-4">
                    <h2 class="text-lg font-semibold mb-4">Ringkasan Pembelian</h2>

                    <div class="border-t border-gray-200 py-4">
                        <?php if ($voucher): ?>
                            <form action="<?php echo BASE_URL . 'cart.php?action=remove_voucher'; ?>" method="post" class="mb-4">
                                <div class="flex items-center justify-between bg-blue-50 p-2 rounded">
                                    <div>
                                        <div class="text-sm font-medium">Voucher diterapkan: <span class="text-blue-700"><?php echo isset($voucher['code']) ? $voucher['code'] : ''; ?></span></div>
                                        <div class="text-xs text-gray-500">
                                            <?php
                                            if (isset($voucher['is_percentage']) && $voucher['is_percentage']) {
                                                echo "Diskon {$voucher['value']}%";
                                            } else if (isset($voucher['value'])) {
                                                echo "Diskon " . formatRupiah($voucher['value']);
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <button type="submit" class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </form>
                        <?php else: ?>
                            <form action="<?php echo BASE_URL . 'cart.php?action=apply_voucher'; ?>" method="post" class="mb-4">
                                <div class="flex space-x-2">
                                    <input type="text" name="voucher_code" placeholder="Kode Voucher" class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white font-medium py-2 px-4 rounded">
                                        Terapkan
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>

                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal</span>
                                <span><?php echo formatRupiah($subtotal); ?></span>
                            </div>

                            <?php if ($discount > 0): ?>
                                <div class="flex justify-between text-green-600">
                                    <span>Diskon</span>
                                    <span>- <?php echo formatRupiah($discount); ?></span>
                                </div>
                            <?php endif; ?>

                            <div class="flex justify-between font-semibold text-lg pt-2 border-t border-gray-200">
                                <span>Total</span>
                                <span><?php echo formatRupiah($total); ?></span>
                            </div>
                        </div>

                        <a href="<?php echo BASE_URL . 'checkout.php'; ?>" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded block text-center">
                            <i class="fas fa-credit-card mr-2"></i> Lanjut ke Pembayaran
                        </a>

                        <div class="mt-4 text-center">
                            <a href="<?php echo BASE_URL . 'products.php'; ?>" class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-arrow-left mr-1"></i> Lanjut Belanja
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>