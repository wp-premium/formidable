<?php

class FrmProAddressesController extends FrmProComboFieldsController {

	public static function show_in_form( $field, $field_name, $atts ) {
        $errors = isset( $atts['errors'] ) ? $atts['errors'] : array();
        $html_id = $atts['html_id'];

		$defaults = self::empty_value_array();
		self::fill_values( $field['value'], $defaults );
		self::fill_values( $field['default_value'], $defaults );

		$sub_fields = self::get_sub_fields( $field );

		include( FrmProAppHelper::plugin_path() . '/classes/views/combo-fields/input.php' );
	}

	public static function add_optional_class( $class, $field ) {
		$class .= ' frm_optional';
		return $class;
	}

	public static function show_in_form_builder( $field, $name = '', $null = null ) {
		_deprecated_function( __METHOD__, '3.0', 'FrmFieldType::show_on_form_builder' );
		$field_type = FrmFieldFactory::get_field_type( 'address', $field );
		return $field_type->show_on_form_builder( $name );
	}

	public static function get_sub_fields( $field ) {
		$fields = array(
			'line1' => array(
				'type'    => 'text',
				'classes' => '',
				'label'   => 1,
				'atts'    => array(
					'x-autocompletetype' => 'address-line1',
					'autocompletetype'   => 'address-line1',
				),
			),
			'line2' => array(
				'type' => 'text',
				'classes' => '',
				'optional' => true,
				'label' => 1,
				'atts' => array(
					'x-autocompletetype' => 'address-line2',
					'autocompletetype'   => 'address-line2',
				),
			),
			'city'  => array(
				'type'    => 'text',
				'classes' => 'frm_third frm_first',
				'label'   => 1,
				'atts'    => array(
					'x-autocompletetype' => 'city',
					'autocompletetype'   => 'city',
				),
			),
			'state' => array(
				'type'    => 'text',
				'classes' => 'frm_third',
				'label'   => 1,
				'atts'    => array(
					'x-autocompletetype' => 'state',
					'autocompletetype'   => 'state',
				),
			),
			'zip'   => array(
				'type'    => 'text',
				'classes' => 'frm_third',
				'label'   => 1,
				'atts'   => array(
					'x-autocompletetype' => 'postal-zip',
					'autocompletetype'   => 'postal-zip',
				),
			),
		);

		if ( 'europe' === $field['address_type'] ) {
			$city_field = $fields['city'];
			unset( $fields['state'], $fields['city'] );
			$fields['city'] = $city_field;
			$fields['city']['classes'] = 'frm_third';
			$fields['zip']['classes'] .= ' frm_first';
		}

		if ( $field['address_type'] == 'us' ) {
			$fields['state']['type'] = 'select';
			$fields['state']['options'] = FrmFieldsHelper::get_us_states();
		} else if ( $field['address_type'] != 'generic' ) {
			$fields['country'] = array(
				'type'    => 'select',
				'classes' => '',
				'label'   => 1,
				'options' => FrmFieldsHelper::get_countries(),
				'atts'    => array(
					'x-autocompletetype' => 'country-name',
					'autocompletetype'   => 'country-name',
				),
			);
		}

		return $fields;
	}

	public static function form_builder_options( $field, $display, $values ) {
		include( FrmProAppHelper::plugin_path() . '/classes/views/combo-fields/addresses/back-end-field-opts.php' );
	}

	public static function display_value( $value ) {
		_deprecated_function( __FUNCTION__, '3.0', 'FrmProFieldAddress->get_display_value' );
		$field_obj = FrmFieldFactory::get_field_type( 'address' );
		return $field_obj->get_display_value( $value );
	}

	public static function add_csv_columns( $headings, $atts ) {
		if ( $atts['field']->type == 'address' ) {
			$values = self::empty_value_array();

			foreach ( $values as $heading => $value ) {
				$label = self::get_field_label( $atts['field'], $heading );

				$headings[ $atts['field']->id . '_' . $heading ] = strip_tags( $label );
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
		$default_labels['line1'] = __( 'Line 1', 'formidable-pro' );
		$default_labels['line2'] = __( 'Line 2', 'formidable-pro' );
		$default_labels['country'] = __( 'Country', 'formidable-pro' );

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
			'city'  => __( 'City', 'formidable-pro' ),
			'state' => __( 'State/Province', 'formidable-pro' ),
			'zip'   => __( 'Zip/Postal', 'formidable-pro' ),
			'country' => __( 'Country', 'formidable-pro' ),
		);
		return $options;
	}
}
