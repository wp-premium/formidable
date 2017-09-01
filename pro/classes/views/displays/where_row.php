<div id="frm_where_field_<?php echo esc_attr( $where_key ); ?>" class="frm_where_row">
    <select id="where_field_id" class="frm_insert_where_options" name="options[where][<?php echo esc_attr( $where_key ); ?>]">
        <option value=""><?php _e( '&mdash; Select &mdash;' ) ?></option>
        <option value="created_at" <?php selected($where_field, 'created_at') ?>><?php _e( 'Entry creation date', 'formidable' ) ?></option>
        <option value="updated_at" <?php selected($where_field, 'updated_at') ?>><?php _e( 'Entry updated date', 'formidable' ) ?></option>
        <option value="id" <?php selected($where_field, 'id') ?>><?php _e( 'Entry ID', 'formidable' ) ?></option>
        <option value="item_key" <?php selected($where_field, 'item_key') ?>><?php _e( 'Entry key', 'formidable' ) ?></option>
		<option value="post_id" <?php selected($where_field, 'post_id') ?>><?php _e( 'Post ID', 'formidable' ) ?></option>
        <option value="parent_item_id" <?php selected($where_field, 'parent_item_id') ?>><?php _e( 'Parent entry ID', 'formidable' ) ?></option>
        <option value="is_draft" <?php selected($where_field, 'is_draft') ?>><?php _e( 'Entry status', 'formidable' ) ?></option>
        <?php
        if ( is_numeric($form_id) ) {
			FrmProFieldsHelper::get_field_options( $form_id, $where_field, 'not', array( 'break', 'end_divider', 'divider', 'file', 'captcha', 'form' ), array( 'inc_sub' => 'include' ) );
        } ?>
        <option value="ip" <?php selected($where_field, 'ip') ?>><?php _e( 'IP', 'formidable' ) ?></option>
    </select>
    <?php _e( 'is', 'formidable' ) ?>
    <select id="where_field_is_<?php echo esc_attr( $where_key ); ?>" class="frm_where_is_options" name="options[where_is][<?php echo esc_attr( $where_key ); ?>]">
        <option value="=" <?php selected($where_is, '=') ?>><?php _e( 'equal to', 'formidable' ) ?></option>
        <option value="!=" <?php selected($where_is, '!=') ?>><?php _e( 'NOT equal to', 'formidable' ) ?></option>
        <option value=">" <?php selected($where_is, '>') ?>><?php _e( 'greater than', 'formidable' ) ?></option>
        <option value="<" <?php selected($where_is, '<') ?>><?php _e( 'less than', 'formidable' ) ?></option>
        <option value=">=" <?php selected($where_is, '>=') ?>><?php _e( 'greater than or equal to', 'formidable' ) ?> &nbsp;</option>
        <option value="<=" <?php selected($where_is, '<=') ?>><?php _e( 'less than or equal to', 'formidable' ) ?></option>
        <option value="LIKE" <?php selected($where_is, 'LIKE') ?>><?php _e( 'like', 'formidable' ) ?></option>
        <option value="not LIKE" <?php selected($where_is, 'not LIKE') ?>><?php _e( 'NOT like', 'formidable' ) ?></option>
		<option value="LIKE%" <?php selected($where_is, 'LIKE%') ?>><?php _e( 'starts with', 'formidable' ) ?></option>
		<option value="%LIKE" <?php selected($where_is, '%LIKE') ?>><?php _e( 'ends with', 'formidable' ) ?></option>
		<option value="group_by" <?php selected($where_is, 'group_by') ?>><?php _e( 'unique (get oldest entries)', 'formidable' ) ?></option>
		<option value="group_by_newest" <?php selected($where_is, 'group_by_newest') ?>><?php _e( 'unique (get newest entries)', 'formidable' ) ?></option>
    </select>
    <span id="where_field_options_<?php echo esc_attr( $where_key ); ?>" class="frm_where_val frm_inline<?php echo esc_attr( strpos( $where_is, 'group_by' ) === 0 ? ' frm_hidden' : '' ); ?>">
        <?php FrmProDisplaysController::add_where_options($where_field, $where_key, $where_val); ?>
    </span>
    <a href="javascript:void(0)" class="frm_remove_tag frm_icon_font" data-removeid="frm_where_field_<?php echo esc_attr( $where_key ); ?>" data-showlast="#frm_where_options .frm_add_where_row"></a>
    <a href="javascript:void(0)" class="frm_add_where_row frm_add_tag frm_icon_font"></a>

</div>