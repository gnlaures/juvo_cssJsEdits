<?php
if (!defined('ABSPATH')) exit;

$arrpropids = [];
$affordable = [];
$pet_friendly = [];
$beachfront = [];
$popular = [];
$handicaped = [];
$longtermrental = [];
$amazing_views = [];
$evcharger = [];
$parameters = ['display_type' => 'offers'];
$accommodations = getAccommodations($parameters);
if (!empty($accommodations)) {
	foreach ($accommodations as $accommodation) {
		$WeeklyPrice = round($accommodation->WeeklyPrice ?? 0);
		$arrpropids[] = (int)$accommodation->AccommodationId;
		if ($WeeklyPrice <= 500) {
			$affordable[] = (int)$accommodation->AccommodationId;
		}
		if ($accommodation->pet_friendly) {
			$pet_friendly[] = (int)$accommodation->AccommodationId;
		}
		if ($accommodation->beachfront) {
			$beachfront[] = (int)$accommodation->AccommodationId;
		}
		if ($accommodation->popular) {
			$popular[] = (int)$accommodation->AccommodationId;
		}
		if ($accommodation->disabled_friendly) {
			$handicaped[] = (int)$accommodation->AccommodationId;
		}
		if ($accommodation->longtermrental) {
			$longtermrental[] = (int)$accommodation->AccommodationId;
		}
		if ($accommodation->amazing_views) {
			$amazing_views[] = (int)$accommodation->AccommodationId;
		}
		if ($accommodation->charger) {
			$evcharger[] = (int)$accommodation->AccommodationId;
		}
	}
}
?>
<section class="propSea" id="propSea">
	<div class="propTot">
		<div id="filterContainer-del" class="aai-swiper-container filtersSection">
			<div id="aai-owl-carousel" class="flex-container owl-carousel">
				<label class="filter-label flex-item">
			        <input type="checkbox" class="filter-checkbox" data-filter="affordable">
			        <span class="overlay-bg"></span>
			        <span class="svg-filter-affordable"></span>
			        <span class="filter-text">Affordable</span>
			    </label>
		    	<label class="filter-label flex-item">
			        <input type="checkbox" class="filter-checkbox" data-filter="pet_friendly">
			        <span class="overlay-bg"></span>
			        <span class="svg-filter-dogfriendly"></span>
			        <span class="filter-text">Dog Friendly</span>
		    	</label>
		    	<label class="filter-label flex-item">
			        <input type="checkbox" class="filter-checkbox" data-filter="beachfront">
			        <span class="overlay-bg"></span>
			        <span class="svg-filter-beach"></span>
			        <span class="filter-text">Close to Beach</span>
		    	</label>
		    	<label class="filter-label flex-item">
			        <input type="checkbox" class="filter-checkbox" data-filter="popular">
			        <span class="overlay-bg"></span>
			        <span class="svg-filter-popular"></span>
			        <span class="filter-text">Rated 4.5+</span>
		    	</label>
		    	<label class="filter-label flex-item">
			        <input type="checkbox" class="filter-checkbox" data-filter="handicaped">
			        <span class="overlay-bg"></span>
			        <span class="svg-filter-adapted"></span>
			        <span class="filter-text">Adapted</span>
		    	</label>
		    	<?php
		    	/*<label class="filter-label flex-item">
			        <input type="checkbox" class="filter-checkbox" data-filter="longtermrental">
			        <span class="overlay-bg"></span>
			        <span class="svg-filter-longtermrentals"></span>
			        <span class="filter-text">Long Term Rental</span>
		    	</label>
		    	*/
		    	?>
		    	<label class="filter-label flex-item">
			        <input type="checkbox" class="filter-checkbox" data-filter="amazing_views">
			        <span class="overlay-bg"></span>
			        <span class="svg-filter-amazingviews"></span>
			        <span class="filter-text">Nice Views</span>
		    	</label>
		    	<label class="filter-label flex-item">
			        <input type="checkbox" class="filter-checkbox" data-filter="evcharger">
			        <span class="overlay-bg"></span>
			        <span class="svg-filter-evcharger"></span>
			        <span class="filter-text">Electric Car Charger</span>
		    	</label>
		    </div>
		</div>
	</div>
	<ul id="prop-view" class="active-offers-property-view"></ul>
	<div id="loading" class="loadingStatic">
		<div class="fwk-border spinner"></div>
		<div class="fwk-border spinner-active"></div>
	</div>
	<div id="loadMoreButton" class="loadMoreButtonShowHide">
		<button class="loadmore-button-wrapper" onclick="loadOffersHomes();" title="Load More" aria-label="Load More">Load More <span class="svg-readmore"></span></button>
	</div>
</section>
<script>
<?php
echo "var javascript_array = ". json_encode($arrpropids) . ";\n";
echo "var affordable = ". json_encode($affordable) . ";\n";
echo "var beachfront = ". json_encode($beachfront) . ";\n";
echo "var pet_friendly = ". json_encode($pet_friendly) . ";\n";
echo "var handicaped = ". json_encode($handicaped) . ";\n";
echo "var evcharger = ". json_encode($evcharger) . ";\n";
echo "var popular = ". json_encode($popular) . ";\n";
echo "var longtermrental = ". json_encode($longtermrental) . ";\n";
echo "var amazing_views = ". json_encode($amazing_views) . ";\n";
?>
var offersUrl = '<?php echo admin_url('admin-ajax.php'); ?>?action=accommodations';
</script>