<?php
define('APP_NAME', 'HMIF Archive');
define('APP_URL', 'http://localhost/hmif-archive');
define('UPLOAD_PATH', __DIR__ . '/../../public/uploads/');
define('UPLOAD_URL', APP_URL . '/public/uploads/');
define('PROFILE_PATH', __DIR__ . '/../../public/uploads/profiles/');
define('MAX_FILE_SIZE', 50 * 1024 * 1024); // 50MB
define('ALLOWED_EXTENSIONS', ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'zip', 'rar']);

// hierarki role atmin
define('ROLE_ADMIN', ['ketua', 'waketua', 'sekum', 'bendum']);
define('ROLE_DEPT_MANAGER', ['kadin', 'wakadin']);
define('ROLE_DIV_MANAGER', ['kadiv']);
define('ROLE_STAFF', ['staf']);

session_name('HMIF_SESSION');
ini_set('session.gc_maxlifetime', 3600);
