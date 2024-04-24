<?php
/* save xml feeds data to mysql tables */
function import_feed($feed) {
	global $wpdb;
	$language = 'en';
	$company = 'james';
	$data = [];
	$plugin_dir = plugin_dir_path(__FILE__);
	switch($feed) {
		case 'Accommodations.xml':
			$accommodationsFile = $plugin_dir . 'feeds/' . $feed;
			$accommodationsOutput = file_get_contents($accommodationsFile);
			$accommodationsXml = simplexml_load_string($accommodationsOutput);
			$services = getServices();
			$accoids = [];
			$kinds = getKinds();
			$arrkinds = [];
			if (!empty($kinds)) {
				foreach($kinds as $kind) {
					$arrkinds[$kind->MasterKindCode] = $kind->MasterKindName;
				}
			}
			foreach ($accommodationsXml->Accommodation as $accommodation) {
				if ($accommodation->Company == $company) {
					$AcceptYoungsters = (string)$accommodation->Features->Distribution->AcceptYoungsters;
					$data['AccommodationId'] = (int)$accommodation->AccommodationId;
					$data['AccommodationName'] = (string)$accommodation->AccommodationName;
					$data['AccommodationUnits'] = (string)$accommodation->AccommodationUnits;
					$data['Currency'] = (string)$accommodation->Currency;
					$data['UserId'] = (int)$accommodation->UserId;
					$data['CompanyId'] = (int)$accommodation->CompanyId;
					$data['IdGallery'] = (int)$accommodation->IdGallery;
					$data['OccupationalRuleId'] = (int)$accommodation->OccupationalRuleId;
					$data['PriceModifierId'] = (string)$accommodation->PriceModifierId;
					$data['AcceptYoungsters'] = ($AcceptYoungsters == 'true') ? 1 : 0;
					$accoids[] = $data['AccommodationId'];
					$data['TrainStationDistance'] = getMinDistance('TRAIN', $accommodation);
					$data['SuperMarketDistance'] = getMinDistance('SUPERMARKET', $accommodation);
					$data['GolfDistance'] = getMinDistance('GOLF', $accommodation);
					$data['CityDistance'] = getMinDistance('TOWN', $accommodation);
					$data['StopBusDistance'] = getMinDistance('BUS', $accommodation);
					$data['BeachDistance'] = getMinDistance('BEACH', $accommodation);
					$data['AirportDistance'] = getMinDistance('AIRPORT', $accommodation);
					$DoubleBeds = isset($accommodation->Features->Distribution->DoubleBeds) ? (int)$accommodation->Features->Distribution->DoubleBeds : 0;
					$IndividualBeds = isset($accommodation->Features->Distribution->IndividualBeds) ? (int)$accommodation->Features->Distribution->IndividualBeds : 0;
					$IndividualSofaBed = isset($accommodation->Features->Distribution->IndividualSofaBed) ? (int)$accommodation->Features->Distribution->IndividualSofaBed : 0;
					$DoubleSofaBed = isset($accommodation->Features->Distribution->DoubleSofaBed) ? (int)$accommodation->Features->Distribution->DoubleSofaBed : 0;
					$QueenBeds = isset($accommodation->Features->Distribution->QueenBeds) ? (int)$accommodation->Features->Distribution->QueenBeds : 0;
					$KingBeds = isset($accommodation->Features->Distribution->KingBeds) ? (int)$accommodation->Features->Distribution->KingBeds : 0;
					$BunkBeds = isset($accommodation->Features->Distribution->Berths) ? (int)$accommodation->Features->Distribution->Berths : 0;
					$data['Beds'] = $DoubleBeds + $IndividualBeds + $IndividualSofaBed + $DoubleSofaBed + $QueenBeds + $KingBeds + $BunkBeds;
					$data['Bedrooms'] = (int)$accommodation->Features->Distribution->Bedrooms;
					$bathValue = 0;
					if (isset($accommodation->Features->Distribution->BathroomWithBathtub)) {
						$bathroomWithBathtubValue = trim((string) $accommodation->Features->Distribution->BathroomWithBathtub);
						if ($bathroomWithBathtubValue !== '' && $bathroomWithBathtubValue !== '0') {
							$bathValue += (int) $bathroomWithBathtubValue;
						}
					}
					if (isset($accommodation->Features->Distribution->BathroomWithShower)) {
						$bathroomWithShowerValue = trim((string) $accommodation->Features->Distribution->BathroomWithShower);
						if ($bathroomWithShowerValue !== '' && $bathroomWithShowerValue !== '0') {
							$bathValue += (int) $bathroomWithShowerValue;
						}
					}
					$data['Bathrooms'] = $bathValue;
					$reviews = [];
					$totalRatings = 0;
					$totalReviews = 0;
					$aspectRatings = [];
					if (is_array($accommodation->Reviews->Review) || is_object($accommodation->Reviews->Review)) {
						foreach ($accommodation->Reviews->Review as $guestReviews) {
							$rating = (int)$guestReviews->Rating;
							$totalRatings += $rating;
							$totalReviews++;
							$review = [];
							if (isset($guestReviews->GuestName)) {
								$review['GuestName'] = (string)$guestReviews->GuestName;
							}
							if (isset($guestReviews->Language)) {
								$review['Language'] = (string)$guestReviews->Language;
							}
							if (isset($guestReviews->Rating)) {
								$review['GeneralRating'] = (string)$guestReviews->Rating;
							}
							if (isset($guestReviews->Title)) {
								$review['Title'] = (string)$guestReviews->Title;
							}
							if (isset($guestReviews->PositiveComment)) {
								$review['PositiveComment'] = (string)$guestReviews->PositiveComment;
							}
							if (isset($guestReviews->NegativeComment)) {
								$review['NegativeComment'] = (string)$guestReviews->NegativeComment;
							}
							if (isset($guestReviews->Reply)) {
								$review['OwnersReply'] = (string)$guestReviews->Reply;
							}
							$aspects = [];
							foreach ($guestReviews->RatingAspects->RatingAspect as $aspect) {
								$aspectType = (string)$aspect->AspectType;
								$aspectRating = (int)$aspect->Rating;
								if (!isset($aspectRatings[$aspectType])) {
									$aspectRatings[$aspectType] = ['totalAR' => 0, 'countAR' => 0];
								}
								$aspectRatings[$aspectType]['totalAR'] += $aspectRating;
								$aspectRatings[$aspectType]['countAR']++;
								$aspectData = [];
								if (isset($aspect->AspectType)) {
									$aspectData['AspectType'] = (string)$aspect->AspectType;
								}
								if (isset($aspect->Rating)) {
									$aspectData['AspectRating'] = (string)$aspect->Rating;
								}
								$aspects[] = $aspectData;
							}
							$review['RatingAspects'] = $aspects;
							if (isset($guestReviews->BookingStartDate)) {
								$review['BookingStartDate'] = (string)$guestReviews->BookingStartDate;
							}
							if (isset($guestReviews->BookingEndDate)) {
								$review['BookingEndDate'] = (string)$guestReviews->BookingEndDate;
							}
							if (isset($guestReviews->ReviewDate)) {
								$review['ReviewDate'] = (string)$guestReviews->ReviewDate;
							}
							$reviews[] = $review;
						}
					}
					// Calculate average aspect ratings for each aspect type
					$averageAspectRatings = [];
					foreach ($aspectRatings as $aspectType => $datas) {
						$averageAspectRatings[$aspectType] = $datas['totalAR'] / $datas['countAR'];
					}
					$data['TotalRatings'] = $totalRatings;
					$data['TotalReviews'] = $totalReviews;
					$data['Reviews'] = json_encode($reviews);
					$data['AverageAspectRatings'] = json_encode($averageAspectRatings);
					$data['LocalizationData'] = json_encode(array(
						'RegionCode' => (string)$accommodation->LocalizationData->Region->RegionCode,
						'RegionName' => (string)$accommodation->LocalizationData->Region->Name,
						'CountryCode' => (string)$accommodation->LocalizationData->Country->CountryCode,
						'CountryISOCode' => (string)$accommodation->LocalizationData->Country->ISOCode,
						'CountryName' => (string)$accommodation->LocalizationData->Country->Name,
						'ResortCode' => (string)$accommodation->LocalizationData->Resort->ResortCode,
						'ResortName' => (string)$accommodation->LocalizationData->Resort->Name,
						'CityCode' => (string)$accommodation->LocalizationData->City->CityCode,
						'CityName' => (string)$accommodation->LocalizationData->City->Name,
						'ProvinceCode' => (string)$accommodation->LocalizationData->Province->ProvinceCode,
						'ProvinceName' => (string)$accommodation->LocalizationData->Province->Name,
						'LocalityCode' => (string)$accommodation->LocalizationData->Locality->LocalityCode,
						'LocalityName' => (string)$accommodation->LocalizationData->Locality->Name,
						'DistrictCode' => (string)$accommodation->LocalizationData->District->DistrictCode,
						'DistrictName' => (string)$accommodation->LocalizationData->District->Name,
						'KindOfWay' => (string)$accommodation->LocalizationData->KindOfWay,
						'Way' => (string)$accommodation->LocalizationData->Way,
						'Number' => (string)$accommodation->LocalizationData->Number,
						'Block' => (string)$accommodation->LocalizationData->Block,
						'Door' => (string)$accommodation->LocalizationData->Door,
						'Floor' => (string)$accommodation->LocalizationData->Floor,
						'GoogleLatitude' => (string)$accommodation->LocalizationData->GoogleMaps->Latitude,
						'GoogleLongitude' => (string)$accommodation->LocalizationData->GoogleMaps->Longitude,
						'GoogleZoom' => (string)$accommodation->LocalizationData->GoogleMaps->Zoom,
					));
					$data['Features'] = json_encode(array(
						'MinimumOccupation' => (string)$accommodation->Features->Distribution->MinimumOccupation,
						'PeopleCapacity' => (string)$accommodation->Features->Distribution->PeopleCapacity,
						'AcceptYoungsters' => (string)$accommodation->Features->Distribution->AcceptYoungsters,
						'AdultsCapacity' => (string)$accommodation->Features->Distribution->AdultsCapacity,
						'OccupationWithoutSupplement' => (string)$accommodation->Features->Distribution->OccupationWithoutSupplement,
						'Bedrooms' => (string)$accommodation->Features->Distribution->Bedrooms,
						'DoubleBeds' => (string)$accommodation->Features->Distribution->DoubleBeds,
						'IndividualBeds' => (string)$accommodation->Features->Distribution->IndividualBeds,
						'IndividualSofaBed' => (string)$accommodation->Features->Distribution->IndividualSofaBed,
						'DoubleSofaBed' => (string)$accommodation->Features->Distribution->DoubleSofaBed,
						'QueenBeds' => (string)$accommodation->Features->Distribution->QueenBeds,
						'KingBeds' => (string)$accommodation->Features->Distribution->KingBeds,
						'Toilets' => (string)$accommodation->Features->Distribution->Toilets,
						'BathroomWithBathtub' => (string)$accommodation->Features->Distribution->BathroomWithBathtub,
						'BathroomWithShower' => (string)$accommodation->Features->Distribution->BathroomWithShower,
						'Berths' => (string)$accommodation->Features->Distribution->Berths,
						'AreaHousingArea' => (string)$accommodation->Features->Distribution->AreaHousing->Area,
						'AreaHousingUnit' => (string)$accommodation->Features->Distribution->AreaHousing->AreaUnit,
						'AreaPlotArea' => (string)$accommodation->Features->Distribution->AreaPlot->Area,
						'AreaPlotUnit' => (string)$accommodation->Features->Distribution->AreaPlot->AreaUnit,
					));
					$data['AdultsCapacity'] = (string)$accommodation->Features->Distribution->AdultsCapacity;
					$data['PeopleCapacity'] = (string)$accommodation->Features->Distribution->PeopleCapacity;
					$totalDouble = $DoubleBeds + $QueenBeds + $KingBeds;
		            $totalDoubleSofa = $DoubleSofaBed;
		            $totalSingle = $IndividualBeds + $IndividualSofaBed;
		            $totalBeds = $totalDouble + $totalSingle + $totalDoubleSofa + $BunkBeds;
		            $totalOccupants = (($totalDouble + $totalDoubleSofa + $BunkBeds) * 2) + $totalSingle;
					$data['OccupantsCapacity'] = (string)$totalOccupants;
					$data['MinimumOccupation'] = (string)$accommodation->Features->Distribution->MinimumOccupation;
					$data['RegionCode'] = (string)$accommodation->LocalizationData->Region->RegionCode;
					$data['RegionName'] = (string)$accommodation->LocalizationData->Region->Name;
					$data['CountryName'] = (string)$accommodation->LocalizationData->Country->Name;
					$data['ResortCode'] = (string)$accommodation->LocalizationData->Resort->ResortCode;
					$data['ResortName'] = (string)$accommodation->LocalizationData->Resort->Name;
					$data['CityCode'] = (string)$accommodation->LocalizationData->City->CityCode;
					$data['CityName'] = (string)$accommodation->LocalizationData->City->Name;
					$data['ProvinceCode'] = (string)$accommodation->LocalizationData->Province->ProvinceCode;
					$data['ProvinceName'] = (string)$accommodation->LocalizationData->Province->Name;
					$data['LocalityCode'] = (string)$accommodation->LocalizationData->Locality->LocalityCode;
					$data['LocalityName'] = (string)$accommodation->LocalizationData->Locality->Name;
					$data['DistrictCode'] = (string)$accommodation->LocalizationData->District->DistrictCode;
					$data['DistrictName'] = (string)$accommodation->LocalizationData->District->Name;
					$data['Labels'] = json_encode((array)$accommodation->Labels->Label);
					$pet_friendly = 0;
					$internet = 0;
					$fireplace = 0;
					$pool = 0;
					$charger = 0;
					$disabled_friendly = 0; 
					$amazing_views = 0;
					$beachfront = 0;
					$popular = 0;
					$longtermrental = 0;
					$beachfront = (string)$accommodation->Features->Location->LocationViews->ViewToBeach === 'true';
					$view_to_lake = (string)$accommodation->Features->Location->LocationViews->ViewToLake === 'true';
					$view_to_pool = (string)$accommodation->Features->Location->LocationViews->ViewToSwimmingPool === 'true';
					$view_to_golf = (string)$accommodation->Features->Location->LocationViews->ViewToGolf === 'true';
					$view_to_garden = (string)$accommodation->Features->Location->LocationViews->ViewToGarden === 'true';
					$view_to_river = (string)$accommodation->Features->Location->LocationViews->ViewToRiver === 'true';
					$view_to_mountain = (string)$accommodation->Features->Location->LocationViews->ViewToMountain === 'true';
					if ($beachfront || $view_to_lake || $view_to_pool || $view_to_golf || $view_to_garden || $view_to_river || $view_to_mountain ) {
						$amazing_views = 1;
					}
					foreach ($accommodation->Labels->Label as $labelsToFilter) {
						if ($labelsToFilter == 'tpservices') {
							$longtermrental = 1;
						}
						if (strtolower($labelsToFilter) === 'electric car charger') {
								$charger = 1;
						}
					}
					if ($totalReviews > 0) {
						$averageRating = $totalRatings / $totalReviews;
					} else {
						$averageRating = 0;
					}
					$convertedRatingOutOf5 = number_format(($averageRating / 10) * 5, 1);
					$averageRatingRounded = $convertedRatingOutOf5;
					if ((float)$averageRatingRounded >= 4.5) {
	                	$popular = 1;
	                }
					$extraservices = [];
					$pet_dangerous_allowed = 0;
					foreach ($accommodation->Features->ExtrasAndServices->SpecialServices->SpecialService as $service) {
						if ((int)$service->Code === 9) {
							if ($service->Allowed == 'si') {
								$pet_friendly = 1;
								if ($service->DangerousPetsAllowed == 'true') {
									$pet_dangerous_allowed = 1;
								}
							}
						}
						$normalizedSpecialServices = [];
						foreach ($service as $keySpecialServices => $valueSpecialServices) {
							$normalizedSpecialServices[trim((string)$keySpecialServices)] = trim((string)$valueSpecialServices);
						}
						$type = '';
						$changeFrequency = '';
						$countableLimit = '';
						if (isset($normalizedSpecialServices['Type'])) {
							if (!empty($services)) {
								foreach ($services as $s) {
									if ($s->ServiceCode == $normalizedSpecialServices['Code']) {
										$type = $s->ServiceName;
										break;
									}
								}
							} else {
								$type = $normalizedSpecialServices['Type'];
							}
						}
						if (isset($normalizedSpecialServices['ChangeBedClothes']) && $normalizedSpecialServices['ChangeBedClothes'] == 'true') {
							$changeFrequency = " (change bed clothes " . $normalizedSpecialServices['ChangeFrequency'] . " times)";
						}
						if (isset($normalizedSpecialServices['ChangeTowels']) && $normalizedSpecialServices['ChangeTowels'] == 'true') {
							$changeFrequency = " (change towels " . $normalizedSpecialServices['ChangeFrequency'] . " times)";
						}
						if (isset($normalizedSpecialServices['Countable']) && $normalizedSpecialServices['Countable'] == 'true') {
							$countableLimit = " (" . $normalizedSpecialServices['CountableLimit'] . " spaces)";
						}
						if (isset($normalizedSpecialServices['DangerousPetsAllowed'])) {
					        $changeFrequency = "";
					        $countableLimit = "";
					    }
						// Build the service description
						$serviceDescription = trim($type . ' ' . $changeFrequency . ' ' . $countableLimit);
					    // Add service description only if there's relevant information
					    if (!empty($serviceDescription)) {
					        $extraservices[] = $serviceDescription;
					    }
					}
					$data['ExtraServices'] = json_encode($extraservices);
					if (!empty($extraservices)) {
						foreach ($extraservices as $serviceDesc) {
							if ($serviceDesc === 'Internet Access') {
								$internet = 1;
							}
							if ($serviceDesc === 'Pet') {
								$pet_friendly = 1;
							}
							if ($serviceDesc === 'Heating') {
								$fireplace = 1;
							}
							
						}
					}
					$optionTitles = [];
					$characteristics = [];
					processCharacteristics($accommodation->Features->HouseCharacteristics, $characteristics, $optionTitles);
					if (!empty($optionTitles)) {
						foreach ($optionTitles as $optionCharacteristics) {
							if (in_array($optionCharacteristics, ['SwimmingPool', 'TVSatellite', 'HandicappedFacilities', 'Kitchen'])) {
								if ($optionCharacteristics == 'SwimmingPool') {
									$pool = 1;
								}
								if ($optionCharacteristics == 'HandicappedFacilities') {
									$disabled_friendly = 1;
								}
							}
						}
					}
					if (in_array((string)$accommodation->Features->HouseCharacteristics->HandicappedFacilities, ['true', '1', 'yes', 'apta-discapacitados'])) {
						$disabled_friendly = 1;
					} else if (in_array((string)$accommodation->Features->HouseCharacteristics->HandicappedFacilities, ['sin-escaleras'])) {
						$disabled_friendly = 0;
					}
					$data['CharacteristicsOptionTitles'] = json_encode($optionTitles);
					$data['internet'] = $internet;
					$data['pet_friendly'] = $pet_friendly;
					$data['pet_dangerous_allowed'] = $pet_dangerous_allowed;
					$data['fireplace'] = $fireplace;
					$data['charger'] = $charger;
					$data['pool'] = $pool;
					$data['disabled_friendly'] = $disabled_friendly;
					$data['popular'] = $popular;
					$data['beachfront'] = $beachfront;
					$data['amazing_views'] = $amazing_views;
					$data['longtermrental'] = $longtermrental;
					$MasterKindCode = (int)$accommodation->MasterKind->MasterKindCode;
					$data['PropertyType'] = !empty($arrkinds[$MasterKindCode]) ? $arrkinds[$MasterKindCode] : '';
					$data['DateUpdated'] = date('Y-m-d H:i:s');
					$accommodations_table = $wpdb->prefix . "accommodations";
					$result = $wpdb->update($accommodations_table, $data, array('AccommodationId' => $data["AccommodationId"]));
					if ($result === FALSE || $result < 1) {
						$wpdb->insert($accommodations_table, $data);
					}
				}
			}
			if (!empty($accoids)) {
				//$wpdb->query("DELETE FROM " . $wpdb->prefix . "accommodations WHERE AccommodationId NOT IN(".implode(',', array_map( 'absint', $accoids)).")");
				$accommodations_table = $wpdb->prefix . "accommodations";
				$accoids_imp = implode(',', array_fill(0, count($accoids), '%d'));
			    $prepared_query = $wpdb->prepare("DELETE FROM " . $accommodations_table  . " WHERE AccommodationId NOT IN($accoids_imp)", $accoids);
			    $wpdb->query($prepared_query);
			}
		break;
	
		case 'Descriptions.xml':
			$descriptionsFile = $plugin_dir . 'feeds/' . $feed;
			$descriptionsOutput = file_get_contents($descriptionsFile);
			$descriptionsXml = simplexml_load_string($descriptionsOutput);
			foreach ($descriptionsXml->Accommodation as $accommodationDesc) {
				$images = array();
				foreach ($accommodationDesc->Pictures->Picture as $picture) {
					$AdaptedURI = (string)$picture->AdaptedURI;
					$image = array(
						'Name' => (string)$picture->Name,
						'Type' => (string)$picture->Type,
						'Description' => (string)$picture->Description,
						'ThumbnailURI' => (string)$picture->ThumbnailURI,
						'AdaptedURI' => $AdaptedURI,
						'OriginalURI' => (string)$picture->OriginalURI,
					);
					$images[] = $image;
				}
				$totalBookingAmount = 0.0;
				foreach ($accommodationDesc->InternationalizedItem as $item) {
					if ($item->Language == $language) {
						foreach ($item->ExtrasSummary->ObligatoryOrIncluded->Extra as $extra) {
							$Name = (string)$extra->Name;
							$Description = (string)$extra->Description;
							if ($Name !== 'Security Deposit (refundable)') {
								// Check for the specific strings in the description and extract the number
								if (strpos($Description, '/ booking') !== false || strpos($Description, '/booking') !== false) {
									preg_match('/\b\d+(\.\d+)?\b/', $Description, $matches);
									if (!empty($matches)) {
										// Convert the first match to a float and add it to the total
										$totalBookingAmount += floatval($matches[0]);
									}
								}
								if (strpos($Description, '/ day') !== false || strpos($Description, '/day') !== false) {
									// Extract the number, multiply by 7 and add to the total booking amount
									preg_match('/\b\d+(\.\d+)?\b/', $Description, $matches);
									if (!empty($matches)) {
										// Convert the first match to a float, multiply by 7, and add it to the total
										$totalBookingAmount += floatval($matches[0]) * 7;
									}
								}
							}
						}
					}
				}
				if (!empty($images)) {
					$AccommodationId = (int)$accommodationDesc->AccommodationId;
					$accommodations_table = $wpdb->prefix . "accommodations";
				    $images_json = json_encode($images);
				    $currentWeeklyRateQuery = $wpdb->prepare("SELECT WeeklyRate FROM " . $accommodations_table . " WHERE AccommodationId = %d", $AccommodationId);
				    $currentWeeklyRate = $wpdb->get_var($currentWeeklyRateQuery);
				    if (!empty($currentWeeklyRate) && $currentWeeklyRate != 0) {
					    $newWeeklyPrice = $currentWeeklyRate + $totalBookingAmount;
					} else {
					    $newWeeklyPrice = $currentWeeklyRate;
					}
				    $query = $wpdb->prepare("UPDATE " . $accommodations_table . " SET WeeklyPrice = %f, Images = %s WHERE AccommodationId = %d", $newWeeklyPrice, $images_json, $AccommodationId);
				    $wpdb->query($query);
				}
			}
		break;
		
		case 'Rates.xml':
			$ratesFile = $plugin_dir . 'feeds/' . $feed;
			$ratesOutput = file_get_contents($ratesFile);
			$ratesXml = simplexml_load_string($ratesOutput);
			foreach($ratesXml->AccommodationList->Accommodation as $rate) {
				if (!empty($rate)) {
					$AccommodationId = (string)$rate->AccommodationId;
					$weeklyprice = getWeeklyPrice($rate);
					$accommodations_table = $wpdb->prefix . "accommodations";
					$wpdb->update($accommodations_table, ['WeeklyRate' => $weeklyprice, 'Rates' => json_encode($rate)], ['AccommodationId' => $AccommodationId]);
				}
			}
		break;
	
		case 'Availabilities.xml':
			$availabilitiesFile = $plugin_dir . 'feeds/' . $feed;
			$availabilitiesOutput = file_get_contents($availabilitiesFile);
			$availabilitiesXml = simplexml_load_string($availabilitiesOutput);
			foreach ($availabilitiesXml->AccommodationList->Accommodation as $availability) {
				if (!empty($availability)) {
					$data = array(
						'AccommodationId' => (string)$availability->AccommodationId,
						'OccupationalRuleId' => (string)$availability->OccupationalRuleId,
						'MinDaysNotice' => (string)$availability->MinDaysNotice,
						'Availabilities' => json_encode($availability->Availabilities)
					);
					$data['DateUpdated'] = date('Y-m-d H:i:s');
					$availabilities_table = $wpdb->prefix . "availabilities";
					$result = $wpdb->update($availabilities_table, $data, array('AccommodationId' => $data["AccommodationId"]));
					if ($result === FALSE || $result < 1) {
						$wpdb->insert($availabilities_table, $data);
					}
				}
			}
		break;
		
		case 'OccupationalRules.xml':
			$occupationalRulesFile = $plugin_dir . 'feeds/' . $feed;
			$occupationalRulesOutput = file_get_contents($occupationalRulesFile);
			$occupationalRulesXml = simplexml_load_string($occupationalRulesOutput);
			foreach ($occupationalRulesXml->OccupationalRule as $occupationalRules) {
				if (!empty($occupationalRules)) {
					$data = array(
						'Id' => (string)$occupationalRules->Id,
						'Name' => (string)$occupationalRules->Name
					);
					$seasons = [];
					$nights = 0;
					foreach ($occupationalRules->Season as $season) {
						$seasons[] = $season;
						$nights += (int)$season->MinimumNights;
					}
					$data['Seasons'] = json_encode($seasons);
					$data['DateUpdated'] = date('Y-m-d H:i:s');
					$occupationalrules_table = $wpdb->prefix . "occupationalrules";
					$result = $wpdb->update($occupationalrules_table, $data, array('Id' => $data["Id"]));
					if ($result === FALSE || $result < 1) {
						$wpdb->insert($occupationalrules_table, $data);
					}
					$accommodations_table = $wpdb->prefix . "accommodations";
					$wpdb->update($accommodations_table, ['Nights' => $nights], array('OccupationalRuleId' => $data["Id"]));
				}
			}
		break;
		
		case 'PriceModifiers.xml':
			$priceModifiersFile = $plugin_dir . 'feeds/' . $feed;
			$priceModifiersOutput = file_get_contents($priceModifiersFile);
			$priceModifiersXml = simplexml_load_string($priceModifiersOutput);
			foreach ($priceModifiersXml->PriceModifier as $priceModifiers) {
				if (!empty($priceModifiers)) {
					$data = array(
						'Id' => (string)$priceModifiers->Id,
						'Name' => (string)$priceModifiers->Name
					);
					$seasons = [];
					$totalOffersPM = 0;
					foreach ($priceModifiers->Season as $season) {
						$seasons[] = $season;
						$minNightsAllowed = (int)$season->MinNumberOfNights;
                        $numNightsAllowed = (int)$season->NumberOfNights;
                        // Skip this offer if MinNumberOfNights or NumberOfNights is less than 7
                        if ($numNightsAllowed < 7) {
                            continue;
                        }
						$amountPM = (float)$season->Amount;
						// Check if the Amount is not positive
						if ($amountPM <= 0) {
							$totalOffersPM++;
						}
					}
					$data['Seasons'] = json_encode($seasons);
					$data['DateUpdated'] = date('Y-m-d H:i:s');
					$pricemodifiers_table = $wpdb->prefix . "pricemodifiers";
					$result = $wpdb->update($pricemodifiers_table, $data, array('Id' => $data["Id"]));
					if ($result === FALSE || $result < 1) {
						$wpdb->insert($wpdb->prefix . 'pricemodifiers', $data);
					}
					$accommodations_table = $wpdb->prefix . "accommodations";
					$wpdb->update($accommodations_table, ['ActiveOffers' => $totalOffersPM], array('PriceModifierId' => $data["Id"]));
				}
			}
		break;
		case 'Services.xml':
			$servicesFile = $plugin_dir . 'feeds/' . $feed;
			$servicesOutput = file_get_contents($servicesFile);
			$servicesXml = simplexml_load_string($servicesOutput);
			foreach ($servicesXml->Service as $services) {
				$data = array(
					'ServiceCode' => (string)$services->Code,
					'ServiceName' => '',
					'DateUpdated' => date('Y-m-d H:i:s')
				);
				foreach ($services->Name as $name) {
					if ($name->Language == $language) {
						$data['ServiceName'] = (string)$name->Text;
					}
				}
				$services_table = $wpdb->prefix . "services";
				$result = $wpdb->update($services_table, $data, array('ServiceCode' => $data["ServiceCode"]));
				if ($result === FALSE || $result < 1) {
					$wpdb->insert($services_table, $data);
				}
			}
		break;
		case 'Kinds.xml':
			$kindsFile = $plugin_dir . 'feeds/' . $feed;
			$kindsOutput = file_get_contents($kindsFile);
			$kindsXml = simplexml_load_string($kindsOutput);
			foreach ($kindsXml->InternationalizedKinds as $internationalizedKind) {
				if (strtolower(trim((string)$internationalizedKind->Language)) === strtolower(trim($language))) {
					foreach ($internationalizedKind->MasterKind as $kind) {
						if (!empty($kind)) {
							$data = array(
								'MasterKindCode' => (string)$kind->MasterKindCode,
								'MasterKindName' => (string)$kind->MasterKindName,
							);
							$data['DateUpdated'] = date('Y-m-d H:i:s');
							$kinds_table = $wpdb->prefix . "kinds";
							$result = $wpdb->update($kinds_table, $data, array('MasterKindCode' => $data["MasterKindCode"]));
							if ($result === FALSE || $result < 1) {
								$wpdb->insert($kinds_table, $data);
							}
						}
					}
				}
			}
		break;
		default:
		break;
	}
}

function getWeeklyPrice($rate, $dateFrom = '') {
    if (empty($dateFrom)) {
        $dateFrom = date('Y-m-d');
    }
    $weeklyprice = null;
    $lowestPrice = null;
    $priceFound = false;
    if (!empty($rate)) {
        foreach ($rate->Rates->RatePeriod as $ratePeriod) {
            if ($dateFrom >= $ratePeriod->StartDate && $dateFrom <= $ratePeriod->EndDate) {
                foreach ($ratePeriod as $accommPlan) {
                    if (isset($accommPlan->Type) && isset($accommPlan->Price)) {
                        $priceValue = (float)$accommPlan->Price;
                        if ($lowestPrice === null || $priceValue < $lowestPrice) {
                            $lowestPrice = $priceValue;
                            $priceFound = true;
                        }
                    }
                }
            }
        }
        if (!$priceFound) {
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
        if ($lowestPrice !== null) {
            $weeklyprice = $lowestPrice * 7;
        }
    }
    return $weeklyprice;
}

function getMinDistance($place, $acco) {
	$value = null; $prev = 100000;
	if (!empty($acco->Features->Location->NearestPlaces->NearestPlace)) {
		foreach($acco->Features->Location->NearestPlaces->NearestPlace as $NearestPlace) {
			$PlaceType = (string)$NearestPlace->PlaceType;
			if ($PlaceType == $place) {
				$value = (float)$NearestPlace->Value;
				if ($value < $prev) {
					$prev = $value;
				}
			}
		}
	}
	if ($prev < 100000) {
		return $prev;
	} else {
		if (($place == 'TRAIN') && !empty($acco->Features->Location->LocationDistances->TrainStationDistance)) {
			$value = (float)$acco->Features->Location->LocationDistances->TrainStationDistance->Value;
		}
		if (($place == 'SUPERMARKET') && !empty($acco->Features->Location->LocationDistances->SuperMarketDistance)) {
			$value = (float)$acco->Features->Location->LocationDistances->SuperMarketDistance->Value;
		}
		if (($place == 'GOLF') && !empty($acco->Features->Location->LocationDistances->GolfDistance)) {
			$value = (float)$acco->Features->Location->LocationDistances->GolfDistance->Value;
		}
		if (($place == 'TOWN') && !empty($acco->Features->Location->LocationDistances->CityDistance)) {
			$value = (float)$acco->Features->Location->LocationDistances->CityDistance->Value;
		}
		if (($place == 'BUS') && !empty($acco->Features->Location->LocationDistances->StopBusDistance)) {
			$value = (float)$acco->Features->Location->LocationDistances->StopBusDistance->Value;
		}
		if (($place == 'BEACH') && !empty($acco->Features->Location->LocationDistances->BeachDistance)) {
			$value = (float)$acco->Features->Location->LocationDistances->BeachDistance->Value;
		}
		if (($place == 'AIRPORT') && !empty($acco->Features->Location->LocationDistances->AirportDistance)) {
			$value = (float)$acco->Features->Location->LocationDistances->AirportDistance->Value;
		}
	}
	return $value;	
}

function getServices() {
	global $wpdb;
	$services_table = $wpdb->prefix . "services";
	$query = "SELECT * FROM " . $services_table;
	$rows = $wpdb->get_results($query);
	return $rows;
}

function getKinds() {
	global $wpdb;
	$kinds_table = $wpdb->prefix . "kinds";
	$query = "SELECT * FROM " . $kinds_table;
	$rows = $wpdb->get_results($query);
	return $rows;
}

function getPriceModifier($id) {
	global $wpdb;
	$id = (int)$id;
	$pricemodifiers_table = $wpdb->prefix . "pricemodifiers";
	$query = $wpdb->prepare("SELECT * FROM " . $pricemodifiers_table . " WHERE Id = %d", $id);
	$row = $wpdb->get_row($query);
	return $row;
}

function getOccupationalRule($id) {
	global $wpdb;
	$id = (int)$id;
	$occupationalrules_table = $wpdb->prefix . "occupationalrules";
	$query = $wpdb->prepare("SELECT * FROM " . $occupationalrules_table . " WHERE Id = %d", $id);
	$row = $wpdb->get_row($query);
	return $row;
}

function getAllAccommodations() {
	global $wpdb;
	$accommodations_table = $wpdb->prefix . "accommodations";
	$query = "SELECT * FROM " . $accommodations_table;
	$rows = $wpdb->get_results($query);
	return $rows;
}

function getAccommodation($id) {
	global $wpdb;
	$id = (int)$id;
	$accommodations_table = $wpdb->prefix . "accommodations";
	$query = $wpdb->prepare("SELECT * FROM " . $accommodations_table . " WHERE AccommodationId = %d", $id);
	$row = $wpdb->get_row($query);
	return $row;
}

function getAvailability($id) {
	global $wpdb;
	$id = (int)$id;
	$availabilities_table = $wpdb->prefix . "availabilities";
	$query = $wpdb->prepare("SELECT * FROM " . $availabilities_table . " WHERE AccommodationId = %d", $id);
	$row = $wpdb->get_row($query);
	return $row;
}

function getAvailabilities($dateFrom = '', $dateTo = '') {
	global $wpdb;
	$availabilities_table = $wpdb->prefix . "availabilities";
	$query = "SELECT * FROM " . $availabilities_table;
	$rows = $wpdb->get_results($query);
	$availabilities = [];
	$tomorrows_date = date('Y-m-d', strtotime('+1 day'));
	if (!empty($rows) && $dateFrom) {
        foreach ($rows as $availability) {
            if (!empty($availability->Availabilities)) {
				$Availabilities = json_decode($availability->Availabilities);
				if (is_array($Availabilities->AvailabilityPeriod)) {
					foreach ($Availabilities->AvailabilityPeriod as $period) {
						if ($period && in_array($period->State, ['AVAILABLE', 'ONREQUEST'])) {
							$startDate_org = (string) $period->StartDate;
							$endDate = (string) $period->EndDate;
							$startDate = ($startDate_org >= $tomorrows_date) ? $startDate_org : $tomorrows_date;
							if ($dateFrom >= $startDate && $dateTo <= $endDate) {
								$availabilities[] = (int) $availability->AccommodationId;
							}
						}
					}
				} else {
					$period = $Availabilities->AvailabilityPeriod;
					if ($period && in_array($period->State, ['AVAILABLE', 'ONREQUEST'])) {
						$startDate_org = (string) $period->StartDate;
						$endDate = (string) $period->EndDate;
						$startDate = ($startDate_org >= $tomorrows_date) ? $startDate_org : $tomorrows_date;
						if ($dateFrom >= $startDate && $dateTo <= $endDate) {
							$availabilities[] = (int) $availability->AccommodationId;
						}
					}
				}
            }
        }
	}
	$availabilities = array_unique($availabilities);
	return $availabilities;
}

function getRangeAvailabilities($dateFrom = '', $dateTo = '') {
    global $wpdb;
    $availabilities_table = $wpdb->prefix . "availabilities";
    $query = "SELECT * FROM " . $availabilities_table;
    $rows = $wpdb->get_results($query);
    $availabilities = [];
    $tomorrows_date = date('Y-m-d', strtotime('+1 day'));
    if (!empty($rows) && $dateFrom) {
    	$datetime1 = new DateTime(date('Y-m-d'));
		$datetime2 = new DateTime($dateFrom);
		$interval = $datetime1->diff($datetime2);
		$diffnights = (int)$interval->format('%a');
        foreach ($rows as $availability) {
            if (!empty($availability->Availabilities) && ($availability->MinDaysNotice <= $diffnights)) {
				$Availabilities = json_decode($availability->Availabilities);
				if (is_array($Availabilities->AvailabilityPeriod)) {
					foreach ($Availabilities->AvailabilityPeriod as $period) {
						if ($period && in_array($period->State, ['AVAILABLE', 'ONREQUEST'])) {
							$startDate_org = (string)$period->StartDate;
							$endDate = (string)$period->EndDate;
							$date = new DateTime($endDate);
							$date->modify('+1 day');
							$endDate = $date->format('Y-m-d');
							$startDate = ($startDate_org >= $tomorrows_date) ? $startDate_org : $tomorrows_date;
							if ($dateFrom >= $startDate && $dateTo <= $endDate) {
								$availabilities[] = (int) $availability->AccommodationId;
							}
						}
					}
				} else {
					$period = $Availabilities->AvailabilityPeriod;
					if ($period && in_array($period->State, ['AVAILABLE', 'ONREQUEST'])) {
						$startDate_org = (string)$period->StartDate;
						$endDate = (string)$period->EndDate;
						$date = new DateTime($endDate);
						$date->modify('+1 day');
						$endDate = $date->format('Y-m-d');
						$startDate = ($startDate_org >= $tomorrows_date) ? $startDate_org : $tomorrows_date;
						if ($dateFrom >= $startDate && $dateTo <= $endDate) {
							$availabilities[] = (int) $availability->AccommodationId;
						}
					}
				}
            }
        }
    }
    $availabilities = array_unique($availabilities);
    return $availabilities;
}

function checkCheckInDaysAvailabilities($dateFrom = '', $dateTo = '') {
    global $wpdb;
    $occupationalrules_table = $wpdb->prefix . "occupationalrules";
    $query = "SELECT * FROM " . $occupationalrules_table;
    $rows = $wpdb->get_results($query);
    $ruleids = [];
	$tomorrows_date = date('Y-m-d', strtotime('+1 day'));
    if (!empty($rows) && $dateFrom) {
		$checkinday = strtoupper(date('l', strtotime($dateFrom)));
		$datetime1 = new DateTime($dateFrom);
		$datetime2 = new DateTime($dateTo);
		$interval = $datetime1->diff($datetime2);
		$nights = (int)$interval->format('%a');
        foreach ($rows as $OccupationalRule) {
            if (!empty($OccupationalRule->Seasons)) {
				$Seasons = json_decode($OccupationalRule->Seasons);
				if (is_array($Seasons)) {
					foreach ($Seasons as $Season) {
						$WeekDay = $Season->CheckInDays->WeekDay;
						$startDate = (string)$Season->StartDate;
						$endDate = (string)$Season->EndDate;
						$startDate = ($startDate >= $tomorrows_date) ? $startDate : $tomorrows_date;
						$min_nights = (int)$Season->MinimumNights;
						if (!empty($Season->MaximumNights)) {
							$max_nights = (int)$Season->MaximumNights;
						} else {
							$max_nights = 1000;
						}
						if ($dateFrom >= $startDate && $dateFrom <= $endDate && $nights >= $min_nights && $nights <= $max_nights) {
							if (is_array($WeekDay) && in_array($checkinday, $WeekDay)) {
								$ruleids[] = (int)$OccupationalRule->Id;
							} else {
								if ($checkinday == $WeekDay) {
									$ruleids[] = (int)$OccupationalRule->Id;
								}
							}
						}
					}
				} else {
					$WeekDay = $Seasons->CheckInDays->WeekDay;
					$startDate = (string)$Season->StartDate;
					$endDate = (string)$Season->EndDate;
					$startDate = ($startDate >= $tomorrows_date) ? $startDate : $tomorrows_date;
					$min_nights = (int)$Season->MinimumNights;
					if (!empty($Season->MaximumNights)) {
						$max_nights = (int)$Season->MaximumNights;
					} else {
						$max_nights = 1000;
					}
					if ($dateFrom >= $startDate && $dateFrom <= $endDate && $nights >= $min_nights && $nights <= $max_nights) {
						if (is_array($WeekDay) && in_array($checkinday, $WeekDay)) {
							$ruleids[] = (int)$OccupationalRule->Id;
						} else {
							if ($checkinday == $WeekDay) {
								$ruleids[] = (int)$OccupationalRule->Id;
							}
						}
					}
				}
            }
        }
    }
    $ruleids = array_unique($ruleids);
    return $ruleids;
}

function getAccommodations($parameters) {
	/*
	if(!empty($_REQUEST['import'])){
		import_feed('Accommodations.xml');
	}
	*/
	global $wpdb;
	if (isset($parameters['dateFrom'])) {
		$dateFrom = $parameters['dateFrom'];
	} else {
		$dateFrom = '';
	}
	if (isset($parameters['dateTo'])) {
		$dateTo = $parameters['dateTo'];
	} else {
		$dateTo = '';
	}
	$accommodations_table = $wpdb->prefix . "accommodations";
	$query = "SELECT * FROM " . $accommodations_table . " WHERE 1 = 1";
	if (isset($parameters['propids']) && !empty($parameters['propids'])) {
		if (isset($parameters['display_type']) && ($parameters['display_type'] != 'featured' && $parameters['display_type'] != 'viewed')) {
	        $propids = explode('_', $parameters['propids']);
	        $propids_imp = implode(', ', array_fill(0, count($propids), '%d'));
	        $formatted_query = $wpdb->prepare(" AND AccommodationId IN($propids_imp)", $propids);
	        $query .= $formatted_query;
	    } else if (isset($parameters['display_type']) && $parameters['display_type'] == 'featured') {
	        $propids = array_map('trim', explode(',', $parameters['propids']));
	    	$propids = array_map('intval', $propids);
	        $propids_imp = implode(', ', array_fill(0, count($propids), '%d'));
	        $formatted_query = $wpdb->prepare(" AND AccommodationId IN($propids_imp)", $propids);
	        $query .= $formatted_query;
	    } else {
	    	$propids = explode('_', $parameters['propids']);
	        $propids_imp = implode(', ', array_fill(0, count($propids), '%d'));
	        $formatted_query = $wpdb->prepare(" AND AccommodationId IN($propids_imp)", $propids);
	        $query .= $formatted_query;
	    }
    }
    if (isset($parameters['propertyIds']) && !empty($parameters['propertyIds']) && isset($parameters['display_type']) && $parameters['display_type'] == 'viewed') {
        $propids2 = array_map('trim', explode(',', $parameters['propertyIds']));
    	$propids2 = array_map('intval', $propids2);
        $propids2_imp = implode(', ', array_fill(0, count($propids2), '%d'));
        $formatted_query2 = $wpdb->prepare(" AND AccommodationId IN($propids2_imp)", $propids2);
        $query .= $formatted_query2;
    }
	if ($dateFrom) {
		$ruleids = checkCheckInDaysAvailabilities($dateFrom, $dateTo);
		if (!empty($ruleids)) {
			$ruleids_imp = implode(', ', array_fill(0, count($ruleids), '%d'));
			$query .= $wpdb->prepare(" AND OccupationalRuleId IN($ruleids_imp)", $ruleids);
		} else {
			$query .= " AND OccupationalRuleId IN(0)";
		}
		$availabilities = getRangeAvailabilities($dateFrom, $dateTo);
		if (!empty($availabilities)) {
			$availabilities_imp = implode(', ', array_fill(0, count($availabilities), '%d'));
			$query .= $wpdb->prepare(" AND AccommodationId IN($availabilities_imp)", $availabilities);
		} else {
			$query .= " AND AccommodationId IN(0)";
		}
	}
	if (isset($parameters['offerid']) && !empty($parameters['offerid'])) {
		$offerid_code = $parameters['offerid'];
        $query .= $wpdb->prepare(" AND PriceModifierId IN(%d)", $offerid_code);
	}
	if (isset($parameters['display_type']) && !empty($parameters['display_type']) && $parameters['display_type'] == 'offers') {
		$query .= " AND ActiveOffers > 0";
	}
	if (isset($parameters['province']) && !empty($parameters['province']) && ($parameters['province'] != 'all')) { 
		$region = (string)$parameters['province'];
		$region = strtolower($region);
        $query .= $wpdb->prepare(" AND (LOWER(RegionName) = %s OR LOWER(LocalityName) = %s)", $region, $region);
	}
	if (isset($parameters['region']) && !empty($parameters['region']) && ($parameters['region'] != 'all')) { 
		$region = (string)$parameters['region'];
		$region = strtolower($region);
        $query .= $wpdb->prepare(" AND (LOWER(RegionName) = %s)", $region);
	}
	if (isset($parameters['city']) && !empty($parameters['city']) && ($parameters['city'] != 'all')) {
		$city = (string)$parameters['city'];
		$city = strtolower($city);
        $query .= $wpdb->prepare(" AND (LOWER(CityName) = %s OR LOWER(LocalityName) = %s)", $city, $city);
	}
	if (isset($parameters['label']) && !empty($parameters['label']) && ($parameters['label'] != 'all')) {
		$label = (string)$parameters['label'];
		$label_query = '%' . $wpdb->esc_like(strtolower($label)) . '%';
        $query .= $wpdb->prepare(" AND LOWER(Labels) LIKE %s", $label_query);
	}
	if (isset($parameters['labelexact']) && !empty($parameters['labelexact']) && ($parameters['labelexact'] != 'all')) {
		//error_log('Label Exact is set: ' . $parameters['labelexact']);
	    $labelexact = (string)$parameters['labelexact'];
	    //error_log('Lowercased Label Exact: ' . $labelexact);
		$label_query = '%"' . $wpdb->esc_like($labelexact) . '"%';
		$label_query2 = '%"' . $wpdb->esc_like($labelexact) . ' "%';
	    $query .= $wpdb->prepare(" AND (Labels LIKE %s OR Labels LIKE %s)", $label_query, $label_query2);
	}
	if (!empty($parameters['labelexact']) && $parameters['labelexact'] == 'tpservices') {
		$query .= " AND longtermrental > 0";
	} else {
		if (!empty($parameters['display_type']) && $parameters['display_type'] == 'favorites') {
		} else {
			$query .= " AND longtermrental < 1";
		}
	}
	if (isset($parameters['destination']) && !empty($parameters['destination'])) {
		$destination = (int)$parameters['destination'];
        $query .= $wpdb->prepare(" AND (AccommodationId = %d OR RegionCode = %d OR LocalityCode = %d OR DistrictCode = %d OR ProvinceCode = %d OR CityCode = %d)", $destination, $destination, $destination, $destination, $destination, $destination);
	}
	if (isset($parameters['AdultNum']) && !empty($parameters['AdultNum'])) {
		$AdultNum = (int)$parameters['AdultNum'];
		$query .= $wpdb->prepare(" AND AdultsCapacity >= %d", $AdultNum);
	}
	if (isset($parameters['ChildrenNum']) && !empty($parameters['ChildrenNum'])) {
		$AcceptYoungsters = '1';
		$numPeople = (int)$parameters['ChildrenNum'] + (int)$parameters['AdultNum'];
		$query .= $wpdb->prepare(" AND OccupantsCapacity >= %d AND MinimumOccupation <= %d", $numPeople, $numPeople);
	}
	if (isset($parameters['thh-rating']) && !empty($parameters['thh-rating'])) {
		$srating = (float)$parameters['thh-rating'];
		$query .= $wpdb->prepare(" AND (TotalReviews > 0 AND ((TotalRatings/TotalReviews)/10)*5 < %f)", $srating);
	}
	if (isset($parameters['thh-max-price']) && isset($parameters['thh-min-price']) && !empty($parameters['thh-max-price']) && !empty($parameters['thh-min-price'])) {
		$max_price = (float)$parameters['thh-max-price'];
		$min_price = (float)$parameters['thh-min-price'];
		$query .= $wpdb->prepare(" AND (WeeklyPrice BETWEEN %f AND %f)", $min_price, $max_price);
	}
	if (isset($parameters['thh-distance-airport']) && !empty($parameters['thh-distance-airport'])) {
		$search_airport = (float)$parameters['thh-distance-airport'];
		$query .= $wpdb->prepare(" AND AirportDistance IS NOT NULL AND AirportDistance <= %f", $search_airport);
	}
	if (isset($parameters['thh-distance-seaside']) && !empty($parameters['thh-distance-seaside'])) {
		$search_seaside = (float)$parameters['thh-distance-seaside'];
		$query .= $wpdb->prepare(" AND BeachDistance IS NOT NULL AND BeachDistance <= %f", $search_seaside);
	}
	if (isset($parameters['thh-distance-busstation']) && !empty($parameters['thh-distance-busstation'])) {
		$search_busstation = (float)$parameters['thh-distance-busstation'];
		$query .= $wpdb->prepare(" AND StopBusDistance IS NOT NULL AND StopBusDistance <= %f", $search_busstation);
	}
	if (isset($parameters['thh-distance-town']) && !empty($parameters['thh-distance-town'])) {
		$search_town = (float)$parameters['thh-distance-town'];
		$query .= $wpdb->prepare(" AND CityDistance IS NOT NULL AND CityDistance <= %f", $search_town);
	}
	if (isset($parameters['thh-distance-golf']) && !empty($parameters['thh-distance-golf'])) {
		$search_golf = (float)$parameters['thh-distance-golf'];
		$query .= $wpdb->prepare(" AND GolfDistance IS NOT NULL AND GolfDistance <= %f", $search_golf);
	}
	if (isset($parameters['thh-distance-supermarket']) && !empty($parameters['thh-distance-supermarket'])) {
		$search_supermarket = (float)$parameters['thh-distance-supermarket'];
		$query .= $wpdb->prepare(" AND SuperMarketDistance IS NOT NULL AND SuperMarketDistance <= %f", $search_supermarket);
	}
	if (isset($parameters['thh-distance-trainstation']) && !empty($parameters['thh-distance-trainstation'])) {
		$search_trainstation = (float)$parameters['thh-distance-trainstation'];
		$query .= $wpdb->prepare(" AND TrainStationDistance IS NOT NULL AND TrainStationDistance <= %f", $search_trainstation);
	}
	if (isset($parameters['thh-groups-allowed']) && !empty($parameters['thh-groups-allowed'])) {
		$query .= " AND AcceptYoungsters = 1";
	}
	if (isset($parameters['thh-active-offers']) && !empty($parameters['thh-active-offers'])) {
		$query .= " AND ActiveOffers > 0";
	}
	if (isset($parameters['thh-bedrooms']) && !empty($parameters['thh-bedrooms'])) {
		if ($parameters['thh-bedrooms'] != 'more') {
			$bedrooms = (int)$parameters['thh-bedrooms'];
			$query .= $wpdb->prepare(" AND Bedrooms = %d", $bedrooms);
		} else {
			$query .= " AND Bedrooms >= 6";
		}
	}
	if (isset($parameters['thh-beds']) && !empty($parameters['thh-beds'])) {
		if ($parameters['thh-beds'] != 'more') {
			$beds = (int)$parameters['thh-beds']; 
			$query .= $wpdb->prepare(" AND Beds = %d", $beds);
		} else {
			$query .= " AND Beds >= 6";
		}
	}
	if (isset($parameters['thh-bathrooms']) && !empty($parameters['thh-bathrooms'])) {
		if ($parameters['thh-bathrooms'] != 'more') {
			$bathrooms = (int)$parameters['thh-bathrooms'];
			$query .= $wpdb->prepare(" AND Bathrooms = %d", $bathrooms);
		} else {
			$query .= " AND Bathrooms >= 6";
		}
	}
	if (isset($parameters['thh-features']) && !empty($parameters['thh-features'])) {
		$features = $parameters['thh-features'];
		if (in_array('Dog friendly', $features)) {
			$query .= " AND pet_friendly = 1";
		}
		if (in_array('Internet', $features)) {
			$query .= " AND internet = 1";
		}
		if (in_array('Fireplace', $features)) {
			$query .= " AND fireplace = 1";
		}
		if (in_array('Swimming pool', $features)) {
			$query .= " AND pool = 1";
		}
		if (in_array('Disabled Friendly', $features)) {
			$query .= " AND disabled_friendly = 1";
		}
		if (in_array('Electric car charger', $features)) {
			$query .= " AND charger = 1";
		}
	}
	if (isset($parameters['sortOrder']) && !empty($parameters['sortOrder'])) {
	    switch ($parameters['sortOrder']) {
	        case 'occupant_asc':
	        	$query .= " ORDER BY OccupantsCapacity ASC, AccommodationId ASC";
	        	break;
	        // case 'price_asc':
	        // 	$query .= " ORDER BY CASE WHEN WeeklyPrice IS NULL THEN 1 ELSE 0 END, WeeklyPrice ASC, AccommodationId ASC";
	        // 	break;
	        // case 'price_desc':
	        // 	$query .= " ORDER BY CASE WHEN WeeklyPrice IS NULL THEN 1 ELSE 0 END, WeeklyPrice DESC, AccommodationId ASC";
	        // 	break;
	        case 'town_asc':
	        	$query .= " ORDER BY LocalityName ASC, AccommodationId ASC";
	        	break;
	        case 'propertytype_desc':
	        	$query .= " ORDER BY PropertyType ASC, AccommodationId ASC";
	        	break;
	        case 'bedrooms_asc':
	        	$query .= " ORDER BY Bedrooms ASC, AccommodationId ASC";
	        	break;
	        case 'review_desc':
	        	$query .= " ORDER BY TotalReviews DESC, AccommodationId ASC";
	        	break;
	        case 'default':
	        	$query .= " ORDER BY OccupantsCapacity ASC, AccommodationId ASC";
	        	break;
	    }
	} else {
		$query .= " ORDER BY OccupantsCapacity ASC, AccommodationId ASC";
	}
	// honey comment
	// if (isset($parameters['page_num']) && !empty($parameters['page_num'])) {
	// 	$page_num = isset($parameters['page_num']) ? (int) $parameters['page_num'] : 1;
    //     $page_size = isset($parameters['page_size']) ? (int) $parameters['page_size'] : 20;
	// 	$start = ($page_num - 1) * $page_size;
	// 	$query .= $wpdb->prepare(" LIMIT %d, %d", $start, $page_size);
	// }
	//honey comment end
	//error_log('Final Query: ' . $query);
	$accommodations = $wpdb->get_results($query);
	return $accommodations;
}

/* Get days between two dates */

function getDaysBetweenTwoDates($check_in_date, $check_out_date) {
	$datetime1 = new DateTime($check_in_date);
	$datetime2 = new DateTime($check_out_date);
	$difference = $datetime1->diff($datetime2);
	return $difference->d;
}

/* get ByAccommodation Price */
function getPriceByRoomTypeAccommodation($number_of_nights, $price) {
	return $price * $number_of_nights;
}

function checkPriceModifiersConditionMeet($min_number_of_nights,$max_number_of_nights,$number_of_nights,$max_date,$minimum_number_of_days_before_arrival,$no_of_nights_booking,$check_in_date) {
	$today_date = date('Y-m-d');
	if ($min_number_of_nights != '0' && $min_number_of_nights > $no_of_nights_booking) {
		return false;
	}
	if ($max_number_of_nights != '0' && $max_number_of_nights < $no_of_nights_booking) {
		return false;
	}
	if ($number_of_nights != '0' && $number_of_nights != $no_of_nights_booking) {
		return false;
	}
	if ($max_date !='0' && strtotime($max_date) !== strtotime($today_date)) {
		return false;
	}
	if ($minimum_number_of_days_before_arrival != '0' && getDaysBetweenTwoDates($today_date,$check_in_date) != $minimum_number_of_days_before_arrival) {
		return false;
	}
	return 1;
}

function getPriceModifiersPrice($accomodation_price, $price_modifier_id,$check_in_date, $check_out_date,$total_nights) {
	global $wpdb;
	$results_accID = $wpdb->get_results($wpdb->prepare("SELECT * FROM wp_pricemodifiers WHERE Id = %s", $price_modifier_id));
	if (!empty($results_accID)) {
		$seasons = $results_accID[0]->Seasons;
		$sesons_details = json_decode($seasons);
		$total_discount = 0;
		$is_dicount_applied  = false;
		if (count($sesons_details) > 0) {
			$kind_code = [];
			foreach($sesons_details as $season_key => $sesons_detail) {
				$is_accumulative = $sesons_detail->Kind->IsCumulative;
				if ($is_dicount_applied  == true && $is_accumulative == 'false') {
					break;
				}
				$start_date = $sesons_detail->StartDate;
				$end_date = $sesons_detail->EndDate;
				$min_number_of_nights = isset($sesons_detail->MinNumberOfNights) ? $sesons_detail->MinNumberOfNights : 0;
				$max_number_of_nights = isset($sesons_detail->MaxNumberOfNights) ? $sesons_detail->MaxNumberOfNights : 0;
				$number_of_nights = isset($sesons_detail->NumberOfNights) ? $sesons_detail->NumberOfNights    : 0;
				$max_date = isset($sesons_detail->MaxDate) ? $sesons_detail->MaxDate : 0; 
				$minimum_number_of_days_before_arrival = isset($sesons_detail->DaysAdvance) ?  $sesons_detail->DaysAdvance : 0;
				$check_condition_meet = checkPriceModifiersConditionMeet($min_number_of_nights,$max_number_of_nights,$number_of_nights,$max_date,$minimum_number_of_days_before_arrival,$total_nights,$check_in_date);
				$discount_amount = $sesons_detail->Amount;
				$operation = '';
				if ($check_condition_meet) {
					
					if (!in_array($sesons_detail->Kind->Code,$kind_code)) {
						if (str_starts_with($sesons_detail->Amount, '-')) {
							$operation = '-';
							$discount_amount = ltrim($discount_amount,'-');
						}
						if (strtotime($start_date) <= strtotime($check_in_date) && strtotime($end_date) >= strtotime($check_out_date)) {
							$kind_code[] = $sesons_detail->Kind->Code;
							$total_discounted_value = 0;
							if ($sesons_detail->DiscountSupplementType == 'percent') {
								$total_discounted_value = ($accomodation_price * $discount_amount) /100;
							} else if($sesons_detail->DiscountSupplementType == 'amount') {
								$total_discounted_value = $discount_amount;
							}
							if ($operation == '-') {
								$total_discount = $total_discount - $total_discounted_value;
							} else {
								$total_discount = $total_discount + $total_discounted_value;
							}
							$is_dicount_applied = true;
						}
					}
				}
			}
			$accomodation_price = $accomodation_price + $total_discount;
			return $accomodation_price;
		}
	} else {
		return $accomodation_price;
	}
}

/* get ByAccommodation Price Ends */
function calculateRecursivePrice($check_in_date, $checkout_date, $rate_key,$price,$accommodation_rates_period) {
	$check_in_date = calculateNextCheckInDate($check_in_date);
	foreach($accommodation_rates_period as $new_rate_key => $new_rates_period) {
		if ($new_rate_key > $rate_key) {
			if (strtotime($check_in_date) >= strtotime($new_rates_period->StartDate)) {
				$accommodation_rate_type = $new_rates_period->RoomOnly->Type;
				$accommodation_price = $new_rates_period->RoomOnly->Price;
				if (strtotime($checkout_date) <= strtotime($new_rates_period->EndDate)) {
					if ($check_in_date == $checkout_date) {
						$total_nights = 0;
					} else {
						$total_nights = intval(getDaysBetweenTwoDates($check_in_date,$checkout_date));
					}
					$price = $price  + getPriceByRoomTypeAccommodation($total_nights, $accommodation_price);
					break;
				} else {
					$total_nights = intval(getDaysBetweenTwoDates($check_in_date,$new_rates_period->EndDate)) + 1;
					$price = $price + getPriceByRoomTypeAccommodation($total_nights, $accommodation_price);
					$price = calculateRecursivePrice($new_rates_period->EndDate, $checkout_date, $new_rate_key,$price,$accommodation_rates_period);
				}
			}
		}
	}
	return $price;
}

function calculateNextCheckInDate($check_in_date) {
	$new_check_in_date = new DateTime($check_in_date);
	return $new_check_in_date->modify('+1 day')->format('Y-m-d');
}

// get days between tow dates
function getDateBetweenTwoDates($check_in_date, $check_out_date) {
	$period = new DatePeriod(
		new DateTime($check_in_date),
		new DateInterval('P1D'),
		new DateTime($check_out_date)
   );
   return $period;
}

// Calculate Price For Each date
function calculatePriceForEachDate($date,$booking_nights,$accomodation_price,$sesons_details) {
	$total_discount = 0;
	$is_dicount_applied  = false;
	if (count($sesons_details) > 0) {
		$kind_code = [];
		foreach($sesons_details as $season_key => $sesons_detail) {
			$start_date = $sesons_detail->StartDate;
			$end_date = $sesons_detail->EndDate;
			if (strtotime($start_date) <= strtotime($date) && strtotime($end_date) >= strtotime($date)) {
				$min_number_of_nights = isset($sesons_detail->MinNumberOfNights) ? $sesons_detail->MinNumberOfNights : 0;
				$max_number_of_nights = isset($sesons_detail->MaxNumberOfNights) ? $sesons_detail->MaxNumberOfNights : 0;
				$number_of_nights = isset($sesons_detail->NumberOfNights) ? $sesons_detail->NumberOfNights    : 0;
				$max_date = isset($sesons_detail->MaxDate) ? $sesons_detail->MaxDate : 0; 
				$minimum_number_of_days_before_arrival = isset($sesons_detail->DaysAdvance) ?  $sesons_detail->DaysAdvance : 0;
				$check_condition_meet = checkPriceModifiersConditionMeet($min_number_of_nights,$max_number_of_nights,$number_of_nights,$max_date,$minimum_number_of_days_before_arrival,$booking_nights,$date);
				$discount_amount = $sesons_detail->Amount;
				$operation = '';
				if ($check_condition_meet) {
					$is_accumulative = $sesons_detail->Kind->IsCumulative;
					if ($is_dicount_applied  == true && $is_accumulative == 'false') {
						break;
					} else {
						if (!in_array($sesons_detail->Kind->Code,$kind_code)) {
							if (str_starts_with($sesons_detail->Amount, '-')) {
								$operation = '-';
								$discount_amount = ltrim($discount_amount,'-');
							}
							if (strtotime($start_date) <= strtotime($date) && strtotime($end_date) >= strtotime($date)) {
								$kind_code[] = $sesons_detail->Kind->Code;
								$total_discounted_value = 0;
								if ($sesons_detail->DiscountSupplementType == 'percent') {
									$total_discounted_value = ($accomodation_price * $discount_amount) / 100;
								} else if($sesons_detail->DiscountSupplementType == 'amount') {
									$total_discounted_value = $discount_amount;
								}
								if ($operation == '-') {
									$total_discount = $total_discount - $total_discounted_value;
								} else {
									$total_discount = $total_discount + $total_discounted_value;
								}
								$is_dicount_applied = true;
							}
						}
					}
				}
			}
		}
		$accomodation_price = $accomodation_price + $total_discount;
		return $accomodation_price;
	}
}

function getAccomodationCostForDate($date,$accommodation_rates_period) {
	foreach($accommodation_rates_period as $rate_period) {
		if (strtotime($date) >= strtotime($rate_period->StartDate) && strtotime($rate_period->EndDate) >= strtotime($date)) {
			return $rate_period->RoomOnly->Price;
		}
	}
}

/* calculate cost */
function calculateAccomodationsCost($check_in_date, $check_out_date, $accommodation_details) {
	global $wpdb;
	$accommodation_rates_details = json_decode($accommodation_details->Rates);
	$accommodation_rates_period = $accommodation_rates_details->Rates->RatePeriod;
	$new_check_in_date = '';
	$total_nights = intval(getDaysBetweenTwoDates($check_in_date,$check_out_date));
	$price = 0;
	$sesons_details = [];
	$price_without_offer = 0;
	$modifier_price = 0;
	if ($accommodation_details->PriceModifierId) {
		$results_accID = $wpdb->get_results($wpdb->prepare("SELECT * FROM wp_pricemodifiers WHERE Id = %s", $accommodation_details->PriceModifierId));
		if (!empty($results_accID)) {
			$seasons = $results_accID[0]->Seasons;
			$sesons_details = json_decode($seasons);
		}
	}
	foreach($accommodation_rates_period as $rate_key => $rates_period) {
		if (is_object($rates_period) && isset($rates_period->StartDate, $rates_period->EndDate)) {
			if (strtotime($check_in_date) >= strtotime($rates_period->StartDate) && strtotime($rates_period->EndDate) >= strtotime($check_in_date)) {
				// check if end date exist in same rate period
				$accommodation_rate_type = $rates_period->RoomOnly->Type;
				$accommodation_price = $rates_period->RoomOnly->Price;
				$period = getDateBetweenTwoDates($check_in_date, $check_out_date);
				if (strtotime($check_out_date) <= strtotime($rates_period->EndDate)) {
					foreach ($period as $key => $value) {
						$stay_date = $value->format('Y-m-d');
						if (!empty($sesons_details)) {
							$price_without_offer = $price_without_offer + $accommodation_price;
							$price = $price + calculatePriceForEachDate($stay_date,$total_nights,$accommodation_price,$sesons_details);
							$modifier_price = $price;
						} else {
							$price_without_offer = $price_without_offer + $accommodation_price;
							$modifier_price = $price +$accommodation_price;
						}
					}
					break;
				} else {
					foreach ($period as $key => $value) {
						$stay_date = $value->format('Y-m-d');
						$accommodation_price = getAccomodationCostForDate($stay_date,$accommodation_rates_period);
						if (strtotime($rates_period->EndDate) >= strtotime($stay_date)) {
							if (!empty($sesons_details)){
								$price_without_offer = $price_without_offer + $accommodation_price;
								$price = $price + calculatePriceForEachDate($stay_date,$total_nights,$accommodation_price,$sesons_details);
								$modifier_price = $price;
							} else {
								$price_without_offer = $price_without_offer + $accommodation_price;
								$modifier_price = $price +$accommodation_price;
							}
						} else {
							if (!empty($sesons_details)) {
								$price_without_offer = $price_without_offer + $accommodation_price;
								$price = $price + calculatePriceForEachDate($stay_date,$total_nights,$accommodation_price,$sesons_details);
								$modifier_price = $price;
							} else {
								$price_without_offer = $price_without_offer + $accommodation_price;
								$modifier_price = $price + $accommodation_price;
							}
						}
						
					}
					break;
				}
			}
		}
	}
	return ['price_without_offer' => $price_without_offer, 'price_with_offer' => $modifier_price];
}

function getExtraServiceCost($accommodationId, $language,$total_nights) {
	$extra_service_cost = 0;
	$descriptionsData = getDescriptionsFeeds($accommodationId, $language);
	if (!empty($descriptionsData['Extras']['ObligatoryOrIncluded'])) {
		foreach ($descriptionsData['Extras']['ObligatoryOrIncluded'] as $extraDescription) {
			if ($extraDescription['Name'] != 'Security Deposit (refundable)' && $extraDescription['Description'] != 'Included') {
				$description =  explode(' ',$extraDescription['Description']);
				$description_cost = ltrim($description[0],'€');
				if ($description[count($description) - 1] == 'day') {
					$description_cost = $description_cost * $total_nights;
				}
				$extra_service_cost = $extra_service_cost + (float)$description_cost;
			}
		}
	}
	return $extra_service_cost;
}

// function check service availiable
function checkServiceAvailaible($check_in_dat_exp,$check_out_in_dat_exp,$commonservice,$total_nights,$total_person) {
	if ($commonservice->Season->StartDay <= $check_in_dat_exp['2'] && $commonservice->Season->StartMonth <= $check_in_dat_exp['1'] && $commonservice->Season->FinalDay >= $check_out_in_dat_exp['1'] && $commonservice->Season->FinalMonth >= $check_out_in_dat_exp['1']) {
		switch($commonservice->AdditionalPrice->Unit) {
			case 'EURO-RESERVA' : 
				return $commonservice->AdditionalPrice->Quantity;
			break;
			case 'EURO-DIA' : 
				return $commonservice->AdditionalPrice->Quantity * $total_nights;
			break;
			case 'EURO-PERSONA':
				return 	$commonservice->AdditionalPrice->Quantity * $total_person;
			break;
		}
	}
	return 0;
}

// get Extra services cost
function getMandatoryAccommodationServices($accommodationId,$company,$check_in_date,$checkout_date,$total_nights,$total_person,$accommodationsXml) {
	$extra_cost = 0;
	if ($accommodationsXml !== false) {
		foreach ($accommodationsXml->Accommodation as $accommodation) {
			if ($accommodation->Company == $company && $accommodation->AccommodationId == $accommodationId) {
				foreach ($accommodation->Features->ExtrasAndServices->CommonServices->CommonService as $commonservice) {
					if ($commonservice->IncludedInPrice != true || $commonservice->Application != 'OPCIONAL') {
						$check_in_dat_exp = explode("-",$check_in_date);
						$check_out_in_dat_exp = explode("-",$check_in_date);
						switch($commonservice->Application) {
							case 'OBLIGATORIO-SIEMPRE' : 
								$extra_cost = $extra_cost + checkServiceAvailaible($check_in_dat_exp,$check_out_in_dat_exp,$commonservice,$total_nights,$total_person);
							break;
						}
					}
				}
				foreach ($accommodation->Features->ExtrasAndServices->SpecialServices->SpecialService as $commonservice) {
					if ($commonservice->IncludedInPrice != true || $commonservice->Application != 'OPCIONAL') {
						if ($commonservice->Code != '11') {
							$check_in_dat_exp = explode("-",$check_in_date);
							$check_out_in_dat_exp = explode("-",$check_in_date);
							switch($commonservice->Application) {
								case 'OBLIGATORIO-SIEMPRE' : 
									$extra_cost = $extra_cost + checkServiceAvailaible($check_in_dat_exp,$check_out_in_dat_exp,$commonservice,$total_nights,$total_person);
								break;
							}
						}
					}
				}
			}
		}
	}
	return $extra_cost;
}

function sortAsc($a, $b) {
	if ($a['price'] > $b['price']) {
        return 1;
    } elseif ($a['price'] < $b['price']) {
        return -1;
    }
    return 0;
}

function sortDesc($a, $b) {
	if ($a['price'] > $b['price']) {
        return -1;
    } elseif ($a['price'] < $b['price']) {
        return 1;
    }
    return 0;
}

/* get list of accomodations based on search filters */
function get_accommodations() {
	global $wpdb;
	$plugin_url = plugins_url('', __FILE__);
	$parameters = $_REQUEST;
	$arraccommo = [];
	$pictureLimit = 4;
	$accommodations = getAccommodations($parameters); 
	$username = 'trident';
	$password = '7Mx4EuPGpPy6';
	$apiKey = 'trident';
	$secretKey = '';
	$LoginGA = 'james';
	$company = 'james';
	$partnerCode = '25ce87c2384f552afd0144c97669c840';
	$language = 'en';
	$languageUpper = 'EN';
	// @honey get user search data 
	$calculate_custom_price = false;
	if (isset($_GET['dateFrom'])) {
		$dateFrom = $_GET['dateFrom'];
		$calculate_custom_price = true;
	} else {
		$dateFrom = '';
	}
	if (!$dateFrom) {
		$calc_dateFrom = date('Y-m-d');
		$calc_dateFromDay = date('Y-m-d', strtotime($calc_dateFrom . ' +1 day'));
		$dateFrom = $calc_dateFromDay;
	}
	if (isset($_GET['dateTo'])) {
		$dateTo = $_GET['dateTo'];
		//$dateToAPI = $dateTo;
		// Due to dateTo not being the checkout date, we need to minus the checkout date by one to give the number of nights stayed
		$dateToAPI = $_GET['dateTo'];
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
	if (isset($_GET['AdultNum'])) {
		$adultsNumber = $_GET['AdultNum'];
		$getBookingPrice_info = 'yes';
	} else {
		$adultsNumber = 1;
		$getBookingPrice_info = 'no';
	}
	$childrenNumber = isset($_GET['ChildrenNum']) ? $_GET['ChildrenNum'] : 0;
	$childAges = array();
	$child_greater_then_two = 0;
	for ($i = 1; $i <= 6; $i++) {
		$key = 'Child_' . $i . '_Age';
		$childAge = isset($_GET[$key]) ? $_GET[$key] : '';
		if ($childAge > 2) {
			$child_greater_then_two = $child_greater_then_two + 1;
		}
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
	$childAges_GetBookingPrice = [
		'Child1_Age' => $child1Age,
		'Child2_Age' => $child2Age,
		'Child3_Age' => $child3Age,
		'Child4_Age' => $child4Age,
		'Child5_Age' => $child5Age,
		'Child6_Age' => $child6Age
	];
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
	$plugin_dir = plugin_dir_path(__FILE__);
	$accommodationsFile = $plugin_dir . 'feeds/Accommodations.xml';
	$accommodationsXml = 0;
	$total_person = $adultsNumber + $child_greater_then_two;
	if (file_exists($accommodationsFile)) {
		$accommodationsOutput = file_get_contents($accommodationsFile);
		if ($accommodationsOutput !== false) {
			$accommodationsXml = simplexml_load_string($accommodationsOutput);
		}
	}
	if (!empty($accommodations)) {
		$index = 0;
		foreach ($accommodations as $accommodation) {
			
			$accommodationId = $accommodation->AccommodationId;
			$PriceModifierId = $accommodation->PriceModifierId;
			$accommodation_price_type = '/week';
			$price_without_offer = 0;

			$accommodation_price_from = '<span class="from">From</span> ';
			if (isset($_GET['dateFrom']) && isset($_GET['dateTo']) && $_GET['dateFrom'] != '' && $_GET['dateTo'] !='') {
				$accommodation_price_from = '';
				
				$total_nights = getDaysBetweenTwoDates($dateFrom, $dateToAPI);
				$accomodationCost = calculateAccomodationsCost($dateFrom, $dateToAPI, $accommodation);
				if ($accommodationsXml != '0') {
					$extra_Service_cost = getMandatoryAccommodationServices($accommodationId, $company, $dateFrom, $dateToAPI, $total_nights, $total_person, $accommodationsXml);
				} else {
					$extra_Service_cost =getExtraServiceCost($accommodationId, $language, $total_nights);
				}
				if ($accomodationCost['price_with_offer'] > 0) {
					$accommodation->WeeklyPrice = $accomodationCost['price_with_offer'] + $extra_Service_cost;
					$price_without_offer = $accomodationCost['price_without_offer'] + $extra_Service_cost;
					$accommodation_price_type = '/' . $total_nights . ' nights';
				} else {
					$accommodation->WeeklyPrice = '0.00';
				}
			}else{
				$rates = json_decode($accommodation->Rates);
				$accommodation->WeeklyPrice = $accommodation->WeeklyRate;
				
				if(isset($rates->Rates->RatePeriod) && is_array($rates->Rates->RatePeriod) && count($rates->Rates->RatePeriod) > 0){
					usort($rates->Rates->RatePeriod,function($first,$second){
    					return $first->RoomOnly->Price > $second->RoomOnly->Price;
					});
					$dateFrom = $rates->Rates->RatePeriod[0]->StartDate;
					$dateToAPI = date('Y-m-d', strtotime($dateFrom . ' +7 day'));
					if ($accommodationsXml != '0') {
					$extra_Service_cost = getMandatoryAccommodationServices($accommodationId, $company, $dateFrom, $dateToAPI, '7', $total_person, $accommodationsXml);
					} else {
						$extra_Service_cost =getExtraServiceCost($accommodationId, $language, $total_nights);
					}
				
					$accommodation->WeeklyPrice = $accommodation->WeeklyRate +$extra_Service_cost;
				}
			}
			if (!is_null($accommodation->Images) && is_string($accommodation->Images)) {
			    $arrimages = json_decode($accommodation->Images);
			} else {
				$arrimages = '';
			}
			$imageList = [];
			$images = '';
			$acco_url = accommodation_url($accommodation);
			// @honey get accid for accomodation
			$results_accID = $wpdb->get_results($wpdb->prepare("SELECT * FROM wp_accommodations WHERE AccommodationId = %s", $accommodationId));
			if (!empty($results_accID)) {
				$accID = $results_accID[0]->UserId;
			} else {
				$accID = '';
			}
			$booking_details = [
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
					)	
				]
			];
			foreach ($childAges_GetBookingPrice as $childKey_GetBookingPrice => $childAge_GetBookingPrice) {
				if (!empty($childAge_GetBookingPrice)) {
					$booking_details['Criteria']['Occupants'][$childKey_GetBookingPrice] = $childAge_GetBookingPrice;
				}
			}
			if (!empty($arrimages)) {
			    $totalImages = count($arrimages);
			    foreach ($arrimages as $index => $picture) {
			        $AdaptedURI = acco_image_url($picture->AdaptedURI);
			        if (!empty($AdaptedURI) && count($imageList) < $pictureLimit) {
			            $responsiveSizes = "(min-width: 320px) 63%, 100vw";
			            $loadingAttribute = 'loading="lazy"';
			            $imageHTML = '<li><a href="' . $acco_url . '" title="' . $accommodation->AccommodationName . '" aria-label="' . $picture->Name . '"><img src="' . $AdaptedURI . '" data-src="' . $AdaptedURI . '" srcset="' . $AdaptedURI . ' 1x, ' . $AdaptedURI . ' 2x" sizes="' . $responsiveSizes . '" ' . $loadingAttribute . ' alt="' . $picture->Name . '" width="281" height="220"></a></li>';
			            $imageList[] = $imageHTML;
			        }
			    }
			    if (!empty($imageList)) {
			        $lastImageHTML = array_pop($imageList);
			        $modifiedImageHTML = preg_replace('/width="281"/', 'width="446"', $lastImageHTML);
			        $modifiedImageHTML2 = preg_replace('/sizes="[^"]*"/', 'sizes="(min-width: 320px) 100%, 100vw"', $modifiedImageHTML);
			        $imageList[] = $modifiedImageHTML2;
			    }
			    $carouselClass = count($imageList) === 0 || count($imageList) === 1 ? 'search-carousel-single' : 'search-carousel';
			    $images .= '<ul class="' . $carouselClass . '">';
			    $images .= implode('', $imageList);
			    $images .= '</ul>';
			} else {
			    $images .= '<ul class="search-carousel-single">';
			    $images .= '<li><a href="' . $acco_url . '" title="' . $accommodation->AccommodationName . '" aria-label="' . $accommodation->AccommodationName . '"><img src="' . $plugin_url . '/images/empty-image.png" data-src="' . $plugin_url . '/images/empty-image.png" srcset="' . $plugin_url . '/images/empty-image.png 1x, ' . $plugin_url . '/images/empty-image.png 2x" sizes="(min-width: 320px) 100%, 100vw" loading="lazy" alt="Empty Default Image" width="446" height="220"></a></li>';
			    $images .= '</ul>';
			}
			if (!strpos($images, 'class="search-carousel-single"') || !strpos($images, 'class="search-carousel"')) {
			    if (preg_match('/<ul[^>]*>(.*?)<\/ul>/is', $images, $ulMatch)) {
			        $firstUlBlock = $ulMatch[0];
			        $modifiedUlBlock = preg_replace_callback('/<img[^>]+>/i', function($imgMatch) use (&$imgCounter) {
			            static $imgCounter = 0;
			            $imgTag = $imgMatch[0];
			            if ($imgCounter < 2) {
			                $imgTag = str_replace('loading="lazy"', '', $imgTag);
			                $imgCounter++;
			            }
			            return $imgTag;
			        }, $firstUlBlock, 2);
			        $images = str_replace($firstUlBlock, $modifiedUlBlock, $images);
			    }
			}
			$WeeklyPrice = round($accommodation->WeeklyPrice ?? 0);
			$affordable = ($WeeklyPrice <= 500) ? 1 : 0;
			if (!empty($parameters['display_type']) && in_array($parameters['display_type'], ['offers', 'holiday'])) {
				$home = '<li class="property" data-propid="' . $accommodationId . '" data-affordable="' . $affordable . '" data-beachfront="' . $accommodation->beachfront . '" data-handicaped="' . $accommodation->disabled_friendly . '" data-pet_friendly="' . $accommodation->pet_friendly . '"  data-popular="' . $accommodation->popular . '" data-charger="' . $accommodation->charger . '" data-longtermrental="' . $accommodation->longtermrental . '" data-amazing-views="' . $accommodation->amazing_views . '">';
			} else {
				$home = '<li class="property" data-propid="' . $accommodationId . '" data-internet="' . $accommodation->internet . '" data-fireplace="' . $accommodation->fireplace . '" data-handicaped="' . $accommodation->disabled_friendly . '" data-pet_friendly="' . $accommodation->pet_friendly . '"  data-pool="' . $accommodation->pool . '" data-charger="' . $accommodation->charger . '">';
			}
			$home .= '<figure class="carousel-container">
				<div class="swiper-container">
					' . $images . '
					<div class="search-carousel-dots"></div>
				</div>';
			$home .= '<div class="occupants-bedrooms-box">';
			if (!empty($accommodation->OccupantsCapacity)) {
				$home .= '<span class="occupants"><span><span class="svg-occupants" aria-hidden="true"></span><span class="tot-text" aria-hidden="true">' . $accommodation->OccupantsCapacity . '</span><span class="visually-hidden">' . $accommodation->OccupantsCapacity . ' Occupant' . ($accommodation->OccupantsCapacity != 1 ? 's' : '') . '</span></span></span>';
			} else if (!empty($accommodation->PeopleCapacity)) {
				$home .= '<span class="occupants"><span><span class="svg-occupants" aria-hidden="true"></span><span class="tot-text" aria-hidden="true">' . $accommodation->PeopleCapacity . '</span><span class="visually-hidden">' . $accommodation->PeopleCapacity . ' Occupant' . ($accommodation->PeopleCapacity != 1 ? 's' : '') . '</span></span></span>';
			} else if (!empty($accommodation->AdultsCapacity)) {
				$home .= '<span class="occupants"><span><span class="svg-occupants" aria-hidden="true"></span><span class="tot-text" aria-hidden="true">' . $accommodation->AdultsCapacity . '</span><span class="visually-hidden">' . $accommodation->AdultsCapacity . ' Occupant' . ($accommodation->AdultsCapacity != 1 ? 's' : '') . '</span></span></span>';
			} else {
				if (!empty($accommodation->MinimumOccupation)) {
					$home .= '<span class="occupants"><span><span class="svg-occupants" aria-hidden="true"></span><span class="tot-text" aria-hidden="true">' . $accommodation->MinimumOccupation . '</span><span class="visually-hidden">' . $accommodation->MinimumOccupation . ' Occupant' . ($accommodation->MinimumOccupation != 1 ? 's' : '') . '</span></span></span>';
				}
			}
			if (!empty($accommodation->Bedrooms)) {
				$home .= '<span class="bedrooms"><span><span class="svg-bedrooms" aria-hidden="true"></span><span class="tot-text" aria-hidden="true">' . $accommodation->Bedrooms . '</span><span class="visually-hidden">' . $accommodation->Bedrooms . ' Bedroom' . ($accommodation->Bedrooms != 1 ? 's' : '') . '</span></span></span>';
			} else {
				$home .= '<span class="bedrooms"><span><span class="svg-bedrooms" aria-hidden="true"></span><span class="tot-text" aria-hidden="true">0</span><span class="visually-hidden">0 Bedrooms</span></span></span>';
			}
			$home .= '</div>
				</figure>
				<div class="prop-content">
				<div class="offers-petfriendly">';
			if ($accommodation->ActiveOffers > 0) {
				$home .= '<span class="offers"><span><span class="svg-offers"></span> ' . $accommodation->ActiveOffers . ' Active Offer' . ($accommodation->ActiveOffers != 1 ? 's' : '') . '</span></span>';
			} else {
				$home .= '<span class="offers"></span>';
			}
			if (!empty($accommodation->pet_friendly)) {
				$home .= '<span class="petfriendly"><div class="svg-pet-container"><span class="svg-petfriendly"></span></div> <div class="text-petfriendly">Dog Friendly</div></span>';
			}
			$home .= '</div>';
			$totalRatings = $accommodation->TotalRatings;
			$totalReviews = $accommodation->TotalReviews;
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
				$home .= '<div class="reviewsContentRates" style="align-items: center; margin-top: 5px;">';
				$home .= '<div class="star-ratings' . $averageRatingConverted . '" role="img" aria-label="Rating of this property out of 5" style="margin-top: 0;"></div>';
				$home .= '<div class="reviewsAmt">' . $totalReviews . ' review' . ($totalReviews != 1 ? 's' : '') . '</div>';
				$home .= '<button class="favouritesProp" aria-label="Add to Favourites"><i class="far fa-heart fa-lg"></i></button>';
				$home .= '</div>';
			} else {
				$home .= '<div class="reviewsContentRates" style="align-items: center; margin-top: 5px;">';
				$home .= '<div class="star-ratings0" role="img" aria-label="Rating of this property out of 5" style="margin-top: 0;"></div>';
				$home .= '<div class="reviewsAmt">No reviews yet</div>';
				$home .= '<button class="favouritesProp" aria-label="Add to Favourites"><i class="far fa-heart fa-lg"></i></button>';
				$home .= '</div>';
			}
			$home .= '<h3><a class="accom-name" href="' . $acco_url . '" title="' . $accommodation->AccommodationName . '" aria-label="' . $accommodation->AccommodationName . '">' . $accommodation->AccommodationName . '</a></h3>';
			$namesAddress = array_filter([
				$accommodation->DistrictName,
				$accommodation->LocalityName,
				$accommodation->CityName,
				$accommodation->RegionName,
				$accommodation->CountryName
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
			$home .= '<h4><span class="icon-box"><i class="fas fa-map-marker-alt fa-lg marginR5"></i></span><span class="text-box">' . $formattedNamesAddress . '</span></h4>';
			/*
			$home .= '<div class="features-swipe-wrapper features-swipe-blurred-list" id="features-swipe-section' . $index . '">';
			$home .= '<ul class="features-swipe">';
			if (!empty($accommodation->OccupantsCapacity)) {
				$home .= '<li class="features-swipe-item no-bullet">Up to ' . $accommodation->OccupantsCapacity . ' guest' . ($accommodation->OccupantsCapacity != 1 ? 's' : '') . '</li>';
			} else if (!empty($accommodation->PeopleCapacity)) {
				$home .= '<li class="features-swipe-item no-bullet">Up to ' . $accommodation->PeopleCapacity . ' guest' . ($accommodation->PeopleCapacity != 1 ? 's' : '') . '</li>';
			} else if (!empty($accommodation->AdultsCapacity)) {
				$home .= '<li class="features-swipe-item no-bullet">Up to ' . $accommodation->AdultsCapacity . ' guest' . ($accommodation->AdultsCapacity != 1 ? 's' : '') . '</li>';
			} else {
				if (!empty($accommodation->MinimumOccupation)) {
					$home .= '<li class="features-swipe-item no-bullet">Min of ' . $accommodation->MinimumOccupation . ' guest' . ($accommodation->MinimumOccupation != 1 ? 's' : '') . '</li>';
				}
			}
			if (!empty($accommodation->Bedrooms)) {
				$home .= '<li class="features-swipe-item">' . $accommodation->Bedrooms . ' bedroom' . ($accommodation->Bedrooms != 1 ? 's' : '') . ' (' . $accommodation->Beds . ' bed' . ($accommodation->Beds != 1 ? 's' : '') . ')</li>';
			}
			$accommodation->Features = json_decode($accommodation->Features);
			if (!empty($accommodation->Features->BathroomWithBathtub)) {
				$numBathroomWithBathtub  = (int)$accommodation->Features->BathroomWithBathtub;
				$home .= '<li class="features-swipe-item">' . $numBathroomWithBathtub  . ' bathroom' . ($numBathroomWithBathtub  != 1 ? 's' : '') . ' with bathtub</li>';
			}
			if (!empty($accommodation->Features->BathroomWithShower)) {
				$numBathroomWithShower  = (int)$accommodation->Features->BathroomWithShower;
				$home .= '<li class="features-swipe-item">' . $numBathroomWithShower  . ' bathroom' . ($numBathroomWithShower  != 1 ? 's' : '') . ' with shower</li>';
			}
			if (!empty($accommodation->Features->Toilets)) {
				$numToilets = (int)$accommodation->Features->Toilets;
				$home .= '<li class="features-swipe-item">' . $numToilets . ' toilet' . ($numToilets != 1 ? 's' : '') . '</li>';
			}
			if (!empty($accommodation->AcceptYoungsters)) {
				$home .= '<li class="features-swipe-item">Children allowed</li>';
			}
			$home .= '</ul>';
			$home .= '<div class="paddles">';
			$home .= '<span class="left-features-paddle paddle hidden">';
			$home .= '<i class="chevron-left-features fas fa-chevron-left"></i>';
			$home .= '</span>';
			$home .= '<span class="right-features-paddle paddle">';
			$home .= '<i class="chevron-right-features fas fa-chevron-right"></i>';
			$home .= '</span>';
			$home .= '</div>';
			$home .= '</div>';
			*/
			if (!empty($accommodation->WeeklyPrice) && $accommodation->WeeklyPrice != '0.00') {
				$Currency = str_replace('EUR', '&euro;', $accommodation->Currency);
				$WeeklyPrice = round($accommodation->WeeklyPrice ?? 0);
				if ($accommodation->WeeklyPrice >= $price_without_offer) {
					$home .= '<span class="price">' . $accommodation_price_from . $Currency . $WeeklyPrice . ' ' . $accommodation_price_type . '</span>';
				} else {
					$actualPrice = round($price_without_offer ?? 0);
					$home .= '<span class="price">' . $accommodation_price_from;
					$home .= '<label class="orignal-price">' . $Currency . $actualPrice . '</label>';
					$home .= '<label class="discounted-price">  ' . $Currency . $WeeklyPrice . ' ' . $accommodation_price_type . '</label></span>';
				}
			} else {
				$home .= '<span class="price"><label class="discounted-price">Price not available!</a></span>';
			}
			$home .= '</div></li>';
			$arraccommo[] = ['homes' => $home, 'price' => $accommodation->WeeklyPrice];
			$index++;
		}
	}
	if (isset($parameters['sortOrder']) && !empty($parameters['sortOrder'])) {
		if ($parameters['sortOrder'] == 'price_asc') {
			usort($arraccommo, "sortAsc");
		}
		if ($parameters['sortOrder'] == 'price_desc') {
			usort($arraccommo, "sortDesc");
		}
	}
	if (isset($parameters['page_num']) && !empty($parameters['page_num'])) {
		$page_num = isset($parameters['page_num']) ? (int) $parameters['page_num'] : 1;
        $page_size = isset($parameters['page_size']) ? (int) $parameters['page_size'] : 10;
		$start = ($page_num - 1) * $page_size;
		$arraccommo = array_slice($arraccommo,$start,$page_size);
	}
	wp_send_json($arraccommo);
	die;
}
add_action('wp_ajax_accommodations', 'get_accommodations');
add_action('wp_ajax_nopriv_accommodations', 'get_accommodations');

function get_recently_viewed_accommodations() {
    $propertyIds = isset($_POST['propertyIds']) ? explode(',', $_POST['propertyIds']) : [];
    $accommodations = getAccommodations(['propertyIds' => $propertyIds]);
    ob_start();
    include_once(plugin_dir_path( __FILE__ ) . '/viewed_homes.php');
    $htmlContent = ob_get_clean();
    wp_send_json_success($htmlContent);
}
add_action('wp_ajax_get_recently_viewed_accommodations', 'get_recently_viewed_accommodations');
add_action('wp_ajax_nopriv_get_recently_viewed_accommodations', 'get_recently_viewed_accommodations');

function accommodation_url($accommodation) {
	$acco_url = '';
	if (!empty($accommodation)) {
		$CityName_url = strtolower(trim((string)$accommodation->CityName));
		$LocalityName_url = strtolower(trim((string)$accommodation->LocalityName));
		$LocalityName_url = str_replace(array(",", "'"), '', $LocalityName_url);
		$LocalityName_url = preg_replace('/[\[\]()]/', '', $LocalityName_url);
		$LocalityName_url = str_replace(array("/", ".", "+"), '-', $LocalityName_url);
		$LocalityName_url = preg_replace('/\s+/', '-', $LocalityName_url);
		$LocalityName_url = preg_replace('/-+/', '-', $LocalityName_url);
		$AccommodationName_url = strtolower(trim((string)$accommodation->AccommodationName));
		$AccommodationName_url = str_replace(array(",", "'", "&"), '', $AccommodationName_url);
		$AccommodationName_url = preg_replace('/[\[\]()]/', '', $AccommodationName_url);
		$AccommodationName_url = str_replace(array("/", ".", "+"), '-', $AccommodationName_url);
		$AccommodationName_url = preg_replace('/\s+/', '-', $AccommodationName_url);
		$AccommodationName_url = preg_replace('/-+/', '-', $AccommodationName_url);
		if ($accommodation->longtermrental) {
			$acco_url = '/long-term-rental/';
		} else {
			$acco_url = '/property/';
		}
		$acco_url .= $CityName_url . '/' . $LocalityName_url . '/' . $AccommodationName_url . '/' . (int)$accommodation->AccommodationId . '/';
		$accommodation_params = false;
		if (!empty($_REQUEST['dateFrom']) && !empty($_REQUEST['dateTo'])) {
			$accommodation_params = true;
			$acco_url .= '?dateFrom=' . $_REQUEST['dateFrom'] . '&dateTo=' . $_REQUEST['dateTo'];
		}
		if (!empty($_REQUEST['AdultNum'])) {
			if ($accommodation_params) {
				$acco_url .= '&AdultNum=' . $_REQUEST['AdultNum'];
			} else {
				$acco_url .= '?AdultNum=' . $_REQUEST['AdultNum'];
			}
			$accommodation_params = true;
		}
		if (!empty($_REQUEST['ChildrenNum'])) {
			if ($accommodation_params) {
				$acco_url .= '&ChildrenNum=' . $_REQUEST['ChildrenNum'];
			} else {
				$acco_url .= '?ChildrenNum=' . $_REQUEST['ChildrenNum'];
			}
			$accommodation_params = true;
		}
		for ($i = 1; $i <= 6; $i++) {
			$child_age = 'Child_' . $i . '_Age';
			if (!empty($_REQUEST[$child_age])) {
				if ($accommodation_params) {
					$acco_url .= '&' . $child_age . '=' . $_REQUEST[$child_age];
				} else {
					$acco_url .= '?' . $child_age . '=' . $_REQUEST[$child_age];
				}
				$accommodation_params = true;
			}
		}	
	}
	return $acco_url;
}

function getAccommodationPrice($accommodation, $dateFrom, $dateToAPI, $language = 'en') {
	$company = 'james';
	$accommodation_price_from = '';
	$total_nights = getDaysBetweenTwoDates($dateFrom,$dateToAPI);
	$accomodationCost = calculateAccomodationsCost($dateFrom,$dateToAPI,$accommodation);
	$accommodationsFile = $plugin_dir . 'feeds/Accommodations.xml';
	$accommodationsXml = 0;
	if (file_exists($accommodationsFile)) {
		$accommodationsOutput = file_get_contents($accommodationsFile);
		if ($accommodationsOutput !== false) {
			$accommodationsXml = simplexml_load_string($accommodationsOutput);
		}
	}
	if ($accommodationsXml != '0') {
		$total_person = 1;
		$extra_Service_cost = getMandatoryAccommodationServices($accommodation->AccommodationId, $company, $dateFrom, $dateToAPI, $total_nights, $total_person, $accommodationsXml);
	} else {
		$extra_Service_cost =getExtraServiceCost($accommodation->AccommodationId, $language, $total_nights);
	}
	if ($accomodationCost['price_with_offer'] > 0) {
		$price = round($accomodationCost['price_with_offer'] + $extra_Service_cost);
		$price_without_offer = round($accomodationCost['price_without_offer'] + $extra_Service_cost);
		$accommodation_price_type = ' /' . $total_nights . ' nights';
		$Currency = str_replace('EUR', '&euro;', $accommodation->Currency);
		if ($price_without_offer > $price) { 
			$accommodation_price_final = '<label class=\"precio_result\">' ;
			$accommodation_price_final .= '<label class=\"orignal-price\">' . $Currency . $price_without_offer . '</label>';
			$accommodation_price_final .= '<label class=\"discounted-price\">  '. $Currency . $price . $accommodation_price_type . '</label></label>';
			// $accommodation_price_final = '<label class=\"precio_result\"><label class=\"orignal-price\">'.$Currency.$price.$accommodation_price_type.'</label></label>';
		} else {
			$accommodation_price_final = '<label class=\"precio_result\">' . $Currency . $price . $accommodation_price_type . '</label>';
		}
	} else {
		$accommodation_price_final = '<label class=\"precio_result\"><label class=\"discounted-price\">Price not available!</a></label>';
	}
	return $accommodation_price_final;
}

/* get data for map markers and marker popup */
function get_mapdata() {
	$plugin_url = plugins_url('', __FILE__);
	$parameters = [];
	$nparameters = [];
	$tparameters = []; 
	parse_str($_SERVER['REQUEST_URI'], $nparameters);
	foreach($nparameters as $k => $v) {
		$k = str_replace('?', '', $k);
		if ($k == 'datosRequest') {
			$datosRequest = base64_decode($_REQUEST['datosRequest']);
			parse_str($datosRequest, $tparameters);
			foreach($tparameters as $tk => $tv) {
				$nk = preg_replace('/[0-9]+/', '', $tk);
				$parameters[$nk] = $tv;
			}
		} else {
			$parameters[$k] = $v;
		}
	}
	if ($parameters['type'] == 'marker') {
		$arraccommo = [
			'locations' => [], 
			'markerType' => 1, 
			'marker' => $plugin_url . '/images/house.png',
			'favmarker' => $plugin_url . '/images/favhouse_redesign.png',
			'clustermarker' => $plugin_url . '/images/cluster.png',
			'multihousemarker' => $plugin_url . '/images/multihouse.png',
			'zoom_limit' => 18,
			'target_blank' => 0,
			'mapStyle' => $plugin_url . '/js/defaultStyleMap.js',
			'mapResultsVersion' => 1
		];
		$accommodations = getAccommodations($parameters);
		if (!empty($accommodations)) {
			foreach($accommodations as $accommodation) {
				$LocalizationData = json_decode($accommodation->LocalizationData);
				$GoogleLatitude = (float)$LocalizationData->GoogleLatitude;
				$GoogleLongitude = (float)$LocalizationData->GoogleLongitude;
				$arraccommo['locations'][] = ['id' => null, 'idCRS' => $accommodation->AccommodationId, 'idResort' => $accommodation->ResortCode, 'loginGa' => null, 'latitud' => $GoogleLatitude, 'longitud' => $GoogleLongitude, 'favorito' => false];
			}
		}
		wp_send_json($arraccommo);
	} else if ($parameters['type'] == 'window') {
		$site_url_finder = get_site_url();
		if (!empty($parameters['accommodations'])) {
			echo '[';
			$i = 0;
			foreach($parameters['accommodations'] as $ac) {
				$accommodationId = $ac['idCRS'];
				$accommodation = getAccommodation($accommodationId);
				if ($parameters['dateFrom'] != '' && $parameters['dateTo'] != '') {
					$price = getAccommodationPrice($accommodation, $parameters['dateFrom'], $parameters['dateTo'], $language = 'en');
				} else {
					$Currency = str_replace('EUR', '&euro;', $accommodation->Currency);
					$WeeklyPrice = round($accommodation->WeeklyPrice ?? 0);
					$price = $Currency . $WeeklyPrice . ' /week';
				}
				$acco_url = accommodation_url($accommodation);
				if ($i) {
					echo ',';
				}
				$arrimages = json_decode($accommodation->Images); 
				echo '{
					"id": ' . $accommodationId . ',
					"api_id": ' . $accommodation->CompanyId . ',
					"idTour": ' . $accommodation->IdGallery . ',
					"url": "' . $acco_url . '",
					"capacidadpersonas": ' . $accommodation->OccupantsCapacity . ',
					"textopersonas": "' . $accommodation->OccupantsCapacity . ' Occupant' . ($accommodation->OccupantsCapacity != 1 ? 's' : '') . '",
					"contenidoTagsSubcabecera": "<span class=\"tagSubCabecera pobl\">' . $accommodation->LocalityName . ', </span><span class=\"tagSubCabecera prov\">' . $accommodation->ProvinceName . '</span>",
					"tipo": "House",
					"dormitorios": ' . $accommodation->Bedrooms . ',
					"textohabitaciones": "' . $accommodation->Bedrooms . ' Bedroom' . ($accommodation->Bedrooms != 1 ? 's' : '') . '",
					"nombre": "' . $accommodation->AccommodationName . '",
					"fomoNotification": null,
					"precio": "\n<div class=\"map-column\">\n' . $price . '\n</div>",
					"ciudad": "' . $accommodation->CityName . '",
					"img": {
						"Imagen": "' . acco_image_url($arrimages[0]->AdaptedURI) . '",
						"Dimensiones": " width=\'161\' height=\'118\' ",
						"OpcionesVentanaTour": "scrollbars=0,width=700,height=450",
						"Descripcion": "' . $arrimages[0]->Description . '<strong>...</strong>",
						"IdiomaEliza": 2,
						"urlgaleria3d": null,
						"imagen_title": "' . $arrimages[0]->Name . '",
						"imagen_alt": "' . $arrimages[0]->Description . '",
						"imagen_big": "' . $arrimages[0]->OriginalURI . '",
						"tourVirtual": 1292735,
						"imagen_original": "' . $site_url_finder . '/wp-content/uploads/fotos/",
						"imagen_sm": "' . $arrimages[0]->ThumbnailURI . '",
						"imagen_hg": "' . $arrimages[0]->OriginalURI . '"
					},
					"valoraciones": null,
					"directorio": "' . $site_url_finder . '/wp-content/uploads/fotos/",
					"flexibleSearch": "",
					"favorito": false
				}';
				//https://thhdev.wpengine.com/wp-content/uploads/fotos/"imagen_original": "https://www.tridentholidayhomes.ie/rentals/fotos/",
				$i++;
			}
			echo ']';
		} else {
			$accommodationId = $parameters['idCRS'];
			$accommodation = getAccommodation($accommodationId);
			$arrimages = json_decode($accommodation->Images); 
			$Currency = str_replace('EUR', '&euro;', $accommodation->Currency);
			if ($parameters['dateFrom'] != '' && $parameters['dateTo'] != '') {
				$price = getAccommodationPrice($accommodation, $parameters['dateFrom'], $parameters['dateTo'], $language = 'en');
			} else {
				$Currency = str_replace('EUR', '&euro;', $accommodation->Currency);
				$WeeklyPrice = round($accommodation->WeeklyPrice ?? 0);
				$price = '<label class=\"text_desde\">From</label>\n<label class=\"precio_result\">';
				$price = $price . $Currency . $WeeklyPrice . ' /week</label>';
			}
			$acco_url = accommodation_url($accommodation);
			echo '{
				"id": ' . $accommodationId . ',
				"api_id": ' . $accommodation->CompanyId . ',
				"idTour": ' . $accommodation->IdGallery . ',
				"url": "' . $acco_url . '",
				"capacidadpersonas": ' . $accommodation->OccupantsCapacity . ',
				"textopersonas": "'. $accommodation->OccupantsCapacity . ' Occupant' . ($accommodation->OccupantsCapacity != 1 ? 's' : '') . '",
				"contenidoTagsSubcabecera": "<span class=\"tagSubCabecera pobl\">' . $accommodation->LocalityName . ', </span><span class=\"tagSubCabecera prov\">' . $accommodation->ProvinceName . '</span>",
				"tipo": "House",
				"dormitorios": ' . $accommodation->Bedrooms . ',
				"textohabitaciones": "' . $accommodation->Bedrooms . ' Bedroom' . ($accommodation->Bedrooms != 1 ? 's' : '') . '",
				"nombre": "' . $accommodation->AccommodationName . '",
				"fomoNotification": null,
				"precio": "\n<div class=\"map-column\">\n' . $price . '\n</div>\n<div class=\"map-column\">\n<div class=\"contendor_boton_results\">\n<a target=\"_self\" title=\"View Accommodation\" aria-label=\"View Accommodation\" border=\"0\" href=\"' . $acco_url . '\">+ VIEW</a>\n</div>\n</div>\n",
				"ciudad": "' . $accommodation->CityName . '",
				"img": {
					"Imagen": "' . acco_image_url($arrimages[0]->AdaptedURI) . '",
					"Dimensiones": " width=\'161\' height=\'118\' ",
					"OpcionesVentanaTour": "scrollbars=0,width=700,height=450",
					"Descripcion": "' . $arrimages[0]->Description . '<strong>...</strong>",
					"IdiomaEliza": 2,
					"urlgaleria3d": null,
					"imagen_title": "' . $arrimages[0]->Name . '",
					"imagen_alt": "' . $arrimages[0]->Description . '",
					"imagen_big": "' . $arrimages[0]->OriginalURI . '",
					"tourVirtual": 1292735,
					"imagen_original": "' . $site_url_finder . '/rentals/fotos/",
					"imagen_sm": "' . $arrimages[0]->ThumbnailURI . '",
					"imagen_hg": "' . $arrimages[0]->OriginalURI . '"
				},
				"valoraciones": null,
				"directorio": "' . $site_url_finder . '/rentals/fotos/",
				"flexibleSearch": "",
				"favorito": false
			}';
		}
	}
	die;
}
add_action('wp_ajax_mapsdata', 'get_mapdata');
add_action('wp_ajax_nopriv_mapsdata', 'get_mapdata');
?>