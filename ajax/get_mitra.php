<?php
include '../connect-db.php'; // TAMBAHKAN BARIS INI

header('Content-Type: application/json');

// --- Logika untuk pencarian berdasarkan ID ---
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $mitra_list = [];
    $query = mysqli_query($connect, "SELECT id_mitra, nama_laundry FROM mitra WHERE id_mitra = $id");
    if ($row = mysqli_fetch_assoc($query)) {
        $mitra_list[] = $row;
    }
    echo json_encode($mitra_list);
    exit; // Hentikan skrip setelah selesai
}

// --- Logika untuk pencarian berdasarkan keyword ---
$keyword = isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : '';
$mitra_list = [];

if (strlen($keyword) > 0) {
    $query = mysqli_query($connect, "SELECT id_mitra, nama_laundry FROM mitra WHERE nama_laundry LIKE '%$keyword%' LIMIT 5");
    while ($row = mysqli_fetch_assoc($query)) {
        $mitra_list[] = $row;
    }
}

echo json_encode($mitra_list);
?>