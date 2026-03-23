<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$pageTitle = 'Dashboard';
require_once __DIR__ . '/template/header.php';

$db = getDB();

if (isAdmin($currentUser)) {
    $totalArchives = $db->query("SELECT COUNT(*) FROM archives")->fetchColumn();
    $totalUsers = $db->query("SELECT COUNT(*) FROM users WHERE is_active=1")->fetchColumn();
    $totalPrograms = $db->query("SELECT COUNT(*) FROM programs WHERE status='aktif'")->fetchColumn();
    $totalDepts = $db->query("SELECT COUNT(*) FROM departments")->fetchColumn();
    
    // for recent archives
    $recentArchives = $db->query("
        SELECT a.*, d.nama as dept_nama, dv.nama as div_nama, 
               u.nama_lengkap as uploader_name, u.foto_profil
        FROM archives a
        LEFT JOIN departments d ON a.department_id = d.id
        LEFT JOIN divisions dv ON a.division_id = dv.id
        LEFT JOIN users u ON a.uploaded_by = u.id
        ORDER BY a.created_at DESC LIMIT 8
    ")->fetchAll();

    // top depts by archive count
    $deptStats = $db->query("
        SELECT d.nama, COUNT(a.id) as total
        FROM departments d
        LEFT JOIN archives a ON d.id = a.department_id
        GROUP BY d.id ORDER BY total DESC
    ")->fetchAll();
    
} elseif (isDeptManager($currentUser)) {
    $deptId = $currentUser['department_id'];
    $totalArchives = $db->prepare("SELECT COUNT(*) FROM archives WHERE department_id=?");
    $totalArchives->execute([$deptId]);
    $totalArchives = $totalArchives->fetchColumn();
    
    $totalPrograms = $db->prepare("SELECT COUNT(*) FROM programs WHERE department_id=? AND status='aktif'");
    $totalPrograms->execute([$deptId]);
    $totalPrograms = $totalPrograms->fetchColumn();
    
    $totalUsers = $db->prepare("SELECT COUNT(*) FROM users WHERE department_id=? AND is_active=1");
    $totalUsers->execute([$deptId]);
    $totalUsers = $totalUsers->fetchColumn();
    
    $recentArchives = $db->prepare("
        SELECT a.*, d.nama as dept_nama, dv.nama as div_nama,
               u.nama_lengkap as uploader_name
        FROM archives a
        LEFT JOIN departments d ON a.department_id = d.id
        LEFT JOIN divisions dv ON a.division_id = dv.id
        LEFT JOIN users u ON a.uploaded_by = u.id
        WHERE a.department_id = ?
        ORDER BY a.created_at DESC LIMIT 8
    ");
    $recentArchives->execute([$deptId]);
    $recentArchives = $recentArchives->fetchAll();
    $totalDepts = 1;
    
} elseif (isDivManager($currentUser)) {
    $divId = $currentUser['division_id'];
    $totalArchives = $db->prepare("SELECT COUNT(*) FROM archives WHERE division_id=?");
    $totalArchives->execute([$divId]);
    $totalArchives = $totalArchives->fetchColumn();
    $totalPrograms = 0; $totalUsers = 0; $totalDepts = 0;
    
    $recentArchives = $db->prepare("
        SELECT a.*, d.nama as dept_nama, dv.nama as div_nama,
               u.nama_lengkap as uploader_name
        FROM archives a
        LEFT JOIN departments d ON a.department_id = d.id
        LEFT JOIN divisions dv ON a.division_id = dv.id
        LEFT JOIN users u ON a.uploaded_by = u.id
        WHERE a.division_id = ?
        ORDER BY a.created_at DESC LIMIT 8
    ");
    $recentArchives->execute([$divId]);
    $recentArchives = $recentArchives->fetchAll();
    
} else {
    // for staf, only public access
    $totalArchives = $db->query("SELECT COUNT(*) FROM archives")->fetchColumn();
    $totalPrograms = $db->query("SELECT COUNT(*) FROM programs WHERE status='aktif'")->fetchColumn();
    $totalUsers = 0; $totalDepts = 0;
    
    $recentArchives = $db->query("
        SELECT a.*, d.nama as dept_nama, dv.nama as div_nama,
               u.nama_lengkap as uploader_name
        FROM archives a
        LEFT JOIN departments d ON a.department_id = d.id
        LEFT JOIN divisions dv ON a.division_id = dv.id
        LEFT JOIN users u ON a.uploaded_by = u.id
        ORDER BY a.created_at DESC LIMIT 8
    ")->fetchAll();
}

// for category breakdown
$prokerCount = $db->query("SELECT COUNT(*) FROM archives WHERE kategori='Proker'")->fetchColumn();
$nonProkerCount = $db->query("SELECT COUNT(*) FROM archives WHERE kategori='Non-Proker'")->fetchColumn();
?>

<div class="page-content">
    <!-- for welcome banner -->
    <div class="glass-card" style="padding:28px 32px;margin-bottom:28px;background:linear-gradient(135deg,rgba(0,83,122,0.45),rgba(1,60,88,0.35));overflow:hidden;position:relative" data-animate>
        <div style="position:absolute;right:-20px;top:-30px;font-size:120px;opacity:0.06;pointer-events:none">🎓</div>
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px">
            <div>
                <div style="font-family:var(--font-display);font-size:24px;font-weight:800;color:var(--text-primary);margin-bottom:4px">
                    Selamat datang, <?= sanitize(explode(' ', $currentUser['nama_lengkap'])[0]) ?>!
                </div>
                <p style="color:var(--text-muted);font-size:13.5px">
                    <?= getRoleLabel($currentUser['role']) ?> 
                    <?php if ($currentUser['dept_nama']): ?> · <?= sanitize($currentUser['dept_nama']) ?><?php endif; ?>
                    <?php if ($currentUser['div_nama']): ?> · <?= sanitize($currentUser['div_nama']) ?><?php endif; ?>
                </p>
                <p style="color:var(--text-muted);font-size:12px;margin-top:4px"><?= date('l, d F Y') ?></p>
            </div>
            <?php if (!in_array($currentUser['role'], ROLE_STAFF)): ?>
            <a href="<?= APP_URL ?>/archives/upload.php" class="btn btn-primary">
                Upload Arsip
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- for stats -->
    <div class="stats-grid" style="margin-bottom:28px">
        <div class="stat-card" data-animate>
            <span class="stat-icon">🗂️</span>
            <div class="stat-label">Total Arsip</div>
            <div class="stat-value" data-count="<?= $totalArchives ?>"><?= number_format($totalArchives) ?></div>
            <div class="stat-change"><?= $prokerCount ?> Proker · <?= $nonProkerCount ?> Non-Proker</div>
        </div>
        <div class="stat-card" data-animate>
            <span class="stat-icon">📋</span>
            <div class="stat-label">Program Aktif</div>
            <div class="stat-value" data-count="<?= $totalPrograms ?>"><?= number_format($totalPrograms) ?></div>
            <div class="stat-change">Program berjalan</div>
        </div>
        <?php if (isAdmin($currentUser)): ?>
        <div class="stat-card" data-animate>
            <span class="stat-icon">👥</span>
            <div class="stat-label">Pengguna Aktif</div>
            <div class="stat-value" data-count="<?= $totalUsers ?>"><?= number_format($totalUsers) ?></div>
            <div class="stat-change">Anggota aktif</div>
        </div>
        <div class="stat-card" data-animate>
            <span class="stat-icon">🏛️</span>
            <div class="stat-label">Total Dinas</div>
            <div class="stat-value" data-count="<?= $totalDepts ?>"><?= number_format($totalDepts) ?></div>
            <div class="stat-change">Unit kerja</div>
        </div>
        <?php elseif (isDeptManager($currentUser)): ?>
        <div class="stat-card" data-animate>
            <span class="stat-icon">👥</span>
            <div class="stat-label">Anggota Dinas</div>
            <div class="stat-value" data-count="<?= $totalUsers ?>"><?= number_format($totalUsers) ?></div>
        </div>
        <?php endif; ?>
    </div>

    <!-- two column layout -->
    <div style="display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start" class="dashboard-grid">
        <!-- recent rkive -->
        <div data-animate>
            <div class="section-header">
                <div class="section-title">Arsip Terbaru</div>
                <a href="<?= APP_URL ?>/archives/index.php" class="btn btn-outline btn-sm">Lihat Semua</a>
            </div>

            <?php if (empty($recentArchives)): ?>
            <div class="empty-state glass-card">
                <div class="empty-title">Belum ada arsip</div>
                <div class="empty-desc">Mulai upload arsip pertama Anda</div>
            </div>
            <?php else: ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Kategori</th>
                            <th>Dinas</th>
                            <th>Waktu</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentArchives as $arch): ?>
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px">
                                    <div class="file-type-icon <?= getFileIconClass($arch['filetype']) ?>" style="width:34px;height:34px;border-radius:8px;font-size:16px">
                                        <?= getFileIcon($arch['filetype']) ?>
                                    </div>
                                    <div>
                                        <div style="font-weight:600;color:var(--text-primary);font-size:13px"><?= sanitize(substr($arch['judul'], 0, 40)) ?><?= strlen($arch['judul']) > 40 ? '...' : '' ?></div>
                                        <div style="font-size:11px;color:var(--text-muted)"><?= $arch['uploader_name'] ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge <?= $arch['kategori'] == 'Proker' ? 'badge-blue' : 'badge-gold' ?>"><?= $arch['kategori'] ?></span></td>
                            <td style="font-size:12px;color:var(--text-muted)"><?= sanitize($arch['dept_nama']) ?></td>
                            <td style="font-size:11px;color:var(--text-muted)"><?= timeAgo($arch['created_at']) ?></td>
                            <td>
                                <a href="<?= APP_URL ?>/archives/download.php?id=<?= $arch['id'] ?>" class="download-btn" title="Download">
                                    Unduh
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <!-- for side panel -->
        <div style="display:flex;flex-direction:column;gap:20px">
            <!-- for category chart -->
            <div class="glass-card" style="padding:22px" data-animate>
                <div class="section-title" style="margin-bottom:18px;font-size:15px">Distribusi Arsip</div>
                <div style="margin-bottom:16px">
                    <div style="display:flex;justify-content:space-between;margin-bottom:6px">
                        <span style="font-size:12px;color:var(--text-muted)">Arsip Proker</span>
                        <span style="font-size:12px;font-weight:700;color:var(--blue-light)"><?= $prokerCount ?></span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width:<?= $totalArchives > 0 ? round(($prokerCount/$totalArchives)*100) : 0 ?>%"></div>
                    </div>
                </div>
                <div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:6px">
                        <span style="font-size:12px;color:var(--text-muted)">Arsip Non-Proker</span>
                        <span style="font-size:12px;font-weight:700;color:var(--gold)"><?= $nonProkerCount ?></span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width:<?= $totalArchives > 0 ? round(($nonProkerCount/$totalArchives)*100) : 0 ?>;background:linear-gradient(90deg,var(--gold),var(--yellow-light))"></div>
                    </div>
                </div>
            </div>

            <?php if (isAdmin($currentUser) && !empty($deptStats)): ?>
            <!-- for department stats -->
            <div class="glass-card" style="padding:22px" data-animate>
                <div class="section-title" style="margin-bottom:18px;font-size:15px">Arsip per Dinas</div>
                <?php foreach ($deptStats as $dept): ?>
                <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid rgba(168,232,249,0.05)">
                    <span style="font-size:12.5px;color:var(--text-secondary)"><?= sanitize($dept['nama']) ?></span>
                    <span class="badge badge-blue" style="font-size:11px"><?= $dept['total'] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
@media (max-width: 900px) {
    .dashboard-grid { grid-template-columns: 1fr !important; }
}
</style>

<?php require_once __DIR__ . '/template/footer.php'; ?>
