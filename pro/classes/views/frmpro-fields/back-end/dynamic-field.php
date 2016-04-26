<tr><td><label><?php _e( 'Display as', 'formidable' ) ?></label></td>
	<td><select name="field_options[data_type_<?php echo $field['id'] ?>]" class="frm_toggle_mult_sel">
			<?php foreach ( $frm_field_selection['data']['types'] as $type_key => $type_name ) {
				$selected = (isset($field['data_type']) && $field['data_type'] == $type_key) ? ' selected="selected"':''; ?>
				<option value="<?php echo $type_key ?>"<?php echo $selected; ?>><?php echo $type_name ?></option>
			<?php } ?>
		</select>
	</td>
</tr>

<tr><td><?php _e( 'Entries', 'formidable' ) ?></td>
	<td><label for="restrict_<?php echo $field['id'] ?>"><input type="checkbox" name="field_options[restrict_<?php echo $field['id'] ?>]" id="restrict_<?php echo $field['id'] ?>" value="1" <?php echo ($field['restrict'] == 1) ? 'checked="checked"' : ''; ?>/> <?php _e( 'Limit selection choices to those created by the user filling out this form', 'formidable' ) ?></label></td>
</tr>