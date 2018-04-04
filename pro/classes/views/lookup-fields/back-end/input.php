<?php

// Lookup Field Dropdown
if ( 'select' == $field['data_type'] ) {
?>
<select name="<?php echo esc_attr( $field_name ) ?>" id="<?php echo esc_attr( $html_id ) ?>" >
<?php
	foreach ( $field['options'] as $opt ) {
		$opt_value = ( $opt == $field['lookup_placeholder_text'] ) ? '' : $opt;
		$selected = ( in_array( $opt_value, $saved_value_array ) ) ? ' selected="selected"' : '';
?><option value="<?php echo esc_attr( $opt_value ); ?>"<?php echo $selected; ?>><?php
	echo ( $opt == '' ) ? ' ' : esc_html( $opt );
?></option>
<?php
	}
?>
</select>
<span id="frm_clear_on_focus_<?php echo esc_attr( $field['id'] ) ?>" class="frm_clear_on_focus frm-show-click">
<?php FrmFieldsHelper::show_default_blank_js( $field['default_blank'] ); ?>
<input type="hidden" name="field_options[default_blank_<?php echo esc_attr( $field['id'] ) ?>]" value="<?php echo esc_attr( $field['default_blank'] ) ?>" />
</span>

<?php

} else if ( 'radio' == $field['data_type'] || 'checkbox' == $field['data_type'] ) {
	// Checkbox and Radio Lookup Fields

	if ( empty( $field['options'] ) ) {
		?><span><?php _e( 'No options found', 'formidable-pro' ); ?></span><?php
	} else if ( count( $field['options'] ) == 1 && reset( $field['options'] ) == '' ) {
		?><span><?php _e( 'Options will populate dynamically in form', 'formidable-pro' ); ?></span><?php
	} else {
		?>
		<ul id="frm_field_<?php echo esc_attr( $field['id'] ); ?>_opts"
			class="frm_sortable_field_opts frm_clear<?php echo ( count( $field['options'] ) > 10 ) ? ' frm_field_opts_list' : ''; ?>"><?php
		foreach ( $field['options'] as $opt_key => $opt_value ) {
			$checked = ( in_array( $opt_value, $saved_value_array ) ) ? ' checked="checked"' : '';

			?>
			<li class="frm_single_option">
			<input type="<?php echo esc_attr( $field['data_type'] ); ?>" name="<?php echo esc_attr( $field_name ) ?>"
				   value="<?php echo esc_attr( $opt_value ) ?>"<?php echo $checked ?>/>
			<label class="frm_ipe_field_option field_<?php echo esc_attr( $field['id'] ) ?>_option"
				   id="<?php echo esc_attr( $html_id . '-' . $opt_key ) ?>"><?php echo esc_attr( $opt_value ) ?></label>
			</li><?php
		}
		unset( $opt_key, $checked, $opt_value );
		?>
		</ul><?php
	}
} else if ( 'text' == $field['data_type'] ) {
	// Text Lookup Field

	?><input type="text" id="<?php echo esc_attr( $html_id ) ?>" name="<?php echo esc_attr( $field_name ) ?>" value="<?php echo esc_attr( $field['default_value'] ) ?>"<?php echo $width_string ?> class="dyn_default_value" /><?php
	FrmFieldsHelper::clear_on_focus_html( $field, $display );
}
