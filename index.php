<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Fetch featured products
$products = fetchFeaturedProducts();
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
    <title>eCommerce Project</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="assets/css/styles.css" rel="stylesheet">
</head>

<body class="bg-white text-black">
    <header class="bg-green-500 p-4">
        <h1 class="text-3xl font-bold text-white">Welcome to Our eCommerce Store</h1>
        <nav>
            <ul class="flex space-x-4">
                <li><a href="pages/index.php" class="text-white">Home</a></li>
                <li><a href="pages/cart.php" class="text-white">Cart</a></li>
            </ul>
        </nav>
    </header>

    <main class="p-4">
        <h2 class="text-2xl font-semibold">Featured Products</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <?php foreach ($products as $product): ?>
                <div class="border border-gray-300 p-4 rounded-lg">
                    <h3 class="text-xl font-bold"><?php echo htmlspecialchars($product['name']); ?></h3>
                    <p class="text-green-600"><?php echo htmlspecialchars($product['price']); ?> USD</p>
                    <p><?php echo htmlspecialchars($product['description']); ?></p>
                    <?php if ($showSamples): ?>
                        <form action="pages/cart.php" method="post" class="mt-4">
                            <input type="hidden" name="action" value="add_sample">
                            <input type="hidden" name="sample_id" value="<?php echo htmlspecialchars($product['sample_id']); ?>">
                            <input type="hidden" name="name" value="<?php echo htmlspecialchars($product['name']); ?>">
                            <input type="hidden" name="price" value="<?php echo htmlspecialchars($product['price']); ?>">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="bg-gray-300 text-gray-700 py-2 px-4 rounded hover:bg-gray-400">Add Sample to Cart</button>
                        </form>
                    <?php else: ?>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <a href="pages/product.php?id=<?php echo $product['id']; ?>" class="bg-green-500 text-white py-2 px-4 rounded hover:bg-green-600">View Product</a>
                            <form action="pages/cart.php" method="post">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="bg-black text-white py-2 px-4 rounded hover:bg-gray-800">Add to Cart</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <footer class="bg-black text-white text-center p-4">
        <p>&copy; <?php echo date("Y"); ?> eCommerce Project. All rights reserved.</p>
    </footer>
</body>

</html>