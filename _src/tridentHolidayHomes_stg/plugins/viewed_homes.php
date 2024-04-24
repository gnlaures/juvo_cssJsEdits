<?php
if (!defined('ABSPATH')) exit;

$parameters = ['display_type' => 'viewed', 'page_num' => 1, 'page_size' => 10];
$recentlyViewedPropertyIds = isset($_POST['propertyIds']) ? $_POST['propertyIds'] : '';
if (!empty($recentlyViewedPropertyIds)) {
    $parameters['propertyIds'] = $recentlyViewedPropertyIds;
    $accommodations = getAccommodations($parameters);
?>
<div class="recentlyviewed-container">
	<div id="sHomes" class="swiper recentlyViewedSwiper sHomes">
		<ul class="swiper-wrapper prop-grid-view viewed-homes">
		<?php 
		$index = 0;
		$pictureLimit = 4;
		foreach($accommodations as $accommodation) {
			$accommodationId = $accommodation->AccommodationId;
			$PriceModifierId = $accommodation->PriceModifierId;
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
	        $full_prop_url = $CityName_url . '/' . $LocalityName_url . '/' . $AccommodationName_url . '/' . (int)$accommodationId . '/';
			$arrimages = json_decode($accommodation->Images); 
			$imageList = [];
			$images = '';
			if (!empty($arrimages)) {
				foreach ($arrimages as $picture) {
					$AdaptedURI = acco_image_url($picture->AdaptedURI);
					if (!empty($AdaptedURI) && count($imageList) < $pictureLimit) {
						$imageList[] = '<li><a href="/property/' . $full_prop_url . '" title="' . $accommodation->AccommodationName . '" aria-label="' . $picture->Name . '"><img src="' . $AdaptedURI . '" data-src="' . $AdaptedURI . '" loading="lazy" alt="' . $picture->Name . '"></a></li>';
					}
				}
				$carouselClass = count($imageList) === 0 || count($imageList) === 1 ? 'search-carousel-single' : 'search-carousel';
				$images .= '<ul class="' . $carouselClass . '">';
				$images .= implode('', $imageList);
				$images .= '</ul>';
			} else {
				$images .= '<ul class="search-carousel-single">';
				$images .= '<li><a href="/property/' . $full_prop_url . '" title="' . $accommodation->AccommodationName . '" aria-label="' . $accommodation->AccommodationName . '"><img src="' . $plugin_url . '/images/empty-image.png" data-src="' . $plugin_url . '/images/empty-image.png" loading="lazy" alt="Empty Default Image"></a></li>';
				$images .= '</ul>';
			} 		
			$home = '<li class="swiper-slide-recentlyviewed recentlyviewed-wrapper" data-propid="'  . $accommodationId . '">
				<figure class="carousel-container">
					<div class="swiper-container swiper-no-swiping">
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
				$home .= '<span class="bedrooms"><span><span class="svg-bedrooms" aria-hidden="true"></span><span class="tot-text" aria-hidden="true">0</span>span class="visually-hidden">0 Bedrooms</span></span></span>';
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
			$home .= '<h3><a href="/property/' . $full_prop_url . '" title="' . $accommodation->AccommodationName . '" aria-label="' . $accommodation->AccommodationName . '">' . $accommodation->AccommodationName . '</a></h3>';
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
			$home .= '<div class="features-swipe-wrapper features-swipe-blurred-list swiper-no-swiping" id="features-swipe-section' . $index . '">';
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
				$WeeklyPrice = round($accommodation->WeeklyPrice);
				$home .= '<span class="price"><span class="from">From</span> ' . $Currency . $WeeklyPrice . ' /week</span>';
			} else {
				$home .= '<span class="price"><a href="/contact-us/" class="button-contact" title="Contact Us" aria-label="Contact Us">Contact Us</a></span>';
			}
			$home .= '</div></li>';
			echo $home;
			$index++;
		}
		?>
		</ul>
	</div>
	<div class="swiper-button-prev outter-swiper-button"></div>
	<div class="swiper-button-next outter-swiper-button"></div>
	<div class="swiper-pagination"></div>
</div>
<?php
} else {
	echo "<p>You are yet to view an accommodation that will be listed here...</p>";
}
?>