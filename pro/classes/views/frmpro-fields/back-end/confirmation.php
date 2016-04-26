<tr><td><?php _e( 'Confirmation Field', 'formidable' ) ?></td>
	<td><select name="field_options[conf_field_<?php echo $field['id'] ?>]" class="conf_field" id="frm_conf_field_<?php echo $field['id'] ?>">
			<option value=""<?php selected($field['conf_field'], ''); ?>><?php _e( 'None', 'formidable' ) ?></option>
			<option value="inline"<?php selected($field['conf_field'], 'inline'); ?>><?php _e( 'Inline', 'formidable' ) ?></option>
			<option value="below"<?php selected($field['conf_field'], 'below'); ?>><?php _e( 'Below Field', 'formidable' ) ?></option>
		</select>
	</td>
</tr>