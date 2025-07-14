//menangkap id
var keyword = document.getElementById('keyword');
var cariData = document.getElementById('cariData');
var container = document.getElementById('container');

//trigger ketika cariData di klik
// cariData.addEventListener('mouseover', function () {
//     alert('Tombol Ditekan !');
// });

//trigger ketika 
keyword.addEventListener('keyup', function () {
    //alert('Tombol Ditekan !');

    // buat object ajax
    var xhr = new XMLHttpRequest();

    // cek kesiapan ajax
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            //menampilkan di console
            // console.log(xhr.responseText);

            // manipulasi dokumen html id='container'
            container.innerHTML = xhr.responseText;
        }
    }

    // eksekusi ajax
    // metode = GET, sumber = ajax/coba.txt, true = ashyncronous
    xhr.open('GET', 'ajax/mitra.php?keyword=' + keyword.value, true);
    xhr.send();

});

// Fungsi untuk menangani laporan rating
function submitRatingReport(transaksiId, mitraId, alasan) {
    const formData = new FormData();
    formData.append('id_transaksi', transaksiId);
    formData.append('id_mitra', mitraId);
    formData.append('alasan', alasan);
    formData.append('action', 'report_rating');

    fetch('ajax/report_rating.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Berhasil', 'Laporan telah dikirim ke admin untuk ditinjau.', 'success');
            // Disable tombol report
            const reportBtn = document.querySelector(`[data-transaksi-id="${transaksiId}"]`);
            if (reportBtn) {
                reportBtn.disabled = true;
                reportBtn.textContent = 'Sudah Dilaporkan';
                reportBtn.style.background = '#999';
            }
        } else {
            Swal.fire('Error', data.message || 'Gagal mengirim laporan.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error', 'Terjadi kesalahan saat mengirim laporan.', 'error');
    });
}