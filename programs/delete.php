<?php
require_once __DIR__ . '/../src/config/app.php';
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/middleware/auth.php';
require_once __DIR__ . '/../src/helpers/functions.php';
requireLogin();
$id = intval($_GET['id'] ?? 0);
if (!$id) redirect(APP_URL . '/programs/index.php');
$db = getDB();
$stmt = $db->prepare("SELECT * FROM programs WHERE id=?");
$stmt->execute([$id]);
$prog = $stmt->fetch();
if (!$prog || (!isAdmin($currentUser) && !(isDeptManager($currentUser) && $prog['department_id'] == $currentUser['department_id']))) {
    setFlash('error', 'Tidak memiliki izin.'); redirect(APP_URL . '/programs/index.php');
}
$db->prepare("DELETE FROM programs WHERE id=?")->execute([$id]);
setFlash('success', 'Program berhasil dihapus.');
redirect(APP_URL . '/programs/index.php');
