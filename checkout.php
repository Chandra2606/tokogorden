<?php
$active_page = 'checkout';
$page_title = 'Checkout';
$show_breadcrumb = true;

require_once 'config/config.php';
require_once 'lib/models/Product.php';
require_once 'lib/models/Address.php';
require_once 'lib/models/Order.php';

if (!isLoggedIn()) {
    setFlashMessage('error', 'Silakan login terlebih dahulu untuk melakukan checkout');
    redirect('login.php');
}

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    setFlashMessage('error', 'Keranjang belanja Anda kosong');
    redirect('cart.php');
}

$productModel = new Product($conn);
$addressModel = new Address($conn);
$orderModel = new Order($conn);

$addresses = $addressModel->getByUserId($_SESSION['user_id']);
$defaultAddress = $addressModel->getDefaultAddress($_SESSION['user_id']);

$cartItems = $_SESSION['cart'];
$voucher = isset($_SESSION['voucher']) ? $_SESSION['voucher'] : null;

$subtotal = 0;
$total_items = 0;
$discount = 0;
$total_discount = 0;

foreach ($cartItems as $index => $item) {
    $product = $productModel->getById($item['id']);
    if ($product) {
        if ($product['stock'] < $item['quantity']) {
            setFlashMessage('error', 'Stok produk "' . $product['name'] . '" tidak mencukupi');
            redirect('cart.php');
        }

        $cartItems[$index]['name'] = $product['name'];
        $cartItems[$index]['price'] = $item['price'];
        $cartItems[$index]['total'] = $item['price'] * $item['quantity'];

        // Pastikan discount_amount sudah dihitung
        if (!isset($item['discount_amount'])) {
            $original_price = $product['price'];
            $discounted_price = $item['price'];
            $cartItems[$index]['discount_amount'] = ($original_price - $discounted_price) * $item['quantity'];
        } else {
            $cartItems[$index]['discount_amount'] = $item['discount_amount'];
        }

        $total_discount += $cartItems[$index]['discount_amount'];
        $subtotal += $cartItems[$index]['total'];
        $total_items += $item['quantity'];
    } else {
        unset($cartItems[$index]);
    }
}

$cartItems = array_values($cartItems);

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

$shipping_cost = 0;

$grand_total = $total + $shipping_cost;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address_id = isset($_POST['address_id']) ? intval($_POST['address_id']) : 0;
    $shipping_address = '';
    $phone = '';

    if ($address_id > 0) {
        $address = $addressModel->getById($address_id);
        if ($address && $address['user_id'] == $_SESSION['user_id']) {
            $full_address = !empty($address['full_address']) ? $address['full_address'] : 'Alamat tidak diisi';

            $shipping_address = $address['recipient_name'] . ', ' . $full_address . ', ' .
                $address['district'] . ', ' . $address['city'] . ', ' .
                $address['province'] . ' ' . $address['postal_code'];
            $phone = $address['phone'];
        } else {
            setFlashMessage('error', 'Alamat pengiriman tidak valid');
            redirect('checkout.php');
        }
    } else {
        setFlashMessage('error', 'Pilih alamat pengiriman');
        redirect('checkout.php');
    }

    $payment_method = isset($_POST['payment_method']) ? sanitize($_POST['payment_method']) : '';

    if (empty($payment_method)) {
        setFlashMessage('error', 'Pilih metode pembayaran');
        redirect('checkout.php');
    }

    $notes = isset($_POST['notes']) ? sanitize($_POST['notes']) : '';

    $orderData = [
        'user_id' => $_SESSION['user_id'],
        'total_price' => $grand_total,
        'shipping_address' => $shipping_address,
        'phone' => $phone,
        'notes' => $notes,
        'payment_method' => $payment_method
    ];

    $order_id = $orderModel->create(
        $_SESSION['user_id'],
        $cartItems,
        $grand_total,
        $shipping_address,
        $phone,
        $notes
    );

    if ($order_id) {
        $_SESSION['cart'] = [];
        $_SESSION['voucher'] = null;

        setFlashMessage('success', 'Pesanan Anda berhasil dibuat');
        redirect('order-success.php?id=' . $order_id);
    } else {
        setFlashMessage('error', 'Gagal membuat pesanan. Silakan coba lagi');
        redirect('checkout.php');
    }
}

$breadcrumb_items = [
    [
        'label' => 'Keranjang Belanja',
        'url' => 'cart.php'
    ],
    [
        'label' => 'Checkout'
    ]
];

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Checkout</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Form Checkout -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-semibold">Alamat Pengiriman</h2>
                </div>
                <div class="p-6">
                    <?php if (empty($addresses)): ?>
                        <div class="text-center py-4">
                            <p class="text-gray-500 mb-4">Anda belum memiliki alamat pengiriman</p>
                            <a href="customer/addresses.php" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg inline-flex items-center">
                                <i class="fas fa-plus mr-2"></i> Tambah Alamat Baru
                            </a>
                        </div>
                    <?php else: ?>
                        <form action="" method="POST" id="checkoutForm">
                            <div class="mb-6">
                                <?php foreach ($addresses as $address): ?>
                                    <div class="mb-2">
                                        <label class="flex items-start p-4 border rounded-lg <?php echo ($defaultAddress && $address['id'] == $defaultAddress['id']) ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-blue-300'; ?> cursor-pointer transition-colors">
                                            <input type="radio" name="address_id" value="<?php echo $address['id']; ?>" <?php echo ($defaultAddress && $address['id'] == $defaultAddress['id']) ? 'checked' : ''; ?> class="mt-1 mr-3">
                                            <div>
                                                <div class="font-medium"><?php echo htmlspecialchars($address['recipient_name']); ?></div>
                                                <div class="text-sm text-gray-600"><?php echo htmlspecialchars($address['phone']); ?></div>
                                                <div class="text-sm mt-1">
                                                    <?php echo htmlspecialchars($address['full_address']); ?>,
                                                    <?php echo htmlspecialchars($address['district']); ?>,
                                                    <?php echo htmlspecialchars($address['city']); ?>,
                                                    <?php echo htmlspecialchars($address['province']); ?>
                                                    <?php echo htmlspecialchars($address['postal_code']); ?>
                                                </div>
                                                <?php if ($address['is_default']): ?>
                                                    <div class="inline-block mt-1 px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">Alamat Utama</div>
                                                <?php endif; ?>
                                            </div>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                                <div class="mt-2">
                                    <a href="customer/addresses.php" class="text-blue-600 hover:text-blue-800 text-sm inline-flex items-center">
                                        <i class="fas fa-plus-circle mr-1"></i> Tambah Alamat Baru
                                    </a>
                                </div>
                            </div>

                            <!-- Metode Pembayaran -->
                            <div class="mb-6">
                                <h3 class="text-md font-semibold mb-3">Metode Pembayaran</h3>
                                <div class="space-y-2">
                                    <label class="flex items-center p-4 border rounded-lg border-gray-200 hover:border-blue-300 cursor-pointer transition-colors">
                                        <input type="radio" name="payment_method" value="transfer_bank" checked class="mr-3">
                                        <div>
                                            <div class="font-medium">Transfer Bank</div>
                                            <div class="text-sm text-gray-600">Pembayaran manual melalui transfer bank</div>
                                        </div>
                                    </label>
                                    <label class="flex items-center p-4 border rounded-lg border-gray-200 hover:border-blue-300 cursor-pointer transition-colors">
                                        <input type="radio" name="payment_method" value="cod" class="mr-3">
                                        <div>
                                            <div class="font-medium">Cash on Delivery (COD)</div>
                                            <div class="text-sm text-gray-600">Bayar saat barang diterima</div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Catatan Pesanan -->
                            <div class="mb-6">
                                <h3 class="text-md font-semibold mb-2">Catatan Pesanan (Opsional)</h3>
                                <textarea name="notes" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" placeholder="Tambahkan catatan untuk pesanan Anda"></textarea>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Ringkasan Pesanan -->
        <div>
            <div class="bg-white rounded-lg shadow-md overflow-hidden sticky top-4">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-semibold">Ringkasan Pesanan</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-3 mb-6">
                        <?php foreach ($cartItems as $item): ?>
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-16 h-16 bg-gray-100 rounded overflow-hidden">
                                    <img src="<?php echo !empty($item['image']) ? BASE_URL . 'assets/images/products/' . $item['image'] : BASE_URL . 'assets/images/products/default-product.jpg'; ?>"
                                        class="w-full h-full object-cover">
                                </div>
                                <div class="ml-4 flex-grow">
                                    <div class="font-medium"><?php echo htmlspecialchars($item['name']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo $item['quantity']; ?> x <?php echo formatRupiah($item['price']); ?></div>
                                </div>
                                <div class="ml-2 text-right">
                                    <div class="font-medium"><?php echo formatRupiah($item['total']); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="border-t border-gray-200 pt-4">
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="font-medium"><?php echo formatRupiah($subtotal); ?></span>
                        </div>
                        <?php if ($discount > 0): ?>
                            <div class="flex justify-between mb-2">
                                <span class="text-gray-600">Diskon</span>
                                <span class="font-medium text-red-600">-<?php echo formatRupiah($discount); ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-600">Pengiriman</span>
                            <span class="font-medium"><?php echo $shipping_cost > 0 ? formatRupiah($shipping_cost) : 'Gratis'; ?></span>
                        </div>
                        <div class="flex justify-between border-t border-gray-200 mt-3 pt-3">
                            <span class="font-semibold">Total</span>
                            <span class="font-bold text-xl"><?php echo formatRupiah($grand_total); ?></span>
                        </div>
                    </div>

                    <div class="mt-6">
                        <?php if (!empty($addresses)): ?>
                            <button type="submit" form="checkoutForm" class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-4 rounded-lg inline-flex items-center justify-center">
                                <i class="fas fa-check mr-2"></i> Buat Pesanan
                            </button>
                        <?php else: ?>
                            <a href="customer/addresses.php" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg inline-flex items-center justify-center">
                                <i class="fas fa-plus-circle mr-2"></i> Tambah Alamat Terlebih Dahulu
                            </a>
                        <?php endif; ?>
                        <a href="cart.php" class="mt-3 w-full bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-2 px-4 rounded-lg inline-flex items-center justify-center">
                            <i class="fas fa-arrow-left mr-2"></i> Kembali ke Keranjang
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>