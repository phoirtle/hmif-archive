<?php
$pageTitle = 'Manajemen Divisi';
require_once __DIR__ . '/../template/header.php';
if (!isAdmin($currentUser)) { setFlash('error','Tidak memiliki akses.'); redirect(APP_URL.'/dashboard.php'); }
$db = getDB();
$divisions = $db->query("
    SELECT dv.*, d.nama as dept_nama,
        COUNT(DISTINCT u.id) as user_count,
        COUNT(DISTINCT a.id) as archive_count
    FROM divisions dv
    LEFT JOIN departments d ON dv.department_id = d.id
    LEFT JOIN users u ON dv.id = u.division_id AND u.is_active=1
    LEFT JOIN archives a ON dv.id = a.division_id
    GROUP BY dv.id ORDER BY d.nama, dv.nama
")->fetchAll();
?>
<div class="page-content">
    <div class="section-header" data-animate>
        <div class="section-title">Daftar Divisi <span class="badge badge-blue"><?= count($divisions) ?></span></div>
        <a href="<?= APP_URL ?>/divisions/create.php" class="btn btn-primary">Tambah Divisi</a>
    </div>
    <div class="table-container" data-animate>
        <table>
            <thead><tr><th>Nama Divisi</th><th>Dinas</th><th>Kode</th><th>Anggota</th><th>Arsip</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($divisions as $dv): ?>
            <tr>
                <td>
                    <div style="font-weight:600;color:var(--text-primary)"><?= sanitize($dv['nama']) ?></div>
                    <?php if ($dv['deskripsi']): ?><div style="font-size:11px;color:var(--text-muted)"><?= sanitize(substr($dv['deskripsi'],0,50)) ?></div><?php endif; ?>
                </td>
                <td><span class="badge badge-blue"><?= sanitize($dv['dept_nama']) ?></span></td>
                <td><span style="font-family:monospace;color:var(--gold);font-size:12px"><?= sanitize($dv['kode']) ?></span></td>
                <td style="color:var(--text-secondary)"><?= $dv['user_count'] ?></td>
                <td style="color:var(--text-secondary)"><?= $dv['archive_count'] ?></td>
                <td>
                    <div style="display:flex;gap:6px">
                        <a href="<?= APP_URL ?>/archives/index.php?div_id=<?= $dv['id'] ?>" class="btn btn-outline btn-sm">Arsip</a>
                        <a href="<?= APP_URL ?>/divisions/edit.php?id=<?= $dv['id'] ?>" class="btn-icon" style="width:30px;height:30px;font-size:13px">✏️</a>
                        <button class="btn-icon" style="width:30px;height:30px;font-size:13px"
                            onclick="confirmDelete('<?= APP_URL ?>/divisions/delete.php?id=<?= $dv['id'] ?>', '<?= sanitize($dv['nama']) ?>')">🗑️</button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../template/footer.php'; ?>
