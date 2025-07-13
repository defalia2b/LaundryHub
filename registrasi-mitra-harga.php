<?php

session_start();
include 'connect-db.php';
include 'functions/functions.php';

// Pastikan hanya mitra yang sudah login yang bisa mengakses halaman ini
cekMitra();

$idMitra = $_SESSION["mitra"];

// 1. Cek apakah ada 'flash message' dari halaman registrasi sebelumnya untuk ditampilkan saat halaman pertama kali dibuka
$pesan_sukses_awal = null;
if (isset($_SESSION['pesan_sukses'])) {
    $pesan_sukses_awal = $_SESSION['pesan_sukses'];
    unset($_SESSION['pesan_sukses']); // Hapus pesan setelah diambil agar tidak muncul lagi
}

// 2. Proses saat form harga di-submit
if (isset($_POST["submit"])) {

    function dataHarga($data)
    {
        global $connect, $idMitra;

        $cuci = htmlspecialchars($data["cuci"]);
        $setrika = htmlspecialchars($data["setrika"]);
        $komplit = htmlspecialchars($data["komplit"]);

        // Validasi input
        if (!validasiHarga($cuci) || !validasiHarga($setrika) || !validasiHarga($komplit)) {
            return false; // Hentikan jika validasi gagal
        }

        $query_cuci = "INSERT INTO harga (jenis, id_mitra, harga) VALUES ('cuci', '$idMitra', '$cuci')";
        $query_setrika = "INSERT INTO harga (jenis, id_mitra, harga) VALUES ('setrika', '$idMitra', '$setrika')";
        $query_komplit = "INSERT INTO harga (jenis, id_mitra, harga) VALUES ('komplit', '$idMitra', '$komplit')";

        mysqli_query($connect, $query_cuci);
        mysqli_query($connect, $query_setrika);
        mysqli_query($connect, $query_komplit);

        return mysqli_affected_rows($connect) > 0;
    }

    // Panggil fungsi untuk menyimpan harga
    if (dataHarga($_POST)) {
        // 3. JIKA SUKSES: Simpan pesan ke session dan redirect
        $_SESSION['pesan_sukses'] = "Pendaftaran Selesai! Harga layanan Anda telah berhasil disimpan.";
        header("Location: status.php");
        exit;
    } else {
        // 4. JIKA GAGAL: Simpan pesan error ke session dan redirect kembali ke halaman ini
        $_SESSION['pesan_error'] = "Terjadi kesalahan saat menyimpan harga. " . ($_SESSION['pesan_error'] ?? '');
        header("Location: registrasi-mitra-harga.php");
        exit;
    }
}

// 5. Cek 'flash message' error jika terjadi redirect dari proses gagal di atas
$pesan_error_submit = null;
if (isset($_SESSION['pesan_error'])) {
    $pesan_error_submit = $_SESSION['pesan_error'];
    unset($_SESSION['pesan_error']);
}

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
// 6. Tampilkan popup sesuai dengan pesan yang ada
if ($pesan_sukses_awal) {
    // Popup selamat datang saat pertama kali tiba di halaman ini
    echo "<script>Swal.fire('Berhasil!', '" . addslashes($pesan_sukses_awal) . "', 'success');</script>";
}
if ($pesan_error_submit) {
    // Popup error jika submit gagal dan halaman di-reload
    echo "<script>Swal.fire('Gagal', '" . addslashes($pesan_error_submit) . "', 'error');</script>";
}
?>
</body>
</html>