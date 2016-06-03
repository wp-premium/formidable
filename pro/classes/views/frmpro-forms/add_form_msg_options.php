<tr class="edit_action_message_box edit_action_box <?php echo ($values['edit_action'] == 'message' && $values['editable'] == 1 ) ? '' : 'frm_hidden'; ?>">
    <td>
        <div><?php _e( 'On Update', 'formidable' ) ?></div>
        <textarea name="options[edit_msg]" id="edit_msg" cols="50" rows="2" class="frm_long_input"><?php echo FrmAppHelper::esc_textarea($values['edit_msg']); ?></textarea>
    </td>
</tr>
<tr class="hide_save_draft <?php echo esc_attr( $values['save_draft'] ? '' : ' frm_hidden' ); ?>">
    <td>
        <div><?php _e( 'Saved Draft', 'formidable' ) ?></div>
        <textarea name="options[draft_msg]" id="draft_msg" cols="50" rows="2" class="frm_long_input"><?php echo FrmAppHelper::esc_textarea($values['draft_msg']); ?></textarea>
        <!--
        <select name="options[save_draft]" id="save_draft" class="hide_save_draft">
            <option value="0"><?php _e( 'No one', 'formidable' ) ?></option>
            <option value="1" <?php echo (($values['save_draft'] == 1) ?' selected="selected"':''); ?>><?php _e( 'Logged-in Users', 'formidable' ) ?></option>
        </select>
        -->
    </td>
</tr>