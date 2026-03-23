<?php
require_once __DIR__ . '/src/config/app.php';
require_once __DIR__ . '/src/config/database.php';
require_once __DIR__ . '/src/middleware/auth.php';
require_once __DIR__ . '/src/helpers/functions.php';

startSession();
if (isLoggedIn()) redirect(APP_URL . '/dashboard.php');

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email dan password wajib diisi.';
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            setFlash('success', 'Selamat datang, ' . $user['nama_lengkap'] . '!');
            redirect(APP_URL . '/dashboard.php');
        } else {
            $error = 'Email atau password salah. Coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — HMIF Archive</title>
    <link rel="stylesheet" href="<?= APP_URL ?>/public/assets/css/main.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/public/assets/css/login.css">
</head>
<body>
<div class="login-page">
    <div class="login-bg"></div>

    <div class="login-left">
        <div class="login-rings">
            <div class="ring"></div>
            <div class="ring"></div>
            <div class="ring"></div>
            <div class="ring"></div>
        </div>

        <div class="brand-logo">
            <img src="<?= APP_URL ?>/public/assets/images/logo-hmif.png" alt="Logo" style="width:130px;height:100px;">
        </div>
        
        <div class="brand-title">
            <span>Himpunan Mahasiswa Informatika</span>
            <span style="margin-top: 10px; font-size:25px;font-weight:700;-webkit-text-fill-color:var(--text-secondary)">Pengelolaan Arsip Digital</span>
        </div>
        
        <p class="brand-subtitle">
            Universitas Sriwijaya
        </p>

        <div class="brand-stats">
            <div class="brand-stat">
                <div class="brand-stat-value">7</div>
                <div class="brand-stat-label">Dinas</div>
            </div>
            <div class="brand-stat">
                <div class="brand-stat-value">8</div>
                <div class="brand-stat-label">Divisi</div>
            </div>
            <div class="brand-stat">
                <div class="brand-stat-value">∞</div>
                <div class="brand-stat-label">Arsip</div>
            </div>
        </div>
    </div>

    <div class="login-right">
        <div class="login-form-container">
            <div class="login-form-title">Masuk</div>
            <p class="login-form-desc">Gunakan akun Anda yang sudah terdaftar untuk mengakses sistem arsip</p>

            <?php if ($error): ?>
            <div class="alert alert-error" style="margin-bottom:20px">
                ❌ <?= sanitize($error) ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="login-input-group">
                    <span class="login-input-icon">📧</span>
                    <input 
                        type="email" 
                        name="email" 
                        id="email"
                        class="login-input" 
                        placeholder="Masukkan E-Mail"
                        value="<?= sanitize($email) ?>"
                        required
                        autocomplete="email"
                    >
                </div>

                <div class="login-input-group">
                    <span class="login-input-icon">🔒</span>
                    <input 
                        type="password" 
                        name="password" 
                        id="password"
                        class="login-input" 
                        placeholder="Masukkan Password"
                        required
                        autocomplete="current-password"
                    >
                    <button type="button" class="show-password-btn" id="showPasswordBtn">👁️</button>
                </div>

                <button type="submit" class="login-btn">
                    Masuk
                </button>
            </form>

            <div class="login-footer">
                <p class="login-footer-text">
                    Lupa password? Hubungi admin<br>
                    <span style="opacity:0.5">HMIF UNSRI © <?= date('Y') ?></span>
                </p>
            </div>
        </div>
    </div>
</div>

<script src="<?= APP_URL ?>/public/assets/js/login.js"></script>
</body>
</html>
