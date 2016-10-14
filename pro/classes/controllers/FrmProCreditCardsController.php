<?php

class FrmProCreditCardsController extends FrmProComboFieldsController {

	public static function show_in_form( $field, $field_name, $atts ) {
		$frm_settings = FrmAppHelper::get_settings();

        $errors = isset( $atts['errors'] ) ? $atts['errors'] : array();
        $html_id = $atts['html_id'];

		$defaults = self::empty_value_array();
		if ( empty( $field['value'] ) ) {
			$field['value'] = $defaults;
		} else {
			$field['value'] = array_merge( $defaults, (array) $field['value'] );
		}

		if ( $field['default_value'] == $field['value'] ) {
			$field['value'] = $defaults;
		}

		$sub_fields = self::get_sub_fields( $field );
		$remove_names = ( $field['save_cc'] == -1 );

		include( FrmAppHelper::plugin_path() .'/pro/classes/views/combo-fields/input.php' );
	}

	public static function show_in_form_builder( $field, $name = '', $null = null ) {
		if ( ! is_array( $field['default_value'] ) ) {
			$field['default_value'] = self::default_labels();
		}
		$defaults = self::empty_value_array();
		self::fill_values( $field['default_value'], $defaults );

		$field['value'] = $field['default_value'];
		$sub_fields = self::get_sub_fields( $field );

		parent::show_in_form_builder( $field, $name, $sub_fields );
	}

	public static function get_sub_fields( $field ) {
		$frm_settings = FrmAppHelper::get_settings();
		$html5_type = ( $frm_settings->use_html ) ? 'tel' : 'text';

		$fields = array(
			'cc'    => array(
				'type' => $html5_type, 'classes' => 'frm_full frm_cc_number', 'label' => 0,
				'atts' => array(
					'x-autocompletetype' => 'cc-number', 'autocompletetype' => 'cc-number', 'autocorrect' => 'off',
					'spellcheck' => 'off', 'autocapitalize' => 'off',
					'data-name' => $field['id'] . '-cc',
				),
			),
			'month' => array(
				'type' => 'select', 'classes' => 'frm_first frm_fourth frm_cc_exp_month',
				'label' => 0, 'options' => range( 1, 12 ),
				'placeholder' => __( 'Month', 'formidable' ),
			),
			'year'  => array(
				'type' => 'select', 'classes' => 'frm_fourth frm_cc_exp_year',
				'label' => 0, 'options' => range( date('Y'), date('Y') + 10 ),
				'placeholder' => __( 'Year', 'formidable' ),
			),
			'cvc'  => array(
				'type' => $html5_type, 'classes' => 'frm_half frm_cc_cvc', 'label' => 0,
				'atts' => array(
					'spellcheck' => 'off', 'autocapitalize' => 'off',
					'maxlength' => 4, 'autocorrect' => 'off', 'autocomplete' => 'off',
					'data-name' => $field['id'] . '-cvc',
				),
			),
		);

		return $fields;
	}

	public static function add_default_options( $options ) {
		$options['save_cc'] = 4;
		return $options;
	}

	public static function form_builder_options( $field, $display, $values ) {
		include( FrmAppHelper::plugin_path() .'/pro/classes/views/combo-fields/credit-cards/back-end-field-opts.php' );
	}

	public static function display_value( $value ) {
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

	public static function add_csv_columns( $headings, $atts ) {
		if ( $atts['field']->type == 'credit_card' ) {

			$default_labels = self::default_labels();
			$default_labels['month'] = __( 'Expiration Month', 'formidable' );
			$default_labels['year'] = __( 'Expiration Year', 'formidable' );

			$values = self::empty_value_array();
			foreach ( $values as $heading => $value ) {
				if ( isset( $default_labels[ $heading ] ) ) {
					$label = $default_labels[ $heading ];
				} else {
					$label = $atts['field']->name;
				}

				$headings[ $atts['field']->id .'_'. $heading ] = strip_tags( $label );
			}
		}

		return $headings;
	}

	private static function empty_value_array() {
		return array( 'cc' => '', 'month' => '', 'year' => '', 'cvc' => '' );
	}

	private static function default_labels() {
		$options = array(
			'cc'  => __( 'Card number', 'formidable' ),
			'cvc' => __( 'CVC', 'formidable' ),
		);
		return $options;
	}
}
