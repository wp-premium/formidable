<?php

/**
 * @since 3.0
 */
class FrmProFieldBreak extends FrmFieldType {

	/**
	 * @var string
	 * @since 3.0
	 */
	protected $type = 'break';

	/**
	 * @var bool
	 * @since 3.0
	 */
	protected $has_input = false;

	/**
	 * @var bool
	 * @since 3.0
	 */
	protected $has_html = false;

	protected function get_new_field_name() {
		return __( 'Next', 'formidable-pro' );
	}

	protected function field_settings_for_type() {
		$settings = array(
			'default_blank' => false,
			'required'      => false,
			'visibility'    => false,
			'description'   => false,
			'label_position' => false,
			'css'           => false,
			'options'       => true,
		);
		FrmProFieldsHelper::fill_default_field_display( $settings );
		return $settings;
	}

	protected function extra_field_opts() {
		return array(
			'show_hide' => 'hide',
		);
	}

	public function prepare_field_html( $args ) {
		global $frm_vars;

		$args = $this->fill_display_field_values( $args );

		FrmProFieldsHelper::set_field_js( $this->field );

		$post_form_id = FrmAppHelper::get_post_param( 'form_id', 0, 'absint' );
		$current_page = isset( $frm_vars['prev_page'][ $this->field['form_id'] ] ) ? $frm_vars['prev_page'][ $this->field['form_id'] ] : 0;
		$is_current_page = $current_page == $this->field['field_order'];

		$should_scroll = $is_current_page || ! isset( $frm_vars['scrolled'] );
		if ( $this->field['form_id'] == $post_form_id && ! defined('DOING_AJAX') && $should_scroll ) {
			$frm_vars['scrolled'] = true;
			//scroll to the form when we move to the next page
			FrmFormsHelper::get_scroll_js( $this->field['form_id'] );
		}

		if ( $is_current_page ) {
			$html = parent::prepare_field_html( $args );
		} else {
			$html = '<input type="hidden" name="frm_page_order_' . esc_attr( $this->field['form_id'] ) . '" id="frm_page_order_' . esc_attr( $this->field['form_id'] ) . '" value="' . esc_attr( $this->field['field_order'] ) . '" />';
		}

		return $html;
	}

	public function get_label_class() {
		return $this->get_field_column( 'label' );
	}

	public function front_field_input( $args, $shortcode_atts ) {
		global $frm_vars;
		$current_page = isset( $frm_vars['prev_page'][ $this->field['form_id'] ] ) ? $frm_vars['prev_page'][ $this->field['form_id'] ] : 0;
		return '<input type="hidden" name="frm_next_page" class="frm_next_page" id="frm_next_p_' . esc_attr( $current_page ) . '" value="" />';
	}
}
