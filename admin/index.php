<?php
require_once __DIR__ . '/../includes/admin.php';

requireAdminLogin();

$products = getAllProducts();
$message = $_GET['message'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Products</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body class="bg-white text-black">
    <header class="bg-black text-white p-4 flex items-center justify-between">
        <h1 class="text-xl font-bold">Admin Dashboard</h1>
        <div class="flex gap-3">
            <a href="../index.php" class="bg-gray-700 px-3 py-1 rounded hover:bg-gray-600">View Store</a>
            <a href="create.php" class="bg-green-500 px-3 py-1 rounded hover:bg-green-600">Add Product</a>
            <a href="logout.php" class="bg-red-500 px-3 py-1 rounded hover:bg-red-600">Logout</a>
        </div>
    </header>

    <main class="p-6">
        <h2 class="text-2xl font-semibold mb-4">Products</h2>
        <?php if ($message): ?>
            <p class="mb-4 text-green-600"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <?php if (empty($products)): ?>
            <p class="text-gray-600">No products found. Add your first product.</p>
        <?php else: ?>
            <table class="min-w-full border border-gray-300">
                <thead>
                    <tr class="bg-green-500 text-white">
                        <th class="border px-4 py-2">Image</th>
                        <th class="border px-4 py-2">Name</th>
                        <th class="border px-4 py-2">Price</th>
                        <th class="border px-4 py-2">Stock</th>
                        <th class="border px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td class="border px-4 py-2">
                                <?php if (!empty($product['image'])): ?>
                                    <img src="<?php echo htmlspecialchars(getProductImageUrl($product['image'], '../')); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="h-16 w-24 rounded object-cover">
                                <?php else: ?>
                                    <span class="text-xs text-gray-500">No image</span>
                                <?php endif; ?>
                            </td>
                            <td class="border px-4 py-2"><?php echo htmlspecialchars($product['name']); ?></td>
                            <td class="border px-4 py-2">$<?php echo number_format($product['price'], 2); ?></td>
                            <td class="border px-4 py-2"><?php echo (int) $product['stock']; ?></td>
                            <td class="border px-4 py-2">
                                <div class="flex flex-wrap gap-2">
                                    <a href="edit.php?id=<?php echo $product['id']; ?>" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">Edit</a>
                                    <form action="delete.php" method="post" onsubmit="return confirm('Delete this product?');">
                                        <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                        <button type="submit" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>
</body>

</html>