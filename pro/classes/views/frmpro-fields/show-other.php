<?php

if ( $field['type'] == 'hidden' ) {
	if ( FrmAppHelper::is_admin() && ( ! isset( $args['action'] ) || $args['action'] != 'create' ) && FrmProFieldsHelper::field_on_current_page( $field['id'] ) ) { ?>
<div id="frm_field_<?php $field['id'] ?>_container" class="frm_form_field form-field frm_top_container">
<label class="frm_primary_label"><?php echo wp_kses_post( $field['name'] ) ?>:</label> <?php echo wp_kses_post( $field['value'] ); ?>
</div>
<?php
	}

	$field['html_id'] = $html_id;
	FrmProFieldsHelper::insert_hidden_fields( $field, $field_name, $field['value'] );

} else if ( $field['type'] == 'user_id' ) {
    $user_ID = get_current_user_id();
    $value = ( is_numeric($field['value']) || ( FrmAppHelper::is_admin() && $_POST && isset($_POST['item_meta'][$field['id']]) ) || (isset($args['action']) && $args['action'] == 'update') ) ? $field['value'] : ($user_ID ? $user_ID : '' );
    echo '<input type="hidden" id="'. esc_attr( $html_id ) .'" name="'. esc_attr( $field_name ) .'" value="'. esc_attr($value) .'" data-frmval="' . esc_attr( $value ) . '"/>'."\n";
    unset($value);

} else if ( $field['type'] == 'break' ) {
    global $frm_vars;

	$post_form_id = FrmAppHelper::get_post_param( 'form_id', 0, 'absint' );
	if ( isset( $frm_vars['prev_page'][ $field['form_id'] ] ) && $frm_vars['prev_page'][ $field['form_id'] ] == $field['field_order'] ) {
        echo FrmFieldsHelper::replace_shortcodes($field['custom_html'], $field, array(), $form); ?>
<input type="hidden" name="frm_next_page" class="frm_next_page" id="frm_next_p_<?php echo isset($frm_vars['prev_page'][$field['form_id']]) ? $frm_vars['prev_page'][$field['form_id']] : 0; ?>" value="" />
<?php
		if ( $field['form_id'] == $post_form_id && ! defined( 'DOING_AJAX' ) ) {
            $frm_vars['scrolled'] = true;
            //scroll to the form when we move to the next page
            FrmFormsHelper::get_scroll_js($field['form_id']);
        }

    }else{ ?>
<input type="hidden" name="frm_page_order_<?php echo esc_attr( $field['form_id'] ) ?>" value="<?php echo esc_attr( $field['field_order'] ); ?>" />
<?php
		if ( $field['form_id'] == $post_form_id && ! defined('DOING_AJAX') && ! isset( $frm_vars['scrolled'] ) ) {
            $frm_vars['scrolled'] = true;
            //scroll to the form when we move to the next page
            FrmFormsHelper::get_scroll_js($field['form_id']);
        }
    }
}