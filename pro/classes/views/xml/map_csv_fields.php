<div class="wrap">
    <h2><?php _e( 'Import/Export', 'formidable-pro' ); ?></h2>

	<?php include( FrmAppHelper::plugin_path() . '/classes/views/shared/errors.php' ); ?>
    <div id="poststuff" class="metabox-holder">
    <div id="post-body">
    <div id="post-body-content">

    <div class="postbox ">
    <h3 class="hndle"><span><?php _e( 'Map Fields', 'formidable-pro' ) ?></span></h3>
    <div class="inside">

    <form method="post">
        <input type="hidden" name="frm_action" value="import_csv" />
		<input type="hidden" name="frm_import_file" value="<?php echo esc_attr( $media_id ) ?>" />
		<input type="hidden" name="row" value="<?php echo esc_attr( $row ) ?>" />
		<input type="hidden" name="form_id" value="<?php echo esc_attr( $form_id ) ?>" />
        <input type="hidden" name="csv_del" value="<?php echo esc_attr($csv_del) ?>" />
        <input type="hidden" name="csv_files" value="<?php echo esc_attr($csv_files) ?>" />
        <table class="form-table">
            <thead>
            <tr class="form-field">
                <th><b><?php esc_html_e( 'CSV header', 'formidable' ); ?></b></th>
                <th><b><?php esc_html_e( 'Sample data', 'formidable' ); ?></b></th>
                <th><b><?php esc_html_e( 'Corresponding Field', 'formidable' ); ?></b></th>
            </tr>
            </thead>
            <?php foreach ( $headers as $i => $header ) { ?>
            <tr class="form-field">
                <td><?php echo htmlspecialchars($header) ?></td>
                <td><?php if ( isset( $example[ $i ] ) ) { ?>
                    <span class="howto"><?php echo htmlspecialchars( $example[ $i ] ) ?></span>
                <?php } ?></td>
                <td>
                    <select name="data_array[<?php echo esc_attr( $i ) ?>]" id="mapping_<?php echo esc_attr( $i ) ?>">
                        <option value=""> </option>
						<?php
						foreach ( $fields as $field ) {
							if ( FrmField::is_no_save_field( $field->type ) ) {
                                continue;
                            }
							$selected = ( strtolower( strip_tags( $field->name ) ) == strtolower( htmlspecialchars( $header ) ) );
                            $selected = apply_filters('frm_map_csv_field', $selected, $field, $header);
                        ?>
                            <option value="<?php echo esc_attr( $field->id ) ?>" <?php selected($selected, true) ?>><?php echo FrmAppHelper::truncate($field->name, 50) ?></option>
                        <?php
                            unset($field);
                        }
                        ?>
                        <option value="post_id"><?php _e( 'Post ID', 'formidable-pro' ) ?></option>
                        <option value="created_at" <?php selected(strtolower(__( 'Timestamp', 'formidable-pro' )), strtolower(htmlspecialchars($header))) . selected(strtolower(__( 'Created at', 'formidable-pro' )), strtolower(htmlspecialchars($header))) . selected('created_at', $header) ?>><?php _e( 'Created at', 'formidable-pro' ) ?></option>
                        <option value="user_id" <?php selected(strtolower(__( 'Created by', 'formidable-pro' )), strtolower(htmlspecialchars($header))) . selected('user_id', $header) ?>><?php _e( 'Created by', 'formidable-pro' ) ?></option>
                        <option value="updated_at" <?php selected(__( 'last updated', 'formidable-pro' ), strtolower(htmlspecialchars($header))) . selected(__( 'updated at', 'formidable-pro' ), strtolower(htmlspecialchars($header))) . selected('updated_at', $header) ?>><?php _e( 'Updated at', 'formidable-pro' ) ?></option>
                        <option value="updated_by" <?php selected(__( 'updated by', 'formidable-pro' ), strtolower(htmlspecialchars($header))) . selected('updated_by', $header) ?>><?php _e( 'Updated by', 'formidable-pro' ) ?></option>
                        <option value="ip" <?php selected('ip', strtolower($header)) ?>><?php _e( 'IP Address', 'formidable-pro' ) ?></option>
                        <option value="is_draft" <?php selected('is_draft', strtolower($header)); selected('draft', strtolower($header)) ?>><?php _e( 'Is Draft', 'formidable-pro' ) ?></option>
                        <option value="id" <?php selected(__( 'Entry ID', 'formidable-pro' ), htmlspecialchars($header)) . selected('id', strtolower(htmlspecialchars($header))); ?>><?php _e( 'Entry ID', 'formidable-pro' ) ?></option>
                        <option value="item_key" <?php selected(__( 'Entry Key', 'formidable-pro' ), htmlspecialchars($header)) . selected('key', strtolower(htmlspecialchars($header))); ?>><?php _e( 'Entry Key', 'formidable-pro' ) ?></option>
                    </select>
                </td>
            </tr>
            <?php } ?>
        </table>
        <p class="submit">
            <input type="submit" value="<?php esc_attr_e( 'Import', 'formidable-pro' ) ?>" class="button-primary" />
        </p>
        <p class="howto"><?php esc_html_e( 'Note: If you select a field for the Entry ID or Entry Key, the matching entry with that ID or key will be updated.', 'formidable-pro' ) ?></p>
    </form>

    </div>
    </div>
    </div>
    </div>
    </div>
</div>
