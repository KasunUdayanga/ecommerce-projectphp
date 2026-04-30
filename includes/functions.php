<?php
function getDbConnection() {
    include 'config.php';
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

function fetchProducts($limit = 10) {
    $conn = getDbConnection();
    $sql = "SELECT * FROM products LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $products = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $conn->close();
    return $products;
}

function addToCart($productId, $quantity) {
    session_start();
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] += $quantity;
    } else {
        $_SESSION['cart'][$productId] = $quantity;
    }
}

function getCartItems() {
    session_start();
    return isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
}

function clearCart() {
    session_start();
    unset($_SESSION['cart']);
}
?>