<?php
try {
    // Test Credentials
    /*
    Partner Code (feeds) : 836efa4efbe7fa63f2ebbae30d7b965f
    User: itsatentoapi_test
    Password: testapixml
    LoginGa: itsalojamientos

    Code:
    $username = 'itsatentoapi_test';
    $password = 'testapixml';
    $apiKey = 'itsalojamientos';
    $secretKey = '';
    $company = 'itsalojamientos';
    $partnerCode = '836efa4efbe7fa63f2ebbae30d7b965f';
    */
    global $wpdb;
    // Avantio API credentials and other parameters
    $username = 'trident';
    $password = '7Mx4EuPGpPy6';
    $apiKey = 'trident';
    $secretKey = '';
    $LoginGA = 'james';
    if (isset($_POST['prop_id'])) {
        $accommodationId = isset($_POST['prop_id']) ? $_POST['prop_id'] : '';
    } else if (null !== get_query_var('prop_id') && get_query_var('prop_id') != '') {
        $accommodationId = get_query_var('prop_id', '');
    } else {
        $accommodationId = '';
    }
    if (!empty($accommodationId)) {
        $results_accID = $wpdb->get_results($wpdb->prepare("SELECT * FROM wp_accommodations WHERE AccommodationId = %s", $accommodationId));
		
        if (!empty($results_accID)) {
			$pagename = get_query_var('pagename', '');
			if (!$results_accID[0]->longtermrental && $pagename != 'property') {
				$sanitizedURL = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
				wp_redirect(home_url(str_replace('long-term-rental', 'property', $sanitizedURL)), 301);
				exit();
			}
			if ($results_accID[0]->longtermrental && $pagename != 'long-term-rental') {
				$sanitizedURL = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
				wp_redirect(home_url(str_replace('property', 'long-term-rental', $sanitizedURL)), 301);
				exit();
			}
			$occupation_id = $results_accID[0]->OccupationalRuleId;
            $CityName_url = strtolower(trim((string)$results_accID[0]->CityName));
            $LocalityName_url = strtolower(trim((string)$results_accID[0]->LocalityName));
            $LocalityName_url = str_replace(array(",", "'"), '', $LocalityName_url);
            $LocalityName_url = preg_replace('/[\[\]()]/', '', $LocalityName_url);
            $LocalityName_url = str_replace(array("/", ".", "+"), '-', $LocalityName_url);
            $LocalityName_url = preg_replace('/\s+/', '-', $LocalityName_url);
            $LocalityName_url = preg_replace('/-+/', '-', $LocalityName_url);
            $AccommodationName_url = strtolower(trim((string)$results_accID[0]->AccommodationName));
            $AccommodationName_url = str_replace(array(",", "'", "&"), '', $AccommodationName_url);
            $AccommodationName_url = preg_replace('/[\[\]()]/', '', $AccommodationName_url);
            $AccommodationName_url = str_replace(array("/", ".", "+"), '-', $AccommodationName_url);
            $AccommodationName_url = preg_replace('/\s+/', '-', $AccommodationName_url);
            $AccommodationName_url = preg_replace('/-+/', '-', $AccommodationName_url);
            if (isset($_POST['county_name'])) {
                $county_name = isset($_POST['county_name']) ? $_POST['county_name'] : '';
            } else if (null !== get_query_var('county_name') && get_query_var('county_name') != '') {
                $county_name = get_query_var('county_name', '');
            } else {
                $county_name = '';
            }
            if (isset($_POST['town_name'])) {
                $town_name = isset($_POST['town_name']) ? $_POST['town_name'] : '';
            } else if (null !== get_query_var('town_name') && get_query_var('town_name') != '') {
                $town_name = get_query_var('town_name', '');
            } else {
                $town_name = '';
            }
            if (isset($_POST['address_name'])) {
                $address_name = isset($_POST['address_name']) ? $_POST['address_name'] : '';
            } else if (null !== get_query_var('address_name') && get_query_var('address_name') != '') {
                $address_name = get_query_var('address_name', '');
            } else {
                $address_name = '';
            }
			$CityName_url = urlencode($CityName_url);
			$LocalityName_url = urlencode($LocalityName_url);
			$AccommodationName_url = urlencode($AccommodationName_url);
            if (!empty($county_name) && $county_name === $CityName_url && !empty($town_name) && $town_name === $LocalityName_url && !empty($address_name) && $address_name === $AccommodationName_url) {
                $accID = $results_accID[0]->UserId;
            } else {
                $accID = '';
            }
        } else {
            $accID = '';
        }
		/*if (isset($_POST['acc_id'])) {
			$accID = isset($_POST['acc_id']) ? $_POST['acc_id'] : '';
		} else if (null !== get_query_var('acc_id') && get_query_var('acc_id') != '') {
			$accID = get_query_var('acc_id', '');
		} else {
			$accID = '';
		}*/
		$company = 'james';
		$partnerCode = '25ce87c2384f552afd0144c97669c840';
		$language = 'en';
		$languageUpper = 'EN';
		// Set the parameters
		if (isset($_REQUEST['AdultNum'])) {
			$adultsNumber = $_REQUEST['AdultNum'];
			$getBookingPrice_info = 'yes';
		} else {
			$adultsNumber = 1;
			$getBookingPrice_info = 'no';
		}
		if(!empty($_REQUEST['dateFrom']))
			$getBookingPrice_info = 'yes';

		$childrenNumber = isset($_REQUEST['ChildrenNum']) ? $_REQUEST['ChildrenNum'] : 0;
		$childAges = array();
		for ($i = 1; $i <= 6; $i++) {
			$key = 'Child_' . $i . '_Age';
			$childAge = isset($_REQUEST[$key]) ? $_REQUEST[$key] : '';
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
		} else if (isset($_GET['dateFrom'])) {
			$dateFrom = $_GET['dateFrom'];
		} else {
			$dateFrom = '';
		}
		if (isset($_POST['dateTo'])) {
			$dateTo = $_POST['dateTo'];
			// Due to dateTo not being the checkout date, we need to minus the checkout date by one to give the number of nights stayed
			$dateToAPI = date('Y-m-d', strtotime($dateTo . ' -1 day'));
		} else if (isset($_GET['dateTo'])) {
			$dateTo = $_GET['dateTo'];
			$dateToAPI = date('Y-m-d', strtotime($dateTo . ' -1 day'));
		} else {
			$dateTo = '';
			$dateToAPI = '';
		}
		if ($dateFrom === '' ) {
			$getBookingPrice_info = 'yes';
			if (isset($occupation_id) && $occupation_id) {
				$occupationalRuleData = getOccupationalRulesFeeds($accommodationId, $occupation_id);
			}
			$availabilityData = AvailabilityFeedsPHP($accommodationId, $language);
			$currentDate = new DateTime();  // Current date
			$earliestStartDate = clone $currentDate;
			if (isset($availabilityData['MinDaysNotice'][0])) {
				$earliestStartDate->modify('+' . $availabilityData['MinDaysNotice'][0] . ' days');
			}
			$minimumNightsRequired = isset($occupationalRuleData['SeasonsOR'][0]['MinimumNights']) ? $occupationalRuleData['SeasonsOR'][0]['MinimumNights'] : 0;
			//$current_date = date_format($earliestStartDate->date,"Y-m-d");
			$current_date = $earliestStartDate->format('Y-m-d');
			$current_date_day = $earliestStartDate->format('l');
			if (isset($availabilityData['availableRanges']) && count($availabilityData['availableRanges']) > 0) {
				usort($availabilityData['availableRanges'], function ($a, $b) {
					return strtotime($a['start']) - strtotime($b['start']);
				});
				$end_date = $earliestStartDate->modify('+' . $minimumNightsRequired . ' days')->format('Y-m-d');
				foreach($availabilityData['availableRanges'] as $key=>$range) {
					if ($range['start'] <= $current_date && $range['end'] >= $end_date) {
						$range_preiod = $range;
						break;
					} else if(isset($availabilityData['availableRanges'][$key+1]) && $availabilityData['availableRanges'][$key+1]['start'] <= $end_date) {
						$range_preiod = $range;
						break;
					} else {
						if ($key == '0' && $range['start'] > $current_date) {
							$range_preiod = $range;
							$currentDate = new DateTime($range['start']);  // Current date
							$earliestStartDate = clone $currentDate;
							$current_date = $earliestStartDate->format('Y-m-d');
							$current_date_day = $earliestStartDate->format('l');
							$end_date = $earliestStartDate->modify('+' . $minimumNightsRequired . ' days')->format('Y-m-d');
							break;
						} else {
							$currentDate = new DateTime($availabilityData['availableRanges'][$key+1]['start']);  // Current date
							$earliestStartDate = clone $currentDate;
							$current_date = $earliestStartDate->format('Y-m-d');
							$current_date_day = $earliestStartDate->format('l');
							$end_date = $earliestStartDate->modify('+' . $minimumNightsRequired . ' days')->format('Y-m-d');	
						}
						
					}
				}
			}
			if (isset($occupationalRuleData['SeasonsOR']) && count($occupationalRuleData['SeasonsOR']) > 0 ) {
				foreach($occupationalRuleData['SeasonsOR'] as $season_data) {
					$season_start_date_time = new DateTime(str_replace('/', '-', $season_data['StartDate']));
					$season_start_date = $season_start_date_time->format('Y-m-d');
					$season_end_date_time = new DateTime(str_replace('/', '-', $season_data['EndDate']));
					$season_end_date = $season_end_date_time->format('Y-m-d');
					if (strtotime($season_start_date) <= strtotime($range_preiod['start']) && strtotime($range_preiod['start']) <= strtotime($season_end_date)) {
						if (is_array($season_data['CheckInDays'])) {
							if (in_array(strtoupper($current_date_day), $season_data['CheckInDays'])) {
								$check_in_date = $current_date;
								$currentCheckInDate = new DateTime($check_in_date);
								$check_out_date = $currentCheckInDate->modify('+' . $season_data['MinimumNights'] . ' days')->format('Y-m-d');
							} else {
								$closest_date = findClosestDate($earliestStartDate, $season_data['CheckInDays']);
								if ($closest_date !== null) {
							        $check_in_date = $closest_date->format('Y-m-d');
							        $check_out_date = $closest_date->modify('+' . $season_data['MinimumNights'] . ' days')->format('Y-m-d');
							    } else {
									$check_in_date = $current_date;
									$currentCheckInDate = new DateTime($current_date);
									$check_out_date = $currentCheckInDate->modify('+' . $minimumNightsRequired . ' days')->format('Y-m-d');
								}
							}
						} else if($season_data['CheckInDays'] != '') {
							$check_in_day = $season_data['CheckInDays'];
							if (strtoupper($current_date_day) == $check_in_day) {
								$check_in_date = $current_date;
								$currentCheckInDate = new DateTime($check_in_date);
								$check_out_date = $currentCheckInDate->modify('+' . $season_data['MinimumNights'] . ' days')->format('Y-m-d');
							} else {
								$next_cd = 'next '.strtolower($check_in_day);
								$currentCheckInDate = new DateTime($current_date);
								$currentCheckInDate->modify($next_cd);
								$check_in_date = $currentCheckInDate->format('Y-m-d');
								$check_out_date = $currentCheckInDate->modify('+' . $season_data['MinimumNights'] . ' days')->format('Y-m-d');
							}
						} else {
							$currentCheckInDate = new DateTime($current_date);
							$check_in_date = $current_date;
							$check_out_date = $currentCheckInDate->modify('+' . $season_data['MinimumNights'] . ' days')->format('Y-m-d');
						}
					}	
				}
			}
			if (!isset($check_in_date)) {
				$check_in_date = $current_date;
			}
			if (!isset($check_out_date)) {
				$currentCheckInDate = new DateTime($current_date);
				$check_out_date = $currentCheckInDate->modify('+' . $minimumNightsRequired . ' days')->format('Y-m-d');
			}
			$dateFrom = $check_in_date;
			$dateTo = $check_out_date;
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
					'LoginGA' => $LoginGA
				],
				'Occupants' => array(
					'AdultsNumber' => $adultsNumber
				),
				'DateFrom' => $dateFrom,
				'DateTo' => $dateToAPI,
				
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
					'LoginGA' => $LoginGA
				],
				'Occupants' => array(
					'AdultsNumber' => $adultsNumber
				),
				'ArrivalDate' => $dateFrom,
				'DepartureDate' => $dateTo,

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
		GetHTMLFeedsForAccommodation($company, $accommodationId, $accID, $dateFrom, $dateTo, $dateToAPI, $result_IsAvailable, $result_GetBookingPrice, $language, $adultsNumber, $getBookingPrice_info, $childrenNumber, $childAge);
	} else if(!current_user_can( 'edit_posts' )) {
		wp_redirect(home_url('/search-ireland/'), 301);
		exit();
	}
} catch (SoapFault $e) {
	/*
	echo 'Message: ' .$e->getMessage();
	die;
	*/
    wp_redirect(home_url('/search-ireland/'), 301);
    exit();
}
function findSeasonDetails(){

}
function findClosestDate($current_date, $availaible_days) {
	$next_day = $current_date->modify('+1 days');
	$next_date_day = $next_day->format('l');
	if (in_array(strtoupper($next_date_day), $availaible_days)) {
		return $next_day;
	} else {
		 findClosestDate($next_day, $availaible_days);
	}
}
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
		echo $name . ': <img src="' . $strVal . '" data-src="' . $strVal . '" loading="lazy" alt="' . $strValName . '" /><br>';
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
	$row = getAvailability($accommodationId);
	$availableRanges = array();
	$tomorrows_date = date('Y-m-d', strtotime('+1 day'));
	if (!empty($row)) {
		$Availabilities = json_decode($row->Availabilities);
		if (is_array($Availabilities->AvailabilityPeriod)) {
			foreach($Availabilities->AvailabilityPeriod as $period) {
				if ($period->State == 'AVAILABLE' || $period->State == 'ONREQUEST') {
					$startDate_org = (string)$period->StartDate;
					$endDate = (string)$period->EndDate;
					// adding +1 for checkout 
					$checkoutDate = new DateTime($endDate);
					$checkoutDate->modify('+ 1 days');
					if ($startDate_org >= $tomorrows_date) {
						$startDate = $startDate_org;
					} else {
						$startDate = $tomorrows_date;
					}
					$availableRanges[] = array(
						"start" => $startDate,
						"end" => $checkoutDate->format('Y-m-d')
					);
				}
			}
		} else {
			if ($Availabilities->AvailabilityPeriod->State == 'AVAILABLE' || $Availabilities->AvailabilityPeriod->State == 'ONREQUEST') {
				$startDate_org = (string)$Availabilities->AvailabilityPeriod->StartDate;
				$endDate = (string)$Availabilities->AvailabilityPeriod->EndDate;
				// adding +1 for checkout 
				$checkoutDate = new DateTime($endDate);
				$checkoutDate->modify('+ 1 days');
				if ($startDate_org >= $tomorrows_date) {
					$startDate = $startDate_org;
				} else {
					$startDate = $tomorrows_date;
				}
				$availableRanges[] = array(
					"start" => $startDate,
					"end" => $checkoutDate->format('Y-m-d')
				);
			}
		}
		// Convert the PHP array to a JSON string
		$availableRangesJson = json_encode($availableRanges);
		// Output the JSON data for JavaScript to use
		echo "<script>var availableRanges = $availableRangesJson;</script>";
	}
}
function getPriceModifierFeeds($accommodationId, $PriceModifierId) {
	$row = getPriceModifier($PriceModifierId);
	$data = array();
	if (!empty($row)) {
		$totalOffers = 0;
		$seasonPMData = array();
		$Seasons = json_decode($row->Seasons);
		foreach ($Seasons as $seasonPM){
			$minNightsAllowed = !empty($seasonPM->MinNumberOfNights) ? (int)$seasonPM->MinNumberOfNights : 0;
			$numNightsAllowed = !empty($seasonPM->NumberOfNights) ? (int)$seasonPM->NumberOfNights : 0;
			// Skip this offer if MinNumberOfNights or NumberOfNights is less than 7
			if ($numNightsAllowed < 7) {
				continue;
			}
			$amountPM = (float)$seasonPM->Amount;
			if ($amountPM <= 0) {
				$startDate = date('d/m/Y', strtotime((string)$seasonPM->StartDate));
				$endDate = date('d/m/Y', strtotime((string)$seasonPM->EndDate));
				$seasonPMInfo = array(
					'StartDate' => $startDate,
					'EndDate' => $endDate,
					'MinNumberOfNights' => $minNightsAllowed,
					'MaxNumberOfNights' => !empty($seasonPM->MaxNumberOfNights) ? (int)$seasonPM->MaxNumberOfNights : 0,
					'NumberOfNights' => $numNightsAllowed,
					'Type' => (string)$seasonPM->Type,
					'DiscountSupplementType' => (string)$seasonPM->DiscountSupplementType,
					'Amount' => abs((float)$seasonPM->Amount),
					'Currency' => !empty($seasonPM->Currency) ? (string)$seasonPM->Currency : 'EUR',
					'MaxDate' => (string)$seasonPM->MaxDate
				);
				$seasonPMData[] = $seasonPMInfo; // Store each season's details in the array
				$totalOffers++;
			}
		}
		$data['TotalOffers'] = $totalOffers; // Assign the count to the TotalOffers key
		$data['SeasonsPM'] = $seasonPMData; // Assign the array to the 'SeasonsPM' key
	}
	return $data;
}
function getSimilarHomes($accommodationId, $minOccupants, $minBedrooms, $location) {
	$matchingProperties = [];
	$longTermRentalsPeriod = 365;
	$accommodations = getAllAccommodations();
	if (!empty($accommodations)) {
		foreach ($accommodations as $accommodation) {
			$currentID = (int)$accommodation->AccommodationId;
			$occupationalRuleData = getOccupationalRulesFeeds($currentID, $accommodation->OccupationalRuleId);
			if ((int)$accommodation->PeopleCapacity >= $minOccupants && (int)$accommodation->Bedrooms >= $minBedrooms && (string)$accommodation->CityName == $location && (int)$accommodation->AccommodationId != $accommodationId && isset($occupationalRuleData['SeasonsOR'][0]['MinimumNights']) && $occupationalRuleData['SeasonsOR'][0]['MinimumNights'] < $longTermRentalsPeriod) { 
				$matchingProperties[] = $accommodation;
			}
		}
		if (count($matchingProperties) < 10) {
			foreach ($accommodations as $accommodation) {
				$currentID = (int)$accommodation->AccommodationId;
				$occupationalRuleData = getOccupationalRulesFeeds($currentID, $accommodation->OccupationalRuleId);
				if ((int)$accommodation->PeopleCapacity >= $minOccupants  && (int)$accommodation->Bedrooms >= $minBedrooms && (int)$accommodation->AccommodationId != $accommodationId && isset($occupationalRuleData['SeasonsOR'][0]['MinimumNights']) && $occupationalRuleData['SeasonsOR'][0]['MinimumNights'] < $longTermRentalsPeriod) {
					$matchingProperties[] = $accommodation;
					if (count($matchingProperties) >= 10) {
						break;
					}
				}
			}
		}
	}
	return $matchingProperties;
}
function get_image_url($url) {
	$upload_dir = wp_upload_dir();
	$file = str_replace('http://img.crs.itsolutions.es', $upload_dir['basedir'], $url);
	$fileurl = str_replace('http://img.crs.itsolutions.es', $upload_dir['baseurl'], $url);
	if (!file_exists($file)) {
		wp_mkdir_p(dirname($file));
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_HEADER, 0); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_URL, $url); 
		$imgdata = curl_exec($ch); 
		curl_close($ch);
		file_put_contents($file, $imgdata);
	}
	return $fileurl;
}

function getBookingUrl($dateFrom,$dateTo,$childrenNumber,$adultsNumber){
	$booking_url_queries = '?FRMEntrada=' . date('d/m/Y', strtotime($dateFrom)) . '&FRMSalida=' . date('d/m/Y', strtotime($dateTo)) . '&FRMAdultos=' . (int)$adultsNumber;
					if (!empty($childrenNumber)) {
						$booking_url_queries .= '&FRMNinyos=' . (int)$childrenNumber;
						if ($childrenNumber > 0) {
							$childAges = array();
							for ($i = 1; $i <= $childrenNumber; $i++) {
								$key = 'Child_' . $i . '_Age';
								$childAge = isset($_REQUEST[$key]) ? $_REQUEST[$key] : '';
								
								$childAges[] = $childAge; // Double $$ is used to access the variable variable
							}
							
							$booking_url_queries .= '&EdadesNinyos=' . implode(';', $childAges);
						}
					}
	return $booking_url_queries;
}
function GetHTMLFeedsForAccommodation($company, $accommodationId, $accID, $dateFrom, $dateTo, $dateToAPI, $result_IsAvailable, $result_GetBookingPrice, $language, $adultsNumber, $getBookingPrice_info, $childrenNumber, $childAges) {
	global $wpdb, $descriptionsData;
	$descriptionsData = getDescriptionsFeeds($accommodationId, $language);
	$accommodationData = getAccommodationFeeds($company, $accommodationId, $accID, $dateFrom, $dateTo, $dateToAPI, $result_IsAvailable, $result_GetBookingPrice, $language);
	$priceModifierData = getPriceModifierFeeds($accommodationId, $accommodationData['PriceModifierId']);
	$occupationalRuleData = getOccupationalRulesFeeds($accommodationId, $accommodationData['OccupationalRuleId']);
	$availabilityData = AvailabilityFeedsPHP($accommodationId, $language);
	$longTermRentalsPeriod = '365'; // 12 months converted to days
	/* if (isset($occupationalRuleData['SeasonsOR'][0]['MinimumNights']) && $occupationalRuleData['SeasonsOR'][0]['MinimumNights'] < $longTermRentalsPeriod) { */
	if (!empty($accommodationData)) {
		/***************** Get Acommodation meta port video  *****************/
		$accommodation_matterport_video = $wpdb->get_results($wpdb->prepare("SELECT AccommodationMatterportVideo FROM wp_accommodations WHERE AccommodationId = %s", $accommodationId), ARRAY_A);
		/***************** Get Acommodation meta port video ends *****************/
		/******* BANNER *******/
		$output_banner = '<div class="box-section-full">';
		// Start Breadcrumbs
		$output_banner .= '<div class="container-seo">';
		$output_banner .= '<div class="breadcrumb">';
		$output_banner .= '<a href="javascript:void(0);" onclick="goBack()" class="m-right" title="Go Back" aria-label="Go Back"><i class="fas fa-arrow-left icon-back"></i> Back</a> <div class="breadcrumb-inner-mobile-top">| <a href="/" class="m-left" title="Home Page" aria-label="Home Page"><i class="fas fa-home icon-homepage"></i></a>';
		$locationData = array(
			'CountryName' => 'CountryCode',
			'RegionName' => 'RegionCode',
			'ProvinceName' => 'ProvinceCode',
			'CityName' => 'CityCode',
			'LocalityName' => 'LocalityCode',
			'DistrictName' => 'DistrictCode'
		);
		$displayedNames = array(); // To keep track of already displayed names
		$locationCount = count($locationData) - 1;
		$currentLocation = 0;
		foreach ($locationData as $nameKey => $codeKey) {
			$currentLocation++;
			// Check for "Sin especificar" which is Spanish for "Not Specified"
			if (isset($descriptionsData['Location'][$nameKey]) && !in_array($descriptionsData['Location'][$nameKey], $displayedNames) && ($descriptionsData['Location'][$nameKey] !== "Sin especificar" && $descriptionsData['Location'][$nameKey] !== "Not specified")) {
				// Convert "Irlanda" to "Ireland"
				if ($descriptionsData['Location'][$nameKey] === "Irlanda") {
					$descriptionsData['Location'][$nameKey] = "Ireland";
				}
				if ($descriptionsData['Location'][$nameKey] === "Ireland") {
					$location_url_change = '/search-ireland/';
				} else {
					$location_url_change = '/search-ireland/' . str_replace(' ', '-', strtolower($descriptionsData['Location'][$nameKey])) . '/';
				}
				if ($currentLocation === $locationCount) {
					// This is the last item in the loop
					$displayedNamesCount = count($displayedNames);
					if ($displayedNamesCount >= 1) {
						// Get the last two displayed names
						$lastTwoNames = array_slice($displayedNames, -1);
						// Create the last part of the URL by concatenating the names with a slash
						$lastPartOfUrl = implode('/', array_map(function($name) {
							return str_replace(' ', '-', strtolower($name));
						}, $lastTwoNames));
						// Append this last part to the URL for the last link only
						$location_url_change = '/search-ireland/' . $lastPartOfUrl . '/' . str_replace(' ', '-', strtolower($descriptionsData['Location'][$nameKey])) . '/';
					}
				}
				if ($descriptionsData['Location'][$nameKey] !== "Achill Sound") {
					$output_banner .= ' › <a href="' . $location_url_change . '" title="' . $descriptionsData['Location'][$nameKey] . '" aria-label="' . $descriptionsData['Location'][$nameKey] . ' Search Page"><span>' . $descriptionsData['Location'][$nameKey] . '</span></a>';
				}
				$displayedNames[] = $descriptionsData['Location'][$nameKey];
			}
		}
		$output_banner .= ' › ' . $descriptionsData['AccommodationName']; // Display accommodation name at the end
		$output_banner .= '</div>';
		$output_banner .= '<div class="shareicons"><a href="javascript:void(0);" class="sharepop" title="Social Share Popup" aria-label="Social Share Popup"><span class="elementor-icon-list-icon shareicon-marginr5"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="20" viewBox="0 0 16 20" fill="none"><path d="M13.7998 6.00975H11.7983C11.3935 6.00975 11.0649 6.3378 11.0649 6.7431C11.0649 7.14839 11.3935 7.47644 11.7983 7.47644H13.8003C14.2046 7.47644 14.5337 7.80547 14.5337 8.20979V17.8C14.5337 18.2043 14.2046 18.5333 13.8003 18.5333H2.7762C2.37189 18.5333 2.04286 18.2043 2.04286 17.8V8.20979C2.04286 7.80547 2.37189 7.47644 2.7762 7.47644H4.77823C5.18352 7.47644 5.51157 7.14839 5.51157 6.7431C5.51157 6.3378 5.18352 6.00975 4.77823 6.00975H2.7762C1.56325 6.00975 0.576172 6.99683 0.576172 8.20979V17.8C0.576172 19.0129 1.56325 20 2.7762 20H13.8003C15.0133 20 16.0003 19.0129 16.0003 17.8V8.20979C15.9998 6.99635 15.0133 6.00975 13.7998 6.00975ZM5.55362 4.52938L7.57911 2.50388V12.1586C7.57911 12.5634 7.90716 12.8919 8.31245 12.8919C8.71775 12.8919 9.0458 12.5634 9.0458 12.1586V2.50388L11.0713 4.52938C11.2145 4.67262 11.4023 4.744 11.59 4.744C11.7777 4.744 11.9655 4.67262 12.1087 4.52938C12.3952 4.24337 12.3952 3.77843 12.1087 3.49243L8.83166 0.21487C8.54468 -0.0716233 8.08072 -0.0716233 7.79423 0.21487L4.51667 3.49243C4.23018 3.77843 4.23018 4.24337 4.51667 4.52938C4.80316 4.81587 5.26712 4.81587 5.55362 4.52938Z" fill="#777777"></path></svg></span><span class="elementor-icon-list-text">Share</span></a> <a href="javascript:void(0);" class="favouritesPropS shareicon-marginlr5" title="Save as Favourite" aria-label="Save as Favourite"><i class="far fa-heart iconheart"></i> Save</a></div>';
		$output_banner .= '</div>';
		$output_banner .= '</div>';
		// End Breadcrumbs
		// Start #gallery_full
		$output_banner .= '<div id="gallery_full">';
		$output_banner .= '<div id="photos_section_e">';
			// Start #photo_container
			$output_banner .= '<div id="photo_container" class="grid-container">';
				// Start #galleryGrid
				$output_banner .= '<div id="galleryGrid" class="photo-gallery count-images-20 swiper-wrapper">';
				foreach ($descriptionsData['Images'] as $index => $image) {
					if ($index === 0) {
						$output_banner .= '<div class="galleryGrid__cover swiper-slide">';
					} else {
						$output_banner .= '<div>';
					}
					$AdaptedURI = get_image_url($image['AdaptedURI']);
					$OriginalURI = get_image_url($image['OriginalURI']);
					$output_banner .= '<a href="' . $OriginalURI . '" id="ft_' . $index . '" data-size="' . $image['Type'] . '" data-lightbox="mygallery" title="' . $image['Name'] . '" aria-label="' . $image['Name'] . '">';
					$output_banner .= '<img src="' . $OriginalURI . '" data-src="' . $OriginalURI . '" loading="lazy" title="' . $image['Name'] . '" alt="' . $image['Name'] . '">';
					$output_banner .= '</a>';
					if ($index === 0) {
						//$output_banner .= '<span class="watermark-newretreat"></span>';
					}
					$output_banner .= '</div>';
				}
				$output_banner .= '</div>';
				$output_banner .= '<div class="swiper-button-prev"></div>';
				$output_banner .= '<div class="swiper-button-next"></div>';
				// End #galleryGrid
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
		$output_topheader .= '<div class="breadcrumb-inner-mobile-content">';
		$output_topheader .= '<div class="breadcrumb">';
		$output_topheader .= '<a href="/" title="Home Page" aria-label="Home Page"><i class="fas fa-home icon-homepage"></i></a>';
		$locationData = array(
			'CountryName' => 'CountryCode',
			'RegionName' => 'RegionCode',
			'ProvinceName' => 'ProvinceCode',
			'CityName' => 'CityCode',
			'LocalityName' => 'LocalityCode',
			'DistrictName' => 'DistrictCode'
		);
		$displayedNames = array(); // To keep track of already displayed names
		$locationCount = count($locationData) - 1;
		$currentLocation = 0;
		foreach ($locationData as $nameKey => $codeKey) {
			$currentLocation++;
			// Check for "Sin especificar" which is Spanish for "Not Specified"
			if (isset($descriptionsData['Location'][$nameKey]) && !in_array($descriptionsData['Location'][$nameKey], $displayedNames) && ($descriptionsData['Location'][$nameKey] !== "Sin especificar" && $descriptionsData['Location'][$nameKey] !== "Not specified")) {
				// Convert "Irlanda" to "Ireland"
				if ($descriptionsData['Location'][$nameKey] === "Irlanda") {
					$descriptionsData['Location'][$nameKey] = "Ireland";
				}
				if ($descriptionsData['Location'][$nameKey] === "Ireland") {
					$location_url_change = '/search-ireland/';
				} else {
					$location_url_change = '/search-ireland/' . str_replace(' ', '-', strtolower($descriptionsData['Location'][$nameKey])) . '/';
				}
				if ($currentLocation === $locationCount) {
					// This is the last item in the loop
					$displayedNamesCount = count($displayedNames);
					if ($displayedNamesCount >= 1) {
						// Get the last two displayed names
						$lastTwoNames = array_slice($displayedNames, -1);
						// Create the last part of the URL by concatenating the names with a slash
						$lastPartOfUrl = implode('/', array_map(function($name) {
							return str_replace(' ', '-', strtolower($name));
						}, $lastTwoNames));
						// Append this last part to the URL for the last link only
						$location_url_change = '/search-ireland/' . $lastPartOfUrl . '/' . str_replace(' ', '-', strtolower($descriptionsData['Location'][$nameKey])) . '/';
					}
				}
				if ($descriptionsData['Location'][$nameKey] !== "Achill Sound") {
					$output_topheader .= ' › <a href="' . $location_url_change . '" title="' . $descriptionsData['Location'][$nameKey] . '" aria-label="' . $descriptionsData['Location'][$nameKey] . ' Search Page"><span>' . $descriptionsData['Location'][$nameKey] . '</span></a>';
				}
				$displayedNames[] = $descriptionsData['Location'][$nameKey];
			}
		}
		$output_topheader.= ' › ' . $descriptionsData['AccommodationName']; // Display accommodation name at the end
		$output_topheader .= '</div>';
		$output_topheader .= '</div>';
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
				$averageRatingConverted = sprintf("%d%d", $integerPart, $decimalPart);
			}
			$output_topheader .= '<div class="reviewsContentRates-Details">';
			$output_topheader .= '<a href="#reviewsAnchor" id="linkToAccordionReviews">';
			$output_topheader .= '<div class="star-ratings' . $averageRatingConverted . '" role="img" aria-label="Rating of this property out of 5"></div>';
			$output_topheader .= '<div class="reviewsAmt-single">' . $totalReviews . ' review' . ($totalReviews != 1 ? 's' : '') . '</div>';
			//$output_topheader .= '<div class="idProp">Property ID: #' . $accommodationData['AccommodationId'] .'</div>';
			$output_topheader .= '</a>';
			$output_topheader .= '</div>';
		} else {
			$output_topheader .= '<div class="reviewsContentRates-Details">';
			$output_topheader .= '<div class="star-ratings0" role="img" aria-label="Rating of this property out of 5"></div>';
			$output_topheader .= '<div class="reviewsAmt-single">No reviews yet</div>';
			//$output_topheader .= '<div class="idProp">Property ID: #' . $accommodationData['AccommodationId'] .'</div>';
			$output_topheader .= '</div>';
		}
		// End .reviewsContentRates
		$displayedRNames = array();
		$reversedNames = array_reverse($locationData, true);
		$output_address = '';
		foreach ($reversedNames as $nameRLKey => $codeRLKey) {
			if (isset($descriptionsData['Location'][$nameRLKey]) && !in_array($descriptionsData['Location'][$nameRLKey], $displayedRNames) && ($descriptionsData['Location'][$nameRLKey] !== "Sin especificar" && $descriptionsData['Location'][$nameRLKey] !== "Not specified")) {
				if (!empty($output_address)) {
					$output_address .= ', ';
				}
				// Convert "Irlanda" to "Ireland"
				if ($descriptionsData['Location'][$nameRLKey] === "Irlanda") {
					$descriptionsData['Location'][$nameRLKey] = "Ireland";
				}
				$output_address .= $descriptionsData['Location'][$nameRLKey];
				$displayedRNames[] = $descriptionsData['Location'][$nameRLKey];
			}
		}
		$output_topheader .= '<h1><span class="accommodationName">' . $descriptionsData['AccommodationName'] . '</span>';
		if (!empty($output_address)) {
			$output_topheader .= '<span class="accommodationSubHeader">' . $output_address . '</span>';
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
				if ($serviceDescription === 'Internet Access') {
					$topFeatures[] = '<li class="features-tooltip"><span class="features-tooltip-text">Internet</span> <span class="svg-wifi"></span> <span>Internet</span></li>';
				}
				if ($serviceDescription === 'Pet') {
					$topFeatures[] = '<li class="features-tooltip"><span class="features-tooltip-text">Dog Friendly</span> <span class="svg-pet"></span> <span>Dog Friendly</span></li>';
				}
				if ($serviceDescription === 'Heating') {
					$topFeatures[] = '<li class="features-tooltip"><span class="features-tooltip-text">Heating</span> <span class="svg-heating"></span> <span>Heating</span></li>';
				}
				if ($serviceDescription === 'Air conditioning') {
					$topFeatures[] = '<li class="features-tooltip"><span class="features-tooltip-text">Air Conditioning</span> <span class="svg-airconditioning"></span> <span>Air Conditioning</span></li>';
				}
				if (stristr($serviceDescription, 'Parking')) {
					$topFeatures[] = '<li class="features-tooltip"><span class="features-tooltip-text span-left">Parking<br /></span> <span class="svg-parking"></span> <span>Parking</span></li>';
				}
				
			}
		}
		if (!empty($accommodationData['Labels']) &&(in_array('electric car charger', $accommodationData['Labels']))) {
			$topFeatures[] = '<li class="features-tooltip"><span class="features-tooltip-text span-left">EV Charger<br /></span> <span class="svg-evcharger"></span> <span>EV Charger</span></li>';
		}
        if (isset($accommodationData['Features']['Bedrooms']) && $accommodationData['Features']['Bedrooms'] !== '') {
            // No. of Beds
            $DoubleBeds = isset($accommodationData['Features']['DoubleBeds']) ? (int)$accommodationData['Features']['DoubleBeds'] : 0;
            $DoubleSofaBed = isset($accommodationData['Features']['DoubleSofaBed']) ? (int)$accommodationData['Features']['DoubleSofaBed'] : 0;
            $QueenBeds = isset($accommodationData['Features']['QueenBeds']) ? (int)$accommodationData['Features']['QueenBeds'] : 0;
            $KingBeds = isset($accommodationData['Features']['KingBeds']) ? (int)$accommodationData['Features']['KingBeds'] : 0;
            $IndividualBeds = isset($accommodationData['Features']['IndividualBeds']) ? (int)$accommodationData['Features']['IndividualBeds'] : 0;
            $IndividualSofaBed = isset($accommodationData['Features']['IndividualSofaBed']) ? (int)$accommodationData['Features']['IndividualSofaBed'] : 0;
            $BunkBeds = isset($accommodationData['Features']['Berths']) ? (int)$accommodationData['Features']['Berths'] : 0;
            $totalDouble = $DoubleBeds + $QueenBeds + $KingBeds;
            $totalDoubleSofa = $DoubleSofaBed;
            $totalSingle = $IndividualBeds + $IndividualSofaBed;
            $totalBeds = $totalDouble + $totalSingle + $totalDoubleSofa + $BunkBeds;
            $totalOccupants = (($totalDouble + $totalDoubleSofa + $BunkBeds) * 2) + $totalSingle;
        }
		if (isset($accommodationData['Features']['PeopleCapacity']) && $accommodationData['Features']['PeopleCapacity'] !== '') {
			$peopleCapacityValue = trim((string) $accommodationData['Features']['PeopleCapacity']);
			if ($peopleCapacityValue !== '' && $peopleCapacityValue !== '0' && $peopleCapacityValue >= $totalOccupants) {
				$numPeopleCapacity = (int) $peopleCapacityValue;
				$topFeatures[] = '<li class="features-tooltip"><span class="features-tooltip-text">' . $numPeopleCapacity . ' Occupant' . ($numPeopleCapacity != 1 ? 's' : '') . '</span> <span class="svg-guests"></span> <span>' . $numPeopleCapacity . '</span></li>';
			} else {
                $numPeopleCapacity = (int) $totalOccupants;
                $topFeatures[] = '<li class="features-tooltip"><span class="features-tooltip-text">' . $numPeopleCapacity . ' Occupant' . ($numPeopleCapacity != 1 ? 's' : '') . '</span> <span class="svg-guests"></span> <span>' . $numPeopleCapacity . '</span></li>';
            }
		} else if (isset($accommodationData['Features']['AdultsCapacity']) && $accommodationData['Features']['AdultsCapacity'] !== '') {
			$adultsCapacityValue = trim((string) $accommodationData['Features']['AdultsCapacity']);
			if ($adultsCapacityValue !== '' && $adultsCapacityValue !== '0' && $adultsCapacityValue >= $totalOccupants) {
				$numAdultsCapacity = (int) $adultsCapacityValue;
				$topFeatures[] = '<li class="features-tooltip"><span class="features-tooltip-text">' . $numAdultsCapacity . ' Occupant' . ($numAdultsCapacity != 1 ? 's' : '') . '</span> <span class="svg-guests"></span> <span>' . $numAdultsCapacity . '</span></li>';
			} else {
                $numAdultsCapacity = (int) $totalOccupants;
                $topFeatures[] = '<li class="features-tooltip"><span class="features-tooltip-text">' . $numAdultsCapacity . ' Occupant' . ($numAdultsCapacity != 1 ? 's' : '') . '</span> <span class="svg-guests"></span> <span>' . $numAdultsCapacity . '</span></li>';
            }
		} else {
			if (isset($accommodationData['Features']['MinimumOccupation']) && $accommodationData['Features']['MinimumOccupation'] !== '') {
				$minimumOccupationValue = trim((string) $accommodationData['Features']['MinimumOccupation']);
				if ($minimumOccupationValue !== '' && $minimumOccupationValue !== '0') {
					$numMinimumOccupation = (int) $minimumOccupationValue;
					$topFeatures[] = '<li class="features-tooltip"><span class="features-tooltip-text">' . $numMinimumOccupation . ' Occupant' . ($numMinimumOccupation != 1 ? 's' : '') . '</span> <div><span class="svg-guests"></span> <span>' . $numMinimumOccupation . '</span></li>';
				}
			}
		}
		if (!empty($accommodationData['Characteristics'])) {
			foreach ($accommodationData['Characteristics'] as $viewCharacteristics) {
				if (in_array($viewCharacteristics, ['TV', 'Garden', 'Terrace'])) {
					if ($viewCharacteristics === 'Garden') {
						$topFeatures[] = '<li class="features-tooltip"><span class="features-tooltip-text span-left">Garden<br /></span> <span class="svg-garden"></span> <span>Garden</span></li>';
					}
					if ($viewCharacteristics === 'Terrace') {
						$topFeatures[] = '<li class="features-tooltip"><span class="features-tooltip-text">Terrace</span> <span class="svg-terrace"></span> <span>Terrace</span></li>';
					}
					if ($viewCharacteristics === 'TV') {
						$topFeatures[] = '<li class="features-tooltip"><span class="features-tooltip-text span-left">TV<br /></span> <span class="svg-tv"></span> <span>TV</span></li>';
					}
				}
			}
			if (isset($accommodationData['Characteristics']['HandicappedFacilities']) && in_array((string)$accommodationData['Characteristics']['HandicappedFacilities'], ['true', '1', 'yes', 'apta-discapacitados'])) {
				$topFeatures[] = '<li class="features-tooltip"><span class="features-tooltip-text">Disabled Facilities</span> <span class="svg-disabledfriendly"></span> <span>Disabled Facilities</span></li>';
			}
		}
		if (!empty($accommodationData['CharacteristicsOptionTitles'])) {
			foreach ($accommodationData['CharacteristicsOptionTitles'] as $optionCharacteristics) {
				if (in_array($optionCharacteristics, ['SwimmingPool', 'TVSatellite', 'HandicappedFacilities', 'Kitchen'])) {
					if ($optionCharacteristics === 'SwimmingPool') {
						$topFeatures[] = '<li class="features-tooltip"><span class="features-tooltip-text">Swimming Pool</span> <span class="svg-pool"></span> <span>Swimming Pool</span></li>';
					}
				}
			}
		}
		if (isset($accommodationData['Features']['Toilets']) && $accommodationData['Features']['Toilets'] !== '') {
			$numToilets = isset($accommodationData['Features']['Toilets']) ? (int)$accommodationData['Features']['Toilets'] : 0;
			$topFeatures[] = '<li class="features-tooltip"><span class="features-tooltip-text span-left">' . $numToilets . ' Toilet' . ($numToilets != 1 ? 's' : '') . '<br></span> <span class="svg-toilets"></span> <span>' . $numToilets . '</span></li>';
		}
		if ((isset($accommodationData['Features']['BathroomWithBathtub']) && $accommodationData['Features']['BathroomWithBathtub'] !== '') || (isset($accommodationData['Features']['BathroomWithShower']) && $accommodationData['Features']['BathroomWithShower'] !== '')) {
			$BathroomWithBathtub_num = isset($accommodationData['Features']['BathroomWithBathtub']) ? (int)$accommodationData['Features']['BathroomWithBathtub'] : 0;
			$BathroomWithShower_num = isset($accommodationData['Features']['BathroomWithShower']) ? (int)$accommodationData['Features']['BathroomWithShower'] : 0;
			$totalBathrooms = $BathroomWithBathtub_num + $BathroomWithShower_num;
			if ($BathroomWithBathtub_num > 0 && $BathroomWithShower_num > 0) {
				$Bathroom_br = '<br>';
			} else {
				$Bathroom_br = '';
			}
			if ($BathroomWithBathtub_num > 0) {
				$BathroomWithBathtub_tooltip = $BathroomWithBathtub_num . ' Bathroom' . ($BathroomWithBathtub_num != 1 ? 's' : '') . ' With Bathtub';
			} else {
				$BathroomWithBathtub_tooltip = '';
			}
			if ($BathroomWithShower_num > 0) {
				$BathroomWithShower_tooltip = $BathroomWithShower_num . ' Bathroom' . ($BathroomWithShower_num != 1 ? 's' : '') . ' With Shower';
			} else {
				$BathroomWithShower_tooltip = '';
			}
			$topFeatures[] = '<li class="features-tooltip"><span class="features-tooltip-text span-left">' . $BathroomWithBathtub_tooltip . $Bathroom_br . $BathroomWithShower_tooltip . '<br></span> <span class="svg-bath"></span> <span>' . $totalBathrooms . '</span></li>';
		}
		if (isset($accommodationData['Features']['Bedrooms']) && $accommodationData['Features']['Bedrooms'] !== '') {
			// No. of Bedrooms
			$Bedrooms = isset($accommodationData['Features']['Bedrooms']) ? (int)$accommodationData['Features']['Bedrooms'] : 0;
			$topFeatures[] = '<li class="features-tooltip"><span class="features-tooltip-text span-left">' . $Bedrooms . ' Bedroom' . ($Bedrooms != 1 ? 's' : '') . '</span> <span class="svg-bedrooms"></span> <span>' . $Bedrooms . '</span></li>';
			// Initialize tooltip variables without line breaks
			$DoubleBeds_tooltip = $KingBeds_tooltip = $QueenBeds_tooltip = $totalSingle_tooltip = $totalSingleSofa_tooltip = $totalDoubleSofa_tooltip = $BunkBeds_tooltip = '';
			// Double Beds
			if ($DoubleBeds > 0) {
			    $DoubleBeds_tooltip = $DoubleBeds . ' Double Bed' . ($DoubleBeds != 1 ? 's' : '');
			    // Add <br> if any subsequent bed type is available
			    if ($KingBeds > 0 || $QueenBeds > 0 || $IndividualBeds > 0 || $IndividualSofaBed > 0 || $totalDoubleSofa > 0 || $BunkBeds > 0) {
			        $DoubleBeds_tooltip .= '<br>';
			    }
			}
			// King Beds
			if ($KingBeds > 0) {
			    $KingBeds_tooltip = $KingBeds . ' King Bed' . ($KingBeds != 1 ? 's' : '');
			    // Add <br> if any subsequent bed type is available
			    if ($QueenBeds > 0 || $IndividualBeds > 0 || $IndividualSofaBed > 0 || $totalDoubleSofa > 0 || $BunkBeds > 0) {
			        $KingBeds_tooltip .= '<br>';
			    }
			}
			// Queen Beds
			if ($QueenBeds > 0) {
			    $QueenBeds_tooltip = $QueenBeds . ' Queen Bed' . ($QueenBeds != 1 ? 's' : '');
			    // Add <br> if any subsequent bed type is available
			    if ($IndividualBeds > 0 || $IndividualSofaBed > 0 || $totalDoubleSofa > 0 || $BunkBeds > 0) {
			        $QueenBeds_tooltip .= '<br>';
			    }
			}
			// Single Beds
			if ($IndividualBeds > 0) {
			    $totalSingle_tooltip = $IndividualBeds . ' Single Bed' . ($IndividualBeds != 1 ? 's' : '');
			    // Add <br> if any subsequent bed type is available
			    if ($IndividualSofaBed > 0 || $totalDoubleSofa > 0 || $BunkBeds > 0) {
			        $totalSingle_tooltip .= '<br>';
			    }
			}
			// Single Sofa Beds
			if ($IndividualSofaBed > 0) {
			    $totalSingleSofa_tooltip = $IndividualSofaBed . ' Single Sofa Bed' . ($IndividualSofaBed != 1 ? 's' : '');
			    // Add <br> if any subsequent bed type is available
			    if ($totalDoubleSofa > 0 || $BunkBeds > 0) {
			        $totalSingleSofa_tooltip .= '<br>';
			    }
			}
			// Double Sofa Beds
			if ($totalDoubleSofa > 0) {
			    $totalDoubleSofa_tooltip = $totalDoubleSofa . ' Double Sofa Bed' . ($totalDoubleSofa != 1 ? 's' : '');
			    // Add <br> if Bunk Beds are available
			    if ($BunkBeds > 0) {
			        $totalDoubleSofa_tooltip .= '<br>';
			    }
			}
			// Bunk Beds
			if ($BunkBeds > 0) {
			    $BunkBeds_tooltip = $BunkBeds . ' Bunk Bed' . ($BunkBeds != 1 ? 's' : '');
			}
			$topFeatures[] = '<li class="features-tooltip"><span class="features-tooltip-text span-left">' . $DoubleBeds_tooltip . $KingBeds_tooltip . $QueenBeds_tooltip . $totalSingle_tooltip . $totalSingleSofa_tooltip . $totalDoubleSofa_tooltip . $BunkBeds_tooltip . '</span> <span class="svg-bed"></span> <span>' . $totalBeds . ' Bed' . ($totalBeds != 1 ? 's' : '') . '</span></li>';
		}
		if (isset($accommodationData['Features']['AreaHousingArea']) || isset($accommodationData['Features']['AreaPlotArea'])) {
			if (isset($accommodationData['Features']['AreaHousingArea']) && $accommodationData['Features']['AreaHousingArea'] !== '' && isset($accommodationData['Features']['AreaHousingUnit']) && $accommodationData['Features']['AreaHousingUnit'] !== '') {
				$areaHousing = (string)$accommodationData['Features']['AreaHousingArea'] . ' ' . (string)$accommodationData['Features']['AreaHousingUnit'];
				$topFeatures[] = '<li class="features-tooltip"><span class="features-tooltip-text">Area Housing: ' . $areaHousing . '</span> <span class="svg-area"></span> <span>' . $areaHousing . '</span></li>';
			}
			if (isset($accommodationData['Features']['AreaPlotArea']) && $accommodationData['Features']['AreaPlotArea'] !== '' && isset($accommodationData['Features']['AreaPlotUnit']) && $accommodationData['Features']['AreaPlotUnit'] !== '') {
				$areaPlot = (string)$accommodationData['Features']['AreaPlotArea'] . ' ' . (string)$accommodationData['Features']['AreaPlotUnit'];
				$topFeatures[] = '<li class="features-tooltip"><span class="features-tooltip-text">Area Plot: ' . $areaPlot . '</span> <span class="svg-area"></span> <span>' . $areaPlot . '</span></li>';
			}
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
		$output_rightsidebar .= '<div class="right-sidebar-mob"><a role="button" tabindex="0" aria-label="Close" href="#" class="closerpop"><i class="eicon-close"></i></a>';
		$output_rightsidebar .= '<div class="response">';
		$output_rightsidebar .= '<div class="sidebar-pricebox">';
		// Start Price
		$display_booking_button = false;
		if ($result_IsAvailable->Available->AvailableCode) {
			if ($getBookingPrice_info === 'yes') {
				if ($result_GetBookingPrice->BookingPrice && $result_IsAvailable->Available->AvailableCode == 1) {
					$display_booking_button = true;
					$descriptionsData = getDescriptionsFeeds($accommodationId, $language);
					$priceConvert_BP = str_replace('EUR', '&euro;', $result_GetBookingPrice->BookingPrice->Currency);
					$priceNF = number_format($result_GetBookingPrice->BookingPrice->RoomOnlyFinal, 2);
					$priceRound_BP = round((float)$result_GetBookingPrice->BookingPrice->RoomOnlyFinal);
					$priceWithoutOffer = number_format($result_GetBookingPrice->BookingPrice->RoomOnlyFinalWithoutOffer, 2);
					$priceWithoutOffer_BP = round((float)$result_GetBookingPrice->BookingPrice->RoomOnlyFinalWithoutOffer);
					$peopleText = ((int)$adultsNumber === 1) ? ' Person' : ' People';
					$bookingStartDateAPI = strtotime($dateFrom);
					$bookingEndDateAPI = strtotime($dateTo);
					$bookingMinimumNightsAPI = ceil(($bookingEndDateAPI - $bookingStartDateAPI) / (60 * 60 * 24));
					$bookingNumNightsText = ((int)$bookingMinimumNightsAPI === 1) ? $bookingMinimumNightsAPI.' night' : $bookingMinimumNightsAPI.' nights';
					//$output_rightsidebar .= '<div class="column-xs-8"><label class="from">From</label><span class="aprice">' . $priceConvert_BP . $priceNF . '</span><span class="perweek">/' . $bookingNumNightsText . '</span><span class="form-instructions">Set dates and guests to see the exact price</span></div>';
					if ($priceWithoutOffer_BP > $priceRound_BP) {
						$actualPrice = $priceWithoutOffer_BP;
						$output_rightsidebar .= '<div class="column-xs-8"><label class="from"></label><span class="aprice"><span class="orignal-price">' . $priceConvert_BP . $actualPrice . '</span><span class="discounted-price"> '. $priceConvert_BP . $priceRound_BP . '</span></span><span class="perweek">/' . $bookingNumNightsText . '</span><span class="form-instructions">Set dates and guests to see the exact price</span></div>';
					} else {
						$output_rightsidebar .= '<div class="column-xs-8"><label class="from"></label><span class="aprice">' . $priceConvert_BP . $priceRound_BP . '</span><span class="perweek">/' . $bookingNumNightsText . '</span><span class="form-instructions">Set dates and guests to see the exact price</span></div>';
					}
					$output_rightsidebar .= '<div class="column-right-button"><a class="button-book-search mobilebookingpop button-book" role="button" aria-label="Book">Book</a></div>';
				} else {
					$output_rightsidebar .= '<div class="column-xs-8"><label class="from">From</label><span class="aprice">' . $accommodationData['RatePrice'] . '</span><span class="perweek">/week</span><span class="form-instructions">Set dates and guests to see the exact price</span></div>';
					$output_rightsidebar .= '<div class="column-right-button"><a class="button-book-search mobilebookingpop" role="button" aria-label="Set Dates &amp; Guests">Set Dates &amp; Guests</a></div>';
				}
			} else {
				if (isset($result_GetBookingPrice->BookingPrice)) {
					$output_rightsidebar .= '<div class="column-xs-8"><label class="from">From</label><span class="aprice">' . $accommodationData['RatePrice'] . '</span><span class="perweek">/week</span><span class="form-instructions">Set dates and guests to see the exact price</span></div>';
					$output_rightsidebar .= '<div class="column-right-button"><a class="button-book-search mobilebookingpop" role="button" aria-label="Set Dates &amp; Guests">Set Dates &amp; Guests</a></div>';
				}
			}
		} else {
			if (isset($result_GetBookingPrice->BookingPrice)) {
				$output_rightsidebar .= '<div class="column-xs-8"><label class="from">From</label><span class="aprice">' . $accommodationData['RatePrice'] . '</span><span class="perweek">/week</span><span class="form-instructions">Set dates and guests to see the exact price</span></div>';
				$output_rightsidebar .= '<div class="column-right-button"><a class="button-book-search mobilebookingpop" role="button" aria-label="Set Dates &amp; Guests">Set Dates &amp; Guests</a></div>';
			}
		}
		if (empty($accommodationData['RatePrice']) || $accommodationData['RatePrice'] == '0' || $accommodationData['RatePrice'] == '0.00') {
			$output_rightsidebar .= '<div id="fomo_content">';
			$output_rightsidebar .= '<span class="form-instructions">';
			$output_rightsidebar .= '<span class="h1title">Enquiry from Website</span>';
			$output_rightsidebar .= 'Please contact us for the price and availability of this accommodation for your application to be put on the waiting list.';
			$output_rightsidebar .= '<a href="' . $descriptionsData['ContactURL'] . '" class="button-contact-sidebar contactParameters" title="Contact Us" aria-label="Contact Us" target="_blank" rel="nofollow">Contact Us</a>';
			$output_rightsidebar .= '</span></div>';
		}
		$output_rightsidebar .= '</div>';
		$dateForm_converted = date('d/m/Y', strtotime($dateFrom));
        $dateTo_converted = date('d/m/Y', strtotime($dateTo));
		$output_rightsidebar .= '<div class="checkinDate-box-outer"><label class="checkinDate-box-label">Dates: </label><span class="checkinDate-box-span">' . $dateForm_converted . ' - ' . $dateTo_converted . '</span></div>';
		$output_rightsidebar .= '</div>';
		$output_rightsidebar .= '</div>';
		$output_rightsidebar .= '<div class="right-sidebar">';
		if (isset($priceModifierData['TotalOffers']) && $priceModifierData['TotalOffers'] > 0) {
			$output_rightsidebar .= '<div class="active-offers"><span class="svg-offers"></span> <span class="text-offers">' . $priceModifierData['TotalOffers'] . ' Active Offer' . ($priceModifierData['TotalOffers'] != 1 ? 's' : '') . '</span></div>';
		}
		$output_rightsidebar .= '<div class="response"><div class="sidebar-pricebox">';
		// Start Price
		if ($result_IsAvailable->Available->AvailableCode) {
			$descriptionsData = getDescriptionsFeeds($accommodationId, $language);
			if ($getBookingPrice_info === 'yes') {
				$todaysDate = date('Y-m-d');
				$fromDate_post = $dateFrom;
				$todayTimestamp = strtotime($todaysDate);
				$otherDateTimestamp = strtotime($fromDate_post);
				$numberOfDays = floor(($otherDateTimestamp - $todayTimestamp) / (60 * 60 * 24));
				$minDaysNotice = intval($availabilityData['MinDaysNotice']);
				$numberOfDays = intval($numberOfDays);
				if ($result_GetBookingPrice->BookingPrice && $result_IsAvailable->Available->AvailableCode == 1 && isset($minDaysNotice) && $minDaysNotice <= $numberOfDays) {
					$priceConvert_BP = str_replace('EUR', '&euro;', $result_GetBookingPrice->BookingPrice->Currency);
					//$priceNF = (float)number_format($result_GetBookingPrice->BookingPrice->RoomOnlyFinal, 2);
					$priceNF = number_format($result_GetBookingPrice->BookingPrice->RoomOnlyFinal);
					$priceRound_BP = round((float)$result_GetBookingPrice->BookingPrice->RoomOnlyFinal);
					$priceWithoutOffer = number_format($result_GetBookingPrice->BookingPrice->RoomOnlyFinalWithoutOffer, 2);
					$priceWithoutOffer_BP = round((float)$result_GetBookingPrice->BookingPrice->RoomOnlyFinalWithoutOffer);
					$peopleText = ((int)$adultsNumber === 1) ? ' Person' : ' People';
					$bookingStartDateAPI = strtotime($dateFrom);
					$bookingEndDateAPI = strtotime($dateTo);
					$bookingMinimumNightsAPI = ceil(($bookingEndDateAPI - $bookingStartDateAPI) / (60 * 60 * 24));
					$bookingNumNightsText = ((int)$bookingMinimumNightsAPI === 1) ? $bookingMinimumNightsAPI.' night' : $bookingMinimumNightsAPI.' nights';
					//$output_rightsidebar .= '<div class="column-xs-4"><label class="from">From</label></div>';
					$output_rightsidebar .= '<div class="column-xs-4"><label class="from"></label></div>';
					if ($priceWithoutOffer_BP > $priceRound_BP) {
						$actualPrice = $priceWithoutOffer_BP;
						$output_rightsidebar .= '<div class="column-xs-8"><span class="aprice"><span class="orignal-price">' . $priceConvert_BP . $actualPrice . '</span><span class="discounted-price"> '. $priceConvert_BP . $priceRound_BP . '</span></span><span class="perweek">/' . $bookingNumNightsText . '</span></div>';
					} else {
						$output_rightsidebar .= '<div class="column-xs-8"><span class="aprice">' . $priceConvert_BP . $priceRound_BP . '</span><span class="perweek">/' . $bookingNumNightsText . '</span></div>';
					}
					$output_rightsidebar .= '</div>';
					$output_rightsidebar .= '<div class="sidebar-priceinfobox">';
					//$output_rightsidebar .= '<div class="column-xs-12"><label>For</label> <span class="iprice">' . (int)$bookingMinimumNightsAPI . '</span><span class="pertype">' . $bookingNumNightsText . '</span></div>';
					//$output_rightsidebar .= '<div class="column-xs-12"><label>For</label> <span class="iprice">' . (int)$adultsNumber . '</span><span class="pertype">' . $peopleText . '</span></div>';
					$booking_url_queries = '?FRMEntrada=' . date('d/m/Y', strtotime($dateFrom)) . '&FRMSalida=' . date('d/m/Y', strtotime($dateTo)) . '&FRMAdultos=' . (int)$adultsNumber;
					if (!empty($childrenNumber)) {
						$booking_url_queries .= '&FRMNinyos=' . (int)$childrenNumber;
						if ($childrenNumber > 0) {
							$childAges = array();
							for ($i = 1; $i <= $childrenNumber; $i++) {
								$key = 'Child_' . $i . '_Age';
								$childAge = isset($_REQUEST[$key]) ? $_REQUEST[$key] : '';
								
								$childAges[] = $childAge; // Double $$ is used to access the variable variable
							}
							
							$booking_url_queries .= '&EdadesNinyos=' . implode(';', $childAges);
						}
					}
				} else {
					if (!empty($accommodationData['RatePrice']) && $accommodationData['RatePrice'] != '0' && $accommodationData['RatePrice'] != '0.00') {
						$output_rightsidebar .= '<div class="column-xs-4"><label class="from">From</label></div>';
						$output_rightsidebar .= '<div class="column-xs-8"><span class="aprice">' . $accommodationData['RatePrice'] . '</span><span class="perweek">/week</span></div>';
					}
				}
			} else {
				if (!empty($accommodationData['RatePrice']) && $accommodationData['RatePrice'] != '0' && $accommodationData['RatePrice'] != '0.00') {
					$output_rightsidebar .= '<div class="column-xs-4"><label class="from">From</label></div>';
					$output_rightsidebar .= '<div class="column-xs-8"><span class="aprice">' . $accommodationData['RatePrice'] . '</span><span class="perweek">/week</span></div>';
				}
			}
		} else {
			if (!empty($accommodationData['RatePrice']) && $accommodationData['RatePrice'] != '0' && $accommodationData['RatePrice'] != '0.00') {
				$output_rightsidebar .= '<div class="column-xs-4"><label class="from">From</label></div>';
				$output_rightsidebar .= '<div class="column-xs-8"><span class="aprice">' . $accommodationData['RatePrice'] . '</span><span class="perweek">/week</span></div>';
			}
		}
		// End Price
		$output_rightsidebar .= '</div></div>';
		$output_rightsidebar .= '<div id="form-container">';
		$descriptionsData = getDescriptionsFeeds($accommodationId, $language);
		if (isset($descriptionsData['BookingURL'])) {
		    $descriptionsData['BookingURL'] = preg_replace(
		        '/^(http:\/\/www\.|https:\/\/www\.)/',
		        'https://bookings.',
		        $descriptionsData['BookingURL']
		    );
		}
		if (isset($descriptionsData['ContactURL'])) {
		    $descriptionsData['ContactURL'] = preg_replace(
		        '/^(http:\/\/www\.|https:\/\/www\.)/',
		        'https://bookings.',
		        $descriptionsData['ContactURL']
		    );
		}
		if (!empty($accommodationData['RatePrice']) && $accommodationData['RatePrice'] != '0' && $accommodationData['RatePrice'] != '0.00') {
			$output_rightsidebar .= '<span class="form-instructions">* You have to set the dates and guests to see the exact price.</span>';
			$output_rightsidebar .= '<div id="fomo_content"></div>';
			$currentURL_Form = esc_url($_SERVER['REQUEST_URI']);
			$output_rightsidebar .= '<form name="formReserveAccommodation" id="formReserveAccommodation" class="formReserveAccommodationClass" method="POST" action="">';
			$output_rightsidebar .= '<fieldset id="miniform_online">';
			$output_rightsidebar .= '<div id="form_minRespo">';
			$output_rightsidebar .= '<div class="dates">';
			$output_rightsidebar .= '<label for="daterange" class="label-title">Check In/Out Dates</label>';
			$output_rightsidebar .= '<span class="sidebar-input svg-calendar-before">';
			$currentDate = new DateTime();  // Current date
			$earliestStartDate = clone $currentDate;
			$earliestStartDate->modify('+' . $availabilityData['MinDaysNotice'] . ' days');
			$minimumNightsRequired = isset($occupationalRuleData['SeasonsOR'][0]['MinimumNights']) ? $occupationalRuleData['SeasonsOR'][0]['MinimumNights'] : 0;
			$firstAvailableRange_begin = null;
			if (isset($availabilityData['availableRanges']) && !empty($availabilityData['availableRanges'])) {
				foreach ($availabilityData['availableRanges'] as $range) {
					$rangeStartDate = DateTime::createFromFormat('Y-m-d', $range['start']);
					$rangeEndDate = DateTime::createFromFormat('Y-m-d', $range['end']);
					if ($rangeEndDate >= $earliestStartDate) {
						$bookingStartDate = ($rangeStartDate > $earliestStartDate) ? $rangeStartDate : $earliestStartDate;
						$potentialEndDate = clone $bookingStartDate;
						$potentialEndDate->modify("+$minimumNightsRequired days");
						if ($potentialEndDate <= $rangeEndDate) {
							$firstAvailableRange_begin = array(
								'start' => $bookingStartDate->format('Y-m-d'),
								'end' => $potentialEndDate->format('Y-m-d')
							);
							break;
						}
					}
				}
			}
			// if ($firstAvailableRange_begin) {
			// 	$fromDate_input = $firstAvailableRange_begin['start'];
			// 	$fromDate = $fromDate_input;
			// 	$toDate_input = $firstAvailableRange_begin['end'];
			// 	$dateTo = $toDate_input;
			// } else {
			// 	$fromDate_input = $earliestStartDate->format('d/m/Y');
			// 	$fromDate = $earliestStartDate->format('Y-m-d');
			// 	$toDate_input = $earliestStartDate->modify("+$minimumNightsRequired days")->format('d/m/Y');
			// 	$dateTo = $toDate_input;
			// }
			$fromDate_input = $dateFrom;
			$toDate_input = $dateTo;
			$fromDate_post = $fromDate_input;
			$toDate_post = $toDate_input;
			$output_rightsidebar .= '<input id="daterange" type="text" name="daterange" placeholder="From - To" value="'. $fromDate_input . ' - ' . $toDate_input . '" aria-label="Check In and Check Out Date Range" readonly />';
			// if (isset($_POST['dateFrom'])) {
			// 	$fromDate_post = $_POST['dateFrom'];
			// } else if (isset($_GET['dateFrom'])) {
			// 	$fromDate_post = $_GET['dateFrom'];
			// } else if (isset($fromDate_input)) {
			// 	$fromDate_post = $fromDate_input;
			// } else {
			// 	$fromDate_post = $dateFrom;
			// }
			
			$output_rightsidebar .= '<input type="hidden" name="dateFrom" id="dateFrom" value="' . $fromDate_post . '" aria-label="Date From" />';
			// if (isset($_POST['dateTo'])) {
			// 	$toDate_post = $_POST['dateTo'];
			// } else if (isset($_GET['dateTo'])) {
			// 	$toDate_post = $_GET['dateTo'];
			// } else if (isset($toDate_input)) {
			// 	$toDate_post = $toDate_input;
			// } else {
			// 	$toDate_post = $dateTo;
			// }
			$adultNumber = 1;
			if (isset($_POST['AdultNum'])) {
				$adultNumber = $_POST['AdultNum'];
			} else if(isset($_GET['AdultNum'])) {
				$adultNumber = $_GET['AdultNum'];
			}
			$childrenNumber = 0;
			if (isset($_POST['ChildrenNum'])) {
				$childrenNumber = $_POST['ChildrenNum'];
			} else if(isset($_GET['ChildrenNum'])) {
				$childrenNumber = $_GET['ChildrenNum'];
			}
			$output_rightsidebar .= '<input type="hidden" name="dateTo" id="dateTo" value="' . $toDate_post . '" aria-label="Date To" />';
			$output_rightsidebar .= '</span>';
			$output_rightsidebar .= '</div>';
			// Start Occupancy Settings
			$output_rightsidebar .= '<div class="occupancy">';
			$output_rightsidebar .= '<label for="occupancy-box" class="label-title">Add Guests</label>';
			$output_rightsidebar .= '<span class="select_online">';
			$output_rightsidebar .= '<div class="personas_select">';
			$output_rightsidebar .= '<span class="sidebar-input svg-guests-before">';
			$output_rightsidebar .= '<input id="occupancy-box" class="occupancy-box" type="text" value="1 Adult - 0 Children" aria-label="Guests" readonly />';
			$output_rightsidebar .= '<div id="occupancy-dropdown" class="occupancy-dropdown occupancy-hidden">';
			$output_rightsidebar .= '<div class="adult people adults-container">';
			$output_rightsidebar .= '<div class="adults-label">';
			$output_rightsidebar .= '<label for="AdultNum" class="label-visible">Adults</label>';
			$output_rightsidebar .= '<small>Ages 13 or above</small>';
			$output_rightsidebar .= '</div>';
			$output_rightsidebar .= '<div class="adults-input">';
			$output_rightsidebar .= '<span id="plusminus-a-minus" class="plusminus handleMinus">-</span>';
			$output_rightsidebar .= '<input type="number" name="AdultNum" id="AdultNum" class="num" min="1" max="20" step="1" aria-valuemin="1" aria-valuemax="20" aria-valuenow="1" value="' . $adultNumber . '" aria-label="Number of Adults" readonly />';
			$output_rightsidebar .= '<span id="plusminus-a-plus" class="plusminus handlePlus">+</span>';
			$output_rightsidebar .= '</div>';
			$output_rightsidebar .= '</div>';
			$output_rightsidebar .= '<div class="childs people childs-container">';
			$output_rightsidebar .= '<div class="childs-label">';
			$output_rightsidebar .= '<label for="ChildrenNum" class="label-visible">Children</label>';
			$output_rightsidebar .= '<small>Ages 0-12</small>';
			$output_rightsidebar .= '</div>';
			$output_rightsidebar .= '<div class="childs-input">';
			$output_rightsidebar .= '<span id="plusminus-c-minus" class="plusminus handleMinus">-</span>';
			$output_rightsidebar .= '<input type="number" name="ChildrenNum" id="ChildrenNum" class="num" min="0" max="6" step="1" aria-valuemin="0" aria-valuemax="6" aria-valuenow="0" value="' .$childrenNumber. '" aria-label="Number of Children" readonly />';
			$output_rightsidebar .= '<span id="plusminus-c-plus" class="plusminus handlePlus">+</span>';
			$output_rightsidebar .= '</div>';
			$output_rightsidebar .= '</div>';
			$childAges = array();
			for ($i = 1; $i <= 6; $i++) :
				$output_rightsidebar .= '<div class="child-age-selects">';
				$output_rightsidebar .= '<div class="child' . $i . ' people">';
				$output_rightsidebar .= '<div class="childs-label">';
				$output_rightsidebar .= '<label for="Child_' . $i . '_Age" class="label-visible">Child ' . $i . ' Age</label>';
				$output_rightsidebar .= '</div>';
				$output_rightsidebar .= '<div class="childsage-input">';
				$output_rightsidebar .= '<select id="Child_' . $i . '_Age" class="select children_age_select" name="Child_' . $i . '_Age" aria-label="Child ' . $i . ' Age">';
				$output_rightsidebar .= '<option value="">Select Age</option>';
				for ($j = 0; $j <= 12; $j++) :
					$selected_cainput = (isset($_REQUEST['Child_' . $i . '_Age']) && $_REQUEST['Child_' . $i . '_Age'] === (string)$j) ? 'selected="selected"' : '';
					$output_rightsidebar .= '<option value="' . $j . '" ' . $selected_cainput . '>' . ($j === 0 ? '0 years' : $j . ' year' . ($j != 1 ? 's' : '')) . '</option>';
				endfor;
				$output_rightsidebar .= '</select>';
				$output_rightsidebar .= '</div>';
				$output_rightsidebar .= '</div>';
				$output_rightsidebar .= '</div>';
			endfor;
			// End Occupancy Settings
			$output_rightsidebar .= '</div>';
			$output_rightsidebar .= '</span>';
			$output_rightsidebar .= '</div>';
			$output_rightsidebar .= '</span>';
			$output_rightsidebar .= '</div>';
			if (!empty($booking_url_queries)) {
				$hideClassPrice = 'priceDisplayNone';
			} else {
				$hideClassPrice = '';
			}
			$output_rightsidebar .= '<div id="bt_act" class="botonR_fondo' . $hideClassPrice . '"><input type="hidden" name="prop_id" id="prop_id" value="' . $accommodationId . '" /><input type="hidden" name="acc_id" id="acc_id" value="' . $accID . '" />';
			if ($result_IsAvailable->Available->AvailableCode == 1 && $getBookingPrice_info === 'yes') {
				//$output_rightsidebar .= '<a href="' . $descriptionsData['ContactURL'] . '" class="button-contact-sidebar" title="Contact Agency" aria-label="Contact Us" target="_blank" rel="nofollow">Contact Us</button>';
			} else if ($result_IsAvailable->Available->AvailableCode != 1 && $getBookingPrice_info === 'yes' && $result_IsAvailable->Available->AvailableMessage === 'The compulsory arrival date is not fulfilled') {
				$output_rightsidebar .= '<button class="button-book-search" type="input" name="reserve-submit-button" id="reserve-submit-button" aria-label="Check Availability">Edit Dates</button>';
			} else if ($result_IsAvailable->Available->AvailableMessage === 'Under petition') {
				$output_rightsidebar .= '<a id="reserve-contact-us" class="button-contact-sidebar contactParameters" href="' . $descriptionsData['ContactURL'] . '" title="Contact Agency" aria-label="Contact Us" target="_blank" rel="nofollow">Contact Us</a>';
			} else {
				$output_rightsidebar .= '<button class="button-book-search" type="input" name="reserve-submit-button" id="reserve-submit-button" aria-label="Check Availability">Check Availability</button>';
			}
			$output_rightsidebar .= '</div>';
			if (!empty($booking_url_queries)) {
				$output_rightsidebar .= '<div class="sidebar-priceinfobox"><div class="bookingb">';
				$output_rightsidebar .= '<a href="' . $descriptionsData['BookingURL'] . $booking_url_queries . '" class="button-book" title="Book Accommodation" aria-label="Book Accommodation" rel="nofollow">Book</a>';
				$output_rightsidebar .= '</div></div>';
			}
			$output_rightsidebar .= '</div>';
			$output_rightsidebar .= '</fieldset>';
			$output_rightsidebar .= '</form>';
			$output_rightsidebar .= '<div id="sidebar-ajax-loader"></div>';
		} else {
			$output_rightsidebar .= '<div id="fomo_content">';
			$output_rightsidebar .= '<span class="form-instructions">';
			$output_rightsidebar .= '<span class="h1title">Enquiry from Website</span>';
			$output_rightsidebar .= 'Please contact us for the price and availability of this accommodation for your application to be put on the waiting list.';
			$output_rightsidebar .= '<a href="' . $descriptionsData['ContactURL'] . '" class="button-contact-sidebar contactParameters" title="Contact Us" aria-label="Contact Us" target="_blank" rel="nofollow">Contact Us</a>';
			$output_rightsidebar .= '</span></div>';
		}
		$output_rightsidebar .= '<span class="border-bottom1px"></span>';
		$output_rightsidebar .= '<div class="needinghelp">';
		$output_rightsidebar .= '<span class="svg-chat"></span>';
		$output_rightsidebar .= '<span class="text-box">Need help? <span>Get in Touch</span></span>';
		$output_rightsidebar .= '</div>';
		$output_rightsidebar .= '<div class="needinghelp-buttons">';
		$output_rightsidebar .= '<a class="button-needinghelp" href="https://api.whatsapp.com/send?phone=353858054978" target="_blank">WhatsApp</a> <a class="button-needinghelp needhelp" id="contactPhoneButton" href="javascript:void(0)">Phone</a> <a class="button-needinghelp contactParameters" href="' . $descriptionsData['ContactURL'] .'" target="_blank" rel="nofollow">Contact</a>';
		$output_rightsidebar .= '</div>';
		AvailabilityFeeds($accommodationId);
		if (!empty($accommodationData['RatePrice']) && $accommodationData['RatePrice'] != '0') {
			$output_rightsidebar .= '<div id="errormessage">';
			$todaysDate = date('Y-m-d');
			$todayTimestamp = strtotime($todaysDate);
			$otherDateTimestamp = strtotime($fromDate_post);
			$numberOfDays = floor(($otherDateTimestamp - $todayTimestamp) / (60 * 60 * 24));
			$minDaysNotice = intval($availabilityData['MinDaysNotice']);
			$numberOfDays = intval($numberOfDays);
			if (isset($minDaysNotice) && $minDaysNotice > $numberOfDays) {
				//$output_rightsidebar .= '<span class="form-error-results">This property requires a minimum notice of ' . $availabilityData['MinDaysNotice'] . ' day' . ($availabilityData['MinDaysNotice'] != 1 ? 's' : '') . '</span>';
				$output_rightsidebar .= '<span class="form-error-results">Please contact us direct to check the availability and price for these dates</span>';
			} else if ($result_IsAvailable->Available->AvailableCode === 0 && $getBookingPrice_info === 'yes') {
				$output_rightsidebar .= '<span class="form-error-results">The accommodation is not available on these dates. Please contact us to discuss your needs</span>';
			} else if ($result_IsAvailable->Available->AvailableCode === -5 && $getBookingPrice_info === 'yes') {
				$errorMessageSet = false;
				// Handle the check-in and check-out rules
				$occupationalRules = isset($result_IsAvailable->OccupationalRule) ? $result_IsAvailable->OccupationalRule : [];
				$occupationalRules = is_array($occupationalRules) ? $occupationalRules : [$occupationalRules];
				foreach ($occupationalRules as $occRule) {
				    // Handle check-in rules
				    if (isset($occRule->CheckInDays->WeekDay)) {
				        $allowedDays = (array)$occRule->CheckInDays->WeekDay;
				        $allowedDays = array_map('strtoupper', $allowedDays);
				        $checkInDateDay = strtoupper(date('l', strtotime($dateFrom)));
				        if (!in_array($checkInDateDay, $allowedDays)) {
				            $checkInDaysFormatted = implode(', ', array_map('ucwords', array_map('strtolower', $allowedDays)));
				            $output_rightsidebar .= '<span class="form-error-results">The booking during this period is only possible with an arrival on ' . ucwords($checkInDaysFormatted) . '</span>';
				            $errorMessageSet = true;
				            break;
				        }
				    }
				    // Handle check-out rules, only if no previous error message has been set
				    if (!$errorMessageSet && isset($occRule->CheckOutDays->WeekDay)) {
				        $allowedDays = (array)$occRule->CheckOutDays->WeekDay;
				        $allowedDays = array_map('strtoupper', $allowedDays);
				        $checkOutDateDay = strtoupper(date('l', strtotime($dateTo)));
				        if (!in_array($checkOutDateDay, $allowedDays)) {
				            $checkOutDaysFormatted = implode(', ', array_map('ucwords', array_map('strtolower', $allowedDays)));
				            $output_rightsidebar .= '<span class="form-error-results">The booking during this period is only possible with a departure on ' . ucwords($checkOutDaysFormatted) . '</span>';
				            $errorMessageSet = true;
				            break;
				        }
				    }
				}
				// Minimum stay requirement only if no previous error message has been set
				if (!$errorMessageSet) {
				    $occupationalRuleData = getOccupationalRulesFeeds($accommodationId, $accommodationData['OccupationalRuleId']);
				    $minStay = '';
				    if (!empty($result_IsAvailable->OccupationalRule->MinimumNights)) {
				        $minStay = $result_IsAvailable->OccupationalRule->MinimumNights;
				    } else {
				        if (isset($occupationalRuleData['SeasonsOR'][0]['MinimumNights']) && $occupationalRuleData['SeasonsOR'][0]['MinimumNights'] != '') {
				            $minStay = $occupationalRuleData['SeasonsOR'][0]['MinimumNights'];
				        }
				    }
				    if ($minStay !== '') {
				        $output_rightsidebar .= '<span class="form-error-results">This property requires a ' . $minStay . ' night minimum stay';
				    }
				}
			} else if ($result_IsAvailable->Available->AvailableCode === -7 && $getBookingPrice_info === 'yes') {
				// Calculate the difference in the time and if it exceeds 2 months to display a message
	        	$bookingStartDate = new DateTime($dateFrom);
				$bookingEndDate = new DateTime($dateTo);
				$interval = $bookingStartDate->diff($bookingEndDate);
				// Check if the difference is 2 months or more
				if ($interval->m >= 2 || $interval->y > 0) {
			    	$exceeds2Months = 'The number of nights booked exceeds the maximum permitted. Please contact us to discuss your needs';
				} else {
					if ($interval->days > 21) {
						$exceeds2Months = 'For bookings of longer than 21 days please contact us offline by email or by telephone';
					} else {
				    	$exceeds2Months = 'The number of nights booked exceeds the maximum permitted';
					}
				}
	            $output_rightsidebar .= '<span class="form-error-results">' . $exceeds2Months . '</span>';
			} else if ($result_IsAvailable->Available->AvailableCode === -8 && $getBookingPrice_convert === 'yes') {
				$output_rightsidebar .= '<span class="form-error-results">Please contact us direct to check the availability and price for these dates</span>';
			} else if ($result_IsAvailable->Available->AvailableCode === -9 && $getBookingPrice_convert === 'yes') {
				$output_rightsidebar .= '<span class="form-error-results">The accommodation is no longer available</span>';
			} else if ($result_IsAvailable->Available->AvailableCode === -99 && $getBookingPrice_info === 'yes') {
				$output_rightsidebar .= '<span class="form-error-results">The number of occupants exceeds the maximum permitted</span>';
			} else if (!$result_IsAvailable->Available->AvailableCode && $getBookingPrice_info === 'yes') {
				$output_rightsidebar .= '<span class="form-error-results">Dates do not match for your selection, please select different dates</span>';
			} else if ($result_IsAvailable->Available->AvailableCode != 1 && $getBookingPrice_info === 'yes' && $result_IsAvailable->Available->AvailableMessage === 'The compulsory departure date is not fulfilled') {
				$occupationalRules = isset($result_IsAvailable->OccupationalRule) ? $result_IsAvailable->OccupationalRule : [];
				$occupationalRules = is_array($occupationalRules) ? $occupationalRules : [$occupationalRules];
				foreach ($occupationalRules as $occRule) {
				    // Handle check-out rules, only if no previous error message has been set
				    if (isset($occRule->CheckOutDays->WeekDay)) {
				        $allowedDays = (array)$occRule->CheckOutDays->WeekDay;
				        $allowedDays = array_map('strtoupper', $allowedDays);
				        $checkOutDateDay = strtoupper(date('l', strtotime($dateTo)));
				        if (!in_array($checkOutDateDay, $allowedDays)) {
				            $checkOutDaysFormatted = implode(', ', array_map('ucwords', array_map('strtolower', $allowedDays)));
				            $output_rightsidebar .= '<span class="form-error-results">The booking during this period is only possible with a departure on ' . ucwords($checkOutDaysFormatted) . '</span>';
				            break;
				        }
				    }
				    // Handle check-in rules
				    if (isset($occRule->CheckInDays->WeekDay)) {
				        $allowedDays = (array)$occRule->CheckInDays->WeekDay;
				        $allowedDays = array_map('strtoupper', $allowedDays);
				        $checkInDateDay = strtoupper(date('l', strtotime($dateFrom)));
				        if (!in_array($checkInDateDay, $allowedDays)) {
				            $checkInDaysFormatted = implode(', ', array_map('ucwords', array_map('strtolower', $allowedDays)));
				            $output_rightsidebar .= '<span class="form-error-results">The booking during this period is only possible with an arrival on ' . ucwords($checkInDaysFormatted) . '</span>';
				            break;
				        }
				    }
				}
			} else if ($result_IsAvailable->Available->AvailableCode != 1 && $getBookingPrice_info === 'yes' && $result_IsAvailable->Available->AvailableMessage === 'The compulsory arrival date is not fulfilled') {
				$occupationalRules = isset($result_IsAvailable->OccupationalRule) ? $result_IsAvailable->OccupationalRule : [];
				$occupationalRules = is_array($occupationalRules) ? $occupationalRules : [$occupationalRules];
				foreach ($occupationalRules as $occRule) {
				    // Handle check-in rules
				    if (isset($occRule->CheckInDays->WeekDay)) {
				        $allowedDays = (array)$occRule->CheckInDays->WeekDay;
				        $allowedDays = array_map('strtoupper', $allowedDays);
				        $checkInDateDay = strtoupper(date('l', strtotime($dateFrom)));
				        if (!in_array($checkInDateDay, $allowedDays)) {
				            $checkInDaysFormatted = implode(', ', array_map('ucwords', array_map('strtolower', $allowedDays)));
				            $output_rightsidebar .= '<span class="form-error-results">The booking during this period is only possible with an arrival on ' . ucwords($checkInDaysFormatted) . '</span>';
				            break;
				        }
				    }
				    // Handle check-out rules, only if no previous error message has been set
				    if (isset($occRule->CheckOutDays->WeekDay)) {
				        $allowedDays = (array)$occRule->CheckOutDays->WeekDay;
				        $allowedDays = array_map('strtoupper', $allowedDays);
				        $checkOutDateDay = strtoupper(date('l', strtotime($dateTo)));
				        if (!in_array($checkOutDateDay, $allowedDays)) {
				            $checkOutDaysFormatted = implode(', ', array_map('ucwords', array_map('strtolower', $allowedDays)));
				            $output_rightsidebar .= '<span class="form-error-results">The booking during this period is only possible with a departure on ' . ucwords($checkOutDaysFormatted) . '</span>';
				            break;
				        }
				    }
				}
			} else if ($result_IsAvailable->Available->AvailableCode != 1 && $getBookingPrice_info === 'yes' && $result_IsAvailable->Available->AvailableMessage === 'Under petition') {
	            $output_rightsidebar .= '<span class="form-error-results">We will contact you when our office reopens about your booking request</span>';
	        } else if ($result_IsAvailable->Available->AvailableCode != 1 && $getBookingPrice_info === 'yes') {
				//$output_rightsidebar .= '<span class="form-error-results">' . $result_IsAvailable->Available->AvailableMessage . '</span>';
				$output_rightsidebar .= '<span class="form-error-results">Please contact us direct to check the availability and price for these dates</span>';
			} else if (empty($result_GetBookingPrice->BookingPrice) && $result_IsAvailable->Available->AvailableCode == 1 && $getBookingPrice_info === 'yes') {
	        	$output_rightsidebar .= '<span class="form-error-results">Please contact us direct to check the availability and price for these dates</span>';
	        }
			$output_rightsidebar .= '</div>';
		} else {
			$output_rightsidebar .= '<div id="contactmessage">';
			$output_rightsidebar .= '<span class="form-contact-results">Contact us for more information</span>';
			$output_rightsidebar .= '</div>';
		}
		$output_rightsidebar .= '</div>';
		$output_rightsidebar .= '</div>';
		$output_rightsidebar .= '</div>';
		$output_rightsidebar .= '</div>';
		if (isset($fromDate_post)) {
			$noticeDays = $availabilityData['MinDaysNotice'];
			$getFirstActiveDate = date('d/m/Y', strtotime('+' . $noticeDays . ' day'));
			$getFirstDate = date('d/m/Y', strtotime($fromDate_post));
		} else {
			$noticeDays = $availabilityData['MinDaysNotice'] ;
			$getFirstActiveDate = date('d/m/Y', strtotime('+' . $noticeDays . ' day'));
			$getFirstDate = $getFirstActiveDate;
		}
		if (isset($toDate_post)) {
			$getLastDate = date('d/m/Y', strtotime($toDate_post));
		} else {
			$getLastDate = date('d/m/Y', strtotime('+3 day'));
		}
		$currentURL_RV = esc_url($_SERVER['REQUEST_URI']);
		$output_rightsidebar .= '<script>
		var accommodationIdToAdd = "' . $accommodationId . '";
		var accommodationNameToAdd = "' . $descriptionsData['AccommodationName'] . '";
		var accommodationURLToAdd = "' . $currentURL_RV . '";
		var accommodationStartDate = "' . $getFirstDate . '";
		var accommodationEndDate = "' . $getLastDate . '";
		var accommodationMinDate = "' . $getFirstActiveDate . '";
		</script>';
		echo $output_rightsidebar;

		// Videos if any in the description
		$videoDescriptionText = str_replace("'", "&#39;", $descriptionsData['Description']);
		// Match all iframe tags in the content
		preg_match_all('/<iframe[^>]+>.*?<\/iframe>/i', $videoDescriptionText, $iframeMatches);
		$output_iframes = '';
		if (!empty($iframeMatches[0])) {
			$output_iframes .= '<div class="video-prop">';
			// Iterate over matches and append to the output string
			foreach ($iframeMatches[0] as $iframe) {
				if (strpos($iframe, 'loading=') === false) {
		            $lazyIframe = preg_replace('/<iframe/i', '<iframe loading="lazy"', $iframe);
		            $output_iframes .= $lazyIframe . "<br>"; 
		        } else {
		            $output_iframes .= $iframe . "<br>";
		        }
			}
			$output_iframes .= '</div>';
		}
		echo $output_iframes;

		/******* DESCRIPTION *******/
		$output_description = '<div class="accordion-accommodation">';
		$output_description .= '<button class="accordion-accommodation-item active-accordion-accommodation" title="Property Description Accordion" aria-label="Property Description Accordion">Description</button>';
		$output_description .= '<div class="accordion-accommodation-content" style="display: block;">';
		$output_description .= '<div id="descripcionf" class="box-left">';
		$output_description .= '<div id="description_container">';
		$output_description .= '<div id="descriptionText" class="shrinked">';
		//$descriptionText = str_replace("'", "&#39;", $descriptionsData['Description']);
        //$descriptionText = str_replace("+353 1 201 8440 ", "", $descriptionsData['Description']);
        $descriptionText = $descriptionsData['Description'];
		// Replace single quote
		$descriptionText = str_replace("'", "&#39;", $descriptionText);
		// Remove phone number
		$descriptionText = str_replace("+353 1 201 8440 ", "", $descriptionText);
		// Remove all inline styles
		$descriptionText = preg_replace('/ style=("|\')[^"\']*("|\')/', '', $descriptionText);
		// Remove iframe tags entirely
		$descriptionText = preg_replace('/<iframe[^>]*>.*?<\/iframe>/', '', $descriptionText);
		// Replace special double quotes - causing issue in hrefs
		$descriptionText = str_replace('“', '', $descriptionText);
		$descriptionText = str_replace('”', '', $descriptionText);
		// Replace styled headings with proper HTML heading tags and remove the paragraph tags around them.
		$patternsDesc = [
			'/<p><h1>(.+?)<\/h1><\/p>/',
			'/<p><h2>(.+?)<\/h2><\/p>/',
			'/<p><h3>(.+?)<\/h3><\/p>/',
			'/<p><h4>(.+?)<\/h4><\/p>/',
			'/<p><h5>(.+?)<\/h5><\/p>/',
			'/<p><h6>(.+?)<\/h6><\/p>/',
		];
		$replacementsDesc = [
			'<h1>$1</h1>',
			'<h2>$1</h2>',
			'<h3>$1</h3>',
			'<h4>$1</h4>',
			'<h5>$1</h5>',
			'<h6>$1</h6>',
		];
		$descriptionText = preg_replace($patternsDesc, $replacementsDesc, $descriptionText);
		// Remove all <br> tags
		$descriptionText = preg_replace('/<br\s*\/?>/', '', $descriptionText);
		// Process the description text to add paragraphs properly.
		$processedDescription = '';
		$parts = preg_split('/(<h[1-6]>.*?<\/h[1-6]>)/', $descriptionText, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
		foreach ($parts as $part) {
			if (preg_match('/<h[1-6]>.*<\/h[1-6]>/', $part)) {
				// If part is a heading, add it directly.
				$processedDescription .= $part;
			} else {
				// If part is not a heading, wrap it with <p> tags.
				// Trim to remove whitespace and split by line breaks to wrap each line individually.
				$lines = preg_split("/(\r\n|\n|\r)/", trim($part));
				foreach ($lines as $line) {
					if (!empty($line)) {
						$processedDescription .= '<p>' . $line . '</p>';
					}
				}
			}
		}
		// Remove any empty or unclosed HTML tags
		$processedDescription = preg_replace('/<p><\/p>/', '', $processedDescription); // Remove empty paragraphs
		$processedDescription = preg_replace('/<[^\/>]*>([\s]*?|)<\/[^>]*>/', '', $processedDescription); // Remove empty or unclosed tags
		$patternsDesc2 = [
			'/<p><h1>(.+?)<\/h1>/',
			'/<p><h2>(.+?)<\/h2>/',
			'/<p><h3>(.+?)<\/h3>/',
			'/<p><h4>(.+?)<\/h4>/',
			'/<p><h5>(.+?)<\/h5>/',
			'/<p><h6>(.+?)<\/h6>/',
		];
		$replacementsDesc2 = [
			'<h1>$1</h1>',
			'<h2>$1</h2>',
			'<h3>$1</h3>',
			'<h4>$1</h4>',
			'<h5>$1</h5>',
			'<h6>$1</h6>',
		];
		$processedDescription = preg_replace($patternsDesc2, $replacementsDesc2, $processedDescription);
		// Find all the consecutive list items and convert them into features structure
		$processedDescription = preg_replace_callback(
			'/(<p>-.*?<\/p>)+/s',
			function ($matches) {
				// Grab the entire match and split into individual <p> tags
				$listItems = preg_split('/<\/p>/', $matches[0], -1, PREG_SPLIT_NO_EMPTY);
				// Start the features div
				$featuresHtml = '<div class="features">';
				foreach ($listItems as $item) {
					// Clean up the line and remove the dash and the <p> tags
					$cleanLine = trim(preg_replace('/<p>-\s*/', '', $item));
					// Add the feature div structure
					$featuresHtml .= '<div class="feature"><div><span class="features-item-check"></span><span class="feature-text"> ' . $cleanLine . '</span></div></div>';
				}
				// Close the features div
				$featuresHtml .= '</div>';
				return $featuresHtml;
			},
			$processedDescription
		);
		$output_description .= $processedDescription;

		/*
		// Split the text into paragraphs by <br> tags.
		$descriptionParagraphs = preg_split('/<br\s*\/?>/', $descriptionText);
		foreach ($descriptionParagraphs as $descriptionParagraph) {
			// Skip empty lines.
			if (trim($descriptionParagraph) == '') continue;
			$descriptionText = preg_replace($patternsDesc2, $replacementsDesc2, $descriptionText);
			// Remove any remaining opening and closing <p> tags to avoid nesting them
			$descriptionParagraph = preg_replace('/<\/?p>/', '', $descriptionParagraph);
			// Check if the line is already a header or iframe, which shouldn't be wrapped in <p> tags.
			if (!preg_match('/^(<h[1-6]>)/', $descriptionParagraph)) {
				$output_description .= '<p>' . $descriptionParagraph . '</p>';
			} else {
				$output_description .= $descriptionParagraph; // These are already in the correct format
			}
		}*/
		$output_description .= '<div id="readmore-container"><div class="readmore-relative"><button class="readmore-button-wrapper" title="Read More" aria-label="Read More">Read More <span class="svg-readmore"></span></button></div></div>';
		$output_description .= '<div id="readless-container" class="read-hidden"><div class="readless-relative"><button class="readless-button-wrapper" title="Read Less" aria-label="Read Less">Read Less <span class="svg-readmore"></span></button></div></div>';
		$output_description .= '</div>';
		$output_description .= '</div>';
		$output_description .= '</div>';
		$output_description .= '</div>';
		echo $output_description;

		/******* CHECKIN/CHECKOUT SCHEDULES *******/
		$output_cioschedules = '<button class="accordion-accommodation-item active-accordion-accommodation" title="Check In / Out Schedules Accordion" aria-label="Check In / Out Schedules Accordion">Check In / Out Schedules</button>';
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
		$output_availabilitycalender = '<button class="accordion-accommodation-item active-accordion-accommodation" title="Availability Calendar Accordion" aria-label="Availability Calendar Accordion">Availability Calendar</button>';
		$output_availabilitycalender .= '<div class="accordion-accommodation-content" style="display: block;">';
		// Calender notes
		if (isset($occupationalRuleData['SeasonsOR'][0]['MinimumNights']) && $occupationalRuleData['SeasonsOR'][0]['MinimumNights'] != '' || isset($availabilityData['availableRanges']) && !empty($availabilityData['availableRanges'])) {
			$output_availabilitycalender .= '<ul>';
		}
		if (isset($occupationalRuleData['SeasonsOR'][0]['MinimumNights']) && $occupationalRuleData['SeasonsOR'][0]['MinimumNights'] != '') {
			if (isset($occupationalRuleData['SeasonsOR'][0]['MaximumNights']) && $occupationalRuleData['SeasonsOR'][0]['MaximumNights'] != '' && $occupationalRuleData['SeasonsOR'][0]['MaximumNights'] != '0') {
				$maxNights = ', with a maximum of <strong>' . $occupationalRuleData['SeasonsOR'][0]['MaximumNights'] . ' night' . ($occupationalRuleData['SeasonsOR'][0]['MaximumNights'] != 1 ? 's' : '') . '</strong>';
			} else {
				$maxNights = '';
			}
			$output_availabilitycalender .= '<li>This property requires a <strong>' . $occupationalRuleData['SeasonsOR'][0]['MinimumNights'] . ' night' . ($occupationalRuleData['SeasonsOR'][0]['MinimumNights'] != 1 ? 's' : '') . '</strong> minimum stay' . $maxNights . '</li>';
		}
		// Check if there are available ranges in the result
		if (isset($availabilityData['availableRanges']) && !empty($availabilityData['availableRanges'])) {
			// Sort the available ranges by the start date in ascending order
			usort($availabilityData['availableRanges'], function ($a, $b) {
				return strtotime($a['start']) - strtotime($b['start']);
			});
			// Get the first available start date
			$currentDate = new DateTime();  // Current date
			$earliestStartDate = clone $currentDate;
			$earliestStartDate->modify('+' . $availabilityData['MinDaysNotice'] . ' days');
			$minimumNightsRequired = isset($occupationalRuleData['SeasonsOR'][0]['MinimumNights']) ? $occupationalRuleData['SeasonsOR'][0]['MinimumNights'] : 0;
			$firstAvailableRange_begin = null;
			if (isset($availabilityData['availableRanges']) && !empty($availabilityData['availableRanges'])) {
				foreach ($availabilityData['availableRanges'] as $range) {
					$rangeStartDate = DateTime::createFromFormat('Y-m-d', $range['start']);
					$rangeEndDate = DateTime::createFromFormat('Y-m-d', $range['end']);
					if ($rangeEndDate >= $earliestStartDate) {
						$bookingStartDate = ($rangeStartDate > $earliestStartDate) ? $rangeStartDate : $earliestStartDate;
						$potentialEndDate = clone $bookingStartDate;
						 $potentialEndDate->modify("+$minimumNightsRequired days");
						if ($potentialEndDate <= $rangeEndDate) {
							$firstAvailableRange_begin = array(
								'start' => $bookingStartDate->format('Y-m-d'),
								'end' => $potentialEndDate->format('Y-m-d')
							);
							break;
						}
					}
				}
			}
			
			if (isset($firstAvailableRange_begin) && $firstAvailableRange_begin !== null) {
				$firstAvailableStartDate = date('d/m/Y', strtotime($firstAvailableRange_begin['start']));
			} else {
				$firstAvailableStartDate = date('d/m/Y', strtotime($availabilityData['availableRanges'][0]['start']));
			}
			$output_availabilitycalender .= '<li>The next available date for <strong>' . $occupationalRuleData['SeasonsOR'][0]['MinimumNights'] . ' night' . ($occupationalRuleData['SeasonsOR'][0]['MinimumNights'] != 1 ? 's' : '') . '</strong> stay is <strong>' . $firstAvailableStartDate . '</strong></li>';
		}
		if (isset($occupationalRuleData['SeasonsOR'][0]['MinimumNights']) && $occupationalRuleData['SeasonsOR'][0]['MinimumNights'] != '' || isset($availabilityData['availableRanges']) && !empty($availabilityData['availableRanges'])) {
			$output_availabilitycalender .= '</ul>';
		}
		$output_availabilitycalender .= '<p class="availability-cont"><span class="available-dot"></span> Available <span class="notavailable-dot"></span> Unavailable <span class="availablesel-dot"></span> Selected</p>';
		// Start Swiper Calendar
		$output_availabilitycalender .= '<div class="swiper availabilityCalenderSwiper">';
		$output_availabilitycalender .= '<div class="swiper-wrapper">';
		$currentYear = date('Y');
		$currentMonth = date('n');
		// get only the availability within 12 months of now
		$monthsToShow = 24;
		for ($i = 0; $i < $monthsToShow; $i++) {
			$year = $currentYear + floor(($currentMonth + $i - 1) / 12);
			$month = ($currentMonth + $i - 1) % 12 + 1;
			$output_availabilitycalender .= '<div class="swiper-slide-calender calendar-date-month-wrapper">';
			// Find the first day of the month
			$firstDayOfMonth = new DateTime("$year-$month-01");
			$firstDayOfWeek = $firstDayOfMonth->format('N');
			$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
			$weeksInMonth = ceil(($daysInMonth + $firstDayOfWeek - 1) / 7);
			$output_availabilitycalender .= '<div class="calendar-date-month-wrapper-title">';
			$output_availabilitycalender .= '<h2>' . date('F Y', strtotime($year . '-' . $month . '-01')) . '</h2>';
			$output_availabilitycalender .= '<span class="calendar-date-month-seperator"></span>';
			$output_availabilitycalender .= '</div>';
			$output_availabilitycalender .= '<div class="calendar-date-container">';
			// Define the days of the week in the correct order starting from Monday
			$daysOfWeek = ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'];
			foreach ($daysOfWeek as $dayOfWeek) {
				$output_availabilitycalender .= '<div class="calendar-date-day">' . $dayOfWeek . '</div>';
			}
			// Fill in empty cells for days before the first day of the month
			for ($emptyDay = 1; $emptyDay < $firstDayOfWeek; $emptyDay++) {
				$output_availabilitycalender .= '<div class="calendar-date-number calendar-date-number-notavailable"></div>';
			}
			$currentDay = 1; // Initialize the current day
			$currentDate = new DateTime();  // Tomorrow
			$earliestStartDate = clone $currentDate;
			$noticeDateExcludingTodays = $availabilityData['MinDaysNotice'] - 1;
			$earliestStartDate->modify('+' . $noticeDateExcludingTodays . ' days');
			for ($week = 0; $week < $weeksInMonth; $week++) {
				for ($dayOfWeek = $firstDayOfWeek; $dayOfWeek <= 7; $dayOfWeek++) {
					if ($currentDay <= $daysInMonth) {
						$currentDate = new DateTime("$year-$month-$currentDay");
						// If before the earliest start date, mark as not available
						if ($currentDate < $earliestStartDate) {
							$availabilityClass = ' calendar-date-number-notavailable'; // Default class
						} else {
							// Determine the availability class based on the current date
							$availabilityClass = ' calendar-date-number-notavailable'; // Default class
							foreach ($availabilityData['availableRanges'] as $rangeAvail) {
								$startRangeAvail = new DateTime($rangeAvail['start']);
								$endRangeAvail = new DateTime($rangeAvail['end']);
								$endRangeAvail->modify('+ 1 days');
								if ($currentDate >= $startRangeAvail && $currentDate <= $endRangeAvail) {
									$availabilityClass = ' calendar-date-number-available'; // Set class to free for available days
									break; // No need to check further
								}
							}
						}
						$output_availabilitycalender .= '<div class="calendar-date-number' . $availabilityClass . '" data-date="' . $currentDate->format('Y') . '-' . $currentDate->format('m') . '-' . $currentDate->format('d') . '">' . $currentDate->format('d') . '</div>';
						$currentDay++;
					} else {
						$output_availabilitycalender .= '<div class="calendar-date-number calendar-date-number-notavailable"></div>';
					}
				}
				$firstDayOfWeek = 1; // Reset to Monday for the next week
			}
			$output_availabilitycalender .= '</div>';
			$output_availabilitycalender .= '</div>';
		}
		$output_availabilitycalender .= '</div>';
		$output_availabilitycalender .= '<div class="swiper-button-prev"></div>';
		$output_availabilitycalender .= '<div class="swiper-button-next"></div>';
		$output_availabilitycalender .= '</div>';
		// End Swiper Calendar
		$output_availabilitycalender .= '</div>';
		echo $output_availabilitycalender;

		/******* AMENITIES (FEATURES) *******/
		$output_features = '<button class="accordion-accommodation-item active-accordion-accommodation" title="Amenities Accordion" aria-label="Amenities Accordion">Amenities</button>';
		$output_features .= '<div class="accordion-accommodation-content" style="display: block;">';
		$output_features .= '<div id="propertyInfo" class="box-left">';
		// Start #mainFeatures
		$output_features .= '<div id="mainFeatures">';
		$output_features .= '<h3 class="subtitle-section">Featured</h3>';
		$output_features .= '<div class="features">';
		$topFeatured = [];
		if (!empty($accommodationData['ExtraServices'])) {
			foreach ($accommodationData['ExtraServices'] as $serviceDescription) {
				if ($serviceDescription === 'Internet Access') {
					$topFeatured[] = '<div class="feature"><div><span class="svg-wifi"></span> <span>Internet</span></div></div>';
				}
				if ($serviceDescription === 'Pet') {
					$topFeatured[] = '<div class="feature"><div><span class="svg-pet"></span> <span>Dog Friendly</span></div></div>';
				}
				if ($serviceDescription === 'Heating') {
					$topFeatured[] = '<div class="feature"><div><span class="svg-heating"></span> <span>Heating</span></div></div>';
				}
				if ($serviceDescription === 'Air conditioning') {
					$topFeatured[] = '<div class="feature"><div><span class="svg-airconditioning"></span> <span>Air Conditioning</span></div></div>';
				}
				if (stristr($serviceDescription, 'Parking')) {
					$topFeatured[] = '<div class="feature"><div><span class="svg-parking"></span> <span>Parking</span></div></div>';
				}
				
			}
		}
		if (!empty($accommodationData['Labels']) &&(in_array('electric car charger',$accommodationData['Labels']))) {
			$topFeatured[] = '<div class="feature"><div><span class="svg-evcharger"></span> <span>EV Charger</span></div></div>';
		}
		if (isset($accommodationData['Features']['PeopleCapacity']) && $accommodationData['Features']['PeopleCapacity'] !== '') {
			$numPeopleCapacity = (int)$accommodationData['Features']['PeopleCapacity'];
            if ($numPeopleCapacity !== '' && $numPeopleCapacity !== '0' && $numPeopleCapacity >= $totalOccupants) {
                $topFeatured[] = '<div class="feature"><div><span class="svg-guests"></span> <span>' . $numPeopleCapacity . ' Occupant' . ($numPeopleCapacity != 1 ? 's' : '') . '</span></div></div>';
            } else {
                $topFeatured[] = '<div class="feature"><div><span class="svg-guests"></span> <span>' . $totalOccupants . ' Occupant' . ($totalOccupants != 1 ? 's' : '') . '</span></div></div>';
            }
		} else if (isset($accommodationData['Features']['AdultsCapacity']) && $accommodationData['Features']['AdultsCapacity'] !== '') {
			$numPeopleCapacity = (int)$accommodationData['Features']['AdultsCapacity'];
            if ($numPeopleCapacity !== '' && $numPeopleCapacity !== '0' && $numPeopleCapacity >= $totalOccupants) {
                $topFeatured[] = '<div class="feature"><div><span class="svg-guests"></span> <span>' . $numPeopleCapacity . ' Occupant' . ($numPeopleCapacity != 1 ? 's' : '') . '</span></div></div>';
            } else {
                $topFeatured[] = '<div class="feature"><div><span class="svg-guests"></span> <span>' . $totalOccupants . ' Occupant' . ($totalOccupants != 1 ? 's' : '') . '</span></div></div>';
            }
		} else {
			if (isset($accommodationData['Features']['MinimumOccupation']) && $accommodationData['Features']['MinimumOccupation'] !== '') {
				$numPeopleCapacity = (int)$accommodationData['Features']['MinimumOccupation'];
				$topFeatured[] = '<div class="feature"><div><span class="svg-guests"></span> <span>' . $numPeopleCapacity . ' Occupant' . ($numPeopleCapacity != 1 ? 's' : '') . '</span></div></div>';
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
			if (isset($accommodationData['Characteristics']['HandicappedFacilities']) && in_array((string)$accommodationData['Characteristics']['HandicappedFacilities'], ['true', '1', 'yes', 'apta-discapacitados'])) {
				$topFeatured[] = '<div class="feature"><div><span class="svg-disabledfriendly"></span> <span>Disabled Facilities</span></div></div>';
			}
		}
		if (!empty($accommodationData['CharacteristicsOptionTitles'])) {
			foreach ($accommodationData['CharacteristicsOptionTitles'] as $optionCharacteristics) {
				if (in_array($optionCharacteristics, ['SwimmingPool', 'TVSatellite', 'HandicappedFacilities', 'Kitchen'])) {
					if ($optionCharacteristics === 'SwimmingPool') {
						$topFeatured[] = '<div class="feature"><div><span class="svg-pool"></span> <span>Swimming Pool</span></div></div>';
					}
				}
			}
		}
		if (isset($accommodationData['Features']['AreaHousingArea']) && $accommodationData['Features']['AreaHousingArea'] !== '' && isset($accommodationData['Features']['AreaHousingUnit']) && $accommodationData['Features']['AreaHousingUnit'] !== '') {
			$areaHousing = (string)$accommodationData['Features']['AreaHousingArea'] . ' ' . (string)$accommodationData['Features']['AreaHousingUnit'];
			$topFeatured[] = '<div class="feature"><div><span class="svg-area"></span> <span>Area Housing: ' . $areaHousing . '</span></div></div>';
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
				$optionCharacteristicsGeneral = str_replace("Barbacue", "Barbecue", $optionCharacteristicsGeneral);
				$output_features .= '<div class="feature"><div> <span class="features-item-check"></span> <span class="feature-text">' . $optionCharacteristicsGeneral . '</span></div></div>';
			}
		}
		if (isset($accommodationData['Characteristics']['HandicappedFacilities']) && in_array((string)$accommodationData['Characteristics']['HandicappedFacilities'], ['sin-escaleras'])) {
			$output_features .= '<div class="feature"><div> <span class="features-item-check"></span> <span class="feature-text">Access without stairs</span></div></div>';
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
				$output_features .= '<div class="feature"><div><span class="svg-dbed"></span> <span>' . $DoubleBeds_num . ' Double Bed' . ($DoubleBeds_num != 1 ? 's' : '') . '</span></div></div>';
			}
			if (isset($accommodationData['Features']['IndividualBeds']) && $accommodationData['Features']['IndividualBeds'] !== '') {
				$IndividualBeds_num = isset($accommodationData['Features']['IndividualBeds']) ? (int)$accommodationData['Features']['IndividualBeds'] : 0;
				$output_features .= '<div class="feature"><div><span class="svg-ibed"></span> <span>' . $IndividualBeds_num . ' Single Bed' . ($IndividualBeds_num != 1 ? 's' : '') . '</span></div></div>';
			}
			if (isset($accommodationData['Features']['DoubleSofaBed']) && $accommodationData['Features']['DoubleSofaBed'] !== '') {
				$DoubleSofaBed_num = isset($accommodationData['Features']['DoubleSofaBed']) ? (int)$accommodationData['Features']['DoubleSofaBed'] : 0;
				$output_features .= '<div class="feature"><div><span class="svg-dsbed"></span> <span>' . $DoubleSofaBed_num . ' Double Sofa Bed' . ($DoubleSofaBed_num != 1 ? 's' : '') . '</span></div></div>';
			}
			if (isset($accommodationData['Features']['IndividualSofaBed']) && $accommodationData['Features']['IndividualSofaBed'] !== '') {
				$IndividualSofaBed_num = isset($accommodationData['Features']['IndividualSofaBed']) ? (int)$accommodationData['Features']['IndividualSofaBed'] : 0;
				$output_features .= '<div class="feature"><div><span class="svg-isbed"></span> <span>' . $IndividualSofaBed_num . ' Single Sofa Bed' . ($IndividualSofaBed_num != 1 ? 's' : '') . '</span></div></div>';
			}
			if (isset($accommodationData['Features']['KingBeds']) && $accommodationData['Features']['KingBeds'] !== '') {
				$KingBeds_num = isset($accommodationData['Features']['KingBeds']) ? (int)$accommodationData['Features']['KingBeds'] : 0;
				$output_features .= '<div class="feature"><div><span class="svg-kbed"></span> <span>' . $KingBeds_num . ' King Bed' . ($KingBeds_num != 1 ? 's' : '') . '</span></div></div>';
			}
			if (isset($accommodationData['Features']['QueenBeds']) && $accommodationData['Features']['QueenBeds'] !== '') {
				$QueenBeds_num = isset($accommodationData['Features']['QueenBeds']) ? (int)$accommodationData['Features']['QueenBeds'] : 0;
				$output_features .= '<div class="feature"><div><span class="svg-qbed"></span> <span>' . $QueenBeds_num . ' Queen Bed' . ($QueenBeds_num != 1 ? 's' : '') . '</span></div></div>';
			}
			if (isset($accommodationData['Features']['Berths']) && $accommodationData['Features']['Berths'] !== '') {
				$BunkBeds_num = isset($accommodationData['Features']['Berths']) ? (int)$accommodationData['Features']['Berths'] : 0;
				$output_features .= '<div class="feature"><div><span class="svg-ibed"></span> <span>' . $BunkBeds_num . ' Bunk Bed' . ($BunkBeds_num != 1 ? 's' : '') . '</span></div></div>';
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
		if (isset($accommodationData['Features']['BathroomWithBathtub']) || isset($accommodationData['Features']['BathroomWithShower']) || isset($accommodationData['Features']['Toilets'])) {
			if (isset($accommodationData['Features']['BathroomWithBathtub']) && $accommodationData['Features']['BathroomWithBathtub'] !== '') {
				$BathroomWithBathtub_num = isset($accommodationData['Features']['BathroomWithBathtub']) ? (int)$accommodationData['Features']['BathroomWithBathtub'] : 0;
				$output_features .= '<div class="feature"><div><span class="svg-bath"></span> <span>' . $BathroomWithBathtub_num . ' Bathroom' . ($BathroomWithBathtub_num != 1 ? 's' : '') . ' With Bathtub</span></div></div>';
			}
			if (isset($accommodationData['Features']['BathroomWithShower']) && $accommodationData['Features']['BathroomWithShower'] !== '') {
				$BathroomWithShower_num = isset($accommodationData['Features']['BathroomWithShower']) ? (int)$accommodationData['Features']['BathroomWithShower'] : 0;
				$output_features .= '<div class="feature"><div> <span class="svg-shower"></span> <span>' . $BathroomWithShower_num . ' Bathroom' . ($BathroomWithShower_num != 1 ? 's' : '') . ' With Shower</span></div></div>';
			}
			if (isset($accommodationData['Features']['Toilets']) && $accommodationData['Features']['Toilets'] !== '') {
				$Toilets_num = isset($accommodationData['Features']['Toilets']) ? (int)$accommodationData['Features']['Toilets'] : 0;
				$output_features .= '<div class="feature"><div><span class="svg-toilets"></span> <span>' . $Toilets_num . ' Toilet' . ($BathroomWithBathtub_num != 1 ? 's' : '') . '</span></div></div>';
			}
		} else {
			$output_features .= '<div class="feature"><div>No bathrooms available</div></div>';
		}
		$output_features .= '</div>';
		$output_features .= '</div>';
		// End #mainBathrooms
		// Start #mainViews
		if (!empty($accommodationData['ViewType'])) {
			$output_features .= '<div id="mainViews">';
			$output_features .= '<h3 class="subtitle-section">View(s) from Accommodation</h3>';
			$output_features .= '<div class="features">';
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
						$output_features .= '<div class="feature"><div> <span class="svg-river"></span> <span>River</span></div></div>';
					}
					if ($viewDescription === 'Golf') {
						$output_features .= '<div class="feature"><div> <span class="svg-golf"></span> <span>Golf</span></div></div>';
					}
					if ($viewDescription === 'Beach') {
						$output_features .= '<div class="feature"><div> <span class="svg-beach"></span> <span>Beach</span></div></div>';
					}
				}
			}
			$output_features .= '</div>';
			$output_features .= '</div>';
		}
		// End #mainViews
		$output_features .= '</div>';
		$output_features .= '</div>';
		/******* SERVICES *******/
		$output_features .= '<button class="accordion-accommodation-item" title="Services Accordion" aria-label="Services Accordion">Services</button>';
		$output_features .= '<div class="accordion-accommodation-content">';
		$output_features .= '<div id="propertyInfo" class="box-left">';
		// Start #mainExtraServices
		$output_features .= '<div id="mainExtraServices">';
		$output_features .= '<h3 class="subtitle-section">Mandatory or Included Services</h3>';
		$output_features .= '<div class="features">';
		$mandatory_services = ''; $optional_services = '';
		// Loop through obligatory or included services
		if (!empty($descriptionsData['Extras']['ObligatoryOrIncluded'])) {
			foreach ($descriptionsData['Extras']['ObligatoryOrIncluded'] as $extraDescription) {
				if ($extraDescription['Name'] != 'Security Deposit (refundable)') {
					if (!in_array(strtolower($extraDescription['Name']), ['baby high chair', 'cot/crib'])) {
						$mandatory_services .= '<div class="feature"><div> <span class="features-item-check"></span> <span class="feature-text">' . $extraDescription['Name'] . ' - ' . $extraDescription['Description'] . '</span></div></div>';
					} else {
						$optional_services .= '<div class="feature"><div> <span class="features-item-check"></span> <span class="feature-text">' . $extraDescription['Name'] . ' - Upon Request' . '</span></div></div>';
					}
				}
			}
		}
		if (!empty($mandatory_services)) {
			$output_features .= $mandatory_services;
		} else {
			$output_features .= '<div class="feature"><div>No obligatory or included services available</div></div>';
		}
		$output_features .= '</div>';
		$output_features .= '</div>';
		// End #mainExtraServices
		// Start #mainOptionalServices
		$output_features .= '<div id="mainOptionalServices">';
		$output_features .= '<h3 class="subtitle-section">Optional Services</h3>';
		$output_features .= '<div class="features">';
		// Loop through optional services
		if (!empty($descriptionsData['Extras']['Optional'])) {
			foreach ($descriptionsData['Extras']['Optional'] as $extraOptionalDescription) {
				$optional_services .= '<div class="feature"><div> <span class="features-item-check"></span> <span class="feature-text">' . $extraOptionalDescription['Name'] . ' - ' . $extraOptionalDescription['Description'] . '</span></div></div>';
			}
		}
		if (!empty($optional_services)) {
			$output_features .= $optional_services;
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
			$output_specialoffers .= '<button class="accordion-accommodation-item active-accordion-accommodation" title="Special Offers Accordion" aria-label="Special Offers Accordion">Special Offers</button>';
			$output_specialoffers .= '<div class="accordion-accommodation-content" style="display: block;">';
			$output_specialoffers .= '<div id="propertyInfo" class="box-left">';
			// Start #mainSpecialOffers
			$output_specialoffers .= '<div id="mainSpecialOffers">';
			$output_specialoffers .= '<div class="features">';
			$firstOfferDisplayed = false;
			$offerCount = 0;
			foreach ($priceModifierData['SeasonsPM'] as $offersDescription) {
				$hiddenClass = "";
				$offerCount++;
				if ($offerCount > 8) {
					$hiddenClass = " customerOffers-shrinked hidden-offer";
				}
				if ($offersDescription['MinNumberOfNights']) {
					$offerNights = ' for a minimum of ' . $offersDescription['MinNumberOfNights'] . ' night' . ($offersDescription['MinNumberOfNights'] != 1 ? 's' : '');
				} else if ($offersDescription['MaxNumberOfNights']) {
					$offerNights = ' for a maximum of ' . $offersDescription['MaxNumberOfNights'] . ' night' . ($offersDescription['MaxNumberOfNights'] != 1 ? 's' : '');
				} else if ($offersDescription['NumberOfNights']) {
					$offerNights = ' for ' . $offersDescription['NumberOfNights'] . ' night' . ($offersDescription['NumberOfNights'] != 1 ? 's' : '');
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
				$output_specialoffers .= '<div class="feature' . $hiddenClass . '"><div class="offer-header"><span class="offer-first"><span class="svg-offers"></span></span><span class="offer-percentage">' . $offerAmtType . '</span></div>';
				$output_specialoffers .= '<div class="offer-content"><span class="offer-title">Discount of ' . $offerAmtType . $offerNights . '</span>';
				$output_specialoffers .= '<span class="offer-startend">From: ' . $offersDescription['StartDate'] . '</span>';
				$output_specialoffers .= '<span class="offer-startend">To: ' . $offersDescription['EndDate'] . '</span></div></div>';
			}
			if (count($offersDescription) > 8) {
				$output_specialoffers .= '<div id="loadmore-offers-container"><div class="loadmore-relative"><button class="loadmore-offers-button-wrapper" title="Load More" aria-label="Load More">Load More <span class="svg-loadmore"></span></button></div></div>';
				$output_specialoffers .= '<div id="loadless-offers-container" class="hidden-offer"><div class="loadless-relative"><button class="loadless-offers-button-wrapper" title="Show Less" aria-label="Show Less">Show Less <span class="svg-loadmore"></span></button></div></div>';
			}
			$output_specialoffers .= '</div>';
			$output_specialoffers .= '</div>';
			// End #mainSpecialOffers
			$output_specialoffers .= '</div>';
			$output_specialoffers .= '</div>';
		}
		echo $output_specialoffers;

		/******* MAP & NEARBY ATTRACTIONS *******/
		$output_mapandattractions = '<button class="accordion-accommodation-item" title="Map and Nearby Attractions Accordion" aria-label="Map and Nearby Attractions Accordion">Map and Nearby Attractions</button>';
		$output_mapandattractions .= '<div class="accordion-accommodation-content">';
		$output_mapandattractions .= '<div id="propertyInfo" class="box-left">';
		// Start #mainMap
		$output_mapandattractions .= '<div id="mainMap">';
		// Start Swiper Nearest Attractions
		//$output_mapandattractions .= '<div class="swiper attractionsSwiper">';
		//$output_mapandattractions .= '<div class="swiper-wrapper">';
		if (!empty($accommodationData['Places'])) {
			$output_mapandattractions .= '<div class="features">';
			foreach ($accommodationData['Places'] as $placesDescription) {
				//$output_mapandattractions .= '<div class="swiper-slide-attractions">';
				//$output_mapandattractions .= '<div class="features">';
				$output_mapandattractions .= '<div class="feature"><div> <span>' . $placesDescription . '</span></div></div>';
				//$output_mapandattractions .= '</div>';
				//$output_mapandattractions .= '</div>';
			}
			$output_mapandattractions .= '</div>';
		}
		//$output_mapandattractions .= '</div>';
		//$output_mapandattractions .= '<div class="swiper-button-prev"></div>';
		//$output_mapandattractions .= '<div class="swiper-button-next"></div>';
		//$output_mapandattractions .= '</div>';
		// End Swiper Nearest Attractions
		$output_mapandattractions .= '<div class="overlay" onClick="style.pointerEvents=\'none\'"></div>';
		$querystr = 'destination='.$accommodationId;
		/*$output_mapandattractions .= '<div class="widget mapa-alojamientos">
			<div id="mapa-alojamientos">
				<link rel="stylesheet" href="/wp-content/plugins/avantio-api-integration/css/mapbox.css" type="text/css">
				<link href="https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.css" rel="stylesheet">
				<link rel="stylesheet" href="/wp-content/plugins/avantio-api-integration/css/widgetmap.css" type="text/css">
				<link rel="stylesheet" href="/wp-content/plugins/avantio-api-integration/css/loader.css" type="text/css">
				<span id="maps-data"  data-url="'.admin_url('admin-ajax.php').'" data-request="'.base64_encode($querystr).'"></span>
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
				<div id="map_canvas" style="width:100%;height:75vh;">
				  <div id="loading-map">
					<div class="fwk-border spinner"></div>
					<div class="fwk-border spinner-active"></div>
				  </div>
				</div>
				<script>if (!window.jQuery || typeof jQuery == \'undefined\') {document.write(\'<script src="/wp-includes/js/jquery/jquery.min.js"><\/script>\')}</script>
				<script defer type="text/javascript" src="/wp-content/plugins/avantio-api-integration/js/detectElement.js"></script>
				<script defer type="text/javascript" src="/wp-content/plugins/avantio-api-integration/js/Maps.js"></script>
				<script defer type="text/javascript" src="/wp-content/plugins/avantio-api-integration/js/MapBox.js"></script>
				<script defer type="text/javascript" src="/wp-content/plugins/avantio-api-integration/js/widgetMaps.js"></script>
			</div>
		</div>';*/
		$output_mapandattractions .= '<div id="map"><iframe loading="lazy" width="100%" height="100%" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" id="gmap_canvas" src="https://maps.google.com/maps?width=100%&amp;height=100%&amp;hl=en&amp;q=' . $accommodationData['LocalizationData']['GoogleLatitude'] . ',' . $accommodationData['LocalizationData']['GoogleLongitude'] . '&amp;t=&amp;z=' . $accommodationData['LocalizationData']['GoogleZoom'] . '&amp;ie=UTF8&amp;iwloc=B&amp;output=embed"></iframe></div>';
		$output_mapandattractions .= '</div>';
		$output_mapandattractions .= '</div>';
		$output_mapandattractions .= '</div>';
		echo $output_mapandattractions;

		/******* REVIEWS *******/
		$output_reviews = '<button id="reviewsAnchor" class="accordion-accommodation-item" title="Reviews Accordion" aria-label="Reviews Accordion">Reviews</button>';
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
		$output_reviews .= '<span class="num_reviews">' . $totalReviews . ' <span class="text_reviews">review' . ($totalReviews != 1 ? 's' : '') . '</span></span>';
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
				$averageAspectRatingConverted = sprintf("%d%d", $integerAspectPart, $decimalAspectPart);
			}
			$aspectType = str_replace('_', ' ', $aspectType);
			$output_reviews .= '<div class="feature"><div> <span class="feature-text uppercase-text">' . ucfirst(strtolower($aspectType)) . '</span><span class="star-ratings' . $averageAspectRatingConverted . '" aria-label="Rating of this review out of 5"></span></div></div>';
		}
		$output_reviews .= '</div>';
		if (!empty($accommodationData['Reviews'])) {
			$output_reviews .= '<div class="features">';
			// Start .customerReviews-container
			$output_reviews .= '<div class="customerReviews-container">';
			$output_reviews .= '<div class="customerReviews-wrapper">';
			$firstReviewDisplayed = false;
			$reviewCounter = 0;
			foreach ($accommodationData['Reviews'] as $reviewsComments) {
				$hiddenClass = $reviewCounter >= 1 ? " hidden-review" : "";
				$reviewWrapperClass = $reviewCounter >= 1 ? "customerReviews-shrinked" : "";
				if ($firstReviewDisplayed) {
					$hiddenClass = " hidden-review";
				}
				// Customer Reviews
				if (count($reviewsComments) > 1) {
					if ($firstReviewDisplayed) {
						$output_reviews .= '<div class="customerReviews-shrinked' . $hiddenClass . '">';
					}
				}
				$output_reviews .= '<div class="customerReviews-inner">';
				$output_reviews .= '<div class="customerReviews">';
				$output_reviews .= '<div>';
				if (isset($reviewsComments['Title']) && (isset($reviewsComments['PositiveComment']) || isset($reviewsComments['NegativeComment']))) {
					$output_reviews .= '<span class="reviewTitle">' . $reviewsComments['Title'] . '</span>';
				} else {
					if (isset($reviewsComments['GuestName'])) {
						$output_reviews .= '<span class="reviewTitle">Review by ' . $reviewsComments['GuestName'] . '</span>';
					} else {
						$output_reviews .= '<span class="reviewTitle">Customer Review</span>';
					}
				}
				if (isset($reviewsComments['GeneralRating'])) {
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
				if (isset($reviewsComments['Title']) && (isset($reviewsComments['PositiveComment']) || isset($reviewsComments['NegativeComment']))) {
					if (isset($reviewsComments['PositiveComment'])) {
						$output_reviews .= '<span class="reviewComment">&quot;' . $reviewsComments['PositiveComment'] . '&quot;</span>';
					}
					if (isset($reviewsComments['NegativeComment'])) {
						$output_reviews .= '<span class="reviewComment">&quot;' . $reviewsComments['NegativeComment'] . '&quot;</span>';
					}
				} else {
					if (isset($reviewsComments['Title'])) {
						$output_reviews .= '<span class="reviewComment">&quot;' . $reviewsComments['Title'] . '&quot;</span>';
					} else {
						$output_reviews .= '<span class="reviewComment">&quot;No review added&quot;</span>';
					}
				}
				if (isset($reviewsComments['GuestName'])) {
					$output_reviews .= '<span class="reviewCustomer">- ' . $reviewsComments['GuestName'] . '</span>';
				}
				if (isset($reviewsComments['ReviewDate'])) {
					$output_reviews .= '<span class="reviewDate">' . date('d/m/Y', strtotime($reviewsComments['ReviewDate'])) . '</span>';
				}
				$output_reviews .= '</div>';
				$output_reviews .= '</div>';
				$output_reviews .= '</div>';
				// Replies by Website Owner
				if (isset($reviewsComments['OwnersReply']) && $reviewsComments['OwnersReply'] != '') {
					$output_reviews .= '<div class="customerReviews-reply-wrapper">';
					$output_reviews .= '<div class="customerReviews-reply-icon"><span class="svg-downright"></span></div>';
					$output_reviews .= '<div class="customerReviews-reply">';
					$output_reviews .= '<div class="customerReviews">';
					$output_reviews .= '<div>';
					$reviewOwnersReplyText = str_replace("'", "&#39;", $reviewsComments['OwnersReply']);
					$reviewOwnersReplyLines = explode("\n", $reviewOwnersReplyText);
					$output_reviews .= '<span class="reviewReplies">';
					foreach ($reviewOwnersReplyLines as $reviewOwnersReplyLine) {
						// Remove leading/trailing whitespace and empty lines
						$reviewOwnersReplyLine = trim($reviewOwnersReplyLine);
						if (!empty($reviewOwnersReplyLine)) {
							$output_reviews .= '<p>' . $reviewOwnersReplyLine . '</p>';
						}
					}
					$output_reviews .= '</span>';
					$output_reviews .= '</div>';
					$output_reviews .= '</div>';
					$output_reviews .= '</div>';
					$output_reviews .= '</div>';
				}
				if (count($reviewsComments) > 1) {
					if ($firstReviewDisplayed) {
						$output_reviews .= '</div>';
					}
				}
				if (!$firstReviewDisplayed) {
					$firstReviewDisplayed = true; // Set the flag to true after the first review
				}
				$reviewCounter++;
			}
			$output_reviews .= '</div>';
			if (count($reviewsComments) > 1) {
				$output_reviews .= '<div id="loadmore-reviews-container"><div class="loadmore-relative"><button class="loadmore-reviews-button-wrapper" title="Load More" aria-label="Load More">Load More <span class="svg-loadmore"></span></button></div></div>';
				$output_reviews .= '<div id="loadless-reviews-container" class="hidden-review"><div class="loadless-relative"><button class="loadless-reviews-button-wrapper" title="Show Less" aria-label="Show Less">Show Less <span class="svg-loadmore"></span></button></div></div>';
			}
			$output_reviews .= '</div>';
			// End .customerReviews-container
			$output_reviews .= '</div>';
		}
		$output_reviews .= '</div>';
		// End #mainReviews
		$output_reviews .= '</div>';
		$output_reviews .= '</div>';
		$output_reviews .= '</div>';
		echo $output_reviews;
		
		/**********************3D virtual Tour *************/
		if (!empty($accommodation_matterport_video[0]['AccommodationMatterportVideo'])) {
			$output_three_d_virtual_tour = '<button class="accordion-accommodation-item" title="3D Virtual Tour" aria-label="3D Virtual Tour">3D Virtual Tour</button>';
			$output_three_d_virtual_tour .= '<div class="accordion-accommodation-content">';
			$output_three_d_virtual_tour .= '<div id="propertyInfo" class="box-left">';
			// Start #main3Dvt
			$output_three_d_virtual_tour .= '<div id="main3Dvt">';
			$output_three_d_virtual_tour .= '<div id="virtualTour"><iframe loading="lazy" width="853" height="480" src="' . $accommodation_matterport_video[0]['AccommodationMatterportVideo'].'"></iframe></div>';
			$output_three_d_virtual_tour .= '</div>';
			// End #main3Dvt
			$output_three_d_virtual_tour .= '</div>';
			$output_three_d_virtual_tour .= '</div>';
			echo $output_three_d_virtual_tour;
		}
		/**********************3D virtual Tour *************/

		/******* SECURITY DEPOSITS *******/
		$output_securitydeposit = '';
		// Loop through obligatory or included services
		if (!empty($descriptionsData['Extras']['ObligatoryOrIncluded'])) {
			foreach ($descriptionsData['Extras']['ObligatoryOrIncluded'] as $extraDescription) {
				if ($extraDescription['Name'] === 'Security Deposit (refundable)') {
					$output_securitydeposit .= '<button class="accordion-accommodation-item" title="' . $extraDescription['Name'] . '" aria-label="' . $extraDescription['Name'] . '">' . $extraDescription['Name'] . '</button>';
					$output_securitydeposit .= '<div class="accordion-accommodation-content">';
					$output_securitydeposit .= '<div id="propertyInfo" class="box-left">';
					// Start #mainSecurityDeposit
					$output_securitydeposit .= '<div id="mainSecurityDeposit">';
					$output_securitydeposit .= '<div class="features">';
					$output_securitydeposit .= '<div class="feature"><div> <span class="features-item-check"></span> <span class="feature-text"><b>Amount:</b> ' . $extraDescription['Description'] . '</span></div></div>';
					if (isset($accommodationData['ExtraPaymentMethod']) && $accommodationData['ExtraPaymentMethod'] != '') {
						// Translate Spanish payment method words to English
						$translations = [
							'TARJETA' => 'Credit Card',
							'DINERO' => 'Cash',
							'DINERO-TARJETA' => 'Cash or Credit Card',
						];
						$spanishPaymentMethod = $accommodationData['ExtraPaymentMethod'][0];
						$translatedText = isset($translations[$spanishPaymentMethod]) ? $translations[$spanishPaymentMethod] : $spanishPaymentMethod;
						$output_securitydeposit .= '<div class="feature"><div> <span class="features-item-check"></span> <span class="feature-text"><b>Payment method:</b> ' . $translatedText . '</span></div></div>';
					}
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
		$output_bookingrules = '<button class="accordion-accommodation-item" title="Important Information / Booking Conditions Accordion" aria-label="Important Information / Booking Conditions Accordion">Important Information / Booking Conditions</button>';
		$output_bookingrules .= '<div class="accordion-accommodation-content">';
		$output_bookingrules .= '<div id="propertyInfo" class="box-left">';
		// Start #mainBookingConditions
		$output_bookingrules .= '<div id="mainBookingConditions">';
		if (isset($result_GetBookingPrice)) {
			if (isset($result_GetBookingPrice->CancellationPolicies) && isset($result_GetBookingPrice->CancellationPolicies->Description)) {
				$output_bookingrules .= '<h3 class="subtitle-section">Cancellation policies</h3>';
				$output_bookingrules .= '<div class="features">';
				$bookingConditionLines = explode("\n", trim($result_GetBookingPrice->CancellationPolicies->Description));
				$output_bookingrules .= '<span>' . array_shift($bookingConditionLines) . '</span>';
				foreach ($bookingConditionLines as $bookingConditionLine) {
					$cleanedBookingConditionLine = trim(preg_replace('/^-+/', '', $bookingConditionLine));
					$output_bookingrules .= '<div class="feature"><div><span class="features-item-check"></span><span class="feature-text">' . $cleanedBookingConditionLine . '</span></div></div>';
				}
				$output_bookingrules .= '</div>';
			}
		}
		$output_bookingrules .= '<h3 class="subtitle-section">Additional notes</h3>';
		$output_bookingrules .= '<div class="features">';
		$output_bookingrules .= '<div class="feature"><div> <span class="features-item-check"></span> <span class="feature-text">We kindly ask you to phone us about your arrival time once you are at your destination</span></div></div>';
		$output_bookingrules .= '<div class="feature"><div> <span class="features-item-check"></span> <span class="feature-text">The keys must be picked up during the above mentioned hours only</span></div></div>';
		$output_bookingrules .= '</div>';
		$output_bookingrules .= '<h3 class="subtitle-section">Terms &amp; Conditions</h3>';
		$output_bookingrules .= '<div class="features">';
		$output_bookingrules .= '<div class="feature"><div> <span class="features-item-check"></span> <span class="feature-text">Please review our <a href="/general-conditions/" title="Terms & Conditions" aria-label="Terms & Conditions"><strong>Terms &amp; Conditions</strong></a> in the link provided.</span></div></div>';
		$output_bookingrules .= '</div>';
		$output_bookingrules .= '</div>';
		// End #mainBookingConditions
		$output_bookingrules .= '</div>';
		$output_bookingrules .= '</div>';
		echo $output_bookingrules;

		/******* COMMENTS *******/
		$output_comments = '<button class="accordion-accommodation-item" title="Coments Accordion" aria-label="Comments Accordion">Comments</button>';
		$output_comments .= '<div class="accordion-accommodation-content">';
		$output_comments .= '<div id="propertyInfo" class="box-left">';
		// Start #mainComments
		$output_comments .= '<div id="mainComments">';
		$output_comments .= '<div class="features">';
		$AcceptYoungsters = false;
		$SmokingAllowed = false;
		$PetsAllowed = false;
		if (isset($accommodationData['Comments']['AcceptYoungsters']) && $accommodationData['Comments']['AcceptYoungsters'] !== '') {
			if ($accommodationData['Comments']['AcceptYoungsters'] !== '' || $accommodationData['Comments']['AcceptYoungsters'] !== 'false') {
				$output_comments .= '<div class="feature"><div> <span class="features-item-check"></span> <span class="feature-text">This accommodation does not accept groups of young people (Up to 25 years)</span></div></div>';
				$AcceptYoungsters = false;
			} else {
				$AcceptYoungsters = true;
			}
		}
		if (isset($accommodationData['Comments']['SmokingAllowed']) && $accommodationData['Comments']['SmokingAllowed'] !== '') {
			if ($accommodationData['Comments']['SmokingAllowed'] !== '' || $accommodationData['Comments']['SmokingAllowed'] !== 'false') {
				$output_comments .= '<div class="feature"><div> <span class="features-item-check"></span> <span class="feature-text">No smoking allowed</span></div></div>';
				$SmokingAllowed = false;
			} else {
				$SmokingAllowed = true;
			}
		}
		$DangerousPetAllowed = false; $PetAllowed = false;
		if (!empty($accommodationData['ExtraServices'])) {
			foreach ($accommodationData['ExtraServices'] as $serviceDescription) {
				if ($serviceDescription === 'DangerousPetsAllowed') {
                    $DangerousPetAllowed = true;
                }
                if ($serviceDescription === 'Pet') {
					$PetAllowed = true;
				}
			}
			if(!$PetAllowed)
			$output_comments .= '<div class="feature"><div> <span class="features-item-check"></span> <span class="feature-text"> No dogs allowed</span></div></div>';
			if($PetAllowed && !$DangerousPetAllowed)
			$output_comments .= '<div class="feature"><div> <span class="features-item-check"></span> <span class="feature-text"> Animals are admitted to this accommodation, except for dangerous animals</span></div></div>';
		}
		if ($AcceptYoungsters && $SmokingAllowed && $PetsAllowed) {
			$output_comments .= '<div class="feature"><div> <span class="features-item-check"></span> <span class="feature-text"> No comments available</span></div></div>';
		}
		$output_comments .= '</div>';
		$output_comments .= '</div>';
		// End #mainComments
		$output_comments .= '</div>';
		$output_comments .= '</div>';
		// End .box-section
		echo $output_comments;

		/******* SIMILAR HOMES *******/
		if (isset($accommodationData['Features']['PeopleCapacity']) && $accommodationData['Features']['PeopleCapacity'] !== '') {
			$peopleCapacityValue = trim((string) $accommodationData['Features']['PeopleCapacity']);
			if ($peopleCapacityValue !== '' && $peopleCapacityValue !== '0') {
				$numPeopleOccupants = (int) $peopleCapacityValue;
			}
		} else if (isset($accommodationData['Features']['AdultsCapacity']) && $accommodationData['Features']['AdultsCapacity'] !== '') {
			$adultsCapacityValue = trim((string) $accommodationData['Features']['AdultsCapacity']);
			if ($adultsCapacityValue !== '' && $adultsCapacityValue !== '0') {
				$numPeopleOccupants = (int) $adultsCapacityValue;
			}
		} else {
			if (isset($accommodationData['Features']['MinimumOccupation']) && $accommodationData['Features']['MinimumOccupation'] !== '') {
				$minimumOccupationValue = trim((string) $accommodationData['Features']['MinimumOccupation']);
				if ($minimumOccupationValue !== '' && $minimumOccupationValue !== '0') {
					$numPeopleOccupants = (int) $minimumOccupationValue;
				}
			}
		}

		$plugin_url = plugins_url('', __FILE__);
		$similarProperties = getSimilarHomes($accommodationId, $numPeopleOccupants, $Bedrooms, $descriptionsData['Location']['CityName']);
		if (!empty($similarProperties)) {
			$counterSimilarHomes = 0;
			$output_similarhomes = '';
			// Loop through similar homes
			$output_similarhomes .= '<button class="accordion-accommodation-item active-accordion-accommodation" title="Similar Homes Accordion" aria-label="Similar Homes Accordion">Similar Homes</button>';
			$output_similarhomes .= '<div class="accordion-accommodation-content" style="display: block;">';
			// Start .similarHomesSwiper
			$output_similarhomes .= '<div id="sHomes" class="swiper similarHomesSwiper sHomes">';
			$output_similarhomes .= '<ul class="swiper-wrapper prop-grid-view">';
			// Loop through similar homes
			foreach ($similarProperties as $property) {
				if ((int)$property->AccommodationId === $accommodationId) {
					continue;
				}
				if ($counterSimilarHomes >= 10) {
					break;
				}
				$CityName_url = strtolower(trim((string)$property->CityName));
				$LocalityName_url = strtolower(trim((string)$property->LocalityName));
				$LocalityName_url = str_replace(array(",", "'"), '', $LocalityName_url);
				$LocalityName_url = preg_replace('/[\[\]()]/', '', $LocalityName_url);
				$LocalityName_url = str_replace(array("/", ".", "+"), '-', $LocalityName_url);
				$LocalityName_url = preg_replace('/\s+/', '-', $LocalityName_url);
				$LocalityName_url = preg_replace('/-+/', '-', $LocalityName_url);
				$AccommodationName_url = strtolower(trim((string)$property->AccommodationName));
				$AccommodationName_url = str_replace(array(",", "'", "&"), '', $AccommodationName_url);
				$AccommodationName_url = preg_replace('/[\[\]()]/', '', $AccommodationName_url);
				$AccommodationName_url = str_replace(array("/", ".", "+"), '-', $AccommodationName_url);
				$AccommodationName_url = preg_replace('/\s+/', '-', $AccommodationName_url);
				$AccommodationName_url = preg_replace('/-+/', '-', $AccommodationName_url);
				$full_prop_url = $CityName_url . '/' . $LocalityName_url . '/' . $AccommodationName_url . '/' . (int)$property->AccommodationId . '/';
				$output_similarhomes .= '<li class="swiper-slide-similarhomes similarhomes-wrapper" data-propid="' . $property->AccommodationId . '">';
				$output_similarhomes .= '<figure class="carousel-container">';
				$output_similarhomes .= '<div class="swiper-container">';
				$arrimages = json_decode($property->Images);
				if (!empty($arrimages)) {
					$imageList_SH = [];
					$pictureLimit_SH = 4;
					foreach ($arrimages as $pictures_SH) {
						$AdaptedURI = acco_image_url($pictures_SH->AdaptedURI);
						if (!empty($AdaptedURI) && count($imageList_SH) < $pictureLimit_SH) {
							$imageList_SH[] = '<li><a href="/property/' . $full_prop_url . '" title="' . $property->AccommodationName . '" aria-label="' . $pictures_SH->Name . '"><img src="' . $AdaptedURI . '" data-src="' . $AdaptedURI . '" loading="lazy" alt="' . $pictures_SH->Name . '"></a></li>';
						}
					}
					$carouselClass_SH = count($imageList_SH) === 0 || count($imageList_SH) === 1 ? 'search-carousel-single' : 'search-carousel';
					$output_similarhomes .= '<ul class="' . $carouselClass_SH . '">';
					$output_similarhomes .= implode('', $imageList_SH);
					$output_similarhomes .= '</ul>';
				} else {
					$output_similarhomes .= '<ul class="search-carousel-single">';
					$output_similarhomes .= '<li><a href="/property/' . $full_prop_url . '" title="' . $property->AccommodationName . '" aria-label="' . $property->AccommodationName . '"><img src="' . $plugin_url . '/images/empty-image.png" data-src="' . $plugin_url . '/images/empty-image.png" loading="lazy" alt="Empty Default Image"></a></li>';
					$output_similarhomes .= '</ul>';
				}
				$output_similarhomes .= '<div class="search-carousel-dots"></div>';
				$output_similarhomes .= '</div>';
				$output_similarhomes .= '<div class="occupants-bedrooms-box">';
				if (isset($property->PeopleCapacity)) {
					$peopleCapacityValue = trim((string) $property->PeopleCapacity);
					if ($peopleCapacityValue !== '' && $peopleCapacityValue !== '0') {
						$numPeopleCapacity = (int) $peopleCapacityValue;
						$output_similarhomes .= '<span class="occupants"><span><span class="svg-occupants" aria-hidden="true"></span><span class="tot-text" aria-hidden="true">' . $numPeopleCapacity . '</span><span class="visually-hidden">' . $numPeopleCapacity . ' Occupant' . ($numPeopleCapacity != 1 ? 's' : '') . '</span></span></span>';
					}
				} else if (isset($property->AdultsCapacity)) {
					$adultsCapacityValue = trim((string) $property->AdultsCapacity);
					if ($adultsCapacityValue !== '' && $adultsCapacityValue !== '0') {
						$numAdultsCapacity = (int) $adultsCapacityValue;
						$output_similarhomes .= '<span class="occupants"><span><span class="svg-occupants" aria-hidden="true"></span><span class="tot-text" aria-hidden="true">' . $numAdultsCapacity . '</span><span class="visually-hidden">' . $numAdultsCapacity . ' Occupant' . ($numAdultsCapacity != 1 ? 's' : '') . '</span></span></span>';
					}
				} else {
					if (isset($property->MinimumOccupation)) {
						$minimumOccupationValue = trim((string) $property->MinimumOccupation);
						if ($minimumOccupationValue !== '' && $minimumOccupationValue !== '0') {
							$numMinimumOccupation = (int) $minimumOccupationValue;
							$output_similarhomes .= '<span class="occupants"><span><span class="svg-occupants" aria-hidden="true"></span><span class="tot-text" aria-hidden="true">' . $numMinimumOccupation . '</span><span class="visually-hidden">' . $numMinimumOccupation . ' Occupant' . ($numMinimumOccupation != 1 ? 's' : '') . '</span><</span></span>';
						}
					}
				}
				if (isset($property->Bedrooms)) {
					$bedroomsValue = trim((string) $property->Bedrooms);
					if ($bedroomsValue !== '' && $bedroomsValue !== '0') {
						$numBedrooms = (int) $bedroomsValue;
						$output_similarhomes .= '<span class="bedrooms"><span><span class="svg-bedrooms" aria-hidden="true"></span><span class="tot-text" aria-hidden="true">' . $numBedrooms . '</span><span class="visually-hidden">' . $numBedrooms . ' Bedroom' . ($numBedrooms != 1 ? 's' : '') . '</span></span></span>';
					} else {
						$output_similarhomes .= '<span class="bedrooms"><span><span class="svg-bedrooms" aria-hidden="true"></span><span class="tot-text" aria-hidden="true">0</span><span class="visually-hidden">0 Bedrooms</span></span></span>';
					}
				} else {
					$output_similarhomes .= '<span class="bedrooms"><span><span class="svg-bedrooms" aria-hidden="true"></span><span class="tot-text" aria-hidden="true">0</span><span class="visually-hidden">0 Bedrooms</span></span></span>';
				}
				/*$output_similarhomes .= '</div>';
				$output_similarhomes .= '</figure>';
				$output_similarhomes .= '<div class="prop-content">';
				$output_similarhomes .= '<div class="offers-petfriendly">';*/
				$output_similarhomes .= '</div>
				</figure>
				<div class="prop-content">
				<div class="offers-petfriendly">';
				if ($property->ActiveOffers > 0) {
					$output_similarhomes .= '<span class="offers"><span><span class="svg-offers"></span> ' . $property->ActiveOffers . ' Active Offer' . ($property->ActiveOffers != 1 ? 's' : '') . '</span></span>';
				} else {
					$output_similarhomes .= '<span class="offers"></span>';
				}
				if (!empty($property->pet_friendly)) {
					$output_similarhomes .= '<span class="petfriendly"><div class="svg-pet-container"><span class="svg-petfriendly"></span></div> <div class="text-petfriendly">Dog Friendly</div></span>';
				}
				$output_similarhomes .= '</div>';
				$totalRatings = $property->TotalRatings;
				$totalReviews = $property->TotalReviews;
				if ($totalReviews > 0) {
					$averageRating = $totalRatings / $totalReviews;
					$convertedRatingOutOf5 = number_format(($averageRating / 10) * 5, 1);
					$averageRatingRounded = $convertedRatingOutOf5;
					if ($averageRatingRounded > 5) {
						$averageRatingConverted = '5';
					} else {
						$integerPart = floor($averageRatingRounded);
						$decimalPart = ($averageRatingRounded - $integerPart) * 10;
						$averageRatingConverted = sprintf("%d%d", $integerPart, $decimalPart);
					}
					$output_similarhomes .= '<div class="reviewsContentRates">';
					$output_similarhomes .= '<div class="star-ratings' . $averageRatingConverted . '" role="img" aria-label="Rating of this property out of 5"></div>';
					$output_similarhomes .= '<div class="reviewsAmt">' . $totalReviews . ' review' . ($totalReviews != 1 ? 's' : '') . '</div>';
					$output_similarhomes .= '<button class="favouritesProp" aria-label="Add to Favourites"><i class="far fa-heart fa-lg"></i></button>';
					$output_similarhomes .= '</div>';
				} else {
					$output_similarhomes .= '<div class="reviewsContentRates">';
					$output_similarhomes .= '<div class="star-ratings0" aria-label="Rating of this property out of 5"></div>';
					$output_similarhomes .= '<div class="reviewsAmt">No reviews yet</div>';
					$output_similarhomes .= '<button class="favouritesProp" aria-label="Add to Favourites"><i class="far fa-heart fa-lg"></i></button>';
					$output_similarhomes .= '</div>';
				}
				$output_similarhomes .= '<h3><a href="/property/' . $full_prop_url . '" title="' . $property->AccommodationName . '" aria-label="' . $property->AccommodationName . '">' . $property->AccommodationName . '</a></h3>';
				$namesAddress = array_filter([
					$property->DistrictName,
					$property->LocalityName,
					$property->CityName,
					$property->ProvinceName,
					$property->RegionName,
					$property->CountryName
				]);
				$uniqueNamesAddress = array_unique($namesAddress);
				$filteredNamesAddress = [];
				foreach ($uniqueNamesAddress as $name) {
					$name = trim($name);
					// Check for "Sin especificar" which is Spanish for "Not Specified"
					if (!empty($name) && ($name !== "Sin especificar" && $name !== "Not specified")) {
						// Convert "Irlanda" to "Ireland"
						if ($name === "Irlanda") {
							$name = "Ireland";
						}
						$filteredNamesAddress[] = $name;
					}
				}
				$formattedNamesAddress = implode(', ', $filteredNamesAddress);
				$output_similarhomes .= '<h4><span class="icon-box"><i class="fas fa-map-marker-alt fa-lg marginR5"></i></span><span class="text-box">' . $formattedNamesAddress . '</span></h4>';
				/*
				$output_similarhomes .= '<div class="features-swipe-wrapper features-swipe-blurred-list" id="features-swipe-section' . $counterSimilarHomes . '">';
				$output_similarhomes .= '<ul class="features-swipe">';
				if (isset($property->PeopleCapacity)) {
					$peopleCapacityValue = trim((string) $property->PeopleCapacity);
					if ($peopleCapacityValue !== '' && $peopleCapacityValue !== '0') {
						$numPeopleCapacity = (int) $peopleCapacityValue;
						$output_similarhomes .= '<li class="features-swipe-item no-bullet">Up to ' . $numPeopleCapacity . ' guest' . ($numPeopleCapacity != 1 ? 's' : '') . '</li>';
					}
				} else if (isset($property->AdultsCapacity)) {
					$adultsCapacityValue = trim((string) $property->AdultsCapacity);
					if ($adultsCapacityValue !== '' && $adultsCapacityValue !== '0') {
						$numAdultsCapacity = (int) $adultsCapacityValue;
						$output_similarhomes .= '<li class="features-swipe-item no-bullet">Up to ' . $numAdultsCapacity . ' guest' . ($numAdultsCapacity != 1 ? 's' : '') . '</li>';
					}
				} else {
					if (isset($property->MinimumOccupation)) {
						$minimumOccupationValue = trim((string) $property->MinimumOccupation);
						if ($minimumOccupationValue !== '' && $minimumOccupationValue !== '0') {
							$numMinimumOccupation = (int) $minimumOccupationValue;
							$output_similarhomes .= '<li class="features-swipe-item no-bullet">Min of ' . $numMinimumOccupation . ' guest' . ($numMinimumOccupation != 1 ? 's' : '') . '</li>';
						}
					}
				}
				if (!empty($property->Bedrooms)) {
					if ($bedroomsValue !== '' && $bedroomsValue !== '0') {
						$totalBeds = $property->Beds;
						$output_similarhomes .= '<li class="features-swipe-item">' . $property->Bedrooms . ' bedroom' . ($property->Bedrooms != 1 ? 's' : '') . ' (' . $totalBeds . ' bed' . ($totalBeds != 1 ? 's' : '') . ')</li>';
					}
				}
				if (is_string($property->Features)) {
				   $property->Features = json_decode($property->Features);
				}
				if (!empty($property->Features->BathroomWithBathtub)) {
					$numBathroomWithBathtub  = (int)$property->Features->BathroomWithBathtub;
					$output_similarhomes .= '<li class="features-swipe-item">' . $numBathroomWithBathtub  . ' bathroom' . ($numBathroomWithBathtub  != 1 ? 's' : '') . ' with bathtub</li>';
				}
				if (!empty($property->Features->BathroomWithShower)) {
					$numBathroomWithShower  = (int)$property->Features->BathroomWithShower;
					$output_similarhomes .= '<li class="features-swipe-item">' . $numBathroomWithShower  . ' bathroom' . ($numBathroomWithShower  != 1 ? 's' : '') . ' with shower</li>';
				}
				if (!empty($property->Features->Toilets)) {
					$numToilets = (int)$property->Features->Toilets;
					$output_similarhomes .= '<li class="features-swipe-item">' . $numToilets . ' toilet' . ($numToilets != 1 ? 's' : '') . '</li>';
				}
				if (!empty($property->AcceptYoungsters)) {
					$output_similarhomes .= '<li class="features-swipe-item">Children allowed</li>';
				}
				$output_similarhomes .= '</ul>';
				$output_similarhomes .= '<div class="paddles">';
				$output_similarhomes .= '<span class="left-features-paddle paddle hidden">';
				$output_similarhomes .= '<i class="chevron-left-features fas fa-chevron-left"></i>';
				$output_similarhomes .= '</span>';
				$output_similarhomes .= '<span class="right-features-paddle paddle">';
				$output_similarhomes .= '<i class="chevron-right-features fas fa-chevron-right"></i>';
				$output_similarhomes .= '</span>';
				$output_similarhomes .= '</div>';
				$output_similarhomes .= '</div>';
				*/
				if (!empty($property->WeeklyPrice) && $property->WeeklyPrice != '0.00') {
					$Currency = str_replace('EUR', '&euro;', $property->Currency);
					$WeeklyPrice = round($property->WeeklyPrice);
					$output_similarhomes .= '<span class="price"><span class="from">From</span> ' . $Currency . $WeeklyPrice . ' /week</span>';
				} else {
					$output_similarhomes .= '<span class="price"><a href="' . $descriptionsData['ContactURL'] . '" class="button-contact-sidebar contactParameters" title="Contact Us" aria-label="Contact Us" target="_blank" rel="nofollow">Contact Us</a></span>';
				}
				$output_similarhomes .= '</div>';
				$output_similarhomes .= '</li>';
				$counterSimilarHomes++;
			}
			$output_similarhomes .= '</ul>';
			$output_similarhomes .= '<div class="swiper-button-prev"></div>';
			$output_similarhomes .= '<div class="swiper-button-next"></div>';
			$output_similarhomes .= '</div>';
			// End .similarHomesSwiper
			$output_similarhomes .= '</div>';
		}
		// last accordion div and sticky
		$output_similarhomes .= '<span class="stop-sticky"></span>';
		$output_similarhomes .= '</div>';
		// End .accordion-accommodation
		$output_similarhomes .= '</div>';
		echo $output_similarhomes;
	} else {
		$output_noproperty = '<div class="box-section sticky-sidebar-container">';
		$output_noproperty .= '<div class="alert alert-danger">We are sorry that this property is not found. Please try again with another property.</div>';
		$output_noproperty .= '</div>';
		echo $output_noproperty;
	}
}
?>