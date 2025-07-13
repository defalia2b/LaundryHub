<?php
session_start();
include 'connect-db.php';
include 'functions/functions.php';

// Menghalangi akses jika sudah login
cekLogin();

// Penanganan Pesan Error dari Session
$pesan_error = null;
if (isset($_SESSION['pesan_error'])) {
    $pesan_error = $_SESSION['pesan_error'];
    unset($_SESSION['pesan_error']);
}

// Ketika tombol registrasi di klik
if (isset($_POST["registrasi"])) {

    // Fungsi Registrasi dengan Validasi Detail
    function registrasi($data)
    {
        global $connect;

        $nama = htmlspecialchars($data["nama"]);
        $email = htmlspecialchars($data["email"]);
        $noTelp = htmlspecialchars($data["noTelp"]);
        $alamat = htmlspecialchars($data["alamat"]);
        $latitude = floatval($data["latitude"]);
        $longitude = floatval($data["longitude"]);
        $password = htmlspecialchars($data["password"]);
        $password2 = htmlspecialchars($data["password2"]);

        // --- VALIDASI SISI SERVER ---
        if (!preg_match("/^[a-zA-Z .'-]+$/", $nama)) {
            return "Nama hanya boleh mengandung huruf dan spasi.";
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "Format email yang Anda masukkan tidak valid.";
        }
        if (!preg_match("/^[0-9]{10,15}$/", $noTelp)) {
            return "Nomor Telepon harus berupa 10 hingga 15 digit angka.";
        }
        if (empty($alamat) || empty($latitude) || empty($longitude)) {
            return "Alamat belum ditentukan. Silakan klik peta untuk mengisinya.";
        }
        if ($password !== $password2) {
            return "Konfirmasi password tidak cocok.";
        }

        $result = mysqli_query($connect, "SELECT email FROM pelanggan WHERE email = '$email'");
        if (mysqli_fetch_assoc($result)) {
            return "Email sudah terdaftar. Silakan gunakan email lain.";
        }

        // Query INSERT dengan kolom latitude & longitude
        $query = "INSERT INTO pelanggan (nama, email, telp, alamat, latitude, longitude, foto, password) 
                  VALUES ('$nama', '$email', '$noTelp', '$alamat', '$latitude', '$longitude', 'default.png', '$password')";
        mysqli_query($connect, $query);
        return mysqli_affected_rows($connect) > 0;
    }

    $hasil_registrasi = registrasi($_POST);

    if ($hasil_registrasi === true) {
        $email = $_POST["email"];
        $query = mysqli_query($connect, "SELECT * FROM pelanggan WHERE email = '$email'");
        $pelanggan = mysqli_fetch_assoc($query);

        $_SESSION["pelanggan"] = $pelanggan["id_pelanggan"];
        $_SESSION["login-pelanggan"] = true;

        $_SESSION['pesan_sukses'] = "Pendaftaran Berhasil! Selamat Bergabung dengan LaundryHub.";
        header("Location: index.php");
        exit;

    } else {
        $_SESSION['pesan_error'] = $hasil_registrasi;
        header("Location: registrasi-pelanggan.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "headtags.html"; ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <style>
        #map { height: 400px; cursor: pointer; background-color: #f0f0f0; border-radius: 5px; }
        textarea[readonly] {
            color: #9e9e9e !important;
            border-bottom: 1px dotted #9e9e9e !important;
        }
    </style>
    <title>Registrasi Pelanggan</title>
</head>
<body>

<?php include 'header.php'; ?>
<main class="main-content">
    <div class="row">
        <div class="col s12 m8 l6 offset-m2 offset-l3">
            <h3 class="header light center">Registrasi Pelanggan</h3>
            <div class="card-panel">
                <form action="" method="POST">
                    <div class="input-field">
                        <input type="text" id="nama" name="nama" required pattern="[a-zA-Z\s.]+" title="Nama hanya boleh mengandung huruf dan spasi.">
                        <label for="nama">Nama Lengkap</label>
                    </div>
                    <div class="input-field">
                        <input type="email" id="email" name="email" required>
                        <label for="email">Email</label>
                    </div>
                    <div class="input-field">
                        <input type="tel" id="telp" name="noTelp" required pattern="[0-9]{10,15}" title="Nomor telepon harus terdiri dari 10-15 digit angka.">
                        <label for="telp">No. Telepon</label>
                    </div>

                    <label>Tentukan Alamat Anda di Peta</label>
                    <div id="map" style="margin-top: 10px;"></div>
                    <p class="light" id="map-helper-text">Klik pada peta untuk mengisi alamat Anda secara otomatis.</p>

                    <input type="hidden" name="latitude" id="latitude">
                    <input type="hidden" name="longitude" id="longitude">

                    <div class="input-field">
                        <textarea id="alamat" class="materialize-textarea" name="alamat" readonly></textarea>
                        <label for="alamat">Alamat Lengkap (Otomatis dari Peta)</label>
                    </div>

                    <div class="input-field">
                        <input type="password" id="password" name="password" required>
                        <label for="password">Password</label>
                    </div>
                    <div class="input-field">
                        <input type="password" id="repassword" name="password2" required>
                        <label for="repassword">Konfirmasi Password</label>
                    </div>
                    <div class="center" style="margin-top: 20px;">
                        <button class="btn-large blue darken-3 waves-effect waves-light" type="submit" name="registrasi">Daftar</button>
                    </div>
                </form>
                <div class="center" style="margin-top: 25px;">
                    Ingin menjadi mitra kami?<br/>
                    <a href="registrasi-mitra.php">Daftar sebagai Mitra sekarang!</a>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include "footer.php"; ?>
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var map = L.map('map').setView([-6.200000, 106.816666], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        var marker;
        var alamatInput = document.getElementById('alamat');
        var mapHelperText = document.getElementById('map-helper-text');

        map.on('click', function(e) {
            var lat = e.latlng.lat;
            var lon = e.latlng.lng;

            if (marker) {
                marker.setLatLng(e.latlng);
            } else {
                marker = L.marker(e.latlng).addTo(map);
            }

            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lon;

            mapHelperText.innerText = "Mencari alamat...";

            fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lon}`)
                .then(res => res.json())
                .then(data => {
                    if (data.display_name) {
                        alamatInput.value = data.display_name;
                        mapHelperText.innerText = "Alamat berhasil ditemukan!";
                    }
                    M.updateTextFields();
                })
                .catch(err => {
                    console.error("Gagal fetch alamat:", err);
                    mapHelperText.innerText = "Gagal mendapatkan alamat. Silakan coba klik lagi.";
                    alamatInput.value = "Gagal mengambil alamat otomatis.";
                    M.updateTextFields();
                });
        });
    });
</script>

<?php
if ($pesan_error) {
    echo "<script>Swal.fire('Registrasi Gagal', '" . addslashes($pesan_error) . "', 'error');</script>";
}
?>
</body>
</html>