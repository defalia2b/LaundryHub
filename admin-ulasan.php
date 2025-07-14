<?php
session_start();
include 'connect-db.php';
include 'functions/functions.php';

// Hanya Admin yang bisa mengakses halaman ini
if (!isset($_SESSION["login-admin"])) {
    header("Location: login-admin.php");
    exit;
}
$user_id = $_SESSION["admin"];

// === LOGIKA AKSI ADMIN ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_laporan = intval($_POST['id_laporan']);
    $id_transaksi = intval($_POST['id_transaksi']);

    // Jika admin memilih "Hapus Ulasan"
    if (isset($_POST['hapus_ulasan'])) {
        // 1. Ubah status ulasan di tabel transaksi menjadi 'Dihapus'
        $update_transaksi = mysqli_query($connect, "UPDATE transaksi SET status_ulasan = 'Dihapus' WHERE id_transaksi = '$id_transaksi'");

        // 2. Ubah status laporan menjadi 'Ditinjau'
        $update_laporan = mysqli_query($connect, "UPDATE laporan_ulasan SET status_laporan = 'Ditinjau' WHERE id_laporan = '$id_laporan'");

        $_SESSION['pesan_sukses'] = "Ulasan untuk transaksi #$id_transaksi telah berhasil dihapus.";
    }
    // Jika admin memilih "Abaikan Laporan"
    elseif (isset($_POST['abaikan_laporan'])) {
        // 1. Cukup ubah status laporan menjadi 'Ditinjau'
        $update_laporan = mysqli_query($connect, "UPDATE laporan_ulasan SET status_laporan = 'Ditinjau' WHERE id_laporan = '$id_laporan'");
        $_SESSION['pesan_sukses'] = "Laporan untuk ulasan transaksi #$id_transaksi telah diabaikan.";
    }

    header("Location: admin-ulasan.php");
    exit;
}


// --- PENGAMBILAN DATA LAPORAN YANG MASIH 'Menunggu' ---
$query_laporan = mysqli_query($connect,
    "SELECT 
        lu.id_laporan, 
        lu.alasan, 
        lu.tgl_laporan,
        t.id_transaksi, 
        t.rating, 
        t.komentar,
        pl.nama AS nama_pelanggan,
        m.nama_laundry AS nama_mitra
    FROM laporan_ulasan lu
    JOIN transaksi t ON lu.id_transaksi = t.id_transaksi
    JOIN pelanggan pl ON t.id_pelanggan = pl.id_pelanggan
    JOIN mitra m ON lu.id_mitra = m.id_mitra
    WHERE lu.status_laporan = 'Menunggu'
    ORDER BY lu.tgl_laporan ASC"
);

$pesan_sukses = $_SESSION['pesan_sukses'] ?? null;
unset($_SESSION['pesan_sukses']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "headtags.html"; ?>
    <link rel="stylesheet" href="css/rating.css">
    <title>Moderasi Ulasan - Admin Panel</title>
    <style>
        .report-card {
            border-left: 5px solid #ef5350; /* Merah untuk laporan */
            margin-bottom: 25px;
        }
        .report-card .card-content {
            padding-bottom: 10px;
        }
        .report-info {
            font-size: 0.9rem;
            color: #757575;
        }
        .review-content {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
        }
        .review-content blockquote {
            margin: 0;
            padding: 0 15px;
            border-left: 3px solid #ccc;
        }
        .actions {
            text-align: right;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<main class="main-content">
    <div class="container">
        <h3 class="header col s12 light center">Manajemen Laporan Ulasan</h3>
        <p class="center grey-text">Tinjau ulasan yang dilaporkan oleh mitra dan ambil tindakan yang diperlukan.</p>

        <?php if (mysqli_num_rows($query_laporan) > 0) : ?>
            <?php while ($laporan = mysqli_fetch_assoc($query_laporan)) : ?>
                <?php
                // Logika untuk menampilkan bintang rating
                $rating_db = (float)$laporan['rating'];
                $display_stars = round($rating_db / 2);
                $display_stars = max(0, min(5, $display_stars));
                ?>
                <div class="card report-card">
                    <div class="card-content">
                        <span class="card-title">Laporan untuk Transaksi #<?= $laporan['id_transaksi'] ?></span>
                        <div class="report-info">
                            <p><strong>Dilaporkan oleh:</strong> <?= htmlspecialchars($laporan['nama_mitra']) ?></p>
                            <p><strong>Tanggal Laporan:</strong> <?= date('d M Y, H:i', strtotime($laporan['tgl_laporan'])) ?></p>
                            <p><strong>Alasan Laporan:</strong> <?= htmlspecialchars($laporan['alasan']) ?></p>
                        </div>

                        <div class="review-content">
                            <h6>Ulasan yang Dilaporkan:</h6>
                            <p>
                                <strong>Pelanggan:</strong> <?= htmlspecialchars($laporan['nama_pelanggan']) ?><br>
                                <strong>Rating:</strong> 
                                <div class="rating-display">
                                    <span class="stars" data-rating="<?= $laporan['rating'] ?>">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <span class="rating-star <?= $i <= ($display_stars) ? 'filled' : '' ?>">★</span>
                                        <?php endfor; ?>
                                    </span>
                                    <span class="rating-value"><?= number_format($laporan['rating'] / 2, 1) ?>/5</span>
                                </div>
                            </p>
                            <blockquote><?= htmlspecialchars($laporan['komentar']) ?></blockquote>
                        </div>
                    </div>
                    <div class="card-action actions">
                        <form action="" method="post" style="display: inline;">
                            <input type="hidden" name="id_laporan" value="<?= $laporan['id_laporan'] ?>">
                            <input type="hidden" name="id_transaksi" value="<?= $laporan['id_transaksi'] ?>">

                            <button type="submit" name="abaikan_laporan" class="btn-flat waves-effect waves-green" onclick="return confirm('Anda yakin ingin mengabaikan laporan ini? Ulasan tidak akan berubah.')">Abaikan Laporan</button>
                            <button type="submit" name="hapus_ulasan" class="btn red waves-effect waves-light" onclick="return confirm('PERINGATAN: Anda akan menghapus ulasan ini secara permanen. Aksi ini tidak dapat dibatalkan. Lanjutkan?')">Hapus Ulasan</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else : ?>
            <div class="card-panel green lighten-5" style="border-left: 5px solid #66bb6a;">
                <p class="green-text text-darken-2" style="font-size: 1.1rem;"><i class="material-icons tiny">check_circle</i> Tidak ada laporan ulasan yang perlu ditinjau saat ini. Semua sudah beres!</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include "footer.php"; ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="js/rating.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        M.Modal.init(document.querySelectorAll('.modal'));

        // SweetAlert2 untuk notifikasi sukses
        <?php if ($pesan_sukses): ?>
        Swal.fire({
            title: 'Berhasil!',
            text: '<?= addslashes($pesan_sukses) ?>',
            icon: 'success',
            confirmButtonText: 'OK'
        });
        <?php endif; ?>
    });
</script>
</body>
</html>