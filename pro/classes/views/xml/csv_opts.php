<div class="show_csv hide-if-js">
    <p><label class="frm_left_label"><?php _e( 'CSV Delimiter', 'formidable' ); ?></label>
        <input type="text" name="csv_del" value="<?php echo esc_attr($csv_del) ?>" />
    </p>

    <p><label class="frm_left_label"><?php _e( 'Import Into Form', 'formidable' ); ?></label>
        <select name="form_id">
        <?php foreach ( $forms as $form ) {
            if ( $form->is_template ) {
                continue;
            }
        ?>
            <option value="<?php echo (int) $form->id ?>"><?php echo ( $form->name == '' ) ? __( '(no title)' ) : esc_html( $form->name ) ?></option>
        <?php } ?>
        </select>
    </p>
	<p>
		<label for="csv_files">
			<input type="checkbox" name="csv_files" id="csv_files" value="1" <?php checked( $csv_files, 1 ) ?> />
			<?php esc_html_e( 'Import files. If you would like to import files from your CSV, check this box.', 'formidable' ); ?>
		</label>
	</p>
	<p class="howto"><?php _e( 'Note: Only entries can by imported via CSV.', 'formidable' ) ?></p>
</div>
