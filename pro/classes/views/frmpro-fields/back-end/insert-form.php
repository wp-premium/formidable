<tr><td><?php _e( 'Insert Form', 'formidable' ) ?></td>
	<td><?php FrmFormsHelper::forms_dropdown('field_options[form_select_'. $field['id'] .']', $field['form_select'], array(
			'exclude' => $field['form_id'],
		)); ?>
	</td>
</tr>