<tr>
	<td><label><?php _e( 'Filter options', 'formidable-pro' ) ?></label></td>
    <td>
		<label for="lookup_filter_current_user_<?php echo absint( $field['id'] ) ?>">
			<input type="checkbox" name="field_options[lookup_filter_current_user_<?php echo absint( $field['id'] ) ?>]" id="lookup_filter_current_user_<?php echo absint( $field['id'] ) ?>" value="1" <?php checked( $field['lookup_filter_current_user'], 1 ); ?> />
    <?php _e( 'Limit options to those created by the current user', 'formidable-pro' ) ?>
	</label>
		<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Does not apply to administrators.', 'formidable-pro' ) ?>"></span>
    </td>
</tr>
