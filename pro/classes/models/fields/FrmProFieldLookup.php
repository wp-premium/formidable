<?php

/**
 * @since 3.0
 */
class FrmProFieldLookup extends FrmFieldType {

	/**
	 * @var string
	 * @since 3.0
	 */
	protected $type = 'lookup';

	protected $is_tall = true;

	public function show_on_form_builder( $name = '' ) {
		$field = FrmFieldsHelper::setup_edit_vars( $this->field );
		FrmProLookupFieldsController::show_lookup_field_input_on_form_builder( $field );
	}

	protected function field_settings_for_type() {
		$settings = array(
			'read_only' => true,
			'unique'    => true,
		);

		FrmProFieldsHelper::fill_default_field_display( $settings );
		return $settings;
	}

	public function prepare_front_field( $values, $atts ) {
		FrmProLookupFieldsController::maybe_get_initial_lookup_field_options( $values );
		return $values;
	}

	public function front_field_input( $args, $shortcode_atts ) {
		ob_start();

		FrmProLookupFieldsController::get_front_end_lookup_field_html( $this->field, $args['field_name'], $args['html_id'] );
		$input_html = ob_get_contents();
		ob_end_clean();

		return $input_html;
	}

	protected function prepare_import_value( $value, $atts ) {
		if ( FrmField::get_option( $this->field, 'data_type' ) == 'checkbox' ) {
			$value = FrmProXMLHelper::convert_imported_value_to_array( $value );
		}
		return $value;
	}

	protected function extra_field_opts() {
		return array(
			'data_type'                  => 'select',
			'watch_lookup'               => array(),
			'get_values_form'            => '',
			'get_values_field'           => '',
			'lookup_filter_current_user' => false,
			'lookup_placeholder_text'    => '',
			'lookup_option_order'        => 'ascending',
		);
	}
}
