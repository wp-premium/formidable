<?php

class FrmProAddress {

	public static function validate( $errors, $field, $values, $args ) {
		_deprecated_function( __FUNCTION__, '3.0', 'FrmEntryValidate::validate_field_types' );
		FrmEntryValidate::validate_field_types( $errors, $field, $value, $args );

		return $errors;
	}

	public static function validate_required_fields( &$errors, $field, $values ) {
		_deprecated_function( __FUNCTION__, '3.0', 'FrmEntryValidate::validate_field_types' );
	}

	public static function validate_zip( &$errors, $field, $values ) {
		_deprecated_function( __FUNCTION__, '3.0', 'FrmEntryValidate::validate_field_types' );
	}
}
