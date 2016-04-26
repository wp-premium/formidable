<tr>
	<td><?php _e( 'Placeholder text', 'formidable' ); ?>
		<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Set the placeholder text for your Lookup field.', 'formidable' ) ?>"></span>
	</td>
    <td><input type="text" name="field_options[lookup_placeholder_text_<?php echo $field['id'] ?>]" id="lookup_placeholder_text_<?php echo $field['id'] ?>" value="<?php echo esc_attr($field['lookup_placeholder_text']) ?>" class="frm_long_input" /></td>
</tr>
<tr id="frm_multiple_cont_<?php echo $field['id'] ?>" <?php echo ( $field['data_type'] != 'select' ) ? ' class="frm_hidden"' : ''; ?>>
	<td><?php _e( 'Autocomplete', 'formidable' ) ?></td>
	<td>
		<label for="autocom_<?php echo $field['id'] ?>"><input type="checkbox" name="field_options[autocom_<?php echo $field['id'] ?>]" id="autocom_<?php echo $field['id'] ?>" value="1" <?php echo ( $field['autocom'] ) ? 'checked="checked"' : ''; ?> />
    <?php _e( 'enable autocomplete', 'formidable' ) ?></label>
	</td>
</tr>