<?php

class FrmProCreditCard {

	public static function validate( $errors, $field, $values, $args ) {
		_deprecated_function( __FUNCTION__, '3.0', 'FrmEntryValidate::validate_field_types' );
		FrmEntryValidate::validate_field_types( $errors, $field, $value, $args );
		return $errors;
	}

	public static function validate_required_fields( &$errors, $field, $values ) {
		_deprecated_function( __FUNCTION__, '3.0', 'FrmEntryValidate::validate_field_types' );
	}

	public static function should_require( $field, $values ) {
		_deprecated_function( __FUNCTION__, '3.0', 'FrmEntryValidate::validate_field_types' );
	}

	public static function validate_cc_number( &$errors, $field, $values ) {
		_deprecated_function( __FUNCTION__, '3.0', 'FrmEntryValidate::validate_field_types' );
	}

    public static function secure_before_redirect( $url, $form, $atts ) {
		_deprecated_function( __FUNCTION__, '3.0', 'FrmProFieldCreditCard->set_value_before_save' );
        self::delete_values( $atts['id'], $form );
        return $url;
    }

    /**
	 * Clear values if not redirected
	 */
    public static function secure_after_save( $atts ) {
		_deprecated_function( __FUNCTION__, '3.0', 'FrmProFieldCreditCard->set_value_before_save' );
        self::delete_values( $atts['entry_id'], $atts['form'] );
    }

	private static function delete_values( $entry_id, $form ) {
		_deprecated_function( __FUNCTION__, '3.0', 'FrmProFieldCreditCard->set_value_before_save' );

		$form_id = is_numeric( $form ) ? $form : $form->id;
		$credit_card_fields = FrmField::get_all_types_in_form( $form_id, 'credit_card', '', 'include' );
		foreach ( $credit_card_fields as $cc_field ) {
			$cc_values = FrmEntryMeta::get_entry_meta_by_field( $entry_id, $cc_field->id );

			$field_obj = FrmFieldFactory::get_field_object( $cc_field );
			$cc_values = $field_obj->set_value_before_save( $cc_values );

			FrmEntryMeta::update_entry_meta( $entry_id, $cc_field->id, null, $cc_values );
		}
	}

	/**
	 * The CVC shouldn't be stored
	 */
	public static function delete_cvc( &$cc_values ) {
		_deprecated_function( __FUNCTION__, '3.0', 'FrmProFieldCreditCard->set_value_before_save' );
	}

	/**
	 * If the whole cc number isn't required, get rid of it
	 */
	public static function remove_extra_cc_digits( &$cc_values, $cc_field ) {
		_deprecated_function( __FUNCTION__, '3.0', 'FrmProFieldCreditCard->set_value_before_save' );
	}
}
