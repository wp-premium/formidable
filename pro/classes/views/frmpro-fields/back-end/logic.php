<tr><td><?php esc_html_e( 'Conditional Logic', 'formidable' ); ?></td>
	<td>
		<a href="javascript:void(0)" id="logic_<?php echo absint( $field['id'] ) ?>" class="frm_add_logic_row frm_add_logic_link <?php
		echo ( ! empty($field['hide_field']) && (count($field['hide_field']) > 1 || reset($field['hide_field']) != '') ) ? ' frm_hidden' : '';
		?>">
			<?php esc_html_e( 'Use Conditional Logic', 'formidable' ) ?>
		</a>
		<div class="frm_logic_rows<?php echo ( ! empty( $field['hide_field'] ) && ( count($field['hide_field']) > 1 || reset( $field['hide_field'] ) != '' ) ) ? '' : ' frm_hidden'; ?>">
			<div id="frm_logic_row_<?php echo absint( $field['id'] ) ?>">
				<select name="field_options[show_hide_<?php echo absint( $field['id'] ) ?>]">
					<option value="show" <?php selected($field['show_hide'], 'show') ?>><?php echo ($field['type'] == 'break') ? __( 'Do not skip', 'formidable' ) : __( 'Show', 'formidable' ); ?></option>
					<option value="hide" <?php selected($field['show_hide'], 'hide') ?>><?php echo ($field['type'] == 'break') ? __( 'Skip', 'formidable' ) : __( 'Hide', 'formidable' ); ?></option>
				</select>

				<?php $all_select =
					'<select name="field_options[any_all_'. absint( $field['id'] ) .']">'.
					'<option value="any" '. selected($field['any_all'], 'any', false) .'>'. __( 'any', 'formidable' ) .'</option>'.
					'<option value="all" '. selected($field['any_all'], 'all', false) .'>'. __( 'all', 'formidable' ) .'</option>'.
					'</select>';

				echo ($field['type'] == 'break') ?  sprintf(__( 'next page if %s of the following match:', 'formidable' ), $all_select) : sprintf(__( 'this field if %s of the following match:', 'formidable' ), $all_select);
				unset($all_select);

				if ( ! empty( $field['hide_field'] ) ) {
					foreach ( (array) $field['hide_field'] as $meta_name => $hide_field ) {
						include(FrmAppHelper::plugin_path() .'/pro/classes/views/frmpro-fields/_logic_row.php');
					}
				}
				?>
			</div>
		</div>


	</td>
</tr>