<tr><td><label><?php _e( 'Range', 'formidable' ) ?></label></td>
	<td>
		<select name="field_options[minnum_<?php echo $field['id'] ?>]">
			<?php for ( $i = 0; $i < 10; $i++ ) {
				$selected = (isset($field['minnum']) && $field['minnum'] == $i)? ' selected="selected"':''; ?>
				<option value="<?php echo $i ?>"<?php echo $selected; ?>><?php echo $i ?></option>
			<?php } ?>
		</select> <?php _e( 'to', 'formidable' ) ?>
		<select name="field_options[maxnum_<?php echo $field['id'] ?>]">
			<?php for( $i = 1; $i <= 20; $i++ ) {
				$selected = (isset($field['maxnum']) && $field['maxnum'] == $i)? ' selected="selected"':''; ?>
				<option value="<?php echo $i ?>"<?php echo $selected; ?>><?php echo $i ?></option>
			<?php } ?>
		</select>
	</td>
</tr>
<tr><td><label><?php _e( 'Stars', 'formidable' ) ?></label></td>
	<td><label for="star_<?php echo $field['id'] ?>"><input type="checkbox" value="1" name="field_options[star_<?php echo $field['id'] ?>]" id="star_<?php echo $field['id'] ?>" <?php checked((isset($field['star']) ? $field['star'] : 0), 1) ?> />
			<?php _e( 'Show options as stars', 'formidable' ) ?>
		</label>
	</td>
</tr>