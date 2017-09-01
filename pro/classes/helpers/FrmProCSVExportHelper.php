<?php

class FrmProCSVExportHelper{

	public static function generate_csv( $atts ) {
		_deprecated_function( __METHOD__, '2.0.19', 'FrmCSVExportHelper::'. __FUNCTION__ );
		return FrmCSVExportHelper::generate_csv( $atts );
	}

	private static function set_class_paramters() {
		_deprecated_function( __METHOD__, '2.0.19', 'FrmCSVExportHelper::'. __FUNCTION__ );
		return;
	}

	public static function get_csv_format() {
		_deprecated_function( __METHOD__, '2.0.19', 'FrmCSVExportHelper::'. __FUNCTION__ );
		FrmCSVExportHelper::get_csv_format();
		return;
	}

	public static function encode_value( $line ) {
		_deprecated_function( __METHOD__, '2.0.19', 'FrmCSVExportHelper::'. __FUNCTION__ );
		return FrmCSVExportHelper::encode_value( $line );
    }

	/**
	 * @since 2.0
	 */
	public static function escape_csv( $value ) {
		_deprecated_function( __METHOD__, '2.0.19', 'FrmCSVExportHelper::'. __FUNCTION__ );
		return FrmCSVExportHelper::escape_csv( $value );
	}
}