<?php
$pageTitle = 'Program Kerja';
require_once __DIR__ . '/../template/header.php';

$db = getDB();
$search = trim($_GET['search'] ?? '');
$dept_id = $_GET['dept_id'] ?? '';
$status = $_GET['status'] ?? '';

$where = ['1=1'];
$params = [];

if (isDeptManager($currentUser)) {
    $where[] = 'p.department_id = ?';
    $params[] = $currentUser['department_id'];
}

if ($search) { $where[] = 'p.nama LIKE ?'; $params[] = "%$search%"; }
if ($dept_id) { $where[] = 'p.department_id = ?'; $params[] = $dept_id; }
if ($status) { $where[] = 'p.status = ?'; $params[] = $status; }

$whereStr = implode(' AND ', $where);
$stmt = $db->prepare("
    SELECT p.*, d.nama as dept_nama,
           COUNT(a.id) as archive_count
    FROM programs p
    LEFT JOIN departments d ON p.department_id = d.id
    LEFT JOIN archives a ON p.id = a.program_id
    WHERE $whereStr
    GROUP BY p.id
    ORDER BY p.created_at DESC
");
$stmt->execute($params);
$programs = $stmt->fetchAll();

$departments = $db->query("SELECT * FROM departments ORDER BY nama")->fetchAll();
?>

<div class="page-content">
    <!-- for filter -->
    <div class="glass-card" style="padding:20px;margin-bottom:24px" data-animate>
        <form method="GET">
            <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end">
                <div style="flex:1;min-width:200px">
                    <label class="form-label">Cari Program</label>
                    <input type="text" name="search" class="form-control" placeholder="Nama program..." value="<?= sanitize($search) ?>">
                </div>
                <?php if (isAdmin($currentUser)): ?>
                <div style="min-width:160px">
                    <label class="form-label">Dinas</label>
                    <select name="dept_id" class="form-control">
                        <option value="">Semua Dinas</option>
                        <?php foreach ($departments as $d): ?>
                        <option value="<?= $d['id'] ?>" <?= $dept_id == $d['id'] ? 'selected' : '' ?>><?= sanitize($d['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                <div style="min-width:140px">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="">Semua</option>
                        <option value="persiapan" <?= $program['status'] == 'persiapan' ? 'selected' : '' ?>>Persiapan</option>
                        <option value="berjalan" <?= $program['status'] == 'berjalan' ? 'selected' : '' ?>>Berjalan</option>
                        <option value="selesai" <?= $program['status'] == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                        <option value="dibatalkan" <?= $program['status'] == 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                    </select>
                </div>
                <div style="display:flex;gap:8px">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <?php if ($search || $dept_id || $status): ?>
                    <a href="<?= APP_URL ?>/programs/index.php" class="btn btn-outline">✕</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <div class="section-header" data-animate>
        <div class="section-title">
            Daftar Program Kerja
            <span class="badge badge-blue"><?= count($programs) ?></span>
        </div>
        <?php if (isAdmin($currentUser) || isDeptManager($currentUser)): ?>
        <a href="<?= APP_URL ?>/programs/create.php" class="btn btn-primary">Tambah Program</a>
        <?php endif; ?>
    </div>

    <?php if (empty($programs)): ?>
    <div class="empty-state glass-card">
        <span class="empty-icon"></span>
        <div class="empty-title">Belum ada program kerja</div>
        <div class="empty-desc">Program kerja akan ditampilkan di sini</div>
    </div>
    <?php else: ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:18px">
        <?php foreach ($programs as $prog): ?>
        <div class="glass-card" style="padding:24px" data-animate>
            <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:14px">
                <div style="flex:1">
                    <div style="font-family:var(--font-display);font-size:15px;font-weight:700;color:var(--text-primary);margin-bottom:6px">
                        <?= sanitize($prog['nama']) ?>
                    </div>
                    <div style="font-size:12px;color:var(--text-muted)"><?= sanitize($prog['dept_nama']) ?></div>
                </div>
                <span class="badge <?= $prog['status'] == 'aktif' ? 'badge-green' : ($prog['status'] == 'selesai' ? 'badge-blue' : 'badge-red') ?>">
                    <?= ucfirst($prog['status']) ?>
                </span>
            </div>

            <?php if ($prog['deskripsi']): ?>
            <p style="font-size:12.5px;color:var(--text-muted);margin-bottom:14px;line-height:1.6">
                <?= sanitize(substr($prog['deskripsi'], 0, 100)) ?>...
            </p>
            <?php endif; ?>

            <div style="display:flex;align-items:center;justify-content:space-between;padding-top:12px;border-top:1px solid rgba(168,232,249,0.06)">
                <div style="display:flex;align-items:center;gap:6px">
                    <span style="font-size:12px;font-weight:600;color:var(--blue-light)"><?= $prog['archive_count'] ?> arsip</span>
                </div>
                <div style="display:flex;gap:8px">
                    <a href="<?= APP_URL ?>/archives/index.php?program_id=<?= $prog['id'] ?>" class="btn btn-outline btn-sm">Lihat Arsip</a>
                    <?php if (isAdmin($currentUser) || (isDeptManager($currentUser) && $prog['department_id'] == $currentUser['department_id'])): ?>
                    <a href="<?= APP_URL ?>/programs/edit.php?id=<?= $prog['id'] ?>" class="btn-icon" style="width:30px;height:30px;font-size:13px">✏️</a>
                    <button class="btn-icon" style="width:30px;height:30px;font-size:13px"
                        onclick="confirmDelete('<?= APP_URL ?>/programs/delete.php?id=<?= $prog['id'] ?>', '<?= sanitize($prog['nama']) ?>')">🗑️</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../template/footer.php'; ?>
