<div id="frm_watch_lookup_<?php echo esc_attr( $field_id . '_' . $row_key ); ?>">
	<select name="field_options[watch_lookup_<?php echo esc_attr( $field_id ) ?>][]">
		<option value=""><?php esc_html_e( '&mdash; Select Field &mdash;', 'formidable-pro' ); ?></option>
		<?php
		foreach ( $lookup_fields as $field_option ) {
			if ( $field_option->id == $field_id ) {
	            continue;
	        }
			$selected = ( $field_option->id == $selected_field ) ? ' selected="selected"' : '';
	    ?>
	    <option value="<?php echo esc_attr( $field_option->id ); ?>"<?php
			echo esc_attr( $selected );
			?>><?php
			echo ( '' == $field_option->name ) ? $field_option->id . ' ' . __( '(no label)', 'formidable-pro' ) : esc_html( $field_option->name );
	    ?></option>
	    <?php } ?>
	</select>
	<a href="javascript:void(0)" class="frm_remove_tag frm_icon_font" data-removeid="frm_watch_lookup_<?php echo esc_attr( $field_id . '_' . $row_key ); ?>" data-showlast="#frm_add_watch_lookup_link_<?php echo esc_attr( $field_id ); ?>" data-fieldid="<?php echo esc_attr( $field_id ); ?>"></a>
	<a href="javascript:void(0)" class="frm_add_tag frm_icon_font frm_add_watch_lookup_row"></a>
</div>
