<div id="postbox-container-1" class="postbox-container">
    <div id="submitdiv" class="postbox ">
    <div class="inside">
        <div class="submitbox">
        <div id="minor-publishing" style="border:none;">
        <div class="misc-pub-section frm-postbox-no-h3">
            <?php
            if ( $record->is_draft ) {
                echo FrmProFormsHelper::get_draft_button($form, 'button-secondary');
            }
            ?>

            <a href="?page=formidable-entries&amp;frm_action=show&amp;id=<?php echo (int) $record->id; ?>" class="button-secondary alignright"><?php _e( 'View', 'formidable' ) ?></a>

            <div class="clear"></div>

             <?php do_action('frm_edit_entry_publish_box', $record); ?>
        </div>
        <div id="misc-publishing-actions">
            <?php include(FrmAppHelper::plugin_path() .'/classes/views/frm-entries/_sidebar-shared-pub.php'); ?>
        </div>
        </div>

        <div id="major-publishing-actions">
            <?php if ( current_user_can('frm_delete_entries') ) { ?>
    	    <div id="delete-action">
    	    <a href="?page=formidable-entries&amp;frm_action=destroy&amp;id=<?php echo (int) $record->id; ?>&amp;form=<?php echo (int) $form->id ?>" class="submitdelete deletion" onclick="return confirm('<?php _e( 'Are you sure you want to delete this entry?', 'formidable' ) ?>');" title="<?php esc_attr_e( 'Delete' ) ?>"><?php _e( 'Delete' ) ?></a>
    	    <?php if ( ! empty( $record->post_id ) ) { ?>
			<a href="?page=formidable-entries&amp;frm_action=destroy&amp;id=<?php echo (int) $record->id; ?>&amp;form=<?php echo (int) $form->id ?>&amp;keep_post=1" class="submitdelete deletion" style="margin-left:10px;" onclick="return confirm('<?php _e( 'Are you sure you want to delete this entry?', 'formidable' ) ?>');" title="<?php esc_attr_e( 'Delete entry but leave the post', 'formidable' ) ?>"><?php _e( 'Delete without Post', 'formidable' ) ?></a>
    	    <?php } ?>
    	    </div>
    	    <?php } ?>
    	    <div id="publishing-action">
				<?php if ( ! FrmProFormsHelper::is_final_page( $form->id ) ) { ?>
					<input type="submit" class="button frm_page_skip hide-no-js" data-page="" value="<?php esc_attr_e( 'Save', 'formidable' ) ?>">
				<?php } ?>
				<?php submit_button( $submit, 'primary', '', false ); ?>
            </div>
            <div class="clear"></div>
        </div>
        </div>
    </div>
    </div>

    <?php do_action('frm_edit_entry_sidebar', $record);
    FrmEntriesController::entry_sidebar($record);
    ?>
</div>