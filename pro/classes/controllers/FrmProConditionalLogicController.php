<?php

/**
 * @since 2.02.13
 */
class FrmProConditionalLogicController {

	/**
	 * Check if a given field should be present in another field's logic options
	 *
	 * @since 2.02.13
	 *
	 * @param array $current_field - The current field being displayed for editing
	 * @param object $logic_field - The logic field
	 *
	 * @return bool
	 */
	public static function is_field_present_in_logic_options( $current_field, $logic_field ) {
		$present        = true;
		$parent_form_id = isset( $current_field[ 'parent_form_id' ] ) ? $current_field[ 'parent_form_id' ] : '0';
		$in_section_id =  isset( $logic_field->field_options['in_section'] ) ? $logic_field->field_options['in_section'] : '0';

		if ( $logic_field->id == $current_field[ 'id' ] ) {
			$present = false;
		} else if ( FrmField::is_no_save_field( $logic_field->type ) ) {
			$present = false;
		} else if ( in_array( $logic_field->type, array( 'file', 'rte', 'date', 'address', 'credit_card' ) ) ) {
			$present = false;
		} else if ( FrmProField::is_list_field( $logic_field ) ) {
			$present = false;
		} else if ( $logic_field->form_id != $current_field['form_id'] && $logic_field->form_id != $parent_form_id ) {
			$present = false;
		} else if ( $in_section_id == $current_field['id'] ) {
			$present = false;
		}

		return $present;
	}
}
