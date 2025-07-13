<?php
session_start(); // Wajib ada di baris paling atas

// --- START: Mekanisme Keamanan & Role Access ---

// 1. Cek Apakah Pengguna Sudah Login
// Jika tidak ada session 'id_pelanggan', artinya pengguna belum login.
// Maka, paksa pengguna kembali ke halaman login.
if (!isset($_SESSION["id_pelanggan"])) {
    header("Location: login.php");
    exit; // Hentikan eksekusi script
}

// 2. Ambil ID Pelanggan yang Sedang Login
// ID ini akan digunakan untuk memfilter data di database.
include 'connect-db.php';
include 'functions/functions.php';
$id_pelanggan = $_SESSION["id_pelanggan"];

// 3. Query Spesifik untuk Pengguna yang Login
// Perhatikan klausa `WHERE transaksi.id_pelanggan = '$id_pelanggan'`.
// Ini adalah kunci utamanya: Query ini HANYA akan mengambil data transaksi
// yang kolom 'id_pelanggan'-nya cocok dengan ID pengguna yang sedang login.
// Dengan begitu, pengguna A tidak akan pernah bisa melihat data pengguna B.
$query = mysqli_query($connect, "SELECT transaksi.*, mitra.nama_laundry, mitra.foto 
                                 FROM transaksi 
                                 JOIN mitra ON transaksi.id_mitra = mitra.id_mitra 
                                 WHERE transaksi.id_pelanggan = '$id_pelanggan' 
                                 ORDER BY transaksi.tgl_transaksi DESC");

// --- END: Mekanisme Keamanan & Role Access ---
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "headtags.html"; ?>
    <title>Riwayat Transaksi</title>
</head>
<body>
<?php include 'header.php'; ?>
<main class="main-content">
    <div class="container">
        <h3 class="page-title">Riwayat Transaksi Anda</h3>
        <?php if (mysqli_num_rows($query) > 0) : ?>
            <div class="card-container">
                <?php while ($transaksi = mysqli_fetch_assoc($query)) : ?>
                    <div class="card-panel card-transaksi">
                        <div class="row valign-wrapper" style="margin-bottom: 0;">
                            <div class="col s3 m2 l2">
                                <img src="img/mitra/<?= htmlspecialchars($transaksi['foto']) ?>" alt="Logo Mitra" class="circle responsive-img">
                            </div>
                            <div class="col s9 m10 l10">
                                <span class="card-title" style="font-weight: 500;"><?= htmlspecialchars($transaksi['nama_laundry']) ?></span>
                                <p class="grey-text" style="margin-top: -5px;">ID Pesanan: #<?= htmlspecialchars($transaksi['id_transaksi']) ?></p>
                                <p>Tanggal Pesan: <?= date('d M Y', strtotime($transaksi['tgl_transaksi'])) ?></p>
                                <p>Total Bayar: <strong>Rp <?= number_format($transaksi['total_bayar']) ?>,-</strong></p>
                                <p>Status: <span class="status-chip status-<?= strtolower(htmlspecialchars($transaksi['status'])) ?>"><?= ucfirst(htmlspecialchars($transaksi['status'])) ?></span></p>

                                <div style="margin-top: 15px;">
                                    <?php if ($transaksi['status'] == 'selesai' && is_null($transaksi['rating'])) : ?>
                                        <a href="beri-ulasan.php?id=<?= $transaksi['id_transaksi'] ?>" class="btn waves-effect waves-light">
                                            <i class="material-icons left">star_rate</i>Beri Ulasan
                                        </a>
                                    <?php elseif ($transaksi['status'] == 'selesai' && !is_null($transaksi['rating'])) : ?>
                                        <a class="btn disabled">
                                            <i class="material-icons left">check_circle</i>Ulasan Diberikan
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else : ?>
            <div class="center-align" style="padding: 40px;">
                <i class="material-icons large grey-text">history</i>
                <h5 class="light">Anda belum memiliki riwayat transaksi.</h5>
                <p class="light">Ayo mulai memesan laundry pertama Anda!</p>
                <a href="index.php" class="btn-large waves-effect waves-light" style="margin-top: 20px;">Cari Mitra Laundry</a>
            </div>
        <?php endif; ?>
    </div>
</main>
<?php include 'footer.php'; ?>
</body>
</html>