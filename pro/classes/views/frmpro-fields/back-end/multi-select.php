<tr id="frm_multiple_cont_<?php echo $field['id'] ?>" <?php echo ( $field['type'] == 'data' && (! isset($field['data_type']) || $field['data_type'] != 'select' ) ) ? ' class="frm_hidden"' : ''; ?>>
	<td><?php _e( 'Multiple select', 'formidable' ) ?></td>
	<td><label for="multiple_<?php echo $field['id'] ?>"><input type="checkbox" name="field_options[multiple_<?php echo $field['id'] ?>]" id="multiple_<?php echo $field['id'] ?>" value="1" <?php echo ( isset( $field['multiple'] ) && $field['multiple'] ) ? 'checked="checked"' : ''; ?> />
			<?php _e( 'enable multiselect', 'formidable' ) ?></label>
		<div style="padding-top:4px;">
			<label for="autocom_<?php echo $field['id'] ?>"><input type="checkbox" name="field_options[autocom_<?php echo $field['id'] ?>]" id="autocom_<?php echo $field['id'] ?>" value="1" <?php echo ( isset( $field['autocom'] ) && $field['autocom'] ) ? 'checked="checked"' : ''; ?> />
				<?php _e( 'enable autocomplete', 'formidable' ) ?></label>
		</div>
	</td>
</tr>