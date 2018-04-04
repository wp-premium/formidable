<?php

/**
 * @since 2.04
 */
class FrmProFieldValue extends FrmFieldValue {

	/**
	 * FrmFieldValue constructor.
	 *
	 * @param stdClass $field
	 * @param stdClass $entry
	 */
	public function __construct( $field, $entry ) {
		parent::__construct( $field, $entry );
	}

	/**
	 * Initialize the saved_value property
	 *
	 * @since 2.04
	 *
	 * @param stdClass $entry
	 */
	protected function init_saved_value( $entry ) {

		if ( $entry->form_id != $this->field->form_id ) {
			//If parent entry ID and child repeating field

			$where = array(
				'form_id' => $this->field->form_id,
				'parent_item_id' => $entry->id,
			);
			$child_entry_ids = FrmDb::get_col( 'frm_items', $where );

			if ( is_array( $child_entry_ids ) ) {
				$this->saved_value = array();

				foreach ( $child_entry_ids as $child_entry_id ) {
					$child_entry = FrmEntry::getOne( $child_entry_id, true );
					$current_field_value = new FrmProFieldValue( $this->field, $child_entry );
					$this->saved_value[ $child_entry_id ] = $current_field_value->get_saved_value();
				}
			}
		} elseif ( isset( $this->field->field_options['post_field'] ) && $this->field->field_options['post_field'] ) {

			$this->saved_value = $this->get_post_value( $entry );
			$this->clean_saved_value();

		} else {

			parent::init_saved_value( $entry );

		}
	}

	/**
	 * Initialize a field's displayed value
	 *
	 * @since 2.04
	 *
	 * @param array $atts
	 */
	public function prepare_displayed_value( $atts = array() ) {
		// TODO: Pick up on making this work well
		// TODO: maybe move these various functions into FrmFieldType classes
		$this->displayed_value = $this->saved_value;

		if ( $this->has_child_entries() ) {
			$this->prepare_displayed_value_for_field_with_child_entries( $atts );
		} else {

			// TODO: all display value generation should be handled in one classes or subclasses
			$this->generate_post_field_displayed_value();
			$this->generate_displayed_value_for_field_type( $atts );
			$this->filter_array_displayed_value();
			$this->get_option_label_for_saved_value();
			$this->filter_displayed_value( $atts );
		}
	}

	protected function prepare_displayed_value_for_field_with_child_entries( $atts = array() ) {
		$this->displayed_value = array();

		if ( isset( $atts['include_fields'] ) ) {
			$atts['include_fields'] = '';
		}

		if ( isset( $atts['fields'] ) ) {
			$atts['fields'] = '';
		}

		foreach ( $this->saved_value as $child_id ) {
			$child_values = new FrmProEntryValues( $child_id, $atts );
			$this->displayed_value[ $child_id ] = $child_values->get_field_values();
		}
	}

	/**
	 * Get the saved value for a post field
	 *
	 * @since 2.04
	 *
	 * @param stdClass $entry
	 *
	 * @return mixed
	 */
	protected function get_post_value( $entry ) {
		$pass_args = array(
			'links' => false,
			'truncate' => false,
		);

		return FrmProEntryMetaHelper::get_post_or_meta_value( $entry, $this->field, $pass_args );
	}

	/* Check if an embedded form or repeating section has child entries
	 * @since 2.04
	 *
	 * @return bool
	 */
	public function has_child_entries() {
		$has_child_entries = false;

		if ( $this->field->type === 'form' || FrmField::is_repeating_field( $this->field ) ) {

			if ( FrmAppHelper::is_not_empty_value( $this->saved_value ) ) {
				$has_child_entries = true;
			}
		}

		return $has_child_entries;

	}

	/**
	 * Get a post field's displayed value
	 *
	 * @since 2.04
	 *
	 * @return mixed
	 */
	protected function generate_post_field_displayed_value() {
		if ( FrmField::is_option_true( $this->field, 'post_field' ) ) {
			$entry = FrmEntry::getOne( $this->entry_id, true );
			$this->displayed_value = FrmProEntryMetaHelper::get_post_or_meta_value( $entry, $this->field, array( 'truncate' => true ) );
			$this->displayed_value = maybe_unserialize( $this->displayed_value );
		}
	}

	/**
	 * Filter an array displayed value
	 *
	 * @since 2.04
	 */
	protected function filter_array_displayed_value() {
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
	protected function get_option_label_for_saved_value() {
		$this->displayed_value = FrmProEntriesController::get_option_label_for_saved_value( $this->displayed_value, $this->field, array() );
	}
}
