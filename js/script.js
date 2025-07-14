$(document).ready(function () {
    $('.sidenav').sidenav();
});

document.addEventListener('DOMContentLoaded', function () {
    // Initialize sliders if they exist
    var sliderElems = document.querySelectorAll('.slider');
    if (sliderElems.length > 0) {
        var sliderInstances = M.Slider.init(sliderElems);
    }
    
    // Kode rating-star di-nonaktifkan, sudah di-handle oleh js/rating.js
    /*
    var ratingStars = document.querySelectorAll('.rating-star');
    if (ratingStars.length > 0) {
        ratingStars.forEach(function(star) {
            star.addEventListener('click', function() {
                var container = this.closest('.rating-stars');
                var stars = container.querySelectorAll('.rating-star');
                var rating = Array.from(stars).indexOf(this) + 1;
                
                // Update stars display
                stars.forEach(function(s, index) {
                    if (index < rating) {
                        s.classList.add('filled');
                    } else {
                        s.classList.remove('filled');
                    }
                });
                
                // Update hidden input if exists
                var hiddenInput = container.parentElement.querySelector('input[name="rating"]');
                if (hiddenInput) {
                    hiddenInput.value = rating * 2; // Convert to 1-10 scale
                }
                
                // Update rating text if exists
                var ratingText = container.parentElement.querySelector('.rating-text');
                if (ratingText) {
                    var texts = ['', 'Sangat Buruk', 'Buruk', 'Cukup', 'Baik', 'Sangat Baik'];
                    ratingText.textContent = texts[rating] || '';
                }
            });
        });
    }
    */
});