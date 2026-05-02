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

        .form-shell {
            border: 1px solid #dcfce7;
            border-radius: 1.2rem;
            background: linear-gradient(180deg, #f0fdf4 0%, #ffffff 45%);
            box-shadow: 0 10px 28px rgba(22, 163, 74, .08);
            padding: 2rem;
        }

        .form-input {
            border: 1px solid #e5e7eb;
            border-radius: 0.8rem;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: border-color .3s ease, box-shadow .3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: #86efac;
            box-shadow: 0 0 0 3px rgba(134, 239, 172, .1);
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
    </style>
</head>

<body class="bg-white text-black">
    <header class="bg-gradient-to-r from-green-100 to-green-600 text-white p-4 flex items-center justify-between">
        <h1 class="text-2xl font-bold">Edit Product</h1>
        <a href="index.php" class="bg-white text-green-600 px-4 py-2 rounded-lg hover:bg-gray-100 font-medium transition-colors">← Back</a>
    </header>

    <main class="container mx-auto px-4 py-10">
        <div class="flex flex-col gap-2 mb-6">
            <span class="soft-badge">Update</span>
            <h2 class="text-3xl font-bold">Edit Product Details</h2>
        </div>

        <?php if ($error): ?>
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-red-800">
                <p class="font-semibold">✕ <?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>

        <?php if ($product): ?>
            <div class="max-w-3xl">
                <form method="POST" action="edit.php?id=<?php echo $product['id']; ?>" class="form-shell space-y-6" enctype="multipart/form-data">
                    <div>
                        <label class="form-label" for="name">Product Name</label>
                        <input class="form-input w-full" type="text" name="name" id="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                    </div>

                    <div>
                        <label class="form-label" for="description">Description</label>
                        <textarea class="form-input w-full" name="description" id="description" rows="5" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="form-label" for="price">Price (LKR)</label>
                            <input class="form-input w-full" type="number" step="0.01" name="price" id="price" value="<?php echo htmlspecialchars($product['price']); ?>" required>
                        </div>
                        <div>
                            <label class="form-label" for="stock">Stock Quantity</label>
                            <input class="form-input w-full" type="number" name="stock" id="stock" min="0" value="<?php echo htmlspecialchars($product['stock']); ?>" required>
                        </div>
                    </div>

                    <div>
                        <label class="form-label" for="image">Product Image</label>
                        <?php if (!empty($product['image'])): ?>
                            <div class="mb-4">
                                <p class="text-sm text-gray-600 mb-2">Current Image:</p>
                                <img src="<?php echo htmlspecialchars(getProductImageUrl($product['image'], '../')); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="rounded-lg max-w-xs border border-gray-200">
                            </div>
                        <?php endif; ?>
                        <div class="border-2 border-dashed border-green-300 rounded-lg p-6 text-center bg-green-50 cursor-pointer hover:border-green-500 transition-colors">
                            <input class="hidden" type="file" name="image" id="image" accept="image/png,image/jpeg,image/webp">
                            <label for="image" class="cursor-pointer block">
                                <p class="text-gray-700 font-medium mb-1">📸 Click to upload or drag and drop</p>
                                <p class="text-sm text-gray-600">Replace with new image (PNG, JPEG, or WebP)</p>
                            </label>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button type="submit" class="flex-1 bg-green-500 text-white py-3 rounded-lg hover:bg-green-600 transition-colors font-semibold">
                            ✓ Save Changes
                        </button>
                        <a href="index.php" class="flex-1 bg-gray-200 text-gray-800 py-3 rounded-lg hover:bg-gray-300 transition-colors font-semibold text-center">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </main>
</body>

</html>