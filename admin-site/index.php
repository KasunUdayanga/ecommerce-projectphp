<?php
require_once __DIR__ . '/../shared-core/includes/admin.php';

requireAdminLogin();

$searchQuery = trim($_GET['q'] ?? '');
$message = $_GET['message'] ?? '';

if ($searchQuery !== '') {
    $products = searchProductsByName($searchQuery, 200);
} else {
    $products = getAllProducts();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Products</title>
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

        .product-card {
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            background: #ffffff;
            overflow: hidden;
            transition: transform .3s ease, box-shadow .3s ease, border-color .3s ease;
            display: flex;
            flex-direction: column;
        }

        .product-card:hover {
            transform: translateY(-4px);
            border-color: #86efac;
            box-shadow: 0 12px 28px rgba(22, 163, 74, .12);
        }

        .product-image {
            overflow: hidden;
            border-radius: .8rem;
            background: #f8fafc;
            height: 12rem;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform .45s ease;
        }

        .product-card:hover .product-image img {
            transform: scale(1.06);
        }
    </style>
</head>

<body class="bg-white text-black">
    <header class="bg-gradient-to-r from-green-100 to-green-600 text-white p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 sm:gap-2">
        <h1 class="text-2xl sm:text-3xl font-bold">Admin Dashboard</h1>
        <div class="flex flex-wrap gap-2 w-full sm:w-auto">
            <a href="/ecommerce-projectphp/user-site/index.php" class="bg-white text-green-600 px-4 py-2 rounded-lg hover:bg-gray-100 font-medium transition-colors text-sm sm:text-base">View Store</a>
            <a href="/ecommerce-projectphp/admin-site/admin/orders.php" class="bg-white text-green-600 px-4 py-2 rounded-lg hover:bg-gray-100 font-medium transition-colors text-sm sm:text-base">Orders</a>
            <a href="/ecommerce-projectphp/admin-site/admin/create.php" class="bg-white text-green-600 px-4 py-2 rounded-lg hover:bg-gray-100 font-medium transition-colors text-sm sm:text-base">Add Product</a>
            <a href="/ecommerce-projectphp/admin-site/admin/logout.php" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-100 font-medium transition-colors text-sm sm:text-base">Logout</a>
        </div>
    </header>

    <main class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
        <div class="flex flex-col gap-4 mb-8 md:flex-row md:items-center md:justify-between">
            <div>
                <span class="soft-badge">Inventory</span>
                <h2 class="mt-2 text-3xl sm:text-4xl font-bold">Products Management</h2>
            </div>
            <div class="flex items-center gap-3">
                <form method="GET" action="index.php" class="flex items-center gap-2">
                    <input type="text" name="q" value="<?php echo htmlspecialchars($searchQuery ?? ''); ?>" placeholder="Search products by name or id" class="rounded-lg border border-gray-300 px-4 py-3 text-sm text-sm focus:border-green-500 focus:outline-none">
                    <button type="submit" class="justify-center rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-700 hover:bg-green-100 transition-colors whitespace-nowrap">Search</button>
                </form>
                <?php if (!empty($searchQuery)): ?>
                    <a href="index.php" class="text-sm text-gray-600 hover:underline">Clear</a>
                <?php endif; ?>
                <a href="/ecommerce-projectphp/admin-site/admin/orders.php" class="inline-flex items-center justify-center rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-700 hover:bg-green-100 transition-colors whitespace-nowrap">
                    Open Order Confirmations
                </a>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 text-green-800 text-sm sm:text-base">
                <p class="font-semibold">✓ <?php echo htmlspecialchars($message); ?></p>
            </div>
        <?php endif; ?>

        <?php if (empty($products)): ?>
            <div class="admin-shell text-center py-12">
                <p class="text-lg sm:text-xl text-gray-600 mb-6">No products found yet</p>
                <a href="create.php" class="inline-flex bg-green-500 text-white py-3 px-8 rounded-lg hover:bg-green-600 transition-colors font-semibold text-sm sm:text-base">
                    Add Your First Product
                </a>
            </div>
        <?php else: ?>
            <div class="admin-shell">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card p-4 sm:p-5">
                            <div class="product-image">
                                <?php if (!empty($product['image'])): ?>
                                    <img src="<?php echo htmlspecialchars(getProductImageUrl($product['image'])); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-40 sm:h-48 object-cover">
                                <?php else: ?>
                                    <div class="w-full h-40 sm:h-48 bg-gray-100 flex items-center justify-center text-gray-500 text-sm">No image</div>
                                <?php endif; ?>
                            </div>
                            <div class="flex flex-col flex-grow p-2">
                                <h3 class="font-bold text-lg sm:text-xl text-gray-800 line-clamp-2"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p class="text-xs sm:text-sm text-gray-600 mt-2 line-clamp-2"><?php echo htmlspecialchars($product['description']); ?></p>

                                <div class="mt-4 space-y-2">
                                    <div class="flex justify-between text-xs sm:text-sm">
                                        <span class="text-gray-600">Price:</span>
                                        <span class="font-bold text-green-600">LKR <?php echo number_format($product['price'], 2); ?></span>
                                    </div>
                                    <div class="flex justify-between text-xs sm:text-sm">
                                        <span class="text-gray-600">Stock:</span>
                                        <span class="font-bold <?php echo $product['stock'] > 0 ? 'text-green-600' : 'text-red-600'; ?>"><?php echo (int) $product['stock']; ?> units</span>
                                    </div>
                                </div>

                                <div class="mt-4 pt-4 border-t border-gray-200 flex flex-col sm:flex-row gap-2">
                                    <a href="edit.php?id=<?php echo $product['id']; ?>" class="flex-1 bg-blue-500 text-white py-2 sm:py-3 px-3 rounded-lg hover:bg-blue-100 transition-colors font-medium text-xs sm:text-sm text-center">
                                        ✎ Edit
                                    </a>
                                    <form action="delete.php" method="post" class="flex-1" onsubmit="return confirm('Delete this product?');">
                                        <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                        <button type="submit" class="w-full bg-red-500 text-white py-2 sm:py-3 px-3 rounded-lg hover:bg-red-600 transition-colors font-medium text-xs sm:text-sm">
                                            🗑 Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </main>
</body>

</html>