<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Fetch product ID from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch product details from the database
$product = getProductById($product_id);

if (!$product) {
    echo "Product not found.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="../assets/css/styles.css" rel="stylesheet">
    <title><?php echo htmlspecialchars($product['name']); ?></title>
</head>

<body class="bg-white text-black">
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4"><?php echo htmlspecialchars($product['name']); ?></h1>
        <?php if (!empty($product['image'])): ?>
            <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="mb-4">
        <?php endif; ?>
        <p class="text-lg mb-4"><?php echo htmlspecialchars($product['description']); ?></p>
        <p class="text-xl font-semibold mb-4">Price: $<?php echo number_format($product['price'], 2); ?></p>
        <form action="cart.php" method="post">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
            <input type="hidden" name="quantity" value="1">
            <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Add to Cart</button>
        </form>
        <a href="index.php" class="text-green-500 hover:text-green-600 hover:underline">Back to Products</a>
    </div>
</body>

</html>