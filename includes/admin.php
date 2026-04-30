<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

function getAdminConfig()
{
    global $db_config;
    if (!isset($db_config) || !is_array($db_config)) {
        $db_config = require_once __DIR__ . '/config.php';
    }

    return is_array($db_config) ? $db_config : [];
}

function isAdminLoggedIn()
{
    ensureSessionStarted();
    return !empty($_SESSION['admin_logged_in']);
}

function requireAdminLogin()
{
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function attemptAdminLogin($username, $password)
{
    ensureSessionStarted();
    $config = getAdminConfig();
    $expectedUser = $config['admin_username'] ?? 'admin';
    $expectedPassword = $config['admin_password'] ?? '';
    $passwordHash = $config['admin_password_hash'] ?? null;

    $usernameMatch = hash_equals($expectedUser, (string) $username);
    $passwordMatch = false;

    if ($passwordHash) {
        $passwordMatch = password_verify((string) $password, $passwordHash);
    } else {
        $passwordMatch = hash_equals($expectedPassword, (string) $password);
    }

    if ($usernameMatch && $passwordMatch) {
        $_SESSION['admin_logged_in'] = true;
        return true;
    }

    return false;
}

function logoutAdmin()
{
    ensureSessionStarted();
    unset($_SESSION['admin_logged_in']);
}
