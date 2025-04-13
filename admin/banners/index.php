<?php
$active_page = 'banners';
$page_title = 'Kelola Banner';

require_once '../../config/config.php';
require_once '../../lib/models/Banner.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$bannerModel = new Banner($conn);
$banners = $bannerModel->getAll();

// Handle toggle status
if (isset($_GET['action']) && $_GET['action'] == 'toggle' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if ($bannerModel->toggleStatus($id)) {
        $_SESSION['success_message'] = 'Status banner berhasil diubah';
    } else {
        $_SESSION['error_message'] = 'Gagal mengubah status banner';
    }
    redirect('admin/banners/index.php');
}

include '../inc/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Kelola Banner</h1>
    <a href="create.php" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded inline-flex items-center">
        <i class="fas fa-plus mr-2"></i> Tambah Banner
    </a>
</div>

<div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg">
            <?php
            echo $_SESSION['success_message'];
            unset($_SESSION['success_message']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg">
            <?php
            echo $_SESSION['error_message'];
            unset($_SESSION['error_message']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($banners)): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Banner</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Detail</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prioritas</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($banners as $banner): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex-shrink-0 h-20 w-40">
                                    <img class="h-20 w-40 object-cover" src="<?php echo !empty($banner['image']) ? BASE_URL . 'assets/images/banners/' . $banner['image'] : BASE_URL . 'assets/images/default-banner.jpg'; ?>" alt="<?php echo $banner['title']; ?>">
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900"><?php echo $banner['title']; ?></div>
                                <div class="text-sm text-gray-500"><?php echo substr($banner['description'], 0, 100) . (strlen($banner['description']) > 100 ? '...' : ''); ?></div>
                                <?php if (!empty($banner['link'])): ?>
                                    <div class="text-sm text-blue-500">
                                        <a href="<?php echo $banner['link']; ?>" target="_blank" title="Buka link">
                                            <?php echo substr($banner['link'], 0, 40) . (strlen($banner['link']) > 40 ? '...' : ''); ?>
                                            <i class="fas fa-external-link-alt ml-1"></i>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($banner['is_active']): ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Aktif
                                    </span>
                                <?php else: ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Tidak Aktif
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo $banner['priority']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="edit.php?id=<?php echo $banner['id']; ?>" class="text-blue-600 hover:text-blue-900" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="?action=toggle&id=<?php echo $banner['id']; ?>" class="<?php echo $banner['is_active'] ? 'text-yellow-600 hover:text-yellow-900' : 'text-green-600 hover:text-green-900'; ?>" title="<?php echo $banner['is_active'] ? 'Nonaktifkan' : 'Aktifkan'; ?>">
                                        <i class="fas <?php echo $banner['is_active'] ? 'fa-ban' : 'fa-check-circle'; ?>"></i>
                                    </a>
                                    <a href="delete.php?id=<?php echo $banner['id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Apakah Anda yakin ingin menghapus banner ini?');" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="p-6 text-center">
            <p class="text-gray-500">Tidak ada banner yang ditemukan.</p>
        </div>
    <?php endif; ?>
</div>

<?php include '../inc/footer.php'; ?>