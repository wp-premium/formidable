<?php

/**
 * @since 3.0
 */
class FrmProFieldScale extends FrmFieldType {

	/**
	 * @var string
	 * @since 3.0
	 */
	protected $type = 'scale';

	protected function input_html() {
		return $this->multiple_input_html();
	}

	protected function include_form_builder_file() {
		return FrmProAppHelper::plugin_path() . '/classes/views/frmpro-fields/back-end/field-' . $this->type . '.php';
	}

	protected function field_settings_for_type() {
		$settings = array(
			'default_value' => true,
			'unique'        => true,
			'default_blank' => false,
		);

		FrmProFieldsHelper::fill_default_field_display( $settings );
		return $settings;
	}

	protected function extra_field_opts() {
		$opts = array(
			'minnum' => 1,
			'maxnum' => 10,
		);

		$options = $this->get_field_column('options');
		if ( ! empty( $options ) ) {
			$range = maybe_unserialize( $options );

			$opts['minnum'] = reset( $range );
			$opts['maxnum'] = end( $range );
		}

		return $opts;
	}

	protected function new_field_settings() {
		return array(
			'options' => range( 1, 10 ),
		);
	}

	public function get_container_class() {
		// Add class to inline Scale field
		$class = '';
		if ( $this->field['label'] == 'inline' ) {
			$class = ' frm_scale_container';
		}
		return $class;
	}

	protected function include_front_form_file() {
		return FrmProAppHelper::plugin_path() . '/classes/views/frmpro-fields/10radio.php';
	}
}
