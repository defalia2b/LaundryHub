<?php
session_start();
include 'connect-db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include 'headtags.html'; ?>
    <title>Syarat dan Ketentuan - LaundryHub</title>
</head>
<body>

<?php include 'header.php'; ?>

<main class="main-content">
    <div class="container">
        <h3 class="header light center">Syarat dan Ketentuan</h3>
        <p class="center light">Aturan penggunaan layanan dan kemitraan di LaundryHub.</p>
        <div class="row">
            <div class="col s12 m10 offset-m1 l8 offset-l2">
                <div class="card-panel">
                    <div class="card-content">
                        <ol style="padding-left: 20px;">
                            <li>Memiliki lokasi usaha laundry yang strategis dan teridentifikasi oleh Google Maps.</li>
                            <li>Mitra memiliki nama usaha serta logo perusahaan agar dapat diposting di website LaundryHub.</li>
                            <li>Mampu memberikan layanan Laundry dengan kualitas prima dan harga yang bersaing.</li>
                            <li>Memiliki driver yang bersedia untuk melakukan penjemputan dan pengantaran terhadap laundry pelanggan.</li>
                            <li>Harga dari jenis laundry ditentukan berdasarkan berat per kilo (kg) ditambah dengan biaya ongkos kirim.</li>
                            <li>Bersedia untuk memberikan informasi kepada pelanggan mengenai harga Laundry Kiloan.</li>
                            <li>Bersedia untuk menerapkan sistem poin kepada pelanggan.</li>
                            <li>Bersedia memberikan kompensasi untuk setiap kemungkinan terjadinya seperti kehilangan pakaian atau kerusakan pakaian pada saat proses Laundry dilakukan.</li>
                            <li>Mitra tidak diperkenankan untuk melakukan kerjasama dengan pihak Laundry lainnya.</li>
                            <li>Sebagai kompensasi atas kerjasama adalah sistem bagi hasil sebesar 5%, yang diperhitungkan dari setiap 7 hari.</li>
                            <li>Status mitra secara otomatis dicabut apabila melanggar kesepakatan yang telah ditetapkan dalam surat perjanjian kerjasama ataupun mitra ingin mengundurkan diri.</li>
                        </ol>
                    </div>
                    <div class="card-action">
                        <a href="index.php">Kembali ke Halaman Utama</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'footer.php' ?>
</body>
</html>