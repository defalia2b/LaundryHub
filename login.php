<?php
session_start();
include 'connect-db.php';
include 'functions/functions.php';

// Jika sudah login, langsung alihkan ke halaman utama
cekLogin();

if (isset($_POST["login"])) {
    // Pastikan jenis akun telah dipilih
    if (empty($_POST["akun"])) {
        echo "<script>Swal.fire('Gagal Login','Pilih jenis akun terlebih dahulu (Admin/Mitra/Pelanggan)','warning');</script>";
    } else {
        $jenis_akun = $_POST["akun"];
        $email_or_user = htmlspecialchars($_POST["email"]);
        $password = htmlspecialchars($_POST["password"]);

        if ($jenis_akun == 'mitra') {
            $result = mysqli_query($connect, "SELECT * FROM mitra WHERE email = '$email_or_user'");

            if (mysqli_num_rows($result) === 1) {
                $row = mysqli_fetch_assoc($result);
                // PERBAIKAN: Membandingkan password dengan variabel $row yang benar
                if ($password === $row["password"]) {
                    $_SESSION["mitra"] = $row["id_mitra"];
                    $_SESSION["login-mitra"] = true;
                    header("Location: status.php"); // Pengalihan halaman yang lebih baik
                    exit;
                } else {
                    echo "<script>Swal.fire('Gagal Login','Password yang Anda masukkan salah','error');</script>";
                }
            } else {
                echo "<script>Swal.fire('Gagal Login','Email belum terdaftar sebagai Mitra','error');</script>";
            }

        } else if ($jenis_akun == 'pelanggan') {
            $result = mysqli_query($connect, "SELECT * FROM pelanggan WHERE email = '$email_or_user'");

            if (mysqli_num_rows($result) === 1) {
                $row = mysqli_fetch_assoc($result);
                if ($password === $row["password"]) {
                    $_SESSION["pelanggan"] = $row["id_pelanggan"];
                    $_SESSION["login-pelanggan"] = true;
                    header("Location: index.php");
                    exit;
                } else {
                    echo "<script>Swal.fire('Gagal Login','Password yang Anda masukkan salah','error');</script>";
                }
            } else {
                echo "<script>Swal.fire('Gagal Login','Email belum terdaftar sebagai Pelanggan','error');</script>";
            }

        } else if ($jenis_akun == 'admin') {
            $result = mysqli_query($connect, "SELECT * FROM admin WHERE username = '$email_or_user'");

            if (mysqli_num_rows($result) === 1) {
                $row = mysqli_fetch_assoc($result);
                if ($password === $row["password"]) {
                    $_SESSION["admin"] = $row["id_admin"];
                    $_SESSION["login-admin"] = true;
                    header("Location: admin.php");
                    exit;
                } else {
                    echo "<script>Swal.fire('Gagal Login','Password yang Anda masukkan salah','error');</script>";
                }
            } else {
                echo "<script>Swal.fire('Gagal Login','Username tidak terdaftar sebagai Admin','error');</script>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "headtags.html"; ?>
    <title>Halaman Login - LaundryHub</title>
</head>
<body>
<?php include 'header.php'; ?>
<main class="main-content">
    <div class="container center">
        <h3 class="header light center">Halaman Login</h3>
        <form action="" method="post">
            <div class="input-field inline">
                <ul>
                    <li>
                        <label><input name="akun" value="admin" type="radio"/><span>Admin</span> </label>
                        <label><input name="akun" value="mitra" type="radio" checked/><span>Mitra</span> </label>
                        <label><input name="akun" value="pelanggan" type="radio"/><span>Pelanggan</span></label>
                    </li>
                    <li>
                        <label for="email">Username / Email</label>
                        <input type="text" id="email" name="email" placeholder="Username untuk admin, Email untuk lainnya" required>
                    </li>
                    <li>
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Password" required>
                    </li>
                    <br>
                    <li>
                        <div class="center">
                            <button class="waves-effect blue darken-2 btn" type="submit" name="login">Login</button>
                        </div>
                    </li>
                </ul>
            </div>
        </form>
    </div>
</main>
<?php include "footer.php"; ?>
</body>
</html>