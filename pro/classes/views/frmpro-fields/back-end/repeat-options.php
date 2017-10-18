<tr class="show_repeat_sec">
	<td><label><?php _e( 'Repeat Layout', 'formidable' ) ?></label></td>
	<td>
		<select name="field_options[format_<?php echo absint( $field['id'] ) ?>]">
			<option value=""><?php _e( 'Default: No automatic formatting', 'formidable' ) ?></option>
			<option value="inline" <?php selected($field['format'], 'inline') ?>><?php _e( 'Inline: Display each field and label in one row', 'formidable' ) ?></option>
			<option value="grid" <?php selected($field['format'], 'grid') ?>><?php _e( 'Grid: Display labels as headings above rows of fields', 'formidable' ) ?></option>
		</select>
		<input type="hidden" name="field_options[form_select_<?php echo absint( $field['id'] ) ?>]" value="<?php echo esc_attr( $field['form_select'] ) ?>" />
	</td>
</tr>
<tr class="show_repeat_sec">
    <td><label><?php _e( 'Repeat Limit', 'formidable' ) ?></label>
        <span class="frm_help frm_icon_font frm_tooltip_icon"
              title="<?php esc_attr_e( 'The maximum number of times the end user is allowed to duplicate this section of fields in one entry', 'formidable' ) ?>"></span>
    </td>
    <td>
        <input type="number" class="frm_repeat_limit" name="field_options[repeat_limit_<?php echo absint(
                $field['id'] ) ?>]" value="<?php echo esc_attr( $field['repeat_limit'] ) ?>" size="3" min="2" step="1" max="999"/>
    </td>
</tr>