<?php

class FrmProFieldsHelper{

    public static function get_default_value( $value, $field, $dynamic_default = true, $allow_array = false ) {
		$unserialized = maybe_unserialize( $value );
		if ( is_array( $unserialized ) ) {
			if ( $field->type == 'time' && FrmProTimeField::is_time_empty( $value ) ) {
				$value = '';
			} elseif ( FrmAppHelper::is_empty_value( $unserialized ) || count( array_filter( $unserialized ) ) === 0  ) {
				$value = '';
			} else {
				return $value;
			}
		}

        $prev_val = '';
		if ( $field && $dynamic_default ) {
            if ( FrmField::is_option_value_in_object( $field, 'dyn_default_value' ) ) {
                $prev_val = $value;
                $value = $field->field_options['dyn_default_value'];
            }
        }

		$pass_args = array(
			'allow_array' => $allow_array,
			'field'       => $field,
			'prev_val'    => $prev_val,
		);

		self::replace_non_standard_formidable_shortcodes( $pass_args, $value );
		self::replace_field_id_shortcodes( $value, $allow_array );
		self::do_shortcode( $value, $allow_array );
		self::maybe_force_array( $value, $field, $allow_array );

		return $value;
	}

	/**
	 * Replace Formidable shortcodes (that are not added with add_shortcode) in a string
	 *
	 * @since 2.0.24
	 * @param array $args
	 * @param string $value
	 */
	public static function replace_non_standard_formidable_shortcodes( $args, &$value ) {
		$default_args = array(
			'allow_array' => false,
			'field' => false,
			'prev_val' => '',
		);
		$args = wp_parse_args( $args, $default_args );

		$matches = self::get_shortcodes_from_string( $value );

		if ( isset( $matches[0] ) ) {
			$args['matches'] = $matches;

			foreach ( $matches[1] as $match_key => $shortcode ) {
				$args['shortcode'] = $shortcode;
				$args['match_key'] = $match_key;
				self::replace_shortcode_in_string( $value, $args  );
			}
		}
	}

	/**
	 * @since 2.0.8
	 */
	private static function get_shortcode_to_functions() {
		return array(
			'email'         => array( 'FrmProAppHelper', 'get_current_user_value'),
			'login'         => array( 'FrmProAppHelper', 'get_current_user_value'),
			'username'      => array( 'FrmProAppHelper', 'get_current_user_value'),
			'display_name'  => array( 'FrmProAppHelper', 'get_current_user_value'),
			'first_name'    => array( 'FrmProAppHelper', 'get_current_user_value'),
			'last_name'     => array( 'FrmProAppHelper', 'get_current_user_value'),
			'user_role'     => array( 'FrmProAppHelper', 'get_current_user_value'),
			'user_id'       => array( 'FrmProAppHelper', 'get_user_id'),
			'post_id'       => array( 'FrmProAppHelper', 'get_current_post_value'),
			'post_title'    => array( 'FrmProAppHelper', 'get_current_post_value'),
			'post_author_email' => 'get_the_author_meta',
			'ip'            => array( 'FrmAppHelper', 'get_ip_address'),
			'admin_email'   => array( 'FrmFieldsHelper', 'dynamic_default_values'),
			'siteurl'       => array( 'FrmFieldsHelper', 'dynamic_default_values'),
			'frmurl'        => array( 'FrmFieldsHelper', 'dynamic_default_values'),
			'sitename'      => array( 'FrmFieldsHelper', 'dynamic_default_values'),
		);
	}

	private static function get_shortcode_function_parameters() {
		return array(
			'email'         => 'user_email',
			'login'         => 'user_login',
			'username'      => 'user_login',
			'display_name'  => 'display_name',
			'first_name'    => 'user_firstname',
			'last_name'     => 'user_lastname',
			'user_role'     => 'roles',
			'post_id'       => 'ID',
			'post_title'    => 'post_title',
			'post_author_email' => 'user_email',
			'admin_email'   => 'admin_email',
			'siteurl'       => 'siteurl',
			'frmurl'        => 'frmurl',
			'sitename'      => 'sitename',
		);
	}

	/**
	 * @since 2.0.8
	 */
	private static function get_shortcodes_from_string( $string ) {
		$shortcode_functions = self::get_shortcode_to_functions();
		$match_shortcodes = implode( '|', array_keys( $shortcode_functions ) );
		$match_shortcodes .= '|user_meta|post_meta|server|auto_id|date|time|age|get';
		preg_match_all( '/\[(' . $match_shortcodes . '|get-(.?))\b(.*?)(?:(\/))?\]/s', $string, $matches, PREG_PATTERN_ORDER );
		return $matches;
	}

	/**
	 * @since 2.0.8
	 */
	private static function replace_shortcode_in_string( &$value, $args  ) {
		$shortcode_functions = self::get_shortcode_to_functions();

		if ( isset( $shortcode_functions[ $args['shortcode'] ] ) ) {
			$new_value = self::get_shortcode_value_from_function( $args['shortcode'] );
		} else {
			$new_value = self::get_other_shortcode_values( $args );
		}

		if ( is_array($new_value) ) {
			if ( count($new_value) === 1 ) {
				$new_value = reset($new_value);
			}
			$value = $new_value;
		} else {
			$value = str_replace( $args['matches'][0][ $args['match_key'] ], $new_value, $value );
		}
	}

	/**
	 * @since 2.0.8
	 */
	private static function get_shortcode_value_from_function( $shortcode ) {
		$shortcode_functions = self::get_shortcode_to_functions();
		$shortcode_atts = self::get_shortcode_function_parameters();

		return call_user_func( $shortcode_functions[ $shortcode ], isset( $shortcode_atts[ $shortcode ] ) ? $shortcode_atts[ $shortcode ] : '' );
	}

	/**
	 * @since 2.0.8
	 */
	private static function get_other_shortcode_values( $args ) {
		$atts = FrmShortcodeHelper::get_shortcode_attribute_array( stripslashes( $args['matches'][3][ $args['match_key'] ] ) );
		if ( isset( $atts['return_array'] ) ) {
			$args['allow_array'] = $atts['return_array'];
		}
		$args['shortcode_atts'] = $atts;
		$new_value = '';

		switch ( $args['shortcode'] ) {
			case 'user_meta':
				if ( isset( $atts['key'] ) ) {
					$new_value = FrmProAppHelper::get_current_user_value( $atts['key'], false );
				}
			break;

			case 'post_meta':
				if ( isset( $atts['key'] ) ) {
					$new_value = FrmProAppHelper::get_current_post_value( $atts['key'] );
				}
			break;

			case 'get':
				$new_value = self::do_get_shortcode( $args );
			break;

			case 'auto_id':
				$new_value = self::do_auto_id_shortcode( $args );
			break;

			case 'server':
				if ( isset( $atts['param'] ) ) {
					$new_value = FrmAppHelper::get_server_value( $atts['param'] );
				}
			break;

			case 'date':
				$new_value = FrmProAppHelper::get_date( isset( $atts['format'] ) ? $atts['format'] : '' );
			break;

			case 'time':
				$new_value = FrmProAppHelper::get_time( $atts );
			break;

			case 'age':
				$new_value = self::do_age_shortcode( $atts );
			break;

			default:
				$new_value = self::check_posted_item_meta( $args['matches'][0][ $args['match_key'] ], $args['shortcode'], $atts, $args['allow_array'] );
			break;
		}

		return $new_value;
	}

	/**
	 * @since 2.0.8
	 */
	private static function do_get_shortcode( $args ) {
		// reverse compatability for [get-param] shortcode
		if ( strpos( $args['matches'][0][ $args['match_key'] ], '[get-' ) === 0 ) {
			$val = $args['matches'][0][ $args['match_key'] ];
			$param = str_replace( '[get-', '', $val );
			if ( preg_match( '/\[/s', $param ) ) {
				$val .= ']';
			} else {
				$param = trim( $param, ']' ); //only if is doesn't create an imbalanced []
			}
			$new_value = FrmFieldsHelper::process_get_shortcode( compact( 'param' ), $args['allow_array'] );
		} else {
			$atts = $args['shortcode_atts'];
			$atts['prev_val'] = $args['prev_val'];
			$new_value = FrmFieldsHelper::dynamic_default_values( $args['shortcode'], $atts, $args['allow_array'] );
		}

		return $new_value;
	}

	/**
	 * @since 2.0.8
	 */
	private static function do_auto_id_shortcode( $args ) {
		$last_entry = FrmProEntryMetaHelper::get_max( $args['field'] );

		if ( ! $last_entry && isset( $args['shortcode_atts']['start'] ) ) {
			$new_value = absint( $args['shortcode_atts']['start'] );
		} else {
			$new_value = absint( $last_entry ) + 1;
		}

		return $new_value;
	}

	private static function do_age_shortcode( $args ) {
		if ( ! isset( $args['id'] ) ) {
			$value = '';
		} else {
			$time = FrmProAppHelper::get_date( 'U' );
			$value = 'Math.floor((' . absint( $time ) . '/(60*60*24)-[' . esc_attr( $args['id'] ) . '])/365.25)';
		}
		return $value;
	}

    /**
     * Check for shortcodes in default values but prevent the form shortcode from filtering
     *
     * @since 2.0
     */
    private static function do_shortcode( &$value, $return_array = false ) {
		$is_final_val_set = self::do_array_shortcode( $value, $return_array );
		if ( $is_final_val_set ) {
			return;
		}

        global $frm_vars;
        $frm_vars['skip_shortcode'] = true;
        if ( is_array($value) ) {
            foreach ( $value as $k => $v ) {
                $value[$k] = do_shortcode($v);
                unset($k, $v);
            }
        } else {
            $value = do_shortcode($value);
        }
        $frm_vars['skip_shortcode'] = false;
    }

	/**
	* If shortcode must return an array, bypass the WP do_shortcode function
	* This is set up to return arrays for frm-field-value shortcode in multiple select fields
	*
	* @param $value - string which will be switched to array, pass by reference
	* @param $return_array - boolean keeps track of whether or not an array should be returned
	* @return boolean to tell calling function (do_shortcode) if final value is set
	*/
	private static function do_array_shortcode( &$value, $return_array ) {
		if ( ! $return_array || is_array( $value ) ) {
			return false;
		}

		// If frm-field-value shortcode and it should return an array, bypass the WP do_shortcode function
		if ( strpos( $value, '[frm-field-value ' ) !== false ) {
			preg_match_all( '/\[(frm-field-value)\b(.*?)(?:(\/))?\]/s', $value, $matches, PREG_PATTERN_ORDER );

			foreach ( $matches[0] as $short_key => $tag ) {
				$atts = FrmShortcodeHelper::get_shortcode_attribute_array( $matches[2][ $short_key ] );
				$atts['return_array'] = $return_array;

				$value = FrmProEntriesController::get_field_value_shortcode( $atts );
			}

			return true;
		}

		return false;
	}

    private static function replace_field_id_shortcodes( &$value, $allow_array ) {
        if ( empty($value) ) {
            return;
        }

        if ( is_array($value) ) {
            foreach ( $value as $k => $v ) {
				self::replace_each_field_id_shortcode( $v, $allow_array );
				$value[ $k ] = $v;
                unset($k, $v);
            }
        } else {
			self::replace_each_field_id_shortcode( $value, $allow_array );
        }
    }

    private static function replace_each_field_id_shortcode( &$value, $return_array ) {
        preg_match_all( "/\[(\d*)\b(.*?)(?:(\/))?\]/s", $value, $matches, PREG_PATTERN_ORDER);
        if ( ! isset($matches[0]) ) {
            return;
        }

        foreach ( $matches[0] as $match_key => $val ) {
            $shortcode = $matches[1][$match_key];
            if ( ! is_numeric($shortcode) || ! isset($_REQUEST) || ! isset($_REQUEST['item_meta']) ) {
                continue;
            }

            $new_value = FrmAppHelper::get_param( 'item_meta['. $shortcode .']', false, 'post', 'wp_kses_post' );
            if ( ! $new_value && isset($atts['default']) ) {
                $new_value = $atts['default'];
            }

            if ( is_array($new_value) && ! $return_array ) {
                $new_value = implode(', ', $new_value);
            }

            if ( is_array($new_value) ) {
                $value = $new_value;
            } else {
                $value = str_replace($val, $new_value, $value);
            }
        }
    }

    /**
     * If this default value should be an array, we will make sure it is
     *
     * @since 2.0
     */
    private static function maybe_force_array( &$value, $field, $return_array ) {
        if ( ! $return_array || is_array($value) || strpos($value, ',') === false ) {
            // this is already in the correct format
            return;
        }

		//If checkbox, multi-select dropdown, or checkbox data from entries field and default value has a comma
		if ( FrmField::is_field_with_multiple_values( $field ) && ( in_array( $field->type, array( 'data', 'lookup' ) ) || ! in_array( $value, $field->options ) ) ) {
			//If the default value does not match any options OR if data from entries field (never would have commas in values), explode to array
			$value = explode(',', $value);
		}
    }

    private static function check_posted_item_meta( $val, $shortcode, $atts, $return_array ) {
        if ( ! is_numeric($shortcode) || ! isset($_REQUEST) || ! isset($_REQUEST['item_meta']) ) {
            return $val;
        }

        //check for posted item_meta
        $new_value = FrmAppHelper::get_param('item_meta['. $shortcode .']', false, 'post');

        if ( ! $new_value && isset($atts['default']) ) {
            $new_value = $atts['default'];
        }

        if ( is_array($new_value) && ! $return_array ) {
            $new_value = implode(', ', $new_value);
        }

        return $new_value;
    }

    /**
     * Get the input name and id
     * Called when loading a dynamic DFE field
     * @since 2.0
     */
    public static function get_html_id_from_container(&$field_name, &$html_id, $field, $hidden_field_id) {
        $id_parts = explode('-', str_replace('_container', '', $hidden_field_id));
        $plus = ( count($id_parts) == 3 ) ? '-' . end($id_parts) : ''; // this is in a sub field
        $html_id = FrmFieldsHelper::get_html_id($field, $plus);
        if ( $plus != '' ) {
            // get the name for the sub field
            $field_name .= '['. $id_parts[1] .']['. end($id_parts) .']';
        }
        $field_name .= '['. $field['id'] .']';
    }

	public static function setup_new_field_vars( $values ) {
        $values['field_options'] = maybe_unserialize($values['field_options']);
        $defaults = self::get_default_field_opts($values);

		foreach ( $defaults as $opt => $default ) {
			$values[ $opt ] = ( isset( $values['field_options'][ $opt ] ) ) ? $values['field_options'][ $opt ] : $default;
		}

        unset($defaults);

        if ( ! empty($values['hide_field']) && ! is_array($values['hide_field']) ) {
            $values['hide_field'] = (array) $values['hide_field'];
        }

        return $values;
    }

	public static function setup_new_vars( $values, $field ) {
		$values['entry_id'] = 0;

		self::fill_field_options( $field, $values, false );
		self::prepare_field_array( $field, $values );

		if ( $values['type'] == 'scale' ) {
			$values['minnum'] = 1;
			$values['maxnum'] = 10;

		} else if ( $values['type'] == 'user_id' || $values['original_type'] == 'user_id' ) {
			$show_admin_field = FrmAppHelper::is_admin() && current_user_can('frm_edit_entries') && ! FrmAppHelper::is_admin_page('formidable' );
			if ( $show_admin_field && self::field_on_current_page( $field ) ) {
				$user_ID = get_current_user_id();
				$values['value'] = ( $_POST && isset($_POST['item_meta'][ $field->id ] ) ) ? $_POST['item_meta'][ $field->id ] : $user_ID;
			}
		}

		self::prepare_post_fields( $field, $values );
		self::filter_default_values( $field, $values );
		self::add_field_javascript( $values );

		return $values;
	}

	private static function filter_default_values( $field, &$values ) {
		$is_default = ( $values['default_value'] === $values['value'] );
		if ( is_array( $values['value'] ) ) {
			foreach ( $values['value'] as $val_key => $val ) {
				$values['value'][ $val_key ] = apply_filters( 'frm_filter_default_value', $val, $field, false );
			}
		} else if ( ! empty( $values['value'] ) ) {
			$values['value'] = apply_filters( 'frm_filter_default_value', $values['value'], $field, false );
		}

		if ( $is_default ) {
			$values['default_value'] = $values['value'];
		}
	}

	public static function setup_edit_vars( $values, $field, $entry_id = 0 ) {
		$values['entry_id'] = $entry_id;

		self::fill_field_options( $field, $values );
		self::prepare_field_array( $field, $values );

		if ( $values['type'] == 'tag' ) {
			if ( empty( $values['value'] ) ) {
				self::tags_to_list($values, $entry_id);
			}
		}

		self::maybe_show_hidden_field( $field, $values );
		self::prepare_post_fields( $field, $values );

		FrmProNestedFormsController::format_saved_values_for_hidden_nested_forms( $values );

		self::add_field_javascript( $values );

		return $values;
	}

	/**
	* Populate the options for a field when loaded (front and back-end)
	*
	* @since 2.0.08
	* @param object $field
	* @param array $values, pass by reference
	*/
	private static function fill_field_options( $field, &$values, $allow_blank = true ) {
		$values['use_key'] = false;

		foreach ( self::get_default_field_opts( $values, $field ) as $opt => $default ) {
			$use_value = isset( $field->field_options[ $opt ] ) && ( $field->field_options[ $opt ] != '' || $allow_blank );
			if ( $use_value ) {
				$values[ $opt ] = $field->field_options[ $opt ];
			} else {
				$values[ $opt ] = $default;
			}
		}
	}

	/**
	* Used to setup fields for new and edit
	*
	* @since 2.2.10
	*/
	private static function prepare_field_array( $field, &$values ) {
		$values['hide_field'] = (array) $values['hide_field'];
		$values['hide_field_cond'] = (array) $values['hide_field_cond'];
		$values['hide_opt'] = (array) $values['hide_opt'];
		$values['name'] = self::get_default_value( $values['name'], $field, false );
		self::prepare_field_types( $field, $values );
	}

	private static function prepare_field_types( $field, &$values ) {

		if ( $values['type'] == 'data' && in_array( $values['data_type'], array( 'select', 'radio', 'checkbox' ) ) && is_numeric( $values['form_select'] ) ) {
			FrmProDynamicFieldsController::add_options_for_dynamic_field( $field, $values, array( 'entry_id' => $values['entry_id'] ) );
	
		} elseif ( $values['type'] == 'time' ) {
			$values['options'] = FrmProTimeField::get_time_options( $values );
			$values['value'] = self::get_time_display_value( $values['value'], array(), $field );
		} elseif ( $values['type'] == 'date' || $values['original_type'] == 'date' ) {
			$values['value'] = FrmProAppHelper::maybe_convert_from_db_date( $values['value'] );
		} elseif ( $values['type'] == 'lookup' ) {
			FrmProLookupFieldsController::maybe_get_initial_lookup_field_options( $values );
		} elseif ( $values['type'] == 'user_id' ) {
			self::prepare_user_id_field( $field, $values );
		} elseif ( ! empty( $values['options'] ) ) {
			$is_builder_page = FrmAppHelper::is_admin() && FrmAppHelper::is_admin_page('formidable' );
			if ( ! $is_builder_page ) {
				self::prepare_to_show_field_options( $field, $values );
			}
		}
	}

	private static function prepare_user_id_field( $field, &$values ) {
		$show_admin_field = FrmAppHelper::is_admin() && current_user_can('frm_edit_entries') && ! FrmAppHelper::is_admin_page('formidable' );
		if ( $show_admin_field && self::field_on_current_page( $field ) ) {
			$values['type'] = 'select';
			$values['options'] = self::get_user_options();
			$values['use_key'] = true;
			$values['custom_html'] = FrmFieldsHelper::get_default_html('select');
		}
	}

	private static function prepare_post_fields( $field, &$values ) {		
		if ( $values['post_field'] == 'post_category' ) {
			$values['use_key'] = true;
			$values['options'] = self::get_category_options( $values );
			if ( $values['type'] == 'data' && $values['data_type'] == 'select' && ( ! $values['multiple'] || $values['autocom'] ) ) {
				// add a blank option
				$values['options'] = array( '' => '' ) + (array) $values['options'];
			}
		} else if ( $values['post_field'] == 'post_status' && ! in_array( $field->type, array( 'hidden', 'text' ) ) ) {
			$values['use_key'] = true;
			$values['options'] = self::get_status_options( $field, $values['options'] );
		}
	}

	private static function prepare_to_show_field_options( $field, &$values ) {
		foreach ( $values['options'] as $val_key => $val_opt ) {
			self::maybe_remove_separate_value( $field, $val_opt );
			if ( is_array( $val_opt ) ) {
				foreach ( $val_opt as $opt_key => $opt ) {
					$values['options'][ $val_key ][ $opt_key ] = self::get_default_value( $opt, $field, false );
					unset( $opt_key, $opt );
				}
			} else {
				$values['options'][ $val_key ] = self::get_default_value( $val_opt, $field, false );
			}
			unset( $val_key, $val_opt );
		}
	}

	/**
	 * If a field doesn't have separate values, simplify the options array
	 * to include only the key and displayed value.
	 * Since v2.03, the field options always include separate values.
	 * This causes trouble with custom code reverse compatability.
	 *
	 * @since 2.03.05
	 */
	private static function maybe_remove_separate_value( $field, &$opt ) {
		if ( ! is_array( $opt ) || ! isset( $opt['label'] ) ) {
			return;
		}

		$no_separate_values = FrmField::is_option_empty( $field, 'separate_value' );
		if ( $no_separate_values ) {
			$opt = $opt['label'];
		}
	}

	private static function maybe_show_hidden_field( $field, &$values ) {
		if ( $values['type'] == 'hidden' ) {
			$admin_edit = FrmAppHelper::is_admin() && current_user_can('administrator') && ! FrmAppHelper::is_admin_page('formidable' );
			if ( $admin_edit && self::field_on_current_page( $field ) ) {
				$values['type'] = 'text';
				$values['custom_html'] = FrmFieldsHelper::get_default_html('text');
			}
		}
	}

	/**
	 * Add field-specific JavaScript to global $frm_vars
	 *
	 * @since 2.01.0
	 * @param array $values
	 */
	public static function add_field_javascript( $values ) {
		self::setup_conditional_fields( $values );
		FrmProLookupFieldsController::setup_lookup_field_js( $values );
	}

    public static function tags_to_list(&$values, $entry_id) {
        $post_id = FrmDb::get_var( 'frm_items', array( 'id' => $entry_id), 'post_id' );
        if ( ! $post_id ) {
            return;
        }

        $tags = get_the_terms( $post_id, $values['taxonomy'] );
        if ( empty($tags) ) {
            $values['value'] = '';
            return;
        }

        $names = array();
        foreach ( $tags as $tag ) {
            $names[] = $tag->name;
        }

        $values['value'] = implode(', ', $names);
    }

    public static function get_default_field_opts( $values = false, $field = false ) {
        $minnum = 1;
        $maxnum = 10;
        $step = 1;
        $align = 'block';
        $show_hide = 'show';

		$field_type = ( $values ) ? $values['type'] : $field->type;
		switch ( $field_type ) {
			case 'number':
				$minnum = 0;
				$maxnum = 9999;
				$step = 'any';
			break;
			case 'scale':
				if ( $field ) {
					$range = maybe_unserialize( $field->options );
					$minnum = reset( $range );
					$maxnum = end( $range );
				}
			break;
			case 'time':
				$step = 30;
			break;
			case 'radio':
				$align = FrmStylesController::get_style_val( 'radio_align', ( $field ? $field->form_id : 'default' ) );
			break;
			case 'checkbox':
				$align = FrmStylesController::get_style_val( 'check_align', ( $field ? $field->form_id : 'default' ) );
			break;
			case 'break':
				$show_hide = 'hide';
			break;
		}

        $end_minute = 60 - (int) $step;

        $frm_settings = FrmAppHelper::get_settings();

        $opts = array(
            'slide' => 0, 'form_select' => '', 'show_hide' => $show_hide, 'any_all' => 'any', 'align' => $align,
            'hide_field' => array(), 'hide_field_cond' =>  array( '=='), 'hide_opt' => array(), 'star' => 0,
            'post_field' => '', 'custom_field' => '', 'taxonomy' => 'category', 'exclude_cat' => 0, 'ftypes' => array(),
            'data_type' => 'select', 'restrict' => 0, 'start_year' => 2000, 'end_year' => 2020, 'read_only' => 0,
            'admin_only' => '', 'locale' => '', 'attach' => false, 'minnum' => $minnum, 'maxnum' => $maxnum,
			'delete' => false,
			'step' => $step, 'clock' => 12, 'single_time' => 0,
			'start_time' => '00:00', 'end_time' => '23:' . $end_minute,
			'unique' => 0, 'use_calc' => 0, 'calc' => '', 'calc_dec' => '', 'calc_type' => '',
            'dyn_default_value' => '', 'multiple' => 0, 'unique_msg' => $frm_settings->unique_msg, 'autocom' => 0,
            'format' => '', 'repeat' => 0, 'add_label' => __( 'Add', 'formidable' ), 'remove_label' => __( 'Remove', 'formidable' ),
            'conf_field' => '', 'conf_input' => '', 'conf_desc' => '',
            'conf_msg' => __( 'The entered values do not match', 'formidable' ), 'other' => 0,
			'in_section' => 0,
        );

		FrmProLookupFieldsController::add_autopopulate_value_field_options( $values, $field, $opts );

		FrmProLookupFieldsController::add_field_options_specific_to_lookup_field( $values, $field, $opts );

        $opts = apply_filters('frm_default_field_opts', $opts, $values, $field);
		$opts = apply_filters( 'frm_default_'. $field_type .'_field_opts', $opts, $values, $field );

		unset( $values, $field );

        return $opts;
    }

	public static function setup_input_masks( $field ) {
		$html = '';
		$text_lookup = $field['type'] == 'lookup' && $field['data_type'] == 'text';
		$is_format_field = in_array( $field['type'], array( 'phone', 'text' ) ) || $text_lookup;
		if ( self::is_format_option_true_with_no_regex( $field ) &&	$is_format_field ) {
			$html = self::setup_input_mask( $field['format'] );
		}

		return $html;
	}

	public static function setup_input_mask( $format ) {
		global $frm_input_masks;
		$frm_input_masks[] = true;
		return ' data-frmmask="'. esc_attr( preg_replace( '/\d/', '9', $format ) ) .'"';
	}

	/**
	* Initialize the field array when a field is loaded independent of the rest of the form
	*
	* @param object $field_object
	* @return array $args
	*/
	public static function initialize_array_field( $field_object, $args = array() ) {
		$field_values = array( 'id', 'required', 'name', 'description', 'form_id', 'options', 'field_key', 'type' );
		$field = array( 'value' => '' );
		foreach ( $field_values as $field_value ) {
			$field[ $field_value ] = $field_object->{$field_value};
		}

		$field['original_type'] = $field['type'];
		$field['type'] = apply_filters( 'frm_field_type', $field['type'], $field_object, '' );
		$field['size'] = ( isset( $field_object->field_options['size'] ) && $field_object->field_options['size'] != '' ) ? $field_object->field_options['size'] : '';
		$field['blank'] = $field_object->field_options['blank'];
		$field['default_value'] = isset( $args['default_value'] ) ? $args['default_value'] : '';

		if ( isset( $args['field_id'] ) ) {
			// this might not be needed. Is field_id ever different from $field['id']?
			$field['id'] = $args['field_id'];
		}

		return $field;
	}

	/**
	 * Triggered when the repeat option is toggled on the form builder page
	 *
	 * When a field is changed to repeat:
	 *  - Get the metas for the fields in the section
	 *  - Create an entry and change the entry id on those metas
	 *
	 * When a field is changed to not repeat:
	 * 	- Change the entry id on all metas for the first entry to the parent entry id
	 *	- Delete the other entries and meta
	 *
	 * @since 2.0
	 */
	public static function update_for_repeat( $args ) {
		if ( $args['checked'] ) {
			// Switching to repeatable
			self::move_fields_to_form( $args['children'], $args['form_id'] );
			self::move_entries_to_child_form( $args );
			$form_select = $args['form_id'];
		} else {
			// Switching to non-repeatable
			self::move_fields_to_form( $args['children'], $args['parent_form_id'] );
			self::move_entries_to_parent_form( $args );
			$form_select = '';
		}

		// update the repeat setting and form_select
		$section = FrmField::getOne( $args['field_id'] );
		$section->field_options['repeat'] = $args['checked'];
		$section->field_options['form_select'] = $form_select;
		FrmField::update( $args['field_id'], array( 'field_options' => $section->field_options ) );
	}

	/**
	* Move fields to a different form
	* Used when switching from repeating to non-repeating (or vice versa)
	*/
	private static function move_fields_to_form( $field_ids, $form_id ) {
		global $wpdb;

		$where = array( 'id' => $field_ids, 'type !' => 'end_divider' );
		FrmDb::get_where_clause_and_values( $where );
		array_unshift( $where['values'], $form_id );
		$wpdb->query( $wpdb->prepare( 'UPDATE ' . $wpdb->prefix . 'frm_fields SET form_id=%d ' . $where['where'], $where['values'] ) );
	}

	/**
	* Move entries from parent form to child form
	*
	* @since 2.0.09
	*/
	private static function move_entries_to_child_form( $args ) {
		global $wpdb;

		// get the ids of the entries saved in these fields
		$item_ids = FrmDb::get_col( 'frm_item_metas', array( 'field_id' => $args['children'] ), 'item_id', array( 'group_by' => 'item_id' ) );

		foreach ( $item_ids as $old_id ) {
			// Create a new entry in the child form
	        $new_id = FrmEntry::create( array( 'form_id' => $args['form_id'], 'parent_item_id' => $old_id ) );

			// Move the parent item_metas to the child form
			$where = array( 'item_id' => $old_id, 'field_id' => $args['children'] );
			FrmDb::get_where_clause_and_values( $where );
			array_unshift( $where['values'], $new_id );
			$c = $wpdb->query( $wpdb->prepare( 'UPDATE ' . $wpdb->prefix . 'frm_item_metas SET item_id = %d ' . $where['where'], $where['values'] ) );

			if ( $c ) {
				// update the section field meta with the new entry ID
				$u = FrmEntryMeta::update_entry_meta( $old_id, $args['field_id'], null, $new_id );
				if ( ! $u ) {
					// add the row if it wasn't there to update
					FrmEntryMeta::add_entry_meta( $old_id, $args['field_id'], null, $new_id );
				}
			}
		}
	}

	/**
	* Delete entries from repeating sections and transfer first row to parent entries
	*/
	private static function move_entries_to_parent_form( $args ) {
		global $wpdb;

		// get the ids of the entries saved in child fields
		$items = FrmDb::get_results( $wpdb->prefix . 'frm_item_metas m LEFT JOIN ' . $wpdb->prefix . 'frm_items i ON i.id=m.item_id', array( 'field_id' => $args['children'] ), 'item_id,parent_item_id', array( 'order_by' => 'i.created_at ASC' ) );

		$updated_ids = array();
		foreach ( $items as $item ) {
			$child_id = $item->item_id;
			$parent_id = $item->parent_item_id;
			if ( ! in_array( $parent_id, $updated_ids ) ) {
				// Change the item_id in frm_item_metas to match the parent item ID
				$wpdb->query( $wpdb->prepare( 'UPDATE ' . $wpdb->prefix . 'frm_item_metas SET item_id = %d WHERE item_id = %d', $parent_id, $child_id ) );
				$updated_ids[] = $parent_id;
			}

			// Delete the child entry
			FrmEntry::destroy( $child_id );
		}

		// delete all the metas for the repeat section
		$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . 'frm_item_metas WHERE field_id=%d', $args['field_id'] ) );

		// Delete the child form
		FrmForm::destroy( $args['form_id'] );
	}

	/**
	 * Set up the $frm_vars['rules'] array
	 *
	 * @param array $field
	 */
	public static function setup_conditional_fields( $field ) {
		// TODO: prevent this from being called at all on the form builder page
		if ( FrmAppHelper::is_admin_page('formidable' ) ) {
			return;
		}

		global $frm_vars;

		if ( false == self::are_logic_rules_needed_for_this_field( $field, $frm_vars ) ) {
			return;
		}

		self::maybe_initialize_global_rules_array( $frm_vars );

		$logic_rules = self::get_logic_rules_for_field( $field, $frm_vars );

		foreach ( $field['hide_field'] as $i => $logic_field_id ) {
			$logic_field = self::get_field_from_conditional_logic( $logic_field_id );
			if ( ! $logic_field ) {
				continue;
			}
			$add_field = true;

			self::add_condition_to_logic_rules( $field, $i, $logic_rules );

			self::maybe_initialize_logic_field_rules( $logic_field, $field, $frm_vars );

			self::add_to_logic_field_dependents( $logic_field_id, $field['id'], $frm_vars );
		}
		unset( $i, $logic_field_id, $logic_field );

		if ( isset( $add_field ) && $add_field == true ) {

			// Add current field's logic rules to global rules array
			$frm_vars['rules'][ $field['id'] ] = $logic_rules;

			self::set_logic_rule_status_to_complete( $field['id'], $frm_vars );
			self::maybe_add_script_for_confirmation_field( $field, $logic_rules, $frm_vars );
			self::add_field_to_global_dependent_ids( $field, $logic_rules['fieldType'], $frm_vars );
		}
	}

	/**
	 * Check if global conditional logic rules are needed for a field
	 *
	 * @since 2.01.0
	 * @param array $field
	 * @param array $frm_vars
	 * @return bool
	 */
	private static function are_logic_rules_needed_for_this_field( $field, $frm_vars ) {
		$logic_rules_needed = true;

        if ( empty( $field['hide_field'] ) || ( empty( $field['hide_opt'] ) && empty( $field['form_select'] ) ) ) {
        	// Field doesn't have conditional logic on it
            $logic_rules_needed = false;

        } else if ( isset( $frm_vars['rules'][ $field['id'] ]['status'] ) && 'complete' == $frm_vars['rules'][ $field['id'] ]['status'] ) {
        	// Field has already been checked
        	$logic_rules_needed = false;

        } else if ( FrmAppHelper::doing_ajax() && ( ! isset( $frm_vars['footer_loaded'] ) || $frm_vars['footer_loaded'] !== true ) ) {
        	// Don't load rules again when adding a row in a repeating section or turning the page in a "Submit with ajax" form
        	$logic_rules_needed = false;
        }

        return $logic_rules_needed;
	}

	/**
	 * Initialize the $frm_vars rules array if it isn't already initialized
	 *
	 * @since 2.01.0
	 * @param array $frm_vars
	 */
	private static function maybe_initialize_global_rules_array( &$frm_vars ) {
        if ( ! isset( $frm_vars['rules'] ) || ! $frm_vars['rules'] ) {
			$frm_vars['rules'] = array();
		}
	}

	/**
	 * Get the logic rules for the current field
	 *
	 * @since 2.01.0
	 * @param array $field
	 * @param array $frm_vars
	 * @return array
	 */
	private static function get_logic_rules_for_field( $field, $frm_vars ) {
		if ( ! isset( $frm_vars['rules'][ $field['id'] ] ) ) {
			$logic_rules = self::initialize_logic_rules_for_field_array( $field, $field['parent_form_id'] );
		} else {
			$logic_rules = $frm_vars['rules'][ $field['id'] ];
		}

		return $logic_rules;
	}

	/**
	 * Initialize the logic rules for a field
	 *
	 * @since 2.01.0
	 * @param array $field
	 * @param int $form_id
	 * @return array
	 */
	private static function initialize_logic_rules_for_field_array( $field, $form_id ) {
        $original_type = self::get_original_field_type( $field );

		$logic_rules = array(
        	'fieldId' => $field['id'],
			'fieldKey' => $field['field_key'],
			'fieldType' => $original_type,
			'inputType' => self::get_the_input_type_for_logic_rules( $field, $original_type ),
			'isMultiSelect' => FrmField::is_multiple_select( $field ),
			'formId' => $form_id,
			'inSection' => isset( $field['in_section'] ) ? $field['in_section'] : '0',
			'inEmbedForm' => isset( $field['in_embed_form'] ) ? $field['in_embed_form'] : '0',
			'isRepeating' => ( $form_id != $field['form_id'] ),
			'dependents' => array(),
			'showHide' => isset( $field['show_hide'] ) ? $field['show_hide'] : 'show',
			'anyAll' => isset( $field['any_all'] ) ? $field['any_all'] : 'any',
			'conditions' => array(),
        );

        return $logic_rules;
	}

	/**
	 * Get the original field type
	 *
	 * @since 2.01.0
	 * @param array $field
	 * @return string
	 */
	private static function get_original_field_type( $field ) {
		if ( isset( $field['original_type'] ) ) {
			$field_type = $field['original_type'];
		} else {
			$field_type = $field['type'];
		}

		return $field_type;
	}

	/**
	 * Get the input type from a field
	 *
	 * @since 2.01.0
	 * @param array $field
	 * @param string $field_type
	 * @return string
	 */
	private static function get_the_input_type_for_logic_rules( $field, $field_type ) {
		if ( $field_type == 'data' || $field_type == 'lookup' ) {
			$cond_type = $field['data_type'];
		} else if ( $field_type == 'scale' ) {
			$cond_type = 'radio';
		} else {
			$cond_type = $field_type;
		}
		$cond_type = apply_filters( 'frm_logic_' . $field_type . '_input_type', $cond_type );

		return $cond_type;
	}

	/**
	 * Set the logic rule status to complete
	 *
	 * @since 2.01.0
	 * @param int $field_id
	 * @param array $frm_vars
	 */
	private static function set_logic_rule_status_to_complete( $field_id, &$frm_vars ) {
		$frm_vars['rules'][ $field_id ]['status'] = 'complete';
	}

	/**
	 * Get the field object for a logic field
	 *
	 * @since 2.01.0
	 * @param mixed $logic_field_id
	 * @return boolean|object
	 */
	private static function get_field_from_conditional_logic( $logic_field_id ) {
		// TODO: maybe get rid of the getOne call here if the field already exists in $frm_vars['rules']?
		if ( ! is_numeric( $logic_field_id ) ) {
			$logic_field = false;
		} else {
			$logic_field = FrmField::getOne( $logic_field_id );
		}

		return $logic_field;
	}

	/**
	 * Add a row of conditional logic to the logic_rules array
	 *
	 * @since 2.01.0
	 * @param array $field
	 * @param int $i
	 * @param array $logic_rules
	 */
	private static function add_condition_to_logic_rules( $field, $i, &$logic_rules ) {
		$value = self::get_default_value( $field['hide_opt'][ $i ], $field, false );
		$logic_rules['conditions'][] = array(
			'fieldId'  => $field['hide_field'][ $i ],
			'operator' => $field['hide_field_cond'][ $i ],
			'value'    => $value,
		);
	}

	/**
	 * Add a logic field to the frm_vars rules array
	 *
	 * @since 2.01.0
	 * @param object $logic_field
	 * @param array $dependent_field
	 * @param array $frm_vars
	 */
	private static function maybe_initialize_logic_field_rules( $logic_field, $dependent_field, &$frm_vars ) {
		if ( ! isset( $frm_vars['rules'][ $logic_field->id ] ) ) {
			if ( self::is_logic_field_in_embedded_form_with_dependent_field( $logic_field, $dependent_field ) ) {
				$logic_field->in_embed_form = $dependent_field['in_embed_form'];
			}
			$frm_vars['rules'][ $logic_field->id ] = self::initialize_logic_rules_for_fields_object( $logic_field, $dependent_field['parent_form_id'] );
		}
	}

	/**
	 * Check if a dependent field is in an embedded form and if logic field is also in that embedded form
	 *
	 * @since 2.02.06
	 * @param object $logic_field
	 * @param array $dependent_field
	 * @return bool
	 */
	private static function is_logic_field_in_embedded_form_with_dependent_field( $logic_field , $dependent_field ) {
		return FrmField::is_option_true_in_array( $dependent_field, 'in_embed_form' ) && $logic_field->form_id == $dependent_field['form_id'];
	}

	/**
	 * Initialize the logic rules for a field object
	 *
	 * @since 2.01.0
	 * @param object $field
	 * @param int $form_id
	 * @return array
	 */
	private static function initialize_logic_rules_for_fields_object( $field, $form_id ) {
		$field_array = self::convert_field_object_to_flat_array( $field );
		return self::initialize_logic_rules_for_field_array( $field_array, $form_id );
	}

	/**
	 * @param object $field
	 * @return array $field_array
	 */
	public static function convert_field_object_to_flat_array( $field ) {
		return FrmFieldsHelper::convert_field_object_to_flat_array( $field );
	}

	/**
	 * Add dependent field to logic field's dependents
	 *
	 * @since 2.01.0
	 * @param int $logic_field_id
	 * @param int $dep_field_id
	 * @param array $frm_vars
	 */
	private static function add_to_logic_field_dependents( $logic_field_id, $dep_field_id, &$frm_vars ) {
		$frm_vars['rules'][ $logic_field_id ]['dependents'][] = $dep_field_id;
	}

	/**
	 * Add rules for a confirmation field
	 *
	 * @since 2.01.0
	 * @param array $field
	 * @param array $logic_rules
	 * @param array $frm_vars
	 */
	private static function maybe_add_script_for_confirmation_field( $field, $logic_rules, &$frm_vars ){
		// TODO: maybe move confirmation field inside of field div
		if ( ! FrmField::is_option_empty( $field, 'conf_field' ) ) {

			// Add the rules for confirmation field
			$conf_field_rules = $logic_rules;
			$conf_field_rules['fieldId'] = 'conf_' . $logic_rules['fieldId'];
			$conf_field_rules['fieldKey'] = 'conf_' . $logic_rules['fieldKey'];
			$frm_vars['rules'][ 'conf_' . $field['id'] ] = $conf_field_rules;

			// Add to all logic field dependents
			self::add_conf_field_to_logic_field_dependents( $conf_field_rules, $frm_vars );
		}
	}

	/**
	 * Add confirmation field as a dependent for all of its logic fields
	 *
	 * @since 2.01.0
	 * @param array $conf_field_rules
	 * @param array $frm_vars
	 */
	private static function add_conf_field_to_logic_field_dependents( $conf_field_rules, &$frm_vars ) {
		foreach ( $conf_field_rules['conditions'] as $condition ) {
			self::add_to_logic_field_dependents( $condition['fieldId'], $conf_field_rules['fieldId'], $frm_vars );
		}
	}

	/**
	 * Add dependent field to the dep_logic_fields or dep_dynamic_fields array
	 *
	 * @since 2.01.0
	 * @param array $field
	 * @param string $original_field_type
	 * @param array $frm_vars
	 */
	private static function add_field_to_global_dependent_ids( $field, $original_field_type, &$frm_vars ) {
		if ( $original_field_type == 'data' ) {
			// Add to dep_dynamic_fields
			if ( ! isset( $frm_vars['dep_dynamic_fields'] ) ) {
				$frm_vars['dep_dynamic_fields'] = array();
			}
			$frm_vars['dep_dynamic_fields'][] = $field['id'];
		} else {
			// Add to dep_logic_fields
			if ( ! isset( $frm_vars['dep_logic_fields'] ) ) {
				$frm_vars['dep_logic_fields'] = array();
			}
			$frm_vars['dep_logic_fields'][] = $field['id'];

			if ( FrmField::is_option_true_in_array( $field, 'conf_field' ) ) {
				$frm_vars['dep_logic_fields'][] = 'conf_' . $field['id'];
			}
		}
	}

	public static function get_category_options( $field ) {
		// TODO: Dynamic fields get categories here - maybe combine with FrmProPost::get_category_dropdown()?
		if ( is_object( $field ) ) {
			$field = (array) $field;
			$field = array_merge( $field, $field['field_options'] );
		}

        $post_type = FrmProFormsHelper::post_type($field['form_id']);
        if ( ! isset($field['exclude_cat']) ) {
            $field['exclude_cat'] = 0;
        }

        $exclude = (is_array($field['exclude_cat'])) ? implode(',', $field['exclude_cat']) : $field['exclude_cat'];
        $exclude = apply_filters('frm_exclude_cats', $exclude, $field);

        $args = array(
            'orderby' => 'name', 'order' => 'ASC', 'hide_empty' => false,
            'exclude' => $exclude, 'type' => $post_type
        );

		if ( $field['type'] != 'data' ) {
            $args['parent'] = '0';
		}

        $args['taxonomy'] = FrmProAppHelper::get_custom_taxonomy($post_type, $field);
        if ( ! $args['taxonomy'] ) {
            return;
        }

        $args = apply_filters('frm_get_categories', $args, $field);

        $categories = get_categories($args);

        $options = array();
		foreach ( $categories as $cat ) {
			$options[ $cat->term_id ] = $cat->name;
		}

        $options = apply_filters('frm_category_opts', $options, $field, array( 'cat' => $categories, 'args' => $args) );

        return $options;
    }

	public static function get_child_checkboxes( $args ) {
        $defaults = array(
            'field' => 0, 'field_name' => false, 'opt_key' => 0, 'opt' => '',
            'type' => 'checkbox', 'value' => false, 'exclude' => 0, 'hide_id' => false,
            'tax_num' => 0
        );
        $args = wp_parse_args($args, $defaults);

        if ( ! $args['field'] || ! isset($args['field']['post_field']) || $args['field']['post_field'] != 'post_category' ) {
            return;
        }

        if ( ! $args['value'] ) {
            $args['value'] = isset($args['field']['value']) ? $args['field']['value'] : '';
        }

        if ( ! $args['exclude'] ) {
            $args['exclude'] = is_array($args['field']['exclude_cat']) ? implode(',', $args['field']['exclude_cat']) : $args['field']['exclude_cat'];
            $args['exclude'] = apply_filters('frm_exclude_cats', $args['exclude'], $args['field']);
        }

        if ( ! $args['field_name'] ) {
            $args['field_name'] = 'item_meta['. $args['field']['id'] .']';
        }

        if ( $args['type'] == 'checkbox' ) {
            $args['field_name'] .= '[]';
        }
        $post_type = FrmProFormsHelper::post_type($args['field']['form_id']);
        $taxonomy = 'category';

        $cat_atts = array(
            'orderby' => 'name', 'order' => 'ASC', 'hide_empty' => false,
            'parent' => $args['opt_key'], 'exclude' => $args['exclude'], 'type' => $post_type,
        );
        if ( ! $args['opt_key'] ) {
            $cat_atts['taxonomy'] = FrmProAppHelper::get_custom_taxonomy($post_type, $args['field']);
			if ( ! $cat_atts['taxonomy'] ) {
                echo '<p>'. __( 'No Categories', 'formidable' ) .'</p>';
                return;
            }

            $taxonomy = $cat_atts['taxonomy'];
        }

        $children = get_categories($cat_atts);
        unset($cat_atts);
    
        $level = $args['opt_key'] ? 2 : 1;
    	foreach ( $children as $key => $cat ) {  ?>
    	<div class="frm_catlevel_<?php echo (int) $level ?>"><?php self::_show_category( array(
            'cat' => $cat, 'field' => $args['field'], 'field_name' => $args['field_name'],
            'exclude' => $args['exclude'], 'type' => $args['type'], 'value' => $args['value'],
            'level' => $level, 'onchange' => '', 'post_type' => $post_type,
            'taxonomy' => $taxonomy, 'hide_id' => $args['hide_id'], 'tax_num' => $args['tax_num'],
        )) ?></div>
<?php   }
    }

    /**
    * Get the max depth for any given taxonomy (recursive function)
    *
    * Since 2.0
    *
    * @param string $cat_name - taxonomy name
    * @param int $parent - parent ID, 0 by default
    * @param int $cur_depth - depth of current taxonomy path
    * @param int $max_depth - max depth of given taxonomy
    * @return int $max_depth - max depth of given taxonomy
    */
	public static function get_category_depth( $cat_name, $parent = 0, $cur_depth = 0, $max_depth = 0 ) {
        if ( ! $cat_name ) {
            $cat_name = 'category';
        }

        // Return zero if taxonomy is not hierarchical
        if ( $parent == 0 && ! is_taxonomy_hierarchical( $cat_name ) ) {
            $max_depth = 0;
            return $max_depth;
        }

        // Get all level one categories first
        $categories = get_categories( array( 'number' => 10, 'taxonomy' => $cat_name, 'parent' => $parent, 'orderby' => 'name', 'order' => 'ASC', 'hide_empty' => false ) );

        //Only go 5 levels deep at the most
        if ( empty( $categories ) || $cur_depth == 5 ) {
            // Only update the max depth, if the current depth is greater than the max depth so far
            if ( $cur_depth > $max_depth ) {
                $max_depth = $cur_depth;
            }

            return $max_depth;
        }

        // Increment the current depth
        $cur_depth++;

        foreach ( $categories as $key => $cat ) {
            $parent = $cat->cat_ID;
            // Get children
            $max_depth = self::get_category_depth( $cat_name, $parent, $cur_depth, $max_depth );
        }
        return $max_depth;
    }

    public static function _show_category($atts) {
    	if ( ! is_object($atts['cat']) ) {
    	    return;
    	}

    	if ( is_array($atts['value']) ) {
    		$checked = (in_array($atts['cat']->cat_ID, $atts['value'])) ? 'checked="checked" ' : '';
    	} else if ( $atts['cat']->cat_ID == $atts['value'] ) {
    	    $checked = 'checked="checked" ';
    	} else {
    	    $checked = '';
    	}

    	$sanitized_name = ( isset($atts['field']['id']) ? $atts['field']['id'] : $atts['field']['field_options']['taxonomy'] ) .'-'. $atts['cat']->cat_ID;
        // Makes sure ID is unique for excluding checkboxes in Categories/Taxonomies in Create Post action
        if ( $atts['tax_num'] ) {
            $sanitized_name .= '-' . $atts['tax_num'];
        }

    	?>
    	<div class="frm_<?php echo esc_attr( $atts['type'] ) ?>" id="frm_<?php echo esc_attr( $atts['type'] .'_'. $sanitized_name ) ?>">
    	    <label for="field_<?php echo esc_attr( $sanitized_name ) ?>"><input type="<?php echo esc_attr( $atts['type'] ) ?>" name="<?php echo esc_attr( $atts['field_name'] ) ?>" <?php
    	    echo ( isset($atts['hide_id']) && $atts['hide_id'] ) ? '' : 'id="field_'. esc_attr( $sanitized_name ) .'"';
    	    ?> value="<?php echo esc_attr( $atts['cat']->cat_ID ) ?>" <?php
    	    echo $checked;
    	    do_action('frm_field_input_html', $atts['field']);
    	    //echo ($onchange);
    	    ?> /><?php echo esc_html( $atts['cat']->cat_name ) ?></label>
<?php
    	$children = get_categories( array(
    	    'type' => $atts['post_type'], 'orderby' => 'name',
    	    'order' => 'ASC', 'hide_empty' => false, 'exclude' => $atts['exclude'],
    	    'parent' => $atts['cat']->cat_ID, 'taxonomy' => $atts['taxonomy'],
    	));

    	if ( $children ) {
    	    $atts['level']++;
    	    foreach ( $children as $key => $cat ) {
    	        $atts['cat'] = $cat; ?>
    	<div class="frm_catlevel_<?php echo esc_attr( $atts['level'] ) ?>"><?php self::_show_category( $atts ); ?></div>
<?php       }
        }
    	echo '</div>';
    }

    /**
	 * Filter the post status options for the current user
	 * Add default options if there are no valid options
	 *
	 * @param object $field
	 * @param array $options
	 *
	 * @return array
	 */
	public static function get_status_options( $field, $options = array() ) {
		return self::get_post_status_options( $field->form_id, $options );
	}

	/**
	 * Filter the post status options for the current user
	 * Add default options if there are no valid options
	 *
	 * @param string $form_id
	 * @param array $options
	 *
	 * @return array
	 */
	public static function get_post_status_options( $form_id, $options = array() ) {
		if ( FrmAppHelper::is_admin() ) {
			$post_status_options = self::get_initial_post_status_options();
		} else {
			$post_status_options = self::get_post_status_options_for_current_user( $form_id );
		}

		if ( empty( $post_status_options ) ) {
			return array();
		}

		$post_status_keys = array_keys( $post_status_options );
		$post_status_keys[] = 'publish'; // allow publish to be included as an option for everyone

		$final_options = array();
		foreach ( $options as $opt_key => $opt ) {
			if ( is_array( $opt ) ){
				$opt_key = isset( $opt['value'] ) ? $opt['value'] : ( isset( $opt['label'] ) ? $opt['label'] : reset( $opt ) );
			} else {
				$opt_key = $opt;
			}

			if ( in_array( $opt_key, $post_status_keys ) ) {
				$final_options[ $opt_key ] = $opt;
			}
		}

		if ( empty( $final_options ) ) {
			$final_options = $post_status_options;
		}

		return $final_options;
	}

    /**
	 * Get the initial options for a Post Status field
	 *
	 * @since 2.03.01
	 *
	 * @return array
	 */
	public static function get_initial_post_status_options() {
		$post_statuses = get_post_statuses();

		foreach ( $post_statuses as $key => $value ) {
			$post_statuses[ $key ] = array( 'label' => $value, 'value' => $key );
		}

		return $post_statuses;
	}

    /**
	 * Get the possible post status options for the current user
	 *
	 * @since 2.03.01
	 *
	 * @param string $form_id
	 *
	 * @return array - associative array with lowercase post status options as keys
	 */
	private static function get_post_status_options_for_current_user( $form_id ) {
		$post_status_options = array();
		$post_type = FrmProFormsHelper::post_type( $form_id );
		$post_type_object = get_post_type_object( $post_type );

		if ( ! $post_type_object ) {
			return $post_status_options;
		}

		$post_status_options = get_post_statuses();

		// Remove options that the current user should not have
		$can_publish = current_user_can( $post_type_object->cap->publish_posts );
		if ( ! $can_publish ) {
			unset( $post_status_options['publish'] );

			if ( isset( $post_status_options['future'] ) ) {
				unset( $post_status_options['future'] );
			}
		}

		return $post_status_options;
	}

	public static function get_user_options() {
		$users = get_users( array(
			'fields' => array( 'ID', 'user_login', 'display_name'),
			'blog_id' => $GLOBALS['blog_id'],
			'orderby' => 'display_name',
		) );

		$options = array( '' => '' );
		foreach ( $users as $user ) {
			$options[ $user->ID ] = ( ! empty( $user->display_name ) ) ? $user->display_name : $user->user_login;
		}
        return $options;
    }

    public static function posted_field_ids( $where ) {
		$form_id = FrmAppHelper::get_post_param( 'form_id', 0, 'absint' );
		if ( $form_id && FrmProFormsHelper::has_another_page( $form_id ) ) {
			$where['fi.field_order <'] = FrmAppHelper::get_post_param( 'frm_page_order_' . $form_id, 0, 'absint' );
        }
        return $where;
    }

	public static function set_field_js( $field, $id = 0 ) {
        global $frm_vars;

        if ( ! isset($frm_vars['datepicker_loaded']) || ! is_array($frm_vars['datepicker_loaded']) ) {
            return;
        }

        $field_key = '';
        if ( isset($frm_vars['datepicker_loaded']['^field_'. $field['field_key']]) && $frm_vars['datepicker_loaded']['^field_'. $field['field_key']] ) {
            $field_key = '^field_'. $field['field_key'];
        } else if ( isset($frm_vars['datepicker_loaded']['field_'. $field['field_key']]) && $frm_vars['datepicker_loaded']['field_'. $field['field_key']] ) {
            $field_key = 'field_'. $field['field_key'];
        }

        if ( empty($field_key) ) {
            return;
        }

		$field[ 'start_year' ] = self::convert_to_static_year( $field[ 'start_year' ] );
		$field[ 'end_year' ] = self::convert_to_static_year( $field[ 'end_year' ] );

		$default_date = self::get_default_cal_date( $field['start_year'], $field['end_year'] );

        $field_js = array(
            'start_year' => $field['start_year'], 'end_year' => $field['end_year'],
            'locale' => $field['locale'], 'unique' => $field['unique'],
            'field_id' => $field['id'], 'entry_id' => $id, 'default_date' => $default_date,
        );
        $frm_vars['datepicker_loaded'][$field_key] = $field_js;
    }

	/**
	 * If using -100, +10, or maybe just 10 for the start or end year
	 * @since 2.0.12
	 */
	public static function convert_to_static_year( $year ) {
		if ( strlen( $year ) != 4 || strpos( $year, '-' ) !== false || strpos( $year, '+' ) !== false ) {
			$year = date( 'Y', strtotime( $year .' years' ) );
		}
		return (int) $year;
	}

	/**
	* Set the default date for jQuery calendar
	*
	* @since 2.0.12
	* @param int $start_year
	* @param int $end_year
	* @return string $default_date
	*/
	private static function get_default_cal_date( $start_year, $end_year ) {
		$current_year = (int) date('Y');

		// If current year falls inside of the date range, make the default date today's date
		if ( $current_year >= $start_year && $current_year <= $end_year ) {
			$default_date = '';
		} else {
			$default_date = 'January 1, ' . $start_year . ' 00:00:00';
		}

		return $default_date;
	}

    public static function get_form_fields( $fields, $form_id, $errors = array() ) {
		$error = ! empty( $errors );
		$page_numbers = self::get_base_page_info( compact( 'fields', 'form_id', 'error', 'errors' ) );

		$ajax = FrmProFormsHelper::has_form_setting( array( 'form_id' => $form_id, 'setting_name' => 'ajax_submit', 'expected_setting' => 1 ) );

		foreach ( (array) $fields as $k => $f ) {

			// prevent sub fields from showing
			if ( $f->form_id != $form_id ) {
				unset( $fields[ $k ] );
			}

			if ( $ajax ) {
				self::set_ajax_field_globals( $f );
			}

			if ( $f->type != 'break' ) {
				continue;
			}

			$page_numbers['page_breaks'][ $f->field_order ] = $f;

			self::get_next_and_prev_page( $f, $error, $page_numbers );

			unset( $f, $k );
		}
		unset( $ajax );

		if ( empty( $page_numbers['page_breaks'] ) ) {
			// there are no page breaks, so let's not check the pagination
			return $fields;
		}

        if ( ! $page_numbers['prev_page_obj'] && $page_numbers['prev_page'] ) {
            $page_numbers['prev_page'] = 0;
        }

		self::skip_conditional_pages( $page_numbers );
		self::set_prev_page_global( $form_id, $page_numbers );
		self::set_next_page_to_field_order( $form_id, $page_numbers );

		self::set_page_num_global( $page_numbers );

        unset( $page_numbers['page_breaks'] );

		self::set_fields_to_hidden( $fields, $page_numbers );

        return $fields;
    }

	/**
	 * @param array $atts - includes form_id, error, fields
	 */
	public static function get_base_page_info( $atts ) {
		$page_numbers = array(
			'page_breaks' => array(), 'go_back' => false, 'next_page' => false,
			'set_prev' => 0, 'set_next' => false,
			'get_last' => false, 'prev_page_obj' => false,
			'prev_page' => FrmAppHelper::get_param( 'frm_page_order_' . $atts['form_id'], false, 'get', 'absint' ),
		);

		if ( FrmProFormsHelper::going_to_prev( $atts['form_id'] ) ) {
			$page_numbers['go_back'] = true;
			$page_numbers['next_page'] = FrmAppHelper::get_param( 'frm_next_page' );
			$page_numbers['prev_page'] = $page_numbers['set_prev'] = $page_numbers['next_page'] - 1;
		} else if ( FrmProFormsHelper::saving_draft() && ! $atts['error'] ) {
			$page_numbers['next_page'] = FrmAppHelper::get_param( 'frm_page_order_' . $atts['form_id'], false );

			// If next_page is zero, assume user clicked "Save Draft" on last page of form
			if ( $page_numbers['next_page'] == 0 ) {
				$page_numbers['next_page'] = count( $atts['fields'] );
			}

			$page_numbers['prev_page'] = $page_numbers['set_prev'] = $page_numbers['next_page'] - 1;
		}

        if ( $atts['error'] ) {
            $page_numbers['set_prev'] = $page_numbers['prev_page'];

            if ( $page_numbers['prev_page'] ) {
				$came_from_page = self::get_last_page_num( $atts );

				if ( false === $came_from_page ) {
					$page_numbers['prev_page'] = $page_numbers['prev_page'] - 1;
				} else {
					$page_numbers['prev_page'] = $came_from_page - 1;
					if ( $page_numbers['set_prev'] ) {
						$page_numbers['set_prev'] = $page_numbers['prev_page'];
					}
				}
            } else {
                $page_numbers['prev_page'] = 999;
                $page_numbers['get_last'] = true;
            }
        }

		return $page_numbers;
	}

	private static function get_last_page_num( $atts ) {
		$has_last_page = isset( $_POST['frm_last_page'] );
		if ( $has_last_page ) {
			$came_from_page = FrmAppHelper::get_param( 'frm_last_page', false, 'get', 'sanitize_text_field' );
		} else {
			$came_from_page = false;
		}

		self::get_page_with_error( $atts, $came_from_page );
		return $came_from_page;
	}

	private static function get_page_with_error( $atts, &$came_from_page ) {
		if ( empty( $atts['errors'] ) ) {
			return;
		}

		$error_fields = array_keys( $atts['errors'] );
		$field_ids = array();
		foreach ( $error_fields as $error_field ) {
			if ( strpos( $error_field, 'field' ) === 0 ) {
				$field_ids[] = str_replace( 'field', '', $error_field );
			}
		}

		if ( ! empty( $field_ids ) ) {
			$first_error = FrmDb::get_var( 'frm_fields', array( 'id' => $field_ids ), 'field_order', array( 'order_by' => 'field_order ASC' ) );
			if ( is_numeric( $first_error ) ) {
				$came_from_page = $first_error + 1;
			}
		}
	}

	/**
	 * When a form is loaded with ajax, we need all the info for
	 * the fields included in the footer with the first page
	 */
	private static function set_ajax_field_globals( $f ) {
		global $frm_vars;
		$ajax_now = ! FrmAppHelper::doing_ajax();
		if ( ! $ajax_now && isset( $frm_vars['inplace_edit'] ) && $frm_vars['inplace_edit'] ) {
			$ajax_now = true;
		}

		switch ( $f->type ) {
			case 'date':
				if ( ! FrmField::is_read_only( $f ) ) {
					if ( ! isset( $frm_vars['datepicker_loaded'] ) || ! is_array( $frm_vars['datepicker_loaded'] ) ) {
						$frm_vars['datepicker_loaded'] = array();
					}
					$frm_vars['datepicker_loaded'][ 'field_' . $f->field_key ] = $ajax_now;
				}
			break;
			case 'time':
				if ( isset( $f->field_options['unique'] ) && $f->field_options['unique'] &&
				 isset( $f->field_options['single_time'] ) && $f->field_options['single_time']) {
					if ( ! isset( $frm_vars['timepicker_loaded'] ) ) {
						$frm_vars['timepicker_loaded'] = array();
					}
					$frm_vars['timepicker_loaded'][ 'field_' . $f->field_key ] = $ajax_now;
				}
			break;
			case 'text':
			case 'phone':
				if ( self::is_format_option_true_with_no_regex( $f ) ) {
					global $frm_input_masks;
					$frm_input_masks[] = $ajax_now;
				}
			break;
		}
	}

	/**
	 * Check if the format option isset and true without a regular expression
	 *
	 * @since 2.02.06
	 * @param array|object $field
	 * @return bool
	 */
	private static function is_format_option_true_with_no_regex( $field ) {
		$has_non_regex_format = false;

		if ( is_array( $field ) ) {
			$has_non_regex_format = FrmField::is_option_true_in_array( $field, 'format' ) && strpos( $field['format'], '^' ) !== 0;
		} else {
			FrmField::is_option_true_in_object( $field, 'format' ) && strpos( $field->field_options['format'], '^' ) !== 0;
		}

		return $has_non_regex_format;
	}

	private static function get_next_and_prev_page( $f, $error, &$page_numbers ) {
        if ( ( $page_numbers['prev_page'] || $page_numbers['go_back'] ) && ! $page_numbers['get_last'] ) {
            if ( ( ( $error || $page_numbers['go_back'] ) && $f->field_order < $page_numbers['prev_page'] ) || ( ! $error && ! $page_numbers['go_back'] && ! $page_numbers['prev_page_obj'] && $f->field_order == $page_numbers['prev_page'] ) ) {
                $page_numbers['prev_page_obj'] = true;
                $page_numbers['prev_page'] = $f->field_order;
            } else if ( $page_numbers['set_prev'] && $f->field_order < $page_numbers['set_prev'] ) {
                $page_numbers['prev_page_obj'] = true;
                $page_numbers['prev_page'] = $f->field_order;
            } else if ( ( $f->field_order > $page_numbers['prev_page'] ) && ! $page_numbers['set_next'] && ( ! $page_numbers['next_page'] || is_numeric( $page_numbers['next_page'] ) ) ) {
                $page_numbers['next_page'] = $f;
                $page_numbers['set_next'] = true;
            }
		} else if ( $page_numbers['get_last'] ) {
            $page_numbers['prev_page_obj'] = true;
            $page_numbers['prev_page'] = $f->field_order;
            $page_numbers['next_page'] = false;
        } else if ( ! $page_numbers['next_page'] ) {
            $page_numbers['next_page'] = $f;
        } else if ( is_numeric( $page_numbers['next_page'] ) && $f->field_order == $page_numbers['next_page'] ) {
            $page_numbers['next_page'] = $f;
        }
	}

	private static function skip_conditional_pages( &$page_numbers ) {
		if ( $page_numbers['prev_page'] ) {
            $current_page = $page_numbers['page_breaks'][ $page_numbers['prev_page'] ];
            if ( self::is_field_hidden( $current_page, stripslashes_deep( $_POST ) ) ) {
                $current_page = apply_filters( 'frm_get_current_page', $current_page, $page_numbers['page_breaks'], $page_numbers['go_back'] );
				if ( ! $current_page || $current_page->field_order != $page_numbers['prev_page'] ) {
					$page_numbers['prev_page'] = $current_page ? $current_page->field_order : 0;
                    foreach ( $page_numbers['page_breaks'] as $o => $pb ) {
                        if ( $o > $page_numbers['prev_page'] ) {
                            $page_numbers['next_page'] = $pb;
                            break;
                        }
                    }

					if ( $page_numbers['next_page']->field_order <= $page_numbers['prev_page'] ) {
                        $page_numbers['next_page'] = false;
					}
                }
            }
        }
	}

	private static function set_prev_page_global( $form_id, $page_numbers ) {
		global $frm_vars;
		if ( $page_numbers['prev_page'] ) {
			$frm_vars['prev_page'][ $form_id ] = $page_numbers['prev_page'];
		} else {
			unset( $frm_vars['prev_page'][ $form_id ] );
		}
	}

	private static function set_next_page_to_field_order( $form_id, &$page_numbers ) {
		global $frm_vars;
		if ( $page_numbers['next_page'] ) {
			if ( is_numeric( $page_numbers['next_page'] ) && isset( $page_numbers['page_breaks'][ $page_numbers['next_page'] ] ) ) {
				$page_numbers['next_page'] = $page_numbers['page_breaks'][ $page_numbers['next_page'] ];
			}

			if ( ! is_numeric( $page_numbers['next_page'] ) ) {
				$frm_vars['next_page'][ $form_id ] = $page_numbers['next_page'];
				$page_numbers['next_page'] = $page_numbers['next_page']->field_order;
			}
		}else{
			unset( $frm_vars['next_page'][ $form_id ] );
		}
	}

	private static function set_page_num_global( $page_numbers ) {
		global $frm_page_num;
        $pages = array_keys( $page_numbers['page_breaks'] );
        $frm_page_num = $page_numbers['prev_page'] ? ( array_search( $page_numbers['prev_page'], $pages ) + 2 ) : 1;
	}

	private static function set_fields_to_hidden( &$fields, $page_numbers ) {
		if ( $page_numbers['next_page'] || $page_numbers['prev_page'] ) {
			foreach ( $fields as $f ) {
				if ( $f->type == 'hidden' || $f->type == 'user_id' ) {
                    continue;
				}

				if ( self::hide_on_page( $page_numbers, $f ) ) {
					$f->field_options['original_type'] = $f->type;
					$f->type = 'hidden';
                }

                unset($f);
            }
        }
	}

	/**
	 * Check if a field should be hidden on the current page
	 */
	private static function hide_on_page( $page_numbers, $f ) {
		return ( $page_numbers['prev_page'] && $page_numbers['next_page'] && ( $f->field_order < $page_numbers['prev_page'] ) && ( $f->field_order > $page_numbers['next_page'] ) ) || ( $page_numbers['prev_page'] && $f->field_order < $page_numbers['prev_page'] ) || ( $page_numbers['next_page'] && $f->field_order > $page_numbers['next_page'] );
	}

	public static function get_current_page( $next_page, $page_breaks, $go_back, $order = 'asc' ) {
        $first = $next_page;
        $set_back = false;

        if ( $go_back && $order == 'asc' ) {
            $order = 'desc';
            $page_breaks = array_reverse( $page_breaks, true );
        }

		foreach ( $page_breaks as $pb ) {
			if ( $go_back && $pb->field_order < $next_page->field_order ) {
				$next_page = $pb;
				$set_back = true;
				break;
			} else if ( ! $go_back && $pb->field_order > $next_page->field_order && $pb->field_order != $first->field_order ) {
				$next_page = $pb;
				break;
			}
			unset( $pb );
		}

        if ( $go_back && ! $set_back ) {
            $next_page = 0;
        }

		if ( self::skip_next_page( $next_page ) ) {
			if ( $first == $next_page ) {
				// the last page is conditional
				$next_page = -1;
			} else {
				$next_page = self::get_current_page( $next_page, $page_breaks, $go_back, $order );
			}
		}

        return $next_page;
    }

	private static function skip_next_page( $next_page ) {
		return $next_page && self::is_field_hidden( $next_page, stripslashes_deep( $_POST ) );
	}

    public static function show_custom_html($show, $field_type) {
        if ( in_array($field_type, array( 'hidden', 'user_id', 'break', 'end_divider')) ) {
            $show = false;
        }
        return $show;
    }

	public static function get_default_html( $default_html, $type ) {
		if ( $type == 'divider' ) {
            $default_html = <<<DEFAULT_HTML
<div id="frm_field_[id]_container" class="frm_form_field frm_section_heading form-field[error_class]">
<h3 class="frm_pos_[label_position][collapse_class]">[field_name]</h3>
[if description]<div class="frm_description">[description]</div>[/if description]
[collapse_this]
</div>
DEFAULT_HTML;
		} else if ( $type == 'html' ) {
            $default_html = '<div id="frm_field_[id]_container" class="frm_form_field form-field">[description]</div>';
        } else if ( $type == 'form' ) {
            $default_html = <<<DEFAULT_HTML
<div id="frm_field_[id]_container" class="frm_form_field form-field [required_class][error_class]">
[input]
</div>
DEFAULT_HTML;
        }

        return $default_html;
    }

    /**
    * Check if field is radio or Dynamic radio
    *
    * Since 2.0
    *
    * @param array $field
    * @return boolean true if field type is radio or Dynamic radio
    */
    public static function is_radio( $field ) {
        return ( $field['type'] == 'radio' || ( $field['type'] == 'data' && $field['data_type'] == 'radio' ) || ( $field['type'] == 'lookup' && $field['data_type'] == 'radio' ) );
    }

    /**
    * Check if field is checkbox or Dynamic checkbox
    *
    * Since 2.0
    *
    * @param array $field
    * @return boolean true if field type is checkbox or Dynamic checkbox
    */
    public static function is_checkbox( $field ) {
        return ( $field['type'] == 'checkbox' || ( $field['type'] == 'data' && $field['data_type'] == 'checkbox' ) || ( $field['type'] == 'lookup' && $field['data_type'] == 'checkbox' ) );
    }

	public static function before_replace_shortcodes( $html, $field ) {
		$is_radio = self::is_radio( $field );
		$is_checkbox = self::is_checkbox( $field );

		if ( isset( $field['align'] ) && ( $is_radio || $is_checkbox ) ) {
            $required_class = '[required_class]';

			$radio_align = ( $is_radio && $field['align'] != FrmStylesController::get_style_val( 'radio_align', $field['form_id'] ) );
			$check_align = ( $is_checkbox && $field['align'] != FrmStylesController::get_style_val( 'check_align', $field['form_id'] ) );

			if ( $radio_align || $check_align ) {
				$required_class .= ( $field['align'] == 'inline' ) ? ' horizontal_radio' : ' vertical_radio';
                $html = str_replace('[required_class]', $required_class, $html);
            }
        }

		if ( isset( $field['classes'] ) && strpos( $field['classes'], 'frm_grid' ) !== false ) {
            $opt_count = count($field['options']) + 1;
            $html = str_replace('[required_class]', '[required_class] frm_grid_'. $opt_count, $html);
			if ( strpos( $html, ' horizontal_radio' ) ) {
                $html = str_replace(' horizontal_radio', ' vertical_radio', $html);
			}
            unset($opt_count);
        }

        return $html;
    }

    public static function replace_html_shortcodes($html, $field, $atts) {
        if ( 'divider' == $field['type'] ) {
            global $frm_vars;

            $html = str_replace( array( 'frm_none_container', 'frm_hidden_container', 'frm_top_container', 'frm_left_container', 'frm_right_container'), '', $html);

            if ( isset($frm_vars['collapse_div']) && $frm_vars['collapse_div'] ) {
                $html = "</div>\n". $html;
                $frm_vars['collapse_div'] = false;
            }

			if ( isset($frm_vars['div']) && $frm_vars['div'] && $frm_vars['div'] != $field['id'] ) {
				// close the div if it's from a different section
				$html = "</div>\n". $html;
				$frm_vars['div'] = false;
			}

			if ( FrmField::is_option_true( $field, 'slide' ) ) {
                $trigger = ' frm_trigger';
                $collapse_div = '<div class="frm_toggle_container" style="display:none;">';
            } else {
                $trigger = $collapse_div = '';
            }

			if ( FrmField::is_option_true( $field, 'repeat' ) ) {
                $errors = isset($atts['errors']) ? $atts['errors'] : array();
                $field_name = 'item_meta['. $field['id'] .']';
                $html_id = FrmFieldsHelper::get_html_id($field);
                $frm_settings = FrmAppHelper::get_settings();

                ob_start();
                include(FrmAppHelper::plugin_path() .'/classes/views/frm-fields/input.php');
                $input = ob_get_contents();
                ob_end_clean();

				if ( FrmField::is_option_true( $field, 'slide' ) ) {
                    $input = $collapse_div . $input .'</div>';
                }

                $html = str_replace('[collapse_this]', $input, $html);

            } else {
				self::remove_close_div( $field, $html );

                if ( strpos($html, '[collapse_this]') !== false ) {
                    $html = str_replace('[collapse_this]', $collapse_div, $html);

                    // indicate that a second div is open
                    if ( ! empty($collapse_div) ) {
                        $frm_vars['collapse_div'] = $field['id'];
                    }
                }
            }

			self::maybe_add_collapse_icon( $trigger, $field, $html );

            $html = str_replace('[collapse_class]', $trigger, $html);
		} else if ( $field['type'] == 'html' ) {
			if ( apply_filters( 'frm_use_wpautop', true ) ) {
				$html = wpautop( $html );
			}
            $html = apply_filters('frm_get_default_value', $html, (object) $field, false);
            $html = do_shortcode($html);
		} else if ( FrmField::is_option_true( $field, 'conf_field' ) ) {
			$html .= self::get_confirmation_field_html( $field, $atts );
		}

        if ( strpos($html, '[collapse_this]') ) {
            $html = str_replace('[collapse_this]', '', $html);
        }

        return $html;
	}

	/**
	 * Get the HTML for a confirmation field
	 *
	 * @param array $field
	 * @param array $atts
	 * @return string
	 */
	private static function get_confirmation_field_html( $field, $atts ) {
		$conf_field = self::create_confirmation_field_array( $field, $atts );

		$args = self::generate_repeat_args_for_conf_field( $field, $atts );

		// Replace shortcodes
		$conf_html = FrmFieldsHelper::replace_shortcodes( $field['custom_html'], $conf_field, $atts['errors'], '', $args);

		// Add a couple of classes
		$label_class = 'frm_primary_label';
		if ( strpos( $conf_html, $label_class ) === false ) {
			$label_class = 'frm_pos_';
		}
		$conf_html = str_replace( $label_class, 'frm_conf_label ' . $label_class, $conf_html );

		$container_class = 'frm_form_field';
		if ( strpos( $conf_html, $container_class ) === false ) {
			$container_class = 'form-field';
		}
		$conf_html = str_replace( $container_class, $container_class . ' frm_conf_field', $conf_html );

		// Remove label if stacked. Hide if inline.
		if ( $field['conf_field'] == 'inline' ) {
			$conf_html = str_replace( $container_class, $container_class . ' frm_hidden_container', $conf_html );
		} else {
		   $conf_html = str_replace( $container_class, $container_class . ' frm_none_container', $conf_html );
		}

		return $conf_html;
	}

	/**
	 * Create a confirmation field array to prepare for replace_shortcodes function
	 *
	 * @since 2.0.25
	 * @param array $field
	 * @param array $atts
	 * @return array
	 */
	private static function create_confirmation_field_array( $field, $atts ) {
		$conf_field = $field;

		$conf_field['id'] = 'conf_' . $field['id'];
		$conf_field['name'] = __( 'Confirm', 'formidable' ) . ' ' . $field['name'];
		$conf_field['description'] = $field['conf_desc'];
		$conf_field['field_key'] = 'conf_' . $field['field_key'];

		if ( $conf_field['classes'] ) {
			$conf_field['classes'] = str_replace( array( 'first_', 'frm_first' ), '', $conf_field['classes'] );
		} else if ( $conf_field['conf_field'] == 'inline' ) {
			$conf_field['classes'] = ' frm_half';
		}

		//Prevent loop
		$conf_field['conf_field'] = 'stop';

		// Filter default value/placeholder text
		$field['conf_input'] = apply_filters('frm_get_default_value', $field['conf_input'], (object) $field, false);

		//If clear on focus, set default value. Otherwise, set value.
		if ( $conf_field['clear_on_focus'] == 1 ) {
			$conf_field['default_value'] = $field['conf_input'];
			$conf_field['value'] = '';
		} else {
			$conf_field['value'] = $field['conf_input'];
		}

		//If going back and forth between pages, keep value in confirmation field
		if ( ( ! isset( $conf_field['reset_value'] ) || ! $conf_field['reset_value'] ) && isset( $_POST['item_meta'] ) ) {
			$temp_args = array();
			if ( isset( $atts['section_id'] ) ) {
				$temp_args = array( 'parent_field_id' => $atts['section_id'], 'key_pointer' => str_replace( '-', '', $atts['field_plus_id'] ) );
			}
			FrmEntriesHelper::get_posted_value( $conf_field['id'], $conf_field['value'], $temp_args );
		}

		return $conf_field;
	}

	/**
	 * Generate the repeat args for a confirmation field
	 *
	 * @since 2.0.25
	 * @param array $field
	 * @param array $atts
	 * @return array
	 */
	private static function generate_repeat_args_for_conf_field( $field, $atts ) {
		//If inside of repeating section
		$args = array();
		if ( isset( $atts['section_id'] ) ) {
			$args['field_name'] = preg_replace('/\[' . $field['id'] . '\]$/', '', $atts['field_name']);
			$args['field_name'] = $args['field_name'] . '[conf_' . $field['id'] . ']';
			$args['field_id'] = 'conf_' . $atts['field_id'];
			$args['field_plus_id'] = $atts['field_plus_id'];
			$args['section_id'] = $atts['section_id'];
		}

		return $args;
	}

	/**
	* Remove the close div from HTML (specifically for divider field types)
	*
	* @since 2.0.09
	* @param string $html - pass by reference
	*/
	private static function remove_close_div( $field, &$html ) {
		$end_div = '/\<\/div\>(\s*)?$/';
		if ( preg_match( $end_div, $html ) ) {
			global $frm_vars;
			// indicate that the div is open
			$frm_vars['div'] = $field['id'];

			$html = preg_replace( $end_div, '', $html );
		}
	}

	/**
	* Add the collapse icon next to collapsible section headings
	*
	* @since 2.0.14
	*
	* @param string $trigger
	* @param array $field
	* @param string $html, pass by reference
	*/
	private static function maybe_add_collapse_icon( $trigger, $field, &$html ) {
		if ( ! empty( $trigger ) ) {
			$style = FrmStylesController::get_form_style( $field['form_id'] );

			preg_match_all( "/\<h[2-6]\b(.*?)(?:(\/))?\>(.*?)(?:(\/))?\<\/h[2-6]>/su", $html, $headings, PREG_PATTERN_ORDER);

			if ( isset( $headings[3] ) && ! empty( $headings[3] ) ) {
				$header_text = reset( $headings[3] );
				$search_header_text = '>' . $header_text . '<';
				$old_header_html = reset( $headings[0] );

				if ( 'before' == $style->post_content['collapse_pos'] ) {
					$new_header_html = str_replace( $search_header_text, '><i class="frm_icon_font frm_arrow_icon"></i> ' . $header_text . '<', $old_header_html );
				} else {
					$new_header_html = str_replace( $search_header_text, '>' . $header_text . '<i class="frm_icon_font frm_arrow_icon"></i><', $old_header_html );
				}

				$html = str_replace( $old_header_html, $new_header_html, $html );

			}
		}
	}

	public static function get_export_val( $val, $field, $entry = array() ) {
		if ( $field->type == 'user_id' ) {
            $val = self::get_display_name($val, 'user_login');
		} else if ( $field->type == 'file' ) {
            $val = self::get_file_name($val, false);
		} else if ( $field->type == 'date' ) {
            $wp_date_format = apply_filters('frm_csv_date_format', 'Y-m-d');
            $val = self::get_date($val, $wp_date_format);
		} else if ( $field->type == 'data' ) {
            $new_val = maybe_unserialize($val);

			if ( empty( $new_val ) && ! empty( $entry ) && FrmProField::is_list_field( $field ) ) {
				FrmProEntriesHelper::get_dynamic_list_values( $field, $entry, $new_val );
			}

			if ( is_numeric( $new_val ) ) {
                $val = self::get_data_value($new_val, $field); //replace entry id with specified field
			} else if ( is_array( $new_val ) ) {
                $field_value = array();
				foreach ( $new_val as $v ) {
                    $field_value[] = self::get_data_value($v, $field);
                    unset($v);
                }
                $val = implode(', ', $field_value);
            }
		}

        return $val;
    }

	public static function get_file_icon( $media_id ) {
        if ( ! $media_id || ! is_numeric( $media_id ) ) {
            return;
        }

        $attachment = get_post($media_id);
        if ( ! $attachment ) {
            return;
        }

        $image = $orig_image = wp_get_attachment_image($media_id, 'thumbnail', true);

        //if this is a mime type icon
        if ( $image && ! preg_match("/wp-content\/uploads/", $image) ) {
            $label = basename($attachment->guid);
            $image .= " <span id='frm_media_$media_id' class='frm_upload_label'><a href='". wp_get_attachment_url($media_id) ."'>$label</a></span>";
        } else if ( $image ) {
			$image = '<a href="' . esc_url( wp_get_attachment_url( $media_id ) ) . '" class="frm_file_link">' . $image . '</a>';
        }

        $image = apply_filters('frm_file_icon', $image, array( 'media_id' => $media_id, 'image' => $orig_image));

        return $image;
    }

    /**
	 * Get the file name for the given media IDs
	 *
	 * @param $media_ids
	 * @param bool $short
	 * @param string $sep
	 *
	 * @return string
	 */
	public static function get_file_name( $media_ids, $short = true, $sep = 'default' ) {
		$sep = ( $sep === 'default' ) ? "<br/>\r\n" : $sep;
		$value = '';
		$media_ids = (array) $media_ids;

		foreach ( $media_ids as $media_id ) {
			$value = self::get_file_name_from_array( compact( 'media_id', 'sep', 'short' ), $value );
			unset( $media_id );
		}

		return $value;
	}

	/**
	 * The file id may be an array.
	 * Loop through values in the nested array too.
	 *
	 * @since 2.03.10
	 */
	private static function get_file_name_from_array( $atts, $value ) {
		if ( is_array( $atts['media_id'] ) ) {
			foreach ( $atts['media_id'] as $id ) {
				$atts['media_id'] = $id;
				self::get_file_name_from_id( $atts, $value );
			}
		} else {
			self::get_file_name_from_id( $atts, $value );
		}

		return $value;
	}

	/**
	 * Get the file output values from the media id
	 */
	private static function get_file_name_from_id( $atts, &$value ) {
		if ( ! is_numeric( $atts['media_id'] ) ) {
			return;
		}

		$attachment = get_post( $atts['media_id'] );
		if ( ! $attachment ) {
			return;
		}

		$url = wp_get_attachment_url( $atts['media_id'] );

		$label = $atts['short'] ? basename( $attachment->guid ) : $url;
		$action = FrmAppHelper::simple_get( 'action', 'sanitize_title' );
		$frm_action = FrmAppHelper::simple_get( 'frm_action', 'sanitize_title' );

		if ( $frm_action == 'csv' || $action == 'frm_entries_csv' ) {
			if ( ! empty( $value ) ) {
				$value .= ', ';
			}
		} else if ( FrmAppHelper::is_admin() ) {
			$url = '<a href="' . esc_url( $url ) . '">' . $label . '</a>';
			if ( strpos( FrmAppHelper::simple_get( 'page', 'sanitize_title' ), 'formidable' ) === 0 ) {
				$url .= '<br/><a href="' . esc_url( admin_url( 'media.php?action=edit&attachment_id=' . $atts['media_id'] ) ) . '">' . __( 'Edit Uploaded File', 'formidable' ) . '</a>';
			}
		} else if ( ! empty( $value ) ) {
			$value .= $atts['sep'];
		}

		$value .= $url;
	}

	/**
	* Get the value that will be displayed for a Dynamic Field
	*/
	public static function get_data_value( $value, $field, $atts = array() ) {
		// Make sure incoming data is in the right format
        if ( ! is_object($field) ) {
            $field = FrmField::getOne($field);
        }

		$linked_field_id = self::get_linked_field_id( $atts, $field );

		// If value is an entry ID and the Dynamic field is not mapped to a taxonomy
        if ( ctype_digit( $value ) && ( ! isset( $field->field_options['form_select'] ) || $field->field_options['form_select'] != 'taxonomy' ) && $linked_field_id ) {

			$linked_field = FrmField::getOne( $linked_field_id );

			// Get the value to display
			self::get_linked_field_val( $linked_field, $atts, $value );
        }

		// Implode arrays
		if ( is_array( $value ) ) {
            $value = implode( ( isset( $atts['sep'] ) ? $atts['sep'] : ', ' ), $value );
		}

        return $value;
    }

	/**
	* Get the ID of the linked field to display
	* Called by self::get_data_value
	*
	* @param $atts array
	* @param $field object
	* @return $linked_field_id int or false
	*/
	private static function get_linked_field_id( $atts, $field ) {
		// If show=25 or show="user_email" is set, then get that value
		if ( isset( $atts['show'] ) && $atts['show'] ) {
			$linked_field_id = $atts['show'];

		// If show=25 is NOT set, then just get the ID of the field selected in the Dynamic field's options
		} else if ( isset( $field->field_options['form_select'] ) && is_numeric( $field->field_options['form_select'] ) ) {
		    $linked_field_id = $field->field_options['form_select'];

		// The linked field ID could be false if Dynamic field is mapped to a taxonomy, using really old settings, or if settings were not completed
		} else {
			$linked_field_id = false;
		}
		return $linked_field_id;
	}

	/**
	* Get the value in the linked field
	* Called by self::get_data_value
	*
	* @param $linked_field object or false
	* @param $atts array
	* @param $value int
	*/
	private static function get_linked_field_val( $linked_field, $atts, &$value ) {
		$is_final_val = false;

		// If linked field is a post field
		if ( $linked_field && isset( $linked_field->field_options['post_field'] ) && $linked_field->field_options['post_field']  ) {
			$value = self::get_linked_post_field_val( $value, $atts, $linked_field );

		// If linked field
		} else if ( $linked_field ) {
		    $value = FrmEntryMeta::get_entry_meta_by_field( $value, $linked_field->id );

			if ( $value === null ) {
				return;
			}

		// No linked field (using show=ID, show="first_name", show="user_email", etc.)
		} else {
		    $user_id = FrmDb::get_var( 'frm_items', array( 'id' => $value), 'user_id' );
		    if ( $user_id ) {
				$show = isset( $atts['show'] ) ? $atts['show'] : 'display_name';
				$value = self::get_display_name( $user_id, $show, array( 'blank' => true ) );
		    } else {
		        $value = '';
		    }
			$is_final_val = true;
		}

		if ( ! $is_final_val ) {
			self::get_linked_field_display_val( $linked_field, $atts, $value );
		}
	}

	/**
	* Get the displayed value for Dynamic field that imports data from a post field
	* Called from self::get_linked_field_val
	*/
	private static function get_linked_post_field_val( $value, $atts, $linked_field ) {
		global $wpdb;
		$post_id = FrmDb::get_var($wpdb->prefix .'frm_items', array( 'id' => $value), 'post_id');
		if ( $post_id ) {
		    if ( ! isset($atts['truncate']) ) {
		        $atts['truncate'] = false;
		    }

		    $new_value = FrmProEntryMetaHelper::get_post_value($post_id, $linked_field->field_options['post_field'], $linked_field->field_options['custom_field'], array( 'form_id' => $linked_field->form_id, 'field' => $linked_field, 'type' => $linked_field->type, 'truncate' => $atts['truncate']));
		}else{
		    $new_value = FrmEntryMeta::get_entry_meta_by_field($value, $linked_field->id);
		}
		return $new_value;
	}

	/**
	* Get display value for linked field
	* Called by self::get_linked_field_val
	*/
	private static function get_linked_field_display_val( $linked_field, $atts, &$value ) {
		if ( $linked_field ) {
			if ( isset($atts['show']) && ! is_numeric($atts['show']) ) {
			    $atts['show'] = $linked_field->id;
			} else if ( isset($atts['show']) && ( (int) $atts['show'] == $linked_field->id || $atts['show'] == $linked_field->field_key ) ) {
			    unset($atts['show']);
			}

			// If user ID field, show display name by default
			if ( $linked_field->type == 'user_id' ) {
				unset( $atts['show'] );
			}

			if ( ! isset($atts['show']) && isset($atts['show_info']) ) {
			    $atts['show'] = $atts['show_info'];
				// Prevent infinite recursion
				unset( $atts['show_info'] );
			}

			$value = FrmFieldsHelper::get_display_value( $value, $linked_field, $atts );
		}
	}

	public static function get_date( $date, $date_format = false ) {
		if ( empty( $date ) ) {
			return $date;
		}

		if ( ! $date_format ) {
			$date_format = apply_filters( 'frm_date_format', get_option( 'date_format' ) );
		}

		return self::format_values_in_array( $date, $date_format, array( 'self', 'get_single_date' ) );
	}

    public static function get_single_date($date, $date_format) {
		if ( preg_match( '/^\d{1-2}\/\d{1-2}\/\d{4}$/', $date ) ) {
            $frmpro_settings = new FrmProSettings();
            $date = FrmProAppHelper::convert_date($date, $frmpro_settings->date_format, 'Y-m-d');
        }

        return date_i18n($date_format, strtotime($date));
    }

	public static function format_values_in_array( $value, $format, $callback ) {
		if ( empty( $value ) ) {
			return $value;
		}

		if ( is_array( $value ) ) {
			$formated_values = array();
			foreach ( $value as $v ) {
				$formated_values[] = call_user_func_array( $callback, array( $v, $format ) );
				unset( $v );
			}
			$value = $formated_values;
		} else {
			$value = call_user_func_array( $callback, array( $value, $format ) );
		}

		return $value;
	}

    public static function get_display_name( $user_id, $user_info = 'display_name', $args = array() ) {
        $defaults = array(
            'blank' => false, 'link' => false, 'size' => 96
        );

        $args = wp_parse_args($args, $defaults);

        $user = get_userdata($user_id);
        $info = '';

        if ( $user ) {
            if ( $user_info == 'avatar' ) {
                $info = get_avatar( $user_id, $args['size'] );
			} elseif ( $user_info == 'author_link' ) {
				$info = get_author_posts_url( $user_id );
            } else {
                $info = isset($user->$user_info) ? $user->$user_info : '';
            }

            if ( empty($info) && ! $args['blank'] ) {
                $info = $user->user_login;
            }
        }

        if ( $args['link'] ) {
			$info = '<a href="' .  esc_url( admin_url('user-edit.php?user_id=' . $user_id ) ) . '">' . $info . '</a>';
        }

        return $info;
    }

    public static function get_subform_ids(&$subforms, $field) {
        if ( isset($field->field_options['form_select']) && is_numeric($field->field_options['form_select']) ) {
            $subforms[] = $field->field_options['form_select'];
        }
    }

	public static function get_field_options( $form_id, $value = '', $include = 'not', $types = array(), $args = array() ) {
		$inc_embed = $inc_repeat = isset( $args['inc_sub'] ) ? $args['inc_sub'] : 'exclude';
		$fields = FrmField::get_all_for_form( (int) $form_id, '', $inc_embed, $inc_repeat );

		if ( empty( $fields ) ) {
			return;
		}

		if ( empty( $types) ) {
			$types = array( 'break', 'divider', 'end_divider', 'data', 'file', 'captcha', 'form' );
		} else if ( ! is_array( $types ) ) {
			$types = explode( ',', $types );
			$temp_types = $types;
			foreach ( $temp_types as $k => $t ) {
				$types[ $k ] = trim( $types[ $k ], "'" );
				unset( $k, $t );
			}
			unset( $temp_types );
		}

		foreach ( $fields as $field ) {
			$stop = ( $include != 'not' && ! in_array( $field->type, $types ) ) || ( $include == 'not' && in_array( $field->type, $types ) );
			if ( $stop || FrmProField::is_list_field( $field ) ) {
				continue;
			}
			unset( $stop );

            ?>
            <option value="<?php echo (int) $field->id ?>" <?php selected($value, $field->id) ?>><?php echo esc_html( FrmAppHelper::truncate($field->name, 50) ) ?></option>
        <?php
        }
    }

	public static function get_shortcode_select( $form_id, $target_id = 'content', $type = 'all' ) {
        $field_list = array();
		$exclude = FrmField::no_save_fields();

        if ( is_numeric($form_id) ) {
            if ( $type == 'field_opt' ) {
                $exclude[] = 'data';
                $exclude[] = 'checkbox';
            }

            $field_list = FrmField::get_all_for_form($form_id, '', 'include');
        }

        $linked_forms = array();
        ?>
        <select class="frm_shortcode_select frm_insert_val" data-target="<?php echo esc_attr( $target_id ) ?>">
            <option value="">&mdash; <?php _e( 'Select a value to insert into the box below', 'formidable' ) ?> &mdash;</option>
            <?php if ( $type != 'field_opt' && $type != 'calc' ) { ?>
            <option value="id"><?php _e( 'Entry ID', 'formidable' ) ?></option>
            <option value="key"><?php _e( 'Entry Key', 'formidable' ) ?></option>
            <option value="post_id"><?php _e( 'Post ID', 'formidable' ) ?></option>
            <option value="ip"><?php _e( 'User IP', 'formidable' ) ?></option>
            <option value="created-at"><?php _e( 'Entry creation date', 'formidable' ) ?></option>
            <option value="updated-at"><?php _e( 'Entry update date', 'formidable' ) ?></option>

			<optgroup label="<?php esc_attr_e( 'Form Fields', 'formidable' ) ?>">
            <?php }

            if ( ! empty($field_list) ) {
            foreach ( $field_list as $field ) {
                if ( in_array($field->type, $exclude) ) {
                    continue;
                }

				if ( $type != 'calc' && FrmProField::is_list_field( $field ) ) {
                    continue;
                }

            ?>
                <option value="<?php echo esc_attr( $field->id ) ?>"><?php echo esc_html( $field_name =  FrmAppHelper::truncate( $field->name, 60 ) ) ?> (<?php _e( 'ID', 'formidable' ) ?>)</option>
                <option value="<?php echo esc_attr( $field->field_key ) ?>"><?php echo esc_html( $field_name ) ?> (<?php _e( 'Key', 'formidable' ) ?>)</option>
                <?php if ( $field->type == 'file' && $type != 'field_opt' && $type != 'calc' ) { ?>
                    <option class="frm_subopt" value="<?php echo esc_attr( $field->field_key ) ?> size=thumbnail"><?php _e( 'Thumbnail', 'formidable' ) ?></option>
                    <option class="frm_subopt" value="<?php echo esc_attr( $field->field_key ) ?> size=medium"><?php _e( 'Medium', 'formidable' ) ?></option>
                    <option class="frm_subopt" value="<?php echo esc_attr( $field->field_key ) ?> size=large"><?php _e( 'Large', 'formidable' ) ?></option>
                    <option class="frm_subopt" value="<?php echo esc_attr( $field->field_key ) ?> size=full"><?php _e( 'Full Size', 'formidable' ) ?></option>
                <?php } else if ( $field->type == 'data' && $type != 'calc' ) {
					//get all fields from linked form
                    if ( isset($field->field_options['form_select']) && is_numeric($field->field_options['form_select']) ) {

                        $linked_form = FrmDb::get_var( 'frm_fields', array( 'id' => $field->field_options['form_select']), 'form_id' );
                        if ( ! in_array($linked_form, $linked_forms) ) {
                            $linked_forms[] = $linked_form;
							$linked_fields = FrmField::getAll( array( 'fi.type not' => FrmField::no_save_fields(), 'fi.form_id' => (int) $linked_form ) );
                            foreach ( $linked_fields as $linked_field ) { ?>
                    <option class="frm_subopt" value="<?php echo esc_attr( $field->id .' show='. $linked_field->id ) ?>"><?php echo esc_html( FrmAppHelper::truncate($linked_field->name, 60) ) ?> (<?php _e( 'ID', 'formidable' ) ?>)</option>
                    <option class="frm_subopt" value="<?php echo esc_attr( $field->field_key .' show='. $linked_field->field_key ) ?>"><?php echo esc_html( FrmAppHelper::truncate($linked_field->name, 60) ) ?> (<?php _e( 'Key', 'formidable' ) ?>)</option>
                    <?php
                            }
                        }
                    }
                }
            }
            }

            if ( $type != 'field_opt' && $type != 'calc' ) { ?>
            </optgroup>
			<optgroup label="<?php esc_attr_e( 'Helpers', 'formidable' ) ?>">
                <option value="editlink"><?php _e( 'Admin link to edit the entry', 'formidable' ) ?></option>
                <?php if ( $target_id == 'content' ) { ?>
                <option value="detaillink"><?php _e( 'Link to view single page if showing dynamic entries', 'formidable' ) ?></option>
                <?php }

                if ( $type != 'email' ) { ?>
                <option value="evenodd"><?php _e( 'Add a rotating \'even\' or \'odd\' class', 'formidable' ) ?></option>
                <?php } else if ( $target_id == 'email_message' ) { ?>
                <option value="default-message"><?php _e( 'Default Email Message', 'formidable' ) ?></option>
                <?php } ?>
                <option value="siteurl"><?php _e( 'Site URL', 'formidable' ) ?></option>
                <option value="sitename"><?php _e( 'Site Name', 'formidable' ) ?></option>
            </optgroup>
            <?php } ?>
        </select>
    <?php
    }

	/**
	* Get the HTML for a file upload field depending on the $atts
	*
	* @since 2.0.19
	*
	* @param array $atts
	* @param string|array|int $replace_with
	*/
	private static function get_file_html_from_atts( $atts, &$replace_with ) {
		$show_id = isset( $atts['show'] ) && $atts['show'] == 'id';
		if ( ! $show_id && ! empty( $replace_with ) ) {
			//size options are thumbnail, medium, large, or full
			$size = isset($atts['size']) ? $atts['size'] : (isset($atts['show']) ? $atts['show'] : 'thumbnail');

			$new_atts = array(
				'show_filename' => ( isset($atts['show_filename']) && $atts['show_filename'] ) ? true : false,
				'show_image' => ( isset( $atts['show_image'] ) && $atts['show_image'] ) ? true : false,
				'add_link' => ( isset( $atts['add_link'] ) && $atts['add_link'] ) ? true : false
			);

			self::modify_atts_for_reverse_compatibility( $atts, $new_atts );

			$ids = (array) $replace_with;
			$replace_with = self::get_displayed_file_html( $ids, $size, $new_atts );
		}

		if ( is_array( $replace_with ) ) {
			$replace_with = array_filter( $replace_with );
		}
	}

	/**
	* Maintain reverse compatibility for html=1, links=1, and show=label
	*
	* @since 2.0.19
	*
	* @param array $atts
	* @param array $new_atts
	*/
	private static function modify_atts_for_reverse_compatibility( $atts, &$new_atts ) {
		// For show=label
		if ( ! $new_atts['show_filename'] && isset( $atts['show'] ) && $atts['show'] == 'label' ) {
			$new_atts['show_filename'] = true;
		}

		// For html=1
		$inc_html = ( isset( $atts['html'] ) && $atts['html'] );
		if ( $inc_html && ! $new_atts['show_image'] ) {

			if ( $new_atts['show_filename'] ) {
				// For show_filename with html=1
				$new_atts['show_image'] = false;
				$new_atts['add_link'] = true;
			} else {
				// html=1 without show_filename=1
				$new_atts['show_image'] = true;
				$new_atts['add_link_for_non_image'] = true;
			}
		}

		// For links=1
		$show_links = ( isset( $atts['links'] ) && $atts['links'] );
		if ( $show_links && ! $new_atts['add_link'] ) {
			$new_atts['add_link'] = true;
		}
	}

	/**
	* Get HTML for a file upload field depending on atts and file type
	*
	* @since 2.0.19
	*
	* @param array $ids
	* @param string $size
	* @param array $atts
	* @return array|string
	*/
	public static function get_displayed_file_html( $ids, $size = 'thumbnail', $atts = array() ) {
		$defaults = array(
			'show_filename' => false,
			'show_image' => false,
			'add_link' => false,
			'add_link_for_non_image' => false,
		);
		$atts = wp_parse_args( $atts, $defaults );
		$atts['size'] = $size;

		$img_html = array();
		foreach ( (array) $ids as $id ) {
			if ( ! is_numeric( $id ) ) {
				if ( ! empty( $id ) ) {
					// If a custom value was set with a hook, don't remove it
					$img_html[] = $id;
				}
				continue;
			}

			$img = self::get_file_display( $id, $atts );

			if ( isset( $img ) ) {
				$img_html[] = $img;
			}
		}
		unset( $img, $id );

		if ( count( $img_html ) == 1 ) {
			$img_html = reset( $img_html );
		}

		return $img_html;
	}

	/**
	* Get the HTML to display an uploaded in a File Upload field
	*
	* @since 2.02
	*
	* @param int $id
	* @param array $atts
	* @return string $img_html
	*/
	private static function get_file_display( $id, $atts ) {
		if ( empty( $id ) || ! self::file_exists_by_id( $id ) ) {
			return '';
		}

		$img_html = $image_url = '';
		$image = wp_get_attachment_image_src( $id, $atts['size'], false );
		$is_non_image = ! wp_attachment_is_image( $id );

		if ( $atts['show_image'] ) {
			$img_html = wp_get_attachment_image( $id, $atts['size'], $is_non_image );
		}

		// If show_filename=1 is included
		if ( $atts['show_filename'] ) {
			$label = self::get_single_file_name( $id );
			if ( $atts['show_image'] ) {
				$img_html .= ' <span id="frm_media_' . absint( $id ) . '" class="frm_upload_label">' . $label . '</span>';
			} else {
				$img_html .= $label;
			}
		}

		// If neither show_image or show_filename are included, get file URL
		if ( empty( $img_html ) ) {
			if ( $is_non_image ) {
				$img_html = $image_url = wp_get_attachment_url( $id );
			} else {
				$img_html = $image['0'];
			}
		}

		// If add_link=1 is included
		if ( $atts['add_link'] || ( $is_non_image && $atts['add_link_for_non_image'] ) ) {
			if ( empty( $image_url ) ) {
				$image_url = wp_get_attachment_url( $id );
			}
			$img_html = '<a href="' . esc_url( $image_url ) . '" class="frm_file_link">' . $img_html . '</a>';
		}

		$atts['media_id'] = $id;
		$img_html = apply_filters( 'frm_image_html_array', $img_html, $atts );

		return $img_html;
	}

	/**
	 * Check if a file exists on the site
	 *
	 * @since 2.02.11
	 * @param $id
	 *
	 * @return bool
	 */
	private static function file_exists_by_id( $id ) {
		global $wpdb;

		$query = $wpdb->prepare( 'SELECT post_type FROM ' . $wpdb->posts . ' WHERE ID=%d', $id );
		$type = $wpdb->get_var( $query );

		return ( $type === 'attachment' );
	}

	/**
	* Get the file name for a single media ID
	*
	* @since 2.0.19
	*
	* @param int $id
	* @return boolean|string $filename
	*/
	private static function get_single_file_name( $id ) {
		$attachment = get_post( $id );
		if ( ! $attachment ) {
			$filename = false;
		} else {
			$filename = basename( $attachment->guid );
		}
		return $filename;
	}

    public static function get_display_value( $replace_with, $field, $atts = array() ) {
		$field_type = is_array( $field ) ? $field['type'] : $field->type;
        $function_name = 'get_'. $field_type .'_display_value';
        if ( method_exists(__CLASS__, $function_name) ) {
			$replace_with = self::$function_name( $replace_with, $atts, $field );
        }

        return $replace_with;
    }

	public static function get_user_id_display_value($replace_with, $atts) {
		$user_info = self::prepare_user_info_attribute( $atts );
		$replace_with = self::get_display_name($replace_with, $user_info, $atts);

		if ( is_array( $replace_with ) ) {
			$sep = isset( $atts['sep'] ) ? $atts['sep'] : ', ';
			$replace_with = implode( $sep, $replace_with );
		}

		return $replace_with;
	}

	/**
	 * Get a JSON array of values from Repeating Section
	 *
	 * @since 2.03.08
	 *
	 * @param $value
	 * @param $atts
	 * @param $field
	 *
	 * @return mixed
	 */
	public static function get_divider_display_value( $value, $atts, $field ) {
		if ( ! FrmField::is_repeating_field( $field ) ) {
			return $value;
		}

		if ( ! is_array( $value ) && ! empty( $value ) && $atts['format'] === 'json' ) {
			$child_entries = explode( ',', $value );
			$value = array();

			foreach ( $child_entries as $child_id ) {

				$pass_args = array(
	                'format' => 'array',
	                'include_blank' => true,
	                'id' => $child_id,
	                'user_info' => false,
	            );

				$child_entry = FrmEntriesController::show_entry_shortcode( $pass_args );
				$value[] = $child_entry;
			}

			$value = json_encode( $value );
		}

		return $value;
	}

	/**
	 * Generate the user info attribute for the get_display_name() function
	 *
	 * @since 2.03.07
	 * @param $atts
	 *
	 * @return string
	 */
	private static function prepare_user_info_attribute( $atts ) {
		if ( isset( $atts['show'] ) ) {
			if ( $atts['show'] === 'id' ) {
				$user_info = 'ID';
			} else {
				$user_info = $atts['show'];
			}
		} else {
			$user_info = 'display_name';
		}

		return $user_info;
	}

	public static function get_time_options( $values ) {
		_deprecated_function( __FUNCTION__, '2.03.01', 'FrmProTimeField::get_time_options' );
		return FrmProTimeField::get_time_options( $values );
	}

	public static function show_time_field( $field, $values ) {
		_deprecated_function( __FUNCTION__, '2.03.01', 'FrmProTimeField::show_time_field' );
		FrmProTimeField::show_time_field( $field, $values );
	}

	public static function time_array_to_string( &$value ) {
		_deprecated_function( __FUNCTION__, '2.03.01', 'FrmProTimeField::time_array_to_string' );
		FrmProTimeField::time_array_to_string( $value );
	}

	public static function is_time_empty( $value ) {
		_deprecated_function( __FUNCTION__, '2.03.01', 'FrmProTimeField::is_time_empty' );
		return FrmProTimeField::is_time_empty( $value );
	}

	public static function get_time_display_value( $replace_with, $atts, $field ) {
		if ( empty( $replace_with ) ) {
			return $replace_with;
		}

		$defaults = array(
			'format' => FrmProAppHelper::get_time_format_for_field( $field ),
		);

		$atts = wp_parse_args( $atts, $defaults );
		if ( is_array( $replace_with ) && isset( $replace_with['H'] ) ) {
			FrmProTimeField::time_array_to_string( $replace_with );
		} elseif ( ! is_array( $replace_with ) && strpos( $replace_with, ',' ) ) {
			$replace_with = explode( ',', $replace_with );
		}

		return self::format_values_in_array( $replace_with, $atts['format'], array( 'FrmProAppHelper', 'format_time' ) );
	}

    public static function get_date_display_value($replace_with, $atts) {
		if ( $replace_with === false ) {
			return $replace_with;
		}

        $defaults = array(
            'format'    => false,
        );
        $atts = wp_parse_args($atts, $defaults);

        if ( ! isset($atts['time_ago']) ) {
			if ( ! is_array( $replace_with ) && strpos( $replace_with, ',' ) ) {
				$replace_with = explode( ',', $replace_with );
			}

			$replace_with = self::format_values_in_array( $replace_with, $atts['format'], array( 'self', 'get_date' ) );
		} else {
			$replace_with = self::get_date( $replace_with, 'Y-m-d H:i:s' );
			$replace_with = FrmAppHelper::human_time_diff( strtotime( $replace_with ), strtotime( date_i18n( 'Y-m-d' ) ), absint( $atts['time_ago'] ) );
		}

        return $replace_with;
    }

    public static function get_file_display_value($replace_with, $atts) {
        if ( ! is_numeric($replace_with) && ! is_array($replace_with) ) {
            return $replace_with;
        }

		$showing_image = ( isset( $atts['html'] ) && $atts['html'] ) || ( isset( $atts['show_image'] ) && $atts['show_image'] );
		$default_sep = $showing_image ? ' ' : ', ';
		$atts['sep'] = isset( $atts['sep'] ) ? $atts['sep'] : $default_sep;

		self::get_file_html_from_atts( $atts, $replace_with );

        if ( is_array($replace_with) ) {
            $replace_with = implode($atts['sep'], $replace_with);

			if ( $showing_image ) {
				$replace_with = '<div class="frm_file_container">' . $replace_with . '</div>';
			}
        }

        return $replace_with;
    }

	public static function get_image_display_value( $replace_with, $atts ) {
		$defaults = array(
			'html'  => false,
		);
		$atts = wp_parse_args( $atts, $defaults );

		if ( $atts['html'] ) {
			$images = '';
			foreach ( (array) $replace_with as $url ) {
				$images .= '<img src="' . esc_attr( $url ) . '" class="frm_image_from_url" alt="" /> ';
			}
			$replace_with = $images;
		}

		return $replace_with;
	}

    public static function get_number_display_value($replace_with, $atts) {
        $defaults = array(
            'dec_point' => '.', 'thousands_sep' => '',
            'sep'       => ', ',
        );
        $atts = wp_parse_args($atts, $defaults);

        $new_val = array();
        $replace_with = array_filter( (array) $replace_with, 'strlen' );

        foreach ( $replace_with as $v ) {
            if ( strpos($v, $atts['sep']) ) {
                $v = explode($atts['sep'], $v);
            }

            foreach ( (array) $v as $n ) {
                if ( ! isset($atts['decimal']) ) {
                    $num = explode('.', $n);
                    $atts['decimal'] = isset($num[1]) ? strlen($num[1]) : 0;
                }

				if ( is_numeric( $n ) ) {
					$n = number_format($n, $atts['decimal'], $atts['dec_point'], $atts['thousands_sep']);
				}

                $new_val[] = $n;
            }

            unset($v);
        }
        $new_val = array_filter( (array) $new_val, 'strlen' );

        return implode($atts['sep'], $new_val);
    }

    public static function get_data_display_value($replace_with, $atts, $field) {
        //if ( is_numeric($replace_with) || is_array($replace_with) )

        if ( ! isset($field->field_options['form_select']) || $field->field_options['form_select'] == 'taxonomy' ) {
            return $replace_with;
        }

        $sep = isset($atts['sep']) ? $atts['sep'] : ', ';
        $atts['show'] = isset($atts['show']) ? $atts['show'] : false;

        if ( ! empty($replace_with) && ! is_array($replace_with) ) {
            $replace_with = explode($sep, $replace_with);
        }

        $linked_ids = (array) $replace_with;
        $replace_with = array();

        if ( $atts['show'] == 'id' ) {
            // keep the values the same since we already have the ids
            $replace_with = $linked_ids;
        } else if ( in_array($atts['show'], array( 'key', 'created-at', 'created_at', 'updated-at', 'updated_at, updated-by, updated_by', 'post_id')) ) {

            $nice_show = str_replace('-', '_', $atts['show']);

            foreach ( $linked_ids as $linked_id ) {
                $linked_entry = FrmEntry::getOne($linked_id);

                if ( isset($linked_entry->{$atts['show']}) ) {
                    $replace_with[] = $linked_entry->{$atts['show']};
                } else if ( isset($linked_entry->{$nice_show}) ) {
                    $replace_with[] = $linked_entry->{$nice_show};
                } else {
                    $replace_with[] = $linked_entry->item_key;
                }
            }
        } else {
            foreach ( $linked_ids as $linked_id ) {
                $new_val = self::get_data_value($linked_id, $field, $atts);

                if ( $linked_id == $new_val ) {
                    continue;
                }
				if ( is_array( $new_val ) ) {
                    $new_val = implode($sep, $new_val);
                }

                $replace_with[] = $new_val;

                unset($new_val);
            }
        }

        return implode($sep, $replace_with);
    }

	/**
	* Check if a field is hidden through the frm_is_field_hidden hook
	*
	* @since 2.0.13
	* @param boolean $hidden
	* @param object $field
	* @param array $values
	* @return boolean $hidden
	*/
	public static function route_to_is_field_hidden( $hidden, $field, $values ) {
		$hidden = self::is_field_hidden( $field, $values );
		return $hidden;
	}

	/**
	 * Check if a field is conditionally hidden
	 *
	 * @param object $field
	 * @param array $values
	 * @return bool
	 */
	public static function is_field_hidden( $field, $values ) {
		return ! self::is_field_conditionally_shown( $field, $values );
	}

	/**
	 * Check if a field is conditionally shown
	 *
	 * @since 2.02.03
	 * @param object $field
	 * @param array $values
	 * @return bool
	 */
	private static function is_field_conditionally_shown( $field, $values ) {
		if ( ! self::field_needs_conditional_logic_checking( $field ) ) {
			return true;
		}

		self::prepare_conditional_logic( $field );

		$logic_outcomes = self::get_conditional_logic_outcomes( $field, $values );

		$visible = self::is_field_visible_from_logic_outcomes( $field, $logic_outcomes );

		if ( $visible && ! self::dynamic_field_has_options( $field, $values ) ) {
			$visible = false;
		}

		return $visible;
	}

	/**
	 * Check if a field needs to have the conditional logic checked
	 *
	 * @since 2.02.03
	 * @param object $field
	 * @return bool
	 */
	private static function field_needs_conditional_logic_checking( $field ) {
		$needs_check = true;

		if ( $field->type == 'user_id' || $field->type == 'hidden' || ! isset( $field->field_options['hide_field'] ) || empty( $field->field_options['hide_field'] ) ) {
			$needs_check = false;
		}

		return $needs_check;
	}

	/**
	 * Prepare conditional logic settings
	 *
	 * @since 2.02.03
	 * @param object $field
	 */
	private static function prepare_conditional_logic( &$field ) {
		$field->field_options['hide_field'] = (array) $field->field_options['hide_field'];

		if ( isset( $field->field_options['hide_field_cond'] ) ) {
			$field->field_options['hide_field_cond'] = (array) $field->field_options['hide_field_cond'];
		} else {
			$field->field_options['hide_field_cond'] = array( '==');
		}

		$field->field_options['hide_opt'] = (array) $field->field_options['hide_opt'];
	}

	/**
	 * Get the conditional logic outcomes for a field
	 *
	 * @since 2.02.03
	 * @param object $field
	 * @param array $values
	 * @return array
	 */
	private static function get_conditional_logic_outcomes( $field, $values ) {
		$logic_outcomes = array();
		foreach ( $field->field_options['hide_field'] as $logic_key => $logic_field ) {

			$observed_value = self::get_observed_logic_value( $field, $values, $logic_field );
			$logic_value = self::get_conditional_logic_value( $field, $logic_key, $observed_value );
			$operator = $field->field_options['hide_field_cond'][ $logic_key ];

			$logic_outcomes[] = FrmFieldsHelper::value_meets_condition( $observed_value, $operator, $logic_value );
		}

		return $logic_outcomes;
	}

	/**
	 * Check if a field is conditionally shown based on the conditional logic outcomes
	 *
	 * @since 2.02.03
	 * @param object $field
	 * @param array $logic_outcomes
	 * @return bool
	 */
	private static function is_field_visible_from_logic_outcomes( $field, $logic_outcomes ) {
		$action = isset( $field->field_options['show_hide'] ) ? $field->field_options['show_hide'] : 'show';
		$any_all = isset( $field->field_options['any_all'] ) ? $field->field_options['any_all'] : 'any';
		$visible = ( 'show' == $action ) ? true : false;

		self::check_logic_outcomes( $any_all, $logic_outcomes, $visible );

		return $visible;
	}

	/**
	 * Check if a Dynamic field has options at validation
	 *
	 * @since 2.02.03
	 * @param object $field
	 * @param array $values
	 * @return bool
	 */
	private static function dynamic_field_has_options( $field, $values ) {
		$has_options = true;

		if ( $field->type != 'data' || $field->field_options['data_type'] == 'data' ) {
			return $has_options;
		}

		foreach ( $field->field_options['hide_field'] as $logic_field_id ) {
			if ( ! self::is_dynamic_field( $logic_field_id ) ) {
				continue;
			}

			if ( ! self::logic_field_retrieves_options( $field, $values, $logic_field_id ) ) {
				$has_options = false;
				break;
			}
		}

		$args = array( 'field' => $field, 'values' => $values );
		$has_options = apply_filters( 'frm_dynamic_field_has_options', $has_options, $args );

		return $has_options;
	}

	/**
	 * Get the value for a single row of conditional logic
	 *
	 * @since 2.02.03
	 * @param object $field
	 * @param int $key
	 * @param string|array $observed_value
	 * @return string|array
	 */
	private static function get_conditional_logic_value( $field, $key, $observed_value ) {
		$logic_value = $field->field_options['hide_opt'][ $key ];
		self::get_logic_value_for_dynamic_field( $field, $key, $observed_value, $logic_value );

		return $logic_value;
	}

	/**
	 * Get the observed value from a logic field
	 *
	 * @since 2.02.03
	 * @param object $field
	 * @param array $values
	 * @param int $logic_field_id
	 * @return bool
	 */
	private static function get_observed_logic_value( $field, $values, $logic_field_id ) {
		$observed_value = '';
		if ( isset( $values['item_meta'][ $logic_field_id ] ) ) {
			// logic field is not repeating/embedded
			$observed_value = $values['item_meta'][ $logic_field_id ];
		} else if ( isset( $field->temp_id ) && $field->id != $field->temp_id ) {
			// logic field is repeating/embedded
			$id_parts = explode( '-', $field->temp_id );
			if ( isset( $_POST['item_meta'][ $id_parts[1] ][ $id_parts[2] ] ) && isset( $_POST['item_meta'][ $id_parts[1] ][ $id_parts[2] ][ $logic_field_id ] ) ) {
				$observed_value = stripslashes_deep( $_POST['item_meta'][ $id_parts[1] ][ $id_parts[2] ][ $logic_field_id ] );
			}
		}

		return $observed_value;
	}

	/**
	 * Get the value for a single row of conditional logic when field and parent is Dynamic
	 *
	 * @since 2.02.03
	 * @param object $field
	 * @param int $key
	 * @param mixed $observed_value
	 * @param string $logic_value
	 */
	private static function get_logic_value_for_dynamic_field( $field, $key, $observed_value, &$logic_value ) {
		if ( $field->type != 'data' || $field->field_options['data_type'] == 'data' ) {
			return;
		}

		if ( ! self::is_dynamic_field( $field->field_options['hide_field'][ $key ] ) ) {
			return;
		}

		// If logic is "Dynamic field is equal to anything"
		if ( empty( $field->field_options['hide_opt'][ $key ] ) ) {
			$logic_value = $observed_value;

			// If no value is set in parent field, make sure logic doesn't return true
			if ( empty( $observed_value ) && $field->field_options['hide_field_cond'][$key] == '==' ) {
				$logic_value = 'anything';
			}
		}

		$hide_field = FrmField::getOne( $field->field_options['hide_field'][ $key ] );
		$logic_value = apply_filters( 'frm_is_dynamic_field_empty', $logic_value, compact( 'field', 'key', 'hide_field', 'observed_value' )  );
		if ( has_filter( 'frm_is_dynamic_field_empty' ) ) {
			_deprecated_function( 'The frm_is_dynamic_field_empty filter', '2.02.03', 'the frm_dynamic_field_has_options filter' );
		}
	}

	/**
	 * Check whether a field is visible or not from conditional logic outcomes
	 *
	 * @since 2.02.03
	 * @param string $any_all
	 * @param array $logic_outcomes
	 * @param bool $visible
	 */
	private static function check_logic_outcomes( $any_all, $logic_outcomes, &$visible ) {
		if ( 'any' == $any_all ) {
			if ( ! in_array( true, $logic_outcomes ) ) {
				$visible = ! $visible;
			}
		} else {
			if ( in_array( false, $logic_outcomes ) ) {
				$visible = ! $visible;
			}
		}
	}

	/**
	 * Check if a field is Dynamic
	 *
	 * @since 2.02.03
	 * @param int $field_id
	 * @return bool
	 */
	private static function is_dynamic_field( $field_id ) {
		$field_type = FrmField::get_type( $field_id );
		return ( $field_type && $field_type == 'data' );
	}

	/**
	 * Check if a Dynamic logic field retrieves options for the child
	 *
	 * @since 2.02.03
	 * @param object $field
	 * @param array $values
	 * @param int $logic_field_id
	 * @return bool
	 */
	private static function logic_field_retrieves_options( $field, $values, $logic_field_id ) {
		$observed_value = self::get_observed_logic_value( $field, $values, $logic_field_id );

		if ( empty( $observed_value ) ) {
			return false;
		}

		if ( ! is_array( $observed_value ) ) {
			$observed_value = explode( ',', $observed_value );
		}

		$linked_field_id = isset( $field->field_options['form_select'] ) ? $field->field_options['form_select'] : '';

		if ( $linked_field_id == 'taxonomy' ) {
			// Category fields
			$has_options = self::does_parent_taxonomy_have_children( $field->field_options['taxonomy'], $observed_value );
		} else {
			// Standard dynamic fields
			$linked_field = FrmField::getOne( $linked_field_id );
			$field_options = array();
			FrmProEntryMetaHelper::meta_through_join( $logic_field_id, $linked_field, $observed_value, $field, $field_options );
			$has_options = ! empty( $field_options );
		}

		return $has_options;
	}

	/**
	 * Checks if child categories exist for a given taxonomy and parent taxonomy IDs
	 *
	 * @since 2.02.03
	 *
	 * @param string $taxonomy
	 * @param array $parent_taxonomy_ids
	 * @return array
	 */
	private static function does_parent_taxonomy_have_children( $taxonomy, $parent_taxonomy_ids ) {
		$has_children = false;

		if ( empty( $parent_taxonomy_ids ) ) {
			return $has_children;
		}

		$child_categories = array();
		foreach ( $parent_taxonomy_ids as $parent_id ) {
			$args = array(
				'parent' => (int) $parent_id,
				'taxonomy' => $taxonomy,
				'hide_empty' => 0,
			);
			$new_cats = get_categories( $args );
			$child_categories = array_merge( $new_cats, $child_categories );

			// Stop as soon as there are options
			if ( ! empty( $child_categories ) ) {
				$has_children = true;
				break;
			}
		}

		return $has_children;
	}

    public static function &is_field_visible_to_user($field) {
        $visible = true;

		if ( FrmField::is_option_empty( $field, 'admin_only' ) ) {
            return $visible;
        }

        if ( $field->field_options['admin_only'] == 1 ) {
            $field->field_options['admin_only'] = 'administrator';
        }

        if ( ( $field->field_options['admin_only'] == 'loggedout' && is_user_logged_in() ) ||
            ( $field->field_options['admin_only'] == 'loggedin' && ! is_user_logged_in() ) ||
            ( ! in_array($field->field_options['admin_only'], array( 'loggedout', 'loggedin', '') ) &&
            ! FrmAppHelper::user_has_permission( $field->field_options['admin_only'] ) ) ) {
                $visible = false;
        }

        return $visible;
    }

    /**
     * Loop through value in hidden field and display arrays in separate fields
     * @since 2.0
     */
	public static function insert_hidden_fields( $field, $field_name, $checked, $opt_key = false ) {
		if ( FrmProNestedFormsController::is_hidden_nested_form_field( $field ) ) {
			FrmProNestedFormsController::insert_hidden_nested_form( $field, $field_name, $checked );
			return;
		}

		if ( is_array( $checked ) ) {
			foreach ( $checked as $k => $checked2 ) {
                $checked2 = apply_filters('frm_hidden_value', $checked2, $field);
                self::insert_hidden_fields($field, $field_name .'['. $k .']', $checked2, $k);
                unset($k, $checked2);
            }

        } else {
        	$html_id = $field['html_id'];
			self::hidden_html_id( $field, $opt_key, $html_id );
?>
<input type="hidden" name="<?php echo esc_attr( $field_name ) ?>" id="<?php echo esc_attr( $html_id ) ?>" value="<?php echo esc_attr( $checked ) ?>" <?php do_action( 'frm_field_input_html', $field )?> />
<?php
			self::insert_extra_hidden_fields( $field, $opt_key );
        }
    }

	/**
	 * The html id needs to be the same as when the fields are displayed normally
	 * so the calculations will work correctly
	 *
	 * @since 2.0.5
	 *
	 * @param array $field
	 * @param string|boolean $opt_key
	 * @param string $html_id
	 */
	private static function hidden_html_id( $field, $opt_key, &$html_id ) {
		$html_id_end = $opt_key;
		if ( $opt_key === false && isset( $field['original_type'] ) && in_array( $field['original_type'], array( 'radio', 'checkbox', 'scale' ) ) ) {
			$html_id_end = 0;
		}

		if ( $html_id_end !== false ) {
			$html_id .= '-' . $html_id_end;
		}
	}

	/**
	* Add confirmation and "other" hidden fields to help carry all data throughout the form
	* Note: This doesn't control the HTML for fields in repeating sections
	*
	* @since 2.0
	*
	* @param array $field
	* @param string|boolean $opt_key
	*/
	public static function insert_extra_hidden_fields( $field, $opt_key = false ) {
		// If we're dealing with a repeating section, hidden fields are already taken care of
		if ( isset( $field['original_type'] ) && $field['original_type'] == 'divider' ) {
			return;
		}

		//If confirmation field on previous page, store value in hidden field
		if ( FrmField::is_option_true( $field, 'conf_field' ) && isset( $_POST['item_meta']['conf_' . $field['id']] ) ) {
		    self::insert_hidden_confirmation_fields( $field );

		//If Other field on previous page, store value in hidden field
		} else if ( FrmField::is_option_true( $field, 'other' ) && isset( $_POST['item_meta']['other'][ $field['id'] ] ) ) {
			self::insert_hidden_other_fields( $field, $opt_key );
		}
    }

	/**
	* Insert hidden confirmation fields
	*
	* @since 2.0.8
	* @param array $field
	*/
	private static function insert_hidden_confirmation_fields( $field ){
		if ( isset( $field['reset_value'] ) && $field['reset_value'] ) {
			$value = '';
		} else {
			$value = $_POST['item_meta'][ 'conf_' . $field['id'] ];
		}

		include( FrmAppHelper::plugin_path() .'/pro/classes/views/frmpro-fields/front-end/hidden-conf-field.php' );
	}

	/**
	* Insert hidden Other fields
	*
	* @since 2.0.8
	* @param array $field
	* @param string|int|boolean $opt_key
	* @param string $html_id
	*/
	private static function insert_hidden_other_fields( $field, $opt_key ){
		$other_id = FrmFieldsHelper::get_other_field_html_id( $field['original_type'], $field['html_id'], $opt_key );

		// Checkbox and multi-select dropdown fields
		if ( $opt_key && ! is_numeric( $opt_key ) && isset( $_POST['item_meta']['other'][ $field['id'] ][ $opt_key ] ) && $_POST['item_meta']['other'][ $field['id'] ][ $opt_key ] ) {
			$posted_val = stripslashes_deep( $_POST['item_meta']['other'][ $field['id'] ][ $opt_key ] );
		    ?>
			<input type="hidden" name="item_meta[other][<?php echo esc_attr( $field['id'] ) ?>][<?php echo esc_attr( $opt_key ) ?>]" id="<?php echo esc_attr( $other_id ) ?>" value="<?php echo esc_attr( $posted_val ); ?>" />
		    <?php

		// Radio fields and regular dropdowns
		} else if ( ! is_array( $field['value'] ) && ! is_array( $_POST['item_meta']['other'][ $field['id'] ] ) ) {
			$posted_val = stripslashes_deep( $_POST['item_meta']['other'][ $field['id'] ] );
			?>
			<input type="hidden" name="item_meta[other][<?php echo esc_attr( $field['id'] ) ?>]" id="<?php echo esc_attr( $other_id ) ?>" value="<?php echo esc_attr( $posted_val ); ?>" />
		    <?php
		}
	}

    /**
     * Check if the field is in a child form and return the parent form id
     * @since 2.0
     * @return int The ID of the form or parent form
     */
    public static function get_parent_form_id($field) {
        $form = FrmForm::getOne($field->form_id);

        // include the parent form ids if this is a child field
        $form_id = $field->form_id;
        if ( ! empty($form->parent_form_id) ) {
            $form_id = $form->parent_form_id;
        }

        return $form_id;
    }

    /**
     * Get the parent section field
     *
     * @since 2.0
     * @return Object|false The section field object if there is one
     */
    public static function get_parent_section($field, $form_id = 0) {
		if ( ! $form_id ) {
            $form_id = $field->form_id;
        }

		$query = array( 'fi.field_order <' => $field->field_order - 1, 'fi.form_id' => $form_id, 'fi.type' => array( 'divider', 'end_divider') );
        $section = FrmField::getAll($query, 'field_order', 1);

        return $section;
    }

    public static function field_on_current_page($field) {
        global $frm_vars;
        $current = true;

        $prev = 0;
        $next = 9999;
        if ( ! is_object($field) ) {
            $field = FrmField::getOne($field);
        }

        if ( $frm_vars['prev_page'] && is_array($frm_vars['prev_page']) && isset($frm_vars['prev_page'][$field->form_id]) ) {
            $prev = $frm_vars['prev_page'][$field->form_id];
        }

        if ( $frm_vars['next_page'] && is_array($frm_vars['next_page']) && isset($frm_vars['next_page'][$field->form_id]) ) {
            $next = $frm_vars['next_page'][$field->form_id];
            if ( is_object($next) ) {
                $next = $next->field_order;
            }
        }

        if ( $field->field_order < $prev || $field->field_order > $next ) {
            $current = false;
        }

        $current = apply_filters('frm_show_field_on_page', $current, $field);
        return $current;
    }

	public static function switch_field_ids( $val ) {
        // for reverse compatability
        return FrmFieldsHelper::switch_field_ids($val);
    }

	public static function get_table_options( $field_options ) {
 		$columns = array();
 		$rows = array();
		if ( is_array( $field_options ) ) {
			foreach ( $field_options as $opt_key => $opt ) {
				switch ( substr( $opt_key, 0, 3 ) ) {
 				case 'col':
 					$columns[$opt_key] = $opt;
 					break;
 				case 'row':
 					$rows[$opt_key] = $opt;
 					break;
 				}
 			}
 		}
 		return array( $columns, $rows );
 	}

	public static function set_table_options( $field_options, $columns, $rows ) {
		if ( is_array( $field_options ) ) {
			foreach ( $field_options as $opt_key => $opt ) {
				if ( substr( $opt_key, 0, 3 ) == 'col' || substr( $opt_key, 0, 3 ) == 'row' ) {
 					unset($field_options[$opt_key]);
				}
 			}
			unset( $opt_key, $opt );
		} else {
 			$field_options = array();
		}

		foreach ( $columns as $opt_key => $opt ) {
			$field_options[ $opt_key ] = $opt;
		}

		foreach ( $rows as $opt_key => $opt ) {
			$field_options[ $opt_key ] = $opt;
		}

 		return $field_options;
 	}

	public static function modify_available_fields( $field_types ) {
		// Add additional options to Section fields
		$field_types['divider'] = array(
			'name'  => __( 'Section', 'formidable' ),
			'types' => array(
				''   => __( 'Heading', 'formidable' ),
				'slide'  => __( 'Collapsible', 'formidable' ),
				'repeat' => __( 'Repeatable', 'formidable' ),
			),
		);

		// Add additional options to Dynamic fields
		$field_types['data'] = array(
			'name'  => __( 'Dynamic Field', 'formidable' ),
			'types' => array(
				'select'    => __( 'Dropdown', 'formidable' ),
				'radio'     => __( 'Radio Buttons', 'formidable' ),
				'checkbox'  => __( 'Checkboxes', 'formidable' ),
				'data'      => __( 'List', 'formidable' ),
			),
		);

		// only show the credit card field when an add-on says so
		$show_credit_card = apply_filters( 'frm_include_credit_card', false );
		if ( ! $show_credit_card ) {
			unset( $field_types['credit_card'] );
		}

		$field_types['lookup'] = FrmProLookupFieldsController::get_lookup_options_for_insert_fields_tab();

		return $field_types;
	}

	/**
	* Allow text values to autopopulate Dynamic fields
	*
	* @since 2.0.15
	* @param string|array $value
	* @param object $field
	* @param boolean $dynamic_default
	* @param boolean $allow_array
	* @return string|array $value
	*/
	public static function get_dynamic_field_default_value( $value, $field, $dynamic_default = true, $allow_array = false ) {
		if ( $field->type == 'data' && isset( $field->field_options['data_type'] ) && $field->field_options['data_type'] != 'data' && $value && ! is_numeric( $value ) ) {
			// If field is Dynamic dropdown, checkbox, or radio field and the default value is not an entry ID

			if ( is_array( $value ) ) {
				$new_values = array();
				foreach ( $value as $val ) {
					$val = trim( $val );
					if ( $val && ! is_numeric( $val ) ) {
						$new_values[] = self::get_id_for_dynamic_field( $field, $val );
					} else if ( is_numeric( $val ) ) {
						$new_values[] = $val;
					}
				}
				$value = $new_values;
			} else {
				$value = self::get_id_for_dynamic_field( $field, $value );
			}
		}
		return $value;
	}

	/**
	* Get the entry ID or category ID to autopopulate a Dynamic field
	*
	* @since 2.0.15
	* @param object $field
	* @param string $value
	* @return int $value
	*/
	private static function get_id_for_dynamic_field( $field, $value ) {
		if ( isset( $field->field_options['post_field'] ) && $field->field_options['post_field'] == 'post_category' ) {
			// Category fields
			$id = FrmProField::get_cat_id_from_text( $value );
		} else {
			// Non post fields
			$id = FrmProField::get_dynamic_field_entry_id( $field->field_options['form_select'], $value, '=' );
		}
		return $id;
	}

	/**
	* Get the hidden inputs for a Dynamic field when it has no options to show or when it is readonly
	*
	* @since 2.0.16
	* @param array $field
	* @param string $disabled
	*/
	public static function maybe_get_hidden_dynamic_field_inputs( $field, $args ) {
		if ( ! in_array( $field['data_type'], array( 'select', 'radio', 'checkbox' ) ) ) {
			return;
		}

		if ( ( empty( $field['options'] ) || ! empty( $args['disabled'] ) ) ) {
			$field_name = $args['field_name'];
			$html_id = $args['html_id'];

			if ( is_array( $field['value'] ) ) {
				foreach ( $field['value'] as $value ) {
					require( FrmAppHelper::plugin_path() .'/pro/classes/views/frmpro-fields/hidden-dynamic-inputs.php' );
            	}
			} else {
				$value = $field['value'];
				require( FrmAppHelper::plugin_path() .'/pro/classes/views/frmpro-fields/hidden-dynamic-inputs.php' );
			}
		}
	}

	/**
	 * Get the classes for a field div
	 *
	 * @since 2.02.05
	 * @param string $classes
	 * @param array $field
	 * @param array $args (should include field_id item)
	 * @return string
	 */
	public static function get_field_div_classes( $classes, $field, $args ) {
		// Add a class for repeating/embedded fields
		if ( $field['id'] != $args['field_id'] ) {
			$classes .= ' frm_field_' . $field['id'] . '_container';
		}

		// Add class to embedded form field
		if ( $field['type'] == 'form' ) {
			$classes .= ' frm_embed_form_container';
		}

		// Add class to HTML field
		if ( $field['type'] == 'html' ) {
			$classes .= ' frm_html_container';
		}

		// Add classes to inline confirmation field (if it doesn't already have classes set)
		if ( isset( $field['conf_field'] ) && $field['conf_field'] == 'inline' && ! $field['classes'] ) {
			$classes .= ' frm_first frm_half';
		}

		// Add class if field includes other option
		if ( isset( $field['other'] ) && true == $field['other'] ) {
			$classes .= ' frm_other_container';
		}

		// Add class to Dynamic fields
		if ( $field['type'] == 'data' ) {
			$classes .= ' frm_dynamic_' . $field['data_type'] . '_container';
		}

		// Add class to inline Scale field
		if ( $field['type'] == 'scale' && $field['label'] == 'inline' ) {
			$classes .= ' frm_scale_container';
		}

		// Add classes to Section
		if ( $field['type'] == 'divider' ) {

			// If the top margin needs to be removed from a section heading
			if ( $field['label'] == 'none' ) {
				$classes .= ' frm_hide_section';
			}

			// If this is a repeating section that should be hidden with exclude_fields or fields shortcode, hide it
			if ( $field['repeat'] ) {
				global $frm_vars;
				if ( isset( $frm_vars['show_fields'] ) && ! empty( $frm_vars['show_fields'] ) && ! in_array( $field['id'], $frm_vars['show_fields'] ) && ! in_array( $field['field_key'], $frm_vars['show_fields'] ) ) {
					$classes .= ' frm_hidden';
				}
			}
		}

		return $classes;
	}

	public static function get_linked_options( $values, $field, $entry_id = false ) {
		_deprecated_function( __FUNCTION__, '2.01.0', 'FrmProDynamicFieldsController::get_independent_options' );
		return FrmProDynamicFieldsController::get_independent_options( $values, $field, $entry_id );
	}

	public static function include_blank_option($options, $field) {
		_deprecated_function( __FUNCTION__, '2.01.0', 'FrmProDynamicFieldsController::include_blank_option' );
		return FrmProDynamicFieldsController::include_blank_option( $options, $field );
	}

	public static function is_list_field( $field ) {
		_deprecated_function( __FUNCTION__, '2.0.9', 'FrmProField::is_list_field' );
		return FrmProField::is_list_field( $field );
	}

	public static function is_read_only( $field ) {
		_deprecated_function( __FUNCTION__, '2.0.9', 'FrmField::is_read_only' );
		return FrmField::is_read_only( $field );
	}

	public static function is_repeating_field( $field ) {
		_deprecated_function( __FUNCTION__, '2.0.09', 'FrmField::is_repeating_field' );
		return FrmField::is_repeating_field( $field );
	}

	public static function get_file_from_atts( $atts, $field, &$replace_with ) {
		_deprecated_function( __FUNCTION__, '2.0.19', 'FrmProFieldsHelper::get_file_html_from_atts' );
		if ( $field->type == 'file' ) {
			self::get_file_html_from_atts( $atts, $replace_with );
		}
	}

	public static function get_media_from_id( $replace_with, $size, $atts = array() ) {
		_deprecated_function( __FUNCTION__, '2.0.19', 'FrmProFieldsHelper::get_displayed_file_html' );
		$replace_with = (array) $replace_with;
		return self::get_displayed_file_html( $replace_with, $size, $atts );
	}

	public static function get_field_matches() {
		_deprecated_function( __FUNCTION__, '2.02.05', 'FrmProStatisticsController::stats_shortcode' );
		return '';
	}

	public static function value_meets_condition($observed_value, $cond, $hide_opt) {
		_deprecated_function( __FUNCTION__, '2.0', 'FrmFieldsHelper::value_meets_condition' );
		return FrmFieldsHelper::value_meets_condition($observed_value, $cond, $hide_opt);
	}

	public static function get_field_stats( $id, $type = 'total', $user_id = false, $value = false, $round = 100, $limit = '', $atts = array(), $drafts = false ) {
		_deprecated_function( __FUNCTION__, '2.02.05', 'FrmProStatisticsController::stats_shortcode' );
		$pass_atts = array(
			'id' => $id,
			'type' => $type,
			'round' => $round,
			'limit' => $limit,
			'drafts' => $drafts,
		);

		if ( $user_id !== false ) {
			$pass_atts['user_id'] = $user_id;
		}

		if ( $value !== false ) {
			$pass_atts['value'] = $value;
		}

		$pass_atts = array_merge( $pass_atts, $atts );

		return FrmProStatisticsController::stats_shortcode( $pass_atts );
	}

	public static function load_hidden_sub_field_javascript( $field ) {
		_deprecated_function( __FUNCTION__, '2.02.06', 'FrmProNestedFormsController::load_hidden_sub_field_javascript' );
		FrmProNestedFormsController::load_hidden_sub_field_javascript( $field );
	}

	public static function check_conditional_shortcode(&$content, $replace_with, $atts, $tag, $condition = 'if', $args = array() ) {
		_deprecated_function( __FUNCTION__, '2.02.08', 'FrmProContent::' . __FUNCTION__ );
		FrmProContent::check_conditional_shortcode( $content, $replace_with, $atts, $tag, $condition, $args );
	}

	public static function foreach_shortcode($replace_with, $args, &$repeat_content) {
		_deprecated_function( __FUNCTION__, '2.02.08', 'FrmProContent::' . __FUNCTION__ );
		FrmProContent::foreach_shortcode( $replace_with, $args, $repeat_content );
	}

	public static function conditional_replace_with_value($replace_with, $atts, $field, $tag) {
		_deprecated_function( __FUNCTION__, '2.02.08', 'FrmProContent::' . __FUNCTION__ );
		return FrmProContent::conditional_replace_with_value( $replace_with, $atts, $field, $tag );
	}

	public static function trigger_shortcode_atts($atts, $display, $args, &$replace_with) {
		_deprecated_function( __FUNCTION__, '2.02.08', 'FrmProContent::' . __FUNCTION__ );
		FrmProContent::trigger_shortcode_atts( $atts, $display, $args, $replace_with );
	}

	public static function atts_sanitize($replace_with) {
		_deprecated_function( __FUNCTION__, '2.02.08', 'FrmProContent::' . __FUNCTION__ );
		return FrmProContent::atts_sanitize( $replace_with );
	}

	public static function atts_sanitize_url($replace_with) {
		_deprecated_function( __FUNCTION__, '2.02.08', 'FrmProContent::' . __FUNCTION__ );
		return FrmProContent::atts_sanitize_url( $replace_with );
	}

	public static function atts_truncate($replace_with, $atts, $display, $args) {
		_deprecated_function( __FUNCTION__, '2.02.08', 'FrmProContent::' . __FUNCTION__ );
		return FrmProContent::atts_truncate( $replace_with, $atts, $display, $args );
	}

	public static function atts_clickable($replace_with) {
		_deprecated_function( __FUNCTION__, '2.02.08', 'FrmProContent::' . __FUNCTION__ );
		return FrmProContent::atts_clickable( $replace_with );
	}

	public static function replace_shortcodes( $content, $entry, $shortcodes, $display = false, $show = 'one', $odd = '', $args = array() ) {
		_deprecated_function( __FUNCTION__, '2.02.08', 'FrmProContent::' . __FUNCTION__ );
		return FrmProContent::replace_shortcodes( $content, $entry, $shortcodes, $display, $show, $odd, $args );
	}

	public static function replace_single_shortcode($shortcodes, $short_key, $tag, $entry, $display, $args, &$content) {
		_deprecated_function( __FUNCTION__, '2.02.08', 'FrmProContent::' . __FUNCTION__ );
		FrmProContent::replace_single_shortcode( $shortcodes, $short_key, $tag, $entry, $display, $args, $content );
	}

	public static function replace_calendar_date_shortcode($content, $date) {
		_deprecated_function( __FUNCTION__, '2.02.08', 'FrmProContent::' . __FUNCTION__ );
		return FrmProContent::replace_calendar_date_shortcode( $content, $date );
	}

	public static function do_shortcode_event_date(&$content, $atts, $shortcodes, $short_key, $args) {
		_deprecated_function( __FUNCTION__, '2.02.08', 'FrmProContent::' . __FUNCTION__ );
		FrmProContent::do_shortcode_event_date( $content, $atts, $shortcodes, $short_key, $args );
	}

	public static function do_shortcode_entry_count(&$content, $atts, $shortcodes, $short_key, $args) {
		_deprecated_function( __FUNCTION__, '2.02.08', 'FrmProContent::' . __FUNCTION__ );
		FrmProContent::do_shortcode_entry_count( $content, $atts, $shortcodes, $short_key, $args );
	}

	public static function do_shortcode_detaillink(&$content, $atts, $shortcodes, $short_key, $args, $display) {
		_deprecated_function( __FUNCTION__, '2.02.08', 'FrmProContent::' . __FUNCTION__ );
		FrmProContent::do_shortcode_detaillink( $content, $atts, $shortcodes, $short_key, $args, $display );
	}

	public static function do_shortcode_editlink(&$content, $atts, $shortcodes, $short_key, $args) {
		_deprecated_function( __FUNCTION__, '2.02.08', 'FrmProContent::' . __FUNCTION__ );
		FrmProContent::do_shortcode_editlink( $content, $atts, $shortcodes, $short_key, $args );
	}

	public static function do_shortcode_deletelink(&$content, $atts, $shortcodes, $short_key, $args) {
		_deprecated_function( __FUNCTION__, '2.02.08', 'FrmProContent::' . __FUNCTION__ );
		FrmProContent::do_shortcode_deletelink( $content, $atts, $shortcodes, $short_key, $args );
	}

	public static function do_shortcode_evenodd(&$content, $atts, $shortcodes, $short_key, $args) {
		_deprecated_function( __FUNCTION__, '2.02.08', 'FrmProContent::' . __FUNCTION__ );
		FrmProContent::do_shortcode_evenodd( $content, $atts, $shortcodes, $short_key, $args );
	}

	public static function do_shortcode_post_id(&$content, $atts, $shortcodes, $short_key, $args) {
		_deprecated_function( __FUNCTION__, '2.02.08', 'FrmProContent::' . __FUNCTION__ );
		FrmProContent::do_shortcode_post_id( $content, $atts, $shortcodes, $short_key, $args );
	}

	public static function do_shortcode_parent_id(&$content, $atts, $shortcodes, $short_key, $args) {
		_deprecated_function( __FUNCTION__, '2.02.08', 'FrmProContent::' . __FUNCTION__ );
		FrmProContent::do_shortcode_parent_id( $content, $atts, $shortcodes, $short_key, $args );
	}

	public static function do_shortcode_id(&$content, $atts, $shortcodes, $short_key, $args) {
		_deprecated_function( __FUNCTION__, '2.02.08', 'FrmProContent::' . __FUNCTION__ );
		FrmProContent::do_shortcode_id( $content, $atts, $shortcodes, $short_key, $args );
	}

	public static function do_shortcode_created_at(&$content, $atts, $shortcodes, $short_key, $args) {
		_deprecated_function( __FUNCTION__, '2.02.08', 'FrmProContent::' . __FUNCTION__ );
		FrmProContent::do_shortcode_created_at( $content, $atts, $shortcodes, $short_key, $args );
	}

	public static function do_shortcode_updated_at(&$content, $atts, $shortcodes, $short_key, $args) {
		_deprecated_function( __FUNCTION__, '2.02.08', 'FrmProContent::' . __FUNCTION__ );
		FrmProContent::do_shortcode_updated_at( $content, $atts, $shortcodes, $short_key, $args );
	}

	public static function do_shortcode_created_by(&$content, $atts, $shortcodes, $short_key, $args) {
		_deprecated_function( __FUNCTION__, '2.02.08', 'FrmProContent::' . __FUNCTION__ );
		FrmProContent::do_shortcode_created_by( $content, $atts, $shortcodes, $short_key, $args );
	}

	public static function do_shortcode_updated_by(&$content, $atts, $shortcodes, $short_key, $args) {
		_deprecated_function( __FUNCTION__, '2.02.08', 'FrmProContent::' . __FUNCTION__ );
		FrmProContent::do_shortcode_updated_by( $content, $atts, $shortcodes, $short_key, $args );
	}

	public static function do_shortcode_is_draft( &$content, $atts, $shortcodes, $short_key, $args ) {
		_deprecated_function( __FUNCTION__, '2.02.08', 'FrmProContent::' . __FUNCTION__ );
		FrmProContent::do_shortcode_is_draft( $content, $atts, $shortcodes, $short_key, $args );
	}
}
