<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';

if (!isUserLoggedIn()) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$userName = getLoggedInUserName(); // Ensure this is set
$cartItems = getCartItems();
$shippingFee = 250.00;
$subTotal = array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $cartItems));
$grandTotal = $subTotal + $shippingFee;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($cartItems)) {
        $_SESSION['checkout_error'] = 'Your cart is empty.';
        header('Location: cart.php');
        exit;
    }

    // Create the order
    $orderId = createOrder($userId, $cartItems, $shippingFee);

    // Clear the cart
    clearCart();

    // Redirect to order confirmation page
    header("Location: order_confirmation.php?order_id=$orderId");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="/ecommerce-projectphp/assets/css/styles.css" rel="stylesheet">
    <title>Checkout</title>
    <link rel="icon" href="/ecommerce-projectphp/assets/titlelog.png" type="image/png">
</head>

<body class="bg-white text-black">
    <?php require_once __DIR__ . '/../includes/layout/header.php'; ?>
    <div class="container mx-auto p-4">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
            <h1 class="text-2xl font-bold">Checkout</h1>
            <span class="text-sm text-gray-500">Signed in as <?php echo htmlspecialchars($userName); ?></span>
        </div>

        <form method="POST">
            <h2 class="text-xl font-semibold mb-2">Order Summary</h2>
            <ul class="mb-4 space-y-2">
                <?php foreach ($cartItems as $item): ?>
                    <li class="flex justify-between border-b border-gray-200 pb-2">
                        <span><?php echo htmlspecialchars($item['name']); ?> (x<?php echo (int) $item['quantity']; ?>)</span>
                        <span>LKR<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="mt-4 space-y-1 text-sm text-gray-700">
                <p class="flex justify-between"><span>Subtotal:</span><span class="font-semibold">LKR <?php echo number_format($subTotal, 2); ?></span></p>
                <p class="flex justify-between"><span>Shipping:</span><span class="font-semibold">LKR <?php echo number_format($shippingFee, 2); ?></span></p>
                <p class="flex justify-between border-t border-gray-200 pt-2 text-base"><span class="font-bold">Final Total:</span><span class="font-bold text-green-600">LKR <?php echo number_format($grandTotal, 2); ?></span></p>
            </div>
            <button type="submit" class="mt-4 bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Place Order</button>
        </form>
    </div>
    <?php require_once __DIR__ . '/../includes/layout/footer.php'; ?>
</body>

</html>