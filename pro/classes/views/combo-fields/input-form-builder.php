<div class="frm_multi_fields_container">
	<?php foreach ( $sub_fields as $key => $sub_field ) { ?>
		<div id="frm_field_<?php echo esc_attr( $field['id'] .'-'. $key ) ?>_container" class="frm_form_field form-field <?php
		echo esc_attr( $sub_field['classes'] ); ?>">
			<?php if ( $sub_field['type'] == 'select' ) { ?>
				<select name="<?php echo esc_attr( $field_name ) ?>[<?php echo esc_attr( $key ) ?>]" id="<?php echo esc_attr( $html_id .'_'. $key ) ?>" >
					<option value="">
						<?php echo esc_html( FrmProComboFieldsController::get_dropdown_label( compact( 'field', 'key', 'sub_field' ) ) ); ?>
					</option>
					<?php foreach ( $sub_field['options'] as $option ) { ?>
						<option value="<?php echo esc_attr( $option ) ?>" <?php selected( $field['value'][ $key ], $option ) ?>>
							<?php echo esc_html( $option ) ?>
						</option>
					<?php } ?>
				</select>
			<?php } else { ?>
				<input type="<?php echo esc_attr( $sub_field['type'] ) ?>" id="<?php echo esc_attr( $html_id . '_' . $key ) ?>" value="<?php echo esc_attr( $field['value'][ $key ] ) ?>" <?php
				if ( ! isset( $remove_names ) || ! $remove_names ) {
					echo ' name="' . esc_attr( $field_name ) . '[' . esc_attr( $key ) . ']" ';
				}
				?> class="dyn_default_value" />
			<?php }

			if ( $sub_field['label'] ) {
				FrmProAddressesController::include_sub_label( array(
					'field' => $field, 'option_name' => $key . '_desc'
				) );
			} ?>
		</div>
	<?php } ?>
</div>