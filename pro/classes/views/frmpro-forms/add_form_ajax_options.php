<tr>
    <td colspan="2">
		<label for="ajax_submit"><input type="checkbox" name="options[ajax_submit]" id="ajax_submit" value="1"<?php echo ($values['ajax_submit']) ? ' checked="checked"' : ''; ?> /> <?php _e( 'Submit this form with AJAX', 'formidable' ) ?></label>
		<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'If your form includes a file upload field, ajax submission will not be used.', 'formidable' ) ?>"></span>
    </td>
</tr>