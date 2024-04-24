<?php
try {
    // Avantio API credentials and other parameters
    $username = 'itsatentoapi_test';
    $password = 'testapixml';
    $apiKey = 'itsalojamientos';
    $secretKey = '';

    // Set the accommodation, user, and company details
    // $accommodationId = '60505';
    // $accID = '1238513302';
    //$accommodationId = '200697';
    //$accID = '1499249825';
    $accommodationId = '55705';
    $accID = '1210067611';
    /*if (null !== get_query_var('prop_id') && get_query_var('prop_id') != '') {
        $accommodationId = get_query_var('prop_id', '');
    } else {
        $accommodationId = '';
    }
    if (null !== get_query_var('acc_id') && get_query_var('acc_id') != '') {
        $accID = get_query_var('acc_id', '');
    } else {
        $accID = '';
    }*/
    $company = 'itsalojamientos';
    $partnerCode = '836efa4efbe7fa63f2ebbae30d7b965f';
    $language = 'en';
    $languageUpper = 'EN';

    // Set the parameters
    if (isset($_POST['AdultNum'])) {
        $adultsNumber = $_POST['AdultNum'];
        $getBookingPrice_info = 'yes';
    } else {
        $adultsNumber = 1;
        $getBookingPrice_info = 'no';
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

    /********** SetBooking - request data with filters **********/
    // The operation “SetBooking” makes the booking of an accommodation. It blocks the availability occupation period of that booking. It requires the data of the person who book the accommodation and other compulsory fields such as accommodation, dates, etc.
    // This service needs a valid payment method. Please contact with Avantio.
    /*$request_SetBooking = [
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
            'ArrivalDate' => $dateFrom,
            'DepartureDate' => $dateTo,
            'ClientData' => [
                'Name' => '',
                'Surname' => '',
                'DNI' => '',
                'Address' => '',
                'Locality' => '',
                'PostCode' => '',
                'City' => '',
                'Country' => '',
                'Telephone' => '',
                'Telephone2' => '',
                'EMail' => '',
                'Fax' => '',
                'Board' => '',
                'PaymentMethod' => ''
            ],
            'CreditCardData' => [
                'CardType' => '',
                'CardNumber' => '',
                'ExpiryDate' => '',
                'Cardholder' => '',
                'CCVCode' => ''
            ],
            'SecureAuthtentication' => [
                'CAVV' => '',
                'ECI' => '',
                'XID' => '',
                'ThreeDSVersion' => '',
                'DsTransID' => '',
                'ExceptionType' => ''
            ],
            'BookingType' => '',
            'Comments' => '',
            'SendMailToOrganization' => $sendMailToOrganization,
            'SendMailToTourist' => $sendMailToTourist,
            'Services' => [
                'Service' => [
                    'Code' => '',
                    'Amount' => '',
                    'PromotionalCode' => ''
                ]
            ]
        ]
    ];
    $result_SetBooking = $client->SetBooking($request_SetBooking);
    var_dump($result_SetBooking);*/

    /********** GetBooking - request data with filters **********/
    // A booking has two parameters with which it can be localized. In order to get a booking, it is necessary to fulfill only one of the BookingCode or the Localizator parameters.
    /*$request_GetBooking = [
        'Credentials' => [
            'Language' => 'EN',
            'UserName' => $username,
            'Password' => $password
        ],
        'Criteria' => [
            'BookingCode' => $bookingCode,
            'Localizator' => $localizator
        ]
    ];*/
    //$result_GetBooking = $client->GetBooking($request_GetBooking);
    //var_dump($result_GetBooking);

    /********** CancelBooking - request data with filters **********/
    // A booking has to be referred by one of the next two parameters. In order to cancel a booking, either the Bookingcode or the Localizator of the booking have to be fulfilled.
    /*$request_CancelBooking = [
        'Credentials' => [
            'Language' => 'EN',
            'UserName' => $username,
            'Password' => $password
        ],
        'Criteria' => [
            'BookingCode' => $bookingCode,
            'Localizator' => $localizator,
            'SendMailToOrganization' => $sendMailToOrganization,
            'SendMailToTourist' => $sendMailToTourist
        ]
    ];*/
    //$result_CancelBooking = $client->CancelBooking($request_CancelBooking);
    //var_dump($result_CancelBooking);

    /********** GetBookingList - request data with filters (statistical purposes) **********/
    // This service returns the list of bookings.
    // Note: This service should only be used for statistical purposes.
    /*$request_GetBookingList = [
        'Credentials' => [
            'Language' => 'EN',
            'UserName' => $username,
            'Password' => $password
        ],
        'Criteria' => [
            'StartDate' => $startDate,
            'EndDate' => $endDate,
            'Filter' => 'CREATION_DATE', // CREATION_DATE or STAY_DATES
            'LastUpdatedDate' => $lastUpdatedDate
        ]
    ];*/
    //$result_GetBookingList = $client->GetBookingList($request_GetBookingList);
    //var_dump($result_GetBookingList);

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
    function DescriptionFeeds($accommodationId, $language) {
        $plugin_dir = plugin_dir_path(__FILE__);
        $descriptionsFile = $plugin_dir.'feeds/Descriptions.xml';
        // Check if the file exists
        if (file_exists($descriptionsFile) && $accommodationId) {
            // Get the contents of the file
            $descriptionsOutput = file_get_contents($descriptionsFile);
            // If there's an output
            if ($descriptionsOutput !== false) {
                // Try to load the XML from the string
                $descriptionsXml = simplexml_load_string($descriptionsOutput);
                if ($descriptionsXml !== false) {
                    $descriptionsPicsFound = false;
                    foreach ($descriptionsXml->Accommodation as $descs) {
                        if ($descs->AccommodationId == $accommodationId) {
                            foreach ($descs->InternationalizedItem as $InternationalizedItem) {
                                if ($InternationalizedItem->Language == $language) {
                                    return $InternationalizedItem->BookingURL;
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    echo '<br><h1><i>-------- Search Filters (start) -------</i></h1>';
    echo '<b style="color:blue">** Currently defaults to one property at the moment **</b>' . '<br><br>';
    echo '<b style="color:blue">Date inputs to be in the format of yyyy-mm-dd</b>' . '<br><br>';
    ?>
    <div id="formulario_resultados">
        <?php $currentURL_Form = esc_url($_SERVER['REQUEST_URI']); ?>
        <form name="formBusquedaAlquileres" style="background: none repeat scroll 0 0 #fff;border: 1px solid #bbb;padding: 30px;width: 99.5%;box-sizing: border-box;" id="formBusquedaAlquileres" method="POST" action="<?php echo $currentURL_Form; ?>">
            <fieldset id="miniform_online">
                <div id="form_minRespo">
                    <div class="dates">
                        <label for="travel-period">Check In/Out Dates</label>
                        <span class="custom-input">
                            <input type="text" name="daterange" value="<?php if (isset($dateFrom)) { echo date('d/m/Y', strtotime($dateFrom)); } ?> - <?php if (isset($dateTo)) { echo date('d/m/Y', strtotime($dateTo)); } ?>" />
                            <input type="hidden" name="dateFrom" id="dateFrom" value="<?php if (isset($_POST['dateFrom'])) { echo $_POST['dateFrom']; } ?>" />
                            <input type="hidden" name="dateTo" id="dateTo" value="<?php if (isset($_POST['dateTo'])) { echo $_POST['dateTo']; } ?>" />
                        </span>
                    </div>
                    <div class="adult people">
                        <label for="Adults" class="label-title">Adults:</label>
                        <span class="select_online">
                            <div class="personas_select" >
                                <span class="custom-input">
                                    <select name="AdultNum" id="AdultNum" class="select">
                                        <?php for ($i = 1; $i <= 20; $i++) : ?>
                                        <option value="<?php echo $i; ?>"<?php if (isset($_POST['AdultNum']) && $_POST['AdultNum'] === (string)$i) { echo ' selected="selected"'; } ?>><?php echo $i; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </span>
                            </div>
                        </span>
                    </div>
                    <div class="childs people">
                        <label for="Children" class="label-title">Children:</label>
                        <span class="select_online">
                            <div class="personas_select" >
                                <span class="custom-input">
                                    <select name="ChildrenNum" id="ChildrenNum" class="select">
                                        <option value="">- -</option>
                                        <?php for ($i = 1; $i <= 6; $i++) : ?>
                                        <option value="<?php echo $i; ?>"<?php if (isset($_POST['ChildrenNum']) && $_POST['ChildrenNum'] === (string)$i) { echo ' selected="selected"'; } ?>><?php echo $i; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </span>
                            </div>
                        </span>
                    </div>
                    <?php
                    $childAges = array();
                    for ($i = 1; $i <= 6; $i++) {
                        $key = 'Child_' . $i . '_Age';
                        $childAge = isset($_POST[$key]) ? $_POST[$key] : '';
                        $childAges[$key] = $childAge;
                    }
                    $childrenNum = isset($_POST['ChildrenNum']) ? (int)$_POST['ChildrenNum'] : 0;
                    for ($i = 1; $i <= 6; $i++) :
                    ?>
                    <div class="child<?php echo $i; ?> people"<?php if ($i > $childrenNum) { echo ' style="display: none;"'; } ?>>
                        <label for="counterNinyos" class="label-title">Child <?php echo $i; ?> Age:</label>
                        <span class="select_online">
                            <div class="personas_select">
                                <span class="custom-input">
                                    <select id="Child_<?php echo $i; ?>_Age" class="select" name="Child_<?php echo $i; ?>_Age">
                                        <option value="">- -</option>
                                        <?php for ($j = 0; $j <= 6; $j++) : ?>
                                            <option value="<?php echo $j; ?>"<?php if ($childAges['Child_' . $i . '_Age'] === (string)$j) { echo ' selected="selected"'; } ?>><?php echo $j === 0 ? '0 years' : $j . ' year' . ($j > 1 ? 's' : ''); ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </span>
                            </div>
                        </span>
                    </div>
                    <?php endfor; ?>
                    <div class="alerts">
                        <div class="alert-box">
                            <i class='icon-info-circled'></i>
                            <span></span>
                        </div>
                    </div>
                    <div id="bt_act" class="botonR_fondo"><br>
                        <button class="button-book-search" type="input" name="submit" id="submit">Search</button>
                    </div>
                </div>
            </fieldset>
        </form>
        <?php
        if ($result_IsAvailable->Available->AvailableCode) {
        ?>
            <?php
            if ($getBookingPrice_info === 'yes') {
                if ($result_GetBookingPrice->BookingPrice && $result_IsAvailable->Available->AvailableCode == 1) {
                    $priceConvert_BP = str_replace('EUR', '&euro;', $result_GetBookingPrice->BookingPrice->Currency);
                    $peopleText = ((int)$adultsNumber === 1) ? $adultsNumber . ' Person' : (int)$adultsNumber . ' People';
                    $bookingStartDateAPI = strtotime($dateFrom);
                    $bookingEndDateAPI = strtotime($dateTo);
                    $bookingMinimumNightsAPI = ceil(($bookingEndDateAPI - $bookingStartDateAPI) / (60 * 60 * 24));
                    $bookingNumNightsText = ((int)$bookingMinimumNightsAPI === 1) ? (int)$bookingMinimumNightsAPI . ' Night' : (int)$bookingMinimumNightsAPI . ' Nights';
                    echo '<div style="margin-top:30px; background: #ffffff;border: 1px solid #bbb;">';
                    echo '<div style="display: table-header-group;text-align: center;padding: 20px;width: 90%;font-size: 32px;width: 100%;display: block;margin-bottom: 10px;font-weight: 700;">';
                    echo '<label style="font-size: 23px;font-weight: 300;margin-left: 0;width: 100%;margin: 0 auto;margin-left: auto;display: table;">Price</label>';
                    echo $priceConvert_BP . $result_GetBookingPrice->BookingPrice->RoomOnlyFinal;
                    echo '<span style="font-size: 21px !important;">.00</span>';
                    echo '<span style="font-size: 23px !important;font-weight: 300;">/night</span>';
                    echo '</div>';
                    echo '<div style="text-align: center;padding: 20px;width: 90%;font-size: 32px;width: 100%;display: block;margin-bottom: 10px;font-weight: 700;">';
                    echo '<span style="border-top: 1px solid #aaa;float: left;font-size: 17px;margin: 0 auto;padding-top: 14px;width: 100%;font-weight: 700;"><label style="text-transform: capitalize;font-weight: 400;margin: 0 auto;display: table;font-size: 13px !important;">for</label>' . $bookingNumNightsText .'<label style="text-transform: capitalize;font-weight: 400;margin: 0 auto;display: table;font-size: 13px !important;">for <span style="text-transform: none;font-size: 17px !important;font-weight: 700;">' . $peopleText . '</span></label></span>';
                    /*echo '<form name="formBooking" id="formBooking" method="POST" action="/booking/">';
                    echo '<input type="hidden" name="accommID" id="accommID" value="' . $accommodationId . '">';
                    echo '<input type="hidden" name="accID" id="accID" value="' . $accID . '">';
                    echo '<input type="hidden" name="arrivalDate" id="arrivalDate" value="' . $dateFrom . '">';
                    echo '<input type="hidden" name="departureDate" id="departureDate" value="' . $dateTo . '">';
                    echo '<input type="hidden" name="bookingPrice" id="bookingPrice" value="' . $result_GetBookingPrice->BookingPrice->RoomOnlyFinal . '">';
                    echo '<input type="hidden" name="minNights" id="minNights" value="' . (int)$bookingMinimumNightsAPI . '">';
                    echo '<input type="hidden" name="adultsNumb" id="adultsNumb" value="' . (int)$adultsNumber . '">';
                    $childrenNum = isset($_POST['ChildrenNum']) ? (int)$_POST['ChildrenNum'] : 0;
                    echo '<input type="hidden" name="ChildrenNumber" id="ChildrenNumber" value="' . (int)$childrenNumber . '">';
                    echo '<input type="hidden" name="child1Aged" id="child1Aged" value="' . (int)$child1Age . '">';
                    echo '<input type="hidden" name="child2Aged" id="child2Aged" value="' . (int)$child2Age . '">';
                    echo '<input type="hidden" name="child3Aged" id="child3Aged" value="' . (int)$child3Age . '">';
                    echo '<input type="hidden" name="child4Aged" id="child4Aged" value="' . (int)$child4Age . '">';
                    echo '<input type="hidden" name="child5Aged" id="child5Aged" value="' . (int)$child5Age . '">';
                    echo '<input type="hidden" name="child6Aged" id="child6Aged" value="' . (int)$child6Age . '">';
                    echo '<button class="button-book" type="input" name="submitBooking" id="submitBooking">Book</button>';
                    echo '</form>';*/
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
                    echo '<form name="formReservaPropiedad" id="formReservaPropiedad" method="POST" action="' . DescriptionFeeds($accommodationId, $language) . $booking_url_queries . '">';
                    echo '<button class="button-book" type="input" name="submitBooking" id="submitBooking">Book</button>';
                    echo '</form>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                if ($result_GetBookingPrice->BookingPrice) {
                    $priceConvert_BP = str_replace('EUR', '&euro;', $result_GetBookingPrice->BookingPrice->Currency);
                    echo '<div style="margin-top:30px; background: #ffffff;border: 1px solid #bbb;">';
                    echo '<div style="display: table-header-group;text-align: center;padding: 20px;width: 90%;font-size: 32px;width: 100%;display: block;margin-bottom: 10px;font-weight: 700;">';
                    echo '<label style="font-size: 23px;font-weight: 300;margin-left: 0;width: 100%;margin: 0 auto;margin-left: auto;display: table;">From</label>';
                    echo $priceConvert_BP . $result_GetBookingPrice->BookingPrice->RoomOnly;
                    echo '<span style="font-size: 21px !important;">.00</span>';
                    echo '<span style="font-size: 23px !important;font-weight: 300;">/week</span>';
                    echo '</div>';
                    echo '</div>';
                }
            }
            ?>
        </div>
    <?php } ?>
    </div>
    <?php
    AvailabilityFeeds($accommodationId);
    ?>
    <script>
   
    <?php
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
    ?>
     var getFirstDate = '<?php echo $getFirstDate; ?>';
     var getLastDate = '<?php echo $getLastDate; ?>';
    </script>
    <script  defer type="text/javascript" src="/wp-content/plugins/avantio-api-integration/js/api-response.js"></script>
    <?php
    if (!$result_IsAvailable->Available->AvailableCode && $getBookingPrice_info === 'yes') {
        echo '<h2 style="color:red">Dates do not match for this property. Please try again.</h2>';
    } else if ($result_IsAvailable->Available->AvailableCode === 0 && $getBookingPrice_info === 'yes') {
        echo '<h2 style="color:red">The accommodation is not available.</h2>';
    } else if ($result_IsAvailable->Available->AvailableCode === -5 && $getBookingPrice_info === 'yes') {
        echo '<h2 style="color:red">This property requires a ' . $result_IsAvailable->OccupationalRule->MinimumNights . ' night minimum stay.</h2>';
    } else if ($result_IsAvailable->Available->AvailableCode === -7 && $getBookingPrice_info === 'yes') {
        echo '<h2 style="color:red">The number of stay exceeds the maximum permitted.</h2>';
    } else if (($result_IsAvailable->Available->AvailableCode === -8 || $result_IsAvailable->Available->AvailableCode === -9) && $getBookingPrice_convert === 'yes') {
        echo '<h2 style="color:red">The accommodation is no longer available.</h2>';
    } else if ($result_IsAvailable->Available->AvailableCode === -99 && $getBookingPrice_info === 'yes') {
        echo '<h2 style="color:red">The number of occupants exceeds the maximum permitted.</h2>';
    } else if ($result_IsAvailable->Available->AvailableCode != 1 && $getBookingPrice_info === 'yes') {
        echo '<h2 style="color:red">' . $result_IsAvailable->Available->AvailableMessage . '</h2>';
    }
    // Response code -3 from the Availabilities.xml file with message like
    // "The booking during this period is only possible with an arrival on Saturday (for example: 28/10/2023)"
    // Response code -4 from the Availabilities.xml file with message like
    // "The booking during this period is only possible with a departure on Saturday (for example: 28/10/2023)"
    /*echo '<br><b style="color:blue">** Form Fields Results **</b>' . '<br><br>';
    echo 'Check In / Date From (TEST): ' . $dateFrom . '<br>';
    echo 'Date To (TEST): ' . $dateToAPI . '<br>';
    echo 'Check Out (TEST): ' . $dateTo . '<br>';
    echo 'Adults (TEST): ' . $adultsNumber . '<br>';
    $AdultNum_POST = isset($_POST['AdultNum']) ? (int)$_POST['AdultNum'] : 'no POST made';
    echo 'Adults (TEST POST): ' . $AdultNum_POST . '<br>';
    echo 'Child_1_Age (TEST POST): ' . $child1Age . '<br>';
    echo 'Child_2_Age (TEST POST): ' . $child2Age . '<br>';
    echo 'Child_3_Age (TEST POST): ' . $child3Age . '<br>';
    echo 'Child_4_Age (TEST POST): ' . $child4Age . '<br>';
    echo 'Child_5_Age (TEST POST): ' . $child5Age . '<br>';
    echo 'Child_6_Age (TEST POST): ' . $child6Age . '<br><br>';
    echo '<br><h1><i>-------- Search Filters (end) -------</i></h1>';*/
    ?>  
    <?php
    function AccommodationsFeeds($company, $accommodationId, $accID, $dateFrom, $dateTo, $dateToAPI, $result_IsAvailable, $result_GetBookingPrice, $language) {
        if ($result_IsAvailable->Available) {
            if ($result_IsAvailable->Available->AvailableCode) {
                echo '<br><h1><i>-------- IsAvailable - API (start) -------</i></h1>';
                echo '<b style="color:blue">The Operation IsAvailable informs whether or not an accommodation is available for certain dates and number of people.</b>' . '<br><br>';
                echo 'Availability Code: ' . $result_IsAvailable->Available->AvailableCode . '<br>';
                echo 'Availability Message: ' . $result_IsAvailable->Available->AvailableMessage . '<br>';
                echo 'Minimum Nights: ' . $result_IsAvailable->OccupationalRule->MinimumNights . '<br>';
                echo 'Minimum Nights Online: ' . $result_IsAvailable->OccupationalRule->MinimumNightsOnline . '<br>';
                echo 'Maximum Nights: ' . $result_IsAvailable->OccupationalRule->MaximumNights . '<br>';
                echo 'Start Date: ' . $result_IsAvailable->OccupationalRule->StartDate . '<br>';
                echo 'End Date: ' . $result_IsAvailable->OccupationalRule->EndDate . '<br>';
                $inputStartDateAPI = strtotime($dateFrom);
                $inputEndDateAPI = strtotime($dateToAPI);
                $orgEndDateAPI = strtotime($dateTo);
                $minimumNightsAPI = ceil(($inputEndDateAPI - $inputStartDateAPI) / (60 * 60 * 24));
                $minimumNightsAPIORG = ceil(($orgEndDateAPI - $inputStartDateAPI) / (60 * 60 * 24));
                echo 'Minimum Nights sent to DateTo: ' . $minimumNightsAPI . '<br></span>';
                //echo 'Week Day: ' . $result_IsAvailable->OccupationalRule->CheckInDays->Weekday . '<br>';
                //echo 'Month Day: ' . $result_IsAvailable->OccupationalRule->CheckInDays->Monthday . '<br>';
                //echo 'Check Out Days: ' . $result_IsAvailable->OccupationalRule->CheckOutDays->Weekday . '<br>';
                //echo 'Check In Days: ' . $result_IsAvailable->Available->CheckInDays->Weekday . '<br></span>';
                echo '<br><h1><i>-------- IsAvailable - API (end) -------</i></h1>';
            }
        }
        //echo $result_GetBookingPrice->BookingPrice;
        if ($result_GetBookingPrice->BookingPrice) {
            $priceConvert_BP = str_replace('EUR', '&euro;', $result_GetBookingPrice->BookingPrice->Currency);
            echo '<br><h1><i>-------- GetBookingPrice - API (start) -------</i></h1>';
            echo '<b style="color:blue">The operation GetBookingPrice returns the different prices that would cost a booking for the selected dates and number of people. This operation considers the discounts and supplements applied to the accommodation.<br><br>
                CAUTION: the parameters “WithoutOffer” do not consider discounts and supplements.</b>' . '<br><br>';
            echo 'Room Only: ' . $priceConvert_BP . $result_GetBookingPrice->BookingPrice->RoomOnly . '<br>';
            echo 'Room Only Final: ' . $priceConvert_BP . $result_GetBookingPrice->BookingPrice->RoomOnlyFinal . '<br>';
            echo 'Room Only Without Offer: ' . $priceConvert_BP . $result_GetBookingPrice->BookingPrice->RoomOnlyWithoutOffer . '<br>';
            echo 'Room Only Final Without Offer: ' . $priceConvert_BP . $result_GetBookingPrice->BookingPrice->RoomOnlyFinalWithoutOffer . '<br>';
            echo 'Room Only Payment When Booking: ' . $priceConvert_BP . $result_GetBookingPrice->BookingPrice->RoomOnlyPaymentWhenBooking . '<br>';
            if (isset($result_GetBookingPrice->BookingPrice->RoomOnlyFinalPaymentDetails->PaymentDetail)) {
                foreach ($result_GetBookingPrice->BookingPrice->RoomOnlyFinalPaymentDetails->PaymentDetail as $detail) {
                    echo 'Payment Number: ' . $detail->PaymentNumber . '<br>';
                    echo 'Payment Time: ' . $detail->PaymentTime . '<br>';
                    echo 'Amount: ' . $priceConvert_BP . $detail->Amount . '<br>';
                    echo 'Payment Date: ' . $detail->PaymentDate . '<br>';
                    if (isset($detail->PaymentMethods->PaymentMethod)) {
                        foreach ($detail->PaymentMethods->PaymentMethod as $method) {
                            echo 'Payment Method: ' . $method . '<br>';
                        }
                    }
                }
            }
            echo 'Currency: ' . $result_GetBookingPrice->BookingPrice->Currency . '<br>';
            echo 'Applied Tax Percentage: ' . $result_GetBookingPrice->BookingPrice->AppliedTaxPercentage . '<br>';
            //echo 'Promotional Code Status: ' . $result_GetBookingPrice->BookingPrice->PromotionalCodeStatus . '<br>';
            echo 'Cancellation Policies: ' . $result_GetBookingPrice->CancellationPolicies->Description . '<br>';
            echo 'Terms and Conditions: ' . $result_GetBookingPrice->TermsAndConditions . '<br>';
            echo 'Tourist Tax Amount: ' . $result_GetBookingPrice->TouristTaxAmount . '<br>';
            // Display Services
            echo 'Services: <br>';
            if (isset($result_GetBookingPrice->Services->Service)) {
                foreach ($result_GetBookingPrice->Services->Service as $service) {
                    echo 'Service Code: ' . $service->Code . '<br>';
                    echo 'Amount: ' . $service->Amount . '<br>';
                    //echo 'Promotional Code Status: ' . $service->PromotionalCodeStatus . '<br>';
                    echo 'Price: ' . $priceConvert_BP . $service->Price . '<br>';
                    echo 'Applied Tax Percentage: ' . $service->AppliedTaxPercentage . '<br>';
                    echo '<br>';
                }
            }
            echo '<br><h1><i>-------- GetBookingPrice - API (end) -------</i></h1>';
        }
        $plugin_dir = plugin_dir_path(__FILE__); // Get the full path to the plugin directory
        $accommodationsFile = $plugin_dir.'feeds/Accommodations.xml';
        $occupationalRulesFile = $plugin_dir.'feeds/OccupationalRules.xml';
        $priceModifiersFile = $plugin_dir.'feeds/PriceModifiers.xml';
        $servicesFile = $plugin_dir.'feeds/Services.xml';
        $availabilitiesFile = $plugin_dir.'feeds/Availabilities.xml';
        $descriptionsFile = $plugin_dir.'feeds/Descriptions.xml';
        $ratesFile = $plugin_dir.'feeds/Rates.xml';
        $kindsFile = $plugin_dir.'feeds/Kinds.xml';
        $geographicAreasFile = $plugin_dir.'feeds/GeographicAreas.xml';
        // Check if the file exists
        if (file_exists($accommodationsFile) && file_exists($priceModifiersFile) && file_exists($occupationalRulesFile) && file_exists($servicesFile) && file_exists($availabilitiesFile) && file_exists($descriptionsFile) && file_exists($ratesFile) && file_exists($kindsFile) && file_exists($geographicAreasFile)) {
            // Get the contents of the file
            $accommodationsOutput = file_get_contents($accommodationsFile);
            $priceModifiersOutput = file_get_contents($priceModifiersFile);
            $occupationalRulesOutput = file_get_contents($occupationalRulesFile);
            $servicesOutput = file_get_contents($servicesFile);
            $availabilitiesOutput = file_get_contents($availabilitiesFile);
            $descriptionsOutput = file_get_contents($descriptionsFile);
            $ratesOutput = file_get_contents($ratesFile);
            $kindsOutput = file_get_contents($kindsFile);
            $geographicAreasOutput = file_get_contents($geographicAreasFile);
            // If there's an output
            if ($accommodationsOutput !== false && $priceModifiersOutput !== false && $occupationalRulesOutput !== false && $servicesOutput !== false && $availabilitiesOutput !== false && $descriptionsOutput !== false && $ratesOutput !== false && $kindsOutput !== false && $geographicAreasOutput !== false) {
                // Try to load the XML from the string
                $accommodationsXml = simplexml_load_string($accommodationsOutput);
                $priceModifiersXml = simplexml_load_string($priceModifiersOutput);
                $occupationalRulesXml = simplexml_load_string($occupationalRulesOutput);
                $servicesXml = simplexml_load_string($servicesOutput);
                $availabilitiesXml = simplexml_load_string($availabilitiesOutput);
                $descriptionsXml = simplexml_load_string($descriptionsOutput);
                $ratesXml = simplexml_load_string($ratesOutput);
                $kindsXml = simplexml_load_string($kindsOutput);
                $geographicAreasXml = simplexml_load_string($geographicAreasOutput);
                // If the XML loaded successfully
                if ($accommodationsXml !== false) {
                    // Iterate over the properties in the XML
                    foreach ($accommodationsXml->Accommodation as $accommodation) {
                        if ($accommodation->Company == $company && $accommodation->UserId == $accID && $accommodation->AccommodationId == $accommodationId) {
                            // Checking Availability API and Occupational Rule
                            if ($occupationalRulesXml !== false) {
                                $occupationalRulesFound = false;
                                foreach ($occupationalRulesXml->OccupationalRule as $occupationalRules) {
                                    if ((int)$occupationalRules->Id === (int)$accommodation->OccupationalRuleId) {
                                        foreach ($occupationalRules->Season as $season) {
                                            $seasonStartDate = strtotime($season->StartDate);
                                            $seasonEndDate = strtotime($season->EndDate);
                                            $inputStartDate = strtotime($dateFrom);
                                            $inputEndDate = strtotime($dateTo);
                                            $orgStartDate = $dateFrom;
                                            $orgEndDate = $dateTo;
                                            $minimumNights = ceil(($inputEndDate - $inputStartDate) / (60 * 60 * 24));
                                            /***** Debug Start *****/
                                            echo 'Minimum Nights: ' . (int)$season->MinimumNights. '<br>';
                                            if ($minimumNights >= (int)$season->MinimumNights) {
                                                echo 'Minimum Nights Validated: ' . (int)$season->MinimumNights. '<br>';
                                            }
                                            echo "Start Date: " . $season->StartDate . '=' . $seasonStartDate . '<br>';
                                            echo "End Date: " . $season->EndDate . '=' . $seasonEndDate . '<br>';
                                            echo "Minimum Nights: " . $minimumNights. '<br>';
                                            echo "Input Start Date: " . $orgStartDate . '=' . $inputStartDate . '<br>';
                                            echo "Input End Date: " . $orgEndDate . '=' . $inputEndDate . '<br>';
                                            // Check if input dates fall within the season
                                            if ($inputStartDate >= $seasonStartDate && $inputEndDate <= $seasonEndDate && $minimumNights >= (int)$season->MinimumNights) {
                                                // Calculate MinimumNights based on the input dates
                                                echo '<br><h1><i>-------- Availability and Occuptional Rule (start) -------</i></h1>';
                                                echo "Start Date: " . $season->StartDate . '<br>';
                                                echo "End Date: " . $season->EndDate . '<br>';
                                                echo "Minimum Nights: " . $minimumNights . '<br>';
                                                echo '<br><h1><i>-------- Availability and Occuptional Rule (end) -------</i></h1>';
                                                $occupationalRulesFound = true;
                                             } else if ($minimumNights < (int)$season->MinimumNights) {
                                                echo '<br><h1><i>-------- Availability and Occuptional Rule (start) -------</i></h1>';
                                                echo "This property requires a " . $season->MinimumNights . " night minimum stay.";
                                                echo '<br><h1><i>-------- Availability and Occuptional Rule (end) -------</i></h1>';
                                                $occupationalRulesFound = true;
                                            } else {
                                                echo '<br><h1><i>-------- Availability and Occuptional Rule (start) -------</i></h1>';
                                                echo "Dates are not available with the occupational rules.";
                                                echo '<br><h1><i>-------- Availability and Occuptional Rule (end) -------</i></h1>';
                                            }
                                            /***** Debug End *****/
                                        }
                                    }
                                }
                                if (!$occupationalRulesFound) {
                                    echo '<br><h1><i>-------- Availability and Occuptional Rule (start) -------</i></h1>';
                                    echo "No applicable occupational rules found for the given dates.";
                                    echo '<br><h1><i>-------- Availability and Occuptional Rule (end) -------</i></h1>';
                                }
                            }
                            echo '<br><h1><i>-------- Availabilities.xml (start) -------</i></h1>';
                            echo '<b style="color:blue">The file Availabilities.xml contains the availability periods of all accommodations</b>' . '<br><br>';
                            echo '<b>-------- Availability Details -------</b>' . '<br>';
                            // Find the corresponding availabilities for this accommodation
                            if ($availabilitiesXml !== false) {
                                $availabilitiesFound = false;
                                foreach ($availabilitiesXml->AccommodationList->Accommodation as $availability) {
                                    if ($availability->AccommodationId == $accommodationId) {
                                        StringIfNotEmpty('Accommodation ID', $availability->AccommodationId);
                                        StringIfNotEmpty('Occupational Rule ID', $availability->OccupationalRuleId);
                                        foreach($availability->Availabilities->AvailabilityPeriod as $period) {
                                            StringIfNotEmpty('Period Code', $period->PeriodCode);
                                            StringIfNotEmpty('Availability Start Date', $period->StartDate);
                                            StringIfNotEmpty('Availability End Date', $period->EndDate);
                                            StringIfNotEmpty('State', $period->State);
                                        }
                                        StringIfNotEmpty('Min Days Notice', $availability->MinDaysNotice);
                                        $availabilitiesFound = true;
                                    }
                                }
                                if (!$availabilitiesFound) {
                                    echo 'Availability Start Date: n/a<br>';
                                    echo 'Availability End Date: n/a<br>';
                                    echo '----------------------' . '<br>';
                                }
                            }
                            //var_dump($accommodation);
                            echo '<br><h1><i>-------- Availabilities.xml (end) -------</i></h1>';
                            echo '<br><h1><i>-------- Accommodations.xml (start) -------</i></h1>';
                            echo '<b style="color:blue">This file contents all data about accommodations (except pictures, descriptions and Avantio system URLs. You can get this data in file Descriptions.xml).</b>' . '<br><br>';
                            echo '<b>-------- Private Property Details -------</b>' . '<br>';
                            StringIfNotEmpty('User ID', $accommodation->UserId);
                            StringIfNotEmpty('Company', $accommodation->Company);
                            StringIfNotEmpty('Company ID', $accommodation->CompanyId);
                            echo '<br>' . '<b>-------- Accommodation Details -------</b>' . '<br>';
                            StringIfNotEmpty('Accommodation Name', $accommodation->AccommodationName);
                            StringIfNotEmpty('Accommodation ID', $accommodation->AccommodationId);
                            StringIfNotEmpty('Purpose', $accommodation->Purpose);
                            StringIfNotEmpty('Type (UserKind)', $accommodation->UserKind);
                            StringIfNotEmpty('Type Code (MasterKind)', $accommodation->MasterKind->MasterKindCode);
                            StringIfNotEmpty('Type (MasterKind)', $accommodation->MasterKind->MasterKindName);
                            StringIfNotEmpty('ID Gallery', $accommodation->IdGallery);
                            StringIfNotEmpty('Occupational Rule ID', $accommodation->OccupationalRuleId);
                            StringIfNotEmpty('Price Modifier ID', $accommodation->PriceModifierId);
                            StringIfNotEmpty('Touristic Registration Number', $accommodation->TouristicRegistrationNumber);
                            StringIfNotEmpty('Accommodation Units', $accommodation->AccommodationUnits);
                            StringIfNotEmpty('Currency', $accommodation->Currency);
                            StringIfNotEmpty('VAT', $accommodation->VAT->Included);
                            StringIfNotEmpty('Region Code', $accommodation->LocalizationData->Region->RegionCode);
                            StringIfNotEmpty('Region Name', $accommodation->LocalizationData->Region->Name);
                            StringIfNotEmpty('Country Code', $accommodation->LocalizationData->Country->CountryCode);
                            StringIfNotEmpty('Country ISO Code', $accommodation->LocalizationData->Country->ISOCode);
                            StringIfNotEmpty('Country Name', $accommodation->LocalizationData->Country->Name);
                            StringIfNotEmpty('Resort Code', $accommodation->LocalizationData->Resort->ResortCode);
                            StringIfNotEmpty('Resort Name', $accommodation->LocalizationData->Resort->Name);
                            StringIfNotEmpty('City Code', $accommodation->LocalizationData->City->CityCode);
                            StringIfNotEmpty('City Name', $accommodation->LocalizationData->City->Name);
                            StringIfNotEmpty('Province Code', $accommodation->LocalizationData->Province->ProvinceCode);
                            StringIfNotEmpty('Province Name', $accommodation->LocalizationData->Province->Name);
                            StringIfNotEmpty('Locality Code', $accommodation->LocalizationData->Locality->LocalityCode);
                            StringIfNotEmpty('Locality Name', $accommodation->LocalizationData->Locality->Name);
                            StringIfNotEmpty('District Code', $accommodation->LocalizationData->District->DistrictCode);
                            StringIfNotEmpty('District Name', $accommodation->LocalizationData->District->Name);
                            StringIfNotEmpty('KindOfWay', $accommodation->LocalizationData->KindOfWay);
                            StringIfNotEmpty('Way', $accommodation->LocalizationData->Way);
                            StringIfNotEmpty('Number', $accommodation->LocalizationData->Number);
                            StringIfNotEmpty('Block', $accommodation->LocalizationData->Block);
                            StringIfNotEmpty('Door', $accommodation->LocalizationData->Door);
                            StringIfNotEmpty('Floor', $accommodation->LocalizationData->Floor);
                            // Google Cords
                            echo '<b>-------- Google Maps -------</b>' . '<br>';
                            StringIfNotEmpty('Google Maps Latitude', $accommodation->LocalizationData->GoogleMaps->Latitude);
                            StringIfNotEmpty('Google Maps Longitude', $accommodation->LocalizationData->GoogleMaps->Longitude);
                            StringIfNotEmpty('Google Maps Zoom', $accommodation->LocalizationData->GoogleMaps->Zoom);
                            echo '<b>-------- Features -------</b>' . '<br>';
                            //var_dump($accommodation->Features->Distribution);die;
                            StringIfNotEmpty('Min Capacity', $accommodation->Features->Distribution->MinimumOccupation);
                            StringIfNotEmpty('Max Capacity', $accommodation->Features->Distribution->PeopleCapacity);
                            StringIfNotEmpty('Children Allowed', $accommodation->Features->Distribution->AcceptYoungsters);
                            StringIfNotEmpty('Adults Capacity', $accommodation->Features->Distribution->AdultsCapacity);
                            StringIfNotEmpty('Without Supplement', $accommodation->Features->Distribution->OccupationWithoutSupplement);
                            StringIfNotEmpty('Bedrooms', $accommodation->Features->Distribution->Bedrooms);
                            StringIfNotEmpty('Double Beds', $accommodation->Features->Distribution->DoubleBeds);
                            StringIfNotEmpty('Single Beds', $accommodation->Features->Distribution->IndividualBeds);
                            StringIfNotEmpty('Single Sofa Bed', $accommodation->Features->Distribution->IndividualSofaBed);
                            StringIfNotEmpty('Double Sofa Bed', $accommodation->Features->Distribution->DoubleSofaBed);
                            StringIfNotEmpty('Queen Beds', $accommodation->Features->Distribution->QueenBeds);
                            StringIfNotEmpty('King Beds', $accommodation->Features->Distribution->KingBeds);
                            StringIfNotEmpty('Toilets', $accommodation->Features->Distribution->Toilets);
                            StringIfNotEmpty('Bathtub', $accommodation->Features->Distribution->BathroomWithBathtub);
                            StringIfNotEmpty('Shower', $accommodation->Features->Distribution->BathroomWithShower);
                            StringIfNotEmpty('Berths', $accommodation->Features->Distribution->Berths);
                            StringIfNotEmpty('Area Housing Unit', $accommodation->Features->Distribution->AreaHousing->AreaUnit);
                            StringIfNotEmpty('Area Plot Unit', $accommodation->Features->Distribution->AreaPlot->AreaUnit);
                            echo '<b>-------- Characteristics -------</b>' . '<br>';
                            if (isset($accommodation->Features->HouseCharacteristics)) {
                                echo '<ul>';
                            }
                            // If "TV" is true and "NumOfTelevisions" is greater than zero, echo it
                            if ($accommodation->Features->HouseCharacteristics->TV == 'true' &&
                                intval($accommodation->Features->HouseCharacteristics->NumOfTelevisions) > 0) {
                                $numTVs = intval($accommodation->Features->HouseCharacteristics->NumOfTelevisions);
                                echo "<li>TV (" . $numTVs . ")</li>";
                            }
                            foreach ($accommodation->Features->HouseCharacteristics as $keyCharacteristics => $valueCharacteristics) {
                                // Convert the value to a string
                                $valueStr = strval($valueCharacteristics);
                                // Skip if the key is "TV" or "NumOfTelevisions"
                                if ($keyCharacteristics == "TV" || $keyCharacteristics == "NumOfTelevisions") {
                                    continue;
                                }
                                // If the value is 'true' or a positive number, and the key is not "TV" or "NumOfTelevisions", echo it
                                if (($valueStr === 'true' || (is_numeric($valueStr) && $valueStr > 0)) && $keyCharacteristics != "TV" && $keyCharacteristics != "NumOfTelevisions") {
                                    // If the key is not all uppercase, convert it
                                    if (!ctype_upper($keyCharacteristics)) {
                                        $keyCharacteristics = lcfirst(preg_replace('/([A-Z])/', ' $1', $keyCharacteristics));
                                    }
                                    $convertedKC = $keyCharacteristics . ' (' . $valueCharacteristics . ")";
                                    $convertedKC = str_replace(' (true)', '', $convertedKC);
                                    echo "<li>" . $convertedKC . "</li>";
                                }
                                // If the value is a sub-node (as with the Kitchen or TVSatellite), check its sub-values
                                if (is_object($valueCharacteristics)) {
                                    foreach ($valueCharacteristics as $subKey => $subValue) {
                                        // Convert the sub-value to a string
                                        $subValueStr = strval($subValue);
                                        // If the sub-value is 'true' or a positive number, echo it
                                        if (($subValueStr === 'true' || (is_numeric($subValueStr) && $subValueStr > 0)) && $subKey != "TV" && $subKey != "NumOfTelevisions") {
                                            // If the sub-key is not all uppercase, convert it
                                            if (!ctype_upper($subKey)) {
                                                $subKey = lcfirst(preg_replace('/([A-Z])/', ' $1', $subKey));
                                            }
                                            $convertedSubKey = $subKey . ' (' . $subValue . ")";
                                            $convertedSubKey = str_replace(' (true)', '', $convertedSubKey);
                                            echo "<li>" . $convertedSubKey . "</li>";
                                        }
                                    }
                                }
                            }
                            if (isset($accommodation->Features->HouseCharacteristics)) {
                                echo '</ul>';
                            }
                            echo '<b>-------- Location & Views -------</b>' . '<br>';
                            $places = [];  // To store places and eliminate duplicates
                            // Access LocationDescription elements directly and print only if they exist and are non-empty
                            StringIfNotEmpty('Where', $accommodation->Features->Location->LocationDescription->Where);
                            StringIfNotEmpty('How to', $accommodation->Features->Location->LocationDescription->Howto);
                            StringIfNotEmpty('Description 1', $accommodation->Features->Location->LocationDescription->Description1);
                            StringIfNotEmpty('Description 2', $accommodation->Features->Location->LocationDescription->Description2);
                            foreach ($accommodation->Features->Location as $keyLocation => $valueLocation) {
                                if (is_object($valueLocation) && $keyLocation != 'LocationDescription') {
                                    foreach ($valueLocation as $subKey => $subValue) {
                                        $parentKey2 = $subKey;
                                        if ($parentKey2 == 'LocationDistances' || $parentKey2 == 'NearestPlaces') {
                                            echo "<b>-------- " . str_replace(['LocationDistances', 'NearestPlaces'], ['Location Distances', 'Nearest Places'], $parentKey2) . " --------</b><br>";
                                            echo '<ul>';
                                            if (is_object($subValue)) {
                                                foreach ($subValue as $subSubKey => $subSubValue) {
                                                    $parentKey3 = $subSubKey;
                                                    if (is_object($subSubValue)) {
                                                        $valueKey = "";
                                                        $nameKey = "";
                                                        $unitKey = "";
                                                        $placeTypeKey = "";
                                                        foreach ($subSubValue as $subSubSubKey => $subSubSubValue) {
                                                            if ($subSubSubKey == "Value") {
                                                                $valueKey = $subSubSubValue;
                                                            } else if ($subSubSubKey == "Unit") {
                                                                $unitKey = $subSubSubValue;
                                                            } else if ($subSubSubKey == "Name") {
                                                                $nameKey = $subSubSubValue;
                                                            } else if ($subSubSubKey == "PlaceType") {
                                                                $placeTypeKey = $subSubSubValue;
                                                            }
                                                        }
                                                        if ($parentKey2 == 'LocationDistances') {
                                                            $parentKey3 = str_replace('Distance', '', $parentKey3);  // removing 'Distance' from key
                                                        } else if ($parentKey2 == 'NearestPlaces') {
                                                            $parentKey3 = $placeTypeKey;
                                                        }
                                                        $parentKey3 = addSpaces($parentKey3);
                                                        $placeString = trim($parentKey3) . ": " . $nameKey . ", " . $valueKey . " " . $unitKey;
                                                        // Check if the place is already printed
                                                        if (!in_array($placeString, $places)) {
                                                            echo "<li>" . $placeString . "</li>";
                                                            $places[] = $placeString;  // Add this place to the array
                                                        }
                                                    }
                                                }
                                            }
                                            echo '</ul><br>';
                                        }
                                    }
                                }
                            }
                            // Views from Accommodation - Location Views
                            $viewTypes = ['ViewToBeach', 'ViewToSwimmingPool', 'ViewToGolf', 'ViewToGarden', 'ViewToRiver', 'ViewToMountain', 'ViewToLake'];
                            $viewsFUL = false;
                            foreach ($viewTypes as $viewType) {
                                if (isset($accommodation->Features->Location->LocationViews->$viewType)) {
                                    if (!$viewsFUL) {
                                        echo "<b>-------- Views from Accommodation --------</b><br>";
                                        echo '<ul>';
                                        $viewsFUL = true;
                                    }
                                    if (!empty($accommodation->Features->Location->LocationViews->$viewType) && $accommodation->Features->Location->LocationViews->$viewType == "true") {
                                        $convertedViewType = str_replace('ViewTo', '', $viewType);
                                        $convertedViewType = addSpaces($convertedViewType);
                                        echo "<li>" . $convertedViewType . "</li>";
                                    }
                                }
                            }
                            if ($viewsFUL) {
                                echo '</ul><br>';
                            }
                            // Extras and Services
                            echo '<b>-------- Extras and Services (need to get name from Services.xml with Code ID) -------</b>' . '<br>';
                            if (isset($accommodation->Features->ExtrasAndServices->SpecialServices->SpecialService)) {
                                echo '<ul>';
                            }
                            foreach ($accommodation->Features->ExtrasAndServices->SpecialServices->SpecialService as $service) {
                                $normalizedSpecialServices = [];
                                foreach ($service as $keySpecialServices => $valueSpecialServices) {
                                    $normalizedSpecialServices[trim((string)$keySpecialServices)] = trim((string)$valueSpecialServices);
                                }
                                //var_dump($normalizedSpecialServices);die;
                                $type = '';
                                $changeFrequency = '';
                                //$includedInPrice = '';
                                $countableLimit = '';
                                if (isset($normalizedSpecialServices['Type'])) {
                                    if ($servicesXml !== false) {
                                        $servicesFound = false;
                                        foreach ($servicesXml->Service as $services) {
                                            foreach ($services->Name as $name) {  // Loop over the Name array
                                                if ($name->Language == $language && $services->Code == $normalizedSpecialServices['Code']) {
                                                    $type = (string)$name->Text;
                                                    $servicesFound = true;
                                                    break;
                                                }
                                            }
                                            if ($servicesFound) {
                                                break;
                                            }
                                        }
                                    } else {
                                        //$type = $normalizedSpecialServices['Type'] . '(' . $normalizedSpecialServices['Code'] . ')';
                                        $type = $normalizedSpecialServices['Type'];
                                    }
                                }
                                if (isset($normalizedSpecialServices['ChangeBedClothes']) && $normalizedSpecialServices['ChangeBedClothes'] == 'true') {
                                    $changeFrequency = " (change bed clothes " . $normalizedSpecialServices['ChangeFrequency'] . " times)";
                                }
                                if (isset($normalizedSpecialServices['ChangeTowels']) && $normalizedSpecialServices['ChangeTowels'] == 'true') {
                                    $changeFrequency = " (change towels " . $normalizedSpecialServices['ChangeFrequency'] . " times)";
                                }
                                /*if (isset($normalizedSpecialServices['IncludedInPrice']) && $normalizedSpecialServices['IncludedInPrice'] == 'true') {
                                    $includedInPrice = "(included in price)";
                                }*/
                                if (isset($normalizedSpecialServices['Countable']) && $normalizedSpecialServices['Countable'] == 'true') {
                                    $countableLimit = " (" . $normalizedSpecialServices['CountableLimit'] . " spaces)";
                                }
                                //if (!empty($type) && !empty($includedInPrice)) {
                                if (!empty($type)) {
                                    if (!empty($changeFrequency)) {
                                        //echo $type . " - " . $changeFrequency . " " . $includedInPrice . "<br>";
                                        echo "<li>" . $type . $changeFrequency . "</li>";
                                    } else if (!empty($countableLimit)) {
                                        //echo $type . " - " . $countableLimit . " " . $includedInPrice . "<br>";
                                        echo "<li>" . $type . $countableLimit . "</li>";
                                    } else {
                                        //echo $type . " " . $includedInPrice . "<br>";
                                        echo "<li>" . $type . "</li>";
                                    }
                                }
                            }
                            if (isset($accommodation->Features->ExtrasAndServices->SpecialServices->SpecialService)) {
                                echo '</ul><br>';
                            }
                            // Common Services
                            echo '<b>-------- Common Services (need to get name from Services.xml with Code ID) -------</b>' . '<br>';
                            $commonServicesMap = [];
                            foreach ($servicesXml->Service as $commonServiceObj) {
                                foreach ($commonServiceObj->Name as $commonServiceName) {
                                    if ($commonServiceName->Language == $language) {
                                        $commonServiceObjName = (string)$commonServiceName->Text;
                                        $commonServiceObjCode = (string)$commonServiceObj->Code;
                                        $commonServicesMap[$commonServiceObjCode] = $commonServiceObjName;
                                        break;
                                    }
                                }
                            }
                            if (isset($accommodation->Features->ExtrasAndServices->CommonServices->CommonService)) {
                                echo '<ul>';
                            }
                            foreach ($accommodation->Features->ExtrasAndServices->CommonServices->CommonService as $commonservice) {
                                $normalizedCommonServices = [];
                                //var_dump($commonservice);
                                foreach ($commonservice as $keyCommonServices => $valueCommonServices) {
                                    $normalizedCommonServices[trim((string)$keyCommonServices)] = trim((string)$valueCommonServices);
                                }
                                $code = '';
                                $countableLimit = '';
                                if (isset($normalizedCommonServices['Code'])) {
                                    $code = $normalizedCommonServices['Code'];
                                }
                                $typeC = isset($commonServicesMap[$code]) ? $commonServicesMap[$code] : '';
                                if (isset($normalizedCommonServices['Countable'])) {
                                    if (isset($normalizedCommonServices['CountableLimit']) && !empty($normalizedCommonServices['CountableLimit'])) {
                                        $countableLimit = " (limit: ".  $normalizedCommonServices['CountableLimit'] . ")";
                                    } else {
                                        $countableLimit = "";
                                    }
                                }
                                echo "<li>" . $typeC . " " . $countableLimit . "</li>";
                            }
                            if (isset($accommodation->Features->ExtrasAndServices->CommonServices->CommonService)) {
                                echo '</ul><br>';
                            }
                            // Check In/Out Info
                            echo '<b>-------- Check In/Out Info -------</b>' . '<br>';
                            $checkInRule = $accommodation->CheckInCheckOutInfo->CheckInRules->CheckInRule;
                            $from = (string)$checkInRule->Schedule->From;
                            $to = (string)$checkInRule->Schedule->To;
                            $from_12hr = date("g:ia", strtotime($from));
                            $to_12hr = date("g:ia", strtotime($to));
                            CheckInIfNotEmpty('Check In', $from_12hr, $to_12hr);
                            $checkout_12hr = date("g:ia", strtotime($accommodation->CheckInCheckOutInfo->CheckOutSchedule));
                            StringIfNotEmpty('Check Out', $checkout_12hr);
                            // Reviews
                            echo '<b>-------- Reviews -------</b>' . '<br>';
                            if (is_array($accommodation->Reviews->Review) || is_object($accommodation->Reviews->Review)) {
                                foreach ($accommodation->Reviews->Review as $guestReviews) {
                                    StringIfNotEmpty('Guest Name', $guestReviews->GuestName);
                                    StringIfNotEmpty('Language', $guestReviews->Language);
                                    StringIfNotEmpty('General Rating', $guestReviews->Rating);
                                    StringIfNotEmpty('Title', $guestReviews->Title);
                                    StringIfNotEmpty('Positive Comment', $guestReviews->PositiveComment);
                                    StringIfNotEmpty('Negative Comment', $guestReviews->NegativeComment);
                                    StringIfNotEmpty('Owners Reply', $guestReviews->Reply);
                                    foreach ($guestReviews->RatingAspects->RatingAspect as $aspect) {
                                        StringIfNotEmpty('Aspect Type', $aspect->AspectType);
                                        StringIfNotEmpty('Aspect Rating', $aspect->Rating);
                                    }
                                    StringIfNotEmpty('Booking Start Date', $guestReviews->BookingStartDate);
                                    StringIfNotEmpty('Booking End Date', $guestReviews->BookingEndDate);
                                    StringIfNotEmpty('Review Date', $guestReviews->ReviewDate);
                                }
                            }
                            // Regulation Data
                            echo '<b>-------- Regulation Data -------</b>' . '<br>';
                            StringIfNotEmpty('Register Reference', $accommodation->RegulationData->RegisterReference);
                            // Tourist Taxes
                            echo '<b>-------- Tourist Taxes -------</b>' . '<br>';
                            if (is_array($accommodation->TouristTaxes->TouristTax) || is_object($accommodation->TouristTaxes->TouristTax)) {
                                foreach ($accommodation->TouristTaxes->TouristTax as $touristTax) {
                                    StringIfNotEmpty('Tax Name', $touristTax->Name);
                                    StringIfNotEmpty('Tax ID', $touristTax->TaxId);
                                    StringIfNotEmpty('VAT ID', $touristTax->VatId);
                                    StringIfNotEmpty('VAT Included', $touristTax->VatIncluded);
                                    StringIfNotEmpty('Payment Moment', $touristTax->PaymentMoment);
                                    foreach ($touristTax->Seasons->Season as $season) {
                                        StringIfNotEmpty('Start Day', $season->StartDay);
                                        StringIfNotEmpty('Start Month', $season->StartMonth);
                                        StringIfNotEmpty('End Day', $season->EndDay);
                                        StringIfNotEmpty('End Month', $season->EndMonth);
                                        StringIfNotEmpty('Adults Active', $season->AdultsActive);
                                        StringIfNotEmpty('Children Active', $season->ChildrenActive);
                                        StringIfNotEmpty('Pet Active', $season->PetActive);
                                        StringIfNotEmpty('Reservation Active', $season->ReservationActive);
                                        StringIfNotEmpty('Reservation Unit Amount', $season->ReservationUnitAmount);
                                        StringIfNotEmpty('Reservation Unit Amount Excluded Vat', $season->ReservationUnitAmountExcludedVat);
                                        StringIfNotEmpty('Reservation Type', $season->ReservationType);
                                    }
                                }
                            }
                            echo '<br><h1><i>-------- Accommodations.xml (end) -------</i></h1>';
                            echo '<br><h1><i>-------- OccupationalRules.xml (start) -------</i></h1>';
                            echo '<b style="color:blue">The occupational rules are rules which restrict the check in, check out days and the length of the online booking. They can permit either the days of the week (Monday, Tuesday and so on) or the days of the month (1, 2…31) in which people are allowed to book an accommodation, forbidding the rest of the days. Also they defined the minimum length of the booking.<br>
                                Note:<br>
                                If any OccupationalRuleId, found in Accommodations.xml file, is not defined on the OccupationalRules.xml file means that the rule is expired and we do not have valid information about it.</b>' . '<br><br>';
                            // Find the corresponding occupational rules for this accommodation
                            if ($occupationalRulesXml !== false) {
                                $occupationalRulesFound = false;
                                foreach ($occupationalRulesXml->OccupationalRule as $occupationalRules) {
                                    //var_dump($occupationalRulesXml);
                                    if ((int)$occupationalRules->Id === (int)$accommodation->OccupationalRuleId) {
                                        StringIfNotEmpty('Occupational Rule ID', $occupationalRules->Id);
                                        foreach($occupationalRules->Season as $season) {
                                            StringIfNotEmpty('Start Date', $season->StartDate);
                                            StringIfNotEmpty('End Date', $season->EndDate);
                                            StringIfNotEmpty('Minimum Nights', $season->MinimumNights);
                                            StringIfNotEmpty('Minimum Nights Online', $season->MinimumNightsOnline);
                                            // Add CheckInDays and CheckOutDays
                                            if (isset($season->CheckInDays)) {
                                                foreach ($season->CheckInDays->WeekDay as $checkInDay) {
                                                    StringIfNotEmpty('Check In Day', $checkInDay);
                                                }
                                                foreach ($season->CheckInDays->MonthDay as $checkInMonthDay) {
                                                    StringIfNotEmpty('Check In Month Day', $checkInMonthDay);
                                                }
                                            }
                                            if (isset($season->CheckOutDays)) {
                                                foreach ($season->CheckOutDays->WeekDay as $checkOutDay) {
                                                    StringIfNotEmpty('Check Out Day', $checkOutDay);
                                                }
                                                foreach ($season->CheckOutDays->MonthDay as $checkOutMonthDay) {
                                                    StringIfNotEmpty('Check Out Month Day', $checkOutMonthDay);
                                                }
                                            }
                                        }
                                        $occupationalRulesFound = true;
                                    }
                                }
                            }
                            echo '<br><h1><i>-------- OccupationalRules.xml (end) -------</i></h1>';
                            echo '<br><h1><i>-------- PriceModifiers.xml (start) -------</i></h1>';
                            echo '<b style="color:blue">The file contains the discounts and the supplements of the accommodation.</b>' . '<br><br>';
                            // Find the corresponding occupational rules for this accommodation
                            if ($priceModifiersXml !== false) {
                                $priceModifiersFound = false;
                                foreach ($priceModifiersXml->PriceModifier as $priceModifiers) {
                                    if ((int)$priceModifiers->Id === (int)$accommodation->PriceModifierId) {
                                        StringIfNotEmpty('Name', $priceModifiers->Name);
                                        StringIfNotEmpty('Price Modifiers ID', $priceModifiers->Id);
                                        StringIfNotEmpty('Maximum Percentage', $priceModifiers->MaximumPercentage);
                                        foreach($priceModifiers->Season as $seasonPM) {
                                            if (isset($seasonPM->Kind)) {
                                                foreach ($seasonPM->Kind->Code as $codePM) {
                                                    StringIfNotEmpty('Code', $codePM);
                                                }
                                                foreach ($seasonPM->Kind->IsCumulative as $isCumulativePM) {
                                                    StringIfNotEmpty('Is Cumulative', $isCumulativePM);
                                                }
                                            }
                                            StringIfNotEmpty('Start Date', $seasonPM->StartDate);
                                            StringIfNotEmpty('End Date', $seasonPM->EndDate);
                                            StringIfNotEmpty('Min Number Of Nights', $seasonPM->MinNumberOfNights);
                                            StringIfNotEmpty('Max Number Of Nights', $seasonPM->MaxNumberOfNights);
                                            StringIfNotEmpty('Number Of Nights', $seasonPM->NumberOfNights);
                                            StringIfNotEmpty('Max Date', $seasonPM->MaxDate);
                                            StringIfNotEmpty('Days Advance', $seasonPM->DaysAdvance);
                                            StringIfNotEmpty('Type', $seasonPM->Type);
                                            StringIfNotEmpty('Discount Supplement Type', $seasonPM->DiscountSupplementType);
                                            StringIfNotEmpty('Amount', $seasonPM->Amount);
                                            StringIfNotEmpty('Currency', $seasonPM->Currency);
                                            if (isset($seasonPM->VAT)) {
                                                foreach ($seasonPM->VAT->Included as $vatIncluded) {
                                                    StringIfNotEmpty('VAT Included', $vatIncluded);
                                                }
                                                foreach ($seasonPM->VAT->Percentage as $vatPercentage) {
                                                    StringIfNotEmpty('VAT Percentage', $vatPercentage);
                                                }
                                            }
                                        }
                                        $priceModifiersFound = true;
                                    }
                                }
                            }
                            echo '<br><h1><i>-------- PriceModifiers.xml (end) -------</i></h1>';
                            echo '<br><h1><i>-------- Description.xml (start) -------</i></h1>';
                            echo '<b style="color:blue">This file contains the following information:<br>
                            - Accommodation’s text descriptions,<br>
                            - All fields of an accommodation, those can be in different languages,<br>
                            - URL’s of the accommodation photos,<br>
                            - URL to Avantio’s system.<br><br>
                            You need to note following parameters (FRMEntrada, FRMSalida, FRMAdultos) by GET method to use correctly URLs, DetailsURL and BookingURL.<br><br>
                            In BookingURL parameters are required. It is also recommended to introduce parameters in DetailsURL. It is useful for users. They just have to introduce dates and capacity once</b>' . '<br><br>';
                            if ($descriptionsXml !== false) {
                                //echo 'Descriptions XML found...<br>';
                                $descriptionsPicsFound = false;
                                //var_dump($descriptionsXml); die;
                                foreach ($descriptionsXml->Accommodation as $pictures) {
                                    if ($pictures->AccommodationId == $accommodationId) {
                                        StringIfNotEmpty('Accommodation ID', $pictures->AccommodationId);
                                        echo '<b>-------- Images ----------</b>' . '<br>';
                                        StringIfNotEmpty('Last Modified', $pictures->Pictures->LastModified);
                                        foreach ($pictures->Pictures->Picture as $picture) {
                                            StringIfNotEmpty('Name of Image', $picture->Name);
                                            StringIfNotEmpty('Type', $picture->Type);
                                            StringIfNotEmpty('Description of Image', $picture->Description);
                                            ImageIfNotEmpty('Thumbnail Image Size', $picture->ThumbnailURI, $picture->Name);
                                            ImageIfNotEmpty('Normal Image Size', $picture->AdaptedURI, $picture->Name);
                                            ImageIfNotEmpty('Original Image Size', $picture->OriginalURI, $picture->Name);
                                        }
                                        $descriptionsPicsFound = true;
                                    }
                                }
                                if (!$descriptionsPicsFound) {
                                    echo 'Thumbnail Image Size: n/a<br>';
                                    echo 'Normal Image Size: n/a<br>';
                                    echo 'Original Image Size: n/a<br>';
                                }
                                $descriptionsFound = false;
                                foreach ($descriptionsXml->Accommodation as $descs) {
                                    if ($descs->AccommodationId == $accommodationId) {
                                        echo '<b>-------- Description ----------</b>' . '<br>';
                                        foreach ($descs->InternationalizedItem as $InternationalizedItem) {
                                            if ($InternationalizedItem->Language == $language) {
                                                StringIfNotEmpty('Language', $InternationalizedItem->Language);
                                                StringIfNotEmpty('Accommodation Name', $InternationalizedItem->AccommodationName);
                                                StringIfNotEmpty('Description', $InternationalizedItem->Description);
                                                StringIfNotEmpty('Detailed URL', $InternationalizedItem->DetailsURL);
                                                StringIfNotEmpty('Booking URL', $InternationalizedItem->BookingURL);
                                                StringIfNotEmpty('Contact URL', $InternationalizedItem->ContactURL);
                                                StringIfNotEmpty('Property Type Code (MasterKind)', $InternationalizedItem->MasterKind->MasterKindCode);
                                                StringIfNotEmpty('Property Type (MasterKind)', $InternationalizedItem->MasterKind->MasterKindName);
                                                StringIfNotEmpty('Country Code', $InternationalizedItem->Country->CountryCode);
                                                StringIfNotEmpty('Country Name', $InternationalizedItem->Country->Name);
                                                StringIfNotEmpty('Region Code', $InternationalizedItem->Region->RegionCode);
                                                StringIfNotEmpty('Region Name', $InternationalizedItem->Region->Name);
                                                StringIfNotEmpty('City Code', $InternationalizedItem->City->CityCode);
                                                StringIfNotEmpty('City Name', $InternationalizedItem->City->Name);
                                                StringIfNotEmpty('Province Code', $InternationalizedItem->Province->ProvinceCode);
                                                StringIfNotEmpty('Province Name', $InternationalizedItem->Province->Name);
                                                StringIfNotEmpty('Locality Code', $InternationalizedItem->Locality->LocalityCode);
                                                StringIfNotEmpty('Locality Name', $InternationalizedItem->Locality->Name);
                                                StringIfNotEmpty('District Code', $InternationalizedItem->District->DistrictCode);
                                                StringIfNotEmpty('District Name', $InternationalizedItem->District->Name);
                                                echo '<b>-------- Extras (mandatory or included services) ----------</b>' . '<br>';
                                                echo '<ul>';
                                                foreach ($InternationalizedItem->ExtrasSummary->ObligatoryOrIncluded->Extra as $ObligatoryOrIncluded) {
                                                    echo '<li>' . $ObligatoryOrIncluded->Name . ': ' . $ObligatoryOrIncluded->Description . '</li>';
                                                }
                                                echo '</ul>';
                                                echo '<b>-------- Optional (optional services - also in Accommondations.xml) ----------</b>' . '<br>';
                                                echo '<ul>';
                                                foreach ($InternationalizedItem->ExtrasSummary->Optional->Extra as $Optional) {
                                                    echo '<li>' . $Optional->Name . ': ' . $Optional->Description . '</li>';
                                                }
                                                echo '</ul>';
                                            }
                                        }
                                        $descriptionsFound = true;
                                        break;
                                    }
                                    if ($descriptionsFound) {
                                        break;
                                    }
                                }
                                if (!$descriptionsFound) {
                                    echo 'Description: n/a<br>';
                                }
                            }
                            echo '<br><h1><i>-------- Description.xml (end) -------</i></h1>';
                            echo '<br><h1><i>-------- Rates.xml (start) -------</i></h1>';
                            echo '<b style="color:blue">The file Rates.xml contains the current rates of all accommodations.</b>' . '<br><br>';
                            // Print available dates for each property
                            echo '<b>-------- Available Rates -------</b>' . '<br>';
                            // Find the corresponding rates for this accommodation
                            if ($ratesXml !== false) {
                                $rateFound = false;
                                foreach($ratesXml->AccommodationList->Accommodation as $rate) {
                                    if ($rate->AccommodationId == $accommodationId) {
                                        StringIfNotEmpty('Accommodation Id', $rate->AccommodationId);
                                        StringIfNotEmpty('Capacity', $rate->Capacity);
                                        foreach($rate->Rates->RatePeriod as $ratePeriod) {
                                            StringIfNotEmpty('Rate Start Date', $ratePeriod->StartDate);
                                            StringIfNotEmpty('Rate End Date', $ratePeriod->EndDate);
                                            // Calculate the number of nights
                                            $startDate = new DateTime($ratePeriod->StartDate);
                                            $convertedStartDate = $startDate->format('d/m/Y');
                                            $endDate = new DateTime($ratePeriod->EndDate);
                                            $convertedEndDate = $endDate->format('d/m/Y');
                                            echo "Converted dates: from " . $convertedStartDate . " to " . $convertedEndDate . "<br>";
                                            // Loop through meal plans
                                            foreach($ratePeriod as $mealPlan) {
                                                // Check if the meal plan node has Type and Price attributes
                                                if (isset($mealPlan->Type) && isset($mealPlan->Price)) {
                                                    StringIfNotEmpty('Meal Plan', $mealPlan->getName()); // Add this line to show meal plan type
                                                    StringIfNotEmpty('Rate Type', $mealPlan->Type);
                                                    $priceConvert = str_replace('EUR', '&euro;', $ratePeriod->Currency);
                                                    StringIfNotEmpty('Rate Price', $priceConvert . $mealPlan->Price);
                                                }
                                                // Discounts are only valid per person and not ByAccommodation
                                                if (isset($mealPlan->Type) && $mealPlan->Type != 'ByAccommodation') {
                                                    // Discounts or Special Offers
                                                    foreach ($mealPlan->Discounts as $discountOffers) {
                                                        StringIfNotEmpty('Guest Name', $discountOffers->GuestName);
                                                        StringIfNotEmpty('Language', $discountOffers->Language);
                                                        StringIfNotEmpty('General Rating', $discountOffers->Rating);
                                                        foreach ($discountOffers->ThirdPerson as $thirdPerson) {
                                                            StringIfNotEmpty('Name', 'Third Person');
                                                            StringIfNotEmpty('Type', $thirdPerson->Type);
                                                            StringIfNotEmpty('Discount', $thirdPerson->Discount);
                                                            echo "Discount of 50% for bookings of more than 6 nights<br>";
                                                            echo "From " . $convertedStartDate . " to " . $convertedEndDate;
                                                        }
                                                        foreach ($discountOffers->ForthPerson as $forthPerson) {
                                                            StringIfNotEmpty('Name', 'Forth Person');
                                                            StringIfNotEmpty('Type', $forthPerson->Type);
                                                            StringIfNotEmpty('Discount', $forthPerson->Discount);
                                                            echo "Discount of 50% for bookings of more than 6 nights<br>";
                                                            echo "From " . $convertedStartDate . " to " . $convertedEndDate;
                                                        }
                                                        foreach ($discountOffers->FifthPerson as $fifthPerson) {
                                                            StringIfNotEmpty('Name', 'Fifth Person');
                                                            StringIfNotEmpty('Type', $fifthPerson->Type);
                                                            StringIfNotEmpty('Discount', $fifthPerson->Discount);
                                                            echo "Discount of 50% for bookings of more than 6 nights<br>";
                                                            echo "From " . $convertedStartDate . " to " . $convertedEndDate;
                                                        }
                                                        foreach ($discountOffers->SixthPerson as $sixthPerson) {
                                                            StringIfNotEmpty('Name', 'Sixth Person');
                                                            StringIfNotEmpty('Type', $sixthPerson->Type);
                                                            StringIfNotEmpty('Discount', $sixthPerson->Discount);
                                                            echo "Discount of 50% for bookings of more than 6 nights<br>";
                                                            echo "From " . $convertedStartDate . " to " . $convertedEndDate;
                                                        }
                                                        foreach ($discountOffers->SeventhPerson as $seventhPerson) {
                                                            StringIfNotEmpty('Name', 'Seventh Person');
                                                            StringIfNotEmpty('Type', $seventhPerson->Type);
                                                            StringIfNotEmpty('Discount', $seventhPerson->Discount);
                                                            echo "Discount of 50% for bookings of more than 6 nights<br>";
                                                            echo "From " . $convertedStartDate . " to " . $convertedEndDate;
                                                        }
                                                        foreach ($discountOffers->FirstChild as $firstChild) {
                                                            StringIfNotEmpty('Name', 'First Child');
                                                            StringIfNotEmpty('Type', $firstChild->Type);
                                                            StringIfNotEmpty('Discount', $firstChild->Discount);
                                                            echo "Discount of 50% for bookings of more than 6 nights<br>";
                                                            echo "From " . $convertedStartDate . " to " . $convertedEndDate;
                                                        }
                                                        foreach ($discountOffers->SecondChild as $secondChild) {
                                                            StringIfNotEmpty('Name', 'Second Child');
                                                            StringIfNotEmpty('Type', $secondChild->Type);
                                                            StringIfNotEmpty('Discount', $secondChild->Discount);
                                                            echo "Discount of 50% for bookings of more than 6 nights<br>";
                                                            echo "From " . $convertedStartDate . " to " . $convertedEndDate;
                                                        }
                                                        foreach ($discountOffers->ThirdChild as $thirdChild) {
                                                            StringIfNotEmpty('Name', 'Third Child');
                                                            StringIfNotEmpty('Type', $thirdChild->Type);
                                                            StringIfNotEmpty('Discount', $thirdChild->Discount);
                                                            echo "Discount of 50% for bookings of more than 6 nights<br>";
                                                            echo "From " . $convertedStartDate . " to " . $convertedEndDate;
                                                        }
                                                        foreach ($discountOffers->ForthChild as $forthChild) {
                                                            StringIfNotEmpty('Name', 'Forth Child');
                                                            StringIfNotEmpty('Type', $forthChild->Type);
                                                            StringIfNotEmpty('Discount', $forthChild->Discount);
                                                            echo "Discount of 50% for bookings of more than 6 nights<br>";
                                                            echo "From " . $convertedStartDate . " to " . $convertedEndDate;
                                                        }
                                                    }
                                                }
                                            }
                                            SupplementsIfNotEmpty('Rate Supplements', $ratePeriod->Currency, $ratePeriod->Supplements->SupplementByAdditionalPerson->ApplicableFromNumberPeople, $ratePeriod->Supplements->SupplementByAdditionalPerson->Supplement);
                                            StringIfNotEmpty('Rate Currency', $ratePeriod->Currency);
                                        }
                                        IncludedIfNotEmpty('VAT', $rate->VAT->Included);
                                        StringIfNotEmpty('VAT Percentage', $rate->VAT->Percentage);
                                        echo '----------------------' . '<br>';
                                        $rateFound = true;
                                    }
                                }
                                if (!$rateFound) {
                                    echo 'Price: n/a<br>';
                                    echo '----------------------' . '<br>';
                                }
                            }
                            echo '<br><h1><i>-------- Rates.xml (end) -------</i></h1>';
                            echo '<br><h1><i>-------- Kinds.xml (start) -------</i></h1>';
                            echo '<b style="color:blue">This file contains a list of all kinds of accommodations that own a broker. Accommodations type are in several languages.</b>' . '<br><br>';
                            echo '<b>-------- Kinds Details associated with this accommondation -------</b>' . '<br>';
                            if ($kindsXml !== false) {
                                $kindsFound = false;
                                foreach ($kindsXml->InternationalizedKinds as $internationalizedKind) {
                                    if (strtolower(trim((string)$internationalizedKind->Language)) === strtolower(trim($language))) {
                                        echo '<br><b style="color:blue">Kind associated with this accommondation</b>' . '<br>';
                                        foreach ($internationalizedKind->MasterKind as $kind) {
                                            if ((string)$kind->MasterKindCode === (string)$accommodation->MasterKind->MasterKindCode) {
                                                StringIfNotEmpty('Master Kind Code', $kind->MasterKindCode);
                                                StringIfNotEmpty('Master Kind Name', $kind->MasterKindName);
                                                $kindsFound = true;
                                            }
                                        }
                                    }
                                }
                                $kindsallFound = false;
                                foreach ($kindsXml->InternationalizedKinds as $internationalizedKindall) {
                                    if (strtolower(trim((string)$internationalizedKindall->Language)) === strtolower(trim($language))) {
                                        echo '<br><b style="color:blue">Additional kinds associated with ALL types of accommondations. Need it for the search filter</b>' . '<br>';
                                        foreach ($internationalizedKindall->MasterKind as $kindall) {
                                            StringIfNotEmpty('Master Kind Code', $kindall->MasterKindCode);
                                            StringIfNotEmpty('Master Kind Name', $kindall->MasterKindName);
                                            $kindsallFound = true;
                                        }
                                    }
                                }
                            }
                            echo '<br><h1><i>-------- Kinds.xml (end) -------</i></h1>';
                            echo '<br><h1><i>-------- GeographicAreas.xml(start) -------</i></h1>';
                            echo '<b style="color:blue">The file “GeographicAreas.xml” contains a structure such as Countries > Regions > Cities > Localities > Districts in which the broker has accommodations. This structure is based on the geographic regions of the countries. It is the seam structure for each language.</b>' . '<br><br>';
                            if ($geographicAreasXml !== false) {
                                $geographicRegionsFound = false;
                                $geographicCityFound = false;
                                $geographicLocalityFound = false;
                                $geographicDistrictFound = false;
                                foreach ($geographicAreasXml->InternationalizedItem as $internationalizedItem) {
                                    if (strtolower(trim((string)$internationalizedItem->Language)) === strtolower(trim($language))) {
                                        foreach ($internationalizedItem->Countries->Country as $country) {
                                            if ((string)$country->CountryCode === (string)$accommodation->LocalizationData->Country->CountryCode) {
                                                StringIfNotEmpty('Country Code', $country->CountryCode);
                                                StringIfNotEmpty('Country Name', $country->Name);
                                                // Assuming there can be multiple regions in a country
                                                foreach ($country->Regions->Region as $region) {
                                                    if ((string)$region->RegionCode === (string)$accommodation->LocalizationData->Region->RegionCode) {
                                                        StringIfNotEmpty('Region Code', $region->RegionCode);
                                                        StringIfNotEmpty('Region Name', $region->Name);
                                                        $geographicRegionsFound = true;
                                                        // Assuming there can be multiple cities in a region
                                                        foreach ($region->Cities->City as $city) {
                                                            if ((string)$city->CityCode === (string)$accommodation->LocalizationData->City->CityCode) {
                                                                StringIfNotEmpty('City Code', $city->CityCode);
                                                                StringIfNotEmpty('City Name', $city->Name);
                                                                $geographicCityFound = true;
                                                                // Assuming there can be multiple localities in a city
                                                                foreach ($city->Localities->Locality as $locality) {
                                                                    if ((string)$locality->LocalityCode === (string)$accommodation->LocalizationData->Locality->LocalityCode) {
                                                                        StringIfNotEmpty('Locality Code', $locality->LocalityCode);
                                                                        StringIfNotEmpty('Locality Name', $locality->Name);
                                                                        $geographicLocalityFound = true;
                                                                        // Assuming there can be multiple distrincts in a locality
                                                                        foreach ($locality->Districts->District as $district) {
                                                                            if ((string)$district->DistrictCode === (string)$accommodation->LocalizationData->District->DistrictCode) {
                                                                                StringIfNotEmpty('District Code', $district->DistrictCode);
                                                                                StringIfNotEmpty('District Name', $district->Name);
                                                                                $geographicDistrictFound = true;
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            echo '<br><h1><i>-------- GeographicAreas.xml (end) -------</i></h1>';
                        }
                    }
                } else {
                    echo 'No properties available with the specified filters.';
                }
            } else {
                echo "Failed to read XML.";
            }
        } else {
            echo "XML file does not exist.";
        }
    }
    ?>
    <?php
    AccommodationsFeeds($company, $accommodationId, $accID, $dateFrom, $dateTo, $dateToAPI, $result_IsAvailable, $result_GetBookingPrice, $language);
} catch (SoapFault $e) {
    //echo 'Error: ' . $e->getMessage();
    //echo 'Error: ' . $e->getMessage() . PHP_EOL . 'Error Code: ' . $e->getCode();
    echo "Accommodation not available.";
}
?>