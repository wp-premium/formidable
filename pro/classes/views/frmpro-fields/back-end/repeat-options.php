<tr class="show_repeat_sec">
	<td><label><?php esc_html_e( 'Repeat Layout', 'formidable-pro' ); ?></label></td>
	<td>
		<select name="field_options[format_<?php echo absint( $field['id'] ) ?>]">
			<option value=""><?php esc_html_e( 'Default: No automatic formatting', 'formidable-pro' ); ?></option>
			<option value="inline" <?php selected( $field['format'], 'inline' ); ?>>
				<?php esc_html_e( 'Inline: Display each field and label in one row', 'formidable-pro' ); ?>
			</option>
			<option value="grid" <?php selected( $field['format'], 'grid' ); ?>>
				<?php esc_html_e( 'Grid: Display labels as headings above rows of fields', 'formidable-pro' ); ?>
			</option>
		</select>
		<input type="hidden" name="field_options[form_select_<?php echo absint( $field['id'] ) ?>]" value="<?php echo esc_attr( $field['form_select'] ) ?>" />
	</td>
</tr>
<tr class="show_repeat_sec">
	<td><label><?php esc_html_e( 'Repeat Limit', 'formidable-pro' ); ?></label>
        <span class="frm_help frm_icon_font frm_tooltip_icon"
              title="<?php esc_attr_e( 'The maximum number of times the end user is allowed to duplicate this section of fields in one entry', 'formidable-pro' ) ?>"></span>
    </td>
    <td>
        <input type="number" class="frm_repeat_limit" name="field_options[repeat_limit_<?php echo absint( $field['id'] ); ?>]" value="<?php echo esc_attr( $field['repeat_limit'] ); ?>" size="3" min="2" step="1" max="999" />
    </td>
</tr>
