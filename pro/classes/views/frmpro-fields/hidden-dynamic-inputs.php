<?php _deprecated_file( basename( __FILE__ ), '3.0', null, 'FrmFieldType::maybe_include_hidden_values' ); ?>
<input type="hidden" name="<?php echo esc_attr( $field_name ) ?><?php echo ( $field['data_type'] == 'checkbox' ) ? '[]' : '' ?>" id="<?php echo esc_attr( $html_id ) ?>" value="<?php echo esc_attr( $value ) ?>" <?php do_action('frm_field_input_html', $field) ?> />
