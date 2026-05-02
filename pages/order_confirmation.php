<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';

if (!isUserLoggedIn()) {
    header('Location: login.php');
    exit;
}

$orderId = $_GET['order_id'] ?? null;
if (!$orderId) {
    header('Location: index.php');
    exit;
}

$conn = getDbConnection();
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $orderId, $_SESSION['user_id']);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    $conn->close();
    header('Location: index.php');
    exit;
}

$stmt = $conn->prepare("SELECT oi.*, p.name AS product_name FROM order_items oi LEFT JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$orderItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

$subTotal = (float) ($order['total_price'] ?? 0);
$shippingFee = isset($order['shipping_fee']) ? (float) $order['shipping_fee'] : 0.0;
$finalTotal = isset($order['grand_total']) ? (float) $order['grand_total'] : ($subTotal + $shippingFee);
$totalItems = 0;
foreach ($orderItems as $item) {
    $totalItems += (int) ($item['quantity'] ?? 0);
}

$orderDateText = '';
if (!empty($order['created_at'])) {
    $timestamp = strtotime($order['created_at']);
    if ($timestamp !== false) {
        $orderDateText = date('F j, Y g:i A', $timestamp);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <link rel="icon" href="/ecommerce-projectphp/assets/titlelog.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 text-gray-800">
    <div class="max-w-4xl mx-auto mt-10 p-6 bg-white shadow-md rounded-lg">
        <h1 class="text-3xl font-bold text-green-600 text-center mb-4">Order Confirmation</h1>
        <p class="text-center text-gray-600 mb-6">
            Thank you for your order! Your order ID is
            <span class="font-semibold text-gray-800"><?php echo htmlspecialchars($orderId); ?></span>.
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                <p class="text-xs uppercase tracking-wide text-gray-500">Order Date</p>
                <p class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($orderDateText !== '' ? $orderDateText : 'N/A'); ?></p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                <p class="text-xs uppercase tracking-wide text-gray-500">Items</p>
                <p class="text-sm font-semibold text-gray-800"><?php echo (int) $totalItems; ?> item(s)</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                <p class="text-xs uppercase tracking-wide text-gray-500">Order Number</p>
                <p class="text-sm font-semibold text-gray-800">#<?php echo htmlspecialchars((string) $orderId); ?></p>
            </div>
        </div>

        <h2 class="text-2xl font-semibold text-green-500 text-center mb-4">Order Details</h2>
        <div class="bg-gray-50 p-4 rounded-lg shadow-sm">
            <ul class="divide-y divide-gray-200">
                <?php foreach ($orderItems as $item): ?>
                    <li class="flex justify-between py-2">
                        <div>
                            <p class="font-medium text-gray-700"><?php echo htmlspecialchars($item['product_name'] ?: ('Product #' . $item['product_id'])); ?></p>
                            <p class="text-xs text-gray-500">Product ID: <?php echo htmlspecialchars((string) $item['product_id']); ?></p>
                        </div>
                        <div class="text-right">
                            <p class="text-gray-500"><?php echo (int) $item['quantity']; ?> x <?php echo formatCurrency($item['price']); ?></p>
                            <p class="text-sm font-semibold text-gray-700">Line Total: <?php echo formatCurrency($item['price'] * $item['quantity']); ?></p>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="mt-4 border-t border-gray-200 pt-3 space-y-1 text-right">
                <p class="text-sm text-gray-600">Subtotal: <?php echo formatCurrency($subTotal); ?></p>
                <p class="text-sm text-gray-600">Shipping: <?php echo formatCurrency($shippingFee); ?></p>
                <p class="text-lg font-bold text-green-600">Final Total: <?php echo formatCurrency($finalTotal); ?></p>
            </div>
        </div>
        <div class="text-center mt-6">
            <a href="../index.php" class="bg-green-500 text-white px-6 py-2 rounded-lg shadow hover:bg-green-600">
                Back to Home
            </a>
        </div>
    </div>
</body>

</html>