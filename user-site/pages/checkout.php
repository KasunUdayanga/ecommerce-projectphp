<?php
session_start();
require_once __DIR__ . '/../../shared-core/includes/functions.php';

if (!isUserLoggedIn()) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$userName = getLoggedInUserName();
$cartItems = getCartItems();
$shippingFee = 250.00;
$subTotal = array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $cartItems));
$grandTotal = $subTotal + $shippingFee;
$paymentMethod = 'cod';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_contact'])) {
        $newAddress = trim($_POST['address'] ?? '');
        $newPhone = trim($_POST['phone'] ?? '');
        if ($newAddress === '' || $newPhone === '') {
            $contactError = 'Please provide both address and mobile number.';
        } else {
            if (updateUserContact((int)$userId, $newAddress, $newPhone)) {
                $contactSuccess = 'Contact details updated.';
                $user = getUserById((int)$userId);
            } else {
                $contactError = 'Failed to update contact details.';
            }
        }
    } elseif (isset($_POST['place_order'])) {
        if (empty($cartItems)) {
            $_SESSION['checkout_error'] = 'Your cart is empty.';
            header('Location: cart.php');
            exit;
        }

        $paymentMethod = trim((string) ($_POST['payment_method'] ?? 'cod'));
        $allowedPaymentMethods = ['cod', 'card', 'bank_transfer'];
        if (!in_array($paymentMethod, $allowedPaymentMethods, true)) {
            $paymentMethod = 'cod';
        }

        $receiptTmpPath = '';
        $receiptExtension = '';
        if ($paymentMethod === 'bank_transfer') {
            $receiptError = (int) ($_FILES['bank_receipt']['error'] ?? UPLOAD_ERR_NO_FILE);
            $receiptName = (string) ($_FILES['bank_receipt']['name'] ?? '');
            $receiptTmpPath = (string) ($_FILES['bank_receipt']['tmp_name'] ?? '');
            $receiptExtension = strtolower(pathinfo($receiptName, PATHINFO_EXTENSION));
            $allowedReceiptExtensions = ['jpg', 'jpeg', 'png', 'pdf'];

            if ($receiptError !== UPLOAD_ERR_OK || $receiptName === '' || $receiptTmpPath === '') {
                $contactError = 'Please upload your bank transfer receipt.';
            } elseif (!in_array($receiptExtension, $allowedReceiptExtensions, true)) {
                $contactError = 'Receipt must be a JPG, PNG, or PDF file.';
            }
        }

        if (!empty($contactError ?? '')) {
        } else {
            $postedAddress = trim($_POST['address'] ?? '');
            $postedPhone = trim($_POST['phone'] ?? '');
            if ($postedAddress !== '' && $postedPhone !== '') {
                updateUserContact((int)$userId, $postedAddress, $postedPhone);
            }

            // Create the order
            $orderId = createOrder($userId, $cartItems, $shippingFee, $paymentMethod);

            if ($paymentMethod === 'bank_transfer' && $receiptTmpPath !== '') {
                $receiptUploadDir = __DIR__ . '/../uploads/receipts';
                if (!is_dir($receiptUploadDir)) {
                    mkdir($receiptUploadDir, 0777, true);
                }

                $receiptFileName = 'receipt_order_' . $orderId . '.' . $receiptExtension;
                $receiptDestination = $receiptUploadDir . '/' . $receiptFileName;
                move_uploaded_file($receiptTmpPath, $receiptDestination);
            }

            // Clear the cart
            clearCart();

            // If card payment chosen, send user to post-order payment flow
            if ($paymentMethod === 'card') {
                header("Location: pay_order.php?order_id=$orderId");
                exit;
            }

            // Redirect to order confirmation page for other methods
            header("Location: order_confirmation.php?order_id=$orderId");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="/ecommerce-projectphp/shared-core/assets/css/styles.css" rel="stylesheet">
    <title>Checkout</title>
    <link rel="icon" href="/ecommerce-projectphp/shared-core/assets/titlelog.png" type="image/png">
</head>

<body class="bg-white text-black">
    <?php require_once __DIR__ . '/../../shared-core/includes/layout/header.php'; ?>
    <?php
    // load user contact info
    $user = getUserById((int)$userId);
    $contactError = $contactError ?? '';
    $contactSuccess = $contactSuccess ?? '';
    global $db_config;
    $config = is_array($db_config) ? $db_config : [];
    ?>

    <div class="container mx-auto p-4">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
            <h1 class="text-2xl font-bold">Checkout</h1>
            <span class="text-sm text-gray-500">Signed in as <?php echo htmlspecialchars($userName); ?></span>
        </div>

        <?php if ($contactSuccess): ?>
            <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-4 text-green-800">
                <?php echo htmlspecialchars($contactSuccess); ?>
            </div>
        <?php endif; ?>
        <?php if ($contactError): ?>
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-red-800">
                <?php echo htmlspecialchars($contactError); ?>
            </div>
        <?php endif; ?>

        <!-- Responsive layout: contact (left) + summary (right) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <form method="POST" id="checkoutForm" enctype="multipart/form-data">
                    <div class="mb-6 p-4 rounded-lg border border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold mb-2 flex items-center gap-2">
                            <!-- location icon -->
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243A8 8 0 1117.657 16.657z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Shipping Contact
                        </h3>

                        <div class="mb-3">
                            <label for="fullNameDisplay" class="text-sm font-medium text-gray-700">Full name</label>
                            <input id="fullNameDisplay" type="text" readonly class="w-full mt-1 p-3 border rounded bg-gray-100 text-gray-800 font-semibold" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="address" class="text-sm font-medium text-gray-700">Address</label>
                            <textarea id="address" name="address" required class="w-full mt-1 p-3 border rounded placeholder-gray-400" placeholder="Street, city, postal code"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="text-sm font-medium text-gray-700">Mobile Number</label>
                            <div class="relative">
                                <input id="phone" name="phone" type="tel" required pattern="\+?[0-9\s\-]{7,20}" title="Enter a valid phone number" class="w-full mt-1 p-3 border rounded" value="<?php echo htmlspecialchars($user['phone_number'] ?? ''); ?>" placeholder="e.g. +94 77 123 4567">
                                <div class="absolute right-3 top-3 text-gray-400"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h2l.4 2M7 13h10l4-8H5.4M7 13l-1.2 6m1.2-6L9 5m0 0l2 8m2-8l1.6 6M9 5h6"></path>
                                    </svg></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">We will use this mobile number to contact you about delivery.</p>
                        </div>

                        <div class="flex gap-2 mt-4">
                            <button type="submit" name="update_contact" class="bg-white border border-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-50">Save Contact</button>
                        </div>
                    </div>

                    <div class="mb-6 p-4 rounded-lg border border-gray-200 bg-white">
                        <h3 class="text-lg font-semibold mb-3">Payment Method</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label class="flex items-start gap-3 rounded-lg border border-gray-200 p-3 cursor-pointer hover:border-green-400 has-[:checked]:border-green-500 has-[:checked]:bg-green-50">
                                <input type="radio" name="payment_method" value="cod" class="mt-1" checked>
                                <span>
                                    <span class="block font-semibold">Cash on Delivery</span>
                                    <span class="block text-sm text-gray-600">Pay when your order is delivered.</span>
                                </span>
                            </label>
                            <label class="flex items-start gap-3 rounded-lg border border-gray-200 p-3 cursor-pointer hover:border-green-400 has-[:checked]:border-green-500 has-[:checked]:bg-green-50">
                                <input type="radio" name="payment_method" value="card" class="mt-1">
                                <span>
                                    <span class="block font-semibold">Card Payment</span>
                                    <span class="block text-sm text-gray-600">you'll complete card payment after placing the order.</span>
                                </span>
                            </label>
                            <label class="flex items-start gap-3 rounded-lg border border-gray-200 p-3 cursor-pointer hover:border-green-400 has-[:checked]:border-green-500 has-[:checked]:bg-green-50">
                                <input type="radio" name="payment_method" value="bank_transfer" class="mt-1">
                                <span>
                                    <span class="block font-semibold">Bank Account Transfer</span>
                                    <span class="block text-sm text-gray-600">Transfer to our bank account after placing the order.</span>
                                </span>
                            </label>
                        </div>

                        <div id="bankDetails" class="mt-4 hidden rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900">
                            <p class="font-semibold mb-1">Bank Transfer Details</p>
                            <p>Bank: <?php echo htmlspecialchars($config['bank_name'] ?? ''); ?></p>
                            <p>Account Name: <?php echo htmlspecialchars($config['bank_account_name'] ?? ''); ?></p>
                            <p>Account Number: <?php echo htmlspecialchars($config['bank_account_number'] ?? ''); ?></p>
                            <p>Branch: <?php echo htmlspecialchars($config['bank_branch'] ?? ''); ?></p>
                        </div>

                        <div id="bankReceiptSection" class="mt-4 hidden rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm text-gray-800">
                            <label for="bank_receipt" class="block font-semibold mb-2">Upload Bank Transfer Receipt</label>
                            <input id="bank_receipt" name="bank_receipt" type="file" accept=".jpg,.jpeg,.png,.pdf" class="block w-full rounded border border-gray-300 bg-white text-sm text-gray-700 file:mr-3 file:rounded file:border-0 file:bg-green-600 file:px-4 file:py-2 file:text-white hover:file:bg-green-700">
                            <p class="mt-2 text-xs text-gray-500">Upload a clear image or PDF of your transfer receipt.</p>
                        </div>

                        <div id="gatewayNote" class="mt-4 hidden rounded-lg border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-900"></div>
                        <div class="mt-5 flex justify-end">
                            <button type="submit" id="placeOrderBtn" name="place_order" class="inline-flex w-full sm:w-auto items-center justify-center rounded-lg bg-green-600 px-6 py-3 text-sm text-white shadow-sm transition hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:bg-green-400">
                                Place Order
                            </button>
                        </div>
                    </div>


                    <div class="block lg:hidden mb-6 p-4 rounded-lg border border-gray-200 bg-white">
                        <h4 class="text-lg font-semibold mb-2">Order Summary</h4>
                        <ul class="mb-3 space-y-2">
                            <?php foreach ($cartItems as $item): ?>
                                <li class="flex justify-between border-b border-gray-100 pb-2">
                                    <span class="text-sm"><?php echo htmlspecialchars($item['name']); ?> (x<?php echo (int) $item['quantity']; ?>)</span>
                                    <span class="text-sm font-medium"><?php echo formatCurrency($item['price'] * $item['quantity']); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="text-sm text-gray-700">
                            <p class="flex justify-between"><span>Subtotal</span><span class="font-semibold"><?php echo formatCurrency($subTotal); ?></span></p>
                            <p class="flex justify-between"><span>Shipping</span><span class="font-semibold"><?php echo formatCurrency($shippingFee); ?></span></p>
                            <p class="flex justify-between border-t border-gray-100 pt-2 text-base"><span class="font-bold">Total</span><span class="font-bold text-green-600"><?php echo formatCurrency($grandTotal); ?></span></p>
                        </div>
                    </div>
                </form>
            </div>

            <aside class="hidden lg:block">
                <div class="sticky top-6 p-5 rounded-lg border border-gray-200 bg-white shadow-sm">
                    <h4 class="text-lg font-semibold mb-3">Your Order</h4>
                    <ul class="mb-3 divide-y divide-gray-100">
                        <?php foreach ($cartItems as $item): ?>
                            <li class="py-2 flex justify-between">
                                <span class="text-sm"><?php echo htmlspecialchars($item['name']); ?> (x<?php echo (int) $item['quantity']; ?>)</span>
                                <span class="text-sm font-medium"><?php echo formatCurrency($item['price'] * $item['quantity']); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="text-sm text-gray-700 space-y-2">
                        <p class="flex justify-between"><span>Subtotal</span><span class="font-semibold"><?php echo formatCurrency($subTotal); ?></span></p>
                        <p class="flex justify-between"><span>Shipping</span><span class="font-semibold"><?php echo formatCurrency($shippingFee); ?></span></p>
                        <p class="flex justify-between border-t border-gray-100 pt-2 text-base"><span class="font-bold">Total</span><span class="font-bold text-green-600"><?php echo formatCurrency($grandTotal); ?></span></p>
                    </div>
                </div>
            </aside>
        </div>
    </div>
    <?php require_once __DIR__ . '/../../shared-core/includes/layout/footer.php'; ?>
    <script>
        (function() {
            const form = document.getElementById('checkoutForm');
            if (!form) return;
            const address = document.getElementById('address');
            const phone = document.getElementById('phone');
            const placeBtn = document.getElementById('placeOrderBtn');
            const bankDetails = document.getElementById('bankDetails');
            const bankReceiptSection = document.getElementById('bankReceiptSection');
            const bankReceiptInput = document.getElementById('bank_receipt');
            const gatewayNote = document.getElementById('gatewayNote');
            const paymentMethods = Array.from(form.querySelectorAll('input[name="payment_method"]'));

            function validate() {
                const a = address && address.value.trim().length > 3;
                const p = phone && phone.value.trim().length > 6;
                const selected = paymentMethods.find((method) => method.checked);
                const value = selected ? selected.value : 'cod';
                const receiptReady = value !== 'bank_transfer' || (bankReceiptInput && bankReceiptInput.files && bankReceiptInput.files.length > 0);
                const ready = a && p && receiptReady;

                if (placeBtn) placeBtn.disabled = !ready;
                if (placeBtn) placeBtn.style.opacity = ready ? '1' : '0.6';
            }

            function updatePaymentUI() {
                const selected = paymentMethods.find((method) => method.checked);
                const value = selected ? selected.value : 'cod';

                const isBankTransfer = value === 'bank_transfer';

                if (bankDetails) {
                    bankDetails.classList.toggle('hidden', !isBankTransfer);
                }

                if (bankReceiptSection) {
                    bankReceiptSection.classList.toggle('hidden', !isBankTransfer);
                }

                if (bankReceiptInput) {
                    bankReceiptInput.required = isBankTransfer;
                    if (!isBankTransfer) {
                        bankReceiptInput.value = '';
                    }
                }

                if (gatewayNote) {
                    if (value === 'card') {
                        gatewayNote.textContent = 'After placing the order you will be prompted to complete card payment.';
                        gatewayNote.classList.remove('hidden');
                    } else {
                        gatewayNote.classList.add('hidden');
                        gatewayNote.textContent = '';
                    }
                }
            }

            address && address.addEventListener('input', validate);
            phone && phone.addEventListener('input', validate);
            bankReceiptInput && bankReceiptInput.addEventListener('change', validate);
            paymentMethods.forEach((method) => method.addEventListener('change', updatePaymentUI));
            validate();
            updatePaymentUI();
        })();
    </script>
</body>

</html>