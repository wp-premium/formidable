<tr><td><label><?php _e( 'Calendar Localization', 'formidable' ) ?></label></td>
	<td>
		<select name="field_options[locale_<?php echo $field['id'] ?>]">
			<?php foreach ( $locales as $locale_key => $locale ) {
				$selected = (isset($field['locale']) && $field['locale'] == $locale_key)? ' selected="selected"':''; ?>
				<option value="<?php echo $locale_key ?>"<?php echo $selected; ?>><?php echo $locale ?></option>
			<?php } ?>
		</select>
	</td>
</tr>
<tr>
	<td>
		<label><?php _e( 'Year Range', 'formidable' ) ?></label>
		<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Use four digit years or +/- years to make it dynamic. For example, use -5 for the start year and +5 for the end year.', 'formidable' ) ?>" ></span>
	</td>
	<td>
		<span><?php _e( 'Start Year', 'formidable' ) ?></span>
		<input type="text" name="field_options[start_year_<?php echo $field['id'] ?>]" value="<?php echo isset($field['start_year']) ? $field['start_year'] : ''; ?>" size="4"/>

		<span><?php _e( 'End Year', 'formidable' ) ?></span>
		<input type="text" name="field_options[end_year_<?php echo $field['id'] ?>]" value="<?php echo isset($field['end_year']) ? $field['end_year'] : ''; ?>" size="4"/>
	</td>
</tr>