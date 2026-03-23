<?php
$pageTitle = 'Edit Arsip';
require_once __DIR__ . '/../template/header.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) redirect(APP_URL . '/archives/index.php');

$db = getDB();
$stmt = $db->prepare("SELECT * FROM archives WHERE id = ?");
$stmt->execute([$id]);
$archive = $stmt->fetch();

if (!$archive || !canManageArchive($archive, $currentUser)) {
    setFlash('error', 'Arsip tidak ditemukan atau Anda tidak memiliki akses.');
    redirect(APP_URL . '/archives/index.php');
}

$departments = $db->query("SELECT * FROM departments ORDER BY nama")->fetchAll();
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

    if (empty($judul)) $errors[] = 'Judul wajib diisi.';
    if (empty($kategori)) $errors[] = 'Kategori wajib dipilih.';

    if (empty($errors)) {
        // handle new file upload
        $newFilename = $archive['filename'];
        $newOriginalFilename = $archive['original_filename'];
        $newFilesize = $archive['filesize'];
        $newFiletype = $archive['filetype'];

        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['file'];
            if ($file['size'] > MAX_FILE_SIZE) {
                $errors[] = 'Ukuran file melebihi batas.';
            } elseif (!isAllowedFile($file['name'])) {
                $errors[] = 'Tipe file tidak diizinkan.';
            } else {
                $newFilename = generateUniqueFilename($file['name']);
                $destPath = UPLOAD_PATH . $newFilename;
                if (move_uploaded_file($file['tmp_name'], $destPath)) {
                    // delete old file
                    $oldPath = UPLOAD_PATH . $archive['filename'];
                    if (file_exists($oldPath)) unlink($oldPath);
                    $newOriginalFilename = $file['name'];
                    $newFilesize = $file['size'];
                    $newFiletype = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                } else {
                    $errors[] = 'Gagal menyimpan file baru.';
                }
            }
        }

        if (empty($errors)) {
            $stmt = $db->prepare("
                UPDATE archives SET 
                    judul=?, kategori=?, program_id=?, department_id=?, division_id=?,
                    deskripsi=?, is_public=?, filename=?, original_filename=?,
                    filesize=?, filetype=?
                WHERE id=?
            ");
            $stmt->execute([
                $judul, $kategori, $program_id, $department_id, $division_id,
                $deskripsi, $is_public, $newFilename, $newOriginalFilename,
                $newFilesize, $newFiletype, $id
            ]);
            setFlash('success', 'Arsip berhasil diperbarui!');
            redirect(APP_URL . '/archives/index.php');
        }
    }

    // re-populate from POST
    $archive = array_merge($archive, [
        'judul' => $judul, 'kategori' => $kategori, 'program_id' => $program_id,
        'department_id' => $department_id, 'division_id' => $division_id,
        'deskripsi' => $deskripsi, 'is_public' => $is_public
    ]);
}
?>

<div class="page-content">
    <div style="max-width:700px;margin:0 auto">
        <div class="breadcrumb" style="margin-bottom:20px" data-animate>
            <a href="<?= APP_URL ?>/archives/index.php">Arsip</a>
            <span>/</span>
            <span style="color:var(--text-secondary)">Edit</span>
        </div>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            ❌ <?= implode('<br>', array_map('sanitize', $errors)) ?>
        </div>
        <?php endif; ?>

        <div class="glass-card" style="padding:32px" data-animate>
            <h2 style="font-family:var(--font-display);font-size:22px;font-weight:700;margin-bottom:24px">Edit</h2>

            <form method="POST" enctype="multipart/form-data">
                <!-- Current file info -->
                <div style="padding:14px 16px;background:rgba(168,232,249,0.06);border:1px solid rgba(168,232,249,0.12);border-radius:var(--radius-md);margin-bottom:20px;display:flex;align-items:center;gap:12px">
                    <span style="font-size:24px"><?= getFileIcon($archive['filetype']) ?></span>
                    <div>
                        <div style="font-size:13px;font-weight:600;color:var(--text-primary)"><?= sanitize($archive['original_filename']) ?></div>
                        <div style="font-size:11px;color:var(--text-muted)"><?= formatFileSize($archive['filesize'] ?? 0) ?> · <?= strtoupper($archive['filetype']) ?></div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Ganti File <span style="color:var(--text-muted)">(opsional)</span></label>
                    <div class="file-upload-area">
                        <input type="file" name="file">
                        <span class="upload-icon" style="font-size:28px">🔄</span>
                        <div class="upload-text">Upload file baru untuk mengganti</div>
                        <div class="upload-hint">Biarkan kosong jika tidak ingin mengganti file</div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Judul Arsip *</label>
                        <input type="text" name="judul" class="form-control" value="<?= sanitize($archive['judul']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kategori *</label>
                        <select name="kategori" class="form-control" onchange="handleKategoriChange(this.value)">
                            <option value="Proker" <?= $archive['kategori'] == 'Proker' ? 'selected' : '' ?>>Program Kerja</option>
                            <option value="Non-Proker" <?= $archive['kategori'] == 'Non-Proker' ? 'selected' : '' ?>>Non-Proker</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" id="programGroup" style="<?= $archive['kategori'] == 'Proker' ? '' : 'display:none' ?>">
                    <label class="form-label">Program Kerja</label>
                    <select name="program_id" class="form-control">
                        <option value="">-- Tidak terkait program --</option>
                        <?php foreach ($programs as $prog): ?>
                        <option value="<?= $prog['id'] ?>" <?= $archive['program_id'] == $prog['id'] ? 'selected' : '' ?>>
                            <?= sanitize($prog['nama']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Dinas *</label>
                        <select name="department_id" class="form-control" onchange="filterByDept(this.value)">
                            <?php foreach ($departments as $d): ?>
                            <option value="<?= $d['id'] ?>" <?= $archive['department_id'] == $d['id'] ? 'selected' : '' ?>><?= sanitize($d['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Divisi</label>
                        <select name="division_id" class="form-control" id="division_id">
                            <option value="">-- Tidak spesifik --</option>
                            <?php foreach ($divisions as $dv): ?>
                            <option value="<?= $dv['id'] ?>" data-dept="<?= $dv['department_id'] ?>" 
                                <?= $archive['division_id'] == $dv['id'] ? 'selected' : '' ?>>
                                <?= sanitize($dv['nama']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="3"><?= sanitize($archive['deskripsi']) ?></textarea>
                </div>

                <div class="form-group">
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 16px;background:rgba(168,232,249,0.05);border:1px solid rgba(168,232,249,0.12);border-radius:var(--radius-md)">
                        <div>
                            <div style="font-size:14px;font-weight:600;color:var(--text-primary)">Publik</div>
                            <div style="font-size:12px;color:var(--text-muted)">Arsip dapat dilihat semua anggota</div>
                        </div>
                        <label class="toggle">
                            <input type="checkbox" name="is_public" value="1" <?= $archive['is_public'] ? 'checked' : '' ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>

                <div style="display:flex;gap:12px">
                    <a href="<?= APP_URL ?>/archives/index.php" class="btn btn-outline" style="flex:1;justify-content:center">Batal</a>
                    <button type="submit" class="btn btn-primary" style="flex:2;justify-content:center">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function handleKategoriChange(val) {
    document.getElementById('programGroup').style.display = val === 'Proker' ? 'block' : 'none';
}
// init
filterByDept(document.querySelector('select[name="department_id"]').value);
</script>

<?php require_once __DIR__ . '/../template/footer.php'; ?>
