<?php
require_once __DIR__ . '/src/config/app.php';
require_once __DIR__ . '/src/middleware/auth.php';
startSession();
if (isLoggedIn()) {
    header('Location: ' . APP_URL . '/dashboard.php');
} else {
    header('Location: ' . APP_URL . '/login.php');
}
exit;
