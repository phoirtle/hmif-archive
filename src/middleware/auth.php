<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . APP_URL . '/login.php');
        exit;
    }
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    $db = getDB();
    $stmt = $db->prepare("
        SELECT u.*, d.nama as dept_nama, d.kode as dept_kode, 
               dv.nama as div_nama, dv.kode as div_kode
        FROM users u
        LEFT JOIN departments d ON u.department_id = d.id
        LEFT JOIN divisions dv ON u.division_id = dv.id
        WHERE u.id = ? AND u.is_active = 1
    ");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function isAdmin($user = null) {
    if (!$user) $user = getCurrentUser();
    return $user && in_array($user['role'], ROLE_ADMIN);
}

function isDeptManager($user = null) {
    if (!$user) $user = getCurrentUser();
    return $user && in_array($user['role'], ROLE_DEPT_MANAGER);
}

function isDivManager($user = null) {
    if (!$user) $user = getCurrentUser();
    return $user && in_array($user['role'], ROLE_DIV_MANAGER);
}

function canManageArchive($archive, $user = null) {
    if (!$user) $user = getCurrentUser();
    if (!$user) return false;
    if (isAdmin($user)) return true;
    if (isDeptManager($user)) {
        return $archive['department_id'] == $user['department_id'];
    }
    if (isDivManager($user)) {
        return $archive['division_id'] == $user['division_id'];
    }
    return false;
}

function canUploadToScope($dept_id, $div_id, $user = null) {
    if (!$user) $user = getCurrentUser();
    if (!$user) return false;
    if (isAdmin($user)) return true;
    if (isDeptManager($user)) {
        return $user['department_id'] == $dept_id;
    }
    if (isDivManager($user)) {
        return $user['department_id'] == $dept_id && $user['division_id'] == $div_id;
    }
    return false;
}

function getRoleLabel($role) {
    $labels = [
        'ketua' => 'Ketua HMIF',
        'waketua' => 'Wakil Ketua',
        'sekum' => 'Sekretaris Umum',
        'bendum' => 'Bendahara Umum',
        'kadin' => 'Kepala Dinas',
        'wakadin' => 'Wakil Kepala Dinas',
        'kadiv' => 'Kepala Divisi',
        'staf' => 'Staf',
    ];
    return $labels[$role] ?? $role;
}
