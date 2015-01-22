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


	function __construct() {
		// Custom taxonomy (category specifically for doctors)
		add_action( 'init', array( $this, 'create_locations_taxonomy' ), 20 );
		add_action( 'init', array( $this, 'create_specialities_taxonomy' ), 20 );
		add_action( 'init', array( $this, 'link_custom_taxonomies_with_custom_post_types' ), 20 );

		// Custom fields for a custom taxonomy.
		$this->locations_meta_fields();

		// Add the javascript to the locations page
		$this->add_javascript_to_locations();

		add_filter( 'the_content', array( $this, 'insert_location_content' ) );
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
			$my_meta->addNumber( $prefix . 'phone_number', array(
				'name' => __( 'Phone Number ', 'tax-meta' )
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


			//Finish Meta Box Declaration
			$my_meta->Finish();
		}
	}

	/*
	 * Simply adds the location javascript if the page is locations
	 */
	function add_javascript_to_locations() {
		if ( is_page( 'locations' ) ) {
			wp_register_script( 'locations_google_map', plugin_dir_path( __FILE__ ) . 'js/google-map.js' );
			wp_enqueue_script( 'locations_google_map' );
		}
	}

	/*
	 * Inserts the location details for each location on the 'location' page. This is used
	 * by javascript to build the google map points.
	 */
	function insert_location_content( $content ) {
		//echo $content;
		// Get all terms for this specific taxonomy and loop through to display them all in radio buttons.
		$terms          = get_terms( self::taxonomy_locations );
		$term_meta_data = array( 'phone_number', 'hours_of_operation', 'latitude', 'longitude', 'address', 'url' );
		$is_first_item  = true;

		$locations = [ ];


		foreach ( $terms as $term ) {

			// 1. Get the meta information about that term.
			$saved_data = get_tax_meta( $term->term_id, self::meta_taxonomy_prefix . 'phone_number' );
			$saved_data = get_tax_meta( $term->term_id, self::meta_taxonomy_prefix . 'hours_of_operation' );
			$saved_data = get_tax_meta( $term->term_id, self::meta_taxonomy_prefix . 'latitude' );
			$saved_data = get_tax_meta( $term->term_id, self::meta_taxonomy_prefix . 'longitude' );
			$saved_data = get_tax_meta( $term->term_id, self::meta_taxonomy_prefix . 'address' );
			$saved_data = get_tax_meta( $term->term_id, self::meta_taxonomy_prefix . 'url' );

			$this_location_info = [ ];

			// 2. Create a key->value map of our meta data (and built-in data).
			foreach ( $term_meta_data as $meta ) {
				$this_location_info[ $meta ] = get_tax_meta( $term->term_id, $meta ); // set key->value
			}
			$this_location_info[ 'name' ]        = $term->name; // human readable title
			$this_location_info[ 'description' ] = $term->description; // description

			// 3. Add this map to the array of all locations, with the key being the location slug.
			$locations[ $term->slug ] = $this_location_info;

		}
		// All location data is in the array. Output it.

		$content .= '<input type="hidden" name="' . self::html_input_name_locations . '" data-locations=' . "'" . json_encode( $locations ) . "'" . ' />';

		return $content;


	}

}

new ucf_health_locations();

?>