-- ================================================
-- HMIF Archive - Database Schema (Versi Lengkap)
-- Himpunan Mahasiswa Informatika UNSRI
-- ================================================

CREATE DATABASE IF NOT EXISTS hmif_archive;
USE hmif_archive;

-- Drop tables if exist (reset bersih)
DROP TABLE IF EXISTS archives;
DROP TABLE IF EXISTS programs;
DROP TABLE IF EXISTS divisions;
DROP TABLE IF EXISTS departments;
DROP TABLE IF EXISTS users;

-- ================================================
-- Tabel Users
-- ================================================
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    foto_profil VARCHAR(255) NULL,
    role ENUM('ketua','waketua','sekum','bendum','kadin','wakadin','kadiv','staf') NOT NULL,
    department_id INT NULL,
    division_id INT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ================================================
-- Tabel Departments (Dinas)
-- ================================================
CREATE TABLE departments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(100) NOT NULL,
    kode VARCHAR(10) UNIQUE NOT NULL,
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ================================================
-- Tabel Divisions (Divisi)
-- ================================================
CREATE TABLE divisions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(100) NOT NULL,
    department_id INT NOT NULL,
    kode VARCHAR(10) UNIQUE NOT NULL,
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE
);

-- ================================================
-- Tabel Programs (Proker)
-- ================================================
CREATE TABLE programs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(200) NOT NULL,
    department_id INT NOT NULL,
    deskripsi TEXT,
    status ENUM('aktif','selesai','dibatalkan') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE
);

-- ================================================
-- Tabel Archives
-- ================================================
CREATE TABLE archives (
    id INT PRIMARY KEY AUTO_INCREMENT,
    judul VARCHAR(255) NOT NULL,
    kategori ENUM('Proker','Non-Proker') NOT NULL,
    program_id INT NULL,
    department_id INT NOT NULL,
    division_id INT NULL,
    filename VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255),
    filesize INT,
    filetype VARCHAR(50),
    uploaded_by INT NOT NULL,
    deskripsi TEXT,
    download_count INT DEFAULT 0,
    is_public TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE SET NULL,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE,
    FOREIGN KEY (division_id) REFERENCES divisions(id) ON DELETE SET NULL,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_dept_div (department_id, division_id),
    INDEX idx_category (kategori),
    INDEX idx_uploaded_by (uploaded_by)
);

-- ================================================
-- Data: Departments (7 Dinas)
-- ================================================
INSERT INTO departments (nama, kode, deskripsi) VALUES
('PSDM',          'PSDM',     'Pengembangan Sumber Daya Mahasiswa'),
('Kastrad',       'KASTRAD',  'Kastrad'),
('Kominfo',       'KOMINFO',  'Komunikasi dan Informasi'),
('Kewirausahaan', 'KWU',      'Pengembangan Kewirausahaan'),
('Administrasi',  'ADM',      'Administrasi dan Keuangan'),
('PMB',           'PMB',      'Pengembangan Minat Bakat'),
('Akademik',      'AKADEMIK', 'Pengembangan Akademik');

-- ================================================
-- Data: Divisions (8 Divisi)
-- ================================================
INSERT INTO divisions (nama, department_id, kode, deskripsi) VALUES
('Adkam',    2, 'ADK',      'Advokasi Kemahasiswaan'),
('Polpro',   2, 'POL',      'Politik dan Propaganda'),
('Mulmed',   3, 'MULMED',   'Multimedia'),
('Humas',    3, 'HUMAS',    'Hubungan Masyarakat'),
('Seni',     6, 'SENI',     'Seni dan Budaya'),
('Olahraga', 6, 'OLAHRAGA', 'Pengembangan Olahraga'),
('PTI',      7, 'PTI',      'Pengembangan Teknologi Informasi'),
('PIP',      7, 'PIP',      'Pengembangan Ilmu Pengetahuan');

-- ================================================
-- Data: Programs (4 Proker contoh)
-- ================================================
INSERT INTO programs (nama, department_id, deskripsi, status) VALUES
('Open Recruitment Staf HMIF 2026',       1, 'Open Recruitment Staf HMIF periode 2026', 'selesai'),
('Raker x IPK x Makrab HMIF 2026',        1, 'Raker, IPK, dan Makrab Awal Kepengurusan', 'selesai'),
('Bincang Hangat bersama Kepala Dinas',   2, 'Diskusi rutin dengan seluruh Kadep', 'aktif'),
('Webinar Kerja Praktik',                  5, 'Panduan KP dan magang untuk mahasiswa', 'aktif');

-- ================================================
-- Data: Users
-- Password: pusatsemesta
-- Hash: password_hash('pusatsemesta', PASSWORD_DEFAULT)
-- ================================================

-- Admin level (4 orang: ketua, wakil, 2 sekum, 2 bendum)
INSERT INTO users (email, password, nama_lengkap, role, department_id, division_id, is_active) VALUES
('kahim@hmif.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Muhammad Soni Juliansyah', 'ketua',   NULL, NULL, 1),
('wakahim@hmif.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Muhammad Fajri Ramadhan',  'waketua', NULL, NULL, 1),
('sekum1@hmif.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Risna Azzahra',            'sekum',   NULL, NULL, 1),
('sekum2@hmif.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dea Amalia Rombon',        'sekum',   NULL, NULL, 1),
('bendum1@hmif.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Farisya Satya Utami',      'bendum',  NULL, NULL, 1),
('bendum2@hmif.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ashila Wardhani',          'bendum',  NULL, NULL, 1),

-- Kadin & Wakadin
('kadin.adm@hmif.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kadin Administrasi', 'kadin',   5, NULL, 1),
('wakadin.psdm@hmif.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Wakadin PSDM',       'wakadin', 1, NULL, 1),
('kadin.kominfo@hmif.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kadin Kominfo',      'kadin',   3, NULL, 1),

-- Kadiv
('kadiv.pip@hmif.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kadiv PIP',   'kadiv', 7, 8, 1),
('kadiv.humas@hmif.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kadiv Humas', 'kadiv', 3, 4, 1),
('kadiv.seni@hmif.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kadiv Seni',  'kadiv', 6, 5, 1),

-- Staf
('staf.psdm1@hmif.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Staf PSDM 1',    'staf', 1, NULL, 1),
('staf.kominfo@hmif.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Staf Kominfo',   'staf', 3, 3,    1),
('staf.akademik@hmif.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Staf Akademik',  'staf', 7, NULL, 1);

-- ================================================
-- Indexes tambahan untuk performa
-- ================================================
CREATE INDEX idx_users_role_dept ON users(role, department_id);
CREATE INDEX idx_archives_created ON archives(created_at);
CREATE INDEX idx_archives_dept ON archives(department_id);

-- ================================================
-- Summary
-- ================================================
SELECT
    (SELECT COUNT(*) FROM users)       AS total_users,
    (SELECT COUNT(*) FROM departments) AS total_departments,
    (SELECT COUNT(*) FROM divisions)   AS total_divisions,
    (SELECT COUNT(*) FROM programs)    AS total_programs,
    (SELECT COUNT(*) FROM archives)    AS total_archives;
