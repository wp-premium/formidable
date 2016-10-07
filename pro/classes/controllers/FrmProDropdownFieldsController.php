<?php

class FrmProDropdownFieldsController{

	public static function get_hidden_fields_with_readonly_values( $field, $field_name, $html_id ) {
		$html = '';

		if ( is_array( $field['value'] ) ) {
			foreach ( $field['value'] as $selected_value ) {
				$html .= '<input type="hidden" value="' . esc_attr( $selected_value ) . '" name="' . esc_attr( $field_name ) . '[]" />';
			}
		} else {
			$html .= '<input type="hidden" value="' . esc_attr( $field['value'] ) . '" name="' . esc_attr( $field_name ) . '" id="' . esc_attr( $html_id ) . '" />';
		}

		return $html;
	}
}