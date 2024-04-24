let selfMap;
let popup;
let customLocationFlag = true;
let lastClickMarkerLong = 0;
let lastClickMarkerLat = 0;
let heightDivCanvas;
let divMapaAlojamientos;
let divMapBar;
if (document.getElementById('mapa-alojamientos')) {
  heightDivCanvas = document.getElementById('mapa-alojamientos').clientHeight;
  divMapaAlojamientos = document.getElementById('mapa-alojamientos');
  divMapBar = document.querySelector('.map-bar');
}
function MapBox(data, type) {
  Maps.call(this, data, type);
  this.maxZoom = this.data.zoom_limit;
  this.urlRewriteOnline = window.location.hostname;
  this.markerType;
  this.markerPath;
  this.favMarkerPath;
  this.clusterMarkerPath;
  this.multiHouseMarkerPath;
  this.mapResultsVersion;
  this.script = document.createElement('script');
  this.script2 = document.createElement('script');
  this.setParametersMap();
};
MapBox.prototype = Object.create(Maps.prototype);
/*********************Set Parameters***********************/ 
MapBox.prototype.setParametersMap = function () {
  if (document.getElementById('zoomMap') && document.getElementById('coordinatesClient')) {
    this.zoom = document.getElementById('zoomMap').value;
    this.coordinates = JSON.parse(document.getElementById('coordinatesClient').value);
  }
}
/*********************Set Icons****************************/ 
MapBox.prototype.setUrlIcons = function () {
  this.markerType = this.data.markerType;
  this.markerPath = this.data.marker;
  this.favMarkerPath = this.data.favmarker;
  this.clusterMarkerPath = this.data.clustermarker;
  this.multiHouseMarkerPath = this.data.multihousemarker;
  this.mapResultsVersion = this.data.mapResultsVersion;
}
/*********************Check Dev****************************/ 
MapBox.prototype.checkIsDev = function () {
  if (~this.urlRewriteOnline.indexOf(".local")) {
    return true;
  }
  return false;
}
/*********************Load Map****************************/ 
/*MapBox.prototype.loadMap = function () {
  selfMap = this;
  this.script.async = true;
  this.script.src = '/wp-content/plugins/avantio-api-integration/js/mapbox-gl.js';
  this.script.type = 'text/javascript';
  jQuery(document).ready(function() {
    setTimeout(function() {
      document.body.appendChild(selfMap.script);
    }, 2000);
  });
  this.script.addEventListener('load', function() {
    selfMap.script2.async = true;
    selfMap.script2.src = '/wp-content/plugins/avantio-api-integration/js/mapboxgl-spiderifier.js';
    selfMap.script2.type = 'text/javascript';
    document.body.appendChild(selfMap.script2);
    selfMap.script2.addEventListener('load', function() {
      selfMap.setUrlIcons();
      selfMap.initMap();
      selfMap.addImages();
      map.on('load', function () {
        selfMap.addMarkers();
        selfMap.addEventsMarkers();
        const loadingMap = document.getElementById('loading-map');
        loadingMap.style.display = 'none';
      });
    });
  });
};*/
MapBox.prototype.loadMap = function () {
  var selfMap = this;
  selfMap.setUrlIcons();
  selfMap.initMap();
  selfMap.addImages();

  map.on('load', function () {
    selfMap.addMarkers();
    selfMap.addEventsMarkers();
    var loadingMap = document.getElementById('loading-map');
    loadingMap.style.display = 'none';
  });
};
/*********************Get Token****************************/ 
MapBox.prototype.getAccessToken = function () {
  return document.getElementById('api-key-mapbox').value;
}
/*********************Initialize Map****************************/ 
MapBox.prototype.initMap = function () {
  mapboxgl.accessToken = this.getAccessToken();
  if (!this.coordinates) {
    this.coordinates = { "lat" : 53.7798, "lng" : -7.3055 };
    customLocationFlag = false;
  }
  if (!this.zoom) {
    this.zoom = 6;
  }
  map = new mapboxgl.Map({
    container: 'map_canvas',
    style: this.data.mapStyle,
    center: [this.coordinates['lng'], this.coordinates['lat']],
    maxZoom: 17,
    minZoom: 2,
    zoom: this.zoom,
    scrollZoom: false,
    dragPan: false,
    attributionControl: true
  });
  if (document.getElementById('map-style-type').value == 1) {
    map.setStyle('mapbox://styles/mapbox/satellite-streets-v10');
  }
  map.addControl(new mapboxgl.NavigationControl(), 'bottom-right');
  selfMap.createSpiderifier();
};
/*********************Create MapboxglSpiderifier****************************/  
MapBox.prototype.createSpiderifier = function () {
  if (this.mapResultsVersion == 2 && this.markerType == 0) {
    spiderifier = new MapboxglSpiderifier(map, {
      animate: true,
      animationSpeed: 200,
      customPin: true,
      initializeLeg: this.initializeSpiderLeg,
      circleSpiralSwitchover: 15,
      circleFootSeparation: 90, 
      spiralFootSeparation: 30
    });
  }
  if (!(this.mapResultsVersion == 2 && this.markerType == 0)) {
    spiderifier = new MapboxglSpiderifier(map, {
      animate: true,
      animationSpeed: 200,
      customPin: true,
      initializeLeg: this.initializeSpiderLeg,
      circleSpiralSwitchover: 30,
      circleFootSeparation: 34, 
      spiralFootSeparation: 36
    });
  }
};
/*********************Initialize Spider Leg****************************/ 
MapBox.prototype.initializeSpiderLeg = function (spiderLeg) {
  const pinElem = spiderLeg.elements.pin;
  const feature = spiderLeg.feature;
  pinElem.innerHTML = '<img loading="lazy" alt="' + accommodation.nombre + '" data-src="' + selfMap.markerPath + '" src="' + selfMap.markerPath + '">';
  jQuery(pinElem)
    .on('click', function() {
      if (popup !== undefined) {
        popup.remove();
      }
      selfMap.getAccommodationData(feature, function(accommodationData) {
        const infoWindow = selfMap.getInfoWindow(accommodationData);
        popup = new mapboxgl.Popup({
          closeButton: true,
          closeOnClick: false,
          offset: MapboxglSpiderifier.popupOffsetForSpiderLeg(spiderLeg)
        });
        popup.setHTML(infoWindow)
        .addTo(map);
        offset = -125;
        if (multiphoto == 1) {
          offset = -200;
        }
        spiderLeg.mapboxMarker.setPopup(popup);
        map.panTo(popup.getLngLat(), {
          offset: [0, offset]
        });
        selfMap.setStyles();
      });
    });
}
/*********************Open Pop Up Event****************************/ 
MapBox.prototype.openMultiHousePopUp = function (properties) {
  map.getSource('locations').getClusterChildren(properties.cluster_id, function(err, data) {
    const coordinates = [data[0].geometry.coordinates[0], data[0].geometry.coordinates[1]];
    const arrayProperties = [];
    data.forEach(function(property) {
      arrayProperties.push(property.properties);
    });
    selfMap.getAccommodationMultiHouseData(arrayProperties, function(accommodationMultiHouseData, chunk) {
      let offset = -125;
      const infoWindow = selfMap.getInfoWindow(accommodationMultiHouseData);
      const multiHouseContainer = '<div id="multiHouseContainer"></div>';
      if (chunk === 4) {
        popup = new mapboxgl.Popup()
        .setLngLat(coordinates)
        .setHTML(multiHouseContainer)
        .addTo(map);
        let center = popup.getLngLat();
        map.panTo(center, {
          offset: [0, offset]
        });
        selfMap.setStyles();
      }
      document.getElementById('multiHouseContainer').insertAdjacentHTML('beforeend', infoWindow);
    });
  });
}
/*********************Multihouse Pop up Event****************************/ 
MapBox.prototype.multiHousePopUp = function (e) {
  selfMap.openMultiHousePopUp(e.features[0].properties);
}
/*********************Filter Data Locations**************************/ 
MapBox.prototype.filterDataLocations = function (locations) {
  const geoJSONData = {
    'type': 'FeaturedCollection',
    'features': []
  };
  geoJSONData.features = locations.map(function (location) {
    return {
      'type': 'Feature',
      'properties': location,
      'geometry': {
        'type': 'Point',
        'coordinates': [
          location.longitud,
          location.latitud
        ]
      }
    }
  });
  return geoJSONData;
};
/*********************Add Images Markers****************************/ 
MapBox.prototype.addImages = function () {
  map.loadImage(this.clusterMarkerPath, function(error, image) {
    if (error) {
      throw error;
    }
    map.addImage('clusterMarkerPath', image);
  });
  map.loadImage(this.markerPath, function(error, image) {
    if (error) {
      throw error;
    }
    map.addImage('markerPath', image);
  });
  map.loadImage(this.favMarkerPath, function(error, image) {
    if (error) {
      throw error;
    }
    map.addImage('favMarkerPath', image);
  });

  map.loadImage(this.multiHouseMarkerPath, function(error, image) {
    if (error) {
      throw error;
    }
    map.addImage('multiHouseMarkerPath', image);
  });
}
/*********************Add Markers****************************/ 
MapBox.prototype.addMarkers = function () {
  const geoJSONData = this.filterDataLocations(this.data.locations);
  map.addSource("locations", {
    type: "geojson",
    data: geoJSONData,
    cluster: true,
    clusterMaxZoom: 20,
    clusterRadius: 40
  });
  selfMap.addMarkersToMapbox();
  if (!customLocationFlag) {
    const bounds = new mapboxgl.LngLatBounds();
    geoJSONData.features.forEach(function (feature) {
      bounds.extend(feature.geometry.coordinates);
    });
    map.fitBounds(bounds, {padding: 100});
  }
}
/*********************Add Markers To Mapbox****************************/ 
MapBox.prototype.addMarkersToMapbox = function () {
  let clusterMarkerTextColor = '#ffffff';
  let clusterMarkerTextOffset = [0,-1.9];
  if (this.markerType == 1) {
    clusterMarkerTextColor = '#007072';
  }
  if (this.markerType == 2) {
    clusterMarkerTextOffset = [0,-2];
  }
  if (this.markerType == 3) {
    clusterMarkerTextColor = '#007072';
    clusterMarkerTextOffset = [0.1,-2.3];
  }
  if (this.mapResultsVersion == 2 && this.markerType == 0) {
    clusterMarkerTextColor = '#007072';
    clusterMarkerTextOffset = [0, -0.1];
  }
  if (this.mapResultsVersion == 2 && this.markerType == 0) {
    map.addLayer({
      'id': "markers",
      'type': "circle",
      'source': 'locations',
      'filter': ["!has", "point_count"],
      'paint': {
        'circle-radius': {
          'base': 1.75,
          'stops': [[12, 18], [18, 120]]
        },
        'circle-color': 'rgba(0, 114, 115, 0.4)'
      }
    });
    map.addLayer({
      'id': "cluster",
      'type': "circle",
      'source': "locations",
      'filter': ["has", "point_count"],
      'maxzoom' : 20,
      'paint': {
        'circle-radius': {
          'base': 1.75,
          'stops': [[12, 25], [18, 85]]
        },
        'circle-color': 'rgba(0, 114, 115, 0.4)'
      }
    });
    map.addLayer({
      'id': "cluster-count",
      'type': "symbol",
      'source': "locations",
      'filter': ["has", "point_count"],
      'maxzoom': 20,
      'layout': {
        "text-field": "{point_count_abbreviated}",
        "text-font": ["DIN Offc Pro Medium", "Arial Unicode MS Bold"],
        "text-size": 12
      }
    });
    map.addLayer({
      'id': "multiHouseMarker",
      'type': "circle",
      'source': "locations",
      'filter': ["has", "point_count"],
      minzoom: 16,
      maxzoom: 20,
      'paint': {
        'circle-radius': {
          'base': 1.75,
          'stops': [[12, 25], [18, 85]]
        },
        'circle-color': 'rgba(155, 155, 155, 0)'
      }
    });
  }
  if (!(this.mapResultsVersion == 2 && this.markerType == 0)) {
    map.addLayer({
      id: "markers",
      type: "symbol",
      source: "locations",
      filter: ["!has", "point_count"],
      layout: {
        "icon-image": "markerPath",
        "icon-size": 1,
        "icon-ignore-placement": true,
        "icon-offset": [0,-20]
      }
    });
    map.addLayer({
      id: "cluster",
      type: "symbol",
      source: "locations",
      filter: ["has", "point_count"],
      maxzoom: 17,
      layout: {
        "text-field": "{point_count}",
        "text-size": 13,
        "text-font": ["Arial Unicode MS Bold"],
        "text-offset": clusterMarkerTextOffset,
        "icon-image": "clusterMarkerPath",
        "icon-size": 1,
        "icon-ignore-placement": true,
        "icon-offset": [0,-20]
      },
      paint: {
        "text-color": clusterMarkerTextColor,
      }
    });
    map.addLayer({
      id: "multiHouseMarker",
      type: "symbol",
      source: "locations",
      filter: ["has", "point_count"],
      minzoom: 17,
      maxzoom: 20,
      layout: {
        "icon-image": "multiHouseMarkerPath",
        "icon-size": 1,
        "icon-ignore-placement": true
      }
    });
    map.addLayer({
      id: "favMarker",
      type: "symbol",
      source: "locations",
      filter: ["==", "favorito", true],
      layout: {
        "icon-image": "favMarkerPath",
        "icon-size": 1,
        "icon-ignore-placement": true,
        "icon-offset": [0,-20]
      }
    });
  }
}
/*********************Cluster Event****************************/ 
MapBox.prototype.clusterEvent = function (e) {
  const features = map.queryRenderedFeatures(e.point, { layers: ['cluster'] });
  const clusterId = features[0].properties.cluster_id;
  map.getSource('locations').getClusterExpansionZoom(clusterId, function (err, zoom) {
      if (err) {
        return;
      }
      map.easeTo({
          center: features[0].geometry.coordinates,
          zoom: zoom
      });
  });
}
/*********************MouseMove Event****************************/ 
MapBox.prototype.mouseMove = function (e) {
  var features = map.queryRenderedFeatures(e.point, {
    layers: ['multiHouseMarker']
  });
}
/*********************Open Pop Up Event****************************/ 
MapBox.prototype.openPopUp = function (properties) {
  this.getAccommodationData(properties, function(accommodationData) {
    const coordinates = [accommodationData.longitud, accommodationData.latitud];
    const infoWindow = selfMap.getInfoWindow(accommodationData);
    let options = {};
    let offset = -125;
    if (popup !== undefined) {
      popup.remove();
    }
    if (multiphoto == 1) {
      options.className = 'container-multiphoto';
      offset = -200;
    }
    popup = new mapboxgl.Popup(options)
    .setLngLat(coordinates)
    .setHTML(infoWindow)
    .addTo(map);
    let center = popup.getLngLat();
    map.panTo(center, {
      offset: [0, offset]
    });
    selfMap.setStyles();
  });
}
/******************Zoom On Click Marker Event********************/ 
MapBox.prototype.zoomOnClickMarker = function (e) {
  if ((map.getZoom() < 13) && (lastClickMarkerLong != e.longitud && lastClickMarkerLat != e.latitud)) {
    map.flyTo({
      center: [e.longitud, e.latitud],
      zoom: 13,
      speed: 2,
      curve: 1,
      easing(t) {
        return t;
      }
    });
  }
  lastClickMarkerLong = e.longitud;
  lastClickMarkerLat = e.latitud;
}

/*********************Add Events Marker****************************/ 
MapBox.prototype.addEventsMarkers = function () {
  map.on('click', function(e) {    
    map['scrollZoom'].enable();
    if (selfMap.type === 'results') {
      addFavoritosResultados(idFavorito);
    }
    if (popup !== undefined) {
      popup.remove();
    }
    selfMap.resizeMapControl();
  });
  map.on('zoomstart', function() {
    spiderifier.unspiderfy();
  });
  map.on('mousemove', function(e) {
    map['dragPan'].enable();
  });
  map.on('mouseout', function(e) {
    map['dragPan'].disable();
    map['scrollZoom'].disable();
  });
  map.on('mousemove', this.mouseMove);
  map.on('click', 'multiHouseMarker', function(event) {
    return selfMap.multiHousePopUp(event);
  });
  map.on('click', 'cluster', this.clusterEvent);
  map.on('click', 'markers', function(e) {
    selfMap.zoomOnClickMarker(e.features[0].properties);
    selfMap.openPopUp(e.features[0].properties);
  });
  if (mouseover && modeResort) {
    map.on('mouseover', 'markers', function(e) {
      selfMap.openPopUp(e.features[0].properties);
    });
    map.on('mouseover', 'multiHouseMarker', function(event) {
      return selfMap.multiHousePopUp(event);
    });
  }
  map.on('mouseenter', 'markers', function () {
    map.getCanvas().style.cursor = 'pointer';
  });
  map.on('mouseleave', 'markers', function () {
      map.getCanvas().style.cursor = '';
  });
  map.on('mouseenter', 'cluster', function () {
    map.getCanvas().style.cursor = 'pointer';
  });
  map.on('mouseleave', 'cluster', function () {
      map.getCanvas().style.cursor = '';
  });
  map.on('mouseenter', 'multiHouseMarker', function () {
    map.getCanvas().style.cursor = 'pointer';
  });
  map.on('mouseleave', 'multiHouseMarker', function () {
      map.getCanvas().style.cursor = '';
  });
};
/*********************Control Fullscreen****************************/
MapBox.prototype.resizeMapControl = function () {
  if (document.getElementById('button-fullscreen-map')) {
    if (selfMap.isMobile()) {
      document.getElementById('button-fullscreen-map').addEventListener('click',  selfMap.controlButtonMap);
      divMapaAlojamientos.addEventListener('click', selfMap.fullScreenMap);
    }
  }
  jQuery(window).resize(function() {
    document.querySelector('#mapa-alojamientos canvas').style.height = heightDivCanvas + "px";
    map.resize();
    if (selfMap.isMobile()) {
      document.getElementById('button-fullscreen-map').addEventListener('click',  selfMap.controlButtonMap);
      divMapaAlojamientos.addEventListener('click', selfMap.fullScreenMap);
      return;
    }
    if(!selfMap.isMobile()) {
      document.getElementById('button-fullscreen-map').removeEventListener('click',  selfMap.controlButtonMap);
      divMapaAlojamientos.removeEventListener('click', selfMap.fullScreenMap);
      divMapBar.classList.remove('on');
      divMapBar.style.display = 'none';
      map.resize();
    }
  });
}
MapBox.prototype.controlButtonMap = function() {
  document.querySelector('#mapa-alojamientos canvas').style.height = heightDivCanvas + 'px';
  divMapBar.classList.remove('on');
  divMapBar.style.display = 'none';
  divMapaAlojamientos.classList.remove('map-fullscreen');
  document.querySelector('#map_canvas').style.height = '100%';
  map.resize();
  event.stopPropagation();
}
MapBox.prototype.fullScreenMap = function() {
  document.querySelector('#mapa-alojamientos canvas').style.height = heightDivCanvas + 'px';
  divMapBar.style.display = 'block';
  document.querySelector('#map_canvas').style.height = '100vh';
  divMapaAlojamientos.classList.add('map-fullscreen');
  map.resize();
  setTimeout(() => {
    map.resize();
    divMapBar.classList.add('on');
  }, 250);
}
MapBox.prototype.isMobile = function () {
  if (window.innerWidth <= 425 && navigator.platform != 'iPhone' && navigator.platform != 'iPad') {
    return true;
  }
  return false;
}