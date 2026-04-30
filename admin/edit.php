<?php
require_once __DIR__ . '/../includes/admin.php';

requireAdminLogin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$product = $id > 0 ? getProductById($id) : null;
$error = '';

if (!$product) {
    $error = 'Product not found.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $product) {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float) ($_POST['price'] ?? 0);
    $stock = (int) ($_POST['stock'] ?? 0);
    $imagePath = processProductImageUpload($_FILES['image'] ?? [], $product['image'] ?? null);

    if ($name === '' || $description === '') {
        $error = 'Name and description are required.';
    } else {
        if (updateProduct($product['id'], $name, $description, $price, $stock, $imagePath)) {
            header('Location: index.php?message=Product%20updated');
            exit;
        }
        $error = 'Could not update product.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body class="bg-white text-black">
    <header class="bg-black text-white p-4 flex items-center justify-between">
        <h1 class="text-xl font-bold">Edit Product</h1>
        <a href="index.php" class="bg-gray-700 px-3 py-1 rounded hover:bg-gray-600">Back</a>
    </header>

    <main class="p-6 max-w-2xl mx-auto">
        <?php if ($error): ?>
            <p class="mb-4 text-red-600"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <?php if ($product): ?>
            <form method="POST" action="edit.php?id=<?php echo $product['id']; ?>" class="space-y-4" enctype="multipart/form-data">
                <div>
                    <label class="block mb-1 font-semibold" for="name">Product Name</label>
                    <input class="w-full border border-gray-300 rounded p-2" type="text" name="name" id="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                </div>
                <div>
                    <label class="block mb-1 font-semibold" for="description">Description</label>
                    <textarea class="w-full border border-gray-300 rounded p-2" name="description" id="description" rows="4" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>
                <div>
                    <label class="block mb-1 font-semibold" for="price">Price</label>
                    <input class="w-full border border-gray-300 rounded p-2" type="number" step="0.01" name="price" id="price" value="<?php echo htmlspecialchars($product['price']); ?>" required>
                </div>
                <div>
                    <label class="block mb-1 font-semibold" for="stock">Stock</label>
                    <input class="w-full border border-gray-300 rounded p-2" type="number" name="stock" id="stock" min="0" value="<?php echo htmlspecialchars($product['stock']); ?>" required>
                </div>
                <div>
                    <label class="block mb-1 font-semibold" for="image">Product Image (4:3)</label>
                    <?php if (!empty($product['image'])): ?>
                        <img src="<?php echo htmlspecialchars(getProductImageUrl($product['image'], '../')); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="mb-3 rounded-lg max-w-xs">
                    <?php endif; ?>
                    <input class="w-full border border-gray-300 rounded p-2" type="file" name="image" id="image" accept="image/png,image/jpeg,image/webp">
                    <p class="text-xs text-gray-500 mt-1">Uploading a new image replaces the old one.</p>
                </div>
                <button type="submit" class="bg-green-500 text-white py-2 px-4 rounded hover:bg-green-600">Save Changes</button>
            </form>
        <?php endif; ?>
    </main>
</body>

</html>