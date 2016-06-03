<tr>
	<td><?php esc_html_e( 'Dynamic default value', 'formidable' ); ?>
		<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Set a dynamic default value in your field with a shortcode like [get param=whatever] or [frm-field-value field_id=x user_id=current]. If using [get param=whatever], the retrieved value must match one of the options in the field in order for that option to be selected.', 'formidable' ) ?>"></span>
	</td>
    <td>
		<input type="text" name="field_options[dyn_default_value_<?php echo absint( $field['id'] ) ?>]" id="dyn_default_value_<?php echo absint( $field['id'] ) ?>" value="<?php echo esc_attr( $field['dyn_default_value'] ) ?>" class="dyn_default_value frm_long_input" />
	</td>
</tr>