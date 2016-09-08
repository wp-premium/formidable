<?php

class FrmProPhoneFieldsController{

	/**
	 * Show the Pro options for a phone field on the form builder page
	 *
	 * @since 2.02.06
	 * @param array $field
	 * @param array $display
	 */
	public static function show_field_options_in_form_builder( $field, $display ) {
		FrmProFieldsController::show_format_option( $field );
		FrmProFieldsController::show_visibility_option( $field );
		FrmProFieldsController::show_conditional_logic_option( $field );
		FrmProFieldsController::show_dynamic_values_options( $field, $display );
	}
}