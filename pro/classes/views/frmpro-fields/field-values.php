<?php
if ( ! isset($new_field) || ! $new_field ) { ?>
<input type="text" name="<?php echo (isset($current_field_id)) ? 'field_options[hide_opt_'. $current_field_id .']' : $field_name ?>" value="" />
<?php
    return;
}

if ( ! isset($is_settings_page) ) {
	$is_settings_page = ( FrmAppHelper::simple_get( 'frm_action' ) == 'settings' );
    $anything = $is_settings_page ? '' : __( 'Anything', 'formidable' );
}

if ( $new_field->type == 'data' ) {

	if ( isset( $new_field->field_options['form_select'] ) && is_numeric( $new_field->field_options['form_select'] ) ) {
        $new_entries = FrmEntryMeta::getAll( array( 'it.field_id' => (int) $new_field->field_options['form_select'] ), '', ' LIMIT 300', true);
    }

    $new_field->options = array();
    if ( isset($new_entries) && ! empty($new_entries) ) {
        foreach ( $new_entries as $ent ) {
            $new_field->options[$ent->item_id] = $ent->meta_value;
        }
    }
} else if ( $new_field->type == 'lookup' ) {
	$new_field->options = FrmProLookupFieldsController::get_lookup_field_values_for_conditional_logic( $new_field );

} else if ( isset( $new_field->field_options['post_field'] ) && $new_field->field_options['post_field'] == 'post_status' ) {
    $new_field->options = FrmProFieldsHelper::get_status_options($new_field);
}



if ( isset( $new_field->field_options['post_field'] ) && $new_field->field_options['post_field'] == 'post_category' ) {
    if ( ! isset($field_name) ) {
        $field_name = 'field_options[hide_opt_'. $current_field_id .']';
    }

    $new_field = (array) $new_field;
    $new_field['value'] = (isset($field) && isset($field['hide_opt'][$meta_name])) ? $field['hide_opt'][$meta_name] : '';

    $new_field['exclude_cat'] = (isset($new_field['field_options']['exclude_cat'])) ? $new_field['field_options']['exclude_cat'] : '';

    $cat_dropdown = FrmFieldsHelper::dropdown_categories( array(
        'name' => $field_name .'[]',
        'id' => $field_name,
        'field' => $new_field,
        'show_option_all' => ( ( $new_field['type'] == 'data' && ( ! isset($field_type) || ( isset($field_type) && $field_type == 'data' ) ) ) ? $anything : ' ' )
    ) );
    // Set first value in category dropdown to empty string instead of 0
    $cat_dropdown = str_replace("value='0'", 'value=""', $cat_dropdown);
    echo $cat_dropdown;
}else{
    if ( ! isset($field_name) ) {
        $field_name = 'field_options[hide_opt_'. $current_field_id .'][]';
    }

    $temp_field = (array) $new_field;
    foreach ( $new_field->field_options as $fkey => $fval ) {
        $temp_field[$fkey] = $fval;
        unset( $fkey, $fval );
    }

    if ( ! isset($val) ) {
        $val = (isset($field) && isset($field['hide_opt'][$meta_name])) ? $field['hide_opt'][$meta_name] : '';
    }

if ( in_array( $new_field->type, array( 'select', 'radio', 'checkbox', 'scale', 'data' ) ) ) { ?>
<select name="<?php echo esc_attr( $field_name ) ?>">
    <option value=""><?php echo ( $new_field->type == 'data' && ( ! isset($field_type) || ( isset($field_type) && $field_type == 'data' ) ) ) ? $anything : ''; ?></option>
<?php
	if ( $new_field->options ) {
	foreach ( $new_field->options as $opt_key => $opt ) {
        if ( $opt == '' ) {
            continue;
        }

        $field_val = apply_filters('frm_field_value_saved', $opt, $opt_key, $temp_field); //use VALUE instead of LABEL
        $opt = apply_filters('frm_field_label_seen', $opt, $opt_key, $temp_field);
        unset($field_array);
    ?>
	<option value="<?php echo esc_attr( $field_val ); ?>"<?php selected( esc_attr( $val ), esc_attr( $field_val ) ) ?>><?php echo FrmAppHelper::truncate( $opt, 25 ); ?></option>
<?php }
    } ?>
</select>
<?php
} else if ( $new_field->type == 'user_id' ) {
?>
<select name="<?php echo esc_attr( $field_name ) ?>">
    <option value=""> </option>
    <option value="current_user" <?php selected($val, 'current_user') ?>><?php _e( 'Current User', 'formidable' ) ?></option>
<?php
    $users = FrmProFieldsHelper::get_user_options();
    foreach ( $users as $user_id => $user_login ) {
        if ( empty($user_id) ) {
            continue;
        }
    ?>
    <option value="<?php echo esc_attr( $user_id ) ?>" <?php selected( $val, $user_id ) ?>><?php echo esc_html( $user_login ) ?></option>
<?php
    }
    unset($users);
?>
</select>
<?php
} else {
?>
<input type="text" name="<?php echo esc_attr( $field_name ) ?>" value="<?php echo esc_attr( $val ); ?>" />
<?php
}
unset($val);
}
