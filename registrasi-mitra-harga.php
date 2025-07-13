<?php

session_start();
include 'connect-db.php';
include 'functions/functions.php';

$pesan_sukses = null;
if (isset($_SESSION['pesan_sukses'])) {
    $pesan_sukses = $_SESSION['pesan_sukses'];
    unset($_SESSION['pesan_sukses']); // Hapus pesan setelah diambil
}

cekMitra();

$idMitra = $_SESSION["mitra"];

// --- AWAL BAGIAN YANG PERLU DIPERBAIKI ---
// KODE PENGGANTI
if (isset($_POST["submit"])) {

    function dataHarga($data)
    {
        global $connect, $idMitra;

        $cuci = htmlspecialchars($data["cuci"]);
        $setrika = htmlspecialchars($data["setrika"]);
        $komplit = htmlspecialchars($data["komplit"]);

        validasiHarga($cuci);
        validasiHarga($setrika);
        validasiHarga($komplit);

        $query2 = "INSERT INTO harga VALUES ('', 'cuci', '$idMitra', '$cuci')";
        $query3 = "INSERT INTO harga VALUES ('', 'setrika', '$idMitra', '$setrika')";
        $query4 = "INSERT INTO harga VALUES ('', 'komplit', '$idMitra', '$komplit')";

        mysqli_query($connect, $query2);
        mysqli_query($connect, $query3);
        mysqli_query($connect, $query4);

        return mysqli_affected_rows($connect);
    }

    // Panggil fungsi yang benar (dataHarga), bukan registrasi
    if (dataHarga($_POST) > 0) {
        echo "
        <script>
            Swal.fire('Pendaftaran Selesai','Harga layanan Anda telah berhasil disimpan.','success').then(function(){
                window.location = 'status.php';
            });
        </script>
        ";
    } else {
        echo mysqli_error($connect);
    }
}
// --- AKHIR BAGIAN YANG PERLU DIPERBAIKI ---

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "headtags.html"; ?>
    <title>Langkah 2: Atur Harga Layanan</title>
</head>
<body>

<?php include 'header.php' ?>
<main class="main-content">
    <div class="row">
        <div class="col s6 offset-s3">
            <h3 class="header light center">Atur Harga Layanan Anda (per Kg)</h3>
            <div class="card-panel">
                <form action="" method="post">
                    <div class="input-field inline">
                        <ul>
                            <li>
                                <label for="cuci">Cuci Saja (Rp)</label>
                                <input type="number" name="cuci" value="0" required>
                            </li>
                            <li>
                                <label for="setrika">Setrika Saja (Rp)</label>
                                <input type="number" name="setrika" value="0" required>
                            </li>
                            <li>
                                <label for="komplit">Cuci + Setrika (Rp)</label>
                                <input type="number" name="komplit" value="0" required>
                            </li>
                            <li>
                                <div class="center">
                                    <button class="btn-large blue darken-2" type="submit" name="submit">Selesaikan Pendaftaran</button>
                                </div>
                            </li>
                        </ul>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>
<?php
// Tampilkan popup jika ada pesan sukses dari session
if ($pesan_sukses) {
    echo "<script>Swal.fire('Berhasil!', '" . addslashes($pesan_sukses) . "', 'success');</script>";
}
?>
</body>
</html>