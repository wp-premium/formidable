<?php

$message = __( 'The formidable/pro/classes/views/field-values.php template is deprecated as of version 2.03.05. Please use the FrmFieldsHelper::display_field_value_selector method instead.', 'formidable' );
trigger_error( $message );

// TODO: remove this file by August 2017. The code below is purely for reverse compatibility.

// Get selector field ID
if ( ! isset( $new_field ) || ! $new_field ) {
	$selector_field_id = 0;
} else {
	$selector_field_id = (int) $new_field->id;
}

$selector_args = array();

// Get field name
if ( isset( $field_name ) ) {
	$selector_args[ 'html_name' ] = $field_name;
} else if ( isset( $current_field_id ) ) {
	$selector_args['html_name'] = 'field_options[hide_opt_' . $current_field_id . '][]';
} else {
	return;
}

// Get value
if ( isset( $val ) ) {
	$selector_args['value' ] = $val;
} else {
	$selector_args['value'] = ( isset( $field ) && isset( $field['hide_opt'][$meta_name] ) ) ? $field['hide_opt'][$meta_name] : '';
}

// Get source
$is_settings_page = ( FrmAppHelper::simple_get( 'frm_action' ) == 'settings' );
$selector_args['source'] = ( $is_settings_page ) ? 'form_actions' : ( isset( $field_type ) ? $field_type : 'unknown' );

FrmFieldsHelper::display_field_value_selector( $selector_field_id, $selector_args );