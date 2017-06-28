<?php
/**
 * REST API Setting Options controller
 *
 * Handles requests to the /settings/$group/$setting endpoints.
 *
 * @author   WooThemes
 * @category API
 * @package  WooCommerce/API
 * @since    3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Filters new address settings into the settings general endpoint
 *
 * These new settings are being added to WC 3.x in
 * pull request https://github.com/woocommerce/woocommerce/pull/15636
 * This filter is used to insert them if not present.
 *
 * THIS FILTER SHOULD NOT BE PORTED TO WOOCOMMERCE CORE as the above PR
 * already takes care of this in WooCommerce core.
 */
function wc_rest_dev_add_address_settings_to_settings_general( $settings ) {
	$new_settings = array(
		array(
			'id' => 'woocommerce_store_address',
			'type' => 'text',
			'option_key' => 'woocommerce_store_address',
			'default' => '',
		),
		array(
			'id' => 'woocommerce_store_address_2',
			'type' => 'text',
			'option_key' => 'woocommerce_store_address_2',
			'default' => '',
		),
		array(
			'id' => 'woocommerce_store_city',
			'type' => 'text',
			'option_key' => 'woocommerce_store_city',
			'default' => '',
		),
		array(
			'id' => 'woocommerce_store_postcode',
			'type' => 'text',
			'option_key' => 'woocommerce_store_postcode',
			'default' => '',
		),
	);

	// For each of the new settings, make sure the setting id doesn't
	// already exist in the settings array and then add it
	$ids = array_column( $settings, 'id' );
	foreach ( $new_settings as $new_setting ) {
		if ( ! in_array( $new_setting['id'], $ids ) ) {
			$settings[] = $new_setting;
		}
	}

	return $settings;
}
add_filter( 'woocommerce_settings-general', 'wc_rest_dev_add_address_settings_to_settings_general', 999 );

/**
 * REST API Setting Options controller class.
 *
 * @package WooCommerce/API
 */
class WC_REST_Dev_Setting_Options_Controller extends WC_REST_Setting_Options_Controller {

	/**
	 * WP REST API namespace/version.
	 */
	protected $namespace = 'wc/v3';

	/**
	 * Get setting data.
	 *
	 * @param string $group_id Group ID.
	 * @param string $setting_id Setting ID.
	 * @return stdClass|WP_Error
	 */
	public function get_setting( $group_id, $setting_id ) {
		$setting = parent::get_setting( $group_id, $setting_id );
		if ( is_wp_error( $setting ) ) {
			return $setting;
		}
		$setting['group_id'] = $group_id;
		return $setting;
	}

	/**
	 * Callback for allowed keys for each setting response.
	 *
	 * @param  string $key Key to check
	 * @return boolean
	 */
	public function allowed_setting_keys( $key ) {
		return in_array( $key, array(
			'id',
			'group_id',
			'label',
			'description',
			'default',
			'tip',
			'placeholder',
			'type',
			'options',
			'value',
			'option_key',
		) );
	}

	/**
	 * Get the settings schema, conforming to JSON Schema.
	 *
	 * @return array
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'              => 'http://json-schema.org/draft-04/schema#',
			'title'                => 'setting',
			'type'                 => 'object',
			'properties'           => array(
				'id'               => array(
					'description'  => __( 'A unique identifier for the setting.', 'woocommerce' ),
					'type'         => 'string',
					'arg_options'  => array(
						'sanitize_callback' => 'sanitize_title',
					),
					'context'      => array( 'view', 'edit' ),
					'readonly'     => true,
				),
				'group_id'         => array(
					'description'  => __( 'An identifier for the group this setting belongs to.', 'woocommerce' ),
					'type'         => 'string',
					'arg_options'  => array(
						'sanitize_callback' => 'sanitize_title',
					),
					'context'      => array( 'view', 'edit' ),
					'readonly'     => true,
				),
				'label'            => array(
					'description'  => __( 'A human readable label for the setting used in interfaces.', 'woocommerce' ),
					'type'         => 'string',
					'arg_options'  => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
					'context'      => array( 'view', 'edit' ),
					'readonly'     => true,
				),
				'description'      => array(
					'description'  => __( 'A human readable description for the setting used in interfaces.', 'woocommerce' ),
					'type'         => 'string',
					'arg_options'  => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
					'context'      => array( 'view', 'edit' ),
					'readonly'     => true,
				),
				'value'          => array(
					'description'  => __( 'Setting value.', 'woocommerce' ),
					'type'         => 'mixed',
					'context'      => array( 'view', 'edit' ),
				),
				'default'          => array(
					'description'  => __( 'Default value for the setting.', 'woocommerce' ),
					'type'         => 'mixed',
					'context'      => array( 'view', 'edit' ),
					'readonly'     => true,
				),
				'tip'              => array(
					'description'  => __( 'Additional help text shown to the user about the setting.', 'woocommerce' ),
					'type'         => 'string',
					'arg_options'  => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
					'context'      => array( 'view', 'edit' ),
					'readonly'     => true,
				),
				'placeholder'      => array(
					'description'  => __( 'Placeholder text to be displayed in text inputs.', 'woocommerce' ),
					'type'         => 'string',
					'arg_options'  => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
					'context'      => array( 'view', 'edit' ),
					'readonly'     => true,
				),
				'type'             => array(
					'description'  => __( 'Type of setting.', 'woocommerce' ),
					'type'         => 'string',
					'arg_options'  => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
					'context'      => array( 'view', 'edit' ),
					'enum'         => array( 'text', 'email', 'number', 'color', 'password', 'textarea', 'select', 'multiselect', 'radio', 'image_width', 'checkbox' ),
					'readonly'     => true,
				),
				'options'          => array(
					'description' => __( 'Array of options (key value pairs) for inputs such as select, multiselect, and radio buttons.', 'woocommerce' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
			),
		);

		return $this->add_additional_fields_schema( $schema );
	}

}
