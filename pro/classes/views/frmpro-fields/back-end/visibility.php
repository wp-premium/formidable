<tr>
	<td>
		<label><?php esc_html_e( 'Visibility', 'formidable-pro' ); ?></label>
		<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Determines who can see this field. The selected user role and higher user roles will be able to see this field. The only exception is logged-out users. Only logged-out users will be able to see the field if that option is selected.', 'formidable-pro' ) ?>" ></span>
	</td>
	<td>
		<?php
		if ( $field['admin_only'] == 1 ) {
			$field['admin_only'] = 'administrator';
		} else if ( empty($field['admin_only']) ) {
			$field['admin_only'] = '';
		}
		?>

		<select name="field_options[admin_only_<?php echo absint( $field['id'] ) ?>]">
			<option value=""><?php esc_html_e( 'Everyone', 'formidable-pro' ); ?></option>
			<?php FrmAppHelper::roles_options($field['admin_only']); ?>
			<option value="loggedin" <?php selected( $field['admin_only'], 'loggedin' ); ?>>
				<?php esc_html_e( 'Logged-in Users', 'formidable-pro' ); ?>
			</option>
			<option value="loggedout" <?php selected( $field['admin_only'], 'loggedout' ); ?>>
				<?php esc_html_e( 'Logged-out Users', 'formidable-pro' ); ?>
			</option>
		</select>
	</td>
</tr>
