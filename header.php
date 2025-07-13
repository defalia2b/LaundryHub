<nav class="blue darken2">
    <div class="container">
        <div class="nav-wrapper">
            <?php
            // --- LOGIKA UNTUK LOGO KIRI (DINAMIS) ---
            $logo_link = 'index.php'; // Default link
            if (isset($_SESSION["login-admin"])) {
                $logo_link = 'admin.php';
            } elseif (isset($_SESSION["login-mitra"])) {
                $logo_link = 'status.php';
            }
            ?>

            <a id="logo-container" href="<?= $logo_link ?>" class="brand-logo"><i class="material-icons left large">local_laundry_service</i>LaundryHub</a>

            <ul class="right hide-on-med-and-down">
                <li>
                    <?php
                    global $connect;

                    if (isset($_SESSION["login-pelanggan"]) && isset($_SESSION["pelanggan"])) {
                        $idPelanggan_header = $_SESSION["pelanggan"];
                        // PERBAIKAN: Menggunakan nama variabel yang unik
                        $query_header = mysqli_query($connect, "SELECT nama FROM pelanggan WHERE id_pelanggan = '$idPelanggan_header'");
                        $data_header = mysqli_fetch_assoc($query_header);
                        $nama_header = $data_header["nama"] ?? 'Pelanggan'; // Default value jika data tidak ditemukan
                        echo "<a href='pelanggan.php'><b>$nama_header</b> (Pelanggan)</a>";

                    } else if (isset($_SESSION["login-mitra"]) && isset($_SESSION["mitra"])) {
                        $id_mitra_header = $_SESSION["mitra"];
                        $query_header = mysqli_query($connect, "SELECT nama_laundry FROM mitra WHERE id_mitra = '$id_mitra_header'");
                        $data_header = mysqli_fetch_assoc($query_header);
                        $nama_header = $data_header["nama_laundry"] ?? 'Mitra';
                        echo "<a href='mitra.php'><b>$nama_header</b> (Mitra)</a>";

                    } else if (isset($_SESSION["login-admin"]) && isset($_SESSION["admin"])) {
                        echo "<a href='admin.php'><span><b>Admin</b> (Admin)</span></a>";

                    } else {
                        echo "<a href='registrasi.php'><b>Registrasi</b></a>";
                    }
                    ?>
                </li>
                <li>
                    <?php
                    if (isset($_SESSION["login-pelanggan"]) || isset($_SESSION["login-mitra"]) || isset($_SESSION["login-admin"])) {
                        echo "<a href='logout.php'><b>Logout</b></a>";
                    } else {
                        echo "<a href='login.php'><b>Login</b></a>";
                    }
                    ?>
                </li>
            </ul>

            <ul id="nav-mobile" class="sidenav">
                <li>
                    <?php
                    if (isset($_SESSION["login-pelanggan"])) {
                        echo "<a href='pelanggan.php'><b>Profil Saya</b></a>";
                    } else if (isset($_SESSION["login-mitra"])) {
                        echo "<a href='mitra.php'><b>Profil Mitra</b></a>";
                    } else if (isset($_SESSION["login-admin"])) {
                        echo "<a href='admin.php'><b>Dasbor Admin</b></a>";
                    } else {
                        echo "<a href='registrasi.php'><b>Registrasi</b></a>";
                    }
                    ?>
                </li>
                <li>
                    <?php
                    if (isset($_SESSION["login-pelanggan"]) || isset($_SESSION["login-mitra"]) || isset($_SESSION["login-admin"])) {
                        echo "<a href='logout.php'><b>Logout</b></a>";
                    } else {
                        echo "<a href='login.php'><b>Login</b></a>";
                    }
                    ?>
                </li>
            </ul>
            <a href="#" data-target="nav-mobile" class="sidenav-trigger"><i class="material-icons">menu</i></a>
        </div>
    </div>
</nav>
<br/>