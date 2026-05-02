<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';

$config = require_once __DIR__ . '/../includes/config.php';
$clientId = $config['google_client_id'] ?? '';
$redirectUri = $config['google_redirect_uri'] ?? '';
$redirectTarget = $_GET['redirect'] ?? 'index.php';

if (!$clientId || !$redirectUri) {
    die('Google OAuth is not configured. Please set GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET in includes/config.php');
}

$stateData = [
    'redirect' => $redirectTarget,
    'nonce' => bin2hex(random_bytes(8)),
];
$state = base64_encode(json_encode($stateData));
$_SESSION['google_oauth_state'] = $state;

$params = [
    'client_id' => $clientId,
    'redirect_uri' => $redirectUri,
    'response_type' => 'code',
    'scope' => 'openid email profile',
    'state' => $state,
    'access_type' => 'offline',
    'prompt' => 'select_account'
];

$authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
header('Location: ' . $authUrl);
exit;
