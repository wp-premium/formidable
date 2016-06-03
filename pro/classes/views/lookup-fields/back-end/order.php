<tr>
	<td><label><?php _e( 'Option order', 'formidable' ) ?></label>
	<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Set the order for the values in your Lookup Field.', 'formidable' ) ?>"></span>
	</td>
    <td>
		<select name="field_options[lookup_option_order_<?php echo esc_attr( $field['id'] ) ?>]">
		    <option value="ascending" <?php selected( $field['lookup_option_order'], 'ascending' ) ?>><?php _e( 'Ascending (A-Z)', 'formidable' ) ?></option>
			<option value="descending" <?php selected( $field['lookup_option_order'], 'descending' ) ?>><?php _e( 'Descending (Z-A)', 'formidable' ) ?></option>
			<option value="no_order" <?php selected( $field['lookup_option_order'], 'no_order' ) ?>><?php _e( 'No order set', 'formidable' ) ?></option>
		</select>
    </td>
</tr>