<?php

session_start();
include 'connect-db.php';
include 'functions/functions.php';

// validasi login
cekAdmin();

//konfirgurasi pagination
$jumlahDataPerHalaman = 5;
$query = mysqli_query($connect,"SELECT * FROM mitra");
$jumlahData = mysqli_num_rows($query);
//ceil() = pembulatan ke atas
$jumlahHalaman = ceil($jumlahData / $jumlahDataPerHalaman);

//menentukan halaman aktif
if ( isset($_GET["page"])){
    $halamanAktif = $_GET["page"];
}else{
    $halamanAktif = 1;
}

//data awal
$awalData = ( $jumlahDataPerHalaman * $halamanAktif ) - $jumlahDataPerHalaman;

//fungsi memasukkan data di db ke array
$mitra = mysqli_query($connect,"SELECT * FROM mitra ORDER BY id_mitra DESC LIMIT $awalData, $jumlahDataPerHalaman");


//ketika tombol cari ditekan
if ( isset($_POST["cari"])) {
    $keyword = htmlspecialchars($_POST["keyword"]);

    $query = "SELECT * FROM mitra WHERE 
        nama_laundry LIKE '%$keyword%' OR
        nama_pemilik LIKE '%$keyword%' OR
        kota LIKE '%$keyword%' OR
        email LIKE '%$keyword%' OR
        alamat LIKE '%$keyword%'
        ORDER BY id_mitra DESC
        LIMIT $awalData, $jumlahDataPerHalaman
        ";

    $mitra = mysqli_query($connect,$query);

    //konfirgurasi pagination
    $jumlahDataPerHalaman = 3;
    $jumlahData = mysqli_num_rows($mitra);
    $jumlahHalaman = ceil($jumlahData / $jumlahDataPerHalaman);

    if ( isset($_GET["page"])){
        $halamanAktif = $_GET["page"];
    }else{
        $halamanAktif = 1;
    }
}
?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <?php include "headtags.html"; ?>
        <title>Data Mitra</title>
    </head>
    <body>

    <?php include 'header.php'; ?>
    <main class="main-content">
        <h3 class="header light center">List Mitra</h3>
        <br>


        <form class="col s12 center" action="" method="post">
            <div class="input-field inline">
                <input type="text" size=40 name="keyword" placeholder="Keyword">
                <button type="submit" class="btn waves-effect blue darken-2" name="cari"><i class="material-icons">send</i></button>
            </div>
        </form>
        <div class="row">
            <div class="col s10 offset-s1">

                <ul class="pagination center">
                    <?php if( $halamanAktif > 1 ) : ?>
                        <li class="disabled-effect blue darken-1">
                            <a href="?page=<?= $halamanAktif - 1; ?>"><i class="material-icons">chevron_left</i></a>
                        </li>
                    <?php endif; ?>
                    <?php for( $i = 1; $i <= $jumlahHalaman; $i++ ) : ?>
                        <?php if( $i == $halamanAktif ) : ?>
                            <li class="active grey"><a href="?page=<?= $i; ?>"><?= $i ?></a></li>
                        <?php else : ?>
                            <li class="waves-effect blue darken-1"><a href="?page=<?= $i; ?>"><?= $i ?></a></li>
                        <?php endif; ?>
                    <?php endfor; ?>
                    <?php if( $halamanAktif < $jumlahHalaman ) : ?>
                        <li class="waves-effect blue darken-1">
                            <a class="page-link" href="?page=<?= $halamanAktif + 1; ?>"><i class="material-icons">chevron_right</i></a>
                        </li>
                    <?php endif; ?>
                </ul>
                <table cellpadding=10 border=1>
                    <tr>
                        <th>ID Mitra</th>
                        <th>Nama Laundry</th>
                        <th>Nama Pemilik</th>
                        <th>No Telp</th>
                        <th>Email</th>
                        <th>Kota</th>
                        <th>Alamat Lengkap</th>
                        <th>Aksi</th>
                    </tr>

                    <?php foreach ($mitra as $dataMitra) : ?>

                        <tr>
                            <td><?= $dataMitra["id_mitra"] ?></td>
                            <td><?= $dataMitra["nama_laundry"] ?></td>
                            <td><?= $dataMitra["nama_pemilik"] ?></td>
                            <td><?= $dataMitra["telp"] ?></td>
                            <td><?= $dataMitra["email"] ?></td>
                            <td><?= $dataMitra["kota"] ?></td>
                            <td><?= $dataMitra["alamat"] ?></td>
                            <td><a class="btn red darken-2" href="list-mitra.php?hapus=<?= $dataMitra['id_mitra'] ?>" onclick="return confirm('Apakah anda yakin ingin menghapus data ?')"><i class="material-icons">delete</i></a></td>
                        </tr>

                    <?php endforeach ?>
                </table>
            </div>
        </div>

        </div>
    </main>

    <?php include "footer.php"; ?>
    </body>
    </html>
<?php

if (isset($_GET["hapus"])){

    $idMitra = $_GET["hapus"];
    $query = mysqli_query($connect, "DELETE FROM mitra WHERE id_mitra = '$idMitra'");

    if ( mysqli_affected_rows($connect) > 0 ){
        echo "
            <script>
                Swal.fire('Data Mitra Berhasil Di Hapus','','success').then(function(){
                    window.location = 'list-mitra.php';
                });
            </script>
        ";
    }
}
?>