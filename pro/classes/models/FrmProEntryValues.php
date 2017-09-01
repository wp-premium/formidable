<?php

/**
 * @since 2.04
 */
class FrmProEntryValues extends FrmEntryValues {

	/**
	 * @var FrmProFieldValue[]
	 */
	protected $field_values = array();

	/**
	 * @var stdClass
	 */
	private $current_section = null;

	/**
	 * @var stdClass
	 */
	private $current_embedded_form = null;

	public function __construct( $entry_id, $atts = array() ) {
		parent::__construct( $entry_id, $atts );
	}

	/**
	 * Set the field values
	 *
	 * @since 2.04
	 */
	protected function init_field_values() {
		foreach ( $this->fields as $field ) {

			$this->set_current_container( $field );

			if ( $this->is_field_included( $field ) ) {
				$this->add_field_values( $field );
			}
		}
	}

	/**
	 * Set/clear the current section or embedded form
	 *
	 * @since 2.04
	 *
	 * @param stdClass $field
	 */
	private function set_current_container( $field ) {
		if ( $field->type === 'divider' ) {
			$this->current_section = $field;
		} else if ( $field->type === 'end_divider' ) {
			$this->current_section = null;
		}

		if ( $field->type === 'form' ) {
			$this->current_embedded_form = $field;
		} else if ( is_object( $this->current_embedded_form ) && $field->form_id != $this->current_embedded_form->field_options['form_select'] ) {
			$this->current_embedded_form = null;
		}
	}

	/**
	 * Check if a field is included
	 *
	 * @since 2.04
	 *
	 * @param stdClass $field
	 *
	 * @return bool
	 */
	protected function is_field_included( $field ) {

		if ( ! empty( $this->include_fields ) ) {
			$is_included = $this->is_self_or_parent_in_array( $field, $this->include_fields );
		} else if ( ! empty( $this->exclude_fields ) ) {
			$is_included = ! $this->is_self_or_parent_in_array( $field, $this->exclude_fields );
		} else {
			$is_included = true;
		}

		return $is_included;
	}

	/**
	 * If embedded form or section is included/excluded, apply this to children as well
	 *
	 * @since 2.04
	 *
	 * @param stdClass $field
	 * @param array $fields
	 *
	 * @return bool
	 */
	private function is_self_or_parent_in_array( $field, $fields ) {
		if ( $this->is_field_in_array( $field, $fields ) ) {
			$in_array = true;
		} else if ( is_object( $this->current_section ) && $this->is_field_in_array( $this->current_section, $fields ) ) {
			$in_array = true;
		} else if ( is_object( $this->current_embedded_form ) && $this->is_field_in_array( $this->current_embedded_form, $fields ) ) {
			$in_array = true;
		} else {
			$in_array = false;
		}

		return $in_array;
	}

	/**
	 * Add a field's values to the field_values property
	 *
	 * @since 2.04
	 *
	 * @param stdClass $field
	 */
	protected function add_field_values( $field ) {
		$atts = array(
			'exclude_fields' => $this->exclude_fields,// TODO: is this necessary?
			'source' => $this->source,
			);

		$this->field_values[ $field->id ] = new FrmProFieldValue( $field, $this->entry, $atts );

		$this->update_is_empty_flag_on_parent_container( $field->id );
	}

	/**
	 * Update the is_empty_container flag on a parent field
	 *
	 * @since 2.04
	 *
	 * @param int|string $field_id
	 */
	private function update_is_empty_flag_on_parent_container( $field_id ) {
		if ( ! is_object( $this->current_section ) && ! is_object( $this->current_embedded_form ) ) {
			return;
		}

		$displayed_value = $this->field_values[ $field_id ]->get_displayed_value();

		if ( $displayed_value !== '' && ! empty( $displayed_value ) ) {
			if ( is_object( $this->current_section ) && $field_id != $this->current_section->id ) {
				$this->field_values[ $this->current_section->id ]->set_is_empty_container( false );
			}

			if ( is_object( $this->current_embedded_form ) && $field_id != $this->current_embedded_form->id ) {
				$this->field_values[ $this->current_embedded_form->id ]->set_is_empty_container( false );
			}
		}
	}

}