<?php
$is_multiple = FrmField::is_option_true( $field, 'multiple' );
$media_ids = maybe_unserialize( $field['value'] );
if ( $is_multiple ) {
	$media_ids = array_map( 'trim', array_filter( (array) $media_ids, 'is_numeric' ) );
} else if ( is_array( $media_ids ) ) {
	$media_ids = reset( $media_ids );
}
$field['value'] = $media_ids;

$input_name = $field_name . ( $is_multiple ? '[]' : '' );

if ( FrmField::is_read_only( $field ) ) {
	// Read only file upload field shows the entry without an upload button
	foreach ( (array) $media_ids as $media_id ) {
?>
<input type="hidden" value="<?php echo esc_attr( $media_id ) ?>" name="<?php echo esc_attr( $input_name ); ?>" />
<div class="frm_file_icon"><?php echo FrmProFieldsHelper::get_file_icon( $media_id ); ?></div>
<?php
	}

} else {
    FrmProFileField::setup_dropzone( $field, compact( 'field_name', 'html_id', 'file_name' ) );

	$extra_atts = '';
	$hidden_value = $media_ids;

	if ( $is_multiple ) {
		$hidden_value = '';
		$extra_atts = ' data-frmfile="' . esc_attr( $field['id'] ) . '" multiple="multiple" ';
	}

	global $frm_vars;
	$file_settings = $frm_vars['dropzone_loaded'][ $file_name ];

?>
<input type="hidden" name="<?php echo esc_attr( $input_name ) ?>" value="<?php echo esc_attr( $hidden_value ) ?>" data-frmfile="<?php echo esc_attr( $field['id'] ) ?>" />

<div class="frm_dropzone frm_<?php echo esc_attr( $file_settings['maxFiles'] == 1 ? 'single' : 'multi' ) ?>_upload" id="<?php echo esc_attr( $file_name ) ?>_dropzone">
	<div class="fallback">
		<input type="file" name="<?php echo esc_attr( $file_name . ( $is_multiple ? '[]' : '' ) ) ?>" id="<?php echo esc_attr( $html_id ) ?>" <?php echo $extra_atts; do_action( 'frm_field_input_html', $field ) ?> />
		<?php foreach ( $file_settings['mockFiles'] as $file ) { ?>
			<div class="dz-preview dz-complete dz-image-preview">
				<div class="dz-image">
					<img src="<?php echo esc_attr( $file['url'] ) ?>" alt="<?php echo esc_attr( $file['name'] ) ?>" />
				</div>
				<div class="dz-details">
					<div class="dz-filename">
						<span data-dz-name="">
							<a href="<?php echo esc_attr( $file['file_url'] ) ?>"><?php echo esc_html( $file['name'] ) ?></a>
						</span>
					</div>
				</div>
				<a class="dz-remove frm_remove_link" href="javascript:undefined;" data-frm-remove="<?php echo esc_attr( $field_name ) ?>">
					<?php esc_html_e( 'Remove file', 'formidable' ) ?>
				</a>
				<?php if ( $is_multiple ) { ?>
				<input type="hidden" name="<?php echo esc_attr( $field_name ); ?>[]" value="<?php echo esc_attr( $file['id'] ) ?>" />
				<?php } ?>
			</div>
		<?php } ?>
		<div class="frm_clearfix <?php echo is_admin() ? 'clear' : ''; ?>"></div>
		<?php include_once( FrmAppHelper::plugin_path() . '/pro/classes/views/frmpro-entries/loading.php' ); ?>
	</div>
	<div class="dz-message needsclick">
		<span class="frm_icon_font frm_upload_icon"></span>
		<span class="frm_upload_text"><?php esc_html_e( 'Drop a file here or click to upload', 'formidable' ) ?></span>
		<span class="frm_compact_text"><?php esc_html_e( 'Choose File', 'formidable' ); ?></span>
		<div class="frm_small_text">
			<?php echo esc_html( sprintf( __( 'Maximum upload size: %sMB', 'formidable' ), $file_settings['maxFilesize'] ) ) ?>
		</div>
	</div>
</div>
<?php
}
