<?php

/**
 * @since 3.0
 */
class FrmProFieldData extends FrmFieldType {

	/**
	 * @var string
	 * @since 3.0
	 */
	protected $type = 'data';

	protected $is_tall = true;

	protected function input_html() {
		return $this->multiple_input_html();
	}

	protected function include_form_builder_file() {
		return FrmProAppHelper::plugin_path() . '/classes/views/frmpro-fields/back-end/field-' . $this->type . '.php';
	}

	protected function field_settings_for_type() {
		$settings = array();

		if ( $this->field && isset( $this->field->field_options['data_type'] ) ) {
			$settings['default_value'] = true;
			$settings['read_only'] = true;
			$settings['unique'] = true;

			switch ( $this->field->field_options['data_type'] ) {
				case 'data':
					$settings['required'] = false;
					$settings['default_blank'] = false;
					$settings['read_only'] = false;
					$settings['unique'] = false;
					break;
				case 'select':
					$settings['size'] = true;
			}
		}

		FrmProFieldsHelper::fill_default_field_display( $settings );
		return $settings;
	}

	protected function extra_field_opts() {
		return array(
			'data_type' => 'select',
			'restrict' => 0,
		);
	}

	public function prepare_front_field( $values, $atts ) {
		$data_type = FrmField::get_option( $this->field, 'data_type' );
		$form_select = FrmField::get_option( $this->field, 'form_select' );
		if ( in_array( $data_type, array( 'select', 'radio', 'checkbox' ) ) && is_numeric( $form_select ) ) {
			$entry_id = isset( $values['entry_id'] ) ? $values['entry_id'] : 0;
			FrmProDynamicFieldsController::add_options_for_dynamic_field( $this->field, $values, array( 'entry_id' => $entry_id ) );
		}
		return $values;
	}

	/**
	 * Remove the frm_opt_container class for dropdowns
	 */
	protected function after_replace_html_shortcodes( $args, $html ) {
		$data_type = FrmField::get_option( $this->field, 'data_type' );
		if ( 'select' === $data_type ) {
			$html = str_replace( '"frm_opt_container', '"frm_data_container', $html );
		}
		return $html;
	}

	/**
	 * @since 3.0
	 */
	protected function prepare_display_value( $value, $atts ) {
		if ( ! isset( $this->field->field_options['form_select'] ) || $this->field->field_options['form_select'] == 'taxonomy' ) {
			return $value;
		}

		$atts['show'] = isset( $atts['show'] ) ? $atts['show'] : false;

		if ( ! empty( $value ) && ! is_array( $value ) && strpos( $value, $atts['sep'] ) !== false ) {
			$value = explode( $atts['sep'], $value );
		}

		if ( $atts['show'] == 'id' ) {
			// keep the values the same since we already have the ids
			return (array) $value;
		}

		$show_opts = array( 'key', 'created-at', 'created_at', 'updated-at', 'updated_at, updated-by, updated_by', 'post_id' );
		if ( in_array( $atts['show'], $show_opts ) ) {
			$value = $this->get_show_value( $value, $atts );
		} else {
			$value = $this->get_data_value( $value, $atts );
		}

		return $value;
	}

	/**
	 * @since 3.0
	 */
	private function get_show_value( $linked_ids, $atts ) {
		$nice_show = str_replace( '-', '_', $atts['show'] );

		$value = array();
		foreach ( (array) $linked_ids as $linked_id ) {
			$linked_entry = FrmEntry::getOne( $linked_id );

			if ( isset( $linked_entry->{$atts['show']} ) ) {
				$value[] = $linked_entry->{$atts['show']};
			} else if ( isset( $linked_entry->{$nice_show} ) ) {
				$value[] = $linked_entry->{$nice_show};
			} else {
				$value[] = $linked_entry->item_key;
			}
		}
		return $value;
	}

	/**
	 * @since 3.0
	 */
	private function get_data_value( $linked_ids, $atts ) {
		$value = array();

		if ( ! empty( $linked_ids ) ) {
			if ( is_array( $linked_ids ) ) {
				foreach ( $linked_ids as $linked_id ) {
					$new_val = $this->get_single_data_value( $linked_id, $atts );
					if ( $new_val !== false ) {
						$value[] = $new_val;
					}

					unset( $new_val, $linked_id );
				}
				$value = array_filter( $value, 'strlen' );
			} else {
				$value = $this->get_single_data_value( $linked_ids, $atts );
			}
		}
		return $value;
	}

	private function get_single_data_value( $linked_id, $atts ) {
		$atts['includes_list_data'] = true;
		$value = FrmProFieldsHelper::get_data_value( $linked_id, $this->field, $atts );

		if ( $linked_id === $value && ! FrmProField::is_list_field( $this->field ) ) {
			$value = false;
		} elseif ( is_array( $value ) ) {
			$value = implode( $atts['sep'], $value );
		}

		return $value;
	}

	public function get_container_class() {
		$class = parent::get_container_class();
		return $class . ' frm_dynamic_' . $this->field['data_type'] . '_container';
	}

	protected function include_front_form_file() {
		return FrmProAppHelper::plugin_path() . '/classes/views/frmpro-fields/data-options.php';
	}

	/**
	 * Override parent to check if options are empty.
	 * TODO: Why is this needed?
	 */
	protected function maybe_include_hidden_values( $args ) {
		$hidden = '';
		$field = isset( $args['field'] ) ? $args['field'] : $this->field;
		$is_read_only = empty( $field['options'] ) || ( FrmField::is_read_only( $this->field ) && ! FrmAppHelper::is_admin() );
		if ( $is_read_only && $this->show_readonly_hidden() ) {
			$hidden = $this->show_hidden_values( $args );
		}
		return $hidden;
	}


	protected function show_readonly_hidden() {
		return in_array( FrmField::get_option( $this->field, 'data_type' ), array( 'select', 'radio', 'checkbox' ) );
	}

	protected function is_readonly_array() {
		return FrmField::get_option( $this->field, 'data_type' ) == 'checkbox';
	}

	/**
	 * Get the entry IDs for a value imported in a Dynamic field
	 *
	 * @since 3.0
	 *
	 * @param array|string|int $value
	 * @param array $ids
	 *
	 * @return array|string|int
	 */
	protected function prepare_import_value( $value, $atts ) {
		if ( ! $this->field || FrmProField::is_list_field( $this->field ) ) {
			return $value;
		}

		$value = FrmProXMLHelper::convert_imported_value_to_array( $value );
		$this->switch_imported_entry_ids( $atts['ids'], $value );

		if ( count( $value ) <= 1 ) {
			$value = reset( $value );
		} else {
			$value = array_map( 'trim', $value );
		}

		return $value;
	}

	/**
	 * Switch the old entry IDs imported to new entry IDs for a Dynamic field
	 *
	 * @since 3.0
	 *
	 * @param array $imported_values
	 */
	private function switch_imported_entry_ids( $ids, &$imported_values ) {
		if ( ! is_array( $imported_values ) ) {
			return;
		}

		foreach ( $imported_values as $key => $imported_value ) {

			// This entry was just imported, so we have the id
			if ( is_numeric( $imported_value ) && isset( $ids[ $imported_value ] ) ) {
				$imported_values[ $key ] = $ids[ $imported_value ];
				continue;
			}

			// Look for the entry ID based on the imported value
			// TODO: this may not be needed for XML imports. It appears to always be the entry ID that's exported
			$where  = array( 'field_id' => $this->field->field_options['form_select'], 'meta_value' => $imported_value );
			$new_id = FrmDb::get_var( 'frm_item_metas', $where, 'item_id' );

			if ( $new_id && is_numeric( $new_id ) ) {
				$imported_values[ $key ] = $new_id;
			}
		}
	}
}
