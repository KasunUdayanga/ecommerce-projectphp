<?php
session_start();
include_once __DIR__ . '/../includes/functions.php';

$isLoggedIn = isUserLoggedIn();
$userName = getLoggedInUserName();
$appBase = '/ecommerce-projectphp/';
$loginRedirect = $appBase . 'pages/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI'] ?? ($appBase . 'pages/cart.php'));
$brandName = 'Green Store';
$tagline = 'Fresh finds delivered to your door.';
$homeUrl = $appBase . 'index.php';
$cartUrl = $appBase . 'pages/cart.php';
$adminUrl = $appBase . 'admin/login.php';
$loginUrl = $loginRedirect;
$logoutUrl = $appBase . 'pages/logout.php';
$showHero = false;

// Initialize cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle cart updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'add';
    $productId = $_POST['product_id'] ?? '';
    $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 1;

    switch ($action) {
        case 'add':
            if (!$isLoggedIn) {
                header('Location: ' . $loginRedirect);
                exit;
            }
            addToCart((int) $productId, $quantity);
            break;
        case 'add_sample':
            if (!$isLoggedIn) {
                header('Location: ' . $loginRedirect);
                exit;
            }
            $sampleId = $_POST['sample_id'] ?? $productId;
            $name = $_POST['name'] ?? '';
            $price = $_POST['price'] ?? 0;
            addSampleToCart($sampleId, $name, $price, $quantity);
            break;
        case 'remove':
            removeFromCart($productId);
            break;
        case 'update':
            updateCart($productId, $quantity);
            break;
        default:
            addToCart((int) $productId, $quantity);
            break;
    }
}

$cartItems = getCartItems();
$totalPrice = calculateTotalPrice($cartItems);
$shippingFee = 250.00; // fixed shipping fee
$grandTotal = $totalPrice + $shippingFee;

function calculateTotalPrice($cartItems)
{
    $total = 0;
    foreach ($cartItems as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/ecommerce-projectphp/assets/css/styles.css">
    <title>Shopping Cart</title>
    <link rel="icon" href="/ecommerce-projectphp/assets/titlelog.png" type="image/png">
    <style>
        .soft-badge {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .3rem .7rem;
            border-radius: 9999px;
            font-size: .75rem;
            font-weight: 600;
            color: #166534;
            background: #dcfce7;
        }

        .cart-shell {
            border: 1px solid #dcfce7;
            border-radius: 1.2rem;
            background: linear-gradient(180deg, #f0fdf4 0%, #ffffff 45%);
            box-shadow: 0 10px 28px rgba(22, 163, 74, .08);
            padding: 1.25rem;
        }

        .cart-card {
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            background: #ffffff;
            overflow: hidden;
            transition: transform .3s ease, box-shadow .3s ease, border-color .3s ease;
            display: flex;
            flex-direction: column;
        }

        .cart-card:hover {
            transform: translateY(-4px);
            border-color: #86efac;
            box-shadow: 0 12px 28px rgba(22, 163, 74, .12);
        }

        .cart-image {
            overflow: hidden;
            border-radius: .8rem;
            background: #f8fafc;
        }

        .cart-image img {
            width: 100%;
            height: 11rem;
            object-fit: cover;
            transition: transform .45s ease;
        }

        .cart-card:hover .cart-image img {
            transform: scale(1.06);
        }

        .cart-summary {
            border: 1px solid #dcfce7;
            border-radius: 1.2rem;
            background: linear-gradient(180deg, #f0fdf4 0%, #ffffff 45%);
            box-shadow: 0 10px 28px rgba(22, 163, 74, .08);
            padding: 1.5rem;
        }

        @media (min-width: 768px) {
            .cart-card {
                flex-direction: row;
                align-items: flex-start;
                gap: 1rem;
            }

            .cart-card>.flex-grow {
                flex: 1 1 auto;
            }

            .cart-card form.mt-4 {
                width: 220px;
            }
        }
    </style>
</head>

<body class="bg-white text-black">
    <?php require_once __DIR__ . '/../includes/layout/header.php'; ?>
    <main class="container mx-auto px-4 py-10">
        <div class="flex flex-col gap-2 mb-6 md:flex-row md:items-center md:justify-between">
            <div>
                <span class="soft-badge">Cart Items</span>
                <h1 class="mt-2 text-3xl font-bold">Your Shopping Cart</h1>
            </div>
            <span class="text-sm text-gray-500">Manage your herbal selections</span>
        </div>

        <?php if (!$isLoggedIn): ?>
            <div class="mb-6 rounded-lg border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800">
                <p class="font-semibold mb-2">Login Required</p>
                <p>Please <a href="<?php echo htmlspecialchars($loginRedirect); ?>" class="font-semibold underline hover:text-yellow-900">log in to your account</a> to add items to your cart.</p>
            </div>
        <?php endif; ?>

        <?php if (empty($cartItems)): ?>
            <div class="cart-shell text-center py-12">
                <p class="text-xl text-gray-600 mb-4">Your cart is currently empty</p>
                <a href="<?php echo htmlspecialchars($homeUrl); ?>" class="inline-flex bg-green-500 text-white py-2 px-6 rounded-lg hover:bg-green-600 transition-colors">
                    Continue Shopping
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Cart Items Grid -->
                <div class="lg:col-span-2">
                    <div class="cart-shell">
                        <div class="grid grid-cols-1 gap-6">
                            <?php foreach ($cartItems as $item): ?>
                                <div class="cart-card p-4">
                                    <div class="flex flex-col flex-grow p-2">
                                        <h3 class="font-bold text-lg text-gray-800"><?php echo htmlspecialchars($item['name']); ?></h3>
                                        <p class="text-sm text-gray-600 mt-1">Unit Price: <span class="font-semibold text-green-600">LKR <?php echo number_format($item['price'], 2); ?></span></p>

                                        <div class="mt-4 flex items-center gap-2">
                                            <form method="POST" action="cart.php" class="flex items-center gap-2 flex-grow">
                                                <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                                <label class="text-sm font-semibold text-gray-700">Qty:</label>
                                                <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="100" class="w-16 border border-gray-300 rounded-lg px-2 py-1 text-center focus:outline-none focus:ring-2 focus:ring-green-500">
                                                <button type="submit" name="action" value="update" class="ml-auto bg-green-500 text-white px-3 py-1 rounded-lg text-sm hover:bg-green-600 transition-colors">Update</button>
                                            </form>
                                        </div>

                                        <div class="mt-4 pt-4 border-t border-gray-200 flex items-center justify-between">
                                            <p class="font-semibold text-gray-800">Subtotal:</p>
                                            <p class="text-lg font-bold text-green-600">LKR <?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                                        </div>

                                        <form method="POST" action="cart.php" class="mt-4">
                                            <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                            <button type="submit" name="action" value="remove" class="w-full bg-red-100 text-red-600 py-2 rounded-lg hover:bg-red-200 transition-colors font-medium text-sm">
                                                Remove from Cart
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="lg:col-span-1">
                    <div class="cart-summary sticky top-4">
                        <h2 class="text-xl font-bold text-gray-800 mb-4">Order Summary</h2>

                        <div class="space-y-3 pb-4 border-b border-gray-300">
                            <div class="flex justify-between text-gray-700">
                                <span>Subtotal:</span>
                                <span class="font-semibold">LKR <?php echo number_format($totalPrice, 2); ?></span>
                            </div>
                            <div class="flex justify-between text-gray-700">
                                <span>Shipping:</span>
                                <span class="font-semibold">LKR <?php echo number_format($shippingFee, 2); ?></span>
                            </div>
                        </div>

                        <div class="flex justify-between items-center mt-4 mb-6">
                            <span class="text-lg font-bold text-gray-800">Total:</span>
                            <span class="text-2xl font-bold text-green-600">LKR <?php echo number_format($grandTotal, 2); ?></span>
                        </div>

                        <div class="space-y-2">
                            <a href="checkout.php" class="block w-full bg-green-500 text-white py-3 rounded-lg hover:bg-green-600 transition-colors font-semibold text-center">
                                Proceed to Checkout
                            </a>
                            <a href="<?php echo htmlspecialchars($homeUrl); ?>" class="block w-full bg-gray-200 text-gray-800 py-3 rounded-lg hover:bg-gray-300 transition-colors font-semibold text-center">
                                Continue Shopping
                            </a>
                        </div>

                        <div class="mt-6 p-4 bg-green-50 rounded-lg border border-green-200">
                            <p class="text-xs text-green-700 text-center">
                                <span class="font-semibold">🌿 Secure Checkout</span><br>
                                Your herbal wellness journey is safe with us
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>
    <?php require_once __DIR__ . '/../includes/layout/footer.php'; ?>
</body>

</html>