<?php

/**
 * @since 3.0
 */
class FrmProFieldPassword extends FrmFieldType {

	/**
	 * @var string
	 * @since 3.0
	 */
	protected $type = 'password';
	protected $display_type = 'text';

	protected function field_settings_for_type() {
		$settings = array(
			'size'          => true,
			'unique'        => true,
			'clear_on_focus' => true,
			'invalid'       => true,
			'read_only'     => true,
			'conf_field'    => true,
		);

		FrmProFieldsHelper::fill_default_field_display( $settings );
		return $settings;
	}

	protected function html5_input_type() {
		return 'password';
	}
}
