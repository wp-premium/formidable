<tr>
    <td>
		<label for="ajax_submit">
			<input type="checkbox" name="options[ajax_submit]" id="ajax_submit" value="1" <?php checked( $values['ajax_submit'], 1 ); ?> />
			<?php _e( 'Submit this form with AJAX', 'formidable-pro' ) ?>
		</label>
		<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Submit the form without refreshing the page.', 'formidable-pro' ) ?>"></span>
    </td>
</tr>
