<?php
if ( FrmAppHelper::is_admin() ) { ?>
	<div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea frm_full_rte">
		<?php
		wp_editor( str_replace( '&quot;', '"', $field['value'] ), $html_id,
		array( 'dfw' => true, 'textarea_name' => $field_name )
	);
	?>
</div>
<?php
// Rich text for front-end, including Preview page
} elseif ( $field['type'] == 'rte' ) {

	if ( ! isset( $frm_vars['skip_rte'] ) || ! $frm_vars['skip_rte'] ) {
		$e_args = array( 'media_buttons' => false, 'textarea_name' => $field_name );
		if ( $field['max'] ) {
			$e_args['textarea_rows'] = $field['max'];
		}

		$e_args = apply_filters( 'frm_rte_options', $e_args, $field );

		if ( $field['size'] ) {
		?>
			<style type="text/css">#wp-field_<?php echo esc_attr( $field['field_key'] ) ?>-wrap{width:<?php echo esc_attr( $field['size'] ) . ( is_numeric( $field['size'] ) ? 'px' : '' ); ?>;}</style><?php
		}

		wp_editor( FrmAppHelper::esc_textarea( $field['value'], true ), $html_id, $e_args );

		// If submitting with Ajax or on preview page and tinymce is not loaded yet, load it now

		unset( $e_args );
	} else {
		?>
		<textarea name="<?php echo esc_attr( $field_name ) ?>" id="<?php echo esc_attr( $html_id ) ?>" style="height:<?php echo ( $field['max'] ) ? ( (int) $field['max'] * 17 ) : 125 ?>px;<?php
		if ( ! $field['size'] ) {
			?>width:<?php
			echo FrmStylesController::get_style_val('field_width');
		}
		?>" <?php
		do_action( 'frm_field_input_html', $field );
		?>><?php echo FrmAppHelper::esc_textarea( $field['value'] ) ?></textarea>
		<?php
	}
}
