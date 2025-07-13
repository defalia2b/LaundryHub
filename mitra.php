<?php
session_start();
include 'connect-db.php';
include 'functions/functions.php';

cekMitra();
$idMitra = $_SESSION["mitra"];

// 1. PROSES PENYIMPANAN DATA (Backend logic remains the same)
if (isset($_POST["simpan"])) {
    // ... (Fungsi uploadFoto dan validasi tidak diubah)
    function uploadFoto($current_foto) {
        if (isset($_FILES["foto"]) && $_FILES["foto"]["error"] === 0) {
            $namaFile = $_FILES["foto"]["name"];
            $ukuranFile = $_FILES["foto"]["size"];
            $temp = $_FILES["foto"]["tmp_name"];
            $ekstensiGambarValid = ['jpg', 'jpeg', 'png'];
            $ekstensiGambar = strtolower(end(explode('.', $namaFile)));

            if (!in_array($ekstensiGambar, $ekstensiGambarValid) || $ukuranFile > 3000000) { return false; }
            $namaFileBaru = 'mitra_' . uniqid() . '.' . $ekstensiGambar;
            move_uploaded_file($temp, 'img/mitra/' . $namaFileBaru);
            return $namaFileBaru;
        }
        return $current_foto;
    }

    $namaLaundry = htmlspecialchars($_POST["namaLaundry"]);
    $namaPemilik = htmlspecialchars($_POST["namaPemilik"]);
    $email = htmlspecialchars($_POST["email"]);
    $telp = htmlspecialchars($_POST["telp"]);
    $alamat = htmlspecialchars($_POST["alamat"]);
    $latitude = floatval($_POST["latitude"]);
    $longitude = floatval($_POST["longitude"]);

    $current_mitra_query = mysqli_query($connect, "SELECT foto FROM mitra WHERE id_mitra = '$idMitra'");
    $current_mitra = mysqli_fetch_assoc($current_mitra_query);
    $foto_baru = uploadFoto($current_mitra['foto']);

    $update_query = "UPDATE mitra SET nama_laundry = '$namaLaundry', nama_pemilik = '$namaPemilik', email = '$email', telp = '$telp', alamat = '$alamat', latitude = '$latitude', longitude = '$longitude', foto = '$foto_baru' WHERE id_mitra = $idMitra";
    mysqli_query($connect, $update_query);

    if (mysqli_affected_rows($connect) > 0) { $_SESSION['pesan_sukses'] = "Data profil berhasil diperbarui."; }
    else { $_SESSION['pesan_info'] = "Tidak ada data yang diubah atau terjadi kesalahan."; }

    header("Location: mitra.php");
    exit;
}

// 2. AMBIL DATA DARI DB
$result = mysqli_query($connect, "SELECT * FROM mitra WHERE id_mitra = '$idMitra'");
$mitra = mysqli_fetch_assoc($result);

// 3. CEK 'FLASH MESSAGE'
$pesan_sukses = $_SESSION['pesan_sukses'] ?? null; unset($_SESSION['pesan_sukses']);
$pesan_info = $_SESSION['pesan_info'] ?? null; unset($_SESSION['pesan_info']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include 'headtags.html'; ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <title>Profil Mitra - <?= htmlspecialchars($mitra['nama_laundry']) ?></title>
</head>
<body>
<?php include 'header.php'; ?>

<main class="main-content">
    <div class="container">
        <h3 class="header light center">Profil Usaha Anda</h3>
        <div class="card-panel center-card">
            <form action="" method="post" enctype="multipart/form-data">
                <div class="center">
                    <img src="img/mitra/<?= htmlspecialchars($mitra['foto']) ?>" alt="Foto Usaha" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 4px solid var(--primary-blue);">
                </div>
                <div class="file-field input-field">
                    <div class="btn"><span>Ganti Foto Usaha</span><input type="file" name="foto" id="foto"></div>
                    <div class="file-path-wrapper"><input class="file-path validate" type="text"></div>
                </div>

                <div class="input-field"><i class="material-icons prefix">store</i><input type="text" id="namaLaundry" name="namaLaundry" value="<?= htmlspecialchars($mitra['nama_laundry']) ?>"><label for="namaLaundry">Nama Laundry</label></div>
                <div class="input-field"><i class="material-icons prefix">account_circle</i><input type="text" id="namaPemilik" name="namaPemilik" value="<?= htmlspecialchars($mitra['nama_pemilik']) ?>"><label for="namaPemilik">Nama Pemilik</label></div>
                <div class="input-field"><i class="material-icons prefix">email</i><input type="email" id="email" name="email" value="<?= htmlspecialchars($mitra['email']) ?>"><label for="email">Email</label></div>
                <div class="input-field"><i class="material-icons prefix">phone</i><input type="tel" id="telp" name="telp" value="<?= htmlspecialchars($mitra['telp']) ?>"><label for="telp">No Telp</label></div>

                <label>Klik Peta untuk Memperbarui Lokasi Usaha:</label>
                <div id="map" style="margin-top: 10px;"></div>

                <input type="hidden" id="latitude" name="latitude" value="<?= $mitra['latitude'] ?>">
                <input type="hidden" id="longitude" name="longitude" value="<?= $mitra['longitude'] ?>">

                <div class="input-field"><i class="material-icons prefix">location_on</i><textarea class="materialize-textarea" name="alamat" id="alamat" readonly><?= htmlspecialchars($mitra['alamat']) ?></textarea><label for="alamat">Alamat Lengkap (Otomatis dari Peta)</label></div>

                <div class="center" style="margin-top: 20px;">
                    <button class="btn-large waves-effect waves-light" type="submit" name="simpan" style="width:100%;">Simpan Perubahan</button>
                </div>
                <div class="center" style="margin-top: 15px; display: flex; gap: 10px; justify-content: center;">
                    <a class="btn waves-effect waves-light green" href="edit-harga.php">Ubah Harga</a>
                    <a class="btn waves-effect waves-light red" href="ganti-kata-sandi.php">Ganti Sandi</a>
                </div>
            </form>
        </div>
    </div>
</main>

<?php include "footer.php"; ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // JS untuk peta tidak berubah
    document.addEventListener('DOMContentLoaded', function () {
        var currentLat = <?= $mitra['latitude'] ?: '-6.200000' ?>;
        var currentLng = <?= $mitra['longitude'] ?: '106.816666' ?>;
        var map = L.map('map').setView([currentLat, currentLng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        var marker = L.marker([currentLat, currentLng]).addTo(map);
        map.on('click', function(e) {
            var lat = e.latlng.lat, lon = e.latlng.lng;
            if (marker) { marker.setLatLng(e.latlng); }
            document.getElementById('latitude').value = lat.toFixed(8);
            document.getElementById('longitude').value = lon.toFixed(8);
            fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lon}`)
                .then(res => res.json()).then(data => {
                if (data.display_name) { document.getElementById('alamat').value = data.display_name; }
                M.updateTextFields();
            });
        });
    });
</script>

<?php
if ($pesan_sukses) { echo "<script>Swal.fire('Berhasil', '" . addslashes($pesan_sukses) . "', 'success');</script>"; }
if ($pesan_info) { echo "<script>Swal.fire('Info', '" . addslashes($pesan_info) . "', 'info');</script>"; }
?>
</body>
</html>