<?php
session_start();
include 'connect-db.php';
include 'functions/functions.php';

// Otentikasi Pengguna
$login_type = '';
$user_id = 0;
if (isset($_SESSION["login-pelanggan"])) {
    $login_type = "Pelanggan";
    $user_id = $_SESSION["pelanggan"];
} elseif (isset($_SESSION["login-mitra"])) {
    $login_type = "Mitra";
    $user_id = $_SESSION["mitra"];
} elseif (isset($_SESSION["login-admin"])) {
    $login_type = "Admin";
    $user_id = $_SESSION["admin"];
} else {
    cekBelumLogin(); // Jika tidak ada sesi, paksa ke halaman login
}

// === LOGIKA PENYIMPANAN ULASAN PELANGGAN ===
if ($login_type == "Pelanggan" && isset($_POST["simpan_feedback"])) {
    $id_transaksi = intval($_POST["id_transaksi"]);
    $rating_from_form = intval($_POST["rating"]);
    $komentar = htmlspecialchars($_POST["komentar"]);

    // Konversi rating dari 1-5 (form) ke 1-10 (DB)
    $rating_to_db = max(1, min(10, $rating_from_form * 2));

    $update_query = "UPDATE transaksi SET rating = '$rating_to_db', komentar = '$komentar' WHERE id_transaksi = '$id_transaksi' AND id_pelanggan = '$user_id'";
    mysqli_query($connect, $update_query);

    $_SESSION['pesan_sukses'] = "Terima Kasih! Ulasan Anda telah berhasil disimpan.";
    header("Location: transaksi.php");
    exit;
}

// === LOGIKA LAPORAN ULASAN OLEH MITRA ===
if ($login_type == "Mitra" && isset($_POST["laporkan_ulasan"])) {
    $id_transaksi_laporan = intval($_POST["id_transaksi_laporan"]);
    $alasan_laporan = htmlspecialchars($_POST["alasan_laporan"]);

    // Cek apakah ulasan ini sudah pernah dilaporkan oleh mitra ini untuk mencegah duplikat
    $cek_laporan = mysqli_query($connect, "SELECT id_laporan FROM laporan_ulasan WHERE id_transaksi = '$id_transaksi_laporan' AND id_mitra = '$user_id'");
    if (mysqli_num_rows($cek_laporan) == 0) {
        $insert_laporan = "INSERT INTO laporan_ulasan (id_transaksi, id_mitra, alasan) VALUES ('$id_transaksi_laporan', '$user_id', '$alasan_laporan')";
        mysqli_query($connect, $insert_laporan);
        $_SESSION['pesan_sukses'] = "Laporan Anda telah dikirim dan akan segera ditinjau oleh Admin.";
    } else {
        $_SESSION['pesan_error'] = "Anda sudah pernah melaporkan ulasan ini.";
    }

    header("Location: transaksi.php");
    exit;
}

// --- LOGIKA FILTER DAN PENGAMBILAN DATA ---
$where_clauses = [];
$status_filters = [];
$base_query = "SELECT t.*, p.jenis, p.berat, p.status_pesanan, m.id_mitra as mitra_id_db, m.nama_laundry, pl.nama as nama_pelanggan 
               FROM transaksi t 
               JOIN pesanan p ON t.id_pesanan = p.id_pesanan
               JOIN mitra m ON t.id_mitra = m.id_mitra
               JOIN pelanggan pl ON t.id_pelanggan = pl.id_pelanggan";

if ($login_type == "Mitra") {
    $where_clauses[] = "t.id_mitra = $user_id";
} elseif ($login_type == "Pelanggan") {
    $where_clauses[] = "t.id_pelanggan = $user_id";
}

$filter_status = isset($_GET['status_pembayaran']) ? $_GET['status_pembayaran'] : 'semua';
$filter_mitra_id = isset($_GET['mitra_id']) ? intval($_GET['mitra_id']) : 0;
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

if ($filter_status == 'lunas') {
    $where_clauses[] = "t.status_pembayaran = 'Lunas'";
    $status_filters[] = "status pembayaran <strong>Lunas</strong>";
} elseif ($filter_status == 'belum_bayar') {
    $where_clauses[] = "t.status_pembayaran = 'Belum Bayar'";
    $status_filters[] = "status pembayaran <strong>Belum Bayar</strong>";
}
if ($filter_mitra_id > 0) {
    $mitra_filtered_data = mysqli_fetch_assoc(mysqli_query($connect, "SELECT nama_laundry FROM mitra WHERE id_mitra = $filter_mitra_id"));
    if ($mitra_filtered_data) {
        $where_clauses[] = "t.id_mitra = $filter_mitra_id";
        $status_filters[] = "oleh mitra <strong>" . htmlspecialchars($mitra_filtered_data['nama_laundry']) . "</strong>";
    }
}
// Filter tanggal - lebih fleksibel
if (!empty($start_date) || !empty($end_date)) {
    if (!empty($start_date) && !empty($end_date)) {
        // Kedua tanggal diisi - range
        $where_clauses[] = "DATE(t.tgl_transaksi) BETWEEN '$start_date' AND '$end_date'";
        $status_filters[] = "dari tanggal <strong>" . date('d M Y', strtotime($start_date)) . "</strong> hingga <strong>" . date('d M Y', strtotime($end_date)) . "</strong>";
    } elseif (!empty($start_date)) {
        // Hanya tanggal mulai - dari tanggal tersebut ke atas
        $where_clauses[] = "DATE(t.tgl_transaksi) >= '$start_date'";
        $status_filters[] = "dari tanggal <strong>" . date('d M Y', strtotime($start_date)) . "</strong>";
    } elseif (!empty($end_date)) {
        // Hanya tanggal akhir - sampai tanggal tersebut
        $where_clauses[] = "DATE(t.tgl_transaksi) <= '$end_date'";
        $status_filters[] = "sampai tanggal <strong>" . date('d M Y', strtotime($end_date)) . "</strong>";
    }
}

$query_str = $base_query;
if (!empty($where_clauses)) {
    $query_str .= " WHERE " . implode(' AND ', $where_clauses);
}
$query_str .= " ORDER BY t.tgl_transaksi DESC";
$query = mysqli_query($connect, $query_str);

$pesan_sukses = $_SESSION['pesan_sukses'] ?? null;
unset($_SESSION['pesan_sukses']);
$pesan_error = $_SESSION['pesan_error'] ?? null;
unset($_SESSION['pesan_error']);

// Mengambil daftar ulasan yang sudah dilaporkan oleh mitra ini untuk menonaktifkan tombol lapor
$laporan_terkirim = [];
if ($login_type == 'Mitra') {
    $result_laporan = mysqli_query($connect, "SELECT id_transaksi FROM laporan_ulasan WHERE id_mitra = '$user_id'");
    while($row = mysqli_fetch_assoc($result_laporan)) {
        $laporan_terkirim[] = $row['id_transaksi'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "headtags.html"; ?>
    <link rel="stylesheet" href="css/rating.css">
    <title>Riwayat Transaksi - <?= $login_type ?></title>
    <style>
        .card-transaksi { border-left: 5px solid #42a5f5; margin-bottom: 20px; }
        .card-transaksi .card-content { padding: 15px 20px; }
        .card-transaksi .card-title { font-weight: 500; font-size: 1.2rem; margin-bottom: 10px; }
        .transaction-details p { margin: 5px 0; font-size: 1rem; }
        .transaction-details i { vertical-align: middle; margin-right: 10px; }
        .filter-status { padding: 10px; margin-top: 20px; border-radius: 5px; background-color: #e3f2fd; border: 1px solid #90caf9; }
        .rating-stars .material-icons { cursor: pointer; color: grey; font-size: 2rem; }
        .rating-stars .material-icons.selected { color: #ffb400; }
        
        /* Admin Table Styles */
        .admin-table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-top: 20px;
        }
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        .admin-table th {
            background: linear-gradient(135deg, var(--dark-navy) 0%, var(--primary-blue) 100%);
            color: white;
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .admin-table td {
            padding: 12px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
        }
        .admin-table tr:hover {
            background-color: #f8f9fa;
        }
        .admin-table tr:nth-child(even) {
            background-color: #fafafa;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-lunas { background-color: #4caf50; color: white; }
        .status-belum-bayar { background-color: #ff9800; color: white; }
        .status-gagal { background-color: #f44336; color: white; }
        .rating-display {
            color: #ffb400;
            font-size: 12px;
        }
        .amount-display {
            font-weight: 600;
            color: var(--dark-navy);
        }
        .compact-text {
            font-size: 12px;
            color: var(--text-light);
        }
        .table-actions {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        .table-actions .btn-small {
            font-size: 10px;
            padding: 0 8px;
            height: 24px;
            line-height: 24px;
        }
        .responsive-table-wrapper {
            overflow-x: auto;
            margin-top: 20px;
        }
        .admin-stats {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .stat-card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            flex: 1;
            min-width: 150px;
            text-align: center;
        }
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: var(--primary-blue);
        }
        .stat-label {
            font-size: 12px;
            color: var(--text-light);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .search-box {
            margin-bottom: 20px;
        }
        .search-box input {
            border: 1px solid #ddd;
            border-radius: 20px;
            padding: 10px 15px;
            width: 100%;
            max-width: 300px;
        }
        
        /* Datepicker styling */
        .datepicker {
            cursor: pointer;
        }
        .datepicker:focus {
            border-bottom: 1px solid var(--primary-blue) !important;
            box-shadow: 0 1px 0 0 var(--primary-blue) !important;
        }
        .datepicker + label {
            color: var(--text-light) !important;
        }
        .datepicker:focus + label {
            color: var(--primary-blue) !important;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<main class="main-content">
    <div class="container">
        <h3 class="header col s12 light center">Riwayat Transaksi</h3>

        <div class="card-panel">
            <h5 class="header light">Filter Transaksi</h5>
            <form id="filter-form" action="" method="GET">
                <div class="row">
                    <div class="input-field col s12 m3">
                        <select name="status_pembayaran">
                            <option value="semua" <?= $filter_status == 'semua' ? 'selected' : '' ?>>Semua Pembayaran</option>
                            <option value="lunas" <?= $filter_status == 'lunas' ? 'selected' : '' ?>>Lunas</option>
                            <option value="belum_bayar" <?= $filter_status == 'belum_bayar' ? 'selected' : '' ?>>Belum Bayar</option>
                        </select>
                        <label>Status Pembayaran</label>
                    </div>
                    <?php if ($login_type == "Admin") : ?>
                    <div class="input-field col s12 m3">
                        <select name="mitra_id">
                            <option value="0">Semua Mitra</option>
                            <?php
                            $mitra_query = mysqli_query($connect, "SELECT id_mitra, nama_laundry FROM mitra ORDER BY nama_laundry");
                            while ($mitra = mysqli_fetch_assoc($mitra_query)) :
                                $selected = ($filter_mitra_id == $mitra['id_mitra']) ? 'selected' : '';
                            ?>
                            <option value="<?= $mitra['id_mitra'] ?>" <?= $selected ?>><?= htmlspecialchars($mitra['nama_laundry']) ?></option>
                            <?php endwhile; ?>
                        </select>
                        <label>Filter Mitra</label>
                    </div>
                    <div class="input-field col s12 m3">
                        <input type="text" name="start_date" class="datepicker" value="<?= $start_date ?>">
                        <label class="active">Tanggal Mulai</label>
                    </div>
                    <div class="input-field col s12 m3">
                        <input type="text" name="end_date" class="datepicker" value="<?= $end_date ?>">
                        <label class="active">Tanggal Akhir</label>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="row">
                    <div class="col s12 right-align">
                        <button type="submit" class="btn blue waves-effect">Terapkan Filter</button>
                    </div>
                </div>
            </form>
        </div>

        <?php if ($login_type == "Admin") : ?>
            <?php
            // Hitung statistik untuk admin
            $total_transaksi = mysqli_num_rows($query);
            
            // Query untuk statistik lunas
            $stat_lunas_query = "SELECT COUNT(*) as total FROM transaksi t 
                                JOIN pesanan p ON t.id_pesanan = p.id_pesanan
                                JOIN mitra m ON t.id_mitra = m.id_mitra
                                JOIN pelanggan pl ON t.id_pelanggan = pl.id_pelanggan";
            $stat_lunas_where = [];
            if (!empty($where_clauses)) {
                $stat_lunas_where = $where_clauses;
            }
            $stat_lunas_where[] = "t.status_pembayaran = 'Lunas'";
            $stat_lunas_query .= " WHERE " . implode(' AND ', $stat_lunas_where);
            $query_lunas = mysqli_query($connect, $stat_lunas_query);
            $total_lunas = mysqli_fetch_assoc($query_lunas)['total'] ?? 0;
            
            // Query untuk statistik belum bayar
            $stat_belum_bayar_query = "SELECT COUNT(*) as total FROM transaksi t 
                                      JOIN pesanan p ON t.id_pesanan = p.id_pesanan
                                      JOIN mitra m ON t.id_mitra = m.id_mitra
                                      JOIN pelanggan pl ON t.id_pelanggan = pl.id_pelanggan";
            $stat_belum_bayar_where = [];
            if (!empty($where_clauses)) {
                $stat_belum_bayar_where = $where_clauses;
            }
            $stat_belum_bayar_where[] = "t.status_pembayaran = 'Belum Bayar'";
            $stat_belum_bayar_query .= " WHERE " . implode(' AND ', $stat_belum_bayar_where);
            $query_belum_bayar = mysqli_query($connect, $stat_belum_bayar_query);
            $total_belum_bayar = mysqli_fetch_assoc($query_belum_bayar)['total'] ?? 0;
            
            // Query untuk statistik rating
            $stat_rating_query = "SELECT COUNT(*) as total FROM transaksi t 
                                 JOIN pesanan p ON t.id_pesanan = p.id_pesanan
                                 JOIN mitra m ON t.id_mitra = m.id_mitra
                                 JOIN pelanggan pl ON t.id_pelanggan = pl.id_pelanggan";
            $stat_rating_where = [];
            if (!empty($where_clauses)) {
                $stat_rating_where = $where_clauses;
            }
            $stat_rating_where[] = "t.rating IS NOT NULL";
            $stat_rating_query .= " WHERE " . implode(' AND ', $stat_rating_where);
            $query_rating = mysqli_query($connect, $stat_rating_query);
            $total_rating = mysqli_fetch_assoc($query_rating)['total'] ?? 0;
            ?>
            <div class="admin-stats">
                <div class="stat-card">
                    <div class="stat-number"><?= $total_transaksi ?></div>
                    <div class="stat-label">Total Transaksi</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $total_lunas ?></div>
                    <div class="stat-label">Lunas</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $total_belum_bayar ?></div>
                    <div class="stat-label">Belum Bayar</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $total_rating ?></div>
                    <div class="stat-label">Ada Rating</div>
                </div>
            </div>
            
            <div class="search-box">
                <input type="text" id="searchTable" placeholder="Cari transaksi..." onkeyup="searchTable()">
            </div>
        <?php endif; ?>
        
        <div class="filter-status">
            <p style="margin:0;"><i class="material-icons tiny">info_outline</i>
                <?php
                if (empty($status_filters)) { echo "Menampilkan <strong>semua</strong> transaksi."; }
                else { echo "Menampilkan transaksi dengan " . implode(', ', $status_filters) . "."; }
                ?>
            </p>
        </div>

        <?php
        if (mysqli_num_rows($query) > 0) :
            if ($login_type == "Admin") : ?>
                <div class="responsive-table-wrapper">
                    <div class="admin-table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tanggal</th>
                                    <th>Mitra</th>
                                    <th>Pelanggan</th>
                                    <th>Layanan</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th>Rating</th>
                                    <th>Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($transaksi = mysqli_fetch_assoc($query)) : 
                                    $rating_from_db = (float)$transaksi['rating'];
                                    $display_stars = round($rating_from_db / 2);
                                    $display_stars = max(1, min(5, $display_stars));
                                    
                                    $status_class = 'status-belum-bayar';
                                    if ($transaksi['status_pembayaran'] == 'Lunas') $status_class = 'status-lunas';
                                    if ($transaksi['status_pembayaran'] == 'Gagal') $status_class = 'status-gagal';
                                ?>
                                <tr>
                                    <td>
                                        <strong>#<?= $transaksi['id_transaksi'] ?></strong>
                                    </td>
                                    <td>
                                        <div class="compact-text">
                                            <?= date('d/m/Y', strtotime($transaksi['tgl_transaksi'])) ?>
                                        </div>
                                        <div class="compact-text">
                                            <?= date('H:i', strtotime($transaksi['tgl_transaksi'])) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="compact-text">
                                            <i class="material-icons tiny blue-text">store</i>
                                            <?= htmlspecialchars($transaksi['nama_laundry']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="compact-text">
                                            <i class="material-icons tiny teal-text">person</i>
                                            <?= htmlspecialchars($transaksi['nama_pelanggan']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="compact-text">
                                            <i class="material-icons tiny grey-text">local_laundry_service</i>
                                            <?= ucfirst(htmlspecialchars($transaksi['jenis'])) ?>
                                        </div>
                                        <div class="compact-text">
                                            <?= $transaksi['berat'] ?: 'N/A' ?> kg
                                        </div>
                                        <div class="compact-text" style="font-size: 10px; color: #666;">
                                            ID: <?= $transaksi['id_pesanan'] ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <span class="status-badge <?= $status_class ?>">
                                                <?= $transaksi['status_pembayaran'] ?>
                                            </span>
                                        </div>
                                        <div class="compact-text">
                                            <?= htmlspecialchars($transaksi['status_pesanan']) ?>
                                        </div>
                                        <div class="compact-text" style="font-size: 10px; color: #666;">
                                            <?= $transaksi['status_ulasan'] ?? 'Aktif' ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="amount-display">
                                            Rp <?= number_format($transaksi['total_bayar']) ?>
                                        </div>
                                        <div class="compact-text" style="font-size: 10px; color: #666;">
                                            <?= date('H:i', strtotime($transaksi['tgl_transaksi'])) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($transaksi['rating'] != NULL && $transaksi['status_ulasan'] != 'Dihapus'): ?>
                                            <div class="rating-display">
                                                <?= str_repeat('★', $display_stars) . str_repeat('☆', 5 - $display_stars) ?>
                                            </div>
                                            <div class="compact-text">
                                                (<?= number_format($rating_from_db / 2, 1) ?>/5.0)
                                            </div>
                                        <?php elseif ($transaksi['status_ulasan'] == 'Dihapus'): ?>
                                            <div class="compact-text" style="color: #9e9e9e; font-style: italic;">
                                                Dihapus
                                            </div>
                                        <?php else: ?>
                                            <div class="compact-text" style="font-style: italic;">
                                                -
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="table-actions">
                                            <?php if ($transaksi['rating'] != NULL && $transaksi['status_ulasan'] == 'Aktif'): ?>
                                                <a class="btn-small waves-effect waves-light blue modal-trigger" 
                                                   href="#modal-detail-<?= $transaksi['id_transaksi'] ?>"
                                                   title="Lihat Detail Ulasan">
                                                    <i class="material-icons tiny">star</i>
                                                </a>
                                            <?php endif; ?>
                                            <a class="btn-small waves-effect waves-light teal modal-trigger" 
                                               href="#modal-transaksi-<?= $transaksi['id_transaksi'] ?>"
                                               title="Lihat Detail Transaksi">
                                                <i class="material-icons tiny">visibility</i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Modal Detail Ulasan untuk Admin -->
                <?php 
                // Reset query untuk modal
                $query_modal = mysqli_query($connect, $query_str);
                while ($transaksi_modal = mysqli_fetch_assoc($query_modal)) :
                    if ($transaksi_modal['rating'] != NULL && $transaksi_modal['status_ulasan'] == 'Aktif') :
                        $rating_from_db = (float)$transaksi_modal['rating'];
                        $display_stars = round($rating_from_db / 2);
                        $display_stars = max(1, min(5, $display_stars));
                ?>
                <div id="modal-detail-<?= $transaksi_modal['id_transaksi'] ?>" class="modal">
                    <div class="modal-content">
                        <h4>Detail Ulasan Transaksi #<?= $transaksi_modal['id_transaksi'] ?></h4>
                        <div class="row">
                            <div class="col s12 m6">
                                <p><strong>Mitra:</strong> <?= htmlspecialchars($transaksi_modal['nama_laundry']) ?></p>
                                <p><strong>Pelanggan:</strong> <?= htmlspecialchars($transaksi_modal['nama_pelanggan']) ?></p>
                                <p><strong>Tanggal Transaksi:</strong> <?= date('d M Y, H:i', strtotime($transaksi_modal['tgl_transaksi'])) ?></p>
                                <p><strong>Total Bayar:</strong> Rp <?= number_format($transaksi_modal['total_bayar']) ?></p>
                            </div>
                            <div class="col s12 m6">
                                <p><strong>Layanan:</strong> <?= ucfirst(htmlspecialchars($transaksi_modal['jenis'])) ?> (<?= $transaksi_modal['berat'] ?: 'N/A' ?> kg)</p>
                                <p><strong>Status Pembayaran:</strong> <?= $transaksi_modal['status_pembayaran'] ?></p>
                                <p><strong>Status Pengerjaan:</strong> <?= htmlspecialchars($transaksi_modal['status_pesanan']) ?></p>
                            </div>
                        </div>
                        <div class="divider" style="margin: 20px 0;"></div>
                        <h5>Ulasan Pelanggan</h5>
                        <p><strong>Rating:</strong> 
                            <span style="color: #ffb400; font-size: 18px;">
                                <?= str_repeat('★', $display_stars) . str_repeat('☆', 5 - $display_stars) ?>
                            </span>
                            (<?= number_format($rating_from_db / 2, 1) ?>/5.0)
                        </p>
                        <p><strong>Komentar:</strong></p>
                        <div style="background: #f5f5f5; padding: 15px; border-radius: 5px; margin-top: 10px;">
                            <p style="margin: 0; font-style: italic;"><?= htmlspecialchars($transaksi_modal['komentar']) ?: '<i>Tidak ada komentar.</i>' ?></p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="#!" class="modal-close waves-effect waves-green btn-flat">Tutup</a>
                    </div>
                </div>
                <?php endif; endwhile; ?>
                
                <!-- Modal Detail Transaksi untuk Admin -->
                <?php 
                // Reset query untuk modal transaksi
                $query_transaksi_modal = mysqli_query($connect, $query_str);
                while ($transaksi_detail = mysqli_fetch_assoc($query_transaksi_modal)) :
                ?>
                <div id="modal-transaksi-<?= $transaksi_detail['id_transaksi'] ?>" class="modal">
                    <div class="modal-content">
                        <h4>Detail Transaksi #<?= $transaksi_detail['id_transaksi'] ?></h4>
                        <div class="row">
                            <div class="col s12 m6">
                                <h6>Informasi Transaksi</h6>
                                <p><strong>ID Transaksi:</strong> #<?= $transaksi_detail['id_transaksi'] ?></p>
                                <p><strong>ID Pesanan:</strong> #<?= $transaksi_detail['id_pesanan'] ?></p>
                                <p><strong>Tanggal Transaksi:</strong> <?= date('d M Y, H:i', strtotime($transaksi_detail['tgl_transaksi'])) ?></p>
                                <p><strong>Total Bayar:</strong> Rp <?= number_format($transaksi_detail['total_bayar']) ?></p>
                                <p><strong>Status Pembayaran:</strong> 
                                    <span class="status-badge <?= $transaksi_detail['status_pembayaran'] == 'Lunas' ? 'status-lunas' : 'status-belum-bayar' ?>">
                                        <?= $transaksi_detail['status_pembayaran'] ?>
                                    </span>
                                </p>
                            </div>
                            <div class="col s12 m6">
                                <h6>Informasi Pesanan</h6>
                                <p><strong>Layanan:</strong> <?= ucfirst(htmlspecialchars($transaksi_detail['jenis'])) ?></p>
                                <p><strong>Berat:</strong> <?= $transaksi_detail['berat'] ?: 'N/A' ?> kg</p>
                                <p><strong>Status Pengerjaan:</strong> <?= htmlspecialchars($transaksi_detail['status_pesanan']) ?></p>
                                <p><strong>Status Ulasan:</strong> <?= $transaksi_detail['status_ulasan'] ?? 'Aktif' ?></p>
                            </div>
                        </div>
                        <div class="divider" style="margin: 20px 0;"></div>
                        <div class="row">
                            <div class="col s12 m6">
                                <h6>Informasi Mitra</h6>
                                <p><strong>Nama Laundry:</strong> <?= htmlspecialchars($transaksi_detail['nama_laundry']) ?></p>
                                <p><strong>ID Mitra:</strong> #<?= $transaksi_detail['mitra_id_db'] ?></p>
                            </div>
                            <div class="col s12 m6">
                                <h6>Informasi Pelanggan</h6>
                                <p><strong>Nama Pelanggan:</strong> <?= htmlspecialchars($transaksi_detail['nama_pelanggan']) ?></p>
                            </div>
                        </div>
                        <?php if ($transaksi_detail['rating'] != NULL): ?>
                        <div class="divider" style="margin: 20px 0;"></div>
                        <div class="row">
                            <div class="col s12">
                                <h6>Ulasan Pelanggan</h6>
                                <?php
                                $rating_from_db = (float)$transaksi_detail['rating'];
                                $display_stars = round($rating_from_db / 2);
                                $display_stars = max(1, min(5, $display_stars));
                                ?>
                                <p><strong>Rating:</strong> 
                                    <span style="color: #ffb400; font-size: 18px;">
                                        <?= str_repeat('★', $display_stars) . str_repeat('☆', 5 - $display_stars) ?>
                                    </span>
                                    (<?= number_format($rating_from_db / 2, 1) ?>/5.0)
                                </p>
                                <p><strong>Komentar:</strong></p>
                                <div style="background: #f5f5f5; padding: 15px; border-radius: 5px; margin-top: 10px;">
                                    <p style="margin: 0; font-style: italic;"><?= htmlspecialchars($transaksi_detail['komentar']) ?: '<i>Tidak ada komentar.</i>' ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <a href="#!" class="modal-close waves-effect waves-green btn-flat">Tutup</a>
                    </div>
                </div>
                <?php endwhile; ?>
                
            <?php else : ?>
                <?php while ($transaksi = mysqli_fetch_assoc($query)) :
                    $borderColor = '#42a5f5'; // biru (Belum Bayar)
                    if ($transaksi['status_pembayaran'] == 'Lunas') $borderColor = '#66bb6a'; // hijau
                    if ($transaksi['status_pembayaran'] == 'Gagal') $borderColor = '#ef5350'; // merah
                    ?>
                    <div class="card card-transaksi" style="border-left-color: <?= $borderColor ?>;">
                        <div class="card-content">
                            <div class="row" style="margin-bottom:0;">
                                <div class="col s12 m8">
                                    <span class="card-title">Transaksi #<?= $transaksi['id_transaksi'] ?></span>
                                    <div class="transaction-details">
                                        <?php if ($login_type != 'Mitra') : ?>
                                            <p><i class="material-icons tiny blue-text">store</i>Mitra: <strong><?= htmlspecialchars($transaksi['nama_laundry']) ?></strong></p>
                                        <?php endif; ?>
                                        <?php if ($login_type != 'Pelanggan') : ?>
                                            <p><i class="material-icons tiny teal-text">person</i>Pelanggan: <strong><?= htmlspecialchars($transaksi['nama_pelanggan']) ?></strong></p>
                                        <?php endif; ?>
                                        <p><i class="material-icons tiny grey-text">date_range</i>Tanggal: <?= date('d M Y, H:i', strtotime($transaksi['tgl_transaksi'])) ?></p>
                                        <p><i class="material-icons tiny grey-text">local_laundry_service</i>Layanan: <?= ucfirst(htmlspecialchars($transaksi['jenis'])) ?> (<?= $transaksi['berat'] ?: 'N/A' ?> kg)</p>
                                        <p><i class="material-icons tiny grey-text">sync</i>Status Pengerjaan: <strong><?= htmlspecialchars($transaksi['status_pesanan']) ?></strong></p>
                                    </div>
                                </div>
                                <div class="col s12 m4 right-align">
                                    <p style="margin:0;">Total Bayar</p>
                                    <h5 style="margin-top:0;"><b>Rp <?= number_format($transaksi['total_bayar']) ?></b></h5>
                                    <span class="new badge" data-badge-caption="" style="background-color: <?= $borderColor ?>; color:white;"><?= $transaksi['status_pembayaran'] ?></span>
                                </div>
                            </div>

                            <div class="divider" style="margin: 15px 0;"></div>

                            <div class="row" style="margin-bottom:0;">
                                <div class="col s12">
                                    <strong>Ulasan:</strong>
                                    <?php if ($transaksi['status_ulasan'] == 'Dihapus'): ?>
                                        <p style="margin: 5px 0; color: #9e9e9e; font-style: italic;">Ulasan ini telah dihapus oleh Administrator.</p>
                                    <?php elseif ($login_type == "Pelanggan" && $transaksi['status_pembayaran'] == 'Lunas' && $transaksi['rating'] == NULL) : ?>
                                        <a class="btn-small waves-effect waves-light blue modal-trigger" href="#modal-ulasan-<?= $transaksi['id_transaksi'] ?>" style="margin-left: 10px;">Beri Ulasan</a>
                                    <?php elseif ($transaksi['rating'] != NULL): ?>
                                        <?php
                                        $rating_from_db = (float)$transaksi['rating'];
                                        $display_stars = round($rating_from_db / 2);
                                        $display_stars = max(1, min(5, $display_stars));
                                        ?>
                                        <div style="margin: 5px 0;">
                                            <b>Rating:</b>
                                            <div class="rating-display">
                                                <span class="stars" data-rating="<?= $transaksi['rating'] ?>">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <span class="rating-star <?= $i <= $display_stars ? 'filled' : '' ?>">★</span>
                                                    <?php endfor; ?>
                                                </span>
                                                <span class="rating-value"><?= number_format($rating_from_db / 2, 1) ?>/5.0</span>
                                            </div>
                                            <b>Komentar:</b> <?= htmlspecialchars($transaksi['komentar']) ?: '<i>Tidak ada komentar.</i>' ?><br>

                                            <?php if ($login_type == 'Mitra'): ?>
                                                <?php if(in_array($transaksi['id_transaksi'], $laporan_terkirim)): ?>
                                                    <span class="btn-small disabled" style="margin-top: 5px;">Telah Dilaporkan</span>
                                                <?php else: ?>
                                                    <button class="report-button" 
                                                            data-transaksi-id="<?= $transaksi['id_transaksi'] ?>" 
                                                            data-mitra-id="<?= $user_id ?>"
                                                            style="margin-top: 5px;">
                                                        <i class="material-icons tiny">report</i> Laporkan
                                                    </button>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <p style="margin: 5px 0; font-style: italic;"><?= $transaksi['status_pembayaran'] == 'Lunas' ? 'Belum ada ulasan.' : 'Ulasan dapat diberikan setelah lunas.' ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if ($login_type == "Pelanggan" && $transaksi['status_pembayaran'] == 'Lunas' && $transaksi['rating'] == NULL) : ?>
                    <div id="modal-ulasan-<?= $transaksi['id_transaksi'] ?>" class="modal">
                        <div class="modal-content">
                            <h4>Beri Ulasan untuk Transaksi #<?= $transaksi['id_transaksi'] ?></h4>
                            <form action="" method="post">
                                <input type="hidden" name="id_transaksi" value="<?= $transaksi['id_transaksi'] ?>">
                                <div class="input-field">
                                    <label>Rating Anda (1-5 Bintang)</label><br><br>
                                    <div class="rating-stars">
                                        <i class="material-icons" data-value="1">star</i><i class="material-icons" data-value="2">star</i><i class="material-icons" data-value="3">star</i><i class="material-icons" data-value="4">star</i><i class="material-icons" data-value="5">star</i>
                                    </div>
                                    <input type="hidden" name="rating" class="rating-value" value="0" required>
                                </div>
                                <div class="input-field">
                                    <textarea name="komentar" id="komentar<?= $transaksi['id_transaksi'] ?>" class="materialize-textarea" required></textarea>
                                    <label for="komentar<?= $transaksi['id_transaksi'] ?>">Tulis ulasan Anda...</label>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn waves-effect waves-light blue" type="submit" name="simpan_feedback">Kirim Ulasan</button>
                                    <a href="#!" class="modal-close waves-effect waves-green btn-flat">Batal</a>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($login_type == "Mitra" && $transaksi['rating'] != NULL && $transaksi['status_ulasan'] == 'Aktif' && !in_array($transaksi['id_transaksi'], $laporan_terkirim)) : ?>
                    <div id="modal-laporan-<?= $transaksi['id_transaksi'] ?>" class="modal">
                        <div class="modal-content">
                            <h4>Laporkan Ulasan</h4>
                            <p>Anda akan melaporkan ulasan dari <strong><?= htmlspecialchars($transaksi['nama_pelanggan']) ?></strong>. Mohon berikan alasan Anda.</p>
                            <form action="" method="post">
                                <input type="hidden" name="id_transaksi_laporan" value="<?= $transaksi['id_transaksi'] ?>">
                                <div class="input-field">
                                    <textarea name="alasan_laporan" id="alasan_laporan<?= $transaksi['id_transaksi'] ?>" class="materialize-textarea" required data-length="255"></textarea>
                                    <label for="alasan_laporan<?= $transaksi['id_transaksi'] ?>">Alasan Laporan</label>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn waves-effect waves-light red" type="submit" name="laporkan_ulasan">Kirim Laporan</button>
                                    <a href="#!" class="modal-close waves-effect waves-green btn-flat">Batal</a>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>

                <?php endwhile; ?>
            <?php endif; ?>
        <?php else :
            echo "<p class='center' style='margin-top:20px;'>Tidak ada transaksi yang ditemukan.</p>";
        endif; ?>
    </div>
</main>

<?php include "footer.php"; ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="js/rating.js"></script>
<script>
    function searchTable() {
        const input = document.getElementById('searchTable');
        const filter = input.value.toLowerCase();
        const table = document.querySelector('.admin-table tbody');
        const rows = table.getElementsByTagName('tr');
        
        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            const cells = row.getElementsByTagName('td');
            let found = false;
            
            for (let j = 0; j < cells.length; j++) {
                const cell = cells[j];
                if (cell.textContent.toLowerCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }
            
            if (found) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Inisialisasi komponen Materialize
        M.FormSelect.init(document.querySelectorAll('select'));
        M.Modal.init(document.querySelectorAll('.modal'));
        
        // Inisialisasi datepicker dengan konfigurasi yang benar
        var datepickers = document.querySelectorAll('.datepicker');
        if (datepickers.length > 0) {
            M.Datepicker.init(datepickers, { 
                format: 'yyyy-mm-dd', 
                autoClose: true,
                yearRange: [2020, new Date().getFullYear()],
                showClearBtn: true,
                i18n: {
                    months: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
                    monthsShort: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'],
                    weekdays: ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
                    weekdaysShort: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                    weekdaysAbbrev: ['M', 'S', 'S', 'R', 'K', 'J', 'S']
                }
            });
        }
        
        M.CharacterCounter.init(document.querySelectorAll('textarea[data-length]'));
        M.updateTextFields();

        // SweetAlert2 untuk notifikasi
        <?php if ($pesan_sukses): ?>
        Swal.fire({ title: 'Berhasil!', text: '<?= addslashes($pesan_sukses) ?>', icon: 'success', confirmButtonText: 'OK' });
        <?php endif; ?>
        <?php if ($pesan_error): ?>
        Swal.fire({ title: 'Gagal!', text: '<?= addslashes($pesan_error) ?>', icon: 'error', confirmButtonText: 'OK' });
        <?php endif; ?>

        // Logika untuk rating stars
        document.querySelectorAll('.rating-stars').forEach(starContainer => {
            const hiddenInput = starContainer.nextElementSibling;
            starContainer.querySelectorAll('.material-icons').forEach(star => {
                star.addEventListener('click', function() {
                    const value = this.getAttribute('data-value');
                    hiddenInput.value = value;
                    const allStars = starContainer.querySelectorAll('.material-icons');
                    allStars.forEach(s => s.classList.remove('selected'));
                    for (let i = 0; i < value; i++) {
                        allStars[i].classList.add('selected');
                    }
                });
            });
        });
    });
</script>
</body>
</html>