<tr>
	<td><?php _e( 'Calculations', 'formidable' ) ?></td>
	<td>
		<label for="use_calc_<?php echo esc_attr( $field['id'] ) ?>">
			<input type="checkbox" value="1" name="field_options[use_calc_<?php echo esc_attr( $field['id'] ) ?>]" <?php checked( $field['use_calc'], 1 ) ?> class="use_calc" id="use_calc_<?php echo esc_attr( $field['id'] ) ?>" onchange="frm_show_div('frm_calc_opts<?php echo absint( $field['id'] ) ?>',this.checked,true,'#')" />
			<?php _e( 'Calculate the default value for this field', 'formidable' ) ?>
		</label>
		<div id="frm_calc_opts<?php echo esc_attr( $field['id'] ) ?>" <?php
			if ( ! $field['use_calc'] ) {
				echo 'class="frm_hidden"';
			} ?>>
			<label for="calc_type_<?php echo esc_attr( $field['id'] ) ?>">
				<input type="checkbox" value="text" name="field_options[calc_type_<?php echo esc_attr( $field['id'] ) ?>]" id="calc_type_<?php echo esc_attr( $field['id'] ) ?>" <?php checked( $field['calc_type'], 'text' ) ?> onchange="frm_show_div('frm_num_calc_<?php echo absint( $field['id'] ) ?>',this.checked,false,'#')" />
				<?php esc_html_e( 'This calculation is copying text', 'formidable' ) ?>
				<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'When this box is checked, the values from fields in your calculation will be treated as text and combined literally. When this box is unchecked, only numbers will be used in the calculation, and any included math operations will be applied.', 'formidable' ) ?>" ></span>
			</label>
			<select class="frm_shortcode_select frm_insert_val" data-target="frm_calc_<?php echo esc_attr( $field['id'] ) ?>">
				<option value="">&mdash; <?php _e( 'Select a value to insert into the box below', 'formidable' ) ?> &mdash;</option>
			</select><br/>
			<input type="text" value="<?php echo esc_attr($field['calc']) ?>" id="frm_calc_<?php echo esc_attr( $field['id'] ) ?>" name="field_options[calc_<?php echo absint( $field['id'] ) ?>]" class="frm_long_input"/>

			<div class="frm_small_top_margin <?php echo esc_attr( $field['calc_type'] == 'text' ? 'frm_hidden' : '' ); ?>" id="frm_num_calc_<?php echo esc_attr( $field['id'] ) ?>">
				<input type="text" id="frm_calc_dec_<?php echo esc_attr( $field['id'] ) ?>" class="frm_calc_dec" name="field_options[calc_dec_<?php echo esc_attr( $field['id'] ) ?>]" value="<?php echo esc_attr( $field['calc_dec'] ) ?>"/>
				<span class="howto"> <?php _e( 'decimal places', 'formidable' ); ?></span>
			</div>
		</div>
	</td>
</tr>