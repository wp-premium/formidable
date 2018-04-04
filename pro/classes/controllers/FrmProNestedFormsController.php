<?php

class FrmProNestedFormsController {

	/**
	 * Display an embedded form on the current page
	 *
	 * @since 2.02.06
	 *
	 * @param array $field
	 * @param string $field_name
	 * @param array $errors
	 */
	public static function display_front_end_embedded_form( $field, $field_name, $errors ) {
		self::display_front_end_nested_form( $field_name, $field, array(
			'errors' => $errors,
			'repeat' => 0,
		) );
	}

	/**
	 * Display a repeating section on the current page
	 *
	 * @since 2.02.06
	 *
	 * @param array $field
	 * @param string $field_name
	 * @param array $errors
	 */
	public static function display_front_end_repeating_section( $field, $field_name, $errors ) {
		self::display_front_end_nested_form( $field_name, $field, array(
			'errors' => $errors,
			'repeat' => 5,
		) );
	}

	/**
	 * Display an embedded form/repeating section on the current page
	 *
	 * @param array $field
	 * @param string $field_name
	 * @param array $args
	 */
	public static function display_front_end_nested_form( $field_name, $field, $args = array() ) {
		if ( ! is_numeric( $field['form_select'] ) ) {
			return;
		}

		$defaults = array(
			'repeat' => 0,
			'errors' => array(),
		);

		$args = wp_parse_args( $args, $defaults );

		$subform = FrmForm::getOne( $field['form_select'] );
		if ( empty( $subform ) ) {
			return;
		}

		$subfields = FrmField::get_all_for_form( $field['form_select'] );

		self::insert_basic_hidden_field( $field_name . '[form]', $field['form_select'], '' );

		if ( empty( $subfields ) ) {
			return;
		}

		$repeat_atts = array(
			'form'                    => $subform,
			'fields'                  => $subfields,
			'errors'                  => $args['errors'],
			'parent_field'            => $field,
			'repeat'                  => $args['repeat'],
			'field_name'              => $field_name,
			'is_repeat_limit_reached' => self::is_repeat_limit_reached_for_field( $field ),
		);

		if ( empty( $field['value'] ) ) {
			// Row count must be zero if field value is empty
			$start_rows = apply_filters( 'frm_repeat_start_rows', 1, $field );

			for ( $i = 0, $j = $start_rows; $i < $j; $i ++ ) {
				// add an empty sub entry
				$repeat_atts['row_count'] = $repeat_atts['i'] = $i;
				self::display_single_iteration_of_nested_form( $field_name, $repeat_atts );
			}

			return;
		}

		$row_count = 0;
		foreach ( (array) $field['value'] as $k => $checked ) {
			$repeat_atts['i']     = $k;
			$repeat_atts['value'] = '';

			if ( ! isset( $field['value']['form'] ) ) {
				// this is not a posted value from moving between pages
				$checked = apply_filters( 'frm_hidden_value', $checked, $field );
				if ( empty( $checked ) || ! is_numeric( $checked ) ) {
					continue;
				}

				$repeat_atts['i']        = 'i' . $checked;
				$repeat_atts['entry_id'] = $checked;
				$repeat_atts['value']    = $checked;
			} else if ( $k === 'form' || $k === 'row_ids' ) {
				continue;
			} else if ( strpos( $k, 'i' ) === 0 ) {
				// include the entry id when values are posted
				$repeat_atts['entry_id'] = absint( str_replace( 'i', '', $k ) );
			}

			// Keep track of row count
			$repeat_atts['row_count'] = $row_count;
			$row_count ++;

			// show each existing sub entry
			self::display_single_iteration_of_nested_form( $field_name, $repeat_atts );
			unset( $k, $checked );
		}

		unset( $subform, $subfields );
	}

	/**
	 * Check if repeat limit is reached for a section field
	 *
	 * @since 2.05
	 *
	 * @param array $field
	 *
	 * @return bool
	 */
	private static function is_repeat_limit_reached_for_field( $field ) {
		$is_repeat_limit_reached = false;

		if ( isset( $field['repeat_limit'] ) && ( $field['repeat_limit'] !== '' ) ) {

			if ( empty( $field['value'] ) ) {
				$row_count = 1;
			} else if ( isset( $field['value']['row_ids'] ) ) {
				if ( is_array( $field['value']['row_ids'] ) ) {
					$row_count = count( $field['value']['row_ids'] );
				} else {
					$row_count = 1;
				}
			} else {
				// When editing an entry, the value will be an array of child IDs on initial load
				$row_count = count( $field['value'] );
			}

			$is_repeat_limit_reached = self::is_repeat_limit_reached( $field['repeat_limit'], $row_count );
		}

		return $is_repeat_limit_reached;
	}

	/**
	 * Add a repeating section row with ajax
	 */
	public static function ajax_add_repeat_row() {
		$field_id = absint( $_POST['field_id'] );
		if ( ! $field_id ) {
			wp_die();
		}

		$row_count    = absint( $_POST['numberOfSections'] );
		$field        = FrmField::getOne( $field_id );
		$repeat_limit = absint( FrmField::get_option_in_object( $field, 'repeat_limit' ) );

		$args = array(
			'i'            => absint( $_POST['i'] ),
			'parent_field' => $field->id,
			'form'         => ( isset( $field->field_options['form_select'] ) ? $field->field_options['form_select'] : 0 ),
			'repeat'       => 1,
		);

		$field_name = 'item_meta[' . $args['parent_field'] . ']';

		if ( self::is_repeat_limit_reached( $repeat_limit, $row_count ) ) {
			echo json_encode( array() );
			wp_die();
		}

		// let's show a textarea since the ajax with multiple rte doesn't work well in WP right now
		global $frm_vars;
		$frm_vars['skip_rte'] = true;

		$response = array(
			'is_repeat_limit_reached' => self::is_repeat_limit_reached( $repeat_limit, $row_count + 1 ),
		);

		ob_start();
		self::display_single_iteration_of_nested_form( $field_name, $args );
		$response['html'] = ob_get_contents();
		ob_end_clean();

		echo json_encode( $response );
		wp_die();
	}

	/**
	 * Check if the repeat limit is reached
	 *
	 * @since 2.05
	 *
	 * @param int $repeat_limit
	 * @param int $row_count
	 *
	 * @return bool
	 */
	private static function is_repeat_limit_reached( $repeat_limit, $row_count ) {
		return $repeat_limit && $row_count >= $repeat_limit;
	}

	/**
	 * Load JavaScript for hidden subfields
	 * Applies to repeating sections and embed form fields
	 * TODO: clean this up, maybe don't remove child fields from field array when they're on a different page
	 *
	 * @since 2.01.0
	 *
	 * @param array $field
	 */
	public static function load_hidden_sub_field_javascript( $field ) {
		if ( self::is_hidden_nested_form_field( $field ) ) {

			$child_fields = FrmField::get_all_for_form( $field['form_select'] );
			foreach ( $child_fields as $child_field_obj ) {
				$child_field = FrmProFieldsHelper::convert_field_object_to_flat_array( $child_field_obj );

				$child_field['original_type']  = $child_field['type'];
				$child_field['type']           = 'hidden';
				$child_field['parent_form_id'] = $field['form_id'];
				if ( ! isset( $child_field['value'] ) ) {
					$child_field['value'] = '';
				}

				if ( $field['original_type'] == 'form' ) {
					// This is needed when field script is loaded through hidden sub fields.
					$child_field['in_embed_form'] = $field['id'];
				}

				FrmProFieldsHelper::add_field_javascript( $child_field );
			}
		}
	}

	/**
	 * Format the saved value in nested form fields when value is not posted
	 * This will only be used on initial form load when editing an entry
	 *
	 * @since 2.0
	 *
	 * @param array $field
	 */
	public static function format_saved_values_for_hidden_nested_forms( &$field ) {
		$is_hidden_nested_form_field_with_saved_value = ( self::is_hidden_nested_form_field( $field ) &&
			! isset( $field['value']['form'] ) && ! empty( $field['value'] ) );

		if ( ! $is_hidden_nested_form_field_with_saved_value || ! is_numeric( $field['form_select'] ) ) {
			return;
		}

		// Begin formatting field value
		$field['value'] = array(
			'form'    => $field['form_select'],
			'row_ids' => self::format_entry_ids_for_row_ids( $field['value'] ),
		);

		// Get child fields
		$child_fields = FrmField::get_all_for_form( $field['form_select'] );

		// Loop through children and entries to get values
		foreach ( $field['value']['row_ids'] as $row_id ) {
			$entry_id = str_replace( 'i', '', $row_id );
			$field['value'][ $row_id ] = array( 0 => '' );
			$entry = FrmEntry::getOne( $entry_id, true );
			foreach ( $child_fields as $child ) {
				$field['value'][ $row_id ][ $child->id ] = isset( $entry->metas[ $child->id ] ) ? $entry->metas[ $child->id ] : '';

				if ( $child->type == 'date' ) {
					$current_value = $field['value'][ $row_id ][ $child->id ];
					$field['value'][ $row_id ][ $child->id ] = FrmProAppHelper::maybe_convert_from_db_date( $current_value );
				}
			}
		}
	}

	/**
	 * Check if a field is a hidden repeating section/embedded form
	 *
	 * @since 2.02.06
	 *
	 * @param $field
	 *
	 * @return bool
	 */
	public static function is_hidden_nested_form_field( $field ) {
		$is_hidden_nested_form_field = false;
		if ( isset( $field['original_type'] ) && $field['type'] == 'hidden' ) {
			if ( $field['original_type'] == 'form' ) {
				$is_hidden_nested_form_field = true;
			} else if ( $field['original_type'] == 'divider' && $field['repeat'] ) {
				$is_hidden_nested_form_field = true;
			}
		}

		return $is_hidden_nested_form_field;
	}

	/**
	 * Insert the fields and JavaScript for a hidden nested form
	 *
	 * @since 2.02.06
	 *
	 * @param array $field
	 * @param string $field_name
	 * @param array|string $field_value
	 */
	public static function insert_hidden_nested_form( $field, $field_name, $field_value ) {
		self::load_hidden_sub_field_javascript( $field );
		self::insert_hidden_nested_form_fields( $field, $field_name, $field_value );
	}

	/**
	 * Insert the fields in a hidden nested form
	 *
	 * @since 2.02.06
	 *
	 * @param array $field
	 * @param string $field_name
	 * @param array|string $value_array
	 */
	private static function insert_hidden_nested_form_fields( $field, $field_name, $value_array ) {
		if ( ! is_array( $value_array ) ) {
			self::insert_basic_hidden_field( $field_name, '', $field['html_id'] );

			return;
		}

		foreach ( $value_array as $key => $value ) {

			if ( $key === 'form' ) {
				self::insert_basic_hidden_field( $field_name . '[' . $key . ']', $value, '' );
			} else if ( $key === 'row_ids' ) {
				self::insert_hidden_row_id_inputs( $field, $value );
			} else {
				self::insert_hidden_sub_field_inputs( $field, $field_name . '[' . $key . ']', $value, $key );
			}
		}
	}

	/**
	 * Insert the row_ids input for a nested form
	 *
	 * @since 2.02.06
	 *
	 * @param array $field
	 * @param array|string $value
	 */
	private static function insert_hidden_row_id_inputs( $field, $value ) {
		if ( ! is_array( $value ) ) {
			$value = array( 0 );
		}

		$name = 'item_meta[' . $field['id'] . '][row_ids][]';
		foreach ( $value as $row_id ) {
			self::insert_basic_hidden_field( $name, $row_id, '' );
		}
	}

	/**
	 * Insert the sub fields in a hidden nested form
	 *
	 * @since 2.02.06
	 *
	 * @param array $field
	 * @param string $field_name
	 * @param array|string $value
	 * @param string $value_key
	 */
	private static function insert_hidden_sub_field_inputs( $field, $field_name, $value, $value_key ) {

		if ( is_array( $value ) ) {

			foreach ( $value as $k => $checked2 ) {
				$checked2 = apply_filters( 'frm_hidden_value', $checked2, $field );
				self::insert_hidden_sub_field_inputs( $field, $field_name . '[' . $k . ']', $checked2, $k );
			}
			unset( $k, $checked2 );

		} else {

			$html_id = self::get_html_id_for_hidden_sub_fields( $field_name, $value_key, $field['html_id'] );
			self::insert_basic_hidden_field( $field_name, $value, $html_id );
		}
	}

	/**
	 * Insert a basic hidden field
	 *
	 * @since 2.02.06
	 *
	 * @param string $name
	 * @param string $value
	 * @param string $id
	 */
	private static function insert_basic_hidden_field( $name, $value, $id ) {
		if ( strpos( $name, '[form]' ) !== false ) {
			$class = 'frm_dnc';
		} else {
			$class = '';
		}

		if ( $id ) {
			?><input type="hidden" name="<?php echo esc_attr( $name ) ?>" id="<?php echo esc_attr( $id ) ?>" value="<?php echo esc_attr( $value ) ?>" />
			<?php
		} else {
			?><input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" class="<?php echo esc_attr( $class ); ?>" />
			<?php
		}
	}

	/**
	 * Make sure fields in a hidden repeating section get the correct HTML ids
	 * Their HTML ID should be the same when hidden or visible to work with calculations and logic
	 *
	 * @since 2.02.06
	 *
	 * @param string $field_name
	 * @param string $opt_key
	 * @param string $html_id
	 *
	 * @return string
	 */
	private static function get_html_id_for_hidden_sub_fields( $field_name, $opt_key, $html_id ) {
		$parts = explode( '][', $field_name . '[' );

		if ( count( $parts ) > 2 ) {
			if ( $parts[2] === 'other' ) {
				$html_id = self::get_html_id_for_hidden_other_fields( $parts, $opt_key, $html_id );
			} else {
				$field_id = absint( $parts[2] );

				if ( $field_id === 0 ) {
					$html_id = '';
				} else {
					$field_key = FrmField::get_key_by_id( $field_id );
					if ( $field_key ) {
						$html_id = 'field_' . $field_key . '-' . $parts[1];

						// allow for a multi-dimensional array for the ids
						if ( isset( $parts[3] ) && $parts[3] != '' ) {
							$html_id .= '-' . $parts[3];
						}
					}
				}
			}
		}

		return $html_id;
	}


	/**
	 * Get the HTML ID for hidden other fields inside of repeating sections when value is posted
	 *
	 * @since 2.0.8
	 *
	 * @param array $parts (array of the field name)
	 * @param string|boolean $opt_key
	 * @param string $html_id
	 *
	 * @return string
	 */
	private static function get_html_id_for_hidden_other_fields( $parts, $opt_key, $html_id ) {
		$field_id  = absint( $parts[3] );
		$field_key = FrmField::get_key_by_id( $field_id );

		if ( $field_key ) {
			$html_id = 'field_' . $field_key . '-' . $parts[1];

			// If checkbox field or multi-select dropdown
			if ( $opt_key && FrmFieldsHelper::is_other_opt( $opt_key ) ) {
				$html_id .= '-' . $opt_key . '-otext';
			} else {
				$html_id .= '-otext';
			}
		}

		return $html_id;
	}

	/**
	 * Convert entry IDs to row IDs
	 *
	 * @since 2.02.06
	 *
	 * @param array|string $entry_ids
	 *
	 * @return array
	 */
	private static function format_entry_ids_for_row_ids( $entry_ids ) {
		$row_ids = array();
		foreach ( (array) $entry_ids as $entry_id ) {
			if ( $entry_id ) {
				$row_ids[] = 'i' . $entry_id;
			}
		}

		return $row_ids;
	}

	/**
	 * Display a single iteration of a nested form
	 *
	 * @since 2.02.06
	 *
	 * @param string $field_name
	 * @param array $args
	 */
	private static function display_single_iteration_of_nested_form( $field_name, $args = array() ) {
		$defaults = array(
			'i'                       => 0,
			'entry_id'                => false,
			'form'                    => false,
			'fields'                  => array(),
			'errors'                  => array(),
			'parent_field'            => 0,
			'repeat'                  => 0,
			'row_count'               => false,
			'value'                   => '',
			'field_name'              => '',
			'is_repeat_limit_reached' => false,
		);
		$args = wp_parse_args( $args, $defaults );

		if ( empty( $args['parent_field'] ) ) {
			return;
		}

		if ( is_numeric( $args['parent_field'] ) ) {
			$args['parent_field'] = (array) FrmField::getOne( $args['parent_field'] );
			$args['parent_field']['format'] = isset( $args['parent_field']['field_options']['format'] ) ? $args['parent_field']['field_options']['format'] : '';
		}

		FrmForm::maybe_get_form( $args['form'] );

		if ( empty( $args['fields'] ) ) {
			$args['fields'] = FrmField::get_all_for_form( $args['form']->id );
		}

		$values = array();

		if ( $args['fields'] ) {
			$pass_args = array(
				'parent_form_id'  => $args['parent_field']['form_id'],
				'parent_field_id' => $args['parent_field']['id'],
				'key_pointer'     => $args['i'],
				'repeating'       => true,
				'fields'          => $args['fields'],
				'in_embed_form'   => $args['parent_field']['type'] == 'form' ? $args['parent_field']['id'] : '0',
			);

			// Find the connection between here and where logic is packaged

			if ( empty( $args['entry_id'] ) ) {
				$just_created_entry = FrmFormsController::just_created_entry( $args['parent_field']['form_id'] );
				$values = FrmEntriesHelper::setup_new_vars( $args['fields'], $args['form'], $just_created_entry, $pass_args );
			} else {
				$entry = FrmEntry::getOne( $args['entry_id'], true );
				if ( $entry && $entry->form_id == $args['form']->id ) {
					$values = FrmProEntriesController::setup_entry_values_for_editing( $entry, $pass_args );
				} else {
					return;
				}
			}
		}

		$format = isset( $args['parent_field']['format'] ) ? $args['parent_field']['format'] : '';
		$end    = false;
		$count  = 0;
		foreach ( $values['fields'] as $subfield ) {
			if ( 'end_divider' == $subfield['type'] ) {
				$end = $subfield;
			} else if ( ! in_array( $subfield['type'], array( 'hidden', 'user_id' ) ) ) {
				if ( isset( $subfield['conf_field'] ) && $subfield['conf_field'] ) {
					$count = $count + 2;
				} else {
					$count ++;
				}
			}
			unset( $subfield );
		}
		if ( $args['repeat'] ) {
			$count ++;
		}

		$field_class = self::grid_field_class( $count, $format );
		$section_classes = self::repeat_container_classes( $format, $args );

		echo '<div id="frm_section_' . $args['parent_field']['id'] . '-' . $args['i'] . '" class="' . esc_attr( $section_classes ) . '">' . "\n";

		self::add_hidden_repeat_row_id( $args );
		self::add_default_item_meta_field( $args );

		$label_pos = 'top';
		$field_num = 1;
		foreach ( $values['fields'] as $subfield ) {
			$subfield_name    = $field_name . '[' . $args['i'] . '][' . $subfield['id'] . ']';
			$subfield_plus_id = '-' . $args['i'];
			$subfield_id      = $subfield['id'] . '-' . $args['parent_field']['id'] . $subfield_plus_id;

			if ( $args['parent_field'] && ! empty( $args['parent_field']['value'] ) && isset( $args['parent_field']['value']['form'] ) && isset( $args['parent_field']['value'][ $args['i'] ] ) && isset( $args['parent_field']['value'][ $args['i'] ][ $subfield['id'] ] ) ) {
				// this is a posted value from moving between pages, so set the POSTed value
				$subfield['value'] = $args['parent_field']['value'][ $args['i'] ][ $subfield['id'] ];
			}

			if ( ! empty( $field_class ) ) {
				if ( 1 == $field_num ) {
					$subfield['classes'] .= ' frm_first';
				}
				self::add_class_to_field( $field_class, 'field', $subfield['classes'] );
			}

			$field_num ++;

			if ( 'top' == $label_pos && in_array( $subfield['label'], array( 'top', 'hidden', '' ) ) ) {
				// add placeholder label if repeating
				$label_pos = 'hidden';
			}

			// Track whether field is in an embedded form
			if ( 'form' == $args['parent_field']['type'] ) {
				// TODO: Check if this is needed
				$subfield['in_embed_form'] = $args['parent_field']['id'];
			}

			$field_args = array(
				'field_name'    => $subfield_name,
				'field_id'      => $subfield_id,
				'field_plus_id' => $subfield_plus_id,
				'section_id'    => $args['parent_field']['id'],
				'errors'        => $args['errors'],
				'form'          => $args['form'],
				'parent_form_id' => $args['parent_field']['form_id'],
			);
			$field_obj = FrmFieldFactory::get_field_type( $subfield['type'], $subfield );
			$field_obj->show_field( $field_args );

			unset( $subfield_name, $subfield_id );
		}

		if ( ! $args['repeat'] ) {
			// Close frm_repeat div
			echo '</div>' . "\n";

			return;
		}

		$args['format']      = $format;
		$args['label_pos']   = $label_pos;
		$args['field_class'] = $field_class;
		echo self::get_repeat_buttons( $args, $end );

		// Close frm_repeat div
		echo '</div>' . "\n";
	}

	/**
	 * @since 3.0
	 *
	 * @return string|array
	 */
	private static function grid_field_class( $count, $format ) {
		if ( empty( $format ) ) {
			return '';
		}

		$frm_settings = FrmAppHelper::get_settings();
		if ( $frm_settings->old_css ) {
			$classes = array(
				2 => '_half',
				3 => '_third',
				4 => '_fourth',
				5 => '_fifth',
				6 => '_sixth',
				7 => '_seventh',
				8 => '_eighth',
			);
			$class = ( isset( $classes[ $count ] ) ) ? $classes[ $count ] : '';
		} else {
			if ( 2 == $count ) {
				$class = array( 10, 2 );
			} elseif ( $count < 13 ) {
				$field_width = floor( 12 / ( $count ) );
				$submit_width = 12 - ( $field_width * ( $count - 1 ) );
				$class = array( $field_width, $submit_width );
			} else {
				$class = '';
			}
		}

		return $class;
	}

	private static function add_class_to_field( $add_class, $type, &$classes ) {
		if ( is_array( $add_class ) ) {
			$position = 'button' === $type ? 1 : 0;
			$classes .= ' frm' . $add_class[ $position ];
		} else {
			$classes .= ' frm' . $add_class;
		}
	}

	/**
	 * @since 3.0
	 *
	 * @return string
	 */
	private static function repeat_container_classes( $format, $args ) {
		$section_classes = array(
			'frm_repeat_' . ( empty( $format ) ? 'sec' : $format ),
			'frm_repeat_' . $args['parent_field']['id'] . ( $args['row_count'] === 0 ? ' frm_first_repeat' : '' ),
			'frm_grid_container',
		);
		return implode( ' ', $section_classes );
	}

	/**
	 * Adds the row_ids to a nested form on the current page
	 *
	 * @since 2.02.06
	 *
	 * @param $args
	 */
	private static function add_hidden_repeat_row_id( $args ) {
		echo '<input type="hidden" name="item_meta[' . esc_attr( $args['parent_field']['id'] ) . '][row_ids][]" value="' . esc_attr( $args['i'] ) . '" />';
	}

	/**
	 * Add item meta to each row in repeating section or embedded form so the entry is always validated
	 *
	 * @since 2.0.08
	 *
	 * @param array $args
	 */
	private static function add_default_item_meta_field( $args ) {
		echo '<input type="hidden" name="item_meta[' . $args['parent_field']['id'] . '][' . $args['i'] . '][0]" value="" />';
	}

	/**
	 * Get the HTML for repeat buttons
	 *
	 * @param array $args
	 * @param bool $end
	 *
	 * @return mixed|void
	 */
	private static function get_repeat_buttons( $args, $end = false ) {
		$args['end_format'] = 'icon';

		if ( ! $end ) {
			$end = self::get_end_repeat_field( $args );
		}

		if ( $end ) {
			$args['add_label']    = $end['add_label'];
			$args['remove_label'] = $end['remove_label'];

			if ( ! empty( $end['format'] ) ) {
				$args['end_format'] = $end['format'];
			}
		}

		$triggers = self::repeat_button_html( $args, $end );

		return apply_filters( 'frm_repeat_triggers', $triggers, $end, $args['parent_field'], $args['field_class'] );
	}

	/**
	 * Get the end divider field for a repeating section
	 *
	 * @param array $args
	 *
	 * @return mixed|void
	 */
	private static function get_end_repeat_field( $args ) {
		$query       = array(
			'fi.form_id'    => $args['parent_field']['form_id'],
			'type'          => 'end_divider',
			'field_order >' => $args['parent_field']['field_order'] + 1
		);
		$end_field   = FrmField::getAll( $query, 'field_order', 1 );
		$field_array = FrmProFieldsHelper::initialize_array_field( $end_field );

		foreach ( array( 'format', 'add_label', 'remove_label', 'classes' ) as $o ) {
			if ( isset( $end_field->field_options[ $o ] ) ) {
				$field_array[ $o ] = $end_field->field_options[ $o ];
			}
		}

		FrmFieldsHelper::prepare_new_front_field( $field_array, $end_field );

		return $field_array;
	}

	/**
	 * Get the HTML for the repeat buttons
	 *
	 * @param array $args
	 * @param array $end
	 *
	 * @return string
	 */
	private static function repeat_button_html( $args, $end ) {

		$defaults = array(
			'add_icon'                => '',
			'remove_icon'             => '',
			'add_label'               => __( 'Add', 'formidable' ),
			'remove_label'            => __( 'Remove', 'formidable' ),
			'add_classes'             => ' frm_button',
			'remove_classes'          => ' frm_button',
			'is_repeat_limit_reached' => false,
		);

		$args = wp_parse_args( $args, $defaults );

		if ( ! isset( $args['end_format'] ) && isset( $args['format'] ) ) {
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

		if ( $args['is_repeat_limit_reached'] ) {
			$args['add_classes'] .= ' frm_hide_add_button';
		}

		$classes = 'frm_form_field frm_' . $args['label_pos'] . '_container frm_repeat_buttons';

		self::add_class_to_field( $args['field_class'], 'button', $classes );

		// Get classes for end divider
		$classes .= ( $end && isset( $end['classes'] ) ) ? ' ' . $end['classes'] : '';

		$triggers = '<div class="' . esc_attr( $classes ) . '">';

		if ( 'hidden' == $args['label_pos'] && ! empty( $args['format'] ) ) {
			$triggers .= '<label class="frm_primary_label">&nbsp;</label>';
		}

		$triggers .= '<a href="#" class="frm_add_form_row' . esc_attr( $args['add_classes'] ) . '" data-parent="' . esc_attr( $args['parent_field']['id'] ) . '" aria-label="' . esc_attr( $defaults['add_label'] ) . '">' . $args['add_icon'] . $args['add_label'] . '</a>' . "\n";
		$triggers .= '<a href="#" class="frm_remove_form_row' . esc_attr( $args['remove_classes'] ) . '" data-key="' . esc_attr( $args['i'] ) . '" data-parent="' . esc_attr( $args['parent_field']['id'] ) . '" aria-label="' . esc_attr( $defaults['remove_label'] ) . '">' . $args['remove_icon'] . $args['remove_label'] . '</a> ';

		$triggers .= '</div>';

		return $triggers;
	}
}
