<?php
session_start();
include 'connect-db.php';
include 'functions/functions.php';

cekAdmin();

$jumlahMitra = mysqli_num_rows(mysqli_query($connect, "SELECT id_mitra FROM mitra"));
$jumlahPelanggan = mysqli_num_rows(mysqli_query($connect, "SELECT id_pelanggan FROM pelanggan"));
$jumlahTransaksi = mysqli_num_rows(mysqli_query($connect, "SELECT id_transaksi FROM transaksi"));

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include 'headtags.html'; ?>
    <title>Dasbor Admin - LaundryHub</title>
</head>
<body>
<?php include 'header.php'; ?>

<main class="main-content">
    <div class="container">
        <h3 class="header light center">Dasbor Admin</h3>
        <p class="center light">Selamat datang, Admin! Kelola semua data dari sini.</p>
        <br>

        <div class="row">
            <div class="col s12 m4">
                <div class="card-panel center hoverable">
                    <i class="material-icons large" style="color: var(--primary-blue);">store</i>
                    <h5 class="header">Total Mitra</h5>
                    <h3 class="light" style="color: var(--dark-navy);"><?= $jumlahMitra ?></h3>
                </div>
            </div>
            <div class="col s12 m4">
                <div class="card-panel center hoverable">
                    <i class="material-icons large" style="color: #2ecc71;">people</i>
                    <h5 class="header">Total Pelanggan</h5>
                    <h3 class="light" style="color: var(--dark-navy);"><?= $jumlahPelanggan ?></h3>
                </div>
            </div>
            <div class="col s12 m4">
                <div class="card-panel center hoverable">
                    <i class="material-icons large" style="color: #e74c3c;">receipt</i>
                    <h5 class="header">Total Transaksi</h5>
                    <h3 class="light" style="color: var(--dark-navy);"><?= $jumlahTransaksi ?></h3>
                </div>
            </div>
        </div>

        <div class="section">
            <h4 class="header light center">Menu Manajemen</h4>
            <div class="row">
                <div class="col s12 m4">
                    <a href="list-mitra.php" class="card-panel hoverable center-align" style="display: block; color: var(--text-dark);">
                        <i class="material-icons large" style="color: var(--primary-blue);">store</i>
                        <h6 class="header">Kelola Mitra</h6>
                        <p class="light">Lihat, cari, dan hapus data mitra.</p>
                    </a>
                </div>
                <div class="col s12 m4">
                    <a href="list-pelanggan.php" class="card-panel hoverable center-align" style="display: block; color: var(--text-dark);">
                        <i class="material-icons large" style="color: #2ecc71;">people</i>
                        <h6 class="header">Kelola Pelanggan</h6>
                        <p class="light">Lihat, cari, dan hapus data pelanggan.</p>
                    </a>
                </div>
                <div class="col s12 m4">
                    <a href="transaksi.php" class="card-panel hoverable center-align" style="display: block; color: var(--text-dark);">
                        <i class="material-icons large" style="color: #e74c3c;">receipt</i>
                        <h6 class="header">Riwayat Transaksi</h6>
                        <p class="light">Tinjau semua transaksi yang terjadi.</p>
                    </a>
                </div>
                <div class="col s12 m4">
                    <a href="admin-ulasan.php" class="card-panel hoverable center-align" style="display: block; color: var(--text-dark);">
                        <i class="material-icons large" style="color: #ff9800;">rate_review</i>
                        <h6 class="header">Moderasi Ulasan</h6>
                        <p class="light">Tinjau laporan ulasan dari mitra.</p>
                    </a>
                </div>
            </div>
        </div>

        <div class="row center" style="margin-top: 30px;">
            <a class="btn waves-effect waves-light red" href="ganti-kata-sandi.php">
                <i class="material-icons left">lock</i>Ganti Kata Sandi Admin
            </a>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>
</body>
</html>