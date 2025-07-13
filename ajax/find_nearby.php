<?php
include '../connect-db.php';

// Validasi input latitude dan longitude
if (!isset($_GET['lat']) || !isset($_GET['lon'])) {
    die("Lokasi tidak valid.");
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

// Ambil semua data mitra dari database
$result = mysqli_query($connect, "SELECT * FROM mitra WHERE latitude IS NOT NULL AND longitude IS NOT NULL");

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

// Tampilkan hasilnya dalam format HTML
if (count($mitra_list) > 0) {
    echo '<h4 class="header light center">Mitra Laundry Terdekat</h4>';
    echo '<div class="row card-panel">';
    foreach ($mitra_list as $mitra) {
        // Tampilkan hanya 5 mitra terdekat sebagai contoh
        if(count($mitra_list) > 5 && $mitra['distance'] > 10){ continue; } // Optional: batasi jarak maks 10km jika terlalu banyak

        echo '
        <div class="col s12 m6 l4">
            <div class="icon-block center">
                <a href="detail-mitra.php?id=' . $mitra['id_mitra'] . '">
                    <img src="img/mitra/' . $mitra['foto'] . '" class="circle responsive-img" width="50%" />
                </a>
                <h5 class="center">
                    <a href="detail-mitra.php?id=' . $mitra['id_mitra'] . '">' . htmlspecialchars($mitra["nama_laundry"]) . '</a>
                </h5>
                <p class="light">
                    <b>' . round($mitra['distance'], 1) . ' km dari Anda</b><br>
                    ' . htmlspecialchars($mitra["alamat"]) . ', ' . htmlspecialchars($mitra["kota"]) . '
                </p>
            </div>
        </div>
        ';
    }
    echo '</div>';
} else {
    echo '<h5 class="header light center">Maaf, belum ada mitra laundry yang terdaftar di dekat Anda.</h5>';
}

?>