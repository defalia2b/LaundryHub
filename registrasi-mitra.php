<?php
session_start();
include 'connect-db.php';
include 'functions/functions.php';

cekLogin();

if (isset($_POST["daftar"])) {

    function registrasi($data) {
        global $connect;

        $namaLaundry = htmlspecialchars($data["namaLaundry"]);
        $namaPemilik = htmlspecialchars($data["namaPemilik"]);
        $email = htmlspecialchars($data["email"]);
        $telp = htmlspecialchars($data["telp"]);
        $kota = htmlspecialchars($data["kota"]);
        $alamat = htmlspecialchars($data["alamat"]);
        $latitude = floatval($data["latitude"]);
        $longitude = floatval($data["longitude"]);
        $password = htmlspecialchars($data["password"]);
        $password2 = htmlspecialchars($data["password2"]);

        $result = mysqli_query($connect, "SELECT email FROM mitra WHERE email = '$email'");
        if (mysqli_fetch_assoc($result)) {
            echo "<script>Swal.fire('Pendaftaran Gagal','Email sudah terdaftar','error');</script>";
            return false;
        }

        if ($password != $password2) {
            echo "<script>Swal.fire('Pendaftaran Gagal','Password tidak sama','error');</script>";
            return false;
        }

        if (empty($latitude) || empty($longitude)) {
            echo "<script>Swal.fire('Pendaftaran Gagal','Silakan pilih lokasi usaha Anda di peta','error');</script>";
            return false;
        }

        $query = "INSERT INTO mitra (nama_laundry, nama_pemilik, telp, email, kota, alamat, latitude, longitude, foto, password) 
                  VALUES ('$namaLaundry', '$namaPemilik', '$telp', '$email', '$kota', '$alamat', '$latitude', '$longitude', 'default.png', '$password')";

        mysqli_query($connect, $query);
        return mysqli_affected_rows($connect);
    }

    if (registrasi($_POST) > 0) {
        $email = $_POST['email'];
        $query  = "SELECT * FROM mitra WHERE email = '$email'";
        $result = mysqli_query($connect, $query);
        $mitra = mysqli_fetch_assoc($result);

        $_SESSION["mitra"] = $mitra["id_mitra"];
        $_SESSION["login-mitra"] = true;

        echo "
            <script>
                Swal.fire('Pendaftaran Mitra Berhasil','Anda akan diarahkan untuk mengisi harga layanan.','success').then(function(){
                    window.location = 'registrasi-mitra-harga.php';
                });
            </script>
        ";
    } else {
        echo mysqli_error($connect);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "headtags.html" ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <style> #map { height: 400px; } </style>
    <title>Registrasi Mitra - LaundryHub</title>
</head>
<body>
<?php include 'header.php'; ?>

<main class="main-content">

    <div class="row">
        <div class="col s8 offset-s2">
            <h3 class="header light center">DAFTAR SEBAGAI MITRA</h3>
            <form action="" method="post">
                <div class="input-field inline">
                    <ul>
                        <li><label for="namaLaundry">Nama Laundry</label><input type="text" id="namaLaundry" name="namaLaundry" required></li>
                        <li><label for="namaPemilik">Nama Pemilik</label><input type="text" id="namaPemilik" name="namaPemilik" required></li>
                        <li><label for="telp">No. Telepon</label><input type="tel" id="telp" name="telp" required></li>
                        <li><label for="email">E-mail</label><input type="email" id="email" name="email" required></li>
                        <li><label for="kota">Kota / Kabupaten</label><input type="text" id="kota" name="kota" required></li>
                        <li><label for="alamat">Alamat Lengkap</label><textarea class="materialize-textarea" id="alamat" name="alamat" required></textarea></li>

                        <li>
                            <label>Pilih Lokasi di Peta</label>
                            <div id="map"></div>
                        </li>

                        <li><label for="latitude">Latitude</label><input type="text" id="latitude" name="latitude" readonly required></li>
                        <li><label for="longitude">Longitude</label><input type="text" id="longitude" name="longitude" readonly required></li>

                        <li><label for="password">Password</label><input type="password" name="password" required></li>
                        <li><label for="repassword">Konfirmasi Password</label><input type="password" id="repassword" name="password2" required></li>
                        <li><div class="center"><button class='btn-large blue darken-2' type='submit' name='daftar'>Daftar & Lanjut</button></div></li>
                    </ul>
                </div>
            </form>
        </div>
    </div>
</main>

<?php include "footer.php"; ?>
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script>
    var map = L.map('map').setView([-6.200000, 106.816666], 13); // Default view: Jakarta
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    var marker;
    map.on('click', function(e) {
        if (marker) {
            map.removeLayer(marker);
        }
        marker = L.marker(e.latlng).addTo(map);
        document.getElementById('latitude').value = e.latlng.lat;
        document.getElementById('longitude').value = e.latlng.lng;
        M.updateTextFields();
    });
</script>
</body>
</html>