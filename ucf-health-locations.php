<?php
/*
Plugin Name: UCF Health locations taxonomy
Plugin URI: https://github.com/schrauger/ucf-health-locations
Description: Google map embed with a block layout and configuration.
Version: 3.0.0-alpha
Author: Stephen Schrauger
Author URI: https://www.schrauger.com/
License: GPLv2 or later
*/

namespace ucf_health_locations;

include_once plugin_dir_path( __FILE__ ) . 'acf-pro/admin.php';
include_once plugin_dir_path( __FILE__ ) . 'acf-pro/block.php';


const shortcode_slug = 'ucf_health_locationsmap'; // what people type into their page

const html_input_name_locations = 'ucf_health_locations';
const directions_base_url       = 'https://www.google.com/maps/dir//'; // the double slash at the end is important, in order to have directions TO this place instead of FROM it
const directions_apple_base_url = 'http://maps.apple.com/?q';
const script_register           = 'locations_google_map_js'; // arbitrary unique identifier
const style_register            = 'locations_google_map_css';
const google_maps_register      = 'google-maps';
const google_maps_key           = '//maps.googleapis.com/maps/api/js?key=AIzaSyB-Hs-bKrEM2KWp1gRYzbPM_qhw2yAysxY&sensor=true'; // js with our key.
// we use the medweb@ucf.edu (ie med.organic.songs@gmail.com) account for our api.
// this api key is restricted to our domains, so we shouldn't have a problem with someone reading the api key and trying to use it on their own project.
// https://console.cloud.google.com/google/maps-apis/apis/maps-backend.googleapis.com/credentials?authuser=1&folder=&organizationId=&project=api-project-116744227221


// Add the javascript to the locations page
add_action( 'init', __NAMESPACE__ . '\\register_location_js_css' );
add_action( 'enqueue_block_assets', __NAMESPACE__ . '\\register_location_js_css' );
//add_shortcode( shortcode_slug, __NAMESPACE__ . '\\handle_shortcode' );
//add_action( 'init', __NAMESPACE__ .  '\\initialize_shortcode' );

register_location_js_css();
/**
 * Adds the shortcode to wordpress' index of shortcodes
 */
function initialize_shortcode() {
	if ( ! ( shortcode_exists( shortcode_slug ) ) ) {
		add_shortcode( shortcode_slug, __NAMESPACE__ . '\\replacement' );
	}
}

function replacement_print() {
	echo replacement();
}

function replacement() {
	return handle_shortcode();
}

/**
 *
 * adds the js and css to WordPress so it can enqueue them for pages that use the block
 */
function register_location_js_css() {
	wp_register_script( google_maps_register, google_maps_key );

	wp_register_script(
		script_register,
		plugins_url( 'js/google-map.js', __FILE__ ),
		array( 'jquery' ),
		filemtime( plugin_dir_path( __FILE__ ) . '/js/google-map.js' ),
		true
	);
	wp_register_style(
		style_register,
		plugins_url( 'css/style.css', __FILE__ ),
		array(),
		filemtime( plugin_dir_path( __FILE__ ) . '/css/style.css' ),
		true
	);
}

/**
 * Outputs the location html in place of the shortcode.
 * Also sets a flag to include js and css.
 *
 * @param $attributes
 *
 * @return string
 */
function handle_shortcode( ) {

	enqueue_files();

	return get_location_content( );

}

function enqueue_files() {
	wp_enqueue_script( google_maps_register );
	wp_enqueue_script( script_register );
	wp_enqueue_style( style_register );
}


/**
 * Adds the map and location html to the current page
 * @return string HTML with location map object (which is empty until javascript generates the map on the fly),
 *                as well as the selector list with detailed location information
 */
function get_location_content() {


	/*
	* Visible list of locations.
	*/
	$selector_panel_tabs = '';
	$selector_panel_info = '';


	// Get all the pins for the map
	$pins = array();
	$i = 0;
	$show_first = true; // set to false to hide all details by default. true to show the first one.
	while ( have_rows( 'pin_locations' ) ) {
		the_row();

		$pin_info                               = array();
		$pin_info[ 'name' ]                     = get_sub_field( 'name' );
		$pin_info[ 'description' ]              = get_sub_field( 'description' );
		//$pin_info[ 'phone_number' ]             = get_sub_field( 'phone_numbers' ); // @TODO this is a repeater
		$pin_info[ 'hours_of_operation' ]       = get_sub_field( 'hours_of_operation' );
		//$pin_info[ 'coordinates' ]              = get_sub_field( 'coordinates' ); // @TODO this is a group
		$pin_info[ 'address' ]                  = get_sub_field( 'address' );
		$pin_info[ 'url' ]                      = get_sub_field( 'url' );
		$pin_info[ 'written_directs_pdf_file' ] = get_sub_field( 'written_directs_pdf_file' );
		//$pin_info[''] = get_sub_field('');

		while (have_rows('phone_number')){
			the_row();
			$type = get_sub_field('type');
			$number = get_sub_field('number');
			$pin_info['phone_number'][$type] = $number;
		}

		// coordinates are in a group, which also needs to be looped even though it isn't a repeater
		while (have_rows('coordinates')){
			the_row();
			$pin_info['latitude'] = get_sub_field('latitude');
			$pin_info['longitude'] = get_sub_field('longitude');

		}


		$pin_info[ 'slug' ] = 'ucfh-' . md5(json_encode($pin_info));
		// use md5 to create a unique id that only changes when the pin data changes - for caching and unique id in html
		// note: ids MUST start with a letter, so prefix the md5 to prevent erros

		$pins[$pin_info[ 'slug' ]] = $pin_info;

		// 4. Create an always-visible list entry (outside of the google map interface)

		if ($i === 0 && $show_first){
			$show_current = true;
		} else {
			$show_current = false;
		}

		$selector_panel_tabs .= selector_panel_list_tab( $pin_info, $show_current );
		$selector_panel_info .= selector_panel_list_info( $pin_info, $show_current );

		$i++;
	}

	$unique_id_all_data = 'ucfh-' . md5(json_encode($pins));
	// generate another unique id for the parent object. this way, a page with multiple blocks won't interfere with one another.
	// note: ids MUST start with a letter, so prefix the md5 to prevent erros

	if ( get_field('panel_visible')) {
		$selector_panel = "
			<div class='info selector-panel locations' >
				<ul class='nav nav-tabs' id='{$unique_id_all_data}-tabs' role='tablist' >
					{$selector_panel_tabs}
				</ul>
				<div class='tab-content' id='{$unique_id_all_data}-content'>
					{$selector_panel_info}
				</div>
			</div>
		";

	} else {
		$selector_panel = '';
	}

	// All location data is in the array. Output it.
	$json_object = '<input type="hidden" name="' . html_input_name_locations . '" data-locations=' . "'" . json_encode( $pins ) . "'" . ' />';

	if ( get_field('map_visible') ) {
		$map = "<section><div class='ucf-health-locationsmap'  ></div></section>";
	} else {
		$map = '';
	}

	return "<div class='locations-output' id='{$unique_id_all_data}' >{$map}{$json_object}{$selector_panel}</div>";
}

/**
 * Creates the list item for a specific location. This is shown in a <ul> on the locations page.
 *
 * @param $location_array
 * @param $is_selected boolean If true, marks this tab as active
 *
 * @return string
 */
function selector_panel_list_tab( $location_array, $is_selected = false ) {
	$location = json_decode( json_encode( $location_array ) );
	$is_selected_string = $is_selected ? 'true' : 'false'; // convert boolean to string for js
	$is_active_string = $is_selected ? 'active' : ''; // convert boolean to string for js
	$tab = "
		<li class='nav-item'>
			<a 
			class='nav-link {$is_active_string}' 
			id='tab-{$location->slug}-tab' 
			data-toggle='tab' 
			href='#tab-{$location->slug}-content' 
			role='tab' 
			aria-controls='tab-{$location->slug}-content' 
			aria-selected='{$is_selected_string}'
			data-location='{$location->slug}'
			>
				{$location->name}
			</a>
		</li>
	";
	//$tab .= var_export($location_array, true);

	return $tab;
	//return "<li class='locations {$location->slug}' data-location='{$location->slug}'><div class='location location-{$i}'></div><a href='#'>{$location->name}</a></li>";

}

/**
 * Creates the list item for a specific location. This is shown in a <ul> on the locations page.
 *
 * @param $location_array
 * @param $is_selected boolean If true, marks this tab as active
 *
 * @return string
 */
function selector_panel_list_info( $location_array, $is_selected = false) {
	$location = json_decode( json_encode( $location_array ) );

	$address = "";
	if ( $location->address ) {
		$address .= "			
			<strong>Address:</strong><br />
			<p>" . nl2br( $location->address ) . "</p>
			<a 
			href='" . get_directions( $location ) . "' 
			class='green map location' 
			target='_blank'
			>
				Google Maps
			</a>
			<a 
			href='" . get_directions_apple( $location ) . "' 
			class='green map nomarker location ' 
			target='_blank'
			>
				Apple iOS Maps
			</a>
			";
		if ( $location->written_directions_pdf_file ) {
			$address .= "
			<a 
			href='{$location->written_directions_pdf_file}' 
			class='green map nomarker location ' 
			target='_blank'
			>
				PDF Directions
			</a>
			";
		}
	}


	$phone = "";
	if ( $location->phone_number ) {
		$phone .= "
			<strong>Phone:</strong><br />
			<p>" . nl2br( stripslashes( $location->phone_number ) ) . "</p>
			";
	}
	if ( $location->fax_number ) {
		$phone .= "
			<strong>Fax:</strong><br />
			<p>" . nl2br( $location->fax_number ) . "</p>
			";
	}

	$hours = "";
	if ( $location->hours_of_operation ) {
		$hours .= "
			<strong>Hours:</strong></br>
			<p>" . nl2br( $location->hours_of_operation ) . "</p>
			<p class='notice' >If you have a medical emergency, call 911.</p >
			";
	}

	$extra_classes = "";
	if ($is_selected) {
		$extra_classes .= " show active ";
	}


	$tab_content = "";
	$tab_content .= "
		<div 
		class='tab-pane fade {$extra_classes}' 
		id='tab-{$location->slug}-content' 
		role='tabpanel' 
		aria-labelledby='tab-{$location->slug}-tab'
		>
			<div 
			id='tab-{$location->slug}-pininfo' 
			class='tab-{$location->slug}-pininfo info' 
			data-location='{$location->slug}'
			>
				<ul class=''>
					<div class='third'>
						<h2>" . nl2br( $location->name ) . "</h2>
						{$address}
					</div>
					<div class='third'>
						{$phone}
					</div>
					<div class='third'>
						{$hours}
					</div>
				</ul>
			</div>
		</div>
	";

	return $tab_content;
}

/**
 * Returns a url to google maps with the destination filled out.
 *
 * @param $location Object with address, latitude, and longitude members.
 *
 * @return string href to google maps
 */
function get_directions( $location ) {
	return directions_base_url . urlencode( str_replace( "\n", ', ', $location->address ) ) // change newlines into comma+space so google maps can process it properly
	       . '/@' . $location->latitude . ',' . $location->longitude . ',17z/';
	//  https://www.google.com/maps/dir//6850+Lake+Nona+Blvd,+Orlando,+FL+32827/@28.3676791,-81.2850738,17z/
}

function get_directions_apple( $location ) {
	return directions_apple_base_url
	       . '&ll=' . $location->latitude . ',' . $location->longitude
	       . '&sll=' . $location->latitude . ',' . $location->longitude
	       . '&daddr=' . $location->latitude . ',' . $location->longitude;
	//  https://www.google.com/maps/dir//6850+Lake+Nona+Blvd,+Orlando,+FL+32827/@28.3676791,-81.2850738,17z/
}


?>
