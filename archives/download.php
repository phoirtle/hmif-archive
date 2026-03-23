<?php
require_once __DIR__ . '/../src/config/app.php';
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/middleware/auth.php';
require_once __DIR__ . '/../src/helpers/functions.php';

requireLogin();
$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: ' . APP_URL . '/archives/index.php'); exit; }

$db = getDB();
$stmt = $db->prepare("SELECT * FROM archives WHERE id = ?");
$stmt->execute([$id]);
$archive = $stmt->fetch();

if (!$archive) {
    setFlash('error', 'Arsip tidak ditemukan.');
    redirect(APP_URL . '/archives/index.php');
}

$filePath = UPLOAD_PATH . $archive['filename'];
if (!file_exists($filePath)) {
    setFlash('error', 'File tidak ditemukan di server.');
    redirect(APP_URL . '/archives/index.php');
}

// for update download count
$db->prepare("UPDATE archives SET download_count = download_count + 1 WHERE id = ?")->execute([$id]);

// serve file
$mime = mime_content_type($filePath) ?: 'application/octet-stream';
$downloadName = $archive['original_filename'] ?: $archive['filename'];

header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . addslashes($downloadName) . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: no-cache');
readfile($filePath);
exit;
