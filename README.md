# LaundryHub

Proyek ini adalah Tugas Akhir untuk mata kuliah Interaksi Manusia dan Komputer
Program Studi Informatika, Fakultas Ilmu Komputer, Universitas Esa Unggul

## Panduan Instalasi

1. **Unduh atau Clone Repository Ini**
    - Menggunakan Git:
      `git clone https://github.com/yourusername/laundryhub.git`
    - Atau unduh sebagai ZIP dan ekstrak.

2. **Buat Database bernama `laundryhub` (MariaDB/MySQL)**
    - Menggunakan phpMyAdmin:
        - Buka phpMyAdmin
        - Klik "New" dan buat database dengan nama `laundryhub`
    - Menggunakan terminal/command line:
      ```sql
      CREATE DATABASE laundryhub;
      ```

3. **Impor Struktur Database**
    - Impor file `laundryhub.sql` ke dalam database `laundryhub` melalui phpMyAdmin atau command line.

4. **Konfigurasi Koneksi Database**
    - Edit file `connect-db.php` dan atur username, password, serta host database jika diperlukan.

5. **Jalankan Proyek**
    - Tempatkan folder proyek di direktori web server (misal, `htdocs` untuk XAMPP).
    - Jalankan Apache dan MySQL dari XAMPP.
    - Akses proyek melalui `http://localhost/laundryhub/` di browser.

## Login Admin

- **Email:** `admin`
- **Password:** `admin`

## Fitur Sistem Rating

### Untuk Pelanggan:
- Memberikan rating 1-10 bintang untuk layanan yang sudah selesai
- Menulis komentar/ulasan
- Melihat rating yang sudah diberikan

### Untuk Mitra:
- Melihat rating dan ulasan dari pelanggan
- Melaporkan ulasan yang tidak pantas ke admin
- Melihat status laporan yang sudah dikirim

### Untuk Admin:
- Melihat laporan ulasan dari mitra
- Menghapus ulasan yang tidak pantas
- Mengabaikan laporan yang tidak valid
- Mengelola moderasi konten

## Pemecahan Masalah

- Jika mendapatkan error koneksi database, periksa kredensial di `connect-db.php`.
- Pastikan layanan MariaDB/MySQL sudah berjalan.

---