<tr>
	<td>
		<label><?php _e( 'Multiple files', 'formidable' ) ?></label>
	</td>
	<td>
		<label for="multiple_<?php echo esc_attr( $field['id'] ) ?>">
			<input type="checkbox" name="field_options[multiple_<?php echo esc_attr( $field['id'] ) ?>]" id="multiple_<?php echo esc_attr( $field['id'] ) ?>" value="1" <?php echo checked( $field['multiple'], 1 ) ?> onchange="frm_show_div('limit_file_count_<?php echo absint( $field['id'] ) ?>',this.checked,true,'#')" />
			<?php _e( 'allow multiple files to be uploaded to this field', 'formidable' ) ?>
		</label>
	</td>
</tr>
<tr>
	<td>
		<label><?php _e( 'Delete files', 'formidable' ) ?></label>
	</td>
	<td>
		<label for="delete_<?php echo esc_attr( $field['id'] ) ?>">
			<input type="checkbox" name="field_options[delete_<?php echo esc_attr( $field['id'] ) ?>]" id="delete_<?php echo esc_attr( $field['id'] ) ?>" value="1" <?php echo ( isset( $field['delete'] ) && $field['delete'] ) ? 'checked="checked"' : ''; ?> />
			<?php _e( 'permanently delete old files when replaced or when the entry is deleted', 'formidable' ) ?>
		</label>
	</td>
</tr>
<tr>
	<td>
		<label><?php _e( 'Email Attachment', 'formidable' ) ?></label>
	</td>
	<td>
		<label for="attach_<?php echo esc_attr( $field['id'] ) ?>">
			<input type="checkbox" id="attach_<?php echo esc_attr( $field['id'] ) ?>" name="field_options[attach_<?php echo esc_attr( $field['id'] ) ?>]" value="1" <?php echo ( isset( $field['attach'] ) && $field['attach'] ) ? 'checked="checked"' : ''; ?> />
			<?php _e( 'attach this file to the email notification', 'formidable' ) ?>
		</label>
	</td>
</tr>

<?php if ( $mimes ) { ?>
	<tr><td><label><?php _e( 'Allowed file types', 'formidable' ) ?></label></td>
		<td>
			<label for="restrict_<?php echo esc_html( $field['id'] ) ?>_0">
				<input type="radio" name="field_options[restrict_<?php echo esc_html( $field['id'] ) ?>]" id="restrict_<?php echo esc_html( $field['id'] ) ?>_0" value="0" <?php FrmAppHelper::checked( $field['restrict'], 0 ); ?> onclick="frm_show_div('restrict_box_<?php echo absint( $field['id'] ) ?>',0,1,'.')" />
				<?php _e( 'All types', 'formidable' ) ?>
			</label> &nbsp;

			<label for="restrict_<?php echo esc_html( $field['id'] ) ?>_1">
				<input type="radio" name="field_options[restrict_<?php echo esc_html( $field['id'] ) ?>]" id="restrict_<?php echo esc_html( $field['id'] ) ?>_1" value="1" <?php FrmAppHelper::checked( $field['restrict'], 1 ); ?> onclick="frm_show_div('restrict_box_<?php echo absint( $field['id'] ) ?>',1,1,'.')" />
				<?php _e( 'Specify allowed types', 'formidable' ) ?>

				<span class="restrict_box_<?php echo absint( $field['id'] ) ?> <?php echo ( $field['restrict'] == 1 ? '' : 'frm_invisible' ) ?>">
					<select name="field_options[ftypes_<?php echo esc_attr( $field['id'] ) ?>][]" multiple="multiple" class="frm_multiselect">
						<?php foreach ( $mimes as $ext_preg => $mime ) { ?>
							<option value="<?php echo esc_attr( $ext_preg . '|||' . $mime ) ?>" <?php echo isset( $field['ftypes'][ $ext_preg ] ) ? ' selected="selected"' : ''; ?>>
								<?php echo esc_html( str_replace( '|', ', ', $ext_preg ) ); ?>
							</option>
						<?php } ?>
					</select>
				</span>
			</label>
		</td>
	</tr>
<?php } ?>

<tr>
	<td>
		<label><?php esc_html_e( 'File Limits', 'formidable' ) ?></label>
		<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php echo esc_attr( sprintf( __( 'Set the file size limit for each file uploaded. Your server settings allow a maximum of %d MB.', 'formidable' ), FrmProFileField::get_max_file_size() ) ) ?>" ></span>
	</td>
	<td>
		<label for="size_<?php echo esc_attr( $field['id'] ) ?>">
			<input type="text" id="size_<?php echo esc_attr( $field['id'] ) ?>" name="field_options[size_<?php echo esc_attr( $field['id'] ) ?>]" value="<?php echo esc_attr( $field['size'] ); ?>" size="5" />
			<span class="howto"><?php esc_html_e( 'MB in size', 'formidable' ) ?></span>
		</label> &nbsp;

		<label for="max_<?php echo esc_attr( $field['id'] ) ?>" id="limit_file_count_<?php echo esc_attr( $field['id'] ) ?>" class="<?php echo esc_attr( $field['multiple'] == 1 ? '' : 'frm_hidden' ); ?>">
			<input type="text" id="max_<?php echo esc_attr( $field['id'] ) ?>" name="field_options[max_<?php echo esc_attr( $field['id'] ) ?>]" value="<?php echo esc_attr( $field['max'] ); ?>" size="5" />
			<span class="howto"><?php esc_html_e( 'number of files', 'formidable' ) ?></span>
		</label>
	</td>
</tr>
