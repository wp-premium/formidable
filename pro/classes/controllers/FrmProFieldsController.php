<?php

class FrmProFieldsController {

	public static function &change_type( $type, $field ) {
        global $frm_vars;

        remove_filter('frm_field_type', 'FrmFieldsController::change_type');

		// Don't change user ID fields or repeating sections to hidden
		if ( ! ( $type == 'divider' && FrmField::is_option_true( $field, 'repeat' ) ) && $type != 'user_id' && ( isset( $frm_vars['show_fields'] ) && ! empty( $frm_vars['show_fields'] ) ) && ! in_array( $field->id, $frm_vars['show_fields'] ) && ! in_array( $field->field_key, $frm_vars['show_fields'] ) ) {
            $type = 'hidden';
        }

        if ( $type == '10radio' ) {
            $type = 'scale';
        }

        if ( ! FrmAppHelper::is_admin() && $type != 'hidden' && $type != 'divider' ) {
			if ( ! FrmProFieldsHelper::is_field_visible_to_user( $field ) ) {
                $type = 'hidden';
            }
        }

        return $type;
    }

	public static function use_field_key_value( $opt, $opt_key, $field ) {
        //if(in_array($field['post_field'], array( 'post_category', 'post_status')) or ($field['type'] == 'user_id' and is_admin() and current_user_can('administrator')))
		if ( FrmField::is_option_true( $field, 'use_key' ) ||
            ( isset($field['type']) && $field['type'] == 'data' ) ||
            ( isset($field['post_field']) && $field['post_field'] == 'post_status' )
        ) {
            $opt = $opt_key;
        }

        return $opt;
    }

	public static function show_field( $field, $form, $parent_form_id ) {
        global $frm_vars;

        if ( $field['use_calc'] && $field['calc'] ) {
			$ajax = FrmProForm::is_ajax_on( $form );
			$inplace_edit = isset( $frm_vars['inplace_edit'] ) && $frm_vars['inplace_edit'];
			if ( $ajax && FrmAppHelper::doing_ajax() && ! $inplace_edit ) {
				return;
			}

            global $frm_vars;
            if ( ! isset($frm_vars['calc_fields']) ) {
                $frm_vars['calc_fields'] = array();
            }
			$frm_vars['calc_fields'][ $field['field_key'] ] = FrmProFormsHelper::get_calc_rule_for_field( array(
				'field'   => $field,
				'form_id' => $form->id,
				'parent_form_id' => $parent_form_id,
			) );
        }
    }

	public static function build_field_class( $classes, $field ) {
        if ( 'inline' == $field['conf_field'] ) {
            $classes .= ' frm_conf_inline';
        } else if ( 'below' == $field['conf_field'] ) {
            $classes .= ' frm_conf_below';
        }

        return $classes;
    }

    public static function input_html( $field, $echo = true ) {
        $add_html = '';

		self::add_readonly_input_attributes( $field, $add_html );

		self::maybe_add_data_attribute_for_section( $field, $add_html );

		self::add_multiple_select_attribute( $field, $add_html );

        if ( FrmAppHelper::is_admin_page('formidable' ) ) {
            if ( $echo ) {
                echo $add_html;
            }

            //don't continue if we are on the form builder page
            return $add_html;
        }

		FrmProLookupFieldsController::maybe_add_lookup_input_html( $field, $add_html );

		self::add_html5_input_attributes( $field, $add_html );

		$add_html .= self::setup_input_masks( $field );

		if ( $echo ) {
            echo $add_html;
		}

        return $add_html;
    }

	public static function setup_input_masks( $field ) {
		$html = '';
		$text_lookup = $field['type'] == 'lookup' && $field['data_type'] == 'text';
		$is_format_field = in_array( $field['type'], array( 'phone', 'text' ) ) || $text_lookup;
		if ( FrmProField::is_format_option_true_with_no_regex( $field ) && $is_format_field ) {
			$html = self::setup_input_mask( $field['format'] );
		}

		return $html;
	}

	public static function setup_input_mask( $format ) {
		global $frm_input_masks;
		$frm_input_masks[] = true;
		return ' data-frmmask="' . esc_attr( preg_replace( '/\d/', '9', $format ) ) . '"';
	}

	/**
	 * Add readonly/disabled input attributes
	 *
	 * @since 2.02.06
	 * @param array $field
	 * @param string $add_html
	 */
    private static function add_readonly_input_attributes( $field, &$add_html ) {
		if ( FrmField::is_option_true( $field, 'read_only' ) && $field['type'] != 'hidden' && $field['type'] != 'lookup' ) {
			global $frm_vars;

			if ( ( isset( $frm_vars['readonly'] ) && $frm_vars['readonly'] == 'disabled' ) || ( current_user_can( 'frm_edit_entries' ) && FrmAppHelper::is_admin() ) ) {
				//not read only
			} elseif ( in_array( $field['type'], array( 'select', 'radio', 'checkbox', 'time' ) ) ) {
				$add_html .= ' disabled="disabled" ';
			} else {
				$add_html .= ' readonly="readonly" ';
			}
		}
	}

	/**
	 * Add multiple select attribute
	 *
	 * @since 2.02.06
	 * @param array $field
	 * @param string $add_html
	 */
	private static function add_multiple_select_attribute( $field, &$add_html ) {
		if ( FrmField::is_multiple_select( $field ) ) {
			$add_html .= ' multiple="multiple" ';
		}
	}

	/**
	 * Add a few HTML5 input attributes
	 *
	 * @since 2.02.06
	 * @param array $field
	 * @param string $add_html
	 */
	private static function add_html5_input_attributes( $field, &$add_html ) {
		global $frm_vars;
		$frm_settings = FrmAppHelper::get_settings();

		if ( $frm_settings->use_html ) {
			if ( FrmField::is_option_true( $field, 'autocom' ) && ( $field['type'] == 'select' || ( $field['type'] == 'data' && isset( $field['data_type'] ) && $field['data_type'] == 'select' ) ) ) {
				//add label for autocomplete fields
				$add_html .= ' data-placeholder=" "';
			}

			if ( in_array( $field['type'], array( 'url', 'email' ) ) ) {
				if ( ( ! isset($frm_vars['novalidate']) || ! $frm_vars['novalidate'] ) && ( $field['type'] != 'email' || ( isset($field['value']) && $field['default_value'] == $field['value'] ) ) ) {
					// add novalidate for drafts
					$frm_vars['novalidate'] = true;
				}
			}
		}
	}

	/**
	 * Add data-sectionid attribute for fields in section
	 *
	 * @since 2.01.0
	 * @param array $field
	 * @param string $add_html
	 */
	private static function maybe_add_data_attribute_for_section( $field, &$add_html ) {
		if ( FrmField::is_option_true_in_array( $field, 'in_section' ) ) {
			$add_html .= ' data-sectionid="' . $field['in_section'] . '"';
		}

		// TODO: Add data attribute for embedded form fields as well
	}

	public static function add_field_class( $class, $field ) {
		if ( ! FrmAppHelper::is_admin() && FrmField::is_option_true( $field, 'autocom' ) &&
		( $field['type'] == 'select' || ( $field['type'] == 'data' && isset( $field['data_type'] ) && $field['data_type'] == 'select' ) ) &&
		! empty( $field['options'] ) && ! FrmField::is_read_only( $field ) ) {
			 self::add_autocomplete_classes( $field, $class );
		}

		FrmProLookupFieldsController::maybe_add_autocomplete_class( $field, $class );

        return $class;
    }

	/**
	* Add the autocomplete classes to a $class string
	*
	* @since 2.01.0
	*
	* @param array $field
	* @param string $class
	*/
	public static function add_autocomplete_classes( $field, &$class ) {
		 global $frm_vars;
		 $frm_vars['chosen_loaded'] = true;
		 $class .= ' frm_chzn';

		 $style = FrmStylesController::get_form_style( $field['form_id'] );
		 if ( $style && 'rtl' == $style->post_content['direction'] ) {
			 $class .= ' chosen-rtl';
		 }
	}

	public static function add_separate_value_opt_label( $field ) {
        $class = $field['separate_value'] ? '' : ' frm_hidden';
        echo '<div class="frm-show-click">';
		echo '<div class="field_' . esc_attr( $field['id'] ) . '_option_key frm_option_val_label' . esc_attr( $class ) . '" >' . esc_html__( 'Option Label', 'formidable-pro' ) . '</div>';
		echo '<div class="field_' . esc_attr( $field['id'] ) . '_option_key frm_option_key_label' . esc_attr( $class ) . '" >' . esc_html__( 'Saved Value', 'formidable-pro' ) . '</div>';
        echo '</div>';
    }

	public static function options_form_before( $field ) {
		if ( 'lookup' == $field['type'] ) {
			FrmProLookupFieldsController::show_get_options_from_above_field_options( $field );
		}

        if ( 'data' == $field['type'] ) {
	        $form_list = FrmForm::get_published_forms();

	        $selected_field = $selected_form_id = '';
			$current_field_id = $field['id'];
			if ( isset( $field['form_select'] ) && is_numeric( $field['form_select'] ) ) {
				$selected_field = FrmField::getOne( $field['form_select'] );
		        if ( $selected_field ) {
			        $selected_form_id = FrmProFieldsHelper::get_parent_form_id( $selected_field );
			        $fields = FrmField::get_all_for_form( $selected_form_id );
		        } else {
			        $selected_field = '';
		        }
			} elseif ( isset( $field['form_select'] ) ) {
				$selected_field = $field['form_select'];
			}

	        include(FrmProAppHelper::plugin_path() . '/classes/views/frmpro-fields/options-form-before.php');
        }

	    include(FrmProAppHelper::plugin_path() . '/classes/views/frmpro-fields/back-end/options-before.php');
    }

	public static function options_form_top( $field, $display, $values ) {
        require(FrmProAppHelper::plugin_path() . '/classes/views/frmpro-fields/options-form-top.php');
    }

	public static function options_form( $field, $display, $values ) {
		if ( in_array( $field['type'], array( 'phone', 'lookup', 'text' ) ) ) {
			return;
		}

		FrmProFieldsHelper::fill_default_field_display( $display );

        global $frm_vars;

        $frm_settings = FrmAppHelper::get_settings();

		if ( isset( $display['toggle_labels'] ) && $display['toggle_labels'] ) {
			include( FrmProAppHelper::plugin_path() . '/classes/views/frmpro-fields/back-end/toggle-labels.php' );
		}

        $form_fields = false;
        if ( $display['logic'] && ! empty( $field['hide_field'] ) && is_array( $field['hide_field'] ) ) {
			$form_id = ( isset( $values['id'] ) ? $values['id'] : $field['form_id'] );
			$form_fields = FrmField::get_all_for_form( $form_id );
        }

        if ( 'data' == $field['type'] ) {
			$frm_field_selection = FrmField::pro_field_selection();
        }

		$data_type = FrmField::get_option( $display['field_data'], 'data_type' );
		if ( $display['type'] == 'radio' || $display['type'] == 'checkbox' || ( $display['type'] == 'data' && in_array( $data_type, array( 'radio', 'checkbox' ) ) ) ) {
			include( FrmProAppHelper::plugin_path() . '/classes/views/frmpro-fields/back-end/alignment.php' );
		}

		if ( in_array( $display['type'], array( 'radio', 'checkbox', 'select' ) ) && ( ! isset( $field['post_field'] ) || ( $field['post_field'] != 'post_category' ) ) ) {
		    include( FrmProAppHelper::plugin_path() . '/classes/views/frmpro-fields/back-end/separate-values.php' );
		}

		$include_file_for_field = array(
			'data'        => 'dynamic-field',
			'divider'     => 'repeat-options',
			'end_divider' => 'repeat-buttons',
			'time'        => 'clock-settings',
			'scale'       => 'scale-options',
			'star'        => 'star-options',
			'form'        => 'insert-form',
		);
		if ( isset( $include_file_for_field[ $field['type'] ] ) ) {
			include( FrmProAppHelper::plugin_path() . '/classes/views/frmpro-fields/back-end/' . $include_file_for_field[ $field['type'] ] . '.php' );
		}

		if ( $display['type'] == 'select' || $field['type'] == 'data' ) {
		    include( FrmProAppHelper::plugin_path() . '/classes/views/frmpro-fields/back-end/multi-select.php' );
		}

		$include_file_for_display = array(
			'visibility' => 'visibility',
			'conf_field' => 'confirmation',
			'logic'      => 'logic',
		);

		foreach ( $include_file_for_display as $option => $file ) {
			if ( $display[ $option ] ) {
			    include( FrmProAppHelper::plugin_path() . '/classes/views/frmpro-fields/back-end/' . $file . '.php' );
			}
		}

		if ( $display['default_value'] || $display['calc'] || ( isset( $display['autopopulate'] ) && $display['autopopulate'] ) ) {
		    include( FrmProAppHelper::plugin_path() . '/classes/views/frmpro-fields/back-end/dynamic-values.php' );
		}
    }

	/**
	 * Display the format option
	 *
	 * @since 2.02.06
	 * @param array $field
	 *
	 * @deprecated 3.0
	 * @codeCoverageIgnore
	 */
	public static function show_format_option( $field ) {
		_deprecated_function( __FUNCTION__, '3.0', 'FrmFieldsController::show_format_option' );
		FrmFieldsController::show_format_option( $field );
	}

	/**
	 * Display the visibility option
	 *
	 * @since 2.02.06
	 * @param array $field
	 */
	public static function show_visibility_option( $field ) {
		include( FrmProAppHelper::plugin_path() . '/classes/views/frmpro-fields/back-end/visibility.php' );
	}

	/**
	 * Display the conditional logic option
	 *
	 * @since 2.02.06
	 * @param array $field
	 */
	public static function show_conditional_logic_option( $field ) {
		$form_fields = false;
		if ( ! empty( $field['hide_field'] ) && is_array( $field['hide_field'] ) ) {
			$form_id = isset( $field['parent_form_id'] ) ? $field['parent_form_id'] : $field['form_id'];
			$form_fields = FrmField::get_all_for_form( $form_id );
		}
		include( FrmProAppHelper::plugin_path() . '/classes/views/frmpro-fields/back-end/logic.php' );
	}

	/**
	 * Display the Dynamic Values section
	 *
	 * @since 2.02.06
	 * @param array $field
	 * @param array $display
	 */
	public static function show_dynamic_values_options( $field, $display ) {
		include( FrmProAppHelper::plugin_path() . '/classes/views/frmpro-fields/back-end/dynamic-values.php' );
	}

	public static function get_field_selection() {
		FrmAppHelper::permission_check('frm_view_forms');
        check_ajax_referer( 'frm_ajax', 'nonce' );

		$current_field_id = FrmAppHelper::get_post_param( 'field_id', '', 'absint' );
		$form_id = FrmAppHelper::get_post_param( 'form_id', '', 'sanitize_text_field' );

		if ( is_numeric( $form_id ) ) {
            $selected_field = '';
			$fields = FrmField::get_all_for_form( $form_id );
			if ( $fields ) {
                require( FrmProAppHelper::plugin_path() . '/classes/views/frmpro-fields/field-selection.php' );
			}
        } else {
            $selected_field = $form_id;

            if ( $selected_field == 'taxonomy' ) {
				echo '<span class="howto">' . esc_html__( 'Select a taxonomy on the Form Actions tab of the Form Settings page', 'formidable-pro' ) . '</span>';
				echo '<input type="hidden" name="field_options[form_select_' . esc_attr( $current_field_id ) . ']" value="taxonomy" />';
            }
        }

        wp_die();
    }

	/**
	 * Get the field value selector for field or action logic
	 */
	public static function get_field_values() {
		FrmAppHelper::permission_check('frm_view_forms');
        check_ajax_referer( 'frm_ajax', 'nonce' );

	    $selector_args = array(
	    	'value' => '',
	    );

	    $selector_args['html_name'] = sanitize_text_field( $_POST['name'] );
	    if ( empty( $selector_args['html_name'] ) || $selector_args['html_name'] == 'undefined' ) {
		    $selector_args['html_name'] = 'field_options[hide_opt_' . absint( $_POST['current_field'] ) . '][]';
	    }

	    if ( FrmAppHelper::get_param( 'form_action', '', 'get', 'sanitize_text_field' ) == 'update_settings' ) {
	    	$selector_args['source'] = 'form_actions';
	    } else {
		    $field_type = sanitize_text_field( $_POST['t'] );
	    	$selector_args['source'] = ! empty( $field_type ) ? $field_type : 'unknown';
	    }

	    FrmFieldsHelper::display_field_value_selector( absint( $_POST['field_id'] ), $selector_args );

        wp_die();
    }

	public static function get_dynamic_widget_opts() {
        check_ajax_referer( 'frm_ajax', 'nonce' );

        $form_id = get_post_meta( (int) $_POST['display_id'], 'frm_form_id', true );
        if ( ! $form_id ) {
            wp_die();
        }

		$fields = FrmField::getAll( array( 'fi.type not' => FrmField::no_save_fields(), 'fi.form_id' => $form_id ), 'field_order');

        $options = array(
            'titleValues'   => array(),
            'catValues'     => array(),
        );

        foreach ( $fields as $field ) {
			$options['titleValues'][ $field->id ] = $field->name;
            if ( $field->type == 'select' || $field->type == 'radio' ) {
				$options['catValues'][ $field->id ] = $field->name;
            }
            unset($field);
        }

        echo json_encode($options);

        wp_die();
    }

	public static function date_field_js( $field_id, $options ) {
        if ( ! isset($options['unique']) || ! $options['unique'] ) {
            return;
        }

        $defaults = array(
            'entry_id' => 0, 'start_year' => '-10', 'end_year' => '+10',
            'locale' => '', 'unique' => 0, 'field_id' => 0
        );

        $options = wp_parse_args($options, $defaults);

        global $wpdb;

        $field = FrmField::getOne($options['field_id']);

        if ( isset($field->field_options['post_field']) && $field->field_options['post_field'] != '' ) {
			$query = array( 'post_status' => array( 'publish', 'draft', 'pending', 'future', 'private' ) );
            if ( $field->field_options['post_field'] == 'post_custom' ) {
				$get_field = 'meta_value';
				$get_table = $wpdb->postmeta . ' pm LEFT JOIN ' . $wpdb->posts . ' p ON (p.ID=pm.post_id)';
				$query['meta_value !'] = '';
				$query['meta_key'] = $field->field_options['custom_field'];
            } else {
				$get_field = sanitize_title( $field->field_options['post_field'] );
				$get_table = $wpdb->posts;
            }

			$post_dates = FrmDb::get_col( $get_table, $query, $get_field );
        }

        if ( ! is_numeric($options['entry_id']) ) {
            $disabled = wp_cache_get($options['field_id'], 'frm_used_dates');
        }

        if ( ! isset($disabled) || ! $disabled ) {
			$disabled = FrmDb::get_col( $wpdb->prefix . 'frm_item_metas', array( 'field_id' => $options['field_id'], 'item_id !' => $options['entry_id'] ), 'meta_value' );
        }

        if ( isset($post_dates) && $post_dates ) {
            $disabled = array_unique(array_merge( (array) $post_dates, (array) $disabled ));
        }

		/**
		 * Allows additional logic to be added to selectable dates
		 * To prevent weekends from being selectable, 'true' would be changed to '(day != 0 && day != 6)'
		 *
		 * @since 2.0
		 */
		$selectable_response = apply_filters( 'frm_selectable_dates', 'true', compact( 'field', 'options' ) );

        $disabled = apply_filters('frm_used_dates', $disabled, $field, $options);
		$js_vars = 'var m=(date.getMonth()+1),d=date.getDate(),y=date.getFullYear(),day=date.getDay();';
		if ( empty( $disabled ) ) {
			if ( $selectable_response != 'true' ) {
				// If the filter has been used, include it
				echo ',beforeShowDay:function(date){' . $js_vars . 'return [' . $selectable_response . '];}';
			}

            return;
        }

        if ( ! is_numeric($options['entry_id']) ) {
            wp_cache_set($options['field_id'], $disabled, 'frm_used_dates');
        }

        $formatted = array();
        foreach ( $disabled as $dis ) { //format to match javascript dates
            $formatted[] = date('Y-n-j', strtotime($dis));
        }

        $disabled = $formatted;
        unset($formatted);

		echo ',beforeShowDay: function(date){' . $js_vars . 'var disabled=' . json_encode( $disabled ) . ';if($.inArray(y+"-"+m+"-"+d,disabled) != -1){return [false];} return [' . $selectable_response . '];}';

        //echo ',beforeShowDay: $.datepicker.noWeekends';
    }

	/**
	 * @since 2.0.23
	 */
	public static function maybe_make_field_optional( $required, $field ) {
		if ( $required && ! FrmAppHelper::is_admin_page('formidable' ) ) {
			global $frm_vars;
			$is_editing = isset( $frm_vars['editing_entry'] ) && $frm_vars['editing_entry'] && is_numeric( $frm_vars['editing_entry'] );
			if ( $is_editing ) {
				$optional_on_edit = apply_filters( 'frm_optional_fields_on_edit', array( 'password', 'credit_card' ) );
				if ( in_array( $field['type'], (array) $optional_on_edit ) ) {
					$entry = FrmEntry::getOne( $frm_vars['editing_entry'] );
					if ( $entry && $entry->form_id === $field['form_id'] && ! $entry->is_draft ) {
						$required = false;
					}
				}
			}
		}
		return $required;
	}

	public static function ajax_get_data() {
        //check_ajax_referer( 'frm_ajax', 'nonce' );

		$entry_id = self::get_posted_entry_ids();
		$current_field = FrmAppHelper::get_param( 'current_field', '', 'get', 'absint' );
		$hidden_field_id = FrmAppHelper::get_param( 'hide_id', '', 'get', 'sanitize_text_field' );

		$current = FrmField::getOne($current_field);
		$data_field = FrmField::getOne( $current->field_options['form_select'] );
		if ( strpos( $entry_id, ',' ) ) {
            $entry_id = explode(',', $entry_id);
            $meta_value = array();
			foreach ( $entry_id as $eid ) {
                $new_meta = FrmProEntryMetaHelper::get_post_or_meta_value($eid, $data_field);
                if ( $new_meta ) {
					foreach ( (array) $new_meta as $nm ) {
                        array_push($meta_value, $nm);
                        unset($nm);
                    }
                }
                unset($new_meta, $eid);
            }
			$meta_value = array_unique( $meta_value );
		} else {
            $meta_value = FrmProEntryMetaHelper::get_post_or_meta_value($entry_id, $data_field);
        }

		if ( $meta_value === null ) {
			wp_die();
		}

		$data_display_opts = apply_filters( 'frm_display_data_opts', array( 'html' => true, 'wpautop' => false ) );
		$value = FrmFieldsHelper::get_display_value( $meta_value, $data_field, $data_display_opts );
		if ( is_array( $value ) ) {
			$value = implode( ', ', $value );
		}

		if ( is_array( $meta_value ) ) {
			$meta_value = implode( ', ', $meta_value );
		}

        $current_field = (array) $current;
		foreach ( $current->field_options as $o => $v ) {
			if ( ! isset( $current_field[ $o ] ) ) {
				$current_field[ $o ] = $v;
			}
            unset($o, $v);
        }

        // Set up HTML ID and HTML name
        $html_id = '';
        $field_name = 'item_meta';
        FrmProFieldsHelper::get_html_id_from_container($field_name, $html_id, (array) $current, $hidden_field_id);

		$on_current_page = FrmAppHelper::get_param( 'on_current_page', 'true', 'get', 'sanitize_text_field' );
		$on_current_page = ( $on_current_page == 'true' );

		if ( $on_current_page && FrmProFieldsHelper::is_field_visible_to_user( $current ) ) {
			if ( FrmAppHelper::is_not_empty_value( $value ) && $value !== false ) {
				echo apply_filters( 'frm_show_it', "<p class='frm_show_it'>" . $value . "</p>\n", $value, array( 'field' => $data_field, 'value' => $meta_value, 'entry_id' => $entry_id ) );
			}
			echo '<input type="hidden" id="' . esc_attr( $html_id ) . '" name="' . esc_attr( $field_name ) . '" value="' . esc_attr( $value ) . '" ' . do_action( 'frm_field_input_html', $current_field, false ) . '/>';
		} else {
			echo esc_attr( $value );
		}

		wp_die();
    }

	/**
	 * @since 2.05.04
	 */
	private static function get_posted_entry_ids() {
		$entry_id = FrmAppHelper::get_param( 'entry_id', '', 'get', 'sanitize_text_field' );
		if ( is_array( $entry_id ) ) {
			$entry_id = implode(',', $entry_id);
		}
		return trim( $entry_id, ',' );
	}

	/**
	* Get the HTML for a dependent Dynamic field when the parent changes
	*/
	public static function ajax_data_options() {
        //check_ajax_referer( 'frm_ajax', 'nonce' );

		$args = array(
			'trigger_field_id' => FrmAppHelper::get_param( 'trigger_field_id', '', 'post', 'absint' ),
			'entry_id' => FrmAppHelper::get_param( 'entry_id', '', 'post', 'sanitize_text_field' ),
			'field_id' => FrmAppHelper::get_param( 'field_id', '', 'post', 'absint' ),
			'container_id' => FrmAppHelper::get_param( 'container_id', '', 'post', 'sanitize_title' ),
			'default_value' => FrmAppHelper::get_param( 'default_value', '', 'post', 'sanitize_title' ),
			'prev_val' => FrmAppHelper::get_param( 'prev_val', '', 'post', 'absint' )
		);

		if ( $args['entry_id'] == '' ) {
			wp_die();
		}

		if ( ! is_array( $args['entry_id'] ) ) {
			$entry_id = explode( ',', $args['entry_id'] );
		}

		$args['field_data'] = FrmField::getOne( $args['field_id'] );

		$field = self::initialize_dependent_dynamic_field( $args );

		if ( is_numeric( $args['field_data']->field_options['form_select'] ) ) {
			// If Dynamic field is pulling options from a regular field
			self::get_dependent_dynamic_field_options( $args, $field );

		} else if ( $args['field_data']->field_options['form_select'] == 'taxonomy' ) {
			// If Dynamic field is pulling options from a taxonomy
			self::get_dependent_category_field_options( $args, $field );

		}

		self::get_dependent_dynamic_field_value( $args['prev_val'], $field );

		// Set up HTML ID and HTML name
		$input_args = array(
			'field_name'    => 'item_meta',
			'field_id'      => $args['field_data']->id,
			'field_plus_id' => '',
			'section_id'    => '',
			'html_id'       => '',
		);

		FrmProFieldsHelper::get_html_id_from_container( $input_args['field_name'], $input_args['html_id'], $field, $args['container_id'] );

		if ( FrmField::is_multiple_select( $args['field_data'] ) ) {
			$input_args['field_name'] .= '[]';
		}

		$field_obj = FrmFieldFactory::get_field_type( 'data', $field );
		echo $field_obj->include_front_field_input( $input_args, array() );

		wp_die();
    }

	/**
	* Initialize the field array for a dependent dynamic field
	*
	* @param array $args
	* @return array $field
	*/
	private static function initialize_dependent_dynamic_field( $args ) {
		$field = FrmProFieldsHelper::initialize_array_field( $args['field_data'], $args );
		return $field;
	}

	/**
	* Get the options for a dependent Dynamic field
	*
	* @since 2.0.16
	* @param array $args
	* @param array $field
	*/
	private static function get_dependent_dynamic_field_options( $args, &$field ) {
		$linked_field = FrmField::getOne( $args['field_data']->field_options['form_select'] );

		$field['options'] = array();

		$metas = array();
		FrmProEntryMetaHelper::meta_through_join( $args['trigger_field_id'], $linked_field, $args['entry_id'], $args['field_data'], $metas );
		$metas = stripslashes_deep( $metas );

		if ( FrmProDynamicFieldsController::include_blank_option( $metas, $args['field_data'] ) ) {
			$field['options'][''] = '';
		}

		foreach ( $metas as $meta ) {
			$field['options'][ $meta->item_id ] = FrmEntriesHelper::display_value( $meta->meta_value, $linked_field,
			array( 'type' => $linked_field->type, 'show_icon' => true, 'show_filename' => false)
		);
			unset($meta);
		}

		// change the form_select value so the filter doesn't override the values
		$args['field_data']->field_options['form_select'] = 'filtered_' . $args['field_data']->field_options['form_select'];

		FrmFieldsHelper::prepare_new_front_field( $field, $args['field_data'] );

		// Sort the options
		$pass_args = array( 'metas' => $metas, 'field' => $linked_field, 'dynamic_field' => $field );
		$field['options'] = apply_filters( 'frm_data_sort', $field['options'], $pass_args );
	}

	/**
	* Get the options for a dependent Dynamic category field
	*
	* @since 2.0.16
	* @param array $args
	* @param array $field
	*/
	private static function get_dependent_category_field_options( $args, &$field ) {
		if ( $args['entry_id'] == 0 ) {
			wp_die();
		}

		if ( is_array( $args['entry_id'] ) ) {
			$zero = array_search(0, $args['entry_id']);
			if ( $zero !== false ) {
				unset( $args['entry_id'][ $zero ] );
			}
			if ( empty( $args['entry_id'] ) ) {
				wp_die();
			}
		}

		FrmFieldsHelper::prepare_new_front_field( $field, $args['field_data'] );

		$cat_ids = array_keys($field['options']);

		$cat_args = array( 'include' => implode(',', $cat_ids), 'hide_empty' => false);

		$post_type = FrmProFormsHelper::post_type( $args['field_data']->form_id );
		$cat_args['taxonomy'] = FrmProAppHelper::get_custom_taxonomy($post_type, $args['field_data']);
		if ( ! $cat_args['taxonomy'] ) {
			wp_die();
		}

		$cats = get_categories($cat_args);
		foreach ( $cats as $cat ) {
			if ( ! in_array( $cat->parent, (array) $args['entry_id'] ) ) {
				unset( $field['options'][ $cat->term_id ] );
			}
		}

		if ( count($field['options']) == 1 && reset($field['options']) == '' ) {
			wp_die();
		}

		// Sort the options
		$field['options'] = apply_filters( 'frm_data_sort', $field['options'], array( 'dynamic_field' => $field ) );
	}

	/**
	* Get the field value for a dependent dynamic field
	*
	* @since 2.0.16
	* @param array $prev_val
	* @param array $field
	*/
	private static function get_dependent_dynamic_field_value( $prev_val, &$field ) {

		// Set the value to the previous value if it was set. Otherwise, set to default value.
		if ( $prev_val ) {
			$prev_val = array_unique( $prev_val );
			$field['value'] = $prev_val;
		} else {
			$field['value'] = $field['default_value'];
		}

		// Unset the field value if it isn't an option
		if ( $field['value'] ) {
			$field['value'] = (array) $field['value'];
			foreach ( $field['value'] as $key => $field_val ) {
				if ( ! array_key_exists( $field_val, $field['options'] ) ) {
					unset( $field['value'][ $key ] );
				}
			}
		}

		if ( is_array( $field['value'] ) && empty( $field['value'] ) ) {
			$field['value'] = '';
		}

		// If we have a radio field, set the field value to a string
		if ( $field['data_type'] == 'radio' && is_array( $field['value'] ) ) {
			$field['value'] = reset( $field['value'] );
		}
	}

	/**
	 * @deprecated 2.03
	 * @codeCoverageIgnore
	 */
	public static function ajax_time_options() {
		_deprecated_function( __FUNCTION__, '2.03', 'FrmProTimeFieldsController::ajax_time_options' );
		FrmProTimeFieldsController::ajax_time_options();
	}

	/**
	 * Add an option at the top of the media library page
	 * to show the unattached Formidable files based on user role.
	 * @since 2.02
	 */
	public static function filter_media_library_link() {
		global $current_screen;
		if ( $current_screen && 'upload' == $current_screen->base && current_user_can('frm_edit_entries') ) {
			echo '<label for="frm-attachment-filter" class="screen-reader-text">';
			echo esc_html__( 'Show form uploads', 'formidable-pro' );
			echo '</label>';

			$filtered = FrmAppHelper::get_param( 'frm-attachment-filter', '', 'get', 'absint' );
			echo '<select name="frm-attachment-filter" id="frm-attachment-filter">';
			echo '<option value="">' . esc_html__( 'Hide form uploads', 'formidable-pro' ) . '</option>';
			echo '<option value="1" ' . selected( $filtered, 1 ) . '>' . esc_html__( 'Show form uploads', 'formidable-pro' ) . '</option>';
			echo '</select>';
		}
	}

	/**
	 * If this file is a Formidable file,
	 * temp redirect to the home page
	 * @since 2.02
	 */
	public static function redirect_attachment() {
		global $post;
		if ( is_attachment() && absint( $post->post_parent ) < 1 && ! current_user_can('frm_edit_entries') ) {
			$is_form_upload = get_post_meta( $post->ID, '_frm_file', true );
			if ( $is_form_upload ) {
				wp_redirect( get_bloginfo('wpurl'), 302 );
				die();
			}
		}
	}

	/**
	 * Check for old temp files and delete them
	 *
	 * @since 2.02
	 */
	public static function delete_temp_files() {
		remove_action( 'pre_get_posts', 'FrmProFileField::filter_media_library', 99 );

		$timestamp_cutoff = date( 'Y-m-d H:i:s', strtotime( '-3 hours' ) );
		$old_uploads = get_posts( array(
			'post_type' => 'attachment',
			'posts_per_page' => 50,
			'date_query' => array(
				'column' => 'post_date_gmt',
				'before' => $timestamp_cutoff,
			),
			'meta_query' => array(
				array(
					'key'   => '_frm_temporary',
					'compare' => 'EXISTS',
				),
			),
			'post_parent' => 0,
		) );

		foreach ( $old_uploads as $upload ) {
			// double check in case other plugins have changed the query
			$is_temp = get_post_meta( $upload->ID, '_frm_temporary', true );
			if ( $is_temp ) {
				wp_delete_attachment( $upload->ID, true );
			}
		}

		add_action( 'pre_get_posts', 'FrmProFileField::filter_media_library', 99 );
	}

	public static function ajax_upload() {
		check_ajax_referer( 'frm_ajax', 'nonce' );
		$response = FrmProFileField::ajax_upload();

		if ( ! empty( $response['errors'] ) ) {
			status_header( 403 );
			$status = 403;
			echo implode( ' ', $response['errors'] );
		} else {
			$status = 200;
			echo json_encode( $response['media_ids'] );
		}

		wp_die( '', '', array( 'response' => $status ) );
	}

	public static function _logic_row() {
        check_ajax_referer( 'frm_ajax', 'nonce' );
	    FrmAppHelper::permission_check('frm_edit_forms', 'show');

		$meta_name = FrmAppHelper::get_post_param( 'meta_name', '', 'absint' );
		$field_id = FrmAppHelper::get_post_param( 'field_id', '', 'absint');
		$form_id = FrmAppHelper::get_post_param( 'form_id', '', 'absint' );
	    $hide_field = '';

        $field = FrmField::getOne($field_id);
        $field = FrmFieldsHelper::setup_edit_vars($field);

		$form_fields = FrmField::get_all_for_form( $form_id );

		if ( $field['form_id'] != $form_id ) {
			$field['parent_form_id'] = $form_id;
		}

        if ( ! isset( $field['hide_field_cond'][ $meta_name ] ) ) {
			$field['hide_field_cond'][ $meta_name ] = '==';
        }

        include(FrmProAppHelper::plugin_path() . '/classes/views/frmpro-fields/_logic_row.php');
        wp_die();
	}

	public static function populate_calc_dropdown() {
        check_ajax_referer( 'frm_ajax', 'nonce' );
		FrmAppHelper::permission_check('frm_edit_forms');

	    if ( isset($_POST['form_id']) && isset($_POST['field_id']) ) {
			echo FrmProFieldsHelper::get_shortcode_select( sanitize_text_field( $_POST['form_id'] ), 'frm_calc_' . sanitize_text_field( $_POST['field_id'] ), 'calc' );
        }
	    wp_die();
	}

	public static function create_multiple_fields( $new_field, $form_id ) {
	    // $args = compact('field_data', 'form_id', 'field');
	    if ( empty($new_field) || $new_field['type'] != 'divider' ) {
	        return;
	    }

	    // Add an "End section" when a section field is created
	    FrmFieldsController::include_new_field('end_divider', $form_id);
	}

	/**
	 * Set the form id for the repeating section and any fields inside it
	 *
	 * @since 2.0
	 */
	public static function toggle_repeat() {
        check_ajax_referer( 'frm_ajax', 'nonce' );
		FrmAppHelper::permission_check('frm_edit_forms');

		$form_id = absint( $_POST['form_id'] ); // $form_id should be empty for non-repeating sections
		$parent_form_id = absint( $_POST['parent_form_id'] );
		$checked = absint( $_POST['checked'] );
		$new_form_name = sanitize_text_field( $_POST['field_name'] );

		// Switch to repeating
		if ( $checked ) {

			$form_id = FrmProField::create_repeat_form( 0, array( 'parent_form_id' => $parent_form_id, 'field_name' => $new_form_name ) );

			// New form_select
			echo absint( $form_id );
		}

        if ( $form_id ) {
			$field_id = absint( $_POST['field_id'] );

			// get the array of child fields
            $children = array_filter( (array) $_POST['children'], 'is_numeric');

            if ( ! empty( $children ) ) {
				FrmProFieldsHelper::update_for_repeat( compact('form_id', 'parent_form_id', 'checked', 'field_id', 'children' ) );
            }
        }

        wp_die();
    }

	/**
	 * Update a field after dragging and dropping it on the form builder page
	 *
	 * @since 2.0.24
	 */
	public static function update_field_after_move() {
		FrmAppHelper::permission_check('frm_edit_forms');
		check_ajax_referer( 'frm_ajax', 'nonce' );

		$field_id = FrmAppHelper::get_post_param( 'field', 0, 'absint' );
		$form_id = FrmAppHelper::get_post_param( 'form_id', 0, 'absint' );
		$section_id = FrmAppHelper::get_post_param( 'section_id', 0, 'absint' );

		if ( ! $field_id ) {
			wp_die();
		}

		$update_values = array();

		$field_options = FrmDb::get_var( 'frm_fields', array( 'id' => $field_id ), 'field_options' );
		$field_options = unserialize( $field_options );

		// Update the in_section value
		if ( ! isset( $field_options['in_section'] ) || $field_options['in_section'] != $section_id ) {
			$field_options['in_section'] = $section_id;
			$update_values['field_options'] = $field_options;
		}

		// Update the form_id value
		if ( $form_id ) {
			$update_values['form_id'] = $form_id;
		}

		FrmField::update( $field_id, $update_values );

		wp_die();
	}

	public static function duplicate_section( $section_field, $form_id ) {
		FrmAppHelper::permission_check('frm_edit_forms');
        check_ajax_referer( 'frm_ajax', 'nonce' );

		global $wpdb, $frm_duplicate_ids;

		if ( isset( $_POST['children'] ) ) {
			$children = array_filter( (array) $_POST['children'], 'is_numeric');
			$fields = FrmField::getAll( array( 'fi.id' => $children ), 'field_order');
		} else {
			$fields = array();
		}
		array_unshift( $fields, $section_field );

		$order_query = array( 'field_order >' => $section_field->field_order, 'form_id' => $form_id, 'type' => 'end_divider' );
		$end_section_order = FrmDb::get_var( 'frm_fields', $order_query, 'field_order', array( 'order_by' => 'field_order ASC' ) );
		$field_order = max( $section_field->field_order, $end_section_order );

        $ended = false;

        if ( isset($section_field->field_options['repeat']) && $section_field->field_options['repeat'] ) {
			// create the repeatable form
			$new_form_id = FrmProField::create_repeat_form( 0, array( 'parent_form_id' => $form_id, 'field_name' => $section_field->name ) );

        } else {
            $new_form_id = $form_id;
        }

        foreach ( $fields as $field ) {
            // keep the current form id or give it the id of the newly created form
            $this_form_id = $field->form_id == $form_id ? $form_id : $new_form_id;

            $values = array();
            FrmFieldsHelper::fill_field( $values, $field, $this_form_id );
            if ( FrmField::is_repeating_field( $field ) ) {
                $values['field_options']['form_select'] = $new_form_id;
            }

			$values['field_order'] = $field_order;
			$field_order++;

	        $values = apply_filters( 'frm_duplicated_field', $values );
	        $field_id = FrmField::create( $values );

	        if ( ! $field_id ) {
		        continue;
	        }

	        $frm_duplicate_ids[ $field->id ] = $field_id;
	        $frm_duplicate_ids[ $field->field_key ] = $field_id;

            if ( 'end_divider' == $field->type ) {
                $ended = true;
            }

			$values['id'] = $this_form_id;
			FrmFieldsController::load_single_field($field_id, $values);
        }

        if ( ! $ended ) {
            //make sure the section is ended
            self::create_multiple_fields( (array) $section_field, $form_id );
        }

        // Prevent the function in the free version from completing
        wp_die();
    }

	/**
	 *
	 * Update the repeating form name when a repeating section name is updated
	 *
	 * @since 3.0.03
	 *
	 * @param array $values
	 * @return array $values
	 */
	public static function update_repeater_form_name( $values ) {
		if ( isset( $values['field_options']['repeat'] ) && $values['field_options']['repeat'] ) {
			FrmForm::update( $values['field_options']['form_select'], array( 'name' => $values['name'] ) );
		}

		return $values;
	}

	/**
	 *
	 * Update the repeating form name when a repeating section name is updated
	 *
	 * @since 2.0.12
	 * @deprecated 2.04
	 * @codeCoverageIgnore
	 *
	 * @param array $atts
	 */
	public static function update_repeating_form_name( $atts ) {
		_deprecated_function( __FUNCTION__, '3.0.03', 'FrmProFieldsController::update_repeater_form_name' );
		$field = FrmField::getOne( $atts['id'] );
		if ( FrmField::is_repeating_field( $field ) ) {
			FrmForm::update( $field->field_options['form_select'], array( 'name' => $atts['value'] ) );
		}
	}

	/**
	 * Setup each field's array when an entry is being edited
	 * Similar to FrmAppHelper::fill_field_defaults
	 *
	 * @since 2.01.0
	 *
	 * @param object $entry
	 * @param array $fields
	 * @param array $args (always contains 'parent_form_id')
	 * If field is repeating, $args includes 'repeating', 'parent_field_id' and 'key_pointer'
	 * If field is embedded, $args includes 'in_embed_form'
	 * @return array
	*/
	public static function setup_field_data_for_editing_entry( $entry, $fields, $args ) {
		$new_fields = array();

		foreach ( $fields as $field ) {
			$default_value = apply_filters('frm_get_default_value', $field->default_value, $field, true );

			$field_value = self::get_posted_or_saved_value( $entry, $field, $args );

			$field_array = array(
				'id'            => $field->id,
				'value'         => $field_value,
				'default_value' => $default_value,
				'name'          => $field->name,
				'description'   => $field->description,
				'type'          => apply_filters('frm_field_type', $field->type, $field, $field_value),
				'options'       => $field->options,
				'required'      => $field->required,
				'field_key'     => $field->field_key,
				'field_order'   => $field->field_order,
				'form_id'       => $field->form_id,
				'parent_form_id' => $args['parent_form_id'],
				'in_embed_form' => isset( $args['in_embed_form'] ) ? $args['in_embed_form'] : '0',
			);

			FrmFieldsHelper::prepare_edit_front_field( $field_array, $field, $entry->id, $args );

			if ( ! isset( $field_array['unique'] ) || ! $field_array['unique'] ) {
				$field_array['unique_msg'] = '';
			}

			$field_array = array_merge( $field->field_options, $field_array );

			$values['fields'][ $field->id ] = $field_array;

			$new_fields[ $field->id ] = $field_array;
		}

		return $new_fields;
	}

	/**
	* If the field has a posted value, get it. Otherwise, get the saved field value
	*
	* @since 2.01.0
	* @param object $entry
	* @param object $field
	* @param array $args (if repeating, this includes 'repeating', 'parent_field_id', and 'key_pointer')
	* @return string|array $field_value
	*/
	private static function get_posted_or_saved_value( $entry, $field, $args ) {
		if ( isset( $args['save_draft_click'] ) && $args['save_draft_click'] && FrmField::is_repeating_field( $field ) ) {
			// If save draft was just clicked, and this is a repeating section, get the saved value
			$field_value = self::get_saved_value( $entry, $field );

		} else if ( FrmEntriesHelper::value_is_posted( $field, $args ) ) {
			$field_value = '';
			FrmEntriesHelper::get_posted_value( $field, $field_value, $args );

		} else {
			$field_value = self::get_saved_value( $entry, $field );
		}

		return $field_value;
	}

	/**
	 * Get the saved value for a field
	 *
	 * @since 2.02.05
	 * @param object $entry
	 * @param object $field
	 * @return array|bool|mixed|string
	 */
	private static function get_saved_value( $entry, $field ) {
		$pass_args = array(
			'links' => false,
			'truncate' => false,
		);
		return FrmProEntryMetaHelper::get_post_or_meta_value( $entry, $field, $pass_args );
	}

	/**
	 * @deprecated 3.0
	 * @codeCoverageIgnore
	 */
	public static function show_normal_field( $show ) {
		_deprecated_function( __METHOD__, '3.0' );
		return $show;
	}

	/**
	 * @deprecated 3.0
	 * @codeCoverageIgnore
	 */
	public static function &normal_field_html( $show ) {
		_deprecated_function( __METHOD__, '3.0' );
		return $show;
	}

	/**
	 * @deprecated 3.0
	 * @codeCoverageIgnore
	 */
	public static function show_other() {
		_deprecated_function( __METHOD__, '3.0' );
	}

	/**
	 * @deprecated 3.0
	 * @codeCoverageIgnore
	 */
	public static function show( $field, $name = '' ) {
		_deprecated_function( __FUNCTION__, '3.0', 'FrmFieldType::show_on_form_builder' );
	}

	/**
	 * @deprecated 2.05
	 */
	public static function &label_position( $position ) {
		_deprecated_function( __METHOD__, '2.05', 'FrmFieldsHelper::label_position' );
		return $position;
	}

	/**
	 * @deprecated 3.0
	 * @codeCoverageIgnore
	 */
	public static function display_field_options( $display ) {
		_deprecated_function( __FUNCTION__, '3.0', 'FrmFieldType Modals' );
		return $display;
	}

	/**
	 * @deprecated 3.0
	 * @codeCoverageIgnore
	 */
	public static function form_fields( $field, $field_name, $atts ) {
		_deprecated_function( __FUNCTION__, '3.0', 'FrmFieldType Modals' );
    }

	/**
	 * @deprecated 2.03.10
	 * @codeCoverageIgnore
	 */
	public static function prepare_single_field_for_duplication( $field_values ) {
		_deprecated_function( __FUNCTION__, '2.03.10', 'custom code' );

		return $field_values;
	}
}
