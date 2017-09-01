<tr><td><label><?php _e( 'Range', 'formidable' ) ?></label></td>
	<td>
		<select name="field_options[minnum_<?php echo absint( $field['id'] ) ?>]" class="scale_minnum" id="scale_minnum_<?php echo absint( $field['id'] ) ?>">
			<?php for ( $i = 0; $i < 10; $i++ ) { ?>
				<option value="<?php echo absint( $i ) ?>" <?php selected( $field['minnum'], $i ) ?>>
					<?php echo absint( $i ) ?>
				</option>
			<?php } ?>
		</select> <?php _e( 'to', 'formidable' ) ?>
		<select name="field_options[maxnum_<?php echo absint( $field['id'] ) ?>]" class="scale_maxnum" id="scale_maxnum_<?php echo absint( $field['id'] ) ?>">
			<?php for( $i = 1; $i <= 20; $i++ ) { ?>
				<option value="<?php echo absint( $i ) ?>" <?php selected( $field['maxnum'], $i ) ?>>
					<?php echo absint( $i ) ?>
				</option>
			<?php } ?>
		</select>
	</td>
</tr>
<tr><td><label><?php _e( 'Stars', 'formidable' ) ?></label></td>
	<td>
		<label for="star_<?php echo absint( $field['id'] ) ?>">
			<input type="checkbox" value="1" name="field_options[star_<?php echo absint( $field['id'] ) ?>]" id="star_<?php echo absint( $field['id'] ) ?>" <?php checked( ( isset( $field['star'] ) ? $field['star'] : 0 ), 1 ) ?> />
			<?php _e( 'Show options as stars', 'formidable' ) ?>
		</label>
	</td>
</tr>