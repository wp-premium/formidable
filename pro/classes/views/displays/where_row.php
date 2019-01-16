<div id="frm_where_field_<?php echo esc_attr( $where_key ); ?>" class="frm_where_row">
    <select id="where_field_id" class="frm_insert_where_options" name="options[where][<?php echo esc_attr( $where_key ); ?>]">
		<option value=""><?php esc_html_e( '&mdash; Select &mdash;' ) ?></option>
		<option value="created_at" <?php selected( $where_field, 'created_at' ); ?>>
			<?php esc_html_e( 'Entry creation date', 'formidable-pro' ); ?>
		</option>
		<option value="updated_at" <?php selected( $where_field, 'updated_at' ); ?>>
			<?php esc_html_e( 'Entry updated date', 'formidable-pro' ); ?>
		</option>
		<option value="id" <?php selected( $where_field, 'id' ); ?>>
			<?php esc_html_e( 'Entry ID', 'formidable-pro' ); ?>
		</option>
		<option value="item_key" <?php selected( $where_field, 'item_key' ); ?>>
			<?php esc_html_e( 'Entry key', 'formidable-pro' ); ?>
		</option>
		<option value="post_id" <?php selected( $where_field, 'post_id' ); ?>>
			<?php esc_html_e( 'Post ID', 'formidable-pro' ); ?>
		</option>
		<option value="parent_item_id" <?php selected( $where_field, 'parent_item_id' ); ?>>
			<?php esc_html_e( 'Parent entry ID', 'formidable-pro' ); ?>
		</option>
		<option value="is_draft" <?php selected( $where_field, 'is_draft' ); ?>>
			<?php esc_html_e( 'Entry status', 'formidable-pro' ); ?>
		</option>
        <?php
        if ( is_numeric($form_id) ) {
			FrmProFieldsHelper::get_field_options( $form_id, $where_field, 'not', array( 'break', 'end_divider', 'divider', 'file', 'captcha', 'form' ), array( 'inc_sub' => 'include' ) );
		}
		?>
		<option value="ip" <?php selected( $where_field, 'ip' ); ?>>
			<?php esc_html_e( 'IP', 'formidable-pro' ); ?>
		</option>
    </select>
	<?php esc_html_e( 'is', 'formidable-pro' ); ?>
    <select id="where_field_is_<?php echo esc_attr( $where_key ); ?>" class="frm_where_is_options" name="options[where_is][<?php echo esc_attr( $where_key ); ?>]">
		<?php foreach ( FrmProDisplaysHelper::where_is_options() as $opt => $label ) { ?>
			<option value="<?php echo esc_attr( $opt) ?>" <?php selected( $where_is, $opt ) ?>><?php echo esc_html( $label ) ?></option>
		<?php } ?>
    </select>
    <span id="where_field_options_<?php echo esc_attr( $where_key ); ?>" class="frm_where_val frm_inline<?php echo esc_attr( strpos( $where_is, 'group_by' ) === 0 ? ' frm_hidden' : '' ); ?>">
        <?php FrmProDisplaysController::add_where_options($where_field, $where_key, $where_val); ?>
    </span>
    <a href="javascript:void(0)" class="frm_remove_tag frm_icon_font" data-removeid="frm_where_field_<?php echo esc_attr( $where_key ); ?>" data-showlast="#frm_where_options .frm_add_where_row"></a>
    <a href="javascript:void(0)" class="frm_add_where_row frm_add_tag frm_icon_font"></a>

</div>
