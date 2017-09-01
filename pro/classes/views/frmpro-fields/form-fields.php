<?php
if ( 'date' == $field['type'] ) {
?>
<input type="text" id="<?php echo esc_attr( $html_id ) ?>" name="<?php echo esc_attr( $field_name ) ?>" value="<?php echo esc_attr( $field['value'] ) ?>" <?php do_action( 'frm_field_input_html', $field ) ?>/>
<?php

    if ( ! FrmField::is_read_only( $field ) ) {
        if ( ! isset($frm_vars['datepicker_loaded']) || ! is_array($frm_vars['datepicker_loaded']) ) {
            $frm_vars['datepicker_loaded'] = array();
        }

        if ( ! isset($frm_vars['datepicker_loaded'][ $html_id ]) ) {
            $static_html_id = FrmFieldsHelper::get_html_id($field);
            if ( $html_id != $static_html_id ) {
                // user wildcard for repeating fields
                $frm_vars['datepicker_loaded']['^'. $static_html_id] = true;
            } else {
                $frm_vars['datepicker_loaded'][$html_id] = true;
            }
        }

        FrmProFieldsHelper::set_field_js($field, (isset($entry_id) ? $entry_id : 0));
    }

} else if ( $field['type'] == 'time' ) {
	FrmProTimeField::show_time_field( $field, compact( 'html_id', 'field_name' ) );
} else if ( 'tag' == $field['type'] ) {
    if ( is_array($field['value']) ) {
        FrmProFieldsHelper::tags_to_list($field, $entry_id);
    }
?>
<input type="text" id="<?php echo esc_attr( $html_id ) ?>" name="<?php echo esc_attr( $field_name ) ?>" value="<?php echo esc_attr( $field['value'] ) ?>" <?php do_action( 'frm_field_input_html', $field ) ?>/>
<?php
} else if ( in_array($field['type'], array( 'number', 'password', 'range')) ) {
?>
<input type="<?php echo ( $frm_settings->use_html || $field['type'] == 'password' ) ? esc_attr( $field['type'] ) : 'text'; ?>" id="<?php echo esc_attr( $html_id ) ?>" name="<?php echo esc_attr( $field_name ) ?>" value="<?php echo esc_attr( $field['value'] ) ?>" <?php do_action( 'frm_field_input_html', $field ) ?>/>
<?php
} else if ( $field['type'] == 'phone' ) {
?>
<input type="<?php echo ( $frm_settings->use_html ) ? 'tel' : 'text'; ?>" id="<?php echo esc_attr( $html_id ) ?>" name="<?php echo esc_attr( $field_name ) ?>" value="<?php echo esc_attr( $field['value'] ) ?>" <?php do_action( 'frm_field_input_html', $field ) ?>/>
<?php

} else if ($field['type'] == 'image' ) { ?>
<input type="<?php echo ($frm_settings->use_html) ? 'url' : 'text'; ?>" id="<?php echo esc_attr( $html_id ) ?>" name="<?php echo esc_attr( $field_name ) ?>" value="<?php echo esc_attr( $field['value'] ) ?>" <?php do_action( 'frm_field_input_html', $field ) ?>/>
<?php if ( $field['value'] ) {
		echo FrmProFieldsHelper::get_display_value( $field['value'], $field, array( 'html' => true ) );
    }

} else if ( $field['type'] == 'scale' ) {
    require(FrmAppHelper::plugin_path() .'/pro/classes/views/frmpro-fields/10radio.php');

	if ( FrmField::is_option_true( $field, 'star' ) ) {
        if ( ! isset($frm_vars['star_loaded']) || ! is_array($frm_vars['star_loaded']) ) {
            $frm_vars['star_loaded'] = array(true);
        }
    }

// Rich Text for back-end
} else if ( $field['type'] == 'rte' && FrmAppHelper::is_admin() ) { ?>
<div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea frm_full_rte">
<?php
    wp_editor(str_replace('&quot;', '"', $field['value']), $html_id,
        array( 'dfw' => true, 'textarea_name' => $field_name)
    );
?>
</div>
<?php
// Rich text for front-end, including Preview page
} else if ($field['type'] == 'rte' ) {

    if ( ! isset($frm_vars['skip_rte']) || ! $frm_vars['skip_rte'] ) {
        $e_args = array( 'media_buttons' => false, 'textarea_name' => $field_name);
        if ( $field['max'] ) {
            $e_args['textarea_rows'] = $field['max'];
        }

        $e_args = apply_filters('frm_rte_options', $e_args, $field);

        if ( $field['size'] ) { ?>
<style type="text/css">#wp-field_<?php echo esc_attr( $field['field_key'] ) ?>-wrap{width:<?php echo esc_attr( $field['size'] ) . ( is_numeric( $field['size'] ) ? 'px' : '' ); ?>;}</style><?php
        }

		wp_editor( FrmAppHelper::esc_textarea( $field['value'], true ), $html_id, $e_args );

        // If submitting with Ajax or on preview page and tinymce is not loaded yet, load it now
        if ( ( FrmAppHelper::doing_ajax() || FrmAppHelper::is_preview_page() ) && ( ! isset($frm_vars['tinymce_loaded']) || ! $frm_vars['tinymce_loaded']) ) {
            add_action( 'wp_print_footer_scripts', '_WP_Editors::editor_js', 50 );
			add_action( 'wp_print_footer_scripts', '_WP_Editors::enqueue_scripts', 1 );
			$frm_vars['tinymce_loaded'] = true;
		}
        unset($e_args);
	} else {
?>
<textarea name="<?php echo esc_attr( $field_name ) ?>" id="<?php echo esc_attr( $html_id ) ?>" style="height:<?php echo ($field['max']) ? ( (int) $field['max'] * 17 ) : 125 ?>px;<?php
if ( ! $field['size'] ) {
    ?>width:<?php echo FrmStylesController::get_style_val('field_width');
} ?>" <?php do_action( 'frm_field_input_html', $field ) ?>><?php echo FrmAppHelper::esc_textarea($field['value']) ?></textarea>
<?php
    }
} else if ( $field['type'] == 'file' ) {
	include( FrmAppHelper::plugin_path() . '/pro/classes/views/frmpro-fields/front-end/' . $field['type'] . '.php');

} else if ( $field['type'] == 'data' ) { ?>
<?php require(FrmAppHelper::plugin_path() .'/pro/classes/views/frmpro-fields/data-options.php'); ?>
<?php

} else if ( $field['type'] == 'form' ) {
    FrmProNestedFormsController::display_front_end_embedded_form( $field, $field_name, $errors );

} else if ( 'divider' == $field['type'] ) {
    FrmProNestedFormsController::display_front_end_repeating_section( $field, $field_name, $errors );

} else if ( 'lookup' == $field['type'] ) {
	FrmProLookupFieldsController::get_front_end_lookup_field_html( $field, $field_name, $html_id );
}
