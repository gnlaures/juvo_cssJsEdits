<?php
try {
    // Avantio's Trident Holiday Homes company name - TEST ACCOUNT to match with xml documents in case of a bug
    $company = 'itsalojamientos';
    $language = 'en';

    // Set the parameters
    if (isset($_POST['AdultNum'])) {
        $adultsNumber = $_POST['AdultNum'];
    } else {
        $adultsNumber = 2;
    }
    if (isset($_POST['dateFrom'])) {
        $dateFrom = $_POST['dateFrom'];
    } else {
        $dateFrom = '';
    }
    if (!$dateFrom) {
        $calc_dateFrom = date('Y-m-d');
        $calc_dateFromDay = date('Y-m-d', strtotime($calc_dateFrom . ' +1 day'));
        $dateFrom = $calc_dateFromDay;
    }
    if (isset($_POST['dateTo'])) {
        $dateTo = $_POST['dateTo'];
    } else {
        $dateTo = '';
    }
    if (!$dateTo) {
        $calc_dateTo = date('Y-m-d');
        //$calc_dateToDay = date('Y-m-d', strtotime($calc_dateTo . ' +1 day'));
        //$calc_dateToYear = date('Y-m-d', strtotime($calc_dateToDay . ' +1 year'));
        $calc_dateToYear = date('Y-m-d', strtotime($calc_dateTo . ' +3 day'));
        $dateTo = $calc_dateToYear;
    }
    // Number of properties per page
    $pageSize = 20;
	$uriSegments = explode("/", parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    if (isset($uriSegments[2]) && $uriSegments[2] != '') {
        $currentPage = $uriSegments[2];
		$base_uri = $uriSegments[1];
    } else {
		$currentPage = 1;
        $base_uri = 'rentals-search';
    }

    // Escaping and Sanatising
    function StringIfNotEmpty($name, $value) {
        $strVal = (string)$value;
        if ($strVal !== '') {
            echo $name . ': ' . $strVal . '<br>';
        }
    }
    function IncludedIfNotEmpty($name, $value) {
        $strVal = (string)$value;
        if ($strVal == '1') {
            $str_NewVal = 'Included';
        } else {
            $str_NewVal = 'Not Included';
        }
        echo $name . ': ' . $str_NewVal . '<br>';
    }
    function ImageIfNotEmpty($name, $value, $valueName) {
        $strVal = (string)$value;
        $strValName = (string)$valueName;
        if ($strVal !== '') {
            echo $name . ': <img src="' . $strVal . '" alt="' . $strValName . '" /><br>';
        }
    }
    function CheckInIfNotEmpty($name, $fromValue, $toValue) {
        $fromVal = (string)$fromValue;
        $toVal = (string)$toValue;
        if ($toVal !== '') {
            echo $name . ': ' . $fromVal . ' - ' . $toVal . '<br>';
        }
        if ($fromVal !== '' && $toVal === '') {
            echo $name . ': ' . $fromVal . '<br>';
        }
    }
    function SupplementsIfNotEmpty($name, $currency, $value, $valueName) {
        $strVal = (string)$value;
        $strValName = (string)$valueName;
        $currencyConvert = str_replace('EUR', '&euro;', $currency);
        if ($strVal !== '') {
            if ($strVal <= 1) {
                echo $name . ': ' . $strVal . ' person (' . $currencyConvert . $strValName . ' per person)<br>';
            } else {
                echo $name . ': ' . $strVal . ' people (' . $currencyConvert . $strValName . ' per person)<br>';
            }
        }
    }
    function addSpaces($string) {
        if (preg_match('/[a-z]/', $string)) {
            return preg_replace('/(?<!\ )[A-Z]/', ' $0', $string);
        } else {
            return $string;
        }
    }
    // Total Accommodations
    function TotalPropertiesAccommodationsFeeds($company) {
        $plugin_dir = plugin_dir_path(__FILE__); // Get the full path to the plugin directory
        $accommodationsFile = $plugin_dir.'feeds/Accommodations.xml';
        $totalProperties = 0;
        if (file_exists($accommodationsFile)) {
            // Get the contents of the file
            $accommodationsOutput = file_get_contents($accommodationsFile);
            if ($accommodationsOutput !== false) {
                $accommodationsXml = simplexml_load_string($accommodationsOutput);
                if ($accommodationsXml !== false) {
                    // Iterate over the properties in the XML
                    foreach ($accommodationsXml->Accommodation as $accommodation) {
                        if ($accommodation->Company == $company) {
                            $result_prop = $accommodation->UserId;
                            if ($result_prop) {
                                $totalProperties += count($result_prop);
                            }
                        }
                    }
                }
            }
        }
        return $totalProperties;
    }
    function AccommodationsFeeds($company, $language, $propertiesPerPage, $offset) {
        // Get the full path to the plugin directory
        $plugin_dir = plugin_dir_path(__FILE__);
        $plugin_url = plugins_url('', __FILE__);
        // Get the XML files
        $accommodationsFile = $plugin_dir.'feeds/Accommodations.xml';
        $priceModifiersFile = $plugin_dir.'feeds/PriceModifiers.xml';
        $availabilitiesFile = $plugin_dir.'feeds/Availabilities.xml';
        $descriptionsFile = $plugin_dir.'feeds/Descriptions.xml';
        $ratesFile = $plugin_dir.'feeds/Rates.xml';
        // Next 2 may just be used for the filters search. Need to be sorted once the filter is added
        $kindsFile = $plugin_dir.'feeds/Kinds.xml';
        $geographicAreasFile = $plugin_dir.'feeds/GeographicAreas.xml';
        // Check if the file exists
        if (file_exists($accommodationsFile) && file_exists($priceModifiersFile) && file_exists($availabilitiesFile) && file_exists($descriptionsFile) && file_exists($ratesFile) && file_exists($kindsFile) && file_exists($geographicAreasFile)) {
            // Get the contents of the file
            $accommodationsOutput = file_get_contents($accommodationsFile);
            $priceModifiersOutput = file_get_contents($priceModifiersFile);
            $availabilitiesOutput = file_get_contents($availabilitiesFile);
            $descriptionsOutput = file_get_contents($descriptionsFile);
            $ratesOutput = file_get_contents($ratesFile);
            $kindsOutput = file_get_contents($kindsFile);
            $geographicAreasOutput = file_get_contents($geographicAreasFile);
            // If there's an output
            if ($accommodationsOutput !== false && $priceModifiersOutput !== false && $availabilitiesOutput !== false && $descriptionsOutput !== false && $ratesOutput !== false && $kindsOutput !== false && $geographicAreasOutput !== false) {
                // Try to load the XML from the string
                $accommodationsXml = simplexml_load_string($accommodationsOutput);
                $priceModifiersXml = simplexml_load_string($priceModifiersOutput);
                $availabilitiesXml = simplexml_load_string($availabilitiesOutput);
                $descriptionsXml = simplexml_load_string($descriptionsOutput);
                $ratesXml = simplexml_load_string($ratesOutput);
                $kindsXml = simplexml_load_string($kindsOutput);
                $geographicAreasXml = simplexml_load_string($geographicAreasOutput);
                // If the XML loaded successfully
                if ($accommodationsXml !== false) {
                    echo '<section class="propSea" id="propSea">';
                    echo '<div class="header_order_print">';
                    echo '<ul class="result-selector">';
                    echo '<li id="icon-grid" class="items fas fa-th icon selected"><span>Grid</span></li>';
                    echo '<li id="icon-list" class="items fas fa-th-list icon"><span>List</span></li>';
                    echo '<li id="map-maker" class="fas fa-map-marker-alt"><span>Map</span></li>';
                    echo '</ul>';
                    echo '</div>';
                    echo '<ul id="prop-view">';
                    // Iterate over the properties in the XML
                    $index = 0;
                    foreach ($accommodationsXml->Accommodation as $accommodation) {
                        if ($accommodation->Company == $company) {
                            //echo "Index: $index, Offset: $offset, Properties Per Page: $propertiesPerPage<br>";
                            if ($index >= $offset && $index < ($offset + $propertiesPerPage)) {
                                echo '<li data-propid="'.$accommodation->AccommodationId.'">';
                                echo '<figure class="carousel-container">';
                                echo '<div class="swiper-container">';
                                if ($descriptionsXml !== false) {
                                    $descriptionsPicsFound = false;
                                    $imageList = [];
                                    $pictureLimit = 10;
                                    foreach ($descriptionsXml->Accommodation as $pictures) {
                                        if ((int)$pictures->AccommodationId == (int)$accommodation->AccommodationId) {
                                            foreach ($pictures->Pictures->Picture as $picture) {
                                                if (!empty($picture->AdaptedURI) && count($imageList) < $pictureLimit) {
                                                    $imageList[] = '<li><a href="/rentals/'. (int)$accommodation->AccommodationId .'/'. (int)$accommodation->UserId . '/" title="' . $accommodation->AccommodationName . '"><img src="' . $picture->AdaptedURI . '" alt="' . $picture->Name . '"></a></li>';
                                                }
                                            }
                                            $carouselClass = count($imageList) === 0 || count($imageList) === 1 ? 'carousel-single' : 'carousel';
                                            echo '<ul class="' . $carouselClass . '">';
                                            echo implode('', $imageList);
                                            echo '</ul>';
                                            $descriptionsPicsFound = true;
                                            break;
                                        }
                                    }
                                    if (!$descriptionsPicsFound || count($imageList) === 0) {
                                        echo '<ul class="carousel-single">';
                                        echo '<li><a href="/rentals/'. (int)$accommodation->AccommodationId .'/'. (int)$accommodation->UserId . '/" title="' . $accommodation->AccommodationName . '"><img src="' . $plugin_url . '/images/empty-image.png" alt="Empty Default Image"></a></li>';
                                        echo '</ul>';
                                    }
                                }
                                echo '<div class="carousel-dots"></div>';
                                echo '</div>';
                                if ($priceModifiersXml !== false) {
                                    $priceModifiersFound = false;
                                    if ($priceModifiersXml->PriceModifier) {
                                        foreach ($priceModifiersXml->PriceModifier as $priceModifiers) {
                                            if ((int)$priceModifiers->Id === (int)$accommodation->PriceModifierId) {
                                                $totalOffers = count($priceModifiers->Season);
                                                if ($totalOffers > 0) {
                                                    echo '<figcaption><span>' . $totalOffers . ' Active Offer' . ($totalOffers > 1 ? 's' : '') . '</span></figcaption>';
                                                    echo '<span class="watermark-newretreat-small"></span>';
                                                } else {
                                                    echo '<figcaption><span>0 Active Offers</span></figcaption>';
                                                    echo '<span class="watermark-newretreat-small"></span>';
                                                }
                                                $priceModifiersFound = true;                        
                                            }
                                        }
                                    }
                                }
                                echo '</figure>';
                                echo '<div class="prop-content">';
                                $totalRatings = 0;
                                $totalReviews = 0;
                                if ($accommodation->Reviews && $accommodation->Reviews->Review) {
                                    foreach ($accommodation->Reviews->Review as $guestReviews) {
                                        $rating = (int)$guestReviews->Rating;
                                        $totalRatings += $rating;
                                        $totalReviews++;
                                    }
                                }
                                if ($totalReviews > 0) {
                                    $averageRating = $totalRatings / $totalReviews;
                                    $convertedRatingOutOf5 = number_format(($averageRating / 10) * 5, 1);
                                    $averageRatingRounded = $convertedRatingOutOf5;
                                    if ($averageRatingRounded > 5) {
                                        $averageRatingConverted = '5';
                                    } else {
                                        $integerPart = floor($averageRatingRounded);
                                        $decimalPart = ($averageRatingRounded - $integerPart) * 10;
                                        if ($decimalPart == 5) {
                                            $averageRatingConverted = sprintf("%02d.5", $integerPart);
                                        } else {
                                            $averageRatingConverted = sprintf("%d%d", $integerPart, $decimalPart);
                                        }
                                    }
                                    echo '<div class="reviewsContentRates">';
                                    echo '<div class="star-ratings' . $averageRatingConverted . '" aria-label="Rating of this property out of 5"></div>';
                                    echo '<div class="reviewsAmt">' . $totalReviews . ' review' . ($totalReviews > 1 ? 's' : '') . '</div>';
                                    echo '<div class="favouritesProp"><i class="far fa-heart fa-lg"></i></div>';
                                    echo '</div>';
                                } else {
                                    echo '<div class="reviewsContentRates">';
                                    echo '<div class="star-ratings0" aria-label="Rating of this property out of 5"></div>';
                                    echo '<div class="reviewsAmt">No reviews yet</div>';
                                    echo '<div class="favouritesProp"><i class="far fa-heart fa-lg"></i></div>';
                                    echo '</div>';
                                }
                                echo '<h3><a href="/rentals/'. (int)$accommodation->AccommodationId .'/'. (int)$accommodation->UserId . '/" title="' . $accommodation->AccommodationName . '">' . $accommodation->AccommodationName . '</a></h3>';
                                $namesAddress = array_filter([
                                    $accommodation->LocalizationData->District->Name,
                                    $accommodation->LocalizationData->Locality->Name,
                                    $accommodation->LocalizationData->Province->Name,
                                    $accommodation->LocalizationData->City->Name,
                                    $accommodation->LocalizationData->Resort->Name,
                                    $accommodation->LocalizationData->Country->Name,
                                    $accommodation->LocalizationData->Region->Name
                                ]);
                                $uniqueNamesAddress = array_unique($namesAddress);
                                $filteredNamesAddress = [];
                                foreach ($uniqueNamesAddress as $name) {
                                    $name = trim($name);
                                    if (!empty($name)) {
                                        $filteredNamesAddress[] = $name;
                                    }
                                }
                                $formattedNamesAddress = implode(', ', $filteredNamesAddress);
                                echo '<h4><span class="icon-box"><i class="fas fa-map-marker-alt fa-lg marginR5"></i></span><span class="text-box">' . $formattedNamesAddress . '</span></h4>';
                                echo '<div class="blurred-list-container" id="scrollContainer">';
                                echo '<i class="chevron-left fas fa-chevron-left"></i>';
                                echo '<ul class="prop-bullet-list">';
                                if (isset($accommodation->Features->Distribution->PeopleCapacity) && $accommodation->Features->Distribution->PeopleCapacity !== '') {
                                    $numPeopleCapacity = (int)$accommodation->Features->Distribution->PeopleCapacity;
                                    echo '<li class="no-bullet">Up to ' . $numPeopleCapacity . ' guest' . ($numPeopleCapacity > 1 ? 's' : '') . '</li>';
                                } else if (isset($accommodation->Features->Distribution->AdultsCapacity) && $accommodation->Features->Distribution->AdultsCapacity !== '') {
                                    $numAdultsCapacity = (int)$accommodation->Features->Distribution->AdultsCapacity;
                                    echo '<li class="no-bullet">Up to ' . $numAdultsCapacity . ' guest' . ($numAdultsCapacity > 1 ? 's' : '') . '</li>';
                                } else {
                                    if (isset($accommodation->Features->Distribution->MinimumOccupation) && $accommodation->Features->Distribution->MinimumOccupation !== '') {
                                        $numMinimumOccupation = (int)$accommodation->Features->Distribution->MinimumOccupation;
                                        echo '<li class="no-bullet">Min of ' . $numMinimumOccupation . ' guest' . ($numMinimumOccupation > 1 ? 's' : '') . '</li>';
                                    } else {
                                        echo '<li class="no-bullet">Min of 0 guests</li>';
                                    }
                                }
                                if (isset($accommodation->Features->Distribution->Bedrooms) && $accommodation->Features->Distribution->Bedrooms !== '') {
                                    $numBedrooms = (int)$accommodation->Features->Distribution->Bedrooms;
                                    echo '<li>' . $numBedrooms . ' bedroom' . ($numBedrooms > 1 ? 's' : '') . '</li>';
                                }
                                if (isset($accommodation->Features->Distribution->Toilets) && $accommodation->Features->Distribution->Toilets !== '') {
                                    $numToilets = (int)$accommodation->Features->Distribution->Toilets;
                                    echo '<li>' . $numToilets . ' bathroom' . ($numToilets > 1 ? 's' : '') . '</li>';
                                }
                                if ($accommodation->Features->ExtrasAndServices->SpecialServices->SpecialService) {
                                    foreach ($accommodation->Features->ExtrasAndServices->SpecialServices->SpecialService as $service) {
                                        if ((int)$service->Code === 9) {
                                            if ($service->Allowed == 'si') {
                                                echo '<li>Pets allowed</li>';
                                            }
                                        }
                                    }
                                }
                                if ($accommodation->Features->Distribution->AcceptYoungsters) {
                                    echo '<li>Children allowed</li>';
                                }
                                echo '</ul>';
                                echo '<i class="chevron-right fas fa-chevron-right"></i>';
                                echo '</div>';
                                if ($ratesXml !== false) {
                                    $lowestPrice = null;
                                    foreach ($ratesXml->AccommodationList->Accommodation as $rate) {
                                        if ((int)$rate->AccommodationId == (int)$accommodation->AccommodationId) {
                                            foreach ($rate->Rates->RatePeriod as $ratePeriod) {
                                                foreach ($ratePeriod as $accommPlan) {
                                                    if (isset($accommPlan->Type) && isset($accommPlan->Price)) {
                                                        $priceValue = (float)$accommPlan->Price;
                                                        if ($lowestPrice === null || $priceValue < $lowestPrice) {
                                                            $lowestPrice = $priceValue;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    if ($lowestPrice !== null) {
                                        $priceConvert = str_replace('EUR', '&euro;', $ratePeriod->Currency);
                                        echo '<span class="price"><span class="from">From</span> ' . $priceConvert . $lowestPrice . ' /week</span>';
                                    }
                                }
                                echo '</div>';
                                echo '</li>';
                            }
                            $index++;
                        }
                    }
                    echo '</ul>';
                    echo '</section>';
                } else {
                    //echo "Failed to parse XML.";
                }
            } else {
                //echo "Failed to read XML.";
            }
        } else {
            //echo "XML file does not exist.";
        }
    }
    // Process and display the filtered search information with pagination from the xml file
    if ($company) {
        $propertiesPerPage = 20;
        $offset = ($currentPage - 1) * $propertiesPerPage;
        AccommodationsFeeds($company, $language, $propertiesPerPage, $offset);
        $totalProperties = TotalPropertiesAccommodationsFeeds($company);
        $totalPages = ceil($totalProperties / $propertiesPerPage);
        echo '<div>';
        //echo '<p><br>Total Properties: ' . $totalProperties . '</p>';
        //echo '<p>Page ' . $currentPage . ' of ' . $totalPages . '</p>';
        echo '<div class="pagination-container">';
        echo '<ul class="pagination-box">';
        //$base_url_pagenumb = get_permalink();
        // Previous Button
        if ($currentPage > 1) {
            echo '<li><a href="/'.$base_uri.'/' . ($currentPage - 1) . '/" class="pagination-btn"><span class="fas fa-chevron-left"></span></a></li>';
        }
        // Numbers Button
        for ($i = 1; $i <= $totalPages; $i++) {
            echo '<li><a href="/'.$base_uri.'/' . $i . '/" class="pagination-numbers ' . (($i == $currentPage) ? 'current' : '') . '">' . $i . '</a></li>';
        }
        // Next Button
        if ($currentPage < $totalPages) {
            echo '<li><a href="/'.$base_uri.'/' . ($currentPage + 1) . '/" class="pagination-btn"><span class="fas fa-chevron-right"></span></a></li>';
        }
        echo '</ul>';
        echo '</div>';
        echo '</div>';
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
            echo '<span class="buttons-criteria-block"><a class="elementor-button elementor-button-clear-red elementor-button-link elementor-size-sm" href="' . rewriteFiltersURL($currentParm_url, 'clearDates') . '" onclick="clearDatesViaTab(event)" aria-label="Clear Dates Filter">>Dates <span class="xfilter">x</span></a></span>';
        }
        if (isset($queryURL_params['AdultNum']) && $queryURL_params['AdultNum'] !== '') {
            echo '<span class="buttons-criteria-block"><a class="elementor-button elementor-button-clear-red elementor-button-link elementor-size-sm" href="' . rewriteFiltersURL($currentParm_url, 'clearPeople') . '" onclick="clearOccupantsViaTab(event)" aria-label="Clear Occupants Filter">>Occupants <span class="xfilter">x</span></a></span>';
        }
        if ($thhParamsPresent) {
            echo '<span class="buttons-criteria-block"><a class="elementor-button elementor-button-clear-red elementor-button-link elementor-size-sm" href="' . rewriteFiltersURL($currentParm_url, 'clearThhParams') . '" onclick="clearFilterViaTab(event)" aria-label="Clear Main Filter">>Filter <span class="xfilter">x</span></a></span>';
        }
        echo '</span></div>';
    }
} catch (Exception $e) {
    //echo 'Error: ' . $e->getMessage() . PHP_EOL . 'Error Code: ' . $e->getCode();
    echo '<div class="alert alert-filtering">No properties available.</div>';
}
?>