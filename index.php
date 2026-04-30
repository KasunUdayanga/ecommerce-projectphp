<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Fetch featured products
$products = fetchFeaturedProducts();
$isLoggedIn = isUserLoggedIn();
$userName = getLoggedInUserName() ?? '';
$redirectUrl = $_SERVER['REQUEST_URI'] ?? '/';
$brandName = 'Green Store';
$tagline = 'Fresh finds delivered to your door.';
$homeUrl = 'pages/index.php';
$cartUrl = 'pages/cart.php';
$adminUrl = 'admin/login.php';
$loginUrl = 'pages/login.php?redirect=' . urlencode($redirectUrl);
$logoutUrl = 'pages/logout.php';
$showHero = true;
$heroTitle = 'Discover modern essentials';
$heroSubtitle = 'Shop curated products, add them to your cart, and checkout in minutes.';
$heroPrimaryUrl = $homeUrl;
$heroPrimaryLabel = 'Browse Products';
$heroSecondaryUrl = $cartUrl;
$heroSecondaryLabel = 'View Cart';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($brandName); ?></title>
    <link rel="icon" href="/ecommerce-projectphp/assets/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body class="bg-white text-black font-poppins">
    <div class="page-container">
        <?php include 'includes/layout/header.php'; ?>

        <?php if ($showHero): ?>
            <section class="bg-green-500 text-white py-16">
                <div class="container mx-auto text-center">
                    <h1 class="text-4xl font-bold mb-4"><?php echo htmlspecialchars($heroTitle); ?></h1>
                    <p class="text-lg mb-6"><?php echo htmlspecialchars($heroSubtitle); ?></p>
                    <div class="flex justify-center space-x-4">
                        <a href="<?php echo htmlspecialchars($heroPrimaryUrl); ?>" class="bg-white text-green-500 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100">
                            <?php echo htmlspecialchars($heroPrimaryLabel); ?>
                        </a>
                        <a href="<?php echo htmlspecialchars($heroSecondaryUrl); ?>" class="bg-gray-100 text-green-500 px-6 py-3 rounded-lg font-semibold hover:bg-white">
                            <?php echo htmlspecialchars($heroSecondaryLabel); ?>
                        </a>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <main class="content container mx-auto px-4 py-10">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-semibold">Featured Products</h2>
                <span class="text-sm text-gray-500">Top picks just for you</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php foreach ($products as $product): ?>
                    <div class="border border-gray-200 bg-white p-5 rounded-2xl shadow-sm hover:shadow-md transition">
                        <div class="product-media">
                            <?php if (!empty($product['image'])): ?>
                                <img src="<?php echo htmlspecialchars(getProductImageUrl($product['image'])); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-48 object-cover rounded-lg">
                            <?php else: ?>
                                <img src="assets/images/placeholder.png" alt="No images available" class="w-full h-48 object-cover rounded-lg">
                            <?php endif; ?>
                        </div>
                        <h3 class="text-xl font-bold mt-4"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="text-sm text-gray-600 mt-2"><?php echo htmlspecialchars($product['description']); ?></p>
                        <p class="text-green-600 mt-4 font-semibold">LKR <?php echo number_format($product['price'], 2); ?></p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <a href="pages/product.php?id=<?php echo $product['id']; ?>"
                                class="bg-green-500 hover:text-white py-2 px-4 rounded-lg">
                                View Product
                            </a>
                            <form action="pages/cart.php" method="post">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <input type="hidden" name="quantity" value="1">
                                <?php if ($isLoggedIn): ?>
                                    <button type="submit" class="bg-black text-white py-2 px-4 rounded-lg hover:bg-gray-800">Add to Cart</button>
                                <?php else: ?>
                                    <button type="button" data-login-required="true" class="bg-black text-white py-2 px-4 rounded-lg hover:bg-gray-800">Add to Cart</button>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>

        <?php include 'includes/layout/footer.php'; ?>
    </div>

    <?php if (!$isLoggedIn): ?>
        <div id="login-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold">Login Required</h3>
                    <button type="button" data-login-close class="text-gray-400 hover:text-gray-600" aria-label="Close">✕</button>
                </div>
                <?php if (!empty($_SESSION['register_error'])): ?>
                    <p class="text-red-600 mb-4"><?php echo htmlspecialchars($_SESSION['register_error']); ?></p>
                    <?php unset($_SESSION['register_error']); ?>
                <?php else: ?>
                    <p class="text-gray-600 mb-4">Please sign in to add items to your cart.</p>
                <?php endif; ?>
                <form method="POST" action="pages/login.php" class="space-y-4">
                    <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirectUrl); ?>">
                    <div>
                        <label class="block mb-1 font-semibold" for="login-identifier">Email or Username</label>
                        <input class="w-full border border-gray-300 rounded-lg p-2" type="text" name="identifier" id="login-identifier" required>
                    </div>
                    <div>
                        <label class="block mb-1 font-semibold" for="login-password">Password</label>
                        <input class="w-full border border-gray-300 rounded-lg p-2" type="password" name="password" id="login-password" required>
                    </div>
                    <button type="submit" class="w-full bg-green-500 text-white py-2 rounded-lg hover:bg-green-600">Sign In</button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <script>
        const loginModal = document.getElementById('login-modal');
        if (loginModal) {
            const loginButtons = document.querySelectorAll('[data-login-required]');
            loginButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    loginModal.classList.remove('hidden');
                    loginModal.classList.add('flex');
                });
            });
            const closeButtons = loginModal.querySelectorAll('[data-login-close]');
            closeButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    loginModal.classList.add('hidden');
                    loginModal.classList.remove('flex');
                });
            });
            loginModal.addEventListener('click', (event) => {
                if (event.target === loginModal) {
                    loginModal.classList.add('hidden');
                    loginModal.classList.remove('flex');
                }
            });
        }
    </script>
</body>

</html>