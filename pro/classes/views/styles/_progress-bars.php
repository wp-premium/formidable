
<div class="field-group field-group-border clearfix frm-first-row">
	<label for="frm_progress_bg_color"><?php _e( 'BG Color', 'formidable' ) ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('progress_bg_color') ) ?>" id="frm_progress_bg_color" class="hex" value="<?php echo esc_attr( $style->post_content['progress_bg_color'] ) ?>" size="4" />
</div>

<div class="field-group clearfix frm-first-row">
	<label for="frm_progress_color"><?php _e( 'Text Color', 'formidable' ) ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('progress_color') ) ?>" id="frm_progress_color" class="hex" value="<?php echo esc_attr( $style->post_content['progress_color'] ) ?>" />
</div>

<div class="field-group clearfix field-group-border">
	<label for="frm_progress_active_bg_color_color"><?php _e( 'Active BG', 'formidable' ) ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('progress_active_bg_color') ) ?>" id="frm_progress_active_bg_color_color" class="hex" value="<?php echo esc_attr( $style->post_content['progress_active_bg_color'] ) ?>" size="4" />
</div>

<div class="field-group clearfix">
	<label for="frm_progress_active_color"><?php _e( 'Active Text', 'formidable' ) ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('progress_active_color') ) ?>" id="frm_progress_active_color" class="hex" value="<?php echo esc_attr( $style->post_content['progress_active_color'] ) ?>" size="4" />
</div>

<div class="field-group field-group-border clearfix">
	<label for="frm_progress_border_color"><?php _e( 'Border Color', 'formidable' ) ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('progress_border_color') ) ?>" id="frm_progress_border_color" class="hex" value="<?php echo esc_attr( $style->post_content['progress_border_color'] ) ?>" size="4" />
</div>

<div class="field-group clearfix">
	<label for="frm_progress_border_size"><?php _e( 'Border Size', 'formidable' ) ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('progress_border_size') ) ?>" id="frm_progress_border_size" value="<?php echo esc_attr( $style->post_content['progress_border_size'] ) ?>" size="4" />
</div>

<div class="field-group clearfix">
	<label for="frm_progress_size"><?php _e( 'Circle Size', 'formidable' ) ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('progress_size') ) ?>" id="frm_progress_size" value="<?php echo esc_attr( $style->post_content['progress_size'] ) ?>" size="4" />
</div>
<div class="clear"></div>

