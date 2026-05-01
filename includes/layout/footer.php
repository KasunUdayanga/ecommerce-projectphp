<?php
$footerText = $footerText ?? ('© ' . date('Y') . ' Green Store. All rights reserved.');
?>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
<footer class="mt-auto bg-gray-900 text-white py-6 md:py-5">
    <div class="container mx-auto px-4 md:px-6">
        <!-- Main Footer Content -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-4 items-center">
            <!-- Logo Section -->
            <div class="flex items-center justify-center md:justify-start">
                <img src="/ecommerce-projectphp/assets/logo.png" alt="Logo" class="h-8 md:h-10 w-auto">
            </div>

            <!-- Copyright Text -->
            <span class="text-center text-xs md:text-sm text-gray-400 order-3 md:order-2 col-span-1 md:col-span-1">
                <?php echo htmlspecialchars($footerText); ?>
            </span>

            <!-- Social Icons -->
            <div class="flex items-center justify-center md:justify-end gap-4 md:gap-5 order-2 md:order-3 text-gray-400">
                <a href="https://facebook.com" class="hover:text-green-500 transition-colors duration-200 text-sm md:text-base" target="_blank" rel="noopener noreferrer" title="Facebook">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="https://twitter.com" class="hover:text-green-500 transition-colors duration-200 text-sm md:text-base" target="_blank" rel="noopener noreferrer" title="Twitter">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="https://instagram.com" class="hover:text-green-500 transition-colors duration-200 text-sm md:text-base" target="_blank" rel="noopener noreferrer" title="Instagram">
                    <i class="fab fa-instagram"></i>
                </a>
            </div>
        </div>
    </div>
</footer>