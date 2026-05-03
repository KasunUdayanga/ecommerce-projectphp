<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in
if (!isUserLoggedIn()) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$userId = $_SESSION['user_id'];
$orders = getUserOrders($userId);
$userName = getLoggedInUserName();
$isLoggedIn = true;
$appBase = '/ecommerce-projectphp/';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/ecommerce-projectphp/assets/css/styles.css">
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

        .order-shell {
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            background: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            padding: 1.25rem;
        }

        .order-shell:hover {
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            transition: box-shadow 0.3s ease;
        }

        .delivery-timeline {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 1rem;
        }

        .timeline-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            flex: 1;
            position: relative;
        }

        .timeline-item::after {
            content: '';
            position: absolute;
            top: 1.5rem;
            left: 50%;
            width: 100%;
            height: 2px;
            background: #e5e7eb;
        }

        .timeline-item:last-child::after {
            display: none;
        }

        .timeline-dot {
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            background: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 600;
            position: relative;
            z-index: 2;
        }

        .timeline-dot.active {
            background: #22c55e;
            color: white;
        }

        .timeline-dot.pending {
            background: #f3f4f6;
            color: #6b7280;
            border: 2px solid #d1d5db;
        }

        .timeline-label {
            font-size: 0.75rem;
            font-weight: 500;
            text-align: center;
            margin-top: 0.5rem;
            color: #6b7280;
        }

        .timeline-label.active {
            color: #22c55e;
            font-weight: 600;
        }
    </style>
</head>

<body class="bg-gray-50 text-black">
    <?php require_once __DIR__ . '/../includes/layout/header.php'; ?>

    <main class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
        <div class="flex flex-col gap-4 mb-8 md:flex-row md:items-center md:justify-between">
            <div>
                <span class="soft-badge">Account</span>
                <h2 class="mt-2 text-3xl sm:text-4xl font-bold">My Orders</h2>
                <p class="text-sm text-gray-600 mt-2">Track your orders and delivery status</p>
            </div>
            <a href="<?php echo htmlspecialchars($appBase . 'index.php'); ?>" class="inline-flex items-center justify-center rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-700 hover:bg-green-100 transition-colors whitespace-nowrap">
                Continue Shopping
            </a>
        </div>

        <?php if (empty($orders)): ?>
            <div class="order-shell text-center py-12">
                <p class="text-lg sm:text-xl text-gray-600 mb-4">No orders yet</p>
                <p class="text-sm text-gray-500 mb-6">Once you place an order, you can track it here.</p>
                <a href="<?php echo htmlspecialchars($appBase . 'pages/products.php'); ?>" class="inline-block bg-green-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700 transition-colors">
                    Browse Products
                </a>
            </div>
        <?php else: ?>
            <div class="space-y-6">
                <?php foreach ($orders as $order): ?>
                    <?php
                    $deliveryBadge = getDeliveryStatusBadge($order['delivery_status']);
                    $orderStatusBadge = $order['order_status'] === 'confirmed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-800';
                    $deliveryStages = ['pending' => 0, 'processing' => 1, 'shipped' => 2, 'out_for_delivery' => 3, 'delivered' => 4];
                    $currentStage = $deliveryStages[$order['delivery_status']] ?? 0;
                    ?>
                    <div class="order-shell">
                        <!-- Order Header -->
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-200">
                            <div>
                                <p class="text-xs uppercase text-gray-500 font-semibold">Order ID</p>
                                <p class="text-2xl font-bold text-gray-800">#<?php echo htmlspecialchars((string) $order['id']); ?></p>
                                <p class="text-sm text-gray-600 mt-1">Ordered on <?php echo date('M d, Y', strtotime($order['created_at'])); ?></p>
                            </div>
                            <div class="flex flex-col gap-2 items-start sm:items-end">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold <?php echo $deliveryBadge['bg'] . ' ' . $deliveryBadge['text']; ?>">
                                    <?php echo htmlspecialchars($deliveryBadge['label']); ?>
                                </span>
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold bg-blue-100 text-blue-700">
                                    <?php echo htmlspecialchars(ucfirst($order['payment_status'])); ?>
                                </span>
                            </div>
                        </div>

                        <!-- Delivery Timeline -->
                        <div class="mt-6 mb-6">
                            <p class="text-xs uppercase text-gray-500 font-semibold mb-4">Delivery Status</p>
                            <div class="delivery-timeline">
                                <div class="timeline-item">
                                    <div class="timeline-dot <?php echo $currentStage >= 0 ? 'active' : 'pending'; ?>">✓</div>
                                    <span class="timeline-label <?php echo $currentStage >= 0 ? 'active' : ''; ?>">Pending</span>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-dot <?php echo $currentStage >= 1 ? 'active' : 'pending'; ?>">✓</div>
                                    <span class="timeline-label <?php echo $currentStage >= 1 ? 'active' : ''; ?>">Processing</span>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-dot <?php echo $currentStage >= 2 ? 'active' : 'pending'; ?>">✓</div>
                                    <span class="timeline-label <?php echo $currentStage >= 2 ? 'active' : ''; ?>">Shipped</span>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-dot <?php echo $currentStage >= 3 ? 'active' : 'pending'; ?>">✓</div>
                                    <span class="timeline-label <?php echo $currentStage >= 3 ? 'active' : ''; ?>">Out for Delivery</span>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-dot <?php echo $currentStage >= 4 ? 'active' : 'pending'; ?>">✓</div>
                                    <span class="timeline-label <?php echo $currentStage >= 4 ? 'active' : ''; ?>">Delivered</span>
                                </div>
                            </div>
                        </div>

                        <!-- Order Items -->
                        <div class="mb-6 pb-6 border-b border-gray-200">
                            <p class="text-xs uppercase text-gray-500 font-semibold mb-3">Items</p>
                            <div class="space-y-2">
                                <?php if (!empty($order['items'])): ?>
                                    <?php foreach ($order['items'] as $item): ?>
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-700">
                                                <span class="font-semibold"><?php echo htmlspecialchars($item['product_name'] ?? 'Product'); ?></span>
                                                <span class="text-gray-500"> × <?php echo (int) $item['quantity']; ?></span>
                                            </span>
                                            <span class="text-sm font-semibold text-gray-800">LKR <?php echo number_format((float) $item['price'], 2); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-sm text-gray-500">No items found</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Order Totals -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <p class="text-xs uppercase text-gray-500 font-semibold">Subtotal</p>
                                <p class="text-lg font-semibold text-gray-800">LKR <?php echo number_format((float) $order['total_price'], 2); ?></p>
                            </div>
                            <div>
                                <p class="text-xs uppercase text-gray-500 font-semibold">Shipping</p>
                                <p class="text-lg font-semibold text-gray-800">LKR <?php echo number_format((float) $order['shipping_fee'], 2); ?></p>
                            </div>
                            <div>
                                <p class="text-xs uppercase text-gray-500 font-semibold">Total</p>
                                <p class="text-lg font-semibold text-green-600">LKR <?php echo number_format((float) $order['grand_total'], 2); ?></p>
                            </div>
                        </div>

                        <!-- Payment & Order Status -->
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p class="text-xs uppercase text-gray-500 font-semibold mb-1">Payment Method</p>
                                    <p class="font-semibold text-gray-800"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $order['payment_method']))); ?></p>
                                </div>
                                <div>
                                    <p class="text-xs uppercase text-gray-500 font-semibold mb-1">Order Status</p>
                                    <p class="font-semibold text-gray-800"><?php echo htmlspecialchars(ucfirst($order['order_status'])); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <?php require_once __DIR__ . '/../includes/layout/footer.php'; ?>
</body>

</html>