<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

$products = fetchProducts(9);
$showSamples = false;
if (empty($products)) {
    $products = getSampleProducts();
    $showSamples = true;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - eCommerce Project</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body class="bg-white text-black">
    <header class="p-4 bg-green-500">
        <h1 class="text-3xl font-bold text-white">Welcome to Our eCommerce Store</h1>
        <nav class="mt-2">
            <ul class="flex space-x-4">
                <li><a href="index.php" class="text-white hover:text-green-200">Home</a></li>
                <li><a href="index.php" class="text-white hover:text-green-200">Products</a></li>
                <li><a href="cart.php" class="text-white hover:text-green-200">Cart</a></li>
            </ul>
        </nav>
    </header>
    <main class="p-4">
        <h2 class="text-2xl font-semibold">Featured Products</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
            <?php if (empty($products)): ?>
                <p class="text-gray-600">No products available yet.</p>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <div class="border border-gray-300 rounded-lg p-4">
                        <h3 class="font-bold"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="mt-2"><?php echo htmlspecialchars($product['description']); ?></p>
                        <p class="mt-2 text-lg font-semibold">$<?php echo number_format($product['price'], 2); ?></p>
                        <?php if ($showSamples): ?>
                            <form action="cart.php" method="post" class="mt-4">
                                <input type="hidden" name="action" value="add_sample">
                                <input type="hidden" name="sample_id" value="<?php echo htmlspecialchars($product['sample_id']); ?>">
                                <input type="hidden" name="name" value="<?php echo htmlspecialchars($product['name']); ?>">
                                <input type="hidden" name="price" value="<?php echo htmlspecialchars($product['price']); ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="bg-gray-300 text-gray-700 py-2 px-4 rounded hover:bg-gray-400">Add Sample to Cart</button>
                            </form>
                        <?php else: ?>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <a href="product.php?id=<?php echo $product['id']; ?>" class="bg-green-500 text-white py-2 px-4 rounded hover:bg-green-600">View Product</a>
                                <form action="cart.php" method="post">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="bg-black text-white py-2 px-4 rounded hover:bg-gray-800">Add to Cart</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
    <footer class="p-4 bg-black text-white text-center">
        <p>&copy; <?php echo date('Y'); ?> eCommerce Project. All rights reserved.</p>
    </footer>
</body>

</html>