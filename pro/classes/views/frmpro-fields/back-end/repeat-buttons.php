<tr>
	<td><label><?php esc_html_e( 'Repeat Links', 'formidable-pro' ); ?></label></td>
	<td>
		<select class="frm_repeat_format" name="field_options[format_<?php echo absint( $field['id'] ) ?>]">
			<option value=""><?php esc_html_e( 'Icons', 'formidable-pro' ); ?></option>
			<option value="text" <?php selected( $field['format'], 'text' ); ?>>
				<?php esc_html_e( 'Text links', 'formidable-pro' ); ?>
			</option>
			<option value="both" <?php selected( $field['format'], 'both' ); ?>>
				<?php esc_html_e( 'Text links with icons', 'formidable-pro' ); ?>
			</option>
		</select>
	</td>
</tr>
<tr class="frm_repeat_text <?php echo ( $field['format'] == '' ) ? 'hide-if-js' : ''; ?>">
	<td><label><?php esc_html_e( 'Add New Label', 'formidable-pro' ); ?></label></td>
	<td><input type="text" name="field_options[add_label_<?php echo absint( $field['id'] ) ?>]" value="<?php echo esc_attr($field['add_label']) ?>" />
	</td>
</tr>

<tr class="frm_repeat_text <?php echo ( $field['format'] == '' ) ? 'hide-if-js' : ''; ?>">
	<td><label><?php esc_html_e( 'Remove Label', 'formidable-pro' ); ?></label></td>
	<td><input type="text" name="field_options[remove_label_<?php echo absint( $field['id'] ) ?>]" value="<?php echo esc_attr( $field['remove_label'] ) ?>" />
	</td>
</tr>
