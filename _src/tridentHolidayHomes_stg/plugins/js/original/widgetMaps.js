const mapCanvas = document.getElementById('map_canvas');
const detectElement = new DetectElement(mapCanvas);
let elementDetected = false;
function MapsWidget (data, type, mapTypeSelected) {
    this.data = data;
    this.type = type;
    this.mapTypeSelected = mapTypeSelected;
}
MapsWidget.prototype = {
    initialize: function() {
        const map = this.getMap();
        map.loadMap();
    },
    getMap: function() {
        if (mapTypeSelected === 1) {
            return new MapBox(this.data);
        }
        return new GoogleMaps(this.data, this.type);
    }
}
function loadMap() {
    if (detectElement.getPosition()) {
        elementDetected = true;
        const data = {
            bk: bk,
            Idioma: idioma,
            FRMPurpose: purpose,
            datosRequest: datosRequest,
            datosBusqueda: null,
            type: 'marker'
        }
        if (modeResort && modeResort.value == 1) {
            data.modeResort = modeResort.value;
        }
        jQuery.ajax({
            url: url + '?action=mapsdata',
            contentType: "application/json",
            dataType: 'json',
            data: data,
            type: 'GET',
            success: function (result) {
                const data = result;
				const resultsMaps = new MapsWidget(data, 'home', mapTypeSelected);
				resultsMaps.initialize();
            }
        });
    }
}
if (!elementDetected) {
    setTimeout(function() {
        if (!elementDetected) {
            loadMap();
        }
    }, 0);
    const windowEvent = jQuery(window);
    windowEvent.on('load.detected resize.detected scroll.detected', function() {
        if (!elementDetected) {
            loadMap();
        } else {
            windowEvent.off('load.detected resize.detected scroll.detected');
        }
    });
}
function loaDivdGalery(divLoad) {
    var owl = jQuery(divLoad).owlCarousel({
        lazyLoad: true,
        nav: true,
        navText: ["<i class='icon icon-left-open'></i>","<i class='icon icon-right-open'></i>"],
        items: 1,
        loop: true
    });
    if (jQuery('input[name="skin"]').val() == 'redesign') {
        jQuery('.fa-th').click(function () {
            owl.owlCarousel('resize');
        });
    }
}