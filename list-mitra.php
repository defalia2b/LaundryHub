<?php
session_start();
include 'connect-db.php';
include 'functions/functions.php';

cekAdmin();

// Pagination logic remains the same
$jumlahDataPerHalaman = 6;
$keyword = $_POST['keyword'] ?? '';

// Query untuk total data (dengan atau tanpa keyword)
$count_query_str = "SELECT COUNT(*) as total FROM mitra";
if (!empty($keyword)) {
    $count_query_str .= " WHERE nama_laundry LIKE '%$keyword%' OR nama_pemilik LIKE '%$keyword%' OR email LIKE '%$keyword%' OR alamat LIKE '%$keyword%'";
}
$totalDataQuery = mysqli_query($connect, $count_query_str);
$jumlahData = mysqli_fetch_assoc($totalDataQuery)['total'];
$jumlahHalaman = ceil($jumlahData / $jumlahDataPerHalaman);
$halamanAktif = $_GET["page"] ?? 1;
$awalData = ($jumlahDataPerHalaman * $halamanAktif) - $jumlahDataPerHalaman;

// Query untuk mengambil data per halaman dengan rating
$query_str = "SELECT m.*, 
               AVG(t.rating) as avg_rating,
               COUNT(t.rating) as total_reviews
               FROM mitra m 
               LEFT JOIN transaksi t ON m.id_mitra = t.id_mitra AND t.rating IS NOT NULL AND t.status_ulasan = 'Aktif'
               GROUP BY m.id_mitra";
if (!empty($keyword)) {
    $query_str .= " HAVING m.nama_laundry LIKE '%$keyword%' OR m.nama_pemilik LIKE '%$keyword%' OR m.email LIKE '%$keyword%' OR m.alamat LIKE '%$keyword%'";
}
$query_str .= " ORDER BY m.id_mitra DESC LIMIT $awalData, $jumlahDataPerHalaman";
$mitra_list = mysqli_query($connect, $query_str);

// Hapus data
if (isset($_GET["hapus"])){
    $idMitra = $_GET["hapus"];
    mysqli_query($connect, "DELETE FROM mitra WHERE id_mitra = '$idMitra'");
    if (mysqli_affected_rows($connect) > 0 ){
        echo "<script>Swal.fire('Berhasil', 'Data Mitra telah dihapus', 'success').then(() => window.location = 'list-mitra.php');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "headtags.html"; ?>
    <link rel="stylesheet" href="css/rating.css">
    <title>Kelola Data Mitra</title>
    <style>
        .mitra-card {
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .mitra-card .card-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .mitra-card .card-content .card-title {
            flex-shrink: 0;
        }
        .mitra-card .card-content p:first-of-type {
            flex-shrink: 0;
        }
        .rating-section {
            flex: 1;
            min-height: 60px;
            display: flex;
            align-items: center;
        }
        .rating-display {
            margin: 0 !important;
        }
        .no-rating-text {
            color: #999;
            font-size: 12px;
            margin: 0;
            display: flex;
            align-items: center;
            height: 100%;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>
<main class="main-content">
    <div class="container">
        <h3 class="header light center">Kelola Data Mitra</h3>
        <div class="card-panel">
            <form class="row valign-wrapper" action="" method="post">
                <div class="input-field col s10">
                    <i class="material-icons prefix">search</i>
                    <input type="text" id="keyword" name="keyword" value="<?= htmlspecialchars($keyword) ?>">
                    <label for="keyword">Cari Mitra (Nama, Pemilik, Email, Alamat)</label>
                </div>
                <div class="col s2">
                    <button type="submit" class="btn waves-effect waves-light">Cari</button>
                </div>
            </form>
        </div>

        <div class="row">
            <?php if(mysqli_num_rows($mitra_list) > 0): foreach ($mitra_list as $dataMitra) : ?>
                <div class="col s12 m6 l4">
                    <div class="card mitra-card">
                        <div class="card-image">
                            <img src="img/mitra/<?= htmlspecialchars($dataMitra['foto']) ?>" style="height: 200px; object-fit: cover;">
                        </div>
                        <div class="card-content">
                            <span class="card-title activator grey-text text-darken-4"><?= htmlspecialchars($dataMitra['nama_laundry']) ?><i class="material-icons right">more_vert</i></span>
                            <p><?= htmlspecialchars($dataMitra['nama_pemilik']) ?></p>
                            
                            <div class="rating-section">
                                <?php if ($dataMitra['avg_rating']): ?>
                                    <div class="rating-display">
                                        <span class="stars" data-rating="<?= $dataMitra['avg_rating'] ?>">
                                            <?php 
                                            $avg_rating_5 = $dataMitra['avg_rating'] / 2;
                                            $display_stars = round($avg_rating_5);
                                            for ($i = 1; $i <= 5; $i++): 
                                            ?>
                                                <span class="rating-star <?= $i <= $display_stars ? 'filled' : '' ?>">★</span>
                                            <?php endfor; ?>
                                        </span>
                                        <span class="rating-value"><?= number_format($avg_rating_5, 1) ?>/5</span>
                                        <span style="font-size: 12px; color: #666;">(<?= $dataMitra['total_reviews'] ?> ulasan)</span>
                                    </div>
                                <?php else: ?>
                                    <p class="no-rating-text">Belum ada rating</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-reveal">
                            <span class="card-title grey-text text-darken-4">Detail Info<i class="material-icons right">close</i></span>
                            <p><i class="material-icons tiny">email</i> <?= htmlspecialchars($dataMitra['email']) ?></p>
                            <p><i class="material-icons tiny">phone</i> <?= htmlspecialchars($dataMitra['telp']) ?></p>
                            <p><i class="material-icons tiny">place</i> <?= htmlspecialchars($dataMitra['alamat']) ?></p>
                        </div>
                        <div class="card-action">
                            <a href="list-mitra.php?hapus=<?= $dataMitra['id_mitra'] ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus data mitra ini?')" class="red-text">Hapus Mitra</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; else: ?>
                <p class="center light">Data mitra tidak ditemukan.</p>
            <?php endif; ?>
        </div>

        <ul class="pagination center">
            <?php if ($halamanAktif > 1): ?>
                <li class="waves-effect"><a href="?page=<?= $halamanAktif - 1 ?>"><i class="material-icons">chevron_left</i></a></li>
            <?php else: ?>
                <li class="disabled"><a href="#!"><i class="material-icons">chevron_left</i></a></li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $jumlahHalaman; $i++): ?>
                <li class="<?= ($i == $halamanAktif) ? 'active' : 'waves-effect' ?>" style="<?= ($i == $halamanAktif) ? 'background-color: var(--primary-blue);' : '' ?>">
                    <a href="?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <?php if ($halamanAktif < $jumlahHalaman): ?>
                <li class="waves-effect"><a href="?page=<?= $halamanAktif + 1 ?>"><i class="material-icons">chevron_right</i></a></li>
            <?php else: ?>
                <li class="disabled"><a href="#!"><i class="material-icons">chevron_right</i></a></li>
            <?php endif; ?>
        </ul>
    </div>
</main>
<?php include "footer.php"; ?>
<script src="js/rating.js"></script>
</body>
</html>