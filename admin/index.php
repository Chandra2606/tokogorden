<?php
$active_page = 'dashboard';
$page_title = 'Dashboard Admin';

require_once '../config/config.php';
require_once '../lib/models/Product.php';
require_once '../lib/models/Order.php';
require_once '../lib/models/User.php';
require_once '../lib/models/Review.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$productModel = new Product($conn);
$orderModel = new Order($conn);
$userModel = new User($conn);
$reviewModel = new Review($conn);

$totalProducts = $productModel->countAll();
$totalOrders = $orderModel->countAll();
$totalUsers = $userModel->countAll();
$totalRevenue = $orderModel->getTotalRevenue();
$recentOrders = $orderModel->getRecent(5);
$topProducts = $productModel->getTopSelling(5);
$latestReviews = $reviewModel->getAll(5);

$monthlySales = $orderModel->getMonthlySalesData();
$monthLabels = json_encode(array_column($monthlySales, 'month'));
$salesData = json_encode(array_column($monthlySales, 'total'));

include 'inc/header.php';
?>

<h1 class="text-2xl font-semibold text-gray-900 mb-6">Dashboard</h1>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 text-blue-500 mr-4">
                <i class="fas fa-box text-xl"></i>
            </div>
            <div>
                <div class="text-sm text-gray-500">Total Produk</div>
                <div class="text-xl font-semibold"><?php echo $totalProducts; ?></div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 text-green-500 mr-4">
                <i class="fas fa-shopping-cart text-xl"></i>
            </div>
            <div>
                <div class="text-sm text-gray-500">Total Pesanan</div>
                <div class="text-xl font-semibold"><?php echo $totalOrders; ?></div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-100 text-yellow-500 mr-4">
                <i class="fas fa-users text-xl"></i>
            </div>
            <div>
                <div class="text-sm text-gray-500">Total Pengguna</div>
                <div class="text-xl font-semibold"><?php echo $totalUsers; ?></div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-purple-100 text-purple-500 mr-4">
                <i class="fas fa-money-bill-wave text-xl"></i>
            </div>
            <div>
                <div class="text-sm text-gray-500">Total Pendapatan</div>
                <div class="text-xl font-semibold"><?php echo formatRupiah($totalRevenue); ?></div>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
    <div class="xl:col-span-2 bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold mb-4">Penjualan Bulanan</h2>
        <div>
            <canvas id="salesChart" height="300"></canvas>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold mb-4">Top Produk</h2>
        <?php if (count($topProducts) > 0): ?>
            <div class="space-y-4">
                <?php foreach ($topProducts as $product): ?>
                    <div class="flex items-center">
                        <div class="h-10 w-10 bg-gray-100 rounded-md overflow-hidden mr-3">
                            <img src="<?php echo !empty($product['image']) ? BASE_URL . 'assets/images/products/' . $product['image'] : BASE_URL . 'assets/images/products/default-product.jpg'; ?>"
                                alt="<?php echo $product['name']; ?>"
                                class="h-full w-full object-cover">
                        </div>
                        <div class="flex-1">
                            <div class="text-sm font-medium"><?php echo $product['name']; ?></div>
                            <div class="text-xs text-gray-500"><?php echo formatRupiah($product['price']); ?></div>
                        </div>
                        <div class="bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-1 rounded">
                            <?php echo $product['total_sold']; ?> terjual
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-gray-500 text-sm">Belum ada data penjualan produk.</p>
        <?php endif; ?>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">Pesanan Terbaru</h2>
            <a href="<?php echo BASE_URL . 'admin/orders/index.php'; ?>" class="text-blue-600 hover:text-blue-800 text-sm">
                Lihat Semua
            </a>
        </div>
        <?php if (count($recentOrders) > 0): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pelanggan</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                    <a href="<?php echo BASE_URL . 'admin/orders/detail.php?id=' . $order['id']; ?>" class="text-blue-600 hover:text-blue-800">
                                        #<?php echo $order['id']; ?>
                                    </a>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo $order['user_name']; ?>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo formatRupiah($order['total_price']); ?>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    <?php
                                    $statusClass = '';
                                    switch ($order['status']) {
                                        case 'pending':
                                            $statusClass = 'bg-yellow-100 text-yellow-800';
                                            break;
                                        case 'processing':
                                            $statusClass = 'bg-blue-100 text-blue-800';
                                            break;
                                        case 'shipped':
                                            $statusClass = 'bg-indigo-100 text-indigo-800';
                                            break;
                                        case 'delivered':
                                            $statusClass = 'bg-green-100 text-green-800';
                                            break;
                                        case 'completed':
                                            $statusClass = 'bg-green-100 text-green-800';
                                            break;
                                        case 'cancelled':
                                            $statusClass = 'bg-red-100 text-red-800';
                                            break;
                                    }
                                    $statusLabels = [
                                        'pending' => 'Pending',
                                        'processing' => 'Diproses',
                                        'shipped' => 'Dikirim',
                                        'delivered' => 'Diterima',
                                        'completed' => 'Selesai',
                                        'cancelled' => 'Dibatalkan'
                                    ];
                                    ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                                        <?php echo $statusLabels[$order['status']]; ?>
                                    </span>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('d/m/Y', strtotime($order['created_at'])); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-gray-500 text-sm">Belum ada pesanan.</p>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">Ulasan Terbaru</h2>
            <a href="<?php echo BASE_URL . 'admin/reviews/index.php'; ?>" class="text-blue-600 hover:text-blue-800 text-sm">
                Lihat Semua
            </a>
        </div>
        <?php if (count($latestReviews) > 0): ?>
            <div class="space-y-4">
                <?php foreach ($latestReviews as $review): ?>
                    <div class="border-b border-gray-200 pb-4 last:border-b-0 last:pb-0">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <div class="font-medium"><?php echo $review['user_name']; ?></div>
                                <div class="text-xs text-gray-500"><?php echo date('d M Y', strtotime($review['created_at'])); ?></div>
                            </div>
                            <div class="flex">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php if ($i <= $review['rating']): ?>
                                        <i class="fas fa-star text-yellow-400"></i>
                                    <?php else: ?>
                                        <i class="far fa-star text-yellow-400"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <div class="text-sm text-gray-600 mb-2">
                            <?php
                            $reviewText = isset($review['review']) ? $review['review'] : (isset($review['comment']) ? $review['comment'] : '');
                            echo substr($reviewText, 0, 150) . (strlen($reviewText) > 150 ? '...' : '');
                            ?>
                        </div>
                        <div class="text-xs">
                            <span class="font-medium">Produk: </span>
                            <a href="<?php echo BASE_URL . 'product.php?slug=' . $review['product_slug']; ?>" class="text-blue-600 hover:text-blue-800">
                                <?php echo $review['product_name']; ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-gray-500 text-sm">Belum ada ulasan produk.</p>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('salesChart').getContext('2d');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo $monthLabels; ?>,
                datasets: [{
                    label: 'Penjualan Bulanan',
                    data: <?php echo $salesData; ?>,
                    backgroundColor: 'rgba(59, 130, 246, 0.2)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 2,
                    tension: 0.3,
                    pointBackgroundColor: 'rgba(59, 130, 246, 1)',
                    pointBorderWidth: 2,
                    pointRadius: 4
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                            }
                        }
                    }
                },
                responsive: true,
                maintainAspectRatio: false
            }
        });
    });
</script>

<?php include 'inc/footer.php'; ?>