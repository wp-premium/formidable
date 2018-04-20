
<div class="field-group field-group-border clearfix frm-first-row">
	<label><?php esc_html_e( 'Font Size', 'formidable' ) ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'slider_font_size' ) ); ?>" id="frm_slider_font_size" value="<?php echo esc_attr( $style->post_content['slider_font_size'] ); ?>" size="3" />
</div>

<div class="field-group clearfix frm-first-row">
	<label for="frm_progress_bg_color"><?php _e( 'Color', 'formidable-pro' ) ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'slider_color' ) ) ?>" id="frm_slider_color" class="hex" value="<?php echo esc_attr( $style->post_content['slider_color'] ) ?>" size="4" />
</div>

<div class="field-group clearfix frm-first-row">
	<label for="frm_progress_color"><?php _e( 'Bar Color', 'formidable-pro' ) ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'slider_bar_color' ) ) ?>" id="frm_slider_bar_color" class="hex" value="<?php echo esc_attr( $style->post_content['slider_bar_color'] ) ?>" />
</div>
