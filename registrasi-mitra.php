<?php
session_start();
include 'connect-db.php';
include 'functions/functions.php';

cekLogin();

$pesan_error = null;
if (isset($_SESSION['pesan_error'])) {
    $pesan_error = $_SESSION['pesan_error'];
    unset($_SESSION['pesan_error']);
}

if (isset($_POST["daftar"])) {

    function registrasi($data)
    {
        global $connect;

        $namaLaundry = htmlspecialchars($data["namaLaundry"]);
        $namaPemilik = htmlspecialchars($data["namaPemilik"]);
        $email = htmlspecialchars($data["email"]);
        $telp = htmlspecialchars($data["telp"]);
        $alamat = htmlspecialchars($data["alamat"]); // Alamat diambil dari form
        $latitude = floatval($data["latitude"]);
        $longitude = floatval($data["longitude"]);
        $password = htmlspecialchars($data["password"]);
        $password2 = htmlspecialchars($data["password2"]);

        // Validasi
        if (!preg_match("/^[a-zA-Z .'-]+$/", $namaLaundry) || !preg_match("/^[a-zA-Z .'-]+$/", $namaPemilik)) {
            return "Nama Laundry/Pemilik hanya boleh mengandung huruf dan spasi.";
        }
        if (!preg_match("/^[0-9]{10,15}$/", $telp)) {
            return "Nomor Telepon harus berupa 10 hingga 15 digit angka.";
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "Format email yang Anda masukkan tidak valid.";
        }
        if ($password !== $password2) {
            return "Konfirmasi password tidak cocok.";
        }
        if (empty($latitude) || empty($longitude) || empty($alamat)) {
            return "Lokasi usaha belum dipilih. Silakan klik peta untuk mengisinya secara otomatis.";
        }
        $result = mysqli_query($connect, "SELECT email FROM mitra WHERE email = '$email'");
        if (mysqli_fetch_assoc($result)) {
            return "Email sudah terdaftar. Silakan gunakan email lain.";
        }

        // Query INSERT tanpa kolom 'kota'
        $query = "INSERT INTO mitra (nama_laundry, nama_pemilik, telp, email, alamat, latitude, longitude, foto, password) 
                  VALUES ('$namaLaundry', '$namaPemilik', '$telp', '$email', '$alamat', '$latitude', '$longitude', 'default.png', '$password')";
        mysqli_query($connect, $query);
        return mysqli_affected_rows($connect) > 0;
    }

    $hasil_registrasi = registrasi($_POST);

    if ($hasil_registrasi === true) {
        $email = $_POST['email'];
        $query  = "SELECT id_mitra FROM mitra WHERE email = '$email'";
        $result = mysqli_query($connect, $query);
        $mitra = mysqli_fetch_assoc($result);

        $_SESSION["mitra"] = $mitra["id_mitra"];
        $_SESSION["login-mitra"] = true;
        $_SESSION['pesan_sukses'] = "Pendaftaran Mitra Berhasil! Sekarang, silakan atur harga layanan Anda.";
        header("Location: registrasi-mitra-harga.php");
        exit;
    } else {
        $_SESSION['pesan_error'] = $hasil_registrasi;
        header("Location: registrasi-mitra.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "headtags.html" ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <style> #map { height: 400px; background-color: #f0f0f0; border-radius: 5px; } </style>
    <title>Registrasi Mitra - LaundryHub</title>
</head>
<body>
<?php include 'header.php'; ?>

<main class="main-content">
    <div class="row">
        <div class="col s12 m10 l8 offset-m1 offset-l2">
            <h3 class="header light center">DAFTAR SEBAGAI MITRA</h3>
            <div class="card-panel">
                <form action="" method="post" id="registration-form">
                    <div class="input-field">
                        <input type="text" id="namaLaundry" name="namaLaundry" required pattern="[a-zA-Z\s.]+" title="Nama hanya boleh mengandung huruf dan spasi.">
                        <label for="namaLaundry">Nama Laundry</label>
                    </div>
                    <div class="input-field">
                        <input type="text" id="namaPemilik" name="namaPemilik" required pattern="[a-zA-Z\s.]+" title="Nama hanya boleh mengandung huruf dan spasi.">
                        <label for="namaPemilik">Nama Pemilik</label>
                    </div>
                    <div class="input-field">
                        <input type="tel" id="telp" name="telp" required pattern="[0-9]{10,15}" title="Nomor telepon harus terdiri dari 10-15 digit angka.">
                        <label for="telp">No. Telepon</label>
                    </div>
                    <div class="input-field">
                        <input type="email" id="email" name="email" required>
                        <label for="email">E-mail</label>
                    </div>

                    <label>Pilih Lokasi Usaha Anda di Peta</label>
                    <div id="map" style="margin-top:10px; cursor: pointer;"></div>
                    <p class="light" id="map-helper-text">Klik pada peta untuk mengisi alamat, latitude, dan longitude secara otomatis.</p>

                    <div class="input-field">
                        <textarea class="materialize-textarea" id="alamat" name="alamat" readonly style="color: #9e9e9e;"></textarea>
                        <label for="alamat">Alamat Lengkap (Otomatis dari Peta)</label>
                    </div>

                    <div class="row" style="margin-bottom: 0;">
                        <div class="input-field col s6">
                            <input type="text" id="latitude" name="latitude" readonly style="color: #9e9e9e;">
                            <label for="latitude">Latitude</label>
                        </div>
                        <div class="input-field col s6">
                            <input type="text" id="longitude" name="longitude" readonly style="color: #9e9e9e;">
                            <label for="longitude">Longitude</label>
                        </div>
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
                        <button class='btn-large blue darken-2 waves-effect waves-light' type='submit' name='daftar'>Daftar & Lanjut</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php include "footer.php"; ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    var map = L.map('map').setView([-6.200000, 106.816666], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    var marker;
    var alamatInput = document.getElementById('alamat');
    var latitudeInput = document.getElementById('latitude');
    var longitudeInput = document.getElementById('longitude');
    var mapHelperText = document.getElementById('map-helper-text');

    map.on('click', function(e) {
        var lat = e.latlng.lat;
        var lon = e.latlng.lng;

        if (marker) {
            marker.setLatLng(e.latlng);
        } else {
            marker = L.marker(e.latlng).addTo(map);
        }

        latitudeInput.value = lat.toFixed(8);
        longitudeInput.value = lon.toFixed(8);
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
</script>

<?php
if ($pesan_error) {
    echo "<script>Swal.fire('Registrasi Gagal', '" . addslashes($pesan_error) . "', 'error');</script>";
}
?>
</body>
</html>