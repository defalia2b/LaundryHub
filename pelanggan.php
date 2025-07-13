<?php
session_start();
include 'connect-db.php';
include 'functions/functions.php';

// Pastikan hanya pelanggan yang login yang bisa mengakses
cekPelanggan();
$idPelanggan = $_SESSION["pelanggan"];

// 1. PROSES PENYIMPANAN DATA (JIKA FORM DI-SUBMIT)
if (isset($_POST["ubah-data"])) {

    function uploadFoto($current_foto) {
        if (isset($_FILES["foto"]) && $_FILES["foto"]["error"] === 0) {
            $namaFile = $_FILES["foto"]["name"];
            $ukuranFile = $_FILES["foto"]["size"];
            $temp = $_FILES["foto"]["tmp_name"];
            $ekstensiGambarValid = ['jpg', 'jpeg', 'png'];
            $ekstensiGambar = strtolower(end(explode('.', $namaFile)));

            if (!in_array($ekstensiGambar, $ekstensiGambarValid) || $ukuranFile > 3000000) {
                $_SESSION['pesan_error'] = "Upload gagal, format gambar salah atau ukuran terlalu besar.";
                return $current_foto;
            }
            $namaFileBaru = uniqid() . '.' . $ekstensiGambar;
            move_uploaded_file($temp, 'img/pelanggan/' . $namaFileBaru);
            return $namaFileBaru;
        }
        return $current_foto;
    }

    // Ambil data dari form
    $nama = htmlspecialchars($_POST["nama"]);
    $email = htmlspecialchars($_POST["email"]);
    $telp = htmlspecialchars($_POST["telp"]);
    $kota = htmlspecialchars($_POST["kota"]);
    $alamat = htmlspecialchars($_POST["alamat"]);

    // Simpan input ke session agar form tetap terisi jika validasi gagal
    $_SESSION['form_input'] = $_POST;

    // Validasi input
    if (validasiNama($nama) && validasiEmail($email) && validasiTelp($telp)) {
        // Ambil foto saat ini
        $current_user_q = mysqli_query($connect, "SELECT foto FROM pelanggan WHERE id_pelanggan = '$idPelanggan'");
        $current_user = mysqli_fetch_assoc($current_user_q);
        $foto = uploadFoto($current_user['foto']);

        // Query UPDATE
        $query = "UPDATE pelanggan SET
            nama = '$nama',
            email = '$email',
            telp = '$telp',
            kota = '$kota', 
            alamat = '$alamat',
            foto = '$foto'
            WHERE id_pelanggan = $idPelanggan
        ";

        mysqli_query($connect, $query);

        unset($_SESSION['form_input']); // Hapus data form dari session jika berhasil
        if (mysqli_affected_rows($connect) > 0) {
            $_SESSION['pesan_sukses'] = "Data profil berhasil diperbarui.";
        } else {
            // Jika tidak ada error dari validasi tapi tidak ada baris yang berubah
            if (!isset($_SESSION['pesan_error'])) {
                $_SESSION['pesan_info'] = "Tidak ada data yang diubah.";
            }
        }
    }

    // Redirect kembali ke halaman ini untuk menampilkan pesan
    header("Location: pelanggan.php");
    exit;
}

// 2. AMBIL DATA DARI SESSION ATAU DATABASE
if (isset($_SESSION['form_input'])) {
    // Jika ada data form dari percobaan sebelumnya (karena gagal validasi)
    $data = $_SESSION['form_input'];
    unset($_SESSION['form_input']);
    // Ambil foto dari DB karena tidak disimpan di session
    $foto_q = mysqli_query($connect, "SELECT foto FROM pelanggan WHERE id_pelanggan = '$idPelanggan'");
    $data['foto'] = mysqli_fetch_assoc($foto_q)['foto'];
} else {
    // Ambil data dari database jika tidak ada percobaan gagal
    $data_db = mysqli_query($connect, "SELECT * FROM pelanggan WHERE id_pelanggan = '$idPelanggan'");
    $data = mysqli_fetch_assoc($data_db);
}


// 3. CEK 'FLASH MESSAGE' UNTUK NOTIFIKASI
$pesan_sukses = $_SESSION['pesan_sukses'] ?? null;
unset($_SESSION['pesan_sukses']);

$pesan_info = $_SESSION['pesan_info'] ?? null;
unset($_SESSION['pesan_info']);

$pesan_error = $_SESSION['pesan_error'] ?? null;
unset($_SESSION['pesan_error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "headtags.html"; ?>
    <title>Data Pengguna - <?= htmlspecialchars($data["nama"]) ?></title>
</head>
<body>
<?php include 'header.php'; ?>

<main class="main-content">
    <div class="row">
        <div class="col s12 m8 offset-m2">
            <h3 class="header light center">DATA PENGGUNA</h3>
            <div class="card-panel">
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="center">
                        <img src="img/pelanggan/<?= htmlspecialchars($data['foto']) ?>" class="circle responsive-img" width="150px" alt="Foto Profil">
                    </div>
                    <div class="file-field input-field">
                        <div class="btn blue darken-2">
                            <span>Foto Profil</span>
                            <input type="file" name="foto" id="foto">
                        </div>
                        <div class="file-path-wrapper">
                            <input class="file-path validate" type="text" placeholder="Upload foto profil baru">
                        </div>
                    </div>

                    <div class="input-field">
                        <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($data['nama'] ?? '') ?>">
                        <label for="nama">Nama</label>
                    </div>
                    <div class="input-field">
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($data['email'] ?? '') ?>">
                        <label for="email">Email</label>
                    </div>
                    <div class="input-field">
                        <input type="tel" id="telp" name="telp" value="<?= htmlspecialchars($data['telp'] ?? '') ?>">
                        <label for="telp">No Telp</label>
                    </div>
                    <div class="input-field">
                        <input type="text" id="kota" name="kota" value="<?= htmlspecialchars($data['kota'] ?? '') ?>">
                        <label for="kota">Kota / Kabupaten</label>
                    </div>
                    <div class="input-field">
                        <textarea class="materialize-textarea" id="alamat" name="alamat"><?= htmlspecialchars($data['alamat'] ?? '') ?></textarea>
                        <label for="alamat">Alamat Lengkap</label>
                    </div>

                    <div class="center" style="margin-top: 20px;">
                        <button class="btn-large blue darken-2" type="submit" name="ubah-data">Simpan Data</button>
                    </div>
                    <div class="center" style="margin-top: 20px;">
                        <a class="btn red darken-2" href="ganti-kata-sandi.php">Ganti Kata Sandi</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php include "footer.php"; ?>

<?php
// 4. TAMPILKAN POPUP SESUAI PESAN DARI SESSION
if ($pesan_sukses) {
    echo "<script>Swal.fire('Berhasil', '" . addslashes($pesan_sukses) . "', 'success');</script>";
}
if ($pesan_info) {
    echo "<script>Swal.fire('Info', '" . addslashes($pesan_info) . "', 'info');</script>";
}
if ($pesan_error) {
    echo "<script>Swal.fire('Gagal', '" . addslashes($pesan_error) . "', 'error');</script>";
}
?>
</body>
</html>