<?php

/**
 * @since 3.0
 */
class FrmProFieldTime extends FrmFieldType {

	/**
	 * @var string
	 * @since 3.0
	 */
	protected $type = 'time';

	public function show_on_form_builder( $name = '' ) {
		$field = FrmFieldsHelper::setup_edit_vars( $this->field );
		$field['value'] = $field['default_value'];

		$field_name = $this->html_name( $name );
		$html_id = $this->html_id();

		$this->show_time_field( compact( 'field', 'html_id', 'field_name' ) );
	}

	protected function field_settings_for_type() {
		$settings = array(
			'autopopulate'  => true,
			'size'          => true,
			'unique'        => true,
			'read_only'     => true,
			'default_value' => true,
			'invalid'       => true,
		);

		FrmProFieldsHelper::fill_default_field_display( $settings );
		return $settings;
	}

	protected function extra_field_opts() {
		return array(
			'start_time'  => '00:00',
			'end_time'    => '23:30',
			'clock'       => 12,
			'single_time' => 0,
			'step'        => 30,
		);
	}

	protected function fill_default_atts( &$atts ) {
		$defaults = array(
			'format' => $this->get_time_format_for_field(),
		);

		$atts = wp_parse_args( $atts, $defaults );
	}

	public function prepare_front_field( $values, $atts ) {
		$values['options'] = $this->get_options( $values );
		$values['value'] = $this->prepare_field_value( $values['value'], $atts );

		return $values;
	}

	public function prepare_field_value( $value, $atts ) {
		return $this->get_display_value( $value, $atts );
	}

	public function get_options( $values ) {
		if ( empty( $values ) ) {
			// use a text field for conditional logic
			return parent::get_options( $values );
		}

		$this->prepare_time_settings( $values );

		$options = array();
		$this->get_single_time_field_options( $values, $options );

		$use_single_dropdown = FrmField::is_option_true( $values, 'single_time' );
		if ( ! $use_single_dropdown ) {
			$this->get_multiple_time_field_options( $values, $options );
		}

		return $options;
	}

	public function front_field_input( $args, $shortcode_atts ) {
		ob_start();

		$this->show_time_field( array(
			'html_id'    => $args['html_id'],
			'field_name' => $args['field_name'],
		) );
		$input_html = ob_get_contents();
		ob_end_clean();

		return $input_html;
	}

	private function show_time_field( $values ) {
		if ( isset( $values['field'] ) ) {
			$field = $values['field'];
		} else {
			$field = $values['field'] = $this->field;
		}

		$values['field_value'] = $field['value'];
		$this->set_field_column( 'options', $field['options'] );

		$hidden = $this->maybe_include_hidden_values( $values );
		$this->maybe_format_time( $values['field_value'] );

		if ( isset( $field['options']['H'] ) ) {
			$this->time_string_to_array( $values['field_value'] );
			$this->time_string_to_array( $values['field']['default_value'] );

			$html = '<div class="frm_time_wrap"><span dir="ltr">' . "\r\n";

			$values['combo_name'] = 'H';
			$html .= $this->get_select_box( $values ) . "\r\n";

			$html .= '<span class="frm_time_sep">:</span>' . "\r\n";

			$values['combo_name'] = 'm';
			$html .= $this->get_select_box( $values ) . "\r\n";

			$html .= '</span>' . "\r\n";

			if ( isset( $field['options']['A'] ) ) {
				$values['combo_name'] = 'A';
				$html .= $this->get_select_box( $values ) . "\r\n";
			}
			$html .= '</div>';
		} else {
			$this->time_array_to_string( $values['field_value'] );
			$html = $this->get_select_box( $values );
		}

		echo $hidden . $html;
	}

	/**
	 * If the value was in a hidden field on a previous page,
	 * it may still be in the database format
	 *
	 * @since 3.02.01
	 */
	private function maybe_format_time( &$time ) {
		if ( ! is_array( $time ) && ! strpos( $time, ' ' ) ) {
			$time = $this->get_display_value( $time, array(
				'format' => $this->get_time_format_for_field(),
			) );
		}
	}

	/**
	 * Add extra classes on front-end input
	 *
	 * @since 3.01.04
	 */
	protected function get_input_class() {
		$class = '';
		$is_separate = $this->get_field_column( 'options' );
		$combo_name = FrmField::get_option( $this->field, 'combo_name' );
		if ( isset( $is_separate['H'] ) || ! empty( $combo_name ) ) {
			$class = 'auto_width frm_time_select';
		}

		return $class;
	}

	protected function show_readonly_hidden() {
		return true;
	}

	public function validate( $args ) {
		$errors = array();

		if ( is_array( $args['value'] ) ) {
			$this->time_array_to_string( $args['value'] );
			FrmEntriesHelper::set_posted_value( $this->field, $args['value'], $args );
		}

		$is_required = FrmField::is_required( (array) $this->field );
		$is_empty = ! is_array( $args['value'] ) && trim( $args['value'] ) == '';
		if ( $is_required && $is_empty ) {
			$errors[ 'field' . $args['id'] ] = FrmFieldsHelper::get_error_msg( $this->field, 'blank' );
		} elseif ( ! $is_empty && ! $this->in_time_range( $args['value'] ) ) {
			$errors[ 'field' . $args['id'] ] = FrmFieldsHelper::get_error_msg( $this->field, 'invalid' );
		}

		return $errors;
	}

	private function in_time_range( $time ) {
		$values = $this->field->field_options;
		$this->fill_start_end_times( $values );

		$time = FrmProAppHelper::format_time( $time );
		return $time >= $values['start_time'] && $time <= $values['end_time'];
	}

	private function prepare_time_settings( &$values ) {
		$this->fill_start_end_times( $values );

		$values['start_time_str'] = $values['start_time'];
		$values['end_time_str'] = $values['end_time'];

		$this->split_time_setting( $values['start_time'] );
		$this->split_time_setting( $values['end_time'] );

		$this->step_in_minutes( $values['step'] );

		$values['hour_step'] = floor( $values['step'] / 60 );
		if ( ! $values['hour_step'] ) {
			$values['hour_step'] = 1;
		}

		if ( $values['end_time'][0] < $values['start_time'][0] ) {
			$values['end_time'][0] += 12;
		}
	}

	private function fill_start_end_times( &$values ) {
		$values['clock'] = isset( $values['clock'] ) ? $values['clock'] : 12;
		$values['start_time'] = isset( $values['start_time'] ) ? $values['start_time'] : '';
		$values['end_time'] = isset( $values['end_time'] ) ? $values['end_time'] : '';
		$this->format_time( '00:00', $values['start_time'] );
		$this->format_time( '23:59', $values['end_time'] );
	}

	public function is_not_unique( $value, $entry_id ) {
		$used = false;
		$value = FrmProAppHelper::format_time( $value );

		if ( FrmProEntryMetaHelper::value_exists( $this->get_field_column('id'), $value, false ) ) {

			$first_date_field = FrmProFormsHelper::has_field( 'date', $this->get_field_column('form_id') );

			if ( $first_date_field ) {

				$values = array(
					'time_field' => 'field_' . $this->field->field_key,
					'date_field' => 'field_' . $first_date_field->field_key,
					'time_key'   => $this->field->id,
					'date_key'   => $first_date_field->id,
					'date'       => sanitize_text_field( $_POST['item_meta'][ $first_date_field->id ] ), //TODO: repeat name
					'time'       => $value,
					'entry_id'   => $entry_id,
				);

				$not_allowed = array();
				$this->get_disallowed_times( $values, $not_allowed );
				if ( ! empty( $not_allowed ) ) {
					$used = true;
				}
			} else {
				$used = true;
			}
		}

		return $used;
	}

	/**
	 * Prepare the global time field JS information
	 *
	 * @since 3.0
	 *
	 * @param array $values
	 */
	protected function load_field_scripts( $values ) {
		if ( $this->field['unique'] && $this->field['single_time'] && isset( $values['html_id'] ) ) {
			global $frm_vars;

			if ( ! isset( $frm_vars['timepicker_loaded'] ) || ! is_array( $frm_vars['timepicker_loaded'] ) ) {
				$frm_vars['timepicker_loaded'] = array();
			}

			if ( ! isset( $frm_vars['timepicker_loaded'][ $values['html_id'] ] ) ) {
				$frm_vars['timepicker_loaded'][ $values['html_id'] ] = true;
			}
		}

	}

	public function get_disallowed_times( $values, &$remove ) {
		$values['date'] = FrmProAppHelper::maybe_convert_to_db_date( $values['date'], 'Y-m-d' );

		$remove = apply_filters( 'frm_allowed_times', $remove, $values );
		array_walk_recursive( $remove, 'FrmProAppHelper::format_time_by_reference' );

		$values['date_entries'] = $this->get_entry_ids_for_date( $values );
		if ( empty( $values['date_entries'] ) ) {
			return;
		}

		$used_times = $this->get_used_times_for_entries( $values );
		if ( empty( $used_times ) ) {
			return;
		}

		$number_allowed = apply_filters( 'frm_allowed_time_count', 1, $values['time_key'], $values['date_key'] );
		$count = array();
		foreach ( $used_times as $used ) {
			if ( isset( $remove[ $used ] ) ) {
				continue;
			}

			if ( ! isset( $count[ $used ] ) ) {
				$count[ $used ] = 0;
			}
			$count[ $used ]++;

			if ( (int) $count[ $used ] >= $number_allowed ) {
				$remove[ $used ] = $used;
			}
		}
	}

	private function get_entry_ids_for_date( $values ) {
		$query = array( 'meta_value' => $values['date'] );
		FrmProEntryMeta::add_field_to_query( $values['date_key'], $query );

		return FrmEntryMeta::getEntryIds( $query );
	}

	private function get_used_times_for_entries( $values ) {
		$query = array( 'it.item_id' => $values['date_entries'] );
		FrmProEntryMeta::add_field_to_query( $values['time_key'], $query );

		if ( $values['entry_id'] ) {
			$query['it.item_id !'] = $values['entry_id'];
		}
		if ( isset( $values['time'] ) && ! empty( $values['time'] ) ) {
			$query['meta_value'] = $values['time'];
		}

		global $wpdb;
		$select = $wpdb->prefix . 'frm_item_metas it';
		if ( ! is_numeric( $values['time_key'] ) ) {
			$select .= ' LEFT JOIN ' . $wpdb->prefix . 'frm_fields fi ON (it.field_id = fi.id)';
		}

		$used_times = FrmDb::get_col( $select, $query, 'meta_value' );
		return $used_times;
	}

	private function split_time_setting( &$time ) {
		$separator = ':';

		$time = FrmProAppHelper::format_time( $time );
		$time = explode( $separator, $time );
	}

	private function step_in_minutes( &$step ) {
		$separator = ':';
		$step = explode( $separator, $step );
		$step = ( isset( $step[1] ) ) ? ( ( $step[0] * 60 ) + $step[1] ) : ( $step[0] );
		if ( empty( $step ) ) {
			// force an hour step if none was defined to prevent infinite loop
			$step = 60;
		}
	}

	private function get_single_time_field_options( $values, &$options ) {
		$time = strtotime( $values['start_time_str'] );
		$end_time = strtotime( $values['end_time_str'] );
		$format = ( $values['clock'] == 24 ) ? 'H:i' : 'g:i A';
		$values['step'] = max( $values['step'] * 60, 60 ); //switch minutes to seconds

		$options[] = '';
		while ( $time <= $end_time ) {
			$options[] = date( $format, $time );
			$time += $values['step'];
		}
	}

	private function get_multiple_time_field_options( $values, &$options ) {
		$all_times = $options;

		$options['H'] = array( '' );
		$options['m'] = array( '' );

		$this->get_hours( $all_times, $options );
		$this->get_minutes( $all_times, $options );

		if ( $values['clock'] != 24 ) {
			$options['A'] = array( 'AM', 'PM' );
		}
	}

	/**
	 * Get the hour options for a three-dropdown time field
	 *
	 * @since 3.0
	 *
	 * @param array $all_times
	 * @param array $options
	 */
	private function get_hours( $all_times, &$options ) {
		foreach ( $all_times as $time ) {
			if ( $time == '' ) {
				$options['H'][] = '';
				continue;
			}

			$colon_position = strpos( $time, ':' );
			if ( $colon_position !== false ) {
				$hour = substr( $time, 0, $colon_position );
				$options['H'][] = $hour;
			}
		}
		unset( $time );

		$options['H'] = array_unique( $options['H'] );
	}

	/**
	 * Get the minute options for a three-dropdown time field
	 *
	 * @since 3.0
	 *
	 * @param array $all_times
	 * @param array $options
	 */
	private function get_minutes( $all_times, &$options ) {

		foreach ( $all_times as $time ) {

			if ( $time == '' ) {
				$options['m'][] = '';
				continue;
			}

			$colon_position = strpos( $time, ':' );
			if ( $colon_position !== false ) {

				$minute = substr( $time, $colon_position + 1 );
				if ( strpos( $minute, 'M' ) ) {
					// AM/PM is included, so strip it off
					$minute = str_replace( array( ' AM', ' PM' ), '', $minute );
				}

				$options['m'][] = $minute;
			}
		}
		unset( $time );

		$options['m'] = array_unique( $options['m'] );
		sort( $options['m'] );
	}

	/**
	 * Format the start and end time
	 *
	 * @since 3.0
	 *
	 * @param string $default
	 * @param string $time
	 */
	private function format_time( $default, &$time ) {
		if ( strlen( $time ) === 4 && substr( $time, 1, 1 ) === ':' ) {
			$time = '0' . $time;
		} elseif ( strlen( $time ) !== 5 || $time === '' ) {
			$time = $default;
		}
	}

	public function set_value_before_save( $value ) {
		if ( is_array( $value ) ) {
			$this->time_array_to_string( $value );
		}
		return FrmProAppHelper::format_time( $value, 'H:i' );
	}

	protected function prepare_display_value( $value, $atts ) {
		if ( empty( $value ) ) {
			return $value;
		}

		if ( is_array( $value ) && isset( $value['H'] ) ) {
			$this->time_array_to_string( $value );
		} elseif ( ! is_array( $value ) && strpos( $value, ',' ) ) {
			$value = explode( ',', $value );
		}

		return FrmProFieldsHelper::format_values_in_array( $value, $atts['format'], array( 'FrmProAppHelper', 'format_time' ) );
	}

	public function time_array_to_string( &$value ) {
		if ( $this->is_time_empty( $value ) ) {
			$value = '';
		} elseif ( is_array( $value ) ) {
			$new_value = $value['H'] . ':' . $value['m'];
			$new_value .= ( isset( $value['A'] ) ? ' ' . $value['A'] : '' );
			$value = $new_value;
		}
	}

	private function time_string_to_array( &$value ) {
		$defaults = array( 'H' => '', 'm' => '', 'A' => '' );
		if ( is_array( $value ) ) {
			$value = wp_parse_args( $value, $defaults );
		} elseif ( is_string( $value ) && strpos( $value, ':' ) !== false ) {
			$h = explode( ':', $value );
			$m = explode( ' ', $h[1] );

			$value = array(
				'H' => reset( $h ),
				'm' => reset( $m ),
				'A' => isset( $m[1] ) ? $m[1] : '',
			);
		} else {
			$value = $defaults;
		}
	}

	public function is_time_empty( $value ) {
		$empty_string = ! is_array( $value ) && $value == '';
		$empty_array = is_array( $value ) && ( $value['H'] == '' || $value['m'] == '' );
		return $empty_string || $empty_array;
	}

	protected function prepare_import_value( $value, $atts ) {
		return FrmProAppHelper::format_time( $value );
	}

	/**
	 * @since 3.02.01
	 */
	public function get_time_format_for_field( $field = array() ) {
		if ( empty( $field ) ) {
			$field = $this->field;
		}
		$time_format = FrmField::get_option( $field, 'clock', 12 );
		return $this->get_time_format_for_setting( $time_format );
	}

	/**
	 * @since 3.02.01
	 */
	public function get_time_format_for_setting( $time_format ) {
		return ( $time_format == 12 ) ? 'g:i A' : 'H:i';
	}
}
