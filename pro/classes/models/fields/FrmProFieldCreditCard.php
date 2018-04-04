<?php

/**
 * @since 3.0
 */
class FrmProFieldCreditCard extends FrmFieldType {

	/**
	 * @var string
	 * @since 3.0
	 */
	protected $type = 'credit_card';

	/**
	 * @var bool
	 * @since 3.0
	 */
	protected $has_for_label = false;

	protected function field_settings_for_type() {
		$settings = array(
			'clear_on_focus' => true,
			'default_blank' => true,
			'visibility'    => true,
		);

		FrmProFieldsHelper::fill_default_field_display( $settings );
		return $settings;
	}

	protected function extra_field_opts() {
		$options['save_cc'] = 4;

		$default_labels = $this->empty_value_array();
		foreach ( $default_labels as $key => $label ) {
			$options[ $key . '_desc' ] = $label;
		}

		return $options;
	}

	private static function default_labels() {
		return array(
			'cc'  => __( 'Card number', 'formidable-pro' ),
			'cvc' => __( 'CVC', 'formidable-pro' ),
		);
	}

	public function show_on_form_builder( $name = '' ) {
		$field = FrmFieldsHelper::setup_edit_vars( $this->field );
		$defaults = $this->empty_value_array();

		if ( ! is_array( $field['default_value'] ) ) {
			$field['default_value'] = $defaults;
		}

		$this->fill_values( $field['default_value'], $defaults );

		$field['value'] = $field['default_value'];
		$sub_fields = FrmProCreditCardsController::get_sub_fields( $field );
		$field_name = $this->html_name( $name );
		$html_id = $this->html_id();

		include( FrmProAppHelper::plugin_path() . '/classes/views/combo-fields/input-form-builder.php' );
	}

	public function front_field_input( $args, $shortcode_atts ) {
		$pass_args = array( 'errors' => $args['errors'], 'html_id' => $args['html_id'] );
		ob_start();
		FrmProCreditCardsController::show_in_form( $this->field, $args['field_name'], $pass_args );
		$input_html = ob_get_contents();
		ob_end_clean();

		return $input_html;
	}

	protected function prepare_display_value( $value, $atts ) {
		if ( ! is_array( $value ) ) {
			return $value;
		}

		$new_value = '';
		if ( isset( $value['month'] ) && ! empty( $value['month'] ) ) {
			if ( ! empty( $value['cc'] ) ) {
				$new_value = $value['cc'] . ' <br/>';
			}

			$new_value .= $value['month'] . '/' . $value['year'];
		}
		return $new_value;
	}

	private function empty_value_array() {
		return array( 'cc' => '', 'month' => '', 'year' => '', 'cvc' => '' );
	}

	public function validate( $args ) {
		$errors = array();
		$this->validate_required_fields( $errors, $args );
		$this->validate_cc_number( $errors, $args );
		$this->validate_cc_expiration( $errors, $args );
		$this->validate_cvc( $errors, $args );

		return $errors;
	}

	private function validate_required_fields( &$errors, $args ) {
		$values = $args['value'];
		if ( $this->should_require( $values ) ) {
			foreach ( $values as $key => $value ) {
				if ( empty( $value ) ) {
					$errors[ 'field' . $args['id'] . '-' . $key ] = '';
					$errors[ 'field' . $args['id'] ] = FrmFieldsHelper::get_error_msg( $this->field, 'blank' );
				}
			}
		}
	}

	private function should_require( $values ) {
		$partial_fill = ( isset( $values['cc'] ) && ! empty( $values['cc'] ) );
		$saving_draft = FrmProFormsHelper::saving_draft();

		if ( $saving_draft ) {
			$is_editing = false;
		} else {
			$is_editing = ( $_POST && isset( $_POST['id'] ) && is_numeric( $_POST['id'] ) );
			if ( $is_editing ) {
				$is_editing = ! FrmProEntry::is_draft( absint( $_POST['id'] ) );
			}
		}

		if ( $partial_fill && ! $is_editing ) {
			return true;
		}

		if ( ! $this->field->required || $is_editing || $saving_draft ) {
			return false;
		}

		$skip_required = FrmProEntryMeta::skip_required_validation( $this->field );
		return ( ! $skip_required );
	}

	private function validate_cc_number( &$errors, $args ) {
		$values = $args['value'];
		if ( isset( $values['cc'] ) && ! empty( $values['cc'] ) ) {
			// if a CVC is present, then the user must have added it
			$should_validate = ( isset( $values['cvc'] ) && ! empty( $values['cvc'] ) ) || isset( $errors[ 'field' . $args['id'] . '-cvc' ] );
			if ( $should_validate ) {
				$is_valid_cc = $this->is_valid_cc_number( $values['cc'] );
				if ( ! $is_valid_cc ) {
					$errors[ 'field' . $args['id'] . '-cc' ] = __( 'That credit card number is invalid', 'formidable-pro' );
				}
			}
		}
	}

	private function is_valid_cc_number( $card_number ) {
		// Get the first digit
		$firstnumber = substr( $card_number, 0, 1 );

		// Make sure it is the correct amount of digits. Account for dashes being present.
		switch ( $firstnumber ) {
			case 3:
				$is_valid = preg_match( '/^3\d{3}[ \-]?\d{6}[ \-]?\d{5}$/', $card_number );
				break;
			case 4:
				$is_valid = preg_match( '/^4\d{3}[ \-]?\d{4}[ \-]?\d{4}[ \-]?\d{4}$/', $card_number );
				break;
			case 5:
				$is_valid = preg_match( '/^5\d{3}[ \-]?\d{4}[ \-]?\d{4}[ \-]?\d{4}$/', $card_number );
				break;
			case 6:
				$is_valid = preg_match( '/^6011[ \-]?\d{4}[ \-]?\d{4}[ \-]?\d{4}$/', $card_number );
				break;
			default:
				$is_valid = false;
		}

		$this->validate_luhn_algorithm( $card_number, $is_valid );

		return $is_valid;
	}

	private function validate_luhn_algorithm( $card_number, &$is_valid ) {
		if ( ! $is_valid ) {
			return;
		}

		$card_number = str_replace( array( '-', ' ' ), '', $card_number );
		$map = array( 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 0, 2, 4, 6, 8, 1, 3, 5, 7, 9 );
		$sum = 0;
		$last = strlen( $card_number ) - 1;
		for ( $i = 0; $i <= $last; $i++ ) {
			$sum += $map[ $card_number[ $last - $i ] + ( $i & 1 ) * 10 ];
		}

		if ( $sum % 10 != 0 ) {
			$is_valid = false;
		}

		if ( ! $is_valid ) {
			$allow = array( '4242424242424242' );
			$is_valid = in_array( $card_number, $allow );
		}
	}

	/**
	 * Make sure the date is in the future
	 */
	private function validate_cc_expiration( &$errors, $args ) {
		$values = $args['value'];
		if ( isset( $values['month'] ) && ! empty( $values['month'] ) && ! empty( $values['year'] ) ) {
			$is_past_date = ( $values['year'] <= date('Y') && $values['month'] < date('m') );
			if ( $is_past_date ) {
				$errors[ 'field' . $args['id'] . '-month' ] = __( 'That credit card is expired', 'formidable-pro' );
				$errors[ 'field' . $args['id'] . '-year' ] = '';
			}
		}
	}

	private function validate_cvc( &$errors, $args ) {
		$values = $args['value'];
		if ( isset( $values['cvc'] ) && ! empty( $values['cvc'] ) ) {
			$character_count = strlen( $values['cvc'] );
			$is_correct_length = ( $character_count == 3 || $character_count == 4 );
			$is_valid = ( is_numeric( $values['cvc'] ) && $is_correct_length );
			if ( ! $is_valid ) {
				$errors[ 'field' . $args['id'] . '-cvc' ] = __( 'Please enter a valid CVC', 'formidable-pro' );
			}
		}
	}

	public function set_value_before_save( $value ) {
		if ( empty( $value ) ) {
			return $value;
		}

		$serialized = false;
		if ( ! is_array( $value ) ) {
			$value = maybe_unserialize( $value );
			$serialized = true;
		}

		if ( is_array( $value ) ) {
			self::delete_cvc( $value );
			self::remove_extra_cc_digits( $value );
			if ( $serialized ) {
				$value = serialize( $value );
			}
		}

		return $value;
	}

	/**
	 * The CVC shouldn't be stored
	 */
	private function delete_cvc( &$value ) {
		$value['cvc'] = '';
	}

	/**
	 * If the whole cc number isn't required, get rid of it
	 */
	private function remove_extra_cc_digits( &$value ) {
		$save_digits = FrmField::get_option( $this->field, 'save_cc' );

		if ( $save_digits == 16 ) {
			// do nothing
		} elseif ( $save_digits == 0 ) {
			$value['cc'] = '';
		} elseif ( ! empty( $value['cc'] ) ) {
			$length = max( strlen( $value['cc'] ) - 4, 0 );
			$value['cc'] = str_repeat( 'x', $length ) . substr( $value['cc'], -4 );
		}
	}

}
