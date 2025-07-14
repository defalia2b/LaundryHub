<?php
session_start();
include '../connect-db.php';

// Hanya mitra yang bisa melaporkan rating
if (!isset($_SESSION["login-mitra"])) {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'report_rating') {
    $id_transaksi = intval($_POST['id_transaksi']);
    $id_mitra = intval($_POST['id_mitra']);
    $alasan = htmlspecialchars($_POST['alasan']);
    
    // Validasi input
    if (empty($alasan)) {
        echo json_encode(['success' => false, 'message' => 'Alasan pelaporan harus diisi']);
        exit;
    }
    
    // Validasi bahwa transaksi ada dan milik mitra ini
    $check_query = mysqli_query($connect, "SELECT id_transaksi FROM transaksi WHERE id_transaksi = $id_transaksi AND id_mitra = $id_mitra");
    if (mysqli_num_rows($check_query) == 0) {
        echo json_encode(['success' => false, 'message' => 'Transaksi tidak ditemukan atau tidak memiliki akses']);
        exit;
    }
    
    // Cek apakah sudah ada laporan untuk transaksi ini
    $check_report = mysqli_query($connect, "SELECT id_laporan FROM laporan_ulasan WHERE id_transaksi = $id_transaksi AND id_mitra = $id_mitra");
    if (mysqli_num_rows($check_report) > 0) {
        echo json_encode(['success' => false, 'message' => 'Anda sudah melaporkan ulasan ini sebelumnya']);
        exit;
    }
    
    // Insert laporan
    $insert_query = mysqli_query($connect, "INSERT INTO laporan_ulasan (id_transaksi, id_mitra, alasan, status_laporan) VALUES ($id_transaksi, $id_mitra, '$alasan', 'Menunggu')");
    
    if ($insert_query) {
        echo json_encode(['success' => true, 'message' => 'Laporan berhasil dikirim']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal mengirim laporan: ' . mysqli_error($connect)]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Request tidak valid']);
}
?> 