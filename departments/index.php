<?php
$pageTitle = 'Manajemen Dinas';
require_once __DIR__ . '/../template/header.php';

if (!isAdmin($currentUser) && !isDeptManager($currentUser)) {
    setFlash('error', 'Tidak memiliki akses.'); redirect(APP_URL . '/dashboard.php');
}

$db = getDB();
$stmt = $db->query("
    SELECT d.*,
        COUNT(DISTINCT dv.id) as div_count,
        COUNT(DISTINCT u.id) as user_count,
        COUNT(DISTINCT a.id) as archive_count
    FROM departments d
    LEFT JOIN divisions dv ON d.id = dv.department_id
    LEFT JOIN users u ON d.id = u.department_id AND u.is_active = 1
    LEFT JOIN archives a ON d.id = a.department_id
    GROUP BY d.id ORDER BY d.nama
");
$departments = $stmt->fetchAll();
?>
<div class="page-content">
    <div class="section-header" data-animate>
        <div class="section-title">Daftar Dinas <span class="badge badge-blue"><?= count($departments) ?></span></div>
        <?php if (isAdmin($currentUser)): ?>
        <a href="<?= APP_URL ?>/departments/create.php" class="btn btn-primary">Tambah Dinas</a>
        <?php endif; ?>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:18px" data-animate>
        <?php foreach ($departments as $dept): ?>
        <div class="glass-card" style="padding:24px">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:14px">
                <div>
                    <div style="font-family:var(--font-display);font-size:17px;font-weight:700;color:var(--text-primary)"><?= sanitize($dept['nama']) ?></div>
                    <div style="font-size:11px;color:var(--gold);font-weight:600;letter-spacing:1px;margin-top:3px"><?= sanitize($dept['kode']) ?></div>
                </div>
                <?php if (isAdmin($currentUser)): ?>
                <div style="display:flex;gap:6px">
                    <a href="<?= APP_URL ?>/departments/edit.php?id=<?= $dept['id'] ?>" class="btn-icon" style="width:30px;height:30px;font-size:13px">✏️</a>
                    <button class="btn-icon" style="width:30px;height:30px;font-size:13px"
                        onclick="confirmDelete('<?= APP_URL ?>/departments/delete.php?id=<?= $dept['id'] ?>', '<?= sanitize($dept['nama']) ?>')">🗑️</button>
                </div>
                <?php endif; ?>
            </div>

            <?php if ($dept['deskripsi']): ?>
            <p style="font-size:12.5px;color:var(--text-muted);margin-bottom:16px;line-height:1.6"><?= sanitize($dept['deskripsi']) ?></p>
            <?php endif; ?>

            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;padding-top:14px;border-top:1px solid rgba(168,232,249,0.06)">
                <div style="text-align:center">
                    <div style="font-family:var(--font-display);font-size:20px;font-weight:700;color:var(--blue-light)"><?= $dept['div_count'] ?></div>
                    <div style="font-size:10px;color:var(--text-muted);letter-spacing:0.5px">Divisi</div>
                </div>
                <div style="text-align:center">
                    <div style="font-family:var(--font-display);font-size:20px;font-weight:700;color:var(--gold)"><?= $dept['user_count'] ?></div>
                    <div style="font-size:10px;color:var(--text-muted);letter-spacing:0.5px">Anggota</div>
                </div>
                <div style="text-align:center">
                    <div style="font-family:var(--font-display);font-size:20px;font-weight:700;color:var(--yellow-light)"><?= $dept['archive_count'] ?></div>
                    <div style="font-size:10px;color:var(--text-muted);letter-spacing:0.5px">Arsip</div>
                </div>
            </div>

            <div style="margin-top:14px">
                <a href="<?= APP_URL ?>/archives/index.php?dept_id=<?= $dept['id'] ?>" class="btn btn-outline btn-sm" style="width:100%;justify-content:center">
                    Lihat Arsip Dinas
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php require_once __DIR__ . '/../template/footer.php'; ?>
