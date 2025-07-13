<?php
session_start();
include 'connect-db.php';
include 'functions/functions.php';

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: index.php");
    exit;
}
$idMitra = $_GET["id"];

$query = mysqli_query($connect, "SELECT * FROM mitra WHERE id_mitra = '$idMitra'");
if (mysqli_num_rows($query) === 0) {
    header("Location: index.php");
    exit;
}
$mitra = mysqli_fetch_assoc($query);

// --- START: Perbaikan Query dan Logika Rating ---
// Asumsi rating di DB adalah skala 1-10, kita ubah ke skala 5
$rating_query = mysqli_query($connect, "SELECT AVG(rating / 2) as average_rating, COUNT(id_transaksi) as total_reviews FROM transaksi WHERE id_mitra = '$idMitra' AND rating IS NOT NULL AND rating > 0");
$rating_data = mysqli_fetch_assoc($rating_query);
// Pembulatan 1 desimal
$average_rating = round($rating_data['average_rating'] ?? 0, 1);
$total_reviews = $rating_data['total_reviews'] ?? 0;
// --- END: Perbaikan Query dan Logika Rating ---
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "headtags.html"; ?>
    <title>Detail Mitra - <?= htmlspecialchars($mitra["nama_laundry"]) ?></title>
</head>
<body>
<?php include 'header.php'; ?>
<main class="main-content">
    <div class="container">

        <div class="card-panel" style="margin-top: 1rem;">
            <div class="row" style="margin-bottom: 0;">
                <div class="col s12 m4 center-align">
                    <img src="img/mitra/<?= htmlspecialchars($mitra['foto']) ?>" class="responsive-img" alt="Foto <?= htmlspecialchars($mitra['nama_laundry']) ?>" style="width: 200px; height: 200px; border-radius: 50%; object-fit: cover; border: 4px solid var(--primary-blue);">
                </div>
                <div class="col s12 m8">
                    <h3 style="margin-top: 10px; margin-bottom: 5px;"><?= htmlspecialchars($mitra["nama_laundry"]) ?></h3>

                    <div class="star-rating-display" style="margin-bottom: 15px;">
                        <?php
                        $full_stars = floor($average_rating);
                        $half_star = ($average_rating - $full_stars) >= 0.5;
                        $empty_stars = 5 - $full_stars - ($half_star ? 1 : 0);

                        // Tampilkan bintang penuh
                        for ($i = 0; $i < $full_stars; $i++) {
                            echo '<i class="material-icons">star</i>';
                        }
                        // Tampilkan bintang setengah
                        if ($half_star) {
                            echo '<i class="material-icons">star_half</i>';
                        }
                        // Tampilkan bintang kosong
                        for ($i = 0; $i < $empty_stars; $i++) {
                            echo '<i class="material-icons">star_border</i>';
                        }
                        ?>
                        <span style="vertical-align: top; margin-left: 10px; font-weight: 500; font-size: 1.1rem;"><?= $average_rating ?> dari <?= $total_reviews ?> ulasan</span>
                    </div>
                    <p class="light" style="font-size: 1.1rem;"><i class="material-icons tiny" style="vertical-align: middle;">person</i> Pemilik: <?= htmlspecialchars($mitra["nama_pemilik"]) ?></p>
                    <p class="light" style="font-size: 1.1rem;"><i class="material-icons tiny" style="vertical-align: middle;">phone</i> No. HP: <?= htmlspecialchars($mitra["telp"]) ?></p>
                    <p class="light" style="font-size: 1.1rem;"><i class="material-icons tiny" style="vertical-align: middle;">place</i> Alamat: <?= htmlspecialchars($mitra["alamat"]) ?></p>
                    <br>
                    <a class="btn-large waves-effect waves-light" href="pesan-laundry.php?id=<?= $idMitra ?>">
                        <i class="material-icons left">shopping_cart</i>Pesan Layanan Sekarang
                    </a>
                </div>
            </div>
        </div>

        <div class="section">
            <h4 class="header light center">Daftar Harga Layanan (per Kg)</h4>
            <div class="row">
                <?php
                $queryHarga = mysqli_query($connect, "SELECT * FROM harga WHERE id_mitra = '$idMitra' ORDER BY FIELD(jenis, 'cuci', 'setrika', 'komplit')");
                if (mysqli_num_rows($queryHarga) > 0) :
                    while ($harga = mysqli_fetch_assoc($queryHarga)) :
                        $icon = 'local_laundry_service';
                        if ($harga['jenis'] == 'setrika') $icon = 'iron';
                        if ($harga['jenis'] == 'komplit') $icon = 'check_circle';
                        ?>
                        <div class="col s12 m4">
                            <div class="card-panel center-align hoverable">
                                <i class="material-icons large" style="color: var(--primary-blue);"><?= $icon ?></i>
                                <h5><?= ucfirst(htmlspecialchars($harga['jenis'])) ?></h5>
                                <h4 class="light" style="color: var(--dark-navy); margin: 10px 0;">Rp <?= number_format($harga['harga']) ?>,-</h4>
                                <p class="light">per Kilogram</p>
                            </div>
                        </div>
                    <?php
                    endwhile;
                else :
                    echo "<p class='center light'>Mitra ini belum menetapkan harga layanan.</p>";
                endif;
                ?>
            </div>
        </div>

        <div class="section">
            <h4 class="header light center">Ulasan Pelanggan</h4>
            <?php
            $queryUlasan = mysqli_query($connect, "SELECT t.*, p.nama as nama_pelanggan, p.foto as foto_pelanggan 
                                                  FROM transaksi t 
                                                  JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan 
                                                  WHERE t.id_mitra = '$idMitra' AND t.komentar IS NOT NULL AND t.komentar != '' 
                                                  ORDER BY t.tgl_transaksi DESC LIMIT 5");
            if (mysqli_num_rows($queryUlasan) > 0) :
                while ($ulasan = mysqli_fetch_assoc($queryUlasan)) :
                    ?>
                    <div class="card-panel" style="margin-bottom: 1rem;">
                        <div class="row valign-wrapper" style="margin-bottom: 0;">
                            <div class="col s3 m2 l1 center-align">
                                <img src="img/pelanggan/<?= htmlspecialchars($ulasan['foto_pelanggan']) ?>" alt="Foto Pelanggan" class="circle responsive-img">
                            </div>
                            <div class="col s9 m10 l11">
                                <strong style="color: var(--dark-navy); font-size: 1.1rem;"><?= htmlspecialchars($ulasan['nama_pelanggan']) ?></strong>
                                <div class="star-rating-display" style="margin-left: 10px;">
                                    <?php
                                    // Mengubah rating ulasan per item ke skala 5
                                    $item_rating = ($ulasan['rating'] ?? 0) / 2;
                                    $item_full = floor($item_rating);
                                    $item_half = ($item_rating - $item_full) >= 0.5;
                                    $item_empty = 5 - $item_full - ($item_half ? 1 : 0);

                                    for ($i = 0; $i < $item_full; $i++) echo '<i class="material-icons tiny">star</i>';
                                    if ($item_half) echo '<i class="material-icons tiny">star_half</i>';
                                    for ($i = 0; $i < $item_empty; $i++) echo '<i class="material-icons tiny">star_border</i>';
                                    ?>
                                </div>
                                <p class="light" style="margin-top: 5px; font-style: italic;">"<?= htmlspecialchars($ulasan['komentar']) ?>"</p>
                                <p class="grey-text" style="font-size: 0.8rem; margin-top: 8px;">Diulas pada: <?= date('d M Y', strtotime($ulasan['tgl_transaksi'])) ?></p>
                            </div>
                        </div>
                    </div>
                <?php
                endwhile;
            else :
                echo "<p class='center light'>Belum ada ulasan untuk mitra ini.</p>";
            endif;
            ?>
        </div>
    </div>
</main>

<?php include "footer.php"; ?>
</body>
</html>