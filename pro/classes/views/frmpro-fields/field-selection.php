<select name="field_options[form_select_<?php echo esc_attr( $current_field_id ) ?>]">
    <option value=""><?php _e( '&mdash; Select Field &mdash;', 'formidable-pro' ) ?></option>
	<?php
	foreach ( $fields as $field_option ) {
		if ( FrmField::is_no_save_field( $field_option->type ) ) {
            continue;
        }
    ?>
    <option value="<?php echo esc_attr( $field_option->id ) ?>"<?php
    if ( isset($selected_field) && is_object($selected_field) ) {
        selected($selected_field->id, $field_option->id);
    }
    ?>><?php
	echo ( '' == $field_option->name ) ? $field_option->id . ' ' . __( '(no label)', 'formidable-pro' ) : esc_html( $field_option->name );
    ?></option>
    <?php } ?>
</select>
