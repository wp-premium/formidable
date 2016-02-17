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
     * Upload files and add new tags
     *
     * @since 2.0
     * @param array|string $meta_value (the posted value)
     * @param int $field_id
     * @param int $entry_id
     * @return array|string $meta_value
     *
     */
	public static function prepare_data_before_db( $meta_value, $field_id, $entry_id ) {
		// If confirmation field or 0 index, exit now
		if ( ! is_numeric( $field_id ) || $field_id === 0 ) {
			return $meta_value;
		}

		$field = FrmField::getOne($field_id);

		if ( $field->type == 'file' ) {
			// Upload files and get new meta value for file upload fields
			$meta_value = self::prepare_file_upload_meta( $meta_value, $field, $entry_id );

		} else if ( $field->type == 'tag' ) {
			// Create new tags
			self::create_new_tags( $field, $entry_id, $meta_value );
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
        } else if ( $field->type == 'user_id' ) {
            // make sure we have a user ID
            if ( ! is_numeric($value) ) {
                $value = FrmAppHelper::get_user_id_param($value);
                FrmEntriesHelper::set_posted_value($field, $value, $args);
            }

            //add user id to post variables to be saved with entry
            $_POST['frm_user_id'] = $value;
        } else if ( $field->type == 'time' && is_array($value) ) {
            $value = $value['H'] .':'. $value['m'] . ( isset($value['A']) ? ' '. $value['A'] : '' );
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

        self::validate_file_upload($errors, $field, $args);

        // if saving draft, only check file type since it won't be checked later
        // and confirmation field since the confirmation field value is not saved
        if ( FrmProFormsHelper::saving_draft() ) {

            //Check confirmation field if saving a draft
    		self::validate_confirmation_field($errors, $field, $value, $args);

            return $errors;
        }

        self::validate_no_input_fields($errors, $field);

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

        //Don't require a conditionally hidden field
        self::validate_conditional_field($errors, $field, $value);

        //Don't require a field hidden in a conditional page or section heading
        self::validate_child_conditional_field($errors, $field, $value);

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
				foreach ( $_POST['item_meta'][ $field->id ] as $k => $values ) {
					if ( ! empty( $k ) && in_array( $k, array( 'form', 'id' ) ) ) {
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

    public static function validate_file_upload(&$errors, $field, $args) {
        //if the field is a file upload, check for a file
        if ( $field->type != 'file' ) {
            return;
        }

        $file_name = 'file'. $field->id;

        if ( isset( $args['key_pointer'] ) && ( $args['key_pointer'] || $args['key_pointer'] === 0 ) ) {
            $file_name .= '-' . $args['key_pointer'];
        }

        if ( ! isset($_FILES[$file_name]) ) {
            return;
        }

        $file_uploads = $_FILES[$file_name];

        //if the field is a file upload, check for a file
        if ( empty($file_uploads['name']) ) {
            return;
        }

        $filled = true;
        if ( is_array($file_uploads['name']) ) {
            $filled = false;
            foreach ( $file_uploads['name'] as $n ) {
                if ( !empty($n) ) {
                    $filled = true;
                }
            }
        }

        if ( ! $filled ) {
            // no file was uploaded
            return;
        }

        // If blank errors are set, remove them since a file was uploaded in this field
        if ( isset($errors['field'. $field->temp_id]) ) {
            unset($errors['field'. $field->temp_id]);
        }

		if ( FrmField::is_option_true( $field, 'restrict' ) && ! FrmField::is_option_empty( $field, 'ftypes' ) ) {
            $mimes = $field->field_options['ftypes'];
        } else {
            $mimes = null;
        }

        if ( is_array($file_uploads['name']) ) {
            foreach ( $file_uploads['name'] as $name ) {

                // check allowed file size
                if ( ! empty($file_uploads['error']) && in_array(1, $file_uploads['error']) ) {
                    $errors['field'. $field->temp_id] = __( 'This file is too big', 'formidable' );
                }

                if ( empty($name) ) {
                    continue;
                }

                //check allowed mime types for this field
                $file_type = wp_check_filetype( $name, $mimes );
                unset($name);

                if ( ! $file_type['ext'] ) {
                    break;
                }
            }
        } else {
            // check allowed file size
            if ( ! empty($file_uploads['error']) && in_array(1, $file_uploads['error']) ) {
                $errors['field'. $field->temp_id] = __( 'This file is too big', 'formidable' );
            }

            $file_type = wp_check_filetype( $file_uploads['name'], $mimes );
        }

        if ( isset($file_type) && ! $file_type['ext'] ) {
            $errors['field'. $field->temp_id] = ($field->field_options['invalid'] == __( 'This field is invalid', 'formidable' ) || $field->field_options['invalid'] == '' || $field->field_options['invalid'] == $field->name.' '. __( 'is invalid', 'formidable' )) ? __( 'Sorry, this file type is not permitted for security reasons.', 'formidable' ) : $field->field_options['invalid'];
        }
    }

    /**
     * Remove any errors set on fields with no input
     * Also set global to indicate whether section is hidden
     */
    public static function validate_no_input_fields(&$errors, $field) {
		if ( ! in_array($field->type, array( 'break', 'html', 'divider', 'end_divider', 'form' ) ) ) {
            return;
        }

        $hidden = FrmProFieldsHelper::is_field_hidden($field, stripslashes_deep($_POST));
        if ( $field->type == 'break' ) {
            global $frm_hidden_break;
            $frm_hidden_break = array( 'field_order' => $field->field_order, 'hidden' => $hidden);
        } else if ( $field->type == 'divider' ) {
            global $frm_hidden_divider;
            $frm_hidden_divider = array( 'field_order' => $field->field_order, 'hidden' => $hidden);
		} else if ( $field->type == 'end_divider' ) {
			global $frm_hidden_divider;
			$frm_hidden_divider = false;
		} else if ( $field->type == 'form' && $hidden ) {
			global $frm_hidden_form;
			$frm_hidden_form = $field->field_options['form_select'];
		}

        if ( isset($errors['field'. $field->temp_id]) ) {
            unset($errors['field'. $field->temp_id]);
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
		return ( isset( $frm_vars['show_fields'] ) && ! empty( $frm_vars['show_fields'] ) && is_array( $frm_vars['show_fields'] ) && $field->required == '1' && isset( $errors[ 'field' . $field->temp_id ] ) && ! in_array( $field->id, $frm_vars['show_fields'] ) && ! in_array( $field->field_key, $frm_vars['show_fields'] ) );
	}
    /**
     * Don't require a conditionally hidden field
     */
    public static function validate_conditional_field(&$errors, $field, &$value) {
		if ( FrmField::is_option_empty( $field, 'hide_field' ) ) {
            return;
        }

        if ( FrmProFieldsHelper::is_field_hidden($field, stripslashes_deep($_POST)) ) {
            if ( isset($errors['field'. $field->temp_id]) ) {
                unset($errors['field'. $field->temp_id]);
            }
            $value = '';
        }
    }

    /**
     * Don't require a field hidden in a conditional page or section heading
     */
    public static function validate_child_conditional_field(&$errors, $field, &$value) {
        if ( ! isset($errors['field'. $field->temp_id]) && $value == '' ) {
            return;
        }

		global $frm_hidden_break, $frm_hidden_divider, $frm_hidden_form;
		$in_hidden_form = $frm_hidden_form && $frm_hidden_form == $field->form_id;
		$in_hidden_page = $frm_hidden_break && $frm_hidden_break['hidden'];
		$in_hidden_section = $frm_hidden_divider && $frm_hidden_divider['hidden'] && ( ! $frm_hidden_break || $frm_hidden_break['field_order'] < $frm_hidden_divider['field_order'] );

		if ( $in_hidden_page || $in_hidden_form || $in_hidden_section ) {
            if ( isset($errors['field'. $field->temp_id]) ) {
                unset($errors['field'. $field->temp_id]);
            }

			if ( $field->type != 'user_id' ) {
				$value = '';
			}
        }
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
        
        $entry_id = ( $_POST && isset($_POST['id']) ) ? $_POST['id'] : false;

        // get the child entry id for embedded or repeated fields
        if ( isset($field->temp_id) ) {
            $temp_id_parts = explode('-i', $field->temp_id);
            if ( isset($temp_id_parts[1]) ) {
                $entry_id = $temp_id_parts[1];
            }
        }

        if ( $field->type == 'time' ) {
            //TODO: add server-side validation for unique date-time
        } else if ( $field->type == 'date' ) {
            $value = FrmProAppHelper::maybe_convert_to_db_date($value, 'Y-m-d');

            if ( FrmProEntryMetaHelper::value_exists($field->id, $value, $entry_id) ) {
                $errors['field'. $field->temp_id] = FrmFieldsHelper::get_error_msg($field, 'unique_msg');
            }
        } else if ( FrmProEntryMetaHelper::value_exists($field->id, $value, $entry_id) ) {
            $errors['field'. $field->temp_id] = FrmFieldsHelper::get_error_msg($field, 'unique_msg');
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
                $errors['conf_field'. $field->temp_id] = isset($field->field_options['conf_msg']) ? $field->field_options['conf_msg'] : __( 'The entered values do not match', 'formidable' );
                $errors['field' . $field->temp_id] = '';
            }
        } else if ( $args['action'] == 'create' && $conf_val != $value ) {
            //If creating entry
            $errors['conf_field'. $field->temp_id] = isset($field->field_options['conf_msg']) ? $field->field_options['conf_msg'] : __( 'The entered values do not match', 'formidable' );
            $errors['field' . $field->temp_id] = '';
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

    public static function validate_date_field(&$errors, $field, $value) {
        if ( $field->type != 'date' ) {
            return;
        }

        if ( ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ) {
            $frmpro_settings = new FrmProSettings();
            $formated_date = FrmProAppHelper::convert_date($value, $frmpro_settings->date_format, 'Y-m-d');

            //check format before converting
            if ( $value != date($frmpro_settings->date_format, strtotime($formated_date)) ) {
                $errors['field'. $field->temp_id] = FrmFieldsHelper::get_error_msg($field, 'invalid');
            }

            $value = $formated_date;
            unset($formated_date);
        }
        $date = explode('-', $value);

        if ( count($date) != 3 || ! checkdate( (int) $date[1], (int) $date[2], (int) $date[0]) ) {
            $errors['field'. $field->temp_id] = FrmFieldsHelper::get_error_msg($field, 'invalid');
        }
    }

	/**
	* Get media ID(s) to be saved to database and set global media ID values
	*
	* @since 2.0
	* @param array|string $prev_value (posted value)
	* @param object $field
	* @param integer $entry_id
	* @return array|string $meta_value
	*/
	private static function prepare_file_upload_meta( $prev_value, $field, $entry_id ) {
		$last_saved_value = self::get_previous_file_ids( $field, $entry_id );

        // If there are no files to be uploaded, exit now
        if ( ! isset( $_FILES ) ) {
			self::delete_removed_files( $last_saved_value, $prev_value, $field );
			return $prev_value;
        }

        // Assume this field is not repeating
        $repeating = $key_pointer = $parent_field = $file_name = false;

        // Get file name
        self::get_file_name( $field->id, $file_name, $parent_field, $key_pointer, $repeating );

        // If there isn't a file uploaded in this field, exit now
        if ( ! isset( $_FILES[$file_name]) || empty($_FILES[$file_name]['name']) || (int) $_FILES[$file_name]['size'] == 0 ) {
			self::delete_removed_files( $last_saved_value, $prev_value, $field );
			return $prev_value;
        }

		$media_ids = FrmProAppHelper::upload_file( $file_name );

		$mids = self::get_final_media_ids( $media_ids );

		$new_value = self::set_new_file_upload_meta_value( $field, $mids, $prev_value );

		// If no media IDs to upload, end now
		if ( ! empty( $mids ) ) {
			self::update_global_frm_vars_for_uploaded_files( $field->id, $repeating, $mids );

	        // Set new posted values (not sure that this is necessary)
	        self::set_file_posted_vals( $field->id, $new_value, array( 'repeating' => $repeating, 'parent_field' => $parent_field, 'key_pointer' => $key_pointer ) );

	        // If this is a post field
			if ( isset( $_POST['frm_wp_post'] ) && FrmField::is_option_true( $field, 'post_field' ) ) {
	            $_POST['frm_wp_post_custom'][$field->id .'='. $field->field_options['custom_field']] = $mids;
	        }
        }

		self::delete_removed_files( $last_saved_value, $new_value, $field );
		return $new_value;
    }

	/**
	 * Automatically delete files when an entry is deleted.
	 * If the "Delete all entries" button is used, entries will not be deleted
	 * @since 2.0.22
	 */
	public static function delete_files_with_entry( $entry_id, $entry = false ) {
		if ( empty( $entry ) ) {
			return;
		}

		$upload_fields = FrmField::getAll( array( 'fi.type' => 'file', 'fi.form_id' => $entry->form_id ) );
		foreach ( $upload_fields as $field ) {
			self::delete_files_from_field( $field, $entry );
			unset( $field );
		}
	}

	/**
	 * @since 2.0.22
	 */
	public static function delete_files_from_field( $field, $entry ) {
		if ( self::should_delete_files( $field ) ) {
			$media_ids = self::get_previous_file_ids( $field, $entry );
			self::delete_files_now( $media_ids );
		}
	}

	private static function should_delete_files( $field ) {
		$auto_delete = FrmField::get_option_in_object( $field, 'delete' );
		return empty( $auto_delete ) ? false : true;
	}

	/**
	 * @since 2.0.22
	 */
	private static function get_previous_file_ids( $field, $entry_id ) {
		return FrmProEntryMetaHelper::get_post_or_meta_value( $entry_id, $field );
	}

	private static function delete_removed_files( $old_value, $new_value, $field ) {
		if ( self::should_delete_files( $field ) ) {
			$media_ids = self::get_removed_file_ids( $old_value, $new_value );
			self::delete_files_now( $media_ids );
		}
	}

	/**
	 * @since 2.0.22
	 */
	private static function get_removed_file_ids( $old_value, $new_value ) {
		$media_ids = array_diff( (array) $old_value, (array) $new_value );
		return $media_ids;
	}

	/**
	 * @since 2.0.22
	 */
	private static function delete_files_now( $media_ids ) {
		if ( empty( $media_ids ) ) {
			return;
		}

		$media_ids = maybe_unserialize( $media_ids );
		foreach ( (array) $media_ids as $m ) {
			if ( is_numeric( $m ) ) {
				wp_delete_attachment( $m, true );
			}
		}
	}

    /**
    *
    * @since 2.0
    * @param int $field_id
    * @param $new_value to set
    * @param array $args array with repeating, key_pointer, and parent_field
    */
    private static function set_file_posted_vals( $field_id, $new_value, $args = array() ) {
        // If in repeating section
        if ( $args['repeating'] ) {
            $_POST['item_meta'][$args['parent_field']][$args['key_pointer']][$field_id] = $new_value;

        // If not in repeating section
        } else {
            $_POST['item_meta'][$field_id] = $new_value;
        }
    }

	/**
	* Get the final media IDs
	*
	* @since 2.0
	* @param array|string $media_ids
	* @return array $mids
	*/
	private static function get_final_media_ids( $media_ids ) {
        $mids = array();
        foreach ( (array) $media_ids as $media_id ) {
            if ( is_numeric($media_id) ) {
               $mids[] = $media_id;
            } else {
                foreach ( $media_id->errors as $error ) {
                    if ( ! is_array($error[0]) ) {
                        echo $error[0];
                    }
                    unset($error);
                }
            }
            unset($media_id);
        }
		$mids = array_filter( $mids );

		return $mids;
	}


	/**
	* Get the final value for a file upload field
	*
	* @since 2.0.19
	*
	* @param object $field
	* @param array $new_mids
	* @param array|string $prev_value
	* @return array|string $new_value
	*/
	private static function set_new_file_upload_meta_value( $field, $new_mids, $prev_value ) {
		// If no media IDs to upload, end now
		if ( empty( $new_mids ) ) {
			$new_value = $prev_value;
		} else {

			if ( FrmField::is_option_true( $field, 'multiple' ) ) {
				// Multi-file upload fields

				if ( ! empty( $prev_value ) ) {
					$new_value = array_merge( (array) $prev_value, $new_mids );
				} else {
					$new_value = $new_mids;
				}

			} else {
				// Single file upload fields
				$new_value = reset( $new_mids );
			}
		}

		return $new_value;
	}

	/**
	* Update the global $frm_vars for files that were just uploaded
	*
	* @since 2.0.19
	*
	* @param int $field_id
	* @param boolean $repeating
	* @param array $mids
	*/
	private static function update_global_frm_vars_for_uploaded_files( $field_id, $repeating, $mids ) {
		global $frm_vars;

		// Set up progress bar to display on form submission
		if ( ! isset($frm_vars['loading']) || ! $frm_vars['loading'] ) {
			$frm_vars['loading'] = true;
		}

		// Set up global media_id vars. This will be used for post fields.
		if ( ! isset( $frm_vars['media_id'] ) ) {
			$frm_vars['media_id'] = array();
		}

		// If not inside of a repeating section, set the media IDs for this field
		if ( ! $repeating ) {
			// What is this for?
			$frm_vars['media_id'][ $field_id ] = $mids;
		}
	}

    /**
    * Get name of uploaded file
    *
    * @since 2.0
    * @param integer $field_id
    * @param string $file_name pass by reference
    * @param int $parent_field. Retrieves ID of repeating section.
    * @param $key_pointer. Gets pointer if in repeating section.
    * @param boolean $repeating Tells whether field is inside of repeating section.
    *
    */
    public static function get_file_name( $field_id, &$file_name, &$parent_field, &$key_pointer, &$repeating ) {
        $file_name = 'file'. $field_id;

        // Check if there are repeating sections in the form, and adjust the filename accordingly
        if ( isset( $_POST['item_meta']['key_pointer'] ) && isset( $_POST['item_meta']['parent_field'] ) ) {
            // Get the current pointer
			$key_pointer = sanitize_title( $_POST['item_meta']['key_pointer'] );

            // Get the current parent
			$parent_field = absint( $_POST['item_meta']['parent_field'] );

            // Check if the current field is inside of the parent/pointer
            if ( isset( $_POST['item_meta'][$parent_field][$key_pointer][$field_id] ) ) {
                $file_name .= '-'. $key_pointer;
                $repeating = true;
            }
        }
    }

    /**
     * Get metas for post or non-post fields
     *
     * @since 2.0
     */
    public static function get_all_metas_for_field( $field, $args = array() ) {
        global $wpdb;

		$query = array();

		if ( ! FrmField::is_option_true( $field, 'post_field' ) ) {
			// If field is not a post field
			$get_field = 'em.meta_value';
			$get_table = $wpdb->prefix .'frm_item_metas em INNER JOIN '. $wpdb->prefix .'frm_items e ON (e.id=em.item_id)';

			$query['em.field_id'] = $field->id;
			$query['e.is_draft'] = 0;
        } else if ( $field->field_options['post_field'] == 'post_custom' ) {
			// If field is a custom field
			$get_field = 'pm.meta_value';
			$get_table = $wpdb->postmeta . ' pm INNER JOIN ' . $wpdb->prefix . 'frm_items e ON pm.post_id=e.post_id';

			$query['pm.meta_key'] = $field->field_options['custom_field'];

            // Make sure to only get post metas that are linked to this form
			$query['e.form_id'] = $field->form_id;
		} else if ( $field->field_options['post_field'] != 'post_category' ) {
			// If field is a non-category post field
			$get_field = 'p.' . sanitize_title( $field->field_options['post_field'] );
			$get_table = $wpdb->posts . ' p INNER JOIN ' . $wpdb->prefix . 'frm_items e ON p.ID=e.post_id';

            // Make sure to only get post metas that are linked to this form
			$query['e.form_id'] = $field->form_id;
        } else {
			// If field is a category field
            //TODO: Make this work
            return array();
            //$field_options = FrmProFieldsHelper::get_category_options( $field );
        }

        // Add queries for additional args
        self::add_meta_query( $query, $args );

        // Get the metas
		$metas = FrmDb::get_col( $get_table, $query, $get_field );

        // Maybe unserialize
        foreach ( $metas as $k => $v ) {
            $metas[$k] = maybe_unserialize($v);
            unset($k, $v);
        }

        // Strip slashes
        $metas = stripslashes_deep( $metas );

        return $metas;
    }

    public static function add_meta_query( &$query, $args ) {

        // If entry IDs is set
        if ( isset( $args['entry_ids'] ) ) {
            $query['e.id'] = $args['entry_ids'];
        }

        // If user ID is set
        if ( isset( $args['user_id'] ) ) {
            $query['e.user_id'] = $args['user_id'];
        }

        // If start date is set
        if ( isset( $args['start_date'] ) ) {
            $query['e.created_at >'] = date( 'Y-m-d', strtotime( $args['start_date'] ) );
        }

        // If end date is set
        if ( isset( $args['end_date'] ) ) {
            $query['e.created_at <'] = date( 'Y-m-d 23:59:59', strtotime( $args['end_date'] ) );
        }
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
}
