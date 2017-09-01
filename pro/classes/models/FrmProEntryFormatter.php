<?php

/**
 * @since 2.04
 */
class FrmProEntryFormatter extends FrmEntryFormatter {

	/**
	 * @var FrmEntryValues
	 * @since 2.04
	 */
	protected $entry_values = null;

	/**
	 * @var array
	 * @since 2.04
	 */
	protected $skip_fields = array(
		'captcha',
		'break',
		'divider',
		'end_divider',
		'html',
		'form',
		'password',
		'credit_card',
	);

	/**
	 * FrmProEntryFormat constructor
	 *
	 * @since 2.04
	 *
	 * @param array $atts
	 */
	public function __construct( $atts ) {
		parent::__construct( $atts );

		$this->init_include_extras( $atts );
	}

	/**
	 * Set the entry_values property
	 *
	 * @since 2.04
	 *
	 * @param array $atts
	 */
	protected function init_entry_values( $atts ) {
		$atts['source'] = 'entry_formatter';
		$this->entry_values = new FrmProEntryValues( $this->entry->id, $atts );
	}

	/**
	 * Set the include_extras property
	 *
	 * @since 2.04
	 *
	 * @param array $atts
	 */
	protected function init_include_extras( $atts ) {
		if ( isset( $atts['include_extras'] ) && $atts['include_extras'] ) {
			$type_array = array_map( 'strtolower', array_map( 'trim', explode( ',', $atts['include_extras'] ) ) );

			foreach ( $type_array as $field_type ) {
				if ( in_array( $field_type, array( 'section', 'heading' ) ) ) {
					$this->include_extras[] = 'divider';
				} else if ( $field_type == 'page' ) {
					$this->include_extras[] = 'break';
				} else {
					$this->include_extras[] = $field_type;
				}
			};
		}
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
		if ( $field_value->has_child_entries() ) {
			$this->add_rows_for_field_with_child_entries( $field_value, $content );

		} else if ( $this->is_extra_field( $field_value ) ) {
			$this->add_row_for_extra_field( $field_value, $content );

		} else {
			$this->add_row_for_standard_field( $field_value, $content );
		}
	}

	/**
	 * Get child values, then add both parent and child if there are child values
	 *
	 * @since 2.04
	 *
	 * @param FrmProFieldValue $field_value
	 * @param string $content
	 */
	private function add_rows_for_field_with_child_entries( $field_value, &$content ) {
		$child_content = '';
		$this->append_child_entry_values( $field_value, $child_content );

		if ( $child_content !== '' ) {
			$this->append_parent_values( $field_value, $content );
			$content .= $child_content;
		}
	}

	/**
	 * Add an extra field to plain text or html table content
	 *
	 * @since 2.04
	 *
	 * @param FrmProFieldValue $field_value
	 * @param string $content
	 */
	private function add_row_for_extra_field( $field_value, &$content ) {
		if ( ! $this->include_field_in_content( $field_value ) ) {
			return;
		}

		if ( $this->format === 'plain_text_block' ) {
			$this->add_plain_text_row_for_included_extra( $field_value, $content );
		} else if ( $this->format === 'table' ) {
			$this->add_html_row_for_included_extra( $field_value, $content );
		}
	}

	/**
	 * Add a standard row to plain text or html table content
	 *
	 * @since 2.04
	 *
	 * @param FrmProFieldValue $field_value
	 * @param string $content
	 */
	private function add_row_for_standard_field( $field_value, &$content ) {
		if ( ! $this->include_field_in_content( $field_value ) ) {
			return;
		}

		if ( $this->format === 'plain_text_block' ) {
			$this->add_plain_text_row( $field_value->get_field_label(), $field_value->get_displayed_value(), $content );
		} else if ( $this->format === 'table' ) {
			$value_args = $this->package_value_args( $field_value );
			$this->add_html_row( $value_args, $content );
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
	private function append_parent_values( $field_value, &$content ) {
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
	 * Add a single cell row to an HTML table
	 *
	 * @since 2.04
	 *
	 * @param string $display_value
	 * @param string $content
	 */
	private function add_single_cell_html_row( $display_value, &$content ) {
		$display_value = $this->prepare_display_value_for_html_table( $display_value );

		$content .= $this->table_generator->generate_single_cell_table_row( $display_value );
	}

	/**
	 * Add a single value plain text row
	 *
	 * @since 2.04
	 *
	 * @param string $display_value
	 * @param string $content
	 */
	private function add_single_value_plain_text_row( $display_value, &$content ) {
		$content .= $this->prepare_display_value_for_plain_text_content( $display_value );
	}

	/**
	 * Append child entry values to table or plain text content
	 *
	 * @since 2.04
	 *
	 * @param FrmProFieldValue $field_value
	 * @param string $content
	 */
	private function append_child_entry_values( $field_value, &$content ) {
		foreach ( $field_value->get_displayed_value() as $child_id => $field_values ) {

			foreach ( $field_values as $child_field_id => $child_field_info ) {
				$this->add_field_value_to_content( $child_field_info, $content );
			}
		}
	}

	/**
	 * Add a row to table for included extra
	 *
	 * @since 2.04
	 *
	 * @param FrmProFieldValue $field_value
	 * @param string $content
	 */
	private function add_html_row_for_included_extra( $field_value, &$content ) {
		$this->prepare_html_display_value_for_extra_fields( $field_value, $display_value );

		if ( in_array( $field_value->get_field_type(), array( 'break', 'divider', 'html' ) ) ) {
			$this->add_single_cell_html_row( $display_value, $content );
		} else {
			$value_args = $this->package_value_args( $field_value );
			$this->add_html_row( $value_args, $content );
		}
	}

	/**
	 * Add a plain text row for included extra
	 *
	 * @since 2.04
	 *
	 * @param FrmProFieldValue $field_value
	 * @param string $content
	 */
	private function add_plain_text_row_for_included_extra( $field_value, &$content ) {
		$this->prepare_plain_text_display_value_for_extra_fields( $field_value, $display_value );

		if ( in_array( $field_value->get_field_type(), array( 'break', 'divider', 'html' ) ) ) {
			$this->add_single_value_plain_text_row( $display_value, $content );
		} else {
			$this->add_plain_text_row( $field_value->get_field_label(), $display_value, $content );
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
	private function prepare_html_display_value_for_extra_fields( $field_value, &$display_value ) {
		switch ( $field_value->get_field_type() ) {

			case 'break':
				$display_value = '<br/><br/>';
				break;

			case 'divider':
				$display_value = '<h3>' . $field_value->get_field_label() . '</h3>';
				break;

			default:
				$display_value = $field_value->get_displayed_value();
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
	private function prepare_plain_text_display_value_for_extra_fields( $field_value, &$display_value ) {
		$field_type = $field_value->get_field_type();
		if ( $field_type === 'break' ) {
			$display_value = "\r\n\r\n";
		} else if ( $field_type === 'divider' ) {
			$display_value = "\r\n" . $field_value->get_field_label() . "\r\n";
		} else {
			$display_value = $field_value->get_displayed_value() . "\r\n";
		}
	}

	/**
	 * Check if an extra field is included
	 *
	 * @since 2.04
	 *
	 * @param FrmProFieldValue $field_value
	 *
	 * @return bool
	 */
	protected function is_extra_field_included( $field_value ) {

		if ( in_array( $field_value->get_field_type(), $this->include_extras ) ) {

			if ( $field_value->is_empty_container() && ! $this->include_blank ) {
				$include = false;
			} else {
				$include = true;
			}

		} else {
			$include = false;
		}

		return $include;
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
		$field_key = $field_value->get_field_key();
		$field_type = $field_value->get_field_type();

		if ( $field_type == 'form' ) {
			$child_values = $field_value->get_displayed_value();
			$child_values = reset( $child_values );
			$this->push_field_values_to_array( $child_values, $output );

		} else if ( $field_type == 'divider' && $field_value->has_child_entries() ) {
			$output[ $field_key ] = array();

			$count = 0;
			foreach ( $field_value->get_displayed_value() as $row_values ) {
				$output[ $field_key ][ $count ] = array();

				$this->push_field_values_to_array( $row_values, $output[ $field_key ][ $count ] );
				$this->push_repeating_field_values_to_array( $row_values, $count, $output );

				$count++;
			}

		} else {
			parent::push_single_field_to_array( $field_value, $output );
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
	private function push_repeating_field_values_to_array( $row_values, $index, &$output ) {

		foreach ( $row_values as $field_value ) {
			/* @var FrmProFieldValue $field_value */

			if ( $this->include_repeating_field_in_array( $field_value ) ) {

				if ( ! isset( $output[ $field_value->get_field_key() ] ) ) {
					$output[ $field_value->get_field_key() ] = array();
				}

				$displayed_value = $this->prepare_display_value_for_array( $field_value->get_displayed_value() );
				$output[ $field_value->get_field_key() ][ $index ] = $displayed_value;

				$saved_value = $field_value->get_saved_value();
				if ( $displayed_value !== $saved_value ) {

					if ( ! isset( $output[ $field_value->get_field_key() ] ) ) {
						$output[ $field_value->get_field_key() . '-value' ] = array();
					}

					$output[ $field_value->get_field_key() . '-value' ][ $index ] = $field_value->get_saved_value();
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
	private function include_repeating_field_in_array( $field_value ) {
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

}