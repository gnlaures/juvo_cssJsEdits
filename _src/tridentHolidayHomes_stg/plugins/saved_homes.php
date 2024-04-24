<div class="saved-homes propSea" id="propSea">
	<p class="textcenter">You have <span id="homescount"></span> home(s) saved.</p>
	<ul id="prop-view" class="active-offers-property-view"></ul><br />
	<div id="loading" class="loadingStatic">
		<div class="fwk-border spinner"></div>
		<div class="fwk-border spinner-active"></div>
	</div>
	<div id="loadMoreButton" class="loadMoreButtonShowHide">
		<button class="loadmore-button-wrapper" onclick="loadSavedHomes();" title="Load More" aria-label="Load More">Load More <span class="svg-readmore"></span></button>
	</div>
	<br /><br />
</div>
<script>
var savedhomesUrl = '<?php echo admin_url('admin-ajax.php'); ?>?action=accommodations';
</script>