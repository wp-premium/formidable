<tr>
    <td>
		<label for="ajax_submit">
			<input type="checkbox" name="options[ajax_submit]" id="ajax_submit" value="1" <?php checked( $values['ajax_submit'], 1 ); ?> />
			<?php _e( 'Submit this form with AJAX', 'formidable' ) ?>
		</label>
		<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Submit the form without refreshing the page.', 'formidable' ) ?>"></span>
    </td>
</tr>
<tr>
	<td>
		<label for="js_validate">
			<input type="checkbox" name="options[js_validate]" id="js_validate" value="1" <?php checked( $values['js_validate'], 1 ); ?> />
			<?php _e( 'Validate this form with javascript', 'formidable' ) ?>
		</label>
		<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Required fields, email format, and number format can be checked instantly in your browser. You may want to turn this option off if you have any customizations to remove validation messages on certain fields.', 'formidable' ) ?>"></span>
	</td>
</tr>