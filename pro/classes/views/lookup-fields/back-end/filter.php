<tr>
	<td><label><?php _e( 'Filter options', 'formidable' ) ?></label></td>
    <td>
		<label for="lookup_filter_current_user_<?php echo $field['id'] ?>">
			<input type="checkbox" name="field_options[lookup_filter_current_user_<?php echo $field['id'] ?>]" id="lookup_filter_current_user_<?php echo $field['id'] ?>" value="1" <?php echo ( $field['lookup_filter_current_user'] ) ? 'checked="checked"' : ''; ?> />
    <?php _e( 'Limit options to those created by the current user', 'formidable' ) ?>
	</label>
		<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Does not apply to administrators.', 'formidable' ) ?>"></span>
    </td>
</tr>