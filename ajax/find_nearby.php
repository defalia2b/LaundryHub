<?php
include '../connect-db.php';

// Validasi input latitude dan longitude
if (!isset($_GET['lat']) || !isset($_GET['lon'])) {
    http_response_code(400); // Bad Request
    die("Error: Lokasi tidak valid.");
}

$user_lat = floatval($_GET['lat']);
$user_lon = floatval($_GET['lon']);

/**
 * Fungsi untuk menghitung jarak antara dua titik koordinat (Haversine formula).
 * Mengembalikan jarak dalam kilometer (km).
 */
function haversine_distance($lat1, $lon1, $lat2, $lon2) {
    $earth_radius = 6371; // Radius bumi dalam kilometer

    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    $distance = $earth_radius * $c;
    return $distance;
}

// Ambil semua data mitra dari database yang memiliki koordinat dengan rating
$result = mysqli_query($connect, "SELECT m.*, 
                                  AVG(t.rating) as avg_rating,
                                  COUNT(t.rating) as total_reviews
                                  FROM mitra m 
                                  LEFT JOIN transaksi t ON m.id_mitra = t.id_mitra AND t.rating IS NOT NULL AND t.status_ulasan = 'Aktif'
                                  WHERE m.latitude IS NOT NULL AND m.longitude IS NOT NULL
                                  GROUP BY m.id_mitra");

if (!$result) {
    http_response_code(500); // Internal Server Error
    die("Error: Gagal mengakses database.");
}

$mitra_list = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Hitung jarak untuk setiap mitra
    $distance = haversine_distance($user_lat, $user_lon, $row['latitude'], $row['longitude']);
    $row['distance'] = $distance; // Tambahkan jarak ke dalam data mitra
    $mitra_list[] = $row;
}

// Urutkan daftar mitra berdasarkan jarak (yang terdekat di atas)
usort($mitra_list, function($a, $b) {
    return $a['distance'] <=> $b['distance'];
});

// Batasi hasil menjadi 6 teratas untuk tampilan yang lebih bersih
$mitra_list = array_slice($mitra_list, 0, 6);

// Tampilkan hasilnya dalam format HTML yang baru
if (count($mitra_list) > 0) {
    echo '<h4 class="header light center" style="margin-bottom: 2rem;">Mitra Laundry Terdekat</h4>';
    echo '<div class="row">';
    foreach ($mitra_list as $mitra) {
        echo '
        <div class="col s12 m6 l4">
            <div class="card" style="border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                <a href="detail-mitra.php?id=' . $mitra['id_mitra'] . '">
                    <div class="card-image">
                        <img src="img/mitra/' . htmlspecialchars($mitra['foto']) . '" alt="Foto ' . htmlspecialchars($mitra["nama_laundry"]) . '" style="height: 200px; object-fit: cover;">
                        <span class="card-title" style="background: linear-gradient(135deg, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.5) 100%); padding: 10px 15px; border-radius: 0 12px 0 0; font-weight: 600; font-size: 1.2rem;">' . htmlspecialchars($mitra["nama_laundry"]) . '</span>
                    </div>
                </a>
                <div class="card-content" style="padding: 20px; background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);">
                    <p class="truncate" style="color: var(--text-dark); font-size: 1rem; margin-bottom: 15px;">
                        <i class="material-icons tiny" style="vertical-align: middle; color: var(--primary-blue);">place</i> 
                        ' . htmlspecialchars($mitra["alamat"]) . '
                    </p>
                    
                    ' . ($mitra['avg_rating'] ? '
                    <div style="margin-bottom: 15px;">
                        <div style="display: flex; align-items: center; gap: 5px;">
                            <span style="color: #ffb400; font-size: 16px;">' . str_repeat('★', round($mitra['avg_rating'] / 2)) . str_repeat('☆', 5 - round($mitra['avg_rating'] / 2)) . '</span>
                            <span style="font-weight: bold; color: #333; font-size: 14px;">' . number_format($mitra['avg_rating'] / 2, 1) . '/5</span>
                            <span style="font-size: 12px; color: #666;">(' . $mitra['total_reviews'] . ' ulasan)</span>
                        </div>
                    </div>
                    ' : '<p style="color: #999; font-size: 12px; margin-bottom: 15px;">Belum ada rating</p>') . '
                    
                    <div style="margin-top: 15px; display: flex; justify-content: space-between; align-items: center;">
                        <span class="light" style="font-weight: 600; color: var(--dark-navy); font-size: 0.9rem;">
                            <i class="material-icons tiny" style="vertical-align: middle; color: var(--primary-blue);">near_me</i>
                            ' . round($mitra['distance'], 1) . ' km dari Anda
                        </span>
                        <a href="detail-mitra.php?id=' . $mitra['id_mitra'] . '" class="btn-small waves-effect waves-light" style="background: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-navy) 100%); border-radius: 20px; font-weight: 600;">
                            <i class="material-icons tiny left">visibility</i>Detail
                        </a>
                    </div>
                </div>
            </div>
        </div>
        ';
    }
    echo '</div>';
} else {
    echo '
    <div class="center" style="padding: 40px 20px;">
        <i class="material-icons large grey-text text-lighten-1">location_off</i>
        <h5 class="header light">Belum Ada Mitra Ditemukan</h5>
        <p>Maaf, kami belum menemukan mitra laundry yang terdaftar di dekat lokasi Anda saat ini.</p>
    </div>';
}
?>