<?php

/**
 * @since 2.04
 */
class FrmProFieldValue extends FrmFieldValue {

	private $is_empty_container = false;

	/**
	 * FrmFieldValue constructor.
	 *
	 * @param stdClass $field
	 * @param stdClass $entry
	 * @param array $atts
	 */
	public function __construct( $field, $entry, $atts = array() ) {
		parent::__construct( $field, $entry, $atts );

		$this->init_is_empty_container();
	}

	/**
	 * Initialize the saved_value property
	 *
	 * @since 2.04
	 */
	protected function init_saved_value() {

		if ( $this->entry->form_id != $this->field->form_id ) {
			//If parent entry ID and child repeating field

			$where = array(
				'form_id' => $this->field->form_id,
				'parent_item_id' => $this->entry->id,
			);
			$child_entry_ids = FrmDb::get_col( 'frm_items', $where );

			if ( is_array( $child_entry_ids ) ) {
				$this->saved_value = array();

				foreach ( $child_entry_ids as $child_entry_id ) {
					$child_entry = FrmEntry::getOne( $child_entry_id, true );
					$current_field_value = new FrmProFieldValue( $this->field, $child_entry, array( 'source' => $this->source ) );
					$this->saved_value[ $child_entry_id ] = $current_field_value->get_saved_value();
				}
			}

		} else if ( $this->field->type === 'html' ) {

			$this->saved_value = $this->field->description;
			$this->clean_saved_value();

		} else if ( isset( $this->field->field_options['post_field'] ) && $this->field->field_options['post_field'] ){

			$this->saved_value = $this->get_post_value();
			$this->clean_saved_value();

		} else {

			parent::init_saved_value();

		}
	}

	// TODO: make this reusable
	/**
	 * Initialize a field's displayed value
	 *
	 * @since 2.04
	 */
	protected function init_displayed_value() {
		$this->displayed_value = $this->saved_value;

		if ( $this->has_child_entries() ) {
			$this->displayed_value = array();
			foreach ( $this->saved_value as $child_id ) {
				$child_values = new FrmProEntryValues( $child_id, array( 'source' => $this->source ) );
				$this->displayed_value[ $child_id ] = $child_values->get_field_values();
			}

		} else {

			// TODO: improve organization and readability
			$this->generate_post_field_displayed_value();
			$this->generate_displayed_value_for_field_type();
			$this->filter_array_displayed_value();
			$this->get_option_label_for_saved_value();
			$this->filter_displayed_value();
		}
	}

	/**
	 * Initialize the is_empty_container property
	 *
	 * @since 2.04
	 */
	private function init_is_empty_container() {

		// TODO: get this working reliably

		if ( $this->field->type === 'form' || FrmField::is_repeating_field( $this->field ) ) {

			$this->is_empty_container = ! $this->has_child_entries();

		} else if ( $this->field->type === 'divider' ) {

			// TODO: how to determine if divider has values inside of it?
			$this->is_empty_container = true;
		}
	}

	/**
	 * Set the is_empty_container property
	 *
	 * @since 2.04
	 */
	public function set_is_empty_container( $is_empty ) {
		$this->is_empty_container = $is_empty;
	}

	/**
	 * Get the saved value for a post field
	 *
	 * @since 2.04
	 *
	 * @return mixed
	 */
	private function get_post_value() {
		$pass_args = array(
			'links' => false,
			'truncate' => false,
		);

		return FrmProEntryMetaHelper::get_post_or_meta_value( $this->entry, $this->field, $pass_args );
	}

	/* Check if an embedded form or repeating section has child entries
	 * @since 2.04
	 *
	 * @return bool
	 */
	public function has_child_entries() {
		$has_child_entries = false;

		if ( $this->field->type === 'form' || FrmField::is_repeating_field( $this->field ) ) {

			if ( ! empty( $this->saved_value ) && is_array( $this->saved_value ) ) {
				$has_child_entries = true;
			}

		}

		return $has_child_entries;

	}

	/**
	 * Determine if a field is an empty container, meaning it is a divider or embedded form with no values submitted in it
	 *
	 * @since 2.04
	 *
	 * @return bool
	 */
	public function is_empty_container() {
		// TODO: Maybe change to is_empty_section?

		return $this->is_empty_container;

	}

	/**
	 * Get a post field's displayed value
	 *
	 * @since 2.04
	 *
	 * @return mixed
	 */
	private function generate_post_field_displayed_value() {
		if ( FrmField::is_option_true( $this->field, 'post_field' ) ) {
			$this->displayed_value = FrmProEntryMetaHelper::get_post_or_meta_value( $this->entry, $this->field, array( 'truncate' => true ) );
			$this->displayed_value = maybe_unserialize( $this->displayed_value );
		}
	}

	/**
	 * Get the displayed value for different field types
	 *
	 * @since 2.04
	 *
	 * @return mixed
	 */
	private function generate_displayed_value_for_field_type() {

		switch ( $this->field->type ) {
			case 'user_id':
				$this->displayed_value = FrmProFieldsHelper::get_display_name( $this->displayed_value );
				break;
			case 'data':
				if ( is_array( $this->displayed_value ) ) {

					$new_value = array();
					foreach ( $this->displayed_value as $val ) {
						$new_value[] = FrmProFieldsHelper::get_data_value( $val, $this->field );
					}

					$this->displayed_value = $new_value;
				} else {
					$this->displayed_value = FrmProFieldsHelper::get_data_value( $this->displayed_value, $this->field );
				}
				break;
			case 'file':
				$this->displayed_value = FrmProFieldsHelper::get_file_name( $this->displayed_value, true, ', ' );
				if ( FrmField::is_option_true( $this->field, 'multiple' ) ) {
					$this->displayed_value = explode( ', ', $this->displayed_value );
				}
				break;
			case 'date':
				$this->displayed_value = FrmProFieldsHelper::get_date( $this->displayed_value );
				break;
			case 'time':
				$this->displayed_value = FrmProFieldsHelper::get_time_display_value( $this->displayed_value, array(), $this->field );
				break;
		}
	}

	/**
	 * Filter an array displayed value
	 *
	 * @since 2.04
	 */
	private function filter_array_displayed_value() {
		if ( is_array( $this->displayed_value ) ) {
			$new_value = '';
			foreach ( $this->displayed_value as $val ) {
				if ( is_array( $val ) ) {
					// This will affect checkboxes inside of repeating sections
					$new_value .= implode( ', ', $val ) . "\n";
				}
			}

			if ( $new_value != '' ) {
				$this->displayed_value = $new_value;
			} else {
				// Only keep non-empty values in array
				$this->displayed_value = array_filter( $this->displayed_value );
			}
		}
	}

	/**
	 * Get the option label for a saved value
	 *
	 * @since 2.04
	 */
	private function get_option_label_for_saved_value() {
		$this->displayed_value = FrmProEntriesController::get_option_label_for_saved_value( $this->displayed_value, $this->field, array() );
	}

}