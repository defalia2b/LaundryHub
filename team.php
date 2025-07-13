<?php

session_start();
include 'connect-db.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "headtags.html"; ?>
    <title>Team LaundryHub</title>
</head>
<body>

<?php include "header.php"; ?>
<main class="main-content">
    <div class="container">
        <h3 class="header light center">Team LaundryHub</h3>
        <br>

        <div class="row team-container">

            <div class="col s12 m4 l2">
                <div class="card">
                    <div class="card-image center">
                        <img src="img/logo.png" style="width: 80%; padding-top: 15px;">
                        <h5 class="header light">Aina Rahma Putri</h5>
                    </div>
                    <div class="card-content">
                        <p>Bertanggung jawab atas analisis kebutuhan sistem dan merancang alur kerja pengguna. </p>
                    </div>
                </div>
            </div>

            <div class="col s12 m4 l2">
                <div class="card">
                    <div class="card-image center">
                        <img src="img/logo.png" style="width: 80%; padding-top: 15px;">
                        <h5 class="header light">Dewi Farah Aulia</h5>
                    </div>
                    <div class="card-content">
                        <p>Mengembangkan logika backend menggunakan PHP dan mengelola database MySQL.</p>
                    </div>
                </div>
            </div>

            <div class="col s12 m4 l2">
                <div class="card">
                    <div class="card-image center">
                        <img src="img/logo.png" style="width: 80%; padding-top: 15px;">
                        <h5 class="header light">Badrus Salam</h5>
                    </div>
                    <div class="card-content">
                        <p>Fokus pada desain antarmuka (UI/UX), memastikan tampilan aplikasi menarik dan profesional. </p>
                    </div>
                </div>
            </div>

            <div class="col s12 m4 l2">
                <div class="card">
                    <div class="card-image center">
                        <img src="img/logo.png" style="width: 80%; padding-top: 15px;">
                        <h5 class="header light">Kaysa Dzikrya</h5>
                    </div>
                    <div class="card-content">
                        <p>Membuat website menjadi interaktif dengan JavaScript dan memastikan integrasi sistem.</p>
                    </div>
                </div>
            </div>

            <div class="col s12 m4 l2">
                <div class="card">
                    <div class="card-image center">
                        <img src="img/logo.png" style="width: 80%; padding-top: 15px;">
                        <h5 class="header light">Maria Karolina</h5>
                    </div>
                    <div class="card-content">
                        <p>Bertugas dalam pengujian sistem untuk memastikan semua fitur berjalan sesuai kebutuhan. </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>
<?php include "footer.php" ?>
</body>
</html>