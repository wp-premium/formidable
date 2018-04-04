<tr><td><?php _e( 'Confirmation Field', 'formidable-pro' ) ?></td>
	<td>
		<select name="field_options[conf_field_<?php echo absint( $field['id'] ) ?>]" class="conf_field" id="frm_conf_field_<?php echo absint( $field['id'] ) ?>">
			<option value="" <?php selected( $field['conf_field'], '' ); ?>>
				<?php esc_html_e( 'None', 'formidable-pro' ) ?>
			</option>
			<option value="inline" <?php selected( $field['conf_field'], 'inline' ); ?>>
				<?php esc_html_e( 'Inline', 'formidable-pro' ) ?>
			</option>
			<option value="below" <?php selected( $field['conf_field'], 'below' ); ?>>
				<?php esc_html_e( 'Below Field', 'formidable-pro' ) ?>
			</option>
		</select>
	</td>
</tr>
