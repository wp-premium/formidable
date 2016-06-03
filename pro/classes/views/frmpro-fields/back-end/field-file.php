<div class="frm_dropzone dz-clickable">
	<div class="dz-message">
		<span class="frm_icon_font frm_upload_icon"></span>
		<?php esc_html_e( 'Drop a file here or click to upload', 'formidable' ) ?>
		<div class="frm_small_text">
			<?php echo esc_html( sprintf( __( 'Maximum upload size: %sMB', 'formidable' ), FrmProFileField::get_max_file_size( $field['size'] ) ) ) ?>
		</div>
	</div>
</div>
<input type="hidden" name="<?php echo esc_attr( $field_name ) ?>" />
