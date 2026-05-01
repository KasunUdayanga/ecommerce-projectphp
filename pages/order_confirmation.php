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

$stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$orderItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
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
        <h2 class="text-2xl font-semibold text-green-500 text-center mb-4">Order Details</h2>
        <div class="bg-gray-50 p-4 rounded-lg shadow-sm">
            <ul class="divide-y divide-gray-200">
                <?php foreach ($orderItems as $item): ?>
                    <li class="flex justify-between py-2">
                        <span class="font-medium text-gray-700"><?php echo htmlspecialchars($item['product_id']); ?></span>
                        <span class="text-gray-500"><?php echo $item['quantity']; ?> x <?php echo formatCurrency($item['price']); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <p class="text-right text-lg font-bold text-green-600 mt-4">
                Total: <?php echo formatCurrency($order['total_price']); ?>
            </p>
        </div>
        <div class="text-center mt-6">
            <a href="../index.php" class="bg-green-500 text-white px-6 py-2 rounded-lg shadow hover:bg-green-600">
                Back to Home
            </a>
        </div>
    </div>
</body>

</html>