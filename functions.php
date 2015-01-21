<?php
/*
Plugin Name: UCF Health locations taxonomy
Plugin URI: https://github.com/schrauger/ucf-health-locations
Description: A custom taxonomy with custom fields for latitude, longitude, phone, etc.
Version: 0.1
Author: Stephen Schrauger
Author URI: https://www.schrauger.com/
License: GPLv2 or later
*/
require_once( get_plugin_directory() . "/Tax-meta-class/Tax-meta-class/Tax-meta-class.php" ); // lets the 'location' taxonomy have custom fields for lat/long etc

class ucf_health_locations {
	const taxonomy_locations    = 'locations';
	const taxonomy_specialities = 'specialities';

	function __construct() {
		// Custom taxonomy (category specifically for doctors)
		add_action( 'init', array( $this, 'create_locations_taxonomy' ) );
		add_action( 'init', array( $this, 'create_specialities_taxonomy' ) );

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

	function locations_meta_fields() {
		if ( is_admin() ) {
			/*
			* prefix of meta keys, optional
			*/
			$prefix = self::taxonomy_locations . '_';

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
			 * Phone
			 * Fax
			 * Hours (repeater block with day(s)-of-week, opening hours, and closing hours)
			 * Latitude - these are the important fields for the map
			 * Longitude - these are the important fields for the map
			 * Url - link to the location specific home page
			 */
			//text field
			$my_meta->addText( $prefix . 'title', array(
				'name' => __( 'Title ', 'tax-meta' ), /* note that the second argument MUST be a string literal.
                                                         * it CANNOT be a constant or variable, because it is _parsed_
                                                         * by an automation tool for translation. the parser simply looks
                                                         * for the __() function; it does not interpret php code.
                                                        */
				'desc' => 'A human-readable name for the location'
			) );
			//textarea field
			$my_meta->addTextarea( $prefix . 'description', array(
				'name' => __( 'Description/Notes ', 'tax-meta' ),
				'desc' => 'Description or notes about the location'
			) );
			$my_meta->addText( $prefix . 'description', array(
				'name' => __( 'Description/Notes ', 'tax-meta' ),
				'desc' => 'Description or notes about the location'
			) );

			$my_meta->addText( $prefix . 'description', array(
				'name' => __( 'Description/Notes ', 'tax-meta' ),
				'desc' => 'Description or notes about the location'
			) );

			$my_meta->addText( $prefix . 'description', array(
				'name' => __( 'Description/Notes ', 'tax-meta' ),
				'desc' => 'Description or notes about the location'
			) );

			$my_meta->addText( $prefix . 'description', array(
				'name' => __( 'Description/Notes ', 'tax-meta' ),
				'desc' => 'Description or notes about the location'
			) );

			$my_meta->addText( $prefix . 'description', array(
				'name' => __( 'Description/Notes ', 'tax-meta' ),
				'desc' => 'Description or notes about the location'
			) );

			$my_meta->addText( $prefix . 'description', array(
				'name' => __( 'Description/Notes ', 'tax-meta' ),
				'desc' => 'Description or notes about the location'
			) );
			//checkbox field
			$my_meta->addCheckbox( $prefix . 'checkbox_field_id', array( 'name' => __( 'My Checkbox ', 'tax-meta' ) ) );
			//select field
			$my_meta->addSelect( $prefix . 'select_field_id', array(
				'selectkey1' => 'Select Value1',
				'selectkey2' => 'Select Value2'
			), array( 'name' => __( 'My select ', 'tax-meta' ), 'std' => array( 'selectkey2' ) ) );
			//radio field
			$my_meta->addRadio( $prefix . 'radio_field_id', array(
				'radiokey1' => 'Radio Value1',
				'radiokey2' => 'Radio Value2'
			), array( 'name' => __( 'My Radio Filed', 'tax-meta' ), 'std' => array( 'radionkey2' ) ) );
			//date field
			$my_meta->addDate( $prefix . 'date_field_id', array( 'name' => __( 'My Date ', 'tax-meta' ) ) );
			//Time field
			$my_meta->addTime( $prefix . 'time_field_id', array( 'name' => __( 'My Time ', 'tax-meta' ) ) );
			//Color field
			$my_meta->addColor( $prefix . 'color_field_id', array( 'name' => __( 'My Color ', 'tax-meta' ) ) );
			//Image field
			$my_meta->addImage( $prefix . 'image_field_id', array( 'name' => __( 'My Image ', 'tax-meta' ) ) );
			//file upload field
			$my_meta->addFile( $prefix . 'file_field_id', array( 'name' => __( 'My File ', 'tax-meta' ) ) );
			//wysiwyg field
			$my_meta->addWysiwyg( $prefix . 'wysiwyg_field_id', array( 'name' => __( 'My wysiwyg Editor ', 'tax-meta' ) ) );
			//taxonomy field
			$my_meta->addTaxonomy( $prefix . 'taxonomy_field_id', array( 'taxonomy' => 'category' ), array( 'name' => __( 'My Taxonomy ', 'tax-meta' ) ) );
			//posts field
			$my_meta->addPosts( $prefix . 'posts_field_id', array( 'args' => array( 'post_type' => 'page' ) ), array( 'name' => __( 'My Posts ', 'tax-meta' ) ) );
			/*
			* To Create a reapeater Block first create an array of fields
			* use the same functions as above but add true as a last param
			*/
			$repeater_fields[ ] = $my_meta->addText( $prefix . 're_text_field_id', array( 'name' => __( 'My Text ', 'tax-meta' ) ), true );
			$repeater_fields[ ] = $my_meta->addTextarea( $prefix . 're_textarea_field_id', array( 'name' => __( 'My Textarea ', 'tax-meta' ) ), true );
			$repeater_fields[ ] = $my_meta->addCheckbox( $prefix . 're_checkbox_field_id', array( 'name' => __( 'My Checkbox ', 'tax-meta' ) ), true );
			$repeater_fields[ ] = $my_meta->addImage( $prefix . 'image_field_id', array( 'name' => __( 'My Image ', 'tax-meta' ) ), true );
			/*
			* Then just add the fields to the repeater block
			*/
			//repeater block
			$my_meta->addRepeaterBlock( $prefix . 're_', array(
				'inline' => true,
				'name'   => __( 'This is a Repeater Block', 'tax-meta' ),
				'fields' => $repeater_fields
			) );
			/*
			* Don't Forget to Close up the meta box decleration
			*/
			//Finish Meta Box Decleration
			$my_meta->Finish();
		}
	}


}

new ucf_health_locations();

?>
