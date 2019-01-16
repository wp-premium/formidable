<div id="postbox-container-1" class="postbox-container frm-right-panel">
    <div id="submitdiv" class="postbox">
    <div class="inside">
        <div class="submitbox">
        <div id="major-publishing-actions">
    	    <div id="delete-action">
				<a href="javascript:void(0)" class="submitdelete deletion" onclick="history.back(-1)" title="<?php esc_attr_e( 'Cancel', 'formidable-pro' ) ?>">
					<?php esc_html_e( 'Cancel', 'formidable-pro' ); ?>
				</a>
    	    </div>
    	    <div id="publishing-action">
				<?php echo FrmProFormsHelper::get_draft_button( $form, 'button-secondary' ); ?>
				<?php submit_button( $submit, 'primary', '', false ); ?>
            </div>
            <div class="clear"></div>
        </div>
        </div>
    </div>
    </div>
</div>
