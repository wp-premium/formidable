<?php
// Check if field is read only
$disabled = ( FrmField::is_read_only( $field ) && ! FrmAppHelper::is_admin() ) ?  ' disabled="disabled"' : '';

// Dynamic Dropdowns
if ( $field['data_type'] == 'select' ) {
    if ( ! empty( $field['options'] ) ) { ?>
<select <?php echo $disabled ?> name="<?php echo esc_attr( $field_name ) ?>" id="<?php echo esc_attr( $html_id ) ?>" <?php do_action('frm_field_input_html', $field) ?>>
<?php
		if ( $field['options'] ) {
			foreach ( $field['options'] as $opt_key => $opt ) {
$selected = ( $field['value'] == $opt_key || in_array($opt_key, (array) $field['value']) ) ? ' selected="selected"' : ''; ?>
<option value="<?php echo esc_attr( $opt_key ) ?>"<?php echo $selected ?>><?php echo ($opt == '') ? ' ' : esc_html( $opt ); ?></option>
<?php       }
        } ?>
</select>
<?php
    }

} else if ( $field['data_type'] == 'data' && is_numeric( $field['hide_opt'] ) && is_numeric( $field['form_select'] ) ) {
	$value = FrmEntryMeta::get_entry_meta_by_field($field['hide_opt'], $field['form_select']);
	echo wp_kses_post( $value ); ?>
    <input type="hidden" value="<?php echo esc_attr( $value ) ?>" name="<?php echo esc_attr( $field_name ) ?>" />
<?php } else if ( $field['data_type'] == 'data' && is_numeric( $field['hide_field'] ) && is_numeric( $field['form_select'] ) ) {
	$get_id = FrmAppHelper::simple_get( 'id' );
	if ( $_POST && isset( $_POST['item_meta'] ) ) {
		$observed_field_val = $_POST['item_meta'][ $field['hide_field'] ];
	} else if ( $get_id ) {
		$observed_field_val = FrmEntryMeta::get_entry_meta_by_field( $get_id, $field['hide_field'] );
	}

    if ( isset( $observed_field_val ) && is_numeric( $observed_field_val ) ) {
        $value = FrmEntryMeta::get_entry_meta_by_field($observed_field_val, $field['form_select']);
	} else {
        $value = '';
	}
?>
<p><?php echo wp_kses_post( $value ) ?></p>
<input type="hidden" value="<?php echo esc_attr($value) ?>" name="<?php echo esc_attr( $field_name ) ?>" />
<?php } else if ( $field['data_type'] == 'data' && ! is_array($field['value']) ) { ?>
<p><?php echo wp_kses_post( $field['value'] ); ?></p>
<input type="hidden" value="<?php echo esc_attr( $field['value'] ) ?>" name="<?php echo esc_attr( $field_name ) ?>" />
<?php } else if ( $field['data_type'] == 'text' && is_numeric( $field['form_select'] ) ) {
	$get_id = FrmAppHelper::simple_get( 'id' );
	if ( $_POST && isset( $_POST['item_meta'] ) ) {
		$observed_field_val = $_POST['item_meta'][ $field['hide_field'] ];
	} else if ( $get_id ) {
		$observed_field_val = FrmEntryMeta::get_entry_meta_by_field( $get_id, $field['hide_field'] );
	}

	if ( isset( $observed_field_val ) && is_numeric( $observed_field_val ) ) {
        $value = FrmEntryMeta::get_entry_meta_by_field($observed_field_val, $field['form_select']);
	} else {
        $value = '';
	}
?>
<input type="text" value="<?php echo esc_attr( $value ) ?>" name="<?php echo esc_attr( $field_name ) ?>" />

<?php
} else if ( $field['data_type'] == 'checkbox' ) {
    $checked_values = $field['value'];

    if ( ! empty($field['options']) ) {
		foreach ( $field['options'] as $opt_key => $opt ) {
            $checked = ( ( ! is_array($field['value']) && $field['value'] == $opt_key ) || ( is_array($field['value']) && in_array($opt_key, $field['value']) ) ) ? ' checked="checked"' : ''; ?>
<div class="<?php echo esc_attr( apply_filters( 'frm_checkbox_class', 'frm_checkbox', $field, $opt_key ) ) ?>">
	<label for="<?php echo esc_attr( $html_id .'-'. $opt_key ) ?>">
		<input type="checkbox" name="<?php echo esc_attr( $field_name ) ?>[]"  id="<?php echo esc_attr( $html_id .'-'. $opt_key ) ?>" value="<?php echo esc_attr( $opt_key ) ?>" <?php
    echo $checked . $disabled .' ';
    do_action( 'frm_field_input_html', $field );
?> /> <?php echo $opt ?>
	</label>
</div>
<?php   }
	}


} else if ( $field['data_type'] == 'radio' ) {
    if ( ! empty($field['options']) ) {
        foreach ( $field['options'] as $opt_key => $opt ) {
            $checked = ( in_array( $opt_key, (array) $field['value'] ) ) ? ' checked="checked"' : '';?>
<div class="<?php echo esc_attr( apply_filters( 'frm_radio_class', 'frm_radio', $field, $opt_key ) ) ?>">
	<label for="<?php echo esc_attr( $html_id .'-'. $opt_key ) ?>">
		<input type="radio" name="<?php echo esc_attr( $field_name ) ?>" id="<?php echo esc_attr( $html_id .'-'. $opt_key ) ?>" value="<?php echo esc_attr( $opt_key ) ?>" <?php
    echo $checked . $disabled .' ';
    do_action( 'frm_field_input_html', $field );
?> /> <?php echo $opt ?>
	</label>
</div>
<?php
        }
	}

}

FrmProFieldsHelper::maybe_get_hidden_dynamic_field_inputs( $field, array( 'disabled' => $disabled, 'field_name' => $field_name, 'html_id' => $html_id ) );