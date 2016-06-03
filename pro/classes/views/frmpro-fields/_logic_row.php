<div id="frm_logic_<?php echo esc_attr( $field['id'] .'_'. $meta_name ) ?>" class="frm_logic_row">
<select name="field_options[hide_field_<?php echo esc_attr( $field['id'] ) ?>][]" onchange="frmGetFieldValues(this.value,<?php echo (int) $field['id'] ?>,<?php echo esc_attr( $meta_name ) ?>,'<?php echo esc_attr( $field['type'] ) ?>')">
    <option value=""><?php _e( '&mdash; Select &mdash;' ) ?></option>
    <?php
    $sel = false;
	foreach ( $form_fields as $ff ) {
		if ( $ff->id == $field['id'] || FrmField::is_no_save_field( $ff->type ) || in_array( $ff->type, array( 'file', 'rte', 'date', 'address', 'credit_card' ) ) || FrmProField::is_list_field( $ff ) ) {
            continue;
        }

		if ( $ff->id == $hide_field ) {
            $sel = true;
		}
    ?>
	<option value="<?php echo esc_attr( $ff->id ) ?>" <?php selected( $ff->id, $hide_field ) ?>>
		<?php echo esc_html( FrmAppHelper::truncate( $ff->name, 24 ) ); ?>
	</option>
    <?php } ?>
</select>
<?php
if ( $hide_field && ! $sel ) {
//remove conditional logic if the field doesn't exist ?>
<script type="text/javascript">jQuery(document).ready(function(){frmAdminBuild.triggerRemoveLogic(<?php echo (int) $field['id'] ?>, '<?php echo esc_attr( $meta_name ) ?>');});</script>
<?php
}
_e( 'is', 'formidable' );
$field['hide_field_cond'][$meta_name] = htmlspecialchars_decode($field['hide_field_cond'][$meta_name]); ?>

<select name="field_options[hide_field_cond_<?php echo esc_attr( $field['id'] ) ?>][]">
    <option value="==" <?php selected($field['hide_field_cond'][$meta_name], '==') ?>><?php _e( 'equal to', 'formidable' ) ?></option>
    <option value="!=" <?php selected($field['hide_field_cond'][$meta_name], '!=') ?>><?php _e( 'NOT equal to', 'formidable' ) ?> &nbsp;</option>
    <option value=">" <?php selected($field['hide_field_cond'][$meta_name], '>') ?>><?php _e( 'greater than', 'formidable' ) ?></option>
    <option value="<" <?php selected($field['hide_field_cond'][$meta_name], '<') ?>><?php _e( 'less than', 'formidable' ) ?></option>
    <option value="LIKE" <?php selected($field['hide_field_cond'][$meta_name], 'LIKE') ?>><?php _e( 'like', 'formidable' ) ?></option>
    <option value="not LIKE" <?php selected($field['hide_field_cond'][$meta_name], 'not LIKE') ?>><?php _e( 'not like', 'formidable' ) ?> &nbsp;</option>
</select>

<span id="frm_show_selected_values_<?php echo esc_attr( $field['id'] .'_'. $meta_name ) ?>">
<?php
    if ( $hide_field && is_numeric($hide_field) ) {
        $new_field = FrmField::getOne($hide_field);
        $field_type = $field['type'];
    }
    $current_field_id = $field['id'];

    require(FrmAppHelper::plugin_path() .'/pro/classes/views/frmpro-fields/field-values.php');
?>
</span>
<a href="javascript:void(0)" class="frm_remove_tag frm_icon_font" data-removeid="frm_logic_<?php echo esc_attr( $field['id'] .'_'. $meta_name ) ?>"></a>
<a href="javascript:void(0)" class="frm_add_tag frm_icon_font frm_add_logic_row"></a>
</div>