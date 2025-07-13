<?php
session_start();
include 'connect-db.php';
include 'functions/functions.php';

// Menentukan siapa yang login
$login_type = '';
$user_id = 0;
$redirect_page = 'login.php';

if (isset($_SESSION["login-admin"])) {
    $login_type = "Admin";
    $user_id = $_SESSION["admin"];
    $redirect_page = 'admin.php';
} elseif (isset($_SESSION["login-mitra"])) {
    $login_type = "Mitra";
    $user_id = $_SESSION["mitra"];
    $redirect_page = 'mitra.php';
} elseif (isset($_SESSION["login-pelanggan"])) {
    $login_type = "Pelanggan";
    $user_id = $_SESSION["pelanggan"];
    $redirect_page = 'pelanggan.php';
} else {
    header("Location: login.php");
    exit;
}

$error_message = '';
$success_message = '';

if (isset($_POST["gantiPassword"])){
    $passwordLama = htmlspecialchars($_POST["passwordLama"]);
    $passwordBaru = htmlspecialchars($_POST["password"]);
    $konfirmasiPassword = htmlspecialchars($_POST["repassword"]);

    $tabel = strtolower($login_type);
    $id_field = 'id_' . $tabel;

    $data_query = mysqli_query($connect, "SELECT password FROM $tabel WHERE $id_field = '$user_id'");
    $data_user = mysqli_fetch_assoc($data_query);

    if ($passwordLama !== $data_user["password"]) {
        $error_message = 'Password lama yang Anda masukkan salah.';
    } elseif (strlen($passwordBaru) < 6) {
        $error_message = 'Password baru minimal harus 6 karakter.';
    } elseif ($passwordBaru !== $konfirmasiPassword) {
        $error_message = 'Konfirmasi password baru tidak cocok.';
    } else {
        $update_query = mysqli_query($connect, "UPDATE $tabel SET password = '$passwordBaru' WHERE $id_field = '$user_id'");
        if (mysqli_affected_rows($connect) > 0) {
            $success_message = 'Password berhasil diganti! Anda akan diarahkan dalam beberapa detik.';
            echo "<script>
                    setTimeout(function() {
                        window.location.href = '$redirect_page';
                    }, 2000);
                  </script>";
        } else {
            $error_message = 'Tidak ada perubahan pada password atau terjadi kesalahan.';
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
    <title>Ganti Kata Sandi</title>
</head>
<body>
<?php include 'header.php'; ?>
<main class="main-content">
    <div class="container">
        <h3 class="header light center">Ganti Kata Sandi</h3>
        <p class="center light">Untuk keamanan, gunakan kata sandi yang kuat dan unik.</p>
        <div class="card-panel center-card" style="max-width: 500px; margin: 2rem auto;">
            <form action="" method="POST">
                <div class="input-field">
                    <i class="material-icons prefix">lock_open</i>
                    <input type="password" name="passwordLama" id="passwordLama" required>
                    <label for="passwordLama">Password Lama</label>
                </div>
                <div class="input-field">
                    <i class="material-icons prefix">lock</i>
                    <input type="password" name="password" id="password" required>
                    <label for="password">Password Baru (min. 6 karakter)</label>
                </div>
                <div class="input-field">
                    <i class="material-icons prefix">replay</i>
                    <input type="password" name="repassword" id="repassword" required>
                    <label for="repassword">Konfirmasi Password Baru</label>
                </div>
                <br>
                <div class="center">
                    <button class="btn-large waves-effect waves-light" type="submit" name="gantiPassword" style="width:100%;">Ganti Password</button>
                </div>
            </form>
        </div>
    </div>
</main>
<?php include "footer.php"; ?>

<?php
if (!empty($success_message)) { echo "<script>Swal.fire('Berhasil', '" . addslashes($success_message) . "', 'success');</script>"; }
if (!empty($error_message)) { echo "<script>Swal.fire('Gagal', '" . addslashes($error_message) . "', 'error');</script>"; }
?>
</body>
</html>