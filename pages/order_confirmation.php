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
$stmt = $conn->prepare("SELECT o.*, u.username, u.email, u.address, u.phone_number
            FROM orders o
            LEFT JOIN users u ON u.id = o.user_id
            WHERE o.id = ? AND o.user_id = ?");
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
$paymentMethod = (string) ($order['payment_method'] ?? 'cod');
$paymentStatus = (string) ($order['payment_status'] ?? 'pending');
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
    <div class="max-w-5xl mx-auto mt-10 p-6 bg-white shadow-md rounded-lg">
        <header class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-14 h-14 rounded-full bg-green-100 text-green-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Order Confirmed</h1>
                    <p class="text-sm text-gray-500">Thank you - we received your order.</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button id="copyOrderId" data-order-id="<?php echo htmlspecialchars($orderId); ?>" class="inline-flex items-center gap-2 bg-white border border-gray-200 px-3 py-2 rounded hover:bg-gray-50">
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 17l4-4-4-4m5 8l4-4-4-4"></path>
                    </svg>
                    Copy Order ID
                </button>
                <button onclick="window.print()" class="inline-flex items-center gap-2 bg-white border border-gray-200 px-3 py-2 rounded hover:bg-gray-50">
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18h12v-7H6v7z"></path>
                    </svg>
                    Print Receipt
                </button>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <section class="lg:col-span-2">
                <div class="mb-4 p-4 rounded-lg border border-gray-100 bg-gray-50">
                    <h3 class="text-lg font-semibold mb-2">Shipping Details</h3>
                    <div class="text-sm text-gray-800 space-y-2">
                        <p class="font-medium text-gray-900"><?php echo htmlspecialchars($order['username'] ?? ''); ?></p>
                        <p><strong>Address:</strong><br><?php echo nl2br(htmlspecialchars($order['address'] ?? 'Not provided')); ?></p>
                        <p><strong>Mobile:</strong> <?php echo htmlspecialchars($order['phone_number'] ?? 'Not provided'); ?></p>
                        <p class="text-xs text-gray-500 mt-1">If details are incorrect, update your profile before placing future orders.</p>
                    </div>
                </div>

                <div class="p-4 rounded-lg border border-gray-100 bg-white">
                    <h2 class="text-xl font-semibold text-green-600 mb-3">Order Details</h2>
                    <ul class="divide-y divide-gray-100">
                        <?php foreach ($orderItems as $item): ?>
                            <li class="py-3 flex items-start justify-between">
                                <div class="mr-4">
                                    <p class="font-medium text-gray-800"><?php echo htmlspecialchars($item['product_name'] ?: ('Product #' . $item['product_id'])); ?></p>
                                    <p class="text-xs text-gray-500">Product ID: <?php echo htmlspecialchars((string) $item['product_id']); ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-600"><?php echo (int) $item['quantity']; ?> × <?php echo formatCurrency($item['price']); ?></p>
                                    <p class="text-sm font-semibold text-gray-800"><?php echo formatCurrency($item['price'] * $item['quantity']); ?></p>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="mt-4 border-t border-gray-100 pt-3 text-right">
                        <p class="text-sm text-gray-600">Subtotal: <?php echo formatCurrency($subTotal); ?></p>
                        <p class="text-sm text-gray-600">Shipping: <?php echo formatCurrency($shippingFee); ?></p>
                        <p class="text-lg font-bold text-green-600">Total: <?php echo formatCurrency($finalTotal); ?></p>
                    </div>
                </div>
            </section>

            <aside class="lg:col-span-1">
                <div class="sticky top-6 p-4 rounded-lg border border-gray-100 bg-white">
                    <div class="mb-3">
                        <p class="text-xs text-gray-500">Order ID</p>
                        <p class="font-mono font-semibold break-all text-gray-900">#<?php echo htmlspecialchars((string) $orderId); ?></p>
                    </div>
                    <div class="mb-3">
                        <p class="text-xs text-gray-500">Placed</p>
                        <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($orderDateText !== '' ? $orderDateText : 'N/A'); ?></p>
                    </div>
                    <div class="mb-3">
                        <p class="text-xs text-gray-500">Items</p>
                        <p class="font-semibold text-gray-800"><?php echo (int) $totalItems; ?> items</p>
                    </div>
                    <div class="mb-3">
                        <p class="text-xs text-gray-500">Payment Method</p>
                        <p class="font-semibold text-gray-800"><?php echo htmlspecialchars(getPaymentMethodLabel($paymentMethod)); ?></p>
                        <p class="text-xs text-gray-500 mt-1">Status: <?php echo htmlspecialchars($paymentStatus); ?></p>
                    </div>
                    <div class="mt-4">
                        <a href="../index.php" class="block text-center bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Continue Shopping</a>
                    </div>
                </div>
            </aside>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var btn = document.getElementById('copyOrderId');
            if (!btn) return;
            btn.addEventListener('click', function() {
                var id = btn.getAttribute('data-order-id') || '';
                if (!id) return;
                var text = '#' + id;
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(function() {
                        var original = btn.textContent;
                        btn.textContent = 'Copied';
                        setTimeout(function() {
                            btn.textContent = original;
                        }, 2000);
                    }, function() {
                        alert('Copy failed. Order ID: ' + text);
                    });
                } else {
                    alert('Order ID: ' + text);
                }
            });
        });
    </script>
</body>

</html>