<?php

class FrmProAddressesController extends FrmProComboFieldsController {

	public static function show_in_form( $field, $field_name, $atts ) {
        $errors = isset( $atts['errors'] ) ? $atts['errors'] : array();
        $html_id = $atts['html_id'];

		$defaults = self::empty_value_array();
		self::fill_values( $field['value'], $defaults );
		self::fill_values( $field['default_value'], $defaults );

		$sub_fields = self::get_sub_fields( $field );

		include( FrmAppHelper::plugin_path() .'/pro/classes/views/combo-fields/input.php' );
	}

	public static function add_optional_class( $class, $field ) {
		$class .= ' frm_optional';
		return $class;
	}

	public static function show_in_form_builder( $field, $name = '', $null = null ) {
		$defaults = self::empty_value_array();
		self::fill_values( $field['default_value'], $defaults );

		$field['value'] = $field['default_value'];
		$sub_fields = self::get_sub_fields( $field );

		parent::show_in_form_builder( $field, $name, $sub_fields );

		$display = array(
			'clear_on_focus' => true,
			'default_blank' => true,
		);

		FrmFieldsHelper::clear_on_focus_html( $field, $display );
	}

	public static function get_sub_fields( $field ) {
		$fields = array(
			'line1' => array(
				'type' => 'text', 'classes' => '', 'label' => 1,
				'atts' => array( 'x-autocompletetype' => 'address-line1', 'autocompletetype' => 'address-line1' ),
		 	),
			'line2' => array(
				'type' => 'text', 'classes' => '', 'optional' => true, 'label' => 1,
				'atts' => array( 'x-autocompletetype' => 'address-line2', 'autocompletetype' => 'address-line2' ),
			),
			'city'  => array(
				'type' => 'text', 'classes' => 'frm_first frm_third', 'label' => 1,
				'atts' => array( 'x-autocompletetype' => 'city', 'autocompletetype' => 'city' ),
			),
			'state' => array(
				'type' => 'text', 'classes' => 'frm_third', 'label' => 1,
				'atts' => array( 'x-autocompletetype' => 'state', 'autocompletetype' => 'state' ),
			),
			'zip'   => array(
				'type' => 'text', 'classes' => 'frm_third', 'label' => 1,
				 'atts' => array( 'x-autocompletetype' => 'postal-zip', 'autocompletetype' => 'postal-zip' ),
			 ),
		);

		if ( $field['address_type'] == 'us' ) {
			$fields['state']['type'] = 'select';
			$fields['state']['options'] = FrmFieldsHelper::get_us_states();
		} else if ( $field['address_type'] != 'generic' ) {
			$fields['country'] = array(
				'type' => 'select', 'classes' => '', 'label' => 1,
				'options' => FrmFieldsHelper::get_countries(),
				'atts' => array( 'x-autocompletetype' => 'country-name', 'autocompletetype' => 'country-name' ),
			);
		}

		return $fields;
	}

	public static function add_default_options( $options ) {
		$options['address_type'] = 'international';

		$default_labels = self::default_labels();
		foreach ( $default_labels as $key => $label ) {
			$options[ $key . '_desc' ] = $label;
		}

		return $options;
	}

	public static function form_builder_options( $field, $display, $values ) {
		include( FrmAppHelper::plugin_path() .'/pro/classes/views/combo-fields/addresses/back-end-field-opts.php' );
	}

	public static function display_value( $value ) {
		if ( ! is_array( $value ) ) {
			return $value;
		}

		$new_value = '';
		if ( ! empty( $value['line1'] ) ) {
			$defaults = self::empty_value_array();
			self::fill_values( $value, $defaults );

			$new_value = $value['line1'] . ' <br/>';
			if ( ! empty( $value['line2'] ) ) {
				$new_value .= $value['line2'] . ' <br/>';
			}
			$new_value .= $value['city'] . ', ' . $value['state'] . ' <br/>';
			$new_value .= $value['zip'];
			if ( isset( $value['country'] ) && ! empty( $value['country']) ) {
				$new_value .= ' <br/>' . $value['country'];
			}
		}
		return $new_value;
	}

	public static function add_csv_columns( $headings, $atts ) {
		if ( $atts['field']->type == 'address' ) {
			$values = self::empty_value_array();

			foreach ( $values as $heading => $value ) {
				$label = self::get_field_label( $atts['field'], $heading );

				$headings[ $atts['field']->id .'_'. $heading ] = strip_tags( $label );
			}
		}
		return $headings;
	}

	public static function empty_value_array() {
		return array( 'line1' => '', 'line2' => '', 'city' => '', 'state' => '', 'zip' => '', 'country' => '' );
	}

	/**
	 * Get the label for the CSV
	 * @since 2.0.23
	 */
	private static function get_field_label( $field, $field_name ) {
		$default_labels = self::default_labels();
		$descriptions = array_keys( $default_labels );
		$default_labels['line1'] = __( 'Line 1', 'formidable' );
		$default_labels['line2'] = __( 'Line 2', 'formidable' );
		$default_labels['country'] = __( 'Country', 'formidable' );

		$label = isset( $default_labels[ $field_name ] ) ? $default_labels[ $field_name ] : '';
		if ( in_array( $field_name, $descriptions ) ) {
			$saved_label = FrmField::get_option( $field, $field_name . '_desc' );
			if ( ! empty( $saved_label ) ) {
				$label = $saved_label;
			}
		}

		if ( empty( $label ) ) {
			$label = $field_name;
		}

		$label = $field->name . ' - ' . $label;

		return $label;
	}

	private static function default_labels() {
		$options = array(
			'line1' => '',
			'line2' => '',
			'city'  => __( 'City', 'formidable' ),
			'state' => __( 'State/Province', 'formidable' ),
			'zip'   => __( 'Zip/Postal', 'formidable' ),
			'country' => __( 'Country', 'formidable' ),
		);
		return $options;
	}
}
