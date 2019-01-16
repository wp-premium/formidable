<span class="show_repeat_sec repeat_icon_links repeat_format<?php echo esc_attr( $field['format'] ); ?>">
	<a href="javascript:void(0)" class="frm_remove_form_row <?php echo ( $field['format'] == '' ) ? '' : 'frm_button'; ?>">
		<i class="frm_icon_font frm_minus1_icon"> </i>
		<span class="frm_repeat_label"><?php echo esc_html( $field['remove_label'] ) ?></span>
	</a> &nbsp;
	<a href="javascript:void(0)" class="frm_add_form_row <?php echo ( $field['format'] == '' ) ? '' : 'frm_button'; ?>">
		<i class="frm_icon_font frm_plus1_icon"> </i>
		<span class="frm_repeat_label"><?php echo esc_html( $field['add_label'] ) ?></span>
	</a>
</span>
