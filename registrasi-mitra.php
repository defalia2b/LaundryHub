<?php
session_start();
include 'connect-db.php';
include 'functions/functions.php';

cekLogin();

$pesan_error = $_SESSION['pesan_error'] ?? null;
unset($_SESSION['pesan_error']);

if (isset($_POST["daftar"])) {

    function registrasi($data)
    {
        global $connect;
        // ... (Fungsi backend dan validasi tidak diubah, hanya desain frontend)
        $namaLaundry = htmlspecialchars($data["namaLaundry"]);
        $namaPemilik = htmlspecialchars($data["namaPemilik"]);
        $email = htmlspecialchars($data["email"]);
        $telp = htmlspecialchars($data["telp"]);
        $alamat = htmlspecialchars($data["alamat"]);
        $latitude = floatval($data["latitude"]);
        $longitude = floatval($data["longitude"]);
        $password = htmlspecialchars($data["password"]);
        $password2 = htmlspecialchars($data["password2"]);

        if (!preg_match("/^[a-zA-Z .'-]+$/", $namaLaundry) || !preg_match("/^[a-zA-Z .'-]+$/", $namaPemilik)) return "Nama Laundry/Pemilik hanya boleh mengandung huruf dan spasi.";
        if (!preg_match("/^[0-9]{10,15}$/", $telp)) return "Nomor Telepon harus 10-15 digit angka.";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return "Format email tidak valid.";
        if (strlen($password) < 6) return "Password minimal harus 6 karakter.";
        if ($password !== $password2) return "Konfirmasi password tidak cocok.";
        if (empty($latitude) || empty($longitude) || empty($alamat)) return "Lokasi usaha belum dipilih dari peta.";

        $result = mysqli_query($connect, "SELECT email FROM mitra WHERE email = '$email'");
        if (mysqli_fetch_assoc($result)) return "Email sudah terdaftar.";

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
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <title>Registrasi Mitra - LaundryHub</title>
</head>
<body>
<?php include 'header.php'; ?>

<main class="main-content">
    <div class="container">
        <div class="row">
            <div class="col s12 m10 l8 offset-m1 offset-l2">
                <div class="card-panel">
                    <h4 class="header light center">Daftar sebagai Mitra</h4>
                    <p class="center light">Langkah 1: Isi informasi dasar mengenai usaha laundry Anda.</p>
                    <form action="" method="post" id="registration-form">
                        <div class="input-field">
                            <i class="material-icons prefix">store</i>
                            <input type="text" id="namaLaundry" name="namaLaundry" required>
                            <label for="namaLaundry">Nama Laundry</label>
                        </div>
                        <div class="input-field">
                            <i class="material-icons prefix">account_circle</i>
                            <input type="text" id="namaPemilik" name="namaPemilik" required>
                            <label for="namaPemilik">Nama Pemilik</label>
                        </div>
                        <div class="input-field">
                            <i class="material-icons prefix">phone</i>
                            <input type="tel" id="telp" name="telp" required pattern="[0-9]{10,15}" title="Nomor telepon harus 10-15 digit.">
                            <label for="telp">No. Telepon Usaha</label>
                        </div>
                        <div class="input-field">
                            <i class="material-icons prefix">email</i>
                            <input type="email" id="email" name="email" required>
                            <label for="email">E-mail</label>
                        </div>

                        <label>Pilih Lokasi Usaha Anda di Peta:</label>
                        <div id="map" style="margin-top:10px; cursor: pointer;"></div>
                        <p class="light" id="map-helper-text">Klik pada peta untuk mengisi alamat, latitude, dan longitude.</p>

                        <input type="hidden" name="latitude" id="latitude">
                        <input type="hidden" name="longitude" id="longitude">

                        <div class="input-field">
                            <i class="material-icons prefix">location_on</i>
                            <textarea class="materialize-textarea" id="alamat" name="alamat" readonly required></textarea>
                            <label for="alamat">Alamat Lengkap (Otomatis dari Peta)</label>
                        </div>

                        <div class="input-field">
                            <i class="material-icons prefix">lock</i>
                            <input type="password" id="password" name="password" required>
                            <label for="password">Password (min. 6 karakter)</label>
                        </div>
                        <div class="input-field">
                            <i class="material-icons prefix">replay</i>
                            <input type="password" id="repassword" name="password2" required>
                            <label for="repassword">Konfirmasi Password</label>
                        </div>
                        <div class="center" style="margin-top: 20px;">
                            <button class='btn-large waves-effect waves-light' type='submit' name='daftar'>Daftar & Lanjut Atur Harga</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include "footer.php"; ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // Javascript untuk peta Leaflet tetap sama
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

        if (marker) { marker.setLatLng(e.latlng); }
        else { marker = L.marker(e.latlng).addTo(map); }

        latitudeInput.value = lat.toFixed(8);
        longitudeInput.value = lon.toFixed(8);
        mapHelperText.innerText = "Mencari alamat...";
        mapHelperText.style.color = "var(--primary-blue)";

        fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lon}`)
            .then(res => res.json())
            .then(data => {
                if (data.display_name) {
                    alamatInput.value = data.display_name;
                    mapHelperText.innerText = "Alamat berhasil ditemukan!";
                    mapHelperText.style.color = "#2ecc71";
                }
                M.updateTextFields();
            })
            .catch(err => {
                mapHelperText.innerText = "Gagal mendapatkan alamat. Coba klik lagi.";
                mapHelperText.style.color = "red";
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