<div id="postbox-container-1" class="postbox-container frm-right-panel">
    <div id="submitdiv" class="postbox ">
    <div class="inside">
        <div class="submitbox">
	        <div id="major-publishing-actions">
				<div class="alignleft">
					<?php FrmEntriesHelper::actions_dropdown( array( 'id' => $record->id, 'entry' => $record ) ); ?>
				</div>
	    	    <div id="publishing-action">
					<?php
					if ( $record->is_draft ) {
						echo FrmProFormsHelper::get_draft_button( $form, 'button-secondary' );
					}
					?>
					<?php if ( ! FrmProFormsHelper::is_final_page( $form->id ) ) { ?>
						<input type="submit" class="button frm_page_skip hide-no-js" data-page="" value="<?php esc_attr_e( 'Save', 'formidable-pro' ) ?>">
					<?php } ?>
					<?php submit_button( $submit, 'primary', '', false ); ?>
	            </div>
	            <div class="clear"></div>
	        </div>
			<?php if ( has_action( 'frm_edit_entry_publish_box', $record ) ) { ?>
				<div id="minor-publishing" style="border:none;">
					<div class="misc-pub-section frm-postbox-no-h3">
						<?php do_action( 'frm_edit_entry_publish_box', $record ); ?>
					</div>
				</div>
			<?php } ?>
        </div>
    </div>
    </div>

	<?php
	do_action( 'frm_edit_entry_sidebar', $record );
    FrmEntriesController::entry_sidebar($record);
    ?>
</div>
