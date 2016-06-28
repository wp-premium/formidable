<?php

class FrmProCreditCard {

	public static function validate( $errors, $field, $values, $args ) {
		if ( ! isset( $field->temp_id ) ) {
			$field->temp_id = $field->id;
		}

		self::validate_required_fields( $errors, $field, $values );
		self::validate_cc_number( $errors, $field, $values );
		self::validate_cc_expiration( $errors, $field, $values );
		self::validate_cvc( $errors, $field, $values );

		add_filter( 'frm_redirect_url', 'FrmProCreditCard::secure_before_redirect', 50, 3 );
		add_action( 'frm_after_entry_processed', 'FrmProCreditCard::secure_after_save', 100 );

		return $errors;
	}

	public static function validate_required_fields( &$errors, $field, $values ) {
		if ( self::should_require( $field, $values ) ) {
			foreach ( $values as $key => $value ) {
				if ( empty( $value ) ) {
					$errors[ 'field' . $field->temp_id . '-' . $key ] = '';
				}
			}
		}
	}

	public static function should_require( $field, $values ) {
		$partial_fill = ( isset( $values['cc'] ) && ! empty( $values['cc'] ) );
		$is_editing = ( $_POST && isset( $_POST['id'] ) && is_numeric( $_POST['id'] ) );

		if ( $partial_fill && ! $is_editing ) {
			return true;
		}

		if ( ! $field->required || $is_editing ) {
			return false;
		}

		$skip_required = FrmProEntryMeta::skip_required_validation( $field );
		return ( ! $skip_required );
	}

	public static function validate_cc_number( &$errors, $field, $values ) {
		if ( isset( $values['cc'] ) && ! empty( $values['cc'] ) ) {
			// if a CVC is present, then the user must have added it
			$should_validate = ( isset( $values['cvc'] ) && ! empty( $values['cvc'] ) ) || isset( $errors[ 'field' . $field->temp_id . '-cvc' ] );
			if ( $should_validate ) {
				$is_valid_cc = self::is_valid_cc_number( $values['cc'] );
				if ( ! $is_valid_cc ) {
					$errors[ 'field' . $field->temp_id . '-cc' ] = __( 'That credit card number is invalid', 'formidable' );
				}
			}
		}
	}

	private static function is_valid_cc_number( $card_number ) {
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

		self::validate_luhn_algorithm( $card_number, $is_valid );

		return $is_valid;
	}

	private static function validate_luhn_algorithm( $card_number, &$is_valid ) {
		if ( ! $is_valid ) {
			return;
		}

		$credit_card_number = str_replace( array( '-', ' ' ), '', $card_number );
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
			$is_valid = in_array( $credit_card_number, $allow );
		}
	}

	/**
	 * Make sure the date is in the future
	 */
	private static function validate_cc_expiration( &$errors, $field, $values ) {
		if ( isset( $values['month'] ) && ! empty( $values['month'] ) && ! empty( $values['year'] ) ) {
			$is_past_date = ( $values['year'] <= date('Y') && $values['month'] < date('m') );
			if ( $is_past_date ) {
				$errors[ 'field' . $field->temp_id . '-month' ] = __( 'That credit card is expired', 'formidable' );
				$errors[ 'field' . $field->temp_id . '-year' ] = '';
			}
		}
	}

	private static function validate_cvc( &$errors, $field, $values ) {
		if ( isset( $values['cvc'] ) && ! empty( $values['cvc'] ) ) {
			$character_count = strlen( $values['cvc'] );
			$is_correct_length = ( $character_count == 3 || $character_count == 4 );
			$is_valid = ( is_numeric( $values['cvc'] ) && $is_correct_length );
			if ( ! $is_valid ) {
				$errors[ 'field' . $field->temp_id . '-cvc' ] = '';
			}
		}
	}

    public static function secure_before_redirect( $url, $form, $atts ) {
        self::delete_values( $atts['id'], $form );
        return $url;
    }

    /**
	 * Clear values if not redirected
	 */
    public static function secure_after_save( $atts ) {
        self::delete_values( $atts['entry_id'], $atts['form'] );
    }

	private static function delete_values( $entry_id, $form ) {
		$credit_card_fields = FrmField::get_all_types_in_form( $form->id, 'credit_card', '', 'include' );
		foreach ( $credit_card_fields as $cc_field ) {
			$cc_values = FrmEntryMeta::get_entry_meta_by_field( $entry_id, $cc_field->id );
			self::delete_cvc( $cc_values );
			self::remove_extra_cc_digits( $cc_values, $cc_field );
			FrmEntryMeta::update_entry_meta( $entry_id, $cc_field->id, null, $cc_values );
		}
	}

	/**
	 * The CVC shouldn't be stored
	 */
	public static function delete_cvc( &$cc_values ) {
		$cc_values['cvc'] = '';
	}

	/**
	 * If the whole cc number isn't required, get rid of it
	 */
	public static function remove_extra_cc_digits( &$cc_values, $cc_field ) {
		$save_digits = FrmField::get_option( $cc_field, 'save_cc' );

		if ( $save_digits == 16 ) {
			// do nothing
		} else if ( $save_digits == 0 ) {
			$cc_values['cc'] = '';
		} else if ( ! empty( $cc_values['cc'] ) ) {
			$length = max( strlen( $cc_values['cc'] ) - 4, 0 );
			$cc_values['cc'] = str_repeat( 'x', $length ) . substr( $cc_values['cc'], -4 );
		}
	}
}
