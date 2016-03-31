<?php

class FrmProXMLHelper{

    public static function import_xml_entries($entries, $imported) {
        global $frm_duplicate_ids;

        $saved_entries = array();
		$track_child_ids = array();

		// Import all child entries first
		self::put_child_entries_first( $entries );

	    foreach ( $entries as $item ) {
	        $entry = array(
	            'id'            => (int) $item->id,
		        'item_key'      => (string) $item->item_key,
		        'name'          => (string) $item->name,
		        'description'   => FrmAppHelper::maybe_json_decode((string) $item->description),
		        'ip'            => (string) $item->ip,
		        'form_id'       => ( isset($imported['forms'][ (int) $item->form_id] ) ? $imported['forms'][ (int) $item->form_id] : (int) $item->form_id),
		        'post_id'       => ( isset($imported['posts'][ (int) $item->post_id] ) ? $imported['posts'][ (int) $item->post_id] : (int) $item->post_id),
		        'user_id'       => FrmAppHelper::get_user_id_param( (string) $item->user_id ),
		        'parent_item_id' => (int) $item->parent_item_id,
		        'is_draft'      => (int) $item->is_draft,
		        'updated_by'    => FrmAppHelper::get_user_id_param( (string) $item->updated_by ),
		        'created_at'    => (string) $item->created_at,
		        'updated_at'    => (string) $item->updated_at,
	        );

	        $metas = array();
    		foreach ( $item->item_meta as $meta ) {
    		    $field_id = (int) $meta->field_id;
    		    if ( is_array($frm_duplicate_ids) && isset($frm_duplicate_ids[$field_id] ) ) {
    		        $field_id = $frm_duplicate_ids[$field_id];
    		    }
    		    $field = FrmField::getOne($field_id);

    		    if ( ! $field ) {
    		        continue;
    		    }

                $metas[$field_id] = FrmAppHelper::maybe_json_decode((string) $meta->meta_value);

                $metas[$field_id] = apply_filters('frm_import_val', $metas[$field_id], $field);

                self::convert_field_values($field, $field_id, $metas, $saved_entries);
                if ( $field->type == 'user_id' && $metas[$field_id] && is_numeric($metas[$field_id]) ) {
                    $entry['frm_user_id'] = $metas[$field_id];
                }

                unset($field, $meta);
    		}

    		unset($item);

            $entry['item_meta'] = $metas;
            unset($metas);

            // edit entry if the key and created time match
            $editing = FrmDb::get_var( 'frm_items', array( 'item_key' => $entry['item_key'], 'created_at' => date('Y-m-d H:i:s', strtotime($entry['created_at'])) ) );

            if ( $editing ) {
				FrmEntry::update_entry_from_xml( $entry['id'], $entry );
                $imported['updated']['items']++;
                $saved_entries[$entry['id']] = $entry['id'];
            } else if ( $e = FrmEntry::create_entry_from_xml($entry) ) {
                $saved_entries[$entry['id']] = $e;
                $imported['imported']['items']++;
            }

			self::track_imported_child_entries( $saved_entries[ $entry['id'] ], $entry['parent_item_id'], $track_child_ids );

		    unset($entry);
	    }

		self::update_parent_item_ids( $track_child_ids, $saved_entries );

	    unset($entries);

	    return $imported;
    }

	private static function put_child_entries_first( &$entries ) {
		$child_entries = array();
		$regular_entries = array();

		foreach ( $entries as $item ) {
			$parent_item_id = (int) $item->parent_item_id;

			if ( $parent_item_id ) {
				$child_entries[] = $item;
			} else {
				$regular_entries[] = $item;
			}
		}

		$entries = array_merge( $child_entries, $regular_entries );
	}

	/**
	*
	* Track imported entries if they have a parent_item_id
	* Use the old parent_item_id as the array key and set the array value to an array of child IDs
	*
	* @param int|boolean $child_id
	* @param int $parent_id
	* @param array $track_child_ids - pass by reference
	*/
	private static function track_imported_child_entries( $child_id, $parent_id, &$track_child_ids ) {
		if ( ! $parent_id ) {
			return;
		}

		if ( ! isset( $track_child_ids[ $parent_id ] ) ) {
			$track_child_ids[ $parent_id ] = array();
		}

		$track_child_ids[ $parent_id ][] = $child_id;
	}

	/**
	*
	* Update imported child entries so their parent_item_ids match any imported parent entries
	*
	* @since 2.0.12
	*
	* @param array $track_child_ids
	* @param array $saved_entries
	*/
	private static function update_parent_item_ids( $track_child_ids, $saved_entries ) {
		global $wpdb;

		foreach ( $track_child_ids as $old_parent_id => $new_child_ids ) {
			if ( isset( $saved_entries[ $old_parent_id ] ) ) {
				$new_parent_id = $saved_entries[ $old_parent_id ];

				$new_child_ids = '(' . implode( ',', $new_child_ids ) . ')';

				// This parent entry was imported and the parent_item_id column needs to be updated on all children
				$wpdb->query( $wpdb->prepare( 'UPDATE ' . $wpdb->prefix . 'frm_items SET parent_item_id = %d WHERE id IN 
				' . $new_child_ids, $new_parent_id ) );
			}
		}
	}

	public static function import_csv( $path, $form_id, $field_ids, $entry_key = 0, $start_row = 2, $del = ',', $max = 250 ) {
        if ( ! defined('WP_IMPORTING') ) {
            define('WP_IMPORTING', true);
        }

        $form_id = (int) $form_id;
        if ( ! $form_id ) {
            return $start_row;
        }

		if ( ! ini_get( 'safe_mode' ) ) {
            set_time_limit(0); //Remove time limit to execute this function
        }

        if ( $f = fopen($path, 'r') ) {
            unset($path);
            $row = 0;
            //setlocale(LC_ALL, get_locale());

            while ( ( $data = fgetcsv($f, 100000, $del) ) !== false ) {
                $row++;
                if ( $start_row > $row ) {
                    continue;
                }

                $values = array(
                    'form_id' => $form_id,
                    'item_meta' => array(),
                );

                foreach ( $field_ids as $key => $field_id ) {
                    self::csv_to_entry_value($key, $field_id, $data, $values);
                    unset($key, $field_id);
                }

                self::convert_db_cols( $values );
                self::convert_timestamps($values);
                self::save_or_edit_entry($values);

                unset($_POST, $values);

                if ( ($row - $start_row) >= $max ) {
                    fclose($f);
                    return $row;
                }
            }

            fclose($f);
            return $row;
        }
    }

    private static function csv_to_entry_value($key, $field_id, $data, &$values) {
        $data[$key] = isset($data[$key]) ? $data[$key] : '';

        if ( is_numeric($field_id) ) {
            self::set_values_for_fields($key, $field_id, $data, $values);
        } else if ( is_array($field_id) ) {
            self::set_values_for_data_fields($key, $field_id, $data, $values);
        } else {
            $values[$field_id] = $data[$key];
        }
    }

    /**
     * Called by self::csv_to_entry_value
     */
    private static function set_values_for_fields($key, $field_id, $data, &$values) {
        global $importing_fields;

        if ( ! $importing_fields ) {
            $importing_fields = array();
        }

        if ( isset($importing_fields[$field_id]) ) {
            $field = $importing_fields[$field_id];
        } else {
            $field = FrmField::getOne($field_id);
            $importing_fields[$field_id] = $field;
        }

        $values['item_meta'][$field_id] = apply_filters('frm_import_val', $data[$key], $field);

        self::convert_field_values($field, $field_id, $values['item_meta']);
        if ( $field->type == 'user_id' ) {
            $_POST['frm_user_id'] = $values['frm_user_id'] = $values['item_meta'][$field_id];
        }

        if ( isset($_POST['item_meta'][$field_id]) && ( $field->type == 'checkbox' || ( $field->type == 'data' && $field->field_options['data_type'] != 'checkbox') ) ) {
            if ( empty($values['item_meta'][$field_id]) ) {
                $values['item_meta'][$field_id] = $_POST['item_meta'][$field_id];
            } else if ( ! empty($_POST['item_meta'][$field_id]) ) {
                $values['item_meta'][$field_id] = array_merge( (array) $_POST['item_meta'][$field_id], (array) $values['item_meta'][$field_id] );
            }
        }

        $_POST['item_meta'][$field_id] = $values['item_meta'][$field_id];
    }

    /**
     * Called by self::csv_to_entry_value
     */
    private static function set_values_for_data_fields($key, $field_id, $data, &$values) {
        $field_type = isset($field_id['type']) ? $field_id['type'] : false;

        if ( $field_type != 'data' ) {
            return;
        }

        $linked = isset($field_id['linked']) ? $field_id['linked'] : false;
        $field_id = $field_id['field_id'];

        if ( $linked ) {
            $entry_id = FrmDb::get_var( 'frm_item_metas', array( 'meta_value' => $data[$key], 'field_id' => $linked), 'item_id' );
        } else {
            //get entry id of entry with item_key == $data[$key]
            $entry_id = FrmDb::get_var( 'frm_items', array( 'item_key' => $data[$key]) );
        }

        if ( $entry_id ) {
            $values['item_meta'][$field_id] = $entry_id;
        }
    }

    private static function convert_field_values( $field, $field_id, &$metas, $saved_entries = array() ) {
	    switch ( $field->type ) {
            case 'user_id':
                $metas[$field_id] = FrmAppHelper::get_user_id_param( trim($metas[$field_id]) );
            break;
            case 'file':
                $metas[$field_id] = self::get_file_id($metas[$field_id]);
                // If single file upload field, reset array
				if ( ! FrmField::is_option_true( $field, 'multiple' ) ) {
                    $metas[$field_id] = reset( $metas[$field_id] );
                }
            break;
            case 'date':
                $metas[$field_id] = self::get_date($metas[$field_id]);
            break;
            case 'data':
                $metas[$field_id] = self::get_dfe_id($metas[$field_id], $field, $saved_entries);
            break;
            case 'select':
            case 'checkbox':
                $metas[$field_id] = self::get_multi_opts($metas[$field_id], $field);
            break;
			case 'divider':
			case 'form':
				$metas[ $field_id ] = self::get_new_child_ids( $metas[ $field_id ], $field, $saved_entries );
			break;
	    }
    }

    /**
     * Convert timestamps to the database format
     */
    private static function convert_timestamps( &$values ) {
        $offset = get_option('gmt_offset') * 60 * 60;

        $frmpro_settings = new FrmProSettings();
        foreach ( array( 'created_at', 'updated_at') as $stamp ) {
            if ( ! isset($values[$stamp]) ) {
                continue;
            }

            // adjust the date format if it starts with the day
            if ( ! preg_match('/^\d{4}-\d{2}-\d{2}/', trim($values[$stamp])) && substr($frmpro_settings->date_format, 0, 1) == 'd' ) {
                $reg_ex = str_replace(
                    array( '/', '.', '-', 'd', 'j', 'm', 'y', 'Y'),
                    array( '\/', '\.', '\-', '\d{2}', '\d', '\d{2}', '\d{2}', '\d{4}'),
                    $frmpro_settings->date_format
                );

                if ( preg_match('/^'. $reg_ex .'/', trim($values[$stamp])) ) {
                    $values[$stamp] = FrmProAppHelper::convert_date($values[$stamp], $frmpro_settings->date_format, 'Y-m-d H:i:s');
                }
            }

            $values[$stamp] = date('Y-m-d H:i:s', (strtotime($values[$stamp]) - $offset));

            unset($stamp);
        }
    }

    /**
     * Make sure values are in the format they should be saved in
     */
    private static function convert_db_cols( &$values ) {
        if ( ! isset($values['item_key']) || empty($values['item_key']) ) {
            global $wpdb;
            $values['item_key'] = FrmAppHelper::get_unique_key('', $wpdb->prefix .'frm_items', 'item_key');
        }

        if ( isset($values['user_id']) ) {
            $values['user_id'] = FrmAppHelper::get_user_id_param($values['user_id']);
        }

		if ( isset( $values['updated_by'] ) ) {
            $values['updated_by'] = FrmAppHelper::get_user_id_param($values['updated_by']);
        }

		if ( isset( $values['is_draft'] ) ) {
            $values['is_draft'] = (int) $values['is_draft'];
        }
    }

    /**
     * Save the entry after checking if it should be created or updated
     */
    private static function save_or_edit_entry($values) {
        $editing = false;
        if ( isset($values['id']) && $values['item_key'] ) {

            //check for updating by entry ID
            $editing = FrmDb::get_var( 'frm_items', array( 'form_id' => $values['form_id'], 'id' => $values['id']) );
        }

        if ( $editing ) {
            FrmEntry::update($values['id'], $values);
        } else {
            FrmEntry::create($values);
        }
    }

    public static function get_file_id($value) {
        global $wpdb;

        if ( ! is_array($value ) ) {
            $value = explode(',', $value);
        }

        foreach ( (array) $value as $pos => $m) {
            $m = trim($m);
            if (empty($m) ) {
                continue;
            }

            if ( ! is_numeric($m) ) {
                //get the ID from the URL if on this site
                $m = FrmDb::get_col( $wpdb->posts, array( 'guid' => $m), 'ID' );
            }

            if ( ! is_numeric($m) ) {
                unset($value[$pos]);
            } else {
                $value[$pos] = $m;
            }

            unset($pos);
            unset($m);
        }

        return $value;
    }

    public static function get_date($value) {
		if ( ! empty($value) ) {
            $value = date('Y-m-d', strtotime($value));
        }

        return $value;
    }

    public static function get_multi_opts($value, $field) {

        if ( ! $field || empty($value) || in_array($value, (array) $field->options ) ) {
            return $value;
        }

        if ( $field->type != 'checkbox' && $field->type != 'select' ) {
            return $value;
        }

		if ( $field->type == 'select' && ! FrmField::is_option_true( $field, 'multiple' ) ) {
            return $value;
        }

        $checked = is_array($value) ? $value : maybe_unserialize($value);

        if ( ! is_array($checked) ) {
            $checked = explode(',', $checked);
        }

        if ( ! empty($checked) && count($checked) > 1 ) {
            $value = array_map('trim', $checked);
        }

        unset($checked);

        return $value;
    }

    public static function get_dfe_id($value, $field, $ids = array() ) {

		if ( ! $field || FrmProField::is_list_field( $field ) ) {
            return $value;
        }

        if ( ! empty($ids) && is_numeric($value) && isset($ids[$value]) ) {
            // the entry was just imported, so we have the id
            return $ids[$value];
        }

        if ( ! is_array($value) ) {
            $new_id = FrmDb::get_var( 'frm_item_metas', array( 'field_id' => $field->field_options['form_select'], 'meta_value' => $value), 'item_id' );

            if ( $new_id && is_numeric($new_id) ) {
                return $new_id;
            }

            unset($new_id);
        }

        if ( ! is_array($value) && strpos($value, ',') ) {
            $checked = maybe_unserialize($value);

            if ( ! is_array($checked) ) {
                $checked = explode(',', $checked);
            }
        } else {
            $checked = $value;
        }

        if ( ! $checked || ! is_array($checked) ) {
            return $value;
        }

        $value = array_map('trim', $checked);

        foreach ( $value as $dfe_k => $dfe_id ) {
            $query = array( 'field_id' => $field->field_options['form_select'], 'meta_value' => $dfe_id);
            $new_id = FrmDb::get_var( 'frm_item_metas', $query, 'item_id' );

            if ( $new_id ) {
                $value[$dfe_k] = $new_id;
            }
            unset($new_id);
        }

        unset($checked);

        return $value;
    }

	/**
	* Get the new child IDs for a repeating field's or embedded form's meta_value
	*
	* @since 2.0.16
	* @param array $meta_value
	* @param object $field
	* @param array $saved_entries
	* @return array $meta_value
	*/
	private static function get_new_child_ids( $meta_value, $field, $saved_entries ) {
		if ( $field->type == 'form' || FrmField::is_repeating_field( $field ) ) {

			$new_meta_value = array();
			foreach ( (array) $meta_value as $old_child_id ) {
				if ( isset( $saved_entries[ $old_child_id ] ) ) {
					$new_meta_value[] = $saved_entries[ $old_child_id ];
				}
			}

			$meta_value = $new_meta_value;
		}

		return $meta_value;
	}

	/**
	 * Perform an action after a field is imported
	 *
	 * @since 2.0.25
	 * @param array $f
	 * @param int $field_id
	 */
	public static function after_field_is_imported( $f, $field_id ) {
		self::add_in_section_value_to_repeating_fields( $f, $field_id );
	}

	/**
	 * Add the in_section value to fields in a repeating section
	 *
	 * @since 2.0.25
	 * @param array $f
	 * @param int $section_id
	 */
	private static function add_in_section_value_to_repeating_fields( $f, $section_id ) {
		if ( $f['type'] == 'divider'
			&& FrmField::is_option_true( $f['field_options'], 'repeat' )
			&& FrmField::is_option_true( $f['field_options'], 'form_select' )
		) {
			$new_form_id = $f['field_options']['form_select'];
			$child_fields = FrmDb::get_col( 'frm_fields', array( 'form_id' => $new_form_id ), 'id' );

			if ( ! $child_fields ) {
				return;
			}

			self::add_in_section_value_to_field_ids( $child_fields, $section_id );
		}
	}

	/**
	 * Add specific in_section value to an array of field IDs
	 *
	 * @since 2.0.25
	 * @param array $field_ids
	 * @param int $section_id
	 */
	public static function add_in_section_value_to_field_ids( $field_ids, $section_id ) {
		foreach ( $field_ids as $child_id ) {
			$child_field_options = FrmDb::get_var( 'frm_fields', array( 'id' => $child_id ), 'field_options' );
			$child_field_options = maybe_unserialize( $child_field_options );
			$child_field_options['in_section'] = $section_id;

			// Update now
			$update_values = array( 'field_options' => $child_field_options );
			FrmField::update( $child_id, $update_values );
		}
	}
}
