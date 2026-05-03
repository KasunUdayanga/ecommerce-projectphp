<?php
session_start();
require_once __DIR__ . '/../../shared-core/includes/functions.php';

$config = require_once __DIR__ . '/../../shared-core/config.php';
$clientId = $config['google_client_id'] ?? '';
$clientSecret = $config['google_client_secret'] ?? '';
$redirectUri = $config['google_redirect_uri'] ?? '';

if (empty($clientId) || empty($clientSecret) || empty($redirectUri)) {
    die('Google OAuth is not configured. Please set the values in shared-core/config.php');
}

$error = '';
if (!isset($_GET['code'])) {
    $error = 'Authorization code missing.';
} else {
    $code = $_GET['code'];
    $state = $_GET['state'] ?? '';

    // Basic state validation (compare raw token stored in session)
    $savedState = $_SESSION['google_oauth_state'] ?? '';
    if ($savedState && $state !== $savedState) {
        $error = 'Invalid state parameter.';
    } else {
        // Exchange code for access token
        $tokenUrl = 'https://oauth2.googleapis.com/token';
        $postFields = http_build_query([
            'code' => $code,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code'
        ]);

        $ch = curl_init($tokenUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        $resp = curl_exec($ch);
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($resp === false || $httpStatus !== 200) {
            $error = 'Token exchange failed.';
        } else {
            $tokenData = json_decode($resp, true);
            $accessToken = $tokenData['access_token'] ?? null;
            if (!$accessToken) {
                $error = 'No access token returned.';
            } else {
                // Fetch user info
                $userinfoUrl = 'https://openidconnect.googleapis.com/v1/userinfo';
                $ch = curl_init($userinfoUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer {$accessToken}"]);
                $uiResp = curl_exec($ch);
                $uiStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($uiResp === false || $uiStatus !== 200) {
                    $error = 'Failed fetching user info.';
                } else {
                    $userInfo = json_decode($uiResp, true);
                    $email = $userInfo['email'] ?? null;
                    $name = $userInfo['name'] ?? ($userInfo['given_name'] ?? '');

                    if (!$email) {
                        $error = 'Email not provided by Google.';
                    } else {
                        // Find or create local user
                        $existing = getUserByEmail($email);
                        if ($existing) {
                            ensureSessionStarted();
                            $_SESSION['user_id'] = (int) $existing['id'];
                            $_SESSION['user_name'] = $existing['username'];
                        } else {
                            // Register a new user with a random password
                            $randomPassword = bin2hex(random_bytes(8));
                            $usernameCandidate = preg_replace('/[^a-z0-9_\-]/i', '_', strtolower(explode('@', $email)[0]));
                            $newId = registerUser($usernameCandidate, $email, $randomPassword);
                            if ($newId) {
                                ensureSessionStarted();
                                $_SESSION['user_id'] = (int) $newId;
                                $_SESSION['user_name'] = $usernameCandidate;
                            } else {
                                $error = 'Failed to create local user account.';
                            }
                        }

                        // determine redirect
                        $statePayload = @json_decode(base64_decode($state), true);
                        $redirectTarget = $statePayload['redirect'] ?? 'index.php';
                        // Clean up
                        unset($_SESSION['google_oauth_state']);

                        header('Location: ' . $redirectTarget);
                        exit;
                    }
                }
            }
        }
    }
}

// On error, show message and link back
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Google Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="max-w-lg w-full p-6 bg-white rounded-lg shadow">
        <h2 class="text-xl font-bold mb-4">Google Login</h2>
        <p class="text-sm text-red-600 mb-4"><?php echo htmlspecialchars($error); ?></p>
        <a href="login.php" class="inline-block bg-green-500 text-white py-2 px-4 rounded">Back to login</a>
    </div>
</body>

</html>