<?php
session_start();
include 'connect-db.php';
include 'functions/functions.php';

cekPelanggan();

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: index.php");
    exit;
}
$idMitra = $_GET["id"];

$mitra = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM mitra WHERE id_mitra = '$idMitra'"));
$idPelanggan = $_SESSION["pelanggan"];
$pelanggan = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM pelanggan WHERE id_pelanggan = '$idPelanggan'"));

if (isset($_POST["pesan"])) {
    // ... (Backend logic for processing order remains the same)
    $jenis_layanan = htmlspecialchars($_POST["jenis"]);
    $estimasi_berat = floatval($_POST["estimasi_berat"]);
    $catatan = htmlspecialchars($_POST["catatan"]);
    $alamat_antar_jemput = htmlspecialchars($_POST["alamat"]);
    $tgl_mulai = date("Y-m-d H:i:s");

    $harga_result = mysqli_query($connect, "SELECT harga FROM harga WHERE id_mitra = '$idMitra' AND jenis = '$jenis_layanan'");

    if($harga_result && mysqli_num_rows($harga_result) > 0) {
        $harga_data = mysqli_fetch_assoc($harga_result);
        $harga_per_kg = $harga_data['harga'];
        $harga_estimasi = $estimasi_berat * $harga_per_kg;

        $query = "INSERT INTO pesanan (id_mitra, id_pelanggan, tgl_mulai, jenis, estimasi_berat, harga_estimasi, alamat_antar_jemput, catatan, status_pesanan) 
                  VALUES ('$idMitra', '$idPelanggan', '$tgl_mulai', '$jenis_layanan', '$estimasi_berat', '$harga_estimasi', '$alamat_antar_jemput', '$catatan', 'Menunggu Konfirmasi')";

        mysqli_query($connect, $query);

        if (mysqli_affected_rows($connect) > 0) {
            $_SESSION['pesan_sukses'] = "Pesanan berhasil dibuat! Mitra akan segera mengonfirmasi pesanan Anda.";
            header("Location: status.php");
            exit;
        } else {
            $_SESSION['pesan_error'] = "Gagal membuat pesanan. Terjadi kesalahan pada database.";
        }
    } else {
        $_SESSION['pesan_error'] = "Gagal membuat pesanan. Layanan yang dipilih tidak valid.";
    }
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

$pesan_error = $_SESSION['pesan_error'] ?? null;
unset($_SESSION['pesan_error']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include 'headtags.html' ?>
    <title>Pesan Layanan di <?= htmlspecialchars($mitra["nama_laundry"]) ?></title>
</head>
<body>
<?php include 'header.php' ?>

<main class="main-content">
    <div class="container">
        <h3 class="header light center">Formulir Pemesanan</h3>
        <p class="center light">Anda akan memesan layanan di: <strong><?= htmlspecialchars($mitra["nama_laundry"]) ?></strong></p>

        <div class="card-panel">
            <form action="" method="post">
                <div class="row">
                    <div class="col s12 m6">
                        <h5 class="light"><i class="material-icons left">account_circle</i>Informasi Anda</h5>
                        <div class="input-field">
                            <input id="nama" type="text" disabled value="<?= htmlspecialchars($pelanggan['nama']) ?>">
                            <label for="nama">Nama Pelanggan</label>
                        </div>
                        <div class="input-field">
                            <input id="telp" type="text" disabled value="<?= htmlspecialchars($pelanggan['telp']) ?>">
                            <label for="telp">No. Telepon</label>
                        </div>
                        <div class="input-field">
                            <textarea class="materialize-textarea" name="alamat" id="alamat" required><?= htmlspecialchars($pelanggan['alamat']) ?></textarea>
                            <label for="alamat">Alamat Penjemputan/Pengantaran</label>
                            <span class="helper-text">Pastikan alamat ini sudah benar.</span>
                        </div>
                    </div>

                    <div class="col s12 m6">
                        <h5 class="light"><i class="material-icons left">shopping_basket</i>Detail Pesanan</h5>
                        <div class="input-field">
                            <select name="jenis" required>
                                <option value="" disabled selected>-- Pilih Jenis Layanan --</option>
                                <?php
                                $queryHarga = mysqli_query($connect, "SELECT * FROM harga WHERE id_mitra = '$idMitra'");
                                while($harga = mysqli_fetch_assoc($queryHarga)):
                                    ?>
                                    <option value="<?= htmlspecialchars($harga['jenis']) ?>"><?= ucfirst(htmlspecialchars($harga['jenis'])) ?> - Rp <?= number_format($harga['harga']) ?>/kg</option>
                                <?php endwhile; ?>
                            </select>
                            <label>Jenis Layanan</label>
                        </div>
                        <div class="input-field">
                            <input type="number" step="0.1" name="estimasi_berat" id="estimasi_berat" required min="1">
                            <label for="estimasi_berat">Estimasi Berat (Kg)</label>
                        </div>
                        <div class="input-field">
                            <textarea class="materialize-textarea" name="catatan" id="catatan" placeholder="Contoh: Jangan gunakan pemutih, jemur terpisah, dll."></textarea>
                            <label for="catatan">Catatan Tambahan (Opsional)</label>
                        </div>
                    </div>
                </div>
                <div class="row center">
                    <button class="btn-large waves-effect waves-light" type="submit" name="pesan">
                        <i class="material-icons left">send</i> Buat Pesanan
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php include 'footer.php' ?>

<?php
if ($pesan_error) {
    echo "<script>Swal.fire('Gagal', '" . addslashes($pesan_error) . "', 'error');</script>";
}
?>
</body>
</html>