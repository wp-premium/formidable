<tr>
	<td class="frm_150_width">
		<label><?php esc_html_e( 'Validate Password', 'formidable-pro' ); ?></label>
		<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'A strong password is at least 8 characters long and includes a an uppercase letter, a lowercase letter, a number, and a character.', 'formidable-pro' ); ?>"></span>
	</td>
	<td>
		<label for="frm_validate_pass_<?php echo esc_attr( $field['id'] ) ?>">
			<input type="checkbox" id="strong_pass_<?php echo esc_attr( $field['id'] ) ?>" name="field_options[strong_pass_<?php echo esc_attr( $field['id'] ) ?>]" value="1" <?php checked( $field['strong_pass'], 1 ) ?> />
			<?php esc_html_e( 'Require a strong password', 'formidable-pro' ); ?>
		</label>
	</td>
</tr>
