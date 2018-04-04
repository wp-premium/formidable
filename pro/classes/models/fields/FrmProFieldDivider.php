<?php

/**
 * @since 3.0
 */
class FrmProFieldDivider extends FrmFieldType {

	/**
	 * @var string
	 * @since 3.0
	 */
	protected $type = 'divider';

	public function default_html() {
		$default_html = <<<DEFAULT_HTML
<div id="frm_field_[id]_container" class="frm_form_field frm_section_heading form-field[error_class]">
<h3 class="frm_pos_[label_position][collapse_class]">[field_name]</h3>
[if description]<div class="frm_description">[description]</div>[/if description]
[collapse_this]
</div>
DEFAULT_HTML;
		return $default_html;
	}

	protected function builder_text_field( $name = '' ) {
		return '';
	}

	protected function field_settings_for_type() {
		$settings = array(
			'default_blank' => false,
			'required'      => false,
		);

		FrmProFieldsHelper::fill_default_field_display( $settings );
		return $settings;
	}

	protected function extra_field_opts() {
		return array(
			'slide'  => 0,
			'repeat' => 0,
			'repeat_limit' => '',
			'label'  => 'top',
		);
	}

	protected function alter_builder_classes( $classes ) {
		$classes = str_replace( ' frm_not_divider ', ' ', $classes );
		if ( FrmField::get_option( $this->field, 'repeat' ) ) {
			$classes .= ' repeat_section';
		} else {
			$classes .= ' no_repeat_section';
		}
		return $classes;
	}

	/**
	 * Get a JSON array of values from Repeating Section
	 *
	 * @since 2.03.08
	 *
	 * @param $value
	 * @param $atts
	 *
	 * @return mixed
	 */
	protected function prepare_display_value( $value, $atts ) {
		if ( ! FrmField::is_repeating_field( $this->field ) ) {
			return $value;
		}

		if ( ! is_array( $value ) && ! empty( $value ) && isset( $atts['format'] ) && $atts['format'] === 'json' ) {
			$child_entries = explode( ',', $value );
			$value = array();

			foreach ( $child_entries as $child_id ) {

				$pass_args = array(
					'format' => 'array',
					'include_blank' => true,
					'id' => $child_id,
					'user_info' => false,
				);

				$child_entry = FrmEntriesController::show_entry_shortcode( $pass_args );
				$value[] = $child_entry;
			}

			$value = json_encode( $value );
		}

		return $value;
	}

	/**
	 * If field type is section heading, add class so a bottom margin
	 * can be added to either the h3 or description
	 *
	 * @since 3.0
	 */
	protected function before_replace_html_shortcodes( $args, $html ) {
		$add_class = ' frm_section_spacing';
		if ( FrmField::is_option_true( $this->field, 'description' ) ) {
			$html = str_replace( 'frm_description', 'frm_description' . $add_class, $html );
		} else {
			$html = str_replace( '[label_position]', '[label_position]' . $add_class, $html );
		}
		return $html;
	}

	protected function after_replace_html_shortcodes( $args, $html ) {
		global $frm_vars;

		$html = str_replace( array( 'frm_none_container', 'frm_hidden_container', 'frm_top_container', 'frm_left_container', 'frm_right_container' ), '', $html );

		if ( isset( $frm_vars['collapse_div'] ) && $frm_vars['collapse_div'] ) {
			$html = "</div>\n" . $html;
			$frm_vars['collapse_div'] = false;
		}

		if ( isset( $frm_vars['div'] ) && $frm_vars['div'] && $frm_vars['div'] != $this->field['id'] ) {
			// close the div if it's from a different section
			$html = "</div>\n" . $html;
			$frm_vars['div'] = false;
		}

		if ( FrmField::is_option_true( $this->field, 'slide' ) ) {
			$trigger = ' frm_trigger';
			$collapse_div = '<div class="frm_toggle_container frm_grid_container" style="display:none;">';
		} else {
			$trigger = $collapse_div = '';
		}

		if ( FrmField::is_option_true( $this->field, 'repeat' ) ) {
			$errors = isset( $args['errors'] ) ? $args['errors'] : array();

			$input = $this->front_field_input( compact( 'errors', 'form' ), array() );

			if ( FrmField::is_option_true( $this->field, 'slide' ) ) {
				$input = $collapse_div . $input . '</div>';
			}

			$html = str_replace( '[collapse_this]', $input, $html );

		} else {
			$this->remove_close_div( $html );

			if ( strpos( $html, '[collapse_this]' ) !== false ) {
				$html = str_replace( '[collapse_this]', $collapse_div, $html );

				// indicate that a second div is open
				if ( ! empty( $collapse_div ) ) {
					$frm_vars['collapse_div'] = $this->field['id'];
				}
			}
		}

		$this->maybe_add_collapse_icon( $trigger, $html );
		$this->maybe_hide_section( $html );

		return str_replace( '[collapse_class]', $trigger, $html );
	}

	private function maybe_hide_section( &$html ) {
		if ( ! FrmAppHelper::is_admin_page() ) {
			$is_visible = FrmProFieldsHelper::is_field_visible_to_user( $this->field );
			if ( ! $is_visible ) {
				$html = str_replace( ' frm_section_heading ', ' frm_section_heading frm_hidden frm_invisible_section ', $html );
			}
		}
	}

	/**
	 * Remove the close div from HTML (specifically for divider field types)
	 *
	 * @since 3.0
	 * @param string $html - pass by reference
	 */
	private function remove_close_div( &$html ) {
		$end_div = '/\<\/div\>(\s*)?$/';
		if ( preg_match( $end_div, $html ) ) {
			global $frm_vars;
			// indicate that the div is open
			$frm_vars['div'] = $this->field['id'];

			$html = preg_replace( $end_div, '', $html );
		}
	}

	/**
	 * Add the collapse icon next to collapsible section headings
	 *
	 * @since 3.0
	 *
	 * @param string $trigger
	 * @param string $html, pass by reference
	 */
	private function maybe_add_collapse_icon( $trigger, &$html ) {
		if ( ! empty( $trigger ) ) {
			$style = FrmStylesController::get_form_style( $this->field['form_id'] );

			preg_match_all( "/\<h[2-6]\b(.*?)(?:(\/))?\>(.*?)(?:(\/))?\<\/h[2-6]>/su", $html, $headings, PREG_PATTERN_ORDER);

			if ( isset( $headings[3] ) && ! empty( $headings[3] ) ) {
				$header_text = reset( $headings[3] );
				$search_header_text = '>' . $header_text . '<';
				$old_header_html = reset( $headings[0] );

				$icon = '<i class="frm_icon_font frm_arrow_icon" aria-expanded="false" aria-label="' . esc_attr__( 'Toggle fields', 'formidable-pro' ) . '"></i>';
				if ( 'before' == $style->post_content['collapse_pos'] ) {
					$new_header_html = str_replace( $search_header_text, '>' . $icon . ' ' . $header_text . '<', $old_header_html );
				} else {
					$new_header_html = str_replace( $search_header_text, '>' . $header_text . $icon . '<', $old_header_html );
				}

				$html = str_replace( $old_header_html, $new_header_html, $html );
			}
		}
	}

	public function get_label_class() {
		return $this->get_field_column( 'label' );
	}

	public function get_container_class() {
		$classes = '';

		// If the top margin needs to be removed from a section heading
		if ( $this->field['label'] == 'none' ) {
			$classes .= ' frm_hide_section';
		}

		// If this is a repeating section that should be hidden with exclude_fields or fields shortcode, hide it
		if ( $this->field['repeat'] ) {
			global $frm_vars;
			if ( isset( $frm_vars['show_fields'] ) && ! empty( $frm_vars['show_fields'] ) && ! in_array( $this->field['id'], $frm_vars['show_fields'] ) && ! in_array( $this->field['field_key'], $frm_vars['show_fields'] ) ) {
				$classes .= ' frm_hidden';
			}
		}

		return $classes;
	}

	public function front_field_input( $args, $shortcode_atts ) {
		$args = $this->fill_display_field_values( $args );

		ob_start();
		FrmProNestedFormsController::display_front_end_repeating_section( $this->field, $args['field_name'], $args['errors'] );
		$input_html = ob_get_contents();
		ob_end_clean();

		return $input_html;
	}

	protected function prepare_import_value( $value, $atts ) {
		if ( FrmField::is_repeating_field( $this->field ) ) {
			$value = $this->get_new_child_ids( $value, $atts );
		}
		return $value;
	}
}
