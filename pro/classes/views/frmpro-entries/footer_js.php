
<script type="text/javascript">
/*<![CDATA[*/
<?php
if ( isset($frm_vars['tinymce_loaded']) && $frm_vars['tinymce_loaded'] === true ) {
	echo 'var ajaxurl="' . esc_url( admin_url( 'admin-ajax.php', 'relative' ) ) . '";' . "\n";
}

if ( isset( $frm_vars['rules'] ) && ! empty( $frm_vars['rules'] ) ) {
?>var frmrules=<?php echo json_encode( $frm_vars['rules'] ) ?>;
if(typeof __FRMRULES == 'undefined'){__FRMRULES=frmrules;}
else{__FRMRULES=jQuery.extend({},__FRMRULES,frmrules);}<?php
}

if ( isset($frm_vars['google_graphs']) && ! empty($frm_vars['google_graphs']) ) {
    echo '__FRMTABLES='. json_encode($frm_vars['google_graphs']) .";\n";
	echo 'frmFrontForm.loadGoogle();' . "\n";
}

?>
jQuery(document).ready(function($){
<?php
if ( $trigger_form ) { ?>
$(document).off('submit.formidable','.frm-show-form');$(document).on('submit.formidable','.frm-show-form',frmFrontForm.submitForm);
<?php
}

FrmProFormsHelper::load_chosen_js($frm_vars);

$logic_fields = FrmProFormsHelper::hide_conditional_fields( $frm_vars );
if ( ! empty( $logic_fields['hide'] ) ) {
	echo "frmFrontForm.hideCondFields('" . json_encode( $logic_fields['hide'] ) . "');";
}

if ( ! empty( $logic_fields['check'] ) ) {
	echo "frmFrontForm.checkDependent('" . json_encode( $logic_fields['check'] ) . "');";
}

FrmProFormsHelper::load_datepicker_js( $frm_vars );

FrmProFormsHelper::load_calc_js($frm_vars);

FrmProFormsHelper::load_input_mask_js($frm_input_masks);

?>
});
/*]]>*/
</script>
