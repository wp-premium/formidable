<?php

class FrmProAppHelper{

    /**
     * Get the Pro settings
     *
     * @since 2.0
     *
     * @param None
     * @return Object
     */
    public static function get_settings() {
        global $frmpro_settings;
        if ( empty($frmpro_settings) ) {
            $frmpro_settings = new FrmProSettings();
        }
        return $frmpro_settings;
    }

    /**
     * Get the current date in the display format
     * Used by [date] shortcode
     *
     * @since 2.0
     * @return string
     */
    public static function get_date( $format = '' ) {
        if ( empty($format) ) {
			$frmpro_settings = self::get_settings();
            $format = $frmpro_settings->date_format;
        }

        return date_i18n($format, strtotime(current_time('mysql')));
    }

    /**
     * Get the current time
     * Used by [time] shortcode
     *
     * @since 2.0
     * @return string
     */
	public static function get_time( $atts = array() ) {
		$defaults = array( 'format' => 'H:i:s', 'round' => 0 );
		$atts = array_merge( $defaults, (array) $atts );
		$current_time = strtotime( current_time( 'mysql' ) );
		if ( ! empty( $atts['round'] ) ) {
			$round_numerator = 60 * (float) $atts['round'];
			$current_time = round( $current_time / $round_numerator ) * $round_numerator;
		}
		return date_i18n( $atts['format'], $current_time );
	}

	/**
	 * Format the time field values
	 * @since 2.0.14
	 */
	public static function format_time( $time, $format = 'H:i' ) {
		if ( is_array( $time ) ) {
			$time = '';
		}

		if ( $time !== '' ) {
			if ( $format == 'h:i A' ) {
				// for reverse compatability
				$format = 'g:i A';
			}
			$time = date( $format, strtotime( $time ) );
		}
		return $time;
	}

	public static function format_time_by_reference( &$time ) {
		$time = self::format_time( $time, 'H:i' );
	}

	private static function is_later_than_noon( $time, $parts ) {
		return ( ( preg_match( '/PM/', $time ) && ( (int) $parts[0] != 12 ) ) || ( ( (int) $parts[0] == 12 ) && preg_match( '/AM/', $time ) ) );
	}

	public static function get_time_format_for_field( $field ) {
		$time_format = isset( $field->field_options['clock'] ) ? $field->field_options['clock'] : 12;
		return self::get_time_format_for_setting( $time_format );
	}

	public static function get_time_format_for_setting( $time_format ) {
		return ( $time_format == 12 ) ? 'g:i A' : 'H:i';
	}

    /**
     * Get a value from the current user profile
     *
     * @since 2.0
     * @return string|array
     */
    public static function get_current_user_value($value, $return_array = false) {
        global $current_user;
        $new_value = isset($current_user->{$value}) ? $current_user->{$value} : '';
        if ( is_array($new_value) && ! $return_array ) {
            $new_value = implode(', ', $new_value);
        }

        return $new_value;
    }

    /**
     * Get the id of the current user
     * Used by [user_id] shortcode
     *
     * @since 2.0
     * @return string
     */
    public static function get_user_id() {
        $user_ID = get_current_user_id();
        return $user_ID ? $user_ID : '';
    }

    /**
     * Get a value from the currently viewed post
     *
     * @since 2.0
     * @return string
     */
    public static function get_current_post_value($value) {
        global $post;
        if ( ! $post ) {
            return;
        }

        if ( isset($post->{$value}) ) {
            $new_value = $post->{$value};
        } else {
            $new_value = get_post_meta($post->ID, $value, true);
        }

        return $new_value;
    }

    /**
     * Get the email of the author of current post
     * Used by [post_author_email] shortcode
     *
     * @since 2.0
     * @return string
     */
    public static function get_post_author_email() {
        return get_the_author_meta('user_email');
    }

	/**
	 * @since 2.0.2
	 */
	public static function display_to_datepicker_format() {
		$formats = array(
			'm/d/Y' => 'mm/dd/yy',
			'Y/m/d' => 'yy/mm/dd',
			'd/m/Y' => 'dd/mm/yy',
			'd.m.Y' => 'dd.mm.yy',
			'j/m/y' => 'd/mm/y',
			'j/n/y' => 'd/m/y',
			'Y-m-d' => 'yy-mm-dd',
			'j-m-Y' => 'd-mm-yy',
		);
		$formats = apply_filters( 'frm_datepicker_formats', $formats );
		return $formats;
	}

    public static function maybe_convert_to_db_date( $date_str, $to_format = 'Y-m-d' ) {
        $date_str = trim($date_str);
        $in_db_format = preg_match('/^\d{4}-\d{2}-\d{2}/', $date_str);

        if ( ! $in_db_format ) {
            $date_str = self::convert_date($date_str, 'db', $to_format);
        }

        return $date_str;
    }

    public static function maybe_convert_from_db_date( $date_str, $from_format = 'Y-m-d' ) {
        $date_str = trim($date_str);
        $in_db_format = preg_match('/^\d{4}-\d{2}-\d{2}/', $date_str);

        if ( $in_db_format ) {
            $date_str = self::convert_date($date_str, $from_format, 'db');
        }

        return $date_str;
    }

	public static function convert_date( $date_str, $from_format, $to_format ) {
        if ( 'db' == $to_format ) {
			$frmpro_settings = self::get_settings();
            $to_format = $frmpro_settings->date_format;
        } else if ( 'db' == $from_format ) {
			$frmpro_settings = self::get_settings();
            $from_format = $frmpro_settings->date_format;
        }

        $base_struc     = preg_split("/[\/|.| |-]/", $from_format);
        $date_str_parts = preg_split("/[\/|.| |-]/", $date_str );

        $date_elements = array();

        $p_keys = array_keys( $base_struc );
		foreach ( $p_keys as $p_key ) {
            if ( ! empty( $date_str_parts[ $p_key ] ) ) {
                $date_elements[ $base_struc[ $p_key ] ] = $date_str_parts[ $p_key ];
            } else {
                return false;
            }
        }

		if ( is_numeric( $date_elements['m'] ) ) {
			$day = ( isset( $date_elements['j'] ) ? $date_elements['j'] : $date_elements['d'] );
			$year = ( isset( $date_elements['Y'] ) ? $date_elements['Y'] : $date_elements['y'] );
			$dummy_ts = mktime( 0, 0, 0, $date_elements['m'], $day, $year );
		} else {
			$dummy_ts = strtotime( $date_str );
		}

        return date( $to_format, $dummy_ts );
    }

	public static function get_edit_link( $id ) {
        $output = '';
    	if ( current_user_can('administrator') ) {
			$output = '<a href="' . esc_url( admin_url( 'admin.php?page=formidable-entries&frm_action=edit&id=' . $id ) ) . '">' . __( 'Edit', 'formidable' ) . '</a>';
        }

    	return $output;
    }

	public static function get_custom_post_types() {
		$custom_posts = get_post_types( array(), 'object');
		foreach ( array( 'revision', 'attachment', 'nav_menu_item' ) as $unset ) {
			unset( $custom_posts[ $unset ] );
		}

		// alphebetize
		ksort( $custom_posts );

		// keep post and page first
		$first_types = array( 'post' => $custom_posts['post'], 'page' => $custom_posts['page'] );
		$custom_posts = $first_types + $custom_posts;

		return $custom_posts;
	}

	public static function get_custom_taxonomy( $post_type, $field ) {
        $taxonomies = get_object_taxonomies($post_type);
        if ( ! $taxonomies ) {
            return false;
        }else{
            $field = (array) $field;
            if ( ! isset($field['taxonomy']) ) {
                $field['field_options'] = maybe_unserialize($field['field_options']);
                $field['taxonomy'] = $field['field_options']['taxonomy'];
            }

            if ( isset($field['taxonomy']) && in_array($field['taxonomy'], $taxonomies) ) {
                return $field['taxonomy'];
            } else if($post_type == 'post' ) {
                return 'category';
            } else {
                return reset($taxonomies);
            }
        }
    }

	public static function sort_by_array( $array, $order_array ) {
        $array = (array) $array;
        $order_array = (array) $order_array;
        $ordered = array();
		foreach ( $order_array as $key ) {
			if ( array_key_exists( $key, $array ) ) {
                $ordered[$key] = $array[$key];
                unset($array[$key]);
            }
        }
        return $ordered + $array;
    }


	public static function reset_keys( $arr ) {
        $new_arr = array();
		if ( empty( $arr ) ) {
            return $new_arr;
		}

		foreach ( $arr as $val ) {
            $new_arr[] = $val;
            unset($val);
        }
        return $new_arr;
    }

	public static function filter_where( $entry_ids, $args ) {
        $defaults = array(
            'where_opt' => false, 'where_is' => '=', 'where_val' => '',
            'form_id' => false, 'form_posts' => array(), 'after_where' => false,
            'display' => false, 'drafts' => 0, 'use_ids' => false,
        );

        $args = wp_parse_args($args, $defaults);

        if ( ! (int) $args['form_id'] || ! $args['where_opt'] || ! is_numeric($args['where_opt']) ) {
            return $entry_ids;
        }

        $where_field = FrmField::getOne($args['where_opt']);
        if ( ! $where_field ) {
            return $entry_ids;
        }

        self::prepare_where_args($args, $where_field, $entry_ids);

        $new_ids = array();
        self::filter_entry_ids( $args, $where_field, $entry_ids, $new_ids );

        unset($args['temp_where_is']);

        self::prepare_post_filter( $args, $where_field, $new_ids );

        if ( $args['after_where'] ) {
            //only use entries that are found with all wheres
            $entry_ids = array_intersect($new_ids, $entry_ids);
        } else {
            $entry_ids = $new_ids;
        }

        return $entry_ids;
    }

    /**
     * Called by the filter_where function
     */
    private static function prepare_where_args( &$args, $where_field, $entry_ids ) {
		self::prepare_where_datetime( $args, $where_field );

		if ( $args['where_is'] == '=' && $args['where_val'] != '' && FrmField::is_field_with_multiple_values( $where_field ) ) {
            if ( $where_field->type != 'data' || $where_field->field_options['data_type'] != 'checkbox' || is_numeric($args['where_val']) ) {
                // leave $args['where_is'] the same if this is a data from entries checkbox with a numeric value
                $args['where_is'] = 'LIKE';
            }
        }

        $args['temp_where_is'] = str_replace( array( '!', 'not '), '', $args['where_is']);

        //get values that aren't blank and then remove them from entry list
        if ( $args['where_val'] == '' && $args['temp_where_is'] == '=' ) {
            $args['temp_where_is'] = '!=';
        }

		if ( self::option_is_like( $args['where_is'] ) ) {
             //add extra slashes to match values that are escaped in the database
			$args['where_val_esc'] = addslashes( $args['where_val'] );
        } else if ( ! strpos( $args['where_is'], 'in' ) && ! is_numeric( $args['where_val'] ) ) {
			$args['where_val_esc'] = $args['where_val'];
        }
        $filter_args = $args;
        $filter_args['entry_ids'] = $entry_ids;
        $args['where_val'] = apply_filters('frm_filter_where_val', $args['where_val'], $filter_args);

        self::prepare_dfe_text($args, $where_field);
    }

	/**
	 * @since 2.3
	 */
	private static function prepare_where_datetime( &$args, $where_field ) {
		$is_datetime = ( $args['where_val'] == 'NOW' || $where_field->type == 'date' || $where_field->type == 'time' );
		if ( ! $is_datetime || empty( $args['where_val'] ) ) {
			return;
		}

		$date_format = ( $where_field->type == 'time' ) ? 'H:i' : 'Y-m-d';

		if ( $args['where_val'] == 'NOW' ) {
			$args['where_val'] = self::get_date( $date_format );
		} elseif ( ! self::option_is_like( $args['where_is'] )  ) {
			$args['where_val'] = date( $date_format, strtotime( $args['where_val'] ) );
		}
	}

	/**
	 * @since 2.3
	 */
	private static function option_is_like( $where_is ) {
		return in_array( $where_is, array( 'LIKE', 'not LIKE' ) );
	}

	/**
	* Replace a text value where_val with the matching entry IDs for Dynamic Field filters
	*
	* @param array $args
	* @param object $where_field
	*/
	private static function prepare_dfe_text( &$args, $where_field ) {
		if ( $where_field->type != 'data' ) {
			return;
		}

		// Only proceed if we have a non-category dynamic field with a non-numeric/non-array where_val
		$is_a_string_value = ( $args['where_val'] && ! is_numeric( $args['where_val'] ) && ! is_array( $args['where_val'] ) );
		$is_a_post_field = ( isset( $where_field->field_options['post_field'] ) && $where_field->field_options['post_field'] == 'post_category' );
		$continue = ( $is_a_string_value && ! $is_a_post_field );
		$continue = apply_filters( 'frm_search_for_dynamic_text', $continue, $where_field, $args );

		if ( ! $continue ) {
			return;
		}

		$linked_id = FrmProField::get_dynamic_field_entry_id( $where_field->field_options['form_select'], $args['where_val'], $args['temp_where_is'] );

		// If text doesn't return any entry IDs, get entry IDs from entry key
		// Note: Keep for reverse compatibility
		if ( ! $linked_id ) {
			$linked_field = FrmField::getOne($where_field->field_options['form_select']);
			if ( ! $linked_field ) {
				return;
			}

			$linked_id = FrmDb::get_col( 'frm_items', array(
				'form_id' => $linked_field->form_id,
				'item_key ' . FrmDb::append_where_is( $args['temp_where_is'] ) => $args['where_val'],
				) );
		}

		if ( ! $linked_id ) {
			return;
		}

		//Change $args['where_val'] to linked entry IDs
		$args['where_val'] = (array) $linked_id;

		// Don't use old where_val_esc value for filtering
		unset($args['where_val_esc']);

		$args['where_val'] = apply_filters('frm_filter_dfe_where_val', $args['where_val'], $args);
    }

    private static function filter_entry_ids( $args, $where_field, $entry_ids, &$new_ids ) {
		$where_statement = array( 'fi.id' => (int) $args['where_opt'] );

		$field_key = 'meta_value ' . ( in_array( $where_field->type, array( 'number', 'scale') ) ? ' +0 ' : '' ) . FrmDb::append_where_is( $args['temp_where_is'] );
		$nested_where = array( $field_key => $args['where_val'] );
        if ( isset($args['where_val_esc']) && $args['where_val_esc'] != $args['where_val'] ) {
			$nested_where['or'] = 1;
			$nested_where[ ' ' . $field_key ] = $args['where_val_esc'];
        }
		$where_statement[] = $nested_where;

        $args['entry_ids'] = $entry_ids;
        $where_statement = apply_filters('frm_where_filter', $where_statement, $args);

		$filter_args = array( 'is_draft' => $args['drafts'] );

		// If the field is from a repeating section (or embedded form?) get the parent ID
		$filter_args['return_parent_id'] = ( $where_field->form_id != $args['form_id'] );

		// Add entry IDs to $where_statement
		if ( $args['use_ids'] ) {
			if ( is_array( $where_statement ) ) {
				if ( $filter_args['return_parent_id'] ) {
					$where_statement['parent_item_id'] = $entry_ids;
				} else {
					$where_statement['item_id'] = $entry_ids;
				}
			} else {
				// if the filter changed the query to a string, allow it
				$where_statement .= FrmAppHelper::prepend_and_or_where( ' AND ', array( 'item_id' => $entry_ids ) );
			}
		}

		$new_ids = FrmEntryMeta::getEntryIds( $where_statement, '', '', true, $filter_args );

        if ( $args['where_is'] != $args['temp_where_is'] ) {
            $new_ids = array_diff( (array) $entry_ids, $new_ids );
        }
    }

    /**
     * if there are posts linked to entries for this form
     */
    private static function prepare_post_filter( $args, $where_field, &$new_ids ) {
        if ( empty($args['form_posts']) ) {
            // there are not posts related to this view
            return;
        }

        if ( ! isset($where_field->field_options['post_field']) || ! in_array($where_field->field_options['post_field'], array( 'post_category', 'post_custom', 'post_status', 'post_content', 'post_excerpt', 'post_title', 'post_name', 'post_date')) ) {
            // this is not a post field
            return;
        }

        $post_ids = array();
        foreach ( $args['form_posts'] as $form_post ) {
            $post_ids[$form_post->post_id] = $form_post->id;
            if ( ! in_array($form_post->id, $new_ids) ) {
                $new_ids[] = $form_post->id;
            }
        }

        if ( empty($post_ids) ) {
            return;
        }

        global $wpdb;
		$filter_args = array();

        if ( $where_field->field_options['post_field'] == 'post_category' ) {
            //check categories

			$args['temp_where_is'] = FrmDb::append_where_is( str_replace( array( '!', 'not '), '', $args['where_is'] ) );

			$t_where = array(
				'or' => 1,
				't.term_id '. $args['temp_where_is'] => $args['where_val'],
				't.slug '. $args['temp_where_is'] => $args['where_val'],
				't.name '. $args['temp_where_is'] => $args['where_val'],
			);
            unset($args['temp_where_is']);

			$query = array( 'tt.taxonomy' => $where_field->field_options['taxonomy'] );
			$query[] = $t_where;

			$add_posts = FrmDb::get_col(
				$wpdb->terms .' AS t INNER JOIN '. $wpdb->term_taxonomy .' AS tt ON tt.term_id = t.term_id INNER JOIN '. $wpdb->term_relationships .' AS tr ON tr.term_taxonomy_id = tt.term_taxonomy_id',
				$query,
				'tr.object_id',
				$filter_args
			);
            $add_posts = array_intersect($add_posts, array_keys($post_ids));

            if ( in_array($args['where_is'], array( '!=', 'not LIKE') ) ) {
                $remove_posts = $add_posts;
                $add_posts = false;
            } else if ( empty($add_posts) ) {
                $new_ids = array();
                return;
            }
        } else {
			$query = array();
            if ( $where_field->field_options['post_field'] == 'post_custom' && $where_field->field_options['custom_field'] != '' ) {
                //check custom fields
				$get_field = 'post_id';
				$get_table = $wpdb->postmeta;
				$query['meta_key'] = $where_field->field_options['custom_field'];
				$query_key = 'meta_value';
            } else {
                //if field is post field
				$get_field = 'ID';
				$get_table = $wpdb->posts;
				$query_key = sanitize_title( $where_field->field_options['post_field'] );
            }

			$query_key .= ( in_array( $where_field->type, array( 'number', 'scale' ) ) ? ' +0 ' : ' ' ) . FrmDb::append_where_is( $args['where_is'] );
			$query[ $query_key ] = $args['where_val'];

			$add_posts = FrmDb::get_col( $get_table, $query, $get_field, $filter_args );
			$add_posts = array_intersect( $add_posts, array_keys( $post_ids ) );
        }

        if ( $add_posts && ! empty($add_posts) ) {
            $new_ids = array();
            foreach ( $add_posts as $add_post ) {
                if ( ! in_array($post_ids[$add_post], $new_ids) ) {
                    $new_ids[] = $post_ids[$add_post];
                }
            }
        }

        if ( isset($remove_posts) ) {
            if ( ! empty($remove_posts) ) {
                foreach ( $remove_posts as $remove_post ) {
                    $key = array_search($post_ids[$remove_post], $new_ids);
                    if ( $key && $new_ids[$key] == $post_ids[$remove_post] ) {
                        unset($new_ids[$key]);
                    }

                    unset($key);
                }
            }
        } else if ( ! $add_posts ) {
            $new_ids = array();
        }
    }

    /**
     * Let WordPress process the uploads
     * @param int $field_id
     */
	public static function upload_file( $field_id ) {
		_deprecated_function( __FUNCTION__, '2.02', 'FrmProFileField::upload_file' );
        return FrmProFileField::upload_file( $field_id );
    }

	public static function upload_dir( $uploads ) {
		_deprecated_function( __FUNCTION__, '2.02', 'FrmProFileField::upload_dir' );
        return FrmProFileField::upload_dir( $uploads );
    }

	public static function get_rand( $length ) {
        $all_g = "ABCDEFGHIJKLMNOPQRSTWXZ";
        $pass = "";
        for($i=0;$i<$length;$i++) {
            $pass .= $all_g[ rand(0, strlen($all_g) - 1) ];
        }
        return $pass;
    }

    /* Genesis Integration */
	public static function load_genesis() {
        // Add classes to view pagination
        add_filter('frm_pagination_class', 'FrmProAppHelper::gen_pagination_class');
        add_filter('frm_prev_page_label', 'FrmProAppHelper::gen_prev_label');
        add_filter('frm_next_page_label', 'FrmProAppHelper::gen_next_label');
        add_filter('frm_prev_page_class', 'FrmProAppHelper::gen_prev_class');
        add_filter('frm_next_page_class', 'FrmProAppHelper::gen_next_class');
		add_filter( 'frm_page_dots_class', 'FrmProAppHelper::gen_dots_class', 1 );
    }

	public static function gen_pagination_class( $class ) {
        $class .= ' archive-pagination pagination';
        return $class;
    }

	public static function gen_prev_label() {
        return apply_filters( 'genesis_prev_link_text', '&#x000AB;' . __( 'Previous Page', 'formidable' ) );
    }

	public static function gen_next_label() {
        return apply_filters( 'genesis_next_link_text', __( 'Next Page', 'formidable' ) . '&#x000BB;' );
    }

	public static function gen_prev_class( $class ) {
        $class .= ' pagination-previous';
        return $class;
    }

	public static function gen_next_class( $class ) {
        $class .= ' pagination-next';
        return $class;
    }

	public static function gen_dots_class( $class ) {
        $class = 'pagination-omission';
        return $class;
    }
    /* End Genesis */
}
