<?php
$pageTitle = 'Manajemen Pengguna';
require_once __DIR__ . '/../template/header.php';
if (!isAdmin($currentUser)) { setFlash('error','Tidak memiliki akses.'); redirect(APP_URL.'/dashboard.php'); }
$db = getDB();
$search = trim($_GET['search']??'');
$role_filter = $_GET['role']??'';
$dept_filter = $_GET['dept_id']??'';

$where = ['1=1'];
$params = [];
if ($search) { $where[] = '(u.nama_lengkap LIKE ? OR u.email LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($role_filter) { $where[] = 'u.role = ?'; $params[] = $role_filter; }
if ($dept_filter) { $where[] = 'u.department_id = ?'; $params[] = $dept_filter; }

$stmt = $db->prepare("
    SELECT u.*, d.nama as dept_nama, dv.nama as div_nama
    FROM users u
    LEFT JOIN departments d ON u.department_id = d.id
    LEFT JOIN divisions dv ON u.division_id = dv.id
    WHERE ".implode(' AND ',$where)."
    ORDER BY u.role, u.nama_lengkap
");
$stmt->execute($params);
$users = $stmt->fetchAll();
$departments = $db->query("SELECT * FROM departments ORDER BY nama")->fetchAll();
$roles = ['ketua','waketua','sekum','bendum','kadin','wakadin','kadiv','staf'];
?>
<div class="page-content">
    <!-- Filter -->
    <div class="glass-card" style="padding:18px;margin-bottom:24px" data-animate>
        <form method="GET" style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end">
            <div style="flex:1;min-width:180px"><label class="form-label">Cari</label><input type="text" name="search" class="form-control" placeholder="Nama atau email..." value="<?= sanitize($search) ?>"></div>
            <div style="min-width:130px"><label class="form-label">Role</label>
                <select name="role" class="form-control">
                    <option value="">Semua Role</option>
                    <?php foreach ($roles as $r): ?><option value="<?= $r ?>" <?= $role_filter==$r?'selected':'' ?>><?= getRoleLabel($r) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div style="min-width:150px"><label class="form-label">Dinas</label>
                <select name="dept_id" class="form-control">
                    <option value="">Semua Dinas</option>
                    <?php foreach ($departments as $d): ?><option value="<?= $d['id'] ?>" <?= $dept_filter==$d['id']?'selected':'' ?>><?= sanitize($d['nama']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div style="display:flex;gap:8px">
                <button type="submit" class="btn btn-primary">Cari</button>
                <?php if ($search||$role_filter||$dept_filter): ?><a href="<?= APP_URL ?>/users/index.php" class="btn btn-outline">✕</a><?php endif; ?>
            </div>
        </form>
    </div>

    <div class="section-header" data-animate>
        <div class="section-title">Pengguna <span class="badge badge-blue"><?= count($users) ?></span></div>
        <a href="<?= APP_URL ?>/users/create.php" class="btn btn-primary">Tambah Pengguna</a>
    </div>

    <div class="table-container" data-animate>
        <table>
            <thead><tr><th>Pengguna</th><th>Role</th><th>Dinas / Divisi</th><th>Status</th><th>Bergabung</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:10px">
                        <div class="user-avatar-placeholder" style="width:34px;height:34px;font-size:13px;border-radius:50%"><?= strtoupper(substr($u['nama_lengkap'],0,1)) ?></div>
                        <div>
                            <div style="font-weight:600;color:var(--text-primary);font-size:13px"><?= sanitize($u['nama_lengkap']) ?></div>
                            <div style="font-size:11px;color:var(--text-muted)"><?= sanitize($u['email']) ?></div>
                        </div>
                    </div>
                </td>
                <td><span class="badge badge-blue" style="font-size:10px"><?= getRoleLabel($u['role']) ?></span></td>
                <td style="font-size:12px;color:var(--text-muted)">
                    <?= $u['dept_nama'] ? sanitize($u['dept_nama']) : '—' ?>
                    <?php if ($u['div_nama']): ?><br><span style="font-size:11px"><?= sanitize($u['div_nama']) ?></span><?php endif; ?>
                </td>
                <td><span class="badge <?= $u['is_active'] ? 'badge-green' : 'badge-red' ?>"><?= $u['is_active'] ? 'Aktif' : 'Nonaktif' ?></span></td>
                <td style="font-size:11px;color:var(--text-muted)"><?= formatDate($u['created_at']) ?></td>
                <td>
                    <div style="display:flex;gap:6px">
                        <a href="<?= APP_URL ?>/users/edit.php?id=<?= $u['id'] ?>" class="btn-icon" style="width:30px;height:30px;font-size:13px" data-tooltip="Edit">✏️</a>
                        <?php if ($u['id'] != $currentUser['id']): ?>
                        <button class="btn-icon" style="width:30px;height:30px;font-size:13px" data-tooltip="Hapus"
                            onclick="confirmDelete('<?= APP_URL ?>/users/delete.php?id=<?= $u['id'] ?>', '<?= sanitize($u['nama_lengkap']) ?>')">🗑️</button>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../template/footer.php'; ?>
