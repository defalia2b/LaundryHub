<?php
session_start();
include 'connect-db.php';
include 'functions/functions.php';

// Jika sudah login, langsung alihkan ke halaman yang sesuai
cekLogin();

// Inisialisasi variabel pesan error
$error_message = '';

if (isset($_POST["login"])) {
    // Pastikan jenis akun telah dipilih
    if (empty($_POST["akun"])) {
        $error_message = 'Pilih jenis akun terlebih dahulu (Admin/Mitra/Pelanggan).';
    } else {
        $jenis_akun = $_POST["akun"];
        $email_or_user = htmlspecialchars($_POST["email"]);
        $password = htmlspecialchars($_POST["password"]);

        $tabel = '';
        $kolom_user = '';
        $kolom_id = '';
        $redirect_page = '';
        $session_key = '';
        $session_login_key = '';
        $error_email_not_found = '';

        switch ($jenis_akun) {
            case 'mitra':
                $tabel = 'mitra';
                $kolom_user = 'email';
                $kolom_id = 'id_mitra';
                $redirect_page = 'status.php';
                $session_key = 'mitra';
                $session_login_key = 'login-mitra';
                $error_email_not_found = 'Email belum terdaftar sebagai Mitra.';
                break;
            case 'pelanggan':
                $tabel = 'pelanggan';
                $kolom_user = 'email';
                $kolom_id = 'id_pelanggan';
                $redirect_page = 'index.php';
                $session_key = 'pelanggan';
                $session_login_key = 'login-pelanggan';
                $error_email_not_found = 'Email belum terdaftar sebagai Pelanggan.';
                break;
            case 'admin':
                $tabel = 'admin';
                $kolom_user = 'username';
                $kolom_id = 'id_admin';
                $redirect_page = 'admin.php';
                $session_key = 'admin';
                $session_login_key = 'login-admin';
                $error_email_not_found = 'Username tidak terdaftar sebagai Admin.';
                break;
        }

        if ($tabel) {
            $result = mysqli_query($connect, "SELECT * FROM $tabel WHERE $kolom_user = '$email_or_user'");

            if (mysqli_num_rows($result) === 1) {
                $row = mysqli_fetch_assoc($result);
                if ($password === $row["password"]) {
                    $_SESSION[$session_key] = $row[$kolom_id];
                    $_SESSION[$session_login_key] = true;
                    header("Location: $redirect_page");
                    exit;
                } else {
                    $error_message = 'Password yang Anda masukkan salah.';
                }
            } else {
                $error_message = $error_email_not_found;
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
    <title>Login - LaundryHub</title>
</head>
<body>
<?php include 'header.php'; ?>
<main class="main-content">
    <div class="container">
        <div class="row">
            <div class="col s12 m8 l6 offset-m2 offset-l3">
                <div class="card-panel center-card">
                    <h4 class="header center">Login Akun</h4>
                    <p class="center light">Selamat datang kembali! Silakan masuk ke akun Anda.</p>
                    <div class="row">
                        <form class="col s12" action="" method="post">
                            <div class="row">
                                <div class="col s12" style="margin-bottom: 20px;">
                                    <p class="center" style="margin-top:0;">Login sebagai:</p>
                                    <div style="display: flex; justify-content: center; gap: 15px;">
                                        <label><input name="akun" value="pelanggan" type="radio" checked/><span>Pelanggan</span></label>
                                        <label><input name="akun" value="mitra" type="radio"/><span>Mitra</span></label>
                                        <label><input name="akun" value="admin" type="radio"/><span>Admin</span></label>
                                    </div>
                                </div>
                                <div class="input-field col s12">
                                    <i class="material-icons prefix">person_outline</i>
                                    <input type="text" id="email" name="email" required>
                                    <label for="email">Email atau Username</label>
                                </div>
                                <div class="input-field col s12">
                                    <i class="material-icons prefix">lock_outline</i>
                                    <input type="password" id="password" name="password" required>
                                    <label for="password">Password</label>
                                </div>
                            </div>
                            <div class="row center">
                                <button class="btn-large waves-effect waves-light" type="submit" name="login" style="width: 100%;">Login</button>
                            </div>
                        </form>
                    </div>
                    <div class="center" style="margin-top: 20px;">
                        <p class="light">Belum punya akun? <a href="registrasi.php">Daftar di sini</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include "footer.php"; ?>

<?php
if (!empty($error_message)) {
    echo "<script>Swal.fire('Login Gagal', '" . addslashes($error_message) . "', 'error');</script>";
}
?>
</body>
</html>