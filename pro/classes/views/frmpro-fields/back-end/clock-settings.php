<tr>
	<td>
		<label><?php esc_html_e( 'Clock Settings', 'formidable-pro' ) ?></label>
	</td>
	<td>
		<select name="field_options[clock_<?php echo absint( $field['id'] ) ?>]">
			<option value="12" <?php selected($field['clock'], 12) ?>>12</option>
			<option value="24" <?php selected($field['clock'], 24) ?>>24</option>
		</select>
		<span class="howto" style="padding-right:10px;"><?php esc_html_e( 'hour clock', 'formidable-pro' ); ?></span>

		<input type="text" name="field_options[step_<?php echo absint( $field['id'] ) ?>]" value="<?php echo esc_attr( $field['step'] ); ?>" size="3" />
		<span class="howto" style="padding-right:10px;"><?php esc_html_e( 'minute step', 'formidable-pro' ) ?></span>

		<input type="text" name="field_options[start_time_<?php echo absint( $field['id'] ) ?>]" id="start_time_<?php echo absint( $field['id'] ) ?>" value="<?php echo esc_attr( $field['start_time'] ) ?>" size="5"/>
		<span class="howto" style="padding-right:10px;"><?php esc_html_e( 'start time', 'formidable-pro' ) ?></span>

		<input type="text" name="field_options[end_time_<?php echo absint( $field['id'] ) ?>]" id="end_time_<?php echo absint( $field['id'] ) ?>" value="<?php echo esc_attr($field['end_time']) ?>" size="5"/>
		<span class="howto"><?php esc_html_e( 'end time', 'formidable-pro' ) ?></span>

		<p>
			<label for="single_time_<?php echo esc_attr( $field['id'] ) ?>">
				<input type="checkbox" name="field_options[single_time_<?php echo esc_attr( $field['id'] ) ?>]" id="single_time_<?php echo esc_attr( $field['id'] ) ?>" value="1" <?php echo FrmField::is_option_true( $field, 'single_time' ) ? 'checked="checked"' : ''; ?> />
				<?php esc_html_e( 'show a single time dropdown', 'formidable-pro' ); ?>
			</label>
		</p>
	</td>
</tr>
