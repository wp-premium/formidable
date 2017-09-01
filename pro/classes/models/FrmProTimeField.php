<?php

class FrmProTimeField {

	public static function time_array_to_string( &$value ) {
		if ( self::is_time_empty( $value ) ) {
			$value = '';
		} elseif ( is_array( $value ) ) {
			$new_value = $value['H'] . ':' . $value['m'];
			$new_value .= ( isset( $value['A'] ) ? ' ' . $value['A'] : '' );
			$value = $new_value;
		}
	}

	public static function is_time_empty( $value ) {
		$empty_string = ! is_array( $value ) && $value == '';
		$empty_array = is_array( $value ) && ( $value['H'] == '' || $value['m'] == '' );
		return $empty_string || $empty_array;
	}

	public static function get_time_options( $values ) {
		self::prepare_time_settings( $values );

		$options = array();
		self::get_single_time_field_options( $values, $options );

		$use_single_dropdown = FrmField::is_option_true( $values, 'single_time' );
		if ( ! $use_single_dropdown ) {
			self::get_multiple_time_field_options( $values, $options );
		}

		return $options;
	}

	private static function prepare_time_settings( &$values ) {
		self::fill_start_end_times( $values );

		$values['start_time_str'] = $values['start_time'];
		$values['end_time_str'] = $values['end_time'];

		self::split_time_setting( $values['start_time'] );
		self::split_time_setting( $values['end_time'] );

		self::step_in_minutes( $values['step'] );

		$values['hour_step'] = floor( $values['step'] / 60 );
		if ( ! $values['hour_step'] ) {
			$values['hour_step'] = 1;
		}

		if ( $values['end_time'][0] < $values['start_time'][0] ) {
			$values['end_time'][0] += 12;
		}
	}


	private static function fill_start_end_times( &$values ) {
		$values['start_time'] = isset( $values['start_time'] ) ? $values['start_time'] : '';
		$values['end_time'] = isset( $values['end_time'] ) ? $values['end_time'] : '';
		self::format_time( '00:00', $values['start_time'] );
		self::format_time( '23:59', $values['end_time'] );
	}

	/**
	 * Format the start and end time
	 *
	 * @since 2.03.04
	 *
	 * @param string $default
	 * @param string $time
	 */
	private static function format_time( $default, &$time ) {
		if ( strlen( $time ) === 4 && substr( $time, 1, 1 ) === ':' ) {
			$time = '0' . $time;
		} else if ( strlen( $time ) !== 5 || $time === '' ) {
			$time = $default;
		}
	}

	private static function fill_default_time( &$time, $default ) {
		if ( empty( $time ) ) {
			$time = $default;
		}
	}

	private static function split_time_setting( &$time ) {
		$separator = ':';

		$time = FrmProAppHelper::format_time( $time );
		$time = explode( $separator, $time );
	}

	private static function step_in_minutes( &$step ) {
		$separator = ':';
		$step = explode( $separator, $step );
		$step = ( isset( $step[1] ) ) ? ( ( $step[0] * 60 ) + $step[1] ) : ( $step[0] );
		if ( empty( $step ) ) {
			// force an hour step if none was defined to prevent infinite loop
			$step = 60;
		}
	}

	private static function get_single_time_field_options( $values, &$options ) {
		$time = strtotime( $values['start_time_str'] );
		$end_time = strtotime( $values['end_time_str'] );
		$format = ( $values['clock'] == 24 ) ? 'H:i' : 'g:i A';
		$values['step'] = $values['step'] * 60; //switch minutes to seconds

		$options[] = '';
		while ( $time <= $end_time ) {
			$options[] = date( $format, $time );
			$time += $values['step'];
		}
	}

	private static function get_multiple_time_field_options( $values, &$options ) {
		$all_times = $options;

		$options['H'] = array('');
		$options['m'] = array('');

		self::get_hours( $all_times, $options );
		self::get_minutes( $all_times, $options );

		if ( $values['clock'] != 24 ) {
			$options['A'] = array( 'AM', 'PM');
		}
	}

	/**
	 * Get the hour options for a three-dropdown time field
	 *
	 * @since 2.03
	 *
	 * @param array $all_times
	 * @param array $options
	 */
	private static function get_hours( $all_times, &$options ) {
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
	 * @since 2.03
	 *
	 * @param array $all_times
	 * @param array $options
	 */
	private static function get_minutes( $all_times, &$options ) {

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

	public static function show_time_field( $field, $values ) {
		self::prepare_global_js( $field, $values );

		if ( isset( $field['options']['H'] ) ) {
			if ( is_array( $field['value'] ) ) {
				$h = isset( $field['value']['H'] ) ? $field['value']['H'] : '';
				$m = isset( $field['value']['m'] ) ? $field['value']['m'] : '';
				$a = isset( $field['value']['A'] ) ? $field['value']['A'] : '';
			} else if ( is_string( $field['value'] ) && strpos( $field['value'], ':' ) !== false ) {
				$h = explode( ':', $field['value'] );
				$m = explode( ' ', $h[ 1 ] );
				$h = reset( $h );
				$a = isset( $m[ 1 ] ) ? $m[ 1 ] : '';
				$m = reset( $m );
			} else {
				$h = $m = $a = '';
			}

			include( FrmAppHelper::plugin_path() . '/pro/classes/views/frmpro-fields/front-end/time.php' );
		} else {
			self::time_array_to_string( $field['value'] );
			include( FrmAppHelper::plugin_path() . '/pro/classes/views/frmpro-fields/front-end/time-single.php' );
		}
	}

	/**
	 * Prepare the global time field JS information
	 *
	 * @since 2.03.08
	 *
	 * @param array $field
	 * @param array $values
	 */
	private static function prepare_global_js( $field, $values ) {
		if ( $field['unique'] && $field['single_time'] && isset( $values['html_id'] ) ) {
			global $frm_vars;

			if ( ! isset($frm_vars['timepicker_loaded']) || ! is_array($frm_vars['timepicker_loaded']) ) {
				$frm_vars['timepicker_loaded'] = array();
			}

			if ( ! isset($frm_vars['timepicker_loaded'][ $values['html_id'] ]) ) {
				$frm_vars['timepicker_loaded'][ $values['html_id'] ] = true;
			}
		}

	}

	public static function is_datetime_used( $field, $value, $entry_id ) {
		$used = false;
		$value = FrmProAppHelper::format_time( $value );

		if ( FrmProEntryMetaHelper::value_exists( $field->id, $value, false ) ) {

			$first_date_field = FrmProFormsHelper::has_field( 'date', $field->form_id );

			if ( $first_date_field ) {

				$values = array(
					'time_field' => 'field_' . $field->field_key,
					'date_field' => 'field_' . $first_date_field->field_key,
					'time_key'   => $field->id,
					'date_key'   => $first_date_field->id,
					'date'       => sanitize_text_field( $_POST['item_meta'][ $first_date_field->id ] ), //TODO: repeat name
					'time'       => $value,
					'entry_id'   => $entry_id,
				);

				$not_allowed = array();
				self::get_disallowed_times( $values, $not_allowed );
				if ( ! empty( $not_allowed ) ) {
					$used = true;
				}
			} else {
				$used = true;
			}
		}

		return $used;
	}

	public static function get_disallowed_times( $values, &$remove ) {
		$values['date'] = FrmProAppHelper::maybe_convert_to_db_date( $values['date'], 'Y-m-d' );

		$remove = apply_filters( 'frm_allowed_times', $remove, $values );
		array_walk_recursive( $remove, 'FrmProAppHelper::format_time_by_reference' );

		$values['date_entries'] = self::get_entry_ids_for_date( $values );
		if ( empty( $values['date_entries'] ) ) {
			return;
		}

		$used_times = self::get_used_times_for_entries( $values );
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

	private static function get_entry_ids_for_date( $values ) {
		$query = array( 'meta_value' => $values['date'] );
		FrmProEntryMeta::add_field_to_query( $values['date_key'], $query );

		return FrmEntryMeta::getEntryIds( $query );
	}

	private static function get_used_times_for_entries( $values ) {
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

	public static function validate_time_field( &$errors, $field, $value ) {
		if ( $field->type != 'time' ) {
			return;
		}

		$is_empty = ! is_array( $value ) && trim( $value ) == '';
		if ( $field->required == '1' && $is_empty ) {
			$errors[ 'field' . $field->temp_id ] = FrmFieldsHelper::get_error_msg( $field, 'blank' );
		} elseif ( ! $is_empty && ! self::in_time_range( $value, $field ) ) {
			$errors[ 'field' . $field->temp_id ] = FrmFieldsHelper::get_error_msg( $field, 'invalid' );
		}
	}

	private static function in_time_range( $value, $field ) {
		$values = $field->field_options;
		self::fill_start_end_times( $values );

		$value = FrmProAppHelper::format_time( $value );
		return $value >= $values['start_time'] && $value <= $values['end_time'];
	}
}
