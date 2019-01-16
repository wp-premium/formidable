<tr><td><?php esc_html_e( 'Insert Form', 'formidable-pro' ) ?></td>
	<td><?php
		FrmFormsHelper::forms_dropdown( 'field_options[form_select_' . $field['id'] . ']', $field['form_select'], array(
			'exclude' => $field['form_id'],
		) );
		?>
	</td>
</tr>
