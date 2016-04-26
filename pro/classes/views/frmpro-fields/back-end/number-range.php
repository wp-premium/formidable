<tr>
	<td style="width:150px">
		<label><?php _e( 'Number Range', 'formidable' ) ?>
			<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Browsers that support the HTML5 number field require a number range to determine the numbers seen when clicking the arrows next to the field.', 'formidable' ) ?>" ></span>
		</label>
	</td>
	<td><input type="text" name="field_options[minnum_<?php echo $field['id'] ?>]" value="<?php echo esc_attr($field['minnum']); ?>" size="5" /> <span class="howto"><?php echo _e( 'minimum', 'formidable' ) ?></span>
		<input type="text" name="field_options[maxnum_<?php echo $field['id'] ?>]" value="<?php echo esc_attr($field['maxnum']); ?>" size="5" /> <span class="howto"><?php _e( 'maximum', 'formidable' ) ?></span>
		<input type="text" name="field_options[step_<?php echo $field['id'] ?>]" value="<?php echo esc_attr($field['step']); ?>" size="5" /> <span class="howto"><?php _e( 'step', 'formidable' ) ?></span></td>
</tr>