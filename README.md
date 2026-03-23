# 💾 HMIF Archive — Sistem Arsip Digital
**Himpunan Mahasiswa Informatika (HMIF) Universitas Sriwijaya**

Sistem pengelolaan arsip digital ormawa berbasis web dengan desain glassmorphism, dibangun menggunakan PHP native + MySQL.

---

## 🎨 Tampilan & Fitur

### Design System
- **Glassmorphism** dengan backdrop-filter blur
- **Color palette HMIF**: Biru Langit `#A8E8F9`, Biru Teal `#00537A`, Biru Navy `#013C58`, Oranye `#F5A201`, Kuning `#FFBA42`, Kuning Pastel `#FFD35B`
- **Font**: Segoe UI
- **Animasi JS**: card tilt 3D, ripple effect, scroll reveal, floating particles, stat counter

### Halaman
| Halaman | Deskripsi |
|---|---|
| `login.php` | Login — logo di kiri, form di kanan, animated particles |
| `dashboard.php` | Ringkasan statistik, arsip terbaru, quick actions |
| `archives/index.php` | Daftar arsip, filter & search |
| `archives/upload.php` | Upload arsip baru dengan drag & drop |
| `archives/edit.php` | Edit arsip |
| `archives/download.php` | Download file dengan update counter |
| `programs/index.php` | Daftar program kerja |
| `programs/create.php` | Tambah program kerja |
| `programs/edit.php` | Edit program kerja |
| `departments/index.php` | Daftar & statistik dinas |
| `departments/create.php` | Tambah dinas |
| `departments/edit.php` | Edit dinas |
| `divisions/index.php` | Manajemen divisi |
| `divisions/create.php` | Tambah divisi |
| `divisions/edit.php` | Edit divisi |
| `users/index.php` | Manajemen pengguna |
| `users/create.php` | Tambah pengguna |
| `users/edit.php` | Edit pengguna |
| `profile/index.php` | Edit profil + foto + ganti password |

---

## 🔐 Sistem Privilege

| Role | Akses |
|---|---|
| `ketua`, `waketua`, `sekum`, `bendum` | **Admin penuh** — semua fitur, semua dinas/divisi |
| `kadin`, `wakadin` | Mengelola arsip **dinas mereka & divisi di bawahnya** |
| `kadiv` | Mengelola arsip **divisi mereka saja** |
| `staf` | **Lihat & download** saja |

---

## ⚙️ Instalasi

### Kebutuhan
- PHP 7.4+ atau 8.x
- MySQL 5.7+ / MariaDB 10.4+
- Apache + mod_rewrite (XAMPP/Laragon/WAMP)
- Ekstensi PHP: `pdo`, `pdo_mysql`, `gd`, `fileinfo`

### Langkah Instalasi

**1. Clone / extract ke folder web server**
```
htdocs/hmif-archive/   (XAMPP)
www/hmif-archive/      (Laragon)
```

**2. Import database**
```bash
mysql -u root -p < database.sql
```
Atau buka phpMyAdmin → Import → pilih `database.sql`

**3. Konfigurasi**
Edit `src/config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');      // sesuaikan
define('DB_PASS', '');          // sesuaikan
define('DB_NAME', 'hmif_archive');
```

Edit `src/config/app.php`:
```php
define('APP_URL', 'http://localhost/hmif-archive');  // sesuaikan
```

**4. Jalankan Setup**
Buka browser: `http://localhost/hmif-archive/setup.php`

Setup akan otomatis:
- Membuat folder `public/uploads/` dan `public/uploads/profiles/`
- Menambahkan kolom `foto_profil` jika belum ada
- Memverifikasi koneksi database

**5. Hapus setup.php**
```bash
rm setup.php
```

**6. Login**
Buka: `http://localhost/hmif-archive/`

---

## 👤 Akun Default

Semua akun menggunakan password: **`password`**

| Email | Role | Akses |
|---|---|---|
| `kahim@hmif.com` | Ketua HMIF | Admin penuh |
| `wakahim@hmif.com` | Wakil Ketua | Admin penuh |
| `sekum1@hmif.com` | Sekretaris Umum | Admin penuh |
| `bendum1@hmif.com` | Bendahara Umum | Admin penuh |
| `kadin.adm@hmif.com` | Kadin Administrasi | Dinas Administrasi |
| `kadin.kominfo@hmif.com` | Kadin Kominfo | Dinas Kominfo |
| `kadiv.humas@hmif.com` | Kadiv Humas | Divisi Humas |
| `staf.psdm1@hmif.com` | Staf PSDM | Lihat & download |

---

## 📁 Struktur Folder

```
hmif-archive/
├── 📄 index.php              — Redirect ke login/dashboard
├── 📄 login.php              — Halaman login
├── 📄 logout.php             — Handler logout
├── 📄 dashboard.php          — Dashboard utama
├── 📄 setup.php              — Script instalasi (hapus setelah setup)
├── 📄 database.sql           — Skema + data dummy
├── 📄 database_migration.sql — Migration tambahan
├── 📄 .htaccess
│
├── 📂 archives/
│   ├── index.php             — List arsip
│   ├── upload.php            — Upload arsip
│   ├── edit.php              — Edit arsip
│   ├── delete.php            — Hapus arsip
│   └── download.php          — Download file
│
├── 📂 programs/
│   ├── index.php             — List proker
│   ├── create.php            — Tambah proker
│   ├── edit.php              — Edit proker
│   └── delete.php            — Hapus proker
│
├── 📂 departments/
│   ├── index.php, create.php, edit.php, delete.php
│
├── 📂 divisions/
│   ├── index.php, create.php, edit.php, delete.php
│
├── 📂 users/
│   ├── index.php, create.php, edit.php, delete.php
│
├── 📂 profile/
│   └── index.php             — Edit profil + foto + password
│
├── 📂 src/
│   ├── 📂 config/
│   │   ├── database.php      — Konfigurasi database
│   │   └── app.php           — Konstanta aplikasi
│   ├── 📂 middleware/
│   │   └── auth.php          — Autentikasi & otorisasi
│   └── 📂 helpers/
│       └── functions.php     — Fungsi utilitas
│
├── 📂 views/
│   └── header.php        — Sidebar + topbar layout
│   └── footer.php        — Footer + modal global
│
└── 📂 public/
    ├── 📂 assets/
    │   ├── 📂 css/
    │   │   ├── main.css       — Stylesheet utama (glassmorphism)
    │   │   └── login.css      — Stylesheet halaman login
    │   └── 📂 js/
    │       ├── main.js        — Animasi & interaksi utama
    │       └── login.js       — Animasi halaman login
    └── 📂 uploads/
        ├── .htaccess          — Keamanan folder upload
        └── 📂 profiles/       — Foto profil pengguna
```

---

## 🎯 Kategori Arsip

### Proker (Program Kerja)
Arsip yang terkait langsung dengan program kerja tertentu. Contoh: proposal, notulen, dokumentasi, laporan akhir proker.

### Non-Proker (Arsip Umum)
Arsip yang bersifat umum dan tidak terkait proker tertentu. Contoh: surat masuk/keluar, surat keputusan, dokumentasi kegiatan insidental.

---

## 🔧 Format File yang Didukung

| Kategori | Format |
|---|---|
| Dokumen | PDF, DOC, DOCX |
| Spreadsheet | XLS, XLSX |
| Presentasi | PPT, PPTX |
| Gambar | JPG, JPEG, PNG |
| Arsip | ZIP, RAR |

**Maksimal ukuran file: 50 MB**

---

## 🚀 Animasi & Interaksi

- **Card Tilt 3D** — hover pada stat card mengikuti arah kursor
- **Ripple Effect** — klik pada card menampilkan efek gelombang
- **Scroll Reveal** — elemen muncul bertahap saat di-scroll
- **Stat Counter** — angka statistik animasi counting up
- **Floating Particles** — partikel mengambang di halaman login
- **Drag & Drop Upload** — seret file langsung ke area upload
- **Toast Notification** — notifikasi pop-up di pojok kanan bawah
- **Animated Stars** — bintang berkedip di background
- **Glassmorphism Hover** — card terangkat saat di-hover
