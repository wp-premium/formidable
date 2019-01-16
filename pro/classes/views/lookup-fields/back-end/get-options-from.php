<select class="frm_get_values_form" id="get_values_form_<?php echo absint( $field['id'] ); ?>" name="field_options[get_values_form_<?php echo esc_attr( $field['id'] ); ?>]" data-fieldtype="<?php echo esc_attr( $field['type'] ); ?>">
	<option value="">&mdash; <?php esc_html_e( 'Select Form', 'formidable-pro' ); ?> &mdash;</option>
	<?php foreach ( $lookup_args['form_list'] as $form_opts ) { ?>
	<option value="<?php echo absint( $form_opts->id ) ?>"<?php selected( $form_opts->id, $field['get_values_form'] ) ?>><?php echo FrmAppHelper::truncate( $form_opts->name, 30 ) ?></option>
	<?php } ?>
</select>
<select id="get_values_field_<?php echo absint( $field['id'] ) ?>" name="field_options[get_values_field_<?php echo esc_attr( $field['id'] ) ?>]">
	<?php
	self::show_options_for_get_values_field( $lookup_args['form_fields'], $field );
	?>
</select>
