<?php
session_start();
include 'connect-db.php';
include 'functions/functions.php';

// Pastikan hanya mitra yang bisa akses
cekMitra();
$idMitra = $_SESSION["mitra"];

// 1. PROSES PENYIMPANAN DATA (JIKA FORM DI-SUBMIT)
if (isset($_POST["simpan"])) {

    // Fungsi upload foto disederhanakan
    function uploadFoto($current_foto) {
        if (isset($_FILES["foto"]) && $_FILES["foto"]["error"] === 0) {
            $namaFile = $_FILES["foto"]["name"];
            $ukuranFile = $_FILES["foto"]["size"];
            $temp = $_FILES["foto"]["tmp_name"];
            $ekstensiGambarValid = ['jpg', 'jpeg', 'png'];
            $ekstensiGambar = strtolower(end(explode('.', $namaFile)));

            if (!in_array($ekstensiGambar, $ekstensiGambarValid) || $ukuranFile > 3000000) {
                return false;
            }
            $namaFileBaru = uniqid() . '.' . $ekstensiGambar;
            move_uploaded_file($temp, 'img/mitra/' . $namaFileBaru);
            return $namaFileBaru;
        }
        return $current_foto;
    }

    // Ambil data dari form
    $namaLaundry = htmlspecialchars($_POST["namaLaundry"]);
    $namaPemilik = htmlspecialchars($_POST["namaPemilik"]);
    $email = htmlspecialchars($_POST["email"]);
    $telp = htmlspecialchars($_POST["telp"]);
    $kota = htmlspecialchars($_POST["kota"]);
    $alamat = htmlspecialchars($_POST["alamat"]);
    $latitude = floatval($_POST["latitude"]);
    $longitude = floatval($_POST["longitude"]);

    // Dapatkan data mitra saat ini untuk foto
    $current_mitra_query = mysqli_query($connect, "SELECT foto FROM mitra WHERE id_mitra = '$idMitra'");
    $current_mitra = mysqli_fetch_assoc($current_mitra_query);
    $foto_baru = uploadFoto($current_mitra['foto']);

    // Query UPDATE
    $update_query = "UPDATE mitra SET
        nama_laundry = '$namaLaundry',
        nama_pemilik = '$namaPemilik',
        email = '$email',
        telp = '$telp',
        kota = '$kota',
        alamat = '$alamat',
        latitude = '$latitude',
        longitude = '$longitude',
        foto = '$foto_baru'
        WHERE id_mitra = $idMitra
    ";

    mysqli_query($connect, $update_query);

    if (mysqli_affected_rows($connect) > 0) {
        $_SESSION['pesan_sukses'] = "Data profil berhasil diperbarui.";
    } else {
        $_SESSION['pesan_info'] = "Tidak ada data yang diubah atau terjadi kesalahan.";
    }

    header("Location: mitra.php");
    exit;
}

// 2. AMBIL DATA MITRA TERBARU DARI DATABASE
$query = "SELECT * FROM mitra WHERE id_mitra = '$idMitra'";
$result = mysqli_query($connect, $query);
$mitra = mysqli_fetch_assoc($result);

// 3. CEK APAKAH ADA 'FLASH MESSAGE' UNTUK DITAMPILKAN
$pesan_sukses = null;
if (isset($_SESSION['pesan_sukses'])) {
    $pesan_sukses = $_SESSION['pesan_sukses'];
    unset($_SESSION['pesan_sukses']);
}

$pesan_info = null;
if (isset($_SESSION['pesan_info'])) {
    $pesan_info = $_SESSION['pesan_info'];
    unset($_SESSION['pesan_info']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include 'headtags.html'; ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <style> #map { height: 300px; } </style>
    <title>Profil Mitra - <?= htmlspecialchars($mitra['nama_laundry']) ?></title>
</head>
<body>
<?php include 'header.php'; ?>

<main class="main-content">
    <div class="container">
        <div class="row">
            <div class="col s12 m8 offset-m2">
                <h3 class="header light center">Profil Usaha Anda</h3>
                <div class="card-panel">
                    <form action="" method="post" enctype="multipart/form-data">
                        <div class="center">
                            <img src="img/mitra/<?= htmlspecialchars($mitra['foto']) ?>" class="circle responsive-img" width="150px" alt="Foto Profil">
                        </div>
                        <div class="file-field input-field">
                            <div class="btn blue darken-2"><span>Ganti Foto</span><input type="file" name="foto" id="foto"></div>
                            <div class="file-path-wrapper"><input class="file-path validate" type="text"></div>
                        </div>

                        <div class="input-field"><label for="namaLaundry">Nama Laundry</label><input type="text" id="namaLaundry" name="namaLaundry" value="<?= htmlspecialchars($mitra['nama_laundry']) ?>"></div>
                        <div class="input-field"><label for="namaPemilik">Nama Pemilik</label><input type="text" id="namaPemilik" name="namaPemilik" value="<?= htmlspecialchars($mitra['nama_pemilik']) ?>"></div>
                        <div class="input-field"><label for="email">Email</label><input type="email" id="email" name="email" value="<?= htmlspecialchars($mitra['email']) ?>"></div>
                        <div class="input-field"><label for="telp">No Telp</label><input type="tel" id="telp" name="telp" value="<?= htmlspecialchars($mitra['telp']) ?>"></div>
                        <div class="input-field"><label for="kota">Kota / Kabupaten</label><input type="text" name="kota" value="<?= htmlspecialchars($mitra['kota']) ?>"></div>
                        <div class="input-field"><label for="alamat">Alamat Lengkap</label><textarea class="materialize-textarea" name="alamat"><?= htmlspecialchars($mitra['alamat']) ?></textarea></div>

                        <label>Klik di Peta untuk Memperbarui Lokasi Anda</label>
                        <div id="map"></div>

                        <div class="input-field"><label for="latitude">Latitude</label><input type="text" id="latitude" name="latitude" value="<?= $mitra['latitude'] ?>" readonly></div>
                        <div class="input-field"><label for="longitude">Longitude</label><input type="text" id="longitude" name="longitude" value="<?= $mitra['longitude'] ?>" readonly></div>

                        <div class="center" style="margin-top: 20px;">
                            <button class="btn-large waves-effect waves-light blue darken-2" type="submit" name="simpan">Simpan Perubahan</button>
                        </div>
                    </form>
                    <div class="center" style="margin-top: 20px;">
                        <a class="btn waves-effect waves-light green" href="edit-harga.php">Ubah Daftar Harga</a>
                        <a class="btn waves-effect waves-light red" href="ganti-kata-sandi.php">Ganti Kata Sandi</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include "footer.php"; ?>

<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // ... (Kode JavaScript untuk Leaflet map tetap sama) ...
        var currentLat = <?= $mitra['latitude'] ?: '-6.200000' ?>;
        var currentLng = <?= $mitra['longitude'] ?: '106.816666' ?>;
        var map = L.map('map').setView([currentLat, currentLng], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        var marker = L.marker([currentLat, currentLng]).addTo(map);

        map.on('click', function(e) {
            if (marker) {
                map.removeLayer(marker);
            }
            marker = L.marker(e.latlng).addTo(map);
            document.getElementById('latitude').value = e.latlng.lat.toFixed(8);
            document.getElementById('longitude').value = e.latlng.lng.toFixed(8);
            M.updateTextFields();
        });
    });
</script>

<?php
// 4. TAMPILKAN POPUP JIKA ADA PESAN DARI SESSION
if ($pesan_sukses) {
    echo "<script>Swal.fire('Berhasil', '" . addslashes($pesan_sukses) . "', 'success');</script>";
}
if ($pesan_info) {
    echo "<script>Swal.fire('Info', '" . addslashes($pesan_info) . "', 'info');</script>";
}
?>
</body>
</html>