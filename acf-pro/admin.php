<?php
/**
 * Created by IntelliJ IDEA.
 * User: stephen
 * Date: 2021-06-22
 * Time: 4:30 PM
 */

/**
 * Class ucf_health_locations_acf_pro_admin_fields
 * Creates an admin page to let the site admin define site-wide options for embedded google maps. Namely, the api js
 * key.
 */

namespace ucf_health_locations\acf_pro\admin;

const acf_option_settings_page = 'options-general.php';

add_action( 'acf/init', __NAMESPACE__ . '\\add_admin_settings_page', 10 );
add_action( 'acf/init', __NAMESPACE__ . '\\admin_options_fields', 11 );


/**
 * Tells WordPress to generate an empty options subpage within the General options page.
 */
function add_admin_settings_page() {
	if ( function_exists( 'acf_add_options_sub_page' ) ) {
		acf_add_options_sub_page(
			array(
				'page_title'  => 'UCF Health Locations Settings',
				'menu_title'  => 'UCF Health Locations Settings',
				'menu_slug'   => 'ucf-health-locations-general-settings',
				'parent_slug' => acf_option_settings_page,
				'capability'  => 'edit_posts',
				'redirect'    => false
			)
		);
	}
}

/**
 * Creates fields for an admin options page.
 * Lets the site admins define site-wide options for this block,
 * namely the api key.
 */
function admin_options_fields() {
	if ( function_exists( 'acf_add_local_field_group' ) ) {

		acf_add_local_field_group(
			array(
				'key'                   => 'ucf_health_locations_admin_options_fields',
				'title'                 => 'Map Options',
				'fields'                => array(
					array(
						'key'               => 'field_ucf_health_locations_api_key',
						'label'             => 'Google API Key',
						'name'              => 'api_key',
						'type'              => 'text',
						'instructions'      => 'Google maps js api key. Go to <a href="https://console.cloud.google.com/google/maps-apis/apis/maps-backend.googleapis.com/credentials">https://console.cloud.google.com/google/maps-apis/apis/maps-backend.googleapis.com/credentials</a> to get your api key.',
						'required'          => 0,
						'conditional_logic' => 0,
						'wrapper'           => array(
							'width' => '',
							'class' => '',
							'id'    => '',
						),
						'message'           => '',
						'default_value'     => '',
					),
				),
				'location'              => array(
					array(
						array(
							'param'    => 'options_page',
							'operator' => '==',
							'value'    => 'ucf-health-locations-general-settings',
						),
					),
				),
				'menu_order'            => 0,
				'position'              => 'normal',
				'style'                 => 'default',
				'label_placement'       => 'top',
				'instruction_placement' => 'label',
				'hide_on_screen'        => '',
				'active'                => true,
				'description'           => '',
			)
		);
	}
}


