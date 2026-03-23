<?php
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
    return $bytes . ' B';
}

function getFileIcon($filetype) {
    $icons = [
        'pdf' => '📄',
        'doc' => '📝', 'docx' => '📝',
        'xls' => '📊', 'xlsx' => '📊',
        'ppt' => '📋', 'pptx' => '📋',
        'jpg' => '🖼️', 'jpeg' => '🖼️', 'png' => '🖼️',
        'zip' => '🗜️', 'rar' => '🗜️',
    ];
    return $icons[strtolower($filetype)] ?? '📎';
}

function getFileIconClass($filetype) {
    $types = [
        'pdf' => 'icon-pdf',
        'doc' => 'icon-doc', 'docx' => 'icon-doc',
        'xls' => 'icon-xls', 'xlsx' => 'icon-xls',
        'ppt' => 'icon-ppt', 'pptx' => 'icon-ppt',
        'jpg' => 'icon-img', 'jpeg' => 'icon-img', 'png' => 'icon-img',
        'zip' => 'icon-zip', 'rar' => 'icon-zip',
    ];
    return $types[strtolower($filetype)] ?? 'icon-file';
}

function sanitize($str) {
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}

function timeAgo($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    if ($diff->y > 0) return $diff->y . ' tahun lalu';
    if ($diff->m > 0) return $diff->m . ' bulan lalu';
    if ($diff->d > 0) return $diff->d . ' hari lalu';
    if ($diff->h > 0) return $diff->h . ' jam lalu';
    if ($diff->i > 0) return $diff->i . ' menit lalu';
    return 'baru saja';
}

function formatDate($datetime, $format = 'd M Y') {
    $months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    $date = new DateTime($datetime);
    $result = $date->format($format);
    $result = str_replace(['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'], $months, $result);
    return $result;
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function setFlash($type, $message) {
    startSession();
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    startSession();
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function generateUniqueFilename($originalName) {
    $ext = pathinfo($originalName, PATHINFO_EXTENSION);
    return uniqid('hmif_', true) . '.' . strtolower($ext);
}

function isAllowedFile($filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, ALLOWED_EXTENSIONS);
}
