// modal search - mobile
if (document.getElementById('mobileFilerDestination')) {
    document.addEventListener('scroll', function() {
        var input = document.getElementById('mobileFilerDestination');
        input.blur();
    });

// Função para verificar o evento de pressionar tecla
    document.addEventListener('keypress', function(e) {
        var key = e.which || e.keyCode;
        if (key === 13) {
            var input = document.getElementById('mobileFilerDestination');
            input.blur();
        }
    });
}

// identify type of page
if (window.location.href.indexOf("/search-ireland/") > -1) {
    document.body.classList.add("p-search-ireland");
}

if (document.getElementById('mobileFilerDestination') && document.getElementById('rentalContent')) {

    // scroll position
    function handleScroll() {
        var scrolled = window.scrollY;

        // Remover classes existentes
        document.body.classList.remove("scrolledUp", "scrolledDown", "scrolledInit", "scrolledEnd");

        // Adicionar classe conforme a posição do scroll
        if (!document.body.classList.contains('--mapView')) {
            if (scrolled === 0) {
                document.body.classList.add("scrolledInit");
                document.getElementById('formFilterAccommodation').classList.remove('--minified');
            } else if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight) {
                document.body.classList.add("scrolledEnd");
                document.getElementById('formFilterAccommodation').classList.add('--minified');
            } else {
                document.body.classList.add("scrolledDown");
                document.getElementById('formFilterAccommodation').classList.add('--minified');
            }
        }

        // Atualiza a última posição do scroll
        handleScroll.lastScrollPosition = scrolled;
    }
    window.addEventListener("scroll", handleScroll);
    handleScroll.lastScrollPosition = 0;

    // edit search
    var spanEditSearchIreland = document.createElement("span");
    spanEditSearchIreland.id = "js-editSearchIreland";
    document.getElementById("formFilterAccommodation").appendChild(spanEditSearchIreland);
    spanEditSearchIreland.addEventListener("click", function() {
        document.getElementById("formFilterAccommodation").classList.remove("--minified");
    });

    // identify view type
    document.addEventListener("DOMContentLoaded", function() {
        var tabOne = document.querySelector("#formFilterAccommodation .botonR_fondo .list_mobileFilter .tabOne");
        var tabTwo = document.querySelector("#formFilterAccommodation .botonR_fondo .list_mobileFilter .tabTwo");

        tabOne.addEventListener("click", function() {
            document.body.classList.add("--listView");
            document.body.classList.remove("--mapView");
        });

        tabTwo.addEventListener("click", function() {
            document.body.classList.add("--mapView");
            document.body.classList.remove("--listView");
            document.body.classList.remove("scrolledUp", "scrolledDown", "scrolledInit", "scrolledEnd");
            $('html, body').animate({scrollTop: 0}, 'fast');
            setTimeout(function() {
                document.getElementById("formFilterAccommodation").classList.add("--minified");
            }, 500);

        });
    });

}