<?php

// koneksi ke db
session_start();
include 'connect-db.php';
include 'functions/functions.php';

cekLogin();

// ketika tombol registrasi di klik
if (isset($_POST["registrasi"])) {

    // fungsi registrasi
    function registrasi($data)
    {
        global $connect;

        // mengambil data & membersihkan dari tag html
        $nama = htmlspecialchars($data["nama"]);
        $email = htmlspecialchars($data["email"]);
        $noTelp = htmlspecialchars($data["noTelp"]);
        $alamat = htmlspecialchars($data["alamat"]);
        $password = htmlspecialchars($data["password"]);
        $password2 = htmlspecialchars($data["password2"]);

        // validasi dasar
        if (empty($nama) || empty($email) || empty($noTelp) || empty($alamat) || empty($password)) {
            echo "<script>Swal.fire('Pendaftaran Gagal','Semua kolom wajib diisi','error');</script>";
            return false;
        }

        // cek apakah email sudah ada
        $result = mysqli_query($connect, "SELECT email FROM pelanggan WHERE email = '$email'");
        if (mysqli_fetch_assoc($result)) {
            echo "<script>Swal.fire('Pendaftaran Gagal','Email sudah terdaftar','error');</script>";
            return false;
        }

        // cek konfirmasi password
        if ($password !== $password2) {
            echo "<script>Swal.fire('Pendaftaran Gagal','Konfirmasi password tidak sama','error');</script>";
            return false;
        }

        // masukkan data user ke db tanpa enkripsi
        $query = "INSERT INTO pelanggan (nama, email, telp, alamat, foto, password) 
                  VALUES ('$nama', '$email', '$noTelp', '$alamat', 'default.png', '$password')";

        mysqli_query($connect, $query);

        return mysqli_affected_rows($connect);
    }

    if (registrasi($_POST) > 0) {
        // Ambil data user yang baru daftar untuk langsung login
        $email = $_POST["email"];
        $query = mysqli_query($connect, "SELECT * FROM pelanggan WHERE email = '$email'");
        $pelanggan = mysqli_fetch_assoc($query);
        $_SESSION["pelanggan"] = $pelanggan["id_pelanggan"];
        $_SESSION["login-pelanggan"] = true;
        echo "
            <script>
                Swal.fire('Pendaftaran Berhasil','Selamat Bergabung Dengan LaundryHub','success').then(function() {
                    window.location = 'index.php';
                });
            </script>
        ";
    } else {
        // Pesan error dari dalam fungsi registrasi akan muncul
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "headtags.html"; ?>
    <title>Registrasi Pelanggan</title>
</head>
<body>

<?php include 'header.php'; ?>
<main class="main-content">
    <h3 class="header light center">Registrasi Pelanggan</h3>

    <div class="row">
        <div class="col s6 offset-s3">
            <form action="" method="POST">
                <div class="input-field inline">
                    <ul>
                        <li>
                            <label for="nama">Nama Lengkap</label>
                            <input type="text" id="nama" placeholder="Nama Lengkap Anda" name="nama" required>
                        </li>
                        <li>
                            <label for="email">Email</label>
                            <input type="email" id="email" placeholder="contoh@email.com" name="email" required>
                        </li>
                        <li>
                            <label for="telp">No. Telepon</label>
                            <input type="tel" id="telp" placeholder="08123456789" name="noTelp" required>
                        </li>
                        <li>
                            <label for="alamat">Alamat Lengkap</label>
                            <textarea id="alamat" class="materialize-textarea" placeholder="Jl. Contoh No. 123, Kelurahan, Kecamatan, Kota" name="alamat" required></textarea>
                        </li>
                        <li>
                            <label for="password">Password</label>
                            <input type="password" id="password" placeholder="Password" name="password" required>
                        </li>
                        <li>
                            <label for="repassword">Konfirmasi Password</label>
                            <input type="password" id="repassword" placeholder="Ketik ulang password Anda" name="password2" required>
                        </li>
                        <li>
                            <div class="center">
                                <button class="btn-large blue darken-3" type="submit" name="registrasi">Daftar</button>
                            </div>
                        </li>
                    </ul>
                </div>
            </form>
            <div class="center">
                Ingin menjadi mitra kami?<br/>
                <a href="registrasi-mitra.php">Daftar sebagai Mitra sekarang!</a>
            </div>
        </div>
    </div>
</main>

<?php include "footer.php"; ?>
</body>
</html>