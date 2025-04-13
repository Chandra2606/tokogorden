<?php
$active_page = 'discounts';
$page_title = 'Kelola Diskon';

require_once '../../config/config.php';
require_once '../../lib/models/Discount.php';

// Instance model
$discountModel = new Discount($conn);

// Ambil semua diskon
$discounts = $discountModel->getAll();

// Include header
include '../inc/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Kelola Diskon</h1>
    <a href="create.php" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded inline-flex items-center">
        <i class="fas fa-plus mr-2"></i> Tambah Diskon
    </a>
</div>

<div class="bg-white shadow-md rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Diskon</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Periode</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (count($discounts) > 0): ?>
                    <?php foreach ($discounts as $discount): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $discount['id']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $discount['name']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php
                                $typeLabels = [
                                    'bundle' => 'Diskon Bundel',
                                    'product' => 'Diskon Produk',
                                    'voucher' => 'Kode Voucher',
                                    'time' => 'Diskon Waktu'
                                ];
                                echo isset($typeLabels[$discount['type']]) ? $typeLabels[$discount['type']] : $discount['type'];
                                ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php if ($discount['is_percentage']): ?>
                                    <?php echo $discount['value']; ?>%
                                <?php else: ?>
                                    <?php echo formatRupiah($discount['value']); ?>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo $discount['code'] ? $discount['code'] : '-'; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php if ($discount['start_date'] && $discount['end_date']): ?>
                                    <?php echo date('d/m/Y', strtotime($discount['start_date'])); ?> -
                                    <?php echo date('d/m/Y', strtotime($discount['end_date'])); ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($discount['active']): ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Aktif
                                    </span>
                                <?php else: ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Tidak Aktif
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div class="flex space-x-2">
                                    <a href="edit.php?id=<?php echo $discount['id']; ?>" class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="assign.php?id=<?php echo $discount['id']; ?>" class="text-green-600 hover:text-green-900" title="Assign ke Produk">
                                        <i class="fas fa-link"></i>
                                    </a>
                                    <a href="delete.php?id=<?php echo $discount['id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Yakin ingin menghapus diskon ini?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada data diskon</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../inc/footer.php'; ?>