<?php
session_start();
include 'connect-db.php';
include 'functions/functions.php';

// Ambil pesan notifikasi dari session jika ada
$pesan_sukses = null;
if (isset($_SESSION['pesan_sukses'])) {
    $pesan_sukses = $_SESSION['pesan_sukses'];
    unset($_SESSION['pesan_sukses']);
}

// Ambil data lokasi pelanggan jika login
$pelanggan_lat = null;
$pelanggan_lon = null;
$pelanggan_alamat = null;
if (isset($_SESSION['login-pelanggan'])) {
    $idPelanggan = $_SESSION['pelanggan'];
    $queryPelanggan = mysqli_query($connect, "SELECT latitude, longitude, alamat FROM pelanggan WHERE id_pelanggan = '$idPelanggan'");
    if($dataPelanggan = mysqli_fetch_assoc($queryPelanggan)) {
        if (!empty($dataPelanggan['latitude']) && !empty($dataPelanggan['longitude'])) {
            $pelanggan_lat = $dataPelanggan['latitude'];
            $pelanggan_lon = $dataPelanggan['longitude'];
            $pelanggan_alamat = $dataPelanggan['alamat'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LaundryHub - Temukan Laundry Terbaik di Sekitarmu</title>
    <?php include 'headtags.html' ?>
    <style>
        .loader {
            border: 8px solid #f3f3f3; border-radius: 50%;
            border-top: 8px solid #3498db; width: 60px; height: 60px;
            animation: spin 2s linear infinite; margin: 20px auto; display: none;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<main class="main-content">
    <div class="container">
        <br>
        <h1 class="header center orange-text"><img src="img/laundryhub_logo_transparent.png" width="70%" alt="LaundryHub Banner"></h1>
        <div class="row center">
            <h5 class="header col s12 light">Solusi laundry praktis, langsung dari mitra di dekat Anda.</h5>
        </div>

        <div class="row center">
            <div class="button-container" style="display: flex; justify-content: center; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 15px;">
                <?php
                if (isset($_SESSION["login-pelanggan"])) :
                    ?>
                    <a href="status.php" class="btn-large waves-effect waves-light green">
                        <i class="material-icons left">receipt</i>Lihat Status Pesanan
                    </a>
                <?php endif; ?>
                <button id="find-nearby-btn" class="btn-large waves-effect waves-light blue darken-3">
                    <i class="material-icons left">my_location</i>Cari Laundry Terdekat
                </button>
            </div>
            <p id="location-status" class="light"></p>
        </div>

        <div id="mitra-list-container">
            <div class="loader" id="loading-spinner"></div>
        </div>
    </div>
</main>

<?php include "footer.php" ?>

<?php
if ($pesan_sukses) {
    echo "<script>Swal.fire('Berhasil!', '" . addslashes($pesan_sukses) . "', 'success');</script>";
}
?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const findBtn = document.getElementById('find-nearby-btn');
        const statusP = document.getElementById('location-status');
        const mitraContainer = document.getElementById('mitra-list-container');
        const spinner = document.getElementById('loading-spinner');

        // Ambil data dari PHP
        const savedLat = <?= json_encode($pelanggan_lat) ?>;
        const savedLon = <?= json_encode($pelanggan_lon) ?>;
        const savedAlamat = <?= json_encode($pelanggan_alamat) ?>;

        findBtn.addEventListener('click', function() {
            spinner.style.display = 'block';
            mitraContainer.innerHTML = '';

            if (savedLat && savedLon && savedAlamat) {
                // Kasus 1: Pelanggan login dengan alamat tersimpan
                statusP.innerHTML = `Menampilkan laundry di sekitar alamat:<br><strong>${savedAlamat}</strong>`;
                fetchNearbyLaundries(savedLat, savedLon);
            } else {
                // Kasus 2: Tidak login atau tidak punya alamat, pakai GPS
                statusP.textContent = 'Mendeteksi lokasi Anda via GPS...';
                if (!navigator.geolocation) {
                    spinner.style.display = 'none';
                    statusP.textContent = 'Browser Anda tidak mendukung Geolocation.';
                    return;
                }
                navigator.geolocation.getCurrentPosition(showResultsFromGps, gpsError);
            }
        });

        function showResultsFromGps(position) {
            const latitude = position.coords.latitude;
            const longitude = position.coords.longitude;

            // Pertama, dapatkan nama alamat dari koordinat GPS
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}`)
                .then(res => res.json())
                .then(addressData => {
                    const detailedAddress = addressData.display_name || 'Lokasi Terdeteksi';
                    statusP.innerHTML = `Menampilkan laundry di sekitar alamat:<br><strong>${detailedAddress}</strong>`;
                })
                .catch(err => {
                    console.error('Gagal mendapatkan nama alamat:', err);
                    statusP.innerHTML = `Menampilkan hasil untuk lokasi terdeteksi...`;
                })
                .finally(() => {
                    // Setelah alamat ditampilkan, baru cari data laundry
                    fetchNearbyLaundries(latitude, longitude);
                });
        }

        function fetchNearbyLaundries(latitude, longitude) {
            fetch(`ajax/find_nearby.php?lat=${latitude}&lon=${longitude}`)
                .then(response => response.text())
                .then(data => {
                    spinner.style.display = 'none';
                    mitraContainer.innerHTML = data;
                })
                .catch(err => {
                    spinner.style.display = 'none';
                    statusP.textContent = 'Gagal memuat data laundry.';
                    console.error('Error:', err);
                });
        }

        function gpsError() {
            spinner.style.display = 'none';
            statusP.textContent = 'Gagal mendapatkan lokasi GPS. Pastikan Anda mengizinkan akses lokasi.';
        }
    });
</script>
</body>
</html>