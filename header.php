<?php
// Dapatkan nama file dari halaman yang sedang dibuka untuk menyorot tombol yang aktif
$current_page = basename($_SERVER['PHP_SELF']);
// Buat array yang berisi semua kemungkinan halaman registrasi
$register_pages = ['registrasi.php', 'registrasi-pelanggan.php', 'registrasi-mitra.php', 'registrasi-mitra-harga.php'];

// Cek apakah halaman saat ini adalah salah satu dari halaman registrasi atau halaman login
$is_on_register_page = in_array($current_page, $register_pages);
$is_on_login_page = ($current_page == 'login.php');
?>
<nav class="white" role="navigation">
    <div class="container">
        <div class="nav-wrapper">
            <?php
            // --- LOGIKA UNTUK LOGO KIRI (DINAMIS) ---
            $logo_link = 'index.php'; // Link default
            if (isset($_SESSION["login-admin"])) {
                $logo_link = 'admin.php';
            } elseif (isset($_SESSION["login-mitra"])) {
                $logo_link = 'status.php';
            }
            ?>

            <a id="logo-container" href="<?= $logo_link ?>" class="brand-logo">
                <i class="material-icons left large">local_laundry_service</i>LaundryHub
            </a>

            <ul class="right hide-on-med-and-down">
                <?php
                // Jika ada sesi login (pelanggan, mitra, atau admin)
                if (isset($_SESSION["login-pelanggan"]) || isset($_SESSION["login-mitra"]) || isset($_SESSION["login-admin"])) :
                    ?>
                    <li>
                        <?php
                        global $connect;

                        if (isset($_SESSION["login-pelanggan"]) && isset($_SESSION["pelanggan"])) {
                            $idPelanggan_header = $_SESSION["pelanggan"];
                            $query_header = mysqli_query($connect, "SELECT nama FROM pelanggan WHERE id_pelanggan = '$idPelanggan_header'");
                            $data_header = mysqli_fetch_assoc($query_header);
                            $nama_header = $data_header["nama"] ?? 'Pelanggan';
                            echo "<a href='pelanggan.php'><b>$nama_header</b> (Pelanggan)</a>";

                        } else if (isset($_SESSION["login-mitra"]) && isset($_SESSION["mitra"])) {
                            $id_mitra_header = $_SESSION["mitra"];
                            $query_header = mysqli_query($connect, "SELECT nama_laundry FROM mitra WHERE id_mitra = '$id_mitra_header'");
                            $data_header = mysqli_fetch_assoc($query_header);
                            $nama_header = $data_header["nama_laundry"] ?? 'Mitra';
                            echo "<a href='mitra.php'><b>$nama_header</b> (Mitra)</a>";

                        } else if (isset($_SESSION["login-admin"]) && isset($_SESSION["admin"])) {
                            echo "<a href='admin.php'><span><b>Admin</b></span></a>";
                        }
                        ?>
                    </li>
                    <li>
                        <a href='logout.php' class='waves-effect waves-light btn'>Logout</a>
                    </li>
                <?php
                // Jika tidak ada sesi login
                else:
                    ?>
                    <li>
                        <a href="registrasi.php" class="<?= $is_on_register_page ? 'waves-effect waves-light btn' : '' ?>">Registrasi</a>
                    </li>
                    <li>
                        <a href="login.php" class="<?= ($is_on_login_page || !$is_on_register_page) ? 'waves-effect waves-light btn' : '' ?>">Login</a>
                    </li>
                <?php endif; ?>
            </ul>

            <ul id="nav-mobile" class="sidenav">
                <li><h5 class="center" style="color: var(--primary-blue); margin: 20px 0;">LaundryHub</h5></li>
                <li><div class="divider"></div></li>
                <li>
                    <?php
                    if (isset($_SESSION["login-pelanggan"])) {
                        echo "<a href='pelanggan.php'><i class='material-icons'>person</i>Profil Saya</a>";
                    } else if (isset($_SESSION["login-mitra"])) {
                        echo "<a href='mitra.php'><i class='material-icons'>store</i>Profil Mitra</a>";
                    } else if (isset($_SESSION["login-admin"])) {
                        echo "<a href='admin.php'><i class='material-icons'>dashboard</i>Dasbor Admin</a>";
                    } else {
                        echo "<a href='registrasi.php'><i class='material-icons'>person_add</i>Registrasi</a>";
                    }
                    ?>
                </li>
                <li>
                    <?php
                    if (isset($_SESSION["login-pelanggan"]) || isset($_SESSION["login-mitra"]) || isset($_SESSION["login-admin"])) {
                        echo "<a href='logout.php'><i class='material-icons'>exit_to_app</i>Logout</a>";
                    } else {
                        echo "<a href='login.php'><i class='material-icons'>lock_open</i>Login</a>";
                    }
                    ?>
                </li>
            </ul>
            <a href="#" data-target="nav-mobile" class="sidenav-trigger"><i class="material-icons" style="color: var(--dark-navy);">menu</i></a>
        </div>
    </div>
</nav>