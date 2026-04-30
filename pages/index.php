<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

$products = fetchProducts(9);

$isLoggedIn = isUserLoggedIn();
$userName = getLoggedInUserName();
$redirectUrl = $_SERVER['REQUEST_URI'] ?? '/pages/index.php';
$brandName = 'Green Store';
$tagline = 'Fresh finds delivered to your door.';
$homeUrl = 'index.php';
$cartUrl = 'cart.php';
$adminUrl = '../admin/login.php';
$loginUrl = 'login.php?redirect=' . urlencode($redirectUrl);
$logoutUrl = 'logout.php';
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
    <title>Home - GreenStore</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body class="bg-white text-black">
    <?php require_once __DIR__ . '/../includes/layout/header.php'; ?>
    <main class="container mx-auto px-4 py-10">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-semibold">Featured Products</h2>
            <span class="text-sm text-gray-500">Top picks just for you</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
            <?php if (empty($products)): ?>
                <p class="text-gray-600">No products available yet.</p>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <div class="border border-gray-200 bg-white rounded-2xl p-5 shadow-sm hover:shadow-md transition">
                        <div class="product-media">
                            <?php if (!empty($product['image'])): ?>
                                <img src="<?php echo htmlspecialchars(getProductImageUrl($product['image'], '../')); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php else: ?>
                                <span>Product</span>
                            <?php endif; ?>
                        </div>
                        <h3 class="font-bold mt-4"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="mt-2 text-sm text-gray-600"><?php echo htmlspecialchars($product['description']); ?></p>
                        <p class="mt-4 text-lg font-semibold text-green-600">$<?php echo number_format($product['price'], 2); ?></p>
                        <?php if ($showSamples): ?>
                            <form action="cart.php" method="post" class="mt-4">
                                <input type="hidden" name="action" value="add_sample">
                                <input type="hidden" name="sample_id" value="<?php echo htmlspecialchars($product['sample_id']); ?>">
                                <input type="hidden" name="name" value="<?php echo htmlspecialchars($product['name']); ?>">
                                <input type="hidden" name="price" value="<?php echo htmlspecialchars($product['price']); ?>">
                                <input type="hidden" name="quantity" value="1">
                                <?php if ($isLoggedIn): ?>
                                    <button type="submit" class="bg-gray-200 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-300">Add Sample to Cart</button>
                                <?php else: ?>
                                    <button type="button" data-login-required="true" class="bg-gray-200 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-300">Add Sample to Cart</button>
                                <?php endif; ?>
                            </form>
                        <?php else: ?>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <a href="product.php?id=<?php echo $product['id']; ?>" class="bg-green-500 text-white py-2 px-4 rounded-lg hover:bg-green-600">View Product</a>
                                <form action="cart.php" method="post">
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
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
    <?php require_once __DIR__ . '/../includes/layout/footer.php'; ?>

    <?php if (!$isLoggedIn): ?>
        <div id="login-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold">Login Required</h3>
                    <button type="button" data-login-close class="text-gray-400 hover:text-gray-600">✕</button>
                </div>
                <p class="text-gray-600 mb-4">Please sign in to add items to your cart.</p>
                <form method="POST" action="login.php" class="space-y-4">
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