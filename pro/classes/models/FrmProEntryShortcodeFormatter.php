<?php

/**
 * @since 2.04
 */
class FrmProEntryShortcodeFormatter extends FrmEntryShortcodeFormatter {

	/**
	 * FrmProEntryShortcodeFormatter constructor
	 *
	 * @param int|string $form_id
	 * @param array $atts
	 */
	public function __construct( $form_id, $atts ) {
		parent::__construct( $form_id, $atts );

		$this->init_skip_fields();
	}

	/**
	 * Set the skip_fields property
	 *
	 * @since 2.04
	 */
	protected function init_skip_fields() {
		$this->skip_fields = array( 'captcha', 'html', 'end_divider', 'password', 'credit_card' );

		if ( $this->format == 'array' ) {
			$this->skip_fields[] = 'break';
		}
	}

	/**
	 * Generate the default HTML for a single field
	 *
	 * @since 2.04
	 *
	 * @param stdClass stdClass $field
	 *
	 * @return string
	 */
	protected function generate_field_content( $field ) {
		if ( in_array( $field->type, $this->skip_fields ) ) {
			return '';
		}

		if ( $field->type == 'divider' ) {
			$row = $this->generate_section_content( $field );

		} else if ( $field->type == 'form' ) {
			$row = $this->generate_embedded_form_content( $field );

		} else if ( $field->type == 'data' && $field->field_options['data_type'] == 'data' ) {
			$row = $this->generate_dynamic_list_field_content( $field );

		} else if ( $field->type == 'break' ) {
			$row = $this->generate_page_break_content( $field );

		} else {
			$row = parent::generate_field_content( $field );
		}

		return $row;
	}

	/**
	 * Generate a field's array for the default HTML array
	 *
	 * @since 2.04
	 *
	 * @param stdClass $field
	 */
	protected function add_field_array( $field ) {
		if ( in_array( $field->type, $this->skip_fields ) ) {
			return;
		}

		if ( $field->type == 'divider' ) {
			$this->add_section_array( $field );

		} else if ( $field->type == 'form' ) {
			$this->add_embedded_form_array( $field );

		} else if ( $field->type == 'data' && $field->field_options['data_type'] == 'data' ) {
			$this->add_dynamic_list_field_array( $field );

		} else {
			$this->add_single_field_array( $field, $field->id );
		}
	}

	/**
	 * Generate the HTML for a section field
	 *
	 * @since 2.04
	 * @param stdClass $field
	 *
	 * @return string
	 */
	protected function generate_section_content( $field ) {
		$section_value = '[' . $field->id . ' show=description]';
		if ( ! $this->is_plain_text_format() ) {
			$section_value = '<h3>' . $section_value . '</h3>';
		}

		$html = $this->generate_single_cell_shortcode_row( $field, $section_value );

		if ( FrmField::is_option_true( $field, 'repeat' ) ) {
			$html .= '[foreach ' . $field->id . ']';

			foreach ( $this->get_child_fields( $field ) as $child_field ) {
				$html .= $this->generate_field_content( $child_field );
			}

			$html .= '[/foreach ' . $field->id . ']';
		}

		return $html;
	}

	/**
	 * Generate a single cell row of shortcodes for an HTML or plain text table
	 *
	 * @since 2.05
	 *
	 * @param stdClass $field
	 * @param mixed $value
	 *
	 * @return string
	 */
	public function generate_single_cell_shortcode_row( $field, $value ) {
		$row = '[if ' . $field->id . ']';

		if ( $this->is_plain_text_format() ) {
			$row .= $value . "\r\n";
		} else {
			$row .= $this->table_generator->generate_single_cell_table_row( $value );
		}
		$row .= '[/if ' . $field->id . ']';

		if ( $this->is_table_format() ) {
			$row .= "\r\n";
		}

		return $row;
	}

	protected function add_section_array( $field ) {
		if ( FrmField::is_option_true( $field, 'repeat' ) ) {
			foreach ( $this->get_child_fields( $field ) as $child_field ) {
				$this->add_field_array( $child_field );
			}
		}
	}

	protected function get_child_fields( $field ) {
		$child_form_id = $field->field_options['form_select'];

		return FrmField::get_all_for_form( $child_form_id, '', 'exclude', 'exclude' );
	}

	/**
	 * Generate the HTML for an embedded form field
	 *
	 * @since 2.04
	 * @param stdClass $field
	 *
	 * @return string
	 */
	protected function generate_embedded_form_content( $field ) {
		$html = '';

		$child_form_id = $field->field_options['form_select'];
		$child_fields = FrmField::get_all_for_form( $child_form_id, '', 'exclude', 'exclude' );

		foreach ( $child_fields as $child_field ) {
			$html .= $this->generate_field_content( $child_field );
		}

		return $html;
	}

	/**
	 * Add an embedded form's field IDs to the array
	 *
	 * @since 2.04
	 * @param stdClass $field
	 */
	protected function add_embedded_form_array( $field ) {
		$child_form_id = $field->field_options['form_select'];
		$child_fields = FrmField::get_all_for_form( $child_form_id, '', 'exclude', 'exclude' );

		foreach ( $child_fields as $child_field ) {
			$this->add_field_array( $child_field );
		}
	}

	/**
	 * Generate the HTML for a Dynamic List field
	 *
	 * @since 2.04
	 * @param stdClass $field
	 *
	 * @return string
	 */
	protected function generate_dynamic_list_field_content( $field ) {
		$value = '[' . $this->get_dynamic_list_field_value_shortcode( $field ) . ']';

		return $this->generate_two_cell_shortcode_row( $field, $value );
	}

	protected function get_dynamic_list_field_value_shortcode( $field ) {
		if ( ! empty( $field->field_options['hide_field'] ) && ! empty( $field->field_options['form_select'] ) ) {

			$trigger_field_id = reset( $field->field_options['hide_field'] );
			$value = $trigger_field_id . ' show=' . $field->field_options['form_select'];

		} else {
			$value = $field->id;
		}

		return $value;
	}

	/**
	 * Generate the default array for a Dynamic List field
	 *
	 * @since 2.04
	 * @param stdClass $field
	 */
	protected function add_dynamic_list_field_array( $field ) {
		$value = $this->get_dynamic_list_field_value_shortcode( $field );

		$this->add_single_field_array( $field, $value );
	}

	/**
	 * Generate the HTML for a page break field
	 *
	 * @since 2.04
	 *
	 * @return string
	 */
	protected function generate_page_break_content( $field ) {
		if ( $this->is_plain_text_format() ) {
			return '';
		}

		$value = '<br/><br/>';

		return $this->generate_single_cell_shortcode_row( $field, $value );
	}
}
