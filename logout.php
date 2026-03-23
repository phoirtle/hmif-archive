<?php
require_once __DIR__ . '/src/config/app.php';
require_once __DIR__ . '/src/middleware/auth.php';
startSession();
session_destroy();
header('Location: ' . APP_URL . '/login.php');
exit;
