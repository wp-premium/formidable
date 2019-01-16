<?php

/**
 * @since 3.0
 */
class FrmProFieldForm extends FrmFieldType {

	/**
	 * @var string
	 * @since 3.0
	 */
	protected $type = 'form';

	public function default_html() {
		$default_html = <<<DEFAULT_HTML
<div id="frm_field_[id]_container" class="frm_form_field form-field [required_class][error_class]">
[input]
</div>
DEFAULT_HTML;
		return $default_html;
	}

	protected function include_form_builder_file() {
		return FrmProAppHelper::plugin_path() . '/classes/views/frmpro-fields/back-end/field-' . $this->type . '.php';
	}

	protected function field_settings_for_type() {
		$settings = array(
			'default_blank' => false,
			'required'      => false,
			'visibility'    => false,
			'description'   => false,
			'label_position' => false,
		);

		FrmProFieldsHelper::fill_default_field_display( $settings );
		return $settings;
	}

	public function get_container_class() {
		return ' frm_embed_form_container';
	}

	public function front_field_input( $args, $shortcode_atts ) {
		ob_start();

		FrmProNestedFormsController::display_front_end_embedded_form( $this->field, $args['field_name'], $args['errors'] );
		$input_html = ob_get_contents();
		ob_end_clean();

		return $input_html;
	}

	protected function prepare_import_value( $value, $atts ) {
		return $this->get_new_child_ids( $value, $atts );
	}
}
