<?php
require_once __DIR__ . '/../src/config/app.php';
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/middleware/auth.php';
require_once __DIR__ . '/../src/helpers/functions.php';
requireLogin();
if (!isAdmin($currentUser)) { setFlash('error','Tidak memiliki izin.'); redirect(APP_URL.'/divisions/index.php'); }
$id = intval($_GET['id']??0);
if (!$id) redirect(APP_URL.'/divisions/index.php');
$db = getDB();
$db->prepare("DELETE FROM divisions WHERE id=?")->execute([$id]);
setFlash('success','Divisi berhasil dihapus.');
redirect(APP_URL.'/divisions/index.php');
