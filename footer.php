<footer class="page-footer">
    <div class="container">
        <div class="row">
            <div class="col l6 s12">
                <h5 class="white-text">LaundryHub</h5>
                <p class="grey-text text-lighten-4">
                    Solusi laundry praktis, menghubungkan Anda dengan mitra laundry terbaik di sekitar Anda. Proyek ini dibuat untuk mata kuliah Interaksi Manusia Komputer di Universitas Esa Unggul.
                </p>
            </div>
            <div class="col l3 s12">
                <h5 class="white-text">Navigasi</h5>
                <ul>
                    <li><a class="white-text" href="index.php">Home</a></li>
                    <li><a class="white-text" href="login.php">Login</a></li>
                    <li><a class="white-text" href="registrasi.php">Registrasi</a></li>
                    <li><a class="white-text" href="term.php">Syarat & Ketentuan</a></li>
                </ul>
            </div>
            <div class="col l3 s12">
                <h5 class="white-text">Tim Kami</h5>
                <ul>
                    <li><a class="white-text" href="team.php">Aina Rahma Putri</a></li>
                    <li><a class="white-text" href="team.php">Badrus Salam</a></li>
                    <li><a class="white-text" href="team.php">Dewi Farah Aulia</a></li>
                    <li><a class="white-text" href="team.php">Kaysa Dzikrya</a></li>
                    <li><a class="white-text" href="team.php">Maria Karolina</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="footer-copyright">
        <div class="container">
            © 2025 LaundryHub Team. All rights reserved.
        </div>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var sidenav_elems = document.querySelectorAll('.sidenav');
        M.Sidenav.init(sidenav_elems);

        var select_elems = document.querySelectorAll('select');
        M.FormSelect.init(select_elems);

        var modal_elems = document.querySelectorAll('.modal');
        M.Modal.init(modal_elems);

        M.updateTextFields();
    });
</script>