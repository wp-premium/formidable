<tr><td><label><?php _e( 'Clock Settings', 'formidable' ) ?></label></td>
	<td>
		<select name="field_options[clock_<?php echo $field['id'] ?>]">
			<option value="12" <?php selected($field['clock'], 12) ?>>12</option>
			<option value="24" <?php selected($field['clock'], 24) ?>>24</option>
		</select> <span class="howto" style="padding-right:10px;"><?php _e( 'hour clock', 'formidable' ) ?></span>

		<input type="text" name="field_options[step_<?php echo $field['id'] ?>]" value="<?php echo esc_attr($field['step']); ?>" size="3" />
		<span class="howto" style="padding-right:10px;"><?php _e( 'minute step', 'formidable' ) ?></span>

		<input type="text" name="field_options[start_time_<?php echo $field['id'] ?>]" id="start_time_<?php echo $field['id'] ?>" value="<?php echo esc_attr($field['start_time']) ?>" size="5"/>
		<span class="howto" style="padding-right:10px;"><?php _e( 'start time', 'formidable' ) ?></span>

		<input type="text" name="field_options[end_time_<?php echo $field['id'] ?>]" id="end_time_<?php echo $field['id'] ?>" value="<?php echo esc_attr($field['end_time']) ?>" size="5"/>
		<span class="howto"><?php _e( 'end time', 'formidable' ) ?></span>
	</td>
</tr>