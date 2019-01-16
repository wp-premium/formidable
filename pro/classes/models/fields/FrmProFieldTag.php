<?php

/**
 * @since 3.0
 */
class FrmProFieldTag extends FrmFieldType {

	/**
	 * @var string
	 * @since 3.0
	 */
	protected $type = 'tag';
	protected $display_type = 'text';

	protected function field_settings_for_type() {
		$settings = array(
			'size'           => true,
			'clear_on_focus' => true,
		);

		FrmProFieldsHelper::fill_default_field_display( $settings );
		return $settings;
	}

	public function front_field_input( $args, $shortcode_atts ) {
		if ( is_array( $this->field['value'] ) ) {
			global $frm_vars;
			$entry_id = isset( $frm_vars['editing_entry'] ) ? $frm_vars['editing_entry'] : 0;
			FrmProFieldsHelper::tags_to_list( $this->field, $entry_id );
		}

		$input_html = $this->get_field_input_html_hook( $this->field );
		$this->add_aria_description( $args, $input_html );

		return '<input type="text" id="' . esc_attr( $args['html_id'] ) . '" name="' . esc_attr( $args['field_name'] ) . '" value="' . esc_attr( $this->field['value'] ) . '" ' . $input_html . '/>';
	}

	/**
	 * Create new tags
	 *
	 * @since 3.0
	 * @param array|string $value (the posted value)
	 * @param array $atts
	 *
	 * @return array|string $value
	 */
	public function get_value_to_save( $value, $atts ) {
		$this->create_new_tags( $value, $atts );
		return $value;
	}

	private function create_new_tags( $value, $atts ) {
		$tax_type = FrmField::get_option( $this->field, 'taxonomy' );
		$tax_type = empty( $tax_type ) ? 'frm_tag' : $tax_type;

		$tags = explode( ',', stripslashes( $value ) );
		$terms = array();

		if ( isset( $_POST['frm_wp_post'] ) ) {
			$_POST['frm_wp_post'][ $this->get_field_column('id') . '=tags_input' ] = $tags;
		}

		if ( $tax_type != 'frm_tag' ) {
			return;
		}

		foreach ( $tags as $tag ) {
			$slug = sanitize_title( $tag );
			if ( ! isset( $_POST['frm_wp_post'] ) ) {
				if ( ! term_exists( $slug, $tax_type ) ) {
					wp_insert_term( trim( $tag ), $tax_type, array( 'slug' => $slug ) );
				}
			}

			$terms[] = $slug;
		}

		wp_set_object_terms( $atts['entry_id'], $terms, $tax_type );
	}
}
