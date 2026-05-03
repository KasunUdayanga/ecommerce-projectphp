<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Fetch product ID from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch product details from the database
$product = getProductById($product_id);

$isLoggedIn = isUserLoggedIn();
$userName = getLoggedInUserName();
$redirectUrl = $_SERVER['REQUEST_URI'] ?? '/pages/product.php';
$brandName = 'Green Store';
$tagline = 'Fresh finds delivered to your door.';
$homeUrl = 'index.php';
$cartUrl = 'cart.php';
$adminUrl = '../admin/login.php';
$loginUrl = 'login.php?redirect=' . urlencode($redirectUrl);
$logoutUrl = 'logout.php';
$showHero = false;

if (!$product) {
    echo "Product not found.";
    exit;
}

$descriptionText = trim((string) ($product['description'] ?? ''));
$descriptionWords = preg_split('/\s+/', $descriptionText, -1, PREG_SPLIT_NO_EMPTY);
$descriptionPreview = $descriptionText;
if (is_array($descriptionWords) && count($descriptionWords) > 20) {
    $descriptionPreview = implode(' ', array_slice($descriptionWords, 0, 20)) . '...';
}
$stock = isset($product['stock']) ? (int) $product['stock'] : 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="/ecommerce-projectphp/assets/css/styles.css" rel="stylesheet">
    <title><?php echo htmlspecialchars($product['name']); ?></title>
    <link rel="icon" href="/ecommerce-projectphp/assets/titlelog.png" type="image/png">
</head>

<body class="bg-white text-black">
    <?php require_once __DIR__ . '/../includes/layout/header.php'; ?>
    <div class="container mx-auto p-6">
        <a href="../index.php" class="text-sm text-green-600 hover:text-green-700">← Back to Products</a>
        <div class="mt-4 grid gap-8 lg:grid-cols-2">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="product-media">
                    <?php if (!empty($product['image'])): ?>
                        <img src="<?php echo htmlspecialchars(getProductImageUrl($product['image'], '../')); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <?php else: ?>
                        <span>Product Image</span>
                    <?php endif; ?>
                </div>
            </div>
            <div>
                <h1 class="text-3xl font-bold mb-2"><?php echo htmlspecialchars($product['name']); ?></h1>
                <p class="text-gray-600 mb-2"><?php echo htmlspecialchars($descriptionPreview); ?></p>
                <p class="text-2xl font-semibold text-green-600 mb-2">LKR<?php echo number_format($product['price'], 2); ?></p>
                <p class="text-sm text-gray-700 mb-6"><?php echo $stock > 0 ? $stock . ' in stock' : 'Out of stock'; ?></p>
                <form action="cart.php" method="post" class="flex flex-wrap gap-3">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <input type="hidden" name="quantity" value="1">
                    <?php if ($isLoggedIn): ?>
                        <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600">Add to Cart</button>
                    <?php else: ?>
                        <button type="button" data-login-required="true" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600">Add to Cart</button>
                    <?php endif; ?>
                    <a href="cart.php" class="border border-gray-300 px-4 py-2 rounded-lg hover:bg-gray-50">View Cart</a>
                </form>
            </div>
        </div>
    </div>

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