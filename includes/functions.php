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

    if ($dbName === '') {
        die("Database name is missing in config.");
    }

    // 1) Connect to MySQL server without selecting a DB first.
    $conn = new mysqli($dbHost, $dbUser, $dbPass);
    if ($conn->connect_error) {
        die("MySQL server connection failed: " . $conn->connect_error);
    }

    // 2) Create DB if it does not exist.
    $safeDbName = str_replace('`', '``', $dbName);
    $createDbSql = "CREATE DATABASE IF NOT EXISTS `{$safeDbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if (!$conn->query($createDbSql)) {
        die("Database create failed: " . $conn->error);
    }

    // 3) Select the DB.
    if (!$conn->select_db($dbName)) {
        die("Database select failed: " . $conn->error);
    }

    $conn->set_charset('utf8mb4');

    // 4) Create/upgrade tables and seed default user.
    ensureDatabaseSchema($conn);
    ensureDefaultUser($conn);

    return $conn;
}

function ensureDatabaseSchema($conn)
{
    ensureCoreTables($conn);
    runSchemaSetup($conn);

    ensureColumnExists($conn, 'products', 'image', 'image VARCHAR(255) DEFAULT NULL AFTER description');
    ensureColumnExists($conn, 'users', 'address', "address VARCHAR(255) NOT NULL DEFAULT '' AFTER email");
    ensureColumnExists($conn, 'users', 'phone_number', "phone_number VARCHAR(20) NOT NULL DEFAULT '' AFTER address");
    ensureColumnExists($conn, 'orders', 'shipping_fee', 'shipping_fee DECIMAL(10, 2) NOT NULL DEFAULT 0.00 AFTER total_price');
    ensureColumnExists($conn, 'orders', 'grand_total', 'grand_total DECIMAL(10, 2) NOT NULL DEFAULT 0.00 AFTER shipping_fee');
    ensureColumnExists($conn, 'orders', 'payment_method', "payment_method VARCHAR(30) NOT NULL DEFAULT 'cod' AFTER grand_total");
    ensureColumnExists($conn, 'orders', 'payment_status', "payment_status VARCHAR(30) NOT NULL DEFAULT 'pending' AFTER payment_method");
    ensureColumnExists($conn, 'orders', 'order_status', "order_status VARCHAR(30) NOT NULL DEFAULT 'pending' AFTER payment_status");
    ensureColumnExists($conn, 'orders', 'confirmed_at', 'confirmed_at TIMESTAMP NULL DEFAULT NULL AFTER order_status');

    backfillLegacyOrderTotals($conn);
}

function ensureCoreTables($conn)
{
    $tableSqlMap = [
        'users' => "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            address VARCHAR(255) NOT NULL DEFAULT '',
            phone_number VARCHAR(20) NOT NULL DEFAULT '',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        'products' => "CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            image VARCHAR(255) DEFAULT NULL,
            price DECIMAL(10, 2) NOT NULL,
            stock INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        'orders' => "CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            total_price DECIMAL(10, 2) NOT NULL,
            shipping_fee DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            grand_total DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            payment_method VARCHAR(30) NOT NULL DEFAULT 'cod',
            payment_status VARCHAR(30) NOT NULL DEFAULT 'pending',
            order_status VARCHAR(30) NOT NULL DEFAULT 'pending',
            confirmed_at TIMESTAMP NULL DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        'order_items' => "CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL,
            price DECIMAL(10, 2) NOT NULL,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    ];

    foreach ($tableSqlMap as $tableName => $createSql) {
        if (tableExists($conn, $tableName)) {
            continue;
        }

        try {
            $conn->query($createSql);
        } catch (Throwable $exception) {
            // Keep bootstrapping best-effort so a broken legacy table does not stop other tables from being created.
        }
    }
}

function ensureColumnExists($conn, $table, $column, $definition)
{
    if (!tableExists($conn, $table)) {
        return;
    }

    $check = $conn->query("SHOW COLUMNS FROM {$table} LIKE '{$column}'");
    if ($check && $check->num_rows === 0) {
        try {
            $conn->query("ALTER TABLE {$table} ADD COLUMN {$definition}");
        } catch (Throwable $exception) {
            return;
        }
    }
}

function backfillLegacyOrderTotals($conn)
{
    // Backfill legacy rows that were saved before shipping columns existed.
    if (!tableExists($conn, 'orders')) {
        return;
    }

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
        try {
            $conn->query($statement);
        } catch (Throwable $exception) {
            // Keep bootstrapping best-effort so one broken legacy table does not abort the request.
        }
    }
}

function tableExists($conn, $table)
{
    try {
        $escapedTable = $conn->real_escape_string($table);
        $check = $conn->query("SHOW TABLES LIKE '{$escapedTable}'");
        return $check && $check->num_rows > 0;
    } catch (Throwable $exception) {
        return false;
    }
}

function ensureDefaultUser($conn)
{
    if (!tableExists($conn, 'users')) {
        return;
    }

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
    if (!tableExists($conn, 'products')) {
        $conn->close();
        return [];
    }

    try {
        $sql = "SELECT * FROM products ORDER BY created_at DESC LIMIT ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $conn->close();
        return $products;
    } catch (Throwable $exception) {
        $conn->close();
        return [];
    }
}

function fetchFeaturedProducts($limit = 6)
{
    return fetchProducts($limit);
}

function searchProductsByName($query, $limit = 200)
{
    $query = trim((string) $query);
    $products = [];

    if ($query === '') {
        return fetchProducts((int) $limit);
    }

    $conn = getDbConnection();
    if (!tableExists($conn, 'products')) {
        $conn->close();
        return $products;
    }

    try {
        $sql = "SELECT * FROM products WHERE name LIKE ? ORDER BY created_at DESC LIMIT ?";
        $stmt = $conn->prepare($sql);
        $searchTerm = '%' . $query . '%';
        $limit = (int) $limit;
        $stmt->bind_param("si", $searchTerm, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } catch (Throwable $exception) {
        $products = [];
    }

    $conn->close();
    return $products;
}


function getProductById($productId)
{
    $product = null;

    if ($productId <= 0) {
        return null;
    }

    $conn = getDbConnection();
    if (!tableExists($conn, 'products')) {
        $conn->close();
        return $product;
    }

    try {
        $sql = "SELECT * FROM products WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $stmt->close();
        $conn->close();
    } catch (Throwable $exception) {
        $conn->close();
    }

    return $product ?: null;
}

function getAllProducts()
{
    $conn = getDbConnection();
    if (!tableExists($conn, 'products')) {
        $conn->close();
        return [];
    }

    try {
        $sql = "SELECT * FROM products ORDER BY created_at DESC";
        $result = $conn->query($sql);
        $products = [];
        if ($result) {
            $products = $result->fetch_all(MYSQLI_ASSOC);
        }
        $conn->close();
        return $products;
    } catch (Throwable $exception) {
        $conn->close();
        return [];
    }
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
}

function registerUser(string $name, string $email, string $password, string $address = '', string $phoneNumber = '')
{
    $conn = getDbConnection();

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

function getUserById(int $id)
{
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT id, username, email, address, phone_number FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc() ?: null;
    $stmt->close();
    $conn->close();
    return $user;
}

// Update user's address and phone number
function updateUserContact(int $userId, string $address, string $phoneNumber)
{
    $conn = getDbConnection();
    $stmt = $conn->prepare("UPDATE users SET address = ?, phone_number = ? WHERE id = ?");
    $stmt->bind_param("ssi", $address, $phoneNumber, $userId);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $success;
}

function getPaymentMethodLabel(string $paymentMethod)
{
    $labels = [
        'cod' => 'Cash on Delivery',
        'stripe' => 'Card Payment via Stripe',
        'payhere' => 'Card Payment via PayHere',
        'bank_transfer' => 'Bank Account Transfer',
    ];

    return $labels[$paymentMethod] ?? ucfirst(str_replace('_', ' ', $paymentMethod));
}

// format currency as LKR
function formatCurrency($amount)
{
    return 'LKR ' . number_format((float)$amount, 2);
}

function createOrder($userId, $cartItems, $shippingFee = 0.0, $paymentMethod = 'cod')
{
    $conn = getDbConnection();
    $totalPrice = 0;
    $shippingFee = (float) $shippingFee;
    $paymentMethod = trim((string) $paymentMethod) ?: 'cod';
    if ($paymentMethod === 'cod') {
        $paymentStatus = 'cash_on_delivery';
    } elseif ($paymentMethod === 'bank_transfer') {
        $paymentStatus = 'awaiting_transfer';
    } else {
        $paymentStatus = 'pending_payment';
    }

    // Calculate total price
    foreach ($cartItems as $item) {
        $totalPrice += $item['price'] * $item['quantity'];
    }

    $grandTotal = $totalPrice + $shippingFee;

    // Insert into orders table
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_price, shipping_fee, grand_total, payment_method, payment_status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("idddss", $userId, $totalPrice, $shippingFee, $grandTotal, $paymentMethod, $paymentStatus);
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
    $sql = "SELECT o.id, o.user_id, o.total_price, o.shipping_fee, o.grand_total, o.order_status, o.confirmed_at, o.created_at, o.payment_method, o.payment_status, u.username, u.email, u.address, u.phone_number
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
