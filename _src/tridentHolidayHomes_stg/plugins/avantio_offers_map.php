<?php
$offerid = $map_atts['offerid'];
list($nouse, $querystr) = explode('?', $_SERVER['REQUEST_URI']);
if (!empty($offerid)) {
	$querystr .= '&offerid=' . $offerid;
}
?>
<div class="widget mapa-alojamientos">
	<div id="mapa-alojamientos">
		<span id="maps-data"  data-url="<?php echo admin_url('admin-ajax.php'); ?>" data-request="<?php echo base64_encode($querystr); ?>"></span>
		<input type="hidden" name="FRMPurpose" id="FRMPurpose" value="ALQUILER">
		<input type="hidden" name="skin" id="skin" value="redesign">
		<input type="hidden" name="bk-map" id="bk-map" value="bk_trident">
		<input type="hidden" name="idioma-map" id="idioma-map" value="EN">
		<input type="hidden" name="galeriaDinamica" value="0" id="galeriaDinamica">
		<input type="hidden" name="multiphoto" value="0" id="multiphoto">
		<input type="hidden" name="modeResort" id="modeResort" value="">
		<input type="hidden" name="mouseover-map" id="mouseover-map" value="0">
		<input type="hidden" name="map-type-selected" id="map-type-selected" value="1">
		<input type="hidden" name="map-style-type" id="map-style-type" value="0">
		<input type="hidden" name="api-key-mapbox" id="api-key-mapbox" value="pk.eyJ1IjoiYmFsc2luZ2g4IiwiYSI6ImNsbXRmcDljejA0bGQybGxldGl0NDUybzYifQ.LZt9EgGAjUz3qBBEfkIsmg"> 
		<div class="map-bar">
			<span>Map</span>
			<button id="button-fullscreen-map" class="icon-close" type="button" aria-label="Fullscreen Map"></button>
		</div>
		<div id="map_canvas" class="mapOtherPage">
		  <div id="loading-map">
			<div class="fwk-border spinner"></div>
			<div class="fwk-border spinner-active"></div>
		  </div>
		</div>
	</div>
</div>