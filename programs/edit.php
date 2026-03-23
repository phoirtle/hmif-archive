<?php
$pageTitle = 'Edit Program Kerja';
require_once __DIR__ . '/../template/header.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) redirect(APP_URL . '/programs/index.php');

$db = getDB();
$stmt = $db->prepare("SELECT * FROM programs WHERE id = ?");
$stmt->execute([$id]);
$program = $stmt->fetch();

if (!$program) { setFlash('error', 'Program tidak ditemukan.'); redirect(APP_URL . '/programs/index.php'); }
if (!isAdmin($currentUser) && !(isDeptManager($currentUser) && $program['department_id'] == $currentUser['department_id'])) {
    setFlash('error', 'Tidak memiliki izin.'); redirect(APP_URL . '/programs/index.php');
}

$departments = $db->query("SELECT * FROM departments ORDER BY nama")->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $department_id = intval($_POST['department_id'] ?? 0);
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $status = $_POST['status'] ?? 'aktif';
    if (empty($nama)) $errors[] = 'Nama wajib diisi.';
    if (empty($errors)) {
        $db->prepare("UPDATE programs SET nama=?,department_id=?,deskripsi=?,status=? WHERE id=?")
           ->execute([$nama, $department_id, $deskripsi, $status, $id]);
        setFlash('success', 'Program berhasil diperbarui!');
        redirect(APP_URL . '/programs/index.php');
    }
    $program = array_merge($program, compact('nama','department_id','deskripsi','status'));
}
?>
<div class="page-content">
    <div style="max-width:600px;margin:0 auto">
        <div class="breadcrumb" style="margin-bottom:20px" data-animate>
            <a href="<?= APP_URL ?>/programs/index.php">Program Kerja</a>
            <span>/</span>
            <span style="color:var(--text-secondary)">Edit</span>
        </div>
        <?php if (!empty($errors)): ?>
        <div class="alert alert-error">❌ <?= implode('<br>', array_map('sanitize', $errors)) ?></div>
        <?php endif; ?>
        <div class="glass-card" style="padding:32px" data-animate>
            <h2 style="font-family:var(--font-display);font-size:22px;font-weight:700;margin-bottom:24px">Edit Program Kerja</h2>
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Nama Program *</label>
                    <input type="text" name="nama" class="form-control" value="<?= sanitize($program['nama']) ?>" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Dinas *</label>
                        <select name="department_id" class="form-control">
                            <?php foreach ($departments as $d): ?>
                            <option value="<?= $d['id'] ?>" <?= $program['department_id'] == $d['id'] ? 'selected' : '' ?>><?= sanitize($d['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="persiapan" <?= $program['status'] == 'persiapan' ? 'selected' : '' ?>>Persiapan</option>
                            <option value="berjalan" <?= $program['status'] == 'berjalan' ? 'selected' : '' ?>>Berjalan</option>
                            <option value="selesai" <?= $program['status'] == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                            <option value="dibatalkan" <?= $program['status'] == 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="3"><?= sanitize($program['deskripsi']) ?></textarea>
                </div>
                <div style="display:flex;gap:12px">
                    <a href="<?= APP_URL ?>/programs/index.php" class="btn btn-outline" style="flex:1;justify-content:center">Batal</a>
                    <button type="submit" class="btn btn-primary" style="flex:2;justify-content:center">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../template/footer.php'; ?>
