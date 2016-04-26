<tr><td colspan="2"><?php _e( 'Content', 'formidable' ) ?><br/>
<textarea name="field_options[description_<?php echo $field['id'] ?>]" style="width:98%;" rows="8"><?php
	if ( FrmField::is_option_true( $field, 'stop_filter' ) ) {
		echo $field['description'];
	} else{
		echo FrmAppHelper::esc_textarea( $field['description'] );
	}
	?></textarea>
	</td>
</tr>