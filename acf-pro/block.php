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

namespace ucf_health_locations\acf_pro\block;

// create a block, then add ACF fields for options for the block
add_action( 'acf/init', __NAMESPACE__ . '\\create_block' );
add_action( 'acf/init', __NAMESPACE__ . '\\create_fields' );

const block_slug = 'ucf-health-locationsmap';

function create_block() {
	if ( function_exists( 'acf_register_block' ) ) {
		acf_register_block(
			array(
				'name'            => block_slug,
				'title'           => __( 'UCF Health Locations Map' ),
				'description'     => __( 'Google map with UCF Health locations.' ),
				'render_callback' => 'ucf_health_locations\\replacement_print',
				'category'        => 'embed',
				'icon'            => 'id',
				'keywords'        => array(
					'ucf',
					'college',
					'people',
					'directory',
					'profile',
					'person'
				),
				'mode'               => 'edit',
				'enqueue_assets' => 'ucf_health_locations\\enqueue_files',
			)
		);
	}
}

function create_fields() {

	if ( function_exists( 'acf_add_local_field_group' ) ) {
		acf_add_local_field_group(
			array(
				'key'                   => 'group_60d24f666372a',
				'title'                 => 'UCF Health Map',
				'fields'                => array(
					array(
						'key'               => 'field_60d24f7c25ae7',
						'label'             => 'Pin Locations',
						'name'              => 'pin_locations',
						'type'              => 'repeater',
						'instructions'      => '',
						'required'          => 0,
						'conditional_logic' => 0,
						'wrapper'           => array(
							'width' => '',
							'class' => '',
							'id'    => '',
						),
						'collapsed'         => 'field_60d24f8f25ae8',
						'min'               => 0,
						'max'               => 0,
						'layout'            => 'block',
						'button_label'      => '',
						'sub_fields'        => array(
							array(
								'key'               => 'field_60d24f8f25ae8',
								'label'             => 'Name',
								'name'              => 'name',
								'type'              => 'text',
								'instructions'      => '',
								'required'          => 1,
								'conditional_logic' => 0,
								'wrapper'           => array(
									'width' => '',
									'class' => '',
									'id'    => '',
								),
								'default_value'     => '',
								'placeholder'       => '',
								'prepend'           => '',
								'append'            => '',
								'maxlength'         => '',
							),
							array(
								'key'               => 'field_60d251fe25ae9',
								'label'             => 'Description',
								'name'              => 'description',
								'type'              => 'textarea',
								'instructions'      => 'General location information',
								'required'          => 0,
								'conditional_logic' => 0,
								'wrapper'           => array(
									'width' => '',
									'class' => '',
									'id'    => '',
								),
								'default_value'     => '',
								'placeholder'       => 'At University and Quadrangle Blvd. near the main UCF campus.',
								'maxlength'         => '',
								'rows'              => '',
								'new_lines'         => '',
							),
							array(
								'key'               => 'field_60d2523625aea',
								'label'             => 'Phone Numbers',
								'name'              => 'phone_numbers',
								'type'              => 'repeater',
								'instructions'      => '',
								'required'          => 0,
								'conditional_logic' => 0,
								'wrapper'           => array(
									'width' => '',
									'class' => '',
									'id'    => '',
								),
								'collapsed'         => 'field_60d252dc25aec',
								'min'               => 0,
								'max'               => 0,
								'layout'            => 'table',
								'button_label'      => '',
								'sub_fields'        => array(
									/*array(
										'key'               => 'field_60d2524525aeb',
										'label'             => 'Type',
										'name'              => 'type',
										'type'              => 'select',
										'instructions'      => '',
										'required'          => 0,
										'conditional_logic' => 0,
										'wrapper'           => array(
											'width' => '',
											'class' => '',
											'id'    => '',
										),
										'choices'           => array(
											'main'                               => 'Main',
											'billing'                            => 'Billing',
											'fax_main'                           => 'Fax - Main',
											'fax_medical: Fax - Medical Records' => 'fax_medical: Fax - Medical Records',
										),
										'default_value'     => false,
										'allow_null'        => 0,
										'multiple'          => 0,
										'ui'                => 0,
										'return_format'     => 'value',
										'ajax'              => 0,
										'placeholder'       => '',
									),*/
									array(
										'key'               => 'field_60d252dc25aec',
										'label'             => 'Number',
										'name'              => 'number',
										'type'              => 'wysiwyg',
										'instructions'      => '',
										'required'          => 0,
										'conditional_logic' => 0,
										'wrapper'           => array(
											'width' => '',
											'class' => '',
											'id'    => '',
										),
										'default_value'     => '',
										'tabs'              => 'all',
										'toolbar'           => 'basic',
										'media_upload'      => 0,
										'delay'             => 0,
									),
								),
							),
							array(
								'key'               => 'field_60d2537225aed',
								'label'             => 'Hours of Operation',
								'name'              => 'hours_of_operation',
								'type'              => 'textarea',
								'instructions'      => 'Human-readable description of open hours. Ex: Monday 8:30am-7:00pm, Tuesday-Thursday 9:00am-7:00pm',
								'required'          => 0,
								'conditional_logic' => 0,
								'wrapper'           => array(
									'width' => '',
									'class' => '',
									'id'    => '',
								),
								'default_value'     => '',
								'placeholder'       => '',
								'maxlength'         => '',
								'rows'              => '',
								'new_lines'         => '',
							),
							array(
								'key'               => 'field_60d253b125af0',
								'label'             => 'Coordinates',
								'name'              => 'coordinates',
								'type'              => 'group',
								'instructions'      => '',
								'required'          => 0,
								'conditional_logic' => 0,
								'wrapper'           => array(
									'width' => '',
									'class' => '',
									'id'    => '',
								),
								'layout'            => 'block',
								'sub_fields'        => array(
									array(
										'key'               => 'field_60d2538b25aee',
										'label'             => 'Latitude',
										'name'              => 'latitude',
										'type'              => 'number',
										'instructions'      => '',
										'required'          => 0,
										'conditional_logic' => 0,
										'wrapper'           => array(
											'width' => '',
											'class' => '',
											'id'    => '',
										),
										'default_value'     => '',
										'placeholder'       => '',
										'prepend'           => '',
										'append'            => '',
										'min'               => '',
										'max'               => '',
										'step'              => '',
									),
									array(
										'key'               => 'field_60d2539c25aef',
										'label'             => 'Longitude',
										'name'              => 'longitude',
										'type'              => 'number',
										'instructions'      => '',
										'required'          => 0,
										'conditional_logic' => 0,
										'wrapper'           => array(
											'width' => '',
											'class' => '',
											'id'    => '',
										),
										'default_value'     => '',
										'placeholder'       => '',
										'prepend'           => '',
										'append'            => '',
										'min'               => '',
										'max'               => '',
										'step'              => '',
									),
								),
							),
							array(
								'key'               => 'field_60d253f725af1',
								'label'             => 'Address',
								'name'              => 'address',
								'type'              => 'textarea',
								'instructions'      => '',
								'required'          => 0,
								'conditional_logic' => 0,
								'wrapper'           => array(
									'width' => '',
									'class' => '',
									'id'    => '',
								),
								'default_value'     => '',
								'placeholder'       => '',
								'maxlength'         => '',
								'rows'              => '',
								'new_lines'         => '',
							),
							array(
								'key'               => 'field_60d2540325af2',
								'label'             => 'URL',
								'name'              => 'url',
								'type'              => 'url',
								'instructions'      => 'Street address, city, state, zip',
								'required'          => 0,
								'conditional_logic' => 0,
								'wrapper'           => array(
									'width' => '',
									'class' => '',
									'id'    => '',
								),
								'default_value'     => '',
								'placeholder'       => '',
							),
						),
					),
					array(
						'key'               => 'field_60db3893c98df',
						'label'             => 'Panel Visibility',
						'name'              => 'panel_visible',
						'type'              => 'true_false',
						'instructions'      => 'Show or hide the tabbed info fields for all locations',
						'required'          => 0,
						'conditional_logic' => 0,
						'wrapper'           => array(
							'width' => '',
							'class' => '',
							'id'    => '',
						),
						'message'           => '',
						'default_value'     => 1,
						'ui'                => 1,
						'ui_on_text'        => '',
						'ui_off_text'       => '',
					),
					array(
						'key'               => 'field_60db3900c98e0',
						'label'             => 'Map Visibility',
						'name'              => 'map_visible',
						'type'              => 'true_false',
						'instructions'      => 'Show or hide the map',
						'required'          => 0,
						'conditional_logic' => 0,
						'wrapper'           => array(
							'width' => '',
							'class' => '',
							'id'    => '',
						),
						'message'           => '',
						'default_value'     => 0,
						'ui'                => 1,
						'ui_on_text'        => '',
						'ui_off_text'       => '',
					),
				),
				'location'              => array(
					array(
						array(
							'param'    => 'post_type',
							'operator' => '==',
							'value'    => 'post',
						),
					),
					array(
						array(
							'param'    => 'block',
							'operator' => '==',
							'value'    => 'acf/' . block_slug,
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