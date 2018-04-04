<div class="frm_ipe_field_conf_desc description <?php echo ( $field[ $option_name ] == '' ) ? 'frm-show-click' : '' ?>"><?php
	echo ( $field[ $option_name ] == '' ) ? __( '(Click to add description)', 'formidable-pro' ) : force_balance_tags( $field[ $option_name ] );
?></div>
<input type="hidden" name="field_options[<?php echo esc_attr( $option_name . '_' . $field['id'] ) ?>]" value="<?php echo esc_attr( $field[ $option_name ] ); ?>" />
