<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';

if (!isUserLoggedIn()) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$orderId = (int)($_GET['order_id'] ?? 0);
if ($orderId <= 0) {
    header('Location: checkout.php');
    exit;
}

$conn = getDbConnection();
$stmt = $conn->prepare("SELECT id, user_id, grand_total, payment_status FROM orders WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$res = $stmt->get_result();
$order = $res->fetch_assoc();
$stmt->close();

if (!$order || (int)$order['user_id'] !== (int)$userId) {
    $conn->close();
    header('Location: checkout.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_paid_test'])) {
    $stmt = $conn->prepare("UPDATE orders SET payment_status = 'paid', payment_method = 'card' WHERE id = ?");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    header("Location: order_confirmation.php?order_id=$orderId");
    exit;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Pay Order</title>
    <link rel="icon" href="/ecommerce-projectphp/assets/titlelog.png" type="image/png">
</head>

<body class="bg-white text-black">
    <?php require_once __DIR__ . '/../includes/layout/header.php'; ?>

    <div class="container mx-auto p-4">
        <div class="max-w-xl mx-auto bg-white border rounded-lg p-6">
            <h1 class="text-2xl font-bold mb-3">Complete Card Payment</h1>
            <p class="text-sm text-gray-600 mb-4">Order #<?php echo (int)$orderId; ?> — Amount: <?php echo formatCurrency($order['grand_total'] ?? 0); ?></p>

            <div class="space-y-3">
                <div class="p-4 rounded border border-gray-200">
                    <p class="font-semibold">Choose how to pay</p>
                    <p class="text-sm text-gray-600">Click your preferred payment gateway below to securely complete your card payment.</p>
                    <div class="mt-3 flex gap-2">
                        <a href="https://buy.stripe.com/test_8x24gB9PI98K4GYcrPb7y00" target="_blank" class="px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-100">Pay with Stripe</a>
                        <a href="https://sandbox.payhere.lk/pay/of4568b0a" target="_blank" class="px-3 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-100">Pay with PayHere</a>
                    </div>
                </div>

                <div class="p-4 rounded border border-green-100 bg-green-50 text-sm text-green-800">
                    <p class="font-semibold">Developer / Test</p>
                    <p class="mb-2">If you want to simulate a successful card payment in a test environment, click the button below (will mark order as paid).</p>
                    <form method="POST">
                        <button type="submit" name="mark_paid_test" class="px-4 py-2 bg-green-600 text-white rounded">Simulate Successful Payment</button>
                    </form>
                </div>

                <div class="text-sm text-gray-600">
                    <p>After real gateway integration, these buttons should start a checkout session (Stripe Checkout or PayHere) and update the order's payment_status when completed.</p>
                    <p class="mt-2">You will be redirected to the order confirmation page on success.</p>
                </div>
            </div>

            <div class="mt-6">
                <a href="order_confirmation.php?order_id=<?php echo (int)$orderId; ?>" class="text-sm text-blue-600">Back to Order</a>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/../includes/layout/footer.php'; ?>
</body>

</html>