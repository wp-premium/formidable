<tr><td><label><?php _e( 'Repeat Links', 'formidable-pro' ) ?></label></td>
	<td>
		<select class="frm_repeat_format" name="field_options[format_<?php echo absint( $field['id'] ) ?>]">
			<option value=""><?php _e( 'Icons', 'formidable-pro' ) ?></option>
			<option value="text" <?php selected($field['format'], 'text') ?>><?php _e( 'Text links', 'formidable-pro' ) ?></option>
			<option value="both" <?php selected($field['format'], 'both') ?>><?php _e( 'Text links with icons', 'formidable-pro' ) ?></option>
		</select>
	</td>
</tr>
<tr class="frm_repeat_text <?php echo ( $field['format'] == '' ) ? 'hide-if-js' : ''; ?>">
	<td><label><?php _e( 'Add New Label', 'formidable-pro' ); ?></label></td>
	<td><input type="text" name="field_options[add_label_<?php echo absint( $field['id'] ) ?>]" value="<?php echo esc_attr($field['add_label']) ?>" />
	</td>
</tr>

<tr class="frm_repeat_text <?php echo ( $field['format'] == '' ) ? 'hide-if-js' : ''; ?>">
	<td><label><?php _e( 'Remove Label', 'formidable-pro' ) ?></label></td>
	<td><input type="text" name="field_options[remove_label_<?php echo absint( $field['id'] ) ?>]" value="<?php echo esc_attr( $field['remove_label'] ) ?>" />
	</td>
</tr>
