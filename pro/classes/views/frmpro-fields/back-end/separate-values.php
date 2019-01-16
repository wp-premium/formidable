<tr>
	<td>
		<label><?php esc_html_e( 'Separate values', 'formidable-pro' ); ?></label>
		<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php echo esc_attr( sprintf( __( 'Add a separate value to use for calculations, email routing, saving to the database, and many other uses. The option values are saved while the option labels are shown in the form. Use [%s] to show the saved value in emails or views.', 'formidable-pro' ), $field['id'] . ' show=value' ) ); ?>"></span>
	</td>
	<td>
		<label for="separate_value_<?php echo absint( $field['id'] ) ?>">
			<input type="checkbox" name="field_options[separate_value_<?php echo absint( $field['id'] ) ?>]" id="separate_value_<?php echo absint( $field['id'] ) ?>" value="1" <?php checked( $field['separate_value'], 1 ) ?> class="frm_toggle_sep_values" />
			<?php esc_html_e( 'Use separate values', 'formidable-pro' ); ?>
		</label>
	</td>
</tr>
