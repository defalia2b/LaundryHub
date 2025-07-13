<?php
session_start();
include 'connect-db.php';
include 'functions/functions.php';

cekPelanggan();
$idPelanggan = $_SESSION["pelanggan"];

// 1. PROSES PENYIMPANAN DATA (Backend logic remains the same)
if (isset($_POST["ubah-data"])) {
    // ... (Fungsi uploadFoto dan validasi tidak diubah)
    function uploadFoto($current_foto) {
        if (isset($_FILES["foto"]) && $_FILES["foto"]["error"] === 0) {
            $namaFile = $_FILES["foto"]["name"];
            $ukuranFile = $_FILES["foto"]["size"];
            $temp = $_FILES["foto"]["tmp_name"];
            $ekstensiGambarValid = ['jpg', 'jpeg', 'png'];
            $ekstensiGambar = strtolower(end(explode('.', $namaFile)));

            if (!in_array($ekstensiGambar, $ekstensiGambarValid) || $ukuranFile > 3000000) {
                $_SESSION['pesan_error'] = "Upload gagal, format gambar salah atau ukuran > 3MB.";
                return $current_foto;
            }
            $namaFileBaru = 'pelanggan_' . uniqid() . '.' . $ekstensiGambar;
            move_uploaded_file($temp, 'img/pelanggan/' . $namaFileBaru);
            return $namaFileBaru;
        }
        return $current_foto;
    }

    $nama = htmlspecialchars($_POST["nama"]);
    $email = htmlspecialchars($_POST["email"]);
    $telp = htmlspecialchars($_POST["telp"]);
    $alamat = htmlspecialchars($_POST["alamat"]);
    $latitude = floatval($_POST["latitude"]);
    $longitude = floatval($_POST["longitude"]);

    if (validasiNama($nama) && validasiEmail($email) && validasiTelp($telp)) {
        $current_user_q = mysqli_query($connect, "SELECT foto FROM pelanggan WHERE id_pelanggan = '$idPelanggan'");
        $current_user = mysqli_fetch_assoc($current_user_q);
        $foto = uploadFoto($current_user['foto']);

        $query = "UPDATE pelanggan SET nama = '$nama', email = '$email', telp = '$telp', alamat = '$alamat', latitude = '$latitude', longitude = '$longitude', foto = '$foto' WHERE id_pelanggan = $idPelanggan";
        mysqli_query($connect, $query);

        if (mysqli_affected_rows($connect) > 0) {
            $_SESSION['pesan_sukses'] = "Data profil berhasil diperbarui.";
        } else {
            if (!isset($_SESSION['pesan_error'])) {
                $_SESSION['pesan_info'] = "Tidak ada data yang diubah.";
            }
        }
    }
    header("Location: pelanggan.php");
    exit;
}

// 2. AMBIL DATA DARI DATABASE
$data_db = mysqli_query($connect, "SELECT * FROM pelanggan WHERE id_pelanggan = '$idPelanggan'");
$data = mysqli_fetch_assoc($data_db);

// 3. CEK 'FLASH MESSAGE'
$pesan_sukses = $_SESSION['pesan_sukses'] ?? null; unset($_SESSION['pesan_sukses']);
$pesan_info = $_SESSION['pesan_info'] ?? null; unset($_SESSION['pesan_info']);
$pesan_error = $_SESSION['pesan_error'] ?? null; unset($_SESSION['pesan_error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "headtags.html"; ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <title>Profil Saya - <?= htmlspecialchars($data["nama"]) ?></title>
</head>
<body>
<?php include 'header.php'; ?>

<main class="main-content">
    <div class="container">
        <h3 class="header light center">Profil Akun Saya</h3>
        <div class="card-panel center-card">
            <form action="" method="post" enctype="multipart/form-data">
                <div class="center">
                    <img src="img/pelanggan/<?= htmlspecialchars($data['foto'] ?? 'default.png') ?>" alt="Foto Profil" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 4px solid var(--primary-blue);">
                </div>
                <div class="file-field input-field">
                    <div class="btn">
                        <span>Ganti Foto</span>
                        <input type="file" name="foto" id="foto">
                    </div>
                    <div class="file-path-wrapper">
                        <input class="file-path validate" type="text" placeholder="Pilih foto profil baru (opsional)">
                    </div>
                </div>

                <div class="input-field"><i class="material-icons prefix">account_circle</i><input type="text" id="nama" name="nama" value="<?= htmlspecialchars($data['nama'] ?? '') ?>" required><label for="nama">Nama</label></div>
                <div class="input-field"><i class="material-icons prefix">email</i><input type="email" id="email" name="email" value="<?= htmlspecialchars($data['email'] ?? '') ?>" required><label for="email">Email</label></div>
                <div class="input-field"><i class="material-icons prefix">phone</i><input type="tel" id="telp" name="telp" value="<?= htmlspecialchars($data['telp'] ?? '') ?>" required><label for="telp">No Telp</label></div>

                <label>Klik Peta untuk Memperbarui Alamat Anda:</label>
                <div id="map" style="margin-top: 10px;"></div>

                <input type="hidden" name="latitude" id="latitude" value="<?= htmlspecialchars($data['latitude'] ?? '') ?>">
                <input type="hidden" name="longitude" id="longitude" value="<?= htmlspecialchars($data['longitude'] ?? '') ?>">

                <div class="input-field"><i class="material-icons prefix">location_on</i><textarea class="materialize-textarea" id="alamat" name="alamat" readonly required><?= htmlspecialchars($data['alamat'] ?? '') ?></textarea><label for="alamat">Alamat Lengkap (Otomatis dari Peta)</label></div>

                <div class="center" style="margin-top: 20px;">
                    <button class="btn-large waves-effect waves-light" type="submit" name="ubah-data" style="width: 100%;">Simpan Perubahan</button>
                </div>
                <div class="center" style="margin-top: 15px;">
                    <a class="btn waves-effect waves-light red" href="ganti-kata-sandi.php">Ganti Kata Sandi</a>
                </div>
            </form>
        </div>
    </div>
</main>

<?php include "footer.php"; ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // JS untuk peta tidak berubah, hanya memastikan inisialisasi
    document.addEventListener('DOMContentLoaded', function() {
        var currentLat = <?= !empty($data['latitude']) ? $data['latitude'] : '-6.200000' ?>;
        var currentLng = <?= !empty($data['longitude']) ? $data['longitude'] : '106.816666' ?>;
        var map = L.map('map').setView([currentLat, currentLng], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        var marker;
        if (<?= !empty($data['latitude']) ? 'true' : 'false' ?>) {
            marker = L.marker([currentLat, currentLng]).addTo(map);
        }

        map.on('click', function(e) {
            var lat = e.latlng.lat; var lon = e.latlng.lng;
            if (marker) { marker.setLatLng(e.latlng); }
            else { marker = L.marker(e.latlng).addTo(map); }
            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lon;
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
if ($pesan_error) { echo "<script>Swal.fire('Gagal', '" . addslashes($pesan_error) . "', 'error');</script>"; }
?>
</body>
</html>