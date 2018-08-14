<?php
if ( $field['type'] == 'divider' ) {

    if ( ! isset($field['slide']) ) {
        $field['slide'] = false;
    }

?>
<label for="frm_slide_field_<?php echo absint( $field['id'] ) ?>" class="frm_inline_label frm_help" title="<?php esc_attr_e( 'Collapsible: This section will slide open and closed.', 'formidable-pro' ) ?>" >
	<input type="checkbox" id="frm_slide_field_<?php echo absint( $field['id'] ) ?>" name="field_options[slide_<?php echo absint( $field['id'] ) ?>]" value="1" <?php checked( $field['slide'], 1 ); ?>/>
	<?php esc_html_e( 'Collapsible', 'formidable-pro' ); ?>
</label>
<?php
    if ( ! isset($field['repeat']) ) {
        $field['repeat'] = false;
    }
?>
<label for="frm_repeat_field_<?php echo absint( $field['id'] ) ?>" class="frm_inline_label frm_help" title="<?php esc_attr_e( 'Repeatable: This section can be repeated when viewing your form.', 'formidable-pro' ) ?>">
	<input type="checkbox" id="frm_repeat_field_<?php echo absint( $field['id'] ) ?>" name="field_options[repeat_<?php echo absint( $field['id'] ) ?>]" class="frm_repeat_field" value="1" <?php checked( $field['repeat'], 1 ); ?> />
	<?php esc_html_e( 'Repeatable', 'formidable-pro' ); ?>
</label>
<?php
}
