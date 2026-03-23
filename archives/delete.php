<?php
require_once __DIR__ . '/../src/config/app.php';
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/middleware/auth.php';
require_once __DIR__ . '/../src/helpers/functions.php';

requireLogin();
$id = intval($_GET['id'] ?? 0);
if (!$id) redirect(APP_URL . '/archives/index.php');

$db = getDB();
$stmt = $db->prepare("SELECT * FROM archives WHERE id = ?");
$stmt->execute([$id]);
$archive = $stmt->fetch();

if (!$archive || !canManageArchive($archive, $currentUser)) {
    setFlash('error', 'Tidak memiliki izin untuk menghapus arsip ini.');
    redirect(APP_URL . '/archives/index.php');
}

// for delete file
$filePath = UPLOAD_PATH . $archive['filename'];
if (file_exists($filePath)) unlink($filePath);

// for delete record
$db->prepare("DELETE FROM archives WHERE id = ?")->execute([$id]);

setFlash('success', 'Arsip berhasil dihapus.');
redirect(APP_URL . '/archives/index.php');
