<?php
session_start();
include 'connect-db.php';
include 'functions/functions.php';

$pesan_sukses = $_SESSION['pesan_sukses'] ?? null; unset($_SESSION['pesan_sukses']);
$pesan_error = $_SESSION['pesan_error'] ?? null; unset($_SESSION['pesan_error']);

cekBelumLogin();

$login_type = ''; $user_id = 0;
if (isset($_SESSION["login-mitra"])) { $login_type = "Mitra"; $user_id = $_SESSION["mitra"]; }
elseif (isset($_SESSION["login-pelanggan"])) { $login_type = "Pelanggan"; $user_id = $_SESSION["pelanggan"]; }
elseif (isset($_SESSION["login-admin"])) { header("Location: admin.php"); exit; }

// Backend logic for rating submission (Pelanggan)
if ($login_type == "Pelanggan" && isset($_POST["submit_rating"])) {
    $id_transaksi = intval($_POST["id_transaksi"]);
    $rating = intval($_POST["rating"]);
    $komentar = htmlspecialchars($_POST["komentar"]);
    
    // Validasi rating
    if ($rating < 1 || $rating > 10) {
        $_SESSION['pesan_error'] = "Rating harus antara 1-10";
        header("Location: status.php"); exit;
    }
    
    // Validasi komentar
    if (empty($komentar)) {
        $_SESSION['pesan_error'] = "Komentar harus diisi";
        header("Location: status.php"); exit;
    }
    
    // Validasi bahwa transaksi milik pelanggan ini dan belum di-rating
    $check_query = mysqli_query($connect, "SELECT id_transaksi FROM transaksi WHERE id_transaksi = $id_transaksi AND id_pelanggan = $user_id AND rating IS NULL");
    if (mysqli_num_rows($check_query) == 0) {
        $_SESSION['pesan_error'] = "Transaksi tidak ditemukan atau sudah di-rating";
        header("Location: status.php"); exit;
    }
    
    // Update rating
    $update_query = mysqli_query($connect, "UPDATE transaksi SET rating = $rating, komentar = '$komentar' WHERE id_transaksi = $id_transaksi AND id_pelanggan = $user_id");
    
    if ($update_query) {
        $_SESSION['pesan_sukses'] = "Rating berhasil disimpan!";
    } else {
        $_SESSION['pesan_error'] = "Gagal menyimpan rating: " . mysqli_error($connect);
    }
    
    header("Location: status.php"); exit;
}

// Backend logic for Mitra actions (confirm weight, update status)
if ($login_type == "Mitra") {
    if (isset($_POST["konfirmasi_berat"])) {
        // ... (backend logic remains the same)
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
            header("Location: status.php"); exit;
        }
    }
    if (isset($_POST["simpan_status"])) {
        $id_pesanan = intval($_POST["id_pesanan"]);
        $status_baru = htmlspecialchars($_POST["status_pesanan"]);
        
        // Debug: Log the received data
        error_log("Update Status - ID Pesanan: $id_pesanan, Status Baru: $status_baru, User ID: $user_id");
        
        // Validate status is not empty
        if (empty($status_baru)) {
            $_SESSION['pesan_error'] = "Silakan pilih status pesanan terlebih dahulu.";
            header("Location: status.php"); exit;
        }
        
        // Validate that the order belongs to this mitra
        $check_query = mysqli_query($connect, "SELECT id_pesanan FROM pesanan WHERE id_pesanan = $id_pesanan AND id_mitra = $user_id");
        if(mysqli_num_rows($check_query) > 0) {
            $update_query = mysqli_query($connect, "UPDATE pesanan SET status_pesanan = '$status_baru' WHERE id_pesanan = $id_pesanan AND id_mitra = $user_id");
            if($update_query) {
                $_SESSION['pesan_sukses'] = "Status pesanan #$id_pesanan telah diperbarui menjadi: $status_baru";
            } else {
                $_SESSION['pesan_error'] = "Gagal memperbarui status pesanan: " . mysqli_error($connect);
            }
        } else {
            $_SESSION['pesan_error'] = "Pesanan tidak ditemukan atau tidak memiliki akses.";
        }
        header("Location: status.php"); exit;
    }
}

// Backend logic for Pelanggan payment simulation
if (isset($_GET['bayar']) && $login_type == "Pelanggan") {
    // ... (backend logic remains the same)
    $id_transaksi = intval($_GET['bayar']);
    $id_pelanggan_session = $_SESSION['pelanggan'];
    $update_query = "UPDATE transaksi SET status_pembayaran = 'Lunas' WHERE id_transaksi = '$id_transaksi' AND id_pelanggan = '$id_pelanggan_session'";
    mysqli_query($connect, $update_query);
    if (mysqli_affected_rows($connect) > 0) {
        $id_pesanan_q = mysqli_fetch_assoc(mysqli_query($connect, "SELECT id_pesanan FROM transaksi WHERE id_transaksi = '$id_transaksi'"));
        mysqli_query($connect, "UPDATE pesanan SET status_pesanan = 'Sedang Dicuci' WHERE id_pesanan = '".$id_pesanan_q['id_pesanan']."'");
        $_SESSION['pesan_sukses'] = "Pembayaran untuk transaksi #$id_transaksi telah berhasil!";
    } else { $_SESSION['pesan_error'] = "Gagal memproses pembayaran."; }
    header("Location: status.php"); exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "headtags.html" ?>
    <title>Status Pesanan - <?= $login_type ?></title>
    <link rel="stylesheet" href="css/rating.css">
    <style> 
        .badge.new { font-weight: 500; border-radius: 8px; }
        .select-wrapper input.select-dropdown {
            border-bottom: 1px solid #9e9e9e !important;
        }
        .select-wrapper .dropdown-content {
            z-index: 9999 !important;
        }
    </style>
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
            $keyword = $_GET['keyword'] ?? '';
            $filter_status_pesanan = $_GET['status_pesanan'] ?? 'semua';
            $filter_status_pembayaran = $_GET['status_pembayaran'] ?? 'semua';

            if (!empty($keyword)) { $where_clauses[] = "(pl.nama LIKE '%$keyword%' OR pl.telp LIKE '%$keyword%')"; }
            if ($filter_status_pesanan != 'semua') { $where_clauses[] = "p.status_pesanan = '$filter_status_pesanan'"; }
            if ($filter_status_pembayaran != 'semua') { $where_clauses[] = "t.status_pembayaran = '$filter_status_pembayaran'"; }

            $query_str = "SELECT p.*, pl.nama as nama_pelanggan, pl.telp as telp_pelanggan, t.status_pembayaran, t.id_transaksi, t.rating, t.komentar, t.status_ulasan 
                          FROM pesanan p JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan LEFT JOIN transaksi t ON p.id_pesanan = t.id_pesanan";
            if (!empty($where_clauses)) { $query_str .= " WHERE " . implode(' AND ', $where_clauses); }
            $query_str .= " ORDER BY p.tgl_mulai DESC";
            $query = mysqli_query($connect, $query_str);
            ?>
            <div class="card-panel">
                <h5 class="header light">Filter Pesanan</h5>
                <form action="" method="GET">
                    <div class="row">
                        <div class="input-field col s12 m6 l3"><input type="text" name="keyword" id="keyword" value="<?= htmlspecialchars($keyword) ?>"><label for="keyword">Cari Nama/No. HP Pelanggan</label></div>
                        <div class="input-field col s12 m6 l3">
                            <select name="status_pesanan">
                                <option value="semua" <?= $filter_status_pesanan == 'semua' ? 'selected' : '' ?>>Semua Status Pesanan</option>
                                <option value="Menunggu Konfirmasi" <?= $filter_status_pesanan == 'Menunggu Konfirmasi' ? 'selected' : '' ?>>Menunggu Konfirmasi</option>
                                <option value="Menunggu Pembayaran" <?= $filter_status_pesanan == 'Menunggu Pembayaran' ? 'selected' : '' ?>>Menunggu Pembayaran</option>
                                <option value="Sedang Dicuci" <?= $filter_status_pesanan == 'Sedang Dicuci' ? 'selected' : '' ?>>Sedang Dicuci</option>
                                <option value="Proses Pengeringan" <?= $filter_status_pesanan == 'Proses Pengeringan' ? 'selected' : '' ?>>Proses Pengeringan</option>
                                <option value="Sedang Disetrika" <?= $filter_status_pesanan == 'Sedang Disetrika' ? 'selected' : '' ?>>Sedang Disetrika</option>
                                <option value="Siap Diambil" <?= $filter_status_pesanan == 'Siap Diambil' ? 'selected' : '' ?>>Siap Diambil</option>
                                <option value="Selesai" <?= $filter_status_pesanan == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                            </select><label>Status Pesanan</label>
                        </div>
                        <div class="input-field col s12 m6 l3">
                            <select name="status_pembayaran">
                                <option value="semua" <?= $filter_status_pembayaran == 'semua' ? 'selected' : '' ?>>Semua Status Bayar</option>
                                <option value="Belum Ada Tagihan" <?= $filter_status_pembayaran == 'Belum Ada Tagihan' ? 'selected' : '' ?>>Belum Ada Tagihan</option>
                                <option value="Belum Bayar" <?= $filter_status_pembayaran == 'Belum Bayar' ? 'selected' : '' ?>>Belum Bayar</option>
                                <option value="Lunas" <?= $filter_status_pembayaran == 'Lunas' ? 'selected' : '' ?>>Lunas</option>
                            </select><label>Status Pembayaran</label>
                        </div>
                        <div class="input-field col s12 m6 l3"><button type="submit" class="btn">Terapkan</button> <a href="status.php" class="btn-flat">Reset</a></div>
                    </div>
                </form>
            </div>

            <?php if(mysqli_num_rows($query) > 0): while ($pesanan = mysqli_fetch_assoc($query)) : ?>
            <div class="card">
                <div class="card-content">
                    <div class="row" style="margin-bottom:0;">
                        <div class="col s8">
                            <span class="card-title" style="font-weight: 600; color:var(--dark-navy);">Pesanan #<?= $pesanan['id_pesanan'] ?></span>
                            <p><b>Pelanggan:</b> <?= htmlspecialchars($pesanan['nama_pelanggan']) ?> (<?= htmlspecialchars($pesanan['telp_pelanggan']) ?>)</p>
                            <p><b>Jenis:</b> <?= ucfirst(htmlspecialchars($pesanan['jenis'])) ?> | <b>Tgl Pesan:</b> <?= date('d M Y, H:i', strtotime($pesanan['tgl_mulai'])) ?></p>
                            <p>
                                <b>Estimasi Berat:</b> <?= $pesanan['estimasi_berat'] ? $pesanan['estimasi_berat'] . ' kg' : 'Belum diisi' ?> | 
                                <b>Berat Aktual:</b> 
                                <?php if ($pesanan['berat']) : ?>
                                    <span style="color: var(--primary-blue); font-weight: 600;"><?= $pesanan['berat'] ?> kg</span>
                                <?php else : ?>
                                    <span style="color: #ff9800; font-style: italic;">Belum ditimbang</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col s4 right-align">
                            <?php $status_bayar = $pesanan['status_pembayaran'] ?? 'Belum Ada Tagihan';
                            $badge_color = ['Lunas' => 'green', 'Belum Bayar' => 'orange', 'Belum Ada Tagihan' => 'grey']; ?>
                            <span class="new badge <?= $badge_color[$status_bayar] ?>" data-badge-caption=""><?= $status_bayar ?></span>
                            <?php if ($pesanan['harga_final']) : ?>
                                <p style="margin: 5px 0 0 0; font-size: 0.9rem; color: var(--text-light);">
                                    Rp <?= number_format($pesanan['harga_final']) ?>
                                </p>
                            <?php elseif ($pesanan['harga_estimasi']) : ?>
                                <p style="margin: 5px 0 0 0; font-size: 0.9rem; color: var(--text-light);">
                                    <i class="material-icons tiny" style="vertical-align: middle;">info</i>
                                    Estimasi: Rp <?= number_format($pesanan['harga_estimasi']) ?>
                                </p>
                            <?php endif; ?>
                            
                            <?php if ($pesanan['id_transaksi'] && $pesanan['rating'] && $pesanan['status_ulasan'] == 'Aktif'): ?>
                                <div style="margin-top: 10px; padding: 10px; background: #f5f5f5; border-radius: 4px;">
                                    <div class="rating-display">
                                        <span class="stars" data-rating="<?= $pesanan['rating'] ?>">
                                            <?php for (
                                                $i = 1; $i <= 5; $i++): ?>
                                                <span class="rating-star <?= $i <= ($pesanan['rating'] / 2) ? 'filled' : '' ?>">★</span>
                                            <?php endfor; ?>
                                        </span>
                                        <?php
                                        $rating5 = $pesanan['rating'] / 2;
                                        $display = ($rating5 == intval($rating5)) ? intval($rating5) : number_format($rating5, 1);
                                        ?>
                                        <span class="rating-value"><?= $display ?>/5</span>
                                    </div>
                                    <?php if ($pesanan['komentar']): ?>
                                        <p style="margin: 5px 0 0 0; font-style: italic; color: #555;">
                                            "<?= htmlspecialchars($pesanan['komentar']) ?>"
                                        </p>
                                    <?php endif; ?>
                                    <button class="report-button" 
                                            data-transaksi-id="<?= $pesanan['id_transaksi'] ?>" 
                                            data-mitra-id="<?= $user_id ?>">
                                        <i class="material-icons tiny">report</i> Laporkan
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="card-action" style="background-color: #f9f9f9;">
                    <?php if ($pesanan['berat'] == NULL) : // TAHAP KONFIRMASI BERAT ?>
                        <form action="" method="post" class="row valign-wrapper" style="margin-bottom:0;">
                            <input type="hidden" name="id_pesanan" value="<?= $pesanan['id_pesanan'] ?>">
                            <div class="input-field col s8 m5">
                                <input type="number" step="0.1" name="berat_aktual" placeholder="Contoh: 3.5" required>
                                <label>Berat Aktual (Kg)</label>
                                <?php if ($pesanan['estimasi_berat']) : ?>
                                    <span class="helper-text">Estimasi: <?= $pesanan['estimasi_berat'] ?> kg</span>
                                <?php endif; ?>
                            </div>
                            <div class="col s4 m7"><button class="btn blue" type="submit" name="konfirmasi_berat">Konfirmasi Berat</button></div>
                        </form>
                    <?php else: // TAHAP UPDATE STATUS PENGERJAAN ?>
                        <form action="" method="post" class="row valign-wrapper" style="margin-bottom:0;" id="form-status-<?= $pesanan['id_pesanan'] ?>">
                            <input type="hidden" name="id_pesanan" value="<?= $pesanan['id_pesanan'] ?>">
                            <div class="input-field col s8 m5">
                                <select name="status_pesanan" required>
                                    <option value="">Pilih Status</option>
                                    <option value="Menunggu Konfirmasi" <?= $pesanan['status_pesanan'] == 'Menunggu Konfirmasi' ? 'selected' : '' ?>>Menunggu Konfirmasi</option>
                                    <option value="Menunggu Pembayaran" <?= $pesanan['status_pesanan'] == 'Menunggu Pembayaran' ? 'selected' : '' ?>>Menunggu Pembayaran</option>
                                    <option value="Sedang Dicuci" <?= $pesanan['status_pesanan'] == 'Sedang Dicuci' ? 'selected' : '' ?>>Sedang Dicuci</option>
                                    <option value="Proses Pengeringan" <?= $pesanan['status_pesanan'] == 'Proses Pengeringan' ? 'selected' : '' ?>>Proses Pengeringan</option>
                                    <option value="Sedang Disetrika" <?= $pesanan['status_pesanan'] == 'Sedang Disetrika' ? 'selected' : '' ?>>Sedang Disetrika</option>
                                    <option value="Siap Diambil" <?= $pesanan['status_pesanan'] == 'Siap Diambil' ? 'selected' : '' ?>>Siap Diambil</option>
                                    <option value="Sedang Diantar" <?= $pesanan['status_pesanan'] == 'Sedang Diantar' ? 'selected' : '' ?>>Sedang Diantar</option>
                                    <option value="Selesai" <?= $pesanan['status_pesanan'] == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                                </select>
                                <label>Status Pesanan</label>
                            </div>
                            <div class="col s4 m7">
                                <button class="btn green" type="submit" name="simpan_status" onclick="return validateForm(<?= $pesanan['id_pesanan'] ?>)">Update Status</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; else: echo "<p class='center light'>Tidak ada pesanan yang cocok dengan filter Anda.</p>"; endif; ?>


        <?php elseif ($login_type == "Pelanggan") :
            $query = mysqli_query($connect, "SELECT p.*, m.nama_laundry, t.id_transaksi, t.status_pembayaran, t.rating, t.komentar FROM pesanan p JOIN mitra m ON p.id_mitra = m.id_mitra LEFT JOIN transaksi t ON p.id_pesanan = t.id_pesanan WHERE p.id_pelanggan = $user_id ORDER BY p.tgl_mulai DESC");
            if(mysqli_num_rows($query) > 0): while ($pesanan = mysqli_fetch_assoc($query)) :
                $status_color = ($pesanan['status_pembayaran'] == 'Lunas') ? 'green' : 'blue';
                ?>
                <div class="card">
                    <div class="card-content">
                        <div class="row" style="margin-bottom:0;">
                            <div class="col m8 s12">
                                <span class="card-title" style="font-weight: 600; color:var(--dark-navy);"><?= htmlspecialchars($pesanan['nama_laundry']) ?></span>
                                <p><b>ID Pesanan:</b> #<?= $pesanan['id_pesanan'] ?> | <b>Jenis:</b> <?= ucfirst(htmlspecialchars($pesanan['jenis'])) ?></p>
                                <p><b>Tanggal Pesan:</b> <?= date('d M Y, H:i', strtotime($pesanan['tgl_mulai'])) ?></p>
                                <p>
                                    <b>Estimasi Berat:</b> <?= $pesanan['estimasi_berat'] ? $pesanan['estimasi_berat'] . ' kg' : 'Belum diisi' ?> | 
                                    <b>Berat Aktual:</b> 
                                    <?php if ($pesanan['berat']) : ?>
                                        <span style="color: var(--primary-blue); font-weight: 600;"><?= $pesanan['berat'] ?> kg</span>
                                    <?php else : ?>
                                        <span style="color: #ff9800; font-style: italic;">Belum ditimbang</span>
                                    <?php endif; ?>
                                </p>
                                <span class="new badge <?= $status_color ?>" data-badge-caption=""><?= htmlspecialchars($pesanan['status_pesanan']) ?></span>
                                
                                <?php if ($pesanan['id_transaksi'] && $pesanan['status_pembayaran'] == 'Lunas'): ?>
                                    <?php if ($pesanan['rating']): ?>
                                        <!-- Tampilkan rating yang sudah ada -->
                                        <div class="rating-display" style="margin-top: 10px;">
                                            <span class="stars" data-rating="<?= $pesanan['rating'] ?>">
                                                <?php for (
                                                    $i = 1; $i <= 5; $i++): ?>
                                                    <span class="rating-star <?= $i <= ($pesanan['rating'] / 2) ? 'filled' : '' ?>">★</span>
                                                <?php endfor; ?>
                                            </span>
                                            <?php
                                            $rating5 = $pesanan['rating'] / 2;
                                            $display = ($rating5 == intval($rating5)) ? intval($rating5) : number_format($rating5, 1);
                                            ?>
                                            <span class="rating-value"><?= $display ?>/5</span>
                                        </div>
                                        <?php if ($pesanan['komentar']): ?>
                                            <p class="review-comment">"<?= htmlspecialchars($pesanan['komentar']) ?>"</p>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <!-- Form rating untuk pesanan yang sudah selesai -->
                                        <div class="rating-form">
                                            <h6>Berikan Rating untuk Layanan Ini</h6>
                                            
                                            <!-- Instruksi yang lebih jelas -->
                                            <div class="rating-instructions">
                                                <i class="material-icons tiny">info</i>
                                                <strong>Petunjuk:</strong> Klik bintang untuk memberikan rating. Semakin banyak bintang, semakin baik rating Anda.
                                                <br><small>1 bintang = Sangat Buruk, 5 bintang = Sangat Baik</small>
                                            </div>
                                            
                                            <form action="" method="post" id="rating-form-<?= $pesanan['id_transaksi'] ?>">
                                                <input type="hidden" name="id_transaksi" value="<?= $pesanan['id_transaksi'] ?>">
                                                <input type="hidden" name="rating" value="0" id="rating-input-<?= $pesanan['id_transaksi'] ?>">
                                                
                                                <div class="rating-container">
                                                    <div class="rating-stars">
                                                        <?php 
                                                        $tooltips = ['Sangat Buruk', 'Buruk', 'Cukup', 'Baik', 'Sangat Baik'];
                                                        for ($i = 1; $i <= 5; $i++): 
                                                        ?>
                                                            <span class="rating-star" data-value="<?= $i * 2 ?>" data-tooltip="<?= $tooltips[$i-1] ?>" tabindex="0" role="button" aria-label="Rating <?= $i ?> bintang - <?= $tooltips[$i-1] ?>">★</span>
                                                        <?php endfor; ?>
                                                    </div>
                                                    <span class="rating-text" id="rating-text-<?= $pesanan['id_transaksi'] ?>">Pilih rating</span>
                                                </div>
                                                
                                                <div class="input-field">
                                                    <textarea name="komentar" id="komentar_<?= $pesanan['id_transaksi'] ?>" class="materialize-textarea" required></textarea>
                                                    <label for="komentar_<?= $pesanan['id_transaksi'] ?>">Komentar (wajib diisi)</label>
                                                </div>
                                                
                                                <button type="submit" name="submit_rating" class="btn blue waves-effect waves-light" onclick="return validateRatingForm(<?= $pesanan['id_transaksi'] ?>)">
                                                    <i class="material-icons left">star</i>Kirim Rating
                                                </button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <div class="col m4 s12 right-align" style="padding-top:10px;">
                                <?php if ($pesanan['harga_final']) : ?>
                                    <p style="margin:0;">Total Tagihan</p>
                                    <h5 style="margin:0 0 10px 0; color:var(--dark-navy);"><b>Rp <?= number_format($pesanan['harga_final']) ?></b></h5>
                                    <?php if ($pesanan['berat'] && $pesanan['harga_final']) : ?>
                                        <p style="margin:0; font-size: 0.9rem; color: var(--text-light);">
                                            <i class="material-icons tiny" style="vertical-align: middle;">scale</i>
                                            <?= $pesanan['berat'] ?> kg × Rp <?= number_format($pesanan['harga_final'] / $pesanan['berat']) ?>/kg
                                        </p>
                                    <?php endif; ?>
                                    <?php if (isset($pesanan['status_pembayaran']) && $pesanan['status_pembayaran'] == 'Belum Bayar'): ?>
                                        <a href="status.php?bayar=<?= $pesanan['id_transaksi'] ?>" class="btn green pulse">Bayar Sekarang</a>
                                    <?php elseif (isset($pesanan['status_pembayaran'])): ?>
                                        <span class="new badge green" data-badge-caption=""><?= $pesanan['status_pembayaran'] ?></span>
                                    <?php endif; ?>
                                <?php elseif ($pesanan['harga_estimasi']) : ?>
                                    <p style="margin:0;">Estimasi Tagihan</p>
                                    <h5 style="margin:0 0 10px 0; color:var(--text-light);"><b>Rp <?= number_format($pesanan['harga_estimasi']) ?></b></h5>
                                    <p style="margin:0; font-size: 0.9rem; color: var(--text-light);">
                                        <i class="material-icons tiny" style="vertical-align: middle;">info</i>
                                        Estimasi berdasarkan berat <?= $pesanan['estimasi_berat'] ?> kg
                                    </p>
                                    <p class="light" style="margin-top: 10px;"><i>Menunggu konfirmasi berat aktual dari mitra.</i></p>
                                <?php else: ?>
                                    <p class="light"><i>Menunggu konfirmasi harga dari mitra.</i></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; else: echo "<p class='center light'>Anda belum memiliki riwayat pesanan.</p>"; endif; ?>
        <?php endif; ?>
    </div>
</main>

<?php include "footer.php"; ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="js/script.js"></script>
<script src="js/rating.js"></script>
<script>
function validateForm(orderId) {
    const form = document.getElementById('form-status-' + orderId);
    const select = form.querySelector('select[name="status_pesanan"]');
    
    if (!select.value) {
        Swal.fire('Error', 'Silakan pilih status pesanan terlebih dahulu!', 'error');
        return false;
    }
    
    console.log('Submitting form for order:', orderId, 'with status:', select.value);
    return true;
}

// Fungsi untuk validasi form rating
function validateRatingForm(orderId) {
    const form = document.getElementById('rating-form-' + orderId);
    const ratingInput = form.querySelector('input[name="rating"]');
    const commentInput = form.querySelector('textarea[name="komentar"]');
    
    console.log('Validating rating form for order:', orderId);
    console.log('Rating value:', ratingInput.value);
    console.log('Comment value:', commentInput.value);
    
    if (!ratingInput.value || ratingInput.value == '0') {
        Swal.fire('Error', 'Silakan berikan rating terlebih dahulu!', 'error');
        return false;
    }
    
    if (!commentInput.value.trim()) {
        Swal.fire('Error', 'Silakan berikan komentar!', 'error');
        return false;
    }
    
    console.log('Rating form is valid, submitting...');
    return true;
}



// Initialize Materialize select dropdowns
function initializeSelects() {
    const selects = document.querySelectorAll('select');
    console.log('Found', selects.length, 'select elements');
    
    selects.forEach((select, index) => {
        if (select.classList.contains('browser-default')) {
            console.log('Skipping browser default select:', index);
            return; // Skip browser default selects
        }
        
        console.log('Initializing select:', index, select.name);
        
        // Destroy existing instances
        const instances = M.FormSelect.getInstance(select);
        if (instances) {
            console.log('Destroying existing instance for:', select.name);
            instances.destroy();
        }
        
        // Initialize new instance
        try {
            M.FormSelect.init(select);
            console.log('Successfully initialized select:', select.name);
        } catch (error) {
            console.error('Error initializing select:', select.name, error);
        }
    });
}

// Debug form submission
document.addEventListener('DOMContentLoaded', function() {
    // Initialize selects on page load
    initializeSelects();
    
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const formData = new FormData(form);
            console.log('Form submitted with data:');
            for (let [key, value] of formData.entries()) {
                console.log(key + ': ' + value);
            }
        });
    });
    
    // Re-initialize selects after a short delay to ensure DOM is ready
    setTimeout(initializeSelects, 500);
});
</script>
<?php
if ($pesan_sukses) { 
    echo "<script>console.log('Success: " . addslashes($pesan_sukses) . "');</script>";
    echo "<script>Swal.fire('Berhasil', '" . addslashes($pesan_sukses) . "', 'success');</script>"; 
}
if ($pesan_error) { 
    echo "<script>console.log('Error: " . addslashes($pesan_error) . "');</script>";
    echo "<script>Swal.fire('Gagal', '" . addslashes($pesan_error) . "', 'error');</script>"; 
}
?>
</body>
</html>