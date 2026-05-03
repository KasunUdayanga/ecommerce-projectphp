<?php
require_once __DIR__ . '/../../shared-core/includes/admin.php';

requireAdminLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    if ($id > 0) {
        deleteProduct($id);
    }
}

header('Location: /ecommerce-projectphp/admin-site/index.php?message=Product%20deleted');
exit;
