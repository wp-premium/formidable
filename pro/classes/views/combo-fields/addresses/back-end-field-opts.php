<tr>
	<td>
		<label for="address_type_<?php echo esc_attr( $field['id'] ) ?>"><?php _e( 'Address Type', 'formidable' ) ?></label>
	</td>
    <td>
		<select name="field_options[address_type_<?php echo esc_attr( $field['id'] ) ?>]" id="address_type_<?php echo esc_attr( $field['id'] ) ?>">
			<option value="international" <?php selected( $field['address_type'], 'international' ) ?>><?php esc_html_e( 'International', 'formidable' ) ?></option>
			<option value="us" <?php selected( $field['address_type'], 'us' ) ?>><?php esc_html_e( 'United States', 'formidable' ) ?></option>
			<option value="generic" <?php selected( $field['address_type'], 'generic' ) ?>><?php esc_html_e( 'Other - exclude country field', 'formidable' ) ?></option>
		</select>
	</td>
</tr>