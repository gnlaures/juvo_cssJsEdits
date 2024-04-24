<?php
try {
    // Avantio API credentials and other parameters
    $username = 'itsatentoapi_test';
    $password = 'testapixml';
    $apiKey = 'itsalojamientos';
    $secretKey = '';

    if (null !== get_query_var('prop_id') && get_query_var('prop_id') != '') {
        $accommodationId = get_query_var('prop_id', '');
    } else {
        $accommodationId = '';
    }
    if (null !== get_query_var('acc_id') && get_query_var('acc_id') != '') {
        $accID = get_query_var('acc_id', '');
    } else {
        $accID = '';
    }
    $company = 'itsalojamientos';
    $partnerCode = '836efa4efbe7fa63f2ebbae30d7b965f';
    $language = 'en';
    $languageUpper = 'EN';
    // Set the parameters
    if (isset($_POST['AdultNum'])) {
        $adultsNumber = $_POST['AdultNum'];
        $getBookingPrice_info = 'yes';
        error_log('Received AJAX request');
        error_log('AdultNum: ' . $_POST['AdultNum']);

    } else {
        $adultsNumber = 1;
        $getBookingPrice_info = 'no';
    }
    if (isset($_POST['ChildrenNum'])) {
        error_log('ChildrenNum: ' . $_POST['ChildrenNum']);
    }
    $childrenNumber = isset($_POST['ChildrenNum']) ? $_POST['ChildrenNum'] : '';
    $childAges = array();
    for ($i = 1; $i <= 6; $i++) {
        $key = 'Child_' . $i . '_Age';
        $childAge = isset($_POST[$key]) ? $_POST[$key] : '';
        $childAges[$key] = $childAge;
    }
    $child1Age = $childAges['Child_1_Age'];
    $child2Age = $childAges['Child_2_Age'];
    $child3Age = $childAges['Child_3_Age'];
    $child4Age = $childAges['Child_4_Age'];
    $child5Age = $childAges['Child_5_Age'];
    $child6Age = $childAges['Child_6_Age'];
    if ($childrenNumber < 1) {
        $child1Age = '';
    }
    if ($childrenNumber < 2) {
        $child2Age = '';
    }
    if ($childrenNumber < 3) {
        $child3Age = '';
    }
    if ($childrenNumber < 4) {
        $child4Age = '';
    }
    if ($childrenNumber < 5) {
        $child5Age = '';
    }
    if ($childrenNumber < 6) {
        $child6Age = '';
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
        //$dateToAPI = $dateTo;
        // Due to dateTo not being the checkout date, we need to minus the checkout date by one to give the number of nights stayed
        $dateToAPI = date('Y-m-d', strtotime($dateTo . ' -1 day'));
    } else {
        $dateTo = '';
        $dateToAPI = '';
    }
    if (!$dateTo) {
        $calc_dateTo = date('Y-m-d');
        $calc_dateToYear = date('Y-m-d', strtotime($calc_dateTo . ' +2 day'));
        $dateTo = $calc_dateToYear;
        //$dateToAPI = $dateTo;
        // Due to dateTo not being the checkout date, we need to minus the checkout date by one to give the number of nights
        $dateToAPI = date('Y-m-d', strtotime($dateTo . ' -1 day'));
    }
    // Number of properties per page
    $pageSize = 21;
    // Current page number
    $currentPage = 1;

    // Create the SOAP client and set the SOAP headers for authentication
    $client = new SoapClient('http://ws.avantio.com/soap/vrmsConnectionServices.php?wsdl');
    $timestamp = time();
    $signature = md5($apiKey . $secretKey . $timestamp);
    $header = new SoapHeader(
        'http://www.avantio.com/soap/wsse.php',
        'Header',
        [
            'X-Avantiobyte-Api-Key' => $apiKey,
            'X-Avantiobyte-Signature' => $signature,
            'X-Avantiobyte-Timestamp' => $timestamp,
        ]
    );
    $client->__setSoapHeaders($header);

    // IsAvailable
    // The Operation IsAvailable informs whether or not an accommodation is available for certain dates and number of people. There is a an issue with the Nights ONREQUEST parameter within the API's internal CMS which doesn't match up with the minimum stay and is causing an issue with this
    $request_IsAvailable = [
        'Credentials' => [
            'Language' => 'EN',
            'UserName' => $username,
            'Password' => $password
        ],
        'Criteria' => [
            'Accommodation' => [
                'AccommodationCode' => $accommodationId,
                'UserCode' => $accID,
                'LoginGA' => $company
            ],
            'Occupants' => array(
                'AdultsNumber' => $adultsNumber
            ),
            'DateFrom' => $dateFrom,
            'DateTo' => $dateToAPI
        ]
    ];
    // Add child ages if they have values
    $childAges_IsAvailable = [
        'Child1_Age' => $child1Age,
        'Child2_Age' => $child2Age,
        'Child3_Age' => $child3Age,
        'Child4_Age' => $child4Age,
        'Child5_Age' => $child5Age,
        'Child6_Age' => $child6Age
    ];
    foreach ($childAges_IsAvailable as $childKey_IsAvailable => $childAge_IsAvailable) {
        if (!empty($childAge_IsAvailable)) {
            $request_IsAvailable['Criteria']['Occupants'][$childKey_IsAvailable] = $childAge_IsAvailable;
        }
    }
    foreach ($childAges_IsAvailable as $childKey_IsAvailable => $childAge_IsAvailable) {
        if ($childAge_IsAvailable !== null && $childAge_IsAvailable != '') {
            $request_IsAvailable['Criteria']['Occupants'][$childKey_IsAvailable] = $childAge_IsAvailable;
        }
    }
    $result_IsAvailable = $client->IsAvailable($request_IsAvailable);
    //var_dump($result_IsAvailable);

    // GetBookingPrice - request data with filters
    // The operation GetBookingPrice returns the different prices that would cost a booking for the selected dates and number of people. This operation considers the discounts and supplements applied to the accommodation.
    // CAUTION: the parameters “WithoutOffer” do not consider discounts and supplements.
    $request_GetBookingPrice = [
        'Credentials' => [
            'Language' => 'EN',
            'UserName' => $username,
            'Password' => $password
        ],
        'Criteria' => [
            'Accommodation' => [
                'AccommodationCode' => $accommodationId,
                'UserCode' => $accID,
                'LoginGA' => $company
            ],
            'Occupants' => array(
                'AdultsNumber' => $adultsNumber
            ),
            'ArrivalDate' => $dateFrom,
            'DepartureDate' => $dateTo
        ]
    ];
    // Add child ages if they have values
    $childAges_GetBookingPrice = [
        'Child1_Age' => $child1Age,
        'Child2_Age' => $child2Age,
        'Child3_Age' => $child3Age,
        'Child4_Age' => $child4Age,
        'Child5_Age' => $child5Age,
        'Child6_Age' => $child6Age
    ];
    foreach ($childAges_GetBookingPrice as $childKey_GetBookingPrice => $childAge_GetBookingPrice) {
        if (!empty($childAge_GetBookingPrice)) {
            $request_GetBookingPrice['Criteria']['Occupants'][$childKey_GetBookingPrice] = $childAge_GetBookingPrice;
        }
    }
    $result_GetBookingPrice = $client->GetBookingPrice($request_GetBookingPrice);
    //var_dump($result_GetBookingPrice);

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
    function AvailabilityFeeds($accommodationId) {
        $plugin_dir = plugin_dir_path(__FILE__);
        $availabilitiesFile = $plugin_dir.'feeds/Availabilities.xml';
        $availableRanges = array();
        $tomorrows_date = date('Y-m-d', strtotime('+1 day'));
        // Check if the file exists
        if (file_exists($availabilitiesFile) && $accommodationId) {
            // Get the contents of the file
            $availabilitiesOutput = file_get_contents($availabilitiesFile);
            // If there's an output
            if ($availabilitiesOutput !== false) {
                // Try to load the XML from the string
                $availabilitiesXml = simplexml_load_string($availabilitiesOutput);
                if ($availabilitiesXml !== false) {
                    $availabilitiesFound = false;
                    foreach ($availabilitiesXml->AccommodationList->Accommodation as $availability) {
                        if ($availability->AccommodationId == $accommodationId) {
                            foreach($availability->Availabilities->AvailabilityPeriod as $period) {
                                if ($period->State == 'AVAILABLE') {
                                    $startDate_org = (string)$period->StartDate;
                                    $endDate = (string)$period->EndDate;
                                    if ($startDate_org >= $tomorrows_date) {
                                        $startDate = $startDate_org;
                                    } else {
                                        $startDate = $tomorrows_date;
                                    }
                                    $availableRanges[] = array(
                                        "start" => $startDate,
                                        "end" => $endDate
                                    );
                                }
                            }
                        }
                    }
                    // Convert the PHP array to a JSON string
                    $availableRangesJson = json_encode($availableRanges);
                    // Output the JSON data for JavaScript to use
                    echo "<script>var availableRanges = $availableRangesJson;</script>";
                }
            }
        }
    }

    function AvailabilityFeedsPHP($accommodationId) {
        $plugin_dir = plugin_dir_path(__FILE__);
        $availabilitiesFile = $plugin_dir.'feeds/Availabilities.xml';
        $availableRanges = array();
        $tomorrows_date = date('Y-m-d', strtotime('+1 day'));
        // Check if the file exists
        if (file_exists($availabilitiesFile) && $accommodationId) {
            // Get the contents of the file
            $availabilitiesOutput = file_get_contents($availabilitiesFile);
            // If there's an output
            if ($availabilitiesOutput !== false) {
                // Try to load the XML from the string
                $availabilitiesXml = simplexml_load_string($availabilitiesOutput);
                if ($availabilitiesXml !== false) {
                    $availabilitiesFound = false;
                    foreach ($availabilitiesXml->AccommodationList->Accommodation as $availability) {
                        if ($availability->AccommodationId == $accommodationId) {
                            foreach($availability->Availabilities->AvailabilityPeriod as $period) {
                                if ($period->State == 'AVAILABLE') {
                                    $startDate_org = (string)$period->StartDate;
                                    $endDate = (string)$period->EndDate;
                                    if ($startDate_org >= $tomorrows_date) {
                                        $startDate = $startDate_org;
                                    } else {
                                        $startDate = $tomorrows_date;
                                    }
                                    $availableRanges[] = array(
                                        "start" => $startDate,
                                        "end" => $endDate
                                    );
                                }
                            }
                            $data['MinDaysNotice'] = $availability->MinDaysNotice;
                        }
                    }
                    $data['availableRanges'] = $availableRanges;
                }
            }
        }
        return $data;
    }

    function getPriceModifierFeeds($accommodationId, $PriceModifierId, $language) {
        $plugin_dir = plugin_dir_path(__FILE__);
        $priceModifiersFile = $plugin_dir.'feeds/PriceModifiers.xml';
        $data = array();
        if (file_exists($priceModifiersFile) && $accommodationId) {
            $priceModifiersOutput = file_get_contents($priceModifiersFile);
            if ($priceModifiersOutput !== false) {
                $priceModifiersXml = simplexml_load_string($priceModifiersOutput);
                if ($priceModifiersXml !== false) {
                    foreach ($priceModifiersXml->PriceModifier as $priceModifier) {
                        if ((int)$priceModifier->Id === (int)$PriceModifierId) {
                            $seasonPMData = array();
                            foreach ($priceModifier->Season as $seasonPM) {
                                $startDate = date('d/m/Y', strtotime((string)$seasonPM->StartDate));
                                $endDate = date('d/m/Y', strtotime((string)$seasonPM->EndDate));
                                $seasonPMInfo = array(
                                    'StartDate' => $startDate,
                                    'EndDate' => $endDate,
                                    'MinNumberOfNights' => (int)$seasonPM->MinNumberOfNights,
                                    'MaxNumberOfNights' => (int)$seasonPM->MaxNumberOfNights,
                                    'NumberOfNights' => (int)$seasonPM->NumberOfNights,
                                    'Type' => (string)$seasonPM->Type,
                                    'DiscountSupplementType' => (string)$seasonPM->DiscountSupplementType,
                                    'Amount' => abs((float)$seasonPM->Amount),
                                    'Currency' => abs((float)$seasonPM->Currency),
                                    'MaxDate' => abs((float)$seasonPM->MaxDate)
                                );
                                $seasonPMData[] = $seasonPMInfo; // Store each season's details in the array
                            }
                            $seasonPMCount = count($priceModifier->Season); // Count the number of <Season> elements
                            $data['TotalOffers'] = $seasonPMCount; // Assign the count to the TotalOffers key
                            $data['SeasonsPM'] = $seasonPMData; // Assign the array to the 'SeasonsPM' key
                            break; // Exit the loop once PriceModifier is found
                        }
                    }
                }
            }
        }
        return $data;
    }

    function getOccupationalRulesFeeds($accommodationId, $OccupationalRuleId, $language) {
        $plugin_dir = plugin_dir_path(__FILE__);
        $occupationalRulesFile = $plugin_dir.'feeds/OccupationalRules.xml';
        $data = array();
        if (file_exists($occupationalRulesFile) && $accommodationId) {
            $occupationalRulesOutput = file_get_contents($occupationalRulesFile);
            if ($occupationalRulesOutput !== false) {
                $occupationalRulesXml = simplexml_load_string($occupationalRulesOutput);
                if ($occupationalRulesXml !== false) {
                    foreach ($occupationalRulesXml->OccupationalRule as $occupationalRule) {
                        if ((int)$occupationalRule->Id === (int)$OccupationalRuleId) {
                            $seasonORData = array();
                            foreach ($occupationalRule->Season as $seasonOR) {
                                $startDate = date('d/m/Y', strtotime((string)$seasonOR->StartDate));
                                $endDate = date('d/m/Y', strtotime((string)$seasonOR->EndDate));
                                $seasonORInfo = array(
                                    'StartDate' => $startDate,
                                    'EndDate' => $endDate,
                                    'MinimumNights' => (int)$seasonOR->MinimumNights,
                                    'MinimumNightsOnline' => (int)$seasonOR->MinimumNightsOnline
                                );
                                $seasonORData[] = $seasonORInfo; // Store each season's details in the array
                            }
                            $data['SeasonsOR'] = $seasonORData; // Assign the array to the 'SeasonsOR' key
                            break; // Exit the loop once OccupationalRule is found
                        }
                    }
                }
            }
        }
        return $data;
    }

    function GetHTMLFeedsForAccommodation($company, $accommodationId, $accID, $dateFrom, $dateTo, $dateToAPI, $result_IsAvailable, $result_GetBookingPrice, $language, $adultsNumber, $getBookingPrice_info, $childrenNumber, $childAges) {
        $descriptionsData = getDescriptionsFeeds($accommodationId, $language);
        $accommodationData = getAccommodationFeeds($company, $accommodationId, $accID, $dateFrom, $dateTo, $dateToAPI, $result_IsAvailable, $result_GetBookingPrice, $language);
        $priceModifierData = getPriceModifierFeeds($accommodationId, $accommodationData['PriceModifierId'], $language);
        $occupationalRuleData = getOccupationalRulesFeeds($accommodationId, $accommodationData['OccupationalRuleId'], $language);
        $availabilityData = AvailabilityFeedsPHP($accommodationId, $language);
        $longTermRentalsPeriod = '365'; // 12 months converted to days
        if (isset($occupationalRuleData['SeasonsOR'][0]['MinimumNights']) && $occupationalRuleData['SeasonsOR'][0]['MinimumNights'] < $longTermRentalsPeriod) {
            /******* BANNER *******/
            $output_banner = '<div class="box-section-full">';
            // Start Breadcrumbs
            $output_banner .= '<div class="container-seo">';
            $output_banner .= '<div class="breadcrumb">';
            $output_banner .= '<a href="javascript:void(0);" onclick="goBack()" class="m-right"><i class="fas fa-arrow-left icon icon-back"></i> Back</a> | <a href="/" class="m-left"><i class="fas fa-home icon icon-homepage"></i></a>';
            $locationData = array(
                //'CountryName' => 'CountryCode',
                'RegionName' => 'RegionCode',
                'CityName' => 'CityCode',
                'ProvinceName' => 'ProvinceCode',
                'LocalityName' => 'LocalityCode',
                'DistrictName' => 'DistrictCode'
            );
            $displayedNames = array(); // To keep track of already displayed names
            foreach ($locationData as $nameKey => $codeKey) {
                if (isset($descriptionsData['Location'][$nameKey]) && !in_array($descriptionsData['Location'][$nameKey], $displayedNames)) {
                    $output_banner .= ' › <a href="#"><span>' . $descriptionsData['Location'][$nameKey] . '</span></a>';
                    $displayedNames[] = $descriptionsData['Location'][$nameKey];
                }
            }
            $output_banner .= ' › ' . $descriptionsData['AccommodationName']; // Display accommodation name at the end
            $output_banner .= '</div>';
            $output_banner .= '</div>';
            // End Breadcrumbs
            // Start #gallery_full
            $output_banner .= '<div id="gallery_full">';
            $output_banner .= '<div id="photos_section_e">';
                // Start #photo_container
                $output_banner .= '<div id="photo_container" class="grid-container">';
                    // Start #galleryGrid
                    $output_banner .= '<div id="galleryGrid" class="photo-gallery count-images-20">';
                    foreach ($descriptionsData['Images'] as $index => $image) {
                        $output_banner .= '<div>';
                        $output_banner .= '<a href="' . $image['AdaptedURI'] . '" id="ft_' . $index . '" data-size="' . $image['Type'] . '">';
                        $output_banner .= '<img src="' . $image['AdaptedURI'] . '" title="' . $image['Description'] . '" alt="' . $image['Description'] . '">';
                        $output_banner .= '</a>';
                        if ($index === 0) {
                            $output_banner .= '<span class="watermark-newretreat"></span>';
                        }
                        $output_banner .= '</div>';
                    }
                    $output_banner .= '</div>';
                    // End #galleryGrid
                    $output_banner .= '<button class="gallery-button">Photos</button>';
                    $output_banner .= '<div class="owl-nav">';
                    $output_banner .= '<div class="owl-prev"><i class="fas fa-chevron-left icon icon-left-open"></i></div>';
                    $output_banner .= '<div class="owl-next"><i class="fas fa-chevron-right icon icon-right-open"></i></div>';
                    $output_banner .= '</div>';
                    // Start #gallery
                    $output_banner .= '<div id="gallery" class="pswp" tabindex="-1" role="dialog" aria-hidden="true">';
                        $output_banner .= '<div class="pswp__bg"></div>';
                        $output_banner .= '<div class="pswp__scroll-wrap hide-gallery">';
                            $output_banner .= '<div onmouseover="hideGalleryDescription();" class="pswp__container">';
                                $output_banner .= '<div class="pswp__item"></div>';
                                $output_banner .= '<div class="pswp__item"></div>';
                                $output_banner .= '<div class="pswp__item"></div>';
                            $output_banner .= '</div>';
                            $output_banner .= '<div class="pswp__ui pswp__ui--hidden">';
                                $output_banner .= '<div class="pswp__top-bar">';
                                    $output_banner .= '<div class="pswp__counter"></div>';
                                    $output_banner .= '<button class="pswp__button pswp__button--close" title="Close (Esc)"></button>';
                                    $output_banner .= '<button class="pswp__button pswp__button--fs" title="Toggle fullscreen"></button>';
                                    $output_banner .= '<button class="pswp__button pswp__button--zoom" title="Zoom in/out"></button>';
                                    $output_banner .= '<div class="pswp__preloader">';
                                        $output_banner .= '<div class="pswp__preloader__icn">';
                                            $output_banner .= '<div class="pswp__preloader__cut">';
                                                $output_banner .= '<div class="pswp__preloader__donut"></div>';
                                            $output_banner .= '</div>';
                                        $output_banner .= '</div>';
                                    $output_banner .= '</div>';
                                $output_banner .= '</div>';
                                $output_banner .= '<button class="pswp__button pswp__button--arrow--left" title="Previous (arrow left)"></button>';
                                $output_banner .= '<button class="pswp__button pswp__button--arrow--right" title="Next (arrow right)"></button>';
                                $output_banner .= '<div class="pswp__caption seo-container">';
                                    $output_banner .= '<div class="pswp__caption__center"></div>';
                                    $output_banner .= '<i onclick="hideGalleryDescription();" class="icon icon-down-open"></i>';
                                    $output_banner .= '<button onmouseover="showGalleryDescription();" class="show-description">Description</button>';
                                $output_banner .= '</div>';
                            $output_banner .= '</div>';
                        $output_banner .= '</div>';
                    $output_banner .= '</div>';
                    // End #gallery
                $output_banner .= '</div>';
                // End #photo_container
            $output_banner .= '</div>';
            $output_banner .= '</div>';
            // End #gallery_full
            $output_banner .= '</div>';
            // End .box-section-full
            echo $output_banner;

            /******* TOP HEADER *******/
            $output_topheader = '<div id="scroll_page">';
            $output_topheader .= '<div id="acommodationContainerTitle">';
            $output_topheader .= '<div>';
            // Start .reviewsContentRates
            $totalRatings = $accommodationData['TotalRatings'];
            $totalReviews = $accommodationData['TotalReviews'];
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
                $output_topheader .= '<div class="reviewsContentRates">';
                $output_topheader .= '<div class="star-ratings' . $averageRatingConverted . '" aria-label="Rating of this property out of 5"></div>';
                $output_topheader .= '<div class="reviewsAmt">' . $totalReviews . ' review' . ($totalReviews > 1 ? 's' : '') . '</div>';
                //$output_topheader .= '<div class="favouritesProp"><i class="far fa-heart fa-lg"></i></div>';
                $output_topheader .= '<div class="idProp">Property ID: #' . $accommodationData['AccommodationId'] .'</div>';
                $output_topheader .= '</div>';
            } else {
                $output_topheader .= '<div class="reviewsContentRates">';
                $output_topheader .= '<div class="star-ratings0" aria-label="Rating of this property out of 5"></div>';
                $output_topheader .= '<div class="reviewsAmt">No reviews yet</div>';
                $output_topheader .= '<div class="idProp">Property ID: #' . $accommodationData['AccommodationId'] .'</div>';
                $output_topheader .= '</div>';
            }
            // End .reviewsContentRates
            $displayedRNames = array();
            $reversedNames = array_reverse($locationData, true);
            $output_address = '';
            foreach ($reversedNames as $nameRLKey => $codeRLKey) {
                if (isset($descriptionsData['Location'][$nameRLKey]) && !in_array($descriptionsData['Location'][$nameRLKey], $displayedRNames)) {
                    if (!empty($output_address)) {
                        $output_address .= ', ';
                    }
                    $output_address .= $descriptionsData['Location'][$nameRLKey];
                    $displayedRNames[] = $descriptionsData['Location'][$nameRLKey];
                }
            }
            $output_topheader .= '<h1><span class="accommodationName">' . $descriptionsData['AccommodationName'] . '</span>';
            if (!empty($output_address)) {
                $output_topheader .= ' <div><span class="tagSubHeader prov">' . $output_address . '</span></div>';
            }
            $output_topheader .= '</h1>';
            $output_topheader .= '</div>';
            $output_topheader .= '</div>';
            // Start #topFeatures
            $output_topheader .= '<div id="topFeatures">';
            $output_topheader .= '<div>';
            $output_topheader .= '<ul>';
            // Top Features
            $topFeatures = [];
            if (!empty($accommodationData['ExtraServices'])) {
                foreach ($accommodationData['ExtraServices'] as $serviceDescription) {
                    if (preg_match('/\((\d+)\s+spaces\)/', $serviceDescription, $matches1)) {
                        $code = (int)$matches1[1];
                        if ($code === 8) {
                            $topFeatures[] = '<li class="tooltip"><span class="tooltiptext">Internet</span> <span class="svg-wifi"></span> <span>Internet</span></li>';
                        }
                        if ($code === 9) {
                            $topFeatures[] = '<li class="tooltip"><span class="tooltiptext">Pet Friendly</span> <span class="svg-pet"></span> <span>Pet Friendly</span></li>';
                        }
                        if ($code === 1) {
                            $topFeatures[] = '<li class="tooltip"><span class="tooltiptext">Heating</span> <span class="svg-heating"></span> <span>Heating</span></li>';
                        }
                        if ($code === 2) {
                            $topFeatures[] = '<li class="tooltip"><span class="tooltiptext">Air Conditioning</span> <span class="svg-airconditioning"></span> <span>Air Conditioning</span></li>';
                        }
                        if ($code === 3) {
                            $topFeatures[] = '<li class="tooltip"><span class="tooltiptext span-left">Parking<br /></span> <span class="svg-parking"></span> <span>Parking</span></li>';
                        }
                    }
                }
            }
            if (isset($accommodationData['Features']['PeopleCapacity']) && $accommodationData['Features']['PeopleCapacity'] !== '') {
                $numPeopleCapacity = (int)$accommodationData['Features']['PeopleCapacity'];
                $topFeatures[] = '<li class="tooltip"><span class="tooltiptext">' . $numPeopleCapacity . ' Occupant' . ($numPeopleCapacity > 1 ? 's' : '') . '</span> <span class="svg-guests"></span> <span>' . $numPeopleCapacity . '</span></li>';
            } else if (isset($accommodationData['Features']['AdultsCapacity']) && $accommodationData['Features']['AdultsCapacity'] !== '') {
                $numPeopleCapacity = (int)$accommodationData['Features']['AdultsCapacity'];
                $topFeatures[] = '<li class="tooltip"><span class="tooltiptext">' . $numPeopleCapacity . ' Occupant' . ($numPeopleCapacity > 1 ? 's' : '') . '</span> <span class="svg-guests"></span> <span>' . $numPeopleCapacity . '</span></li>';
            } else {
                if (isset($accommodationData['Features']['MinimumOccupation']) && $accommodationData['Features']['MinimumOccupation'] !== '') {
                    $numPeopleCapacity = (int)$accommodationData['Features']['MinimumOccupation'];
                    $topFeatures[] = '<li class="tooltip"><span class="tooltiptext">' . $numPeopleCapacity . ' Occupant' . ($numPeopleCapacity > 1 ? 's' : '') . '</span> <div><span class="svg-guests"></span> <span>' . $numPeopleCapacity . '</span></li>';
                }
            }
            if (!empty($accommodationData['Characteristics'])) {
                foreach ($accommodationData['Characteristics'] as $viewCharacteristics) {
                    if (in_array($viewCharacteristics, ['TV', 'Garden', 'Terrace'])) {
                        if ($viewCharacteristics === 'Garden') {
                            $topFeatures[] = '<li class="tooltip"><span class="tooltiptext span-left">Garden<br /></span> <span class="svg-garden"></span> <span>Garden</span></li>';
                        }
                        if ($viewCharacteristics === 'Terrace') {
                            $topFeatures[] = '<li class="tooltip"><span class="tooltiptext">Terrace</span> <span class="svg-terrace"></span> <span>Terrace</span></li>';
                        }
                        if ($viewCharacteristics === 'TV') {
                            $topFeatures[] = '<li class="tooltip"><span class="tooltiptext span-left">TV<br /></span> <span class="svg-tv"></span> <span>TV</span></li>';
                        }

                    }
                }
            }
            if (!empty($accommodationData['CharacteristicsOptionTitles'])) {
                foreach ($accommodationData['CharacteristicsOptionTitles'] as $optionCharacteristics) {
                    if (in_array($optionCharacteristics, ['SwimmingPool', 'TVSatellite', 'HandicappedFacilities', 'Kitchen'])) {
                        if ($optionCharacteristics === 'SwimmingPool') {
                            $topFeatures[] = '<li class="tooltip"><span class="tooltiptext">Swimming Pool</span> <span class="svg-pool"></span> <span>Swimming Pool</span></li>';
                        }
                        if ($optionCharacteristics === 'HandicappedFacilities') {
                            $topFeatures[] = '<li class="tooltip"><span class="tooltiptext">Handicapped Facilities</span> <span class="svg-handicapped"></span> <span>Handicapped Facilities</span></li>';
                        }
                    }
                }
            }
            if (isset($accommodationData['Features']['Toilets']) && $accommodationData['Features']['Toilets'] !== '') {
                $numToilets = (int)$accommodationData['Features']['Toilets'];
                $topFeatures[] = '<li class="tooltip"><span class="tooltiptext tooltipbath span-left">' . $numToilets . ' Bathroom' . ($numToilets > 1 ? 's' : '') . '<br></span> <span class="svg-bath"></span> <span>' . $numToilets . '</span></li>';
            }
            if (isset($accommodationData['Features']['Bedrooms']) && $accommodationData['Features']['Bedrooms'] !== '') {
                $DoubleBeds = isset($accommodationData['Features']['DoubleBeds']) ? (int)$accommodationData['Features']['DoubleBeds'] : 0;
                $IndividualBeds = isset($accommodationData['Features']['IndividualBeds']) ? (int)$accommodationData['Features']['IndividualBeds'] : 0;
                $IndividualSofaBed = isset($accommodationData['Features']['IndividualSofaBed']) ? (int)$accommodationData['Features']['IndividualSofaBed'] : 0;
                $DoubleSofaBed = isset($accommodationData['Features']['DoubleSofaBed']) ? (int)$accommodationData['Features']['DoubleSofaBed'] : 0;
                $QueenBeds = isset($accommodationData['Features']['QueenBeds']) ? (int)$accommodationData['Features']['QueenBeds'] : 0;
                $KingBeds = isset($accommodationData['Features']['KingBeds']) ? (int)$accommodationData['Features']['KingBeds'] : 0;
                $totalBeds = $DoubleBeds + $IndividualBeds + $IndividualSofaBed + $DoubleSofaBed + $QueenBeds + $KingBeds;
                $Bedrooms = isset($accommodationData['Features']['Bedrooms']) ? (int)$accommodationData['Features']['Bedrooms'] : 0;
                $topFeatures[] = '<li class="tooltip"><span class="tooltiptext tooltipbath span-left">' . $Bedrooms . ' Bedroom' . ($Bedrooms > 1 ? 's' : '') . ' (' . $totalBeds . ' bed' . ($totalBeds > 1 ? 's' : '') . ')</span> <span class="svg-bed"></span> <span>' . $Bedrooms . '</span></li>';
            }
            if (isset($accommodationData['Features']['AreaPlotArea']) && $accommodationData['Features']['AreaPlotArea'] !== '' && isset($accommodationData['Features']['AreaPlotUnit']) && $accommodationData['Features']['AreaPlotUnit'] !== '') {
                $areaPlot = (string)$accommodationData['Features']['AreaPlotArea'] . ' ' . (string)$accommodationData['Features']['AreaPlotUnit'];
                $topFeatures[] = '<li class="tooltip"><span class="tooltiptext">Area Plot: ' . $areaPlot . '</span> <span class="svg-area"></span> <span>' . $areaPlot . '</span></li>';
            }
            $selectedTopFeatures = array_slice($topFeatures, 0, 9);
            foreach ($selectedTopFeatures as $optionTopFeatures) {
                $output_topheader .= $optionTopFeatures;
            }
            $output_topheader .= '</ul>';
            $output_topheader .= '</div>';
            $output_topheader .= '</div>';
            // End #topFeatures
            echo $output_topheader;

            /******* RIGHT SIDEBAR *******/
            $output_rightsidebar = '<div class="box-section sticky-sidebar-container">';
            $output_rightsidebar .= '<div class="sidebar-container">';
            $output_rightsidebar .= '<div id="container-content-slider" class="top">';
            $output_rightsidebar .= '<div class="right-sidebar">';
            $output_rightsidebar .= '<div class="active-offers"><span class="svg-offers"></span> <span class="text-offers">' . $priceModifierData['TotalOffers'] . ' Active Offer' . ($priceModifierData['TotalOffers'] > 1 ? 's' : '') . '</span></div>';
            $output_rightsidebar .= '<div id="response"><div class="sidebar-pricebox">';
            // Start Price
            if ($result_IsAvailable->Available->AvailableCode) {
                if ($getBookingPrice_info === 'yes') {
                    if ($result_GetBookingPrice->BookingPrice && $result_IsAvailable->Available->AvailableCode == 1) {
                        $descriptionsData = getDescriptionsFeeds($accommodationId, $language);
                        $priceConvert_BP = str_replace('EUR', '&euro;', $result_GetBookingPrice->BookingPrice->Currency);
                        $peopleText = ((int)$adultsNumber === 1) ? ' Person' : ' People';
                        $bookingStartDateAPI = strtotime($dateFrom);
                        $bookingEndDateAPI = strtotime($dateTo);
                        $bookingMinimumNightsAPI = ceil(($bookingEndDateAPI - $bookingStartDateAPI) / (60 * 60 * 24));
                        $bookingNumNightsText = ((int)$bookingMinimumNightsAPI === 1) ? ' Night' : ' Nights';
                        $output_rightsidebar .= '<div class="column-xs-4"><label class="from">From</label></div>';
                        $output_rightsidebar .= '<div class="column-xs-8"><span class="aprice">' . $priceConvert_BP . $result_GetBookingPrice->BookingPrice->RoomOnlyFinal .'</span><span class="perweek">/night</span></div>';
                        $output_rightsidebar .= '</div>';
                        $output_rightsidebar .= '<div class="sidebar-priceinfobox">';
                        $output_rightsidebar .= '<div class="column-xs-12"><label>For</label> <span class="iprice">' . (int)$bookingMinimumNightsAPI . '</span><span class="pertype">' . $bookingNumNightsText . '</span></div>';
                        $output_rightsidebar .= '<div class="column-xs-12"><label>For</label> <span class="iprice">' . (int)$adultsNumber . '</span><span class="pertype">' . $peopleText . '</span></div>';
                        $output_rightsidebar .= '<div class="bookingb">';
                        $booking_url_queries = '?FRMEntrada=' . date('d/m/Y', strtotime($dateFrom)) . '&FRMSalida=' . date('d/m/Y', strtotime($dateTo)) . '&FRMAdultos=' . (int)$adultsNumber;
                        if (!empty($childrenNumber)) {
                            $booking_url_queries .= '&FRMNinyos=' . (int)$childrenNumber;
                            if ($childrenNumber > 0) {
                                $childAges = array();
                                for ($i = 1; $i <= $childrenNumber; $i++) {
                                    $childAgeVar = 'child' . $i . 'Age';
                                    $childAges[] = (int)$$childAgeVar; // Double $$ is used to access the variable variable
                                }
                                $booking_url_queries .= '&EdadesNinyos=' . implode(';', $childAges);
                            }
                        }
                        $output_rightsidebar .= '<a href="' . $descriptionsData['BookingURL'] . $booking_url_queries . '" class="button-book">Book</a>';
                        $output_rightsidebar .= '</div>';
                    } else {
                        $output_rightsidebar .= '<div class="column-xs-4"><label class="from">From</label></div>';
                        $output_rightsidebar .= '<div class="column-xs-8"><span class="aprice">' . $accommodationData['RatePrice'] .'</span><span class="perweek">/week</span></div>';
                    }
                } else {
                    if ($result_GetBookingPrice->BookingPrice) {
                        $output_rightsidebar .= '<div class="column-xs-4"><label class="from">from</label></div>';
                        $output_rightsidebar .= '<div class="column-xs-8"><span class="aprice">' . $accommodationData['RatePrice'] .'</span><span class="perweek">/week</span></div>';
                    }
                }
            }
            // End Price
            $output_rightsidebar .= '</div></div>';
            $output_rightsidebar .= '<div id="form-container">';
            $output_rightsidebar .= '<span class="form-instructions">* You have to set the dates and guests to see the exact price.</span>';
            $output_rightsidebar .= '<div id="fomo_content"></div>';
            $currentURL_Form = esc_url($_SERVER['REQUEST_URI']);
            $output_rightsidebar .= '<form name="formReserveAccommodation" style="padding: 20px;width: 100%;box-sizing: border-box;" id="formReserveAccommodation" method="POST" action="">';
            $output_rightsidebar .= '<fieldset id="miniform_online">';
            $output_rightsidebar .= '<div id="form_minRespo">';
            $output_rightsidebar .= '<div class="dates">';
            $output_rightsidebar .= '<label for="travel-period" class="label-title">Check In/Out Dates</label>';
            $output_rightsidebar .= '<span class="sidebar-input svg-calendar-before">';
            if (isset($dateFrom)) {
                $fromDate_input = date('d/m/Y', strtotime($dateFrom));
            }
            if (isset($dateTo)) {
                $toDate_input = date('d/m/Y', strtotime($dateTo));
            }
            $output_rightsidebar .= '<input type="text" name="daterange" placeholder="From - To" value="'. $fromDate_input . ' - ' . $toDate_input . '" />';
            if (isset($_POST['dateFrom'])) {
                $fromDate_post = $_POST['dateFrom'];
            } else {
                $fromDate_post = $dateFrom;
            }
            $output_rightsidebar .= '<input type="hidden" name="dateFrom" id="dateFrom" value="' . $fromDate_post . '" />';
            if (isset($_POST['dateTo'])) {
                $toDate_post = $_POST['dateTo'];
            } else {
                $toDate_post = $dateTo;
            }
            $output_rightsidebar .= '<input type="hidden" name="dateTo" id="dateTo" value="' . $toDate_post . '" />';
            $output_rightsidebar .= '</span>';
            $output_rightsidebar .= '</div>';
            // Start Occupancy Settings
            $output_rightsidebar .= '<div class="occupancy">';
            $output_rightsidebar .= '<label for="Adults" class="label-title">Adults</label>';
            $output_rightsidebar .= '<span class="select_online">';
            $output_rightsidebar .= '<div class="personas_select">';
            $output_rightsidebar .= '<span class="sidebar-input svg-guests-before">';
            $output_rightsidebar .= '<input id="occupancy-box" class="occupancy-box" type="text" value="1 Adult - 0 Children" readonly />';
            $output_rightsidebar .= '<div id="occupancy-dropdown" class="occupancy-dropdown occupancy-hidden">';
            $output_rightsidebar .= '<div class="adult people adults-container">';
            $output_rightsidebar .= '<div class="adults-label">';
            $output_rightsidebar .= '<label for="Adults" class="label-visible">Adults</label>';
            $output_rightsidebar .= '</div>';
            $output_rightsidebar .= '<div class="adults-input">';
            $output_rightsidebar .= '<span id="plusminus-a-minus" class="plusminus handleMinus">-</span>';
            $output_rightsidebar .= '<input type="number" name="AdultNum" id="AdultNum" class="num" min="1" max="20" step="1" aria-valuemin="1" aria-valuemax="20" aria-valuenow="1" value="' . (isset($_POST['AdultNum']) ? $_POST['AdultNum'] : 1) . '" readonly />';
            $output_rightsidebar .= '<span id="plusminus-a-plus" class="plusminus handlePlus">+</span>';
            $output_rightsidebar .= '</div>';
            $output_rightsidebar .= '</div>';
            $output_rightsidebar .= '<div class="childs people childs-container">';
            $output_rightsidebar .= '<div class="childs-label">';
            $output_rightsidebar .= '<label for="Children" class="label-visible">Children</label>';
            $output_rightsidebar .= '</div>';
            $output_rightsidebar .= '<div class="childs-input">';
            $output_rightsidebar .= '<span id="plusminus-c-minus" class="plusminus handleMinus">-</span>';
            $output_rightsidebar .= '<input type="number" name="ChildrenNum" id="ChildrenNum" class="num" min="0" max="6" step="1" aria-valuemin="0" aria-valuemax="6" aria-valuenow="0" value="' . (isset($_POST['ChildrenNum']) ? $_POST['ChildrenNum'] : 0) . '" readonly />';
            $output_rightsidebar .= '<span id="plusminus-c-plus" class="plusminus handlePlus">+</span>';
            $output_rightsidebar .= '</div>';
            $output_rightsidebar .= '</div>';
            $childAges = array();
            for ($i = 1; $i <= 6; $i++) :
                $output_rightsidebar .= '<div class="child-age-selects">';
                $output_rightsidebar .= '<div class="child' . $i . ' people">';
                $output_rightsidebar .= '<div class="childs-label">';
                $output_rightsidebar .= '<label for="Child' . $i . 'Age" class="label-visible">Child ' . $i . ' Age</label>';
                $output_rightsidebar .= '</div>';
                $output_rightsidebar .= '<div class="childsage-input">';
                $output_rightsidebar .= '<select id="Child_' . $i . '_Age" class="select" name="Child_' . $i . '_Age">';
                $output_rightsidebar .= '<option value="">- -</option>';
                for ($j = 0; $j <= 6; $j++) :
                    $selected_cainput = (isset($_POST['Child_' . $i . '_Age']) && $_POST['Child_' . $i . '_Age'] === (string)$j) ? 'selected="selected"' : '';
                    $output_rightsidebar .= '<option value="' . $j . '" ' . $selected_cainput . '>' . ($j === 0 ? '0 years' : $j . ' year' . ($j > 1 ? 's' : '')) . '</option>';
                endfor;
                $output_rightsidebar .= '</select>';
                $output_rightsidebar .= '</div>';
                $output_rightsidebar .= '</div>';
                $output_rightsidebar .= '</div>';
            endfor;
            $output_rightsidebar .= '<button class="button-book-search done-button" type="button">Done</button>';
            // End Occupancy Settings
            $output_rightsidebar .= '</div>';
            $output_rightsidebar .= '</span>';
            $output_rightsidebar .= '</div>';
            $output_rightsidebar .= '</span>';
            $output_rightsidebar .= '</div>';
            $output_rightsidebar .= '<div class="alerts">';
            $output_rightsidebar .= '<div class="alert-box">';
            $output_rightsidebar .= '<i class="icon-info-circled"></i>';
            $output_rightsidebar .= '<span></span>';
            $output_rightsidebar .= '</div>';
            $output_rightsidebar .= '</div>';
            $output_rightsidebar .= '<div id="bt_act" class="botonR_fondo"><input type="hidden" name="prop_id" value="'.$accommodationId.'" /><input type="hidden" name="acc_id" value="'.$accID.'" />';
            $output_rightsidebar .= '<button class="button-book-search" type="input" name="reserve-submit-button" id="reserve-submit-button">Reserve</button>';
            $output_rightsidebar .= '</div>';
            $output_rightsidebar .= '</div>';
            $output_rightsidebar .= '</fieldset>';
            $output_rightsidebar .= '</form>';
            $output_rightsidebar .= '<span class="border-bottom1px"></span>';
            $output_rightsidebar .= '<div class="needinghelp">';
            $output_rightsidebar .= '<span class="svg-chat"></span>';
            $output_rightsidebar .= '<span class="text-box">Needing help? <span>Get in Touch</span></span>';
            $output_rightsidebar .= '</div>';
            $output_rightsidebar .= '<div class="needinghelp-buttons">';
            $output_rightsidebar .= '<a class="button-needinghelp" href="https://api.whatsapp.com/send?phone=353858054978">WhatsApp</a> <a class="button-needinghelp" id="contactPhoneButton" href="#" onclick="javascript:showPopUpWithLoad($(this), \'contactoTelefonico.php\', \'contactoTelefonico\')">Phone</a> <a class="button-needinghelp" href="#">Live Chat</a>';
            $output_rightsidebar .= '</div>';
            AvailabilityFeeds($accommodationId);
    		$output_rightsidebar .= '<div id="errormessage">';
            $todaysDate = date('Y-m-d');
            $todayTimestamp = strtotime($todaysDate);
            $otherDateTimestamp = strtotime($fromDate_post);
            $numberOfDays = floor(($otherDateTimestamp - $todayTimestamp) / (60 * 60 * 24));
            if (isset($availabilityData['MinDaysNotice']) && $availabilityData['MinDaysNotice'] < $numberOfDays) {
                $output_rightsidebar .= '<span class="form-error-results">This property requires a minimum notice of ' . $availabilityData['MinDaysNotice'] . ' day' . ($availabilityData['MinDaysNotice'] > 1 ? 's' : '') . '</span>';
            } else if (!$result_IsAvailable->Available->AvailableCode && $getBookingPrice_info === 'yes') {
                $output_rightsidebar .= '<span class="form-error-results">Dates do not match for this property</span>';
            } else if ($result_IsAvailable->Available->AvailableCode === 0 && $getBookingPrice_info === 'yes') {
                $output_rightsidebar .= '<span class="form-error-results">The accommodation is not available.</span>';
            } else if ($result_IsAvailable->Available->AvailableCode === -5 && $getBookingPrice_info === 'yes') {
                $output_rightsidebar .= '<span class="form-error-results">This property requires a ' . $result_IsAvailable->OccupationalRule->MinimumNights . ' night minimum stay</span>';
            } else if ($result_IsAvailable->Available->AvailableCode === -7 && $getBookingPrice_info === 'yes') {
                $output_rightsidebar .= '<span class="form-error-results">The number of stay exceeds the maximum permitted</span>';
            } else if (($result_IsAvailable->Available->AvailableCode === -8 || $result_IsAvailable->Available->AvailableCode === -9) && $getBookingPrice_convert === 'yes') {
                $output_rightsidebar .= '<span class="form-error-results">The accommodation is no longer available</span>';
            } else if ($result_IsAvailable->Available->AvailableCode === -99 && $getBookingPrice_info === 'yes') {
                $output_rightsidebar .= '<span class="form-error-results">The number of occupants exceeds the maximum permitted</span>';
            } else if ($result_IsAvailable->Available->AvailableCode != 1 && $getBookingPrice_info === 'yes') {
                $output_rightsidebar .= '<span class="form-error-results">' . $result_IsAvailable->Available->AvailableMessage . '</span>';
            }
            $output_rightsidebar .= '</div>';
    		$output_rightsidebar .= '</div>';
            $output_rightsidebar .= '</div>';
            $output_rightsidebar .= '</div>';
            $output_rightsidebar .= '</div>';
            if (isset($dateFrom)) {
                $getFirstDate = date('d/m/Y', strtotime($dateFrom));
            } else {
                $getFirstDate = date('d/m/Y', strtotime('+1 day'));
            }
            if (isset($dateTo)) {
                $getLastDate = date('d/m/Y', strtotime($dateTo));
            } else {
                $getLastDate = date('d/m/Y', strtotime('+3 day'));
            }
            $output_rightsidebar .= '<script>jQuery(document).ready(function($) {
                $(\'input[name="daterange"]\').daterangepicker({
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
                            "Mo",
                            "Tu",
                            "We",
                            "Th",
                            "Fr",
                            "Sa",
                            "Su"
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
                    "startDate": "' . $getFirstDate . '",
                    "endDate": "' . $getLastDate . '",
                    opens: \'left\',
                    isInvalidDate: function(date) {
                        // Check if the date is within any of the available ranges
                        for (var i = 0; i < availableRanges.length; i++) {
                            var start = moment(availableRanges[i].start);
                            var end = moment(availableRanges[i].end);
                            if (date.isBetween(start, end, null, \'[]\')) {
                                // Find the calendar cell for the start and end dates
                                var calendarCells = $(\'input[name="daterange"]\').data(\'daterangepicker\').container.find(\'.calendar tbody td\');
                                // Get the month and year of the selected date
                                var selectedMonth = date.month();
                                var selectedYear = date.year();
                                calendarCells.each(function() {
                                    var cellDate = moment($(this).attr(\'data-date\'));
                                    var cellMonth = cellDate.month();
                                    var cellYear = cellDate.year();
                                    // Check if the cells date is within the selected month and year
                                    if (cellDate.isSame(start, \'day\') && selectedMonth === cellMonth && selectedYear === cellYear) {
                                        $(this).addClass(\'start-available\');
                                    }
                                    if (cellDate.isSame(end, \'day\') && selectedMonth === cellMonth && selectedYear === cellYear) {
                                        $(this).addClass(\'end-available\');
                                    }
                                    // Hide days not part of the current month
                                    if (cellMonth !== selectedMonth || cellYear !== selectedYear) {
                                        $(this).addClass(\'hiddendates\');
                                    } else {
                                        $(this).removeClass(\'hiddendates\');
                                    }
                                });
                                return false;
                            }
                        }
                        // Return true to disable unavailable dates
                        return true;
                    },
                    isOutsideRange: function(date) {
                        var currentMonth = moment().month();
                        var selectedMonth = date.month();
                        return selectedMonth !== currentMonth;
                    },
                }, function(start, end, label) {
                    $(\'#dateFrom\').val(start.format(\'YYYY-MM-DD\'));
                    $(\'#dateTo\').val(end.format(\'YYYY-MM-DD\'));
                });
            });
            </script>';
            echo $output_rightsidebar;

            /******* DESCRIPTION *******/
            $output_description = '<div class="accordion-accommodation">';
            $output_description .= '<button class="accordion-accommodation-item active-accordion-accommodation">Description</button>';
            $output_description .= '<div class="accordion-accommodation-content" style="display: block;">';
            $output_description .= '<div id="descripcionf" class="box-left">';
            $output_description .= '<div id="description_container">';
            $output_description .= '<div id="descriptionText" class="shrinked">';
            $descriptionText = str_replace("'", "&#39;", $descriptionsData['Description']);
            $descriptionParagraphs = preg_split('/<br\s*\/?>/', $descriptionText);
            foreach ($descriptionParagraphs as $descriptionParagraph) {
                $output_description .= '<p>' . $descriptionParagraph. '</p>';
            }
            $output_description .= '<div id="readmore-container"><div class="readmore-relative"><button class="readmore-button-wrapper">Read More <span class="svg-readmore"></span></button></div></div>';
            $output_description .= '<div id="readless-container" class="read-hidden"><div class="readless-relative"><button class="readless-button-wrapper">Read Less <span class="svg-readmore"></span></button></div></div>';
            $output_description .= '</div>';
            $output_description .= '</div>';
            $output_description .= '</div>';
            $output_description .= '</div>';
            echo $output_description;

            /******* CHECKIN/CHECKOUT SCHEDULES *******/
            $output_cioschedules = '<button class="accordion-accommodation-item active-accordion-accommodation">Check In / Out Schedules</button>';
            $output_cioschedules .= '<div class="accordion-accommodation-content" style="display: block;">';
            $output_cioschedules .= '<div id="propertyInfo" class="box-left">';
            $output_cioschedules .= '<div id="checkInOutSchedules">';
            $output_cioschedules .= '<div id="schedules">';
            $output_cioschedules .= '<div>';
            if (isset($accommodationData['CheckIn']) && $accommodationData['CheckIn'] !== '') {
                if (isset($accommodationData['CheckInDays']) && $accommodationData['CheckInDays'] !== '') {
                    $checkInDays = ' | ' . $accommodationData['CheckInDays'];
                } else {
                    $checkInDays = '';
                }
                $output_cioschedules .= '<div class="schedule-entrance"><span class="check-in"></span><div><span>Check-in schedule</span><span><b>from ' . $accommodationData['CheckIn'] . $checkInDays . '</b></span></div></div>';
            }
            $output_cioschedules .= '<hr class="seperator-schedules">';
            if (isset($accommodationData['CheckOut']) && $accommodationData['CheckOut'] !== '') {
                $output_cioschedules .= '<div class="schedule-exit"><span class="check-out"></span><div><span>Check-out schedule</span><span><b>before ' . $accommodationData['CheckOut'] . '</b></span></div></div>';
            }
            $output_cioschedules .= '</div>';
            $output_cioschedules .= '</div>';
            $output_cioschedules .= '</div>';
            $output_cioschedules .= '</div>';
            $output_cioschedules .= '</div>';
            echo $output_cioschedules;

            /******* AVAILABILITY CALENDER *******/
            $output_availabilitycalender = '<button class="accordion-accommodation-item active-accordion-accommodation">Availability Calendar</button>';
            $output_availabilitycalender .= '<div class="accordion-accommodation-content" style="display: block;">';
            // Start #calendar-section
            $output_availabilitycalender .= '<div id="calendar-section" class="box-left">';
            // Start #calendar-container
            $output_availabilitycalender .= '<div id="calendar-container">';
            $currentYear = date('Y');
            $currentMonth = date('n');
            $monthsToShow = 6;
            // First 6 months
            $output_availabilitycalender .= '<div id="month-first-half" class="">';
            for ($i = 0; $i < $monthsToShow; $i++) {
                $year = $currentYear;
                $month = $currentMonth + $i;
                if ($month > 12) {
                    $month -= 12;
                    $year += 1;
                }
                $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                $weeksInMonth = ceil($daysInMonth / 7);
                $output_availabilitycalender .= '<div class="calendar-month">';
                $output_availabilitycalender .= '<table class="calendarTable" cellspacing="1" cellpadding="0">';
                $output_availabilitycalender .= '<tr>';
                $output_availabilitycalender .= '<td class="monthYearText monthYearRow" colspan="7">' . date('F Y', strtotime($year . '-' . $month . '-01')) . '</td>';
                $output_availabilitycalender .= '</tr>';
                $output_availabilitycalender .= '<tr class="dayNamesText">';
                $output_availabilitycalender .= '<td class="dayNamesRow" width="14%">Mon</td><td class="dayNamesRow" width="14%">Tue</td><td class="dayNamesRow" width="14%">Wed</td><td class="dayNamesRow" width="14%">Thur</td><td class="dayNamesRow" width="14%">Fri</td><td class="dayNamesRow" width="14%">Sat</td><td class="dayNamesRow" width="14%">Sun</td>';
                $output_availabilitycalender .= '</tr>';
                for ($week = 0; $week < $weeksInMonth; $week++) {
                    $output_availabilitycalender .= '<tr class="rows">';
                    for ($dayOfWeek = 0; $dayOfWeek < 7; $dayOfWeek++) {
                        $currentDay = $dayOfWeek + ($week * 7) + 1; // Calculate the current day in the loop
                        // Check if the day is within the month
                        if ($currentDay <= $daysInMonth) {
                            // Determine the availability class based on the current date
                            $availabilityClass = 'diaNotAllowed'; // Default class
                            foreach ($availabilityData['availableRanges'] as $rangeAvail) {
                                $startRangeAvail = new DateTime($rangeAvail['start']);
                                $endRangeAvail = new DateTime($rangeAvail['end']);
                                $currentDate = new DateTime("$year-$month-$currentDay");
                                if ($currentDate >= $startRangeAvail && $currentDate <= $endRangeAvail) {
                                    $availabilityClass = 'diaFree'; // Set class to free for available days
                                    break; // No need to check further
                                }
                            }
                            // Update the corresponding HTML cell with the availability class and day
                            $output_availabilitycalender .= '<td class="' . $availabilityClass . '">' . $currentDay . '</td>';
                        } else {
                            // Fill in empty cells for days outside the month
                            $output_availabilitycalender .= '<td class="diaNotAllowed"></td>';
                        }
                    }
                    $output_availabilitycalender .= '</tr>';
                }
                $output_availabilitycalender .= '</table>';
                $output_availabilitycalender .= '</div>';
            }
            $output_availabilitycalender .= '</div>';
            // Last 6 months
            $output_availabilitycalender .= '<div id="month-second-half" class="hidden-calendar">';
            for ($i = 0; $i < $monthsToShow; $i++) {
                $year = $currentYear;
                $month = $currentMonth + $i + $monthsToShow;
                if ($month > 12) {
                    $month -= 12;
                    $year += 1;
                }
                $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                $weeksInMonth = ceil($daysInMonth / 7);
                $output_availabilitycalender .= '<div class="calendar-month">';
                $output_availabilitycalender .= '<table class="calendarTable" cellspacing="1" cellpadding="0">';
                $output_availabilitycalender .= '<tr>';
                $output_availabilitycalender .= '<td class="monthYearText monthYearRow" colspan="7">' . date('F Y', strtotime($year . '-' . $month . '-01')) . '</td>';
                $output_availabilitycalender .= '</tr>';
                $output_availabilitycalender .= '<tr class="dayNamesText">';
                $output_availabilitycalender .= '<td class="dayNamesRow" width="14%">Mon</td><td class="dayNamesRow" width="14%">Tue</td><td class="dayNamesRow" width="14%">Wed</td><td class="dayNamesRow" width="14%">Thur</td><td class="dayNamesRow" width="14%">Fri</td><td class="dayNamesRow" width="14%">Sat</td><td class="dayNamesRow" width="14%">Sun</td>';
                $output_availabilitycalender .= '</tr>';
                for ($week = 0; $week < $weeksInMonth; $week++) {
                    $output_availabilitycalender .= '<tr class="rows">';
                    for ($dayOfWeek = 0; $dayOfWeek < 7; $dayOfWeek++) {
                        $currentDay = $dayOfWeek + ($week * 7) + 1; // Calculate the current day in the loop
                        // Check if the day is within the month
                        if ($currentDay <= $daysInMonth) {
                            // Determine the availability class based on the current date
                            $availabilityClass = 'diaNotAllowed'; // Default class
                            foreach ($availabilityData['availableRanges'] as $rangeAvail) {
                                $startRangeAvail = new DateTime($rangeAvail['start']);
                                $endRangeAvail = new DateTime($rangeAvail['end']);
                                $currentDate = new DateTime("$year-$month-$currentDay");
                                if ($currentDate >= $startRangeAvail && $currentDate <= $endRangeAvail) {
                                    $availabilityClass = 'diaFree'; // Set class to free for available days
                                    break; // No need to check further
                                }
                            }
                            // Update the corresponding HTML cell with the availability class and day
                            $output_availabilitycalender .= '<td class="' . $availabilityClass . '">' . $currentDay . '</td>';
                        } else {
                            // Fill in empty cells for days outside the month
                            $output_availabilitycalender .= '<td class="diaNotAllowed"></td>';
                        }
                    }
                    $output_availabilitycalender .= '</tr>';
                }
                $output_availabilitycalender .= '</table>';
                $output_availabilitycalender .= '</div>';
            }
            $output_availabilitycalender .= '</div>';

            $output_availabilitycalender .= '<div id="icon_left_calendar" class="disabled"><i class="fas fa-chevron-left icon"></i></div>';
            $output_availabilitycalender .= '<div id="icon_right_calendar" class=""><i class="fas fa-chevron-right icon"></i></div>';
            $output_availabilitycalender .= '</div>';
            // End #calendar-container
            $output_availabilitycalender .= '</div>';
            $output_availabilitycalender .= '</div>';
            echo $output_availabilitycalender;

            /******* AMENITIES (FEATURES) *******/
            $output_features = '<button class="accordion-accommodation-item active-accordion-accommodation">Amenities</button>';
            $output_features .= '<div class="accordion-accommodation-content" style="display: block;">';
            $output_features .= '<div id="propertyInfo" class="box-left">';
            // Start #mainFeatures
            $output_features .= '<div id="mainFeatures">';
            $output_features .= '<h3 class="subtitle-section">Featured</h3>';
            $output_features .= '<div class="features">';
            $topFeatured = [];
            if (!empty($accommodationData['ExtraServices'])) {
                foreach ($accommodationData['ExtraServices'] as $serviceDescription) {
                    if (preg_match('/\((\d+)\s+spaces\)/', $serviceDescription, $matches1)) {
                        $code = (int)$matches1[1];
                        if ($code === 8) {
                            $topFeatured[] = '<div class="feature"><div><span class="svg-wifi"></span> <span>Internet</span></div></div>';
                        }
                        if ($code === 9) {
                            $topFeatured[] = '<div class="feature"><div><span class="svg-pet"></span> <span>Pet Friendly</span></div></div>';
                        }
                        if ($code === 1) {
                            $topFeatured[] = '<div class="feature"><div><span class="svg-heating"></span> <span>Heating</span></div></div>';
                        }
                        if ($code === 2) {
                            $topFeatured[] = '<div class="feature"><div><span class="svg-airconditioning"></span> <span>Air Conditioning</span></div></div>';
                        }
                        if ($code === 3) {
                            $topFeatured[] = '<div class="feature"><div><span class="svg-parking"></span> <span>Parking</span></div></div>';
                        }
                    }
                }
            }
            if (isset($accommodationData['Features']['PeopleCapacity']) && $accommodationData['Features']['PeopleCapacity'] !== '') {
                $numPeopleCapacity = (int)$accommodationData['Features']['PeopleCapacity'];
                $topFeatured[] = '<div class="feature"><div><span class="svg-guests"></span> <span>' . $numPeopleCapacity . ' Occupant' . ($numPeopleCapacity > 1 ? 's' : '') . '</span></div></div>';
            } else if (isset($accommodationData['Features']['AdultsCapacity']) && $accommodationData['Features']['AdultsCapacity'] !== '') {
                $numPeopleCapacity = (int)$accommodationData['Features']['AdultsCapacity'];
                $topFeatured[] = '<div class="feature"><div><span class="svg-guests"></span> <span>' . $numPeopleCapacity . ' Occupant' . ($numPeopleCapacity > 1 ? 's' : '') . '</span></div></div>';
            } else {
                if (isset($accommodationData['Features']['MinimumOccupation']) && $accommodationData['Features']['MinimumOccupation'] !== '') {
                    $numPeopleCapacity = (int)$accommodationData['Features']['MinimumOccupation'];
                    $topFeatured[] = '<div class="feature"><div><span class="svg-guests"></span> <span>' . $numPeopleCapacity . ' Occupant' . ($numPeopleCapacity > 1 ? 's' : '') . '</span></div></div>';
                }
            }
            if (!empty($accommodationData['Characteristics'])) {
                foreach ($accommodationData['Characteristics'] as $viewCharacteristics) {
                    if (in_array($viewCharacteristics, ['TV', 'Garden', 'Terrace'])) {
                        if ($viewCharacteristics === 'Garden') {
                            $topFeatured[] = '<div class="feature"><div><span class="svg-garden"></span> <span>Garden</span></div></div>';
                        }
                        if ($viewCharacteristics === 'Terrace') {
                            $topFeatured[] = '<div class="feature"><div><span class="svg-terrace"></span> <span>Terrace</span></div></div>';
                        }
                        if ($viewCharacteristics === 'TV') {
                            $topFeatured[] = '<div class="feature"><div><span class="svg-tv"></span> <span>TV</span></div></div>';
                        }

                    }
                }
            }
            if (!empty($accommodationData['CharacteristicsOptionTitles'])) {
                foreach ($accommodationData['CharacteristicsOptionTitles'] as $optionCharacteristics) {
                    if (in_array($optionCharacteristics, ['SwimmingPool', 'TVSatellite', 'HandicappedFacilities', 'Kitchen'])) {
                        if ($optionCharacteristics === 'SwimmingPool') {
                            $topFeatured[] = '<div class="feature"><div><span class="svg-pool"></span> <span>Swimming Pool</span></div></div>';
                        }
                        if ($optionCharacteristics === 'HandicappedFacilities') {
                            $topFeatured[] = '<div class="feature"><div><span class="svg-handicapped"></span> <span>Handicapped Facilities</span></div></div>';
                        }
                    }
                }
            }
            if (isset($accommodationData['Features']['AreaPlotArea']) && $accommodationData['Features']['AreaPlotArea'] !== '' && isset($accommodationData['Features']['AreaPlotUnit']) && $accommodationData['Features']['AreaPlotUnit'] !== '') {
                $areaPlot = (string)$accommodationData['Features']['AreaPlotArea'] . ' ' . (string)$accommodationData['Features']['AreaPlotUnit'];
                $topFeatured[] = '<div class="feature"><div><span class="svg-area"></span> <span>Area Plot: ' . $areaPlot . '</span></div></div>';
            }
            foreach ($topFeatured as $optionTopFeatured) {
                $output_features .= $optionTopFeatured;
            }
            $output_features .= '</div>';
            $output_features .= '</div>';
            // End #mainFeatures
            // Start #general
            $output_features .= '<div id="mainGeneral">';
            $output_features .= '<h3 class="subtitle-section">General</h3>';
            $output_features .= '<div class="features">';
            if (!empty($accommodationData['CharacteristicsGeneral'])) {
                foreach ($accommodationData['CharacteristicsGeneral'] as $optionCharacteristicsGeneral) {
                    $output_features .= '<div class="feature"><div> <span class="features-item-check"></span> <span class="feature-text">' . $optionCharacteristicsGeneral . '</span></div></div>';
                }
            }
            $output_features .= '</div>';
            $output_features .= '</div>';
            // End #general
            // Start #mainBedrooms
            $output_features .= '<div id="mainBedrooms">';
            $output_features .= '<h3 class="subtitle-section">Bedroom(s)</h3>';
            $output_features .= '<div class="features">';
            if (isset($accommodationData['Features']['Bedrooms']) && $accommodationData['Features']['Bedrooms'] !== '') {
                if (isset($accommodationData['Features']['DoubleBeds']) && $accommodationData['Features']['DoubleBeds'] !== '') {
                    $DoubleBeds_num = isset($accommodationData['Features']['DoubleBeds']) ? (int)$accommodationData['Features']['DoubleBeds'] : 0;
                    $output_features .= '<div class="feature"><div><span class="svg-dbed"></span> <span>' . $DoubleBeds_num . ' Double Bed' . ($DoubleBeds_num > 1 ? 's' : '') . '</span></div></div>';
                }
                if (isset($accommodationData['Features']['IndividualBeds']) && $accommodationData['Features']['IndividualBeds'] !== '') {
                    $IndividualBeds_num = isset($accommodationData['Features']['IndividualBeds']) ? (int)$accommodationData['Features']['IndividualBeds'] : 0;
                    $output_features .= '<div class="feature"><div><span class="svg-ibed"></span> <span>' . $IndividualBeds_num . ' Individual Bed' . ($IndividualBeds_num > 1 ? 's' : '') . '</span></div></div>';
                }
                if (isset($accommodationData['Features']['DoubleSofaBed']) && $accommodationData['Features']['DoubleSofaBed'] !== '') {
                    $DoubleSofaBed_num = isset($accommodationData['Features']['DoubleSofaBed']) ? (int)$accommodationData['Features']['DoubleSofaBed'] : 0;
                    $output_features .= '<div class="feature"><div><span class="svg-dsbed"></span> <span>' . $DoubleSofaBed_num . ' Double Sofa Bed' . ($DoubleSofaBed_num > 1 ? 's' : '') . '</span></div></div>';
                }
                if (isset($accommodationData['Features']['IndividualSofaBed']) && $accommodationData['Features']['IndividualSofaBed'] !== '') {
                    $IndividualSofaBed_num = isset($accommodationData['Features']['IndividualSofaBed']) ? (int)$accommodationData['Features']['IndividualSofaBed'] : 0;
                    $output_features .= '<div class="feature"><div><span class="svg-isbed"></span> <span>' . $IndividualSofaBed_num . ' Individual Sofa Bed' . ($IndividualSofaBed_num > 1 ? 's' : '') . '</span></div></div>';
                }
                if (isset($accommodationData['Features']['KingBeds']) && $accommodationData['Features']['KingBeds'] !== '') {
                    $KingBeds_num = isset($accommodationData['Features']['KingBeds']) ? (int)$accommodationData['Features']['KingBeds'] : 0;
                    $output_features .= '<div class="feature"><div><span class="svg-kbed"></span> <span>' . $KingBeds_num . ' King Bed' . ($KingBeds_num > 1 ? 's' : '') . '</span></div></div>';
                }
                if (isset($accommodationData['Features']['QueenBeds']) && $accommodationData['Features']['QueenBeds'] !== '') {
                    $QueenBeds_num = isset($accommodationData['Features']['QueenBeds']) ? (int)$accommodationData['Features']['QueenBeds'] : 0;
                    $output_features .= '<div class="feature"><div><span class="svg-qbed"></span> <span>' . $QueenBeds_num . ' Queen Bed' . ($QueenBeds_num > 1 ? 's' : '') . '</span></div></div>';
                }
            } else {
                $output_features .= '<div class="feature"><div>No bedrooms available</div></div>';
            }
            $output_features .= '</div>';
            $output_features .= '</div>';
            // End #mainBedrooms
            // Start #mainKitchen
            $output_features .= '<div id="mainKitchen">';
            $output_features .= '<h3 class="subtitle-section">Independent Kitchen (electric)</h3>';
            $output_features .= '<div class="features">';
            if (!empty($accommodationData['KitchenOptionTitles'])) {
                foreach ($accommodationData['KitchenOptionTitles'] as $optionKitchenCharacteristics) {
                    $formattedKitchenTitle = preg_replace('/(?<=\\w)(?=[A-Z])/', ' $1', $optionKitchenCharacteristics);
                    $formattedKitchenTitle = trim($formattedKitchenTitle);
                    $output_features .= '<div class="feature"><div> <span class="features-item-check"></span> <span class="feature-text">' . $formattedKitchenTitle . '</span></div></div>';
                }
            } else {
                $output_features .= '<div class="feature"><div>No kitchen available</div></div>';
            }
            $output_features .= '</div>';
            $output_features .= '</div>';
            // End #mainKitchen
            // Start #mainBathrooms
            $output_features .= '<div id="mainBathrooms">';
            $output_features .= '<h3 class="subtitle-section">Bathroom(s)</h3>';
            $output_features .= '<div class="features">';
            if (isset($accommodationData['Features']['Toilets']) && $accommodationData['Features']['Toilets'] !== '') {
                if (isset($accommodationData['Features']['BathroomWithBathtub']) && $accommodationData['Features']['BathroomWithBathtub'] !== '') {
                    $BathroomWithBathtub_num = isset($accommodationData['Features']['BathroomWithBathtub']) ? (int)$accommodationData['Features']['BathroomWithBathtub'] : 0;
                    $output_features .= '<div class="feature"><div><span class="svg-bath"></span> <span>' . $BathroomWithBathtub_num . ' Bathroom' . ($BathroomWithBathtub_num > 1 ? 's' : '') . ' With Bathtub</span></div></div>';
                }
                if (isset($accommodationData['Features']['BathroomWithShower']) && $accommodationData['Features']['BathroomWithShower'] !== '') {
                    $BathroomWithShower_num = isset($accommodationData['Features']['BathroomWithShower']) ? (int)$accommodationData['Features']['BathroomWithShower'] : 0;
                    $output_features .= '<div class="feature"><div> <span class="svg-bath"></span> <span>' . $BathroomWithShower_num . ' Bathroom' . ($BathroomWithShower_num > 1 ? 's' : '') . ' With Shower</span></div></div>';
                }
            } else {
                $output_features .= '<div class="feature"><div>No bathrooms available</div></div>';
            }
            $output_features .= '</div>';
            $output_features .= '</div>';
            // End #mainBathrooms
            // Start #mainViews
            $output_features .= '<div id="mainViews">';
            $output_features .= '<h3 class="subtitle-section">View(s) from Accommodation</h3>';
            $output_features .= '<div class="features">';
            if (!empty($accommodationData['ViewType'])) {
                foreach ($accommodationData['ViewType'] as $viewDescription) {
                    if (in_array($viewDescription, ['Swimming Pool', 'Garden', 'Mountain', 'Lake', 'River', 'Golf', 'Beach'])) {
                        if ($viewDescription === 'Garden') {
                            $output_features .= '<div class="feature"><div> <span class="svg-garden"></span> <span>Garden</span></div></div>';
                        }
                        if ($viewDescription === 'Mountain') {
                            $output_features .= '<div class="feature"><div> <span class="svg-mountain"></span> <span>Mountain</span></div></div>';
                        }
                        if ($viewDescription === 'Swimming Pool') {
                            $output_features .= '<div class="feature"><div> <span class="svg-pool"></span> <span>Swimming Pool</span></div></div>';
                        }
                        if ($viewDescription === 'Lake') {
                            $output_features .= '<div class="feature"><div> <span class="svg-lake"></span> <span>Lake</span></div></div>';
                        }
                        if ($viewDescription === 'River') {
                            $output_features .= '<div class="feature"><div> <span class="svg-lake"></span> <span>River</span></div></div>';
                        }
                        if ($viewDescription === 'Golf') {
                            $output_features .= '<div class="feature"><div> <span class="svg-lake"></span> <span>Golf</span></div></div>';
                        }
                        if ($viewDescription === 'Beach') {
                            $output_features .= '<div class="feature"><div> <span class="svg-lake"></span> <span>Beach</span></div></div>';
                        }
                    }
                }
            } else {
                $output_features .= '<div class="feature"><div>No views available</div></div>';
            }
            $output_features .= '</div>';
            $output_features .= '</div>';
            // End #mainViews
            $output_features .= '</div>';
            $output_features .= '</div>';
            /******* SERVICES *******/
            $output_features .= '<button class="accordion-accommodation-item">Services</button>';
            $output_features .= '<div class="accordion-accommodation-content">';
            $output_features .= '<div id="propertyInfo" class="box-left">';
            // Start #mainExtraServices
            $output_features .= '<div id="mainExtraServices">';
            $output_features .= '<h3 class="subtitle-section">Mandatory or included services</h3>';
            $output_features .= '<div class="features">';
            // Loop through obligatory or included services
            if (!empty($descriptionsData['Extras']['ObligatoryOrIncluded'])) {
                foreach ($descriptionsData['Extras']['ObligatoryOrIncluded'] as $extraDescription) {
                    if ($extraDescription['Name'] !== 'Security Deposit (refundable)') {
                        $output_features .= '<div class="feature"><div> <span class="features-item-check"></span> <span class="feature-text">' . $extraDescription['Name'] . ' - ' . $extraDescription['Description'] . '</span></div></div>';
                    }
                }
            } else {
                $output_features .= '<div class="feature"><div>No obligatory or included services available</div></div>';
            }
            $output_features .= '</div>';
            $output_features .= '</div>';
            // End #mainExtraServices
            // Start #mainOptionalServices
            $output_features .= '<div id="mainOptionalServices">';
            $output_features .= '<h3 class="subtitle-section">Optional services</h3>';
            $output_features .= '<div class="features">';
            // Loop through optional services
            if (!empty($descriptionsData['Extras']['Optional'])) {
                foreach ($descriptionsData['Extras']['Optional'] as $extraDescription) {
                    $output_features .= '<div class="feature"><div> <span class="features-item-check"></span> <span class="feature-text">' . $extraDescription['Name'] . ' - ' . $extraDescription['Description'] . '</span></div></div>';
                }
            } else {
                $output_features .= '<div class="feature"><div>No optional services available</div></div>';
            }
            $output_features .= '</div>';
            $output_features .= '</div>';
            // End #mainOptionalServices
            $output_features .= '</div>';
            $output_features .= '</div>';
            echo $output_features;

            /******* SPECIAL OFFERS *******/
            $output_specialoffers = '';
            // Loop through obligatory or included services
            if (!empty($priceModifierData['SeasonsPM'])) {
                foreach ($priceModifierData['SeasonsPM'] as $offersDescription) {
                    $output_specialoffers .= '<button class="accordion-accommodation-item active-accordion-accommodation">Special Offers</button>';
                    $output_specialoffers .= '<div class="accordion-accommodation-content" style="display: block;">';
                    $output_specialoffers .= '<div id="propertyInfo" class="box-left">';
                    // Start #mainSpecialOffers
                    $output_specialoffers .= '<div id="mainSpecialOffers">';
                    $output_specialoffers .= '<div class="features">';
                    if ($offersDescription['MinNumberOfNights']) {
                        $offerNights = ' for a minimum of ' . $offersDescription['MinNumberOfNights'] . ' night' . ($offersDescription['MinNumberOfNights'] > 1 ? 's' : '');
                    } else if ($offersDescription['MaxNumberOfNights']) {
                        $offerNights = ' for a maximum of ' . $offersDescription['MaxNumberOfNights'] . ' night' . ($offersDescription['MaxNumberOfNights'] > 1 ? 's' : '');
                    } else if ($offersDescription['NumberOfNights']) {
                        $MinNumberOfNights = ' for ' . $offersDescription['NumberOfNights'] . ' night' . ($offersDescription['NumberOfNights'] > 1 ? 's' : '');
                    } else {
                        $offerNights = '';
                    }
                    if ($offersDescription['DiscountSupplementType'] === 'percent') {
                        if ($offersDescription['Type']) {
                            $offerType = $offersDescription['Type'];
                        } else {
                            $offerType = '%';
                        }
                        $offerAmtType = $offersDescription['Amount'] . $offerType;
                    } else if ($offersDescription['DiscountSupplementType'] === 'amount') {
                        if ($offersDescription['Type']) {
                            //$offerType = $offersDescription['Type'];
                            $offerType = '&euro;';
                        } else {
                            $offerType = '&euro;';
                        }
                        $offerAmtType = $offerType . $offersDescription['Amount'];
                    } else {
                        $offerAmtType = '';
                    }
                    $output_specialoffers .= '<div class="feature"><div class="offer-header"><span class="offer-first"><span class="svg-offers"></span></span><span class="offer-percentage">' . $offerAmtType . '</span></div>';
                    $output_specialoffers .= '<div class="offer-content"><span class="offer-title">Discount of ' . $offerAmtType . $offerNights . '</span>';
                    $output_specialoffers .= '<span class="offer-startend">From: ' . $offersDescription['StartDate'] . '</span>';
                    $output_specialoffers .= '<span class="offer-startend">To: ' . $offersDescription['EndDate'] . '</span></div></div>';
                    $output_specialoffers .= '</div>';
                    $output_specialoffers .= '</div>';
                    // End #mainSpecialOffers
                    $output_specialoffers .= '</div>';
                    $output_specialoffers .= '</div>';
                }
            }
            echo $output_specialoffers;

            /******* MAP & NEARBY ATTRACTIONS *******/
            $output_mapandattractions = '<button class="accordion-accommodation-item">Map &amp; Nearby Attractions</button>';
            $output_mapandattractions .= '<div class="accordion-accommodation-content">';
            $output_mapandattractions .= '<div id="propertyInfo" class="box-left">';
            // Start #mainMap
            $output_mapandattractions .= '<div id="mainMap">';
                //$output_mapandattractions .= '<div id="mapAround">';
                    //$output_mapandattractions .= '<div class="sectionMap"><h2 class="class_title">Map and distances <span class="icon-expand-1"><i class="fas fa-expand-alt"> </i></span></h2></div>';
                //$output_mapandattractions .= '</div>';
            if (!empty($accommodationData['Places'])) {
                $output_mapandattractions .= '<div class="features">';
                foreach ($accommodationData['Places'] as $placesDescription) {
                    $output_mapandattractions .= '<div class="feature"><div> <span>' . $placesDescription . '</span></div></div>';
                }
                $output_mapandattractions .= '</div>';
            }
            /*if (!empty($accommodationData['Places'])) {
                $output_mapandattractions .= '<div id="container_distances">';
                    $output_mapandattractions .= '<div id="text_distancesM">';
                        $output_mapandattractions .= '<button class="accordion_map" id="accordion_map"><a class="accEllipsis"> Nearby Attractions <small>(Points of Interest)</small></a></button>';
                        $output_mapandattractions .= '<div class="panel">';
                            $output_mapandattractions .= '<b class="distanceTitle">Distances</b>';
                            $output_mapandattractions .= '<ul class="textDA textDa_border">';
                            foreach ($accommodationData['Places'] as $placesDescription) {
                                $output_mapandattractions .= '<li class="liDistances"><p class="alignleft-distances">' . $placesDescription . '</p></li>';
                            }
                            $output_mapandattractions .= '</ul>';
                        $output_mapandattractions .= '</div>';
                    $output_mapandattractions .= '</div>';
                $output_mapandattractions .= '</div>';
            }*/
            $output_mapandattractions .= '<div class="overlay" onClick="style.pointerEvents=\'none\'"></div>';
            $output_mapandattractions .= '<div id="map"><iframe width="100%" height="100%" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" id="gmap_canvas" src="https://maps.google.com/maps?width=100%&amp;height=100%&amp;hl=en&amp;q=' . $accommodationData['LocalizationData']['GoogleLatitude'] . ',' . $accommodationData['LocalizationData']['GoogleLongitude'] . '&amp;t=&amp;z=' . $accommodationData['LocalizationData']['GoogleZoom'] . '&amp;ie=UTF8&amp;iwloc=B&amp;output=embed"></iframe></div>';
            $output_mapandattractions .= '</div>';
            $output_mapandattractions .= '</div>';
            $output_mapandattractions .= '</div>';
            echo $output_mapandattractions;

            /******* REVIEWS *******/
            $output_reviews = '<button class="accordion-accommodation-item">Reviews</button>';
            $output_reviews .= '<div class="accordion-accommodation-content">';
            $output_reviews .= '<div class="reviews-container">';
            $output_reviews .= '<div id="reviews" class="box-left" data-language-default="EN">';
            // Start .sectionReviews
            $output_reviews .= '<div class="sectionReviews">';
            $output_reviews .= '<div class="firstReviews">';
            if ($totalReviews > 0) {
                $averageRating = $totalRatings / $totalReviews;
                $convertedRatingOutOf5 = number_format(($averageRating / 10) * 5, 1);
                $output_reviews .= '<span class="reviewMedia">' . $convertedRatingOutOf5 . '</span>';
            } else {
                $output_reviews .= '<span class="reviewMedia">0</span>';
                $totalReviews = '0';
            }
            $output_reviews .= '</div>';
            $output_reviews .= '<div class="secondReviews">';
            $output_reviews .= '<span class="reviews_title ws">General Rating</span>';
            $output_reviews .= '<span class="num_reviews">' . $totalReviews . ' <span class="text_reviews">review' . ($totalReviews > 1 ? 's' : '') . '</span></span>';
            $output_reviews .= '</div>';
            $output_reviews .= '</div>';
            // End .sectionReviews
            $output_reviews .= '<span class="review-instructions">All reviews have been checked and authenticated. Once you have received a "rate your stay" email based on your confirmed booking, you can leave an accommodation review.</span>';
            // Start #mainReviews
            $output_reviews .= '<div id="mainReviews">';
            $output_reviews .= '<div class="features">';
            $averageAspectRatings = $accommodationData['AverageAspectRatings'];
            foreach ($averageAspectRatings as $aspectType => $averageRating) {
                // Calculate formatted aspect rating
                $convertedAspectRatingOutOf5 = number_format(($averageRating / 10) * 5, 1);
                $averageAspectRatingRounded = $convertedAspectRatingOutOf5;
                if ($averageAspectRatingRounded > 5) {
                    $averageAspectRatingConverted = '5';
                } else {
                    $integerAspectPart = floor($averageAspectRatingRounded);
                    $decimalAspectPart = ($averageAspectRatingRounded - $integerAspectPart) * 10;
                    if ($decimalAspectPart == 5) {
                        $averageAspectRatingConverted = sprintf("%02d.5", $integerAspectPart);
                    } else {
                        $averageAspectRatingConverted = sprintf("%d%d", $integerAspectPart, $decimalAspectPart);
                    }
                }
                $output_reviews .= '<div class="feature"><div> <span class="feature-text uppercase-text">' . ucfirst(strtolower($aspectType)) . '</span><span class="star-ratings' . $averageAspectRatingConverted . '" aria-label="Rating of this review out of 5"></span></div></div>';
            }
            $output_reviews .= '</div>';
            if (!empty($accommodationData['Reviews'])) {
                $output_reviews .= '<div class="features">';
                // Start Swiper
                $output_reviews .= '<div class="swiper customerReviewsSwiper">';
                $output_reviews .= '<div class="swiper-wrapper">';
                foreach ($accommodationData['Reviews'] as $reviewsComments) {
                    $output_reviews .= '<div class="swiper-slide">';
                    $output_reviews .= '<div class="customerReviews">';
                    $output_reviews .= '<div>';
                    if ($reviewsComments['Title']) {
                        $output_reviews .= '<span class="reviewTitle">' . $reviewsComments['Title'] . '</span>';
                    }
                    if ($reviewsComments['GeneralRating']) {
                        $convertedGeneralAspectRatingOutOf5 = number_format(($reviewsComments['GeneralRating'] / 10) * 5, 1);
                        $averageGeneralAspectRatingRounded = $convertedGeneralAspectRatingOutOf5;
                        if ($averageGeneralAspectRatingRounded > 5) {
                            $averageGeneralAspectRatingConverted = '5';
                        } else {
                            $integerGeneralAspectPart = floor($averageGeneralAspectRatingRounded);
                            $decimalGeneralAspectPart = ($averageGeneralAspectRatingRounded - $integerGeneralAspectPart) * 10;
                            if ($decimalGeneralAspectPart == 5) {
                                $averageGeneralAspectRatingConverted = sprintf("%02d.5", $integerGeneralAspectPart);
                            } else {
                                $averageGeneralAspectRatingConverted = sprintf("%d%d", $integerGeneralAspectPart, $decimalGeneralAspectPart);
                            }
                        }
                        $output_reviews .= '<span class="star-ratings' . $averageGeneralAspectRatingConverted . ' reviewRating" aria-label="Rating of this review out of 5"></span>';
                    }
                    if ($reviewsComments['PositiveComment']) {
                        $output_reviews .= '<span class="reviewComment">&quot;' . $reviewsComments['PositiveComment'] . '&quot;</span>';
                    }
                    if ($reviewsComments['NegativeComment']) {
                        $output_reviews .= '<span class="reviewComment">&quot;' . $reviewsComments['NegativeComment'] . '&quot;</span>';
                    }
                    if ($reviewsComments['GuestName']) {
                        $output_reviews .= '<span class="reviewCustomer">- ' . $reviewsComments['GuestName'] . '</span>';
                    }
                    if ($reviewsComments['ReviewDate']) {
                        $output_reviews .= '<span class="reviewDate">' . date('d/m/Y', strtotime($reviewsComments['ReviewDate'])) . '</span>';
                    }
                    $output_reviews .= '</div>';
                    $output_reviews .= '</div>';
                    $output_reviews .= '</div>';
                }
                $output_reviews .= '<div class="swiper-slide"><div class="customerReviews"><div><span class="reviewTitle">Bueno</span><span class="star-ratings30 reviewRating" aria-label="Rating of this review out of 5"></span><span class="reviewComment">&quot;muy bonito todo&quot;</span><span class="reviewCustomer">- Travelopo</span><span class="reviewDate">25/09/2013</span></div></div></div>';
                $output_reviews .= '<div class="swiper-slide special-slide"><div class="customerReviews"><div><span class="reviewTitle">Bueno</span><span class="star-ratings30 reviewRating" aria-label="Rating of this review out of 5"></span><span class="reviewComment">&quot;muy bonito todo&quot;</span><span class="reviewCustomer">- Travelopo</span><span class="reviewDate">25/09/2013</span></div></div></div>';
                $output_reviews .= '<div class="swiper-slide"><div class="customerReviews"><div><span class="reviewTitle">Bueno</span><span class="star-ratings30 reviewRating" aria-label="Rating of this review out of 5"></span><span class="reviewComment">&quot;muy bonito todo&quot;</span><span class="reviewCustomer">- Travelopo</span><span class="reviewDate">25/09/2013</span></div></div></div>';
                $output_reviews .= '<div class="swiper-slide"><div class="customerReviews"><div><span class="reviewTitle">Bueno</span><span class="star-ratings30 reviewRating" aria-label="Rating of this review out of 5"></span><span class="reviewComment">&quot;muy bonito todo&quot;</span><span class="reviewCustomer">- Travelopo</span><span class="reviewDate">25/09/2013</span></div></div></div>';
                $output_reviews .= '</div>';
                //$output_reviews .= '<div class="customerReviewsSwiper-pagination"></div>';
                $output_reviews .= '<div class="swiper-button-next"></div>';
                $output_reviews .= '</div>';
                // End Swiper
                $output_reviews .= '</div>';
            }
            $output_reviews .= '</div>';
            // End #mainReviews
            $output_reviews .= '</div>';
            $output_reviews .= '</div>';
            $output_reviews .= '</div>';
            echo $output_reviews;

            /*
            // Start #container_reviews
            $output_reviews .= '<div id="container_reviews">';
            $output_reviews .= '<div id="review_general">';
            $output_reviews .= '<div class="table_vertical">';
            $output_reviews .= '<div class="grades_reviews">';
            $output_reviews .= '<span class="titDA">All reviews have been checked and authenticated. Once you have received a "rate your stay" email based on your confirmed booking, you can leave an accommodation review.</span>';
            $output_reviews .= '</div>';
            $output_reviews .= '</div>';
            // Start .table_vertical list_reviews
            $output_reviews .= '<div class="table_vertical list_reviews">';
            $output_reviews .= '<ul>';
            $output_reviews .= '<div class="container_rating">';
            $output_reviews .= '<li style="" class="points_awarded"><i class="fas fa-star icon icon-star-filled"></i><span>5<span class="maxAssessment">/5</span>Service</span></li>';
            $output_reviews .= '<li style="" class="points_awarded"><i class="fas fa-star icon icon-star-filled"></i><span>5<span class="maxAssessment">/5</span>Cleanliness</span></li>';
            $output_reviews .= '<li style="" class="points_awarded"><i class="fas fa-star icon icon-star-filled"></i><span>5<span class="maxAssessment">/5</span>Accommodation</span></li>';
            $output_reviews .= '<li style="" class="points_awarded"><i class="fas fa-star icon icon-star-filled"></i><span>5<span class="maxAssessment">/5</span>Location</span></li>';
            $output_reviews .= '<li style="" class="points_awarded"><i class="fas fa-star icon icon-star-filled"></i><span>5<span class="maxAssessment">/5</span>Value for money</span></li>';
            $output_reviews .= '</div>';
            $output_reviews .= '</ul>';
            $output_reviews .= '</div>';
            // End .table_vertical list_reviews
            $output_reviews .= '</div>';
            // End #container_reviews
            // Start #list_total_reviews
            $output_reviews .= '<div id="list_total_reviews">';
            // Start #list_val_pag
            $output_reviews .= '<div id="list_val_pag">';
            $output_reviews .= '<div class="fill_values" data-language="EN" data-enable="true">';
            $output_reviews .= '<table align="left" border="0" width="100%">';
            $output_reviews .= '<tr>';
            $output_reviews .= '<td width="100%" colspan="2">';
            $output_reviews .= '<div class="subheading_Assessment">';
            $output_reviews .= '<div>';
            $output_reviews .= '<div class="formatValues">Kizzie holiday cottage</div>';
            $output_reviews .= '<div style="cursor:help;" title="Review: 10 out of 10" class="container_rating title_valRating">';
            $output_reviews .= '<div class="container_symbols reviewsCircles">';
            $output_reviews .= '<div class="stars"><i class="fas fa-star icon icon-star-filled"></i></div>';
            $output_reviews .= '<div class="stars"><i class="fas fa-star icon icon-star-filled"></i></div>';
            $output_reviews .= '<div class="stars"><i class="fas fa-star icon icon-star-filled"></i></div>';
            $output_reviews .= '<div class="stars"><i class="fas fa-star icon icon-star-filled"></i></div>';
            $output_reviews .= '<div class="stars"><i class="fas fa-star icon icon-star-filled"></i></div>';
            $output_reviews .= '</div>';
            $output_reviews .= '</div>';
            $output_reviews .= '</div>';
            $output_reviews .= '<div class="titleF autorVal"><b>Robert&nbsp;(Ireland)</b> &nbsp;&nbsp;</div>';
            $output_reviews .= '</div>';
            $output_reviews .= '</td>';
            $output_reviews .= '</tr>';
            $output_reviews .= '<tr class="titleF">';
            $output_reviews .= '<td valign="top"  width="2%" height="15" align="right"><span class="fas fa-smile icon icon-cara_feliz"></span></td>';
            $output_reviews .= '<td valign="top" width="98%">';
            $output_reviews .= '<div class="text_reviewpost_1 text_valor">';
            $output_reviews .= 'I was delighted with the cottage, it was exactly what I hoped it would be like. It is nicely furnished and clean and tidy. It makes an ideal base for day-trips to visit the beauty spots and places of interest in Kerry and West Cork.I thoroughly enjoyed my week staying there and hope to visit again in the near future. Thanks to Trident Holidays and Joanne(host). VERY HAPPY…';
            $output_reviews .= '</div>';
            $output_reviews .= '</td>';
            $output_reviews .= '</tr>';
            $output_reviews .= '<tr class="titleF">';
            $output_reviews .= '<td valign="top" width="2%" height="15" align="right"><span class="fas fa-frown icon icon-cara_triste"></span></td>';
            $output_reviews .= '<td valign="top" width="98%">';
            $output_reviews .= '<div class="text_negativo_1 text_valor">';
            $output_reviews .= 'I can’t think of anything that needs to be improved. I was very happy with everything.';
            $output_reviews .= '</div>';
            $output_reviews .= '</td>';
            $output_reviews .= '</tr>';
            $output_reviews .= '<tr class="titleF">';
            $output_reviews .= '<td height="35" align="center" colspan="2">';
            $output_reviews .= '<div class="divVoting">';
            $output_reviews .= '<span id="publish_comment_1248976" style="display:none"><i class="fas fa-thumbs-up icon icon-like"></i> Thank you for your review <span class="counter_ut counter_util_1248976">0</span></span>';
            $output_reviews .= '<span onclick="guardComentarioUtilRedesign(1248976,\'bk_trident\')" style="display:" class="guard_commentUtil_1248976"><i class="fas fa-thumbs-up icon icon-like"></i> Has it been useful? <span class="counter_ut counter_util_1248976">0</span></span>';
            $output_reviews .= '</div>';
            $output_reviews .= '<div class="fechaAssessment">';
            $output_reviews .= '<span>2 months</span>';
            $output_reviews .= '</div>';
            $output_reviews .= '</td>';
            $output_reviews .= '</tr>';
            $output_reviews .= '<tr class="titleF">';
            $output_reviews .= '<td valign="top"  width="2%" height="5" align="right"></td>';
            $output_reviews .= '<td valign="top" width="98%">';
            $output_reviews .= '<div class="divResponse">';
            $output_reviews .= '<div class="divIconResponse"><span title="Owner´s reply" alt="Owner´s reply"></span></div>';
            $output_reviews .= '<div class="text_response_1 text_valor">';
            $output_reviews .= 'Hi Robert<br />
                      Many thanks for your great review of Kizzie Holiday Cottage, Killorglin, Co. Kerry.<br />
                      We are delighted you enjoyed your  stay here and greatly appreciate your business and comments, we will pass them on to Joanne.<br />
                      We do hope to welcome you again to Trident Holiday Homes.';
            $output_reviews .= '</div>';
            $output_reviews .= '</div>';
            $output_reviews .= '</td>';
            $output_reviews .= '</tr>';
            $output_reviews .= '<tr>';
            $output_reviews .= '<td colspan="2"><hr></td>';
            $output_reviews .= '</tr>';
            $output_reviews .= '</table>';
            $output_reviews .= '</div>';
            $output_reviews .= '<div class="fill_values" data-language="EN" data-enable="true">';
            $output_reviews .= '<table align="left" border="0" width="100%">';
            $output_reviews .= '<tr>';
            $output_reviews .= '<td width="100%" colspan="2">';
            $output_reviews .= '<div class="subheading_Assessment">';
            $output_reviews .= '<div>';
            $output_reviews .= '<div class="formatValues" >Classy Cottage</div>';
            $output_reviews .= '<div style="cursor:help;" title="Review: 10 out of 10" class="container_rating title_valRating">';
            $output_reviews .= '<div class="container_symbols reviewsCircles">';
            $output_reviews .= '<div class="stars"><i class="fas fa-star icon icon-star-filled"></i></div>';
            $output_reviews .= '<div class="stars"><i class="fas fa-star icon icon-star-filled"></i></div>';
            $output_reviews .= '<div class="stars"><i class="fas fa-star icon icon-star-filled"></i></div>';
            $output_reviews .= '<div class="stars"><i class="fas fa-star icon icon-star-filled"></i></div>';
            $output_reviews .= '<div class="stars"><i class="fas fa-star icon icon-star-filled"></i></div>';
            $output_reviews .= '</div>';
            $output_reviews .= '</div>';
            $output_reviews .= '</div>';
            $output_reviews .= '<div class="titleF autorVal"><b>Janet&nbsp;(United Kingdom)</b> &nbsp;&nbsp;</div>';
            $output_reviews .= '</div>';
            $output_reviews .= '</td>';
            $output_reviews .= '</tr>';
            $output_reviews .= '<tr class="titleF">';
            $output_reviews .= '<td valign="top" width="2%" height="15" align="right"><span class="fas fa-smile icon icon-cara_feliz"></span></td>';
            $output_reviews .= '<td valign="top" width="98%">';
            $output_reviews .= '<div class="text_reviewpost_2 text_valor">';
            $output_reviews .= 'A well put-together cottage, beautifully finished in a peaceful location. Well-equipped kitchen. We hired bikes that were delivered to the cottage. Killorglin is about a mile away so handy to cycle there and back - although the hill out of town on the return got the heart pumping! The pub life in Killorglin is alive and kicking. Very friendly. Town is in a good position to do the drive around the Ring of Kerryhead to Dingle.';
            $output_reviews .= '</div>';
            $output_reviews .= '</td>';
            $output_reviews .= '</tr>';
            $output_reviews .= '<tr class="titleF">';
            $output_reviews .= '<td height="35" align="center" colspan="2">';
            $output_reviews .= '<div class="divVoting">';
            $output_reviews .= '<span id="publish_comment_1231534" style="display:none"><i class="fas fa-thumbs-up icon icon-like"></i> Thank you for your review <span class="counter_ut counter_util_1231534">0</span></span>';
            $output_reviews .= '<span onclick="guardComentarioUtilRedesign(1231534,\'bk_trident\')" style="display:" class="guard_commentUtil_1231534"><i class="fas fa-thumbs-up icon icon-like"></i> Has it been useful? <span class="counter_ut counter_util_1231534">0</span></span>';
            $output_reviews .= '</div>';
            $output_reviews .= '<div class="fechaAssessment">';
            $output_reviews .= '<span>3 months</span>';
            $output_reviews .= '</div>';
            $output_reviews .= '</td>';
            $output_reviews .= '</tr>';
            $output_reviews .= '<tr class="titleF">';
            $output_reviews .= '<td valign="top"  width="2%" height="5" align="right"></td>';
            $output_reviews .= '<td valign="top" width="98%">';
            $output_reviews .= '<div class="divResponse">';
            $output_reviews .= '<div class="divIconResponse"><span title="Owner´s reply" alt="Owner´s reply"></span></div>';
            $output_reviews .= '<div class="text_response_2 text_valor">';
            $output_reviews .= 'Hi Janet<br />
                        Many thanks for your great review of Kizzie Cottage, Killorglin, Co. Kerry.<br />
                        We are delighted you enjoyed your stay and appreciate your comments and business.<br />
                        We hope to welcome you again to Trident Holiday Homes.';
            $output_reviews .= '</div>';
            $output_reviews .= '</div>';
            $output_reviews .= '</td>';
            $output_reviews .= '</tr>';
            $output_reviews .= '<tr>';
            $output_reviews .= '<td colspan="2"><hr></td>';
            $output_reviews .= '</tr>';
            $output_reviews .= '</table>';
            $output_reviews .= '</div>';
            $output_reviews .= '<div class="fill_values expandable-readmore disabled" data-language="EN" data-enable="true">';
            $output_reviews .= '<table align="left" border="0" width="100%">';
            $output_reviews .= '<tr>';
            $output_reviews .= '<td width="100%" colspan="2">';
            $output_reviews .= '<div class="subheading_Assessment">';
            $output_reviews .= '<div>';
            $output_reviews .= '<div class="formatValues" >a really comfortable home</div>';
            $output_reviews .= '<div style="cursor:help;" title="Review: 10 out of 10" class="container_rating title_valRating">';
            $output_reviews .= '<div class="container_symbols reviewsCircles">';
            $output_reviews .= '<div class="stars"><i class="fas fa-star icon icon-star-filled"></i></div>';
            $output_reviews .= '<div class="stars"><i class="fas fa-star icon icon-star-filled"></i></div>';
            $output_reviews .= '<div class="stars"><i class="fas fa-star icon icon-star-filled"></i></div>';
            $output_reviews .= '<div class="stars"><i class="fas fa-star icon icon-star-filled"></i></div>';
            $output_reviews .= '<div class="stars"><i class="fas fa-star icon icon-star-filled"></i></div>';
            $output_reviews .= '</div>';
            $output_reviews .= '</div>';
            $output_reviews .= '</div>';
            $output_reviews .= '<div class="titleF autorVal"><b>Sidonie&nbsp;(Germany)</b> &nbsp;&nbsp;</div>';
            $output_reviews .= '</div>';
            $output_reviews .= '</td>';
            $output_reviews .= '</tr>';
            $output_reviews .= '<tr class="titleF">';
            $output_reviews .= '<td valign="top"  width="2%" height="15" align="right"><span class="fas fa-smile icon icon-cara_feliz"></span></td>';
            $output_reviews .= '<td valign="top" width="98%">';
            $output_reviews .= '<div class="text_reviewpost_4 text_valor">';
            $output_reviews .= 'Kizzy cottage is a comfortable accomodation to settle in after an active day in the beautiful countryside of Kerry.  The furniture is modern but relates to the farming flavour of an irish cottage.  While sitting on the garden bench and having a cup of tea you can see the mountains of Macgillycuddy Reeks.';
            $output_reviews .= '</div>';
            $output_reviews .= '</td>';
            $output_reviews .= '</tr>';
            $output_reviews .= '<tr class="titleF">';
            $output_reviews .= '<td height="35" align="center" colspan="2">';
            $output_reviews .= '<div class="divVoting">';
            $output_reviews .= '<span id="publish_comment_994226" style="display:none"><i class="fas fa-thumbs-up icon icon-like"></i> Thank you for your review <span class="counter_ut counter_util_994226">0</span></span>';
            $output_reviews .= '<span onclick="guardComentarioUtilRedesign(994226,\'bk_trident\')" style="display:" class="guard_commentUtil_994226"><i class="fas fa-thumbs-up icon icon-like"></i> Has it been useful? <span class="counter_ut counter_util_994226">0</span></span>';
            $output_reviews .= '</div>';
            $output_reviews .= '<div class="fechaAssessment">';
            $output_reviews .= '<span>11 months</span>';
            $output_reviews .= '</div>';
            $output_reviews .= '</td>';
            $output_reviews .= '</tr>';
            $output_reviews .= '<tr class="titleF">';
            $output_reviews .= '<td valign="top"  width="2%" height="5" align="right"></td>';
            $output_reviews .= '<td valign="top" width="98%">';
            $output_reviews .= '<div class="divResponse">';
            $output_reviews .= '<div class="divIconResponse"><span title="Owner´s reply" alt="Owner´s reply"></span></div>';
            $output_reviews .= '<div class="text_response_4 text_valor">';
            $output_reviews .= 'Hi Sidonie<br />
                                    Many thanks for your great review of Kizzie Cottage, Killorglin, Co. Kerry.<br />
                                    We are delighted you enjoyed your stay and appreciate your time, comments and business.<br />
                                    We hope to welcome you again to trident Holiday Homes.';
            $output_reviews .= '</div>';
            $output_reviews .= '</div>';
            $output_reviews .= '</td>';
            $output_reviews .= '</tr>';
            $output_reviews .= '<tr>';
            $output_reviews .= '<td colspan="2"><hr></td>';
            $output_reviews .= '</tr>';
            $output_reviews .= '</table>';
            $output_reviews .= '</div>';
            $output_reviews .= '</div>';
            // End #list_val_pag
            $output_reviews .= '<div class="clear"></div>';
            $output_reviews .= '<div class="showExtraReviews">';
            $output_reviews .= '<div class="moreReviews">Show more</div>';
            $output_reviews .= '<div class="lessReviews disabled">Show less</div>';
            $output_reviews .= '</div>';
            $output_reviews .= '</div>';
            // End #list_total_reviews
            $output_reviews .= '</div>';
            // End .sectionReviews
            $output_reviews .= '</div>';
            $output_reviews .= '</div>';
            $output_reviews .= '</div>';
            echo $output_reviews;
            /*
            
            /******* SECURITY DEPOSITS *******/
            $output_securitydeposit = '';
            // Loop through obligatory or included services
            if (!empty($descriptionsData['Extras']['ObligatoryOrIncluded'])) {
                foreach ($descriptionsData['Extras']['ObligatoryOrIncluded'] as $extraDescription) {
                    if ($extraDescription['Name'] === 'Security Deposit (refundable)') {
                        $output_securitydeposit .= '<button class="accordion-accommodation-item">' . $extraDescription['Name'] . '</button>';
                        $output_securitydeposit .= '<div class="accordion-accommodation-content">';
                        $output_securitydeposit .= '<div id="propertyInfo" class="box-left">';
                        // Start #mainSecurityDeposit
                        $output_securitydeposit .= '<div id="mainSecurityDeposit">';
                        $output_securitydeposit .= '<div class="features">';
                        $output_securitydeposit .= '<div class="feature"><div> <span class="features-item-check"></span> <span class="feature-text"><b>Amount:</b> ' . $extraDescription['Description'] . '</span></div></div>';
                        $output_securitydeposit .= '<div class="feature"><div> <span class="features-item-check"></span> <span class="feature-text"><b>Payment method:</b> Cash</span></div></div>';
                        $output_securitydeposit .= '<div class="feature"><div> <span class="features-item-check"></span> <span class="feature-text">To be paid on site</span></div></div>';
                        $output_securitydeposit .= '</div>';
                        $output_securitydeposit .= '</div>';
                        // End #mainSecurityDeposit
                        $output_securitydeposit .= '</div>';
                        $output_securitydeposit .= '</div>';
                    }
                }
            }
            echo $output_securitydeposit;

            /******* IMPORTANT INFORMATION / BOOKING CONDITIONS *******/
            $output_bookingrules = '<button class="accordion-accommodation-item">Important Information / Booking Conditions</button>';
            $output_bookingrules .= '<div class="accordion-accommodation-content">';
            $output_bookingrules .= '<div id="propertyInfo" class="box-left">';
            $output_bookingrules .= '<div style="color:red;font-size:18px">** Need to check the live data and find where there booking conditions are in the file. It is currently hardcoded **</div>';
            // Start #mainBookingConditions
            $output_bookingrules .= '<div id="mainBookingConditions">';
            $output_bookingrules .= '<span>From the booking date until 29 days before check-in, cancellation would have a penalty of 50% of the total price of the rent.</span>';
            $output_bookingrules .= '<h3 class="subtitle-section">Cancellation policies</h3>';
            $output_bookingrules .= '<span>In case of cancellation the following charges will apply</span>';
            $output_bookingrules .= '<div class="features">';
            $output_bookingrules .= '<div class="feature"><div> <span class="features-item-check"></span> <span class="feature-text">From the booking date until 29 days before check-in - 50% of the total rent</span></div></div>';
            $output_bookingrules .= '<div class="feature"><div> <span class="features-item-check"></span> <span class="feature-text">From 28 days before check-in until 15 days before check-in - 100% of the total rent</span></div></div>';
            $output_bookingrules .= '<div class="feature"><div> <span class="features-item-check"></span> <span class="feature-text">From 14 days before check-in until 9 days before check-in - 100% of the total prepayment amount</span></div></div>';
            $output_bookingrules .= '<div class="feature"><div> <span class="features-item-check"></span> <span class="feature-text">From 8 days before, until the check-in - 100% of the total prepayment amount</span></div></div>';
            $output_bookingrules .= '<div class="feature"><div> <span class="features-item-check"></span> <span class="feature-text">No-show - 100% of the total rent</span></div></div>';
            $output_bookingrules .= '</div>';
            $output_bookingrules .= '<h3 class="subtitle-section">Additional notes</h3>';
            $output_bookingrules .= '<div class="features">';
            $output_bookingrules .= '<div class="feature"><div> <span class="features-item-check"></span> <span class="feature-text">We kindly ask you to phone us about your arrival time once you are at your destination</span></div></div>';
            $output_bookingrules .= '<div class="feature"><div> <span class="features-item-check"></span> <span class="feature-text">The keys must be picked up during the above mentioned hours only</span></div></div>';
            $output_bookingrules .= '</div>';
            $output_bookingrules .= '</div>';
            // End #mainBookingConditions
            $output_bookingrules .= '</div>';
            $output_bookingrules .= '</div>';
            echo $output_bookingrules;

            /******* COMMENTS *******/
            $output_comments = '<button class="accordion-accommodation-item">Comments</button>';
            $output_comments .= '<div class="accordion-accommodation-content">';
            $output_comments .= '<div id="propertyInfo" class="box-left">';
            $output_comments .= '<div style="color:red;font-size:18px">** Need to check the live data and find where the comments are in the file. It is currently hardcode **</div>';
            // Start #mainComments
            $output_comments .= '<div id="mainComments">';
            $output_comments .= '<div class="features">';
            $output_comments .= '<div class="feature"><div> <span class="features-item-check"></span> <span class="feature-text">This accommodation does not accept groups of young people (Up to 25 years)</span></div></div>';
            $output_comments .= '<div class="feature"><div> <span class="features-item-check"></span> <span class="feature-text">No smoking</span></div></div>';
            $output_comments .= '<div class="feature"><div> <span class="features-item-check"></span> <span class="feature-text"> No pets allowed</span></div></div>';
            $output_comments .= '</div>';
            $output_comments .= '</div>';
            // End #mainComments
            $output_comments .= '</div>';
            $output_comments .= '</div>';
            // last accordion div and sticky
            $output_comments .= '<span class="stop-sticky"></span>';
            $output_comments .= '</div>';
            // End .accordion-accommodation
            $output_comments .= '</div>';
            // End .box-section
            echo $output_comments;
        } else {
            $output_noproperty = '<div class="box-section sticky-sidebar-container">';
            $output_noproperty .= '<div class="alert alert-danger">We are sorry that this property is not found. Please try again with another property.</div>';
            $output_noproperty .= '</div>';
            echo $output_noproperty;
        }
    }
    ?>
    <?php
    GetHTMLFeedsForAccommodation($company, $accommodationId, $accID, $dateFrom, $dateTo, $dateToAPI, $result_IsAvailable, $result_GetBookingPrice, $language, $adultsNumber, $getBookingPrice_info, $childrenNumber, $childAge);
} catch (SoapFault $e) {
    //echo 'Error: ' . $e->getMessage();
    //echo 'Error: ' . $e->getMessage() . PHP_EOL . 'Error Code: ' . $e->getCode();
    //echo "Accommodation not available.";
    wp_redirect(home_url('/rentals-search/'), 301);
    exit();
}
?>