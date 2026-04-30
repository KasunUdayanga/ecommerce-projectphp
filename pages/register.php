<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';

// Clear stale errors
unset($_SESSION['register_error']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /index.php');
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';

// Validate required fields
if ($name === '' || $email === '' || $password === '') {
    $_SESSION['register_error'] = 'Please fill all required fields.';
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/index.php'));
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['register_error'] = 'Please enter a valid email address.';
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/index.php'));
    exit;
}

// Validate password strength
if (strlen($password) < 6) {
    $_SESSION['register_error'] = 'Password must be at least 6 characters.';
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/index.php'));
    exit;
}

// Confirm passwords match
if ($password !== $password_confirm) {
    $_SESSION['register_error'] = 'Passwords do not match.';
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/index.php'));
    exit;
}

$created = registerUser($name, $email, $password);
if (!$created) {
    $_SESSION['register_error'] = 'Email already registered or server error.';
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/index.php'));
    exit;
}

// Log the user in
$_SESSION['user_id'] = $created;
$_SESSION['user_name'] = $name;
unset($_SESSION['register_error']);

header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/index.php'));
exit;
