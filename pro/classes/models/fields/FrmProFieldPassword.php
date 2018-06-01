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

	/**
	 * @return array
	 */
	protected function extra_field_opts() {
		return array(
			'strong_pass' => 0,
		);
	}

	protected function html5_input_type() {
		return 'password';
	}

	public function show_options( $field, $display, $values ) {
		include( FrmProAppHelper::plugin_path() . '/classes/views/frmpro-fields/back-end/password-options.php' );

		parent::show_options( $field, $display, $values );
	}

	/**
	 * Add extra classes on front-end input
	 *
	 * @since 3.01.04
	 */
	protected function get_input_class() {
		$class = '';
		// add class for javascript validation
		if ( FrmField::get_option( $this->field, 'strong_pass' ) ) {
			$class .= ' frm_strong_pass';
		}

		return $class;
	}

	/**
	 * @param array $args
	 * @return array|mixed errors
	 * @since 3.01.04
	 */
	public function validate( $args ) {
		$errors = array();
		$password = $args['value'];
		if ( '' === trim( $password ) ) {
			return $errors;
		}

		$check_strength = FrmField::get_option( $this->field, 'strong_pass' );

		//validate the password format
		if ( $check_strength ) {
			$message = $this->check_format( $password );
			if ( ! empty( $message ) ) {
				$errors[ 'field' . $args['id'] ] = $message;
			}
		}

		return $errors;
	}

	private function check_format( $password ) {
		$message = array();
		if ( ! $this->is_long_password( $password ) ) {
			$message = __( 'Passwords require at least 8 characters', 'formidable-pro' );
		} elseif ( ! $this->includes_number( $password ) ) {
			$message = __( 'Passwords must include at least one number', 'formidable-pro' );
		} elseif ( ! $this->includes_uppercase( $password ) ) {
			$message = __( 'Passwords must include at least one uppercase letter', 'formidable-pro' );
		} elseif ( ! $this->includes_lowercase( $password ) ) {
			$message = __( 'Passwords must include at least one lowercase letter', 'formidable-pro' );
		} elseif ( ! $this->includes_character( $password ) ) {
			$message = FrmFieldsHelper::get_error_msg( $this->field, 'invalid' );
		}

		return $message;
	}

	private function is_long_password( $password ) {
		return strlen( $password ) >= 8;
	}

	private function includes_number( $password ) {
		return preg_match( '#[0-9]+#', $password );
	}

	private function includes_uppercase( $password ) {
		return preg_match( '#[A-Z]+#', $password );
	}

	private function includes_lowercase( $password ) {
		return preg_match( '#[a-z]+#', $password );
	}

	private function includes_character( $password ) {
		return preg_match( '/(?=.*[^a-zA-Z0-9])/', $password );
	}
}
