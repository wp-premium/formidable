<select name="<?php echo esc_attr( $values['field_name'] ) ?>" id="<?php echo esc_attr( $values['html_id'] ) ?>" <?php do_action( 'frm_field_input_html', $field ) ?>>
    <?php foreach ( (array) $field['options'] as $t ) { ?>
        <option value="<?php echo esc_attr( $t ) ?>" <?php selected( $field['value'], $t ) ?>>
			<?php echo esc_html( $t ) ?>
		</option>
    <?php } ?>
</select>
