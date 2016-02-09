<?php
class FrmProField {

	public static function create( $field_data ) {

        if ( $field_data['field_options']['label'] != 'none' ) {
            $field_data['field_options']['label'] = '';
        }

        $defaults = array(
            'number' => array( 'maxnum'    => 9999999 ),
            'date'  => array( 'max'   => '10' ),
            'phone' => array( 'size'  => '115px' ),
            'rte'   => array( 'max'   => 7 ),
            'end_divider' => array( 'format' => 'both' ), // set icon format
        );

        if ( isset($defaults[$field_data['type']]) ) {
            $field_data['field_options'] = array_merge($field_data['field_options'], $defaults[$field_data['type']]);
            return $field_data;
        }

		switch( $field_data['type'] ) {
            case 'scale':
                $field_data['options'] = serialize(range(1,10));
                $field_data['field_options']['minnum'] = 1;
                $field_data['field_options']['maxnum'] = 10;
                break;
            case 'select':
                $width = FrmStylesController::get_style_val('auto_width', $field_data['form_id']);
                $field_data['field_options']['size'] = $width;
                break;
            case 'user_id':
                $field_data['name'] = __( 'User ID', 'formidable' );
                break;
            case 'divider':
                $field_data['field_options']['label'] = 'top';
                if ( isset($field_data['field_options']['repeat']) && $field_data['field_options']['repeat'] ) {
                    // create the repeatable form
                    $field_data['field_options']['form_select'] = self::create_repeat_form( 0, array( 'parent_form_id' => $field_data['form_id'], 'field_name' => $field_data['name'] ) );
                }
                break;
            case 'break':
                $field_data['name'] = __( 'Next', 'formidable' );
            break;
        }
        return $field_data;
    }

	public static function update( $field_options, $field, $values ) {
		$defaults = FrmProFieldsHelper::get_default_field_opts( false, $field );
		unset( $defaults['post_field'], $defaults['custom_field'], $defaults['taxonomy'], $defaults['exclude_cat'] );

        $defaults['minnum'] = 0;
        $defaults['maxnum'] = 9999;

		foreach ( $defaults as $opt => $default ) {
			$field_options[ $opt ] = isset( $values['field_options'][ $opt . '_' . $field->id ] ) ? $values['field_options'][ $opt . '_' . $field->id ] : $default;
            unset( $opt, $default );
        }

		foreach ( $field_options['hide_field'] as $i => $f ) {
			if ( empty( $f ) ) {
                unset( $field_options['hide_field'][$i], $field_options['hide_field_cond'][$i] );
                if ( isset($field_options['hide_opt']) && is_array($field_options['hide_opt']) ) {
                    unset($field_options['hide_opt'][$i]);
                }
            }
            unset($i, $f);
        }

        if ( $field->type == 'scale' ) {
            if ( (int) $field_options['maxnum'] >= 99 ) {
                $field_options['maxnum'] = 10;
            }

            $options = range($field_options['minnum'], $field_options['maxnum']);

            FrmField::update($field->id, array( 'options' => serialize($options)));
        } else if ( $field->type == 'hidden' && isset($field_options['required']) && $field_options['required'] ) {
            $field_options['required'] = false;
        }

        return $field_options;
    }

	public static function duplicate( $values ) {
        global $frm_duplicate_ids;
        if ( empty($frm_duplicate_ids) || empty($values['field_options']) ) {
            return $values;
        }

        // switch out fields from calculation or default values
        $switch_string = array( 'default_value', 'calc');
        foreach ( $switch_string as $opt ) {
            if ( ( ! isset($values['field_options'][$opt]) || empty($values['field_options'][$opt]) ) &&
                ( ! isset($values[$opt]) || empty($values[$opt]) ) ) {
                continue;
            }

            $this_val = isset($values[$opt]) ? $values[$opt] : $values['field_options'][$opt];
            if ( is_array($this_val) ) {
                continue;
            }

            $ids = implode( '|', array_keys($frm_duplicate_ids) );

            preg_match_all( "/\[(". $ids .")\]/s", $this_val, $matches, PREG_PATTERN_ORDER);
            unset($ids);

            if ( ! isset($matches[1]) ) {
                unset($matches);
                continue;
            }

            foreach ( $matches[1] as $val ) {
                $new_val = str_replace('['. $val .']', '['. $frm_duplicate_ids[$val] .']', $this_val);
                if ( isset($values[$opt]) ) {
                    $this_val = $values[$opt] = $new_val;
                } else {
                    $this_val = $values['field_options'][$opt] = $new_val;
                }
                unset($new_val, $val);
            }

            unset($this_val, $matches);
        }

        // switch out field ids in conditional logic
        if ( isset($values['field_options']['hide_field']) && !empty($values['field_options']['hide_field']) ) {
            $values['field_options']['hide_field_cond'] = maybe_unserialize($values['field_options']['hide_field_cond']);
            $values['field_options']['hide_opt'] = maybe_unserialize($values['field_options']['hide_opt']);
            $values['field_options']['hide_field'] = maybe_unserialize($values['field_options']['hide_field']);

            foreach ( $values['field_options']['hide_field'] as $k => $f ) {
                if ( isset($frm_duplicate_ids[$f]) ) {
                    $values['field_options']['hide_field'][$k] = $frm_duplicate_ids[$f];
                }
                unset($k, $f);
            }
        }

        // switch out field ids if selected in a data from entries field
        if ( 'data' == $values['type'] && isset($values['field_options']['form_select']) &&
            !empty($values['field_options']['form_select']) && isset($frm_duplicate_ids[$values['field_options']['form_select']]) ) {
	        $values['field_options']['form_select'] = $frm_duplicate_ids[$values['field_options']['form_select']];
	    }

        return $values;
    }

	public static function delete( $id ) {
        $field = FrmField::getOne($id);
		if ( empty( $field ) ) {
			return;
		}

        // delete the form this repeating field created
        self::delete_repeat_field($field);

        //TODO: before delete do something with entries with data field meta_value = field_id
    }

    public static function delete_repeat_field($field) {
        if ( ! FrmField::is_repeating_field($field) ) {
            return;
        }

        if ( isset($field->field_options['form_select']) && is_numeric($field->field_options['form_select']) && $field->field_options['form_select'] != $field->form_id ) {
            FrmForm::destroy($field->field_options['form_select']);
        }
    }

	public static function is_list_field( $field ) {
		return $field->type == 'data' && ( ! isset( $field->field_options['data_type'] ) || $field->field_options['data_type'] == 'data' || $field->field_options['data_type'] == '' );
	}

	/**
	* Create the form for a repeating section
	*
	* @since 2.0.12
	*
	* @param int $form_id
	* @param array $atts
	* @return int $form_id
	*/
	public static function create_repeat_form( $form_id, $atts ) {
		$form_values = array(
			'parent_form_id' => $atts['parent_form_id'],
			'name' => $atts['field_name'],
			'status' => 'published',
		);
        $form_values = FrmFormsHelper::setup_new_vars( $form_values );

        $form_id = (int) FrmForm::create( $form_values );

		return $form_id;
	}

	/**
	* Return all the field IDs for the fields inside of a section (not necessarily repeating) or an embedded form
	*
	* @since 2.0.13
	* @param array $field
	* @return array $children
	*/
	public static function get_children( $field ) {
		global $wpdb;

		$children = array();

		// If repeating field or embedded form
		if ( FrmField::is_repeating_field( $field ) || $field['type'] == 'form' ) {
			$repeat_id = isset( $field['form_select'] ) ? $field['form_select'] : $field['field_options']['form_select'];
			$children = FrmDb::get_col( $wpdb->prefix . 'frm_fields', array( 'form_id' => $repeat_id ) );

		} else {
			$end_divider_order = FrmDb::get_var( $wpdb->prefix . 'frm_fields', array( 'form_id' => $field['form_id'], 'type' => 'end_divider', 'field_order>' => $field['field_order'] ), 'field_order', array( 'order_by' => 'field_order ASC' ), 1 );
			$min_field_order = $field['field_order'] + 1;
			$max_field_order = $end_divider_order - 1;

			$children = FrmDb::get_col( $wpdb->prefix . 'frm_fields', array( 'form_id' => $field['form_id'], 'field_order>' => $min_field_order, 'field_order<' => $max_field_order ) );
		}

		return $children;
	}

	/**
	* Get the entry ID from a linked field
	*
	* @since 2.0.15
	* @param int $linked_field_id
	* @param string $where_val
	* @param string $where_is
	* @return int $linked_id
	*/
	public static function get_dynamic_field_entry_id( $linked_field_id, $where_val, $where_is ){
		$query = array(
			'field_id' => $linked_field_id,
			'meta_value' . FrmDb::append_where_is( $where_is ) => $where_val,
		);
		$linked_id = FrmDb::get_col( 'frm_item_metas', $query, 'item_id' );
		return $linked_id;
	}

	/**
	* Get the category ID from the category name
	*
	* @since 2.0.15
	* @param string $cat_name
	* @return int
	*/
	public static function get_cat_id_from_text( $cat_name ) {
		return get_cat_ID( $cat_name );
	}
}
