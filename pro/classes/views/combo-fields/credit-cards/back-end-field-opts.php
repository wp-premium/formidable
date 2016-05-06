<tr>
	<td>
		<label for="save_cc_<?php echo esc_attr( $field['id'] ) ?>"><?php _e( 'Credit Card Security', 'formidable' ) ?></label>
		<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr__( 'By default, only the last four digits of a credit card number will be saved. We recommend that you save as little credit card information on your site as possible.', 'formidable' ) ?>" ></span>
	</td>
    <td>
		<select name="field_options[save_cc_<?php echo esc_attr( $field['id'] ) ?>]" id="save_cc_<?php echo esc_attr( $field['id'] ) ?>">
			<option value="4" <?php selected( $field['save_cc'], '4' ) ?>><?php esc_html_e( 'Save only the last 4 digits', 'formidable' ) ?></option>
			<option value="0" <?php selected( $field['save_cc'], '0' ) ?>><?php esc_html_e( 'Do not store the card number', 'formidable' ) ?></option>
			<option value="16" <?php selected( $field['save_cc'], '16' ) ?>><?php esc_html_e( 'Store the whole card number (not recommended)', 'formidable' ) ?></option>
			<option value="-1" <?php selected( $field['save_cc'], '-1' ) ?>><?php esc_html_e( 'Do not store or POST card values', 'formidable' ) ?></option>
		</select>
		<input type="hidden" name="field_options[clear_on_focus_<?php echo esc_attr( $field['id'] ) ?>]" value="1" />
	</td>
</tr>
