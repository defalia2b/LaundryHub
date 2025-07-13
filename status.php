<?php
session_start();
// Ambil pesan notifikasi dari session jika ada
$pesan_sukses = $_SESSION['pesan_sukses'] ?? null;
unset($_SESSION['pesan_sukses']);
$pesan_error = $_SESSION['pesan_error'] ?? null;
unset($_SESSION['pesan_error']);

include 'connect-db.php';
include 'functions/functions.php';

// Pastikan ada yang login
cekBelumLogin();

// Tentukan jenis pengguna yang login
$login_type = '';
$user_id = 0;

if (isset($_SESSION["login-mitra"]) && isset($_SESSION["mitra"])) {
    $login_type = "Mitra";
    $user_id = $_SESSION["mitra"];
} else if (isset($_SESSION["login-pelanggan"]) && isset($_SESSION["pelanggan"])) {
    $login_type = "Pelanggan";
    $user_id = $_SESSION["pelanggan"];
} else if (isset($_SESSION["login-admin"]) && isset($_SESSION["admin"])) {
    $login_type = "Admin";
    $user_id = $_SESSION["admin"];
}

// Logika untuk Mitra: Konfirmasi Berat & Update Status
if ($login_type == "Mitra") {
    // Proses saat mitra memasukkan berat aktual
    if (isset($_POST["konfirmasi_berat"])) {
        $id_pesanan = intval($_POST["id_pesanan"]);
        $berat_aktual = floatval($_POST["berat_aktual"]);

        $pesanan_res = mysqli_query($connect, "SELECT p.*, h.harga FROM pesanan p JOIN harga h ON p.id_mitra = h.id_mitra AND p.jenis = h.jenis WHERE p.id_pesanan = $id_pesanan AND p.id_mitra = $user_id");
        if(mysqli_num_rows($pesanan_res) > 0) {
            $pesanan = mysqli_fetch_assoc($pesanan_res);
            $harga_final = $berat_aktual * $pesanan['harga'];

            mysqli_query($connect, "UPDATE pesanan SET berat = '$berat_aktual', harga_final = '$harga_final', status_pesanan = 'Menunggu Pembayaran' WHERE id_pesanan = $id_pesanan");

            $id_pelanggan = $pesanan['id_pelanggan'];
            $tgl_transaksi = date("Y-m-d H:i:s");
            mysqli_query($connect, "INSERT INTO transaksi (id_pesanan, id_mitra, id_pelanggan, tgl_transaksi, total_bayar, status_pembayaran) VALUES ('$id_pesanan', '$user_id', '$id_pelanggan', '$tgl_transaksi', '$harga_final', 'Belum Bayar')");

            $_SESSION['pesan_sukses'] = "Berat telah dikonfirmasi dan tagihan dibuat.";
            header("Location: status.php");
            exit;
        }
    }

    // Proses saat mitra mengubah status pengerjaan
    if (isset($_POST["simpan_status"])) {
        $id_pesanan = intval($_POST["id_pesanan"]);
        $status_baru = htmlspecialchars($_POST["status_pesanan"]);
        mysqli_query($connect, "UPDATE pesanan SET status_pesanan = '$status_baru' WHERE id_pesanan = $id_pesanan AND id_mitra = $user_id");

        $_SESSION['pesan_sukses'] = "Status pesanan telah berhasil diperbarui.";
        header("Location: status.php");
        exit;
    }
}

// Logika untuk simulasi pembayaran Pelanggan
if (isset($_GET['bayar']) && $login_type == "Pelanggan") {
    $id_transaksi = intval($_GET['bayar']);
    $id_pelanggan_session = $_SESSION['pelanggan'];
    $update_query = "UPDATE transaksi SET status_pembayaran = 'Lunas' WHERE id_transaksi = '$id_transaksi' AND id_pelanggan = '$id_pelanggan_session'";
    mysqli_query($connect, $update_query);
    if (mysqli_affected_rows($connect) > 0) {
        $_SESSION['pesan_sukses'] = "Pembayaran untuk transaksi #$id_transaksi telah berhasil!";
    } else {
        $_SESSION['pesan_error'] = "Gagal memproses pembayaran.";
    }
    header("Location: status.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "headtags.html" ?>
    <title>Status Pesanan - <?= $login_type ?></title>
</head>
<body>
<?php include 'header.php'; ?>
<main class="main-content">
    <div class="container">
        <h3 class="header col s12 light center">Status Pesanan Anda</h3>
        <br>

        <?php if ($login_type == "Mitra") :

            // --- LOGIKA FILTER KHUSUS MITRA ---
            $where_clauses = ["p.id_mitra = $user_id"];
            $keyword = isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : '';
            $filter_status_pesanan = isset($_GET['status_pesanan']) ? $_GET['status_pesanan'] : 'semua';
            $filter_status_pembayaran = isset($_GET['status_pembayaran']) ? $_GET['status_pembayaran'] : 'semua';

            if (!empty($keyword)) {
                $where_clauses[] = "(pl.nama LIKE '%$keyword%' OR pl.telp LIKE '%$keyword%')";
            }
            if ($filter_status_pesanan != 'semua') {
                $where_clauses[] = "p.status_pesanan = '$filter_status_pesanan'";
            }
            if ($filter_status_pembayaran != 'semua') {
                $where_clauses[] = "t.status_pembayaran = '$filter_status_pembayaran'";
            }

            $query_str = "SELECT p.*, pl.nama as nama_pelanggan, pl.telp as telp_pelanggan, t.status_pembayaran 
                          FROM pesanan p 
                          JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan
                          LEFT JOIN transaksi t ON p.id_pesanan = t.id_pesanan";

            if (!empty($where_clauses)) {
                $query_str .= " WHERE " . implode(' AND ', $where_clauses);
            }
            $query_str .= " ORDER BY p.tgl_mulai DESC";
            $query = mysqli_query($connect, $query_str);
            ?>
            <div class="card-panel">
                <h5 class="header light">Filter Pesanan</h5>
                <form action="" method="GET">
                    <div class="row">
                        <div class="input-field col s12 m4">
                            <input type="text" name="keyword" id="keyword" value="<?= $keyword ?>" placeholder="Nama atau No. HP">
                            <label for="keyword">Cari Pelanggan</label>
                        </div>
                        <div class="input-field col s12 m3">
                            <select name="status_pesanan">
                                <option value="semua" <?= $filter_status_pesanan == 'semua' ? 'selected' : '' ?>>Semua Status Pesanan</option>
                                <option value="Menunggu Konfirmasi" <?= $filter_status_pesanan == 'Menunggu Konfirmasi' ? 'selected' : '' ?>>Menunggu Konfirmasi</option>
                                <option value="Sedang Dicuci" <?= $filter_status_pesanan == 'Sedang Dicuci' ? 'selected' : '' ?>>Sedang Dicuci</option>
                                <option value="Proses Pengeringan" <?= $filter_status_pesanan == 'Proses Pengeringan' ? 'selected' : '' ?>>Proses Pengeringan</option>
                                <option value="Sedang Disetrika" <?= $filter_status_pesanan == 'Sedang Disetrika' ? 'selected' : '' ?>>Sedang Disetrika</option>
                                <option value="Siap Diambil" <?= $filter_status_pesanan == 'Siap Diambil' ? 'selected' : '' ?>>Siap Diambil</option>
                                <option value="Selesai" <?= $filter_status_pesanan == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                            </select>
                            <label>Status Pesanan</label>
                        </div>
                        <div class="input-field col s12 m3">
                            <select name="status_pembayaran">
                                <option value="semua" <?= $filter_status_pembayaran == 'semua' ? 'selected' : '' ?>>Semua Status Bayar</option>
                                <option value="Belum Bayar" <?= $filter_status_pembayaran == 'Belum Bayar' ? 'selected' : '' ?>>Belum Bayar</option>
                                <option value="Lunas" <?= $filter_status_pembayaran == 'Lunas' ? 'selected' : '' ?>>Lunas</option>
                            </select>
                            <label>Status Pembayaran</label>
                        </div>
                        <div class="input-field col s12 m2">
                            <button type="submit" class="btn blue darken-2">Terapkan</button>
                            <a href="status.php" class="btn-flat">Reset</a>
                        </div>
                    </div>
                </form>
            </div>

            <table class="responsive-table striped">
                <thead>
                <tr><th>ID</th><th>Pelanggan</th><th>No. HP</th><th>Detail Pesanan</th><th>Status</th><th>Pembayaran</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                <?php while ($pesanan = mysqli_fetch_assoc($query)) : ?>
                    <tr>
                        <td>#<?= $pesanan['id_pesanan'] ?></td>
                        <td><?= htmlspecialchars($pesanan['nama_pelanggan']) ?></td>
                        <td><?= htmlspecialchars($pesanan['telp_pelanggan']) ?></td>
                        <td>
                            <b>Jenis:</b> <?= ucfirst(htmlspecialchars($pesanan['jenis'])) ?><br>
                            <b>Estimasi:</b> <?= $pesanan['estimasi_berat'] ?> Kg (Rp <?= number_format($pesanan['harga_estimasi']) ?>)<br>
                            <b>Aktual:</b> <?= $pesanan['berat'] ? $pesanan['berat'] . ' Kg (Rp ' . number_format($pesanan['harga_final']) . ')' : '<i>Belum ditimbang</i>' ?>
                        </td>
                        <td><span class="new badge blue" data-badge-caption=""><?= htmlspecialchars($pesanan['status_pesanan']) ?></span></td>
                        <td>
                            <?php
                            $status_bayar = $pesanan['status_pembayaran'] ?? 'Belum Ada Tagihan';
                            $badge_color = ($status_bayar == 'Lunas') ? 'green' : (($status_bayar == 'Belum Bayar') ? 'orange' : 'grey');
                            echo "<span class='new badge $badge_color' data-badge-caption=''>$status_bayar</span>";
                            ?>
                        </td>
                        <td>
                            <?php if ($pesanan['berat'] == NULL) : ?>
                                <form action="" method="post">
                                    <input type="hidden" name="id_pesanan" value="<?= $pesanan['id_pesanan'] ?>">
                                    <div class="input-field" style="width: 150px;">
                                        <input type="number" step="0.1" name="berat_aktual" placeholder="Berat Aktual (Kg)" required>
                                        <button class="btn-small blue darken-2" type="submit" name="konfirmasi_berat">Konfirmasi</button>
                                    </div>
                                </form>
                            <?php else: ?>
                                <form action="" method="post" style="width: 200px;">
                                    <input type="hidden" name="id_pesanan" value="<?= $pesanan['id_pesanan'] ?>">
                                    <select class="browser-default" name="status_pesanan">
                                        <option value="Sedang Dicuci" <?= $pesanan['status_pesanan'] == 'Sedang Dicuci' ? 'selected' : '' ?>>Sedang Dicuci</option>
                                        <option value="Proses Pengeringan" <?= $pesanan['status_pesanan'] == 'Proses Pengeringan' ? 'selected' : '' ?>>Proses Pengeringan</option>
                                        <option value="Sedang Disetrika" <?= $pesanan['status_pesanan'] == 'Sedang Disetrika' ? 'selected' : '' ?>>Sedang Disetrika</option>
                                        <option value="Siap Diambil" <?= $pesanan['status_pesanan'] == 'Siap Diambil' ? 'selected' : '' ?>>Siap Diambil</option>
                                        <option value="Selesai" <?= $pesanan['status_pesanan'] == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                                    </select>
                                    <button class="btn-small" type="submit" name="simpan_status">Update</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>

        <?php elseif ($login_type == "Pelanggan") :
            $query = mysqli_query($connect, "
                SELECT p.*, m.nama_laundry, t.id_transaksi, t.status_pembayaran 
                FROM pesanan p 
                JOIN mitra m ON p.id_mitra = m.id_mitra 
                LEFT JOIN transaksi t ON p.id_pesanan = t.id_pesanan
                WHERE p.id_pelanggan = $user_id 
                ORDER BY p.tgl_mulai DESC
            ");
            ?>
            <table class="responsive-table striped">
                <thead>
                <tr><th>ID Pesanan</th><th>Nama Laundry</th><th>Detail Pesanan</th><th>Status</th><th>Tagihan</th></tr>
                </thead>
                <tbody>
                <?php while ($pesanan = mysqli_fetch_assoc($query)) : ?>
                    <tr>
                        <td>#<?= $pesanan['id_pesanan'] ?></td>
                        <td><?= htmlspecialchars($pesanan['nama_laundry']) ?></td>
                        <td><b>Jenis:</b> <?= ucfirst(htmlspecialchars($pesanan['jenis'])) ?><br><b>Tgl Pesan:</b> <?= date('d M Y', strtotime($pesanan['tgl_mulai'])) ?></td>
                        <td><span class="new badge blue" data-badge-caption=""><?= htmlspecialchars($pesanan['status_pesanan']) ?></span></td>
                        <td>
                            <?php if ($pesanan['harga_final']) : ?>
                                <b>Rp <?= number_format($pesanan['harga_final']) ?></b>
                                <br>
                                <?php if (isset($pesanan['status_pembayaran']) && $pesanan['status_pembayaran'] == 'Belum Bayar'): ?>
                                    <a href="status.php?bayar=<?= $pesanan['id_transaksi'] ?>" class="btn-small green">Bayar Sekarang</a>
                                <?php elseif (isset($pesanan['status_pembayaran'])): ?>
                                    <span class="new badge green" data-badge-caption=""><?= $pesanan['status_pembayaran'] ?></span>
                                <?php endif; ?>
                            <?php else: ?>
                                <i>Menunggu konfirmasi harga dari mitra</i>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</main>

<?php include "footer.php"; ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        M.FormSelect.init(document.querySelectorAll('select'));
    });
</script>
<?php
// Letakkan kode popup di sini, sebelum body ditutup
if ($pesan_sukses) {
    echo "<script>Swal.fire('Berhasil', '" . addslashes($pesan_sukses) . "', 'success');</script>";
}
if ($pesan_error) {
    echo "<script>Swal.fire('Gagal', '" . addslashes($pesan_error) . "', 'error');</script>";
}
?>
</body>
</html>