<?php
session_start();
include 'connect-db.php';
include 'functions/functions.php';

// Pastikan hanya admin yang login yang bisa mengakses halaman ini
cekAdmin();

// Ambil data untuk statistik sederhana (opsional, tapi membuat dasbor lebih informatif)
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
    <style>
        .dashboard-card {
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            color: white;
            margin-bottom: 20px;
        }
        .dashboard-card i {
            font-size: 3em;
        }
        .dashboard-card h5 {
            margin-top: 15px;
            font-weight: bold;
        }
        .dashboard-card p {
            font-size: 1.2em;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<main class="main-content">
    <div class="container">
        <h3 class="header light center">Dasbor Admin</h3>
        <p class="center">Selamat datang, Admin! Kelola semua data dari sini.</p>
        <br>

        <div class="row">
            <div class="col s12 m4">
                <div class="card-panel blue darken-2 white-text center">
                    <i class="material-icons large">store</i>
                    <h5>Total Mitra</h5>
                    <h4 class="light"><?= $jumlahMitra ?></h4>
                </div>
            </div>
            <div class="col s12 m4">
                <div class="card-panel teal darken-2 white-text center">
                    <i class="material-icons large">people</i>
                    <h5>Total Pelanggan</h5>
                    <h4 class="light"><?= $jumlahPelanggan ?></h4>
                </div>
            </div>
            <div class="col s12 m4">
                <div class="card-panel deep-purple darken-2 white-text center">
                    <i class="material-icons large">receipt</i>
                    <h5>Total Transaksi</h5>
                    <h4 class="light"><?= $jumlahTransaksi ?></h4>
                </div>
            </div>
        </div>

        <div class="row">
            <h4 class="header light center">Menu Manajemen</h4>
            <div class="col s12 m6 l4">
                <a href="list-mitra.php" class="btn-large waves-effect waves-light blue darken-3" style="width:100%; margin-bottom:15px;">
                    <i class="material-icons left">store</i>Kelola Mitra
                </a>
            </div>
            <div class="col s12 m6 l4">
                <a href="list-pelanggan.php" class="btn-large waves-effect waves-light teal darken-3" style="width:100%; margin-bottom:15px;">
                    <i class="material-icons left">people</i>Kelola Pelanggan
                </a>
            </div>
            <div class="col s12 m12 l4">
                <a href="transaksi.php" class="btn-large waves-effect waves-light deep-purple darken-3" style="width:100%; margin-bottom:15px;">
                    <i class="material-icons left">receipt</i>Riwayat Transaksi
                </a>
            </div>
        </div>

        <div class="row center" style="margin-top: 30px;">
            <a class="btn waves-effect waves-light red darken-2" href="ganti-kata-sandi.php">Ganti Kata Sandi Admin</a>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>
</body>
</html>