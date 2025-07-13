<?php

// FUNGSI VALIDASI YANG SUDAH DIPERBAIKI

function validasiNama($objek){
    if (empty($objek)){
        $_SESSION['pesan_error'] = "Nama tidak boleh kosong.";
        return false;
    }
    if (!preg_match("/^[a-zA-Z .]*$/", $objek)){
        $_SESSION['pesan_error'] = "Nama hanya boleh mengandung huruf dan spasi.";
        return false;
    }
    return true;
}

function validasiTelp($objek){
    if (empty($objek)){
        $_SESSION['pesan_error'] = "No. Telepon tidak boleh kosong.";
        return false;
    }
    if (!preg_match("/^[0-9]*$/", $objek)){
        $_SESSION['pesan_error'] = "No. Telepon hanya boleh mengandung angka.";
        return false;
    }
    return true;
}

function validasiEmail($objek){
    if (empty($objek)){
        $_SESSION['pesan_error'] = "Email tidak boleh kosong.";
        return false;
    }
    if (!filter_var($objek, FILTER_VALIDATE_EMAIL)){
        $_SESSION['pesan_error'] = "Format email yang Anda masukkan salah.";
        return false;
    }
    return true;
}

function validasiHarga($objek){
    if (empty($objek) && $objek !== '0'){
        $_SESSION['pesan_error'] = "Harga tidak boleh kosong.";
        return false;
    }
    if (!preg_match("/^[0-9]*$/", $objek)){
        $_SESSION['pesan_error'] = "Harga harus dalam format angka.";
        return false;
    }
    return true;
}

// ... (Sisa fungsi lain seperti cekLogin, cekAdmin, dll. tidak perlu diubah) ...
// SESSION

// admin
function cekAdmin(){
    if ( isset($_SESSION["login-admin"]) && isset($_SESSION["admin"]) ){

        $idAdmin = $_SESSION["admin"];

    }else {
        echo "
            <script>
                window.location = 'login.php';
            </script>
        ";
        exit;
    }
}


// mitra
function cekMitra(){
    if (isset($_SESSION["login-mitra"]) && isset($_SESSION["mitra"]) ){

        $idMitra = $_SESSION["mitra"];
    }else {
        echo "
            <script>
                window.location = 'login.php';
            </script>
        ";
        exit;
    }
}


// pengguna
function cekPelanggan(){
    if ( isset($_SESSION["login-pelanggan"]) && isset($_SESSION["pelanggan"]) ){

        $idPelanggan = $_SESSION["pelanggan"];
    }else {
        echo "
            <script>
                window.location = 'login.php';
            </script>
        ";
        exit;
    }
}


// login
function cekLogin(){
    if ( (isset($_SESSION["login-pelanggan"]) && isset($_SESSION["pelanggan"])) || (isset($_SESSION["login-mitra"]) && isset($_SESSION["mitra"])) || (isset($_SESSION["login-admin"]) && isset($_SESSION["admin"])) ) {
        echo "
            <script>
                window.location = 'index.php';
            </script>
        ";
        exit;
    }
}

// belum login
function cekBelumLogin(){
    if ( !(isset($_SESSION["login-pelanggan"]) && isset($_SESSION["pelanggan"])) && !(isset($_SESSION["login-mitra"]) && isset($_SESSION["mitra"])) && !(isset($_SESSION["login-admin"]) && isset($_SESSION["admin"])) ) {
        echo "
            <script>
                window.location = 'login.php';
            </script>
        ";
        exit;
    }
}