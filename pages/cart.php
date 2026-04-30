<?php
session_start();
include '../includes/functions.php';

// Initialize cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle cart updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'add';
    $productId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 1;

    switch ($action) {
        case 'add':
            addToCart($productId, $quantity);
            break;
        case 'remove':
            removeFromCart($productId);
            break;
        case 'update':
            updateCart($productId, $quantity);
            break;
        default:
            addToCart($productId, $quantity);
            break;
    }
}

$cartItems = getCartItems();
$totalPrice = calculateTotalPrice($cartItems);

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
    <link href="../assets/css/styles.css" rel="stylesheet">
    <title>Shopping Cart</title>
</head>

<body class="bg-white text-black">
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Shopping Cart</h1>
        <?php if (empty($cartItems)): ?>
            <p>Your cart is empty.</p>
        <?php else: ?>
            <table class="min-w-full border border-gray-300">
                <thead>
                    <tr class="bg-green-500 text-white">
                        <th class="border px-4 py-2">Product</th>
                        <th class="border px-4 py-2">Quantity</th>
                        <th class="border px-4 py-2">Price</th>
                        <th class="border px-4 py-2">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $item): ?>
                        <tr>
                            <td class="border px-4 py-2"><?php echo htmlspecialchars($item['name']); ?></td>
                            <td class="border px-4 py-2">
                                <form method="POST" action="cart.php">
                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                    <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" class="border p-1">
                                    <button type="submit" name="action" value="update" class="bg-light-green-500 text-white px-2 py-1">Update</button>
                                </form>
                            </td>
                            <td class="border px-4 py-2"><?php echo number_format($item['price'], 2); ?></td>
                            <td class="border px-4 py-2">
                                <form method="POST" action="cart.php">
                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" name="action" value="remove" class="bg-red-500 text-white px-2 py-1">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="mt-4">
                <strong>Total Price: <?php echo number_format($totalPrice, 2); ?></strong>
            </div>
            <div class="mt-4">
                <a href="checkout.php" class="bg-green-500 text-white px-4 py-2">Proceed to Checkout</a>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>