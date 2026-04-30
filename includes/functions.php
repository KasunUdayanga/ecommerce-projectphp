<?php
function ensureSessionStarted()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
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
    return $conn;
}

function ensureDatabaseSchema($conn)
{
    $check = $conn->query("SHOW TABLES LIKE 'products'");
    if ($check && $check->num_rows > 0) {
        return;
    }

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
