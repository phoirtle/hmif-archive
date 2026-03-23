<?php
$pageTitle = 'Profil Saya';
require_once __DIR__ . '/../template/header.php';

$db = getDB();
$errors = [];
$success = false;

$stmt = $db->prepare("SELECT u.*, d.nama as dept_nama, dv.nama as div_nama FROM users u LEFT JOIN departments d ON u.department_id=d.id LEFT JOIN divisions dv ON u.division_id=dv.id WHERE u.id=?");
$stmt->execute([$currentUser['id']]);
$profile = $stmt->fetch();

$archiveCount = $db->prepare("SELECT COUNT(*) FROM archives WHERE uploaded_by=?");
$archiveCount->execute([$currentUser['id']]);
$archiveCount = $archiveCount->fetchColumn();

$downloadCount = $db->prepare("SELECT COALESCE(SUM(download_count),0) FROM archives WHERE uploaded_by=?");
$downloadCount->execute([$currentUser['id']]);
$downloadCount = $downloadCount->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $nama = trim($_POST['nama_lengkap']??'');
        if (empty($nama)) $errors[] = 'Nama tidak boleh kosong.';

        $fotoFilename = $profile['foto_profil'];
        if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['foto_profil'];
            $allowedImg = ['jpg','jpeg','png','webp','gif'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedImg)) {
                $errors[] = 'Format foto tidak didukung (jpg, png, webp).';
            } elseif ($file['size'] > 5*1024*1024) {
                $errors[] = 'Ukuran foto maks 5MB.';
            } else {
                if (!is_dir(PROFILE_PATH)) mkdir(PROFILE_PATH, 0755, true);
                $newFilename = 'profile_' . $currentUser['id'] . '_' . time() . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], PROFILE_PATH . $newFilename)) {

                    if ($profile['foto_profil'] && file_exists(PROFILE_PATH . $profile['foto_profil'])) {
                        unlink(PROFILE_PATH . $profile['foto_profil']);
                    }
                    $fotoFilename = $newFilename;
                } else {
                    $errors[] = 'Gagal mengupload foto.';
                }
            }
        }

        if (empty($errors)) {
            $db->prepare("UPDATE users SET nama_lengkap=?, foto_profil=?, updated_at=NOW() WHERE id=?")
               ->execute([$nama, $fotoFilename, $currentUser['id']]);
            setFlash('success', 'Profil berhasil diperbarui!');
            redirect(APP_URL . '/profile/index.php');
        }
    }

    if ($action === 'change_password') {
        $oldPass = $_POST['old_password']??'';
        $newPass = $_POST['new_password']??'';
        $confirmPass = $_POST['confirm_password']??'';

        if (!password_verify($oldPass, $profile['password'])) {
            $errors[] = 'Password lama tidak benar.';
        } elseif (strlen($newPass) < 6) {
            $errors[] = 'Password baru minimal 6 karakter.';
        } elseif ($newPass !== $confirmPass) {
            $errors[] = 'Konfirmasi password tidak cocok.';
        } else {
            $db->prepare("UPDATE users SET password=? WHERE id=?")->execute([password_hash($newPass, PASSWORD_DEFAULT), $currentUser['id']]);
            setFlash('success', 'Password berhasil diubah!');
            redirect(APP_URL . '/profile/index.php');
        }
    }
}

$avatarUrl = null;
if (!empty($profile['foto_profil']) && file_exists(PROFILE_PATH . $profile['foto_profil'])) {
    $avatarUrl = APP_URL . '/public/uploads/profiles/' . $profile['foto_profil'];
}
?>

<div class="page-content">
    <?php if (!empty($errors)): ?>
    <div class="alert alert-error" data-animate>❌ <?= implode('<br>', array_map('sanitize', $errors)) ?></div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:300px 1fr;gap:24px;align-items:start" class="profile-grid">

        <div style="display:flex;flex-direction:column;gap:16px">
            <div class="glass-card" style="padding:20px;text-align:center" data-animate>
                <div style="position:relative;display:inline-block;margin-bottom:18px;">
                    <?php if ($avatarUrl): ?>
                    <img id="photoPreview" src="<?= $avatarUrl ?>" alt="Foto Profil"
                         style="width:100px;height:100px;border-radius:50%;object-fit:cover;border:3px solid rgba(168,232,249,0.3);box-shadow:0 8px 30px rgba(0,0,0,0.3)">
                    <?php else: ?>
                    <div id="photoPreview" style="width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,var(--blue-mid),var(--blue-dark));border:3px solid rgba(168,232,249,0.3);display:flex;align-items:center;justify-content:center;font-size:40px;font-weight:800;color:var(--blue-light);font-family:var(--font-display);margin:0 auto">
                        <?= strtoupper(substr($profile['nama_lengkap'],0,1)) ?>
                    </div>
                    <?php endif; ?>
                    <div style="position:absolute;bottom:2px;right:2px;width:28px;height:28px;background:var(--gold);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;cursor:pointer;box-shadow:0 2px 10px rgba(245,162,1,0.4)"
                         onclick="document.getElementById('photoInput').click()">📷</div>
                </div>

                <div style="font-family:var(--font-display);font-size:18px;font-weight:700;color:var(--text-primary);margin-bottom:4px">
                    <?= sanitize($profile['nama_lengkap']) ?>
                </div>
                <div style="display:inline-block;padding:4px 14px;background:rgba(245,162,1,0.15);border:1px solid rgba(245,162,1,0.3);border-radius:50px;font-size:12px;font-weight:600;color:var(--gold);margin-bottom:8px">
                    <?= getRoleLabel($profile['role']) ?>
                </div>
                <div style="font-size:12px;color:var(--text-muted);margin-bottom:16px"><?= sanitize($profile['email']) ?></div>

                <?php if ($profile['dept_nama']): ?>
                <div style="display:flex;align-items:center;justify-content:center;gap:6px;font-size:12px;color:var(--text-secondary)">
                    <span>🏛️</span> <?= sanitize($profile['dept_nama']) ?>
                </div>
                <?php endif; ?>
                <?php if ($profile['div_nama']): ?>
                <div style="display:flex;align-items:center;justify-content:center;gap:6px;font-size:12px;color:var(--text-secondary);margin-top:4px">
                    <span>🔖</span> <?= sanitize($profile['div_nama']) ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="glass-card" style="padding:22px" data-animate>
                <div style="font-family:var(--font-display);font-size:14px;font-weight:700;margin-bottom:16px;color:var(--text-primary)">Statistik Saya</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <div style="text-align:center;padding:14px;background:rgba(168,232,249,0.06);border-radius:var(--radius-md)">
                        <div style="font-family:var(--font-display);font-size:24px;font-weight:800;color:var(--blue-light)"><?= $archiveCount ?></div>
                        <div style="font-size:10px;color:var(--text-muted);letter-spacing:0.5px;margin-top:2px">Arsip Diupload</div>
                    </div>
                    <div style="text-align:center;padding:14px;background:rgba(245,162,1,0.08);border-radius:var(--radius-md)">
                        <div style="font-family:var(--font-display);font-size:24px;font-weight:800;color:var(--gold)"><?= $downloadCount ?></div>
                        <div style="font-size:10px;color:var(--text-muted);letter-spacing:0.5px;margin-top:2px">Total Unduhan</div>
                    </div>
                </div>
                <div style="margin-top:14px;padding-top:14px;border-top:1px solid rgba(168,232,249,0.06)">
                    <div style="font-size:11px;color:var(--text-muted)">Bergabung: <?= formatDate($profile['created_at'], 'd M Y') ?></div>
                </div>
            </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:20px">
            <div class="glass-card" style="padding:28px" data-animate>
                <h3 style="font-family:var(--font-display);font-size:18px;font-weight:700;margin-bottom:22px;color:var(--text-primary)">Edit Profil</h3>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="update_profile">
                    <input type="file" id="photoInput" name="foto_profil" accept="image/*" style="display:none" onchange="previewPhoto(this)">

                    <div style="margin-bottom:18px;padding:14px;background:rgba(168,232,249,0.05);border:1px solid rgba(168,232,249,0.1);border-radius:var(--radius-md);display:flex;align-items:center;gap:14px">
                        <span style="font-size:24px">🖼️</span>
                        <div style="flex:1">
                            <div style="font-size:13px;font-weight:600;color:var(--text-primary)">Foto Profil</div>
                            <div style="font-size:11px;color:var(--text-muted)">JPG, PNG, WebP · Maks 5MB</div>
                        </div>
                        <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('photoInput').click()">
                            Ganti Foto
                        </button>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nama Lengkap *</label>
                        <input type="text" name="nama_lengkap" class="form-control" value="<?= sanitize($profile['nama_lengkap']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email <span style="color:var(--text-muted)">(tidak dapat diubah)</span></label>
                        <input type="email" class="form-control" value="<?= sanitize($profile['email']) ?>" disabled style="opacity:0.6;cursor:not-allowed">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Role</label>
                            <input type="text" class="form-control" value="<?= getRoleLabel($profile['role']) ?>" disabled style="opacity:0.6;cursor:not-allowed">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Dinas</label>
                            <input type="text" class="form-control" value="<?= sanitize($profile['dept_nama'] ?? '—') ?>" disabled style="opacity:0.6;cursor:not-allowed">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">
                        Simpan
                    </button>
                </form>
            </div>

            <!-- for change pass -->
            <div class="glass-card" style="padding:28px" data-animate>
                <h3 style="font-family:var(--font-display);font-size:18px;font-weight:700;margin-bottom:22px;color:var(--text-primary)">Ganti Password</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="change_password">
                    <div class="form-group">
                        <label class="form-label">Password Lama *</label>
                        <input type="password" name="old_password" class="form-control" placeholder="Masukkan password saat ini" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Password Baru *</label>
                            <input type="password" name="new_password" class="form-control" placeholder="Min. 6 karakter" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Konfirmasi Password *</label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="Ulangi password baru" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-outline" style="width:100%;justify-content:center">
                        Ubah Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
@media (max-width:900px) { .profile-grid { grid-template-columns:1fr !important; } }
</style>

<script>
function previewPhoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const old = document.getElementById('photoPreview');
            if (old.tagName === 'DIV') {
                const img = document.createElement('img');
                img.id = 'photoPreview';
                img.style.cssText = 'width:100px;height:100px;border-radius:50%;object-fit:cover;border:3px solid rgba(168,232,249,0.3);';
                img.src = e.target.result;
                old.replaceWith(img);
            } else {
                old.src = e.target.result;
            }
            showToast('Foto dipilih. Klik "Simpan" untuk menyimpan.', 'info');
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php require_once __DIR__ . '/../template/footer.php'; ?>
