<?php if ( in_array( $field['type'], array( 'phone', 'tag', 'date', 'number', 'password' ) ) ) { ?>
    <input type="text" id="<?php echo $html_id ?>" name="<?php echo $field_name ?>" value="<?php echo esc_attr($field['default_value']); ?>" <?php do_action('frm_field_input_html', $field) ?> />
<?php } else if ( $field['type'] == 'hidden' ) { ?>
    <input type="text" id="<?php echo $html_id ?>" name="<?php echo $field_name ?>" value="<?php echo esc_attr($field['default_value']); ?>" class="dyn_default_value" />
    <p class="howto frm_clear"><?php _e( 'Note: This field will not show in the form. Enter the value to be hidden.', 'formidable' ) ?></p>
<?php } else if ( $field['type'] == 'time' ) { ?>
<select name="<?php echo $field_name ?>" id="<?php echo $html_id ?>" <?php do_action('frm_field_input_html', $field) ?>>
    <option value=""><?php echo $field['start_time'] ?></option>
    <option value="">...</option>
    <option value=""><?php echo $field['end_time'] ?></option>
</select>
<?php } else if ( $field['type'] == 'user_id' ) { ?>
    <p class="howto frm_clear"><?php _e( 'Note: This field will not show in the form, but will link the user id to it as long as the user is logged in at the time of form submission.', 'formidable' ) ?></p>
<?php } else if ( $field['type'] == 'image' ) { ?>
    <input type="url" id="<?php echo $html_id ?>" name="<?php echo $field_name ?>" value="<?php echo $field['default_value'] ?>" <?php do_action('frm_field_input_html', $field) ?> />
<?php
	} else if ( $field['type'] == 'scale' ) {
        require(FrmAppHelper::plugin_path() .'/pro/classes/views/frmpro-fields/10radio.php');

	} else if ( $field['type'] == 'rte' ) {
        /*
        <div id="<?php //echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea">
        	<?php //the_editor($field['default_value'], $field_name, 'title', false); ?>
        </div>
        */
?>
    <div class="frm_rte">
		<p class="howto"><?php _e( 'These buttons are for illustrative purposes only. They will be functional in your form.', 'formidable' ) ?></p>
        <textarea name="<?php echo $field_name ?>" rows="<?php echo $field['max']; ?>"><?php echo FrmAppHelper::esc_textarea($field['default_value']); ?></textarea>
    </div>
<?php } else if ( $field['type'] == 'html' ) { ?>
<div class="frm_html_field_placeholder">
<div class="howto button-secondary frm_html_field"><?php _e( 'This is a placeholder for your custom HTML.', 'formidable' ) ?><br/><?php _e( 'You can edit this content in the field options.', 'formidable' ) ?></div>
</div>
<?php } else if ( $field['type'] == 'data' ) { ?>
    <div class="clear"></div>
	<?php
	if ( ! isset($field['data_type']) || $field['data_type'] == 'data' ) {
		_e( 'This data is dynamic on change', 'formidable' );
	} else if ( $field['data_type'] == 'select' ) { ?>
        <select name="<?php echo $field_name ?>" id="<?php echo $field_name ?>">
            <?php
			if ( $field['options'] ) {
				foreach ( $field['options'] as $opt_key => $opt ) {
                    $selected = ($field['default_value'] == $opt_key)?(' selected="selected"'):(''); ?>
                    <option value="<?php echo $opt_key ?>"<?php echo $selected ?>><?php echo $opt ?></option>
            <?php }
			} else { ?>
                <option value="">&mdash; <?php _e( 'This data is dynamic on change', 'formidable' ) ?> &mdash;</option>
            <?php } ?>
        </select>
<?php
	} else if ( $field['data_type'] == 'data' && is_numeric( $field['hide_opt'] ) ) {
        echo FrmEntryMeta::get_entry_meta_by_field($field['hide_opt'], $field['form_select']);

		} else if ( $field['data_type'] == 'checkbox' ) {
			$checked_values = $field['default_value'];

			if ( $field['options'] ) {
				foreach ( $field['options'] as $opt_key => $opt ) {
					$checked = FrmAppHelper::check_selected( $checked_values, $opt_key ) ? ' checked="checked"' : '';
            ?>
                <label for="<?php echo $field_name ?>"><input type="checkbox" name="<?php echo $field_name ?>[]" id="<?php echo $field_name ?>" value="<?php echo esc_attr($opt_key) ?>" <?php echo $checked ?>> <?php echo $opt ?></label><br/>
            <?php }
            } else {
                _e( 'There are no options', 'formidable' );
            }
        } else if ($field['data_type'] == 'radio' ) {
            if ( $field['options'] ) {
                foreach ( $field['options'] as $opt_key => $opt ) {
                    $checked = ($field['default_value'] == $opt_key ) ? ' checked="true"':''; ?>
                <input type="radio" name="<?php echo $field_name ?>" id="<?php echo $html_id ?>-<?php echo esc_attr($opt_key) ?>" value="<?php echo esc_attr($opt_key) ?>" <?php echo $checked ?>> <?php echo $opt ?><br/>
            <?php
                }
            } else {
                _e( 'There are no options', 'formidable' );
            }
        } else {
            _e( 'This data is dynamic on change', 'formidable' );
        }

        if ( isset($field['post_field']) && $field['post_field'] == 'post_category' ) { ?>
            <div class="clear"></div>
            <div class="frm-show-click" style="margin-top:5px;">
                <p class="howto"><?php echo FrmFieldsHelper::get_term_link($field['taxonomy']) ?></p>
            </div>
        <?php
        }

	} else if ( $field['type'] == 'file' ) { ?>
	<input type="file" disabled="disabled" <?php echo FrmField::is_option_true( $field, 'size' ) ? 'style="width:' . $field['size'] . ( is_numeric( $field['size'] ) ? 'px' : '' ) . ';"' : ''; ?> />
    <input type="hidden" name="<?php echo $field_name ?>" />
<?php
} else if ( $field['type'] == 'form' ) {
    if ( empty($field['form_select']) ) {
        echo '<p>'. __( 'Select a form to import below', 'formidable' ) .'</p>';
    } else {
        $subfields = FrmField::get_all_for_form($field['form_select'], 5);
        foreach ( $subfields as $subfield ) { ?>
        <div class="subform_section subform_<?php echo $subfield->type ?>">
            <?php if ( $subfield->type != 'break') { ?>
            <label class="frm_primary_label"><?php echo $subfield->name ?></label>
            <?php } ?>
            <?php FrmProFieldsController::show($subfield, 'embed'); ?>
            <div class="clear"></div>
        </div>
<?php
        }
    }

} else if ( 'end_divider' == $field['type'] ) {
?>

<span class="show_repeat_sec repeat_icon_links repeat_format<?php echo $field['format']; ?>">
    <a href="javascript:void(0)" class="frm_remove_form_row <?php echo ( $field['format'] == '' ) ? '' : 'frm_button'; ?>"><i class="frm_icon_font frm_minus1_icon"> </i> <span class="frm_repeat_label"><?php echo $field['remove_label'] ?></span></a>
    <a href="javascript:void(0)" class="frm_add_form_row <?php echo ( $field['format'] == '' ) ? '' : 'frm_button'; ?>"><i class="frm_icon_font frm_plus1_icon"> </i> <span class="frm_repeat_label"><?php echo $field['add_label'] ?></span></a>
</span>
<?php
}
