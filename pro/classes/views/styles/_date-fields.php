<?php
$jquery_themes = FrmStylesHelper::jquery_themes();
$alt_img_name = FrmProStylesController::get_datepicker_names( $jquery_themes );
?>

<div class="field-group clearfix frm-half frm-first-row">
	<label><?php _e( 'Theme', 'formidable-pro' ) ?></label>
	<select name="<?php echo esc_attr( $frm_style->get_field_name('theme_selector') ) ?>">
	    <?php foreach ( $jquery_themes as $theme_name => $theme_title ) { ?>
		<option value="<?php echo esc_attr( $theme_name ) ?>" id="90_<?php echo esc_attr( $alt_img_name[ $theme_name ] ); ?>" <?php selected( $theme_title, $style->post_content['theme_name'] ) ?>>
			<?php echo esc_html( $theme_title ) ?>
		</option>
        <?php } ?>
		<option value="-1" <?php selected( '-1', $style->post_content['theme_css'] ) ?>>
			&mdash; <?php _e( 'None', 'formidable-pro' ) ?> &mdash;
		</option>
	</select>
</div>

<div class="field-group clearfix frm-half frm-first-row frm_right_text">
    <img id="frm_show_cal" src="//jqueryui.com/resources/images/themeGallery/theme_90_<?php echo esc_attr( $alt_img_name[ $style->post_content['theme_css'] ] ) ?>.png" alt="" />
	<input type="hidden" value="<?php echo esc_attr($style->post_content['theme_css']) ?>" id="frm_theme_css" name="<?php echo esc_attr( $frm_style->get_field_name('theme_css') ) ?>" />
    <input type="hidden" value="<?php echo esc_attr($style->post_content['theme_name']) ?>" id="frm_theme_name" name="<?php echo esc_attr( $frm_style->get_field_name('theme_name') ) ?>" />
</div>
<div class="clear"></div>
