<?php
$pageTitle = 'Tambah Program Kerja';
require_once __DIR__ . '/../template/header.php';

if (!isAdmin($currentUser) && !isDeptManager($currentUser)) {
    setFlash('error', 'Tidak memiliki izin.');
    redirect(APP_URL . '/programs/index.php');
}

$db = getDB();

if (isAdmin($currentUser)) {
    $departments = $db->query("SELECT * FROM departments ORDER BY nama")->fetchAll();
} else {
    $stmt = $db->prepare("SELECT * FROM departments WHERE id = ?");
    $stmt->execute([$currentUser['department_id']]);
    $departments = $stmt->fetchAll();
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $department_id = intval($_POST['department_id'] ?? 0);
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $status = $_POST['status'] ?? 'aktif';

    if (empty($nama)) $errors[] = 'Nama program wajib diisi.';
    if (!$department_id) $errors[] = 'Dinas wajib dipilih.';

    if (empty($errors)) {
        $stmt = $db->prepare("INSERT INTO programs (nama, department_id, deskripsi, status) VALUES (?,?,?,?)");
        $stmt->execute([$nama, $department_id, $deskripsi, $status]);
        setFlash('success', 'Program kerja berhasil ditambahkan!');
        redirect(APP_URL . '/programs/index.php');
    }
}
?>
<div class="page-content">
    <div style="max-width:600px;margin:0 auto">
        <div class="breadcrumb" style="margin-bottom:20px" data-animate>
            <a href="<?= APP_URL ?>/programs/index.php">Program Kerja</a>
            <span>/</span>
            <span style="color:var(--text-secondary)">Tambah Baru</span>
        </div>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-error">❌ <?= implode('<br>', array_map('sanitize', $errors)) ?></div>
        <?php endif; ?>

        <div class="glass-card" style="padding:32px" data-animate>
            <h2 style="font-family:var(--font-display);font-size:22px;font-weight:700;margin-bottom:24px">Tambah Program Kerja</h2>
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Nama Program *</label>
                    <input type="text" name="nama" class="form-control" placeholder="Contoh: Open Recruitment 2026"
                           value="<?= sanitize($_POST['nama'] ?? '') ?>" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Dinas *</label>
                        <select name="department_id" class="form-control" required>
                            <option value="">-- Pilih Dinas --</option>
                            <?php foreach ($departments as $d): ?>
                            <option value="<?= $d['id'] ?>" <?= ($_POST['department_id'] ?? '') == $d['id'] ? 'selected' : '' ?>>
                                <?= sanitize($d['nama']) ?>
                            </option>
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
                    <textarea name="deskripsi" class="form-control" rows="3" placeholder="Deskripsi singkat program ini..."><?= sanitize($_POST['deskripsi'] ?? '') ?></textarea>
                </div>
                <div style="display:flex;gap:12px">
                    <a href="<?= APP_URL ?>/programs/index.php" class="btn btn-outline" style="flex:1;justify-content:center">Batal</a>
                    <button type="submit" class="btn btn-primary" style="flex:2;justify-content:center">Simpan Program</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../template/footer.php'; ?>
