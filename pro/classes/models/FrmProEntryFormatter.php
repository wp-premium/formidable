<?php

/**
 * @since 2.04
 */
class FrmProEntryFormatter extends FrmEntryFormatter {

	/**
	 * @var FrmProEntryValues
	 * @since 2.04
	 */
	protected $entry_values = null;

	/**
	 * Set the entry_values property
	 *
	 * @since 2.04
	 *
	 * @param array $atts
	 */
	protected function init_entry_values( $atts ) {
		$entry_atts = $this->prepare_entry_attributes( $atts );
		$this->entry_values = new FrmProEntryValues( $this->entry->id, $entry_atts );
	}

	/**
	 * Which fields to exclude
	 *
	 * @since 3.0
	 */
	protected function skip_fields() {
		$skip_fields = parent::skip_fields();

		$skip_pro_fields = array(
			'break',
			'divider',
			'end_divider',
			'form',
			'password',
			'credit_card',
		);

		return array_merge( $skip_fields, $skip_pro_fields );
	}

	/**
	 * Set the include_extras property
	 *
	 * @since 2.04
	 *
	 * @param array $atts
	 */
	protected function init_include_extras( $atts ) {
		parent::init_include_extras( $atts );

		foreach ( $this->include_extras as $key => $field_type ) {
			if ( in_array( $field_type, array( 'section', 'heading' ) ) ) {
				$this->include_extras[ $key ] = 'divider';
			} else if ( $field_type == 'page' ) {
				$this->include_extras[ $key ] = 'break';
			}
		}
	}

	/**
	 * Initialize the single_cell_fields property
	 *
	 * @since 3.0
	 */
	protected function init_single_cell_fields() {
		parent::init_single_cell_fields();

		$single_cell_fields = array(
			'break',
			'divider',
		);

		$this->single_cell_fields = array_merge( $this->single_cell_fields, $single_cell_fields );
	}

	/**
	 * Add a field value to the HTML table or plain text content
	 *
	 * @since 2.04
	 *
	 * @param FrmProFieldValue $field_value
	 * @param string $content
	 */
	protected function add_field_value_to_content( $field_value, &$content ) {
		$field_type = $field_value->get_field_type();

		if ( $field_value->has_child_entries() ) {
			$this->add_rows_for_field_with_child_entries( $field_value, $content );

		} else if ( $field_type === 'divider' ) {
			$this->add_section_to_content( $field_value, $content );

		} else if ( $field_type === 'end_divider' ) {
			$this->remove_section_placeholder( $content );

		} else {
			parent::add_field_value_to_content( $field_value, $content );
		}
	}

	/**
	 * Add a section heading to the content
	 *
	 * @since 3.0
	 *
	 * @param FrmProFieldValue $field_value
	 * @param string $content
	 */
	protected function add_section_to_content( $field_value, &$content ) {
		if ( $this->is_extra_field_included( $field_value ) ) {
			$content .= $this->section_placeholder();
			parent::add_field_value_to_content( $field_value, $content );
		}
	}

	/**
	 * Split the content at section placeholder and piece back together
	 *
	 * @since 3.0
	 *
	 * @param string $content
	 */
	protected function remove_section_placeholder( &$content ) {
		$section_pos = strpos( $content, $this->section_placeholder() );

		if ( $section_pos !== false ) {
			$section_substring = substr( $content, $section_pos );

			// Remove section substring from content
			$content = str_replace( $section_substring, '', $content );

			// Clean up section substring
			$section_substring = str_replace( $this->section_placeholder(), '', $section_substring );

			if ( $this->section_heading_has_children( $section_substring ) ) {

				// Add section substring to content
				$content .= $section_substring;
			}
		}
	}

	/**
	 * @param $section_substring
	 *
	 * @since 3.0
	 *
	 * @param string $section_substring
	 * @return bool
	 */
	protected function section_heading_has_children( $section_substring ) {
		$has_children = false;

		if ( $this->format === 'plain_text_block' ) {
			$has_children = substr_count( $section_substring, "\r\n" ) > 2;
		} else if ( $this->format === 'table' ) {
			$has_children = substr_count( $section_substring, '<tr' ) > 1;
		}

		return $has_children;
	}

	/**
	 * Get child values, then add both parent and child if there are child values
	 *
	 * @since 2.04
	 *
	 * @param FrmProFieldValue $field_value
	 * @param string $content
	 */
	protected function add_rows_for_field_with_child_entries( $field_value, &$content ) {
		$child_content = '';
		$this->append_child_entry_values( $field_value, $child_content );

		if ( $child_content !== '' ) {
			$this->append_parent_values( $field_value, $content );
			$content .= $child_content;
		}
	}

	/**
	 * Add an embedded form or repeating section to value list/table
	 *
	 * @since 2.04
	 *
	 * @param FrmProFieldValue $field_value
	 * @param string $content
	 */
	protected function append_parent_values( $field_value, &$content ) {
		if ( $this->include_field_in_content( $field_value ) ) {

			if ( $this->is_plain_text ) {
				$this->prepare_plain_text_display_value_for_extra_fields( $field_value, $display_value );
				$this->add_single_value_plain_text_row( $display_value, $content );
			} else {
				$this->prepare_html_display_value_for_extra_fields( $field_value, $display_value );
				$this->add_single_cell_html_row( $display_value, $content );
			}
		}
	}

	/**
	 * Append child entry values to table or plain text content
	 *
	 * @since 2.04
	 *
	 * @param FrmProFieldValue $field_value
	 * @param string $content
	 */
	protected function append_child_entry_values( $field_value, &$content ) {
		foreach ( $field_value->get_displayed_value() as $child_id => $field_values ) {

			foreach ( $field_values as $child_field_id => $child_field_info ) {
				$child_field_info->prepare_displayed_value( $this->atts );

				$this->add_field_value_to_content( $child_field_info, $content );
			}
		}
	}

	/**
	 * Prepare the display value for extra fields an HTML table
	 *
	 * @since 2.04
	 *
	 * @param FrmProFieldValue $field_value
	 * @param mixed $display_value
	 */
	protected function prepare_html_display_value_for_extra_fields( $field_value, &$display_value ) {
		switch ( $field_value->get_field_type() ) {

			case 'break':
				$display_value = '<br/><br/>';
				break;

			case 'divider':
				$display_value = '<h3>' . $field_value->get_field_label() . '</h3>';
				break;

			default:
				parent::prepare_html_display_value_for_extra_fields( $field_value, $display_value );
		}
	}

	/**
	 * Prepare a plain text value for extra fields
	 *
	 * @since 2.04
	 *
	 * @param FrmProFieldValue $field_value
	 * @param mixed $display_value
	 */
	protected function prepare_plain_text_display_value_for_extra_fields( $field_value, &$display_value ) {
		switch ( $field_value->get_field_type() ) {

			case 'break':
				$display_value = "\r\n\r\n";
				break;

			case 'divider':
				$display_value = "\r\n" . $field_value->get_field_label() . "\r\n";
				break;

			default:
				parent::prepare_plain_text_display_value_for_extra_fields( $field_value, $display_value );
		}
	}

	/**
	 * Push a single field to the values array
	 *
	 * @since 2.04
	 *
	 * @param FrmProFieldValue $field_value
	 * @param array $output
	 */
	protected function push_single_field_to_array( $field_value, &$output ) {
		$field_key = $this->get_key_or_id( $field_value );
		$field_type = $field_value->get_field_type();

		$add_child_array = ( 'divider' === $field_type || ( 'form' === $field_type && $this->atts['child_array'] ) ) && $field_value->has_child_entries();

		if ( $add_child_array ) {
			$this->push_children_to_array( compact( 'field_value', 'field_key' ), $output );

		} elseif ( 'form' === $field_type ) {
			$child_values = $field_value->get_displayed_value();
			if ( ! empty( $child_values ) ) {
				$child_values = reset( $child_values );
				$this->push_field_values_to_array( $child_values, $output );
			}
		} else {
			parent::push_single_field_to_array( $field_value, $output );
		}
	}

	/**
	 * @since 3.0
	 */
	private function push_children_to_array( $field_info, &$output ) {
		$field_key = $field_info['field_key'];
		$field_value = $field_info['field_value'];

		$output[ $field_key ] = array(
			'form' => $field_value->get_field_option('form_select'),
		);

		$count = 0;
		foreach ( $field_value->get_displayed_value() as $entry_id => $row_values ) {
			$index = 'i' . $entry_id;
			$output[ $field_key ][ $index ] = array();
			$this->push_field_values_to_array( $row_values, $output[ $field_key ][ $index ] );

			$this->push_repeating_field_values_to_array( $row_values, $count, $output );

			$count++;
		}
	}

	/**
	 * Add repeating field values to the array so they are not nested within section
	 *
	 * @since 2.04
	 *
	 * @param array $row_values
	 * @param int $index
	 * @param array $output
	 */
	protected function push_repeating_field_values_to_array( $row_values, $index, &$output ) {

		foreach ( $row_values as $field_value ) {
			/* @var FrmProFieldValue $field_value */

			if ( $this->include_repeating_field_in_array( $field_value ) ) {

				if ( ! isset( $output[ $this->get_key_or_id( $field_value ) ] ) ) {
					$output[ $this->get_key_or_id( $field_value ) ] = array();
				}

				$displayed_value = $this->prepare_display_value_for_array( $field_value->get_displayed_value() );
				$output[ $this->get_key_or_id( $field_value ) ][ $index ] = $displayed_value;

				$saved_value = $field_value->get_saved_value();
				if ( $displayed_value !== $saved_value ) {

					if ( ! isset( $output[ $this->get_key_or_id( $field_value ) ] ) ) {
						$output[ $this->get_key_or_id( $field_value ) . '-value' ] = array();
					}

					$output[ $this->get_key_or_id( $field_value ) . '-value' ][ $index ] = $field_value->get_saved_value();
				}
			}
		}
	}

	/**
	 * Determine if a repeating field should be included in the array
	 *
	 * @since 2.04
	 *
	 * @param FrmProFieldValue $field_value
	 *
	 * @return bool
	 */
	protected function include_repeating_field_in_array( $field_value ) {
		if ( $this->is_extra_field( $field_value ) ) {
			$include = $this->is_extra_field_included( $field_value );
		} else {
			$include = true;
		}

		return $include;
	}

	/**
	 * Prepare a field's display value for an HTML table
	 *
	 * @since 2.04
	 *
	 * @param mixed $display_value
	 * @param string $type
	 *
	 * @return mixed|string
	 */
	protected function prepare_display_value_for_html_table( $display_value, $type = '' ) {
		if ( $type === 'file' && is_array( $display_value ) ) {
			$display_value = implode( '<br/><br/>', $display_value );
		}

		$display_value = parent::prepare_display_value_for_html_table( $display_value, $type );

		return $display_value;
	}

	/**
	 * Section placeholder string
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	protected function section_placeholder() {
		return '{section_placeholder}';
	}
}
