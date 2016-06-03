<tr>
	<td><?php esc_html_e( 'Placeholder text', 'formidable' ); ?>
		<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Set the placeholder text for your Lookup field.', 'formidable' ) ?>"></span>
	</td>
    <td>
		<input type="text" name="field_options[lookup_placeholder_text_<?php echo esc_attr( $field['id'] ) ?>]" id="lookup_placeholder_text_<?php echo esc_attr( $field['id'] ) ?>" value="<?php echo esc_attr( $field['lookup_placeholder_text'] ) ?>" class="frm_long_input" />
	</td>
</tr>
<tr id="frm_multiple_cont_<?php echo esc_attr( $field['id'] ) ?>" <?php echo ( $field['data_type'] != 'select' ) ? ' class="frm_hidden"' : ''; ?>>
	<td><?php esc_html_e( 'Autocomplete', 'formidable' ) ?></td>
	<td>
		<label for="autocom_<?php echo esc_attr( $field['id'] ) ?>">
			<input type="checkbox" name="field_options[autocom_<?php echo esc_attr( $field['id'] ) ?>]" id="autocom_<?php echo esc_attr( $field['id'] ) ?>" value="1" <?php checked( $field['autocom'], 1 ); ?> />
			<?php esc_html_e( 'enable autocomplete', 'formidable' ) ?>
		</label>
	</td>
</tr>