<?php

session_start();
include 'connect-db.php';
include 'functions/functions.php';

// sesuaikan dengan jenis login
if((isset($_SESSION["login-admin"]) && isset($_SESSION["admin"]))){
    $login = "Admin";
    $idAdmin = $_SESSION["admin"];
}else if( (isset($_SESSION["login-mitra"]) && isset($_SESSION["mitra"]))){
    $idMitra = $_SESSION["mitra"];
    $login = "Mitra";
}else if ((isset($_SESSION["login-pelanggan"]) && isset($_SESSION["pelanggan"]))){
    $idPelanggan = $_SESSION["pelanggan"];
    $login = "Pelanggan";
}else {
    echo "
        <script>
            document.location.href = 'index.php';
        </script>
    ";
    exit;
}

// ubah sandi
if (isset($_POST["gantiPassword"])){
    $passwordLama = htmlspecialchars($_POST["passwordLama"]);
    $password = htmlspecialchars($_POST["password"]);
    $repassword = htmlspecialchars($_POST["repassword"]);

    $tabel = '';
    $id_field = '';
    $id_value = 0;

    if ($login == 'Admin'){
        $tabel = 'admin';
        $id_field = 'id_admin';
        $id_value = $idAdmin;
    } else if ($login == "Mitra"){
        $tabel = 'mitra';
        $id_field = 'id_mitra';
        $id_value = $idMitra;
    } else if ($login == "Pelanggan"){
        $tabel = 'pelanggan';
        $id_field = 'id_pelanggan';
        $id_value = $idPelanggan;
    }

    // Ambil data user dari database
    $data = mysqli_query($connect, "SELECT * FROM $tabel WHERE $id_field = $id_value");
    $data = mysqli_fetch_assoc($data);

    // 1. Cek apakah password lama sesuai (menggunakan perbandingan biasa)
    if ($passwordLama !== $data["password"]) {
        echo "
            <script>   
                Swal.fire('Gagal','Password Lama Salah','error').then(function() {
                    window.location = 'ganti-kata-sandi.php';
                });
            </script>
        ";
        exit;
    }

    // 2. Cek apakah password baru dan konfirmasinya sama
    if ($password !== $repassword) {
        echo "
            <script>   
                Swal.fire('Gagal','Konfirmasi password baru tidak sama','error').then(function() {
                    window.location = 'ganti-kata-sandi.php';
                });
            </script>
        ";
        exit;
    }

    // 3. Update password baru ke database (sebagai plaintext)
    $query = mysqli_query($connect, "UPDATE $tabel SET password = '$password' WHERE $id_field = $id_value");

    if (mysqli_affected_rows($connect) > 0) {
        echo "
            <script>   
                Swal.fire('Berhasil','Password berhasil diganti','success').then(function() {
                    window.location = 'ganti-kata-sandi.php';
                });
            </script>
        ";
    } else {
        echo "
            <script>   
                Swal.fire('Info','Tidak ada perubahan pada password','info');
            </script>
        ";
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
    <h3 class="header col s24 light center">Ganti Kata Sandi</h3>
    <form action="" method="POST" class="col s18 center">
        <div class="input-field inline">
            <input type="password" name="passwordLama" placeholder="Password Lama" required>
            <input type="password" name="password" placeholder="Password Baru" required>
            <input type="password" name="repassword" placeholder="Konfirmasi Password Baru" required>
            <br><br>
            <button class="waves-effect blue darken-2 btn" type="submit" name="gantiPassword">Ganti Password</button>
        </div>
    </form>
    <br>
</main>
<?php include "footer.php"; ?>
</body>
</html>