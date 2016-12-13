<tr class="frm_options_heading">
	<td colspan="2">
		<div class="menu-settings">
			<h3 class="frm_no_bg"><?php _e( 'Dynamic Values', 'formidable' ) ?></h3>
		</div>
	</td>
</tr><?php

if ( $display['default_value'] ) {
	include( FrmAppHelper::plugin_path() . '/pro/classes/views/frmpro-fields/back-end/dynamic-default-value.php' );
}

if ( $display['calc'] ) {
	include( FrmAppHelper::plugin_path() . '/pro/classes/views/frmpro-fields/back-end/calculations.php' );
}

if ( $display['autopopulate'] ) {
	FrmProLookupFieldsController::show_autopopulate_value_section_in_form_builder( $field );
} ?>