# 📚 Tracker Tugas Kuliah

> Aplikasi web berbasis **PHP & MySQL** untuk mencatat, mengelola, dan melacak deadline tugas kuliah — dilengkapi autentikasi, dark mode, dashboard statistik, panel admin, dan notifikasi tugas harian.

<div align="center">

![PHP](https://img.shields.io/badge/PHP-%3E%3D7.4-8892BF?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/Lisensi-Pembelajaran-green?style=flat-square)
![Status](https://img.shields.io/badge/Status-Aktif-brightgreen?style=flat-square)

</div>

---

## 📋 Daftar Isi

- [Fitur Utama](#-fitur-utama)
- [Tampilan Aplikasi](#-tampilan-aplikasi)
- [Struktur File](#-struktur-file)
- [Skema Database](#-skema-database)
- [Instalasi & Setup](#-instalasi--setup)
- [Cara Penggunaan](#-cara-penggunaan)
- [Catatan Keamanan](#-catatan-keamanan)
- [Pengembangan Lanjutan](#-pengembangan-lanjutan)
- [Tim Pengembang](#-tim-pengembang)

---

## ✨ Fitur Utama

### 👤 Autentikasi & Profil
- Register dan login per akun — data tugas terisolasi per user
- Edit profil (nama, email, foto profil, ganti password)
- Indikator kekuatan password real-time saat register
- Panel admin untuk manajemen user (reset password, hapus user, statistik global)

### 📝 Manajemen Tugas (CRUD)
- Tambah tugas dengan detail lengkap: mata kuliah, deskripsi, deadline, prioritas, kategori, dan catatan
- Edit dan hapus tugas
- Tandai tugas sebagai selesai
- Dukungan **tugas berulang** (mingguan / bulanan) via cron job

### ⏰ Countdown & Status Deadline
| Status | Keterangan |
|--------|-----------|
| 🟢 Tugas tuntas | Tugas sudah diselesaikan |
| 🔴 Terlewat | Deadline sudah berlalu, tugas belum selesai |
| ⚡ Hari ini | Deadline jatuh hari ini |
| 🕒 N hari lagi | Sisa waktu pengerjaan |

### 🔍 Pencarian, Filter & Sorting
- Cari tugas berdasarkan nama mata kuliah atau detail tugas
- Filter berdasarkan status (Semua / Belum / Selesai)
- Filter berdasarkan deadline (Minggu ini / Bulan ini / Terlewat)
- Filter berdasarkan prioritas (Tinggi / Sedang / Rendah)
- Sorting: mata kuliah (A–Z / Z–A), deadline (terdekat / terjauh)
- **Pagination** — 10 tugas per halaman

### 📊 Dashboard Statistik
- Ringkasan total tugas, tugas selesai, belum selesai, dan persentase progress
- Penanda tugas minggu ini dan tugas terlewat
- Grafik doughnut, bar chart, dan pie chart (via Chart.js):
  - Progress keseluruhan
  - Sebaran prioritas
  - Sebaran kategori

### 🌙 Tampilan & UX
- **Dark Mode** — toggle tema terang/gelap, preferensi disimpan di `localStorage`
- **Notifikasi banner** — peringatan otomatis jika ada tugas jatuh tempo hari ini
- **Push Notification** — notifikasi browser (jika izin diberikan)
- **Responsif** — tampilan optimal di desktop dan mobile
- Sapaan dinamis berdasarkan waktu (Selamat Pagi/Siang/Sore/Malam)
- Progress bar keseluruhan tugas di halaman utama

### 📤 Export
- Export semua tugas ke **file CSV** lengkap dengan informasi progress, status, dan sisa hari

---

## 🗂️ Struktur File

```
tracker-tugas/
│
├── 📄 koneksi.php          # Konfigurasi koneksi database
│
├── 📄 index.php            # Halaman utama — daftar tugas, filter, pencarian, pagination
├── 📄 tambah.php           # Form tambah tugas baru
├── 📄 edit.php             # Form edit tugas (menggunakan Prepared Statements)
├── 📄 hapus.php            # Proses hapus tugas
├── 📄 selesai.php          # Proses tandai tugas selesai
│
├── 📄 dashboard.php        # Halaman statistik & grafik (Chart.js)
├── 📄 admin.php            # Panel admin — manajemen user & statistik global
├── 📄 export.php           # Export data tugas ke CSV
│
├── 📄 login.php            # Halaman login
├── 📄 register.php         # Halaman register + indikator kekuatan password
├── 📄 proses_login.php     # Handler proses autentikasi login
├── 📄 logout.php           # Proses logout & destroy session
├── 📄 edit_profile.php     # Form edit profil & upload foto
│
├── 📄 cron.php             # Cron job: tugas berulang & reminder deadline H-1
│
├── 🎨 style.css            # Semua styling — light mode, dark mode, responsif
└── ⚡ script.js            # Logika theme toggle & password strength checker
```

---

## 🗃️ Skema Database

### Tabel `users`
```sql
CREATE TABLE users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    nama          VARCHAR(100)  NOT NULL,
    username      VARCHAR(50)   NOT NULL UNIQUE,
    email         VARCHAR(100)  NOT NULL,
    password      VARCHAR(255)  NOT NULL,             -- bcrypt via password_hash()
    role          VARCHAR(20)   DEFAULT 'user',       -- 'user' atau 'admin'
    foto_profil   VARCHAR(255)  DEFAULT NULL,
    created_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
);
```

### Tabel `tugas`
```sql
CREATE TABLE tugas (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    user_id        INT           NOT NULL,
    matkul         VARCHAR(100)  NOT NULL,
    tugas          TEXT          NOT NULL,
    deadline       DATE          NOT NULL,
    status         VARCHAR(20)   NOT NULL DEFAULT 'Belum',
    prioritas      VARCHAR(20)   DEFAULT 'Sedang',     -- Tinggi / Sedang / Rendah
    kategori       VARCHAR(50)   DEFAULT 'Umum',       -- UTS / UAS / Tugas Harian / Kelompok / Praktikum / Umum
    catatan        TEXT          DEFAULT NULL,
    is_recurring   TINYINT(1)    DEFAULT 0,
    recurring_type VARCHAR(20)   DEFAULT NULL,         -- 'mingguan' atau 'bulanan'
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### Tabel `activity_logs` *(opsional, untuk audit trail)*
```sql
CREATE TABLE activity_logs (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT          NOT NULL,
    action     TEXT         NOT NULL,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);
```

---

## 🛠️ Instalasi & Setup

### Prasyarat
- PHP >= 7.4
- MySQL / MariaDB
- Web server lokal: **XAMPP**, **Laragon**, atau **WAMP**

---

### Langkah 1 — Clone / Copy Project

```bash
# Salin folder ke direktori htdocs (contoh XAMPP di Windows)
cp -r tracker-tugas/ C:/xampp/htdocs/

# Atau di Linux/Mac
cp -r tracker-tugas/ /opt/lampp/htdocs/
```

---

### Langkah 2 — Buat Database

Buka **phpMyAdmin** atau jalankan perintah berikut di MySQL CLI:

```sql
CREATE DATABASE db_tugas_kuliah CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_tugas_kuliah;

-- Tabel users
CREATE TABLE users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    nama          VARCHAR(100)  NOT NULL,
    username      VARCHAR(50)   NOT NULL UNIQUE,
    email         VARCHAR(100)  NOT NULL,
    password      VARCHAR(255)  NOT NULL,
    role          VARCHAR(20)   DEFAULT 'user',
    foto_profil   VARCHAR(255)  DEFAULT NULL,
    created_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
);

-- Tabel tugas
CREATE TABLE tugas (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    user_id        INT           NOT NULL,
    matkul         VARCHAR(100)  NOT NULL,
    tugas          TEXT          NOT NULL,
    deadline       DATE          NOT NULL,
    status         VARCHAR(20)   NOT NULL DEFAULT 'Belum',
    prioritas      VARCHAR(20)   DEFAULT 'Sedang',
    kategori       VARCHAR(50)   DEFAULT 'Umum',
    catatan        TEXT          DEFAULT NULL,
    is_recurring   TINYINT(1)    DEFAULT 0,
    recurring_type VARCHAR(20)   DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel activity_logs (opsional)
CREATE TABLE activity_logs (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT          NOT NULL,
    action     TEXT         NOT NULL,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);
```

> 💡 **Tips:** Jika ingin akun admin, setelah register ubah field `role` user tersebut menjadi `'admin'` langsung di phpMyAdmin.

---

### Langkah 3 — Konfigurasi Database

Edit file `koneksi.php` sesuai konfigurasi MySQL kamu:

```php
<?php
$host = "localhost";
$user = "root";
$pass = "";          // Isi password MySQL kamu jika ada
$db   = "db_tugas_kuliah";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
```

---

### Langkah 4 — Jalankan Aplikasi

1. Nyalakan **Apache** dan **MySQL** dari panel XAMPP/Laragon
2. Buka browser dan akses:
   ```
   http://localhost/tracker-tugas/
   ```
3. Klik **"Daftar sekarang"** untuk membuat akun baru
4. Login dan mulai catat tugasmu!

---

## 📖 Cara Penggunaan

### Menambah Tugas
1. Dari halaman utama, klik tombol **＋ Tambah Tugas Baru**
2. Isi formulir: mata kuliah, detail tugas, deadline, prioritas, kategori, dan catatan (opsional)
3. Aktifkan opsi **"Tugas berulang"** jika tugas perlu dibuat ulang setiap minggu/bulan
4. Klik **Simpan ke Daftar**

### Mengelola Tugas
| Aksi | Cara |
|------|------|
| ✅ Tandai selesai | Klik ikon ✅ di kolom Aksi |
| ✏️ Edit tugas | Klik ikon ✏️ di kolom Aksi |
| 🗑️ Hapus tugas | Klik ikon 🗑️ dan konfirmasi |

### Menggunakan Filter
- Gunakan tombol **Semua / Belum / Selesai** untuk filter cepat berdasarkan status
- Gunakan dropdown **Filter & Sortir** untuk filter deadline, prioritas, dan sorting
- Klik **↺ Reset** untuk menghapus semua filter aktif

### Export Data
Klik tombol **📎 Export** di halaman utama untuk mengunduh seluruh daftar tugas dalam format **CSV**.

### Dark Mode
Klik tombol **🌙 Mode Gelap** / **☀️ Mode Terang** di pojok kanan atas. Preferensi tema tersimpan otomatis di browser.

---

## 🔒 Catatan Keamanan

> ⚠️ Aplikasi ini dibuat untuk keperluan **pembelajaran**. Beberapa praktik di bawah ini perlu diperhatikan sebelum digunakan di lingkungan produksi.

### Status Keamanan Saat Ini

| File | Status | Keterangan |
|------|--------|-----------|
| `edit.php` | ✅ Aman | Sudah menggunakan Prepared Statements |
| `proses_login.php` | ⚠️ Perlu upgrade | Masih menggunakan `mysqli_real_escape_string` |
| `tambah.php` | ⚠️ Perlu upgrade | Masih menggunakan `mysqli_real_escape_string` |
| `hapus.php` | ⚠️ Perlu upgrade | Query langsung dengan casting `(int)` |
| `selesai.php` | ⚠️ Perlu upgrade | Query langsung dengan casting `(int)` |
| `register.php` | ✅ Aman | Password di-hash dengan `password_hash()` |

### Rekomendasi untuk Produksi

1. **Migrasikan semua query ke Prepared Statements** untuk mencegah SQL Injection
2. **Tambahkan CSRF token** pada semua form POST
3. **Batasi ukuran upload foto** dan validasi MIME type yang lebih ketat di `edit_profile.php`
4. **Gunakan HTTPS** — jangan jalankan dengan koneksi HTTP di produksi
5. **Simpan kredensial database** di file `.env` atau di luar direktori publik, bukan hardcode di `koneksi.php`
6. **Tambahkan rate limiting** pada endpoint login untuk mencegah brute force

---

## 🚀 Pengembangan Lanjutan

Beberapa ide fitur yang bisa dikembangkan ke depannya:

- [ ] Implementasi email reminder (H-1 deadline) menggunakan PHPMailer
- [ ] Notifikasi real-time dengan WebSocket atau Server-Sent Events
- [ ] Lampiran file pada tugas (upload PDF, gambar, dsb.)
- [ ] Kolaborasi tugas kelompok antar akun
- [ ] Integrasi Google Calendar / iCal export
- [ ] PWA (Progressive Web App) agar bisa diinstall di HP
- [ ] Migrasi ke framework (Laravel / CodeIgniter) untuk struktur yang lebih terorganisir
- [ ] Unit testing dengan PHPUnit

---

## 👥 Tim Pengembang

| Nama | Role |
|------|------|
| **Andini** | Full-stack Developer & UI/UX Designer |

---

## 📄 Lisensi

Proyek ini dibuat untuk keperluan **pembelajaran**. Bebas digunakan, dimodifikasi, dan didistribusikan untuk tujuan non-komersial dengan menyertakan atribusi.

---

<div align="center">
  <sub>Dibuat dengan ☕ dan semangat belajar · 2024</sub>
</div>
