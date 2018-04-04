<?php

$form_key = empty( $field['form_select'] ) ? '' : FrmForm::get_key_by_id( $field['form_select'] );
if ( empty( $form_key ) ) {
	echo '<p>' . esc_html__( 'Select a form to import below', 'formidable-pro' ) . '</p>';
} elseif ( in_array( FrmAppHelper::get_server_value( 'REMOTE_ADDR' ), array( '127.0.0.1', '::1' ) ) ) { ?>
	<div class="frm_html_field_placeholder">
		<div class="howto button-secondary frm_html_field">
			<?php esc_html_e( 'This is a placeholder for an embedded form.', 'formidable' ) ?><br/>
			<?php esc_html_e( 'The extra form fields will show in the form.', 'formidable' ) ?>
		</div>
	</div>
<?php } else { ?>
	<div class="subform_section">
		<img src="<?php echo esc_url( 'http://s0.wordpress.com/mshots/v1/' . urlencode( FrmFormsHelper::get_direct_link( $form_key ) ) ) . '?w=600&h=350'; ?>" style="max-width:100%" alt="<?php echo esc_attr( 'This is a placeholder for an embedded form.', 'formidable-pro' ); ?>" />
	</div>
<?php
}
