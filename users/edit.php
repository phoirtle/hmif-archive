<?php
$pageTitle = 'Edit Pengguna';
require_once __DIR__ . '/../template/header.php';
if (!isAdmin($currentUser)) { setFlash('error','Tidak memiliki izin.'); redirect(APP_URL.'/users/index.php'); }
$id = intval($_GET['id']??0);
$db = getDB();
$stmt = $db->prepare("SELECT * FROM users WHERE id=?"); $stmt->execute([$id]); $user = $stmt->fetch();
if (!$user) { setFlash('error','Pengguna tidak ditemukan.'); redirect(APP_URL.'/users/index.php'); }
$departments = $db->query("SELECT * FROM departments ORDER BY nama")->fetchAll();
$divisions = $db->query("SELECT * FROM divisions ORDER BY nama")->fetchAll();
$roles = ['ketua','waketua','sekum','bendum','kadin','wakadin','kadiv','staf'];
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama_lengkap']??'');
    $email = trim($_POST['email']??'');
    $password = $_POST['password']??'';
    $role = $_POST['role']??'';
    $dept_id = !empty($_POST['department_id']) ? intval($_POST['department_id']) : null;
    $div_id = !empty($_POST['division_id']) ? intval($_POST['division_id']) : null;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    if (empty($nama)) $errors[] = 'Nama wajib.';
    if (empty($errors)) {
        try {
            if (!empty($password) && strlen($password) >= 6) {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $db->prepare("UPDATE users SET email=?,password=?,nama_lengkap=?,role=?,department_id=?,division_id=?,is_active=? WHERE id=?")
                   ->execute([$email,$hashed,$nama,$role,$dept_id,$div_id,$is_active,$id]);
            } else {
                $db->prepare("UPDATE users SET email=?,nama_lengkap=?,role=?,department_id=?,division_id=?,is_active=? WHERE id=?")
                   ->execute([$email,$nama,$role,$dept_id,$div_id,$is_active,$id]);
            }
            setFlash('success','Pengguna berhasil diperbarui!');
            redirect(APP_URL.'/users/index.php');
        } catch (Exception $e) { $errors[] = 'Email sudah digunakan.'; }
    }
    $user = array_merge($user, compact('nama_lengkap','email','role','dept_id','div_id','is_active'));
}
?>
<div class="page-content"><div style="max-width:600px;margin:0 auto">
<div class="breadcrumb" style="margin-bottom:20px" data-animate><a href="<?= APP_URL ?>/users/index.php">Pengguna</a><span>/</span><span style="color:var(--text-secondary)">Edit</span></div>
<?php if (!empty($errors)): ?><div class="alert alert-error">❌ <?= implode('<br>',array_map('sanitize',$errors)) ?></div><?php endif; ?>
<div class="glass-card" style="padding:32px" data-animate>
<h2 style="font-family:var(--font-display);font-size:22px;font-weight:700;margin-bottom:24px">Edit Pengguna</h2>
<form method="POST">
    <div class="form-row">
        <div class="form-group"><label class="form-label">Nama Lengkap *</label><input type="text" name="nama_lengkap" class="form-control" value="<?= sanitize($user['nama_lengkap']) ?>" required></div>
        <div class="form-group"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" value="<?= sanitize($user['email']) ?>" required></div>
    </div>
    <div class="form-row">
        <div class="form-group"><label class="form-label">Password Baru <span style="color:var(--text-muted)">(kosongkan jika tidak ganti)</span></label><input type="password" name="password" class="form-control" placeholder="Min. 6 karakter"></div>
        <div class="form-group"><label class="form-label">Role *</label>
            <select name="role" class="form-control" required>
                <?php foreach ($roles as $r): ?><option value="<?= $r ?>" <?= $user['role']==$r?'selected':'' ?>><?= getRoleLabel($r) ?></option><?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group"><label class="form-label">Dinas</label>
            <select name="department_id" class="form-control" onchange="filterByDept(this.value)">
                <option value="">-- Tidak ada --</option>
                <?php foreach ($departments as $d): ?><option value="<?= $d['id'] ?>" <?= $user['department_id']==$d['id']?'selected':'' ?>><?= sanitize($d['nama']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="form-group"><label class="form-label">Divisi</label>
            <select name="division_id" class="form-control" id="division_id">
                <option value="">-- Tidak ada --</option>
                <?php foreach ($divisions as $dv): ?><option value="<?= $dv['id'] ?>" data-dept="<?= $dv['department_id'] ?>" <?= $user['division_id']==$dv['id']?'selected':'' ?>><?= sanitize($dv['nama']) ?></option><?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="form-group">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 16px;background:rgba(168,232,249,0.05);border:1px solid rgba(168,232,249,0.12);border-radius:var(--radius-md)">
            <div><div style="font-size:14px;font-weight:600;color:var(--text-primary)">Status Aktif</div></div>
            <label class="toggle"><input type="checkbox" name="is_active" value="1" <?= $user['is_active']?'checked':'' ?>><span class="toggle-slider"></span></label>
        </div>
    </div>
    <div style="display:flex;gap:12px"><a href="<?= APP_URL ?>/users/index.php" class="btn btn-outline" style="flex:1;justify-content:center">Batal</a><button type="submit" class="btn btn-primary" style="flex:2;justify-content:center">Simpan</button></div>
</form></div></div></div>
<?php require_once __DIR__ . '/../template/footer.php'; ?>