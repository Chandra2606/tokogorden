    </main>
    <footer class="bg-blue-900 text-white pt-10 pb-6 mt-auto">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-semibold mb-4">Toko Gorden</h3>
                    <p class="text-blue-200 mb-4">Pusat penjualan gorden berkualitas dengan berbagai pilihan design dan warna untuk mempercantik ruangan Anda.</p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-white hover:text-blue-200">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="text-white hover:text-blue-200">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="text-white hover:text-blue-200">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-white hover:text-blue-200">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>
                <div>
                    <h3 class="text-xl font-semibold mb-4">Kategori</h3>
                    <ul class="space-y-2">
                        <?php
                        $categoryModel = new Category($conn);
                        $categories = $categoryModel->getAll();
                        foreach ($categories as $category):
                        ?>
                            <li>
                                <a href="<?php echo BASE_URL . 'category.php?slug=' . $category['slug']; ?>" class="text-blue-200 hover:text-white">
                                    <?php echo $category['name']; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div>
                    <h3 class="text-xl font-semibold mb-4">Informasi</h3>
                    <ul class="space-y-2">
                        <li>
                            <a href="<?php echo BASE_URL . 'about.php'; ?>" class="text-blue-200 hover:text-white">Tentang Kami</a>
                        </li>
                        <li>
                            <a href="<?php echo BASE_URL . 'contact.php'; ?>" class="text-blue-200 hover:text-white">Kontak</a>
                        </li>
                        <li>
                            <a href="<?php echo BASE_URL . 'shipping.php'; ?>" class="text-blue-200 hover:text-white">Informasi Pengiriman</a>
                        </li>
                        <li>
                            <a href="<?php echo BASE_URL . 'privacy.php'; ?>" class="text-blue-200 hover:text-white">Kebijakan Privasi</a>
                        </li>
                        <li>
                            <a href="<?php echo BASE_URL . 'terms.php'; ?>" class="text-blue-200 hover:text-white">Syarat & Ketentuan</a>
                        </li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-xl font-semibold mb-4">Kontak Kami</h3>
                    <ul class="space-y-2">
                        <li class="flex items-start space-x-2">
                            <i class="fas fa-map-marker-alt mt-1"></i>
                            <span>Jl. Gorden Indah No. 123, Jakarta Selatan, Indonesia</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <i class="fas fa-phone-alt mt-1"></i>
                            <span>+62 812 3456 7890</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <i class="fas fa-envelope mt-1"></i>
                            <span>info@tokogorden.com</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <i class="fas fa-clock mt-1"></i>
                            <span>Senin - Sabtu: 08:00 - 20:00</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-blue-800 mt-8 pt-6">
                <div class="flex flex-col md:flex-row md:justify-between items-center">
                    <p class="text-blue-200">&copy; <?php echo date('Y'); ?> Toko Gorden. Hak Cipta Dilindungi.</p>
                    <div class="mt-4 md:mt-0">
                        <div class="flex space-x-4">
                            <img src="<?php echo BASE_URL; ?>assets/images/payment-visa.png" alt="Visa" class="h-8">
                            <img src="<?php echo BASE_URL; ?>assets/images/payment-mastercard.png" alt="Mastercard" class="h-8">
                            <img src="<?php echo BASE_URL; ?>assets/images/payment-paypal.png" alt="Paypal" class="h-8">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    </body>

    </html>