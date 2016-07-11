<?php

class FrmProFormsHelper{

	public static function setup_new_vars( $values ) {

        foreach ( self::get_default_opts() as $var => $default ) {
            $values[$var] = FrmAppHelper::get_param($var, $default);
        }
        return $values;
    }

	public static function setup_edit_vars( $values ) {
        $record = FrmForm::getOne($values['id']);
        foreach ( array( 'logged_in' => $record->logged_in, 'editable' => $record->editable) as $var => $default)
            $values[$var] = FrmAppHelper::get_param($var, $default);

		foreach ( self::get_default_opts() as $opt => $default ) {
            if ( ! isset($values[$opt]) ) {
                $values[$opt] = ( $_POST && isset($_POST['options'][$opt]) ) ? $_POST['options'][$opt] : $default;
            }

            unset($opt, $default);
        }

        return $values;
    }

    public static function get_sub_form($field_name, $field, $args = array()) {
        $defaults = array(
            'repeat'    => 0,
            'errors'    => array(),
        );

        $args = wp_parse_args($args, $defaults);

        $subform = FrmForm::getOne($field['form_select']);
		if ( empty( $subform ) ) {
			return;
		}

        $subfields = FrmField::get_all_for_form($field['form_select']);

?>
<input type="hidden" name="<?php echo esc_attr( $field_name ) ?>[form]" value="<?php echo esc_attr( $field['form_select'] ) ?>" />
<?php

        if ( empty($subfields) ) {
            return;
        }

        $repeat_atts = array(
            'form'  => $subform,
            'fields' => $subfields,
            'errors' => $args['errors'],
            'parent_field' => $field,
            'repeat'    => $args['repeat'],
			'field_name' => $field_name,
        );

        if ( empty($field['value']) ) {
			// Row count must be zero if field value is empty
			$start_rows = apply_filters( 'frm_repeat_start_rows', 1, $field );

			for ( $i = 0, $j = $start_rows; $i < $j; $i++ ) {
				// add an empty sub entry
				$repeat_atts['row_count'] = $repeat_atts['i'] = $i;
				self::repeat_field_set( $field_name, $repeat_atts );
			}
            return;
        }

		$row_count = 0;
        foreach ( (array) $field['value'] as $k => $checked ) {
            $repeat_atts['i'] = $k;
			$repeat_atts['value'] = '';

            if ( ! isset($field['value']['form']) ) {
                // this is not a posted value from moving between pages
                $checked = apply_filters('frm_hidden_value', $checked, $field);
                if ( empty($checked) || ! is_numeric($checked) ) {
                    continue;
                }

                $repeat_atts['i'] = 'i'. $checked;
                $repeat_atts['entry_id'] = $checked;
				$repeat_atts['value'] = $checked;
            } else if ( $k === 'id' ) {
                foreach ( $checked as $entry_id ) {
?>
<input type="hidden" name="<?php echo esc_attr( $field_name ) ?>[id][]" value="<?php echo esc_attr( $entry_id ) ?>" />
<?php
                    unset($entry_id);
                }
                continue;
            } else if ( $k === 'form' ) {
                continue;
			} else if ( strpos( $k, 'i' ) === 0 ) {
				// include the entry id when values are posted
				$repeat_atts['entry_id'] = absint( str_replace( 'i', '', $k ) );
            }

			// Keep track of row count
			$repeat_atts['row_count'] = $row_count;
			$row_count++;

            // show each existing sub entry
            self::repeat_field_set($field_name, $repeat_atts);
            unset($k, $checked);
        }

        unset($subform, $subfields);
    }

    public static function repeat_field_set($field_name, $args = array() ) {
        $defaults = array(
            'i'         => 0,
            'entry_id'  => false,
            'form'      => false,
            'fields'    => array(),
            'errors'    => array(),
            'parent_field' => 0,
            'repeat'    => 0,
			'row_count'	=> false,
			'value'     => '',
			'field_name' => '',
        );
        $args = wp_parse_args($args, $defaults);

        if ( empty($args['parent_field']) ) {
            return;
        }

        if ( is_numeric($args['parent_field']) ) {
            $args['parent_field'] = (array) FrmField::getOne($args['parent_field']);
            $args['parent_field']['format'] = isset($args['parent_field']['field_options']['format']) ? $args['parent_field']['field_options']['format'] : '';
        }

		FrmForm::maybe_get_form( $args['form'] );

        if ( empty($args['fields']) ) {
            $args['fields'] = FrmField::get_all_for_form($args['form']->id);
        }

        $values = array();

        if ( $args['fields'] ) {
			$pass_args = array(
				'parent_form_id' => $args['parent_field']['form_id'],
				'parent_field_id' => $args['parent_field']['id'],
				'key_pointer' => $args['i'],
				'repeating' => true,
				'fields' => $args['fields'],
				'in_embed_form' => $args['parent_field']['type'] == 'form' ? $args['parent_field']['id'] : '0',
			);

            if ( empty($args['entry_id']) ) {
				$values = FrmEntriesHelper::setup_new_vars( $args['fields'], $args['form'], false, $pass_args );
            } else {
                $entry = FrmEntry::getOne($args['entry_id'], true);
                if ( $entry && $entry->form_id == $args['form']->id ) {
					$values = FrmProEntriesController::setup_entry_values_for_editing( $entry, $pass_args );
                } else {
                    return;
                }
            }
        }

        $format = isset($args['parent_field']['format']) ? $args['parent_field']['format'] : '';
        $end = false;
        $count = 0;
        foreach ( $values['fields'] as $subfield ) {
            if ( 'end_divider' == $subfield['type'] ) {
                $end = $subfield;
            } else if ( ! in_array( $subfield['type'] , array( 'hidden', 'user_id' ) ) ) {
                if ( isset( $subfield['conf_field'] ) && $subfield['conf_field'] ) {
                    $count = $count + 2;
                } else {
                    $count++;
                }
            }
            unset($subfield);
        }
        if ( $args['repeat'] ) {
            $count++;
        }

        $classes = array(
            2 => 'half',
            3 => 'third',
            4 => 'fourth',
            5 => 'fifth',
            6 => 'sixth',
            7 => 'seventh',
            8 => 'eighth',
        );

        $field_class = ( ! empty($format) && isset($classes[$count]) ) ? $classes[$count] : '';

        echo '<div id="frm_section_'. $args['parent_field']['id'] .'-'. $args['i'] .'" class="frm_repeat_'. ( empty($format) ? 'sec' : $format ) .' frm_repeat_'. $args['parent_field']['id'] . ( $args['row_count'] === 0 ? ' frm_first_repeat' : '' ) . '">' . "\n";

		self::add_hidden_repeat_entry_id( $args );
		self::add_default_item_meta_field( $args );

        $label_pos = 'top';
        $field_num = 1;
        foreach ( $values['fields'] as $subfield ) {
            $subfield_name = $field_name .'['. $args['i'] .']['. $subfield['id'] .']';
            $subfield_plus_id = '-'. $args['i'];
            $subfield_id = $subfield['id'] .'-'. $args['parent_field']['id'] . $subfield_plus_id;

            if ( $args['parent_field'] && ! empty($args['parent_field']['value']) && isset($args['parent_field']['value']['form']) && isset($args['parent_field']['value'][$args['i']]) && isset($args['parent_field']['value'][$args['i']][$subfield['id']]) ) {
                // this is a posted value from moving between pages, so set the POSTed value
                $subfield['value'] = $args['parent_field']['value'][$args['i']][$subfield['id']];
            }

            if ( !empty($field_class) ) {
                if ( 1 == $field_num ) {
                    $subfield['classes'] .= ' frm_first frm_'. $field_class;
                } else {
                    $subfield['classes'] .= ' frm_'. $field_class;
                }
            }

			$field_num++;

            if ( 'top' == $label_pos && in_array($subfield['label'], array( 'top', 'hidden', '')) ) {
                // add placeholder label if repeating
                $label_pos = 'hidden';
            }

			$field_args = array(
				'field_name'    => $subfield_name,
				'field_id'      => $subfield_id,
				'field_plus_id' => $subfield_plus_id,
				'section_id'     => $args['parent_field']['id'],
			);

            if ( apply_filters('frm_show_normal_field_type', true, $subfield['type']) ) {
				echo FrmFieldsHelper::replace_shortcodes( $subfield['custom_html'], $subfield, $args['errors'], $args['form'], $field_args );
            } else {
				do_action( 'frm_show_other_field_type', $subfield, $args['form'], $field_args );
            }

			// Track whether field is in an embedded form
	        if ( 'form' == $args['parent_field']['type'] ) {
		        $subfield['in_embed_form'] = $args['parent_field']['id'];
	        }

            unset($subfield_name, $subfield_id);
            do_action('frm_get_field_scripts', $subfield, $args['form'], $args['parent_field']['form_id']);
        }

        if ( ! $args['repeat'] ) {
			// Close frm_repeat div
            echo '</div>'. "\n";
            return;
        }

        $args['format'] = $format;
        $args['label_pos'] = $label_pos;
        $args['field_class'] = $field_class;
        echo self::repeat_buttons($args, $end);

		// Close frm_repeat div
        echo '</div>'. "\n";
    }

	/**
	 * Include the id of the entry being edited inside the repeating section
	 * @since 2.0.12
	 */
	private static function add_hidden_repeat_entry_id( $args ) {
		if ( ! empty( $args['value'] ) ) {
			echo '<input type="hidden" name="' . esc_attr( $args['field_name'] ) . '[id][]" value="' . esc_attr( $args['value'] ) . '" />';
		}
	}

	/**
	* Add item meta to each row in repeating section or embedded form so the entry is always validated
	*
	* @since 2.0.08
	* @param array $args
	*/
	private static function add_default_item_meta_field( $args ) {
		echo '<input type="hidden" name="item_meta[' . $args['parent_field']['id'] . '][' . $args['i'] . '][0]" value="" />';
	}

    public static function repeat_buttons($args, $end = false) {
        $args['end_format'] = 'icon';

		if ( ! $end ) {
			$end = self::get_end_repeat_field( $args );
		}

        if ( $end ) {
            $args['add_label'] = $end['add_label'];
            $args['remove_label'] = $end['remove_label'];

            if (  ! empty($end['format']) ) {
                $args['end_format'] = $end['format'];
            }
        }

        $triggers = self::repeat_button_html($args, $end);

        return apply_filters('frm_repeat_triggers', $triggers, $end, $args['parent_field'], $args['field_class']);
    }

	private static function get_end_repeat_field( $args ) {
		$query = array( 'fi.form_id' => $args['parent_field']['form_id'], 'type' => 'end_divider', 'field_order >' => $args['parent_field']['field_order'] + 1 );
		$end_field = FrmField::getAll( $query, 'field_order', 1 );
		$field_array = FrmProFieldsHelper::initialize_array_field( $end_field );

		foreach ( array( 'format', 'add_label' ,'remove_label', 'classes' ) as $o ) {
			if ( isset( $end_field->field_options[ $o ] ) ) {
				$field_array[ $o ] = $end_field->field_options[ $o ];
			}
		}

		$prepared_field = apply_filters( 'frm_setup_new_fields_vars', $field_array, $end_field, array() );

		return $prepared_field;
	}

    public static function repeat_button_html($args, $end) {
        $defaults = array(
            'add_icon'      => '',
            'remove_icon'   => '',
            'add_label'     => __( 'Add', 'formidable' ),
            'remove_label'  => __( 'Remove', 'formidable' ),
            'add_classes'   => ' frm_button',
            'remove_classes' => ' frm_button',
        );

        $args = wp_parse_args($args, $defaults);

        if ( ! isset($args['end_format']) && isset($args['format']) ) {
            $args['end_format'] = $args['format'];
        }

        if ( 'both' == $args['end_format'] ) {
            $args['remove_icon'] = '<i class="frm_icon_font frm_minus_icon"> </i> ';
            $args['add_icon'] = '<i class="frm_icon_font frm_plus_icon"> </i> ';
        } else if ( 'text' != $args['end_format'] ) {
            $args['add_label'] = $args['remove_label'] = '';
            $args['add_classes'] = ' frm_icon_font frm_plus_icon';
            $args['remove_classes'] = ' frm_icon_font frm_minus_icon';
        }

		// Hide Remove button on first row
		if ( $args['row_count'] === 0 ) {
			$args['remove_classes'] .= ' frm_hidden';
		}

		$classes = 'frm_form_field frm_'. $args['label_pos'] .'_container frm_repeat_buttons';
		$classes .= empty( $args['field_class'] ) ? '' : ' frm_' . $args['field_class'];
		// Get classes for end divider
		$classes .= ( $end && isset( $end['classes'] ) ) ? ' ' . $end['classes'] : '';

		$triggers = '<div class="' . esc_attr( $classes ) . '">';

        if ( 'hidden' == $args['label_pos'] && ! empty($args['format']) ) {
            $triggers .= '<label class="frm_primary_label">&nbsp;</label>';
        }

		$triggers .= '<a href="#" class="frm_add_form_row' . esc_attr( $args['add_classes'] ) . '" data-parent="' . esc_attr( $args['parent_field']['id'] ) . '">' . $args['add_icon'] . $args['add_label'] . '</a>' . "\n";
		$triggers .= '<a href="#" class="frm_remove_form_row' . esc_attr( $args['remove_classes'] ) . '" data-key="' . esc_attr( $args['i'] ) . '" data-parent="' . esc_attr( $args['parent_field']['id'] ) . '">' . $args['remove_icon'] . $args['remove_label'] . '</a> ';

		$triggers .= '</div>';

        return $triggers;
    }

	public static function load_chosen_js( $frm_vars ) {
		if ( isset( $frm_vars['chosen_loaded' ] ) && $frm_vars['chosen_loaded'] ) {
			$original_js = 'allow_single_deselect:true';
			$chosen_js = apply_filters( 'frm_chosen_js', $original_js );
			if ( $original_js != $chosen_js ) {
				?>__frmChosen=<?php echo json_encode( $chosen_js ) ?>;<?php
			}
		}
	}

	/**
	 * Load the conditional field IDs for JavaScript
	 *
	 * @since 2.01.0
	 * @param array $frm_vars
	 */
	public static function load_hide_conditional_fields_js( $frm_vars ) {

		if ( self::is_initial_load_for_at_least_one_form( $frm_vars ) ) {
			// Check the logic on all dependent fields
			if ( isset( $frm_vars['dep_logic_fields'] ) && ! empty( $frm_vars['dep_logic_fields'] ) ) {
				echo 'var frmHide=' . json_encode( $frm_vars['dep_logic_fields'] ) . ';';
				echo 'if(typeof __frmHideOrShowFields == "undefined"){__frmHideOrShowFields=frmHide;}';
				echo 'else{__frmHideOrShowFields=jQuery.extend(__frmHideOrShowFields,frmHide);}';
			}
		} else {
			// Save time and just hide the fields that are in frm_hide_fields
			echo '__frmHideFields=true;';
		}

		// Check dependent Dynamic fields
		if ( isset( $frm_vars['dep_dynamic_fields'] )  && ! empty( $frm_vars['dep_dynamic_fields'] ) ) {
			echo '__frmDepDynamicFields=' . json_encode( $frm_vars['dep_dynamic_fields'] ) . ';';
		}
	}

	/**
	 * Check if at least one form is loading for the first time
	 *
	 * @since 2.01.0
	 * @param array $frm_vars
	 * @return bool
	 */
	private static function is_initial_load_for_at_least_one_form( $frm_vars ) {
		$initial_load = true;

		if ( isset( $_POST['form_id'] ) ) {
			foreach ( $frm_vars['forms_loaded'] as $form ) {
				if ( ! is_object( $form ) ) {
					continue;
				}

				if ( isset($frm_vars['prev_page'][ $form->id ]) || self::going_to_prev( $form->id ) || FrmProFormsHelper::saving_draft() ) {
					$initial_load = false;
				} else {
					$initial_load = true;
					break;
				}
			}
		}

		return $initial_load;
	}

	public static function load_dropzone_js( $frm_vars ) {
		if ( ! isset( $frm_vars['dropzone_loaded'] ) || empty( $frm_vars['dropzone_loaded'] ) || ! is_array( $frm_vars['dropzone_loaded'] ) ) {
			return;
		}

		$js = array();
		foreach ( $frm_vars['dropzone_loaded'] as $field_id => $options ) {
			$js[] = $options;
		}
		echo '__frmDropzone=' . json_encode( $js ) . ';';
	}

	public static function load_datepicker_js( $frm_vars ) {
        if ( ! isset($frm_vars['datepicker_loaded']) || empty($frm_vars['datepicker_loaded']) || ! is_array($frm_vars['datepicker_loaded']) ) {
            return;
        }

		$frmpro_settings = FrmProAppHelper::get_settings();

        reset($frm_vars['datepicker_loaded']);
        $datepicker = key($frm_vars['datepicker_loaded']);
		$load_lang = false;

		$datepicker_js = array();
        foreach ( $frm_vars['datepicker_loaded'] as $date_field_id => $options ) {
            if ( strpos($date_field_id, '^') === 0 ) {
                // this is a repeating field
                $trigger_id = 'input[id^="'. str_replace('^', '', esc_attr( $date_field_id ) ) .'"]';
            } else {
                $trigger_id = '#'. esc_attr( $date_field_id );
            }

			$custom_options = self::get_custom_date_js( $date_field_id, $options );

			$date_options = array(
				'triggerID' => $trigger_id,
				'locale'    => $options['locale'],
				'options'   => array(
					'dateFormat'  => $frmpro_settings->cal_date_format,
					'changeMonth' => 'true',
					'changeYear'  => 'true',
					'yearRange'   => $options['start_year'] . ':' . $options['end_year'],
					'defaultDate' => empty( $options['default_date'] ) ? '' : $options['default_date'],
					'beforeShowDay' => null,
				),
				'customOptions'  => $custom_options,
			);
			$date_options = apply_filters( 'frm_date_field_options', $date_options, array( 'field_id' => $date_field_id, 'options' => $options ) );

			if ( empty( $custom_options ) ) {
				$datepicker_js[] = $date_options;
			} else if ( $date_field_id ) {
				?>
jQuery(document).ready(function($){
$('<?php echo $trigger_id ?>').addClass('frm_custom_date');
$(document).on('focusin','<?php echo $trigger_id ?>', function(){
$.datepicker.setDefaults($.datepicker.regional['']);
$(this).datepicker($.extend($.datepicker.regional['<?php echo esc_js( $options['locale'] ) ?>'],{dateFormat:'<?php echo esc_js( $frmpro_settings->cal_date_format ) ?>',changeMonth:true,changeYear:true,yearRange:'<?php echo esc_js( $date_options['options']['yearRange'] ) ?>',defaultDate:'<?php echo esc_js( $date_options['options']['defaultDate'] ); ?>'<?php
echo $custom_options;
?>}));
});
});
<?php
			}

			if ( ! empty( $options['locale'] ) && ! $load_lang ) {
				$load_lang = true;
				$base_url = FrmAppHelper::jquery_ui_base_url();
				wp_enqueue_script( 'jquery-ui-i18n', $base_url . '/i18n/jquery-ui-i18n.min.js' );
				// this was enqueued late, so make sure it gets printed
				add_action( 'wp_footer', 'print_footer_scripts', 21 );
				add_action( 'admin_print_footer_scripts', 'print_footer_scripts', 99 );
			}
        }

		if ( ! empty( $datepicker_js ) ) {
			echo 'var frmDates=' . json_encode( $datepicker_js ) . ';';
			echo 'if(typeof __frmDatepicker == "undefined"){__frmDatepicker=frmDates;}';
			echo 'else{__frmDatepicker=jQuery.extend(__frmDatepicker,frmDates);}';
		}
        self::load_timepicker_js($datepicker, $frm_vars);
    }

	private static function get_custom_date_js( $date_field_id, $options ) {
		ob_start();
		do_action( 'frm_date_field_js', $date_field_id, $options );
		$custom_options = ob_get_contents();
		ob_end_clean();

		return $custom_options;
	}

    public static function load_timepicker_js($datepicker, $frm_vars) {
        if ( ! isset($frm_vars['timepicker_loaded']) || empty($frm_vars['timepicker_loaded']) || ! $datepicker ) {
            return;
        }

		$unique_time_fields = array();
        foreach ( $frm_vars['timepicker_loaded'] as $time_field_id => $options ) {
            if ( ! $options ) {
                continue;
            }

			$unique_time_fields[] = array( 'dateID' => $datepicker, 'timeID' => $time_field_id );
        }

		if ( ! empty( $unique_time_fields ) ) {
			echo '__frmUniqueTimes=' . json_encode( $unique_time_fields ) . ';';
		}
    }

    public static function load_calc_js($frm_vars) {
        if ( ! isset($frm_vars['calc_fields']) || empty($frm_vars['calc_fields']) ) {
            return;
        }

        $calc_rules = array(
            'fields'    => array(),
            'calc'      => array(),
            'fieldKeys' => array(),
			'fieldsWithCalc'	=> array(),
        );

        $triggers = array();

        foreach ( $frm_vars['calc_fields'] as $result => $field ) {
			$calc_rules['fieldsWithCalc'][ $field['field_id'] ] = $result;
            $calc = $field['calc'];
			FrmProFieldsHelper::replace_non_standard_formidable_shortcodes( compact('field'), $calc );
            preg_match_all("/\[(.?)\b(.*?)(?:(\/))?\]/s", $calc, $matches, PREG_PATTERN_ORDER);

            $field_keys = $calc_fields = array();

            foreach ( $matches[0] as $match_key => $val ) {
                $val = trim(trim($val, '['), ']');
                $calc_fields[$val] = FrmField::getOne($val);
                if ( ! $calc_fields[$val] ) {
                    unset($calc_fields[$val]);
                    continue;
                }

				$field_keys[$calc_fields[$val]->id] = self::get_field_call_for_calc( $calc_fields[ $val ], $field['parent_form_id'] );

				$calc_rules['fieldKeys'] = $calc_rules['fieldKeys'] + $field_keys;

                $calc = str_replace($matches[0][$match_key], '['. $calc_fields[$val]->id .']', $calc);

				// Prevent invalid decrement error for -- in calcs
				if ( $field['calc_type'] != 'text' ) {
					$calc = str_replace( '-[', '- [', $calc );
				}
			}

            $triggers[] = reset($field_keys);
			$calc_rules['calc'][ $result ] = self::get_calc_rule_for_field( array(
				'field'    => $field,
				'calc'     => $calc,
				'field_id' => $field['field_id'],
				'form_id'  => $field['parent_form_id'],
			) );
			$calc_rules['calc'][ $result ]['fields'] = array();

            foreach ( $calc_fields as $calc_field ) {
                $calc_rules['calc'][$result]['fields'][] = $calc_field->id;
                if ( isset($calc_rules['fields'][$calc_field->id]) ) {
                    $calc_rules['fields'][$calc_field->id]['total'][] = $result;
                } else {
                    $calc_rules['fields'][$calc_field->id] = array(
                        'total' => array($result),
                        'type'  => ( $calc_field->type == 'lookup' ) ? $calc_field->field_options['data_type'] : $calc_field->type,
                        'key'   => $field_keys[$calc_field->id],
                    );
                }

                if ( $calc_field->type == 'date' ) {
                    if ( ! isset($frmpro_settings) ) {
                        $frmpro_settings = new FrmProSettings();
                    }
                    $calc_rules['date'] = $frmpro_settings->cal_date_format;
                }
            }
        }

        // trigger calculations on page load
        if ( ! empty($triggers) ) {
			$triggers = array_filter( array_unique( $triggers ) );
			$calc_rules['triggers'] = array_values( $triggers );
        }

		$var_name = '__FRMCALC';
		echo 'var frmcalcs=' . json_encode( $calc_rules ) . ";\n";
		echo 'if(typeof ' . $var_name . ' == "undefined"){' . $var_name . '=frmcalcs;}';
		echo 'else{' . $var_name . '=jQuery.extend(true,{},' . $var_name . ',frmcalcs);}';
    }

	public static function get_calc_rule_for_field( $atts ) {
		$field = $atts['field'];

		$rule = array(
			'calc'			=> isset( $atts['calc'] ) ? $atts['calc'] : $field['calc'],
			'calc_dec'		=> $field['calc_dec'],
			'calc_type'		=> $field['calc_type'],
			'form_id'		=> $atts['form_id'],
			'field_id'		=> isset( $atts['field_id'] ) ? $atts['field_id'] : $field['id'],
			'in_section'    => isset( $field['in_section'] ) ? $field['in_section'] : '0',
			'in_embed_form' => isset( $field['in_embed_form'] ) ? $field['in_embed_form'] : '0',
		);

		$rule['inSection'] = $rule['in_section'];
		$rule['inEmbedForm'] = $rule['in_embed_form'];

		if ( isset( $atts['parent_form_id'] ) ) {
			$rule['parent_form_id'] = $atts['parent_form_id'];
		}

		return $rule;
	}

	/**
	 * Get the field call for a calc field
	 *
	 * @since 2.01.0
	 *
	 * @param object $calc_field
	 * @param int $parent_form_id
	 * @return string $field_call
	 */
	private static function get_field_call_for_calc( $calc_field, $parent_form_id ) {
		$html_field_id = '="field_'. $calc_field->field_key;

		// If field is inside of repeating section/embedded form or it is a radio, scale, or checkbox field
		$has_changing_ids = in_array( $calc_field->type, array( 'radio', 'scale', 'checkbox' ) );
		$is_radio_lookup = ( $calc_field->type == 'lookup' && $calc_field->field_options['data_type'] == 'radio' );
		$in_child_form = $parent_form_id != $calc_field->form_id;
		if ( $has_changing_ids || $is_radio_lookup || $in_child_form ) {
			$html_field_id = '^'. $html_field_id .'-';
		} else if ( $calc_field->type == 'select' ) {
			$is_multiselect = FrmField::get_option( $calc_field, 'multiselect' );
			if ( $is_multiselect ) {
				$html_field_id = '^'. $html_field_id;
			}
		}

		$field_call = '[id'. $html_field_id .'"]';

		return $field_call;
	}

    public static function load_input_mask_js() {
		global $frm_input_masks;
        if ( empty($frm_input_masks) ) {
            return;
        }

		$masks = array();
        foreach ( (array) $frm_input_masks as $f_key => $mask ) {
            if ( ! $mask ) {
                continue;
			} else if ( $mask !== true ) {
				// this isn't used in the plugin, but is here for those using the mask filter
				$masks[] = array(
					'trigger' => is_numeric( $f_key ) ? 'input[name="item_meta[' . $f_key . ']"]' : '#field_' . $f_key,
					'mask'    => $mask,
				);
			}
            unset($f_key, $mask);
        }

		if ( ! empty( $masks ) ) {
			echo '__frmMasks=' . json_encode( $masks ) . ';';
		}
    }

	public static function get_default_opts() {
        $frmpro_settings = new FrmProSettings();

        return array(
            'edit_value' => $frmpro_settings->update_value, 'edit_msg' => $frmpro_settings->edit_msg,
            'edit_action' => 'message', 'edit_url' => '', 'edit_page_id' => 0,
            'logged_in' => 0, 'logged_in_role' => '', 'editable' => 0, 'save_draft' => 0,
            'draft_msg' => __( 'Your draft has been saved.', 'formidable' ),
            'editable_role' => '', 'open_editable_role' => '-1',
            'copy' => 0, 'single_entry' => 0, 'single_entry_type' => 'user',
            'success_page_id' => '', 'success_url' => '', 'ajax_submit' => 0,
            'cookie_expiration' => 8000, 'prev_value' => __( 'Previous', 'formidable' ),
			'submit_align' => '', 'js_validate' => 0,
			'protect_files' => 0,
        );
    }

	public static function get_taxonomy_count( $taxonomy, $post_categories, $tax_count = 0 ) {
		if ( isset( $post_categories[ $taxonomy . $tax_count ] ) ) {
			$tax_count++;
			$tax_count = self::get_taxonomy_count( $taxonomy, $post_categories, $tax_count );
        }
        return $tax_count;
    }

	/**
	 * @since 2.0.8
	 */
	public static function can_submit_form_now( $errors, $values ) {
		global $frm_vars;

		$params = ( isset( $frm_vars['form_params'] ) && is_array( $frm_vars['form_params'] ) && isset( $frm_vars['form_params'][ $values['form_id'] ] ) ) ? $frm_vars['form_params'][ $values['form_id'] ] : FrmForm::get_params( $values['form_id'] );
		$values['action'] = $params['action'];

		if ( $params['action'] != 'create' ) {
			if ( self::has_another_page( $values['form_id'] ) ) {
				self::stop_submit_if_more_pages( $values, $errors );
			}
			return $errors;
		}

		$form = FrmForm::getOne( $values['form_id'] );

		if ( isset( $form->options['single_entry'] ) && $form->options['single_entry'] ) {
			if ( ! self::user_can_submit_form( $form ) ) {
				$frmpro_settings = new FrmProSettings();
				$k = is_numeric( $form->options['single_entry_type'] ) ? 'field' . $form->options['single_entry_type'] : 'single_entry';
				$errors[ $k ] = $frmpro_settings->already_submitted;
				self::stop_form_submit();
				return $errors;
			}
		}

		global $wpdb;
		$user_ID = get_current_user_id();

		if ( self::has_another_page( $values['form_id'] ) ) {
			self::stop_submit_if_more_pages( $values, $errors );
		} else if ( $form->editable && isset( $form->options['single_entry'] ) && $form->options['single_entry'] && $form->options['single_entry_type'] == 'user' && $user_ID && ! FrmAppHelper::is_admin() ) {
			$meta = FrmDb::get_var( $wpdb->prefix . 'frm_items', array( 'user_id' => $user_ID, 'form_id' => $form->id ) );

			if ( $meta ) {
				$frmpro_settings = new FrmProSettings();
				$errors['single_entry'] = $frmpro_settings->already_submitted;
				self::stop_form_submit();
			}
		}

		return $errors;
	}

	/**
	 * @since 2.0.8
	 */
	public static function stop_submit_if_more_pages( $values, &$errors ) {
		if ( self::going_to_prev( $values['form_id'] ) ) {
			$errors = array();
			self::stop_form_submit();
		} else if ( $values['action'] == 'create' ) {
			self::stop_form_submit();
		}
	}

	/**
	 * @since 2.0.8
	 */
	public static function stop_form_submit() {
		add_filter( 'frm_continue_to_create', '__return_false' );
	}

	/**
	 * @since 2.0.8
	 * @return boolean
	 */
	public static function user_can_submit_form( $form ) {
		$admin_entry = FrmAppHelper::is_admin();

		$can_submit = true;
		if ( $form->options['single_entry_type'] == 'cookie' && isset( $_COOKIE[ 'frm_form' . $form->id . '_' . COOKIEHASH ] ) ) {
			$can_submit = $admin_entry ? true : false;
		} else if ( $form->options['single_entry_type'] == 'ip' ) {
			if ( ! $admin_entry ) {
				$prev_entry = FrmEntry::getAll( array( 'it.form_id' => $form->id, 'it.ip' => FrmAppHelper::get_ip_address() ), '', 1 );
				if ( $prev_entry ) {
					$can_submit = false;
				}
			}
		} else if ( ( $form->options['single_entry_type'] == 'user' || ( isset( $form->options['save_draft'] ) && $form->options['save_draft'] == 1 ) ) && ! $form->editable ) {
			$user_ID = get_current_user_id();
			if ( $user_ID ) {
				$meta = FrmProEntriesHelper::check_for_user_entry( $user_ID, $form, ( $form->options['single_entry_type'] != 'user' ) );
				if ( $meta ) {
					$can_submit = false;
				}
			}
		}

		return $can_submit;
	}

	/**
	 * @since 2.0.8
	 */
	public static function has_another_page( $form_id ) {
		$more_pages = false;
		if ( ! self::saving_draft() ) {
			if ( self::going_to_prev( $form_id ) ) {
				$more_pages = true;
			} else {
				$more_pages = self::going_to_next( $form_id );
			}
		}

		return $more_pages;
	}

	/**
	 * @return boolean
	 */
	public static function going_to_prev( $form_id ) {
        $back = false;
		$next_page = FrmAppHelper::get_post_param( 'frm_next_page', 0, 'absint' );
		if ( $next_page ) {
			$prev_page = FrmAppHelper::get_post_param( 'frm_page_order_' . $form_id, 0, 'absint' );
			if ( ! $prev_page || ( $next_page < $prev_page ) ) {
                $back = true;
            }
        }
        return $back;
    }

	/**
	 * @since 2.0.8
	 * @return boolean
	 */
	public static function going_to_next( $form_id ) {
		$next_page = FrmAppHelper::get_post_param( 'frm_page_order_' . $form_id, 0, 'absint' );
		$more_pages = false;

		if ( $next_page ) {
			$more_pages = true;
			$page_breaks = FrmField::get_all_types_in_form( $form_id, 'break' );

			$previous_page = new stdClass();
			$previous_page->field_order = 0;

			foreach ( $page_breaks as $page_break ) {
				if ( $page_break->field_order >= $next_page ) {
					$current_page = apply_filters( 'frm_get_current_page', $previous_page, $page_breaks, false );
					if ( ! is_object( $current_page ) && $current_page == -1 ) {
						unset( $_POST[ 'frm_page_order_' . $form_id ] );
						$more_pages = false;
					}
					break;
				}
				$previous_page = $page_break;
			}
		}

		return $more_pages;
	}

    public static function get_prev_button( $form, $class = '' ) {
        $html = '[if back_button]<input type="submit" value="[back_label]" name="frm_prev_page" formnovalidate="formnovalidate" class="frm_prev_page '. $class .'" [back_hook] />[/if back_button]';
        return self::get_draft_button( $form, $class, $html, 'back_button' );
    }

	/**
	 * check if this entry is currently being saved as a draft
	 */
    public static function &saving_draft() {
		$saving_draft = FrmAppHelper::get_post_param( 'frm_saving_draft', '', 'sanitize_title' );
		$saving = ( $saving_draft == '1' && is_user_logged_in() );
        return $saving;
    }

    public static function save_draft_msg( &$message, $form, $record = false ) {
        if ( ! self::saving_draft() ) {
            return;
        }

        $message = isset($form->options['draft_msg']) ? $form->options['draft_msg'] : __( 'Your draft has been saved.', 'formidable' );
    }

    public static function get_draft_button( $form, $class = '', $html = '', $button_type = 'save_draft' ) {
        if ( empty( $html ) ) {
            $html = '[if save_draft]<input type="submit" value="[draft_label]" name="frm_save_draft" formnovalidate="formnovalidate" class="frm_save_draft '. $class .'" [draft_hook] />[/if save_draft]';
        }

        $html = FrmProFormsController::replace_shortcodes($html, $form);
        if ( strpos( $html, '[if '. $button_type .']') !== false ) {
            $html = preg_replace('/(\[if\s+'. $button_type .'\])(.*?)(\[\/if\s+'. $button_type .'\])/mis', '', $html);
        }
        return $html;
    }

	public static function get_draft_link( $form ) {
        $html = self::get_draft_button($form, '', FrmFormsHelper::get_draft_link());
        return $html;
    }

    public static function is_show_data_field($field) {
        return $field['type'] == 'data' && ( $field['data_type'] == '' || $field['data_type'] == 'data' );
    }

    public static function has_field($type, $form_id, $single = true) {
        if ( $single ) {
            $included = FrmDb::get_var( 'frm_fields', array( 'form_id' => $form_id, 'type' => $type) );
            if ( $included ) {
                $included = FrmField::getOne($included);
            }
        } else {
            $included = FrmField::get_all_types_in_form( $form_id, $type );
        }

        return $included;
    }

    /**
     * @since 2.0
     * @return array of repeatable section fields
     */
    public static function has_repeat_field($form_id, $single = true) {
        $fields = self::has_field('divider', $form_id, $single);
        if ( ! $fields ) {
            return $fields;
        }

        $repeat_fields = array();
        foreach ( $fields as $field ) {
            if ( FrmField::is_repeating_field($field) ) {
                $repeat_fields[] = $field;
            }
        }

        return $repeat_fields;
    }

	/**
	 * @param array $atts - includes form_id, setting_name, and expected_setting
	 * @since 2.0.8
	 */
	public static function has_form_setting( $atts ) {
		$form = FrmForm::getOne( $atts['form_id'] );
		return ( isset( $form->options[ $atts['setting_name'] ] ) && $form->options[ $atts['setting_name'] ] == $atts['expected_setting'] );
	}

    public static function &post_type($form) {
        if ( is_numeric($form) ) {
            $form_id = $form;
        } else {
            $form_id = (array) $form['id'];
        }

		$action = FrmFormAction::get_action_for_form( $form_id, 'wppost' );
        $action = reset( $action );

        if ( ! $action || ! isset($action->post_content['post_type']) ) {
            $type = 'post';
        } else {
            $type = $action->post_content['post_type'];
        }

        return $type;
    }
}
