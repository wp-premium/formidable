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
		if ( $field->type !== 'end_divider' ) {
			$this->set_current_container( $field );
		}

		if ( ! empty( $this->include_fields ) ) {
			$is_included = $this->is_self_or_parent_in_array( $field, $this->include_fields );
		} else if ( ! empty( $this->exclude_fields ) ) {
			$is_included = ! $this->is_self_or_parent_in_array( $field, $this->exclude_fields );
		} else {
			$is_included = true;
		}

		if ( $field->type === 'end_divider' ) {
			$this->set_current_container( $field );
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
			'exclude_fields' => $this->exclude_fields,
		);

		$this->field_values[ $field->id ] = new FrmProFieldValue( $field, $this->entry, $atts );
	}
}
