<?php
$brandName = $brandName ?? 'Green Store';
$tagline = $tagline ?? 'Fresh finds delivered to your door.';

// Auto base path: if current script is under /pages/, go up one level
$appBase = $appBase ?? '/ecommerce-projectphp/';
$homeUrl = ($appBase . 'index.php');
$cartUrl = $cartUrl ?? ($appBase . 'pages/cart.php');
$adminUrl = $adminUrl ?? ($appBase . 'admin/login.php');
$loginUrl = $loginUrl ?? ($appBase . 'pages/login.php');
$logoutUrl = $logoutUrl ?? ($appBase . 'pages/logout.php');
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
    $navPath = parse_url($navPath, PHP_URL_PATH) ?? $navPath;
    if ($currentPath === $navPath) {
        return 'text-green-200';
    }

    // Only fall back to basename matching for plain filenames (no directory).
    if (strpos($navPath, '/') === false) {
        $currentBase = basename($currentPath);
        $navBase = basename($navPath);
        return $currentBase === $navBase ? 'text-green-200' : 'hover:text-green-200';
    }

    return 'hover:text-green-200';
}
?>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="/ecommerce-projectphp/assets/css/styles.css">

<header class="bg-green-500 text-white py-4">
    <div class="container mx-auto px-4 md:flex md:items-center md:justify-between">
        <div class="flex w-full items-center justify-between gap-3 md:w-auto">
            <div class="flex items-center">
                <img src="/ecommerce-projectphp/assets/logo.png" alt="Logo" class="h-10 w-30 rounded-full mr-3">
            </div>
            <button type="button" id="mobile-nav-toggle" class="inline-flex items-center justify-center rounded-lg border border-white/40 px-3 py-2 text-sm font-semibold text-white hover:bg-white/10 md:hidden" aria-controls="site-nav" aria-expanded="false">
                Menu
            </button>
        </div>
        <nav id="site-nav" class="hidden flex-col gap-3 pt-4 md:ml-auto md:flex md:flex-row md:items-center md:gap-4 md:pt-0">
            <?php
            $homeActive = isActive($homeUrl, $currentPath);
            $cartActive = isActive($cartUrl, $currentPath);
            $adminActive = isActive($adminUrl, $currentPath);
            $loginActive = isActive($loginUrl, $currentPath);
            $registerActive = isActive($registerUrl, $currentPath);
            ?>
            <a href="<?php echo htmlspecialchars($homeUrl); ?>" class="text-white <?php echo $homeActive === 'text-green-200' ? 'text-green-200 pointer-events-none opacity-80' : 'hover:text-gray-200'; ?>" <?php echo $homeActive === 'text-green-200' ? 'aria-current="page" aria-disabled="true"' : ''; ?>>Home</a>
            <a href="<?php echo htmlspecialchars($cartUrl); ?>" class="text-white <?php echo $cartActive === 'text-green-200' ? 'text-green-200 pointer-events-none opacity-80' : 'hover:text-gray-200'; ?>" <?php echo $cartActive === 'text-green-200' ? 'aria-current="page" aria-disabled="true"' : ''; ?>>Cart</a>
            <a href="<?php echo htmlspecialchars($adminUrl); ?>" class="text-white <?php echo $adminActive === 'text-green-200' ? 'text-green-200 pointer-events-none opacity-80' : 'hover:text-gray-200'; ?>" <?php echo $adminActive === 'text-green-200' ? 'aria-current="page" aria-disabled="true"' : ''; ?>>Admin</a>
            <?php if (!empty($isLoggedIn)): ?>
                <span class="text-white">Hi, <?php echo htmlspecialchars($userName ?? ''); ?></span>
                <a href="<?php echo htmlspecialchars($logoutUrl); ?>" class="text-white hover:text-gray-200">Logout</a>
            <?php else: ?>
                <a href="<?php echo htmlspecialchars($loginUrl); ?>" class="text-white <?php echo $loginActive === 'text-green-200' ? 'text-green-200 pointer-events-none opacity-80' : 'hover:text-gray-200'; ?>" <?php echo $loginActive === 'text-green-200' ? 'aria-current="page" aria-disabled="true"' : ''; ?>>Login</a>
                <a href="<?php echo htmlspecialchars($registerUrl); ?>" class="inline-block bg-white text-green-700 px-4 py-2 rounded-lg font-semibold <?php echo $registerActive === 'text-green-200' ? 'pointer-events-none opacity-80' : 'hover:bg-black hover:text-white'; ?>" <?php echo $registerActive === 'text-green-200' ? 'aria-current="page" aria-disabled="true"' : ''; ?>>Register</a>
            <?php endif; ?>
        </nav>
    </div>
    <?php if ($showHero): ?>
        <div class="container mx-auto px-4 pb-8 pt-4">
            <div class="rounded-2xl bg-white/10 p-6 md:p-10">
                <h2 class="text-2xl md:text-3xl font-bold text-green-100"><?php echo htmlspecialchars($heroTitle); ?></h2>
                <p class="mt-2 text-green-100 max-w-xl"><?php echo htmlspecialchars($heroSubtitle); ?></p>
                <div class="mt-4 flex flex-wrap gap-3">
                    <a href="<?php echo htmlspecialchars($heroPrimaryUrl); ?>" class="bg-white text-green-700 px-4 py-2 rounded-lg font-semibold hover:bg-black hover:text-white"><?php echo htmlspecialchars($heroPrimaryLabel); ?></a>
                    <a href="<?php echo htmlspecialchars($heroSecondaryUrl); ?>" class="bg-white text-green-700 px-4 py-2 rounded-lg font-semibold hover:bg-black hover:text-white"><?php echo htmlspecialchars($heroSecondaryLabel); ?></a>
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

        <form method="post" action="<?php echo htmlspecialchars($registerUrl); ?>" id="register-form">
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
    const mobileNavToggle = document.getElementById('mobile-nav-toggle');
    const siteNav = document.getElementById('site-nav');
    if (mobileNavToggle && siteNav) {
        mobileNavToggle.addEventListener('click', function() {
            const isHidden = siteNav.classList.contains('hidden');
            if (isHidden) {
                siteNav.classList.remove('hidden');
                siteNav.classList.add('flex');
                mobileNavToggle.setAttribute('aria-expanded', 'true');
            } else {
                siteNav.classList.add('hidden');
                siteNav.classList.remove('flex');
                mobileNavToggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

    const openRegisterButton = document.getElementById('open-register-btn');
    if (openRegisterButton) {
        openRegisterButton.addEventListener('click', function() {
            document.getElementById('register-modal').classList.remove('hidden');
        });
    }
    document.querySelectorAll('.modal-close').forEach(function(btn) {
        btn.addEventListener('click', function() {
            btn.closest('.modal').classList.add('hidden');
        });
    });
</script>