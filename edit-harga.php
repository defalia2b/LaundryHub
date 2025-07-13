<?php
session_start();
include 'connect-db.php';
include 'functions/functions.php';

// Pastikan hanya mitra yang bisa akses
cekMitra();
$idMitra = $_SESSION["mitra"];

// 1. PROSES PENYIMPANAN DATA (JIKA FORM DI-SUBMIT)
if (isset($_POST["simpan"])) {
    $hargaCuci = htmlspecialchars($_POST["cuci"]);
    $hargaSetrika = htmlspecialchars($_POST["setrika"]);
    $hargaKomplit = htmlspecialchars($_POST["komplit"]);

    // Validasi input
    validasiHarga($hargaCuci);
    validasiHarga($hargaSetrika);
    validasiHarga($hargaKomplit);

    // Fungsi untuk INSERT atau UPDATE harga
    function prosesHarga($jenis, $harga) {
        global $connect, $idMitra;

        // Cek apakah data sudah ada
        $check_q = mysqli_query($connect, "SELECT id_harga FROM harga WHERE id_mitra = '$idMitra' AND jenis = '$jenis'");

        if (mysqli_num_rows($check_q) > 0) {
            // Jika ada, UPDATE
            $query = "UPDATE harga SET harga = '$harga' WHERE jenis = '$jenis' AND id_mitra = $idMitra";
        } else {
            // Jika tidak ada, INSERT
            $query = "INSERT INTO harga (jenis, id_mitra, harga) VALUES ('$jenis', '$idMitra', '$harga')";
        }
        mysqli_query($connect, $query);
    }

    // Jalankan fungsi untuk setiap jenis layanan
    prosesHarga('cuci', $hargaCuci);
    prosesHarga('setrika', $hargaSetrika);
    prosesHarga('komplit', $hargaKomplit);

    // Set 'flash message' untuk notifikasi dan redirect
    $_SESSION['pesan_sukses'] = "Data harga berhasil diperbarui!";
    header("Location: edit-harga.php");
    exit;
}

// 2. AMBIL DATA HARGA TERBARU DARI DATABASE
$cuci_data = mysqli_query($connect, "SELECT harga FROM harga WHERE id_mitra = '$idMitra' AND jenis = 'cuci'");
$cuci = mysqli_fetch_assoc($cuci_data);

$setrika_data = mysqli_query($connect, "SELECT harga FROM harga WHERE id_mitra = '$idMitra' AND jenis = 'setrika'");
$setrika = mysqli_fetch_assoc($setrika_data);

$komplit_data = mysqli_query($connect, "SELECT harga FROM harga WHERE id_mitra = '$idMitra' AND jenis = 'komplit'");
$komplit = mysqli_fetch_assoc($komplit_data);

// 3. CEK APAKAH ADA 'FLASH MESSAGE' UNTUK DITAMPILKAN
$pesan_sukses = null;
if (isset($_SESSION['pesan_sukses'])) {
    $pesan_sukses = $_SESSION['pesan_sukses'];
    unset($_SESSION['pesan_sukses']); // Hapus pesan setelah diambil agar tidak muncul lagi
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "headtags.html"; ?>
    <title>Ubah Data Harga</title>
</head>
<body>
<?php include 'header.php'; ?>

<main class="main-content">
    <div class="container">
        <h3 class="header light center">Data Harga (per Kg)</h3>
        <p class="center">Jika Anda mitra baru, silakan isi harga layanan Anda. Jika ingin mengubah, cukup ganti angkanya lalu simpan.</p>
        <form action="" method="post">
            <div class="input-field">
                <label for="cuci">Cuci</label>
                <input type="number" id="cuci" name="cuci" value="<?= $cuci ? $cuci['harga'] : '0' ?>" required>
            </div>
            <div class="input-field">
                <label for="setrika">Setrika</label>
                <input type="number" id="setrika" name="setrika" value="<?= $setrika ? $setrika['harga'] : '0' ?>" required>
            </div>
            <div class="input-field">
                <label for="komplit">Cuci + Setrika</label>
                <input type="number" id="komplit" name="komplit" value="<?= $komplit ? $komplit['harga'] : '0' ?>" required>
            </div>
            <div class="input-field center">
                <button class="btn-large blue darken-2" type="submit" name="simpan">Simpan Data</button>
            </div>
        </form>
    </div>
</main>

<?php include "footer.php" ?>

<?php
// 4. TAMPILKAN POPUP JIKA ADA PESAN SUKSES
if ($pesan_sukses) {
    echo "
            <script>
                Swal.fire('Berhasil', '" . addslashes($pesan_sukses) . "', 'success');
            </script>
        ";
}
?>
</body>
</html>