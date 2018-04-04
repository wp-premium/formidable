<tr>
	<td>
		<label><?php _e( 'Multiple files', 'formidable-pro' ) ?></label>
	</td>
	<td>
		<label for="multiple_<?php echo esc_attr( $field['id'] ) ?>">
			<input type="checkbox" name="field_options[multiple_<?php echo esc_attr( $field['id'] ) ?>]" id="multiple_<?php echo esc_attr( $field['id'] ) ?>" value="1" <?php echo checked( $field['multiple'], 1 ) ?> onchange="frm_show_div('limit_file_count_<?php echo absint( $field['id'] ) ?>',this.checked,true,'#')" />
			<?php _e( 'allow multiple files to be uploaded to this field', 'formidable-pro' ) ?>
		</label>
	</td>
</tr>
<tr>
	<td>
		<label><?php _e( 'Delete files', 'formidable-pro' ) ?></label>
	</td>
	<td>
		<label for="delete_<?php echo esc_attr( $field['id'] ) ?>">
			<input type="checkbox" name="field_options[delete_<?php echo esc_attr( $field['id'] ) ?>]" id="delete_<?php echo esc_attr( $field['id'] ) ?>" value="1" <?php echo ( isset( $field['delete'] ) && $field['delete'] ) ? 'checked="checked"' : ''; ?> />
			<?php _e( 'permanently delete old files when replaced or when the entry is deleted', 'formidable-pro' ) ?>
		</label>
	</td>
</tr>
<tr>
	<td>
		<label><?php _e( 'Email Attachment', 'formidable-pro' ) ?></label>
	</td>
	<td>
		<label for="attach_<?php echo esc_attr( $field['id'] ) ?>">
			<input type="checkbox" id="attach_<?php echo esc_attr( $field['id'] ) ?>" name="field_options[attach_<?php echo esc_attr( $field['id'] ) ?>]" value="1" <?php echo ( isset( $field['attach'] ) && $field['attach'] ) ? 'checked="checked"' : ''; ?> />
			<?php _e( 'attach this file to the email notification', 'formidable-pro' ) ?>
		</label>
	</td>
</tr>

<?php if ( $mimes ) { ?>
	<tr><td><label><?php _e( 'Allowed file types', 'formidable-pro' ) ?></label></td>
		<td>
			<label for="restrict_<?php echo esc_html( $field['id'] ) ?>_0">
				<input type="radio" name="field_options[restrict_<?php echo esc_html( $field['id'] ) ?>]" id="restrict_<?php echo esc_html( $field['id'] ) ?>_0" value="0" <?php FrmAppHelper::checked( $field['restrict'], 0 ); ?> onclick="frm_show_div('restrict_box_<?php echo absint( $field['id'] ) ?>',0,1,'.')" />
				<?php _e( 'All types', 'formidable-pro' ) ?>
			</label> &nbsp;

			<label for="restrict_<?php echo esc_html( $field['id'] ) ?>_1">
				<input type="radio" name="field_options[restrict_<?php echo esc_html( $field['id'] ) ?>]" id="restrict_<?php echo esc_html( $field['id'] ) ?>_1" value="1" <?php FrmAppHelper::checked( $field['restrict'], 1 ); ?> onclick="frm_show_div('restrict_box_<?php echo absint( $field['id'] ) ?>',1,1,'.')" />
				<?php _e( 'Specify allowed types', 'formidable-pro' ) ?>

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
		<label><?php esc_html_e( 'File Limits', 'formidable-pro' ) ?></label>
		<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php echo esc_attr( sprintf( __( 'Set the file size limit for each file uploaded. Your server settings allow a maximum of %d MB.', 'formidable-pro' ), FrmProFileField::get_max_file_size() ) ) ?>" ></span>
	</td>
	<td>
		<label for="size_<?php echo esc_attr( $field['id'] ) ?>">
			<input type="text" id="size_<?php echo esc_attr( $field['id'] ) ?>" name="field_options[size_<?php echo esc_attr( $field['id'] ) ?>]" value="<?php echo esc_attr( $field['size'] ); ?>" size="5" />
			<span class="howto"><?php esc_html_e( 'MB in size', 'formidable-pro' ) ?></span>
		</label> &nbsp;

		<label for="max_<?php echo esc_attr( $field['id'] ) ?>" id="limit_file_count_<?php echo esc_attr( $field['id'] ) ?>" class="<?php echo esc_attr( $field['multiple'] == 1 ? '' : 'frm_hidden' ); ?>">
			<input type="text" id="max_<?php echo esc_attr( $field['id'] ) ?>" name="field_options[max_<?php echo esc_attr( $field['id'] ) ?>]" value="<?php echo esc_attr( $field['max'] ); ?>" size="5" />
			<span class="howto"><?php esc_html_e( 'number of files', 'formidable-pro' ) ?></span>
		</label>
	</td>
</tr>

<tr>
	<td>
		<label><?php esc_html_e( 'Auto Resize', 'formidable-pro' ) ?></label>
		<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php echo esc_attr__( 'When a large image is uploaded, resize it before you save it to your site.', 'formidable-pro' ); ?>" ></span>
	</td>
	<td>
		<label>
			<input type="checkbox" id="resize_<?php echo esc_attr( $field['id'] ) ?>" name="field_options[resize_<?php echo esc_attr( $field['id'] ) ?>]" value="1" onchange="frm_show_div('resize_file_<?php echo absint( $field['id'] ) ?>',this.checked,1,'.')" <?php checked( $field['resize'], 1 ); ?> />
			<span class="howto"><?php esc_html_e( 'Resize files before upload', 'formidable-pro' ) ?></span>
		</label>
	</td>
</tr>
<tr class="resize_file_<?php echo esc_attr( $field['id'] ); ?> <?php echo esc_attr( $field['resize'] == 1 ? '' : 'frm_hidden' ); ?>">
	<td>
		<label><?php esc_html_e( 'New file size', 'formidable-pro' ); ?></label>
	</td>
	<td>
		<label id="new_size_<?php echo esc_attr( $field['id'] ) ?>">
			<span class="frm_screen_reader"><?php esc_html_e( 'The size the image should be resized to', 'formidable-pro' ); ?></span>
			<input type="text" id="new_size_<?php echo esc_attr( $field['id'] ) ?>" name="field_options[new_size_<?php echo esc_attr( $field['id'] ) ?>]" value="<?php echo esc_attr( absint( $field['new_size'] ) ); ?>" size="5" />
			<span class="howto"><?php esc_html_e( 'px', 'formidable-pro' ) ?></span>
		</label>

		<label id="resize_dir_<?php echo esc_attr( $field['id'] ) ?>">
			<span class="frm_screen_reader"><?php esc_html_e( 'Resize the image by width or height', 'formidable-pro' ); ?></span>
			<select name="field_options[resize_dir_<?php echo esc_attr( $field['id'] ) ?>]">
				<option value="width" <?php selected( $field['resize_dir'], 'width' ) ?>>
					<?php echo esc_html_e( 'wide', 'formidable-pro' ); ?>
				</option>
				<option value="height" <?php selected( $field['resize_dir'], 'height' ) ?>>
					<?php echo esc_html_e( 'high', 'formidable-pro' ); ?>
				</option>
			</select>
		</label>
	</td>
</tr>
