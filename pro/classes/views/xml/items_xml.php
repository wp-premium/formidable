<?php

if ( ! $item_ids ) {
    return;
}
$item_form_id = 0;

// fetch 20 posts at a time rather than loading the entire table into memory
while ( $next_set = array_splice( $item_ids, 0, 20 ) ) {
$entries = FrmDb::get_results( 'frm_items', array( 'or' => 1, 'id' => $next_set, 'parent_item_id' => $next_set ) );

// Begin Loop
foreach ( $entries as $entry ) {
	if ( $item_form_id != $entry->form_id ) {
		$fields = FrmField::get_all_for_form( $entry->form_id );
        $item_form_id = $entry->form_id;
    }
?>
	<item>
		<id><?php echo absint( $entry->id ) ?></id>
		<item_key><?php echo FrmXMLHelper::cdata($entry->item_key) ?></item_key>
		<name><?php echo FrmXMLHelper::cdata($entry->name) ?></name>
		<description><?php echo FrmXMLHelper::cdata($entry->description) ?></description>
		<created_at><?php echo esc_html( $entry->created_at ) ?></created_at>
		<updated_at><?php echo esc_html( $entry->updated_at ) ?></updated_at>
		<form_id><?php echo absint( $entry->form_id ) ?></form_id>
		<post_id><?php echo absint( $entry->post_id ) ?></post_id>
		<ip><?php echo esc_html( $entry->ip ) ?></ip>
		<is_draft><?php echo absint( $entry->is_draft ) ?></is_draft>
		<user_id><?php echo FrmXMLHelper::cdata(FrmProFieldsHelper::get_display_name($entry->user_id, 'user_login')); ?></user_id>
		<updated_by><?php echo FrmXMLHelper::cdata(FrmProFieldsHelper::get_display_name($entry->updated_by, 'user_login')); ?></updated_by>
		<parent_item_id><?php echo absint( $entry->parent_item_id ) ?></parent_item_id>

<?php
        $metas = FrmDb::get_results( $wpdb->prefix .'frm_item_metas', array( 'item_id' => $entry->id), 'meta_value, field_id' );

		foreach ( $metas as $meta ) { ?>
		<item_meta>
			<field_id><?php echo absint( $meta->field_id ) ?></field_id>
		    <meta_value><?php
		        if ( isset( $fields[ $meta->field_id ] ) ) {
		            $meta->meta_value = FrmProFieldsHelper::get_export_val( $meta->meta_value, $fields[ $meta->field_id ] );
                }

		        echo FrmXMLHelper::cdata($meta->meta_value);

		        unset($meta);
		    ?></meta_value>
		</item_meta>
<?php   } ?>
	</item>
<?php
    unset($metas);

    if ( ! empty( $entry->post_id ) ) {
        $old_ids = $item_ids;
        $item_ids = array($entry->post_id);
        include(FrmAppHelper::plugin_path() .'/classes/views/xml/posts_xml.php');
        $item_ids = $old_ids;
    }

    unset($entry);
}
}

if ( isset($fields) ) {
    unset($fields);
}
