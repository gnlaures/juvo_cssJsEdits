<?php
/*
Plugin Name: Avantio API Integration
Plugin URI: http://www.juvo.ie
Description: This plugin integrates Avantio API with shortcodes to be added to a search and single property page. Use shortcodes: [avantio_search] or [avantio_single_property]. To see all API parameters, use shortcode: [avantio_api]
Version: 1.1.4
Author: JUVO (Brian Ashe)
Author URI: http://www.juvo.ie
License: GPL2
*/
if (!defined('MYPLUGIN_THEME_DIR')) {
    define('MYPLUGIN_THEME_DIR', ABSPATH . 'wp-content/themes/' . get_template());
}
if (!defined('MYPLUGIN_PLUGIN_NAME')) {
    define('MYPLUGIN_PLUGIN_NAME', trim(dirname(plugin_basename(__FILE__)), '/'));
}
if (!defined('MYPLUGIN_PLUGIN_DIR')) {
    define('MYPLUGIN_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . MYPLUGIN_PLUGIN_NAME);
}
if (!defined('MYPLUGIN_PLUGIN_URL')) {
    define('MYPLUGIN_PLUGIN_URL', plugins_url(MYPLUGIN_PLUGIN_NAME));
}

// Version for all  minified files
$minified_plugin_versions_juvo = '2.0.0';

// might need to change this function
function add_defer_attribute($tag, $handle) {
    $scripts_to_defer = array('carousel-script', 'swiper-script');
    if (in_array($handle, $scripts_to_defer)) {
        return str_replace(' src', ' defer="defer" src', $tag);
    }
    return $tag;
}
add_filter('script_loader_tag', 'add_defer_attribute', 10, 2);

function minify_css_js_files($file_paths, $output_path, $type) {
    $content = '';
    foreach ($file_paths as $file_path) {
        $content .= file_get_contents($file_path);
    }
    // Remove single-line comments
    //$content = preg_replace('/\/\/(?!.*("|\'|`).*\/\/).*\n/', '', $content);
    //$content = preg_replace('/\/\/.*\n/', '', $content);
    $content = mb_convert_encoding($content, 'UTF-8', mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true));
    // Remove comments
    $content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $content);
    // Removing trailing commas
	$content = preg_replace('/,\s*}/', '}', $content);
    // Remove spaces after colons, semicolons, and before and after curly braces
    if ($type !== 'f-js') {
	    if ($type !== 'p-js') {
	    	$content = preg_replace('/(?<=[\s{:;])\s+|\s+(?=[}\s{:;])/', '', $content);
	    } else {
	    	$content = preg_replace('/\s*([,:;{}])\s*/', '$1', $content);
	    }
	    $content = str_replace(array(': ', '; ', '{ ', ' }', ' {', '} ', '  '), array(':', ';', '{', '}', '{', '}', ' '), $content);
	    if ($type === 'js') {
	    	//$content = str_replace(array(' = ', '= ', ' ='), '=', $content);
	    	$content = str_replace(array(' = ', '( ', ' )'), array('=', '(', ')'), $content);
	    }
	    // Remove newlines and tabs
	    if ($type !== 'p-js') {
	    	$content = str_replace(array("\r\n", "\r", "\n", "\t"), '', $content);
	    }
	}
	if ($type === 'css') {
	    $content = str_replace(array(';}', ', .', ', ', ' !important'), array('}', ',.', ',', '!important'), $content);
	}
	$content = mb_convert_encoding($content, 'UTF-8');
    file_put_contents($output_path, $content);
}

function avantio_main_load_styles_and_scripts() {
	global $post, $minified_versions_juvo, $minified_plugin_versions_juvo;
	if (isset($minified_versions_juvo) && isset($minified_plugin_versions_juvo) && !empty($minified_versions_juvo) && !empty($minified_plugin_versions_juvo) && $minified_versions_juvo > $minified_plugin_versions_juvo) {
		$minified_plugin_versions_juvo = $minified_versions_juvo;
	}
	$last_minified_version = get_option('last_minified_version', '0');
    if ($minified_plugin_versions_juvo !== $last_minified_version) {
		// Example usage for CSS
		$css_files_to_be_minified = array(
			ABSPATH . 'wp-content/plugins/avantio-api-integration/css/swiper-bundle.min.css',
			ABSPATH . 'wp-content/plugins/avantio-api-integration/css/lightbox-gallery.min.css',
			ABSPATH . 'wp-content/plugins/avantio-api-integration/css/prop-styles.css',
		);
		minify_css_js_files($css_files_to_be_minified, ABSPATH . 'wp-content/plugins/avantio-api-integration/css/minified-juvo-styles.css', 'css');
		$js_files_to_be_minified = array(
			ABSPATH . 'wp-content/plugins/avantio-api-integration/js/prop-carousels.js',
			ABSPATH . 'wp-content/plugins/avantio-api-integration/js/swiper-bundle.min.js',
			ABSPATH . 'wp-content/plugins/avantio-api-integration/js/lightbox-gallery-plus-jquery.min.js',
		);
		minify_css_js_files($js_files_to_be_minified, ABSPATH . 'wp-content/plugins/avantio-api-integration/js/minified-juvo-scripts.js', 'js');
		$js_prop_file_to_be_minified = array(
			ABSPATH . 'wp-content/plugins/avantio-api-integration/js/prop-jq.js',
		);
		minify_css_js_files($js_prop_file_to_be_minified, ABSPATH . 'wp-content/plugins/avantio-api-integration/js/minified-juvo-prop-scripts.js', 'js');
	}
	// Enqueue external CSS files
	wp_enqueue_style('fontawesome-style', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');
	wp_enqueue_style('daterangepickerstyle', 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css');
	wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
	// Enqueue local CSS files
	wp_enqueue_style('prop-styles', MYPLUGIN_PLUGIN_URL . '/css/minified-juvo-styles.css', false, $minified_plugin_versions_juvo);
	// Enqueue jQuery (WordPress already includes jQuery, so no need to load it again)
	wp_enqueue_script('jquery');
	// Property shortcode script
    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'avantio_rentals')) {
    	if ($minified_plugin_versions_juvo !== $last_minified_version) {
    		// JS
			$sidebar_main_js_file_to_be_minified = array(
				ABSPATH . 'wp-content/plugins/avantio-api-integration/js/sidebarRender.js',
			);
			minify_css_js_files($sidebar_main_js_file_to_be_minified, ABSPATH . 'wp-content/plugins/avantio-api-integration/js/minified-sidebar-render.js', 'js');
		}
		wp_enqueue_script('sidebar-render-script', MYPLUGIN_PLUGIN_URL . '/js/minified-sidebar-render.js', array('jquery'), $minified_plugin_versions_juvo, true);
    }
	// Search shortcode script
    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'avantio_rentals_search')) {
    	if ($minified_plugin_versions_juvo !== $last_minified_version) {
    		// JS
			$search_main_js_file_to_be_minified = array(
				ABSPATH . 'wp-content/plugins/avantio-api-integration/js/searchRender.js',
			);
			minify_css_js_files($search_main_js_file_to_be_minified, ABSPATH . 'wp-content/plugins/avantio-api-integration/js/minified-search-render.js', 'js');
		}
		wp_enqueue_script('search-render-script', MYPLUGIN_PLUGIN_URL . '/js/minified-search-render.js', array('jquery'), $minified_plugin_versions_juvo, true);
    }
	// Map shortcode scripts for
    if (is_a($post, 'WP_Post') && (has_shortcode($post->post_content, 'avantio_map') || has_shortcode($post->post_content, 'avantio_offers_map'))) {
    	if ($minified_plugin_versions_juvo !== $last_minified_version) {
    		// CSS
    		$map1_css_files_to_be_minified = array(
				ABSPATH . 'wp-content/plugins/avantio-api-integration/css/mapbox.css',
				ABSPATH . 'wp-content/plugins/avantio-api-integration/css/mapbox-gl.css',
				ABSPATH . 'wp-content/plugins/avantio-api-integration/css/widgetmap.css',
				ABSPATH . 'wp-content/plugins/avantio-api-integration/css/loader.css',
			);
    		minify_css_js_files($map1_css_files_to_be_minified, ABSPATH . 'wp-content/plugins/avantio-api-integration/css/minified-map.css', 'css');
    		// JS
			$map1_js_file_to_be_minified = array(
				ABSPATH . 'wp-content/plugins/avantio-api-integration/js/detectElement.js',
				ABSPATH . 'wp-content/plugins/avantio-api-integration/js/Maps.js',
				ABSPATH . 'wp-content/plugins/avantio-api-integration/js/MapBox.js',
				ABSPATH . 'wp-content/plugins/avantio-api-integration/js/widgetMaps.js',
				ABSPATH . 'wp-content/plugins/avantio-api-integration/js/mapbox-gl.js',
				ABSPATH . 'wp-content/plugins/avantio-api-integration/js/mapboxgl-spiderifier.js',
			);
			minify_css_js_files($map1_js_file_to_be_minified, ABSPATH . 'wp-content/plugins/avantio-api-integration/js/minified-map-scripts.js', 'js');
		}
		wp_enqueue_style('map-styles', MYPLUGIN_PLUGIN_URL . '/css/minified-map.css', false, $minified_plugin_versions_juvo);
		//wp_enqueue_style('map-gl-styles', 'https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.css');
		//wp_enqueue_style('map-gl-styles', MYPLUGIN_PLUGIN_URL . '/css/mapbox-gl.css', false, $minified_plugin_versions_juvo);
		//wp_enqueue_style('map2-styles', MYPLUGIN_PLUGIN_URL . '/css/minified-map2.css', false, $minified_plugin_versions_juvo);
		wp_enqueue_script('map-script', MYPLUGIN_PLUGIN_URL . '/js/minified-map-scripts.js', array('jquery'), $minified_plugin_versions_juvo, true);
    }
	// Saved Homes shortcode scripts
    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'avantio_saved_homes')) {
    	if ($minified_plugin_versions_juvo !== $last_minified_version) {
    		$savedhomes_css_files_to_be_minified = array(
				ABSPATH . 'wp-content/plugins/avantio-api-integration/css/loader.css',
			);
    		minify_css_js_files($savedhomes_css_files_to_be_minified, ABSPATH . 'wp-content/plugins/avantio-api-integration/css/minified-loader.css', 'css');
			$savedhomes_js_file_to_be_minified = array(
				ABSPATH . 'wp-content/plugins/avantio-api-integration/js/savedHomesRender.js',
			);
			minify_css_js_files($savedhomes_js_file_to_be_minified, ABSPATH . 'wp-content/plugins/avantio-api-integration/js/minified-savedhomes-search-scripts.js', 'js');
		}
		wp_enqueue_style('loader-styles', MYPLUGIN_PLUGIN_URL . '/css/minified-loader.css', false, $minified_plugin_versions_juvo);
		wp_enqueue_script('savedhomesrender-script', MYPLUGIN_PLUGIN_URL . '/js/minified-savedhomes-search-scripts.js', array('jquery'), $minified_plugin_versions_juvo, true);
    }
	/* Search Data for filters scripts and css on it's shortcode */
	if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'avantio_search_filter')) {
        if (preg_match_all('/\[avantio_search_filter([^\]]*)\]/', $post->post_content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                // Parse the attributes string
                $attrs = shortcode_parse_atts($match[1]);
                if (isset($attrs['showfilter']) && $attrs['showfilter'] === 'no') {
                	$select2_script_url = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js';
					$search_popup_script_url = '/js/searchFilter.js';
					$search_popup_css_url = '/css/search-filter.css';
                	if ($minified_plugin_versions_juvo !== $last_minified_version) {
						$search_css_files_to_be_minified = array(
							ABSPATH . 'wp-content/plugins/avantio-api-integration' . $search_popup_css_url,
						);
						minify_css_js_files($search_css_files_to_be_minified, ABSPATH . 'wp-content/plugins/avantio-api-integration/css/minified-small-search-styles.css', 'css');
						$search_js_file_to_be_minified = array(
							ABSPATH . 'wp-content/plugins/avantio-api-integration' . $search_popup_script_url,
						);
						minify_css_js_files($search_js_file_to_be_minified, ABSPATH . 'wp-content/plugins/avantio-api-integration/js/minified-small-search-scripts.js', 'js');
					}
			        wp_enqueue_script('select2-script', $select2_script_url, array('jquery'), null, true);
				    wp_enqueue_script('searchpopup-script', MYPLUGIN_PLUGIN_URL . '/js/minified-small-search-scripts.js', array('jquery'), $minified_plugin_versions_juvo, true);
				    wp_enqueue_style('search-styles', MYPLUGIN_PLUGIN_URL . '/css/minified-small-search-styles.css', false, $minified_plugin_versions_juvo);
                } else {
                	$search_withoutpopup_script_url = '/js/searchWithoutFilter.js';
                	$select2_script_url = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js';
					$search_popup_script_url = '/js/searchFilter.js';
					$search_popup_css_url = '/css/search-filter.css';
					if ($minified_plugin_versions_juvo !== $last_minified_version) {
						$search_css_files_to_be_minified = array(
							ABSPATH . 'wp-content/plugins/avantio-api-integration' . $search_popup_css_url,
						);
						minify_css_js_files($search_css_files_to_be_minified, ABSPATH . 'wp-content/plugins/avantio-api-integration/css/minified-small-search-styles.css', 'css');
						$search_filter_js_files_to_be_minified = array(
							ABSPATH . 'wp-content/plugins/avantio-api-integration' . $search_withoutpopup_script_url,
						);
						minify_css_js_files($search_filter_js_files_to_be_minified, ABSPATH . 'wp-content/plugins/avantio-api-integration/js/minified-fsearch-scripts.js', 'js');
						$search_js_files_to_be_minified = array(
							ABSPATH . 'wp-content/plugins/avantio-api-integration' . $search_popup_script_url,
						);
						minify_css_js_files($search_js_files_to_be_minified, ABSPATH . 'wp-content/plugins/avantio-api-integration/js/minified-small-search-scripts.js', 'js');
					}
		        	wp_enqueue_script('searchwithoutpopup-script', MYPLUGIN_PLUGIN_URL . '/js/minified-fsearch-scripts.js', array('jquery'), $minified_plugin_versions_juvo, true);
			        wp_enqueue_script('select2-script', $select2_script_url, array('jquery'), null, true);
				    wp_enqueue_script('searchpopup-script', MYPLUGIN_PLUGIN_URL . '/js/minified-small-search-scripts.js', array('jquery'), $minified_plugin_versions_juvo, true);
				    wp_enqueue_style('search-styles', MYPLUGIN_PLUGIN_URL . '/css/minified-small-search-styles.css', false, $minified_plugin_versions_juvo);
                }
            }
        }
    }
	// Enqueue external JS files
	wp_enqueue_script('moment-script', 'https://cdn.jsdelivr.net/momentjs/latest/moment.min.js', array('jquery'), '1.0', true);
    wp_enqueue_script('daterangepicker-script', 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js', array('jquery'), '1.0', true);
    // Enqueue local JS files
	wp_enqueue_script('additional-script-min', MYPLUGIN_PLUGIN_URL . '/js/minified-juvo-scripts.js', array('jquery'), $minified_plugin_versions_juvo, true);
	//wp_enqueue_script('prop-script', MYPLUGIN_PLUGIN_URL . '/js/prop-jq.js', array('jquery', 'owce-carousel'), $minified_plugin_versions_juvo, true);
	wp_enqueue_script('prop-script', MYPLUGIN_PLUGIN_URL . '/js/minified-juvo-prop-scripts.js', array('jquery', 'owce-carousel'), $minified_plugin_versions_juvo, true);
	// Holiday homes shortcode scripts
    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'avantio_holiday_homes')) {
    	if ($minified_plugin_versions_juvo !== $last_minified_version) {
			$hh_js_file_to_be_minified = array(
				ABSPATH . 'wp-content/plugins/avantio-api-integration/js/holidayHomeRender.js',
			);
			minify_css_js_files($hh_js_file_to_be_minified, ABSPATH . 'wp-content/plugins/avantio-api-integration/js/minified-hh-search-scripts.js', 'js');
		}
		wp_enqueue_script('hhrender-script', MYPLUGIN_PLUGIN_URL . '/js/minified-hh-search-scripts.js', array('jquery'), $minified_plugin_versions_juvo, true);
    }
    // Offers shortcode scripts
    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'avantio_offers')) {
    	if ($minified_plugin_versions_juvo !== $last_minified_version) {
    		$offers_css_files_to_be_minified = array(
				ABSPATH . 'wp-content/plugins/avantio-api-integration/css/loader.css',
			);
    		minify_css_js_files($offers_css_files_to_be_minified, ABSPATH . 'wp-content/plugins/avantio-api-integration/css/minified-loader.css', 'css');
			$offers_js_file_to_be_minified = array(
				ABSPATH . 'wp-content/plugins/avantio-api-integration/js/offersRender.js',
			);
			minify_css_js_files($offers_js_file_to_be_minified, ABSPATH . 'wp-content/plugins/avantio-api-integration/js/minified-offers-search-scripts.js', 'js');
		}
		wp_enqueue_style('loader-styles', MYPLUGIN_PLUGIN_URL . '/css/minified-loader.css', false, $minified_plugin_versions_juvo);
		wp_enqueue_script('offersrender-script', MYPLUGIN_PLUGIN_URL . '/js/minified-offers-search-scripts.js', array('jquery'), $minified_plugin_versions_juvo, true);
    }
	// Localize script data
	wp_localize_script('prop-script', 'propScriptData', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('prop-script-nonce')
    ));
}
add_action('wp_enqueue_scripts', 'avantio_main_load_styles_and_scripts', 1);

function optimize_custom_plugin_assets_nitropack() {
    if (function_exists('nitropack')) {
    	// External CSS files
        nitropack()->autoOptimizeStyles(array('fontawesome-style', 'daterangepickerstyle', 'select2-css'));
    	// Local CSS files
        nitropack()->autoOptimizeStyles(array('swiper-styles', 'lightbox-gallery-style', 'prop-styles'));
        // External JS files
        nitropack()->autoOptimizeScripts(array('moment-script', 'daterangepicker-script'));
        // Local JS files
        nitropack()->autoOptimizeScripts(array('carousel-script', 'swiper-script', 'lightbox-gallery-script', 'prop-script'));
    }
}
add_action('wp_enqueue_scripts', 'optimize_custom_plugin_assets_nitropack');

function acco_image_url($url) {
	$upload_dir = wp_upload_dir();
	$file = str_replace('http://img.crs.itsolutions.es', $upload_dir['basedir'], $url);
	$fileurl = str_replace('http://img.crs.itsolutions.es', $upload_dir['baseurl'], $url);
	if (file_exists($file)) {
		return $fileurl;
	} else {
		if (stristr($url, 'https')) {
			return $url;
		} else {
			return MYPLUGIN_PLUGIN_URL.'/images/empty-image.png';
		}
	}
}

function handle_ajax_request() {
    try {
        // Check nonce for security
        check_ajax_referer('prop-script-nonce', 'nonce');
        // Process form data here
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
		// Avantio API credentials and other parameters
		$username = 'trident';
		$password = '7Mx4EuPGpPy6';
		$apiKey = 'trident';
		$secretKey = '';
		$LoginGA = 'james';
		$accommodationId = isset($_POST['prop_id']) ? $_POST['prop_id'] : '';
		$accID = isset($_POST['acc_id']) ? $_POST['acc_id'] : '';
		$company = 'james';
		$partnerCode = '25ce87c2384f552afd0144c97669c840';
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
					'LoginGA' => $LoginGA
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
		

		/* $accommodationData = getAccommodationFeeds($company, $accommodationId, $accID, $dateFrom, $dateTo, $dateToAPI, $result_IsAvailable, $result_GetBookingPrice, $language); */
		$availabilityData = AvailabilityFeedsPHP($accommodationId, $language);
		if (isset($result_GetBookingPrice)) {
			$result_GetBookingPrice = $result_GetBookingPrice;
		} else {
		    $result_GetBookingPrice = null;
		}
		$accommodationData = getAccommodationFeeds($company, $accommodationId, $accID, $dateFrom, $dateTo, $dateToAPI, $result_IsAvailable, $result_GetBookingPrice, $language);
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
		$errorMessage = '';
		$todaysDate = date('Y-m-d');
        $todayTimestamp = strtotime($todaysDate);
        if (isset($_POST['dateFrom'])) {
            $fromDate_post = $_POST['dateFrom'];
        } else {
            $fromDate_post = $dateFrom;
        }
        $otherDateTimestamp = strtotime($fromDate_post);
        $numberOfDays = floor(($otherDateTimestamp - $todayTimestamp) / (60 * 60 * 24));
        $minDaysNotice = intval($availabilityData['MinDaysNotice']);
        $numberOfDays = intval($numberOfDays);
        $errorMessage .= '';
        if (isset($minDaysNotice) && $minDaysNotice > $numberOfDays) {
            //$errorMessage .= '<span class="form-error-results">This property requires a minimum notice of ' . $availabilityData['MinDaysNotice'] . ' day' . ($availabilityData['MinDaysNotice'] != 1 ? 's' : '') . '</span>';
            $errorMessage .= '<span class="form-error-results">Please contact us direct to check the availability and price for these dates</span>';
        } else if ($result_IsAvailable->Available->AvailableCode === 0 && $getBookingPrice_info === 'yes') {
            $errorMessage .= '<span class="form-error-results">The accommodation is not available on these dates. Please contact us to discuss your needs</span>';
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
			            $errorMessage .= '<span class="form-error-results">The booking during this period is only possible with an arrival on ' . ucwords($checkInDaysFormatted) . '</span>';
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
			            $errorMessage .= '<span class="form-error-results">The booking during this period is only possible with a departure on ' . ucwords($checkOutDaysFormatted) . '</span>';
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
			        $errorMessage .= '<span class="form-error-results">This property requires a ' . $minStay . ' night minimum stay';
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
            $errorMessage .= '<span class="form-error-results">' . $exceeds2Months . '</span>';
        } else if ($result_IsAvailable->Available->AvailableCode === -8 && $getBookingPrice_convert === 'yes') {
        	$errorMessage .= '<span class="form-error-results">Please contact us direct to check the availability and price for these dates</span>';
        } else if ($result_IsAvailable->Available->AvailableCode === -9 && $getBookingPrice_convert === 'yes') {
        	$errorMessage .= '<span class="form-error-results">The accommodation is no longer available</span>';
        } else if ($result_IsAvailable->Available->AvailableCode === -99 && $getBookingPrice_info === 'yes') {
            $errorMessage .= '<span class="form-error-results">The number of occupants exceeds the maximum permitted</span>';
        } else if (!$result_IsAvailable->Available->AvailableCode && $getBookingPrice_info === 'yes') {
            $errorMessage .= '<span class="form-error-results">Dates do not match for your selection, please select different dates</span>';
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
			            $errorMessage .= '<span class="form-error-results">The booking during this period is only possible with a departure on ' . ucwords($checkOutDaysFormatted) . '</span>';
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
			            $errorMessage .= '<span class="form-error-results">The booking during this period is only possible with an arrival on ' . ucwords($checkInDaysFormatted) . '</span>';
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
			            $errorMessage .= '<span class="form-error-results">The booking during this period is only possible with an arrival on ' . ucwords($checkInDaysFormatted) . '</span>';
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
			            $errorMessage .= '<span class="form-error-results">The booking during this period is only possible with a departure on ' . ucwords($checkOutDaysFormatted) . '</span>';
			            break;
			        }
			    }
			}
        } else if ($result_IsAvailable->Available->AvailableCode != 1 && $getBookingPrice_info === 'yes' && $result_IsAvailable->Available->AvailableMessage === 'Under petition' ) {
            $errorMessage .= '<span class="form-error-results">We will contact you when our office reopens about your booking request</span>';
        } else if ($result_IsAvailable->Available->AvailableCode != 1 && $getBookingPrice_info === 'yes') {
            //$errorMessage .= '<span class="form-error-results">' . $result_IsAvailable->Available->AvailableMessage . '</span>';
            $errorMessage .= '<span class="form-error-results">Please contact us direct to check the availability and price for these dates</span>';
        } else if (empty($result_GetBookingPrice->BookingPrice) && $result_IsAvailable->Available->AvailableCode == 1 && $getBookingPrice_info === 'yes') {
        	$errorMessage .= '<span class="form-error-results">Please contact us direct to check the availability and price for these dates</span>';
        }
        $descriptionsData = getDescriptionsFeeds($accommodationId, $language);
        if (!empty($errorMessage)) {
        	// Desktop
        	$output_errorSidebar = '';
        	$output_errorSidebar .= '<div class="sidebar-pricebox">';
        	$output_errorSidebar .= '<div class="column-xs-4"><label class="from">From</label></div>';
            $output_errorSidebar .= '<div class="column-xs-8"><span class="aprice">' . $accommodationData['RatePrice'] .'</span><span class="perweek">/week</span></div>';
            $output_errorSidebar .= '</div>';
			$output_rightsidebar = '';
			if (isset($descriptionsData['ContactURL'])) {
			    $descriptionsData['ContactURL'] = preg_replace(
			        '/^(http:\/\/www\.|https:\/\/www\.)/',
			        'https://bookings.',
			        $descriptionsData['ContactURL']
			    );
			}
			if ($result_IsAvailable->Available->AvailableCode != 1 && $getBookingPrice_info === 'yes' && $result_IsAvailable->Available->AvailableMessage === 'The compulsory arrival date is not fulfilled' ){
				$output_rightsidebar = '<button class="button-book-search" type="button" name="reserve-edit-dates" id="reserve-edit-dates" aria-label="Edit Dates">Edit Dates</button>';
			} else if ($result_IsAvailable->Available->AvailableMessage === 'Under petition') {
				$output_rightsidebar = '<a id="reserve-contact-us" class="button-contact-sidebar contactParameters" href="' . $descriptionsData['ContactURL'] . '" title="Contact Agency" aria-label="Contact Us" target="_blank" rel="nofollow">Contact Us</a>';
			} else {
				$output_rightsidebar = '<button class="button-book-search" type="input" name="reserve-submit-button" id="reserve-submit-button" aria-label="Check Availability">Check Availability</button>';
			}
            // Mobile
            $output_errorMobileSidebar = '';
            $output_errorMobileSidebar .= '<div class="sidebar-pricebox">';
        	$output_errorMobileSidebar .= '<div class="column-xs-8"><label class="from">From</label><span class="aprice">' . $accommodationData['RatePrice'] . '</span><span class="perweek">/week</span><span class="form-instructions">Set dates and guests to see the exact price</span></div>';
            $output_errorMobileSidebar .= '<div class="column-right-button"><a class="button-book-search mobilebookingpop" role="button" aria-label="Set Dates &amp; Guests">Set Dates &amp; Guests</a></div>';
            $output_errorMobileSidebar .= '</div>';
			$response_error_data = array(
		        'error_message' => $errorMessage,
		        'price_sidebar' => $output_errorSidebar,
		        'mobile_sidebar' => $output_errorMobileSidebar,
				'button_side_bar' => $output_rightsidebar
		    );
			wp_send_json_error($response_error_data);
			exit();
		}
        /******* RIGHT SIDEBAR - DESKTOP & MOBILE *******/
        $output_rightsidebar = '<div class="sidebar-pricebox">';
        $output_successMobileSidebar = '<div class="sidebar-pricebox">';
        // Start Price
        if ($result_IsAvailable->Available->AvailableCode) {
            if ($getBookingPrice_info === 'yes') {
                if ($result_GetBookingPrice->BookingPrice && $result_IsAvailable->Available->AvailableCode === 1 && isset($minDaysNotice) && $minDaysNotice <= $numberOfDays) {
                    $descriptionsData = getDescriptionsFeeds($accommodationId, $language);
                    $priceConvert_BP = str_replace('EUR', '&euro;', $result_GetBookingPrice->BookingPrice->Currency);
                    //$priceNF = (float)number_format($result_GetBookingPrice->BookingPrice->RoomOnlyFinal, 2);
                    $priceNF = $result_GetBookingPrice->BookingPrice->RoomOnlyFinal;
                    $priceRound_BP = round($priceNF);
                    $peopleText = ((int)$adultsNumber === 1) ? ' Person' : ' People';
                    $bookingStartDateAPI = strtotime($dateFrom);
                    $bookingEndDateAPI = strtotime($dateTo);
                    $bookingMinimumNightsAPI = ceil(($bookingEndDateAPI - $bookingStartDateAPI) / (60 * 60 * 24));
                    $bookingNumNightsText = ((int)$bookingMinimumNightsAPI === 1) ? $bookingMinimumNightsAPI.' night' : $bookingMinimumNightsAPI.' nights';
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
                    if (isset($descriptionsData['BookingURL'])) {
					    $descriptionsData['BookingURL'] = preg_replace(
					        '/^(http:\/\/www\.|https:\/\/www\.)/',
					        'https://bookings.',
					        $descriptionsData['BookingURL']
					    );
					}
                    // Desktop
                    //$output_rightsidebar .= '<div class="column-xs-4"><label class="from">From</label></div>';
                    $output_rightsidebar .= '<div class="column-xs-4"><label class="from"></label></div>';
                    $output_rightsidebar .= '<div class="column-xs-8"><span class="aprice">' . $priceConvert_BP . $priceRound_BP .'</span><span class="perweek">/' . $bookingNumNightsText . '</span></div>';
                    $output_rightsidebar .= '</div>';
                    $output_rightsidebar .= '<div class="sidebar-priceinfobox">';
                    $output_rightsidebar .= '<div class="bookingb">';
                    $output_rightsidebar .= '<a href="' . $descriptionsData['BookingURL'] . $booking_url_queries . '" class="button-book" title="Book Accommodation" aria-label="Book Accommodation" rel="nofollow">Book</a>';
                    $output_rightsidebar .= '</div>';
                    // Mobile
                    //$output_successMobileSidebar .= '<div class="column-xs-8"><label class="from">From</label><span class="aprice">' . $priceConvert_BP . $priceRound_BP .'</span><span class="form-instructions">Set dates and guests to see the exact price</span></div>';
                    $output_successMobileSidebar .= '<div class="column-xs-8"><label class="from"></label><span class="aprice">' . $priceConvert_BP . $priceRound_BP .'</span><span class="form-instructions">Set dates and guests to see the exact price</span></div>';
                    $output_successMobileSidebar .= '<div class="column-right-button"><a class="button-book-search mobilebookingpop button-book" role="button" aria-label="Book">Book</a></div>';
                    $output_successMobileSidebar .= '</div>';
                    $output_successMobileSidebar .= '<div class="sidebar-priceinfobox">';
                    // $output_successMobileSidebar .= '<div class="bookingb">';
                    // $output_successMobileSidebar .= '<a href="' . $descriptionsData['BookingURL'] . $booking_url_queries . '" class="button-book" title="Book Accommodation" aria-label="Book Accommodation" rel="nofollow">Book</a>';
                    // $output_successMobileSidebar .= '</div>';
                } else {
                	// Desktop
                    $output_rightsidebar .= '<div class="column-xs-4"><label class="from">From</label></div>';
                    $output_rightsidebar .= '<div class="column-xs-8"><span class="aprice">' . $accommodationData['RatePrice'] .'</span><span class="perweek">/week</span></div>';
                    // Mobile
                    $output_successMobileSidebar .= '<div class="column-xs-8"><label class="from">From</label><span class="aprice">' . $accommodationData['RatePrice'] .'</span><span class="perweek">/week</span><span class="form-instructions">Set dates and guests to see the exact price</span></div>';
                    $output_successMobileSidebar .= '<div class="column-right-button"><a class="button-book-search mobilebookingpop" role="button" aria-label="Set Dates &amp; Guests">Set Dates &amp; Guests</a></div>';
                }
            } else {
                if ($result_GetBookingPrice->BookingPrice) {
                	// Desktop
                    $output_rightsidebar .= '<div class="column-xs-4"><label class="from">From</label></div>';
                    $output_rightsidebar .= '<div class="column-xs-8"><span class="aprice">' . $accommodationData['RatePrice'] .'</span><span class="perweek">/week</span></div>';
                    // Mobile
                    $output_successMobileSidebar .= '<div class="column-xs-8"><label class="from">From</label><span class="aprice">' . $accommodationData['RatePrice'] .'</span><span class="perweek">/week</span><span class="form-instructions">Set dates and guests to see the exact price</span></div>';
                    $output_successMobileSidebar .= '<div class="column-right-button"><a class="button-book-search mobilebookingpop" role="button" aria-label="Set Dates &amp; Guests">Set Dates &amp; Guests</a></div>';
                }
            }
        }
		// End Price
        $output_rightsidebar .= '</div>';
        $dateForm_converted = date('d/m/Y', strtotime($dateFrom));
        $dateTo_converted = date('d/m/Y', strtotime($dateTo));
		$output_successMobileSidebar .= '<div class="checkinDate-box-outer"><label class="checkinDate-box-label">Dates: </label><span class="checkinDate-box-span">' . $dateForm_converted . ' - ' . $dateTo_converted . '</span></div>';
        $output_successMobileSidebar .= '</div>';
        // Send back a response
        if (!empty($output_rightsidebar) || !empty($output_successMobileSidebar)) {
        	$response_success_data = array(
        		'success_desktop' => $output_rightsidebar,
        		'success_mobile' => $output_successMobileSidebar
        	);
        	wp_send_json_success($response_success_data);
        	exit();
        }
    } catch (Exception $e) {
        wp_send_json_error($e->getMessage());
    }
}
add_action('wp_ajax_reserve', 'handle_ajax_request');
add_action('wp_ajax_nopriv_reserve', 'handle_ajax_request');

function rewriteFiltersURL($url, $action) {
	/*if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'rewrite_url_action')) {
        wp_redirect(home_url('/rentals-search/'), 301);
        exit();
    }*/
    $parsedUrl = parse_url($url);
    $queryParams = [];
    if (isset($parsedUrl['query'])) {
        parse_str($parsedUrl['query'], $queryParams);
    }
    switch ($action) {
        case 'clearPeople':
            unset($queryParams['AdultNum'], $queryParams['ChildrenNum']);
            break;
        case 'clearDestination':
            unset($queryParams['destination']);
            break;
        case 'clearDates':
            unset($queryParams['daterange'], $queryParams['dateFrom'], $queryParams['dateTo']);
            break;
        case 'clearThhParams':
            foreach ($queryParams as $key => $value) {
                if (strpos($key, 'thh-') === 0) {
                    unset($queryParams[$key]);
                }
            }
            break;
    }
    $newQueryString = http_build_query($queryParams);
    $newUrl = (isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : '') . (isset($parsedUrl['host']) ? $parsedUrl['host'] : '') . (isset($parsedUrl['path']) ? $parsedUrl['path'] : '') . '?' . $newQueryString;
    return $newUrl;
}

function addSpacesA($string) {
	if (preg_match('/[a-z]/', $string)) {
		return preg_replace('/(?<!\ )[A-Z]/', ' $0', $string);
	} else {
		return $string;
	}
}

function AvailabilityFeedsPHP($accommodationId) {
    $data = [];
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
                            if ($period->State == 'AVAILABLE' || $period->State == 'ONREQUEST') {
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

function getDescriptionsFeeds($accommodationId, $language) {
	$plugin_dir = plugin_dir_path(__FILE__);
	$descriptionsFile = $plugin_dir . 'feeds/Descriptions.xml';
	$data = array();
	if (file_exists($descriptionsFile) && $accommodationId) {
		$descriptionsOutput = file_get_contents($descriptionsFile);
		if ($descriptionsFile !== false) {
			$descriptionsXml = simplexml_load_string($descriptionsOutput);
			if ($descriptionsXml !== false) {
				foreach ($descriptionsXml->Accommodation as $accommodationDesc) {
					if ($accommodationDesc->AccommodationId == $accommodationId) {
						$images = array();
						// Process Images Info and add to the images array
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
						$data['Images'] = $images;
						// Process Description Info and add to the data array
						foreach ($accommodationDesc->InternationalizedItem as $item) {
							if ($item->Language == $language) {
								$data['Language'] = (string)$item->Language;
								$data['AccommodationName'] = (string)$item->AccommodationName;
								$data['Description'] = (string)$item->Description;
								$data['DetailsURL'] = (string)$item->DetailsURL;
								$data['BookingURL'] = (string)$item->BookingURL;
								$data['ContactURL'] = (string)$item->ContactURL;
								$data['MasterKind'] = array(
									'Code' => (string)$item->MasterKind->MasterKindCode,
									'Name' => (string)$item->MasterKind->MasterKindName,
								);
								$data['Location'] = array(
									'CountryCode' => (string)$item->Country->CountryCode,
									'CountryName' => (string)$item->Country->Name,
									'RegionCode' => (string)$item->Region->RegionCode,
									'RegionName' => (string)$item->Region->Name,
									'CityCode' => (string)$item->City->CityCode,
									'CityName' => (string)$item->City->Name,
									'ProvinceCode' => (string)$item->Province->ProvinceCode,
									'ProvinceName' => (string)$item->Province->Name,
									'LocalityCode' => (string)$item->Locality->LocalityCode,
									'LocalityName' => (string)$item->Locality->Name,
									'DistrictCode' => (string)$item->District->DistrictCode,
									'DistrictName' => (string)$item->District->Name,
								);
								$extras = array(
									'ObligatoryOrIncluded' => array(),
									'Optional' => array(),
								);
								foreach ($item->ExtrasSummary->ObligatoryOrIncluded->Extra as $extra) {
									$extras['ObligatoryOrIncluded'][] = array(
										'Name' => (string)$extra->Name,
										'Description' => (string)$extra->Description,
									);
								}
								foreach ($item->ExtrasSummary->Optional->Extra as $extra) {
									$extras['Optional'][] = array(
										'Name' => (string)$extra->Name,
										'Description' => (string)$extra->Description,
									);
								}
								$data['Extras'] = $extras;
								break;
							}
						}
						break;
					}
				}
			}
		}
	}
	return $data;
}

function getAccommodationFeeds($company, $accommodationId, $accID, $dateFrom, $dateTo, $dateToAPI, $result_IsAvailable, $result_GetBookingPrice, $language) {
	$plugin_dir = plugin_dir_path(__FILE__);
	$accommodationsFile = $plugin_dir.'feeds/Accommodations.xml';
	$servicesFile = $plugin_dir.'feeds/Services.xml';
	$ratesFile = $plugin_dir.'feeds/Rates.xml';
	$data = array();
	if (file_exists($accommodationsFile) && file_exists($servicesFile) && file_exists($ratesFile) && $accommodationId) {
		$accommodationsOutput = file_get_contents($accommodationsFile);
		$servicesOutput = file_get_contents($servicesFile);
		$ratesOutput = file_get_contents($ratesFile);
		if ($accommodationsOutput !== false) {
			$accommodationsXml = simplexml_load_string($accommodationsOutput);
			$servicesXml = simplexml_load_string($servicesOutput);
			$ratesXml = simplexml_load_string($ratesOutput);
			if ($accommodationsXml !== false) {
				// Iterate over the properties in the XML
				foreach ($accommodationsXml->Accommodation as $accommodation) {
					//var_dump($accommodation); die;
					if ($accommodation->Company == $company && $accommodation->AccommodationId == $accommodationId) {
						$data['UserId'] = (string)$accommodation->UserId;
						$data['Company'] = (string)$accommodation->Company;
						$data['CompanyId'] = (string)$accommodation->CompanyId;
						$data['AccommodationName'] = (string)$accommodation->AccommodationName;
						$data['AccommodationId'] = (string)$accommodation->AccommodationId;
						$data['Purpose'] = (string)$accommodation->Purpose;
						$data['UserKind'] = (string)$accommodation->UserKind;
						$data['MasterKindCode'] = (string)$accommodation->MasterKind->MasterKindCode;
						$data['MasterKindName'] = (string)$accommodation->MasterKind->MasterKindName;
						$data['IdGallery'] = (string)$accommodation->IdGallery;
						$data['OccupationalRuleId'] = (string)$accommodation->OccupationalRuleId;
						$data['PriceModifierId'] = (string)$accommodation->PriceModifierId;
						$data['TouristicRegistrationNumber'] = (string)$accommodation->TouristicRegistrationNumber;
						$data['AccommodationUnits'] = (string)$accommodation->AccommodationUnits;
						$data['Currency'] = (string)$accommodation->Currency;
						$data['Included'] = (string)$accommodation->VAT->Included;
						$data['Labels'] = (array)$accommodation->Labels->Label;
						$data['LocalizationData'] = array(
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
						);
						$data['Features'] = array(
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
						);
						$data['Comments'] = array(
							'AcceptYoungsters' => (string)$accommodation->Features->Distribution->AcceptYoungsters,
							'SmokingAllowed' => (string)$accommodation->Features->HouseCharacteristics->SmokingAllowed,
						);
						// Main Characteristics
						$characteristics = [];
						$optionTitles = [];
						processCharacteristics($accommodation->Features->HouseCharacteristics, $characteristics, $optionTitles);
						if (isset($characteristics) && is_array($characteristics)) {
							$data['Characteristics'] = $characteristics;
						} else {
							$data['Characteristics'] = '';
						}
						if (isset($optionTitles) && is_array($optionTitles)) {
							$data['CharacteristicsOptionTitles'] = $optionTitles;
						} else {
							$data['CharacteristicsOptionTitles'] = '';
						}
						$characteristicsGeneral = [];
						processGeneralCharacteristics($accommodation->Features->HouseCharacteristics, $characteristicsGeneral);
						if (isset($characteristicsGeneral) && is_array($characteristicsGeneral)) {
							$data['CharacteristicsGeneral'] = $characteristicsGeneral;
						} else {
							$data['CharacteristicsGeneral'] = '';
						}
						$kitchenCharacteristics = [];
						$optionKitchenTitles = [];
						processKitchenCharacteristics($accommodation->Features->HouseCharacteristics->Kitchen, $kitchenCharacteristics, $optionKitchenTitles);
						if (isset($kitchenCharacteristics) && is_array($kitchenCharacteristics)) {
							$data['KitchenCharacteristics'] = $kitchenCharacteristics;
						} else {
							$data['KitchenCharacteristics'] = '';
						}
						if (isset($optionKitchenTitles) && is_array($optionKitchenTitles)) {
							$data['KitchenOptionTitles'] = $optionKitchenTitles;
						} else {
							$data['KitchenOptionTitles'] = '';
						}
						// Locations Neareast Places
						$placeInfo = [];
						$data['LocationDescriptionWhere'] = (string)$accommodation->Features->Location->LocationDescription->Where;
						$data['LocationDescriptionHowto'] = (string)$accommodation->Features->Location->LocationDescription->Howto;
						$data['LocationDescriptionDescription1'] = (string)$accommodation->Features->Location->LocationDescription->Description1;
						$data['LocationDescriptionDescription2'] = (string)$accommodation->Features->Location->LocationDescription->Description2;
						foreach ($accommodation->Features->Location as $keyLocation => $valueLocation) {
							if (is_object($valueLocation) && $keyLocation != 'LocationDescription') {
								foreach ($valueLocation as $subKey => $subValue) {
									$parentKey2 = $subKey;
									//if ($parentKey2 == 'LocationDistances' || $parentKey2 == 'NearestPlaces') {
									if ($parentKey2 == 'LocationDistances') {
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
													$numericValue = (float)str_replace(' KM', '', $valueKey);
													$parentKey3 = addSpacesA($parentKey3);
													$placeString = '<span class="top-distances">' . $valueKey . ' ' . $unitKey . '</span><strong>' . trim($parentKey3) . '</strong> - ' . $nameKey;
													// Store place information along with distance value in an array
													$placeInfo[] = array(
														'placeString' => $placeString,
														'numericValue' => $numericValue,
														'unitKey' => $unitKey
													);
												}
											}
										}
									}
								}
							}
						}
						// Sort the place information array by numeric value
						usort($placeInfo, function($a, $b) {
							if ($a['numericValue'] == $b['numericValue']) {
								return 0;
							}
							return ($a['numericValue'] < $b['numericValue']) ? -1 : 1;
						});
						// Create the final places array with sorted information
						$places = [];
						foreach ($placeInfo as $placeData) {
							$places[] = $placeData['placeString'];
						}
						if (isset($places) && is_array($places)) {
							$data['Places'] = $places;
						} else {
							$data['Places'] = '';
						}
						// Views from Accommodation - Location Views
						$viewTypes = ['ViewToBeach', 'ViewToSwimmingPool', 'ViewToGolf', 'ViewToGarden', 'ViewToRiver', 'ViewToMountain', 'ViewToLake'];
						$cleanedViewTypes = [];
						foreach ($viewTypes as $viewType) {
							if (isset($accommodation->Features->Location->LocationViews->$viewType)) {
								if (!empty($accommodation->Features->Location->LocationViews->$viewType) && $accommodation->Features->Location->LocationViews->$viewType == "true") {
									$convertedViewType = str_replace('ViewTo', '', $viewType);
									$convertedViewType = addSpacesA($convertedViewType);
									$cleanedViewTypes[] = (string) trim($convertedViewType); // Remove leading and trailing spaces
								}
							}
						}
						if (isset($cleanedViewTypes) && is_array($cleanedViewTypes)) {
							$data['ViewType'] = $cleanedViewTypes;
						} else {
							$data['ViewType'] = '';
						}
						// Extras and Services
						$extraservices = [];
						$extraPaymentMethod = [];
						foreach ($accommodation->Features->ExtrasAndServices->SpecialServices->SpecialService as $service) {
							// Only include OBLIGATORIO-SIEMPRE services which is spanish for MANDATORY-ALWAYS
							//if ($service->Application == 'OBLIGATORIO-SIEMPRE') {
						    $normalizedSpecialServices = [];
						    foreach ($service as $keySpecialServices => $valueSpecialServices) {
						        $normalizedSpecialServices[trim((string)$keySpecialServices)] = trim((string)$valueSpecialServices);
						    }
						    $type = '';
						    $changeFrequency = '';
						    $countableLimit = '';
							if ((int)$service->Code === 9) {
								if ($service->Allowed == 'si') {
									$type = 'Pet';
								}
							}
						    if (isset($normalizedSpecialServices['Type'])) {
						        if ($servicesXml !== false) {
						            foreach ($servicesXml->Service as $services) {
						                foreach ($services->Name as $name) {
						                    if ($name->Language == $language && $services->Code == $normalizedSpecialServices['Code']) {
						                        $type = (string)$name->Text;
						                        break;
						                    }
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
						    // Check if the "PaymentMethod" element exists and is not empty
					        if (isset($normalizedSpecialServices['PaymentMethod']) && !empty($normalizedSpecialServices['PaymentMethod']) && $normalizedSpecialServices['Code'] == 11) {
					            $extraPaymentMethod[] = $normalizedSpecialServices['PaymentMethod'];
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
							//}
						}
						if (isset($extraservices) && is_array($extraservices)) {
							$data['ExtraServices'] = $extraservices;
						} else {
							$data['ExtraServices'] = '';
						}
						if (isset($extraPaymentMethod) && is_array($extraPaymentMethod)) {
							$data['ExtraPaymentMethod'] = array_filter($extraPaymentMethod);
						} else {
							$data['ExtraPaymentMethod'] = '';
						}
						// Common Services
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
						$commonServices = [];
						foreach ($accommodation->Features->ExtrasAndServices->CommonServices->CommonService as $commonservice) {
							$normalizedCommonServices = [];
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
							$commonServices[] = $typeC . " " . $countableLimit;
						}
						if (isset($commonServices) && is_array($commonServices)) {
							$data['CommonServices'] = $commonServices;
						} else {
							$data['CommonServices'] = '';
						}
						// Check In/Out Info
						$checkInRule = $accommodation->CheckInCheckOutInfo->CheckInRules->CheckInRule;
						$fromVal = (string)$checkInRule->Schedule->From;
						$toVal = (string)$checkInRule->Schedule->To;
						$from_24hr = date("H:i a", strtotime($fromVal));
						$to_24hr = date("H:i a", strtotime($toVal));
						$fromValue = (string)$from_24hr;
						$toValue = (string)$to_24hr;
						$daysOfApplication = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
						$selectedDays = [];
						foreach ($daysOfApplication as $day) {
							if ((string)$checkInRule->DaysOfApplication->$day === 'true') {
								// Extract the first 3 letters of the string
								$selectedDays[] = substr($day, 0, 3);
							}
						}
						if (!isset($data['CheckInDays'])) {
							$data['CheckInDays'] = '';
						}
						if (count($selectedDays) === 7) {
							$data['CheckInDays'] .= 'Monday - Sunday';
						} elseif (count($selectedDays) === 6 && (in_array('Mon', $selectedDays) && in_array('Tue', $selectedDays) && in_array('Wed', $selectedDays) && in_array('Thu', $selectedDays) && in_array('Fri', $selectedDays) && in_array('Sat', $selectedDays))) {
							$data['CheckInDays'] .= 'Monday - Saturday';
						} elseif (count($selectedDays) === 5 && (in_array('Mon', $selectedDays) && in_array('Tue', $selectedDays) && in_array('Wed', $selectedDays) && in_array('Thu', $selectedDays) && in_array('Fri', $selectedDays))) {
							$data['CheckInDays'] .= 'Monday - Friday';
						} else {
							$data['CheckInDays'] .= implode(', ', $selectedDays);
						}
						if (!isset($data['CheckIn'])) {
							$data['CheckIn'] = '';
						}
						if ($toVal !== '') {
							$data['CheckIn'] .= ' ' . (string)$fromValue . ' - ' . $toValue;
						} elseif ($fromVal !== '' && $toVal === '') {
							$data['CheckIn'] .= ' ' . (string)$fromVal;
						}
						$checkout_24hr = date("H:i a", strtotime($accommodation->CheckInCheckOutInfo->CheckOutSchedule));
						if (!isset($data['CheckOut'])) {
							$data['CheckOut'] = '';
						}
						$data['CheckOut'] .= (string)$checkout_24hr;
						// Reviews
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
						$data['Reviews'] = $reviews;
						$data['AverageAspectRatings'] = $averageAspectRatings;
						// Regulations - Taxes
						$regulations = [];
						if (is_array($accommodation->TouristTaxes->TouristTax) || is_object($accommodation->TouristTaxes->TouristTax)) {
							foreach ($accommodation->TouristTaxes->TouristTax as $touristTax) {
								$regulation = [];
								if (isset($touristTax->Name)) {
									$regulation['Name'] = (string)$touristTax->Name;
								}
								if (isset($touristTax->TaxId)) {
									$regulation['TaxId'] = (string)$touristTax->TaxId;
								}
								if (isset($touristTax->VatId)) {
									$regulation['VatId'] = (string)$touristTax->VatId;
								}
								if (isset($touristTax->VatIncluded)) {
									$regulation['VatIncluded'] = (string)$touristTax->VatIncluded;
								}
								if (isset($touristTax->PaymentMoment)) {
									$regulation['PaymentMoment'] = (string)$touristTax->PaymentMoment;
								}
								$seasons = [];
								foreach ($touristTax->Seasons->Season as $season) {
									$seasonData = [];
									if (isset($season->StartDay)) {
										$seasonData['StartDay'] = (string)$season->StartDay;
									}
									if (isset($season->StartMonth)) {
										$seasonData['StartMonth'] = (string)$season->StartMonth;
									}
									if (isset($season->EndDay)) {
										$seasonData['EndDay'] = (string)$season->EndDay;
									}
									if (isset($season->EndMonth)) {
										$seasonData['EndMonth'] = (string)$season->EndMonth;
									}
									if (isset($season->AdultsActive)) {
										$seasonData['AdultsActive'] = (string)$season->AdultsActive;
									}
									if (isset($season->ChildrenActive)) {
										$seasonData['ChildrenActive'] = (string)$season->ChildrenActive;
									}
									if (isset($season->PetActive)) {
										$seasonData['PetActive'] = (string)$season->PetActive;
									}
									if (isset($season->ReservationActive)) {
										$seasonData['ReservationActive'] = (string)$season->ReservationActive;
									}
									if (isset($season->ReservationUnitAmount)) {
										$seasonData['ReservationUnitAmount'] = (string)$season->ReservationUnitAmount;
									}
									if (isset($season->ReservationUnitAmountExcludedVat)) {
										$seasonData['ReservationUnitAmountExcludedVat'] = (string)$season->ReservationUnitAmountExcludedVat;
									}
									if (isset($season->ReservationType)) {
										$seasonData['ReservationType'] = (string)$season->ReservationType;
									}
									$seasons[] = $seasonData;
								}
								$regulations[] = $regulation;
							}
						}
						if (isset($regulations) && is_array($regulations)) {
							$data['Regulations'] = $regulations;
						} else {
							$data['Regulations'] = '';
						}
						// Calculate the total booking amount from the descriptions
						$descriptionsData = getDescriptionsFeeds($accommodationId, $language);
						$totalBookingAmount = 0.0;
						if (!empty($descriptionsData['Extras']['ObligatoryOrIncluded'])) {
						    foreach ($descriptionsData['Extras']['ObligatoryOrIncluded'] as $extraDescriptionBook) {
						        if ($extraDescriptionBook['Name'] !== 'Security Deposit (refundable)') {
						            // Check for the specific strings in the description and extract the number
						            if (strpos($extraDescriptionBook['Description'], '/ booking') !== false || strpos($extraDescriptionBook['Description'], '/booking') !== false) {
						                preg_match('/\b\d+(\.\d+)?\b/', $extraDescriptionBook['Description'], $matches);
						                if (!empty($matches)) {
						                    // Convert the first match to a float and add it to the total
						                    $totalBookingAmount += floatval($matches[0]);
						                }
						            }
						            if (strpos($extraDescriptionBook['Description'], '/ day') !== false || strpos($extraDescriptionBook['Description'], '/day') !== false) {
						                // Extract the number, multiply by 7 and add to the total booking amount
						                preg_match('/\b\d+(\.\d+)?\b/', $extraDescriptionBook['Description'], $matches);
						                if (!empty($matches)) {
						                    // Convert the first match to a float, multiply by 7, and add it to the total
						                    $totalBookingAmount += floatval($matches[0]) * 7;
						                }
						            }
						        }
						    }
						}
						// Rates with included booking services fees
						if ($ratesXml !== false) {
						    $weeklyprice = null;
						    foreach ($ratesXml->AccommodationList->Accommodation as $rate) {
						        if ((int)$rate->AccommodationId == (int)$accommodation->AccommodationId) {
						            $weeklyprice = getWeeklyPrice($rate);
						        }
						    }
						    if ($weeklyprice !== null) {
						        // Add the total booking amount to the lowest price
						        $priceConvert = str_replace('EUR', '&euro;', $accommodation->Currency);
						        $newPriceBeforeRound = $weeklyprice + $totalBookingAmount;
						        $newPrice = round($newPriceBeforeRound);
						        $rateData['RatePrice'] = $priceConvert . $newPrice;
						    }
						}
						if (isset($rateData) && is_array($rateData)) {
							$data['RatePrice'] = $rateData['RatePrice'];
						} else {
							$data['RatePrice'] = '';
						}
					}
				}
			}
		}
	}
	return $data;
}

function getOccupationalRulesFeeds($accommodationId, $OccupationalRuleId) {
	$row = getOccupationalRule($OccupationalRuleId);
	$data = array();
	if (!empty($row)) {
		$Seasons = json_decode($row->Seasons);
		$seasonORData = array();
		foreach ($Seasons as $seasonOR) {
			$startDate = date('d/m/Y', strtotime((string)$seasonOR->StartDate));
			$endDate = date('d/m/Y', strtotime((string)$seasonOR->EndDate));
            $checkInDays = isset($seasonOR->CheckInDays->WeekDay) ? $seasonOR->CheckInDays->WeekDay : array();
			$seasonORInfo = array(
				'StartDate' => $startDate,
				'EndDate' => $endDate,
				'MinimumNights' => !empty($seasonOR->MinimumNights) ? (int)$seasonOR->MinimumNights : 0,
				'MinimumNightsOnline' => !empty($seasonOR->MinimumNightsOnline) ? (int)$seasonOR->MinimumNightsOnline : 0,
				'MaximumNights' => !empty($seasonOR->MaximumNights) ? (int)$seasonOR->MaximumNights : 0,
                'CheckInDays' => $checkInDays
			);
			$seasonORData[] = $seasonORInfo; // Store each season's details in the array
		}
		$data['SeasonsOR'] = $seasonORData; // Assign the array to the 'SeasonsOR' key
	}
    return $data;
}

/*function processCharacteristics($characteristicsData, &$outputCharacteristics, &$outputTitles) {
	foreach ($characteristicsData as $keyCharacteristics => $valueCharacteristic) {
		$valueStr = strval($valueCharacteristic);
		$valueStr = trim($valueStr); // Remove leading and trailing spaces
		// Skip if the key is "TV" or "NumOfTelevisions"
		if ($keyCharacteristics == "NumOfTelevisions") {
			continue;
		}
		// If the value is 'true' or a positive number, and the key is not "TV" or "NumOfTelevisions", add it
		if (($valueStr === 'true' || (is_numeric($valueStr) && $valueStr > 0))  && $valueStr !== '' && $keyCharacteristics != "NumOfTelevisions" && !in_array($keyCharacteristics, ['SwimmingPool', 'TVSatellite', 'HandicappedFacilities', 'Kitchen'])) {
			// If the key is not all uppercase, convert it
			if (!ctype_upper($keyCharacteristics)) {
				$keyCharacteristics = lcfirst(preg_replace('/([A-Z])/', ' $1', $keyCharacteristics));
			}
			// Special case for "Language" values
			if (strpos($keyCharacteristics, 'Language') !== false) {
				$keyCharacteristics = preg_replace('/\bLanguage\s+(\w)\s+(\w)\b/', 'Language $1$2', trim($keyCharacteristics));
			}
			$convertedKC = $keyCharacteristics . ' (' . $valueCharacteristic . ')';
			$convertedKC = str_replace(' (true)', '', $convertedKC);
			$outputCharacteristics[] = (string) trim($convertedKC);
		}
		if ($valueStr !== '') {
			// Extract titles and their values for specific options
			if (in_array($keyCharacteristics, ['SwimmingPool', 'TVSatellite', 'HandicappedFacilities', 'Kitchen'])) {
				$outputTitles[] = $keyCharacteristics;
			}
		}
		// If the value is a sub-node, process its sub-values recursively
		if (is_object($valueCharacteristic)) {
			processCharacteristics($valueCharacteristic, $outputCharacteristics, $outputTitles);
		}
	}
}*/

function processCharacteristics($characteristicsData, &$outputCharacteristics, &$outputTitles) {
    foreach ($characteristicsData as $keyCharacteristics => $valueCharacteristic) {
        $valueStr = strval($valueCharacteristic);
        $valueStr = trim($valueStr); // Remove leading and trailing spaces
        if ($keyCharacteristics == "NumOfTelevisions") {
            continue;
        }
        if (($valueStr === 'true' || (is_numeric($valueStr) && $valueStr > 0)) && $valueStr !== '' && $keyCharacteristics != "NumOfTelevisions") {
            if (!ctype_upper($keyCharacteristics)) {
                $keyCharacteristics = lcfirst(preg_replace('/([A-Z])/', ' $1', $keyCharacteristics));
            }
            if (strpos($keyCharacteristics, 'Language') !== false) {
                $keyCharacteristics = preg_replace('/\bLanguage\s+(\w)\s+(\w)\b/', 'Language $1$2', trim($keyCharacteristics));
            }
            $convertedKC = $keyCharacteristics . ' (' . $valueCharacteristic . ')';
            $convertedKC = str_replace(' (true)', '', $convertedKC);
            $outputCharacteristics[$keyCharacteristics] = (string) trim($convertedKC);
        }
        // Extract titles and their values for specific options
        if (in_array($keyCharacteristics, ['SwimmingPool', 'TVSatellite', 'HandicappedFacilities', 'Kitchen'])) {
            $outputCharacteristics[$keyCharacteristics] = $valueStr;
        }
        if (is_object($valueCharacteristic)) {
            processCharacteristics($valueCharacteristic, $outputCharacteristics, $outputTitles);
        }
    }
}

function processGeneralCharacteristics($characteristicsDataGeneral, &$outputCharacteristicsGeneral) {
	foreach ($characteristicsDataGeneral as $keyCharacteristicsGeneral => $valueCharacteristicGeneral) {
		$valueStrGeneral = strval($valueCharacteristicGeneral);
		$valueStrGeneral = trim($valueStrGeneral); // Remove leading and trailing spaces
		// Skip if the key is excluded
		$excludedKeys = ['SwimmingPool', 'TV', 'NumOfTelevisions', 'TVSatellite', 'HandicappedFacilities', 'Kitchen'];
		if (in_array($keyCharacteristicsGeneral, $excludedKeys)) {
			continue;
		}
		// If the value is 'true', add it to output
		if ($valueStrGeneral === 'true') {
			// If the key is not all uppercase, convert it
			if (!ctype_upper($keyCharacteristicsGeneral)) {
				$keyCharacteristicsGeneral = lcfirst(preg_replace('/([A-Z])/', ' $1', $keyCharacteristicsGeneral));
			}
			$outputCharacteristicsGeneral[] = $keyCharacteristicsGeneral;
		}
		// If the value is a sub-node, process its sub-values recursively
		if (is_object($valueCharacteristicGeneral) && !in_array($keyCharacteristicsGeneral, $excludedKeys)) {
			processGeneralCharacteristics($valueCharacteristicGeneral, $outputCharacteristicsGeneral);
		}
	}
}

// Get Kitchen Features
function processKitchenCharacteristics($kitchenCharacteristicsData, &$outputKitchenCharacteristics, &$outputKitchenTitles) {
	$kitchenArray = json_decode(json_encode($kitchenCharacteristicsData), true);
	foreach ($kitchenArray as $kitchenKey => $kitchenValue) {
		if ($kitchenValue === 'true') {
			$outputKitchenCharacteristics[] = $kitchenKey;
			$outputKitchenTitles[] = $kitchenKey;
		}
	}
}

function avantio_api_shortcode($atts = [], $content = null) {
	$user_avantioapi_details = shortcode_atts( array(
	    'api_key' => '',
	    'email_name' => '',
	    'phone_number' => '',
	), $atts, 'avantio_api');
	ob_start();
	include_once(plugin_dir_path( __FILE__ ) . '/api-responses.php');
	$output = ob_get_clean();
	return $output;
}
add_shortcode('avantio_api', 'avantio_api_shortcode');

function avantio_search_filter_shortcode($atts = [], $content = null) {
	set_transient('avantio_search_filter_used', true, 60 * 10);
	$showfilter = isset($atts['showfilter']) ? $atts['showfilter'] : 'yes';
	$search_atts = shortcode_atts( array(
	    'showfilter' => $showfilter ,
	), $atts, 'avantio_search_filter');
	ob_start();
	include_once(plugin_dir_path( __FILE__ ) . '/search_filter.php');
	$output = ob_get_clean();
	return $output;
}
add_shortcode('avantio_search_filter', 'avantio_search_filter_shortcode');

function replace_viewport_meta_tag() {
    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            var viewportMeta = document.querySelector("meta[name=viewport]");
            if (viewportMeta) {
                viewportMeta.parentNode.removeChild(viewportMeta);
            }
            var newViewportMeta = document.createElement("meta");
            newViewportMeta.name = "viewport";
            newViewportMeta.content = "width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no";
            document.head.appendChild(newViewportMeta);
        });
    </script>';
}
add_action('wp_head', 'replace_viewport_meta_tag');

function avantio_saved_homes_shortcode($atts = [], $content = null) {
	ob_start();
	include_once(plugin_dir_path( __FILE__ ) . '/saved_homes.php');
	$output = ob_get_clean();
	return $output;
}
add_shortcode('avantio_saved_homes', 'avantio_saved_homes_shortcode');

function avantio_map_shortcode($atts = [], $content = null) {
	$areaDestination = isset($atts['areadestination']) ? $atts['areadestination'] : 'all';
	$province = isset($atts['province']) ? $atts['province'] : 'all';
	$region = isset($atts['region']) ? $atts['region'] : 'all';
	$label = isset($atts['label']) ? $atts['label'] : 'all';
	$labelexact = isset($atts['label_exact']) ? $atts['label_exact'] : 'all';
	$map_atts = shortcode_atts( array(
	    'areaDestination' => $areaDestination,
	    'province' => $province,
		'region' => $region,
		'label' => $label,
		'labelexact' => $labelexact,
	), $atts, 'avantio_map');
	ob_start();
	include_once(plugin_dir_path( __FILE__ ) . '/avantio_map.php');
	$output = ob_get_clean();
	return $output;
}
add_shortcode('avantio_map', 'avantio_map_shortcode');

function avantio_rentals_search_shortcode($atts = [], $content = null) {
	$label = isset($atts['label']) ? $atts['label'] : 'all';
	$labelexact = isset($atts['label_exact']) ? $atts['label_exact'] : 'all';
	$county = isset($atts['county']) ? $atts['county'] : 'all';
	$province = isset($atts['province']) ? $atts['province'] : 'all';
	$region = isset($atts['region']) ? $atts['region'] : 'all';
	$user_avantioapi_details = shortcode_atts( array(
	    'county' => $county,
	    'province' => $province,
		'region' => $region,
		'label' => $label,
		'labelexact' => $labelexact,
	), $atts, 'avantio_rentals_search');
	ob_start();
	include_once(plugin_dir_path( __FILE__ ) . '/search.php');
	$output = ob_get_clean();
	return $output;
}
add_shortcode('avantio_rentals_search', 'avantio_rentals_search_shortcode');

function avantio_longterm_rentals_search_shortcode($atts = [], $content = null) {
	$num_pg = isset($atts['num_pg']) ? $atts['num_pg'] : '';
	$uriSegments = explode("/", parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    if (isset($uriSegments[2]) && $uriSegments[2] != '') {
        $num_pg = $uriSegments[2];
    } else {
		$num_pg = 1;
    }
	$user_avantioapi_details = shortcode_atts( array(
	    'num_pg' => $num_pg ,
	), $atts, 'avantio_longterm_rentals_search');
	ob_start();
	include_once(plugin_dir_path( __FILE__ ) . '/search-longterm.php');
	$output = ob_get_clean();
	return $output;
}
add_shortcode('avantio_longterm_rentals_search', 'avantio_longterm_rentals_search_shortcode');

function avantio_rentals_shortcode($atts = [], $content = null) {
	$county_name = isset($atts['county_name']) ? $atts['county_name'] : '';
	$town_name = isset($atts['town_name']) ? $atts['town_name'] : '';
	$address_name = isset($atts['address_name']) ? $atts['address_name'] : '';
	$prop_id = isset($atts['prop_id']) ? $atts['prop_id'] : '';
    $acc_id = isset($atts['acc_id']) ? $atts['acc_id'] : '';
    $user_avantioapi_details = shortcode_atts( array(
    	'county_name' => $county_name,
    	'town_name' => $town_name,
    	'address_name' => $address_name,
	    'prop_id' => $prop_id,
	    'acc_id' => $acc_id,
	), $atts, 'avantio_rentals');
	ob_start();
	include_once(plugin_dir_path( __FILE__ ) . '/single.php');
	$output = ob_get_clean();
	return $output;
}
add_shortcode('avantio_rentals', 'avantio_rentals_shortcode');

function avantio_longterm_rentals_shortcode($atts = [], $content = null) {
	$county_name = isset($atts['county_name']) ? $atts['county_name'] : '';
	$town_name = isset($atts['town_name']) ? $atts['town_name'] : '';
	$address_name = isset($atts['address_name']) ? $atts['address_name'] : '';
	$prop_id = isset($atts['prop_id']) ? $atts['prop_id'] : '';
    $acc_id = isset($atts['acc_id']) ? $atts['acc_id'] : '';
    $user_avantioapi_details = shortcode_atts( array(
    	'county_name' => $county_name,
    	'town_name' => $town_name,
    	'address_name' => $address_name,
	    'prop_id' => $prop_id,
	    'acc_id' => $acc_id,
	), $atts, 'avantio_rentals');
	ob_start();
	include_once(plugin_dir_path( __FILE__ ) . '/single.php');
	$output = ob_get_clean();
	return $output;
}
add_shortcode('avantio_longterm_rentals', 'avantio_longterm_rentals_shortcode');

function avantio_single_test_shortcode($atts = [], $content = null) {
	$prop_id = isset($atts['prop_id']) ? $atts['prop_id'] : '';
    $acc_id = isset($atts['acc_id']) ? $atts['acc_id'] : '';
    $user_avantioapi_details = shortcode_atts( array(
	    'prop_id' => $prop_id,
	    'acc_id' => $acc_id,
	), $atts, 'avantio_single_test');
	ob_start();
	include_once(plugin_dir_path( __FILE__ ) . '/single-test.php');
	$output = ob_get_clean();
	return $output;
}
add_shortcode('avantio_single_test', 'avantio_single_test_shortcode');

function avantio_holiday_homes_shortcode($atts = [], $content = null) {
	$user_avantioapi_details = shortcode_atts( array(
	    'type' => 'active'
	), $atts, 'avantio_holiday_homes');
	ob_start();
	include_once(plugin_dir_path( __FILE__ ) . '/avantio-holiday-home-render.php');
	$output = ob_get_clean();
	return $output;
}
add_shortcode('avantio_holiday_homes', 'avantio_holiday_homes_shortcode');

function avantio_featured_homes_shortcode($atts = [], $content = null) {
	$county = isset($atts['county']) ? $atts['county'] : 'all';
	$propids = isset($atts['propids']) ? $atts['propids'] : '';
	$region = isset($atts['region']) ? $atts['region'] : 'all';
	$homes_atts = shortcode_atts( array(
	    'county' => $county,
		'region' => $region,
	    'propids' => $propids
	), $atts, 'avantio_featured_homes');
	ob_start();
	include_once(plugin_dir_path( __FILE__ ) . '/featured_homes.php');
	$output = ob_get_clean();
	return $output;
}
add_shortcode('avantio_featured_homes', 'avantio_featured_homes_shortcode');

function avantio_viewed_homes_shortcode($atts = [], $content = null) {
	$homes_atts = shortcode_atts( array(), $atts, 'avantio_viewed_homes');
	ob_start();
	include_once(plugin_dir_path( __FILE__ ) . '/viewed_homes.php');
	$output = ob_get_clean();
	return $output;
}
add_shortcode('avantio_viewed_homes', 'avantio_viewed_homes_shortcode');

function avantio_offers_shortcode($atts = [], $content = null) {
	$user_avantioapi_details = shortcode_atts( array(
	    'type' => 'active'
	), $atts, 'avantio_offers');
	ob_start();
	include_once(plugin_dir_path( __FILE__ ) . '/avantio-offers-render.php');
	$output = ob_get_clean();
	return $output;
}
add_shortcode('avantio_offers', 'avantio_offers_shortcode');

function avantio_offers_map_shortcode($atts = [], $content = null) {
	$offerid = isset($atts['offerid']) ? $atts['offerid'] : '';
	$map_atts = shortcode_atts( array(
	    'offerid' => $offerid,
	), $atts, 'avantio_offers_map');
    ob_start();
	include_once(plugin_dir_path( __FILE__ ) . '/avantio_offers_map.php');
	$output = ob_get_clean();
	return $output;
}
add_shortcode('avantio_offers_map', 'avantio_offers_map_shortcode');

$GLOBALS['custom_meta_data_exists'] = false;

function remove_existingMetaData_head_data($metaRemoval) {
	global $custom_meta_data_exists;
	if ($custom_meta_data_exists) {
	    //$metaRemoval = preg_replace('/<title>.*?<\/title>/', '', $metaRemoval);
	    $metaRemoval = preg_replace('/<meta name="viewport" content=".*?" \/>/', '', $metaRemoval);
	    $metaRemoval = preg_replace('/<meta name="title" content=".*?" \/>/', '', $metaRemoval);
	    $metaRemoval = preg_replace('/<meta name="description" content=".*?" \/>/', '', $metaRemoval);
	    $metaRemoval = preg_replace('/<meta name="keywords" content=".*?" \/>/', '', $metaRemoval);
	    // OG Tags for social media
	    $metaRemoval = preg_replace('/<meta property="og:locale" content=".*?" \/>/', '', $metaRemoval);
	    $metaRemoval = preg_replace('/<meta property="og:site_name" content=".*?" \/>/', '', $metaRemoval);
	    $metaRemoval = preg_replace('/<meta property="og:title" content=".*?" \/>/', '', $metaRemoval);
	    $metaRemoval = preg_replace('/<meta property="og:description" content=".*?" \/>/', '', $metaRemoval);
	    $metaRemoval = preg_replace('/<meta property="og:type" content=".*?" \/>/', '', $metaRemoval);
	    $metaRemoval = preg_replace('/<meta property="og:url" content=".*?" \/>/', '', $metaRemoval);
	    $metaRemoval = preg_replace('/<meta property="og:image" content=".*?" \/>/', '', $metaRemoval);
	    $metaRemoval = preg_replace('/<meta property="og:image:width" content=".*?" \/>/', '', $metaRemoval);
	    $metaRemoval = preg_replace('/<meta property="og:image:height" content=".*?" \/>/', '', $metaRemoval);
	    // OG Tags for X (twitter) social media
	    $metaRemoval = preg_replace('/<meta name="twitter:card" content=".*?" \/>/', '', $metaRemoval);
	    $metaRemoval = preg_replace('/<meta name="twitter:title" content=".*?" \/>/', '', $metaRemoval);
	    $metaRemoval = preg_replace('/<meta name="twitter:description" content=".*?" \/>/', '', $metaRemoval);
	    $metaRemoval = preg_replace('/<meta name="twitter:keywords" content=".*?" \/>/', '', $metaRemoval);
	    $metaRemoval = preg_replace('/<meta name="twitter:image:src" content=".*?" \/>/', '', $metaRemoval);
	    $metaRemoval = preg_replace('/<meta name="twitter:domain" content=".*?" \/>/', '', $metaRemoval);
	    // Remove yoast schema graph
	    $metaRemoval = preg_replace('/<script class="yoast-schema-graph" type="application\/ld\+json">.*?<\/script>/s', '', $metaRemoval);
	    // Remove the canonical
	    $metaRemoval = preg_replace('/<link rel="canonical" href=".*?" \/>/', '', $metaRemoval);
	    // Remove shortlink
	    $metaRemoval = preg_replace('/<link rel="shortlink" href=".*?" \/>/', '', $metaRemoval);
	}
    return $metaRemoval;
}

function start_output_buffer() {
    ob_start("remove_existingMetaData_head_data");
}
add_action('init', 'start_output_buffer');

function end_output_buffer() {
    ob_end_flush();
}
add_action('wp_head', 'end_output_buffer', 99);

function disable_yoast_schema_on_property_pages($data) {
    if (is_property_page()) {
        return false;
    }
    return $data;
}
function is_property_page() {
    return (strpos($_SERVER['REQUEST_URI'], '/property/') !== false);
}
add_filter('wpseo_json_ld_output', 'disable_yoast_schema_on_property_pages');

function insert_customMetaData_head_data() {
    global $wpdb, $custom_meta_data_exists;
    $last_slug = get_query_var('prop_id');
    $requestUri = $_SERVER['REQUEST_URI'];
    //$parts = explode('/', trim($requestUri, '/'));
    //$last_slug = count($parts) > 1 ? $parts[count($parts) - 2] : null;
    $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM wp_propertymetainfo WHERE PropertyID = %s", $last_slug));
    if ($results) {
    	$custom_meta_data_exists = true;
    	// Remove existing meta tags
    	remove_action('wp_head', 'remove_existingMetaData_head_data');
        foreach ($results as $row) {
        	$results_acc = $wpdb->get_results($wpdb->prepare("SELECT Images FROM wp_accommodations WHERE AccommodationId = %s", $last_slug), ARRAY_A);
        	if (!empty($results_acc) && !empty($results_acc[0]['Images'])) {
	            $arrimages = json_decode($results_acc[0]['Images'], true);
	            $AdaptedURI_active = false;
	            $AdaptedURI = '';
	            foreach ($arrimages as $image) {
	                if (isset($image['AdaptedURI']) && !empty($image['AdaptedURI'])) {
	                    $AdaptedURI = acco_image_url($image['AdaptedURI']);
	                    $AdaptedURI_active = true;
	                    break;
	                }
	            }
	        }
	        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">';
            echo '<title>' . esc_attr($row->MetaTitle) . '</title>';
            echo '<meta name="title" content="' . esc_attr($row->MetaTitle) . '">';
            echo '<meta name="description" content="' . esc_attr($row->MetaDescription) . '">';
            echo '<meta name="keywords" content="' . esc_attr($row->MetaKeywords) . '">';
            if (!empty($results_acc) && !empty($results_acc[0]['Images'])) {
			    $arrimages_preload_img = json_decode($results_acc[0]['Images'], true);
			    $preload_count = 0;
			    foreach ($arrimages_preload_img as $image_preload_img) {
			        if ($preload_count >= 6) {
			            break;
			        }
			        if (isset($image_preload_img['OriginalURI']) && !empty($image_preload_img['OriginalURI'])) {
			            $OriginalURI = acco_image_url($image_preload_img['OriginalURI']);
			            echo '<link rel="preload" href="' . esc_url($OriginalURI) . '" as="image">';
			            $preload_count++;
			        }
			    }
			}
            echo '<meta content="Holidays" name="classification">';
            // OG Tags for social media
            echo '<meta property="og:locale" content="en_IE">';
            echo '<meta property="og:site_name" content="Trident Holiday Homes">';
            echo '<meta property="og:title" content="' . esc_attr($row->MetaTitle) . '">';
            echo '<meta property="og:description" content="' . esc_attr($row->MetaDescription) . '">';
            echo '<meta property="og:type" content="website">';
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
			$domainName = $_SERVER['HTTP_HOST'];
			$fullURL = $protocol . $domainName . $requestUri;
            echo '<meta property="og:url" content="' . $fullURL . '">';
            if (isset($AdaptedURI_active) && $AdaptedURI_active) {
            	echo '<meta property="og:image" content="' . $AdaptedURI . '">';
            	echo '<meta property="og:image:width" content="650">';
            	echo '<meta property="og:image:height" content="450">';
            }
            // OG Tags for X (twitter) social media
            echo '<meta name="twitter:card" content="summary_large_image">';
            echo '<meta name="twitter:title" content="' . esc_attr($row->MetaTitle) . '">';
            echo '<meta name="twitter:description" content="' . esc_attr($row->MetaDescription) . '">';
            if (isset($AdaptedURI_active) && $AdaptedURI_active) {
            	echo '<meta name="twitter:image:src" content="' . $AdaptedURI . '">';
            }
            echo '<meta name="twitter:domain" content="' . $fullURL . '">';
            // Add the conanical
            echo '<link rel="canonical" href="' . $fullURL . '">';
        }
    }
}
add_action('wp_head', 'insert_customMetaData_head_data');

// Add your custom rewrite rules
function add_rules() {
    add_rewrite_rule('^rentals-test/([^/]*)/([^/]*)/?','index.php?pagename=rentals-test&prop_id=$matches[1]&acc_id=$matches[2]','top');
    add_rewrite_rule('^property/([^/]*)/([^/]*)/([^/]*)/([^/]*)/?','index.php?pagename=property&county_name=$matches[1]&town_name=$matches[2]&address_name=$matches[3]&prop_id=$matches[4]','top');
    add_rewrite_rule('^long-term-rental/([^/]*)/([^/]*)/([^/]*)/([^/]*)/?','index.php?pagename=long-term-rental&county_name=$matches[1]&town_name=$matches[2]&address_name=$matches[3]&prop_id=$matches[4]','top');
}
add_action('init', 'add_rules');

function add_query_vars($vars) {
    $vars[] = "county_name";
    $vars[] = "town_name";
    $vars[] = "address_name";
    $vars[] = "prop_id";
    $vars[] = "acc_id";
    $vars[] = "num_pg";
    return $vars;
}
add_filter('query_vars', 'add_query_vars');

// Schedule the 2-hour and monthly cron task using a custom interval
function setup_avantio_cron_intervals($schedules) {
    $schedules['2hourly'] = array(
        'interval' => 2 * 60 * 60, // 2 hours in seconds
        'display' => __('Every 2 Hours')
    );
    $schedules['daily'] = array(
        'interval' => 24 * 60 * 60, // 24 hours in seconds
        'display' => __('Every Day')
    );
	$schedules['monthly'] = array(
        'interval' => 30 * 24 * 60 * 60, // 30 days in seconds
        'display' => __('Every Month')
    );
    return $schedules;
}
add_filter('cron_schedules', 'setup_avantio_cron_intervals');

// Task 1: Run every hour
function avantio_1hour_cron_function() {
    $partnerCode = '25ce87c2384f552afd0144c97669c840';
    $destination_folder = plugin_dir_path(dirname(__FILE__)) . 'avantio-api-integration/feeds/';
    $unique_timestamp_id = time();
    // Files to download
    $zip_url_availabilities = 'https://feeds.avantio.com/availabilities/' . $partnerCode;
    $zip_url_rates = 'https://feeds.avantio.com/rates/' . $partnerCode;
    // availabilities
    $zip_file_availabilities = file_get_contents($zip_url_availabilities);
    file_put_contents($destination_folder . 'Availabilities_' . $unique_timestamp_id . '.zip', $zip_file_availabilities);
    $zip_availabilities = new ZipArchive;
    if ($zip_availabilities->open($destination_folder . 'Availabilities_' . $unique_timestamp_id . '.zip') === TRUE) {
        $filename_to_extract_availabilities = 'Availabilities.xml';
        if ($zip_availabilities->locateName($filename_to_extract_availabilities) !== false) {
            $zip_availabilities->extractTo($destination_folder, $filename_to_extract_availabilities);
        } else {
            $zip_availabilities->extractTo($destination_folder);
        }
        $zip_availabilities->close();
    }
    unlink($destination_folder . 'Availabilities_' . $unique_timestamp_id . '.zip');
	import_feed($filename_to_extract_availabilities);
    // rates
    $zip_file_rates = file_get_contents($zip_url_rates);
    file_put_contents($destination_folder . 'Rates_' . $unique_timestamp_id . '.zip', $zip_file_rates);
    $zip_rates = new ZipArchive;
    if ($zip_rates->open($destination_folder . 'Rates_' . $unique_timestamp_id . '.zip') === TRUE) {
        $filename_to_extract_rates = 'Rates.xml';
        if ($zip_rates->locateName($filename_to_extract_rates) !== false) {
            $zip_rates->extractTo($destination_folder, $filename_to_extract_rates);
        } else {
            $zip_rates->extractTo($destination_folder);
        }
        $zip_rates->close();
    }
    unlink($destination_folder . 'Rates_' . $unique_timestamp_id . '.zip');
	import_feed($filename_to_extract_rates);
}
add_action('avantio_1hour_cron_task', 'avantio_1hour_cron_function');

if (!wp_next_scheduled('avantio_1hour_cron_task')) {
    // Schedule every hour
    wp_schedule_event(time(), 'hourly', 'avantio_1hour_cron_task');
}

// Task 2: Run every 2 hours
function avantio_2hour_cron_function() {
    $partnerCode = '25ce87c2384f552afd0144c97669c840';
    $destination_folder = plugin_dir_path(dirname(__FILE__)) . 'avantio-api-integration/feeds/';
    $unique_timestamp_id = time();
    // Files to download
    $zip_url_accommodations = 'https://feeds.avantio.com/accommodations/' . $partnerCode;
    $zip_url_descriptions = 'https://feeds.avantio.com/descriptions/' . $partnerCode;
    $zip_url_occupationalrules = 'https://feeds.avantio.com/occupationalrules/' . $partnerCode;
    $zip_url_pricemodifiers = 'https://feeds.avantio.com/pricemodifiers/' . $partnerCode;
    // accommodations
    $zip_file_accommodations = file_get_contents($zip_url_accommodations);
    file_put_contents($destination_folder . 'Accommodations_' . $unique_timestamp_id . '.zip', $zip_file_accommodations);
    $zip_accommodations = new ZipArchive;
    if ($zip_accommodations->open($destination_folder . 'Accommodations_' . $unique_timestamp_id . '.zip') === TRUE) {
        $filename_to_extract_accommodations = 'Accommodations.xml';
        if ($zip_accommodations->locateName($filename_to_extract_accommodations) !== false) {
            $zip_accommodations->extractTo($destination_folder, $filename_to_extract_accommodations);
        } else {
            $zip_accommodations->extractTo($destination_folder);
        }
        $zip_accommodations->close();
    }
    unlink($destination_folder . 'Accommodations_' . $unique_timestamp_id . '.zip');
	import_feed($filename_to_extract_accommodations);
    // descriptions
    $zip_file_descriptions = file_get_contents($zip_url_descriptions);
    file_put_contents($destination_folder . 'Descriptions_' . $unique_timestamp_id . '.zip', $zip_file_descriptions);
    $zip_descriptions = new ZipArchive;
    if ($zip_descriptions->open($destination_folder . 'Descriptions_' . $unique_timestamp_id . '.zip') === TRUE) {
        $filename_to_extract_descriptions = 'Descriptions.xml';
        if ($zip_descriptions->locateName($filename_to_extract_descriptions) !== false) {
            $zip_descriptions->extractTo($destination_folder, $filename_to_extract_descriptions);
        } else {
            $zip_descriptions->extractTo($destination_folder);
        }
        $zip_descriptions->close();
    }
    unlink($destination_folder . 'Descriptions_' . $unique_timestamp_id . '.zip');
	import_feed($filename_to_extract_descriptions);
    // occupationalrules
    $zip_file_occupationalrules = file_get_contents($zip_url_occupationalrules);
    file_put_contents($destination_folder . 'OccupationalRules_' . $unique_timestamp_id . '.zip', $zip_file_occupationalrules);
    $zip_occupationalrules = new ZipArchive;
    if ($zip_occupationalrules->open($destination_folder . 'OccupationalRules_' . $unique_timestamp_id . '.zip') === TRUE) {
        $filename_to_extract_occupationalrules = 'OccupationalRules.xml';
        if ($zip_occupationalrules->locateName($filename_to_extract_occupationalrules) !== false) {
            $zip_occupationalrules->extractTo($destination_folder, $filename_to_extract_occupationalrules);
        } else {
            $zip_occupationalrules->extractTo($destination_folder);
        }
        $zip_occupationalrules->close();
    }
    unlink($destination_folder . 'OccupationalRules_' . $unique_timestamp_id . '.zip');
	import_feed($filename_to_extract_occupationalrules);
    // pricemodifiers
    $zip_file_pricemodifiers = file_get_contents($zip_url_pricemodifiers);
    file_put_contents($destination_folder . 'PriceModifiers_' . $unique_timestamp_id . '.zip', $zip_file_pricemodifiers);
    $zip_pricemodifiers = new ZipArchive;
    if ($zip_pricemodifiers->open($destination_folder . 'PriceModifiers_' . $unique_timestamp_id . '.zip') === TRUE) {
        $filename_to_extract_pricemodifiers = 'PriceModifiers.xml';
        if ($zip_pricemodifiers->locateName($filename_to_extract_pricemodifiers) !== false) {
            $zip_pricemodifiers->extractTo($destination_folder, $filename_to_extract_pricemodifiers);
        } else {
            $zip_pricemodifiers->extractTo($destination_folder);
        }
        $zip_pricemodifiers->close();
    }
    unlink($destination_folder . 'PriceModifiers_' . $unique_timestamp_id . '.zip');
	import_feed($filename_to_extract_pricemodifiers);
}
add_action('avantio_2hour_cron_task', 'avantio_2hour_cron_function');

if (!wp_next_scheduled('avantio_2hour_cron_task')) {
    // Schedule every 2 hours (120 minutes)
    wp_schedule_event(time(), '2hourly', 'avantio_2hour_cron_task');
}

// Task 2: Run every 24 hours
function avantio_daily_cron_function() {
    $partnerCode = '25ce87c2384f552afd0144c97669c840';
    $destination_folder = plugin_dir_path(dirname(__FILE__)) . 'avantio-api-integration/feeds/';
    $unique_timestamp_id = time();
    // Files to download
    $zip_url_services = 'https://feeds.avantio.com/services/' . $partnerCode;
    // services
    $zip_file_services = file_get_contents($zip_url_services);
    file_put_contents($destination_folder . 'Services_' . $unique_timestamp_id . '.zip', $zip_file_services);
    $zip_services = new ZipArchive;
    if ($zip_services->open($destination_folder . 'Services_' . $unique_timestamp_id . '.zip') === TRUE) {
        $filename_to_extract_services = 'Services.xml';
        if ($zip_services->locateName($filename_to_extract_services) !== false) {
            $zip_services->extractTo($destination_folder, $filename_to_extract_services);
        } else {
            $zip_services->extractTo($destination_folder);
        }
        $zip_services->close();
    }
    unlink($destination_folder . 'Services_' . $unique_timestamp_id . '.zip');
	import_feed($filename_to_extract_services);
}
add_action('avantio_daily_cron_task', 'avantio_daily_cron_function');

if (!wp_next_scheduled('avantio_daily_cron_task')) {
    // Schedule every 24 hours
    wp_schedule_event(time(), 'daily', 'avantio_daily_cron_task');
}

// Task 3: Run once a month at 2 AM
function avantio_monthly_cron_function() {
    $partnerCode = '25ce87c2384f552afd0144c97669c840';
    $destination_folder = plugin_dir_path(dirname(__FILE__)) . 'avantio-api-integration/feeds/';
    $unique_timestamp_id = time();
    // Files to download
    $zip_url_kinds = 'https://feeds.avantio.com/kinds/' . $partnerCode;
    $zip_url_geographicareas = 'https://feeds.avantio.com/geographicareas/' . $partnerCode;
    // kinds
    $zip_file_kinds = file_get_contents($zip_url_kinds);
    file_put_contents($destination_folder . 'Kinds_' . $unique_timestamp_id . '.zip', $zip_file_kinds);
    $zip_kinds = new ZipArchive;
    if ($zip_kinds->open($destination_folder . 'Kinds_' . $unique_timestamp_id . '.zip') === TRUE) {
        $filename_to_extract_kinds = 'Kinds.xml';
        if ($zip_kinds->locateName($filename_to_extract_kinds) !== false) {
            $zip_kinds->extractTo($destination_folder, $filename_to_extract_kinds);
        } else {
            $zip_kinds->extractTo($destination_folder);
        }   
        $zip_kinds->close();
    }
    unlink($destination_folder . 'Kinds_' . $unique_timestamp_id . '.zip');
	import_feed($filename_to_extract_kinds);
    // geographicareas
    $zip_file_geographicareas = file_get_contents($zip_url_geographicareas);
    file_put_contents($destination_folder . 'GeographicAreas_' . $unique_timestamp_id . '.zip', $zip_file_geographicareas);
    $zip_geographicareas = new ZipArchive;
    if ($zip_geographicareas->open($destination_folder . 'GeographicAreas_' . $unique_timestamp_id . '.zip') === TRUE) {
        $filename_to_extract_geographicareas = 'GeographicAreas.xml';
        if ($zip_geographicareas->locateName($filename_to_extract_geographicareas) !== false) {
            $zip_geographicareas->extractTo($destination_folder, $filename_to_extract_geographicareas);
        } else {
            $zip_geographicareas->extractTo($destination_folder);
        } 
        $zip_geographicareas->close();
    }
    unlink($destination_folder . 'GeographicAreas_' . $unique_timestamp_id . '.zip');
}
add_action('avantio_monthly_cron_task', 'avantio_monthly_cron_function');

if (!wp_next_scheduled('avantio_monthly_cron_task')) {
    // Schedule for the 1st of the month at 2 AM
    wp_schedule_event(strtotime('first day of this month 2:00'), 'monthly', 'avantio_monthly_cron_task');
}

// Function to flush rewrite rules on plugin activation
function avantio_plugin_activate() {
    // Add your custom rewrite rules
    add_rules();
    // Flush the rewrite rules
    flush_rewrite_rules();
}
// Register activation hook
register_activation_hook(__FILE__, 'avantio_plugin_activate');

// Check if Elementor is activated and add the templates
if (is_plugin_active('elementor/elementor.php')) {
    // Activation Hook: Add templates to Elementor's templates directory
    function avantio_theme_plugin_activation() {
    	$elementor_templates_dir = ELEMENTOR_PATH . 'assets/templates/';
	    $search_template_file = plugin_dir_path(__FILE__) . 'avantio-search-template.php';
	    $search_destination_template_file = $elementor_templates_dir . 'avantio-search-template.php';
	    $single_template_file = plugin_dir_path(__FILE__) . 'avantio-single-template.php';
	    $single_destination_template_file = $elementor_templates_dir . 'avantio-single-template.php';
	    // Check if the /templates/ directory exists, and create it if it doesn't.
	    if (!file_exists($elementor_templates_dir)) {
	        mkdir($elementor_templates_dir);
	    }
        // Use the WP Filesystem API for file copying
        require_once ABSPATH . 'wp-admin/includes/file.php';
        WP_Filesystem();
        // Copy the template files
        if (!file_exists($search_destination_template_file)) {
            copy($search_template_file, $search_destination_template_file);
        }
        if (!file_exists($single_destination_template_file)) {
            copy($single_template_file, $single_destination_template_file);
        }
    }
    register_activation_hook(__FILE__, 'avantio_theme_plugin_activation');
    // Deactivation Hook: Delete templates from Elementor's templates directory
    function avantio_theme_plugin_deactivation() {
        // Get the Elementor templates directory path
        $elementor_templates_dir = ELEMENTOR_PATH . 'assets/templates/';
        $search_destination_template_file = $elementor_templates_dir . 'avantio-search-template.php';
        $single_destination_template_file = $elementor_templates_dir . 'avantio-single-template.php';
        // Use the WP Filesystem API for file deletion
        require_once ABSPATH . 'wp-admin/includes/file.php';
        WP_Filesystem();
        // Delete the template files if they exist
        if (file_exists($search_destination_template_file)) {
            unlink($search_destination_template_file);
        }
        if (file_exists($single_destination_template_file)) {
            unlink($single_destination_template_file);
        }
    }
    register_deactivation_hook(__FILE__, 'avantio_theme_plugin_deactivation');
}

include_once(plugin_dir_path( __FILE__ ) . '/avantio-feeds-api.php');
?>