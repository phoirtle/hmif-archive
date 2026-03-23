<?php
$pageTitle = 'Arsip';
require_once __DIR__ . '/../template/header.php';

$db = getDB();
$search = trim($_GET['search'] ?? '');
$kategori = $_GET['kategori'] ?? '';
$dept_id = $_GET['dept_id'] ?? '';
$div_id = $_GET['div_id'] ?? '';
$program_id = $_GET['program_id'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;

// build query based on role
$where = ['1=1'];
$params = [];

if (isDeptManager($currentUser)) {
    $where[] = 'a.department_id = ?';
    $params[] = $currentUser['department_id'];
} elseif (isDivManager($currentUser)) {
    $where[] = 'a.division_id = ?';
    $params[] = $currentUser['division_id'];
}

if ($search) {
    $where[] = '(a.judul LIKE ? OR a.deskripsi LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($kategori) { $where[] = 'a.kategori = ?'; $params[] = $kategori; }
if ($dept_id) { $where[] = 'a.department_id = ?'; $params[] = $dept_id; }
if ($div_id) { $where[] = 'a.division_id = ?'; $params[] = $div_id; }
if ($program_id) { $where[] = 'a.program_id = ?'; $params[] = $program_id; }

$whereStr = implode(' AND ', $where);

$countStmt = $db->prepare("SELECT COUNT(*) FROM archives a WHERE $whereStr");
$countStmt->execute($params);
$total = $countStmt->fetchColumn();
$totalPages = ceil($total / $perPage);

$stmt = $db->prepare("
    SELECT a.*, d.nama as dept_nama, dv.nama as div_nama, 
           p.nama as program_nama, u.nama_lengkap as uploader_name
    FROM archives a
    LEFT JOIN departments d ON a.department_id = d.id
    LEFT JOIN divisions dv ON a.division_id = dv.id
    LEFT JOIN programs p ON a.program_id = p.id
    LEFT JOIN users u ON a.uploaded_by = u.id
    WHERE $whereStr
    ORDER BY a.created_at DESC
    LIMIT $perPage OFFSET $offset
");
$stmt->execute($params);
$archives = $stmt->fetchAll();

// for filters
$departments = $db->query("SELECT * FROM departments ORDER BY nama")->fetchAll();
$divisions = $db->query("SELECT * FROM divisions ORDER BY nama")->fetchAll();
$programs = $db->query("SELECT * FROM programs ORDER BY nama")->fetchAll();
?>

<div class="page-content">
    <!-- for filter bar-->
    <div class="glass-card" style="padding:20px;margin-bottom:24px" data-animate>
        <form method="GET" action="">
            <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end">
                <div style="flex:1;min-width:200px">
                    <label class="form-label">Cari Arsip</label>
                    <input type="text" name="search" class="form-control" placeholder="Judul atau deskripsi..." value="<?= sanitize($search) ?>">
                </div>
                <div style="min-width:140px">
                    <label class="form-label">Kategori</label>
                    <select name="kategori" class="form-control">
                        <option value="">Semua</option>
                        <option value="Proker" <?= $kategori == 'Proker' ? 'selected' : '' ?>>Proker</option>
                        <option value="Non-Proker" <?= $kategori == 'Non-Proker' ? 'selected' : '' ?>>Non-Proker</option>
                    </select>
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
                <div style="min-width:160px">
                    <label class="form-label">Divisi</label>
                    <select name="div_id" class="form-control">
                        <option value="">Semua Divisi</option>
                        <?php foreach ($divisions as $dv): ?>
                        <option value="<?= $dv['id'] ?>" <?= $div_id == $dv['id'] ? 'selected' : '' ?>><?= sanitize($dv['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                <div style="display:flex;gap:8px">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <?php if ($search || $kategori || $dept_id || $div_id): ?>
                    <a href="<?= APP_URL ?>/archives/index.php" class="btn btn-outline">✕ Reset</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <!-- header -->
    <div class="section-header" data-animate>
        <div>
            <div class="section-title">
                Daftar Arsip
                <span class="badge badge-blue"><?= number_format($total) ?></span>
            </div>
            <p style="font-size:12px;color:var(--text-muted);margin-top:4px">
                <?php if ($search): ?>Hasil pencarian: "<em><?= sanitize($search) ?></em>"<?php endif; ?>
            </p>
        </div>
        <div style="display:flex;gap:8px;align-items:center">
            <!-- for view toggle -->
            <button class="btn-icon" onclick="toggleView('grid')" id="gridViewBtn" data-tooltip="Grid View">⊞</button>
            <button class="btn-icon" onclick="toggleView('list')" id="listViewBtn" data-tooltip="List View">≡</button>
            <?php if (!in_array($currentUser['role'], ROLE_STAFF)): ?>
            <a href="<?= APP_URL ?>/archives/upload.php" class="btn btn-primary">Upload</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- for grid view -->
    <div id="archiveGridView" class="archive-grid" data-animate>
        <?php if (empty($archives)): ?>
        <div class="empty-state" style="grid-column:1/-1">
            <span class="empty-icon">📂</span>
            <div class="empty-title">Tidak ada arsip ditemukan</div>
            <div class="empty-desc">
                <?= $search ? 'Coba kata kunci lain' : 'Belum ada arsip yang diupload' ?>
            </div>
            <?php if (!in_array($currentUser['role'], ROLE_STAFF)): ?>
            <br><a href="<?= APP_URL ?>/archives/upload.php" class="btn btn-primary" style="margin-top:12px">Upload Arsip Pertama</a>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <?php foreach ($archives as $arch): ?>
        <div class="archive-card">
            <div class="archive-card-shimmer"></div>
            <div style="display:flex;align-items:flex-start;gap:14px">
                <div class="file-type-icon <?= getFileIconClass($arch['filetype']) ?>">
                    <?= getFileIcon($arch['filetype']) ?>
                </div>
                <div style="flex:1;min-width:0">
                    <div class="archive-card-title"><?= sanitize($arch['judul']) ?></div>
                    <?php if ($arch['deskripsi']): ?>
                    <div style="font-size:12px;color:var(--text-muted);margin-top:4px;line-height:1.5">
                        <?= sanitize(substr($arch['deskripsi'], 0, 80)) ?>...
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="archive-card-meta">
                <span class="badge <?= $arch['kategori'] == 'Proker' ? 'badge-blue' : 'badge-gold' ?>">
                    <?= $arch['kategori'] ?>
                </span>
                <?php if ($arch['program_nama']): ?>
                <span class="badge badge-gray" style="font-size:10px"><?= sanitize(substr($arch['program_nama'], 0, 25)) ?>...</span>
                <?php endif; ?>
            </div>

            <div style="display:flex;flex-wrap:wrap;gap:8px;font-size:11px;color:var(--text-muted)">
                <span>🏛️ <?= sanitize($arch['dept_nama']) ?></span>
                <?php if ($arch['div_nama']): ?>
                <span>🔖 <?= sanitize($arch['div_nama']) ?></span>
                <?php endif; ?>
                <span>📦 <?= formatFileSize($arch['filesize'] ?? 0) ?></span>
            </div>

            <div class="archive-card-footer">
                <div style="font-size:11px;color:var(--text-muted)">
                    <span>👤 <?= sanitize($arch['uploader_name']) ?></span><br>
                    <span>🕐 <?= timeAgo($arch['created_at']) ?></span>
                </div>
                <div style="display:flex;gap:8px;align-items:center">
                    <?php if (canManageArchive($arch, $currentUser)): ?>
                    <a href="<?= APP_URL ?>/archives/edit.php?id=<?= $arch['id'] ?>" class="btn-icon" data-tooltip="Edit" style="width:30px;height:30px;font-size:13px">✏️</a>
                    <button class="btn-icon" data-tooltip="Hapus" style="width:30px;height:30px;font-size:13px" 
                        onclick="confirmDelete('<?= APP_URL ?>/archives/delete.php?id=<?= $arch['id'] ?>', '<?= sanitize($arch['judul']) ?>')">🗑️</button>
                    <?php endif; ?>
                    <a href="<?= APP_URL ?>/archives/download.php?id=<?= $arch['id'] ?>" class="download-btn">
                        Unduh
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- list view (hidden by default) -->
    <div id="archiveListView" style="display:none" data-animate>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Judul</th>
                        <th>Kategori</th>
                        <th>Program</th>
                        <th>Dinas</th>
                        <th>Ukuran</th>
                        <th>Diupload</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($archives as $arch): ?>
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px">
                                <span style="font-size:18px"><?= getFileIcon($arch['filetype']) ?></span>
                                <div>
                                    <div style="font-weight:600;color:var(--text-primary)"><?= sanitize($arch['judul']) ?></div>
                                    <?php if ($arch['div_nama']): ?>
                                    <div style="font-size:11px;color:var(--text-muted)"><?= sanitize($arch['div_nama']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge <?= $arch['kategori'] == 'Proker' ? 'badge-blue' : 'badge-gold' ?>"><?= $arch['kategori'] ?></span></td>
                        <td style="font-size:12px;color:var(--text-muted)"><?= $arch['program_nama'] ? sanitize(substr($arch['program_nama'],0,30)).'...' : '—' ?></td>
                        <td style="font-size:12px;color:var(--text-muted)"><?= sanitize($arch['dept_nama']) ?></td>
                        <td style="font-size:12px;color:var(--text-muted)"><?= formatFileSize($arch['filesize'] ?? 0) ?></td>
                        <td style="font-size:11px;color:var(--text-muted)"><?= timeAgo($arch['created_at']) ?></td>
                        <td>
                            <div style="display:flex;gap:6px">
                                <a href="<?= APP_URL ?>/archives/download.php?id=<?= $arch['id'] ?>" class="download-btn">Unduh</a>
                                <?php if (canManageArchive($arch, $currentUser)): ?>
                                <a href="<?= APP_URL ?>/archives/edit.php?id=<?= $arch['id'] ?>" class="btn-icon" style="width:30px;height:30px;font-size:13px">✏️</a>
                                <button class="btn-icon" style="width:30px;height:30px;font-size:13px"
                                    onclick="confirmDelete('<?= APP_URL ?>/archives/delete.php?id=<?= $arch['id'] ?>', '<?= sanitize($arch['judul']) ?>')">🗑️</button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination" data-animate>
        <?php if ($page > 1): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page-1])) ?>" class="page-btn">‹</a>
        <?php endif; ?>
        <?php for ($i = max(1, $page-2); $i <= min($totalPages, $page+2); $i++): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" class="page-btn <?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page+1])) ?>" class="page-btn">›</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<script>
let currentView = localStorage.getItem('archiveView') || 'grid';
function toggleView(view) {
    currentView = view;
    localStorage.setItem('archiveView', view);
    document.getElementById('archiveGridView').style.display = view === 'grid' ? 'grid' : 'none';
    document.getElementById('archiveListView').style.display = view === 'list' ? 'block' : 'none';
    document.getElementById('gridViewBtn').style.background = view === 'grid' ? 'rgba(0,83,122,0.5)' : '';
    document.getElementById('listViewBtn').style.background = view === 'list' ? 'rgba(0,83,122,0.5)' : '';
}
toggleView(currentView);
</script>

<?php require_once __DIR__ . '/../template/footer.php'; ?>
