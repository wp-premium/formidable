
<div class="field-group field-group-border clearfix frm-first-row">
	<label><?php esc_html_e( 'Font Size', 'formidable' ) ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'toggle_font_size' ) ); ?>" id="frm_toggle_font_size" value="<?php echo esc_attr( $style->post_content['toggle_font_size'] ); ?>" size="3" />
</div>

<div class="field-group clearfix frm-first-row">
	<label for="frm_progress_bg_color"><?php _e( 'On Color', 'formidable-pro' ) ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'toggle_on_color' ) ) ?>" id="frm_toggle_on_color" class="hex" value="<?php echo esc_attr( $style->post_content['toggle_on_color'] ) ?>" size="4" />
</div>

<div class="field-group clearfix frm-first-row">
	<label for="frm_progress_color"><?php _e( 'Off Color', 'formidable-pro' ) ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'toggle_off_color' ) ) ?>" id="frm_toggle_off_color" class="hex" value="<?php echo esc_attr( $style->post_content['toggle_off_color'] ) ?>" />
</div>
