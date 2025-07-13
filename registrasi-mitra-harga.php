<?php
session_start();
include 'connect-db.php';
include 'functions/functions.php';

cekMitra();
$idMitra = $_SESSION["mitra"];

$pesan_sukses_awal = $_SESSION['pesan_sukses'] ?? null;
unset($_SESSION['pesan_sukses']);

if (isset($_POST["submit"])) {
    function dataHarga($data) {
        global $connect, $idMitra;
        // ... (Fungsi backend dan validasi tidak diubah)
        $cuci = htmlspecialchars($data["cuci"]);
        $setrika = htmlspecialchars($data["setrika"]);
        $komplit = htmlspecialchars($data["komplit"]);

        if (!validasiHarga($cuci) || !validasiHarga($setrika) || !validasiHarga($komplit)) {
            return false;
        }

        // Hapus harga lama jika ada, untuk menghindari duplikasi
        mysqli_query($connect, "DELETE FROM harga WHERE id_mitra = '$idMitra'");

        $query_cuci = "INSERT INTO harga (jenis, id_mitra, harga) VALUES ('cuci', '$idMitra', '$cuci')";
        $query_setrika = "INSERT INTO harga (jenis, id_mitra, harga) VALUES ('setrika', '$idMitra', '$setrika')";
        $query_komplit = "INSERT INTO harga (jenis, id_mitra, harga) VALUES ('komplit', '$idMitra', '$komplit')";

        mysqli_query($connect, $query_cuci);
        mysqli_query($connect, $query_setrika);
        mysqli_query($connect, $query_komplit);

        return mysqli_affected_rows($connect) > 0;
    }

    if (dataHarga($_POST)) {
        $_SESSION['pesan_sukses'] = "Pendaftaran Selesai! Harga layanan Anda telah berhasil disimpan.";
        header("Location: status.php");
        exit;
    } else {
        $_SESSION['pesan_error'] = "Terjadi kesalahan saat menyimpan harga. " . ($_SESSION['pesan_error'] ?? '');
        header("Location: registrasi-mitra-harga.php");
        exit;
    }
}

$pesan_error_submit = $_SESSION['pesan_error'] ?? null;
unset($_SESSION['pesan_error']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "headtags.html"; ?>
    <title>Langkah 2: Atur Harga Layanan - LaundryHub</title>
</head>
<body>

<?php include 'header.php' ?>
<main class="main-content">
    <div class="container">
        <div class="row">
            <div class="col s12 m8 l6 offset-m2 offset-l3">
                <div class="card-panel center-card">
                    <h4 class="header light center">Atur Harga Layanan</h4>
                    <p class="center light">Langkah 2: Masukkan harga per Kg untuk setiap jenis layanan yang Anda tawarkan.</p>
                    <form action="" method="post">
                        <div class="input-field">
                            <i class="material-icons prefix">local_laundry_service</i>
                            <input type="number" name="cuci" id="cuci" value="0" required min="0">
                            <label for="cuci">Harga Cuci Kering (per Kg)</label>
                        </div>
                        <div class="input-field">
                            <i class="material-icons prefix">iron</i>
                            <input type="number" name="setrika" id="setrika" value="0" required min="0">
                            <label for="setrika">Harga Setrika Saja (per Kg)</label>
                        </div>
                        <div class="input-field">
                            <i class="material-icons prefix">check_circle</i>
                            <input type="number" name="komplit" id="komplit" value="0" required min="0">
                            <label for="komplit">Harga Cuci + Setrika (per Kg)</label>
                        </div>
                        <div class="center" style="margin-top: 30px;">
                            <button class="btn-large waves-effect waves-light" type="submit" name="submit" style="width: 100%;">Selesaikan Pendaftaran</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>

<?php
if ($pesan_sukses_awal) {
    echo "<script>Swal.fire('Berhasil!', '" . addslashes($pesan_sukses_awal) . "', 'success');</script>";
}
if ($pesan_error_submit) {
    echo "<script>Swal.fire('Gagal', '" . addslashes($pesan_error_submit) . "', 'error');</script>";
}
?>
</body>
</html>