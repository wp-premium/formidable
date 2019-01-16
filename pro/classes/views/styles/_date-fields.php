<?php
$themes = FrmProStylesController::jquery_themes( $style->post_content['theme_css'] );
$use_themes = count( $themes ) > 1;
$is_default = 1 === $style->menu_order;
$show = $is_default ? 'frm_date_color' : 'frm_hidden'; // hide all settings in non-default theme
?>

<?php if ( ! $is_default ) { ?>
	<p class="howto">
		<?php esc_html_e( 'Make changes to the date colors in the default style.', 'formidable-pro' ); ?>
	</p>
<?php } ?>

<div class="field-group clearfix frm_full frm-first-row <?php echo esc_attr( $use_themes ? '' : 'frm_hidden' ); ?>">
	<select name="<?php echo esc_attr( $frm_style->get_field_name( 'theme_selector' ) ); ?>">
		<?php foreach ( $themes as $theme_name => $theme_title ) { ?>
			<option value="<?php echo esc_attr( $theme_name ); ?>" <?php selected( $theme_name, $style->post_content['theme_css'] ); ?>>
				<?php echo esc_html( $theme_title ); ?>
			</option>
		<?php } ?>
	</select>

	<input type="hidden" value="<?php echo esc_attr( $style->post_content['theme_css'] ); ?>" id="frm_theme_css" name="<?php echo esc_attr( $frm_style->get_field_name( 'theme_css' ) ); ?>" />
	<input type="hidden" value="<?php echo esc_attr( $style->post_content['theme_name'] ); ?>" id="frm_theme_name" name="<?php echo esc_attr( $frm_style->get_field_name( 'theme_name' ) ); ?>" />
</div>

<div class="field-group field-group-border clearfix frm-first-row <?php echo esc_attr( $use_themes ? 'frm_hidden' : $show ); ?>">
	<label for="frm_date_head_bg_color"><?php esc_html_e( 'Head Color', 'formidable-pro' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'date_head_bg_color' ) ); ?>" id="frm_date_head_bg_color" class="hex" value="<?php echo esc_attr( $style->post_content['date_head_bg_color'] ); ?>" size="4" />
</div>

<div class="field-group clearfix frm-first-row <?php echo esc_attr( $use_themes ? 'frm_hidden' : $show ); ?>">
	<label for="frm_date_head_color"><?php esc_html_e( 'Text Color', 'formidable-pro' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'date_head_color' ) ); ?>" id="frm_date_head_color" class="hex" value="<?php echo esc_attr( $style->post_content['date_head_color'] ); ?>" />
</div>

<div class="field-group clearfix frm-first-row <?php echo esc_attr( $use_themes ? 'frm_hidden' : $show ); ?>">
	<label for="frm_date_band_color"><?php esc_html_e( 'Band Color', 'formidable-pro' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'date_band_color' ) ); ?>" id="frm_date_band_color" class="hex" value="<?php echo esc_attr( $style->post_content['date_band_color'] ); ?>" />
</div>
<div class="clear"></div>
