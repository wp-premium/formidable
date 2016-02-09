<?php
if ( 'data' != $field['type'] || ! $form_list ) {
    return;
} ?>
<div class="frm-show-click frm_import_options" style="margin:7px 0 5px;">
<?php _e( 'Load Options From', 'formidable' ) ?>:
<select name="frm_tax_entry_field_<?php echo absint( $field['id'] ) ?>" id="frm_tax_entry_field_<?php echo absint( $field['id'] ) ?>" class="frm_tax_form_select">
    <option value=""><?php _e( '&mdash; Select &mdash;', 'formidable' ) ?></option>
    <option value="form" <?php echo ( is_object($selected_field) ) ? 'selected="selected"' : ''; ?>><?php _e( 'Form Entries', 'formidable' ) ?></option>
    <option value="taxonomy" <?php
        if ( ! is_object($selected_field) ) {
            selected($selected_field, 'taxonomy');
        }
    ?>><?php _e( 'Category/Taxonomy', 'formidable' ) ?></option>
</select>

<span id="frm_show_selected_forms_<?php echo absint( $field['id'] ) ?>" class="<?php echo is_object( $selected_field ) ? '' : 'frm_hidden'; ?>">
<select class="frm_options_field_<?php echo absint( $field['id'] ) ?> frm_get_field_selection" id="frm_options_field_<?php echo absint( $field['id'] ) ?>">
    <option value="">&mdash; <?php _e( 'Select Form', 'formidable' ) ?> &mdash;</option>
    <?php foreach ( $form_list as $form_opts ) { ?>
	<option value="<?php echo absint( $form_opts->id ) ?>" <?php selected( $form_opts->id, $selected_form_id ) ?>><?php echo FrmAppHelper::truncate( $form_opts->name, 30 ) ?></option>
    <?php } ?>
</select>
</span>

<span id="frm_show_selected_fields_<?php echo absint( $field['id'] ) ?>">
    <?php
    if ( is_object($selected_field) ) {
        include(FrmAppHelper::plugin_path() .'/pro/classes/views/frmpro-fields/field-selection.php');
    } else if ( $selected_field == 'taxonomy') { ?>
        <span class="howto"><?php _e( 'Select a taxonomy on the Form Actions tab of the Form Settings page', 'formidable' ); ?></span>
		<input type="hidden" name="field_options[form_select_<?php echo absint( $current_field_id ) ?>]" value="taxonomy" />
    <?php
    }
    ?>
</span>
</div>
