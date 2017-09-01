<div class="clear"></div>
<?php
if ( ! isset( $field['data_type'] ) || $field['data_type'] == 'data' ) {
	_e( 'This data is dynamic on change', 'formidable' );
} else if ( $field['data_type'] == 'select' ) { ?>
	<select name="<?php echo esc_attr( $field_name ) ?>" id="<?php echo esc_attr( $field_name ) ?>" <?php
		echo FrmField::is_multiple_select( $field ) ? 'multiple="multiple" ' : '';
		?>>
		<?php
		if ( $field['options'] ) {
			foreach ( $field['options'] as $opt_key => $opt ) {
				?>
				<option value="<?php echo esc_attr( $opt_key ) ?>" <?php selected( $field['default_value'], $opt_key ) ?>>
					<?php echo esc_html( $opt ) ?>
				</option>
			<?php }
		} else { ?>
			<option value="">&mdash; <?php _e( 'This data is dynamic on change', 'formidable' ) ?> &mdash;</option>
		<?php } ?>
	</select>
<?php
} else if ( $field['data_type'] == 'data' && is_numeric( $field['hide_opt'] ) ) {
	echo FrmEntryMeta::get_entry_meta_by_field( $field['hide_opt'], $field['form_select'] );

} else if ( $field['data_type'] == 'checkbox' ) {
	$checked_values = $field['default_value'];

	if ( $field['options'] ) {
		foreach ( $field['options'] as $opt_key => $opt ) {
			$checked = FrmAppHelper::check_selected( $checked_values, $opt_key ) ? ' checked="checked"' : '';
			?>
			<label for="<?php echo esc_attr( $field_name ) ?>">
				<input type="checkbox" name="<?php echo esc_attr( $field_name ) ?>[]" id="<?php echo esc_attr( $field_name ) ?>" value="<?php echo esc_attr( $opt_key ) ?>" <?php echo $checked ?>>
				<?php echo esc_html( $opt ) ?>
			</label><br/>
		<?php }
	} else {
		esc_html_e( 'There are no options', 'formidable' );
	}
} else if ($field['data_type'] == 'radio' ) {
	if ( $field['options'] ) {
		foreach ( $field['options'] as $opt_key => $opt ) {
			?>
			<input type="radio" name="<?php echo esc_attr( $field_name ) ?>" id="<?php echo esc_attr( $html_id . '-' . $opt_key ) ?>" value="<?php echo esc_attr( $opt_key ) ?>" <?php checked( $field['default_value'], $opt_key ) ?> />
			<?php echo esc_html( $opt ) ?><br/>
			<?php
		}
	} else {
		esc_html_e( 'There are no options', 'formidable' );
	}
} else {
	esc_html_e( 'This data is dynamic on change', 'formidable' );
}

if ( isset( $field['post_field'] ) && $field['post_field'] == 'post_category' ) { ?>
	<div class="clear"></div>
	<div class="frm-show-click" style="margin-top:5px;">
		<p class="howto"><?php echo FrmFieldsHelper::get_term_link( $field['taxonomy'] ) ?></p>
	</div>
	<?php
}
