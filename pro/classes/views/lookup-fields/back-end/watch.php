<tr>
	<td><label><?php _e( 'Watch', 'formidable-pro' ) ?></label></td>
	<td>
	    <a href="javascript:void(0)" id="frm_add_watch_lookup_link_<?php echo esc_attr( $field['id'] ) ?>" class="frm_add_watch_lookup_row frm_add_watch_lookup_link <?php
		echo esc_attr( empty( $field['watch_lookup'] ) ? '' : 'frm_hidden' );
		?>">
		<?php _e( 'Watch Lookup fields', 'formidable-pro' ) ?></a>
		<div id="frm_watch_lookup_block_<?php echo esc_attr( $field['id'] ) ?>" class="frm_add_remove <?php echo esc_attr( empty( $field['watch_lookup'] ) ? 'frm_hidden' : '' ) ?>"><?php
		$field_id = $field['id'];
		foreach ( $field['watch_lookup'] as $row_key => $selected_field ) {
			include( FrmProAppHelper::plugin_path() . '/classes/views/lookup-fields/back-end/watch-row.php' );
		}
		?></div>
	</td>
</tr>
