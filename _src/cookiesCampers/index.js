document.addEventListener('DOMContentLoaded', function() {

    var hamburgerButtons = document.querySelectorAll('.jkit-hamburger-menu');
    hamburgerButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            document.body.classList.add('js-nav-active');
        });
    });

    var hamburgerButtonsClose = document.querySelectorAll('.jkit-close-menu');
    hamburgerButtonsClose.forEach(function(button) {
        button.addEventListener('click', function() {
            document.body.classList.remove('js-nav-active');
        });
    });

});






