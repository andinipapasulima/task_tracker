# 📚 Tracker Tugas Kuliah

Aplikasi web berbasis PHP & MySQL untuk mencatat, mengelola, dan melacak deadline tugas kuliah — dilengkapi sistem autentikasi, dark mode, dan notifikasi tugas harian.

---

## ✨ Fitur Utama

- **Autentikasi** — Register & login per akun, data tugas terisolasi per user
- **CRUD Tugas** — Tambah, edit, hapus, dan tandai tugas sebagai selesai
- **Countdown Deadline** — Indikator sisa hari, tugas terlewat, dan tugas hari ini
- **Notifikasi Harian** — Banner peringatan otomatis jika ada tugas jatuh tempo hari ini
- **Pencarian & Filter** — Cari berdasarkan mata kuliah/detail tugas, filter by status
- **Dark Mode** — Toggle tema terang/gelap, tersimpan di `localStorage`
- **Responsif** — Tampilan optimal di desktop maupun mobile

---

## 🗂️ Struktur File

```
tracker-tugas/
├── koneksi.php          # Konfigurasi koneksi database
├── index.php            # Halaman utama (daftar tugas)
├── tambah.php           # Form tambah tugas baru
├── edit.php             # Form edit tugas
├── hapus.php            # Proses hapus tugas
├── selesai.php          # Proses tandai tugas selesai
├── login.php            # Halaman login
├── register.php         # Halaman register
├── logout.php           # Proses logout & destroy session
├── proses_login.php     # (Opsional) handler login terpisah
├── style.css            # Semua styling (light & dark mode)
└── script.js            # Logika theme toggle
```

---

## 🛠️ Instalasi & Setup

### Prasyarat
- PHP >= 7.4
- MySQL / MariaDB
- Web server (XAMPP / Laragon / WAMP)

### Langkah

**1. Clone / copy project ke folder htdocs**
```bash
# Contoh untuk XAMPP
cp -r tracker-tugas/ C:/xampp/htdocs/
```

**2. Buat database di phpMyAdmin (atau MySQL CLI)**
```sql
CREATE DATABASE db_tugas_kuliah;
USE db_tugas_kuliah;

CREATE TABLE users (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    nama     VARCHAR(100)  NOT NULL,
    username VARCHAR(50)   NOT NULL UNIQUE,
    email    VARCHAR(100)  NOT NULL,
    password VARCHAR(255)  NOT NULL
);

CREATE TABLE tugas (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    user_id  INT           NOT NULL,
    matkul   VARCHAR(100)  NOT NULL,
    tugas    TEXT          NOT NULL,
    deadline DATE          NOT NULL,
    status   VARCHAR(20)   NOT NULL DEFAULT 'Belum',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

**3. Sesuaikan konfigurasi di `koneksi.php`**
```php
$host = "localhost";
$user = "root";
$pass = "";          // sesuaikan password MySQL kamu
$db   = "db_tugas_kuliah";
```

**4. Jalankan**
- Nyalakan Apache & MySQL di XAMPP
- Buka browser → `http://localhost/tracker-tugas/`
- Daftar akun baru lalu login

---

## 🔒 Catatan Keamanan

> File ini menggunakan `mysqli_real_escape_string` dan `password_hash` / `password_verify`. Untuk produksi, sangat disarankan migrasi ke **Prepared Statements** di seluruh file agar terhindar dari SQL Injection.

File yang sudah menggunakan Prepared Statements:
- ✅ `edit.php`

File yang perlu diupgrade ke depannya:
- ⚠️ `tambah.php`, `hapus.php`, `selesai.php`

---

## 👥 Tim Pengembang

| Nama | Role |
|------|------|
| Andini | Developer & Designer |

---

## 📄 Lisensi

Proyek ini dibuat untuk keperluan pembelajaran. Bebas digunakan dan dimodifikasi.
