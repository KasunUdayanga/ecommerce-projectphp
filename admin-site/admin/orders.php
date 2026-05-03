<?php
require_once __DIR__ . '/../../shared-core/includes/admin.php';

requireAdminLogin();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $orderId = (int) ($_POST['order_id'] ?? 0);

    if ($action === 'confirm_order' && $orderId > 0) {
        if (confirmAdminOrder($orderId)) {
            header('Location: orders.php?message=' . urlencode('Order confirmed for shipping.'));
            exit;
        }
        $error = 'Unable to confirm the order.';
    } elseif ($action === 'update_delivery_status' && $orderId > 0) {
        $deliveryStatus = $_POST['delivery_status'] ?? '';
        if (updateDeliveryStatus($orderId, $deliveryStatus)) {
            header('Location: orders.php?message=' . urlencode('Delivery status updated successfully.'));
            exit;
        }
        $error = 'Unable to update delivery status.';
    }
}

$message = $_GET['message'] ?? '';
$orders = getAdminOrders();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Orders</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/ecommerce-projectphp/shared-core/assets/css/styles.css">
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

        .admin-shell {
            border: 1px solid #dcfce7;
            border-radius: 1.2rem;
            background: linear-gradient(180deg, #f0fdf4 0%, #ffffff 45%);
            box-shadow: 0 10px 28px rgba(22, 163, 74, .08);
            padding: 1.5rem;
        }
    </style>
</head>

<body class="bg-white text-black">
    <header class="bg-gradient-to-r from-green-100 to-green-600 text-white p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 sm:gap-2">
        <h1 class="text-2xl sm:text-3xl font-bold">Admin Dashboard</h1>
        <div class="flex flex-wrap gap-2 w-full sm:w-auto">
            <a href="/ecommerce-projectphp/admin-site/index.php" class="bg-white text-green-600 px-4 py-2 rounded-lg hover:bg-gray-100 font-medium transition-colors text-sm sm:text-base">Products</a>
            <a href="/ecommerce-projectphp/user-site/index.php" class="bg-white text-green-600 px-4 py-2 rounded-lg hover:bg-gray-100 font-medium transition-colors text-sm sm:text-base">View Store</a>
            <a href="create.php" class="bg-white text-green-600 px-4 py-2 rounded-lg hover:bg-gray-100 font-medium transition-colors text-sm sm:text-base">Add Product</a>
            <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-100 font-medium transition-colors text-sm sm:text-base">Logout</a>
        </div>
    </header>

    <main class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
        <div class="flex flex-col gap-4 mb-8 md:flex-row md:items-center md:justify-between">
            <div>
                <span class="soft-badge">Shipping</span>
                <h2 class="mt-2 text-3xl sm:text-4xl font-bold">Order Confirmations</h2>
                <p class="text-sm text-gray-600 mt-2">Review shipping details and confirm orders for dispatch.</p>
            </div>
            <a href="/ecommerce-projectphp/admin-site/index.php" class="inline-flex items-center justify-center rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-700 hover:bg-green-100 transition-colors whitespace-nowrap">
                Back to Products
            </a>
        </div>

        <?php if ($message): ?>
            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 text-green-800 text-sm sm:text-base">
                <p class="font-semibold">✓ <?php echo htmlspecialchars($message); ?></p>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-red-800 text-sm sm:text-base">
                <p class="font-semibold">✕ <?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>

        <?php if (empty($orders)): ?>
            <div class="admin-shell text-center py-12">
                <p class="text-lg sm:text-xl text-gray-600 mb-2">No orders found yet</p>
                <p class="text-sm text-gray-500">Shipping confirmations will appear here once customers place orders.</p>
            </div>
        <?php else: ?>
            <!-- Desktop Table View (hidden on mobile) -->
            <div class="admin-shell overflow-x-auto hidden md:block">
                <table class="min-w-full bg-white rounded-lg shadow-sm divide-y divide-gray-100">
                    <thead>
                        <tr class="text-left text-sm text-gray-600">
                            <th class="py-3 px-4 font-semibold">#</th>
                            <th class="py-3 px-4 font-semibold">Customer</th>
                            <th class="py-3 px-4 font-semibold">Shipping</th>
                            <th class="py-3 px-4 font-semibold">Details</th>
                            <th class="py-3 px-4 font-semibold">Delivery</th>
                            <th class="py-3 px-4 font-semibold">Total</th>
                            <th class="py-3 px-4 font-semibold">Status & Action</th>
                            <th class="py-3 px-4 font-semibold">Created</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-700">
                        <?php foreach ($orders as $order): ?>
                            <tr class="odd:bg-white even:bg-green-50 hover:bg-green-100 transition-colors align-top">
                                <td class="py-4 px-4 font-semibold text-gray-800">#<?php echo htmlspecialchars((string) $order['id']); ?></td>
                                <td class="py-4 px-4">
                                    <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($order['username']); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo htmlspecialchars($order['email']); ?></p>
                                </td>
                                <td class="py-4 px-4 text-sm text-gray-700">
                                    <p><span class="font-semibold">Addr:</span> <?php echo htmlspecialchars($order['address'] ?: 'N/A'); ?></p>
                                    <p><span class="font-semibold">Phone:</span> <?php echo htmlspecialchars($order['phone_number'] ?: 'N/A'); ?></p>
                                </td>
                                <td class="py-4 px-4">
                                    <details class="group">
                                        <summary class="cursor-pointer select-none text-sm text-green-700 font-medium">View details</summary>
                                        <div class="mt-2 text-sm text-gray-700 space-y-3">
                                            <div>
                                                <p class="font-semibold text-xs text-gray-600 mb-1">Items:</p>
                                                <?php if (!empty($order['items']) && is_array($order['items'])): ?>
                                                    <ul class="list-inside list-disc">
                                                        <?php foreach ($order['items'] as $it): ?>
                                                            <li><?php echo htmlspecialchars($it['product_name'] ?? 'Product'); ?> × <?php echo (int) $it['quantity']; ?> — LKR <?php echo number_format((float) $it['price'], 2); ?></li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php else: ?>
                                                    <p class="text-xs text-gray-500">No items found</p>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <p class="font-semibold text-xs text-gray-600 mb-1">Payment:</p>
                                                <p class="text-sm font-semibold"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $order['payment_method'] ?? ''))); ?></p>
                                                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($order['payment_status'] ?? ''); ?></p>
                                            </div>
                                        </div>
                                    </details>
                                </td>
                                <td class="py-4 px-4">
                                    <details class="group">
                                        <summary class="cursor-pointer select-none text-sm font-medium px-2 py-1 rounded inline-flex items-center gap-2 <?php
                                                                                                                                                        $delStatus = $order['delivery_status'] ?? 'pending';
                                                                                                                                                        $delBadges = [
                                                                                                                                                            'pending' => 'bg-gray-100 text-gray-700',
                                                                                                                                                            'processing' => 'bg-blue-100 text-blue-700',
                                                                                                                                                            'shipped' => 'bg-purple-100 text-purple-700',
                                                                                                                                                            'out_for_delivery' => 'bg-yellow-100 text-yellow-700',
                                                                                                                                                            'delivered' => 'bg-green-100 text-green-700',
                                                                                                                                                            'cancelled' => 'bg-red-100 text-red-700',
                                                                                                                                                        ];
                                                                                                                                                        echo $delBadges[$delStatus] ?? 'bg-gray-100 text-gray-700';
                                                                                                                                                        ?>">
                                            <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $delStatus))); ?>
                                        </summary>
                                        <div class="mt-2 text-sm text-gray-700">
                                            <form method="POST" class="flex flex-col gap-2">
                                                <input type="hidden" name="action" value="update_delivery_status">
                                                <input type="hidden" name="order_id" value="<?php echo (int) $order['id']; ?>">
                                                <select name="delivery_status" class="text-xs border border-gray-300 rounded px-2 py-1 cursor-pointer">
                                                    <option value="pending" <?php echo ($order['delivery_status'] ?? 'pending') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="processing" <?php echo ($order['delivery_status'] ?? 'pending') === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                                    <option value="shipped" <?php echo ($order['delivery_status'] ?? 'pending') === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                    <option value="out_for_delivery" <?php echo ($order['delivery_status'] ?? 'pending') === 'out_for_delivery' ? 'selected' : ''; ?>>Out for Delivery</option>
                                                    <option value="delivered" <?php echo ($order['delivery_status'] ?? 'pending') === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                    <option value="cancelled" <?php echo ($order['delivery_status'] ?? 'pending') === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                </select>
                                                <button type="submit" class="text-xs bg-blue-600 text-white px-2 py-1 rounded hover:bg-blue-700 font-semibold">Update</button>
                                            </form>
                                        </div>
                                    </details>
                                </td>
                                <td class="py-4 px-4 text-sm text-gray-700">
                                    <p class="font-semibold text-green-600">LKR <?php echo number_format((float) $order['grand_total'], 2); ?></p>
                                </td>
                                <td class="py-4 px-4">
                                    <details class="group">
                                        <summary class="cursor-pointer select-none text-sm font-medium px-3 py-1 rounded-full inline-flex items-center gap-2 <?php echo $order['order_status'] === 'confirmed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-800'; ?>">
                                            <?php echo htmlspecialchars(ucfirst($order['order_status'])); ?>
                                        </summary>
                                        <div class="mt-2 text-sm text-gray-700 space-y-2">
                                            <div>
                                                <p class="font-semibold text-xs text-gray-600 mb-1">Order Status:</p>
                                                <p class="text-sm"><?php echo htmlspecialchars(ucfirst($order['order_status'])); ?></p>
                                                <?php if (!empty($order['confirmed_at'])): ?>
                                                    <p class="text-xs text-gray-500">Confirmed: <?php echo htmlspecialchars($order['confirmed_at']); ?></p>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($order['order_status'] !== 'confirmed'): ?>
                                                <form method="POST" class="pt-2 border-t">
                                                    <input type="hidden" name="action" value="confirm_order">
                                                    <input type="hidden" name="order_id" value="<?php echo (int) $order['id']; ?>">
                                                    <button type="submit" class="w-full rounded bg-green-600 px-3 py-2 text-xs font-semibold text-white hover:bg-green-700 transition-colors">
                                                        Confirm Order
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </details>
                                </td>
                                <td class="py-4 px-4 text-sm text-gray-600 whitespace-nowrap"><?php echo htmlspecialchars($order['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="md:hidden space-y-4">
                <?php foreach ($orders as $order): ?>
                    <div class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6 hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between mb-4 pb-4 border-b border-gray-200">
                            <div>
                                <p class="text-xs uppercase text-gray-500 font-semibold">Order ID</p>
                                <p class="text-lg sm:text-xl font-bold text-gray-800">#<?php echo htmlspecialchars((string) $order['id']); ?></p>
                            </div>
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold <?php echo $order['order_status'] === 'confirmed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-800'; ?>">
                                <?php echo htmlspecialchars(ucfirst($order['order_status'])); ?>
                            </span>
                        </div>

                        <div class="mb-4">
                            <p class="text-xs uppercase text-gray-500 font-semibold mb-2">Customer</p>
                            <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($order['username']); ?></p>
                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($order['email']); ?></p>
                        </div>

                        <div class="mb-4">
                            <p class="text-xs uppercase text-gray-500 font-semibold mb-2">Shipping Details</p>
                            <div class="space-y-1 text-sm text-gray-700">
                                <p><span class="font-semibold">Address:</span> <span class="break-words"><?php echo htmlspecialchars($order['address'] ?: 'N/A'); ?></span></p>
                                <p><span class="font-semibold">Phone:</span> <?php echo htmlspecialchars($order['phone_number'] ?: 'N/A'); ?></p>
                            </div>
                        </div>

                        <div class="mb-4">
                            <p class="text-xs uppercase text-gray-500 font-semibold mb-2">Items</p>
                            <?php if (!empty($order['items']) && is_array($order['items'])): ?>
                                <ul class="list-inside list-disc text-sm text-gray-700">
                                    <?php foreach ($order['items'] as $it): ?>
                                        <li><?php echo htmlspecialchars($it['product_name'] ?? 'Product'); ?> × <?php echo (int) $it['quantity']; ?> — LKR <?php echo number_format((float) $it['price'], 2); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p class="text-sm text-gray-500">No items found</p>
                            <?php endif; ?>
                        </div>

                        <div class="mb-4">
                            <p class="text-xs uppercase text-gray-500 font-semibold mb-2">Payment</p>
                            <p class="font-semibold text-sm text-gray-800"><?php echo htmlspecialchars(ucfirst($order['payment_method'] ?? '')); ?></p>
                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($order['payment_status'] ?? ''); ?></p>
                        </div>

                        <div class="mb-4">
                            <p class="text-xs uppercase text-gray-500 font-semibold mb-2">Delivery Status</p>
                            <div class="mb-3 inline-flex rounded-full px-3 py-1 text-sm font-semibold <?php
                                                                                                        $delStatus = $order['delivery_status'] ?? 'pending';
                                                                                                        $delBadges = [
                                                                                                            'pending' => 'bg-gray-100 text-gray-700',
                                                                                                            'processing' => 'bg-blue-100 text-blue-700',
                                                                                                            'shipped' => 'bg-purple-100 text-purple-700',
                                                                                                            'out_for_delivery' => 'bg-yellow-100 text-yellow-700',
                                                                                                            'delivered' => 'bg-green-100 text-green-700',
                                                                                                            'cancelled' => 'bg-red-100 text-red-700',
                                                                                                        ];
                                                                                                        echo $delBadges[$delStatus] ?? 'bg-gray-100 text-gray-700';
                                                                                                        ?>">
                                <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $delStatus))); ?>
                            </div>
                            <form method="POST" class="flex flex-col gap-2">
                                <input type="hidden" name="action" value="update_delivery_status">
                                <input type="hidden" name="order_id" value="<?php echo (int) $order['id']; ?>">
                                <select name="delivery_status" class="text-sm border border-gray-300 rounded px-2 py-2 cursor-pointer">
                                    <option value="pending" <?php echo ($order['delivery_status'] ?? 'pending') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="processing" <?php echo ($order['delivery_status'] ?? 'pending') === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                    <option value="shipped" <?php echo ($order['delivery_status'] ?? 'pending') === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                    <option value="out_for_delivery" <?php echo ($order['delivery_status'] ?? 'pending') === 'out_for_delivery' ? 'selected' : ''; ?>>Out for Delivery</option>
                                    <option value="delivered" <?php echo ($order['delivery_status'] ?? 'pending') === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                    <option value="cancelled" <?php echo ($order['delivery_status'] ?? 'pending') === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded font-semibold hover:bg-blue-700 transition-colors">Update Delivery Status</button>
                            </form>
                        </div>

                        <!-- Totals -->
                        <div class="mb-4 bg-gray-50 rounded-lg p-3">
                            <p class="text-xs uppercase text-gray-500 font-semibold mb-2">Totals</p>
                            <div class="space-y-1 text-sm">
                                <div class="flex justify-between">
                                    <span>Subtotal:</span>
                                    <span class="font-semibold">LKR <?php echo number_format((float) $order['total_price'], 2); ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Shipping:</span>
                                    <span class="font-semibold">LKR <?php echo number_format((float) $order['shipping_fee'], 2); ?></span>
                                </div>
                                <div class="border-t border-gray-200 pt-2 mt-2 flex justify-between font-bold text-green-600">
                                    <span>Total:</span>
                                    <span>LKR <?php echo number_format((float) $order['grand_total'], 2); ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Dates -->
                        <div class="mb-4 grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <p class="text-xs uppercase text-gray-500 font-semibold">Order Date</p>
                                <p class="text-gray-800 font-medium"><?php echo htmlspecialchars($order['created_at']); ?></p>
                            </div>
                            <?php if (!empty($order['confirmed_at'])): ?>
                                <div>
                                    <p class="text-xs uppercase text-gray-500 font-semibold">Confirmed</p>
                                    <p class="text-gray-800 font-medium"><?php echo htmlspecialchars($order['confirmed_at']); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Action -->
                        <?php if ($order['order_status'] !== 'confirmed'): ?>
                            <form method="POST" class="w-full">
                                <input type="hidden" name="action" value="confirm_order">
                                <input type="hidden" name="order_id" value="<?php echo (int) $order['id']; ?>">
                                <button type="submit" class="w-full rounded-lg bg-green-600 px-4 py-3 text-sm font-semibold text-white hover:bg-green-700 transition-colors">
                                    ✓ Confirm Shipping
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="rounded-lg bg-green-50 px-4 py-3 text-center">
                                <p class="font-semibold text-green-700">✓ Confirmed</p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</body>

</html>