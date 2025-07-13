<?php
session_start();
include 'connect-db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "headtags.html"; ?>
    <title>Tim Kami - LaundryHub</title>
</head>
<body>
<?php include "header.php"; ?>
<main class="main-content">
    <div class="container">
        <h3 class="header light center">Tim Pengembang LaundryHub</h3>
        <p class="center light">Orang-orang di balik pengembangan aplikasi LaundryHub.</p>
        <br>

        <div class="row">
            <?php
            $team_members = [
                ["name" => "Aina Rahma Putri", "role" => "Bertanggung jawab atas analisis kebutuhan sistem dan merancang alur kerja pengguna."],
                ["name" => "Dewi Farah Aulia", "role" => "Mengembangkan logika backend menggunakan PHP dan mengelola database MySQL."],
                ["name" => "Badrus Salam", "role" => "Fokus pada desain antarmuka (UI/UX), memastikan tampilan aplikasi menarik dan profesional."],
                ["name" => "Kaysa Dzikrya", "role" => "Membuat website menjadi interaktif dengan JavaScript dan memastikan integrasi sistem."],
                ["name" => "Maria Karolina", "role" => "Bertugas dalam pengujian sistem untuk memastikan semua fitur berjalan sesuai kebutuhan."]
            ];
            ?>
            <?php foreach ($team_members as $member): ?>
                <div class="col s12 m6 l4">
                    <div class="card-panel center hoverable">
                        <img src="img/logo.png" alt="Logo Tim" style="width: 100px; height: 100px; object-fit: contain; margin-bottom: 15px;">
                        <h5 class="header" style="margin-top: 0;"><?= htmlspecialchars($member['name']) ?></h5>
                        <p class="light"><?= htmlspecialchars($member['role']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>
<?php include "footer.php" ?>
</body>
</html>