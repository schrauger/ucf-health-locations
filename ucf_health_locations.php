<?php

/**
 * Created by PhpStorm.
 * User: stephen
 * Date: 1/21/15
 * Time: 4:36 PM
 *
 * This plugin was created in order to decouple the theme from the site-specific functions. However, the
 * theme won't run quite properly if this plugin is disabled, since there are still some tightly coupled
 * elements on the locations page.
 */
class ucf_health_locations {
	const taxonomy_locations        = 'locations';
	const taxonomy_specialities     = 'specialities';
	const html_input_name_locations = 'ucf_health_locations';
	const meta_taxonomy_prefix      = 'locations_';
	const directions_base_url       = 'https://www.google.com/maps/dir//'; // the double slash at the end is important, in order to have directions TO this place instead of FROM it
	const directions_apple_base_url = 'http://maps.apple.com/?q'; // the double slash at the end is important, in order to have directions TO this place instead of FROM it
	const shortcode                 = 'locationsmap'; // what people type into their page
	const script_register           = 'locations_google_map_js'; // arbitrary unique identifier
	const style_register            = 'locations_google_map_css';
	const google_maps_register      = 'google-maps';
	const google_maps_key           = '//maps.googleapis.com/maps/api/js?key=AIzaSyB-Hs-bKrEM2KWp1gRYzbPM_qhw2yAysxY&sensor=true'; // js with our key

	static $add_js_css; // if shortcode is found, this is set, which causes js/css to load

	function __construct() {
		// Custom taxonomy (category specifically for doctors)
		add_action( 'init', array( $this, 'create_locations_taxonomy' ), 20 );
		add_action( 'init', array( $this, 'create_specialities_taxonomy' ), 20 );
		add_action( 'init', array( $this, 'link_custom_taxonomies_with_custom_post_types' ), 20 );

		// Custom fields for a custom taxonomy.
		$this->locations_meta_fields();

		// Add the javascript to the locations page
		add_action( 'init', array( $this, 'register_location_js_css' ) );
		add_action( 'wp_footer', array($this, 'print_location_js_css'));

		add_shortcode(ucf_health_locations::shortcode, array($this, 'handle_shortcode'));

	}


	/**
	 * Outputs the location html in place of the shortcode.
	 * Also sets a flag to include js and css.
	 * @param $attributes
	 *
	 * @return string
	 */
	function handle_shortcode($attributes) {
		if (!self::$add_js_css) {
			// only add the location once on the page.

			self::$add_js_css = true;
			return $this->get_location_content();
		}
		return '';
	}


	function create_locations_taxonomy() {
		register_taxonomy(
			self::taxonomy_locations, // name/slug of taxonomy
			null,
			array(
				'labels'       => array(
					'name'          => __( 'Locations' ),
					'singular_name' => __( 'Location' )
				),
				'hierarchical' => true
			)
		);
	}

	function create_specialities_taxonomy() {
		register_taxonomy(
			self::taxonomy_specialities, // name/slug of taxonomy
			null, // don't set custom taxonomies for custom post types; link them later with register_taxonomy_for_object_type()
			array(
				'labels'       => array(
					'name'          => __( 'Specialities' ),
					'singular_name' => __( 'Speciality' )
				),
				'hierarchical' => true
				// gives us the 'most used' tab and the ability to structure (might not need it, though)
			)
		);
	}

	/**
	 * Adds our two taxonomies to the custom post type 'doctors'.
	 * If 'doctors' does not exist, it doesn't try to add them.
	 * Note; this should be called after creating the two taxonomies.
	 */
	function link_custom_taxonomies_with_custom_post_types() {
		if ( post_type_exists( 'doctors' ) ) {
			// link our custom taxonomies and custom post types
			// Better safe than sorry when registering custom taxonomies for custom post types:
			// http://codex.wordpress.org/Function_Reference/register_taxonomy#Usage
			register_taxonomy_for_object_type( self::taxonomy_locations, 'doctors' );
			register_taxonomy_for_object_type( self::taxonomy_specialities, 'doctors' );
		}
	}

	/**
	 * Creates the custom fields for the 'locations' taxonomy.
	 * This lets the user put in information about the location
	 * that will show up on the custom google map.
	 */
	function locations_meta_fields() {
		if ( is_admin() ) {
			/*
			* prefix of meta keys, optional
			*/
			$prefix = self::meta_taxonomy_prefix;

			/*
			* configure your meta box
			*/
			$config = array(
				'id'             => 'locations_meta_box',
				// meta box id, unique per meta box
				'title'          => 'Locations Meta Box',
				// meta box title
				'pages'          => array( self::taxonomy_locations ),
				// taxonomy name, accept categories, post_tag and custom taxonomies
				'context'        => 'normal',
				// where the meta box appear: normal (default), advanced, side; optional
				'fields'         => array(),
				// list of meta fields (can be added by field arrays)
				'local_images'   => false,
				// Use local or hosted images (meta box images for add/remove)
				'use_with_theme' => false
				//change path if used with theme set to true, false for a plugin or anything else for a custom path(default false).
			);
			/*
			* Initiate your meta box
			*/
			$my_meta = new Tax_Meta_Class( $config );
			/*
			* Add fields to your meta box
			*/
			/*
			 * slug (not added here; just the name of the taxonomy item)
			 * Human Readable Title
			 * Description/more details
			 * Phone numbers (repeater block with description and number)
			 * Hours (repeater block with day(s)-of-week, opening hours, and closing hours)
			 * Latitude - these are the important fields for the map
			 * Longitude - these are the important fields for the map
			 * Url - link to the location specific home page
			 */

			/*
			 * Human Readable Title
			 */
			//$my_meta->addText( $prefix . 'title', array(
			/*'name' => __( 'Title ', 'tax-meta' ),*/ /* note that the second argument MUST be a string literal.
                                                         * it CANNOT be a constant or variable, because it is _parsed_
                                                         * by an automation tool for translation. the parser simply looks
                                                         * for the __() function; it does not interpret php code.
                                                        */
			//'desc' => 'A human-readable name for the location'
			//) );

			/*
			 * Description/more details
			 */
			/*$my_meta->addTextarea( $prefix . 'description', array(
				'name' => __( 'Description/Notes ', 'tax-meta' ),
				'desc' => 'Description or notes about the location'
			) );*/

			/*
			 * Phone numbers (repeater block with description and number)
			 * Edit: Not doing repeater blocks. The 3rd-party code is a little buggy and needs to be fixed by me before it can be used.
			 */
			/*$repeater_fields_phone[ ] = $my_meta->addText( $prefix . 'phone_description', array( 'name' => __( 'Phone Type (Phone, Fax, etc) ', 'tax-meta' ) ), true );
			$repeater_fields_phone[ ] = $my_meta->addNumber( $prefix . 'phone_number', array( 'name' => __( 'Phone Number ', 'tax-meta' ) ), true );
			$my_meta->addRepeaterBlock( $prefix . 'phone_', array(
				'inline' => true,
				'name'   => __( 'Phone Numbers', 'tax-meta' ),
				'fields' => $repeater_fields_phone
			) );*/
			$my_meta->addTextarea( $prefix . 'phone_number', array(
				'name' => __( 'Phone Number(s) ', 'tax-meta' )
			) );
			$my_meta->addTextarea( $prefix . 'fax_number', array(
				'name' => __( 'Fax Number(s) ', 'tax-meta' )
			) );

			/*
			 * Hours (repeater block with day(s)-of-week, opening hours, and closing hours)
			 * Edit: Not doing repeater blocks. The 3rd-party code is a little buggy and needs to be fixed by me before it can be used.
			 */
			/*$repeater_fields_hours[ ] = $my_meta->addText( $prefix . 'hours_day_of_week', array(
				'name' => __( 'Day(s) of week', 'tax-meta' ),
				'desc' => 'Human-readable day of week. Ex: Monday, Tuesday-Thursday, Weekdays, Weekends'
			), true );
			$repeater_fields_hours[ ] = $my_meta->addTime( $prefix . 'hours_opening', array( 'name' => __( 'Open ', 'tax-meta' ) ), true );
			$repeater_fields_hours[ ] = $my_meta->addTime( $prefix . 'hours_closing', array( 'name' => __( 'Close ', 'tax-meta' ) ), true );
			$my_meta->addRepeaterBlock( $prefix . 'hours_', array(
				'inline' => true,
				'name'   => __( 'Hours of Operation', 'tax-meta' ),
				'fields' => $repeater_fields_hours
			) );*/
			$my_meta->addTextarea( $prefix . 'hours_of_operation', array(
				'name' => __( 'Hours of Operation ', 'tax-meta' ),
				'desc' => 'Human-readable description of open hours. Ex: Monday 8:30am-7:00pm, Tuesday-Thursday 9:00am-7:00pm'
			) );

			/*
			 * Latitude - these are the important fields for the map
			 */
			$my_meta->addNumber( $prefix . 'latitude', array(
				'name' => __( 'Latitude ', 'tax-meta' )
			) );

			/*
			 * Longitude - these are the important fields for the map
			 */
			$my_meta->addNumber( $prefix . 'longitude', array(
				'name' => __( 'Longitude ', 'tax-meta' )
			) );

			/*
			 * Address - street/postal address
			 */
			$my_meta->addTextarea( $prefix . 'address', array(
				'name' => __( 'Address ', 'tax-meta' ),
				'desc' => 'Street address, city, state, zip'
			) );

			/*
 			 * Url - link to the location specific home page
			 */
			$my_meta->addText( $prefix . 'url', array(
				'name' => __( 'Url ', 'tax-meta' ),
				'desc' => "Link to the location's home page"
			) );

			/*
 			 * written_directions_pdf - link to a pdf with text driving instructions
			 */
			$my_meta->addText( $prefix . 'written_directions_pdf', array(
				'name' => __( 'Written Directions PDF File ', 'tax-meta' ),
				'desc' => "Optional - link to a PDF file with written driving instructions"
			) );

			//Finish Meta Box Declaration
			$my_meta->Finish();
		}
	}

	/*
	 * Simply adds the location javascript if the page is locations
	 */
	function add_javascript_to_locations() {
		// 'locations' is the slug of the page we want to alter.
		// since this function is called once already inside The Loop, is_page doesn't work.
		if ( get_query_var( 'name' ) == 'locations' || get_query_var( 'name' ) == 'meet-your-experts' ) {
			$this->register_location_js_css();
		}
	}

	/**
	 *
	 * adds the js and css to the current page
	 */
	function register_location_js_css(){
		wp_register_script( self::google_maps_register, self::google_maps_key);
		wp_register_script( self::script_register, plugins_url( 'js/google-map.js', __FILE__ ), array( 'jquery' ) );
		//wp_enqueue_script( 'locations_google_map' );
		wp_register_style( self::style_register, plugins_url( 'css/style.css', __FILE__ ) );
		//wp_enqueue_style( 'location_google_map_css' );
	}

	function print_location_js_css(){
		if ( ! self::$add_js_css) {
			return;
		}
		wp_print_scripts(self::google_maps_register);
		wp_print_scripts(self::script_register);
		wp_print_styles(self::style_register);
	}


	/**
	 * Adds the map and location html to the current page
	 * @return string HTML with location map object (which is empty until javascript generates the map on the fly),
	 *                as well as the selector list with detailed location information
	 */
	function get_location_content(){
		// Get all terms for this specific taxonomy and loop through to display them all in radio buttons.
		$terms = get_terms( self::taxonomy_locations, array(
			'hide_empty' => false
			// explicitly grab all locations to show on the map, even if no doctors are assigned there yet
		) );

		$term_meta_data = array(
			'phone_number',
			'fax_number',
			'hours_of_operation',
			'latitude',
			'longitude',
			'address',
			'url',
			'written_directions_pdf'
		);
		$is_first_item  = true;

		$locations = array();

		/*
		 * Visible list of locations.
		 */
		$selector_panel      = '';
		$selector_panel_list = '';
		$selector_panel_info = '';

		for ( $i = 0; $length = sizeof( $terms ), $i < $length; $i ++ ) {
			$location = $terms[ $i ];

			/*
			 * Invisible variable with location meta data in a JSON parsable object.
			 */

			// 1. Get the meta information about that term.

			$this_location_info = array();

			// 2. Create a key->value map of our meta data (and built-in data).
			foreach ( $term_meta_data as $meta ) {
				$this_location_info[ $meta ] = get_tax_meta( $location->term_id, self::meta_taxonomy_prefix . $meta ); // set key->value
			}
			$this_location_info[ 'slug' ]                 = $location->slug;
			$this_location_info[ 'name' ]                 = $location->name; // human readable title
			$this_location_info[ 'description' ]          = $location->description; // description
			$this_location_info[ 'directions_url' ]       = $this->get_directions( json_decode( json_encode( $this_location_info ) ) ); // convert array to object for get_directions
			$this_location_info[ 'directions_apple_url' ] = $this->get_directions_apple( json_decode( json_encode( $this_location_info ) ) ); // convert array to object for get_directions

			// 3. Add this map to the array of all locations, with the key being the location slug.
			$locations[ $location->slug ] = $this_location_info;

			// 4. Create an always-visible list entry (outside of the google map interface)
			$selector_panel_list .= $this->selector_panel_list_item( $this_location_info, $i + 1 );
			$selector_panel_info .= $this->selector_panel_list_info( $this_location_info, $i + 1 );

		}

		$selector_panel .= '<h3 class="d">Select a location to learn more:</h3 ><h3 class="m">Select a map point above to learn more:</h3 >';
		$selector_panel .= '<div id="info" class="selector-panel locations" >';
		$selector_panel .= '	<div class="left"><ul>';
		$selector_panel .= $selector_panel_list;
		$selector_panel .= '	</ul></div>';
		$selector_panel .= '	<div class="right">';
		$selector_panel .= $selector_panel_info;
		$selector_panel .= '	</div>';
		$selector_panel .= '</div>';

		// All location data is in the array. Output it.
		$json_object = '<input type="hidden" name="' . self::html_input_name_locations . '" data-locations=' . "'" . json_encode( $locations ) . "'" . ' />';

		$map = '<section><div id="map" ></div></section>';

		return "<div class='locations-output'>" . $map . $json_object . $selector_panel . "</div>";
	}

	/*
	 * Inserts the location details for each location on the 'location' page. This is used
	 * by javascript to build the google map points.
	 */
	function insert_location_content( $content ) {
		// 'locations' is the slug of the page we want to alter.
		// since this function is called once already inside The Loop, is_page doesn't work.
		if ( get_query_var( 'name' ) == 'locations' || get_query_var( 'name' ) == 'meet-your-experts' ) {

			$content = $content . $this->get_location_content();
		}

		return $content;
	}

	/**
	 * Creates the list item for a specific location. This is shown in a <ul> on the locations page.
	 *
	 * @param $location_array
	 * @param $i List item number in array
	 *
	 * @return string
	 */
	function selector_panel_list_item( $location_array, $i ) {
		$location = json_decode( json_encode( $location_array ) );

		return "<li class='locations $location->slug' data-location='$location->slug'><div class='location location-$i'></div><a href='#'>$location->name</a></li>";

	}

	/**
	 * Creates the list item for a specific location. This is shown in a <ul> on the locations page.
	 *
	 * @param $location_array
	 * @param $i List item number in array
	 *
	 * @return string
	 */
	function selector_panel_list_info( $location_array, $i ) {
		//print_r($location_array);
		$location = json_decode( json_encode( $location_array ) );
		$return   = "";
		$return .= "<div class='$location->slug-info info' data-location='$location->slug'>";
		$return .= "	<ul class=''>";
		$return .= "		<div class='third'>";
		$return .= "			<h2>" . nl2br( $location->name ) . "</h2>";
		$return .= "			<strong>Address:</strong><br />";
		$return .= "			<p>" . nl2br( $location->address ) . "</p>";
		$return .= " 			<p><strong>Directions:</strong></p>";
		$return .= "			<a href='" . $this->get_directions( $location ) . "' class='green map location' target='_blank'>Google Maps</a>";
		$return .= "			<a href='" . $this->get_directions_apple( $location ) . "' class='green map nomarker location ' target='_blank'>Apple iOS Maps</a>";
		if ( $location->written_directions_pdf ) {
			$return .= "			<a href='" . $location->written_directions_pdf . "' class='green map nomarker location ' target='_blank'>PDF Directions</a>";
		}
		$return .= "		</div>";
		$return .= "		<div class='third'>";
		$return .= "			<strong>Phone:</strong><br />";
		$return .= "			<p>" . nl2br( $location->phone_number ) . "</p>";
		$return .= "			<strong>Fax:</strong><br />";
		$return .= "			<p>" . nl2br( $location->fax_number ) . "</p>";
		$return .= "		</div>";
		$return .= "		<div class='third'>";
		$return .= "			<strong>Hours:</strong></br>";
		$return .= "			<p>" . nl2br( $location->hours_of_operation ) . "</p>";
		$return .= "			<p class='notice' >If you have a medical emergency, call 911.</p >";
		$return .= "		</div>";
		$return .= "    </ul>";
		$return .= "</div>";

		return $return;
	}

	/**
	 * Returns a url to google maps with the destination filled out.
	 *
	 * @param $location Object with address, latitude, and longitude members.
	 *
	 * @return string href to google maps
	 */
	function get_directions( $location ) {
		return self::directions_base_url . urlencode( str_replace( "\n", ', ', $location->address ) ) // change newlines into comma+space so google maps can process it properly
		       . '/@' . $location->latitude . ',' . $location->longitude . ',17z/';
		//  https://www.google.com/maps/dir//6850+Lake+Nona+Blvd,+Orlando,+FL+32827/@28.3676791,-81.2850738,17z/
	}

	function get_directions_apple( $location ) {
		return self::directions_apple_base_url
		       . '&ll=' . $location->latitude . ',' . $location->longitude
		       . '&sll=' . $location->latitude . ',' . $location->longitude
		       . '&daddr=' . $location->latitude . ',' . $location->longitude;
		//  https://www.google.com/maps/dir//6850+Lake+Nona+Blvd,+Orlando,+FL+32827/@28.3676791,-81.2850738,17z/
	}

	function get_written_directions( $location ) {

	}

}

new ucf_health_locations();

?>