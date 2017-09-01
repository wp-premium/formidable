<?php

class FrmProLookupFieldsController{

	/**
	 * Get the data types for Lookup fields
	 *
	 * @return array $data_types
	 */
	public static function get_lookup_field_data_types(){
		$data_types = array(
			'select'    => __( 'Dropdown', 'formidable' ),
			'radio'     => __( 'Radio Buttons', 'formidable' ),
			'checkbox'  => __( 'Checkboxes', 'formidable' ),
			'text'      => __( 'Single Line Text', 'formidable' ),
		);
		return $data_types;
	}

	/**
	 * Get the data types for Lookup fields, formatted for Insert Field tab
	 *
	 * @return array $lookup_display_options
	 */
	public static function get_lookup_options_for_insert_fields_tab(){
		$lookup_display_options = array(
			'name'  => __( 'Lookup', 'formidable' ),
			'types' => self::get_lookup_field_data_types()
		);
		return $lookup_display_options;
	}

	/**
	 * Add field options specific to Lookup Fields
	 * Used on front and back end. Either $values or $field could be false :/
	 *
	 * @since 2.01.0
	 * @param array $values
	 * @param object $field
	 * @param array $opts
	 */
	public static function add_field_options_specific_to_lookup_field( $values, $field, &$opts ) {
		if ( $field ) {
			$field_type = isset( $field->field_options['original_type'] ) ? $field->field_options['original_type'] : $field->type;
		} else {
			$field_type = $values['type'];
		}

		if ( $field_type == 'lookup' ) {
			$opts['watch_lookup'] = array();
			$opts['get_values_form'] = '';
			$opts['get_values_field'] = '';
			$opts['lookup_filter_current_user'] = false;
			$opts['lookup_placeholder_text'] = '';
			$opts['lookup_option_order'] = 'ascending';
		}
	}

	/**
	 * Clean a Lookup field's options before updating in the database
	 * Necessary when switching from another field type to a Lookup
	 *
	 * @since 2.01.02
	 * @param array $values
	 * @return array $values
	 */
	public static function clean_field_options_before_update( $values ) {
		if ( $values['type'] == 'lookup' ) {

			if ( isset( $values['field_options']['autopopulate_value'] ) ) {
				unset( $values['field_options']['autopopulate_value'] );
			}

			if ( ! empty( $values['options'] ) ) {
				$values['options'] = array();
			}

		}

		return $values;
	}

	/**
	 * Add Autopopulate Values options for certain field types
	 * Used on front and back end. Either $values or $field could be false :/
	 *
	 * @since 2.01.0
	 * @param array $values
	 * @param object $field
	 * @param array $opts
	 */
	public static function add_autopopulate_value_field_options( $values, $field, &$opts ) {
		if ( $field ) {
			$field_type = isset( $field->field_options['original_type'] ) ? $field->field_options['original_type'] : $field->type;
		} else {
			$field_type = $values['type'];
		}

		$autopopulate_field_types = self::get_autopopulate_field_types();
		if ( in_array( $field_type, $autopopulate_field_types ) ) {
			$opts['autopopulate_value'] = false;
			$opts['get_values_form'] = '';
			$opts['get_values_field'] = '';
			$opts['watch_lookup'] = array();
			$opts['get_most_recent_value'] = '';
			$opts['lookup_filter_current_user'] = false;
		}
	}

	/**
	 * Get the field types that should have the "Autopopulate Value" section
	 *
	 * @since 2.01.02
	 * @return array
	 */
	public static function get_autopopulate_field_types() {
		$autopopulate_field_types = array(
			'text',
			'email',
			'url',
			'image',
			'time',
			'user_id',
			'number',
			'phone',
			'date',
			'select',
			'hidden',
			'textarea',
		);

		return $autopopulate_field_types;
	}

	/**
	 * Add some of the standard field options to Lookup fields
	 *
	 * @since 2.01.0
	 * @return array $add_options
	 */
	public static function add_standard_field_options() {
		$add_options = array(
			'read_only' => true,
			'unique' => true
		);

		return $add_options;
	}

	/**
	 * Show the 'Get options from' settings above a lookup field's Field Options
	 *
	 * @since 2.01.0
	 * @param array $field
	 */
	public static function show_get_options_from_above_field_options( $field ) {
		$lookup_args = self::get_args_for_get_options_from_setting( $field );
		if ( $field['data_type'] == 'text' ) {
			$opt_label = __( 'Search values from', 'formidable' );
		} else {
			$opt_label = __( 'Get options from', 'formidable' );
		}

		require( FrmAppHelper::plugin_path() .'/pro/classes/views/lookup-fields/back-end/top-options.php' );
	}

	/**
	 * Show the field options specific to lookup fields for the form builder page
	 *
	 * @since 2.01.0
	 * @param array $field
	 */
	public static function show_lookup_field_options_in_form_builder( $field ) {
		// Display as
		$lookup_args['data_types'] = self::get_lookup_field_data_types();
		require( FrmAppHelper::plugin_path() . '/pro/classes/views/lookup-fields/back-end/display-as.php' );

		if ( $field['data_type'] == 'text' ) {
			// Field size
			$display_max = true;
			require( FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/pixels-wide.php' );

			// Filter options
			require( FrmAppHelper::plugin_path() . '/pro/classes/views/lookup-fields/back-end/filter.php' );

			FrmProFieldsController::show_format_option( $field );
		} else {

			if ( $field['data_type'] == 'select' ) {
				// Automatic width
				require( FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/automatic-width.php' );

				// Placeholder text
				require( FrmAppHelper::plugin_path() . '/pro/classes/views/lookup-fields/back-end/placeholder.php' );
			}

			if ( in_array( $field['data_type'], array( 'checkbox', 'radio' ) ) ) {
				// Alignment
				require( FrmAppHelper::plugin_path() .'/pro/classes/views/frmpro-fields/back-end/alignment.php' );
			}

			// Watch Lookup Fields
			$lookup_fields = self::get_lookup_fields_for_watch_row( $field );
			$field['watch_lookup'] = array_filter( $field['watch_lookup'] );
			require( FrmAppHelper::plugin_path() . '/pro/classes/views/lookup-fields/back-end/watch.php' );

			// Filter options
			require( FrmAppHelper::plugin_path() . '/pro/classes/views/lookup-fields/back-end/filter.php' );

			// Option Order
			require( FrmAppHelper::plugin_path() . '/pro/classes/views/lookup-fields/back-end/order.php' );

			// Dynamic Default Value
			require( FrmAppHelper::plugin_path() . '/pro/classes/views/frmpro-fields/back-end/dynamic-default-value.php' );
		}

		FrmProFieldsController::show_visibility_option( $field );
		FrmProFieldsController::show_conditional_logic_option( $field );
	}

	/**
	 * Show the "Autopopulate Value" option/section in the form builder
	 *
	 * @since 2.01.0
	 * @param array $field
	 */
	public static function show_autopopulate_value_section_in_form_builder( $field ) {
		$lookup_args = self::get_args_for_get_options_from_setting( $field );
		$lookup_fields = self::get_lookup_fields_for_watch_row( $field );

		require( FrmAppHelper::plugin_path() .'/pro/classes/views/frmpro-fields/back-end/autopopulate-values.php' );
	}

	/**
	 * Get the form_list and form_fields for the Get Values From/Get Options From option
	 *
	 * @since 2.01.0
	 * @param array $field
	 * @return array $lookup_args
	 */
	private static function get_args_for_get_options_from_setting( $field ) {
		$lookup_args = array();

		// Get all forms for the -select form- option
		$lookup_args['form_list'] = FrmForm::get_published_forms();

		if ( is_numeric( $field['get_values_form'] ) ) {
			$lookup_args['form_fields'] = self::get_fields_for_get_values_field_dropdown( $field['get_values_form'], $field['type'] );

		} else {
			$lookup_args['form_fields'] = array();
		}

		return $lookup_args;
	}

	/**
	 * Get the fields fot the get_values_field dropdown
	 *
	 * @since 2.01.0
	 *
	 * @param int $form_id
	 * @param string $field_type
	 * @return array $form_fields
	 */
	private static function get_fields_for_get_values_field_dropdown( $form_id, $field_type ) {
		if ( in_array( $field_type, array( 'lookup', 'text', 'hidden' ) ) ) {
			$form_fields = FrmField::get_all_for_form( $form_id );
		} else {
			$where = array( 'type' => $field_type );
			$where[] = array( 'or' => 1, 'fi.form_id' => $form_id, 'fr.parent_form_id' => $form_id );

			$form_fields = FrmField::getAll( $where );
		}

		return $form_fields;
	}

	/**
	 * Get the lookup fields that will appear in the "Watch" option
	 *
	 * @since 2.01.0
	 * @param array $field
	 * @return array $lookup_fields
	 */
	private static function get_lookup_fields_for_watch_row( $field ) {
		$parent_form_id = isset( $field['parent_form_id'] ) ? $field['parent_form_id'] : $field['form_id'];
		$lookup_fields = self::get_limited_lookup_fields_in_form( $parent_form_id, $field['form_id'] );
		return $lookup_fields;
	}

	/**
	 * Get the dropdown options for the "Get Options/Value From" Field option
	 *
	 * @since 2.01.0
	 */
	public static function ajax_get_options_for_get_values_field(){
		check_ajax_referer( 'frm_ajax', 'nonce' );

		$form_id = FrmAppHelper::get_post_param( 'form_id', '', 'absint' );
		$field_type = FrmAppHelper::get_post_param( 'field_type', '', 'sanitize_text_field');
		$fields = self::get_fields_for_get_values_field_dropdown( $form_id, $field_type );

		self::show_options_for_get_values_field( $fields );
		wp_die();
	}

	/**
	 * Show the dropdown options for the "Get Options/Values From" Field option
	 *
	 * @since 2.01.0
	 * @param array $form_fields
	 * @param array $field ($field is not empty on page load)
	 */
	private static function show_options_for_get_values_field( $form_fields, $field = array() ) {
		$select_field_text = __( '&mdash; Select Field &mdash;', 'formidable' );
		echo '<option value="">' . esc_html( $select_field_text ) . '</option>';

		$selected_value = empty( $field ) ? '' : $field['get_values_field'];

		foreach ( $form_fields as $field_option ) {
			if ( FrmField::is_no_save_field( $field_option->type ) ) {
				continue;
			}

			$field_name = FrmAppHelper::truncate( $field_option->name, 30 );
			echo '<option value="' . esc_attr( $field_option->id ) . '"' . selected( $selected_value, $field_option->id ) . '>' . esc_html( $field_name ) . '</option>';
		}
	}

	/**
	 * Show a Lookup Field on the form builder page
	 *
	 * @since 2.01.0
	 * @param array $field
	 */
	public static function show_lookup_field_input_on_form_builder( $field ) {
		if ( $field['data_type'] == 'text' ) {
			// Set up variable for clear_on_focus
			$display = array(
				'clear_on_focus' => true,
				'default_blank' => true
			);

			// Set up width string
			if ( FrmField::is_option_true( $field, 'size' ) ) {
				$width_string = ' style="width:'. $field['size'] . ( is_numeric( $field['size'] ) ? 'px' : '' ) . ';"';
			} else {
				$width_string = '';
			}
		} else {
			// Get the field options
			$field['options'] = self::get_initial_lookup_field_options( $field );
		}

		// Generate field name and HTML id
		$field_name = 'item_meta['. $field['id'] .']';
		if ( 'checkbox' == $field['data_type'] ) {
			$field_name .= '[]';
		}
		$html_id = 'field_' . $field['field_key'];

		// Make sure field value is set
		if ( ! isset( $field['value'] ) ) {
			$field['value'] = '';
		}
		$saved_value_array = (array) $field['value'];

		require(FrmAppHelper::plugin_path() .'/pro/classes/views/lookup-fields/back-end/input.php');
	}

	/**
	 * If current field is repeating, get lookup fields in repeating section and parent form
	 * Otherwise, only get lookup fields in parent form
	 *
	 * @since 2.01.0
	 * @param int $parent_form_id
	 * @param int $current_form_id
	 * @return array
	 */
	private static function get_limited_lookup_fields_in_form( $parent_form_id, $current_form_id ) {
		if ( $parent_form_id == $current_form_id ) {
			// If the current field's form ID matches $form_id, only get fields in that form (not embedded or repeating)
			$inc_repeating = 'exclude';
		} else {
			// If current field is repeating, get lookup fields in repeating section and outside of it
			$inc_repeating = 'include';
		}

		$lookup_fields = FrmField::get_all_types_in_form( $parent_form_id, 'lookup', '', $inc_repeating );

		return $lookup_fields;
	}

	/**
	 * Add a "Watch Lookup" row in the field options (when the + or 'Watch Lookup Fields' link is clicked)
	 *
	 * @since 2.01.0
	 */
	public static function add_watch_lookup_row() {
		check_ajax_referer( 'frm_ajax', 'nonce' );

		$row_key = FrmAppHelper::get_post_param( 'row_key', '', 'absint' );
		$field_id = FrmAppHelper::get_post_param( 'field_id', '', 'absint');
		$form_id = FrmAppHelper::get_post_param( 'form_id', '', 'absint' );

		$selected_field = '';
		$current_field = FrmField::getOne( $field_id );// Maybe (for efficiency) change this to a specific database call
		$lookup_fields = self::get_limited_lookup_fields_in_form( $form_id, $current_field->form_id );

		include( FrmAppHelper::plugin_path() .'/pro/classes/views/lookup-fields/back-end/watch-row.php' );
		wp_die();
	}

	/**
	 * Get the values from a Lookup Field for conditional logic
	 *
	 * @since 2.01.0
	 * @param object $field
	 * @return array
	 */
	public static function get_lookup_field_values_for_conditional_logic( $field ) {
		$linked_field_id = isset( $field->field_options['get_values_field'] ) ? $field->field_options['get_values_field'] : '';

		if ( is_numeric( $linked_field_id ) ) {
			$field_array = array(
				'lookup_filter_current_user' => false,
				'lookup_option_order' => $field->field_options['lookup_option_order']
			);
			$all_values = self::get_independent_lookup_field_values( $linked_field_id, $field_array );

			// Only show 300 options
			$all_values = array_slice( $all_values, 0, 300 );
		} else {
			$all_values = array( __( 'No options available: please check this field\'s options', 'formidable' ) );
		}

		return $all_values;
	}

	/**
	 * Get the initial options for a non-text Lookup field on page load
	 * Used on front and back-end
	 *
	 * @since 2.01.0
	 * @param array $values
	 */
	public static function maybe_get_initial_lookup_field_options( &$values ) {
		if ( $values['data_type'] != 'text' ) {
			$values['options'] = self::get_initial_lookup_field_options( $values );
		}
	}

	/**
	 * Get the options for a lookup field on initial page load
	 * Used on front and back-end
	 *
	 * @since 2.01.0
	 * @param array $values
	 * @return array $options
	 */
	private static function get_initial_lookup_field_options( $values ) {
		if ( self::is_lookup_field_independent( $values['watch_lookup'] ) ) {
			$options = self::get_independent_lookup_field_options( $values );

		} else {
			$options = self::get_initial_dependent_lookup_field_options( $values );
		}

		return $options;
	}

	/**
	 * Check if dependent on another lookup field
	 *
	 * @since 2.01.0
	 * @param array $watch_lookup
	 * @return boolean $independent
	 */
	private static function is_lookup_field_independent( $watch_lookup ) {
		$watch_lookup = array_filter( $watch_lookup );
		if ( empty( $watch_lookup ) ) {
			$independent = true;
		} else {
			$independent = false;
		}

		return $independent;
	}

	/**
	 * Get the options for an independent Lookup field
	 *
	 * @since 2.01.01
	 * @param array $values
	 * @return array
	 */
	private static function get_independent_lookup_field_options( $values ) {
		$linked_field_id = $values['get_values_field'];
		if ( ! $linked_field_id ) {
			return array();
		}

		$options = self::get_independent_lookup_field_values( $linked_field_id, $values );

		if ( 'select' == $values['data_type'] ) {
			$default_option = array( $values['lookup_placeholder_text'] );
			$options = array_merge( $default_option, $options );
		}

		return $options;
	}

	/**
	 * Get the meta values for an independent lookup field
	 *
	 * @since 2.01.0
	 * @param int $linked_field_id
	 * @param array $values
	 * @return array $options
	 */
	private static function get_independent_lookup_field_values( $linked_field_id, $values ) {
		$linked_field = FrmField::getOne( $linked_field_id );
		if ( ! $linked_field ) {
			return array();
		}

		$args = array();

		if ( self::need_to_filter_values_for_current_user( $values ) ) {
			$current_user = get_current_user_id();

			// If user isn't logged in, don't display any options
			if ( $current_user === 0 ) {
				return array();
			}

			$args['user_id'] = $current_user;
		}

		if ( FrmAppHelper::is_admin_page( 'formidable' ) ) {
			$args['limit'] = 500;
		}

		$options = FrmProEntryMeta::get_all_metas_for_field( $linked_field, $args );

		$options = self::flatten_and_unserialize_meta_values( $options );
		self::get_unique_values( $options );
		self::order_values( $values['lookup_option_order'], $options );

		return $options;
	}

	/**
	 * Get the initial options for a dependent lookup field
	 *
	 * @since 2.01.01
	 * @param array $values
	 * @return array
	 */
	private static function get_initial_dependent_lookup_field_options( $values ) {
		if ( isset( $values['value'] ) && $values['value'] ) {
			// If editing an entry or switching between pages, add an option for the saved value
			$options = (array) self::decode_html_entities( $values['value'] );
		} else {
			$options = array();
		}

		if ( 'select' == $values['data_type'] ) {
			$placeholder = array( $values['lookup_placeholder_text'] );
			$options = array_merge( $placeholder, $options );
		} else if ( empty( $options ) ) {
			$options[] = '';
		}

		return $options;
	}

	/**
	 * Decode HTML entities recursively
	 *
	 * @since 2.04
	 *
	 * @param mixed $value
	 *
	 * @return array|string
	 */
	private static function decode_html_entities( $value ) {
		// TODO: add single, centralized function to decode entities

		if ( is_array( $value ) ) {
			foreach ( $value as $key => $single_value ) {
				$value[ $key ] = self::decode_html_entities( $single_value );
			}
		} else {
			$value = html_entity_decode( $value );
		}

		return $value;
	}

	/**
	 * Format the global lookup_fields array that will be parsed to JavaScript
	 *
	 * @since 2.01.0
	 * @param array $values
	 */
	public static function setup_lookup_field_js( $values ) {
		// If on form builder, don't set up the script
		if ( FrmAppHelper::is_admin_page('formidable' ) ) {
			return;
		}

		if ( $values['original_type'] == 'lookup' || FrmField::is_option_true( $values, 'autopopulate_value' ) ) {
			global $frm_vars;

			// If the field has already been through this function, leave now
			// This will happen when the are multiple rows in a repeating section on page load
			if ( isset( $frm_vars['lookup_fields'][ $values['id'] ]['fieldId'] ) ) {
				return;
			}

			if ( ! isset( $frm_vars['lookup_fields'] ) ) {
				$frm_vars['lookup_fields'] = array();
			}

			self::maybe_initialize_frm_vars_lookup_fields_for_id( $values['id'], $frm_vars );

			$lookup_parents = array_filter( $values['watch_lookup'] );

			$frm_vars['lookup_fields'][ $values['id'] ]['fieldId'] = $values['id'];
			$frm_vars['lookup_fields'][ $values['id'] ]['fieldKey'] = $values['field_key'];
			$frm_vars['lookup_fields'][ $values['id'] ]['parents'] = $lookup_parents;
			$frm_vars['lookup_fields'][ $values['id'] ]['fieldType'] = $values['original_type'];
			$frm_vars['lookup_fields'][ $values['id'] ]['formId'] = $values['parent_form_id'];
			$frm_vars['lookup_fields'][ $values['id'] ]['inSection'] = isset( $values['in_section'] ) ? $values['in_section'] : '0';
			$frm_vars['lookup_fields'][ $values['id'] ]['inEmbedForm'] = isset( $values['in_embed_form'] ) ? $values['in_embed_form'] : '0';
			$frm_vars['lookup_fields'][ $values['id'] ]['isRepeating'] = $values['form_id'] != $values['parent_form_id'];
			$frm_vars['lookup_fields'][ $values['id'] ]['isMultiSelect'] = false;
			$frm_vars['lookup_fields'][ $values['id'] ]['isReadOnly'] = (bool) $values['read_only'];

			if ( $values['original_type'] == 'lookup' ) {
				$frm_vars['lookup_fields'][ $values['id'] ]['inputType'] = $values['data_type'];
			} else {
				$frm_vars['lookup_fields'][ $values['id'] ]['inputType'] = $values['original_type'];
			}

			// Add field to parent field's dependents, if there is a parent
			if ( ! empty( $lookup_parents ) ) {
				foreach ( $lookup_parents as $watch_lookup ) {
					self::maybe_initialize_frm_vars_lookup_fields_for_id( $watch_lookup, $frm_vars );
					$frm_vars['lookup_fields'][ $watch_lookup ]['dependents'][] = $values['id'];
				}
			}
		}
	}

	/**
	 * If an index has not been set for the current Lookup Field in $frm_vars, add it now
	 * The global $frm_vars['lookup_fields'] array is used to load Lookup Field JavaScript
	 *
	 * @since 2.01.0
	 * @param int $field_id
	 * @param array $frm_vars
	 */
	private static function maybe_initialize_frm_vars_lookup_fields_for_id( $field_id, &$frm_vars ) {
		if ( ! isset( $frm_vars['lookup_fields'][ $field_id ] ) ) {
			$frm_vars['lookup_fields'][ $field_id ] = array(
				'dependents' => array()
			);
		}
	}

	/**
	 * Check all lookup fields that have parents when a form page is loaded
	 *
	 * @since 2.01.0
	 * @param array $frm_vars
	 */
	public static function load_check_dependent_lookup_js( $frm_vars ) {
		// TODO: don't reload for ajax
		if ( isset( $frm_vars['lookup_fields'] ) && ! empty( $frm_vars['lookup_fields'] ) ) {
			$lookup_field_ids = array();

			foreach ( $frm_vars['lookup_fields'] as $l_id => $lookup_field ) {
				if ( isset( $lookup_field['parents'] ) && $lookup_field['parents'] ) {
					if ( $lookup_field['fieldType'] == 'lookup' ) {
						// Update all dependent Lookup fields
						$lookup_field_ids[] = $l_id;
					} else {
						// Only update non-lookup fields if this is the initial form load
						if ( 'new' === self::get_form_action() ) {
							$lookup_field_ids[] = $l_id;
						}
					}
				}
			}
			echo "__frmDepLookupFields=" . json_encode( $lookup_field_ids ) . ";";
		}
	}

	/**
	 * Get the current action from the URL (new, create, edit, update)
	 *
	 * @since 2.02
	 * @return string $form_action
	 */
	private static function get_form_action() {
		$action_var = isset( $_REQUEST['frm_action'] ) ? 'frm_action' : 'action';

		return FrmAppHelper::get_param( $action_var, 'new', 'get', 'sanitize_title' );
	}

	/**
	 * Get the options for a dependent Lookup Field based on the parent Lookup field values
	 *
	 * @since 2.01.0
	 */
	public static function ajax_get_dependent_lookup_field_options(){
		check_ajax_referer( 'frm_ajax', 'nonce' );
		$field_id = FrmAppHelper::get_param( 'field_id', '', 'post', 'absint' );
		$parent_args = array(
			'parent_field_ids' => FrmAppHelper::get_param( 'parent_fields', '', 'post', 'absint' ),
			'parent_vals' => FrmAppHelper::get_param( 'parent_vals', '', 'post', 'wp_kses_post' ),
		);

		$child_field = FrmField::getOne( $field_id );

		$final_values = self::get_filtered_values_for_dependent_lookup_field( $parent_args, $child_field );

		echo json_encode( $final_values );
		wp_die();
	}

	/**
	 * Echo the HTML to replace a dependent Radio Lookup field's options
	 *
	 * @since 2.01.0
	 */
	public static function ajax_get_dependent_cb_radio_lookup_options() {
		check_ajax_referer( 'frm_ajax', 'nonce' );
		$field_id = FrmAppHelper::get_param( 'field_id', '', 'post', 'absint' );
		$parent_args = array(
			'parent_field_ids' => FrmAppHelper::get_param( 'parent_fields', '', 'post', 'absint' ),
			'parent_vals' => FrmAppHelper::get_param( 'parent_vals', '', 'post', 'wp_kses_post' ),
		);

		$args = array(
			'row_index' => FrmAppHelper::get_param( 'row_index', '', 'post', 'sanitize_text_field' ),
			'container_field_id' => FrmAppHelper::get_param( 'container_field_id', '', 'post', 'sanitize_text_field' ),
			'current_value' => FrmAppHelper::get_param( 'current_value', '', 'post', 'sanitize_text_field' ),
			'default_value' => FrmAppHelper::get_param( 'default_value', '', 'post', 'sanitize_text_field' ),
		);

		$child_field = FrmField::getOne( $field_id );

		$final_values = self::get_filtered_values_for_dependent_lookup_field( $parent_args, $child_field );

		self::show_dependent_cb_radio_lookup_options( $child_field, $args, $final_values );

		wp_die();
	}

	/**
	 * Get the filtered options for a dependent lookup field
	 *
	 * @since 2.01.0
	 *
	 * @param array $parent_args
	 * @param object $child_field
	 * @return array $final_values
	 */
	private static function get_filtered_values_for_dependent_lookup_field( $parent_args, $child_field ) {
		$entry_ids = self::get_entry_ids_from_parent_vals( $parent_args['parent_field_ids'], $parent_args['parent_vals'], $child_field );

		$meta_values = self::get_meta_values_filtered_by_entry_ids( $entry_ids, $child_field );

		self::order_values( $child_field->field_options['lookup_option_order'], $meta_values );

		return $meta_values;
	}

	/**
	 * Show the refreshed options in a Radio Lookup field
	 *
	 * @since 2.01.0
	 *
	 * @param object $child_field
	 * @param array $args
	 * @param array $final_values
	 */
	private static function show_dependent_cb_radio_lookup_options( $child_field, $args, $final_values ) {
		$field = self::initialize_dependent_cb_radio_field_array( $child_field, $final_values, $args );

		$saved_value_array = (array) $args['current_value'];

		$html_id = 'field_' . $child_field->field_key . $args['row_index'];

		$field_name = self::generate_field_name_for_radio_inputs( $child_field, $args );

		$disabled = ( FrmField::is_read_only( $child_field ) && ! FrmAppHelper::is_admin() ) ?  ' disabled="disabled"' : '';

		if ( 'checkbox' == $field['data_type'] ) {
			$field_name .= '[]';
			require( FrmAppHelper::plugin_path() .'/pro/classes/views/lookup-fields/front-end/checkbox-rows.php' );
		} else {
			require( FrmAppHelper::plugin_path() .'/pro/classes/views/lookup-fields/front-end/radio-rows.php' );
		}
	}

	/**
	 * Initialize a refreshed Radio Lookup field array
	 *
	 * @since 2.01.0
	 *
	 * @param object $child_field
	 * @param array $final_values
	 * @param array $args
	 * @return array $field
	 */
	private static function initialize_dependent_cb_radio_field_array( $child_field, $final_values, $args ) {
		$field_options = $child_field->field_options;
		$field = get_object_vars( $child_field ) + $field_options;
		unset( $field['field_options'] );

		$field['original_type'] = 'lookup';
		$field['options'] = ( ! empty( $final_values ) ) ? $final_values : array( '' );
		$field['default_value'] = $args['default_value'];

		return $field;
	}

	/**
	 * Generate the field input name for a repeating, embedded, or standard field
	 *
	 * @since 2.01.0
	 *
	 * @param object $field
	 * @param array $args
	 * @return string $field_name
	 */
	private static function generate_field_name_for_radio_inputs( $field, $args ) {
		if ( $args['row_index'] != '' ) {
			$i = str_replace( '-', '', $args['row_index'] );
			$field_name = 'item_meta[' . $args['container_field_id'] . '][' . $i . '][' . $field->id . ']';
		} else {
			$field_name = 'item_meta[' . $field->id . ']';
		}
		return $field_name;
	}

	/**
	 * Get the values for a text field that is dependent on Lookup Fields
	 *
	 * @since 2.01.0
	 */
	public static function ajax_get_text_field_lookup_value(){
		check_ajax_referer( 'frm_ajax', 'nonce' );
		$parent_field_ids = FrmAppHelper::get_param( 'parent_fields', '', 'post', 'absint' );
		$parent_vals = FrmAppHelper::get_param( 'parent_vals', '', 'post', 'wp_kses_post' );

		$field_id = FrmAppHelper::get_param( 'field_id', '', 'post', 'absint' );

		$child_field = FrmField::getOne( $field_id );

		$entry_ids = self::get_entry_ids_from_parent_vals( $parent_field_ids, $parent_vals, $child_field );

		$meta_values = self::get_meta_values_filtered_by_entry_ids( $entry_ids, $child_field );

		$meta_value = implode( ', ', $meta_values );

		echo wp_kses_post( $meta_value );

		wp_die();
	}

	/**
	 * Get the entry IDs in common for all parent Lookup fields/values
	 *
	 * @since 2.01.0
	 * @param array $parent_field_ids
	 * @param array $selected_values
	 * @param object $child_field
	 * @return array $entry_ids
	 */
	private static function get_entry_ids_from_parent_vals( $parent_field_ids, $selected_values, $child_field ) {
		$entry_ids = array();
		$args = array();

		// TODO: Maybe add current user filter here, or maybe add it in final call
		if ( self::need_to_filter_values_for_current_user( $child_field->field_options ) ) {
			$args['user_id'] = get_current_user_id();
		}

		foreach ( $parent_field_ids as $i => $p_field_id ) {
			$parent_field = FrmField::getOne( $p_field_id );
			$linked_field = FrmField::getOne( $parent_field->field_options['get_values_field'] );

			$parent_val = $selected_values[ $i ];

			$args['comparison_type'] = apply_filters( 'frm_set_comparison_type_for_lookup', 'equals', $parent_field, $child_field );
			$args['and_or'] = apply_filters( 'frm_set_and_or_for_lookup', 'and', $parent_field, $child_field );
			self::apply_current_user_filter_for_non_lookup( $child_field, $parent_field, $args );

			$entry_ids = self::get_entry_ids_for_parent_field_and_value( $linked_field, $parent_val, $args );

			if ( ! $entry_ids ) {
				break;
			}

			self::append_child_entry_ids( $entry_ids );

			$args['entry_ids'] = $entry_ids;
		}

		return $entry_ids;

	}

	/**
	 *
	 * @param stdClass $child_field
	 * @param stdClass $parent_field
	 * @param array $args
	 */
	private static function apply_current_user_filter_for_non_lookup( $child_field, $parent_field, &$args ) {
		if ( $child_field->type !== 'lookup' && self::need_to_filter_values_for_current_user( $parent_field->field_options ) ) {
			$args['user_id'] = get_current_user_id();
		}
	}

	/**
	 * Get the entry IDs for a given field and value
	 *
	 * @since 2.01.01
	 * @param object $linked_field
	 * @param string|array $parent_val
	 * @param array $args
	 * @return array
	 */
	private static function get_entry_ids_for_parent_field_and_value( $linked_field, $parent_val, $args ) {
		if ( is_array( $parent_val ) ) {
			$entry_ids = array( 'first' => true );
			foreach ( $parent_val as $p_val ) {
				$new_entry_ids = FrmProEntryMeta::get_entry_ids_for_field_and_value( $linked_field, $p_val, $args );
				$entry_ids = self::filter_or_merge_entry_ids( $entry_ids, $new_entry_ids, $args['and_or'] );
			}
		} else {
			$entry_ids = FrmProEntryMeta::get_entry_ids_for_field_and_value( $linked_field, $parent_val, $args );
		}

		return $entry_ids;
	}

	/**
	 * Either combine the results, or get only those in common.
	 * and/or depends on the frm_set_and_or_for_lookup filter
	 *
	 * @since 2.03.08
	 */
	private static function filter_or_merge_entry_ids( $entry_ids, $new_entry_ids, $and_or ) {
		if ( isset( $entry_ids['first'] ) ) {
			return $new_entry_ids;
		}

		if ( $and_or == 'or' ) {
			$entry_ids = array_intersect( $entry_ids, $new_entry_ids );
		} else {
			$entry_ids = array_merge( $entry_ids, $new_entry_ids );
		}

		return $entry_ids;
	}

	/**
	 * Append child entry IDs, if there are any, to an array of entry IDs
	 *
	 * @since 2.02.13
	 * @param array $entry_ids
	 */
	private static function append_child_entry_ids( &$entry_ids ) {
		$child_entry_ids = FrmDb::get_col( 'frm_items', array( 'parent_item_id' => $entry_ids ), 'id' );

		if ( is_array( $child_entry_ids ) && ! empty( $child_entry_ids ) ) {
			$entry_ids = array_merge( $entry_ids, $child_entry_ids );
		}
	}

	/**
	 * Get meta values for a specific field, filtered by an array of entry IDs
	 *
	 * @since 2.01.0
	 *
	 * @param array $entry_ids
	 * @param object $child_field
	 * @return array $meta_values
	 */
	private static function get_meta_values_filtered_by_entry_ids( $entry_ids, $child_field ) {
		if ( ! $entry_ids ) {
			return array();
		}

		$args = array(
			'entry_ids' => $entry_ids,
		);

		if ( FrmField::is_option_true_in_object( $child_field, 'get_most_recent_value' ) ) {
			$args['order_by'] = 'e.id DESC';
			$args['limit'] = '1';
		}

		$linked_field = FrmField::getOne( $child_field->field_options['get_values_field'] );

		$meta_values = FrmProEntryMeta::get_all_metas_for_field( $linked_field, $args );

		$meta_values = self::flatten_and_unserialize_meta_values( $meta_values );

		self::get_unique_values( $meta_values );

		return $meta_values;
	}

	/**
	 * Check if the values need to be filtered for the current user
	 *
	 * @since 2.01.0
	 * @param array $field_options
	 * @return bool
	 */
	private static function need_to_filter_values_for_current_user( $field_options ) {
		return FrmField::is_option_true_in_array( $field_options, 'lookup_filter_current_user' ) && ! current_user_can( 'administrator' ) && ! FrmAppHelper::is_admin();
	}

	/**
	 * If meta values are arrays (checkboxes, repeating fields, etc), flatten the values to a single-dimensional array
	 *
	 * @since 2.01.0
	 * @param array $meta_values
	 * @return array $final_values
	 */
	private static function flatten_and_unserialize_meta_values( $meta_values ) {
		$final_values = array();
		foreach ( $meta_values as $meta_val ) {

			$meta_val = maybe_unserialize( $meta_val );
			if ( is_array( $meta_val ) ) {
				$final_values = array_merge( $final_values, $meta_val );
			} else {
				$meta_val = self::decode_html_entities( $meta_val );
				$final_values[] = $meta_val;
			}
		}
		return $final_values;
	}

	/**
	 * Only get unique values in Lookup Fields
	 *
	 * @since 2.01.0
	 * @param array $final_values
	 */
	private static function get_unique_values( &$final_values ) {
		$final_values = array_unique( $final_values );
		$final_values = array_values( $final_values );
	}

	/**
	 * Order the values in a Lookup Field
	 *
	 * @since 2.01.0
	 * @param string $order
	 * @param array $final_values
	 */
	private static function order_values( $order, &$final_values ) {
		if ( ! $final_values ) {
			return;
		}

		if ( $order == 'ascending' || $order == 'descending' ) {
			natcasesort( $final_values );
			if ( $order == 'descending' ) {
				$final_values = array_reverse( $final_values );
			}
			$final_values = array_values( $final_values );
		}

		$final_values = apply_filters( 'frm_order_lookup_options', $final_values, $order );
	}

	/**
	 * Get the HTML for a Lookup Field on the front-end
	 *
	 * @since 2.01.0
	 * @param array $field
	 * @param string $field_name
	 * @param string $html_id
	 */
	public static function get_front_end_lookup_field_html( $field, $field_name, $html_id ) {
		$disabled = self::get_disabled_input_string( $field );
		$saved_value_array = (array) $field['value'];
		$saved_value_array = self::decode_html_entities( $saved_value_array );

		if ( 'checkbox' == $field['data_type'] ) {
			$field_name .= '[]';
		}

		require( FrmAppHelper::plugin_path() .'/pro/classes/views/lookup-fields/front-end/input.php' );
	}

	/**
	 * Get the disabled="disabled" string if a field input should be disabled/readonly
	 *
	 * @since 2.01.0
	 *
	 * @param array $field
	 * @return string $disabled
	 */
	private static function get_disabled_input_string( $field ) {
		$disabled = '';
		if ( FrmField::is_read_only( $field ) && ! FrmAppHelper::is_admin() ) {
			global $frm_vars;
			if ( isset( $frm_vars['readonly'] ) && $frm_vars['readonly'] == 'disabled' ) {
				$disabled = '';
			} else {
				if ( $field['data_type'] == 'text' ) {
					$disabled = ' readonly="readonly"';
				} else {
					$disabled = ' disabled="disabled"';
				}
			}
		}
		return $disabled;
	}

	/**
	 * Add the autocomplete classes to a dropdown field (if the autocomplete option is selected)
	 *
	 * @since 2.01.0
	 *
	 * @param array $field
	 * @param string $class
	 */
	public static function maybe_add_autocomplete_class( $field, &$class ) {
		// Don't add the autocomplete class to the form builder page
		if ( FrmAppHelper::is_admin() || FrmField::is_read_only( $field ) ) {
			return;
		}

		if ( $field['type'] == 'lookup' && $field['data_type'] == 'select' && FrmField::is_option_true( $field, 'autocom' ) ) {
			 FrmProFieldsController::add_autocomplete_classes( $field, $class );
		}
	}

	/**
	 * Add the data-placeholder attribute to lookup fields with the autocomplete option
	 *
	 * @since 2.01.0
	 *
	 * @param array $field
	 * @param string $add_html
	 */
	public static function maybe_add_lookup_input_html( $field, &$add_html ) {
		if ( $field['type'] == 'lookup' && $field['data_type'] == 'select' && FrmField::is_option_true( $field, 'autocom' ) ) {
			// If autocomplete is selected, add a blank data-placeholder so chosen's default isn't used
			$add_html .= ' data-placeholder=" "';
		}
	}
}
