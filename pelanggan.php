<?php
session_start();
include 'connect-db.php';
include 'functions/functions.php';

// Pastikan hanya pelanggan yang login yang bisa mengakses
cekPelanggan();
$idPelanggan = $_SESSION["pelanggan"];

// 1. PROSES PENYIMPANAN DATA (JIKA FORM DI-SUBMIT)
if (isset($_POST["ubah-data"])) {

    function uploadFoto($current_foto) {
        if (isset($_FILES["foto"]) && $_FILES["foto"]["error"] === 0) {
            $namaFile = $_FILES["foto"]["name"];
            $ukuranFile = $_FILES["foto"]["size"];
            $temp = $_FILES["foto"]["tmp_name"];
            $ekstensiGambarValid = ['jpg', 'jpeg', 'png'];
            $ekstensiGambar = strtolower(end(explode('.', $namaFile)));

            if (!in_array($ekstensiGambar, $ekstensiGambarValid) || $ukuranFile > 3000000) {
                $_SESSION['pesan_error'] = "Upload gagal, format gambar salah atau ukuran terlalu besar.";
                return $current_foto;
            }
            $namaFileBaru = uniqid() . '.' . $ekstensiGambar;
            move_uploaded_file($temp, 'img/pelanggan/' . $namaFileBaru);
            return $namaFileBaru;
        }
        return $current_foto;
    }

    $nama = htmlspecialchars($_POST["nama"]);
    $email = htmlspecialchars($_POST["email"]);
    $telp = htmlspecialchars($_POST["telp"]);
    $alamat = htmlspecialchars($_POST["alamat"]); // Alamat diambil dari form yang dikontrol peta

    $_SESSION['form_input'] = $_POST;

    if (validasiNama($nama) && validasiEmail($email) && validasiTelp($telp)) {
        $current_user_q = mysqli_query($connect, "SELECT foto FROM pelanggan WHERE id_pelanggan = '$idPelanggan'");
        $current_user = mysqli_fetch_assoc($current_user_q);
        $foto = uploadFoto($current_user['foto']);

        $query = "UPDATE pelanggan SET
            nama = '$nama',
            email = '$email',
            telp = '$telp',
            alamat = '$alamat',
            foto = '$foto'
            WHERE id_pelanggan = $idPelanggan
        ";
        mysqli_query($connect, $query);

        unset($_SESSION['form_input']);
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


// 3. CEK 'FLASH MESSAGE' UNTUK NOTIFIKASI
$pesan_sukses = $_SESSION['pesan_sukses'] ?? null;
unset($_SESSION['pesan_sukses']);
$pesan_info = $_SESSION['pesan_info'] ?? null;
unset($_SESSION['pesan_info']);
$pesan_error = $_SESSION['pesan_error'] ?? null;
unset($_SESSION['pesan_error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "headtags.html"; ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <style>
        #map { height: 300px; cursor: pointer; }
        textarea[readonly] {
            color: #9e9e9e !important;
            border-bottom: 1px dotted #9e9e9e !important;
        }
    </style>
    <title>Data Pengguna - <?= htmlspecialchars($data["nama"]) ?></title>
</head>
<body>
<?php include 'header.php'; ?>

<main class="main-content">
    <div class="row">
        <div class="col s12 m8 offset-m2">
            <h3 class="header light center">DATA PENGGUNA</h3>
            <div class="card-panel">
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="center">
                        <img src="img/pelanggan/<?= htmlspecialchars($data['foto']) ?>" class="circle responsive-img" width="150px" alt="Foto Profil">
                    </div>
                    <div class="file-field input-field">
                        <div class="btn blue darken-2">
                            <span>Ganti Foto Profil</span>
                            <input type="file" name="foto" id="foto">
                        </div>
                        <div class="file-path-wrapper">
                            <input class="file-path validate" type="text">
                        </div>
                    </div>

                    <div class="input-field">
                        <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($data['nama'] ?? '') ?>">
                        <label for="nama">Nama</label>
                    </div>
                    <div class="input-field">
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($data['email'] ?? '') ?>">
                        <label for="email">Email</label>
                    </div>
                    <div class="input-field">
                        <input type="tel" id="telp" name="telp" value="<?= htmlspecialchars($data['telp'] ?? '') ?>">
                        <label for="telp">No Telp</label>
                    </div>

                    <label>Klik di Peta untuk Memperbarui Alamat Anda</label>
                    <div id="map" style="margin-top: 10px;"></div>

                    <div class="input-field">
                        <textarea class="materialize-textarea" id="alamat" name="alamat" readonly><?= htmlspecialchars($data['alamat'] ?? '') ?></textarea>
                        <label for="alamat">Alamat Lengkap (Ubah via Peta)</label>
                    </div>

                    <div class="center" style="margin-top: 20px;">
                        <button class="btn-large blue darken-2" type="submit" name="ubah-data">Simpan Data</button>
                    </div>
                    <div class="center" style="margin-top: 20px;">
                        <a class="btn red darken-2" href="ganti-kata-sandi.php">Ganti Kata Sandi</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php include "footer.php"; ?>
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Set peta ke lokasi default (Jakarta)
        var map = L.map('map').setView([-6.200000, 106.816666], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        var marker;

        map.on('click', function(e) {
            var lat = e.latlng.lat;
            var lon = e.latlng.lng;

            if (marker) {
                marker.setLatLng(e.latlng);
            } else {
                marker = L.marker(e.latlng).addTo(map);
            }

            fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lon}`)
                .then(res => res.json())
                .then(data => {
                    if (data.display_name) {
                        document.getElementById('alamat').value = data.display_name;
                    }
                    M.updateTextFields();
                });
        });

        // Update tampilan label saat halaman dimuat
        M.updateTextFields();
    });
</script>

<?php
// TAMPILKAN POPUP SESUAI PESAN DARI SESSION
if ($pesan_sukses) {
    echo "<script>Swal.fire('Berhasil', '" . addslashes($pesan_sukses) . "', 'success');</script>";
}
if ($pesan_info) {
    echo "<script>Swal.fire('Info', '" . addslashes($pesan_info) . "', 'info');</script>";
}
if ($pesan_error) {
    echo "<script>Swal.fire('Gagal', '" . addslashes($pesan_error) . "', 'error');</script>";
}
?>
</body>
</html>