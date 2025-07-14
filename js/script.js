$(document).ready(function () {
    $('.sidenav').sidenav();
});

document.addEventListener('DOMContentLoaded', function () {
    // Initialize sliders if they exist
    var sliderElems = document.querySelectorAll('.slider');
    if (sliderElems.length > 0) {
        var sliderInstances = M.Slider.init(sliderElems);
    }
});