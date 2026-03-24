<?php
require_once __DIR__ . '/../src/middleware/auth.php';
require_once __DIR__ . '/../src/helpers/functions.php';
requireLogin();
$currentUser = getCurrentUser();
$flash = getFlash();

function getAvatarSrc($user) {
    if (!empty($user['foto_profil']) && file_exists(PROFILE_PATH . $user['foto_profil'])) {
        return APP_URL . '/public/uploads/profiles/' . $user['foto_profil'];
    }
    return null;
}
$avatarSrc = getAvatarSrc($currentUser);
$initials = strtoupper(substr($currentUser['nama_lengkap'], 0, 1));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Dashboard' ?> — HMIF Archive</title>
    <link rel="stylesheet" href="<?= APP_URL ?>/public/assets/css/main.css">
    <?php if (isset($extraCSS)) foreach ($extraCSS as $css) echo "<link rel='stylesheet' href='$css'>"; ?>
</head>
<body>
<div class="bg-animated"><div class="bg-orb"></div></div>
<div id="mobileOverlay" class="overlay-mobile"></div>

<!-- sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <div style="width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,rgba(0,83,122,0.6),rgba(1,60,88,0.8));border:1.5px solid rgba(168,232,249,0.3);display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0;">
            <img src="<?= APP_URL ?>/public/assets/images/logo-hmif.png" alt="Logo" style="width:30px;height:30px;">
        </div>
        <div class="sidebar-logo-text">
            <div class="org-name">HMIF UNSRI</div>
            <div class="org-sub">Sistem Arsip</div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-label">Menu Utama</div>
        
        <a href="<?= APP_URL ?>/dashboard.php" class="nav-item <?= (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : '' ?>">
            <span>Dashboard</span>
        </a>

        <a href="<?= APP_URL ?>/archives/index.php" class="nav-item <?= (strpos($_SERVER['PHP_SELF'], '/archives/') !== false) ? 'active' : '' ?>">
            <span>Arsip</span>
        </a>

        <a href="<?= APP_URL ?>/programs/index.php" class="nav-item <?= (strpos($_SERVER['PHP_SELF'], '/programs/') !== false) ? 'active' : '' ?>">
            <span>Program Kerja</span>
        </a>

        <?php if (isAdmin($currentUser) || isDeptManager($currentUser)): ?>
        <div class="nav-section-label">Manajemen</div>

        <a href="<?= APP_URL ?>/departments/index.php" class="nav-item <?= (strpos($_SERVER['PHP_SELF'], '/departments/') !== false) ? 'active' : '' ?>">
            <span>Dinas</span>
        </a>

        <?php if (isAdmin($currentUser)): ?>
        <a href="<?= APP_URL ?>/divisions/index.php" class="nav-item <?= (strpos($_SERVER['PHP_SELF'], '/divisions/') !== false) ? 'active' : '' ?>">
            <span>Divisi</span>
        </a>

        <a href="<?= APP_URL ?>/users/index.php" class="nav-item <?= (strpos($_SERVER['PHP_SELF'], '/users/') !== false) ? 'active' : '' ?>">
            <span>Pengguna</span>
        </a>
        <?php endif; ?>
        <?php endif; ?>

        <div class="nav-section-label">Akun</div>
        <a href="<?= APP_URL ?>/profile/index.php" class="nav-item <?= (strpos($_SERVER['PHP_SELF'], '/profile/') !== false) ? 'active' : '' ?>">
            <span>Profil Saya</span>
        </a>
        <a href="<?= APP_URL ?>/logout.php" class="nav-item" onclick="return confirm('Yakin ingin keluar?')">
            <span>Keluar</span>
        </a>
    </nav>

    <div class="sidebar-user">
        <a href="<?= APP_URL ?>/profile/index.php" class="sidebar-user-card">
            <?php if ($avatarSrc): ?>
                <img src="<?= $avatarSrc ?>" alt="Avatar" class="user-avatar">
            <?php else: ?>
                <div class="user-avatar-placeholder"><?= $initials ?></div>
            <?php endif; ?>
            <div class="flex-1" style="min-width:0">
                <div class="user-info-name"><?= sanitize($currentUser['nama_lengkap']) ?></div>
                <div class="user-info-role"><?= getRoleLabel($currentUser['role']) ?></div>
            </div>
        </a>
    </div>
</aside>

<!-- main content -->
<div class="main-content">
    <!-- TOPBAR -->
    <header class="topbar">
        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 25px;">
            <button class="mobile-menu-btn" id="mobileMenuBtn">☰</button>
            <div>
                <div class="page-title">
                    <?= $pageTitle ?? 'Dashboard' ?>
                </div>
                <?php if (isset($pageBreadcrumb)): ?>
                <div class="breadcrumb"><?= $pageBreadcrumb ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="topbar-actions">
            <?php if (!in_array($currentUser['role'], ROLE_STAFF)): ?>
            <a href="<?= APP_URL ?>/archives/upload.php" class="btn btn-primary">
                Upload Arsip
            </a>
            <?php endif; ?>
        </div>
    </header>

    <!-- flash message -->
    <?php if ($flash): ?>
    <div class="page-content" style="padding-bottom:0">
        <div class="alert alert-<?= $flash['type'] == 'success' ? 'success' : ($flash['type'] == 'error' ? 'error' : 'info') ?>">
            <?= $flash['type'] == 'success' ? : ($flash['type'] == 'error' ? '❌' : 'ℹ️') ?>
            <?= sanitize($flash['message']) ?>
        </div>
    </div>
    <?php endif; ?>
