<?php
$active_page = 'users';
$page_title = 'Kelola Pengguna';

require_once '../../config/config.php';
require_once '../../lib/models/User.php';
require_once '../../lib/models/Order.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$userModel = new User($conn);
$orderModel = new Order($conn);

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Filter berdasarkan role
$role = isset($_GET['role']) && !empty($_GET['role']) ? sanitize($_GET['role']) : null;

// Pencarian
$search = isset($_GET['search']) && !empty($_GET['search']) ? sanitize($_GET['search']) : null;

// Mendapatkan semua pengguna
// Pada implementasi nyata, Anda perlu menambahkan fungsi search dan filter di model User
$users = $userModel->getAll();
$totalUsers = $userModel->countAll();

include '../inc/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Kelola Pengguna</h1>
    <a href="create.php" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded inline-flex items-center">
        <i class="fas fa-plus mr-2"></i> Tambah Pengguna
    </a>
</div>

<div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
    <div class="p-4 border-b border-gray-200">
        <form action="" method="GET" class="flex flex-wrap gap-4">
            <div>
                <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                <select id="role" name="role" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Role</option>
                    <option value="admin" <?php echo $role === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    <option value="customer" <?php echo $role === 'customer' ? 'selected' : ''; ?>>Customer</option>
                </select>
            </div>

            <div class="flex-1">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                <input type="text" id="search" name="search" value="<?php echo $search; ?>" placeholder="Cari berdasarkan nama atau email..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="flex items-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">
                    <i class="fas fa-search mr-2"></i> Cari
                </button>

                <?php if ($role || $search): ?>
                    <a href="index.php" class="ml-2 text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times-circle"></i> Reset
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="p-4 text-sm text-green-700 bg-green-100 rounded-lg">
            <?php
            echo $_SESSION['success_message'];
            unset($_SESSION['success_message']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="p-4 text-sm text-red-700 bg-red-100 rounded-lg">
            <?php
            echo $_SESSION['error_message'];
            unset($_SESSION['error_message']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($users)): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kontak</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Daftar</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-500">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900"><?php echo $user['name']; ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo $user['email']; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($user['role'] === 'admin'): ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                        Admin
                                    </span>
                                <?php else: ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Customer
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <?php if (!empty($user['phone'])): ?>
                                        <div><i class="fas fa-phone text-gray-400 mr-1"></i> <?php echo $user['phone']; ?></div>
                                    <?php else: ?>
                                        <div class="text-gray-400">No phone</div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500"><?php echo date("d M Y", strtotime($user['created_at'])); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="edit.php?id=<?php echo $user['id']; ?>" class="text-blue-600 hover:text-blue-900" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <a href="delete.php?id=<?php echo $user['id']; ?>"
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?');"
                                            class="text-red-600 hover:text-red-900" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination (akan diimplementasikan jika jumlah data banyak) -->
        <?php if ($totalUsers > $limit): ?>
            <div class="px-6 py-4 bg-gray-50">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-500">
                        Menampilkan <?php echo ($page - 1) * $limit + 1; ?> sampai <?php echo min($page * $limit, $totalUsers); ?> dari <?php echo $totalUsers; ?> pengguna
                    </div>
                    <div class="flex space-x-1">
                        <?php
                        $totalPages = ceil($totalUsers / $limit);
                        $startPage = max(1, $page - 2);
                        $endPage = min($totalPages, $page + 2);

                        // Query parameter untuk pagination
                        $queryParams = [];
                        if ($role) $queryParams[] = "role=" . urlencode($role);
                        if ($search) $queryParams[] = "search=" . urlencode($search);
                        $queryString = !empty($queryParams) ? '&' . implode('&', $queryParams) : '';

                        if ($page > 1):
                        ?>
                            <a href="?page=<?php echo $page - 1 . $queryString; ?>" class="px-3 py-1 rounded border border-gray-300 text-sm hover:bg-gray-100">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>

                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <a href="?page=<?php echo $i . $queryString; ?>" class="px-3 py-1 rounded border <?php echo $i == $page ? 'bg-blue-600 text-white' : 'border-gray-300 hover:bg-gray-100 text-gray-700'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo $page + 1 . $queryString; ?>" class="px-3 py-1 rounded border border-gray-300 text-sm hover:bg-gray-100">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="p-6 text-center">
            <p class="text-gray-500">Tidak ada pengguna yang ditemukan.</p>
            <?php if ($role || $search): ?>
                <a href="index.php" class="text-blue-600 hover:text-blue-800 mt-2 inline-block">
                    <i class="fas fa-times-circle mr-1"></i> Reset Filter
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../inc/footer.php'; ?>