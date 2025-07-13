<?php
session_start();
include 'connect-db.php';
include 'functions/functions.php';

// Pastikan hanya mitra yang bisa akses
cekMitra();
$idMitra = $_SESSION["mitra"];

// 1. PROSES PENYIMPANAN DATA
if (isset($_POST["simpan"])) {
    $hargaCuci = htmlspecialchars($_POST["cuci"]);
    $hargaSetrika = htmlspecialchars($_POST["setrika"]);
    $hargaKomplit = htmlspecialchars($_POST["komplit"]);

    $error = false;
    if (!validasiHarga($hargaCuci) || !validasiHarga($hargaSetrika) || !validasiHarga($hargaKomplit)) {
        $error = true;
    }

    if (!$error) {
        // Fungsi untuk INSERT atau UPDATE harga
        function prosesHarga($jenis, $harga) {
            global $connect, $idMitra;
            $check_q = mysqli_query($connect, "SELECT id_harga FROM harga WHERE id_mitra = '$idMitra' AND jenis = '$jenis'");
            if (mysqli_num_rows($check_q) > 0) {
                $query = "UPDATE harga SET harga = '$harga' WHERE jenis = '$jenis' AND id_mitra = $idMitra";
            } else {
                $query = "INSERT INTO harga (jenis, id_mitra, harga) VALUES ('$jenis', '$idMitra', '$harga')";
            }
            mysqli_query($connect, $query);
        }

        prosesHarga('cuci', $hargaCuci);
        prosesHarga('setrika', $hargaSetrika);
        prosesHarga('komplit', $hargaKomplit);

        $_SESSION['pesan_sukses'] = "Data harga berhasil diperbarui!";
    }

    header("Location: edit-harga.php");
    exit;
}

// 2. AMBIL DATA HARGA TERBARU DARI DATABASE
$harga_data = [];
$query = mysqli_query($connect, "SELECT jenis, harga FROM harga WHERE id_mitra = '$idMitra'");
while($row = mysqli_fetch_assoc($query)) {
    $harga_data[$row['jenis']] = $row['harga'];
}

// 3. CEK 'FLASH MESSAGE'
$pesan_sukses = $_SESSION['pesan_sukses'] ?? null; unset($_SESSION['pesan_sukses']);
$pesan_error = $_SESSION['pesan_error'] ?? null; unset($_SESSION['pesan_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "headtags.html"; ?>
    <title>Ubah Data Harga Layanan</title>
</head>
<body>
<?php include 'header.php'; ?>

<main class="main-content">
    <div class="container">
        <h3 class="header light center">Ubah Harga Layanan (per Kg)</h3>
        <p class="center light">Perbarui harga layanan Anda. Masukkan 0 jika layanan tidak tersedia.</p>
        <div class="card-panel center-card" style="max-width: 500px; margin: 2rem auto;">
            <form action="" method="post">
                <div class="input-field">
                    <i class="material-icons prefix">local_laundry_service</i>
                    <input type="number" id="cuci" name="cuci" value="<?= htmlspecialchars($harga_data['cuci'] ?? '0') ?>" required min="0">
                    <label for="cuci">Harga Cuci Kering</label>
                </div>
                <div class="input-field">
                    <i class="material-icons prefix">iron</i>
                    <input type="number" id="setrika" name="setrika" value="<?= htmlspecialchars($harga_data['setrika'] ?? '0') ?>" required min="0">
                    <label for="setrika">Harga Setrika Saja</label>
                </div>
                <div class="input-field">
                    <i class="material-icons prefix">check_circle</i>
                    <input type="number" id="komplit" name="komplit" value="<?= htmlspecialchars($harga_data['komplit'] ?? '0') ?>" required min="0">
                    <label for="komplit">Harga Cuci + Setrika</label>
                </div>
                <div class="input-field center">
                    <button class="btn-large waves-effect waves-light" type="submit" name="simpan" style="width: 100%;">Simpan Data Harga</button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php include "footer.php" ?>

<?php
if ($pesan_sukses) { echo "<script>Swal.fire('Berhasil', '" . addslashes($pesan_sukses) . "', 'success');</script>"; }
if ($pesan_error) { echo "<script>Swal.fire('Gagal', '" . addslashes($pesan_error) . "', 'error');</script>"; }
?>
</body>
</html>