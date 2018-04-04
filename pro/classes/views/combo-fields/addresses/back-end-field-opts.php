<tr>
	<td>
		<label for="address_type_<?php echo esc_attr( $field['id'] ) ?>"><?php _e( 'Address Type', 'formidable-pro' ) ?></label>
	</td>
    <td>
		<select name="field_options[address_type_<?php echo esc_attr( $field['id'] ) ?>]" id="address_type_<?php echo esc_attr( $field['id'] ) ?>">
			<option value="international" <?php selected( $field['address_type'], 'international' ) ?>><?php esc_html_e( 'International', 'formidable-pro' ) ?></option>
			<option value="us" <?php selected( $field['address_type'], 'us' ) ?>><?php esc_html_e( 'United States', 'formidable-pro' ) ?></option>
			<option value="europe" <?php selected( $field['address_type'], 'europe' ) ?>><?php esc_html_e( 'Europe', 'formidable-pro' ) ?></option>
			<option value="generic" <?php selected( $field['address_type'], 'generic' ) ?>><?php esc_html_e( 'Other - exclude country field', 'formidable-pro' ) ?></option>
		</select>
	</td>
</tr>
