<?php
session_start();
include 'connect-db.php';
include 'functions/functions.php';

cekAdmin();

// Logika untuk menghapus data
if (isset($_GET["hapus"])){
    $idPelanggan = $_GET["hapus"];
    $query = mysqli_query($connect, "DELETE FROM pelanggan WHERE id_pelanggan = '$idPelanggan'");

    if (mysqli_affected_rows($connect) > 0){
        // Menggunakan session untuk flash message agar notifikasi muncul setelah redirect
        $_SESSION['pesan_sukses'] = "Data Pelanggan berhasil dihapus.";
    } else {
        $_SESSION['pesan_error'] = "Gagal menghapus data pelanggan.";
    }
    // Redirect untuk membersihkan URL dan menampilkan notifikasi
    header("Location: list-pelanggan.php");
    exit;
}

// Logika untuk Pagination dan Pencarian
$jumlahDataPerHalaman = 6;
$keyword = $_GET['keyword'] ?? '';

// Query untuk menghitung total data (dengan atau tanpa keyword)
$count_query_str = "SELECT COUNT(*) as total FROM pelanggan";
if (!empty($keyword)) {
    $keyword_safe = mysqli_real_escape_string($connect, $keyword);
    $count_query_str .= " WHERE nama LIKE '%$keyword_safe%' OR email LIKE '%$keyword_safe%' OR alamat LIKE '%$keyword_safe%'";
}
$totalDataQuery = mysqli_query($connect, $count_query_str);
$jumlahData = mysqli_fetch_assoc($totalDataQuery)['total'];
$jumlahHalaman = ceil($jumlahData / $jumlahDataPerHalaman);
$halamanAktif = $_GET["page"] ?? 1;
$awalData = ($jumlahDataPerHalaman * $halamanAktif) - $jumlahDataPerHalaman;

// Query untuk mengambil data per halaman
$query_str = "SELECT * FROM pelanggan";
if (!empty($keyword)) {
    $keyword_safe = mysqli_real_escape_string($connect, $keyword);
    $query_str .= " WHERE nama LIKE '%$keyword_safe%' OR email LIKE '%$keyword_safe%' OR alamat LIKE '%$keyword_safe%'";
}
$query_str .= " ORDER BY id_pelanggan DESC LIMIT $awalData, $jumlahDataPerHalaman";
$pelanggan_list = mysqli_query($connect, $query_str);

// Cek pesan notifikasi dari session
$pesan_sukses = $_SESSION['pesan_sukses'] ?? null; unset($_SESSION['pesan_sukses']);
$pesan_error = $_SESSION['pesan_error'] ?? null; unset($_SESSION['pesan_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "headtags.html"; ?>
    <title>Kelola Data Pelanggan</title>
</head>
<body>

<?php include 'header.php'; ?>
<main class="main-content">
    <div class="container">
        <h3 class="header light center">Kelola Data Pelanggan</h3>
        <div class="card-panel">
            <form class="row valign-wrapper" action="" method="get">
                <div class="input-field col s10">
                    <i class="material-icons prefix">search</i>
                    <input type="text" id="keyword" name="keyword" value="<?= htmlspecialchars($keyword) ?>">
                    <label for="keyword">Cari Pelanggan (Nama, Email, Alamat)</label>
                </div>
                <div class="col s2">
                    <button type="submit" class="btn waves-effect waves-light">Cari</button>
                </div>
            </form>
        </div>

        <div class="row">
            <?php if(mysqli_num_rows($pelanggan_list) > 0): foreach ($pelanggan_list as $dataPelanggan) : ?>
                <div class="col s12 m6 l4">
                    <div class="card">
                        <div class="card-content">
                        <span class="card-title activator grey-text text-darken-4" style="font-size: 1.2rem; font-weight: 500;">
                            <i class="material-icons left">person</i><?= htmlspecialchars($dataPelanggan['nama']) ?>
                        </span>
                            <p><i class="material-icons tiny" style="vertical-align: middle;">email</i> <?= htmlspecialchars($dataPelanggan['email']) ?></p>
                            <p><i class="material-icons tiny" style="vertical-align: middle;">phone</i> <?= htmlspecialchars($dataPelanggan['telp']) ?></p>
                            <p class="truncate"><i class="material-icons tiny" style="vertical-align: middle;">place</i> <?= htmlspecialchars($dataPelanggan['alamat']) ?></p>
                        </div>
                        <div class="card-action">
                            <a href="list-pelanggan.php?hapus=<?= $dataPelanggan['id_pelanggan'] ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus data pelanggan ini?')" class="red-text">Hapus Pelanggan</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; else: ?>
                <p class="center light col s12">Data pelanggan tidak ditemukan.</p>
            <?php endif; ?>
        </div>

        <ul class="pagination center">
            <?php if ($halamanAktif > 1): ?>
                <li class="waves-effect"><a href="?page=<?= $halamanAktif - 1 ?>&keyword=<?= urlencode($keyword) ?>"><i class="material-icons">chevron_left</i></a></li>
            <?php else: ?>
                <li class="disabled"><a href="#!"><i class="material-icons">chevron_left</i></a></li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $jumlahHalaman; $i++): ?>
                <li class="<?= ($i == $halamanAktif) ? 'active' : 'waves-effect' ?>" style="<?= ($i == $halamanAktif) ? 'background-color: var(--primary-blue);' : '' ?>">
                    <a href="?page=<?= $i ?>&keyword=<?= urlencode($keyword) ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <?php if ($halamanAktif < $jumlahHalaman): ?>
                <li class="waves-effect"><a href="?page=<?= $halamanAktif + 1 ?>&keyword=<?= urlencode($keyword) ?>"><i class="material-icons">chevron_right</i></a></li>
            <?php else: ?>
                <li class="disabled"><a href="#!"><i class="material-icons">chevron_right</i></a></li>
            <?php endif; ?>
        </ul>
    </div>
</main>
<?php include "footer.php"; ?>

<?php
if ($pesan_sukses) { echo "<script>Swal.fire('Berhasil', '" . addslashes($pesan_sukses) . "', 'success');</script>"; }
if ($pesan_error) { echo "<script>Swal.fire('Gagal', '" . addslashes($pesan_error) . "', 'error');</script>"; }
?>
</body>
</html>