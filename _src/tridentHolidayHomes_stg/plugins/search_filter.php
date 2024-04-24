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
            $destinations[$accommodation->RegionCode . '==' . $accommodation->RegionName][$accommodation->CityCode . '==' . $accommodation->CityName][$accommodation->LocalityCode . '==' . $accommodation->LocalityName][$accommodation->DistrictCode] = $accommodation->DistrictName;
        } else {
            $destinations[$accommodation->RegionCode . '==' . $accommodation->RegionName][$accommodation->CityCode . '==' . $accommodation->CityName][$accommodation->LocalityCode . '==' . $accommodation->LocalityName] = [];
        }
    }
}
asort($accommodations);
?>
<div class="searchfilterbar<?php if ($filter != 'no') { ?> searchfilterbar-withoutfilter<?php } ?>">
    <div class="right-sidebar<?php if ($filter == 'no') { ?> right-sidebar-withoutfilter<?php } ?>">
        <form name="formFilterAccommodation" class="formFilterClass" id="formFilterAccommodation" method="GET" action="<?php echo $currentURL_Form; ?>">
            <div class="elementor-container elementor-column-gap-default">
                <div class="rowOne">
                    <div class="elementor-column elementor-col-26">
						<span class="sidebar-input">
                            <div class="c-triggerMobileSearch js-openMobileFilterDestination"></div>
							<select name="destination" class="destination" aria-label="Destination">
								<option></option>
								<?php if (!empty($destinations)) { ?>
                                    <?php foreach($destinations as $region => $cities) {
                                        list($rcode, $rname) = explode('==', $region);
                                        ?>
                                        <optgroup label="<?php echo $rname; ?>">
                                        <option value="<?php echo $rcode; ?>" <?php if (isset($_REQUEST['destination']) && ($_REQUEST['destination'] == $rcode)) { echo 'selected'; } ?> title="city"><?php echo $rname; ?></option>
									<?php foreach($cities as $city => $localities) {
                                        list($ccode, $cname) = explode('==', $city);
                                        ?>
                                        <option value="<?php echo $ccode; ?>" <?php if (isset($_REQUEST['destination']) && ($_REQUEST['destination'] == $ccode)) { echo 'selected'; } ?> title="city"><?php echo $cname; ?></option>
										<?php foreach($localities as $locality => $districts) {
                                            list($lcode, $lname) = explode('==', $locality);
                                            ?>
                                            <option value="<?php echo $lcode; ?>" <?php if (isset($_REQUEST['destination']) && ($_REQUEST['destination'] == $lcode)) { echo 'selected'; } ?>>&nbsp;&nbsp;<?php echo $lname; ?></option>
										<?php foreach($districts as $dcode => $dname) {
                                                ?>
                                                <option value="<?php echo $dcode; ?>" <?php if (isset($_REQUEST['destination']) && ($_REQUEST['destination'] == $dcode)) { echo 'selected'; } ?>>&nbsp;&nbsp;<?php echo $dname; ?></option>
                                            <?php } ?>
                                        <?php } ?>
                                    <?php } ?>
								</optgroup>
                                    <?php } ?>
                                <?php } ?>
                                <?php if (!empty($accommodations)) { ?>
                                    <optgroup label="Holiday Homes">
									<?php foreach($accommodations as $akey => $accommodation) { ?>
                                        <option value="<?php echo $akey; ?>" <?php if (isset($_REQUEST['destination']) && ($_REQUEST['destination'] == $akey)) { echo 'selected'; } ?>><?php echo $accommodation; ?></option>
                                    <?php } ?>
								</optgroup>
                                <?php } ?>
							</select>
						</span>
                        <?php
                        if (!empty($destinations)) {
                            $k = 0;
                            ?>
                            <div class="desktopDestination filterDisplayNone">
                                <input class="select2-search__field" id="inputSearch" type="search" tabindex="0" role="searchbox" aria-label="Destination Search Box">
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
                                                    <h4 class="maindest"><a href="javascript:void(0)" data-destid="<?php echo $rcode; ?>" aria-label="<?php echo $rname; ?>">All of <?php echo $rname; ?></a></h4>
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
                                                <h4>Holiday Homes</h4>
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
                    <div class="dates elementor-column elementor-col-23">
                        <label for="travel-period" class="label-title">Add Dates</label>
                        <span class="sidebar-input svg-calendar-before">
							<?php
                            if (isset($fromDate_post) && isset($toDate_post) && (date('d/m/Y', strtotime($fromDate_post)) !== '01/01/1970' && date('d/m/Y', strtotime($toDate_post)) !== '01/01/1970')) {
                                $datRange_parm = date('d/m/Y', strtotime($fromDate_post)) . ' - ' . date('d/m/Y', strtotime($toDate_post));
                            } else {
                                $datRange_parm = '';
                            }
                            ?>
							<input type="text" name="daterange" id="travel-period" placeholder="Dates" class="add_dates" value="<?php echo $datRange_parm; ?>" aria-label="Dates" readonly />
							<input type="hidden" name="dateFrom" id="dateFrom" value="<?php if (isset($fromDate_post)) { echo $fromDate_post; } ?>" />
							<input type="hidden" name="dateTo" id="dateTo" value="<?php if (isset($toDate_post)) { echo $toDate_post; } ?>" />
						</span>
                    </div>
                    <div class="occupancy elementor-column elementor-col-23">
                        <label for="occupancy-box" class="label-title">Add Guests</label>
                        <span class="select_online">
							<div class="personas_select">
								<span class="sidebar-input svg-guests-before">
                                    <?php
                                    $AdultNum = isset($_REQUEST['AdultNum']) ? $_REQUEST['AdultNum'] : '0';
                                    $ChildrenNum = isset($_REQUEST['ChildrenNum']) ? $_REQUEST['ChildrenNum'] : '0';
                                    ?>
                                    <input id="occupancy-box" type="text" placeholder="Guests" value="<?php echo isset($_REQUEST['AdultNum']) ? $AdultNum . ' Adult - ' . $ChildrenNum . ' Children' : ''; ?>" aria-label="Guests" readonly />
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
                                        <?php 
											$output_rightsidebar = '';
											for ($i = 1; $i <= 6; $i++) : 
    											$output_rightsidebar .= '<div class="child-age-selects">';
    											$output_rightsidebar .= '<div class="child' . $i . ' people">';
    											$output_rightsidebar .= '<div class="childs-label">';
    											$output_rightsidebar .= '<label for="Child_' . $i . '_Age" class="label-visible">Child ' . $i . ' Age</label>';
    											$output_rightsidebar .= '</div>';
    											$output_rightsidebar .= '<div class="childsage-input">';
    											$output_rightsidebar .= '<select id="Child_' . $i . '_Age" class="select children_age_select" name="Child_' . $i . '_Age" aria-label="Child ' . $i . ' Age" disabled>';
    											$output_rightsidebar .= '<option value="">Select Age</option>';
    											for ($j = 0; $j <= 12; $j++) :
    												$selected_cainput = (isset($_GET['Child_' . $i . '_Age']) && $_GET['Child_' . $i . '_Age'] === (string)$j) ? 'selected="selected"' : '';
    												$output_rightsidebar .= '<option value="' . $j . '" ' . $selected_cainput . '>' . ($j === 0 ? '0 years' : $j . ' year' . ($j != 1 ? 's' : '')) . '</option>';
    											endfor;
    											$output_rightsidebar .= '</select>';
    											$output_rightsidebar .= '</div>';
    											$output_rightsidebar .= '</div>';
    											$output_rightsidebar .= '</div>';
    										endfor;
    										echo $output_rightsidebar;
										?>
                                        <div class="occupancy__confirm js-toggleOccupancyMenu">
                                            <span>Confirm Guests</span>
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
                            <a href="javascript:void(0)" class="tabOne active" aria-label="List View">List View</a>
                            <a href="javascript:void(0)" class="tabTwo" aria-label="Map View">Map View</a>
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
<div class="c-mobileFilterDestination">
    <div class="c-mobileFilterDestination__mask js-closeMobileFilterDestination"></div>
    <div class="c-mobileFilterDestination__hero">
        <div class="c-mobileFilterDestination__hero__close js-closeMobileFilterDestination">
            <svg enable-background="new 0 0 492 492" version="1.1" viewBox="0 0 492 492" xml:space="preserve" width="20px" height="20px" xmlns="http://www.w3.org/2000/svg"><path fill="transparent" d="m300.19 246 183.95-183.96c5.06-5.064 7.852-11.82 7.86-19.024 0-7.208-2.792-13.972-7.86-19.028l-16.12-16.116c-5.068-5.076-11.824-7.856-19.036-7.856-7.2 0-13.956 2.78-19.024 7.856l-183.95 183.95-183.96-183.95c-5.06-5.076-11.82-7.856-19.028-7.856-7.2 0-13.96 2.78-19.02 7.856l-16.128 16.116c-10.496 10.496-10.496 27.568 0 38.052l183.96 183.96-183.96 183.95c-5.064 5.072-7.852 11.828-7.852 19.032s2.788 13.96 7.852 19.028l16.124 16.116c5.06 5.072 11.824 7.856 19.02 7.856 7.208 0 13.968-2.784 19.028-7.856l183.96-183.95 183.95 183.95c5.068 5.072 11.824 7.856 19.024 7.856h8e-3c7.204 0 13.96-2.784 19.028-7.856l16.12-16.116c5.06-5.064 7.852-11.824 7.852-19.028s-2.792-13.96-7.852-19.028l-183.95-183.96z"/></svg>
        </div>
        <div class="c-mobileFilterDestination__hero__content">
            <div class="c-mobileSearchDestination">
                <h3 class="c-mobileSearchDestination__title">Your Search</h3>
                <label class="c-mobileSearchDestination__label" for="mobileFilerDestination">Search Destination</label>
                <input type="text" class="c-mobileSearchDestination__filter" placeholder="Enter destination" id="mobileFilerDestination" name="mobileFilerDestination">
                <span class="c-mobileSearchDestination__inputCaption">and select from the results below…</span>
                <span class="c-mobileSearchDestination__subTitle">Select destination</span>
                <div class="c-mobileSearchDestination__list">
                    <?php if (!empty($destinations)) {
                        foreach ($destinations as $region => $cities) {
                            list($rcode, $rname) = explode('==', $region);
                            ?>
                            <div class="c-mobileSearchDestination__list__item">
                                <div class="c-mobileSearchDestination__list__item__head">
                                    <h4 aria-label="<?php echo $rname; ?>"><?php echo $rname; ?></h4>
                                </div>
                                <div class="c-mobileSearchDestination__list__item__content">
                                    <div class="c-mobileSearchDestination__list__item__content__block">
                                        <h5>
                                            <a href="javascript:void(0)" class="js-closeMobileFilterDestination" data-destid="<?php echo $rcode; ?>" aria-label="<?php echo $rname; ?>">All of <?php echo $rname; ?></a>
                                        </h5>
                                    </div>
                                    <?php foreach ($cities as $city => $localities) {
                                        list($ccode, $cname) = explode('==', $city);
                                        ?>
                                        <div class="c-mobileSearchDestination__list__item__content__block">
                                            <h5>
                                                <a href="javascript:void(0)" class="js-closeMobileFilterDestination" data-destid="<?php echo $ccode; ?>" aria-label="<?php echo $cname; ?>"><?php echo $cname; ?></a>
                                            </h5>
                                            <ul>
                                                <?php foreach ($localities as $locality => $districts) {
                                                    list($lcode, $lname) = explode('==', $locality);
                                                    ?>
                                                    <li>
                                                        <a href="javascript:void(0)" class="js-closeMobileFilterDestination" data-destid="<?php echo $lcode; ?>" aria-label="<?php echo $lname; ?>"><?php echo $lname; ?></a>
                                                    </li>
                                                <?php } ?>
                                            </ul>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>
                        <!-- "Holiday Homes" -->
                        <div class="c-mobileSearchDestination__list__item --holidayHomes">
                            <div class="c-mobileSearchDestination__list__item__head">
                                <h4 aria-label="Holiday Homes">Holiday Homes</h4>
                            </div>
                            <div class="c-mobileSearchDestination__list__item__content">
                                <div class="c-mobileSearchDestination__list__item__content__block">
                                    <ul>
                                        <?php foreach ($accommodations as $akey => $accommodation) { ?>
                                            <li>
                                                <a class="js-closeMobileFilterDestination" href="javascript:void(0)" data-destid="<?php echo $akey; ?>" aria-label="<?php echo $accommodation; ?>"><?php echo $accommodation; ?></a>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>
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
                            <section class="elementor-section elementor-inner-section elementor-element elementor-section-full_width elementor-section-content-top elementor-section-height-default elementor-section-height-default">
                                <div class="elementor-container elementor-column-gap-no">
                                    <div class="elementor-column elementor-col-50 elementor-inner-column elementor-element elementor-element-332cc02 widthmobile">
                                        <div class="elementor-widget-wrap elementor-element-populated">
                                            <div class="elementor-element elementor-element-d3b124c elementor-widget elementor-widget-html">
                                                <div class="elementor-widget-container">
                                                    <a href="javascript:clearfilter();" class="elementor-button elementor-button-clear clearleft elementor-button-link elementor-size-sm" title="Clear Filter" aria-label="Clear Filter">Clear Filters</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                            <div class="elementor-element elementor-element-bb9fab8 elementor-widget-divider--view-line elementor-widget elementor-widget-divider">
                                <div class="elementor-widget-container">
                                    <div class="elementor-divider">
            						  <span class="elementor-divider-separator"></span>
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
                                        <span class="elementor-divider-separator"> </span>
                                    </div>
                                </div>
                            </div>
                            <div class="elementor-element elementor-element-ad0f1f1 elementor-widget elementor-widget-heading">
                                <div class="elementor-widget-container">
                                    <h6 class="elementor-heading-title elementor-size-default">General Conditions</h6>
                                </div>
                            </div>
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
                                        <span class="elementor-divider-separator"></span>
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
                                        <span class="elementor-divider-separator"></span>
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
                                        <span class="elementor-divider-separator"></span>
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
                            <section class="elementor-section elementor-inner-section elementor-element elementor-element-7bc0552 elementor-section-full_width elementor-section-content-middle elementor-section-height-default elementor-section-height-default marginT25nb">
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
                            <section class="elementor-section elementor-inner-section elementor-element elementor-element-d471017 elementor-section-full_width elementor-section-content-middle elementor-section-height-default elementor-section-height-default marginT25nb">
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
                            <section class="elementor-section elementor-inner-section elementor-element elementor-element-5fc5a43 elementor-section-full_width elementor-section-content-middle elementor-section-height-default elementor-section-height-default marginT25nb">
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
                            <section class="elementor-section elementor-inner-section elementor-element elementor-element-2ed499c elementor-section-full_width elementor-section-content-middle elementor-section-height-default elementor-section-height-default marginT25nb">
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
                            <section class="elementor-section elementor-inner-section elementor-element elementor-element-d357c15 elementor-section-full_width elementor-section-content-middle elementor-section-height-default elementor-section-height-default marginT25nb">
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
                            <section class="elementor-section elementor-inner-section elementor-element elementor-element-158490e elementor-section-full_width elementor-section-content-middle elementor-section-height-default elementor-section-height-default marginT25nb">
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
                                        <span class="elementor-divider-separator"></span>
                                    </div>
                                </div>
                            </div>
                            <section class="elementor-section elementor-inner-section elementor-element elementor-element-ea11c01 elementor-section-full_width elementor-section-content-middle hidden elementor-section-height-default elementor-section-height-default marginT25nb">
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
                                        <span class="elementor-divider-separator"></span>
                                    </div>
                                </div>
                            </div>
                            <section class="elementor-section elementor-inner-section elementor-element elementor-element-81ffbab elementor-section-full_width elementor-section-content-top elementor-section-height-default elementor-section-height-default">
                                <div class="elementor-container elementor-column-gap-no">
                                    <div class="elementor-column elementor-col-50 elementor-inner-column elementor-element elementor-element-332cc02 widthmobile">
                                        <div class="elementor-widget-wrap elementor-element-populated">
                                            <div class="elementor-element elementor-element-d3b124c elementor-widget elementor-widget-html">
                                                <div class="elementor-widget-container">
                                                    <a href="javascript:clearfilter();" class="elementor-button elementor-button-clear elementor-button-link elementor-size-sm" title="Clear Filter" aria-label="Clear Filter">Clear Filters</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="elementor-column elementor-col-50 elementor-inner-column elementor-element elementor-element-05c53bc widthmobile">
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
<?php
}
?>