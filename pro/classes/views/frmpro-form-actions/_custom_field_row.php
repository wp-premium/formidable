<?php $sanitized_name = sanitize_title_with_dashes($custom_data['meta_name']); ?>

<tr id="frm_postmeta_<?php echo esc_attr( $sanitized_name ) ?>" class="frm_postmeta_row alternate">
    <td class="left"><label class="screen-reader-text" for='meta-256-key'><?php _e( 'Name') ?></label>
    <?php
    if ( isset($cf_keys) && $echo && $custom_data['meta_name'] != '' && ! in_array($custom_data['meta_name'], (array) $cf_keys) ) {
        $cf_keys[] = $custom_data['meta_name'];
    }

	if ( ! isset( $cf_keys ) || empty( $cf_keys ) ) {
	?>
	<input type="text" value="<?php echo esc_attr( $echo ? $custom_data['meta_name'] : '' ) ?>" name="<?php echo esc_attr( $action_control->get_field_name( 'post_custom_fields' ) ) ?>[<?php echo esc_attr( $sanitized_name ) ?>][meta_name]" class="frm_enternew" />
    <?php } else { ?>
    <select name="<?php echo esc_attr( $action_control->get_field_name( 'post_custom_fields' ) ) ?>[<?php echo esc_attr( $sanitized_name ) ?>][meta_name]" class="frm_cancelnew">
        <option value=""><?php _e( '&mdash; Select &mdash;' ); ?></option>
		<?php foreach ( $cf_keys as $cf_key ) { ?>
    	<option value="<?php echo esc_attr($cf_key) ?>"><?php echo esc_html($cf_key) ?></option>
    	<?php
    		unset($cf_key);
    	}
        ?>
    </select>
	<input type="text" class="hide-if-js frm_enternew" name="<?php echo esc_attr( $action_control->get_field_name( 'post_custom_fields' ) ) ?>[<?php echo esc_attr( $sanitized_name ) ?>][custom_meta_name]" value="" />
    <?php } ?>

    <?php if ( isset( $cf_keys ) && ! empty( $cf_keys ) ) { ?>
    <div class="clear"></div>
    <div style="margin-left:8px;">
        <a href="javascript:void(0)" class="hide-if-no-js frm_toggle_cf_opts">
            <span class="frm_cancelnew"><?php _e( 'Enter new'); ?></span>
            <span class="frm_enternew frm_hidden"><?php _e( 'Cancel', 'formidable-pro' ); ?></span>
        </a>
    </div>
    <?php } ?>
    </td>
    <td><label class="screen-reader-text" for='meta-256-value'><?php _e( 'Value', 'formidable-pro' ) ?></label>
    <select name="<?php echo esc_attr( $action_control->get_field_name( 'post_custom_fields' ) ) ?>[<?php echo esc_attr( $sanitized_name ) ?>][field_id]" class="frm_single_post_field">
        <option value=""><?php _e( '&mdash; Select Field &mdash;', 'formidable-pro' ); ?></option>
        <?php
        if ( ! empty($values['fields']) ) {
            if ( ! isset($custom_data['field_id']) ) {
                $custom_data['field_id'] = '';
            }

		foreach ( $values['fields'] as $fo ) {
            $fo = (array) $fo;
			if ( ! FrmField::is_no_save_field( $fo['type'] ) ) {
			?>
		<option value="<?php echo esc_attr( $fo['id'] ) ?>" <?php selected( $custom_data['field_id'], $fo['id'] ) ?>><?php echo FrmAppHelper::truncate( $fo['name'], 50 ) ?></option>
        <?php
            }
            unset($fo);
        }
		}
		?>
    </select>
    </td>
    <td>
        <div style="padding-top:12px">
			<a href="javascript:void(0)" class="frm_remove_tag frm_icon_font" data-removeid="frm_postmeta_<?php echo esc_attr( $sanitized_name ) ?>" data-hidelast="#frm_form_action_<?php echo esc_attr( $action_control->number ) ?> .frm_name_value" data-showlast="#frm_form_action_<?php echo esc_attr( $action_control->number ) ?> .frm_add_postmeta_row"></a>
            <a href="javascript:void(0)" class="frm_add_tag frm_icon_font frm_add_postmeta_row"></a>
        </div>
    </td>
</tr>
<?php
unset( $sanitized_name );
