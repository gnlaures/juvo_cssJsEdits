let infoWindow;
let sidebarMaxHeight;
let markersChanged = [];
let clustersVisibles = 1;
let windowSize = window.innerWidth;
let lastWindowSize;
let idFavorito;
let map;
let multiphoto = 0;
let flexibleSearchMap = 0;
let isLoadingData = false;
if (document.getElementById('flexible-search')) {
  flexibleSearchMap = document.getElementById('flexible-search').value;
}
const bk              = document.getElementById('bk-map').value;
const idioma          = document.getElementById('idioma-map').value;
const purpose         = document.getElementById('FRMPurpose').value;
const galeriaDinamica = document.getElementById('galeriaDinamica').value;
const hasGaleriaDinamica = (galeriaDinamica === '1') ? 'owl-carousel' : '';
const skin            = document.querySelector('input[name="skin"]').value;
const mapsData        = document.getElementById('maps-data');
const modeResort      = document.getElementById('modeResort');
const mouseover       = Number(document.getElementById('mouseover-map').value);
const mapTypeSelected = Number(document.getElementById('map-type-selected').value);
const datosBusqueda = mapsData.dataset.busqueda;
const datosRequest  = mapsData.dataset.request;
const url           = mapsData.dataset.url;
const Maps = function(data, type) {
  this.data = data;
  this.type = type;
}
Maps.prototype = {
  getHtmlResort: function(accommodation, options) {
    let htmlResort =
      '<div class="wrapper-custom-marker">'+
        '<div class="custom-marker">'+
          '<div id="galery_' + accommodation.idResort + '" class="fotografiaR ' + hasGaleriaDinamica + '">';

            accommodation.slider.forEach(function(img) {
              htmlResort +=
                '<a ' + options.target + ' href="' + accommodation.url + '" title="' + accommodation.nombre + '" aria-label="' + accommodation.nombre + '">'+
                  '<img class="owl-lazy" loading="lazy" alt="' + accommodation.nombre + '" data-src="' + img + '" data-owl-src="' + img + '">'+
                '</a>';
            });
    htmlResort +=
      '</div>'+
      '<div class="valoracion_resultados" style="' + options.showRatings + '">'+
        '<div class="MediaValoraciones">' + accommodation.valoraciones + '</div>'+
      '</div>';
    htmlResort +=
      '</div>'+
      '<div class="custom-marker2">'+
        '<div class="map-cabecera">'+
          '<span class="name">' + accommodation.nombre + '</span>'+
        '</div>'+
        '<div class="map-table">' + accommodation.precio + '</div>'+
      '</div>'+
    '</div>';
    return htmlResort;
  },
  getHtmlRedesign: function(accommodation, options) {
    let htmlAccommodation;
    let fomoNotification = '';
    if (skin === 'redesign') {
      const hasReviews = (accommodation.valoraciones != null) ? 'has-reviews' : '';
      htmlAccommodation =
      '<div class="wrapper-custom-marker">'+
        '<div class="custom-marker">'+
          '<div id="galery_' + accommodation.id + '" class="fotografiaR ' + hasGaleriaDinamica + ' ' + hasReviews + '">';
            if (galeriaDinamica === '1' ) {
              jQuery(accommodation.slider.imagenesTour).each(function() {
                if (this.esVideo) {
                  return true;
                }
                htmlAccommodation +=
                  '<a ' + options.target + ' href="' + accommodation.url + '" title="' + accommodation.nombre + '" aria-label="' + accommodation.nombre + '">'+
                    '<img class="owl-lazy" loading="lazy" alt="' + accommodation.nombre + '" data-src="' + accommodation.directorio + this.fichero_big + '" data-owl-src="' + accommodation.directorio + this.fichero_big + '">'+
                  '</a>';
              });
            } else {
              htmlAccommodation +=
                '<a ' + options.target + ' href="' + accommodation.url + '" title="' + accommodation.nombre + '" aria-label="' + accommodation.nombre + '">'+
                  '<img loading="lazy" alt="' + accommodation.nombre + '" data-src="' + accommodation.img.Imagen + '" src="' + accommodation.img.Imagen + '" title="' + accommodation.nombre + '">'+
                '</a>';
            }

      htmlAccommodation +=
        '</div>'+
        '<div class="valoracion_resultados" style="' + options.showRatings + '">'+
          '<div class="MediaValoraciones">' + accommodation.valoraciones + '</div>'+
        '</div>'+
        '<div class="info_alojamiento">';
      if (accommodation.capacidadpersonas > 0) {
        htmlAccommodation += '<span><i class="svg-occupants" alt="' + accommodation.textopersonas + '" title="' + accommodation.textopersonas + '"></i>' + accommodation.capacidadpersonas + '</span> ';
      }
      if (accommodation.dormitorios > 0) {
        htmlAccommodation += '<span><i class="svg-bedrooms" alt="' + accommodation.textohabitaciones + '" title="' + accommodation.textohabitaciones + '"></i>' + accommodation.dormitorios + '</span>';
      }
      htmlAccommodation += '</div>';
      if (accommodation.fomoNotification != null) {
        fomoNotification = accommodation.fomoNotification
      }
      htmlAccommodation +=
        '</div>' +
        '<div class="custom-marker2 bg-marker2">' +
          '<div class="map-cabecera">' +
            '<span class="name">' + accommodation.nombre + '</span>' +
            '<i class="fas fa-map-marker-alt fa-lg marginR5"></i><span class="type">' + accommodation.contenidoTagsSubcabecera + '</span>' +
          '</div>' + fomoNotification +
          accommodation.flexibleSearch +
          '<div class="map-table">' + accommodation.precio + '</div>'+
        '</div>' +
      '</div>';
      return htmlAccommodation;
    }
  },
  getHtmlRedesignMultiphoto: function(accommodation, options) {
    let htmlAccommodation;
    let withoutPhoto = '';
    let classFlexibleSearch = '';
    let fomoNotification = '';
    if (skin === 'redesign') {
      (accommodation.slider.imagenesTour) ? withoutPhoto = '' : withoutPhoto = 'without-photos';
      htmlAccommodation =
      '<div class="wrapper-custom-marker resultados-multiphoto ' + withoutPhoto  + '">'+
        '<div class="custom-marker">'+
          '<div id="galery_' + accommodation.id + '" class="fotografiaR ' + hasGaleriaDinamica + '">';
      if (galeriaDinamica === '1' ) {
        jQuery(accommodation.slider.imagenesTour).each(function() {
          if (this.esVideo) {
            return true;
          }
          htmlAccommodation +=
            '<a ' + options.target + ' href="' + accommodation.url + '" title="' + accommodation.nombre + '" aria-label="' + accommodation.nombre + '">'+
              '<img class="owl-lazy" loading="lazy" alt="' + accommodation.nombre + '" data-src="' + accommodation.directorio + this.fichero_big + '" data-owl-src="' + accommodation.directorio + this.fichero_big + '">'+
            '</a>';
        });
      } else {
        htmlAccommodation +=
          '<a ' + options.target + ' href="' + accommodation.url + '" title="' + accommodation.nombre + '" aria-label="' + accommodation.nombre + '">'+
            '<img loading="lazy" alt="' + accommodation.nombre + '" data-src="' + accommodation.img.Imagen + '" src="' + accommodation.img.Imagen + '">'+
          '</a>';
      }
      htmlAccommodation += '</div>';
      if (accommodation.slider.imagenesTour) {
        htmlAccommodation +=
          '<div class="multiphoto">';
        jQuery(accommodation.slider.imagenesTour).each(function(numberImages) {
          if (this.esVideo) {
            return true;
          }
          if (numberImages < 1) {
            return true;
          }
          htmlAccommodation +=
          '<a ' + options.target + ' href="' + accommodation.url + '" title="' + accommodation.nombre + '" aria-label="' + accommodation.nombre + '">'+
            '<div class="container-image">' +
              '<img class="owl-lazy" loading="lazy" alt="' + accommodation.nombre + '" data-src="' + accommodation.directorio + this.fichero_thumb + '" src="' + accommodation.directorio + this.fichero_thumb + '">' +
            '</div>' +
          '</a>';
          if (numberImages >= 4) {
            return false;
          }
        });
        htmlAccommodation += '</div>';
      }
      htmlAccommodation +='<div class="info_alojamiento">';
      if (accommodation.capacidadpersonas > 0) {
        htmlAccommodation += '<span><i class="svg-occupants" alt="' + accommodation.textopersonas + '" title="' + accommodation.textopersonas + '"></i>' + accommodation.capacidadpersonas + '</span> ';
      }
      if (accommodation.dormitorios > 0) {
        htmlAccommodation += '<span><i class="svg-bedrooms" alt="' + accommodation.textohabitaciones + '" title="' + accommodation.textohabitaciones + '"></i>' + accommodation.dormitorios + '</span>';
      }
      htmlAccommodation += '</div>';
      if (accommodation.flexibleSearch !== '') {
        classFlexibleSearch = 'flexible-search-map';
      }
      if (accommodation.fomoNotification != null) {
        fomoNotification = accommodation.fomoNotification;
      }
      htmlAccommodation +=
        '</div>'+
        '<div class="custom-marker2 ' + classFlexibleSearch + '">'+
          '<div class="map-cabecera">'+
            '<span class="name">' + accommodation.nombre + '</span>'+
            '<i class="fas fa-map-marker-alt fa-lg marginR5"></i><span class="type">' + accommodation.contenidoTagsSubcabecera + '</span>' +
          '</div>'+ fomoNotification +
          '<div class="valoracion_resultados" style="' + options.showRatings + '">'+
            '<div class="MediaValoraciones">' + accommodation.valoraciones + '</div>'+
          '</div>'+
          accommodation.flexibleSearch +
          '<div class="map-table">' + accommodation.precio + '</div>'+
        '</div>'+
      '</div>';
      return htmlAccommodation;
    }
  },
  getHtmlLite: function(accommodation, options) {
    let message;
    let spanPrecio = '';
    if (accommodation.hotel) {
      message = accommodation.tipo;
      accommodation.img.Imagen = accommodation.imghotel;
    } else {
      if (jQuery('#FRMPurpose').val() != 'VENTA') {
        message = accommodation.tipo + '<span class="fa fa-user" alt="' + accommodation.textopersonas + '" title="' + accommodation.textopersonas + '">' + accommodation.capacidadpersonas + '</span>';
        if (accommodation.dormitorios) {
          message = message + '<span class="fa fa-bed" alt="' + accommodation.textohabitaciones + '" title="' + accommodation.textohabitaciones + '">' + accommodation.dormitorios + '</span>';
        }
      } else {
        message = accommodation.tipo + '<span class="fa fa-bed" alt="' + accommodation.textohabitaciones + '" title="' + accommodation.textohabitaciones + '">' + accommodation.dormitorios + '</span>';
      }
    }
    if (accommodation.precio != '') {
      spanPrecio = '<span class="price">' + accommodation.precio + '</span>';
    }
    htmlLite =
    '<div class="wrapper-custom-marker"><div class="custom-marker">'+
      '<a ' + options.target + ' href="' + accommodation.url + '" title="' + accommodation.nombre + '" aria-label="' + accommodation.nombre + '">'+
      '<img loading="lazy" alt="' + accommodation.nombre + '" data-src="' + accommodation.img.Imagen + '" src="' + accommodation.img.Imagen + '"></a>'+
      spanPrecio + '</div><div class="custom-marker2">'+
      '<a href="' + accommodation.url + '" title="' + accommodation.nombre + '" aria-label="' + accommodation.nombre + '"><span class="bottom">'+
      '<span class="type">' + accommodation.nombre + '</span>'+
      '<span class="message">' + message + '</span></span></a>'+
    '</div></div>';
    return htmlLite;
  },
  getHtmlMultiHouse: function(accommodation, options) {
    let htmlAccommodation;
    let target = "_self";
    if (options.target) {
      target = "_blank";
    }
    htmlAccommodation =
    '<div style="cursor: pointer" onclick="window.open(\'' + accommodation.url + '\', \'' + target + '\');">' +
      '<div class="wrapper-multihouse-marker">' +
        '<div class="multihouse-marker-left">' +
          '<img loading="lazy" alt="' + accommodation.nombre + '" data-src="' + accommodation.img.Imagen + '" src="' + accommodation.img.Imagen + '">'+
        '</div>' +
        '<div class="multihouse-marker-right">' +
          '<div>' +
            '<div class="info_alojamiento">';
    if (accommodation.capacidadpersonas > 0) {
      htmlAccommodation += '<span><i class="svg-occupants" alt="' + accommodation.textopersonas + '" title="' + accommodation.textopersonas + '"></i>' + accommodation.capacidadpersonas + '</span> ';
    }
    if (accommodation.dormitorios > 0) {
      htmlAccommodation += '<span><i class="svg-bedrooms" alt="' + accommodation.textohabitaciones + '" title="' + accommodation.textohabitaciones + '"></i>' + accommodation.dormitorios + '</span>';
    }
    htmlAccommodation += '</div>';
    htmlAccommodation += '<div class="map-cabecera">' + '<span class="name">' + accommodation.nombre + '</span>';
    let location = accommodation.ciudad;
    if (skin === 'redesign') {
      location = accommodation.contenidoTagsSubcabecera;
    }
    htmlAccommodation += '<div>' + '<i class="fas fa-map-marker-alt fa-lg marginR5"></i><span class="type">' + location + '</span>' + '</div>';
    htmlAccommodation += '</div></div>';
    if (skin === 'redesign' && accommodation.flexibleSearch) {
      htmlAccommodation += accommodation.flexibleSearch;
    }
    htmlAccommodation += '<div class="map-table">' + accommodation.precio + '</div>' + '</div></div></div>';
    return htmlAccommodation;
  },
  getInfoWindow: function(accommodation) {
    const self = this;
    let options = {
      'target': '',
      'showRatings': 'display:block',
      'setFavorito': 'display:block',
      'delFavorito': 'display:none'
    };
    if (accommodation.targetBlank) {
      options.target = ' target=\'_blank\' ';
    }
    if (!accommodation.valoraciones) {
      options.showRatings = 'display:none';
    }
    if (accommodation.favorito) {
      options.setFavorito = 'display:none';
      options.delFavorito = 'display:block';
    }
    if (skin === 'redesign') {
      if (accommodation.modeResort) {
        return this.getHtmlResort(accommodation, options);
      }
    }
    if (Array.isArray(accommodation)) {
      let htmlMultiHouse = '';
      accommodation.forEach(function(property) {
        htmlMultiHouse += self.getHtmlMultiHouse(property, options);
      });
      return htmlMultiHouse;
    }
    if (skin === 'redesign') {
      multiphoto = document.getElementById('multiphoto').value;
      if (multiphoto == 1) {
        return this.getHtmlRedesignMultiphoto(accommodation, options);
      }
      return this.getHtmlRedesign(accommodation, options);
    }
    return this.getHtmlLite(accommodation, options);
  },
  getAccommodationData: function(marker, callback) {
    const data = {
      bk: bk,
      Idioma: idioma,
      id: marker.id,
      idCRS: marker.idCRS,
      loginGa: marker.loginGa,
      FRMPurpose: purpose,
      datosRequest: datosRequest,
      datosBusqueda: datosBusqueda,
      type: 'window',
      dateFrom: $('#dateFrom').val(),
      dateTo:$('#dateTo').val(),
      AdultNum: $('#AdultNum').val(),
      ChildrenNum: $('#ChildrenNum').val(),
      Child_1_Age: $('#Child_1_Age').val(),
      Child_2_Age: $('#Child_2_Age').val(),
      Child_3_Age: $('#Child_3_Age').val(),
      Child_4_Age: $('#Child_4_Age').val(),
      Child_5_Age: $('#Child_5_Age').val(),
      Child_6_Age: $('#Child_6_Age').val(),
    }
    if (modeResort && modeResort.value == 1) {
      data.modeResort = 1;
      data.idResort = marker.idResort;
    }
    if (marker.multiHouse) {
      data.multiHouse = 1;
    }
    if (!isLoadingData) {
      isLoadingData = true;
      document.body.style.cursor = "wait";
      jQuery.ajax({
        url: url + '?action=mapsdata',
        contentType: "application/json",
        async: 'true',
        dataType: 'json',
        data: data,
        type: 'GET',
        success : function (data) {
          data['latitud'] = marker.latitud;
          data['longitud'] = marker.longitud;
          data['idResort'] = marker.idResort;
          data['loginGa'] = marker.loginGa;
          isLoadingData = false;
          document.body.style.cursor = "default";
          callback(data);
        }
      });
    }
  },
  getAccommodationMultiHouseData: function(markers, callback) {
    let arrayAccommodation = [];
    let chunk = 0;
    markers.forEach(function(marker) {
      const accommodations = {
        id: marker.id,
        idCRS: marker.idCRS,
        loginGa: marker.loginGa
      }
      if (modeResort && modeResort.value == 1) {
        accommodations.modeResort = 1;
        accommodations.idResort = marker.idResort;
      }
      arrayAccommodation.push(accommodations);
    });
    function getDataMultiHouse() {
      const chunkArrayAccommodation = arrayAccommodation.slice(chunk, chunk + 4);
      const data = {
        bk: bk,
        Idioma: idioma,
        FRMPurpose: purpose,
        datosRequest: datosRequest,
        datosBusqueda: datosBusqueda,
        type: 'window',
        multiHouse: 1,
        accommodations: chunkArrayAccommodation,
        dateFrom: $('#dateFrom').val(),
        dateTo:$('#dateTo').val(),
        AdultNum: $('#AdultNum').val(),
        ChildrenNum: $('#ChildrenNum').val(),
        Child_1_Age: $('#Child_1_Age').val(),
        Child_2_Age: $('#Child_2_Age').val(),
        Child_3_Age: $('#Child_3_Age').val(),
        Child_4_Age: $('#Child_4_Age').val(),
        Child_5_Age: $('#Child_5_Age').val(),
        Child_6_Age: $('#Child_6_Age').val()
      }
      chunk = chunk + 4;
      if (!isLoadingData) {
        isLoadingData = true;
        document.body.style.cursor = "wait";
        jQuery.ajax({
          url: url + '?action=mapsdata',
          contentType: "application/json",
          async: 'true',
          dataType: 'json',
          data: data,
          type: 'GET',
          beforeSend: function() {
            if (chunk > 4) {
              const loader = document.createElement('i');
              loader.className = 'icon-spin4 fwk-color';
              document.getElementById('multiHouseContainer').appendChild(loader);
            }
          },
          success: function (response) {
            callback(response, chunk);
            if (document.body.querySelector('#multiHouseContainer .icon-spin4')) {
              document.body.querySelector('#multiHouseContainer .icon-spin4').remove();
            }
            isLoadingData = false;
            document.body.style.cursor = "default";
          }
        });
      }
    }
    if (chunk === 0) {
      getDataMultiHouse();
    }
    function checkScrollLimit() {
      return jQuery('#multiHouseContainer').scrollTop() + jQuery('#multiHouseContainer').innerHeight() >= jQuery('#multiHouseContainer')[0].scrollHeight;
    }
    if (chunk >= 4) {
      document.addEventListener('scroll', function (event) {
        if ( arrayAccommodation.length > chunk && event.target.id === 'multiHouseContainer' && checkScrollLimit() && !document.body.querySelector('#multiHouseContainer .icon-spin4')) {
          getDataMultiHouse();
          event.stopPropagation();
        }
      }, true);
    }
  },
  setStyles: function() {
    const screenResolution = window.screen.width;
    if (screenResolution <= 425 && this.mapTypeSelected == 2) {
      map.panTo(new google.maps.LatLng({lat: marker.latitude, lng: marker.longitude}));
    }
    if ((jQuery('.its--container').length > 0)) {
      jQuery('.bottom').css('color',jQuery('header#header #upper_header').css('color'));
      jQuery('.bottom').css('background-color',jQuery('header#header #upper_header').css('background-color'));
      jQuery('.capacity').css('color',jQuery('header#header #upper_header').css('color'));
      jQuery('.capacity').css('background-color',jQuery('header#header #upper_header').css('background-color'));
      jQuery('span.fa.fa-user').css('color',jQuery('header#header #upper_header').css('background-color'));
      jQuery('span.fa.fa-user').css('background-color',jQuery('header#header #upper_header').css('color'));
      jQuery('span.fa.fa-bed').css('color',jQuery('header#header #upper_header').css('background-color'));
      jQuery('span.fa.fa-bed').css('background-color',jQuery('header#header #upper_header').css('color'));
    }
    if (skin === 'redesign') {
      idFavorito = '#' + jQuery('.custom-marker .favoritos_res').attr('data-id');
      if (galeriaDinamica > 0 || (modeResort && modeResort.value == 1)) {
        loaDivdGalery('.fotografiaR');
      }
      if (this.type == 'results') {
        addFavoritosMapa(idFavorito);
      }
    }
  }
}