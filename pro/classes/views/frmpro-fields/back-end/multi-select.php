<tr id="frm_multiple_cont_<?php echo absint( $field['id'] ) ?>" <?php echo ( $field['type'] == 'data' && ( ! isset( $field['data_type'] ) || $field['data_type'] != 'select' ) ) ? ' class="frm_hidden"' : ''; ?>>
	<td><?php esc_html_e( 'Multiple select', 'formidable' ) ?></td>
	<td>
		<label for="multiple_<?php echo absint( $field['id'] ) ?>">
			<input type="checkbox" name="field_options[multiple_<?php echo absint( $field['id'] ) ?>]" id="multiple_<?php echo absint( $field['id'] ) ?>" value="1" class="frm_multiselect_opt" <?php checked( $field['multiple'], 1 ) ?> />
		<?php esc_html_e( 'enable multiselect', 'formidable' ) ?></label>
		<div style="padding-top:4px;">
			<label for="autocom_<?php echo absint( $field['id'] ) ?>">
				<input type="checkbox" name="field_options[autocom_<?php echo absint( $field['id'] ) ?>]" id="autocom_<?php echo absint( $field['id'] ) ?>" value="1" <?php checked( $field['autocom'], 1 ); ?> />
				<?php esc_html_e( 'enable autocomplete', 'formidable' ) ?>
			</label>
		</div>
	</td>
</tr>