<?php
session_start();
include 'connect-db.php';
include 'functions/functions.php';

// Validasi ID Mitra dari URL
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: index.php");
    exit;
}
$idMitra = $_GET["id"];

// Ambil data mitra dari database
$query = mysqli_query($connect, "SELECT * FROM mitra WHERE id_mitra = '$idMitra'");
if (mysqli_num_rows($query) === 0) {
    // Jika ID tidak ditemukan, kembalikan ke index
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
    <br><br>

    <div class="container">
        <div class="row card-panel">
            <div class="col s12 m4 center">
                <img src="img/mitra/<?= htmlspecialchars($mitra['foto']) ?>" class="responsive-img" style="width: 200px; border-radius: 10px;" />
                <br><br>
                <a class="btn-large waves-effect waves-light red darken-3" href="pesan-laundry.php?id=<?= $idMitra ?>">Pesan Layanan</a>
            </div>
            <div class="col s12 m8">
                <h3><?= htmlspecialchars($mitra["nama_laundry"]) ?></h3>
                <p><i class="material-icons tiny">person</i> Pemilik: <?= htmlspecialchars($mitra["nama_pemilik"]) ?></p>
                <p><i class="material-icons tiny">place</i> Alamat: <?= htmlspecialchars($mitra["alamat"] . ", " . $mitra["kota"]) ?></p>
                <p><i class="material-icons tiny">phone</i> No. HP: <?= htmlspecialchars($mitra["telp"]) ?></p>
            </div>
        </div>

        <div class="row">
            <h4 class="header light center">Daftar Harga Layanan (per Kg)</h4>
            <?php
            $queryHarga = mysqli_query($connect, "SELECT * FROM harga WHERE id_mitra = '$idMitra' ORDER BY jenis");
            if (mysqli_num_rows($queryHarga) > 0) :
                while ($harga = mysqli_fetch_assoc($queryHarga)) :
                    ?>
                    <div class="col s12 m4">
                        <div class="card-panel center-align">
                            <i class="material-icons large blue-text text-darken-2">local_offer</i>
                            <h5><?= ucfirst(htmlspecialchars($harga['jenis'])) ?></h5>
                            <p class="light">Rp <?= number_format($harga['harga']) ?> / Kg</p>
                        </div>
                    </div>
                <?php
                endwhile;
            else :
                echo "<p class='center'>Mitra ini belum menetapkan harga.</p>";
            endif;
            ?>
        </div>

        <div class="row">
            <h4 class="header light center">Ulasan Pelanggan</h4>
            <?php
            $queryUlasan = mysqli_query($connect, "SELECT t.*, p.nama as nama_pelanggan, p.foto as foto_pelanggan 
                                                  FROM transaksi t 
                                                  JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan 
                                                  WHERE t.id_mitra = '$idMitra' AND t.komentar != '' 
                                                  ORDER BY t.tgl_transaksi DESC");
            if (mysqli_num_rows($queryUlasan) > 0) :
                while ($ulasan = mysqli_fetch_assoc($queryUlasan)) :
                    ?>
                    <div class="col s12">
                        <div class="card-panel grey lighten-5 z-depth-1">
                            <div class="row valign-wrapper">
                                <div class="col s2 m1 center-align">
                                    <img src="img/pelanggan/<?= htmlspecialchars($ulasan['foto_pelanggan']) ?>" alt="" class="circle responsive-img">
                                </div>
                                <div class="col s10 m11">
                                    <strong><?= htmlspecialchars($ulasan['nama_pelanggan']) ?></strong>
                                    <p>Rating: <?= $ulasan['rating'] ? str_repeat('&#9733;', $ulasan['rating']) : 'N/A' ?></p>
                                    <p class="black-text">
                                        <?= htmlspecialchars($ulasan['komentar']) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
                endwhile;
            else :
                echo "<p class='center'>Belum ada ulasan untuk mitra ini.</p>";
            endif;
            ?>
        </div>
    </div>
</main>

<?php include "footer.php"; ?>
</body>
</html>