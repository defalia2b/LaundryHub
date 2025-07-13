<?php
session_start();
include 'connect-db.php';
include 'functions/functions.php';

// --- BAGIAN BARU: Ambil pesan notifikasi dari session jika ada ---
$pesan_sukses = null;
if (isset($_SESSION['pesan_sukses'])) {
    $pesan_sukses = $_SESSION['pesan_sukses'];
    unset($_SESSION['pesan_sukses']); // Hapus pesan setelah diambil
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
            <?php
            if (isset($_SESSION["login-pelanggan"])) :
                ?>
                <a href="status.php" class="btn-large waves-effect waves-light green" style="margin-bottom: 15px;">
                    <i class="material-icons left">receipt</i>Lihat Status Pesanan
                </a>
            <?php endif; ?>
            <button id="find-nearby-btn" class="btn-large waves-effect waves-light blue darken-3">
                <i class="material-icons left">my_location</i>Cari Laundry Terdekat
            </button>
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

        findBtn.addEventListener('click', function() {
            if (!navigator.geolocation) {
                statusP.textContent = 'Browser Anda tidak mendukung Geolocation.';
                return;
            }
            statusP.textContent = 'Mendeteksi lokasi Anda...';
            spinner.style.display = 'block';
            mitraContainer.innerHTML = '';
            navigator.geolocation.getCurrentPosition(success, error);
        });

        function success(position) {
            const latitude = position.coords.latitude;
            const longitude = position.coords.longitude;

            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}`)
                .then(res => res.json())
                .then(addressData => {
                    const detailedAddress = addressData.display_name || 'Lokasi Anda';
                    statusP.innerHTML = `Lokasi Anda saat ini:<br><strong>${detailedAddress}</strong><br>Mencari laundry di sekitar...`;
                })
                .catch(err => {
                    console.error('Gagal mendapatkan nama alamat:', err);
                    statusP.textContent = 'Lokasi ditemukan! Mencari laundry di sekitar...';
                })
                .finally(() => {
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
                });
        }

        function error() {
            spinner.style.display = 'none';
            statusP.textContent = 'Gagal mendapatkan lokasi. Pastikan Anda mengizinkan akses lokasi di browser Anda.';
        }
    });
</script>
</body>
</html>