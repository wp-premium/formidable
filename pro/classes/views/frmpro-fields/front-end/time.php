<select name="<?php echo esc_attr( $values['field_name'] ) ?>[H]" id="<?php echo esc_attr( $values['html_id'] ) ?>_H" <?php do_action( 'frm_field_input_html', $field ) ?>>
    <?php foreach ( $field['options']['H'] as $hour ) { ?>
        <option value="<?php echo esc_attr( $hour ) ?>" <?php selected( $h, $hour ) ?>><?php echo esc_html( $hour ) ?></option>
    <?php } ?>
</select>
<span class="frm_time_sep">:</span>
<select name="<?php echo esc_attr( $values['field_name'] ) ?>[m]" id="<?php echo esc_attr( $values['html_id'] ) ?>_m" <?php do_action( 'frm_field_input_html', $field ) ?>>
    <?php foreach ( $field['options']['m'] as $min ) { ?>
        <option value="<?php echo esc_attr( $min ) ?>" <?php selected( $m, $min ) ?>><?php echo esc_html( $min ) ?></option>
    <?php } ?>
</select>
<?php if ( isset( $field['options']['A'] ) ) { ?>
<select name="<?php echo esc_attr( $values['field_name'] ) ?>[A]" id="<?php echo esc_attr( $values['html_id'] ) ?>_A" <?php do_action( 'frm_field_input_html', $field ) ?>>
    <?php foreach ( $field['options']['A'] as $am ) { ?>
        <option value="<?php echo esc_attr( $am ) ?>" <?php selected( $a, $am ) ?>><?php echo esc_html( $am ) ?></option>
    <?php } ?>
</select>
<?php } ?>
