<?php
session_start();
include 'connect-db.php';
include 'functions/functions.php';

cekLogin();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "headtags.html"; ?>
    <title>Pilih Jenis Registrasi - LaundryHub</title>
</head>
<body>

<?php include 'header.php'; ?>

<main class="main-content">
    <div class="container">
        <h3 class="header light center">Bergabung dengan LaundryHub</h3>
        <p class="center light">Pilih jenis akun yang ingin Anda daftarkan.</p>
        <br>

        <div class="row">
            <div class="col s12 m6">
                <div class="card-panel center hoverable">
                    <i class="material-icons large" style="color: var(--primary-blue);">person</i>
                    <h5 class="header">Saya Pelanggan</h5>
                    <p class="light">Daftar untuk mulai memesan layanan laundry dari mitra-mitra terbaik di sekitar Anda.</p>
                    <a href="registrasi-pelanggan.php" class="btn waves-effect waves-light">Daftar Sebagai Pelanggan</a>
                </div>
            </div>

            <div class="col s12 m6">
                <div class="card-panel center hoverable">
                    <i class="material-icons large" style="color: #2ecc71;">store</i>
                    <h5 class="header">Saya Mitra Laundry</h5>
                    <p class="light">Daftarkan usaha laundry Anda untuk menjangkau lebih banyak pelanggan dan kelola pesanan dengan mudah.</p>
                    <a href="registrasi-mitra.php" class="btn waves-effect waves-light" style="background-color: #27ae60;">Daftar Sebagai Mitra</a>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include "footer.php"; ?>

</body>
</html>