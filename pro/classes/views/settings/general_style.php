<p>
	<label for="frm_jquery_css">
		<input type="checkbox" value="1" id="frm_jquery_css" name="frm_jquery_css" <?php checked( $frm_settings->jquery_css, 1 ) ?> />
<?php _e( 'Include the jQuery CSS on ALL pages', 'formidable' ); ?>
	</label>
	<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'The styling for the date field calendar. Some users may be using this css on pages other than just the ones that include a date field.', 'formidable' ) ?>"></span>
</p>

<p>
	<label for="frm_accordion_js">
		<input type="checkbox" value="1" id="frm_accordion_js" name="frm_accordion_js" <?php checked( $frm_settings->accordion_js, 1 ) ?> />
    	<?php _e( 'Include accordion javascript', 'formidable' ); ?>
	</label>
	<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'If you have manually created an accordion form, be sure to include the javascript for it.', 'formidable' ) ?>" ></span>
</p>