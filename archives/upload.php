<?php
$pageTitle = 'Upload Arsip';
require_once __DIR__ . '/../template/header.php';

// only non-staff can upload
if (in_array($currentUser['role'], ROLE_STAFF)) {
    setFlash('error', 'Anda tidak memiliki izin untuk mengupload arsip.');
    redirect(APP_URL . '/archives/index.php');
}

$db = getDB();

// get departments and divisions based on role
if (isAdmin($currentUser)) {
    $departments = $db->query("SELECT * FROM departments ORDER BY nama")->fetchAll();
} elseif (isDeptManager($currentUser)) {
    $stmt = $db->prepare("SELECT * FROM departments WHERE id = ?");
    $stmt->execute([$currentUser['department_id']]);
    $departments = $stmt->fetchAll();
} else {
    $stmt = $db->prepare("SELECT * FROM departments WHERE id = ?");
    $stmt->execute([$currentUser['department_id']]);
    $departments = $stmt->fetchAll();
}

$divisions = $db->query("SELECT * FROM divisions ORDER BY nama")->fetchAll();
$programs = $db->query("SELECT * FROM programs WHERE status != 'dibatalkan' ORDER BY nama")->fetchAll();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul'] ?? '');
    $kategori = $_POST['kategori'] ?? '';
    $program_id = !empty($_POST['program_id']) ? intval($_POST['program_id']) : null;
    $department_id = intval($_POST['department_id'] ?? 0);
    $division_id = !empty($_POST['division_id']) ? intval($_POST['division_id']) : null;
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $is_public = isset($_POST['is_public']) ? 1 : 0;

    // validate
    if (empty($judul)) $errors[] = 'Judul wajib diisi.';
    if (empty($kategori)) $errors[] = 'Kategori wajib dipilih.';
    if (!$department_id) $errors[] = 'Dinas wajib dipilih.';
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'File wajib diupload.';
    }

    // permission check
    if (!canUploadToScope($department_id, $division_id, $currentUser)) {
        $errors[] = 'Anda tidak memiliki izin untuk mengupload ke dinas/divisi ini.';
    }

    if (empty($errors)) {
        $file = $_FILES['file'];
        if ($file['size'] > MAX_FILE_SIZE) {
            $errors[] = 'Ukuran file melebihi batas (50MB).';
        } elseif (!isAllowedFile($file['name'])) {
            $errors[] = 'Tipe file tidak diizinkan.';
        } else {
            // create upload dir
            if (!is_dir(UPLOAD_PATH)) mkdir(UPLOAD_PATH, 0755, true);
            
            $filename = generateUniqueFilename($file['name']);
            $destPath = UPLOAD_PATH . $filename;
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (move_uploaded_file($file['tmp_name'], $destPath)) {
                $stmt = $db->prepare("
                    INSERT INTO archives (judul, kategori, program_id, department_id, division_id, 
                        filename, original_filename, filesize, filetype, uploaded_by, deskripsi, is_public)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $judul, $kategori, $program_id, $department_id, $division_id,
                    $filename, $file['name'], $file['size'], $ext,
                    $currentUser['id'], $deskripsi, $is_public
                ]);
                setFlash('success', 'Arsip berhasil diupload!');
                redirect(APP_URL . '/archives/index.php');
            } else {
                $errors[] = 'Gagal menyimpan file. Coba lagi.';
            }
        }
    }
}
?>

<div class="page-content">
    <div style="max-width:700px;margin:0 auto">
        <!-- breadcrumb -->
        <div class="breadcrumb" style="margin-bottom:20px" data-animate>
            <a href="<?= APP_URL ?>/archives/index.php">Arsip</a>
            <span>/</span>
            <span style="color:var(--text-secondary)">Upload Baru</span>
        </div>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-error" data-animate>
            <div>
                ❌ <strong>Terdapat kesalahan:</strong>
                <ul style="margin-top:6px;padding-left:20px">
                    <?php foreach ($errors as $e): ?>
                    <li><?= sanitize($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>

        <div class="glass-card" style="padding:32px" data-animate>
            <div style="margin-bottom:28px">
                <h2 style="font-family:var(--font-display);font-size:22px;font-weight:700;color:var(--text-primary);margin-bottom:4px">Upload</h2>
                <p style="color:var(--text-muted);font-size:13px">Upload dokumen arsip ke sistem HMIF</p>
            </div>

            <form method="POST" enctype="multipart/form-data">
                <!-- File Upload Area -->
                <div class="form-group">
                    <label class="form-label">File Arsip <span style="color:#f87171">*</span></label>
                    <div class="file-upload-area">
                        <input type="file" name="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.zip,.rar" required>
                        <span class="upload-icon">📁</span>
                        <div class="upload-text">Drag & drop atau klik untuk memilih file</div>
                        <div class="upload-hint">PDF, Word, Excel, PowerPoint, Gambar, ZIP — Maks. 50MB</div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Judul Arsip <span style="color:#f87171">*</span></label>
                        <input type="text" name="judul" class="form-control" 
                               placeholder="Contoh: Proposal Open Recruitment 2026"
                               value="<?= sanitize($_POST['judul'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kategori <span style="color:#f87171">*</span></label>
                        <select name="kategori" class="form-control" required id="kategoriSelect" onchange="handleKategoriChange(this.value)">
                            <option value="">-- Pilih Kategori --</option>
                            <option value="Proker" <?= ($_POST['kategori'] ?? '') == 'Proker' ? 'selected' : '' ?>>Program Kerja (Proker)</option>
                            <option value="Non-Proker" <?= ($_POST['kategori'] ?? '') == 'Non-Proker' ? 'selected' : '' ?>>Non-Proker (Arsip Umum)</option>
                        </select>
                    </div>
                </div>

                <!-- program (only for Proker) -->
                <div class="form-group" id="programGroup" style="display:none">
                    <label class="form-label">Program Kerja</label>
                    <select name="program_id" class="form-control" id="programSelect">
                        <option value="">-- Tidak terkait program tertentu --</option>
                        <?php foreach ($programs as $prog): ?>
                        <option value="<?= $prog['id'] ?>" data-dept="<?= $prog['department_id'] ?>"
                            <?= ($_POST['program_id'] ?? '') == $prog['id'] ? 'selected' : '' ?>>
                            <?= sanitize($prog['nama']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Dinas <span style="color:#f87171">*</span></label>
                        <select name="department_id" class="form-control" required id="deptSelect" onchange="filterByDept(this.value)">
                            <option value="">-- Pilih Dinas --</option>
                            <?php foreach ($departments as $d): ?>
                            <option value="<?= $d['id'] ?>" <?= ($_POST['department_id'] ?? $currentUser['department_id']) == $d['id'] ? 'selected' : '' ?>>
                                <?= sanitize($d['nama']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Divisi <span style="color:var(--text-muted)">(opsional)</span></label>
                        <select name="division_id" class="form-control" id="division_id">
                            <option value="">-- Tidak spesifik divisi --</option>
                            <?php foreach ($divisions as $dv): ?>
                            <option value="<?= $dv['id'] ?>" data-dept="<?= $dv['department_id'] ?>"
                                <?= ($_POST['division_id'] ?? $currentUser['division_id']) == $dv['id'] ? 'selected' : '' ?>>
                                <?= sanitize($dv['nama']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Deskripsi <span style="color:var(--text-muted)">(opsional)</span></label>
                    <textarea name="deskripsi" class="form-control" rows="3" 
                              placeholder="Deskripsi singkat tentang arsip ini..."><?= sanitize($_POST['deskripsi'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 16px;background:rgba(168,232,249,0.05);border:1px solid rgba(168,232,249,0.12);border-radius:var(--radius-md)">
                        <div>
                            <div style="font-size:14px;font-weight:600;color:var(--text-primary)">Publik</div>
                            <div style="font-size:12px;color:var(--text-muted)">Arsip ini dapat dilihat semua anggota</div>
                        </div>
                        <label class="toggle">
                            <input type="checkbox" name="is_public" value="1" <?= ($_POST['is_public'] ?? '') ? 'checked' : '' ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>

                <div style="display:flex;gap:12px;margin-top:8px">
                    <a href="<?= APP_URL ?>/archives/index.php" class="btn btn-outline" style="flex:1;justify-content:center">Batal</a>
                    <button type="submit" class="btn btn-primary" style="flex:2;justify-content:center">
                        Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function handleKategoriChange(val) {
    const programGroup = document.getElementById('programGroup');
    programGroup.style.display = val === 'Proker' ? 'block' : 'none';
}

// init on load
const kategoriVal = document.getElementById('kategoriSelect').value;
if (kategoriVal) handleKategoriChange(kategoriVal);

// init dept filter
const deptVal = document.getElementById('deptSelect').value;
if (deptVal) filterByDept(deptVal);
</script>

<?php require_once __DIR__ . '/../template/footer.php'; ?>
