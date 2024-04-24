<?php
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['destination'])) {
    $destinationId = filter_input(INPUT_GET, 'destination', FILTER_SANITIZE_NUMBER_INT);
    $queryString = http_build_query($_GET);
	$newUrl = "/search-ireland/";
	if (!empty($destinationId)) {
		global $wpdb;
		$accommodations_table = $wpdb->prefix . "accommodations";
		$query = $wpdb->prepare("SELECT RegionCode, RegionName, CityCode, CityName, LocalityCode, LocalityName FROM " . $accommodations_table . " WHERE RegionCode = %d OR CityCode = %d OR LocalityCode = %d LIMIT 1", $destinationId, $destinationId, $destinationId);
		$result = $wpdb->get_row($query);
		if ($result) {
			if ($result->RegionCode == $destinationId) {
				$newUrl = "/search-ireland/" . strtolower($result->RegionName) . "/";
			} elseif ($result->CityCode == $destinationId) {
				$newUrl = "/search-ireland/" . strtolower($result->CityName) . "/";
			} elseif ($result->LocalityCode == $destinationId) {
				$LocalityName_url = strtolower(trim((string)$result->LocalityName));
				$LocalityName_url = str_replace(array(",", "'"), '', $LocalityName_url);
				$LocalityName_url = preg_replace('/[\[\]()]/', '', $LocalityName_url);
				$LocalityName_url = str_replace(array("/", ".", "+", " "), '-', $LocalityName_url);
				$LocalityName_url = preg_replace('/\s+/', '-', $LocalityName_url);
				$LocalityName_url = preg_replace('/-+/', '-', $LocalityName_url);
				$newUrl = "/search-ireland/" . strtolower($result->CityName) . "/" . $LocalityName_url . "/";
			}
		}
	}
    $sanitizedURL = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
    $currentUrl = htmlspecialchars($sanitizedURL, ENT_QUOTES, 'UTF-8');
    if (preg_match("#/search-ireland/|/search-ireland/#", $currentUrl)) {
		$current_url = explode("?", $currentUrl);
        if (!empty($current_url) && ($current_url[0] != $newUrl)) {
            $redirectUrl = $newUrl . ($queryString ? '?' . $queryString : '');
            header("Location: " . $redirectUrl);
            exit;
        }
    }
}
$sanitizedURL = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
$currentURL_Form_fixed = htmlspecialchars($sanitizedURL, ENT_QUOTES, 'UTF-8');
if ($currentURL_Form_fixed) {
	$currentURL_Form = $currentURL_Form_fixed;
}
if (is_front_page()) {
	$currentURL_Form = '/search-ireland/';
} else {
	$currentURL_Form = '';
}
if (isset($_REQUEST['dateFrom'])) {
	$fromDate_post = $_REQUEST['dateFrom'];
} else {
	$fromDate_post = '';
}
if (isset($_REQUEST['dateTo'])) {
	$toDate_post = $_REQUEST['dateTo'];
} else {
	$toDate_post = '';
}
if ($fromDate_post) {
	$getFirstDate = date('d/m/Y', strtotime($fromDate_post));
} else {
	//$getFirstDate = date('d/m/Y', strtotime('+1 day'));
	$getFirstDate = '';
}
if ($toDate_post) {
	$getLastDate = date('d/m/Y', strtotime($toDate_post));
} else {
	//$getLastDate = date('d/m/Y', strtotime('+3 day'));
	$getLastDate = '';
}
$filter = $search_atts['showfilter'];
$arr_accommo = getAllAccommodations();
$accommodations = [];
$destinations = [];
if (!empty($arr_accommo)) {
	foreach($arr_accommo as $accommodation) {
		$AccommodationId = (string)$accommodation->AccommodationId;
		$accommodations[$AccommodationId] = $accommodation->AccommodationName;
		if (!empty($accommodation->DistrictName) && ($accommodation->DistrictName !== "Sin especificar" && $accommodation->DistrictName !== "Not specified")) {
			$destinations[$accommodation->RegionCode.'=='.$accommodation->RegionName][$accommodation->CityCode.'=='.$accommodation->CityName][$accommodation->LocalityCode.'=='.$accommodation->LocalityName][$accommodation->DistrictCode] = $accommodation->DistrictName;
		} else {
			$destinations[$accommodation->RegionCode.'=='.$accommodation->RegionName][$accommodation->CityCode.'=='.$accommodation->CityName][$accommodation->LocalityCode.'=='.$accommodation->LocalityName] = [];
		}
	}
}
asort($accommodations);

?>
<div class="searchfilterbar<?php if($filter != 'no'){ ?> searchfilterbar-withoutfilter<?php } ?>">
	<div class="right-sidebar<?php if($filter == 'no'){ ?> right-sidebar-withoutfilter<?php } ?>">
		<form name="formFilterAccommodation" class="formFilterClass" id="formFilterAccommodation" method="GET" action="<?php echo $currentURL_Form; ?>">
			<div class="elementor-container elementor-column-gap-default">
                <div class="rowOne">
					<div class="elementor-column elementor-col-26">
                        <div class="c-searchDestination">
                            <input type="text" class="c-searchDestination__input" placeholder="Destination" value="" id="#inputSearch">
                            <div class="c-searchDestination__list">
                                <?php
                                if (!empty($destinations)) {
                                    $k = 0;
                                    ?>
                                    <div class="desktopDestination">
                                        <div class="flexCont">
                                            <div class="destinationLefttabs">
                                                <?php foreach($destinations as $region => $cities) {
                                                    list($rcode, $rname) = explode('==', $region);
                                                    ?>
                                                    <a href="javascript:void(0)" class="<?php if (!$k) { ?>active<?php } ?>" data-destid="<?php echo strtolower($rname); ?>Tab" aria-label="<?php echo $rname; ?>"><?php echo $rname; ?></a>
                                                    <?php $k++; } ?>
                                                <a href="javascript:void(0)" data-destid="holidayTab" aria-label="Holiday Homes">Holiday Homes</a>
                                            </div>
                                            <?php $k = 0; foreach($destinations as $region => $cities) {
                                                list($rcode, $rname) = explode('==', $region);
                                                ?>
                                                <div class="rightDiv <?php echo strtolower($rname); ?>Tab<?php if ($k) { ?> filterDisplayNone<?php } else { ?> filterDisplayBlock<?php } ?>">
                                                    <div class="insideRightDiv">
                                                        <div class="countyBlock">
                                                            <h4 class="maindest">All of <?php echo $rname; ?></h4>
                                                        </div>
                                                        <?php foreach($cities as $city => $localities) {
                                                            list($ccode, $cname) = explode('==', $city);
                                                            ?>
                                                            <div class="countyBlock">
                                                                <h4><a href="javascript:void(0)" data-destid="<?php echo $ccode; ?>" aria-label="<?php echo $cname; ?>"><?php echo $cname; ?></a></h4>
                                                                <ul>
                                                                    <?php foreach($localities as $locality => $districts) {
                                                                        list($lcode, $lname) = explode('==', $locality);
                                                                        ?>
                                                                        <li><a href="javascript:void(0)" data-destid="<?php echo $lcode; ?>" aria-label="<?php echo $lname; ?>"><?php echo $lname; ?></a></li>
                                                                    <?php } ?>
                                                                </ul>
                                                            </div>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                                <?php $k++; } ?>
                                            <div class="rightDiv holidayTab filterDisplayNone">
                                                <div class="insideRightDiv">
                                                    <div class="countyBlock">
                                                        <h4><a href="javascript:void(0)" aria-label="Holiday Homes">Holiday Homes</a></h4>
                                                    </div>
                                                    <div class="countyBlock">
                                                        <ul>
                                                            <?php foreach($accommodations as $akey => $accommodation) { ?>
                                                                <li><a href="javascript:void(0)" data-destid="<?php echo $akey; ?>" aria-label="<?php echo $accommodation; ?>"><?php echo $accommodation; ?></a></li>
                                                            <?php } ?>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
					</div>
					<div class="dates elementor-column elementor-col-23">
						<label for="travel-period" class="label-title">Add Dates</label>
						<span class="sidebar-input svg-calendar-before">
							<?php
							if (isset($fromDate_post) && isset($toDate_post)) {
								$datRange_parm = date('d/m/Y', strtotime($fromDate_post)) . ' - ' . date('d/m/Y', strtotime($toDate_post));
							} else {
								$datRange_parm = '';
							}
							?>
							<input type="text" name="daterange" id="travel-period" placeholder="Add Dates" class="add_dates" value="<?php echo $datRange_parm; ?>" aria-label="Add Dates" readonly />
							<input type="hidden" name="dateFrom" id="dateFrom" value="<?php if (isset($fromDate_post)) { echo $fromDate_post; } ?>" />
							<input type="hidden" name="dateTo" id="dateTo" value="<?php if (isset($toDate_post)) { echo $toDate_post; } ?>" />
						</span>
					</div>
					<div class="occupancy elementor-column elementor-col-23">
						<label for="occupancy-box" class="label-title">Add Guests</label>
						<span class="select_online">
							<div class="personas_select">
								<span class="sidebar-input svg-guests-before">
									<input id="occupancy-box" type="text" placeholder="Add Guests" value="1 Adult - 0 Children" aria-label="Add Guests" readonly />
									<div id="occupancy-dropdown" class="occupancy-dropdown occupancy-hidden">
										<div class="adult people adults-container">
											<div class="adults-label">
												<label for="AdultNum" class="label-visible">Adults</label>
												<small>Ages 13 or above</small>
											</div>
											<div class="adults-input">
												<span id="plusminus-a-minus" class="plusminus handleMinus">-</span>
												<input type="number" name="AdultNum" id="AdultNum" class="num" min="1" max="20" step="1" aria-valuemin="1" aria-valuemax="20" aria-valuenow="1" value="<?php echo isset($_REQUEST['AdultNum']) ? $_REQUEST['AdultNum'] : 1; ?>" aria-label="Number of Adults" readonly />
												<span id="plusminus-a-plus" class="plusminus handlePlus">+</span>
											</div>
										</div>
										<div class="childs people childs-container">
											<div class="childs-label">
												<label for="ChildrenNum" class="label-visible">Children</label>
												<small>Ages 0-12</small>
											</div>
											<div class="childs-input">
												<span id="plusminus-c-minus" class="plusminus handleMinus">-</span>
												<input type="number" name="ChildrenNum" id="ChildrenNum" class="num" min="0" max="6" step="1" aria-valuemin="0" aria-valuemax="6" aria-valuenow="0" value="<?php echo isset($_REQUEST['ChildrenNum']) ? $_REQUEST['ChildrenNum'] : 0; ?>" aria-label="Number of Children" readonly />
												<span id="plusminus-c-plus" class="plusminus handlePlus">+</span>
											</div>
										</div>
									</div>
								</span>
							</div>
						</span>
					</div>
				</div>
				<div class="botonR_fondo elementor-column elementor-col-28">
					<?php if ($filter != 'no') { ?>
					<div class="elementor-element filter_icon elementor-list-item-link-inline elementor-widget__width-auto elementor-icon-list--layout-traditional elementor-widget elementor-widget-icon-list">
						<div class="elementor-widget-container elementor-icon-list-item">
							<a href="javascript:void(0);" class="show-filter-popup" aria-label="Add Filter">
								<span class="elementor-icon-list-icon"><i aria-hidden="true" class="fas fa-sliders-h"></i></span>
								<span class="elementor-icon-list-text">Add <br>Filter</span>
							</a>
						</div>
					</div>
					<div class="list_mobileFilter">
						<a href="javascript:void(0)" class="tabOne" aria-label="List View">List View</a>
						<a href="javascript:void(0)" class="tabTwo active" aria-label="Map View">Map View</a>
					</div>
					<?php } ?>
					<div class="elementor-element elementor-widget__width-auto btnBox">
						<button class="button-book-search" type="submit" name="filter-submit-button" id="filter-submit-button" aria-label="Search Properties"><i aria-hidden="true" class="fas fa-search"></i> <span>Search</span></button>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>

<!-- c-searchDestination -->
<style>
    .c-searchDestination {
        display: block;
        width: 100%;
        position: relative;
    }
    .c-searchDestination__input {
        height: 46px;
        line-height: 46px;
        background: #f9f9f9;
        border-radius: 30px;
    }
    .c-searchDestination__list {
        display: none;
    }
    .c-searchDestination__list.is-active {
        display: block;
    }
</style>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const cSearchDestination__input = document.querySelector('.c-searchDestination__input');
        const cSearchDestination__list = document.querySelector('.c-searchDestination__list');

        // show/hide suggestions
        cSearchDestination__input.addEventListener('focus', function () {cSearchDestination__list.classList.add('is-active');});
        //cSearchDestination__input.addEventListener('focusout', function () {cSearchDestination__list.classList.remove('is-active');});

        // suggestions tabs
        const cSearchDestination__tabs = document.querySelectorAll('.c-searchDestination__list .destinationLefttabs a');
        const cSearchDestination__rightDivs = document.querySelectorAll('.c-searchDestination__list .rightDiv');
        cSearchDestination__tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                // remove/add active class
                cSearchDestination__tabs.forEach(function (t) {t.classList.remove('active');});
                tab.classList.add('active');
                // hide/show the content
                cSearchDestination__rightDivs.forEach(function (div) {
                    div.classList.remove('filterDisplayBlock');
                    div.classList.add('filterDisplayNone');
                });
                const cSearchDestination__destId = tab.getAttribute('data-destid');
                const cSearchDestination__correspondingDiv = document.querySelector(`.${cSearchDestination__destId}`);
                cSearchDestination__correspondingDiv.classList.remove('filterDisplayNone');
                cSearchDestination__correspondingDiv.classList.add('filterDisplayBlock');
            });
        });
    });

    document.addEventListener("DOMContentLoaded", function () {
        // set the value to the input
        const cSearchDestination__links = document.querySelectorAll('.c-searchDestination__list .countyBlock a');
        console.log(cSearchDestination__links)
        cSearchDestination__links.forEach(function (link) {
            link.addEventListener('mousedown', function (e) {
                e.preventDefault();
                console.log('click');
                var link_destId = link.getAttribute('data-destid');
                var link_content = link.getAttribute('aria-label');
                console.log(link_destId + ': ' + link_content)
                document.querySelector('.c-searchDestination__input').value = link_destId + ': ' + link_content;
                document.querySelector('.c-searchDestination__list').classList.remove('is-active');
                document.querySelector('.c-searchDestination__input').blur();
            });
        });

        // close suggestions
        document.addEventListener('mousedown', function (event) {
            if (
                !event.target.closest('.c-searchDestination__input') &&
                !event.target.closest('.destinationLefttabs a')
            ) {
                document.querySelector('.c-searchDestination__list').classList.remove('is-active');
            }
        });


    });
</script>





<?php if ($filter != 'no') { ?>
<div class="searchpopform filterDisplayNone">
	<div class="elementor-location-popup">
		<section class="elementor-section elementor-top-section elementor-element elementor-element-5650f37d elementor-section-full_width elementor-section-height-min-height elementor-section-items-top elementor-section-height-default">
		   <div class="elementor-container elementor-column-gap-wide">
		   <div class="elementor-column elementor-col-100 elementor-top-column elementor-element elementor-element-46a20825 search_filter">
			  <div class="elementor-widget-wrap elementor-element-populated scrollMobile">
				 <div class="elementor-element elementor-element-f0dca3c elementor-widget elementor-widget-heading">
					<div class="elementor-widget-container">
					   <h4 class="elementor-heading-title elementor-size-default">Filters</h4>
					   <a role="button" tabindex="0" href="javascript:void(0)" class="dialog-close-button dialog-lightbox-close-button" aria-label="Close Button"><i class="eicon-close"></i></a>
					</div>
				 </div>
				 <div class="elementor-element elementor-element-bb9fab8 elementor-widget-divider--view-line elementor-widget elementor-widget-divider">
					<div class="elementor-widget-container">
					   <div class="elementor-divider">
						  <span class="elementor-divider-separator">
						  </span>
					   </div>
					</div>
				 </div>
				 <section class="elementor-section elementor-inner-section elementor-element elementor-element-abe0dbd elementor-section-full_width elementor-section-content-middle rangePrice elementor-section-height-default elementor-section-height-default">
					<div class="elementor-container elementor-column-gap-no">
					   <div class="elementor-column elementor-col-50 elementor-inner-column elementor-element elementor-element-c6f4cef">
						  <div class="elementor-widget-wrap elementor-element-populated">
							 <div class="elementor-element elementor-element-dfd231d elementor-widget elementor-widget-heading">
								<div class="elementor-widget-container">
								   <h6 class="elementor-heading-title elementor-size-default">Price Range</h6>
								</div>
							 </div>
						  </div>
					   </div>
					   <div class="elementor-column elementor-col-50 elementor-inner-column elementor-element elementor-element-e402ae6">
						  <div class="elementor-widget-wrap elementor-element-populated">
							 <div class="elementor-element elementor-element-978f79c elementor-widget elementor-widget-html">
								<div class="elementor-widget-container">
								   <div class="price-input">
									  <output id="result-min" class="input-min">&euro;0</output>
									  <output id="result-max" class="input-max">&euro;5000</output>
								   </div>
								   <div class="range-slider">
									  <div class="progress"></div>
								   </div>
								   <div class="range-input">
									  <input type="range" name="thh-min-price" class="min-price" id="min-price" min="0" max="5000" value="0" step="50" aria-label="Minimum Price">
									  <input type="range" name="thh-max-price" class="max-price" id="max-price" min="0" max="5000" value="5000" step="50" aria-label="Maximum Price">
								   </div>
								</div>
							 </div>
						  </div>
					   </div>
					</div>
				 </section>
				 <div class="elementor-element elementor-element-1ccf197 elementor-widget-divider--view-line elementor-widget elementor-widget-divider">
					<div class="elementor-widget-container">
					   <div class="elementor-divider">
						  <span class="elementor-divider-separator">
						  </span>
					   </div>
					</div>
				 </div>
				 <div class="elementor-element elementor-element-ad0f1f1 elementor-widget elementor-widget-heading">
					<div class="elementor-widget-container">
					   <h6 class="elementor-heading-title elementor-size-default">General Conditions</h6>
					</div>
				 </div>
				 <?php
				/*<input name="thh-instant-booking" type="hidden" value="Instant Booking">
				<input name="thh-groups-allowed" type="hidden" value="Young groups allowed">*/
				 /*<section class="elementor-section elementor-inner-section elementor-element elementor-element-6ea398e elementor-section-full_width elementor-section-content-middle hidden elementor-section-height-default elementor-section-height-default">
				 	<fieldset>
				 		<legend class="visually-hidden">Instant Booking</legend>
						<div class="elementor-container elementor-column-gap-no">
						   <div class="elementor-column elementor-col-50 elementor-inner-column elementor-element elementor-element-f910f54">
							  <div class="elementor-widget-wrap elementor-element-populated">
								 <div class="elementor-element elementor-element-41ad31f elementor-widget elementor-widget-heading">
									<div class="elementor-widget-container">
									   <p class="elementor-heading-title elementor-size-default">Instant Booking</p>
									</div>
								 </div>
							  </div>
						   </div>
						   <div class="elementor-column elementor-col-50 elementor-inner-column elementor-element elementor-element-f54b29b">
							  <div class="elementor-widget-wrap elementor-element-populated">
								 <div class="elementor-element elementor-element-75de48a elementor-widget elementor-widget-html">
									<div class="elementor-widget-container">
									   <label class="switch">
									   <input name="thh-instant-booking" type="checkbox" value="Instant Booking">
									   <span class="slider round"></span>
									   </label>
									</div>
								 </div>
							  </div>
						   </div>
						</div>
					</section>
				</fieldset>
				 <section class="elementor-section elementor-inner-section elementor-element elementor-element-2bad72a elementor-section-full_width elementor-section-content-middle hidden elementor-section-height-default elementor-section-height-default">
				 	<fieldset>
				 		<legend class="visually-hidden">Young groups allowed</legend>
						<div class="elementor-container elementor-column-gap-no">
						   <div class="elementor-column elementor-col-50 elementor-inner-column elementor-element elementor-element-d255025">
							  <div class="elementor-widget-wrap elementor-element-populated">
								 <div class="elementor-element elementor-element-ebbdfc2 elementor-widget elementor-widget-heading">
									<div class="elementor-widget-container">
									   <p class="elementor-heading-title elementor-size-default">Young groups allowed</p>
									</div>
								 </div>
							  </div>
						   </div>
						   <div class="elementor-column elementor-col-50 elementor-inner-column elementor-element elementor-element-1335765">
							  <div class="elementor-widget-wrap elementor-element-populated">
								 <div class="elementor-element elementor-element-ce12b06 elementor-widget elementor-widget-html">
									<div class="elementor-widget-container">
									   <label class="switch">
									   <input name="thh-groups-allowed" type="checkbox" value="Young groups allowed">
									   <span class="slider round"></span>
									   </label>
									</div>
								 </div>
							  </div>
						   </div>
						</div>
					</fieldset>
				 </section>*/
				 ?>
				 <section class="elementor-section elementor-inner-section elementor-element elementor-element-2133e87 elementor-section-full_width elementor-section-content-middle elementor-section-height-default elementor-section-height-default">
				 	<fieldset>
				 		<legend class="visually-hidden sr-only">With active offers</legend>
						<div class="elementor-container elementor-column-gap-no">
						   <div class="elementor-column elementor-col-50 elementor-inner-column elementor-element elementor-element-52c7a74">
							  <div class="elementor-widget-wrap elementor-element-populated">
								 <div class="elementor-element elementor-element-cf02177 elementor-widget elementor-widget-heading">
									<div class="elementor-widget-container">
									   <p class="elementor-heading-title elementor-size-default" aria-hidden="true">With active offers</p>
									</div>
								 </div>
							  </div>
						   </div>
						   <div class="elementor-column elementor-col-50 elementor-inner-column elementor-element elementor-element-74fb1bd">
							  <div class="elementor-widget-wrap elementor-element-populated">
								 <div class="elementor-element elementor-element-aee6242 elementor-widget elementor-widget-html">
									<div class="elementor-widget-container">
									   <label for="thh-active-offers" class="switch">
									   <input id="thh-active-offers" name="thh-active-offers" type="checkbox" value="With active offers">
									   <span class="slider round"></span>
									   <span class="sr-only">switch</span>
									   </label>
									</div>
								 </div>
							  </div>
						   </div>
						</div>
					</fieldset>
				 </section>
				 <div class="elementor-element elementor-element-1282924 elementor-widget-divider--view-line elementor-widget elementor-widget-divider">
					<div class="elementor-widget-container">
					   <div class="elementor-divider">
						  <span class="elementor-divider-separator">
						  </span>
					   </div>
					</div>
				 </div>
				 <div class="elementor-element elementor-element-2c57df0 elementor-widget elementor-widget-heading">
					<div class="elementor-widget-container">
					   <h6 class="elementor-heading-title elementor-size-default">Layout</h6>
					</div>
				 </div>
				 <section class="elementor-section elementor-inner-section elementor-element elementor-element-3745eb5 elementor-section-full_width elementor-section-content-middle elementor-section-height-default elementor-section-height-default">
					<div class="elementor-container elementor-column-gap-no">
					   <div class="elementor-column elementor-col-50 elementor-inner-column elementor-element elementor-element-8763927">
						  <div class="elementor-widget-wrap elementor-element-populated">
							 <div class="elementor-element elementor-element-0cc9fb7 elementor-widget elementor-widget-heading">
								<div class="elementor-widget-container">
								   <p class="elementor-heading-title elementor-size-default">Bedrooms</p>
								</div>
							 </div>
						  </div>
					   </div>
					   <div class="elementor-column elementor-col-50 elementor-inner-column elementor-element elementor-element-6449edd">
						  <div class="elementor-widget-wrap elementor-element-populated">
							 <div class="elementor-element elementor-element-9d323be mright elementor-widget elementor-widget-html">
								<div class="elementor-widget-container">
								   <label class="radiobox">
								   <input name="thh-bedrooms" type="radio" value="1" aria-label="1 Bedroom">
								   <span class="rbox round">01</span>
								   </label>
								   <label class="radiobox">
								   <input name="thh-bedrooms" type="radio" value="2" aria-label="2 Bedrooms">
								   <span class="rbox round">02</span>
								   </label>
								   <label class="radiobox">
								   <input name="thh-bedrooms" type="radio" value="3" aria-label="3 Bedrooms">
								   <span class="rbox round">03</span>
								   </label>
								   <label class="radiobox">
								   <input name="thh-bedrooms" type="radio" value="4" aria-label="4 Bedrooms">
								   <span class="rbox round">04</span>
								   </label>
								   <label class="radiobox">
								   <input name="thh-bedrooms" type="radio" value="5" aria-label="5 Bedrooms">
								   <span class="rbox round">05</span>
								   </label>
								   <label class="radiobox">
								   <input name="thh-bedrooms" type="radio" value="more" aria-label="6+ Bedrooms">
								   <span class="rbox round">More</span>
								   </label>
								</div>
							 </div>
						  </div>
					   </div>
					</div>
				 </section>
				 <section class="elementor-section elementor-inner-section elementor-element elementor-element-1cb57c7 elementor-section-full_width elementor-section-content-middle elementor-section-height-default elementor-section-height-default">
					<div class="elementor-container elementor-column-gap-no">
					   <div class="elementor-column elementor-col-50 elementor-inner-column elementor-element elementor-element-7198606">
						  <div class="elementor-widget-wrap elementor-element-populated">
							 <div class="elementor-element elementor-element-929e57f elementor-widget elementor-widget-heading">
								<div class="elementor-widget-container">
								   <p class="elementor-heading-title elementor-size-default">Beds</p>
								</div>
							 </div>
						  </div>
					   </div>
					   <div class="elementor-column elementor-col-50 elementor-inner-column elementor-element elementor-element-2442898">
						  <div class="elementor-widget-wrap elementor-element-populated">
							 <div class="elementor-element elementor-element-196d235 mright elementor-widget elementor-widget-html">
								<div class="elementor-widget-container">
								   <label class="radiobox">
								   <input name="thh-beds" type="radio" value="1" aria-label="1 Bed">
								   <span class="rbox round">01</span>
								   </label>
								   <label class="radiobox">
								   <input name="thh-beds" type="radio" value="2" aria-label="2 Beds">
								   <span class="rbox round">02</span>
								   </label>
								   <label class="radiobox">
								   <input name="thh-beds" type="radio" value="3" aria-label="3 Beds">
								   <span class="rbox round">03</span>
								   </label>
								   <label class="radiobox">
								   <input name="thh-beds" type="radio" value="4" aria-label="4 Beds">
								   <span class="rbox round">04</span>
								   </label>
								   <label class="radiobox">
								   <input name="thh-beds" type="radio" value="5" aria-label="5 Beds">
								   <span class="rbox round">05</span>
								   </label>
								   <label class="radiobox">
								   <input name="thh-beds" type="radio" value="more" aria-label="6+ Beds">
								   <span class="rbox round">More</span>
								   </label>
								</div>
							 </div>
						  </div>
					   </div>
					</div>
				 </section>
				 <section class="elementor-section elementor-inner-section elementor-element elementor-element-833feb8 elementor-section-full_width elementor-section-content-middle elementor-section-height-default elementor-section-height-default">
					<div class="elementor-container elementor-column-gap-no">
					   <div class="elementor-column elementor-col-50 elementor-inner-column elementor-element elementor-element-36d3de0">
						  <div class="elementor-widget-wrap elementor-element-populated">
							 <div class="elementor-element elementor-element-6dd05db elementor-widget elementor-widget-heading">
								<div class="elementor-widget-container">
								   <p class="elementor-heading-title elementor-size-default">Bathrooms</p>
								</div>
							 </div>
						  </div>
					   </div>
					   <div class="elementor-column elementor-col-50 elementor-inner-column elementor-element elementor-element-3c0d56c">
						  <div class="elementor-widget-wrap elementor-element-populated">
							 <div class="elementor-element elementor-element-1e44249 mright elementor-widget elementor-widget-html">
								<div class="elementor-widget-container">
								   <label class="radiobox">
								   <input name="thh-bathrooms" type="radio" value="1" aria-label="1 Bathroom">
								   <span class="rbox round">01</span>
								   </label>
								   <label class="radiobox">
								   <input name="thh-bathrooms" type="radio" value="2" aria-label="2 Bathrooms">
								   <span class="rbox round">02</span>
								   </label>
								   <label class="radiobox">
								   <input name="thh-bathrooms" type="radio" value="3" aria-label="3 Bathrooms">
								   <span class="rbox round">03</span>
								   </label>
								   <label class="radiobox">
								   <input name="thh-bathrooms" type="radio" value="4" aria-label="4 Bathrooms">
								   <span class="rbox round">04</span>
								   </label>
								   <label class="radiobox">
								   <input name="thh-bathrooms" type="radio" value="5" aria-label="5 Bathrooms">
								   <span class="rbox round">05</span>
								   </label>
								   <label class="radiobox">
								   <input name="thh-bathrooms" type="radio" value="more" aria-label="6+ Bathrooms">
								   <span class="rbox round">More</span>
								   </label>
								</div>
							 </div>
						  </div>
					   </div>
					</div>
				 </section>
				 <div class="elementor-element elementor-element-fa9f2c0 elementor-widget-divider--view-line elementor-widget elementor-widget-divider">
					<div class="elementor-widget-container">
					   <div class="elementor-divider">
						  <span class="elementor-divider-separator">
						  </span>
					   </div>
					</div>
				 </div>
				 <div class="elementor-element elementor-element-04408d2 elementor-widget elementor-widget-heading">
					<div class="elementor-widget-container">
					   <h6 class="elementor-heading-title elementor-size-default">Main Features</h6>
					</div>
				 </div>
				 <div class="elementor-element elementor-element-cb5a465 mright rounded elementor-widget elementor-widget-html">
					<div class="elementor-widget-container">
					   <label class="radiobox">
					   <input name="thh-features[]" type="checkbox" value="Dog friendly" aria-label="Dog Friendly">
					   <span class="rbox round">Dog Friendly</span>
					   </label>
					   <label class="radiobox">
					   <input name="thh-features[]" type="checkbox" value="Internet" aria-label="Internet">
					   <span class="rbox round">Internet</span>
					   </label>
					   <label class="radiobox">
					   <input name="thh-features[]" type="checkbox" value="Fireplace" aria-label="Fireplace">
					   <span class="rbox round">Fireplace</span>
					   </label>
					   <label class="radiobox">
					   <input name="thh-features[]" type="checkbox" value="Swimming pool" aria-label="Swimming Pool">
					   <span class="rbox round">Swimming Pool</span>
					   </label>
					   <label class="radiobox">
					   <input name="thh-features[]" type="checkbox" value="Electric car charger" aria-label="Electric Car Charger">
					   <span class="rbox round">Electric Car Charger</span>
					   </label>
					   <label class="radiobox">
					   <input name="thh-features[]" type="checkbox" value="Disabled Friendly" aria-label="Disabled Friendly">
					   <span class="rbox round">Disabled Friendly</span>
					   </label>
					</div>
				 </div>
				 <div class="elementor-element elementor-element-215978c elementor-widget-divider--view-line elementor-widget elementor-widget-divider">
					<div class="elementor-widget-container">
					   <div class="elementor-divider">
						  <span class="elementor-divider-separator">
						  </span>
					   </div>
					</div>
				 </div>
				 <div class="elementor-element elementor-element-610865d elementor-widget elementor-widget-heading">
					<div class="elementor-widget-container">
					   <h6 class="elementor-heading-title elementor-size-default">Distance</h6>
					</div>
				 </div>
				 <section class="elementor-section elementor-inner-section elementor-element elementor-element-f67b277 elementor-section-full_width elementor-section-content-middle elementor-section-height-default elementor-section-height-default" data-id="f67b277" data-element_type="section">
					<div class="elementor-container elementor-column-gap-no">
					   <div class="elementor-column elementor-col-50 elementor-inner-column elementor-element elementor-element-d71f6f6">
						  <div class="elementor-widget-wrap elementor-element-populated">
							 <div class="elementor-element elementor-element-a9983ca elementor-widget elementor-widget-heading">
								<div class="elementor-widget-container">
								   <p class="elementor-heading-title elementor-size-default">Airport</p>
								</div>
							 </div>
						  </div>
					   </div>
					   <div class="elementor-column elementor-col-50 elementor-inner-column elementor-element elementor-element-87c19de">
						  <div class="elementor-widget-wrap elementor-element-populated">
							 <div class="elementor-element elementor-element-4b21f6d elementor-widget elementor-widget-html">
								<div class="elementor-widget-container">
								   <div class="range-slider distns">
									  <input type="range" class="single" name="thh-distance-airport" id="airport" value="0" min="0" max="100" step="0.5" aria-label="Distance to Airport">
									  <output class="right" id="result-airport"></output>
								   </div>
								</div>
							 </div>
						  </div>
					   </div>
					</div>
				 </section>
				 <section class="elementor-section elementor-inner-section elementor-element elementor-element-7bc0552 elementor-section-full_width elementor-section-content-middle elementor-section-height-default elementor-section-height-default">
					<div class="elementor-container elementor-column-gap-no">
					   <div class="elementor-column elementor-col-50 elementor-inner-column elementor-element elementor-element-d10f5bd">
						  <div class="elementor-widget-wrap elementor-element-populated">
							 <div class="elementor-element elementor-element-1ab6536 elementor-widget elementor-widget-heading">
								<div class="elementor-widget-container">
								   <p class="elementor-heading-title elementor-size-default">Beach/Seaside</p>
								</div>
							 </div>
						  </div>
					   </div>
					   <div class="elementor-column elementor-col-50 elementor-inner-column elementor-element elementor-element-fa1b706">
						  <div class="elementor-widget-wrap elementor-element-populated">
							 <div class="elementor-element elementor-element-c4dbf24 elementor-widget elementor-widget-html">
								<div class="elementor-widget-container">
								   <div class="range-slider distns">
									  <input type="range" class="single" name="thh-distance-seaside" id="seaside" value="0" min="0" max="100" step="0.5" aria-label="Distance to Beach or Seaside">
									  <output class="right" id="result-seaside"></output>
								   </div>
								</div>
							 </div>
						  </div>
					   </div>
					</div>
				 </section>
				 <section class="elementor-section elementor-inner-section elementor-element elementor-element-d471017 elementor-section-full_width elementor-section-content-middle elementor-section-height-default elementor-section-height-default">
					<div class="elementor-container elementor-column-gap-no">
					   <div class="elementor-column elementor-col-50 elementor-inner-column elementor-element elementor-element-44c705f">
						  <div class="elementor-widget-wrap elementor-element-populated">
							 <div class="elementor-element elementor-element-37d3734 elementor-widget elementor-widget-heading">
								<div class="elementor-widget-container">
								   <p class="elementor-heading-title elementor-size-default">Bus Station</p>
								</div>
							 </div>
						  </div>
					   </div>
					   <div class="elementor-column elementor-col-50 elementor-inner-column elementor-element elementor-element-d8ffcf5">
						  <div class="elementor-widget-wrap elementor-element-populated">
							 <div class="elementor-element elementor-element-1fd123e elementor-widget elementor-widget-html">
								<div class="elementor-widget-container">
								   <div class="range-slider distns">
									  <input type="range" class="single" name="thh-distance-busstation" id="busstation" value="0" min="0" max="100" step="0.5" aria-label="Distance to Bus Station">
									  <output class="right" id="result-busstation"></output>
								   </div>
								</div>
							 </div>
						  </div>
					   </div>
					</div>
				 </section>
				 <section class="elementor-section elementor-inner-section elementor-element elementor-element-5fc5a43 elementor-section-full_width elementor-section-content-middle elementor-section-height-default elementor-section-height-default">
					<div class="elementor-container elementor-column-gap-no">
					   <div class="elementor-column elementor-col-50 elementor-inner-column elementor-element elementor-element-3dfc8d4">
						  <div class="elementor-widget-wrap elementor-element-populated">
							 <div class="elementor-element elementor-element-6816da1 elementor-widget elementor-widget-heading">
								<div class="elementor-widget-container">
								   <p class="elementor-heading-title elementor-size-default">City/Town</p>
								</div>
							 </div>
						  </div>
					   </div>
					   <div class="elementor-column elementor-col-50 elementor-inner-column elementor-element elementor-element-1fd9549">
						  <div class="elementor-widget-wrap elementor-element-populated">
							 <div class="elementor-element elementor-element-27856ca elementor-widget elementor-widget-html">
								<div class="elementor-widget-container">
								   <div class="range-slider distns">
									  <input type="range" class="single" name="thh-distance-town" id="town" value="0" min="0" max="100" step="0.5" aria-label="Distance to City or Town">
									  <output class="right" id="result-town"></output>
								   </div>
								</div>
							 </div>
						  </div>
					   </div>
					</div>
				 </section>
				 <section class="elementor-section elementor-inner-section elementor-element elementor-element-2ed499c elementor-section-full_width elementor-section-content-middle elementor-section-height-default elementor-section-height-default">
					<div class="elementor-container elementor-column-gap-no">
					   <div class="elementor-column elementor-col-50 elementor-inner-column elementor-element elementor-element-87c1582">
						  <div class="elementor-widget-wrap elementor-element-populated">
							 <div class="elementor-element elementor-element-527157f elementor-widget elementor-widget-heading">
								<div class="elementor-widget-container">
								   <p class="elementor-heading-title elementor-size-default">Golf</p>
								</div>
							 </div>
						  </div>
					   </div>
					   <div class="elementor-column elementor-col-50 elementor-inner-column elementor-element elementor-element-7f61515">
						  <div class="elementor-widget-wrap elementor-element-populated">
							 <div class="elementor-element elementor-element-ce1601c elementor-widget elementor-widget-html">
								<div class="elementor-widget-container">
								   <div class="range-slider distns">
									  <input type="range" class="single" name="thh-distance-golf" id="golf" value="0" min="0" max="100" step="0.5" aria-label="Distance to Golf Course">
									  <output class="right" id="result-golf"></output>
								   </div>
								</div>
							 </div>
						  </div>
					   </div>
					</div>
				 </section>
				 <section class="elementor-section elementor-inner-section elementor-element elementor-element-d357c15 elementor-section-full_width elementor-section-content-middle elementor-section-height-default elementor-section-height-default">
					<div class="elementor-container elementor-column-gap-no">
					   <div class="elementor-column elementor-col-50 elementor-inner-column elementor-element elementor-element-812cfa8">
						  <div class="elementor-widget-wrap elementor-element-populated">
							 <div class="elementor-element elementor-element-499ba03 elementor-widget elementor-widget-heading">
								<div class="elementor-widget-container">
								   <p class="elementor-heading-title elementor-size-default">Supermarket</p>
								</div>
							 </div>
						  </div>
					   </div>
					   <div class="elementor-column elementor-col-50 elementor-inner-column elementor-element elementor-element-c158c61">
						  <div class="elementor-widget-wrap elementor-element-populated">
							 <div class="elementor-element elementor-element-63cb2dd elementor-widget elementor-widget-html">
								<div class="elementor-widget-container">
								   <div class="range-slider distns">
									  <input type="range" class="single" name="thh-distance-supermarket" id="supermarket" value="0" min="0" max="100" step="0.5" aria-label="Distance to Supermarket">
									  <output class="right" id="result-market"></output>
								   </div>
								</div>
							 </div>
						  </div>
					   </div>
					</div>
				 </section>
				 <section class="elementor-section elementor-inner-section elementor-element elementor-element-158490e elementor-section-full_width elementor-section-content-middle elementor-section-height-default elementor-section-height-default">
					<div class="elementor-container elementor-column-gap-no">
					   <div class="elementor-column elementor-col-50 elementor-inner-column elementor-element elementor-element-b42fb01">
						  <div class="elementor-widget-wrap elementor-element-populated">
							 <div class="elementor-element elementor-element-81b7ee4 elementor-widget elementor-widget-heading">
								<div class="elementor-widget-container">
								   <p class="elementor-heading-title elementor-size-default">Train Station</p>
								</div>
							 </div>
						  </div>
					   </div>
					   <div class="elementor-column elementor-col-50 elementor-inner-column elementor-element elementor-element-48230f9">
						  <div class="elementor-widget-wrap elementor-element-populated">
							 <div class="elementor-element elementor-element-f66d307 elementor-widget elementor-widget-html">
								<div class="elementor-widget-container">
								   <div class="range-slider distns">
									  <input type="range" class="single" name="thh-distance-trainstation" id="trainstation" value="0" min="0" max="100" step="0.5" aria-label="Distance to Train Station">
									  <output class="right" id="result-trainstation"></output>
								   </div>
								</div>
							 </div>
						  </div>
					   </div>
					</div>
				 </section>
				 <div class="elementor-element elementor-element-f418c54 elementor-widget-divider--view-line elementor-widget elementor-widget-divider">
					<div class="elementor-widget-container">
					   <div class="elementor-divider">
						  <span class="elementor-divider-separator">
						  </span>
					   </div>
					</div>
				 </div>
				 <section class="elementor-section elementor-inner-section elementor-element elementor-element-ea11c01 elementor-section-full_width elementor-section-content-middle hidden elementor-section-height-default elementor-section-height-default">
					<div class="elementor-container elementor-column-gap-no">
					   <div class="elementor-column elementor-col-50 elementor-inner-column elementor-element elementor-element-6db1f50">
						  <div class="elementor-widget-wrap elementor-element-populated">
							 <div class="elementor-element elementor-element-354f88e elementor-widget elementor-widget-heading">
								<div class="elementor-widget-container">
								   <h6 class="elementor-heading-title elementor-size-default">Reviews</h6>
								</div>
							 </div>
						  </div>
					   </div>
					   <div class="elementor-column elementor-col-50 elementor-inner-column elementor-element elementor-element-877fd22">
						  <div class="elementor-widget-wrap elementor-element-populated">
							 <div class="elementor-element elementor-element-ac85cda select-review elementor-widget elementor-widget-html">
								<div class="elementor-widget-container">
								   <div class="rate">
									  <input type="radio" id="star5" name="thh-rating" value="5" aria-label="5 Star Rating" />
									  <label for="star5" title="text">5 stars</label>
									  <input type="radio" id="star4" name="thh-rating" value="4" aria-label="4 Star Rating" />
									  <label for="star4" title="text">4 stars</label>
									  <input type="radio" id="star3" name="thh-rating" value="3" aria-label="3 Star Rating" />
									  <label for="star3" title="text">3 stars</label>
									  <input type="radio" id="star2" name="thh-rating" value="2" aria-label="2 Star Rating" />
									  <label for="star2" title="text">2 stars</label>
									  <input type="radio" id="star1" name="thh-rating" value="1" aria-label="1 Star Rating" />
									  <label for="star1" title="text">1 star</label>
								   </div>
								</div>
							 </div>
						  </div>
					   </div>
					</div>
				 </section>
				 <div class="elementor-element elementor-element-c0269bf hidden elementor-widget-divider--view-line elementor-widget elementor-widget-divider">
					<div class="elementor-widget-container">
					   <div class="elementor-divider">
						  <span class="elementor-divider-separator">
						  </span>
					   </div>
					</div>
				 </div>
				 <section class="elementor-section elementor-inner-section elementor-element elementor-element-81ffbab elementor-section-full_width elementor-section-content-top elementor-section-height-default elementor-section-height-default">
					<div class="elementor-container elementor-column-gap-no">
					   <div class="elementor-column elementor-col-50 elementor-inner-column elementor-element elementor-element-332cc02">
						  <div class="elementor-widget-wrap elementor-element-populated">
							 <div class="elementor-element elementor-element-d3b124c elementor-widget elementor-widget-html">
								<div class="elementor-widget-container">
								   <a href="javascript:clearfilter();" class="elementor-button elementor-button-clear elementor-button-link elementor-size-sm" title="Clear Filter" aria-label="Clear Filter">Clear Search</a>
								</div>
							 </div>
						  </div>
					   </div>
					   <div class="elementor-column elementor-col-50 elementor-inner-column elementor-element elementor-element-05c53bc">
						  <div class="elementor-widget-wrap elementor-element-populated">
							 <div class="elementor-element elementor-element-e76242e elementor-align-right elementor-widget elementor-widget-button">
								<div class="elementor-widget-container">
								   <div class="elementor-button-wrapper">
									  <a class="elementor-button elementor-button-link elementor-size-sm" href="#" id="selectfilters" title="Search Properties" aria-label="Search Properties">
									  <span class="elementor-button-content-wrapper">
									  <span class="elementor-button-text">Search</span>
									  </span>
									  </a>
								   </div>
								</div>
							 </div>
						  </div>
					   </div>
					</div>
				 </section>
			  </div>
		   </div>
		 </div>
		</section>
	</div>
</div>
<script>
jQuery(document).ready(function($) {
	$('.show-filter-popup').click(function() {
		$('.searchpopform').removeClass('filterDisplayNone');
	});
	$('.searchpopform .dialog-close-button').click(function() {
		$('.searchpopform').addClass('filterDisplayNone');
	});
    $(document).on('click', function(e) {
        if (!$('.searchpopform').is(e.target) && $('.searchpopform').has(e.target).length === 0) {
            $('.searchpopform').addClass('filterDisplayNone');
        }
    });
	$('body').append($('.searchpopform'));
    var rangev = document.querySelector(".range-slider .progress");
	$('.searchpopform input').each(function(index,data) {
	    if ($(this).is(':radio') || $(this).is(':checkbox')) {
	        if (localStorage.getItem($(this).attr('name')+index) == $(this).val()) {
                $(this).prop("checked", true);
	        }
        } else {
            if (localStorage.getItem($(this).attr('name')+index) != null) {
                $(this).val(localStorage.getItem($(this).attr('name')+index));
                if ($(this).attr('type') == 'range') {
                    if ($(this).attr('name') == 'thh-max-price') {
                        $('.input-max').html('&euro;'+$(this).val());
                        rangev.style.right = 100 - ($(this).val() / $(this).attr('max')) * 100 + "%";
                    } else if ($(this).attr('name') == 'thh-min-price') {
                        $('.input-min').html('&euro;'+$(this).val());
                        rangev.style.left = $(this).val() / $(this).attr('max') * 100 + "%";
                    } else {
                        $(this).next('output').html('max. '+$(this).val()+' km');
                    }
                }
            }
	    }
	});
	document.getElementById("result-airport").innerHTML = 'max. '+document.getElementById("airport").value+' km';
	document.getElementById("airport").oninput = function() {
	  	document.getElementById("result-airport").innerHTML = 'max. '+this.value+' km';
	}
	document.getElementById("result-seaside").innerHTML = 'max. '+document.getElementById("seaside").value+' km';
	document.getElementById("seaside").oninput = function() {
	  	document.getElementById("result-seaside").innerHTML = 'max. '+this.value+' km';
	}
	document.getElementById("result-busstation").innerHTML = 'max. '+document.getElementById("busstation").value+' km';
	document.getElementById("busstation").oninput = function() {
	  	document.getElementById("result-busstation").innerHTML = 'max. '+this.value+' km';
	}
	document.getElementById("result-town").innerHTML = 'max. '+document.getElementById("town").value+' km';
	document.getElementById("town").oninput = function() {
	  	document.getElementById("result-town").innerHTML = 'max. '+this.value+' km';
	}
	document.getElementById("result-golf").innerHTML = 'max. '+document.getElementById("golf").value+' km';
	document.getElementById("golf").oninput = function() {
	  	document.getElementById("result-golf").innerHTML = 'max. '+this.value+' km';
	}
	document.getElementById("result-market").innerHTML = 'max. '+document.getElementById("supermarket").value+' km';
	document.getElementById("supermarket").oninput = function() {
	  	document.getElementById("result-market").innerHTML = 'max. '+this.value+' km';
	}
	document.getElementById("result-trainstation").innerHTML = 'max. '+document.getElementById("trainstation").value+' km';
	document.getElementById("trainstation").oninput = function() {
	  	document.getElementById("result-trainstation").innerHTML = 'max. '+this.value+' km';
	}
	const rangeInput = document.querySelectorAll(".range-input input"),
        priceInput = document.querySelectorAll(".price-input output"),
        range = document.querySelector(".range-slider .progress");
    const priceGap = 50;
    rangeInput.forEach((input) => {
        input.addEventListener("input", (e) => {
            let minVal = parseInt(rangeInput[0].value),
                maxVal = parseInt(rangeInput[1].value);
            if (maxVal - minVal < priceGap) {
                if (e.target.className === "min-price") {
                    rangeInput[0].value = maxVal - priceGap;
                } else {
                    rangeInput[1].value = minVal + priceGap;
                }
            } else {
                priceInput[0].innerHTML = '&euro;'+minVal;
                priceInput[1].innerHTML = '&euro;'+maxVal;
                range.style.left = (minVal / rangeInput[0].max) * 100 + "%";
                range.style.right = 100 - (maxVal / rangeInput[1].max) * 100 + "%";
            }
        });
    });
	document.getElementById("selectfilters").onclick = function(e) {
		e.preventDefault();
		$('.searchpopform input').each(function(index,data) {
			if ($(this).is(':radio') || $(this).is(':checkbox')) {
				if ($(this).is(':checked')) {
					localStorage.setItem($(this).attr('name')+index, $(this).val());
				} else {
					localStorage.setItem($(this).attr('name')+index, '');
				}
			} else {
				localStorage.setItem($(this).attr('name')+index, $(this).val());
			}
		});
		$('.dialog-close-button').trigger('click');
		$('#filter-submit-button').trigger('click');
	}
});
function clearfilter() {
    localStorage.clear();
    var formDiv = document.querySelector('.search_filter');
    var progressElements = document.querySelectorAll('.progress');
    var outputMin = document.querySelector('#result-min');
    var outputMax = document.querySelector('#result-max');
    var outputAirport = document.querySelector('#result-airport');
    var outputSea = document.querySelector('#result-seaside');
    var outputBus = document.querySelector('#result-busstation');
    var outputTown = document.querySelector('#result-town');
    var outputGolf = document.querySelector('#result-golf');
    var outputMarket = document.querySelector('#result-market');
    var outputTrain = document.querySelector('#result-trainstation');
    var inputs = formDiv.querySelectorAll('input:not(#max-price)');
    var inputsMinMax = formDiv.querySelector('input#max-price');
    var textareas = formDiv.querySelectorAll('textarea');
    var selects = formDiv.querySelectorAll('select');
    inputs.forEach(function(input) {
        if (input.type === 'text' || input.type === 'number' || input.type === 'email') {
            input.value = '';
        } else if (input.type === 'checkbox' || input.type === 'radio') {
            input.checked = false;
        } else if (input.type === 'range') {
            input.value = '0';
            if (outputMin) {
                outputMin.textContent = '€0';
            }
            if (outputAirport) {
                outputAirport.textContent = 'max. 0 km';
            }
            if (outputSea) {
                outputSea.textContent = 'max. 0 km';
            }
            if (outputBus) {
                outputBus.textContent = 'max. 0 km';
            }
            if (outputTown) {
                outputTown.textContent = 'max. 0 km';
            }
            if (outputGolf) {
                outputGolf.textContent = 'max. 0 km';
            }
            if (outputMarket) {
                outputMarket.textContent = 'max. 0 km';
            }
            if (outputTrain) {
                outputTrain.textContent = 'max. 0 km';
            }
        }
    });
    inputsMinMax.forEach(function(input) {
        if (input.type === 'range') {
            input.value = '5000';
            progressElements.forEach(function(elem) {
                elem.style.left = '0%';
                elem.style.right = '0%';
            });
            if (outputMax) {
                outputMax.textContent = '€5000';
            }
        } else if (input.type === 'range') {
            input.value = '0';
            if (outputMin) {
                outputMin.textContent = '€0';
            }
        }
    });
    textareas.forEach(function(textarea) {
        textarea.value = '';
    });
    selects.forEach(function(select) {
        select.selectedIndex = 0;
    });
}
</script>
<?php } ?>

<script>
function clearSearchFilter(){
	Object.keys(localStorage).forEach((key)=>{
		if(key.startsWith('thh-')){
			localStorage.removeItem(key)
		}
	})

}
function clearDestinationViaTab(event) {
    event.preventDefault();
    var formDiv = document.querySelector('#formFilterAccommodation');
    var destinationInput = formDiv.querySelector('input.destination');
    if (destinationInput) {
        $(destinationInput).val('').trigger('change');
    }
    window.location.href = event.currentTarget.href;
}
function clearDatesViaTab(event) {
    event.preventDefault();
    var formDiv = document.querySelector('#formFilterAccommodation');
    var dateRangeInput = formDiv.querySelector('input#travel-period');
    var dateFromInput = formDiv.querySelector('input#dateFrom');
    var dateToInput = formDiv.querySelector('input#dateTo');
    if (dateRangeInput) {
        $(dateRangeInput).val('').trigger('change');
    }
    if (dateFromInput) {
        $(dateFromInput).val('').trigger('change');
    }
    if (dateToInput) {
        $(dateToInput).val('').trigger('change');
    }
    window.location.href = event.currentTarget.href;
}
function clearOccupantsViaTab(event) {
    event.preventDefault();
    var formDiv = document.querySelector('#formFilterAccommodation');
    var occupancyInput = formDiv.querySelector('input#occupancy-box');
    var adultNumInput = formDiv.querySelector('input#AdultNum');
    var childrenNumInput = formDiv.querySelector('input#ChildrenNum');
    if (occupancyInput) {
        $(occupancyInput).val('').trigger('change');
    }
    if (adultNumInput) {
        $(adultNumInput).val('').trigger('change');
    }
    if (childrenNumInput) {
        $(childrenNumInput).val('').trigger('change');
    }
    window.location.href = event.currentTarget.href;
}
function clearFilterViaTab(event) {
    event.preventDefault();
    localStorage.clear();
    window.location.href = event.currentTarget.href;
}
function formatOutput(optionElement) {
  	if (optionElement.title == 'city') {
		var $state = jQuery('<span class="filterWeight600">' + optionElement.text + '</span>');
		return $state;
  	} else {
		return optionElement.text;
  	}
}
function addLeadingZero() {
	var dayNumbers = $('.calendar-table td');
	dayNumbers.each(function() {
		var dayNumber = $(this).text().trim();
		if (dayNumber.length === 1) {
			$(this).text('0' + dayNumber);
		}
	});
}
function hidePrevButtonIfCurrentMonth() {
	var currentMonthYear = moment().format('MMMM YYYY');
	var displayedMonthYear = $('.daterangepicker .left .calendar-table th.month').text().trim();
	if (currentMonthYear === displayedMonthYear) {
		$('.prev').addClass('unclickable');
		$('.prev span').addClass('hidden-calendar');
	} else {
		$('.prev').removeClass('unclickable');
		$('.prev span').removeClass('hidden-calendar');
	}
}
jQuery(document).ready(function($) {
	var screenwidth = $("body").width();
	<?php if (!is_front_page()) { ?>
	clearSearchFilter();
	$('#map_canvas').addClass('mapHomePage');
	$('#map_canvas').removeClass('mapOtherPage');
	<?php } ?>
	$('form#formFilterAccommodation').submit(function(e) {
        for (var a in localStorage) {
            if (a.indexOf('thh-') != -1 && localStorage[a]) {
                $(this).append('<input type="hidden" name="'+a+'" value="'+localStorage[a]+'" />');
            }
        }
        $(this).find('button[name="filter-submit-button"]').prop('disabled', true);
        var dateFrom = $('#dateFrom').val();
        var dateTo = $('#dateTo').val();
        var startDate = moment(dateFrom, 'YYYY-MM-DD');
        var endDate = moment(dateTo, 'YYYY-MM-DD');
        if (startDate.isValid() && endDate.isValid()) {
            $('input[name="daterange"]').val(startDate.format('DD/MM/YYYY') + ' - ' + endDate.format('DD/MM/YYYY'));
        }
        return true;
    });
	// $('.destination').select2({
	// 	placeholder: "Property or Location",
	// 	templateResult: formatOutput,
	// 	allowClear: true
	// });
	if (screenwidth <= 767) {
		// $('.desktopDestination').remove();
		// $('.select2-container, .selection, .select2-selection.select2-selection--single, .select2-selection__rendered, .select2-selection__placeholder, .destination, .select2-search, .select2-search__field').on('click', function (e) {
	    //     $(".select2-container--open").find(".select2-search__field").focus();
	    // });
	    /*$('.destination').on('select2:open', function () {
	        setTimeout(function() {
	            $(".select2-container--open").find(".select2-search__field").focus();
	        }, 50);
	    });*/
	}
	if (screenwidth > 767) {
		// $('.select2-container').on('click', function (e) {
		// 	$('.desktopDestination').toggle();
		// 	$("#inputSearch").focus();
		// 	if ($('.desktopDestination').is(':visible')) {
		// 		$("#inputSearch").val('');
		// 		$("#inputSearch").trigger('input');
		// 	}
		// });
		// $('.destinationLefttabs > a').click(function() {
		// 	$('.destinationLefttabs > a').removeClass('active');
		// 	$(this).addClass('active');
		// 	$('.desktopDestination .rightDiv').hide();
		// 	$('.desktopDestination .'+$(this).data('destid')).show();
		// });
		// $('.countyBlock a').click(function(){
		// 	$('.destination').val($(this).data('destid')).trigger('change');
		// 	$('.desktopDestination').hide();
		// 	$("#inputSearch").val('');
		// });
		// $("#inputSearch").on('input', function () {
		// 	var filter = $(this).val();
		// 	if (filter.length > 0) {
		// 		$('.destinationLefttabs').hide();
		// 		$('.desktopDestination .rightDiv').show();
		// 		$('.desktopDestination').addClass('searchview');
		// 	} else {
		// 		$('.destinationLefttabs').show();
		// 		$('.desktopDestination').removeClass('searchview');
		// 		$('.destinationLefttabs a.active').trigger('click');
		// 	}
		// 	$(".countyBlock ul li, .countyBlock h4:not('.maindest')").each(function () {
		// 		if ($(this).text().search(new RegExp(filter, "i")) < 0) {
		// 			$(this).hide();
		// 		} else {
		// 			$(this).show()
		// 		}
		// 	});
		// });
		// $(document).on('click', function (e) {
		// 	if ($(e.target).closest(".desktopDestination").length === 0 && $(e.target).closest(".select2-container").length === 0) {
		// 		if ($('.desktopDestination').is(':visible')) {
		// 			$(".desktopDestination").hide();
		// 			$("#inputSearch").val('');
		// 		}
		// 	}
		// });
	}
	moment.updateLocale('en', {
      week: { dow: 1 } // Monday is the first day of the week
    });
    var startDate = $('#dateFrom').val();
    var endDate = $('#dateTo').val();
    var isValidStartDate = moment(startDate, 'YYYY-MM-DD', true).isValid();
    var isValidEndDate = moment(endDate, 'YYYY-MM-DD', true).isValid();
    var dateRangePickerOptions = {
        "autoApply": true,
        "locale": {
			"format": "DD/MM/YYYY",
			"separator": " - ",
			"applyLabel": "Apply",
			"cancelLabel": "Cancel",
			"fromLabel": "From",
			"toLabel": "To",
			"customRangeLabel": "Custom",
			"weekLabel": "W",
			"daysOfWeek": [
				"SUN",
                "MON",
                "TUE",
                "WED",
                "THU",
                "FRI",
                "SAT"
			],
			"monthNames": [
				"January",
				"February",
				"March",
				"April",
				"May",
				"June",
				"July",
				"August",
				"September",
				"October",
				"November",
				"December"
			],
		},
		"linkedCalendars": false,
		"showCustomRangeLabel": false,
		opens: 'left',
        isOutsideRange: function(date) {
            return date < moment() || date > moment().add(24, 'months');
        },
        "autoUpdateInput": false,
    };
    if (isValidStartDate && isValidEndDate) {
	    dateRangePickerOptions.startDate = moment(startDate).format('DD/MM/YYYY');
	    dateRangePickerOptions.endDate = moment(endDate).format('DD/MM/YYYY');
	}
	$('input[name="daterange"]').daterangepicker(dateRangePickerOptions, function(start, end, label) {
		if (start.isValid() && end.isValid()) {
			$('#dateFrom').val(start.format('YYYY-MM-DD'));
			$('#dateTo').val(end.format('YYYY-MM-DD'));
			$('input[name="daterange"]').val(start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'));
		} else {
			$('#dateFrom').val('');
			$('#dateTo').val('');
			$('input[name="daterange"]').val('');
		}
		addLeadingZero();
        hidePrevButtonIfCurrentMonth();
		checkRowsAndHide();
	});
	$('input[name="daterange"]').on('apply.daterangepicker', function(ev, picker) {
        $('#dateFrom').val(picker.startDate.format('YYYY-MM-DD'));
        $('#dateTo').val(picker.endDate.format('YYYY-MM-DD'));
    });
	function addLeadingZero() {
        var dayNumbers = $('.calendar-table td');
        dayNumbers.each(function() {
            var dayNumber = $(this).text().trim();
            if (dayNumber.length === 1) {
                $(this).text('0' + dayNumber);
            }
        });
    }
    function hidePrevButtonIfCurrentMonth() {
        var currentMonthYear = moment().format('MMMM YYYY');
        var displayedMonthYear = $('.daterangepicker .left .calendar-table th.month').text().trim();
        if (currentMonthYear === displayedMonthYear) {
            $('.prev').addClass('unclickable');
            $('.prev span').addClass('hidden-calendar');
        } else {
            $('.prev').removeClass('unclickable');
            $('.prev span').removeClass('hidden-calendar');
        }
    }
	function setupMutationObserver() {
        var targetNode = $('.daterangepicker')[0];
        var config = { attributes: false, childList: true, subtree: true };
        var callback = function(mutationsList, observer) {
            for (var mutation of mutationsList) {
                if (mutation.type === 'childList') {
                	addLeadingZero();
                	hidePrevButtonIfCurrentMonth();
                    checkRowsAndHide();
                }
            }
        };
        var observer = new MutationObserver(callback);
        if (targetNode) {
            observer.observe(targetNode, config);
        }
    }
    setupMutationObserver();
    function checkRowsAndHide() {
        $('.calendar-table tbody tr').each(function() {
            var allOff = true;
            $(this).find('td').each(function() {
                if (!$(this).hasClass('off') || !$(this).hasClass('ends')) {
                    allOff = false;
                    return false;
                }
            });
            if (allOff) {
                $(this).addClass('displaynone');
            }
        });
    }
    $('input[name="daterange"]').on('show.daterangepicker', function() {
        addLeadingZero();
        hidePrevButtonIfCurrentMonth();
        checkRowsAndHide();
    });
	$('input.deletable').wrap('<span class="deleteicon"></span>').after($('<span>x</span>').click(function() {
		$(this).prev('input').val('').trigger('change');
	}));
	$('input.add_dates').wrap('<span class="deleteicon"></span>').after($('<span>x</span>').click(function() {
		$(this).prev('input').val('').trigger('change');
		$('#dateFrom').val('');
		$('#dateTo').val('');
	}));
	$('input#occupancy-box').wrap('<span class="deleteicon"></span>').after($('<span>x</span>').click(function() {
		$(this).prev('input').val('').trigger('change');
		$('#AdultNum').val('1');
		$('#ChildrenNum').val('0');
	}));
    function updateElementsSTabs() {
        let elementsUpdated = false;
        if ($('#rentalContent').length) {
            $('#rentalContent').addClass("rentalContentFixed");
            elementsUpdated = true;
        }
        if ($('#mapSection').length) {
            $('#mapSection').addClass("mapHideClass");
            elementsUpdated = true;
        }
        if (elementsUpdated) {
            observerSTabs.disconnect();
        }
    }
    var observerSTabs = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length) {
                updateElementsSTabs();
            }
        });
    });
    observerSTabs.observe(document.body, { childList: true, subtree: true });
    function tabOneAction() {
        $('.tabOne').addClass("active");
        $('.tabTwo').removeClass("active");
        $('#rentalContent').addClass("rentalContentFixed");
        $('#mapSection').removeClass("mapSectionFixed").addClass("mapHideClass");
    }
    function tabTwoAction() {
        $('.tabTwo').addClass("active");
        $('.tabOne').removeClass("active");
        $('#rentalContent').removeClass("rentalContentFixed");
        $('#mapSection').addClass("mapSectionFixed").removeClass("mapHideClass");
    }
    $(".tabOne").on("click touchstart", tabOneAction);
    $(".tabTwo").on("click touchstart", tabTwoAction);
    $('.tabOne').addClass("active");
    $('.tabTwo').removeClass("active");
});
document.addEventListener("DOMContentLoaded", function() {
    const inputContainingDateFrom = document.querySelector("#dateFrom");
    const inputContainingDateTo = document.querySelector("#dateTo");
    if (inputContainingDateFrom) {
        const urlParams = new URLSearchParams(window.location.search);
        const dateRangeParams = urlParams.get('daterange');
        if (dateRangeParams === null || dateRangeParams.trim() === '') {
        	const dateRangeValue = document.querySelector("#travel-period");
            if (dateRangeValue) {
                dateRangeValue.value = '';
            }
        }
    }
});
</script>
