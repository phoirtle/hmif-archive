<?php
require_once __DIR__ . '/../src/config/app.php';
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/middleware/auth.php';
require_once __DIR__ . '/../src/helpers/functions.php';
requireLogin();
if (!isAdmin($currentUser)) { setFlash('error','Tidak memiliki izin.'); redirect(APP_URL.'/users/index.php'); }
$id = intval($_GET['id']??0);
if (!$id || $id == $_SESSION['user_id']) { setFlash('error','Tidak bisa menghapus akun sendiri.'); redirect(APP_URL.'/users/index.php'); }
$db = getDB();
$db->prepare("UPDATE users SET is_active=0 WHERE id=?")->execute([$id]);
setFlash('success','Pengguna berhasil dinonaktifkan.');
redirect(APP_URL.'/users/index.php');
