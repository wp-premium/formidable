<?php

class FrmProComboFieldsController {

	public static function show_in_form_builder( $field, $name = '', $sub_fields = array() ) {
		$frm_settings = FrmAppHelper::get_settings();
		$frm_settings->use_html = false;

		// Generate field name and HTML id
		$field_name = 'item_meta[' . $field['id'] . ']';
		$html_id = 'field_' . $field['field_key'];

		include( FrmAppHelper::plugin_path() .'/pro/classes/views/combo-fields/input-form-builder.php' );
	}

	public static function get_sub_fields( $field ) {
		return array();
	}

	public static function fill_values( &$value, $defaults ) {
		if ( empty( $value ) ) {
			$value = $defaults;
		} else {
			$value = array_merge( $defaults, (array) $value );
		}
	}

	public static function include_placeholder( $default_value, $sub_field, $field = array() ) {
		if ( ! empty( $field ) && ! FrmField::is_option_true( $field, 'clear_on_focus' ) ) {
			return;
		}

		if ( isset( $default_value[ $sub_field ] ) && ! empty( $sub_field ) ) {
			echo ' placeholder="' . esc_attr( $default_value[ $sub_field ] ) . '" ';
		}
	}

	public static function get_dropdown_label( $atts ) {
		$default = isset( $atts['sub_field']['placeholder'] ) ? $atts['sub_field']['placeholder'] : ' ';
		return apply_filters( 'frm_combo_dropdown_label', $default, $atts );
	}

	public static function add_atts_to_input( $atts ) {
		self::include_placeholder( $atts['field']['default_value'], $atts['key'], $atts['field'] );

		if ( isset( $atts['field']['default_value'][ $atts['key'] ] ) ) {
			$atts['field']['default_value'] = $atts['field']['default_value'][ $atts['key'] ];
		} else{
			$atts['field']['default_value'] = '';
		}

		if ( isset( $atts['sub_field']['optional'] ) && $atts['sub_field']['optional'] ) {
			add_filter( 'frm_field_classes', 'FrmProAddressesController::add_optional_class', 20, 2 );
			do_action( 'frm_field_input_html', $atts['field'] );
			remove_filter( 'frm_field_classes', 'FrmProAddressesController::add_optional_class', 20 );
		} else {
			do_action( 'frm_field_input_html', $atts['field'] );
		}

		if ( isset( $atts['sub_field']['atts'] ) ) {
			foreach ( $atts['sub_field']['atts'] as $att_name => $att_value ) {
				echo ' '. esc_attr( $att_name ) . '="'. esc_attr( $att_value ) .'"';
			}
		}
	}

	public static function include_sub_label( $atts ) {
		$is_form_builder = FrmAppHelper::is_admin_page('formidable' );
		$ajax_action = FrmAppHelper::get_param( 'action', '', 'get', 'sanitize_text_field' );
		$is_new_field = FrmAppHelper::doing_ajax() && ( $ajax_action == 'frm_insert_field' || $ajax_action == 'frm_load_field' );
		
		if ( $is_form_builder || $is_new_field ) {
			self::include_inplace_sub_label( $atts );
		} else {
			self::show_sub_label( $atts );
		}
	}

	public static function include_inplace_sub_label( $atts ) {
		$field = $atts['field'];
		$option_name = $atts['option_name'];
		include( FrmAppHelper::plugin_path() .'/pro/classes/views/combo-fields/inplace-sub-label.php' );
	}

	public static function show_sub_label( $atts ) {
		$field = $atts['field'];
		$option_name = $atts['option_name'];
		if ( $field[ $option_name ] !== '' ) {
			echo '<div class="frm_description">' . wp_kses_post( $field[ $option_name ] ) . '</div>';
		}
	}

	public static function maybe_add_error_class( $atts ) {
		$has_error = isset( $atts['errors'][ 'field' . $atts['field']['id'] . '-' . $atts['key'] ]  );
		if ( $has_error ) {
			echo ' frm_blank_field';
		}
	}

	public static function add_default_options( $options ) {
		return $options;
	}

	private static function empty_value_array() {
		return array();
	}
}
