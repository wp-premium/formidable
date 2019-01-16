<li id="frm_delete_field_<?php echo esc_attr( $field['id'] . '-' . $opt_key ) ?>_container" class="frm_single_option frm_other_option">
    <a href="javascript:void(0)" class="frm_single_visible_hover frm_icon_font frm_delete_icon" data-fid="<?php echo absint( $field['id'] ); ?>"> </a>

    <?php if ( $field['type'] != 'select' ) { ?>
        <input type="<?php echo esc_attr( $field['type'] ) ?>" name="<?php echo esc_attr( $field_name . ( $field['type'] == 'checkbox' ) ? '[' . $opt_key . ']' : '' ); ?>" <?php echo isset( $checked ) ? $checked : ''; ?> value="<?php echo esc_attr( $field_val ) ?>"/>
    <?php } ?>

    <label class="frm_ipe_field_option field_<?php echo absint( $field['id'] ); ?>_option" id="field_<?php echo esc_attr( $field['id'] . '-' . $opt_key ); ?>"><?php echo ( $opt == '' ) ? __( '(Blank)', 'formidable-pro' ) : $opt; ?></label>
	<input type="hidden" name="field_options[options_<?php echo esc_attr( $field['id'] ) ?>][<?php echo esc_attr( $opt_key ) ?>]" value="<?php echo esc_attr( $opt ) ?>" />

    <?php
    // Other Text field
    ?>
    <input class="dyn_default_value frm_other_input" id="field_<?php echo esc_attr( $field['field_key'] . '-' . $opt_key ); ?>" type="text" name="item_meta[other][<?php echo absint( $field['id'] ); ?>]<?php echo esc_attr( $field['type'] == 'checkbox' ? '[' . $opt_key . ']' : '' ); ?>" value="<?php echo esc_attr( $other_val ); ?>"/>

</li>

<?php
unset($field_val, $opt, $opt_key, $other_val);
