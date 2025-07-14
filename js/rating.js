// Rating System JavaScript
class RatingSystem {
    constructor() {
        this.currentRating = 0;
        this.init();
    }

    init() {
        this.initRatingStars();
        this.initRatingForms();
        this.initReportButtons();
    }

    initRatingStars() {
        // Inisialisasi bintang rating untuk form
        const ratingContainers = document.querySelectorAll('.rating-stars');
        ratingContainers.forEach(container => {
            const stars = container.querySelectorAll('.rating-star');
            const ratingInput = container.closest('form').querySelector('input[name="rating"]');
            
            stars.forEach((star, index) => {
                star.addEventListener('click', () => {
                    console.log('Star clicked:', index + 1);
                    this.setRating(stars, index + 1, ratingInput);
                });
                
                star.addEventListener('mouseenter', () => {
                    this.highlightStars(stars, index + 1);
                });
                
                // Keyboard navigation
                star.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        console.log('Star selected via keyboard:', index + 1);
                        this.setRating(stars, index + 1, ratingInput);
                    }
                });
            });
            
            container.addEventListener('mouseleave', () => {
                this.resetStars(stars, this.currentRating);
            });
        });
    }

    setRating(stars, rating, input) {
        this.currentRating = rating;
        this.highlightStars(stars, rating);
        if (input) {
            input.value = rating * 2; // Convert to 1-10 scale
        }
        
        // Update text rating
        const ratingText = this.getRatingText(rating);
        const container = stars[0].closest('.rating-container');
        const textElement = container.querySelector('.rating-text');
        if (textElement) {
            textElement.textContent = ratingText;
            textElement.classList.add('selected');
        }
        
        console.log('Rating set to:', rating, 'Value:', input ? input.value : 'no input');
    }

    highlightStars(stars, rating) {
        stars.forEach((star, index) => {
            if (index < rating) {
                star.classList.add('filled', 'active');
                star.style.color = '#ffb400';
                star.style.transform = 'scale(1.1)';
            } else {
                star.classList.remove('filled', 'active');
                star.style.color = '#ddd';
                star.style.transform = 'scale(1)';
            }
        });
    }

    resetStars(stars, rating) {
        stars.forEach((star, index) => {
            if (index < rating) {
                star.classList.add('filled');
                star.classList.remove('active');
                star.style.color = '#ffb400';
                star.style.transform = 'scale(1)';
            } else {
                star.classList.remove('filled', 'active');
                star.style.color = '#ddd';
                star.style.transform = 'scale(1)';
            }
        });
    }

    getRatingText(rating) {
        const texts = {
            1: 'Sangat Buruk',
            2: 'Buruk',
            3: 'Cukup',
            4: 'Baik',
            5: 'Sangat Baik'
        };
        return texts[rating] || '';
    }

    initRatingForms() {
        // Handle form submission untuk rating
        const ratingForms = document.querySelectorAll('.rating-form form');
        console.log('Found', ratingForms.length, 'rating forms');
        
        ratingForms.forEach((form, index) => {
            console.log('Initializing form', index);
            form.addEventListener('submit', (e) => {
                const ratingInput = form.querySelector('input[name="rating"]');
                const commentInput = form.querySelector('textarea[name="komentar"]');
                
                // Debug: log values
                console.log('Form submission - Rating value:', ratingInput ? ratingInput.value : 'no input');
                console.log('Form submission - Comment value:', commentInput ? commentInput.value : 'no input');
                
                // Validasi rating
                if (!ratingInput || !ratingInput.value || ratingInput.value == '0') {
                    e.preventDefault();
                    console.log('Rating validation failed');
                    Swal.fire('Error', 'Silakan berikan rating terlebih dahulu!', 'error');
                    return false;
                }
                
                // Validasi komentar
                if (!commentInput || !commentInput.value.trim()) {
                    e.preventDefault();
                    console.log('Comment validation failed');
                    Swal.fire('Error', 'Silakan berikan komentar!', 'error');
                    return false;
                }
                
                // If we get here, form is valid
                console.log('Form is valid, submitting...');
                return true;
            });
        });
    }

    initReportButtons() {
        // Handle tombol report rating
        const reportButtons = document.querySelectorAll('.report-button');
        reportButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                this.showReportModal(button.dataset.transaksiId, button.dataset.mitraId);
            });
        });
    }

    showReportModal(transaksiId, mitraId) {
        Swal.fire({
            title: 'Laporkan Ulasan',
            html: `
                <div style="text-align: left;">
                    <p>Silakan pilih alasan pelaporan:</p>
                    <select id="alasan-laporan" class="browser-default" style="margin: 10px 0;">
                        <option value="">Pilih alasan...</option>
                        <option value="Komentar kasar atau tidak sopan">Komentar kasar atau tidak sopan</option>
                        <option value="Komentar menyesatkan atau palsu">Komentar menyesatkan atau palsu</option>
                        <option value="Komentar spam atau tidak relevan">Komentar spam atau tidak relevan</option>
                        <option value="Komentar mengandung informasi pribadi">Komentar mengandung informasi pribadi</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                    <textarea id="alasan-custom" placeholder="Jelaskan alasan pelaporan (opsional)" style="width: 100%; height: 100px; margin-top: 10px; display: none;"></textarea>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Laporkan',
            cancelButtonText: 'Batal',
            preConfirm: () => {
                const alasan = document.getElementById('alasan-laporan').value;
                const customAlasan = document.getElementById('alasan-custom').value;
                
                if (!alasan) {
                    Swal.showValidationMessage('Silakan pilih alasan pelaporan');
                    return false;
                }
                
                let finalAlasan = alasan;
                if (alasan === 'Lainnya' && customAlasan.trim()) {
                    finalAlasan = customAlasan.trim();
                }
                
                return { alasan: finalAlasan };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                this.submitReport(transaksiId, mitraId, result.value.alasan);
            }
        });

        // Handle perubahan alasan
        document.getElementById('alasan-laporan').addEventListener('change', function() {
            const customField = document.getElementById('alasan-custom');
            if (this.value === 'Lainnya') {
                customField.style.display = 'block';
            } else {
                customField.style.display = 'none';
            }
        });
    }

    submitReport(transaksiId, mitraId, alasan) {
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

    // Fungsi untuk menampilkan rating yang sudah ada
    displayRating(container, rating, maxRating = 5) {
        const stars = container.querySelectorAll('.rating-star');
        const filledCount = Math.min(rating, maxRating);
        
        stars.forEach((star, index) => {
            if (index < filledCount) {
                star.classList.add('filled');
            } else {
                star.classList.remove('filled');
            }
        });
    }
}

// Inisialisasi rating system saat DOM ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing Rating System...');
    window.ratingSystem = new RatingSystem();
    
    // Tampilkan rating yang sudah ada
    const ratingDisplays = document.querySelectorAll('.rating-display .stars');
    ratingDisplays.forEach(display => {
        const rating = parseInt(display.dataset.rating) || 0;
        const stars = display.querySelectorAll('.rating-star');
        // Convert from 1-10 scale to 1-5 scale for display
        const filledCount = Math.min(Math.round(rating / 2), 5);
        
        stars.forEach((star, index) => {
            if (index < filledCount) {
                star.classList.add('filled');
            } else {
                star.classList.remove('filled');
            }
        });
    });
    
    console.log('Rating System initialized successfully');
}); 