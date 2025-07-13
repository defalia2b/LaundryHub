<?php

session_start();
include 'connect-db.php';
include 'functions/functions.php';

cekMitra();

$idMitra = $_SESSION["mitra"];

if ( isset($_POST["submit"]) ){

    function dataHarga($data){
        global $connect, $idMitra;

        $cuci = htmlspecialchars($data["cuci"]);
        $setrika = htmlspecialchars($data["setrika"]);
        $komplit = htmlspecialchars($data["komplit"]);

        validasiHarga($cuci);
        validasiHarga($setrika);
        validasiHarga($komplit);

        $query2 = "INSERT INTO harga VALUES ('', 'cuci', '$idMitra', '$cuci')";
        $query3 = "INSERT INTO harga VALUES ('', 'setrika', '$idMitra', '$setrika')";
        $query4 = "INSERT INTO harga VALUES ('', 'komplit', '$idMitra', '$komplit')";

        mysqli_query($connect, $query2);
        mysqli_query($connect, $query3);
        mysqli_query($connect, $query4);

        return mysqli_affected_rows($connect);
    }

    if (registrasi($_POST) > 0) {
        // Kode untuk mengambil data mitra yang baru mendaftar
        $email = $_POST['email'];
        $query  = "SELECT * FROM mitra WHERE email = '$email'";
        $result = mysqli_query($connect, $query);
        $mitra = mysqli_fetch_assoc($result);

        // Membuat session untuk mitra
        $_SESSION["mitra"] = $mitra["id_mitra"];
        $_SESSION["login-mitra"] = true;

        // Menampilkan notifikasi sukses dan mengarahkan ke halaman pengaturan harga
        echo "
        <script>
            Swal.fire('Pendaftaran Mitra Berhasil','Anda akan diarahkan untuk mengisi harga layanan.','success').then(function(){
                window.location = 'registrasi-mitra-harga.php';
            });
        </script>
    ";
    } else {
        // Menampilkan error jika pendaftaran gagal
        echo mysqli_error($connect);
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "headtags.html"; ?>
    <title>Langkah 2: Atur Harga Layanan</title>
</head>
<body>

<?php include 'header.php' ?>
<main class="main-content">
    <div class="row">
        <div class="col s6 offset-s3">
            <h3 class="header light center">Atur Harga Layanan Anda (per Kg)</h3>
            <div class="card-panel">
                <form action="" method="post">
                    <div class="input-field inline">
                        <ul>
                            <li>
                                <label for="cuci">Cuci Saja (Rp)</label>
                                <input type="number" name="cuci" value="0" required>
                            </li>
                            <li>
                                <label for="setrika">Setrika Saja (Rp)</label>
                                <input type="number" name="setrika" value="0" required>
                            </li>
                            <li>
                                <label for="komplit">Cuci + Setrika (Rp)</label>
                                <input type="number" name="komplit" value="0" required>
                            </li>
                            <li>
                                <div class="center">
                                    <button class="btn-large blue darken-2" type="submit" name="submit">Selesaikan Pendaftaran</button>
                                </div>
                            </li>
                        </ul>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>
</body>
</html>