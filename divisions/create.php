<?php
$pageTitle = 'Tambah Divisi';
require_once __DIR__ . '/../template/header.php';
if (!isAdmin($currentUser)) { setFlash('error','Tidak memiliki izin.'); redirect(APP_URL.'/divisions/index.php'); }
$db = getDB();
$departments = $db->query("SELECT * FROM departments ORDER BY nama")->fetchAll();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']??''); $dept_id = intval($_POST['department_id']??0); $kode = strtoupper(trim($_POST['kode']??'')); $deskripsi = trim($_POST['deskripsi']??'');
    if (empty($nama)) $errors[] = 'Nama wajib.';
    if (!$dept_id) $errors[] = 'Dinas wajib dipilih.';
    if (empty($errors)) {
        try { $db->prepare("INSERT INTO divisions (nama,department_id,kode,deskripsi) VALUES (?,?,?,?)")->execute([$nama,$dept_id,$kode,$deskripsi]); setFlash('success','Divisi berhasil ditambahkan!'); redirect(APP_URL.'/divisions/index.php'); }
        catch (Exception $e) { $errors[] = 'Kode sudah digunakan.'; }
    }
}
?>
<div class="page-content"><div style="max-width:500px;margin:0 auto">
<div class="breadcrumb" style="margin-bottom:20px" data-animate><a href="<?= APP_URL ?>/divisions/index.php">Divisi</a><span>/</span><span style="color:var(--text-secondary)">Tambah</span></div>
<?php if (!empty($errors)): ?><div class="alert alert-error">❌ <?= implode('<br>',array_map('sanitize',$errors)) ?></div><?php endif; ?>
<div class="glass-card" style="padding:32px" data-animate>
<h2 style="font-family:var(--font-display);font-size:22px;font-weight:700;margin-bottom:24px">Tambah Divisi</h2>
<form method="POST">
<div class="form-row">
    <div class="form-group"><label class="form-label">Nama Divisi *</label><input type="text" name="nama" class="form-control" value="<?= sanitize($_POST['nama']??'') ?>" required></div>
    <div class="form-group"><label class="form-label">Kode *</label><input type="text" name="kode" class="form-control" placeholder="ADK" value="<?= sanitize($_POST['kode']??'') ?>" required maxlength="10"></div>
</div>
<div class="form-group"><label class="form-label">Dinas *</label>
    <select name="department_id" class="form-control" required>
        <option value="">-- Pilih Dinas --</option>
        <?php foreach ($departments as $d): ?><option value="<?= $d['id'] ?>" <?= ($_POST['department_id']??'') == $d['id'] ? 'selected' : '' ?>><?= sanitize($d['nama']) ?></option><?php endforeach; ?>
    </select>
</div>
<div class="form-group"><label class="form-label">Deskripsi</label><textarea name="deskripsi" class="form-control" rows="3"><?= sanitize($_POST['deskripsi']??'') ?></textarea></div>
<div style="display:flex;gap:12px"><a href="<?= APP_URL ?>/divisions/index.php" class="btn btn-outline" style="flex:1;justify-content:center">Batal</a><button type="submit" class="btn btn-primary" style="flex:2;justify-content:center">Simpan</button></div>
</form></div></div></div>
<?php require_once __DIR__ . '/../template/footer.php'; ?>
