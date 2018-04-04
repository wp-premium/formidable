<tr>
	<td>
		<label><?php _e( 'Display as', 'formidable-pro' ) ?></label>
	</td>
	<td>
		<select name="field_options[data_type_<?php echo absint( $field['id'] ) ?>]">
		<?php foreach ( $lookup_args['data_types'] as $type_value => $type_name ) { ?>
			<option value="<?php echo esc_attr( $type_value ) ?>" <?php selected( $field['data_type'], $type_value ) ?>>
				<?php echo esc_html( $type_name ) ?>
			</option>
		<?php } ?>
		</select>
	</td>
</tr>
