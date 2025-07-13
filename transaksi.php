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

// Logika Feedback Pelanggan
if ($login_type == "Pelanggan" && isset($_POST["simpan_feedback"])) {
    $id_transaksi = intval($_POST["id_transaksi"]);
    $rating = intval($_POST["rating"]);
    $komentar = htmlspecialchars($_POST["komentar"]);
    $update_query = "UPDATE transaksi SET rating = '$rating', komentar = '$komentar' WHERE id_transaksi = '$id_transaksi' AND id_pelanggan = '$user_id'";
    mysqli_query($connect, $update_query);
    echo "<script>Swal.fire('Terima Kasih!','Ulasan Anda telah disimpan.','success').then(() => window.location = 'transaksi.php');</script>";
}

// --- LOGIKA FILTER DAN STATUS ---
$where_clauses = [];
$status_filters = []; // Untuk menampilkan status filter aktif
$base_query = "SELECT t.*, p.jenis, p.berat, p.status_pesanan, m.id_mitra as mitra_id_db, m.nama_laundry, pl.nama as nama_pelanggan 
               FROM transaksi t 
               JOIN pesanan p ON t.id_pesanan = p.id_pesanan
               JOIN mitra m ON t.id_mitra = m.id_mitra
               JOIN pelanggan pl ON t.id_pelanggan = pl.id_pelanggan";

// Filter berdasarkan peran pengguna
if ($login_type == "Mitra") {
    $where_clauses[] = "t.id_mitra = $user_id";
} elseif ($login_type == "Pelanggan") {
    $where_clauses[] = "t.id_pelanggan = $user_id";
}

// Mengambil nilai filter dari URL
$filter_status = isset($_GET['status_pembayaran']) ? $_GET['status_pembayaran'] : 'semua'; // DIUBAH
$filter_mitra_id = isset($_GET['mitra_id']) ? intval($_GET['mitra_id']) : 0;
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Menerapkan filter ke query dan membangun pesan status
if ($filter_status == 'lunas') { // DIUBAH
    $where_clauses[] = "t.status_pembayaran = 'Lunas'";
    $status_filters[] = "status pembayaran <strong>Lunas</strong>";
} elseif ($filter_status == 'belum_bayar') { // DIUBAH
    $where_clauses[] = "t.status_pembayaran = 'Belum Bayar'";
    $status_filters[] = "status pembayaran <strong>Belum Bayar</strong>";
}

if ($filter_mitra_id > 0) {
    $where_clauses[] = "t.id_mitra = $filter_mitra_id";
    $mitra_filtered_data = mysqli_fetch_assoc(mysqli_query($connect, "SELECT nama_laundry FROM mitra WHERE id_mitra = $filter_mitra_id"));
    if ($mitra_filtered_data) {
        $status_filters[] = "oleh mitra <strong>" . htmlspecialchars($mitra_filtered_data['nama_laundry']) . "</strong>";
    }
}

if (!empty($start_date) && !empty($end_date)) {
    $where_clauses[] = "DATE(t.tgl_transaksi) BETWEEN '$start_date' AND '$end_date'";
    $status_filters[] = "dari tanggal <strong>" . date('d M Y', strtotime($start_date)) . "</strong> hingga <strong>" . date('d M Y', strtotime($end_date)) . "</strong>";
}

// Menggabungkan semua klausa filter
$query_str = $base_query;
if (!empty($where_clauses)) {
    $query_str .= " WHERE " . implode(' AND ', $where_clauses);
}
$query_str .= " ORDER BY t.tgl_transaksi DESC";
$query = mysqli_query($connect, $query_str);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "headtags.html"; ?>
    <title>Riwayat Transaksi - <?= $login_type ?></title>
    <style>
        .card-transaksi { border-left: 5px solid #42a5f5; margin-bottom: 20px; }
        .card-transaksi .card-content { padding: 15px 20px; }
        .card-transaksi .card-title { font-weight: 500; font-size: 1.2rem; margin-bottom: 10px; }
        .transaction-details p { margin: 5px 0; font-size: 1rem; }
        .transaction-details i { vertical-align: middle; margin-right: 10px; }
        #mitra-list-popup li { cursor: pointer; padding: 10px; }
        #mitra-list-popup li:hover { background-color: #f2f2f2; }
        .filter-status {
            padding: 10px;
            margin-top: 20px;
            border-radius: 5px;
            background-color: #e3f2fd;
            border: 1px solid #90caf9;
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
                    <div class="input-field col s12 m4">
                        <select name="status_pembayaran" id="status-filter">
                            <option value="semua" <?= $filter_status == 'semua' ? 'selected' : '' ?>>Semua Pembayaran</option>
                            <option value="lunas" <?= $filter_status == 'lunas' ? 'selected' : '' ?>>Lunas</option>
                            <option value="belum_bayar" <?= $filter_status == 'belum_bayar' ? 'selected' : '' ?>>Belum Bayar</option>
                        </select>
                        <label>Status Pembayaran</label>
                    </div>

                    <?php if ($login_type != 'Mitra'): ?>
                        <div class="input-field col s12 m4">
                            <input type="text" id="mitra_search" placeholder="Ketik untuk mencari..." autocomplete="off">
                            <label for="mitra_search">Cari Berdasarkan Mitra</label>
                            <input type="hidden" name="mitra_id" id="mitra_id" value="<?= $filter_mitra_id ?>">
                        </div>
                    <?php endif; ?>
                </div>
                <div class="row">
                    <div class="input-field col s12 m4">
                        <input type="date" name="start_date" id="start_date" value="<?= $start_date ?>">
                        <label for="start_date">Dari Tanggal</label>
                    </div>
                    <div class="input-field col s12 m4">
                        <input type="date" name="end_date" id="end_date" value="<?= $end_date ?>">
                        <label for="end_date">Sampai Tanggal</label>
                    </div>
                    <div class="input-field col s12 m4">
                        <button type="button" class="btn blue darken-2" onclick="applyFilters()">Terapkan</button>
                        <a href="transaksi.php" class="btn-flat">Reset</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="filter-status">
            <p style="margin:0;"><i class="material-icons tiny">info_outline</i>
                <?php
                if (empty($status_filters)) {
                    echo "Menampilkan <strong>semua</strong> transaksi.";
                } else {
                    echo "Menampilkan transaksi dengan " . implode(', ', $status_filters) . ".";
                }
                ?>
            </p>
        </div>

        <?php
        if (mysqli_num_rows($query) > 0) :
            while ($transaksi = mysqli_fetch_assoc($query)) :
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
                                <?php if ($login_type == "Pelanggan" && $transaksi['rating'] == NULL) : ?>
                                    <a class="btn-small waves-effect waves-light blue modal-trigger" href="#modal<?= $transaksi['id_transaksi'] ?>" style="margin-left: 10px;">Beri Ulasan</a>
                                <?php else: ?>
                                    <p style="margin: 5px 0;">
                                        <b>Rating:</b> <?= $transaksi['rating'] ? str_repeat('⭐', $transaksi['rating']) : '<i>Belum ada rating</i>' ?><br>
                                        <b>Komentar:</b> <?= htmlspecialchars($transaksi['komentar']) ?: '<i>Tidak ada komentar</i>' ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($login_type == "Pelanggan" && $transaksi['rating'] == NULL) : ?>
                <div id="modal<?= $transaksi['id_transaksi'] ?>" class="modal">
                    <div class="modal-content">
                        <h4>Beri Ulasan untuk Transaksi #<?= $transaksi['id_transaksi'] ?></h4>
                        <form action="" method="post">
                            <input type="hidden" name="id_transaksi" value="<?= $transaksi['id_transaksi'] ?>">
                            <div class="input-field">
                                <input type="number" name="rating" id="rating<?= $transaksi['id_transaksi'] ?>" min="1" max="5" required>
                                <label for="rating<?= $transaksi['id_transaksi'] ?>">Rating (1-5 Bintang)</label>
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

            <?php
            endwhile;
        else :
            echo "<p class='center' style='margin-top:20px;'>Tidak ada transaksi yang sesuai dengan filter Anda.</p>";
        endif;
        ?>
    </div>
</main>

<div id="mitra-modal" class="modal">
    <div class="modal-content">
        <h4>Pilih Mitra</h4>
        <p>Apakah nama mitra yang Anda maksud sudah benar?</p>
        <ul id="mitra-list-popup"></ul>
        <div id="mitra-warning" style="display:none;" class="red-text">Mitra tidak ditemukan. Silakan periksa kembali nama yang Anda masukkan.</div>
    </div>
    <div class="modal-footer">
        <a href="#!" class="modal-close waves-effect waves-green btn-flat">Batal</a>
    </div>
</div>

<?php include "footer.php"; ?>
<script>
    function applyFilters() {
        const form = document.getElementById('filter-form');
        // Hapus parameter kosong agar URL bersih
        const inputs = form.querySelectorAll('input, select');
        inputs.forEach(input => {
            if (!input.value || (input.tagName === 'SELECT' && input.value === 'semua')) {
                input.name = '';
            }
        });
        form.submit();
    }

    document.addEventListener('DOMContentLoaded', function() {
        M.FormSelect.init(document.querySelectorAll('select'));
        M.Modal.init(document.querySelectorAll('.modal'));
        var mitraModal = M.Modal.getInstance(document.getElementById('mitra-modal'));

        const searchInput = document.getElementById('mitra_search');
        const mitraListPopup = document.getElementById('mitra-list-popup');
        const mitraIdInput = document.getElementById('mitra_id');
        const mitraWarning = document.getElementById('mitra-warning');
        let searchTimeout;

        if (searchInput) {
            const currentMitraId = document.getElementById('mitra_id').value;
            if (currentMitraId > 0) {
                fetch(`ajax/get_mitra.php?id=${currentMitraId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.length > 0) {
                            searchInput.value = data[0].nama_laundry;
                            M.updateTextFields();
                        }
                    });
            }

            searchInput.addEventListener('keyup', function() {
                clearTimeout(searchTimeout);
                const keyword = this.value;

                if (keyword.length < 2) {
                    mitraListPopup.innerHTML = '';
                    return;
                }

                searchTimeout = setTimeout(() => {
                    fetch(`ajax/get_mitra.php?keyword=${keyword}`)
                        .then(response => response.json())
                        .then(data => {
                            mitraListPopup.innerHTML = '';
                            mitraWarning.style.display = 'none';

                            if (data.length > 0) {
                                mitraModal.open();
                                data.forEach(mitra => {
                                    const li = document.createElement('li');
                                    li.textContent = mitra.nama_laundry;
                                    li.dataset.id = mitra.id_mitra;
                                    li.addEventListener('click', function() {
                                        mitraIdInput.value = this.dataset.id;
                                        searchInput.value = this.textContent;
                                        mitraModal.close();
                                        applyFilters();
                                    });
                                    mitraListPopup.appendChild(li);
                                });
                            } else {
                                mitraModal.open();
                                mitraWarning.style.display = 'block';
                            }
                        });
                }, 500);
            });
        }
    });
</script>
</body>
</html>