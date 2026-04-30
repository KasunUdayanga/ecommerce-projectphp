<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /index.php');
    exit;
}

// Initialize error message
$error = null;

// Retrieve and sanitize input
$name = trim($_POST['name'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';

// Validate required fields
if ($name === '' || $email === '' || $password === '') {
    $error = 'Please fill all required fields.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // Validate email format
    $error = 'Please enter a valid email address.';
} elseif (strlen($password) < 6) {
    // Validate password length
    $error = 'Password must be at least 6 characters.';
} elseif ($password !== $password_confirm) {
    // Confirm passwords match
    $error = 'Passwords do not match.';
}

// Validate password strength
if (!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[\W]/', $password)) {
    $error = 'Password must include at least one uppercase letter, one number, and one special character.';
}

// If there are validation errors, redirect back with the error
if ($error) {
    $_SESSION['register_error'] = $error;
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/index.php'));
    exit;
}

// Attempt to register the user
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

// Redirect to the homepage or the referring page
header('Location: /index.php');
exit;
