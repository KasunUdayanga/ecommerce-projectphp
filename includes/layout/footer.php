<?php
$footerText = $footerText ?? ('© ' . date('Y') . ' Green Store. All rights reserved.');
?>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
<footer class="mt-auto bg-gray-900 text-white py-4">
    <div class="container mx-auto flex items-center justify-between">
        <div class="flex items-center gap-3">
            <img src="/ecommerce-projectphp/assets/logo.png" alt="Logo" class="h-10 w-30">
        </div>
        <span class="flex-1 text-center text-sm text-gray-400">
            <?php echo htmlspecialchars($footerText); ?>
        </span>
        <div class="flex items-center gap-4 text-gray-400">
            <a href="https://facebook.com" class="hover:text-green-500" target="_blank" rel="noopener noreferrer">
                <i class="fab fa-facebook-f"></i>
            </a>
            <a href="https://twitter.com" class="hover:text-green-500" target="_blank" rel="noopener noreferrer">
                <i class="fab fa-twitter"></i>
            </a>
            <a href="https://instagram.com" class="hover:text-green-500" target="_blank" rel="noopener noreferrer">
                <i class="fab fa-instagram"></i>
            </a>
        </div>
    </div>
</footer>