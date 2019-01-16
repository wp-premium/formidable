<tr class="hide_editable">
    <td>
		<label><?php esc_html_e( 'Update Button Text', 'formidable-pro' ); ?></label>
    </td>
    <td>
        <input type="text" name="options[edit_value]" value="<?php echo esc_attr($values['edit_value']); ?>" />
    </td>
</tr>

<?php if ( $page_field ) { ?>
<tr>
    <td>
		<label><?php esc_html_e( 'Previous Button Text', 'formidable-pro' ); ?></label>
    </td>
    <td>
        <input type="text" name="options[prev_value]" value="<?php echo esc_attr($values['prev_value']); ?>" />
    </td>
</tr>
<?php } ?>

<tr>
    <td>
		<label><?php esc_html_e( 'Submit Button Alignment', 'formidable-pro' ); ?></label>
    </td>
    <td>
        <select name="options[submit_align]">
			<option value=""><?php esc_html_e( 'Default', 'formidable-pro' ); ?></option>
			<option value="center" <?php selected( $values['submit_align'], 'center' ); ?>>
				<?php esc_html_e( 'Center', 'formidable-pro' ); ?>
			</option>
			<option value="inline" <?php selected( $values['submit_align'], 'inline' ); ?>>
				<?php esc_html_e( 'Inline', 'formidable-pro' ); ?>
			</option>
        </select>
    </td>
</tr>

<?php if ( version_compare( FrmAppHelper::plugin_version(), '3.01.04', '>=' ) ) { ?>
<tr>
    <td>
        <label><?php esc_html_e( 'Submit Button Logic', 'formidable-pro' ); ?></label>
	</td>
	<td>
		<?php include( FrmProAppHelper::plugin_path() . '/classes/views/frmpro-forms/_submit_conditional.php' ); ?>
	</td>
</tr>
<?php } ?>
