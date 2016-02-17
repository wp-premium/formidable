<?php if ( $display['type'] == 'radio' || $display['type'] == 'checkbox' || ( $display['type'] == 'data' && in_array( $display['field_data']['data_type'], array( 'radio', 'checkbox' ) ) ) ) { ?>
<tr><td><label><?php _e( 'Alignment', 'formidable' ) ?></label></td>
    <td>
        <select name="field_options[align_<?php echo $field['id'] ?>]">
            <option value="block" <?php selected($field['align'], 'block') ?>><?php _e( 'Multiple Rows', 'formidable' ); ?></option>
            <option value="inline" <?php selected($field['align'], 'inline') ?>><?php _e( 'Single Row', 'formidable' ); ?></option>
        </select>
    </td>
</tr>
<?php }

if ( in_array( $display['type'], array( 'radio', 'checkbox', 'select' ) ) && ( ! isset( $field['post_field'] ) || ( $field['post_field'] != 'post_category' ) ) ) { ?>
<tr><td><label><?php _e( 'Separate values', 'formidable' ); ?></label> <span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php echo sprintf( __( 'Add a separate value to use for calculations, email routing, saving to the database, and many other uses. The option values are saved while the option labels are shown in the form. Use [%s] to show the saved value in emails or views.', 'formidable' ), $field['id'] .' show=value' ) ?>" ></span></td>
    <td><label for="separate_value_<?php echo $field['id'] ?>"><input type="checkbox" name="field_options[separate_value_<?php echo $field['id'] ?>]" id="separate_value_<?php echo $field['id'] ?>" value="1" <?php checked($field['separate_value'], 1) ?> class="frm_toggle_sep_values" /> <?php _e( 'Use separate values', 'formidable' ); ?></label></td>
</tr>
<?php
}

if ( $display['default_value'] ) { ?>
<tr>
	<td><?php _e( 'Dynamic Default Value', 'formidable' ); ?>
		<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'If your radio, checkbox, dropdown, or user ID field needs a dynamic default value like [get param=whatever], insert it in the field options. If using a GET or POST value, it must match one of the options in the field in order for that option to be selected. Dynamic fields require the ID of the linked entry.', 'formidable' ) ?>"></span>
	</td>
    <td><input type="text" name="field_options[dyn_default_value_<?php echo $field['id'] ?>]" id="dyn_default_value_<?php echo $field['id'] ?>" value="<?php echo esc_attr($field['dyn_default_value']) ?>" class="dyn_default_value frm_long_input" /></td>
</tr>
<?php
}

if ( $field['type'] == 'data' ) {
?>
<tr><td><label><?php _e( 'Display as', 'formidable' ) ?></label></td>
    <td><select name="field_options[data_type_<?php echo $field['id'] ?>]" class="frm_toggle_mult_sel">
        <?php foreach ( $frm_field_selection['data']['types'] as $type_key => $type_name ) {
            $selected = (isset($field['data_type']) && $field['data_type'] == $type_key) ? ' selected="selected"':''; ?>
        <option value="<?php echo $type_key ?>"<?php echo $selected; ?>><?php echo $type_name ?></option>
        <?php } ?>
        </select>
    </td>
</tr>

<tr><td><?php _e( 'Entries', 'formidable' ) ?></td>
    <td><label for="restrict_<?php echo $field['id'] ?>"><input type="checkbox" name="field_options[restrict_<?php echo $field['id'] ?>]" id="restrict_<?php echo $field['id'] ?>" value="1" <?php echo ($field['restrict'] == 1) ? 'checked="checked"' : ''; ?>/> <?php _e( 'Limit selection choices to those created by the user filling out this form', 'formidable' ) ?></label></td>
</tr>
<?php
        unset($current_field_id);
}

if ( $display['type'] == 'select' || $field['type'] == 'data' ) { ?>
<tr id="frm_multiple_cont_<?php echo $field['id'] ?>" <?php echo ( $field['type'] == 'data' && (! isset($field['data_type']) || $field['data_type'] != 'select' ) ) ? ' class="frm_hidden"' : ''; ?>>
    <td><?php _e( 'Multiple select', 'formidable' ) ?></td>
    <td><label for="multiple_<?php echo $field['id'] ?>"><input type="checkbox" name="field_options[multiple_<?php echo $field['id'] ?>]" id="multiple_<?php echo $field['id'] ?>" value="1" <?php echo ( isset( $field['multiple'] ) && $field['multiple'] ) ? 'checked="checked"' : ''; ?> />
    <?php _e( 'enable multiselect', 'formidable' ) ?></label>
    <div style="padding-top:4px;">
    <label for="autocom_<?php echo $field['id'] ?>"><input type="checkbox" name="field_options[autocom_<?php echo $field['id'] ?>]" id="autocom_<?php echo $field['id'] ?>" value="1" <?php echo ( isset( $field['autocom'] ) && $field['autocom'] ) ? 'checked="checked"' : ''; ?> />
    <?php _e( 'enable autocomplete', 'formidable' ) ?></label>
    </div>
    </td>
</tr>
<?php
} else if ( $field['type'] == 'divider' ) { ?>
<tr class="show_repeat_sec">
    <td><label><?php _e( 'Repeat Layout', 'formidable' ) ?></label></td>
    <td>
    <select name="field_options[format_<?php echo $field['id'] ?>]">
        <option value=""><?php _e( 'Default: No automatic formatting', 'formidable' ) ?></option>
        <option value="inline" <?php selected($field['format'], 'inline') ?>><?php _e( 'Inline: Display each field and label in one row', 'formidable' ) ?></option>
        <option value="grid" <?php selected($field['format'], 'grid') ?>><?php _e( 'Grid: Display labels as headings above rows of fields', 'formidable' ) ?></option>
    </select>
    <input type="hidden" name="field_options[form_select_<?php echo $field['id'] ?>]" value="<?php echo esc_attr($field['form_select']) ?>" />
    </td>
</tr>
<!-- <tr class="show_repeat_sec">
    <td><label><?php _e( 'Repeat Limit', 'formidable' ) ?></label></td>
    <td>
    <input type="text" name="field_options[multiple_<?php echo $field['id'] ?>]" value="<?php echo esc_attr($field['multiple']) ?>" size="3"/> <span class="howto"><?php _e( 'The number of times the end user is allowed to duplicate this section of fields in one entry', 'formidable' ) ?></span>
    </td>
</tr> -->
<?php
} else if ( $field['type'] == 'end_divider' ) { ?>
<tr><td><label><?php _e( 'Repeat Links', 'formidable' ) ?></label></td>
    <td>
    <select class="frm_repeat_format" name="field_options[format_<?php echo $field['id'] ?>]">
        <option value=""><?php _e( 'Icons', 'formidable' ) ?></option>
        <option value="text" <?php selected($field['format'], 'text') ?>><?php _e( 'Text links', 'formidable' ) ?></option>
        <option value="both" <?php selected($field['format'], 'both') ?>><?php _e( 'Text links with icons', 'formidable' ) ?></option>
    </select>
    </td>
</tr>
<tr class="frm_repeat_text <?php echo ( $field['format'] == '' ) ? 'hide-if-js' : ''; ?>">
    <td><label><?php _e( 'Add New Label', 'formidable' ); ?></label></td>
    <td><input type="text" name="field_options[add_label_<?php echo $field['id'] ?>]" value="<?php echo esc_attr($field['add_label']) ?>" />
    </td>
</tr>

<tr class="frm_repeat_text <?php echo ( $field['format'] == '' ) ? 'hide-if-js' : ''; ?>">
    <td><label><?php _e( 'Remove Label', 'formidable' ) ?></label></td>
    <td><input type="text" name="field_options[remove_label_<?php echo $field['id'] ?>]" value="<?php echo esc_attr($field['remove_label']) ?>" />
    </td>
</tr>
<?php
} else if ( $field['type'] == 'date' ) { ?>
    <tr><td><label><?php _e( 'Calendar Localization', 'formidable' ) ?></label></td>
    <td>
    <select name="field_options[locale_<?php echo $field['id'] ?>]">
        <?php foreach ( $locales as $locale_key => $locale ) {
            $selected = (isset($field['locale']) && $field['locale'] == $locale_key)? ' selected="selected"':''; ?>
            <option value="<?php echo $locale_key ?>"<?php echo $selected; ?>><?php echo $locale ?></option>
        <?php } ?>
    </select>
    </td>
    </tr>
<tr>
	<td>
		<label><?php _e( 'Year Range', 'formidable' ) ?></label>
		<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Use four digit years or +/- years to make it dynamic. For example, use -5 for the start year and +5 for the end year.', 'formidable' ) ?>" ></span>
    </td>
    <td>
    <span><?php _e( 'Start Year', 'formidable' ) ?></span>
    <input type="text" name="field_options[start_year_<?php echo $field['id'] ?>]" value="<?php echo isset($field['start_year']) ? $field['start_year'] : ''; ?>" size="4"/>

    <span><?php _e( 'End Year', 'formidable' ) ?></span>
    <input type="text" name="field_options[end_year_<?php echo $field['id'] ?>]" value="<?php echo isset($field['end_year']) ? $field['end_year'] : ''; ?>" size="4"/>
    </td>
</tr>
<?php } else if ( $field['type'] == 'time' ) { ?>
<tr><td><label><?php _e( 'Clock Settings', 'formidable' ) ?></label></td>
    <td>
        <select name="field_options[clock_<?php echo $field['id'] ?>]">
            <option value="12" <?php selected($field['clock'], 12) ?>>12</option>
            <option value="24" <?php selected($field['clock'], 24) ?>>24</option>
        </select> <span class="howto" style="padding-right:10px;"><?php _e( 'hour clock', 'formidable' ) ?></span>

        <input type="text" name="field_options[step_<?php echo $field['id'] ?>]" value="<?php echo esc_attr($field['step']); ?>" size="3" />
        <span class="howto" style="padding-right:10px;"><?php _e( 'minute step', 'formidable' ) ?></span>

        <input type="text" name="field_options[start_time_<?php echo $field['id'] ?>]" id="start_time_<?php echo $field['id'] ?>" value="<?php echo esc_attr($field['start_time']) ?>" size="5"/>
        <span class="howto" style="padding-right:10px;"><?php _e( 'start time', 'formidable' ) ?></span>

        <input type="text" name="field_options[end_time_<?php echo $field['id'] ?>]" id="end_time_<?php echo $field['id'] ?>" value="<?php echo esc_attr($field['end_time']) ?>" size="5"/>
        <span class="howto"><?php _e( 'end time', 'formidable' ) ?></span>
    </td>
</tr>
<?php } else if ( $field['type'] == 'file' ) { ?>
    <tr><td><label><?php _e( 'Multiple files', 'formidable' ) ?></label></td>
        <td><label for="multiple_<?php echo esc_attr( $field['id'] ) ?>"><input type="checkbox" name="field_options[multiple_<?php echo esc_attr( $field['id'] ) ?>]" id="multiple_<?php echo esc_attr( $field['id'] ) ?>" value="1" <?php echo ( isset( $field['multiple'] ) && $field['multiple'] ) ? 'checked="checked"' : ''; ?> />
        <?php _e( 'allow multiple files to be uploaded to this field', 'formidable' ) ?></label></td>
    </tr>
    <tr><td><label><?php _e( 'Delete files', 'formidable' ) ?></label></td>
        <td><label for="delete_<?php echo esc_attr( $field['id'] ) ?>"><input type="checkbox" name="field_options[delete_<?php echo esc_attr( $field['id'] ) ?>]" id="delete_<?php echo esc_attr( $field['id'] ) ?>" value="1" <?php echo ( isset( $field['delete'] ) && $field['delete'] ) ? 'checked="checked"' : ''; ?> />
        <?php _e( 'permanently delete old files when replaced or when the entry is deleted', 'formidable' ) ?></label></td>
    </tr>
    <tr><td><label><?php _e( 'Email Attachment', 'formidable' ) ?></label></td>
        <td><label for="attach_<?php echo esc_attr( $field['id'] ) ?>"><input type="checkbox" id="attach_<?php echo esc_attr( $field['id'] ) ?>" name="field_options[attach_<?php echo esc_attr( $field['id'] ) ?>]" value="1" <?php echo ( isset( $field['attach'] ) && $field['attach'] ) ? 'checked="checked"' : ''; ?> /> <?php _e( 'attach this file to the email notification', 'formidable' ) ?></label></td>
    </tr>
    <?php if ( $mimes ) { ?>
    <tr><td><label><?php _e( 'Allowed file types', 'formidable' ) ?></label></td>
        <td>
            <label for="restrict_<?php echo $field['id'] ?>_0"><input type="radio" name="field_options[restrict_<?php echo $field['id'] ?>]" id="restrict_<?php echo $field['id'] ?>_0" value="0" <?php FrmAppHelper::checked($field['restrict'], 0); ?> onclick="frm_show_div('restrict_box_<?php echo $field['id'] ?>',this.value,1,'.')" /> <?php _e( 'All types', 'formidable' ) ?></label>
            <label for="restrict_<?php echo $field['id'] ?>_1"><input type="radio" name="field_options[restrict_<?php echo $field['id'] ?>]" id="restrict_<?php echo $field['id'] ?>_1" value="1" <?php FrmAppHelper::checked($field['restrict'], 1); ?> onclick="frm_show_div('restrict_box_<?php echo $field['id'] ?>',this.value,1,'.')" /> <?php _e( 'Specify allowed types', 'formidable' ) ?></label>
            <label for="check_all_ftypes_<?php echo $field['id'] ?>" class="restrict_box_<?php echo $field['id'] ?> <?php echo ($field['restrict'] == 1) ? '' : 'frm_hidden'; ?>"><input type="checkbox" id="check_all_ftypes_<?php echo $field['id'] ?>" onclick="frmCheckAll(this.checked,'field_options[ftypes_<?php echo $field['id'] ?>]')" /> <span class="howto"><?php _e( 'Check All', 'formidable' ) ?></span></label>

            <div class="restrict_box_<?php echo $field['id'] . ($field['restrict'] == 1 ? '' : ' frm_hidden'); ?>">
            <div class="frm_field_opts_list" style="width:100%;">
                <div class="alignleft" style="width:33% !important">
                    <?php
                    $mcount = count($mimes);
                    $third = ceil($mcount/3);
                    $c = 0;
                    if ( ! isset($field['ftypes']) ) {
                        $field['ftypes'] = array();
                    }

                    foreach ( $mimes as $ext_preg => $mime ) {
                        if ( $c == $third || ( ( $c/2 ) == $third ) ) { ?>
                    </div>
                    <div class="alignleft" style="width:33% !important">
                    <?php } ?>
                    <label for="ftypes_<?php echo $field['id'] ?>_<?php echo sanitize_key($ext_preg) ?>"><input type="checkbox" id="ftypes_<?php echo $field['id'] ?>_<?php echo sanitize_key($ext_preg) ?>" name="field_options[ftypes_<?php echo $field['id'] ?>][<?php echo $ext_preg ?>]" value="<?php echo $mime ?>" <?php FrmAppHelper::checked($field['ftypes'], $mime); ?> /> <span class="howto"><?php echo str_replace('|', ', ', $ext_preg); ?></span></label><br/>
                    <?php
                        $c++;
                        unset($ext_preg, $mime);
                    }
					unset( $c, $mcount, $third );
                    ?>
                </div>
            </div>
            </div>
        </td>
    </tr>
    <?php } ?>
<?php } else if ( $field['type'] == 'number' && $frm_settings->use_html ) { ?>
    <tr>
		<td style="width:150px">
			<label><?php _e( 'Number Range', 'formidable' ) ?>
				<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Browsers that support the HTML5 number field require a number range to determine the numbers seen when clicking the arrows next to the field.', 'formidable' ) ?>" ></span>
			</label>
		</td>
        <td><input type="text" name="field_options[minnum_<?php echo $field['id'] ?>]" value="<?php echo esc_attr($field['minnum']); ?>" size="5" /> <span class="howto"><?php echo _e( 'minimum', 'formidable' ) ?></span>
        <input type="text" name="field_options[maxnum_<?php echo $field['id'] ?>]" value="<?php echo esc_attr($field['maxnum']); ?>" size="5" /> <span class="howto"><?php _e( 'maximum', 'formidable' ) ?></span>
        <input type="text" name="field_options[step_<?php echo $field['id'] ?>]" value="<?php echo esc_attr($field['step']); ?>" size="5" /> <span class="howto"><?php _e( 'step', 'formidable' ) ?></span></td>
    </tr>
<?php } else if ( $field['type'] == 'scale' ) { ?>
    <tr><td><label><?php _e( 'Range', 'formidable' ) ?></label></td>
        <td>
            <select name="field_options[minnum_<?php echo $field['id'] ?>]">
				<?php for ( $i = 0; $i < 10; $i++ ) {
                    $selected = (isset($field['minnum']) && $field['minnum'] == $i)? ' selected="selected"':''; ?>
                <option value="<?php echo $i ?>"<?php echo $selected; ?>><?php echo $i ?></option>
                <?php } ?>
            </select> <?php _e( 'to', 'formidable' ) ?>
            <select name="field_options[maxnum_<?php echo $field['id'] ?>]">
				<?php for( $i = 1; $i <= 20; $i++ ) {
                    $selected = (isset($field['maxnum']) && $field['maxnum'] == $i)? ' selected="selected"':''; ?>
                <option value="<?php echo $i ?>"<?php echo $selected; ?>><?php echo $i ?></option>
                <?php } ?>
            </select>
        </td>
    </tr>
    <tr><td><label><?php _e( 'Stars', 'formidable' ) ?></label></td>
        <td><label for="star_<?php echo $field['id'] ?>"><input type="checkbox" value="1" name="field_options[star_<?php echo $field['id'] ?>]" id="star_<?php echo $field['id'] ?>" <?php checked((isset($field['star']) ? $field['star'] : 0), 1) ?> />
            <?php _e( 'Show options as stars', 'formidable' ) ?>
			</label>
        </td>
    </tr>
<?php } else if ( $field['type'] == 'html' ) { ?>
<tr><td colspan="2"><?php _e( 'Content', 'formidable' ) ?><br/>
<textarea name="field_options[description_<?php echo $field['id'] ?>]" style="width:98%;" rows="8"><?php
if ( FrmField::is_option_true( $field, 'stop_filter' ) ) {
	echo $field['description'];
} else{
	echo FrmAppHelper::esc_textarea( $field['description'] );
}
?></textarea>
</td>
</tr>
<?php } else if ( $field['type'] == 'form' ) { ?>
<tr><td><?php _e( 'Insert Form', 'formidable' ) ?></td>
<td><?php FrmFormsHelper::forms_dropdown('field_options[form_select_'. $field['id'] .']', $field['form_select'], array(
        'exclude' => $field['form_id'],
    )); ?>
</td>
</tr>

<?php } else if ( $field['type'] == 'phone' ) { ?>
<tr>
<td><label><?php _e( 'Format', 'formidable' ) ?></label>
<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Insert the format you would like to accept. Use a regular expression starting with ^ or an exact format like (999)999-9999.', 'formidable' ) ?>" ></span>
</td>
<td><input type="text" class="frm_long_input" value="<?php echo esc_attr($field['format']) ?>" name="field_options[format_<?php echo $field['id'] ?>]" />
</td>
</tr>
<?php }

if ( $display['visibility'] ) { ?>
<tr>
<td><label><?php _e( 'Visibility', 'formidable' ) ?></label>
<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Determines who can see this field. The selected user role and higher user roles will be able to see this field. The only exception is logged-out users. Only logged-out users will be able to see the field if that option is selected.', 'formidable' ) ?>" ></span>
</td>
<td>
<?php
    if ( $field['admin_only'] == 1 ) {
        $field['admin_only'] = 'administrator';
    } else if ( empty($field['admin_only']) ) {
        $field['admin_only'] = '';
    }
?>

<select name="field_options[admin_only_<?php echo $field['id'] ?>]">
    <option value=""><?php _e( 'Everyone', 'formidable' ) ?></option>
    <?php FrmAppHelper::roles_options($field['admin_only']); ?>
    <option value="loggedin" <?php selected($field['admin_only'], 'loggedin') ?>><?php _e( 'Logged-in Users', 'formidable' ) ?></option>
    <option value="loggedout" <?php selected($field['admin_only'], 'loggedout') ?>><?php _e( 'Logged-out Users', 'formidable' ); ?></option>
</select>
</td>
</tr>
<?php
}

if ( $display['conf_field'] ) { ?>
<tr><td><?php _e( 'Confirmation Field', 'formidable' ) ?></td>
    <td><select name="field_options[conf_field_<?php echo $field['id'] ?>]" class="conf_field" id="frm_conf_field_<?php echo $field['id'] ?>">
			<option value=""<?php selected($field['conf_field'], ''); ?>><?php _e( 'None', 'formidable' ) ?></option>
			<option value="inline"<?php selected($field['conf_field'], 'inline'); ?>><?php _e( 'Inline', 'formidable' ) ?></option>
			<option value="below"<?php selected($field['conf_field'], 'below'); ?>><?php _e( 'Below Field', 'formidable' ) ?></option>
		</select>
    </td>
</tr>

<?php }

if ( $display['calc'] ) { ?>
<tr><td><?php _e( 'Calculations', 'formidable' ) ?></td>
    <td><label for="use_calc_<?php echo $field['id'] ?>"><input type="checkbox" value="1" name="field_options[use_calc_<?php echo $field['id'] ?>]" <?php checked($field['use_calc'], 1) ?> class="use_calc" id="use_calc_<?php echo $field['id'] ?>" onchange="frm_show_div('frm_calc_opts<?php echo $field['id'] ?>',this.checked,true,'#')" />
        <?php _e( 'Calculate the default value for this field', 'formidable' ) ?></label>
        <div id="frm_calc_opts<?php echo $field['id'] ?>" <?php
            if ( ! $field['use_calc'] ) {
                echo 'class="frm_hidden"';
            } ?>>
            <select class="frm_shortcode_select frm_insert_val" data-target="frm_calc_<?php echo $field['id'] ?>">
                <option value="">&mdash; <?php _e( 'Select a value to insert into the box below', 'formidable' ) ?> &mdash;</option>
            </select><br/>
            <input type="text" value="<?php echo esc_attr($field['calc']) ?>" id="frm_calc_<?php echo $field['id'] ?>" name="field_options[calc_<?php echo $field['id'] ?>]" class="frm_long_input"/>
			<div class="frm_small_top_margin">
				<input type="text" id="frm_calc_dec_<?php echo esc_attr( $field['id'] ) ?>" class="frm_calc_dec" name="field_options[calc_dec_<?php echo esc_attr( $field['id'] ) ?>]" value="<?php echo esc_attr( $field['calc_dec'] ) ?>"/>
				<span class="howto"> <?php _e( 'decimal places', 'formidable' ); ?></span>
			</div>
        </div>
    </td>
</tr>

<?php }

if ( $display['logic'] ) { ?>
<tr><td><?php _e( 'Conditional Logic', 'formidable' ); ?></td>
    <td>
    <a href="javascript:void(0)" id="logic_<?php echo $field['id'] ?>" class="frm_add_logic_row frm_add_logic_link <?php echo
    ( ! empty($field['hide_field']) && (count($field['hide_field']) > 1 || reset($field['hide_field']) != '') ) ? ' frm_hidden' : '';
    ?>"><?php _e( 'Use Conditional Logic', 'formidable' ) ?></a>
    <div class="frm_logic_rows<?php echo ( ! empty( $field['hide_field'] ) && ( count($field['hide_field']) > 1 || reset( $field['hide_field'] ) != '' ) ) ? '' : ' frm_hidden'; ?>">
        <div id="frm_logic_row_<?php echo $field['id'] ?>">
        <select name="field_options[show_hide_<?php echo $field['id'] ?>]">
            <option value="show" <?php selected($field['show_hide'], 'show') ?>><?php echo ($field['type'] == 'break') ? __( 'Do not skip', 'formidable' ) : __( 'Show', 'formidable' ); ?></option>
            <option value="hide" <?php selected($field['show_hide'], 'hide') ?>><?php echo ($field['type'] == 'break') ? __( 'Skip', 'formidable' ) : __( 'Hide', 'formidable' ); ?></option>
        </select>

<?php $all_select =
'<select name="field_options[any_all_'. $field['id'] .']">'.
    '<option value="any" '. selected($field['any_all'], 'any', false) .'>'. __( 'any', 'formidable' ) .'</option>'.
    '<option value="all" '. selected($field['any_all'], 'all', false) .'>'. __( 'all', 'formidable' ) .'</option>'.
'</select>';

    echo ($field['type'] == 'break') ?  sprintf(__( 'next page if %s of the following match:', 'formidable' ), $all_select) : sprintf(__( 'this field if %s of the following match:', 'formidable' ), $all_select);
    unset($all_select);

            if ( ! empty( $field['hide_field'] ) ) {
                foreach ( (array) $field['hide_field'] as $meta_name => $hide_field ) {
                    include(FrmAppHelper::plugin_path() .'/pro/classes/views/frmpro-fields/_logic_row.php');
                }
            }
        ?>
        </div>
    </div>


    </td>
</tr>
<?php }