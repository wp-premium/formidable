<?php

class FrmProTimeField {

	public static function time_array_to_string( &$value ) {
		_deprecated_function( __FUNCTION__, '3.0', 'FrmFieldType::time_array_to_string' );
		$field_obj = FrmFieldFactory::get_field_type( 'time' );
		$field_obj->time_array_to_string( $value );
	}

	public static function is_time_empty( $value ) {
		_deprecated_function( __FUNCTION__, '3.0', 'FrmFieldType::is_time_empty' );
		$field_obj = FrmFieldFactory::get_field_type( 'time' );
		return $field_obj->is_time_empty( $value );
	}

	public static function get_time_options( $values ) {
		_deprecated_function( __FUNCTION__, '3.0', 'FrmFieldType::get_options' );

		$field_obj = FrmFieldFactory::get_field_type( 'time', $values );
		return $field_obj->get_options();
	}

	public static function show_time_field( $field, $values ) {
		_deprecated_function( __FUNCTION__, '3.0', 'FrmFieldType::include_front_field_input' );
		$field_obj = FrmFieldFactory::get_field_type( 'time', $field );
		echo $field_obj->include_front_field_input( $values, array() );
	}

	public static function is_datetime_used( $field, $value, $entry_id ) {
		_deprecated_function( __FUNCTION__, '3.0', 'FrmFieldType::is_not_unique' );
		$field_obj = FrmFieldFactory::get_field_type( 'time', $field );
		return $field_obj->is_not_unique( $value, $entry_id );
	}

	public static function get_disallowed_times( $values, &$remove ) {
		_deprecated_function( __FUNCTION__, '3.0', 'FrmFieldType::get_disallowed_times' );

		$field_obj = FrmFieldFactory::get_field_type( 'time', $values );
		$field_obj->get_disallowed_times( $values, $remove );
	}

	public static function validate_time_field( &$errors, $field, $value, $args = array() ) {
		_deprecated_function( __FUNCTION__, '3.0', 'FrmFieldType::validate' );

        if ( $field->type != 'time' ) {
            return;
        }

		self::validate_field_types( $errors, $field, $value, $args );
	}
}
