<table class="form-table">
    <tr class="form-field">
        <td class="frm_left_label"><?php _e( 'Use Entries from Form', 'formidable' ); ?></td>
        <td><?php FrmFormsHelper::forms_dropdown( 'form_id', $post->frm_form_id, array( 'inc_children' => 'include' ) ); ?>
        </td>
    </tr>
    <tr>
        <td><?php _e( 'View Format', 'formidable' ); ?></td>
        <td>
            <fieldset>
            <p><label for="all"><input type="radio" value="all" id="all" <?php checked($post->frm_show_count, 'all') ?> name="show_count" /> <?php _e( 'All Entries &mdash; list all entries in the specified form', 'formidable' ); ?>.</label></p>
            <p><label for="one"><input type="radio" value="one" id="one" <?php checked($post->frm_show_count, 'one') ?> name="show_count" /> <?php _e( 'Single Entry &mdash; display one entry', 'formidable' ); ?>.</label>
            </p>
            <p><label for="dynamic"><input type="radio" value="dynamic" id="dynamic" <?php checked($post->frm_show_count, 'dynamic') ?> name="show_count" /> <?php _e( 'Both (Dynamic) &mdash; list the entries that will link to a single entry page', 'formidable' ); ?>.</label></p>
            <p><label for="calendar"><input type="radio" value="calendar" id="calendar" <?php checked($post->frm_show_count, 'calendar') ?> name="show_count" /> <?php _e( 'Calendar &mdash; insert entries into a calendar', 'formidable' ); ?>.</label></p>
            </fieldset>

            <div id="date_select_container" class="frm_indent_opt <?php echo ($post->frm_show_count == 'calendar') ? '' : 'frm_hidden'; ?>">
                <?php include(FrmAppHelper::plugin_path() .'/pro/classes/views/displays/_calendar_options.php'); ?>
            </div>
        </td>
    </tr>
</table>