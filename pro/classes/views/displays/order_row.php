<div id="frm_order_field_<?php echo esc_attr( $order_key ); ?>" class="frm_order_row">
	<select id="order_by" name="options[order_by][<?php echo esc_attr( $order_key ); ?>]">
        <option value="id" <?php selected($order_by, 'id') ?>><?php _e( 'Entry ID', 'formidable' ) ?></option>
        <option value="created_at" <?php selected($order_by, 'created_at') ?>><?php _e( 'Entry creation date', 'formidable' ) ?></option>
        <option value="updated_at" <?php selected($order_by, 'updated_at') ?>><?php _e( 'Entry update date', 'formidable' ) ?></option>
        <option value="rand" <?php selected($order_by, 'rand') ?>><?php _e( 'Random', 'formidable' ) ?></option>
        <?php
        if ( is_numeric($form_id) ) {
            FrmProFieldsHelper::get_field_options($form_id, $order_by);
        } ?>
    </select>

    <select id="order" name="options[order][<?php echo esc_attr( $order_key ); ?>]">
        <option value="ASC" <?php selected($order, 'ASC') ?>><?php _e( 'Ascending', 'formidable' ); ?></option>
        <option value="DESC" <?php selected($order, 'DESC') ?>><?php _e( 'Descending', 'formidable' ); ?> &nbsp;</option>
    </select>
		<a href="javascript:void(0)" class="frm_remove_tag frm_icon_font" data-removeid="frm_order_field_<?php echo esc_attr( $order_key ); ?>" data-showlast="#frm_order_options .frm_add_order_row"></a>
	    <a href="javascript:void(0)" class="frm_add_order_row frm_add_tag frm_icon_font"></a>
</div>