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

// Ambil semua data mitra dari database yang memiliki koordinat
$result = mysqli_query($connect, "SELECT * FROM mitra WHERE latitude IS NOT NULL AND longitude IS NOT NULL");

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
            <div class="card">
                <a href="detail-mitra.php?id=' . $mitra['id_mitra'] . '">
                    <div class="card-image">
                        <img src="img/mitra/' . htmlspecialchars($mitra['foto']) . '" alt="Foto ' . htmlspecialchars($mitra["nama_laundry"]) . '" style="height: 200px; object-fit: cover;">
                        <span class="card-title" style="background-color: rgba(0,0,0,0.3); padding: 5px 10px; border-radius: 0 8px 0 0;">' . htmlspecialchars($mitra["nama_laundry"]) . '</span>
                    </div>
                </a>
                <div class="card-content" style="padding: 20px;">
                    <p class="truncate" style="color: var(--text-dark);"><i class="material-icons tiny" style="vertical-align: middle;">place</i> ' . htmlspecialchars($mitra["alamat"]) . '</p>
                    <div style="margin-top: 15px; display: flex; justify-content: space-between; align-items: center;">
                        <span class="light" style="font-weight: 500; color: var(--primary-blue);">
                            <i class="material-icons tiny" style="vertical-align: middle;">near_me</i>
                            ' . round($mitra['distance'], 1) . ' km dari Anda
                        </span>
                        <a href="detail-mitra.php?id=' . $mitra['id_mitra'] . '" class="btn-small waves-effect waves-light">Detail</a>
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