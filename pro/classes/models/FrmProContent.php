<?php

class FrmProContent {
	
	public static function replace_shortcodes( $content, $entry, $shortcodes, $display = false, $show = 'one', $odd = '', $args = array() ) {

		$args['odd'] = $odd;
		$args['show'] = $show;

		foreach ( $shortcodes[0] as $short_key => $tag ) {
			$previous_content = $content;
			self::replace_single_shortcode( $shortcodes, $short_key, $tag, $entry, $display, $args, $content );

			$has_run = ( $content !== $previous_content );
			if ( $has_run ) {
				$shortcodes[0][ $short_key ] = '';
			}
			unset( $previous_content );
		}

		if ( ! empty( $shortcodes[0] ) ) {
			$content = FrmFieldsHelper::replace_content_shortcodes( $content, $entry, $shortcodes );
		}

		return $content;
	}

	public static function replace_single_shortcode( $shortcodes, $short_key, $tag, $entry, $display, $args, &$content ) {
		$conditional = preg_match( '/^\[if/s', $shortcodes[0][ $short_key ] ) ? true : false;
		$foreach = preg_match( '/^\[foreach/s', $shortcodes[0][ $short_key ] ) ? true : false;
		$atts = FrmShortcodeHelper::get_shortcode_attribute_array( $shortcodes[3][ $short_key ] );

		$tag = FrmFieldsHelper::get_shortcode_tag( $shortcodes, $short_key, compact('conditional', 'foreach') );
		if ( strpos( $tag, '-' ) ) {
			$switch_tags = array(
				'post-id', 'created-at', 'updated-at',
				'created-by', 'updated-by', 'parent-id',
				'is-draft',
			);
			if ( in_array( $tag, $switch_tags ) ) {
				$tag = str_replace('-', '_', $tag);
			}
			unset( $switch_tags );
		}

		$tags = array(
			'event_date', 'entry_count', 'detaillink', 'editlink', 'deletelink',
			'created_at', 'updated_at', 'created_by', 'updated_by',
			'evenodd', 'post_id', 'parent_id', 'id', 'is_draft',
		);

		if ( in_array( $tag, $tags ) ) {
			$args['entry'] = $entry;
			$args['tag'] = $tag;
			$args['conditional'] = $conditional;
			$function = 'do_shortcode_' . $tag;
			self::$function( $content, $atts, $shortcodes, $short_key, $args, $display );
			return;
		}

		$field = FrmField::getOne( $tag );
		if ( ! $field ) {
			return;
		}

		if ( ! $foreach && ! $conditional && isset( $atts['show'] ) && ( $atts['show'] == 'field_label' || $atts['show'] == 'description' ) ) {
			// get the field label or description and return before any other checking
			$replace_with = ( $atts['show'] == 'field_label' ) ? $field->name : $field->description;
			$content = str_replace( $shortcodes[0][ $short_key ], $replace_with, $content );
			return;
		}

		$sep = isset( $atts['sep'] ) ? $atts['sep'] : ', ';

		if ( $field->form_id == $entry->form_id ) {
			$replace_with = FrmProEntryMetaHelper::get_post_or_meta_value( $entry, $field, $atts );
		} else {
			// get entry ids linked through repeat field or embeded form
			$child_entries = FrmProEntry::get_sub_entries( $entry->id, true );
			$replace_with = FrmProEntryMetaHelper::get_sub_meta_values( $child_entries, $field, $atts );
			$replace_with = FrmAppHelper::array_flatten( $replace_with );
		}

		$atts['entry_id'] = $entry->id;
		$atts['entry_key'] = $entry->item_key;
		$atts['post_id'] = $entry->post_id;

		self::maybe_get_show_from_array( $replace_with, $atts );

		$replace_with = apply_filters('frmpro_fields_replace_shortcodes', $replace_with, $tag, $atts, $field);

		if ( isset( $atts['show'] ) && $atts['show'] == 'count' ) {
			$replace_with = is_array( $replace_with ) ? count( $replace_with ) : ! empty( $replace_with );
		} else if ( is_array( $replace_with ) && ! $foreach ) {
			$keep_array = apply_filters( 'frm_keep_value_array', false, compact( 'field', 'replace_with' ) );
			$keep_array = apply_filters( 'frm_keep_' . $field->type . '_value_array', $keep_array, compact( 'field', 'replace_with' ) );

			if ( ! $keep_array && $field->type != 'file' ) {
				$replace_with = FrmAppHelper::array_flatten( $replace_with );
				$replace_with = implode( $sep, $replace_with );
			} else if ( empty( $replace_with ) ) {
				$replace_with = '';
			}
		}

		if ( $foreach ) {
			$atts['short_key'] = $shortcodes[0][ $short_key ];
			$args['display'] = $display;
			self::check_conditional_shortcode( $content, $replace_with, $atts, $tag, 'foreach', $args );
		} else if ( $conditional ) {
			$atts['short_key'] = $shortcodes[0][ $short_key ];
			self::check_conditional_shortcode( $content, $replace_with, $atts, $tag, 'if', array( 'field' => $field ) );
		} else {
			if ( empty( $replace_with ) && $replace_with != '0' ) {
				$replace_with = '';
				if ( $field->type == 'number' ) {
					$replace_with = '0';
				}
			} else {
				$replace_with = FrmFieldsHelper::get_display_value( $replace_with, $field, $atts );
			}

			self::trigger_shortcode_atts( $atts, $display, $args, $replace_with );
			$content = str_replace( $shortcodes[0][ $short_key ], $replace_with, $content );
		}
	}

	public static function replace_calendar_date_shortcode( $content, $date ) {
		preg_match_all( "/\[(calendar_date)\b(.*?)(?:(\/))?\]/s", $content, $matches, PREG_PATTERN_ORDER );
		if ( empty( $matches ) ) {
			return $content;
		}

		foreach ( $matches[0] as $short_key => $tag ) {
			$atts = FrmShortcodeHelper::get_shortcode_attribute_array( $matches[2][ $short_key ] );
			self::do_shortcode_event_date( $content, $atts, $matches, $short_key, array( 'event_date' => $date ) );
		}
		return $content;
	}

	public static function do_shortcode_event_date( &$content, $atts, $shortcodes, $short_key, $args ) {
		$event_date = '';
		if ( isset( $args['event_date'] ) ) {
			if ( ! isset( $atts['format'] ) ) {
				$atts['format'] = get_option('date_format');
			}
			$event_date = FrmProFieldsHelper::get_date( $args['event_date'], $atts['format'] );
		}
		$content = str_replace( $shortcodes[0][ $short_key ], $event_date, $content );
	}

	public static function do_shortcode_entry_count( &$content, $atts, $shortcodes, $short_key, $args ) {
		$content = str_replace( $shortcodes[0][ $short_key ], ( isset( $args['record_count'] ) ? $args['record_count'] : '' ), $content );
	}

	public static function do_shortcode_detaillink( &$content, $atts, $shortcodes, $short_key, $args, $display ) {
		if ( $display ) {
			$detail_link = self::get_detail_link( $args, $display );
			$content = str_replace( $shortcodes[0][ $short_key ], $detail_link, $content );
		}
	}

	private static function get_detail_link( $args, $display ) {
		if ( isset( $args['entry_key'] ) ) {
			$entry = $args;
		} else {
			$entry = (array) $args['entry'];
			$entry['entry_id'] = $entry['id'];
			$entry['entry_key'] = $entry['item_key'];
		}

		if ( $entry['post_id'] ) {
			$detail_link = get_permalink( $entry['post_id'] );
		} else {
			$param_value = ( $display->frm_type == 'id' ) ? $entry['entry_id'] : $entry['entry_key'];
			$param = ( isset( $display->frm_param ) && ! empty( $display->frm_param ) ) ? $display->frm_param : 'entry';
			$detail_link = self::get_pretty_url( compact( 'param', 'param_value' ) );
		}

		return $detail_link;
	}

	/*
	 * Make the view urls pretty
	 */
	public static function get_pretty_url( $atts ) {
		global $post;
		$base_url = untrailingslashit( $post ? get_permalink( $post->ID ) : $_SERVER['REQUEST_URI'] );
		if ( ! is_front_page() && self::rewriting_on() ) {
			$url = $base_url . '/' . $atts['param'] . '/' . $atts['param_value'];
		} else {
			$url = esc_url_raw( add_query_arg( $atts['param'], $atts['param_value'], $base_url ) );
		}

		return $url;
	}

	private static function rewriting_on() {
		$permalink_structure = get_option('permalink_structure');
		return ( ! empty( $permalink_structure ) );
	}

	public static function add_rewrite_endpoint() {
		$rewrite_params = self::get_rewrite_params();
		if ( ! empty( $rewrite_params ) ) {
			foreach ( $rewrite_params as $param ) {
				add_rewrite_endpoint( $param, EP_PERMALINK | EP_PAGES );
			}
			add_action( 'request', 'FrmProContent::fix_home_page_query' );
		}
	}


	/**
	 * This is a workaround for a bug in WordPress Core
	 * https://core.trac.wordpress.org/ticket/23867
	 * @since 2.2.10
	 */
	public static function fix_home_page_query( $query ) {
		$rewrite_params = self::get_rewrite_params();
		$included_params = array_intersect( $rewrite_params, array_keys( $query ) );
		if ( ! empty( $included_params ) ) {
			foreach ( $included_params as $key ) {
				$_GET[ $key ] = $query[ $key ];
				unset( $query[ $key ] );
			}
		}

		return $query;
	}

	/*
	 * Get the detail link parameter names from every view
	 * @since 2.2.8
	 */
	private static function get_rewrite_params() {
		global $wpdb;
		$params = FrmDb::get_col( $wpdb->postmeta, array( 'meta_key' => 'frm_param' ), 'meta_value' );
		return array_filter( array_unique( $params ) );
	}

	public static function do_shortcode_editlink( &$content, $atts, $shortcodes, $short_key, $args ) {
		global $post;

		$replace_with = '';
		$link_text = isset( $atts['label'] ) ? $atts['label'] : false;
		if ( ! $link_text ) {
			$link_text = isset( $atts['link_text'] ) ? $atts['link_text'] : __( 'Edit');
		}

		$class = isset( $atts['class'] ) ? $atts['class'] : '';
		$page_id = isset( $atts['page_id'] ) ? $atts['page_id'] : ( $post ? $post->ID : 0 );

		if ( ( isset( $atts['location'] ) && $atts['location'] == 'front') || ( isset( $atts['prefix'] ) && ! empty( $atts['prefix'] ) ) || ( isset( $atts['page_id'] ) && ! empty( $atts['page_id'] ) ) ) {
			$edit_atts = $atts;
			$edit_atts['id'] = isset( $args['foreach_loop'] ) ? $args['entry']->parent_item_id : $args['entry']->id;
			$edit_atts['page_id'] = $page_id;

			$replace_with = FrmProEntriesController::entry_edit_link( $edit_atts );
		} else {
			if ( $args['entry']->post_id ) {
				$replace_with = get_edit_post_link( $args['entry']->post_id );
			} else if ( current_user_can('frm_edit_entries') ) {
				$replace_with = admin_url( 'admin.php?page=formidable-entries&frm_action=edit&id=' . $args['entry']->id );
			}

			if ( ! empty( $replace_with ) ) {
				$replace_with = '<a href="' . esc_url( $replace_with ) . '" class="frm_edit_link ' . esc_attr( $class ) . '">' . $link_text . '</a>';
			}

		}

		$content = str_replace( $shortcodes[0][ $short_key ], $replace_with, $content );
	}

	public static function do_shortcode_deletelink(&$content, $atts, $shortcodes, $short_key, $args) {
		global $post;

		$page_id = isset( $atts['page_id'] ) ? $atts['page_id'] : ( $post ? $post->ID : 0 );

		if ( ! isset( $atts['label'] ) ) {
			$atts['label'] = false;
		}
		$delete_atts = $atts;
		$delete_atts['id'] = $args['entry']->id;
		$delete_atts['page_id'] = $page_id;

		$replace_with = FrmProEntriesController::entry_delete_link( $delete_atts );

		$content = str_replace( $shortcodes[0][ $short_key ], $replace_with, $content );
	}

	public static function do_shortcode_evenodd( &$content, $atts, $shortcodes, $short_key, $args ) {
		$content = str_replace( $shortcodes[0][ $short_key ], $args['odd'], $content );
	}

	public static function do_shortcode_post_id( &$content, $atts, $shortcodes, $short_key, $args ) {
		$content = str_replace( $shortcodes[0][ $short_key ], $args['entry']->post_id, $content );
	}

	public static function do_shortcode_parent_id( &$content, $atts, $shortcodes, $short_key, $args ) {
		$content = str_replace( $shortcodes[0][ $short_key ], $args['entry']->parent_item_id, $content );
	}

	public static function do_shortcode_id( &$content, $atts, $shortcodes, $short_key, $args ) {
		$content = str_replace( $shortcodes[0][ $short_key ], $args['entry']->id, $content );
	}

	public static function do_shortcode_created_at( &$content, $atts, $shortcodes, $short_key, $args ) {
		if ( isset( $atts['format'] ) ) {
			$time_format = ' ';
		} else {
			$atts['format'] = get_option('date_format');
			$time_format = '';
		}

		if ( $args['conditional'] ) {
			$atts['short_key'] = $shortcodes[0][ $short_key ];
			self::check_conditional_shortcode( $content, $args['entry']->{$args['tag']}, $atts, $args['tag'] );
		} else {
			if ( isset( $atts['time_ago'] ) ) {
				$date = FrmAppHelper::human_time_diff( strtotime( $args['entry']->{$args['tag']} ), '', absint( $atts['time_ago'] ) );
			} else {
				$date = FrmAppHelper::get_formatted_time( $args['entry']->{$args['tag']}, $atts['format'], $time_format );
			}

			$content = str_replace( $shortcodes[0][ $short_key ], $date, $content );
		}
	}

	public static function do_shortcode_updated_at( &$content, $atts, $shortcodes, $short_key, $args ) {
		self::do_shortcode_created_at( $content, $atts, $shortcodes, $short_key, $args );
	}

	public static function do_shortcode_created_by( &$content, $atts, $shortcodes, $short_key, $args ) {
		$replace_with = FrmFieldsHelper::get_display_value( $args['entry']->{$args['tag']}, (object) array( 'type' => 'user_id'), $atts );

		if ( $args['conditional'] ) {
			$atts['short_key'] = $shortcodes[0][ $short_key ];
			self::check_conditional_shortcode( $content, $args['entry']->{$args['tag']}, $atts, $args['tag'] );
		} else {
			$content = str_replace( $shortcodes[0][ $short_key ], $replace_with, $content );
		}
	}

	public static function do_shortcode_updated_by( &$content, $atts, $shortcodes, $short_key, $args ) {
		self::do_shortcode_created_by( $content, $atts, $shortcodes, $short_key, $args );
	}


	/**
	 * Process the is_draft shortcode
	 *
	 * @since 2.0.22
	 * @param string $content
	 * @param array $atts
	 * @param array $shortcodes
	 * @param string $short_key
	 * @param array $args
	 */
	public static function do_shortcode_is_draft( &$content, $atts, $shortcodes, $short_key, $args ) {
		if ( $args['conditional'] ) {
			if ( empty( $atts ) ) {
				$atts['equals'] = 1;
			}
			$atts['short_key'] = $shortcodes[0][ $short_key ];

			self::check_conditional_shortcode( $content, $args['entry']->is_draft, $atts, 'is_draft' );
		} else {
			$content = str_replace( $shortcodes[0][ $short_key ], $args['entry']->is_draft, $content );
		}
	}

	/**
	 * @since 2.0.23
	 * when a value is saved as an array, allow show=something to
	 * return a specified value from the array
	 */
	private static function maybe_get_show_from_array( &$replace_with, $atts ) {
		if ( is_array( $replace_with ) && isset( $atts['show'] ) ) {
			if ( isset( $replace_with[ $atts['show'] ] ) ) {
				$replace_with = $replace_with[ $atts['show'] ];
			} else if ( isset( $atts['blank'] ) && $atts['blank'] ) {
				$replace_with = '';
			}
		}
	}

	public static function check_conditional_shortcode( &$content, $replace_with, $atts, $tag, $condition = 'if', $args = array() ) {
		$defaults = array( 'field' => false);
		$args = wp_parse_args( $args, $defaults );

		if ( 'if' == $condition ) {
			$replace_with = self::conditional_replace_with_value( $replace_with, $atts, $args['field'], $tag );
			$replace_with = apply_filters( 'frm_conditional_value', $replace_with, $atts, $args['field'], $tag );
		}

		$start_pos = strpos( $content, $atts['short_key'] );

		// Replace identical conditional and foreach shortcodes in this loop
		while( $start_pos !== false ) {

			$start_pos_len = strlen( $atts['short_key'] );
			$end_pos = strpos( $content, '[/' . $condition . ' ' . $tag . ']', $start_pos );
			$end_pos_len = strlen( '[/' . $condition . ' ' . $tag . ']' );

			if ( $end_pos === false ) {
				$end_pos = strpos( $content, '[/' . $condition . ']', $start_pos );
				$end_pos_len = strlen( '[/' . $condition . ']' );

				if ( $end_pos === false ) {
					return;
				}
			}

			$total_len = ( $end_pos + $end_pos_len ) - $start_pos;

			if ( $replace_with === ''    ) {
				$content = substr_replace( $content, '', $start_pos, $total_len );
			} else if ( 'foreach' == $condition ) {
				$content_len = $end_pos - ( $start_pos + $start_pos_len );
				$repeat_content = substr( $content, $start_pos + $start_pos_len, $content_len );
				self::foreach_shortcode( $replace_with, $args, $repeat_content );
				$content = substr_replace( $content, $repeat_content, $start_pos, $total_len );
			} else {
				$content = substr_replace( $content, '', $end_pos, $end_pos_len );
				$content = substr_replace( $content, '', $start_pos, $start_pos_len );
			}

			$start_pos = strpos( $content, $atts['short_key'] );
		}
	}

	/**
	 * Loop through each entry linked through a repeating field when using [foreach]
	 */
	public static function foreach_shortcode( $replace_with, $args, &$repeat_content ) {
		$foreach_content = '';

		$sub_entries = is_array( $replace_with ) ? $replace_with : explode( ',', $replace_with );
		foreach ( $sub_entries as $sub_entry ) {
			$sub_entry = trim( $sub_entry );
			if ( ! is_numeric( $sub_entry ) ) {
				continue;
			}

			$entry = FrmEntry::getOne( $sub_entry );
			if ( ! $entry ) {
				continue;
			}

			$args['foreach_loop'] = true;

			$shortcodes = FrmProDisplaysHelper::get_shortcodes( $repeat_content, $entry->form_id );
			$repeating_content = $repeat_content;
			foreach ( $shortcodes[0] as $short_key => $tag ) {
				self::replace_single_shortcode( $shortcodes, $short_key, $tag, $entry, $args['display'], $args, $repeating_content );
			}
			$foreach_content .= $repeating_content;
		}

		$repeat_content = $foreach_content;
	}

	public static function conditional_replace_with_value( $replace_with, $atts, $field, $tag ) {
		$conditions = array(
			'equals', 'not_equal',
			'like', 'not_like',
			'less_than', 'greater_than',
		);

		if ( $field && $field->type == 'data' ) {
			$old_replace_with = $replace_with;

			// Only get the displayed value if it hasn't been set yet
			if ( is_numeric( $replace_with ) || is_numeric( str_replace( array( ',', ' '), array( '', '' ), $replace_with ) ) || is_array( $replace_with ) ) {
				$replace_with = FrmFieldsHelper::get_display_value( $replace_with, $field, $atts );
				if ( $old_replace_with == $replace_with ) {
					$replace_with = '';
				}
			}
		} else if ( ( $field && $field->type == 'user_id' ) || in_array( $tag, array( 'updated_by', 'created_by') ) ) {
			// check if conditional is for current user
			if ( isset( $atts['equals'] ) && $atts['equals'] == 'current' ) {
				$atts['equals'] = get_current_user_id();
			}

			if ( isset( $atts['not_equal'] ) && $atts['not_equal'] == 'current' ) {
				$atts['not_equal'] = get_current_user_id();
			}
		} elseif ( self::is_timestamp_tag( $tag ) || ( $field && $field->type == 'date' ) ) {
			self::prepare_date_for_eval( $conditions, $tag, $atts );
		} elseif ( $field && $field->type == 'time' ) {
			$formatted_time = false;
			foreach ( $conditions as $att_name ) {
				if ( isset( $atts[ $att_name ] ) && $atts[ $att_name ] != '' ) {
					if ( strtolower( $atts[ $att_name ] ) == 'now' ) {
						$atts[ $att_name ] = FrmProAppHelper::get_date( 'H:i' );
					} else {
						$atts[ $att_name ] = date( 'H:i', strtotime( $atts[ $att_name ] ) );
					}

					if ( ! $formatted_time ) {
						$replace_with = FrmProAppHelper::format_time( $replace_with, 'H:i' );
						$formatted_time = true;
					}
				}
			}
		}

		self::eval_conditions( $conditions, $atts, $replace_with, $field );

		return $replace_with;
	}

	private static function is_timestamp_tag( $tag ) {
		return preg_match( '/^(created[-|_]at|updated[-|_]at)$/', $tag );
	}

	private static function prepare_date_for_eval( $conditions, $tag, &$atts ) {
		foreach ( $conditions as $att_name ) {
			if ( isset( $atts[ $att_name ] ) && $atts[ $att_name ] != '' && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', trim( $atts[ $att_name ] ) ) ) {
				if ( self::is_timestamp_tag( $tag ) ) {
					self::get_gmt_for_filter( $att_name, $atts[ $att_name ] );
				} elseif ( $atts[ $att_name ] == 'NOW' ) {
					$atts[ $att_name ] = FrmProAppHelper::get_date( 'Y-m-d' );
				} else {
					$atts[ $att_name ] = date( 'Y-m-d', strtotime( $atts[ $att_name ] ) );
				}
			}
			unset( $att_name );
		}
	}

	public static function get_gmt_for_filter( $compare, &$where_val ) {
		$original_value = $where_val;

		if ( $where_val == 'NOW' ) {
			$where_val = current_time( 'mysql', 1 );
		}

		$compare = strtolower( $compare );
		if ( strpos( $compare, 'like' ) === false ) {
			$where_val = date( 'Y-m-d H:i:s', strtotime( $where_val ) );

			// If using less than or equal to, set the time to the end of the day
			if ( $compare == '<=' || $compare == 'less_than' ) {
				$where_val = str_replace( '00:00:00', '23:59:59', $where_val );
			}

			// Convert date to GMT since that is the format in the DB
			if ( strpos( $original_value, 'hour' ) === false ) {
				$where_val = get_gmt_from_date( $where_val );
			}
		}
	}

	private static function eval_conditions( $conditions, $atts, &$replace_with, $field ) {
		foreach ( $conditions as $condition ) {
			if ( ! isset( $atts[ $condition ] ) ) {
				continue;
			}

			$function = 'eval_' . $condition . '_condition';
			self::$function( $atts, $replace_with, $field );
		}
	}

	private static function eval_equals_condition( $atts, &$replace_with, $field ) {
		if ( $replace_with != $atts['equals'] ) {
			if ( $field && $field->type == 'data' ) {
				$replace_with = FrmFieldsHelper::get_display_value( $replace_with, $field, $atts );
				if ( $replace_with != $atts['equals'] ) {
					$replace_with = '';
				}
			} else if ( isset( $field->field_options['post_field'] ) && $field->field_options['post_field'] == 'post_category' ) {
				$cats = explode( ', ', $replace_with );
				$replace_with = '';
				foreach ( $cats as $cat ) {
					if ( $atts['equals'] == strip_tags( $cat ) ) {
						$replace_with = true;
						return;
					}
				}
			} else {
				$replace_with = '';
			}
		} else if ( $atts['equals'] == '' && $replace_with == '' ) {
			//if the field is blank, give it a value
			$replace_with = true;
		}
	}

	private static function eval_not_equal_condition( $atts, &$replace_with, $field ) {
		if ( $replace_with == $atts['not_equal'] ) {
			$replace_with = '';
		} else if ( $replace_with == '' && $atts['not_equal'] !== '' ) {
			$replace_with = true;
		} else if ( ! empty( $replace_with ) && isset( $field->field_options['post_field'] ) && $field->field_options['post_field'] == 'post_category' ) {
			$cats = explode( ', ', $replace_with );
			foreach ( $cats as $cat ) {
				if ( $atts['not_equal'] == strip_tags( $cat ) ) {
					$replace_with = '';
					return;
				}

				unset( $cat );
			}
		}
	}

	private static function eval_like_condition( $atts, &$replace_with ) {
		if ( $atts['like'] == '' ) {
			return;
		}

		if ( stripos( $replace_with, $atts['like'] ) === false ) {
			$replace_with = '';
		}
	}

	private static function eval_not_like_condition( $atts, &$replace_with ) {
		if ( $atts['not_like'] == '' ) {
			return;
		}

		if ( $replace_with == '' ) {
			$replace_with = true;
		} else if ( strpos( $replace_with, $atts['not_like'] ) !== false ) {
			$replace_with = '';
		}
	}

	private static function eval_less_than_condition( $atts, &$field_value ) {
		if ( $field_value < $atts['less_than'] ) {
			// Condition is true
		} else {
			// Condition is false
			$field_value = '';
		}
	}

	private static function eval_greater_than_condition( $atts, &$field_value ) {
		if ( $field_value > $atts['greater_than'] ) {
			// Condition is true
		} else {
			// Condition is false
			$field_value = '';
		}
	}

	public static function trigger_shortcode_atts( $atts, $display, $args, &$replace_with ) {
		$frm_atts = array(
			'sanitize', 'sanitize_url',
			'truncate', 'clickable',
		);
		$included_atts = array_intersect( $frm_atts, array_keys( $atts ) );

		foreach ( $included_atts as $included_att ) {
			$function = 'atts_' . $included_att;
			$replace_with = self::$function( $replace_with, $atts, $display, $args );
		}
	}

	public static function atts_sanitize( $replace_with ) {
		return sanitize_title_with_dashes( $replace_with );
	}

	public static function atts_sanitize_url( $replace_with ) {
		return urlencode( $replace_with );
	}

	public static function atts_truncate( $replace_with, $atts, $display, $args ) {
		if ( isset( $atts['more_text'] ) ) {
			$more_link_text = $atts['more_text'];
		} else {
			$more_link_text = isset( $atts['more_link_text'] ) ? $atts['more_link_text'] : '. . .';
		}

		if ( isset( $atts['no_link'] ) && $atts['no_link'] ) {
			return FrmAppHelper::truncate( $replace_with, (int) $atts['truncate'], 3, $more_link_text );
		}

		// If we're on the listing page of a Dynamic View, use detail link for truncate link
		if ( $display && $display->frm_show_count == 'dynamic' && $args['show'] == 'all' ) {
			$detail_link = self::get_detail_link( $atts, $display );
			$more_link_text = ' <a href="' . esc_url( $detail_link ) . '">' . $more_link_text . '</a>';
			return FrmAppHelper::truncate( $replace_with, (int) $atts['truncate'], 3, $more_link_text );
		}

		$clean_text = wp_strip_all_tags( $replace_with );
		$part_one = FrmAppHelper::truncate( $clean_text, (int) $atts['truncate'], 3, '' );
		$part_two = str_replace( $part_one, '', $clean_text );

		if ( ! empty( $part_two ) ) {
			$replace_with = $part_one .'<a href="#" onclick="jQuery(this).next().css(\'display\', \'inline\');jQuery(this).css(\'display\', \'none\');return false;" class="frm_text_exposed_show"> '. $more_link_text .'</a><span style="display:none;">'. $part_two .'</span>';
		}

		return $replace_with;
	}

	public static function atts_clickable( $replace_with ) {
		return make_clickable( $replace_with );
	}
}