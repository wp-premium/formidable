<?php

/**
 * @since 3.0
 */
class FrmProFieldHidden extends FrmFieldHidden {

	protected function field_settings_for_type() {
		$settings = parent::field_settings_for_type();

		$settings['autopopulate'] = true;
		$settings['visibility'] = false;
		$settings['calc'] = true;
		$settings['logic'] = false;
		$settings['unique'] = true;

		FrmProFieldsHelper::fill_default_field_display( $settings );
		return $settings;
	}

	public function prepare_field_html( $args ) {
		$html = '';
		$args = $this->fill_display_field_values( $args );
		if ( FrmAppHelper::is_admin() && ( ! isset( $args['action'] ) || $args['action'] != 'create' ) && FrmProFieldsHelper::field_on_current_page( $this->field['id'] ) ) {
			$html = '<div id="frm_field_' . esc_attr( $this->field['id'] ) . '_container" class="frm_form_field form-field frm_top_container">
<label class="frm_primary_label">' . wp_kses_post( $this->field['name'] ) . ':</label> ' . wp_kses_post( $this->field['value'] ) . '
</div>';
		}

		$this->field['html_id'] = $args['html_id'];

		ob_start();
		FrmProFieldsHelper::insert_hidden_fields( $this->field, $args['field_name'], $this->field['value'] );
		$html .= ob_get_contents();
		ob_end_clean();

		return $html;
	}
}
