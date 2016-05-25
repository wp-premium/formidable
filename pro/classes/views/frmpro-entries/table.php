<table class="form_results<?php echo ( $atts['style'] ? FrmFormsHelper::get_form_style_class() : '' ); ?>" id="form_results<?php echo (int) $atts['form']->id ?>" cellspacing="0">
    <thead>
    <tr>
    <?php if ( in_array( 'id', $atts['fields']) ) { ?>
    <th><?php _e( 'ID', 'formidable' ); ?></th>
    <?php }
	foreach ( $atts['form_cols'] as $col ) { ?>
        <th><?php echo $col->name; ?></th>
    <?php }
        if ( $atts['edit_link'] ) { ?>
    <th><?php echo $atts['edit_link']; ?></th>
    <?php }
		if ( $atts['delete_link'] ) { ?>
    <th><?php echo $atts['delete_link']; ?></th>
    <?php }
 ?>
    </tr>
    </thead>
    <tbody>
<?php if ( empty( $atts['entries'] ) ) { ?>
	<tr><td colspan="<?php echo count( $atts['form_cols'] ) ?>"><?php echo $atts['no_entries']; ?></td></tr>
<?php
}else{
    $class = 'odd';

	foreach ( $atts['entries'] as $entry ) {  ?>
        <tr class="frm_<?php echo esc_attr( $class ) ?>">
        <?php if ( in_array( 'id', $atts['fields']) ) { ?>
            <td><?php echo (int) $entry->id ?></td>
        <?php }
			foreach ( $atts['form_cols'] as $col ) { ?>
            <td valign="top">
                <?php echo FrmEntriesHelper::display_value(( isset($entry->metas[$col->id]) ? $entry->metas[$col->id] : false ), $col, array( 'type' => $col->type, 'post_id' => $entry->post_id, 'entry_id' => $entry->id));
                ?>
            </td>
<?php       }

            if ( $atts['edit_link'] ) { ?>
			<td><?php
				if ( FrmProEntriesHelper::user_can_edit( $entry, $atts['form'] ) ) {
        			?><a href="<?php echo esc_url( add_query_arg( array( 'frm_action' => 'edit', 'entry' => $entry->id ), $atts['permalink'] ) . $atts['anchor'] )  ?>"><?php echo $atts['edit_link']; ?></a><?php
        		} ?></td>
<?php       }
            if ( $atts['delete_link'] ) { ?>
		<td><?php
		if ( FrmProEntriesHelper::user_can_delete( $entry ) ) {
        ?><a href="<?php echo esc_url( add_query_arg( array( 'frm_action' => 'destroy', 'entry' => $entry->id ) ) ) ?>" class="frm_delete_link" data-frmconfirm="'<?php echo esc_attr( $atts['confirm'] ); ?>"><?php echo $atts['delete_link']; ?></a><?php
		} ?></td>
<?php       }
 ?>
        </tr>
<?php
    $class = ($class == 'even') ? 'odd' : 'even';
    }
}
?>
    </tbody>
    <tfoot>
    <tr>
		<?php foreach ( $atts['form_cols'] as $col ) { ?>
            <th><?php echo $col->name; ?></th>
        <?php }
		if ( $atts['edit_link'] ) { ?>
		    <th><?php echo $atts['edit_link']; ?></th>
		<?php }
		if ( $atts['delete_link'] ) { ?>
		    <th><?php echo $atts['delete_link']; ?></th>
		<?php } ?>
    </tr>
    </tfoot>
</table>