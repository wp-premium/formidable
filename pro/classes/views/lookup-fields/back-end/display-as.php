<tr>
	<td>
		<label><?php _e( 'Display as', 'formidable' ) ?></label>
	</td>
	<td>
		<select name="field_options[data_type_<?php echo $field['id'] ?>]">
		<?php foreach ( $lookup_args['data_types'] as $type_value => $type_name ) {
			$selected = ( $field['data_type'] == $type_value ) ? ' selected="selected"':''; ?>
			<option value="<?php echo $type_value ?>"<?php echo $selected; ?>><?php echo $type_name ?></option>
		<?php } ?>
		</select>
	</td>
</tr>