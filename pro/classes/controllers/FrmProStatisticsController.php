<?php

class FrmProStatisticsController {

	/**
	 * Returns stats requested through the [frm-stats] shortcode
	 *
	 * @param array $atts
	 * @return string
	 */
	public static function stats_shortcode( $atts ) {
		self::convert_old_atts_to_new_atts( $atts );

		self::combine_defaults_and_user_defined_attributes( $atts );

		self::format_atts( $atts );

		if ( ! isset( $atts['id'] ) || ! $atts['id'] ) {
			return __( 'You must include a valid field id or key in your stats shortcode.', 'formidable-pro' );
		}

		return self::get_field_stats( $atts['id'], $atts );
	}

	/**
	 * Get the entry IDs for a field, operator, and value combination
	 *
	 * @param array $args
	 * @return array
	 */
	public static function get_field_matches( $args ) {
		$filter_args = self::get_filter_args( $args );

		if ( ! $filter_args['field'] ) {
			return $filter_args['entry_ids'];
		} else if ( $filter_args['after_where'] && ! $filter_args['entry_ids'] ) {
			return array();
		}

		return self::get_entry_ids_for_field_filter( $filter_args );
	}

	/**
	 * Flatten multi-dimensional arrays for stats and graphs
	 *
	 * @since 2.02.06
	 * @param object $field
	 * @param bool $save_other_key
	 * @param array $field_values
	 */
	public static function flatten_multi_dimensional_arrays_for_stats( $field, $save_other_key, &$field_values ) {
		$cleaned_values = array();

		foreach ( $field_values as $k => $i ) {
			$i = maybe_unserialize( $i );

			if ( ! is_array( $i ) ) {
				$cleaned_values[] = $i;
				continue;
			}

			if ( $field->type == 'address' || $field->type == 'credit_card' ) {
				$cleaned_values[] = implode( ' ', $i );
			} else {
				foreach ( $i as $i_key => $item_value ) {

					if ( $save_other_key && strpos( $i_key, 'other' ) !== false ) {
						// If this is an "other" option, keep key
						$cleaned_values[] = $i_key;
					} else {
						$cleaned_values[] = $item_value;
					}
				}
			}
		}

		$field_values = $cleaned_values;
	}

	/**
	 * Remove and convert deprecated attributes
	 *
	 * @since 2.02.06
	 * @param array $atts
	 */
	private static function convert_old_atts_to_new_atts( &$atts ) {
		if ( isset( $atts['entry_id'] ) ) {
			$atts['entry'] = $atts['entry_id'];
			unset( $atts['entry_id'] );
		}

		if ( isset( $atts['round'] ) ) {
			$atts['decimal'] = $atts['round'];
			unset( $atts['round'] );
		}

		if ( isset( $atts['value'] ) ) {
			if ( isset( $atts['id'] ) ) {
				$field_id = $atts['id'];
				$atts[ $field_id ] = $atts['value'];
			}
			unset( $atts['value'] );
		}
	}

	/**
	 * Combine the default attributes with the user-defined attributes
	 *
	 * @since 2.02.06
	 * @param array $atts
	 */
	private static function combine_defaults_and_user_defined_attributes( &$atts ) {
		$defaults = self::get_stats_defaults();

		$combined_atts = array();
		foreach ( $defaults as $k => $value ) {
			if ( isset( $atts[ $k ] ) ) {
				$combined_atts[ $k ] = $atts[ $k ];
				unset( $atts[ $k ] );
			} else if ( $value !== false ) {
				$combined_atts[ $k ] = $value;
			}
		}

		$combined_atts['filters'] = $atts;

		$atts = $combined_atts;
	}

	/**
	 * Get the default attributes for stats
	 *
	 * @since 2.02.06
	 * @return array
	 */
	private static function get_stats_defaults() {
		$defaults = array(
			'id' => false, //the ID of the field to show stats for
			'type' => 'total', //total, count, average, median, deviation, star, minimum, maximum, unique
			'user_id' => false, //limit the stat to a specific user id or "current"
			'limit' => false, //limit the number of entries used in this calculation
			'drafts' => 0, //don't include drafts by default
			'entry' => false,
			'thousands_sep' => false,
			'decimal' => 2, //how many decimals to include
			'dec_point' => false,
			//any other field ID in the form => the value it should be equal to
		);

		return $defaults;
	}

	/**
	 * Format the attributes for stats
	 *
	 * @since 2.02.06
	 * @param array $atts
	 */
	private static function format_atts( &$atts ) {
		if ( ! isset( $atts['id'] ) || ! $atts['id'] ) {
			return;
		} else {
			$atts['id'] = self::maybe_convert_field_key_to_id( $atts['id'] );
		}

		if ( isset( $atts['user_id'] ) ) {
			$atts['user_id'] = FrmAppHelper::get_user_id_param( $atts['user_id'] );
		}

		if ( isset( $atts['entry'] ) ) {
			$atts['entry_ids'] = self::maybe_convert_entry_keys_to_ids( $atts['entry'] );
		}
	}

	/**
	 * Convert entry keys to IDs
	 *
	 * @since 2.02.06
	 * @param string $entry_keys
	 * @return array
	 */
	private static function maybe_convert_entry_keys_to_ids( $entry_keys ) {
		$entry_keys = explode( ',', $entry_keys );

		$entry_ids = array();
		foreach ( $entry_keys as $key ) {
			if ( is_numeric( $key ) ) {
				$entry_id = $key;
			} else {
				$entry_id = FrmEntry::get_id_by_key( $key );
			}

			if ( $entry_id ) {
				$entry_ids[] = $entry_id;
			}
		}

		return $entry_ids;
	}

	/**
	 * Convert a field key to an ID
	 *
	 * @since 2.02.06
	 * @param string $key
	 * @return int|string
	 */
	private static function maybe_convert_field_key_to_id( $key ) {
		if ( ! is_numeric( $key ) ) {
			$id = FrmField::get_id_by_key( $key );
		} else {
			$id = $key;
		}

		return $id;
	}

	/**
	 * Get field statistic
	 *
	 * @since 2.02.06
	 * @param int $id
	 * @param array $atts
	 * @return int|string|float
	 */
	private static function get_field_stats( $id, $atts ) {
		$field = FrmField::getOne( $id );

		if ( ! $field ) {
			return 0;
		}

		$meta_values = self::get_meta_values_for_single_field( $field, $atts );
		if ( empty( $meta_values ) ) {
			$statistic = 0;
		} else {
			$statistic = self::get_stats_from_meta_values( $atts, $meta_values );
		}

		if ( 'star' === $atts['type'] ) {
			$statistic = self::get_stars( $field, $statistic );
		}

		return $statistic;
	}

	/**
	 * Get the meta values for a single stats field
	 *
	 * @since 2.02.06
	 * @param object $field
	 * @param array $atts
	 * @return array
	 */
	private static function get_meta_values_for_single_field( $field, $atts ) {
		$atts['form_id'] = $field->form_id;
		$atts['form_posts'] = self::get_form_posts_for_statistics( $atts );

		self::check_field_filters( $atts );

		// If there are field filters and entry IDs is empty, stop now
		if ( ! empty( $atts['filters'] ) && empty( $atts['entry_ids'] ) ) {
			return array();
		}

		$meta_args = self::package_filtering_arguments_for_query( $atts );

		$field_values = FrmProEntryMeta::get_all_metas_for_field( $field, $meta_args );

		self::format_field_values( $field, $atts, $field_values );

		return $field_values;
	}

	/**
	 * Get the stars for a given statistic
	 *
	 * @since 2.02.06
	 * @param object $field
	 * @param int $stat
	 * @return string
	 */
	private static function get_stars( $field, $value ) {
		$atts = array( 'html' => true );

		// force star field type to get stats
		$field->type = 'star';

		return FrmFieldsHelper::get_unfiltered_display_value( compact( 'value', 'field', 'atts' ) );
	}

	/**
	 * Calculate a count, total, etc from a field's meta values
	 *
	 * @since 2.02.06
	 * @param array $atts
	 * @param array $meta_values
	 * @return int
	 */
	private static function get_stats_from_meta_values( $atts, $meta_values ) {
		$count = count( $meta_values );

		if ( $atts['type'] != 'count' ) {
			$total = array_sum( $meta_values );
		} else {
			$total = 0;
		}

		switch ( $atts['type'] ) {
			case 'average':
			case 'mean':
			case 'star':
				$stat = ( $total / $count );
				break;
			case 'median':
				$stat = self::calculate_median( $meta_values );
				break;
			case 'deviation':
				$mean = ( $total / $count );
				$stat = 0.0;
				foreach ( $meta_values as $i ) {
					$stat += pow( $i - $mean, 2 );
				}

				if ( $count > 1 ) {
					$stat /= ( $count - 1 );

					$stat = sqrt( $stat );
				} else {
					$stat = 0;
				}
				break;
			case 'minimum':
				$stat = min( $meta_values );
				break;
			case 'maximum':
				$stat = max( $meta_values );
				break;
			case 'count':
				$stat = $count;
				break;
			case 'unique':
				$stat = array_unique( $meta_values );
				$stat = count( $stat );
				break;
			case 'total':
			default:
				$stat = $total;
		}

		return self::get_formatted_statistic( $atts, $stat );
	}

	/**
	 * Calculate the median from an array of values
	 *
	 * @since 2.03.08
	 *
	 * @param array $meta_values
	 *
	 * @return float
	 */
	public static function calculate_median( $meta_values ) {
		$count = count( $meta_values );
		rsort( $meta_values );

		$middle_index = (int) floor( $count / 2 );

		if ( $count % 2 > 0 ) {
			// Odd number of values
			$median = (float) $meta_values[ $middle_index ];
		} else {
			// Even number of values, calculate avg of 2 medians
			$low_middle  = $meta_values[ $middle_index - 1 ];
			$high_middle = $meta_values[ $middle_index ];
			$median      = (float) ( $low_middle + $high_middle ) / 2;
		}

		return $median;
	}

	/**
	 * Get the formatted statistic value
	 *
	 * @since 2.02.06
	 * @param array $atts
	 * @param float $stat
	 * @return float|string
	 */
	private static function get_formatted_statistic( $atts, $stat ) {
		if ( isset( $atts['thousands_sep'] ) || isset( $atts['dec_point'] ) ) {
			$dec_point = isset( $atts['dec_point'] ) ? $atts['dec_point'] : '.';
			$thousands_sep = isset( $atts['thousands_sep'] ) ? $atts['thousands_sep'] : ',';
			$statistic = number_format( $stat, $atts['decimal'], $dec_point, $thousands_sep );
		} else {
			$statistic = round( $stat, $atts['decimal'] );
		}

		return $statistic;
	}

	/**
	 * Get form posts
	 *
	 * @since 2.02.06
	 * @param array $atts
	 * @return mixed
	 */
	private static function get_form_posts_for_statistics( $atts ) {
		$where_post = array( 'form_id' => $atts['form_id'], 'post_id >' => 1 );

		if ( $atts['drafts'] != 'both' ) {
			$where_post['is_draft'] = $atts['drafts'];
		}

		if ( isset( $atts['user_id'] ) ) {
			$where_post['user_id'] = $atts['user_id'];
		}

		return FrmDb::get_results( 'frm_items', $where_post, 'id,post_id' );
	}

	/**
	 * Package the filtering arguments for a field meta query
	 *
	 * @since 2.02.06
	 * @param array $atts
	 * @return array
	 */
	private static function package_filtering_arguments_for_query( $atts ) {
		$pass_args = array(
			'entry_ids' => 'entry_ids',
			'user_id' => 'user_id',
			'created_at_greater_than' => 'start_date',
			'created_at_less_than' => 'end_date',
			'drafts' => 'is_draft',
			'form_id' => 'form_id',
			'limit' => 'limit',
		);

		$meta_args = array();
		foreach ( $pass_args as $atts_key => $arg_key ) {
			if ( isset( $atts[ $atts_key ] ) ) {
				$meta_args[ $arg_key ] = $atts[ $atts_key ];
			}
		}

		$meta_args['order_by'] = 'e.created_at DESC';

		return $meta_args;
	}

	/**
	 * Check field filters in the stats shortcode
	 *
	 * @since 2.02.06
	 * TODO: update this so old filters are converted to new filters
	 * @param array $atts
	 */
	private static function check_field_filters( &$atts ) {
		if ( ! empty( $atts['filters'] ) ) {

			if ( ! isset( $atts['entry_ids'] ) ) {
				$atts['entry_ids'] = array();
				$after_where = false;
			} else {
				$after_where = true;
			}

			foreach ( $atts['filters'] as $orig_f => $val ) {
				// Replace HTML entities with less than/greater than symbols
				$val = str_replace( array( '&gt;', '&lt;' ), array( '>', '<' ), $val );

				// If first character is a quote, but the last character is not a quote
				if ( ( strpos( $val, '"' ) === 0 && substr( $val, -1 ) != '"' ) || ( strpos( $val, "'" ) === 0 && substr( $val, -1 ) != "'" ) ) {
					//parse atts back together if they were broken at spaces
					$next_val = array( 'char' => substr( $val, 0, 1 ), 'val' => $val );
					continue;
					// If we don't have a previous value that needs to be parsed back together
				} else if ( ! isset( $next_val ) ) {
					$temp = FrmAppHelper::replace_quotes( $val );
					foreach ( array( '"', "'" ) as $q ) {
						// Check if <" or >" exists in string and string does not end with ".
						if ( substr( $temp, -1 ) != $q && ( strpos( $temp, '<' . $q ) || strpos( $temp, '>' . $q ) ) ) {
							$next_val = array( 'char' => $q, 'val' => $val );
							$cont = true;
						}
						unset( $q );
					}
					unset( $temp );

					if ( isset( $cont ) ) {
						unset( $cont );
						continue;
					}
				}

				// If we have a previous value saved that needs to be parsed back together (due to WordPress pullling it apart)
				if ( isset( $next_val ) ) {
					if ( substr( FrmAppHelper::replace_quotes( $val ), -1 ) == $next_val['char'] ) {
						$val = $next_val['val'] . ' ' . $val;
						unset( $next_val );
					} else {
						$next_val['val'] .= ' ' . $val;
						continue;
					}
				}

				$pass_args = array(
					'orig_f' => $orig_f,
					'val' => $val,
					'entry_ids' => $atts['entry_ids'],
					'form_id' => $atts['form_id'],
					'form_posts' => $atts['form_posts'],
					'after_where' => $after_where,
					'drafts' => $atts['drafts'],
				);

				$atts['entry_ids'] = self::get_field_matches( $pass_args );
				$after_where = true;

				if ( ! $atts['entry_ids'] ) {
					return;
				}
			}
		}
	}

	/**
	 * Package the arguments needed for a field filter
	 *
	 * @since 2.02.05
	 * @param array $args
	 * @return array
	 */
	private static function get_filter_args( $args ) {
		$filter_args = array(
			'field' => '',
			'operator' => '=',
			'value' => $args['val'],
			'form_id' => $args['form_id'],
			'entry_ids' => $args['entry_ids'],
			'after_where' => $args['after_where'],
			'drafts' => $args['drafts'],
			'form_posts' => $args['form_posts'],
		);

		$f = $args['orig_f'];

		if ( strpos( $f, '_not_equal' ) !== false ) {
			self::get_not_equal_filter_args( $f, $filter_args );

		} else if ( strpos( $f, '_less_than_or_equal_to' ) !== false ) {
			self::get_less_than_or_equal_to_filter_args( $f, $filter_args );

		} else if ( strpos( $f, '_less_than' ) !== false ) {
			self::get_less_than_filter_args( $f, $filter_args );

		} else if ( strpos( $f, '_greater_than_or_equal_to' ) !== false ) {
			self::get_greater_than_or_equal_to_filter_args( $f, $filter_args );

		} else if ( strpos( $f, '_greater_than' ) !== false ) {
			self::get_greater_than_filter_args( $f, $filter_args );

		} else if ( strpos( $f, '_contains' ) !== false ) {
			self::get_contains_filter_args( $f, $filter_args );

		} else if ( strpos( $f, '_does_not_contain' ) !== false ) {
			self::get_does_not_contain_filter_args( $f, $filter_args );

		} else if ( is_numeric( $f ) && $f <= 10 ) {
			// If using <, >, <=, >=, !=. $f will count up for certain atts
			self::get_filter_args_for_deprecated_field_filters( $filter_args );

		} else {
			// $f is field ID, key, updated_at, or created_at
			self::get_equal_to_filter_args( $f, $filter_args );
		}

		self::convert_filter_field_key_to_id( $filter_args );

		self::prepare_filter_value( $filter_args );

		return $filter_args;
	}

	/**
	 * Get the filter arguments for a not_equal filter
	 *
	 * @since 2.02.05
	 * @param string $f
	 * @param array $filter_args
	 */
	private static function get_not_equal_filter_args( $f, &$filter_args ) {
		$filter_args['field'] = str_replace( '_not_equal', '', $f );
		$filter_args['operator'] = '!=';
		self::maybe_get_all_entry_ids_for_form( $filter_args );
	}

	/**
	 * Get the filter arguments for a less_than_or_equal_to filter
	 *
	 * @since 2.02.11
	 * @param string $f
	 * @param array $filter_args
	 */
	private static function get_less_than_or_equal_to_filter_args( $f, &$filter_args ) {
		$filter_args['field'] = str_replace( '_less_than_or_equal_to', '', $f );
		$filter_args['operator'] = '<=';
	}

	/**
	 * Get the filter arguments for a less_than filter
	 *
	 * @since 2.02.05
	 * @param string $f
	 * @param array $filter_args
	 */
	private static function get_less_than_filter_args( $f, &$filter_args ) {
		$filter_args['field'] = str_replace( '_less_than', '', $f );
		$filter_args['operator'] = '<';
	}

	/**
	 * Get the filter arguments for a greater_than_or_equal_to filter
	 *
	 * @since 2.02.11
	 * @param string $f
	 * @param array $filter_args
	 */
	private static function get_greater_than_or_equal_to_filter_args( $f, &$filter_args ) {
		$filter_args['field'] = str_replace( '_greater_than_or_equal_to', '', $f );
		$filter_args['operator'] = '>=';
	}

	/**
	 * Get the filter arguments for a greater_than filter
	 *
	 * @since 2.02.05
	 * @param string $f
	 * @param array $filter_args
	 */
	private static function get_greater_than_filter_args( $f, &$filter_args ) {
		$filter_args['field'] = str_replace( '_greater_than', '', $f );
		$filter_args['operator'] = '>';
	}

	/**
	 * Get the filter arguments for a like filter
	 *
	 * @since 2.02.05
	 * @param string $f
	 * @param array $filter_args
	 */
	private static function get_contains_filter_args( $f, &$filter_args ) {
		$filter_args['field'] = str_replace( '_contains', '', $f );
		$filter_args['operator'] = 'LIKE';
	}

	/**
	 * Get the filter arguments for a like filter
	 *
	 * @since 2.02.13
	 * @param string $f
	 * @param array $filter_args
	 */
	private static function get_does_not_contain_filter_args( $f, &$filter_args ) {
		$filter_args['field'] = str_replace( '_does_not_contain', '', $f );
		$filter_args['operator'] = 'NOT LIKE';
		self::maybe_get_all_entry_ids_for_form( $filter_args );
	}

	/**
	 * Get the filter arguments for an x=value filter
	 *
	 * @since 2.02.05
	 * @param string $f
	 * @param array $filter_args
	 */
	private static function get_equal_to_filter_args( $f, &$filter_args ) {
		$filter_args['field'] = $f;

		if ( $filter_args['value'] === '' ) {
			self::maybe_get_all_entry_ids_for_form( $filter_args );
		}
	}

	/**
	 * Convert a filter field key to an ID
	 *
	 * @since 2.02.05
	 * @param array $filter_args
	 */
	private static function convert_filter_field_key_to_id( &$filter_args ) {
		if ( ! is_numeric( $filter_args['field'] ) && ! in_array( $filter_args['field'], array( 'created_at', 'updated_at' ) ) ) {
			$filter_args['field'] = FrmField::get_id_by_key( $filter_args['field'] );
		}
	}

	/**
	 * Prepare a filter value
	 *
	 * @since 2.02.05
	 * @param array $filter_args
	 */
	private static function prepare_filter_value( &$filter_args ) {
		$filter_args['value'] = FrmAppHelper::replace_quotes( $filter_args['value'] );

		if ( in_array( $filter_args['field'], array( 'created_at', 'updated_at' ) ) ) {
			$filter_args['value'] = str_replace( array( '"', "'" ), '', $filter_args['value'] );
			$filter_args['value'] = date( 'Y-m-d H:i:s', strtotime( $filter_args['value'] ) );
			$filter_args['value'] = get_gmt_from_date( $filter_args['value'] );
		} else {
			$filter_args['value'] = trim( trim( $filter_args['value'], "'" ), '"' );
		}
	}

	/**
	 * Get the filter arguments for deprecated stats parameters
	 *
	 * @since 2.02.05
	 * @param array $filter_args
	 */
	private static function get_filter_args_for_deprecated_field_filters( &$filter_args ) {
		$lpos = strpos( $filter_args['value'], '<' );
		$gpos = strpos( $filter_args['value'], '>' );
		$not_pos = strpos( $filter_args['value'], '!=' );
		$dash_pos = strpos( $filter_args['value'], '-' );

		if ( $not_pos !== false || $filter_args['value'] === '' ) {
			self::maybe_get_all_entry_ids_for_form( $filter_args );
		}

		if ( $not_pos !== false ) {
			// Not equal
			$filter_args['operator'] = '!=';

			$str = explode( $filter_args['operator'], $filter_args['value'] );

			$filter_args['field'] = $str[0];
			$filter_args['value'] = $str[1];

		} else if ( $lpos !== false || $gpos !== false ) {
			// Greater than or less than
			$filter_args['operator'] = ( ( $gpos !== false && $lpos !== false && $lpos > $gpos ) || $lpos === false ) ? '>' : '<';
			$str = explode( $filter_args['operator'], $filter_args['value'] );

			if ( count( $str ) == 2 ) {
				$filter_args['field'] = $str[0];
				$filter_args['value'] = $str[1];
			} else if ( count( $str ) == 3 ) {
				//3 parts assumes a structure like '-1 month'<255<'1 month'
				$pass_args = $filter_args;
				$pass_args['orig_f'] = 0;
				$pass_args['val'] = str_replace( $str[0] . $filter_args['operator'], '', $filter_args['value'] );

				$filter_args['entry_ids'] = self::get_field_matches( $pass_args );
				$filter_args['after_where'] = true;
				$filter_args['field'] = $str[1];
				$filter_args['value'] = $str[0];
				$filter_args['operator'] = ( $filter_args['operator'] == '<' ) ? '>' : '<';
			}

			if ( strpos( $filter_args['value'], '=' ) === 0 ) {
				$filter_args['operator'] .= '=';
				$filter_args['value'] = substr( $filter_args['value'], 1 );
			}
		} else if ( $dash_pos !== false && strpos( $filter_args['value'], '=' ) !== false ) {
			// Field key contains dash
			// If field key contains a dash, then it won't be put in as $f automatically (WordPress quirk maybe?)

			$str = explode( '=', $filter_args['value'] );
			$filter_args['field'] = $str[0];
			$filter_args['value'] = $str[1];
		}
	}

	/**
	 * Get all the entry IDs for a form if entry IDs is empty and after_where is false
	 *
	 * @since 2.02.05
	 * @param array $args
	 */
	private static function maybe_get_all_entry_ids_for_form( &$args ) {
		if ( empty( $args['entry_ids'] ) && $args['after_where'] == 0 ) {

			$query = array( 'form_id' => $args['form_id'] );
			if ( $args['drafts'] != 'both' ) {
				$query['is_draft'] = $args['drafts'];
			}

			$args['entry_ids'] = FrmDb::get_col( 'frm_items', $query );
		}
	}

	/**
	 * Get the entry IDs for a field/column filter
	 *
	 * @since 2.02.05
	 * @param array $filter_args
	 * @return array
	 */
	private static function get_entry_ids_for_field_filter( $filter_args ) {
		if ( in_array( $filter_args['field'], array( 'created_at', 'updated_at' ) ) ) {

			$where = array(
				'form_id' => $filter_args['form_id'],
				$filter_args['field'] . FrmDb::append_where_is( $filter_args['operator'] ) => $filter_args['value'],
			);

			if ( $filter_args['entry_ids'] ) {
				$where['id'] = $filter_args['entry_ids'];
			}

			$entry_ids = FrmDb::get_col( 'frm_items', $where );
		} else {
			$where_atts = apply_filters( 'frm_stats_where', array( 'where_is' => $filter_args['operator'], 'where_val' => $filter_args['value'] ), $filter_args );

			$pass_args = array(
				'where_opt' => $filter_args['field'],
				'where_is' => $where_atts['where_is'],
				'where_val' => $where_atts['where_val'],
				'form_id' => $filter_args['form_id'],
				'form_posts' => $filter_args['form_posts'],
				'after_where' => $filter_args['after_where'],
				'drafts' => $filter_args['drafts'],
			);

			$entry_ids = FrmProAppHelper::filter_where( $filter_args['entry_ids'], $pass_args );
		}

		return $entry_ids;
	}

	/**
	 * Format the retrieved meta values for a field
	 *
	 * @since 2.02.06
	 * @param object $field
	 * @param array $atts
	 * @param array $field_values
	 */
	private static function format_field_values( $field, $atts, &$field_values ) {
		if ( ! $field_values ) {
			return;
		}

		// Flatten multi-dimensional array
		if ( $atts['type'] != 'count' && FrmField::is_field_with_multiple_values( $field ) ) {
			self::flatten_multi_dimensional_arrays_for_stats( $field, false, $field_values );
		}

		$field_values = stripslashes_deep( $field_values );
	}
}
