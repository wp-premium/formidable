<tr>
	<td><label><?php esc_html_e( 'Display as', 'formidable-pro' ); ?></label></td>
	<td>
		<select name="field_options[data_type_<?php echo absint( $field['id'] ) ?>]" class="frm_toggle_mult_sel">
			<?php
			foreach ( $frm_field_selection['data']['types'] as $type_key => $type_name ) {
				$selected = ( isset( $field['data_type'] ) && $field['data_type'] == $type_key ) ? ' selected="selected"' : '';
				?>
				<option value="<?php echo esc_attr( $type_key ) ?>"<?php echo $selected; ?>>
					<?php echo esc_html( $type_name ) ?>
				</option>
			<?php } ?>
		</select>
	</td>
</tr>

<tr><td><?php esc_html_e( 'Entries', 'formidable-pro' ) ?></td>
	<td>
		<label for="restrict_<?php echo absint( $field['id'] ) ?>">
			<input type="checkbox" name="field_options[restrict_<?php echo absint( $field['id'] ) ?>]" id="restrict_<?php echo absint( $field['id'] ) ?>" value="1" <?php checked( $field['restrict'], 1 ) ?>/>
			<?php esc_html_e( 'Limit selection choices to those created by the user filling out this form', 'formidable-pro' ) ?>
		</label>
	</td>
</tr>
