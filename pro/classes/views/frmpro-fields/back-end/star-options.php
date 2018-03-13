<tr>
	<td>
		<label for="radio_maxnum_<?php echo absint( $field['id'] ) ?>">
			<?php _e( 'Maximum Rating', 'formidable-pro' ) ?>
		</label>
	</td>
	<td>
		<input type="hidden" name="field_options[minnum_<?php echo absint( $field['id'] ) ?>]" value="1" />

		<input type="number" name="field_options[maxnum_<?php echo absint( $field['id'] ) ?>]" class="radio_maxnum" id="radio_maxnum_<?php echo absint( $field['id'] ) ?>" value="<?php echo esc_attr( $field['maxnum'] ) ?>" min="1" max="50" step="1" size="4" />
	</td>
</tr>
