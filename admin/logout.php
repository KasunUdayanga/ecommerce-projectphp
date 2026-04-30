<?php
require_once __DIR__ . '/../includes/admin.php';

logoutAdmin();
header('Location: login.php');
exit;
