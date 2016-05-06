<?php

// If read-only, add hidden fields to hold the values
if ( $disabled && $field['data_type'] != 'text' ) {
	foreach ( $saved_value_array as $v ) { ?>
<input name="<?php echo esc_attr( $field_name ) ?>" type="hidden" value="<?php echo esc_attr( $v ) ?>" <?php do_action('frm_field_input_html', $field) ?> />
<?php
	}
}

// Lookup Field Dropdown
if ( 'select' == $field['data_type'] ) {

	// If there are field options, show them in a dropdown
	if ( ! empty( $field['options'] ) ) {
		?>
<select <?php echo $disabled ?> name="<?php echo esc_attr( $field_name ) ?>" id="<?php echo esc_attr( $html_id ) ?>" <?php do_action('frm_field_input_html', $field) ?>>
<?php
		foreach ( $field['options'] as $opt ) {
			$opt_value = ( $opt == $field['lookup_placeholder_text'] ) ? '' : $opt;
			$selected = ( in_array( $opt_value, $saved_value_array ) ) ? ' selected="selected"' : ''; ?>
<option value="<?php echo esc_attr( $opt_value ) ?>"<?php echo $selected ?>><?php echo ($opt == '') ? ' ' : esc_html( $opt ); ?></option>
<?php
		}
?>
</select>
<?php
    }

} else if ( 'radio' == $field['data_type'] ) {
	 // Radio Button Lookup Field

	if ( ! empty( $field['options'] ) ) {
		// If there are field options, show them in a radio button field

		?><div class="frm_opt_container"><?php
		require( FrmAppHelper::plugin_path() .'/pro/classes/views/lookup-fields/front-end/radio-rows.php' );
		?></div><?php
    }
} else if ( 'checkbox' == $field['data_type'] ) {
	// Checkbox Lookup Field

	if ( ! empty( $field['options'] ) ) {

		?><div class="frm_opt_container"><?php
		require( FrmAppHelper::plugin_path() .'/pro/classes/views/lookup-fields/front-end/checkbox-rows.php' );
		?></div><?php
	}
} else if ( 'text' == $field['data_type'] ) {
	 // Text Lookup Field

	 ?><input type="text" id="<?php echo esc_attr( $html_id ) ?>" name="<?php echo esc_attr( $field_name ) ?>" value="<?php echo esc_attr( $field['value'] ) ?>" <?php do_action('frm_field_input_html', $field) ?><?php echo $disabled ?>/><?php
}