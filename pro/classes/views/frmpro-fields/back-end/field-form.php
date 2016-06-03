<?php
if ( empty( $field['form_select'] ) ) {
	echo '<p>'. esc_html__( 'Select a form to import below', 'formidable' ) .'</p>';
} else {
	$subfields = FrmField::get_all_for_form( $field['form_select'], 5 );
	foreach ( $subfields as $subfield ) { ?>
		<div class="subform_section subform_<?php echo esc_attr( $subfield->type ) ?>">
			<?php if ( $subfield->type != 'break' ) { ?>
				<label class="frm_primary_label"><?php echo esc_html( $subfield->name ) ?></label>
			<?php } ?>
			<?php FrmProFieldsController::show( $subfield, 'embed' ); ?>
			<div class="clear"></div>
		</div>
		<?php
	}
}
