<div class="frm_rte">
	<p class="howto"><?php esc_html_e( 'These buttons are for illustrative purposes only. They will be functional in your form.', 'formidable-pro' ) ?></p>
	<textarea name="<?php echo esc_attr( $field_name ) ?>" rows="<?php echo esc_attr( $field['max'] ); ?>"><?php echo FrmAppHelper::esc_textarea( $field['default_value'] ); ?></textarea>
</div>
