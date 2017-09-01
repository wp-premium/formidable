<?php
class FrmProEntryMeta{

    public static function before_save($values) {
        $field = FrmField::getOne($values['field_id']);
        if ( ! $field ) {
            return $values;
        }

		if ( $field->type == 'date' ) {
			$values['meta_value'] = FrmProAppHelper::maybe_convert_to_db_date($values['meta_value'], 'Y-m-d');
		} else if ( $field->type == 'number' && ! is_numeric($values['meta_value']) ) {
			$values['meta_value'] = (float) $values['meta_value'];
		} elseif ( $field->type == 'time' ) {
			$values['meta_value'] = FrmProAppHelper::format_time( $values['meta_value'], 'H:i' );
		}

        return $values;
    }

	/**
	 * @since 2.0.11
	 */
	public static function update_single_field( $atts ) {
		if ( empty( $atts['entry_id'] ) ) {
			return;
		}

		$field = $atts['field_id'];
		FrmField::maybe_get_field( $field );
		if ( ! $field ) {
			return;
		}

		if ( isset( $field->field_options['post_field'] ) && ! empty( $field->field_options['post_field'] ) ) {
			$post_id = FrmDb::get_var( 'frm_items', array( 'id' => $atts['entry_id'] ), 'post_id' );
		} else {
			$post_id = false;
		}

		global $wpdb;
		if ( ! $post_id ) {
			$updated = FrmEntryMeta::update_entry_meta( $atts['entry_id'], $field->id, null, $atts['value'] );

			if ( ! $updated ) {
				$wpdb->query( $wpdb->prepare("DELETE FROM {$wpdb->prefix}frm_item_metas WHERE item_id = %d and field_id = %d", $atts['entry_id'], $field->id ) );
				$updated = FrmEntryMeta::add_entry_meta( $atts['entry_id'], $field->id, '', $atts['value'] );
			}
			wp_cache_delete( $atts['entry_id'], 'frm_entry' );
		} else {
			switch ( $field->field_options['post_field'] ) {
				case 'post_custom':
					$updated = update_post_meta( $post_id, $field->field_options['custom_field'], maybe_serialize( $atts['value'] ) );
				break;
				case 'post_category':
					$taxonomy = ( ! FrmField::is_option_empty( $field, 'taxonomy' ) ) ? $field->field_options['taxonomy'] : 'category';
					$updated = wp_set_post_terms( $post_id, $atts['value'], $taxonomy );
				break;
				default:
					$post = get_post( $post_id, ARRAY_A );
					$post[ $field->field_options['post_field'] ] = maybe_serialize( $atts['value'] );
					$updated = wp_insert_post( $post );
				break;
			}
		}

		if ( $updated ) {
			// set updated_at time
			$wpdb->update( $wpdb->prefix .'frm_items',
				array( 'updated_at' => current_time('mysql', 1), 'updated_by' => get_current_user_id() ),
				array( 'id' => $atts['entry_id'] )
			);
		}

		$atts['field_id'] = $field->id;
		$atts['field'] = $field;

		do_action( 'frm_after_update_field', $atts );
		return $updated;
	}

    /**
     * Add new tags
     *
     * @since 2.0
     * @param array|string $meta_value (the posted value)
     * @param int $field_id
     * @param int $entry_id
     * @return array|string $meta_value
     *
     */
	public static function prepare_data_before_db( $meta_value, $field_id, $entry_id, $atts ) {
		// If confirmation field or 0 index, exit now
		if ( ! $atts['field'] ) {
			return $meta_value;
		}

		if ( $atts['field']->type == 'tag' ) {
			self::create_new_tags( $atts['field'], $entry_id, $meta_value );
		}

		return $meta_value;
    }

	private static function create_new_tags($field, $entry_id, $meta_value) {
		$tax_type = ( ! FrmField::is_option_empty( $field, 'taxonomy' ) ) ? $field->field_options['taxonomy'] : 'frm_tag';

		$tags = explode( ',', stripslashes( $meta_value ) );
        $terms = array();

        if ( isset($_POST['frm_wp_post']) ) {
            $_POST['frm_wp_post'][$field->id.'=tags_input'] = $tags;
        }

        if ( $tax_type != 'frm_tag' ) {
            return;
        }

        foreach ( $tags as $tag ) {
            $slug = sanitize_title($tag);
            if ( ! isset($_POST['frm_wp_post']) ) {
                if ( ! term_exists($slug, $tax_type) ) {
                    wp_insert_term( trim($tag), $tax_type, array( 'slug' => $slug));
                }
            }

            $terms[] = $slug;
        }

        wp_set_object_terms($entry_id, $terms, $tax_type);

    }

    public static function validate($errors, $field, $value, $args) {
        $field->temp_id = $args['id'];

        // Keep current value for "Other" fields because it is needed for correct validation
        if ( ! $args['other'] ) {
            FrmEntriesHelper::get_posted_value($field, $value, $args);
        }

        if ( $field->type == 'form' ||  FrmField::is_repeating_field( $field ) ) {
            self::validate_embedded_form( $errors, $field, $args['exclude'] );

			// get any values updated during nested validation
			FrmEntriesHelper::get_posted_value( $field, $value, $args );
        } else if ( $field->type == 'user_id' ) {
            // make sure we have a user ID
            if ( ! is_numeric($value) ) {
                $value = FrmAppHelper::get_user_id_param($value);
                FrmEntriesHelper::set_posted_value($field, $value, $args);
            }

            //add user id to post variables to be saved with entry
            $_POST['frm_user_id'] = $value;
        } else if ( $field->type == 'time' && is_array($value) ) {
			FrmProTimeField::time_array_to_string( $value );

            FrmEntriesHelper::set_posted_value($field, $value, $args);
        }

        // don't validate if going backwards
        if ( FrmProFormsHelper::going_to_prev($field->form_id) ) {
            return array();
        }

        // clear any existing errors if draft
        if ( FrmProFormsHelper::saving_draft() && isset($errors['field'. $field->temp_id]) ) {
            unset($errors['field'. $field->temp_id]);
        }

        // if saving draft, only check confirmation field since the confirmation field value is not saved
        if ( FrmProFormsHelper::saving_draft() ) {

            //Check confirmation field if saving a draft
    		self::validate_confirmation_field($errors, $field, $value, $args);

            return $errors;
        }

        self::validate_no_input_fields($errors, $field);
		FrmProTimeField::validate_time_field( $errors, $field, $value );

        if ( empty($args['parent_field_id']) && ! isset($_POST['item_meta'][$field->id]) ) {
            return $errors;
        }

		if ( ( ( $field->type != 'tag' && $value == 0 ) || ( $field->type == 'tag' && $value == '' ) ) && isset( $field->field_options['post_field'] ) && $field->field_options['post_field'] == 'post_category' && $field->required == '1' ) {
            $frm_settings = FrmAppHelper::get_settings();
			$errors['field' . $field->temp_id ] = ( ! isset( $field->field_options['blank'] ) || $field->field_options['blank'] == '' || $field->field_options['blank'] == 'Untitled cannot be blank' ) ? $frm_settings->blank_msg : $field->field_options['blank'];
        }

        //Don't require fields hidden with shortcode fields="25,26,27"
        global $frm_vars;
		if ( self::is_field_hidden_by_shortcode( $field, $errors ) ) {
            unset($errors['field'. $field->temp_id]);
            $value = '';
        }

		// Don't require a conditionally hidden field
		self::clear_errors_and_value_for_conditionally_hidden_field( $field, $errors, $value );

        //make sure the [auto_id] is still unique
        self::validate_auto_id($field, $value);

        //check uniqueness
        self::validate_unique_field($errors, $field, $value);
        self::set_post_fields($field, $value, $errors);

        if ( ! FrmProFieldsHelper::is_field_visible_to_user($field) ) {
            //don't validate admin only fields that can't be seen
            unset($errors['field'. $field->temp_id]);
            FrmEntriesHelper::set_posted_value($field, $value, $args);
            return $errors;
        }

		self::validate_confirmation_field($errors, $field, $value, $args);

        //Don't validate the format if field is blank
        if ( FrmAppHelper::is_empty_value( $value ) ) {
            FrmEntriesHelper::set_posted_value($field, $value, $args);
            return $errors;
        }

        if ( ! is_array($value) ) {
            $value = trim($value);
        }

		self::validate_date_field( $errors, $field, $value );

        FrmEntriesHelper::set_posted_value($field, $value, $args);
        return $errors;
    }

	public static function validate_embedded_form( &$errors, $field, $exclude = array() ) {
		// Check if this section is conditionally hidden before validating the nested fields
		self::validate_no_input_fields( $errors, $field );

        $subforms = array();
        FrmProFieldsHelper::get_subform_ids($subforms, $field);

        if ( empty($subforms) ) {
            return;
        }

		$where = array( 'fi.form_id' => $subforms );
        if ( ! empty( $exclude ) ) {
            $where['fi.type not'] = $exclude;
        }

        $subfields = FrmField::getAll($where, 'field_order');
        unset($where);

        foreach ( $subfields as $subfield ) {
			if ( isset( $_POST['item_meta'][ $field->id ] ) && ! empty( $_POST['item_meta'][ $field->id ] ) ) {
				$posted_values = $_POST['item_meta'][ $field->id ];
				foreach ( $posted_values as $k => $values ) {
					if ( ! empty( $k ) && in_array( $k, array( 'form', 'row_ids' ) ) ) {
						continue;
					}

					FrmEntryValidate::validate_field( $subfield, $errors,
                        ( isset($values[$subfield->id]) ? $values[$subfield->id] : '' ),
                        array(
                            'parent_field_id'  => $field->id,
                            'key_pointer'   => $k,
                            'id'            => $subfield->id .'-'. $field->id .'-'. $k,
                        )
                    );

                    unset($k, $values);
                }
            } else {
                // TODO: do something if nothing was submitted
            }
        }
    }

	/**
	 * Remove any errors set on fields with no input
	 * Also set global to indicate whether section is hidden
	 */
	public static function validate_no_input_fields( &$errors, $field ) {
		if ( ! in_array( $field->type, array( 'break', 'html', 'divider', 'end_divider', 'form' ) ) ) {
			return;
		}

		if ( $field->type == 'break' ) {
			global $frm_hidden_break;

			$frm_hidden_break = self::is_individual_field_conditionally_hidden( $field );

		} else if ( $field->type == 'divider' ) {
			global $frm_hidden_divider;

			$frm_hidden_divider = self::is_individual_field_conditionally_hidden( $field );

		} else if ( $field->type == 'form' ) {
			global $frm_hidden_form;

			if ( self::is_individual_field_conditionally_hidden( $field ) ) {
				$frm_hidden_form = $field->field_options['form_select'];
			} else {
				$frm_hidden_form = false;
			}

		} else if ( $field->type == 'end_divider' ) {
			global $frm_hidden_divider;

			$frm_hidden_divider = false;

		}

		if ( isset( $errors['field' . $field->temp_id ] ) ) {
			unset( $errors['field' . $field->temp_id ] );
		}
	}

    public static function validate_hidden_shortcode_field(&$errors, $field, &$value) {
        if ( ! isset($errors['field'. $field->temp_id]) ) {
            return;
        }

        //Don't require fields hidden with shortcode fields="25,26,27"
        global $frm_vars;
		if ( isset( $frm_vars['show_fields'] ) && ! empty( $frm_vars['show_fields'] ) && is_array( $frm_vars['show_fields'] ) && $field->required == '1' && ! in_array( $field->id, $frm_vars['show_fields'] ) && ! in_array( $field->field_key, $frm_vars['show_fields'] ) ) {
            unset($errors['field'. $field->temp_id]);
            $value = '';
        }
    }

	/**
	 * @since 2.0.6
	 */
	private static function is_field_hidden_by_shortcode( $field, $errors ) {
		global $frm_vars;
		return ( isset( $frm_vars['show_fields'] ) && ! empty( $frm_vars['show_fields'] ) && is_array( $frm_vars['show_fields'] ) && $field->required == '1' && isset( $errors['field' . $field->temp_id ] ) && ! in_array( $field->id, $frm_vars['show_fields'] ) && ! in_array( $field->field_key, $frm_vars['show_fields'] ) );
	}


	/**
	 * Clear a field's errors and value when it is conditionally hidden
	 *
	 * @since 2.03.08
	 *
	 * @param stdClass $field
	 * @param array $errors
	 * @param mixed $value
	 */
	private static function clear_errors_and_value_for_conditionally_hidden_field( $field, &$errors, &$value ) {
		// TODO: prevent additional validation when field is conditionally hidden

		if ( ! isset( $errors['field' . $field->temp_id ] ) && $value === '' ) {
			return;
		}

		if ( self::is_field_conditionally_hidden( $field ) ) {

			if ( self::is_field_on_skipped_page() && $field->type == 'user_id' ) {
				// Leave value alone
			} else {
				$value = '';
			}

			if ( isset( $errors['field' . $field->temp_id ] ) ) {
				unset( $errors['field' . $field->temp_id ] );
			}

		}
	}

	/**
	 * Check if a field is conditionally hidden during validation
	 *
	 * @since 2.03.08
	 *
	 * @param stdClass $field
	 *
	 * @return bool
	 */
	private static function is_field_conditionally_hidden( $field ) {
		return self::is_individual_field_conditionally_hidden( $field )
			   || self::is_field_in_hidden_section( $field )
			   || self::is_field_in_hidden_embedded_form( $field )
			   || self::is_field_on_skipped_page();
	}

	/**
	 * Check if an individual field has logic that is hiding it
	 *
	 * @since 2.03.08
	 *
	 * @param stdClass $field
	 *
	 * @return bool
	 */
	private static function is_individual_field_conditionally_hidden( $field ) {
		return FrmProFieldsHelper::is_field_hidden( $field, stripslashes_deep( $_POST ) );
	}

	/**
	 * Check if a field is within a conditionally hidden section
	 *
	 * @since 2.03.08
	 *
	 * @param stdClass $field
	 *
	 * @return bool
	 */
	private static function is_field_in_hidden_section( $field ) {
		global $frm_hidden_divider;

		return $frm_hidden_divider;

	}

	/**
	 * Check if an field is in a conditionally hidden embedded form
	 *
	 * @since 2.03.08
	 *
	 * @param stdClass $field
	 *
	 * @return bool
	 */
	private static function is_field_in_hidden_embedded_form( $field ) {
		global $frm_hidden_form;

		return $frm_hidden_form && $frm_hidden_form == $field->form_id;
	}

	/**
	 * Check if a field is on a page skipped with conditional logic
	 *
	 * @since 2.03.08
	 *
	 * @return bool
	 */
	private static function is_field_on_skipped_page() {
		global $frm_hidden_break;

		return $frm_hidden_break;
	}

    /**
     * Make sure the [auto_id] is still unique
     */
    public static function validate_auto_id($field, &$value) {
		if ( empty( $field->default_value ) || is_array( $field->default_value ) || empty( $value ) || strpos( $field->default_value, '[auto_id' ) === false ) {
            return;
        }

        //make sure we are not editing
        if ( ( $_POST && ! isset($_POST['id']) ) || ! is_numeric($_POST['id']) ) {
            $value = FrmProFieldsHelper::get_default_value($field->default_value, $field);
        }
    }

    /**
     * Make sure this value is unique
     */
    public static function validate_unique_field(&$errors, $field, $value) {
		if ( empty( $value ) || ! FrmField::is_option_true( $field, 'unique' ) ) {
            return;
        }
        
        $entry_id = self::get_validated_entry_id( $field );

        if ( $field->type == 'time' ) {
            if ( FrmProTimeField::is_datetime_used( $field, $value, $entry_id ) ) {
            	$errors['field' . $field->temp_id ] = FrmFieldsHelper::get_error_msg( $field, 'unique_msg' );
            }
        } else if ( $field->type == 'date' ) {
            $value = FrmProAppHelper::maybe_convert_to_db_date($value, 'Y-m-d');

            if ( FrmProEntryMetaHelper::value_exists($field->id, $value, $entry_id) ) {
                $errors['field'. $field->temp_id] = FrmFieldsHelper::get_error_msg($field, 'unique_msg');
            }
        } else if ( FrmProEntryMetaHelper::value_exists($field->id, $value, $entry_id) ) {
            $errors['field'. $field->temp_id] = FrmFieldsHelper::get_error_msg($field, 'unique_msg');
        }
    }

	public static function get_validated_entry_id( $field ) {
		$entry_id = ( $_POST && isset($_POST['id']) ) ? absint( $_POST['id'] ) : 0;

		// get the child entry id for embedded or repeated fields
		if ( isset( $field->temp_id ) ) {
			$temp_id_parts = explode( '-i', $field->temp_id );
			if ( isset( $temp_id_parts[1] ) ) {
				$entry_id = $temp_id_parts[1];
			}
		}

		return $entry_id;
	}

	public static function add_field_to_query( $value, &$query ) {
		if ( is_numeric( $value ) ) {
			$query['it.field_id'] = $value;
		} else {
			$query['fi.field_key'] = $value;
		}
	}

    public static function validate_confirmation_field(&$errors, $field, $value, $args) {
		//Make sure confirmation field matches original field
		if ( ! FrmField::is_option_true( $field, 'conf_field' ) ) {
            return;
        }

        if ( FrmProFormsHelper::saving_draft() ) {
            //Check confirmation field if saving a draft
            $args['action'] = ( $_POST['frm_action'] == 'create' ) ? 'create' : 'update';
            self::validate_check_confirmation_field($errors, $field, $value, $args);
            return;
        }

        $args['action'] = ( $_POST['frm_action'] == 'update' ) ? 'update' : 'create';
        
        self::validate_check_confirmation_field($errors, $field, $value, $args);
    }

    public static function validate_check_confirmation_field(&$errors, $field, $value, $args) {
        $conf_val = '';

		// Temporarily swtich $field->id in order to get and set the value posted in confirmation field
        $field_id = $field->id;
        $field->id = 'conf_'. $field_id;
        FrmEntriesHelper::get_posted_value($field, $conf_val, $args);

		// Switch $field->id back to original id
        $field->id = $field_id;
        unset($field_id);

        //If editing entry or if user hits Next/Submit on a draft
        if ( $args['action'] == 'update' ) {
            //If in repeating section
            if ( isset( $args['key_pointer'] ) && ( $args['key_pointer'] || $args['key_pointer'] === 0 ) ) {
                $entry_id = str_replace( 'i', '', $args['key_pointer'] );
            } else {
                $entry_id = ( $_POST && isset($_POST['id']) ) ? $_POST['id'] : false;
            }

            $prev_value = FrmEntryMeta::get_entry_meta_by_field($entry_id, $field->id);

            if ( $prev_value != $value && $conf_val != $value ) {
				$errors['fieldconf_' . $field->temp_id ] = FrmFieldsHelper::get_error_msg( $field, 'conf_msg' );
                $errors['field' . $field->temp_id] = '';
            }
        } else if ( $args['action'] == 'create' && $conf_val != $value ) {
            //If creating entry
			$errors['fieldconf_' . $field->temp_id ] = FrmFieldsHelper::get_error_msg( $field, 'conf_msg' );
            $errors['field' . $field->temp_id] = '';
        }
    }

    public static function validate_date_field(&$errors, $field, $value) {
        if ( $field->type != 'date' ) {
            return;
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
					$errors['field' . $field->temp_id ] = FrmFieldsHelper::get_error_msg( $field, 'invalid' );
				}
			}

            $value = $formated_date;
			unset( $formated_date );
		}

		$date = explode( '-', $value );

		if ( count( $date ) != 3 || ! checkdate( (int) $date[1], (int) $date[2], (int) $date[0] ) ) {
			$errors['field' . $field->temp_id ] = FrmFieldsHelper::get_error_msg( $field, 'invalid' );
		}
    }

	public static function skip_required_validation( $field ) {
		$going_backwards = FrmProFormsHelper::going_to_prev( $field->form_id );
		if ( $going_backwards ) {
			return true;
		}

		$saving_draft = FrmProFormsHelper::saving_draft();
		if ( $saving_draft ) {
			return true;
		}

		if ( self::is_field_conditionally_hidden( $field ) ) {
			return true;
		}

		return false;
	}

    /**
     * Get metas for post or non-post fields
     *
     * @since 2.0
     */
    public static function get_all_metas_for_field( $field, $args = array() ) {
        global $wpdb;

		$where = array(
			'e.form_id' => $field->form_id,
			'e.is_draft' => 0,
		);

		if ( ! FrmField::is_option_true( $field, 'post_field' ) ) {
			// If field is not a post field
			$get_field = 'em.meta_value';
			$get_table = $wpdb->prefix .'frm_item_metas em INNER JOIN '. $wpdb->prefix .'frm_items e ON (e.id=em.item_id)';
			$where['em.field_id'] = $field->id;

        } else if ( $field->field_options['post_field'] == 'post_custom' ) {
			// If field is a custom field
			$get_field = 'pm.meta_value';
			$get_table = $wpdb->postmeta . ' pm INNER JOIN ' . $wpdb->prefix . 'frm_items e ON pm.post_id=e.post_id';
			$where['pm.meta_key'] = $field->field_options['custom_field'];

		} else if ( $field->field_options['post_field'] != 'post_category' ) {
			// If field is a non-category post field
			$get_field = 'p.' . sanitize_title( $field->field_options['post_field'] );
			$get_table = $wpdb->posts . ' p INNER JOIN ' . $wpdb->prefix . 'frm_items e ON p.ID=e.post_id';

        } else {
			// If field is a category field
			$post_ids = self::get_all_post_ids_for_form( $field->form_id, $args );

			$get_field = 'terms.term_id';
			$get_table = $wpdb->terms . ' AS terms INNER JOIN ' . $wpdb->term_taxonomy . '  AS tt ON tt.term_id = terms.term_id INNER JOIN ' . $wpdb->term_relationships . ' AS tr ON tr.term_taxonomy_id = tt.term_taxonomy_id';
			$where = array( 'tt.taxonomy' => $field->field_options['taxonomy'], 'tr.object_id' => $post_ids );

			if ( $field->field_options['exclude_cat'] ) {
				$where['terms.term_id NOT'] = $field->field_options['exclude_cat'];
			}

			$args = array();
        }

		self::add_to_where_query( $args, $where );
		$query_args = self::setup_args_for_frmdb_query( $args );

        // Get the metas
		$metas = FrmDb::get_col( $get_table, $where, $get_field, $query_args );

        // Maybe unserialize
        foreach ( $metas as $k => $v ) {
            $metas[$k] = maybe_unserialize($v);
            unset($k, $v);
        }

        // Strip slashes
        $metas = stripslashes_deep( $metas );

        return $metas;
    }

	/**
	 * Get all post IDs for form
	 *
	 * @since 2.02.06
	 * @param int $form_id
	 * @param array $args
	 * @return mixed
	 */
    private static function get_all_post_ids_for_form( $form_id, $args ) {
		$where = array(
			'e.form_id' => $form_id,
			'e.is_draft' => 0,
		);

		self::add_to_where_query( $args, $where );

		$query_args = self::setup_args_for_frmdb_query( $args );

		global $wpdb;
		$table = $wpdb->prefix . 'frm_items e';

		return FrmDb::get_col( $table, $where, 'e.post_id', $query_args );
	}

	/**
	 * Get the associative array values for a single field
	 *
	 * @since 2.02.05
	 * @param object $field
	 * @param array $atts
	 * @return array
	 */
	public static function get_associative_array_values_for_field( $field, $atts ) {
		global $wpdb;

		$get_column = 'e.id,';

		$where = array(
			'e.form_id' => $field->form_id,
			'e.is_draft' => 0,
		);

		if ( ! FrmField::is_option_true( $field, 'post_field' ) ) {
			// If field is not a post field
			$get_column .= 'em.meta_value as meta_value';
			$get_table = $wpdb->prefix . 'frm_item_metas em INNER JOIN ' . $wpdb->prefix . 'frm_items e ON (e.id=em.item_id)';
			$where['em.field_id'] = $field->id;

		} else if ( $field->field_options['post_field'] === 'post_custom' ) {
			// If field is a custom field
			$get_column .= 'pm.meta_value as meta_value';
			$get_table = $wpdb->postmeta . ' pm INNER JOIN ' . $wpdb->prefix . 'frm_items e ON pm.post_id=e.post_id';
			$where['pm.meta_key'] = $field->field_options['custom_field'];

		} else if ( $field->field_options['post_field'] !== 'post_category' ) {
			// If field is a non-category post field
			$get_column .= 'p.' . sanitize_title( $field->field_options['post_field'] ) . ' as meta_value';
			$get_table = $wpdb->posts . ' p INNER JOIN ' . $wpdb->prefix . 'frm_items e ON p.ID=e.post_id';

		} else {
			// If field is a category field
			//TODO: Make this work
			return array();
		}

		// Add filtering attributes
		self::add_to_where_query( $atts, $where );

		return FrmDb::get_associative_array_results( $get_column, $get_table, $where );
	}

	/**
	 * Get the associative array values for a column in the frm_items table
	 *
	 * @since 2.02.05
	 * @param string $column
	 * @param array $atts
	 * @return array
	 */
	public static function get_associative_array_values_for_frm_items_column( $column, $atts ) {
		global $wpdb;

		$columns = 'e.id,e.' . $column . ' as meta_value';
		$table = $wpdb->prefix . 'frm_items e';
		$where = array(
			'e.form_id' => $atts['form_id'],
			'e.is_draft' => 0,
		);

		// Add filtering attributes
		self::add_to_where_query( $atts, $where );

		return FrmDb::get_associative_array_results( $columns, $table, $where );
	}

	/**
	 * Get all entry IDs for a specific field and value
	 *
	 * @since 2.01.0
	 * @param object $field
	 * @param string|array $value
	 * @param array $args
	 * @return array
	 */
	public static function get_entry_ids_for_field_and_value( $field, $value, $args = array() ) {
		global $wpdb;

		$where = array(
			'e.form_id' => $field->form_id,
			'e.is_draft' => 0
		);

		$operator = self::get_operator_for_query( $args );

		if ( ! FrmField::is_option_true( $field, 'post_field' ) ) {
			// If field is not a post field
			$get_field = 'em.item_id';
			$get_table = $wpdb->prefix .'frm_item_metas em INNER JOIN ' . $wpdb->prefix . 'frm_items e ON (e.id=em.item_id)';

			$where['em.field_id'] = $field->id;
			$where['em.meta_value' . $operator ] = $value;

		} else if ( $field->field_options['post_field'] == 'post_custom' ) {
			// If field is a custom field
			$get_field = 'e.id';
			$get_table = $wpdb->postmeta . ' pm INNER JOIN ' . $wpdb->prefix . 'frm_items e ON pm.post_id=e.post_id';

			$where['pm.meta_key'] = $field->field_options['custom_field'];
			$where['pm.meta_value' . $operator ] = $value;

		} else if ( $field->field_options['post_field'] != 'post_category' ) {
			// If field is a non-category post field
			$get_field = 'e.id';
			$get_table = $wpdb->posts . ' p INNER JOIN ' . $wpdb->prefix . 'frm_items e ON p.ID=e.post_id';

			$where['p.' . sanitize_title( $field->field_options['post_field'] )  . $operator ] = $value;

		} else {
			// If field is a category field
			//TODO: Make this work
			return array();
		}

		self::add_to_where_query( $args, $where );

		return FrmDb::get_col( $get_table, $where, $get_field );
	}

	/**
	 * Get the operator from the given comparison type
	 *
	 * @since 2.02.05
	 * @param $args
	 * @return string
	 */
	private static function get_operator_for_query( $args ) {
		$operator = '';
		if ( isset( $args['comparison_type'] ) ) {
			if ( 'like' === $args['comparison_type'] ) {
				$operator = ' LIKE';
			} elseif ( '>' === $args['comparison_type'] ){
				$operator = ' >-';
			} elseif ( '<' === $args['comparison_type'] ) {
				$operator = ' <-';
			} elseif ( '>=' === $args['comparison_type'] ) {
				$operator = '>';
			} elseif ( '<=' === $args['comparison_type'] ) {
				$operator = '<';
			}
		}

		return $operator;
	}

	/**
	 * Add entry_ids, user_id, start_date, end_date, and is_draft arguments to WHERE query
	 *
	 * @param $args
	 * @param $where
	 */
    private static function add_to_where_query( $args, &$where ) {

        // If entry IDs is set
        if ( isset( $args['entry_ids'] ) ) {
            $where['e.id'] = $args['entry_ids'];
        }

        // If user ID is set
        if ( isset( $args['user_id'] ) ) {
            $where['e.user_id'] = $args['user_id'];
        }

        // If start date is set
        if ( isset( $args['start_date'] ) ) {
            $where['e.created_at >'] = date( 'Y-m-d 00:00:00', strtotime( $args['start_date'] ) );
        }

        // If end date is set
        if ( isset( $args['end_date'] ) ) {
            $where['e.created_at <'] = date( 'Y-m-d 23:59:59', strtotime( $args['end_date'] ) );
        }

		// If is_draft is set
		if ( isset( $args['is_draft'] ) ) {
			if ( 'both' === $args['is_draft'] ) {
				unset( $where['e.is_draft'] );
			} else {
				$where['e.is_draft'] = $args['is_draft'];
			}
		}
    }

	/**
	 * Convert args to usable query args for FrmDb::get_col function
	 *
	 * @since 2.01.0
	 * @param array $args
	 * @return array
	 */
	private static function setup_args_for_frmdb_query( $args ) {
		$query_args = array();

		if ( isset( $args['limit'] ) ) {
			$query_args['limit'] = $args['limit'];
		}

		if ( isset( $args['order_by'] ) ) {
			$query_args['order_by'] = $args['order_by'];
		}

		return $query_args;
	}

    public static function set_post_fields($field, $value, &$errors) {
        $errors = FrmProEntryMetaHelper::set_post_fields($field, $value, $errors);
        return $errors;
    }

	public static function add_post_value_to_entry( $field, &$entry ) {
		if ( $entry->post_id  && ( $field->type == 'tag' || ( isset( $field->field_options['post_field'] ) && $field->field_options['post_field'] ) ) ) {
			$p_val = FrmProEntryMetaHelper::get_post_value(
				$entry->post_id,
				$field->field_options['post_field'],
				$field->field_options['custom_field'],
				array(
					'truncate' => ( $field->field_options['post_field'] == 'post_category' ),
					'form_id' => $entry->form_id,
					'field' => $field,
					'type' => $field->type,
					'exclude_cat' => ( isset( $field->field_options['exclude_cat'] ) ? $field->field_options['exclude_cat'] : 0 ),
				)
			);
			if ( $p_val != '' ) {
				$entry->metas[ $field->id ] = $p_val;
			}
		}
	}

	public static function add_repeating_value_to_entry( $field, &$entry ) {
		// If field is in a repeating section
		if ( $entry->form_id != $field->form_id ) {
			// get entry ids linked through repeat field or embeded form
			$child_entries = FrmProEntry::get_sub_entries( $entry->id, true );
			$val = FrmProEntryMetaHelper::get_sub_meta_values( $child_entries, $field );
			if ( ! empty( $val ) ) {
				//Flatten multi-dimensional arrays
				if ( is_array( $val ) ) {
					$val = FrmAppHelper::array_flatten( $val );
				}
				$entry->metas[ $field->id ] = $val;
			}
		} else {
			$val = '';
			FrmProEntriesHelper::get_dynamic_list_values( $field, $entry, $val );
			$entry->metas[ $field->id ] = $val;
		}
	}

	public static function validate_number_field( &$errors, $field, $value ) {
		_deprecated_function( __FUNCTION__, '2.0.18', array( 'FrmEntryValidate::validate_number_field') );
		FrmEntryValidate::validate_number_field( $errors, $field, $value );
	}

	public static function validate_phone_field( &$errors, $field, $value ) {
		_deprecated_function( __FUNCTION__, '2.0.18', array( 'FrmEntryValidate::validate_phone_field') );
		FrmEntryValidate::validate_phone_field( $errors, $field, $value );
	}

    public static function validate_file_upload( &$errors, $field, $args ) {
        if ( $field->type != 'file' ) {
            return;
        }

		_deprecated_function( __FUNCTION__, '2.02', 'FrmProFileField::validate_file_upload' );
		FrmProFileField::validate_file_upload( $errors, $field, $args );
    }

	/**
	 * @since 2.0.22
	 */
	public static function delete_files_with_entry( $entry_id, $entry = false ) {
		_deprecated_function( __FUNCTION__, '2.02', 'FrmProFileField::delete_files_with_entry' );
		FrmProFileField::delete_files_with_entry( $entry_id, $entry );
	}

	/**
	 * @since 2.0.22
	 */
	public static function delete_files_from_field( $field, $entry ) {
		_deprecated_function( __FUNCTION__, '2.02', 'FrmProFileField::delete_files_from_field' );
		FrmProFileField::delete_files_from_field( $field, $entry );
	}

    /**
    * Get name of uploaded file
    *
    * @since 2.0
    *
    */
    public static function get_file_name( $field_id, &$file_name, &$parent_field, &$key_pointer, &$repeating ) {
        _deprecated_function( __FUNCTION__, '2.02' );
    }

	public static function get_disallowed_times( $values, &$remove ) {
		_deprecated_function( __FUNCTION__, '2.03.02', 'FrmProTimeField::get_disallowed_times' );
		FrmProTimeField::get_disallowed_times( $values, $remove );
	}

	/**
	 * @deprecated 2.03.08
	 */
	public static function validate_conditional_field( &$errors, $field, &$value ) {
		_deprecated_function( __FUNCTION__, '2.03.08', 'custom code' );
		self::clear_errors_and_value_for_conditionally_hidden_field( $field, $errors, $value );
	}

	/**
	 * @deprecated 2.03.08
	 */
	public static function validate_child_conditional_field( &$errors, $field, &$value ) {
		_deprecated_function( __FUNCTION__, '2.03.08', 'custom code' );
		self::clear_errors_and_value_for_conditionally_hidden_field( $field, $errors, $value );
	}
}
