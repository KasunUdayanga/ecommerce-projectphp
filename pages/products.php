<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

$products = fetchProducts(200);
$isLoggedIn = isUserLoggedIn();
$userName = getLoggedInUserName() ?? '';
$redirectUrl = $_SERVER['REQUEST_URI'] ?? '/pages/products.php';
$brandName = 'Green Store';
$tagline = 'Fresh finds delivered to your door.';
$homeUrl = 'index.php';
$cartUrl = 'cart.php';
$adminUrl = '../admin/login.php';
$loginUrl = 'login.php?redirect=' . urlencode($redirectUrl);
$logoutUrl = 'logout.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($brandName); ?> - All Products</title>
    <link rel="icon" href="/ecommerce-projectphp/assets/titlelog.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="../assets/css/styles.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body class="bg-white text-black font-poppins">
    <div class="page-container">
        <?php include __DIR__ . '/../includes/layout/header.php'; ?>

        <main class="content container mx-auto px-4 py-10">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-semibold">All Products</h2>
                <span class="text-sm text-gray-500">Browse everything</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php foreach ($products as $product): ?>
                    <div class="border border-gray-200 bg-white p-5 rounded-2xl shadow-sm hover:shadow-md transition">
                        <div class="product-media">
                            <?php if (!empty($product['image'])): ?>
                                <img src="<?php echo htmlspecialchars(getProductImageUrl($product['image'],'../')); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-48 object-cover rounded-lg">
                            <?php else: ?>
                                <img src="../assets/images/placeholder.png" alt="No images available" class="w-full h-48 object-cover rounded-lg">
                            <?php endif; ?>
                        </div>
                        <h3 class="text-xl font-bold mt-4"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="text-sm text-gray-600 mt-2"><?php echo htmlspecialchars($product['description']); ?></p>
                        <p class="text-green-600 mt-4 font-semibold">LKR <?php echo number_format($product['price'], 2); ?></p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <a href="product.php?id=<?php echo $product['id']; ?>" class="bg-green-500 text-white hover:text-white py-2 px-4 rounded-lg hover:bg-green-600">
                                View Product
                            </a>
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
                    </div>
                <?php endforeach; ?>
            </div>
        </main>

        <?php include __DIR__ . '/../includes/layout/footer.php'; ?>
    </div>

    <?php if (!$isLoggedIn): ?>
        <div id="login-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold">Login Required</h3>
                    <button type="button" data-login-close class="text-gray-400 hover:text-gray-600" aria-label="Close">✕</button>
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