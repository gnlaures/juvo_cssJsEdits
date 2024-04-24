<?php
if (!defined('ABSPATH')) exit;

$uriSegments = explode("/", $_SERVER['REQUEST_URI']);
$querystr = '';
if (!empty($uriSegments[2]) && stristr($uriSegments[2], '?')) {
	$querystr = $uriSegments[2];
} else if (!empty($uriSegments[3]) && stristr($uriSegments[3], '?')) {
	$querystr = $uriSegments[3];
} else if (!empty($uriSegments[4]) && stristr($uriSegments[4], '?')) {
	$querystr = $uriSegments[4];
} else if (!empty($uriSegments[5]) && stristr($uriSegments[5], '?')) {
	$querystr = $uriSegments[5];
}
$parameters = [];
$nparameters = [];
parse_str($querystr, $nparameters);
foreach($nparameters as $k => $v) {
	$k = str_replace('?', '', $k);
	if ($k != 'Child_1_Age' && $k != 'Child_2_Age' && $k != 'Child_3_Age' && $k != 'Child_4_Age' && $k != 'Child_5_Age' && $k != 'Child_6_Age') {
		$nk = preg_replace('/[0-9]+/', '', $k);
	} else {
		$nk = $k;
	}
	$parameters[$nk] = $v;
}
$label = $user_avantioapi_details['label'];
$labelexact = $user_avantioapi_details['labelexact'];
$parameters['page'] = $uriSegments[1];
$parameters['label'] = $label;
$parameters['labelexact'] = $labelexact;
$county = $user_avantioapi_details['county'];
$parameters['city'] = $county;
$province = $user_avantioapi_details['province'];
$parameters['province'] = $province;
$region = $user_avantioapi_details['region'];
$parameters['region'] = $region;
$accommodations = getAccommodations($parameters);
if (!empty($accommodations)) {
?>
<section class="propSea" id="propSea">
	<div class="propTot dflex">
		<span class="housetot"><?php echo count($accommodations) ?> house<?php echo count($accommodations) != 1 ? 's' : ''; ?> found</span>
		<span class="housesort custom-sort-select">
			<select id="OrderSortSearch" name="OrderSortSearch" onchange="loadHomes();" aria-label="Sort Homes By">
				<option value="occupant_asc">Order By: Nº of Occupants</option>
				<option value="price_asc">Order By: Price low to high</option>
				<option value="price_desc">Order By: Price high to low</option>
				<option value="town_asc">Order By: Town</option>
				<option value="propertytype_desc">Order By: Property Type</option>
				<option value="bedrooms_asc">Order By: Rooms</option>
				<option value="review_desc">Order By: Review</option>
			</select>
			<div class="custom-sort-select-icon">
				<i class="fas fa-chevron-down"></i>
			</div>
		</span>
	</div>
	<ul id="prop-view" class="prop-list-view"></ul>
	<div id="loading" class="loadingStatic">
		<div class="fwk-border spinner"></div>
		<div class="fwk-border spinner-active"></div>
	</div>
	<div id="loadMoreButton" class="loadMoreButtonShowHide">
		<button class="loadmore-button-wrapper" onclick="loadHomes();" title="Load More" aria-label="Load More">Load More <span class="svg-readmore"></span></button>
	</div>
</section>
<script>
<?php
$jsparams = json_encode($parameters, true);
echo "var jsparams = ". $jsparams . ";\n";
?>
var searchMainUrl = '<?php echo admin_url('admin-ajax.php'); ?>?action=accommodations';
var searchRecentlyViewedUrl = '<?php echo admin_url('admin-ajax.php'); ?>?action=get_recently_viewed_accommodations';
</script>
<?php
} else {
	$currentParm_url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	$parsed_url = parse_url($currentParm_url);
	$queryURL_params = [];
	if (isset($parsed_url['query'])) {
	    parse_str($parsed_url['query'], $queryURL_params);
	}
	$thhParamsPresent = false;
	foreach ($queryURL_params as $key => $value) {
	    if (strpos($key, 'thh-') === 0) {
	        $thhParamsPresent = true;
	        break;
	    }
	}
	echo '<div class="alert alert-filtering">
        Unfortunately there are no results for your search, please try selecting a different search criteria.
        <span class="buttons-criteria-container">
        <span class="buttons-criteria-title">Get more results</span>
        <span class="buttons-criteria-para">Remove one of the filters below to get more results.</span>';
	if (isset($queryURL_params['destination']) && $queryURL_params['destination'] !== '') {
	    echo '<span class="buttons-criteria-block"><a class="elementor-button elementor-button-clear-red elementor-button-link elementor-size-sm" href="' . rewriteFiltersURL($currentParm_url, 'clearDestination') . '" onclick="clearDestinationViaTab(event)" aria-label="Clear Property or Location Filter">Property or Location <span class="xfilter">x</span></a></span>';
	}
	if ((isset($queryURL_params['daterange']) && $queryURL_params['daterange'] !== '') || 
	    (isset($queryURL_params['dateFrom']) && $queryURL_params['dateFrom'] !== '') || 
	    (isset($queryURL_params['dateTo']) && $queryURL_params['dateTo'] !== '')) {
	    echo '<span class="buttons-criteria-block"><a class="elementor-button elementor-button-clear-red elementor-button-link elementor-size-sm" href="' . rewriteFiltersURL($currentParm_url, 'clearDates') . '" onclick="clearDatesViaTab(event)" aria-label="Clear Dates Filter">Dates <span class="xfilter">x</span></a></span>';
	}
	if (isset($queryURL_params['AdultNum']) && $queryURL_params['AdultNum'] !== '') {
	    echo '<span class="buttons-criteria-block"><a class="elementor-button elementor-button-clear-red elementor-button-link elementor-size-sm" href="' . rewriteFiltersURL($currentParm_url, 'clearPeople') . '" onclick="clearOccupantsViaTab(event)" aria-label="Clear Occupants Filter">Occupants <span class="xfilter">x</span></a></span>';
	}
	if ($thhParamsPresent) {
	    echo '<span class="buttons-criteria-block"><a class="elementor-button elementor-button-clear-red elementor-button-link elementor-size-sm" href="' . rewriteFiltersURL($currentParm_url, 'clearThhParams') . '" onclick="clearFilterViaTab(event)" aria-label="Clear Main Filter">Filter <span class="xfilter">x</span></a></span>';
	}
	echo '</span></div>';
}     
?>