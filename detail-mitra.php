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
                    <h3 style="margin-top: 10px;"><?= htmlspecialchars($mitra["nama_laundry"]) ?></h3>
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
                                    $rating = $ulasan['rating'] ?? 0;
                                    for ($i = 1; $i <= 5; $i++):
                                        if ($i <= $rating) {
                                            echo '&#9733;'; // Bintang terisi
                                        } else {
                                            echo '<span class="muted-star">&#9733;</span>'; // Bintang kosong
                                        }
                                    endfor;
                                    ?>
                                </div>
                                <p class="light" style="margin-top: 5px;"><?= htmlspecialchars($ulasan['komentar']) ?></p>
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