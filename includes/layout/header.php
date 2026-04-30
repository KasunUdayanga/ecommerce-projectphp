<?php
$brandName = $brandName ?? 'Green Store';
$tagline = $tagline ?? 'Fresh finds delivered to your door.';

// Auto base path: if current script is under /pages/, go up one level
$appBase = $appBase ?? '/ecommerce-projectphp/';
$homeUrl = ($appBase . 'index.php');
$cartUrl = $cartUrl ?? ($appBase . 'pages/cart.php');
$adminUrl = $adminUrl ?? ($appBase . 'admin/login.php');
$loginUrl = $loginUrl ?? ($appBase . 'pages/login.php');
$logoutUrl =  ($appBase . 'pages/logout.php');
$registerUrl = $registerUrl ?? ($appBase . 'pages/register.php');
$showHero = $showHero ?? false;
$heroTitle = $heroTitle ?? 'Discover modern essentials';
$heroSubtitle = $heroSubtitle ?? 'Shop curated products, add them to your cart, and checkout in minutes.';
$heroPrimaryUrl = $heroPrimaryUrl ?? $homeUrl;
$heroPrimaryLabel = $heroPrimaryLabel ?? 'Browse Products';
$heroSecondaryUrl = $heroSecondaryUrl ?? $cartUrl;
$heroSecondaryLabel = $heroSecondaryLabel ?? 'View Cart';

// Determine current page for active nav highlighting
$scriptPath = $_SERVER['SCRIPT_NAME'];
// Normalize to the relative path used in URLs (strip leading / if present)
$currentPath = ltrim($scriptPath, '/');

function isActive($navUrl, $currentPath): string
{
    // Strip any leading / from nav URL for comparison
    $navPath = ltrim($navUrl, '/');
    return $currentPath === $navPath ? 'text-green-200 underline' : 'hover:text-green-200';
}
?><script src="https://cdn.tailwindcss.com"></script>

<header class="bg-green-500 text-white py-4">
    <div class="container mx-auto flex items-center justify-between">
        <div class="flex items-center">
            <img src="/ecommerce-projectphp/assets/logo.png" alt="Logo" class="h-10 w-30 rounded-full mr-3">
        </div>
        <nav class="flex space-x-4 items-center">
            <a href="<?php echo htmlspecialchars($homeUrl); ?>" class="text-white hover:text-gray-200">Home</a>
            <a href="<?php echo htmlspecialchars($cartUrl); ?>" class="text-white hover:text-gray-200">Cart</a>
            <a href="<?php echo htmlspecialchars($adminUrl); ?>" class="text-white hover:text-gray-200">Admin</a>
            <?php if (!empty($isLoggedIn)): ?>
                <span class="text-white">Hi, <?php echo htmlspecialchars($userName ?? ''); ?></span>
                <a href="<?php echo htmlspecialchars($logoutUrl); ?>" class="text-white hover:text-gray-200">Logout</a>
            <?php else: ?>
                <a href="<?php echo htmlspecialchars($loginUrl); ?>" class="text-white hover:text-gray-200">Login</a>
                <a href="<?php echo htmlspecialchars($registerUrl); ?>" class="bg-white text-green-500 px-4 py-2 rounded-lg hover:bg-gray-100">Register</a>
            <?php endif; ?>
        </nav>
    </div>
    <?php if ($showHero): ?>
        <div class="container mx-auto px-4 pb-8 pt-4">
            <div class="rounded-2xl bg-white/10 p-6 md:p-10">
                <h2 class="text-2xl md:text-3xl font-bold text-green-100"><?php echo htmlspecialchars($heroTitle); ?></h2>
                <p class="mt-2 text-green-100 max-w-xl"><?php echo htmlspecialchars($heroSubtitle); ?></p>
                <div class="mt-4 flex flex-wrap gap-3">
                    <a href="<?php echo htmlspecialchars($heroPrimaryUrl); ?>" class="bg-white text-green-700 px-4 py-2 rounded-lg font-semibold hover:bg-green-50"><?php echo htmlspecialchars($heroPrimaryLabel); ?></a>
                    <a href="<?php echo htmlspecialchars($heroSecondaryUrl); ?>" class="border border-white px-4 py-2 rounded-lg font-semibold hover:bg-white/10"><?php echo htmlspecialchars($heroSecondaryLabel); ?></a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</header>

<div id="register-modal" class="modal fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Create an account</h3>
            <button class="modal-close text-gray-600">&times;</button>
        </div>

        <form method="post" action="/pages/register.php" id="register-form">
            <?php if (!empty($_SESSION['register_error'])): ?>
                <p class="mb-3 text-red-600 text-sm text-center"><?php echo htmlspecialchars($_SESSION['register_error']); ?></p>
            <?php endif; ?>
            <input type="text" name="name" placeholder="Full name" required class="w-full mb-2 p-2 border rounded" />
            <input type="email" name="email" placeholder="Email" required class="w-full mb-2 p-2 border rounded" />
            <input type="password" name="password" placeholder="Password (min 6 characters)" required class="w-full mb-2 p-2 border rounded" />
            <input type="password" name="password_confirm" placeholder="Confirm password" required class="w-full mb-4 p-2 border rounded" />
            <div class="flex justify-end space-x-2">
                <button type="button" class="modal-close px-4 py-2 border rounded">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">Create account</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('open-register-btn').addEventListener('click', function() {
        document.getElementById('register-modal').classList.remove('hidden');
    });
    document.querySelectorAll('.modal-close').forEach(function(btn) {
        btn.addEventListener('click', function() {
            btn.closest('.modal').classList.add('hidden');
        });
    });
</script>