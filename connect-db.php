<?php

$username = "root";
$passwordDB = "";
$server = "localhost";
$db_name = "laundryhub";

$connect = mysqli_connect($server, $username, $passwordDB, $db_name);

// Tambahan: Cek jika koneksi gagal
if (!$connect) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

?>