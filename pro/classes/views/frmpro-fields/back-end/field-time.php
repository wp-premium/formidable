<select name="<?php echo esc_attr( $field_name ) ?>" id="<?php echo esc_attr( $html_id ) ?>" <?php do_action( 'frm_field_input_html', $field ) ?>>
	<option value=""><?php echo esc_html( $field['start_time'] ) ?></option>
	<option value="">...</option>
	<option value=""><?php echo esc_html( $field['end_time'] ) ?></option>
</select>
