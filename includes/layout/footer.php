<?php
$footerText = $footerText ?? ('© ' . date('Y') . ' Green Store. All rights reserved.');
?>

<footer class="bg-black text-white text-center py-6">
    <p><?php echo htmlspecialchars($footerText); ?></p>
</footer>