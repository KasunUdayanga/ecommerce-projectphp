<?php
require_once __DIR__ . '/../../shared-core/includes/admin.php';

logoutAdmin();
header('Location: /ecommerce-projectphp/admin-site/index.php');
exit;
