<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - eCommerce Project</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body class="bg-white text-black">
    <header class="p-4 bg-green-500">
        <h1 class="text-3xl font-bold text-white">Welcome to Our eCommerce Store</h1>
        <nav class="mt-2">
            <ul class="flex space-x-4">
                <li><a href="index.php" class="text-white hover:text-light-green-500">Home</a></li>
                <li><a href="product.php" class="text-white hover:text-light-green-500">Products</a></li>
                <li><a href="cart.php" class="text-white hover:text-light-green-500">Cart</a></li>
            </ul>
        </nav>
    </header>
    <main class="p-4">
        <h2 class="text-2xl font-semibold">Featured Products</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
            <!-- Example product card -->
            <div class="border border-gray-300 rounded-lg p-4">
                <h3 class="font-bold">Product Name</h3>
                <p class="mt-2">Product description goes here.</p>
                <p class="mt-2 text-lg font-semibold">$19.99</p>
                <button class="mt-4 bg-green-500 text-white py-2 px-4 rounded hover:bg-light-green-500">Add to Cart</button>
            </div>
            <!-- Repeat product cards as needed -->
        </div>
    </main>
    <footer class="p-4 bg-black text-white text-center">
        <p>&copy; 2023 eCommerce Project. All rights reserved.</p>
    </footer>
</body>
</html>