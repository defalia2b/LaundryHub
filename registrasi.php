<?php

// mulai session
session_start();
include 'connect-db.php';
include 'functions/functions.php';

cekLogin();

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "headtags.html"; ?>
    <title>Registrasi</title>
</head>

<body>
    <!-- header -->
    <?php include 'header.php'; ?>
    <main class="main-content">
        <!-- end header -->

        <h3 class="header light center">Halaman Registrasi</h3>
        <br>

        <!-- body -->
        <div class="container center">
            <a id="download-button" class="btn-large waves-effect waves-light blue darken-3" href="registrasi-pelanggan.php">Registrasi Sebagai Pelanggan</a>
            <a id="download-button" class="btn-large waves-effect waves-light blue darken-3" href="registrasi-mitra.php">Registrasi Sebagai Mitra</a>
        </div>
        <!-- body -->
    </main>

    <!-- footer -->
    <?php include "footer.php"; ?>
    <!-- end footer -->
</body>
</html>