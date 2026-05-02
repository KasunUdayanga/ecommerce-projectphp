<?php
function ensureSessionStarted()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

if (!defined('PRODUCT_UPLOAD_DIR')) {
    define('PRODUCT_UPLOAD_DIR', __DIR__ . '/../uploads/products');
}

if (!defined('MIME_JPEG')) {
    define('MIME_JPEG', 'image/jpeg');
}

if (!defined('MIME_PNG')) {
    define('MIME_PNG', 'image/png');
}

if (!defined('MIME_WEBP')) {
    define('MIME_WEBP', 'image/webp');
}

if (!defined('PRODUCT_ALLOWED_MIME')) {
    define('PRODUCT_ALLOWED_MIME', [MIME_JPEG, MIME_PNG, MIME_WEBP]);
}

if (!defined('PRODUCT_MIME_EXT')) {
    define('PRODUCT_MIME_EXT', [
        MIME_JPEG => 'jpg',
        MIME_PNG => 'png',
        MIME_WEBP => 'webp',
    ]);
}

function getDbConnection()
{
    global $db_config;
    if (!isset($db_config) || !is_array($db_config)) {
        $db_config = require_once __DIR__ . '/config.php';
    }
    $config = is_array($db_config) ? $db_config : [];
    $dbHost = $config['db_host'] ?? '';
    $dbUser = $config['db_user'] ?? '';
    $dbPass = $config['db_pass'] ?? '';
    $dbName = $config['db_name'] ?? '';
    $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    ensureDatabaseSchema($conn);
    ensureDefaultUser($conn);
    return $conn;
}

function ensureDatabaseSchema($conn)
{
    $check = $conn->query("SHOW TABLES LIKE 'products'");
    $productsTableExists = $check && $check->num_rows > 0;

    if (!$productsTableExists) {
        runSchemaSetup($conn);
    }

    $columnCheck = $conn->query("SHOW COLUMNS FROM products LIKE 'image'");
    if ($columnCheck && $columnCheck->num_rows === 0) {
        $conn->query("ALTER TABLE products ADD COLUMN image VARCHAR(255) DEFAULT NULL AFTER description");
    }

    $addressColumnCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'address'");
    if ($addressColumnCheck && $addressColumnCheck->num_rows === 0) {
        $conn->query("ALTER TABLE users ADD COLUMN address VARCHAR(255) NOT NULL DEFAULT '' AFTER email");
    }

    $phoneColumnCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'phone_number'");
    if ($phoneColumnCheck && $phoneColumnCheck->num_rows === 0) {
        $conn->query("ALTER TABLE users ADD COLUMN phone_number VARCHAR(20) NOT NULL DEFAULT '' AFTER address");
    }

    $shippingColumnCheck = $conn->query("SHOW COLUMNS FROM orders LIKE 'shipping_fee'");
    if ($shippingColumnCheck && $shippingColumnCheck->num_rows === 0) {
        $conn->query("ALTER TABLE orders ADD COLUMN shipping_fee DECIMAL(10, 2) NOT NULL DEFAULT 0.00 AFTER total_price");
    }

    $grandTotalColumnCheck = $conn->query("SHOW COLUMNS FROM orders LIKE 'grand_total'");
    if ($grandTotalColumnCheck && $grandTotalColumnCheck->num_rows === 0) {
        $conn->query("ALTER TABLE orders ADD COLUMN grand_total DECIMAL(10, 2) NOT NULL DEFAULT 0.00 AFTER shipping_fee");
    }

    $orderStatusColumnCheck = $conn->query("SHOW COLUMNS FROM orders LIKE 'order_status'");
    if ($orderStatusColumnCheck && $orderStatusColumnCheck->num_rows === 0) {
        $conn->query("ALTER TABLE orders ADD COLUMN order_status VARCHAR(30) NOT NULL DEFAULT 'pending' AFTER grand_total");
    }

    $confirmedAtColumnCheck = $conn->query("SHOW COLUMNS FROM orders LIKE 'confirmed_at'");
    if ($confirmedAtColumnCheck && $confirmedAtColumnCheck->num_rows === 0) {
        $conn->query("ALTER TABLE orders ADD COLUMN confirmed_at TIMESTAMP NULL DEFAULT NULL AFTER order_status");
    }

    // Backfill legacy rows that were saved before shipping columns existed.
    $conn->query("UPDATE orders SET shipping_fee = 250.00 WHERE shipping_fee = 0.00 AND total_price > 0.00 AND grand_total = 0.00");
    $conn->query("UPDATE orders SET grand_total = total_price + shipping_fee WHERE grand_total = 0.00 AND total_price > 0.00");
}

function runSchemaSetup($conn)
{
    $schemaPath = __DIR__ . '/../db/schema.sql';
    if (!file_exists($schemaPath)) {
        return;
    }

    $schemaSql = file_get_contents($schemaPath);
    if ($schemaSql === false) {
        return;
    }

    $statements = array_filter(array_map('trim', explode(';', $schemaSql)));
    foreach ($statements as $statement) {
        if ($statement === '') {
            continue;
        }
        if (!$conn->query($statement)) {
            die('Schema setup failed: ' . $conn->error);
        }
    }
}

function ensureDefaultUser($conn)
{
    $result = $conn->query("SELECT COUNT(*) AS total FROM users");
    if (!$result) {
        return;
    }

    $row = $result->fetch_assoc();
    if ((int) ($row['total'] ?? 0) > 0) {
        return;
    }

    $username = 'customer';
    $email = 'customer@example.com';
    $passwordHash = password_hash('customer123', PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $passwordHash, $email);
    $stmt->execute();
    $stmt->close();
}

function getUserByIdentifier($identifier)
{
    $conn = getDbConnection();
    $sql = "SELECT * FROM users WHERE email = ? OR username = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $identifier, $identifier);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    $conn->close();

    return $user ?: null;
}

function attemptUserLogin($identifier, $password)
{
    ensureSessionStarted();
    $user = getUserByIdentifier($identifier);
    if (!$user) {
        return false;
    }

    if (!password_verify($password, $user['password'])) {
        return false;
    }

    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['user_name'] = $user['username'];
    return true;
}

function isUserLoggedIn()
{
    ensureSessionStarted();
    return !empty($_SESSION['user_id']);
}

function getLoggedInUserName()
{
    ensureSessionStarted();
    return $_SESSION['user_name'] ?? '';
}

function logoutUser()
{
    ensureSessionStarted();
    unset($_SESSION['user_id'], $_SESSION['user_name']);
}

function fetchProducts($limit = 10)
{
    $conn = getDbConnection();
    $sql = "SELECT * FROM products ORDER BY created_at DESC LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $products = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $conn->close();
    return $products;
}

function fetchFeaturedProducts($limit = 6)
{
    return fetchProducts($limit);
}

function searchProductsByName($query, $limit = 200)
{
    $query = trim((string) $query);
    if ($query === '') {
        return fetchProducts((int) $limit);
    }

    $conn = getDbConnection();
    $sql = "SELECT * FROM products WHERE name LIKE ? ORDER BY created_at DESC LIMIT ?";
    $stmt = $conn->prepare($sql);
    $searchTerm = '%' . $query . '%';
    $limit = (int) $limit;
    $stmt->bind_param("si", $searchTerm, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $products = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $conn->close();

    return $products;
}


function getProductById($productId)
{
    if ($productId <= 0) {
        return null;
    }

    $conn = getDbConnection();
    $sql = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();
    $conn->close();

    return $product ?: null;
}

function getAllProducts()
{
    $conn = getDbConnection();
    $sql = "SELECT * FROM products ORDER BY created_at DESC";
    $result = $conn->query($sql);
    $products = [];
    if ($result) {
        $products = $result->fetch_all(MYSQLI_ASSOC);
    }
    $conn->close();
    return $products;
}

function processProductImageUpload($file, $existingPath = null)
{
    $resultPath = $existingPath;
    $mime = getUploadedImageMime($file);
    if (!$mime) {
        return $resultPath;
    }

    if (!isGdAvailableForMime($mime)) {
        return storeOriginalProductImage($file, $existingPath, $mime);
    }

    $sourceImage = createImageFromMime($file['tmp_name'], $mime);
    if ($sourceImage) {
        $srcWidth = imagesx($sourceImage);
        $srcHeight = imagesy($sourceImage);
        $targetRatio = 4 / 3;
        $srcRatio = $srcWidth / $srcHeight;

        if ($srcRatio > $targetRatio) {
            $newWidth = (int) floor($srcHeight * $targetRatio);
            $newHeight = $srcHeight;
            $srcX = (int) floor(($srcWidth - $newWidth) / 2);
            $srcY = 0;
        } else {
            $newWidth = $srcWidth;
            $newHeight = (int) floor($srcWidth / $targetRatio);
            $srcX = 0;
            $srcY = (int) floor(($srcHeight - $newHeight) / 2);
        }

        $targetWidth = 800;
        $targetHeight = 600;
        $targetImage = imagecreatetruecolor($targetWidth, $targetHeight);
        imagecopyresampled(
            $targetImage,
            $sourceImage,
            0,
            0,
            $srcX,
            $srcY,
            $targetWidth,
            $targetHeight,
            $newWidth,
            $newHeight
        );

        if (!is_dir(PRODUCT_UPLOAD_DIR)) {
            mkdir(PRODUCT_UPLOAD_DIR, 0777, true);
        }

        $fileName = 'product_' . uniqid('', true) . '.jpg';
        $filePath = PRODUCT_UPLOAD_DIR . '/' . $fileName;
        imagejpeg($targetImage, $filePath, 85);
        imagedestroy($sourceImage);
        imagedestroy($targetImage);

        if ($existingPath) {
            deleteProductImage($existingPath);
        }

        $resultPath = 'uploads/products/' . $fileName;
    }

    return $resultPath;
}

function getUploadedImageMime($file)
{
    $mime = null;
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return $mime;
    }

    $imageInfo = getimagesize($file['tmp_name']);
    if ($imageInfo !== false) {
        $candidate = $imageInfo['mime'] ?? '';
        if (in_array($candidate, PRODUCT_ALLOWED_MIME, true)) {
            $mime = $candidate;
        }
    }

    return $mime;
}

function isGdAvailableForMime($mime)
{
    $available = function_exists('imagecreatetruecolor')
        && function_exists('imagecopyresampled')
        && function_exists('imagejpeg');

    if ($available && $mime === MIME_PNG) {
        $available = function_exists('imagecreatefrompng');
    }

    if ($available && $mime === MIME_WEBP) {
        $available = function_exists('imagecreatefromwebp');
    }

    if ($available && $mime === MIME_JPEG) {
        $available = function_exists('imagecreatefromjpeg');
    }

    return $available;
}

function createImageFromMime($filePath, $mime)
{
    if ($mime === MIME_PNG) {
        return imagecreatefrompng($filePath);
    }

    if ($mime === MIME_WEBP) {
        return imagecreatefromwebp($filePath);
    }

    return imagecreatefromjpeg($filePath);
}

function storeOriginalProductImage($file, $existingPath, $mime)
{
    $extension = PRODUCT_MIME_EXT[$mime] ?? 'jpg';

    if (!is_dir(PRODUCT_UPLOAD_DIR)) {
        mkdir(PRODUCT_UPLOAD_DIR, 0777, true);
    }

    $fileName = 'product_' . uniqid('', true) . '.' . $extension;
    $filePath = PRODUCT_UPLOAD_DIR . '/' . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        return $existingPath;
    }

    if ($existingPath) {
        deleteProductImage($existingPath);
    }

    return 'uploads/products/' . $fileName;
}

function getProductImageUrl($path, $prefix = '')
{
    if (!$path) {
        return '';
    }

    if (preg_match('/^https?:\/\//', $path)) {
        return $path;
    }

    return $prefix . ltrim($path, '/');
}

function deleteProductImage($path)
{
    if (!$path) {
        return;
    }

    $fullPath = realpath(__DIR__ . '/../' . $path);
    $uploadsRoot = realpath(PRODUCT_UPLOAD_DIR);
    if ($fullPath && $uploadsRoot && strpos($fullPath, $uploadsRoot) === 0 && file_exists($fullPath)) {
        unlink($fullPath);
    }
}

function createProduct($name, $description, $price, $stock, $imagePath = null)
{
    $conn = getDbConnection();
    $sql = "INSERT INTO products (name, description, image, price, stock) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $price = (float) $price;
    $stock = (int) $stock;
    $stmt->bind_param("sssdi", $name, $description, $imagePath, $price, $stock);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $success;
}

function updateProduct($id, $name, $description, $price, $stock, $imagePath = null)
{
    $conn = getDbConnection();
    $sql = "UPDATE products SET name = ?, description = ?, image = ?, price = ?, stock = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $price = (float) $price;
    $stock = (int) $stock;
    $id = (int) $id;
    $stmt->bind_param("sssdii", $name, $description, $imagePath, $price, $stock, $id);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $success;
}

function deleteProduct($id)
{
    $product = getProductById($id);
    if ($product && !empty($product['image'])) {
        deleteProductImage($product['image']);
    }

    $conn = getDbConnection();
    $sql = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $id = (int) $id;
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $success;
}

function addToCart($productId, $quantity = 1)
{
    ensureSessionStarted();
    $quantity = max(1, (int) $quantity);
    $product = getProductById((int) $productId);

    if (!$product) {
        return false;
    }

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$productId] = [
            'id' => (int) $product['id'],
            'name' => $product['name'],
            'price' => (float) $product['price'],
            'quantity' => $quantity,
        ];
    }

    return true;
}

function addSampleToCart($sampleId, $name, $price, $quantity = 1)
{
    ensureSessionStarted();
    $quantity = max(1, (int) $quantity);
    $sampleId = trim((string) $sampleId);
    if ($sampleId === '' || $name === '') {
        return false;
    }

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$sampleId])) {
        $_SESSION['cart'][$sampleId]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$sampleId] = [
            'id' => $sampleId,
            'name' => $name,
            'price' => (float) $price,
            'quantity' => $quantity,
            'is_sample' => true,
        ];
    }

    return true;
}

function removeFromCart($productId)
{
    ensureSessionStarted();
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
    }
}

function updateCart($productId, $quantity)
{
    ensureSessionStarted();
    $quantity = (int) $quantity;
    if ($quantity <= 0) {
        removeFromCart($productId);
        return;
    }

    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['quantity'] = $quantity;
    }
}

function getCartItems()
{
    ensureSessionStarted();
    return isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
}

function clearCart()
{
    ensureSessionStarted();
    unset($_SESSION['cart']);
} {
    // register user, return user id on success or false on failure
    function registerUser(string $name, string $email, string $password, string $address = '', string $phoneNumber = '')
    {
        $conn = getDbConnection();

        // ensure users table exists
        $createSql = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            address VARCHAR(255) NOT NULL DEFAULT '',
            phone_number VARCHAR(20) NOT NULL DEFAULT '',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $conn->query($createSql);

        // check email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->close();
            return false;
        }
        $stmt->close();

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, address, phone_number) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $email, $hash, $address, $phoneNumber);
        $ok = $stmt->execute();
        if (!$ok) {
            $stmt->close();
            return false;
        }
        $id = $stmt->insert_id;
        $stmt->close();
        return $id;
    }

    function getUserByEmail(string $email)
    {
        $conn = getDbConnection();
        $stmt = $conn->prepare("SELECT id, username, email, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc() ?: null;
        $stmt->close();
        return $user;
    }

    // format currency as LKR
    function formatCurrency($amount)
    {
        return 'LKR ' . number_format((float)$amount, 2);
    }
}

function createOrder($userId, $cartItems, $shippingFee = 0.0)
{
    $conn = getDbConnection();
    $totalPrice = 0;
    $shippingFee = (float) $shippingFee;

    // Calculate total price
    foreach ($cartItems as $item) {
        $totalPrice += $item['price'] * $item['quantity'];
    }

    $grandTotal = $totalPrice + $shippingFee;

    // Insert into orders table
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_price, shipping_fee, grand_total) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iddd", $userId, $totalPrice, $shippingFee, $grandTotal);
    $stmt->execute();
    $orderId = $stmt->insert_id;
    $stmt->close();

    // Insert into order_items table and update product stock
    foreach ($cartItems as $item) {
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiid", $orderId, $item['id'], $item['quantity'], $item['price']);
        $stmt->execute();
        $stmt->close();

        // Update product stock
        $stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $stmt->bind_param("ii", $item['quantity'], $item['id']);
        $stmt->execute();
        $stmt->close();
    }

    $conn->close();
    return $orderId;
}

function getAdminOrders()
{
    $conn = getDbConnection();
    $sql = "SELECT o.id, o.user_id, o.total_price, o.shipping_fee, o.grand_total, o.order_status, o.confirmed_at, o.created_at, u.username, u.email, u.address, u.phone_number
            FROM orders o
            INNER JOIN users u ON u.id = o.user_id
            ORDER BY o.created_at DESC";
    $result = $conn->query($sql);
    $orders = [];
    if ($result) {
        $orders = $result->fetch_all(MYSQLI_ASSOC);
    }
    $conn->close();
    return $orders;
}

function confirmAdminOrder($orderId)
{
    $conn = getDbConnection();
    $stmt = $conn->prepare("UPDATE orders SET order_status = 'confirmed', confirmed_at = COALESCE(confirmed_at, NOW()) WHERE id = ?");
    $orderId = (int) $orderId;
    $stmt->bind_param("i", $orderId);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $success;
}
