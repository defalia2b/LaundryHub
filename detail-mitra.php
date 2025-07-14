<?php
session_start();
include 'connect-db.php';
include 'functions/functions.php';

if (isset($_GET['id'])) {
    $id_mitra = $_GET['id'];
    $query_mitra = mysqli_query($connect, "SELECT * FROM mitra WHERE id_mitra = '$id_mitra'");
    if (mysqli_num_rows($query_mitra) == 0) {
        header('Location: index.php');
        exit;
    }
    $data_mitra = mysqli_fetch_assoc($query_mitra);
} else {
    header('Location: index.php');
    exit;
}

// Mengambil dan memproses data ulasan
$query_ulasan = mysqli_query($connect,
    "SELECT t.rating, t.komentar, pl.nama as nama_pelanggan, t.tgl_transaksi 
     FROM transaksi t 
     JOIN pelanggan pl ON t.id_pelanggan = pl.id_pelanggan
     WHERE t.id_mitra = '$id_mitra' AND t.rating IS NOT NULL 
     ORDER BY t.tgl_transaksi DESC"
);

$ulasan_data = [];
$total_rating = 0;
$jumlah_ulasan = 0;

if (mysqli_num_rows($query_ulasan) > 0) {
    $jumlah_ulasan = mysqli_num_rows($query_ulasan);
    while ($row = mysqli_fetch_assoc($query_ulasan)) {
        $ulasan_data[] = $row;
        $total_rating += (float)$row['rating'];
    }
}

// Menghitung rata-rata rating
$rata_rata_rating_10 = ($jumlah_ulasan > 0) ? $total_rating / $jumlah_ulasan : 0;
$rata_rata_rating_5 = $rata_rata_rating_10 / 2;
$display_stars_avg = round($rata_rata_rating_5);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include 'headtags.html'; ?>
    <title><?= htmlspecialchars($data_mitra['nama_laundry']); ?> - Detail Mitra</title>
    <style>
        .mitra-header {
            background: linear-gradient(135deg, var(--dark-navy) 0%, var(--primary-blue) 100%);
            color: white; 
            padding: 50px 20px; 
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .mitra-header h3 { 
            margin: 0; 
            font-weight: 600; 
            font-size: 2.5rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        .mitra-header p { 
            margin: 10px 0 0 0; 
            font-size: 1.1rem;
            opacity: 0.9;
            font-weight: 300;
        }
        .mitra-info, .mitra-ulasan { 
            margin-top: 20px; 
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .mitra-info .card-content {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        }
        .mitra-info .card-title {
            color: var(--dark-navy);
            font-weight: 600;
            font-size: 1.5rem;
            margin-bottom: 20px;
        }
        .mitra-info p {
            font-size: 1.1rem;
            margin: 15px 0;
            color: var(--text-dark);
        }
        .mitra-info p strong {
            color: var(--dark-navy);
            font-weight: 600;
        }
        .mitra-info .material-icons {
            color: var(--primary-blue);
            margin-right: 8px;
        }
        .card-action .btn {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-navy) 100%);
            border-radius: 25px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }
        .card-action .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        }
        .rating-summary { 
            text-align: center; 
            padding: 25px; 
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px; 
            border: 1px solid #dee2e6;
        }
        .rating-summary .rating-value { 
            font-size: 3rem; 
            font-weight: bold; 
            color: var(--dark-navy);
            margin-bottom: 10px;
        }
        .rating-summary .stars { 
            font-size: 2rem; 
            color: #ffb400; 
            margin-bottom: 10px;
        }
        .collection-item.avatar .title { 
            font-weight: 600; 
            color: var(--dark-navy);
        }
        .collection-item .rating-stars { 
            color: #ffb400; 
            margin: 5px 0;
        }
        .collection-item .comment { 
            color: var(--text-dark); 
            margin-top: 8px;
            font-style: italic;
        }
        .collection-header h4 {
            color: var(--dark-navy);
            font-weight: 600;
            margin: 0;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<main class="main-content">
    <div class="mitra-header">
        <h3><?= htmlspecialchars($data_mitra['nama_laundry']); ?></h3>
        <p><?= htmlspecialchars($data_mitra['alamat']); ?></p>
    </div>

    <div class="container">
        <div class="row">
            <div class="col s12 m7">
                <div class="card mitra-info">
                    <div class="card-content">
                        <span class="card-title">Informasi Mitra</span>

                        <p><strong><i class="material-icons tiny">phone</i> Telepon:</strong>
                            <?= !empty($data_mitra['telp']) ? htmlspecialchars($data_mitra['telp']) : 'Tidak tersedia'; ?>
                        </p>

                        <p><strong><i class="material-icons tiny">email</i> Email:</strong> <?= htmlspecialchars($data_mitra['email']); ?></p>

                    </div>
                    <div class="card-action">
                        <a href="pesan-laundry.php?id=<?= $data_mitra['id_mitra']; ?>" class="btn blue waves-effect waves-light">
                            <i class="material-icons left">shopping_cart</i>Pesan Sekarang
                        </a>
                    </div>
                </div>
            </div>
            <div class="col s12 m5">
                <div class="card mitra-ulasan">
                    <div class="card-content">
                        <span class="card-title">Rating & Ulasan</span>
                        <div class="rating-summary">
                            <?php if ($jumlah_ulasan > 0) : ?>
                                <div class="rating-value"><?= number_format($rata_rata_rating_5, 1); ?></div>
                                <div class="stars">
                                    <?= str_repeat('★', $display_stars_avg) . str_repeat('☆', 5 - $display_stars_avg) ?>
                                </div>
                                <div>Berdasarkan <?= $jumlah_ulasan; ?> ulasan</div>
                            <?php else : ?>
                                <div class="grey-text">Belum ada ulasan</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col s12">
                <ul class="collection with-header">
                    <li class="collection-header"><h4>Ulasan Pelanggan</h4></li>
                    <?php if ($jumlah_ulasan > 0) : ?>
                        <?php foreach ($ulasan_data as $ulasan) : ?>
                            <?php
                            $rating_db = (float)$ulasan['rating'];
                            $display_stars_item = round($rating_db / 2);
                            $display_stars_item = max(1, min(5, $display_stars_item));
                            ?>
                            <li class="collection-item avatar">
                                <i class="material-icons circle blue">person</i>
                                <span class="title"><?= htmlspecialchars($ulasan['nama_pelanggan']); ?></span>
                                <p class="rating-stars">
                                    <?= str_repeat('★', $display_stars_item) . str_repeat('☆', 5 - $display_stars_item) ?>
                                </p>
                                <p class="comment">
                                    <?= htmlspecialchars($ulasan['komentar']); ?>
                                </p>
                                <span class="secondary-content grey-text"><?= date('d M Y', strtotime($ulasan['tgl_transaksi'])); ?></span>
                            </li>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <li class="collection-item">
                            <p class="center grey-text">Mitra ini belum memiliki ulasan.</p>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</main>
<?php include 'footer.php'; ?>
</body>
</html>