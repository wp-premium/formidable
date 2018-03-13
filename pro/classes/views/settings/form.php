<p>
	<label class="frm_left_label"><?php _e( 'Edit Message', 'formidable-pro' ); ?>
		<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'The default message seen when after an entry is updated.', 'formidable-pro' ) ?>"></span>
	</label>
    <input type="text" id="frm_edit_msg" name="frm_edit_msg" class="frm_with_left_label" value="<?php echo esc_attr( $frmpro_settings->edit_msg ) ?>" />
</p>

<p>
	<label class="frm_left_label"><?php _e( 'Update Button', 'formidable-pro' ); ?>
		<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'The label on the submit button when editing and entry.', 'formidable-pro' ) ?>" ></span>
	</label>
    <input type="text" id="frm_update_value" name="frm_update_value" class="frm_with_left_label" value="<?php echo esc_attr($frmpro_settings->update_value) ?>" />
</p>


<p>
	<label class="frm_left_label"><?php _e( 'Login Message', 'formidable-pro' ); ?>
		<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'The message seen when a user who is not logged-in views a form only logged-in users can submit.', 'formidable-pro' ) ?>"></span>
	</label>
    <input type="text" id="frm_login_msg" name="frm_login_msg" class="frm_with_left_label" value="<?php echo esc_attr($frm_settings->login_msg) ?>" />
</p>

<p>
	<label class="frm_left_label"><?php _e( 'Previously Submitted Message', 'formidable-pro' ); ?>
		<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'The message seen when a user attempts to submit a form for a second time if submissions are limited.', 'formidable-pro' ) ?>"></span>
	</label>
	<input type="text" id="frm_already_submitted" name="frm_already_submitted" class="frm_with_left_label" value="<?php echo esc_attr($frmpro_settings->already_submitted) ?>" />
</p>
<div class="clear"></div>


<h3><?php _e( 'Miscellaneous', 'formidable-pro' ) ?></h3>

<p>
	<label class="frm_left_label"><?php _e( 'Date Format', 'formidable-pro' ); ?>
		<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Change the format of the date used in the date field.', 'formidable-pro' ) ?>"></span>
	</label>
		<?php $formats = array_keys( FrmProAppHelper::display_to_datepicker_format() ); ?>
        <select name="frm_date_format">
            <?php foreach ( $formats as $f ) { ?>
            <option value="<?php echo esc_attr($f) ?>" <?php selected($frmpro_settings->date_format, $f); ?>>
                <?php echo esc_html( $f . ' &nbsp; &nbsp; ' . date( $f ) ); ?>
            </option>
            <?php } ?>
        </select>
</p>

<p>
	<label class="frm_left_label"><?php _e( 'Admin menu label', 'formidable-pro' ); ?></label>
	<input type="text" name="frm_menu" id="frm_menu" value="<?php echo esc_attr( $frm_settings->menu ) ?>" />
	<?php if ( is_multisite() && current_user_can( 'setup_network' ) ) { ?>
		<label for="frm_mu_menu">
			<input type="checkbox" name="frm_mu_menu" id="frm_mu_menu" value="1" <?php checked( $frm_settings->mu_menu, 1 ) ?> />
			<?php _e( 'Use this menu name site-wide', 'formidable-pro' ); ?>
		</label>
	<?php } ?>
</p>
