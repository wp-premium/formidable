<?php

/**
 * @since 3.0
 */
class FrmProFieldDate extends FrmFieldType {

	/**
	 * @var string
	 * @since 3.0
	 */
	protected $type = 'date';
	protected $display_type = 'text';

	protected function field_settings_for_type() {
		$settings = array(
			'autopopulate'  => true,
			'size'          => true,
			'unique'        => true,
			'clear_on_focus' => true,
			'invalid'       => true,
			'read_only'     => true,
		);
		FrmProFieldsHelper::fill_default_field_display( $settings );
		return $settings;
	}

	protected function extra_field_opts() {
		return array(
			'start_year' => '-10',
			'end_year'   => '+10',
			'locale'     => '',
			'max'        => '10',
		);
	}

	protected function fill_default_atts( &$atts ) {
		$defaults = array(
			'format' => false,
		);
		$atts = wp_parse_args( $atts, $defaults );
	}

	/**
	 * @since 3.01.01
	 */
	public function show_options( $field, $display, $values ) {
		$locales = FrmAppHelper::locales( 'date' );
		include( FrmProAppHelper::plugin_path() . '/classes/views/frmpro-fields/back-end/calendar.php' );

		parent::show_options( $field, $display, $values );
	}

	public function prepare_front_field( $values, $atts ) {
		$values['value'] = FrmProAppHelper::maybe_convert_from_db_date( $values['value'] );
		return $values;
	}

	protected function html5_input_type() {
		return 'text';
	}

	/**
	 * Add extra classes on front-end input
	 *
	 * @since 3.01.04
	 */
	protected function get_input_class() {
		$class = '';
		if ( ! FrmField::is_read_only( $this->field ) ) {
			$class = 'frm_date';
		}
		return $class;
	}

	protected function load_field_scripts( $args ) {
		if ( ! FrmField::is_read_only( $this->field ) ) {
			global $frm_vars;
			if ( ! isset( $frm_vars['datepicker_loaded'] ) || ! is_array( $frm_vars['datepicker_loaded'] ) ) {
				$frm_vars['datepicker_loaded'] = array();
			}

			if ( ! isset( $frm_vars['datepicker_loaded'][ $args['html_id'] ] ) ) {
				$static_html_id = $this->html_id();
				if ( $args['html_id'] != $static_html_id ) {
					// user wildcard for repeating fields
					$frm_vars['datepicker_loaded'][ '^' . $static_html_id ] = true;
				} else {
					$frm_vars['datepicker_loaded'][ $args['html_id'] ] = true;
				}
			}

			$entry_id = isset( $frm_vars['editing_entry'] ) ? $frm_vars['editing_entry'] : 0;
			FrmProFieldsHelper::set_field_js( $this->field, $entry_id );
		}
	}

	public function validate( $args ) {
		$errors = array();
		$value = $args['value'];

		if ( $value == '' ) {
			return $errors;
		}

		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ) {
			$frmpro_settings = new FrmProSettings();
			$formated_date = FrmProAppHelper::convert_date( $value, $frmpro_settings->date_format, 'Y-m-d' );

			//check format before converting
			if ( $value != date( $frmpro_settings->date_format, strtotime( $formated_date ) ) ) {
				$allow_it = apply_filters( 'frm_allow_date_mismatch', false, array(
					'date' => $value, 'formatted_date' => $formated_date,
				) );
				if ( ! $allow_it ) {
					$errors[ 'field' . $args['id'] ] = FrmFieldsHelper::get_error_msg( $this->field, 'invalid' );
				}
			}

			$value = $formated_date;
			unset( $formated_date );
		}

		$date = explode( '-', $value );

		if ( count( $date ) != 3 || ! checkdate( (int) $date[1], (int) $date[2], (int) $date[0] ) ) {
			$errors[ 'field' . $args['id'] ] = FrmFieldsHelper::get_error_msg( $this->field, 'invalid' );
		}

		if ( ! $this->validate_year_is_within_range( $date[0] ) ) {
			$errors[ 'field' . $args['id'] ] = FrmFieldsHelper::get_error_msg( $this->field, 'invalid' );
		}

		return $errors;
	}

	private function validate_year_is_within_range( $year ) {
		$year       = (int) $year;
		$start_year = $this->maybe_convert_relative_year_to_int('start_year');
		$end_year   = $this->maybe_convert_relative_year_to_int('end_year');

		return ( ( ! $start_year || ( $start_year <= $year ) ) && ( ! $end_year || ( $year <= $end_year ) ) );
	}

	private function maybe_convert_relative_year_to_int( $start_end ) {
		$rel_year = FrmField::get_option( $this->field, $start_end );

		if ( is_string( $rel_year ) && strlen( $rel_year ) > 0 && ( '0' === $rel_year || '+' == $rel_year[0] || '-' == $rel_year[0] ) ) {
			$rel_year = date( 'Y', strtotime( $rel_year . ' year' ) );
		}

		return (int) $rel_year;
	}

	public function is_not_unique( $value, $entry_id ) {
		$value = FrmProAppHelper::maybe_convert_to_db_date( $value, 'Y-m-d' );
		return parent::is_not_unique( $value, $entry_id );
	}

	public function set_value_before_save( $value ) {
		return FrmProAppHelper::maybe_convert_to_db_date( $value, 'Y-m-d' );
	}

	protected function prepare_display_value( $value, $atts ) {
		if ( $value === false ) {
			return $value;
		}

		if ( isset( $atts['time_ago'] ) ) {
			$value = FrmProFieldsHelper::get_date( $value, 'Y-m-d H:i:s' );
			$value = FrmAppHelper::human_time_diff( strtotime( $value ), strtotime( date_i18n( 'Y-m-d' ) ), absint( $atts['time_ago'] ) );
		} else {
			if ( ! is_array( $value ) && strpos( $value, ',' ) ) {
				$value = explode( ',', $value );
			}

			if ( isset( $atts['date_format'] ) && ! empty( $atts['date_format'] ) ) {
				$atts['format'] = $atts['date_format'];
			}

			$value = FrmProFieldsHelper::format_values_in_array( $value, $atts['format'], array( 'self', 'get_date' ) );
		}

		return $value;
	}

	protected function prepare_import_value( $value, $atts ) {
		if ( ! empty( $value ) ) {
			$value = date( 'Y-m-d', strtotime( $value ) );
		}

		return $value;
	}
}
