<?php
foreach ( $field['options'] as $opt_key => $opt_value ) {
	$checked = ( in_array( $opt_value, $saved_value_array ) ) ? ' checked="checked"' : '';
	?>
	<div class="<?php echo esc_attr( apply_filters( 'frm_radio_class', 'frm_radio', $field, $opt_value ) ) ?>">
	<label for="<?php echo esc_attr( $html_id . '-' . $opt_key ) ?>">
		<input type="radio" name="<?php echo esc_attr( $field_name ) ?>"
			   id="<?php echo esc_attr( $html_id . '-' . $opt_key ) ?>"
			   value="<?php echo esc_attr( $opt_value ) ?>" <?php
		echo $checked . $disabled . ' ';
		do_action( 'frm_field_input_html', $field );
		?> /> <?php echo $opt_value ?>
	</label>
	</div>
	<?php
}
