<div id="postbox-container-1" class="postbox-container">
    <div id="submitdiv" class="postbox ">
    <div class="inside">
        <div class="submitbox">
        <div class="misc-pub-section frm-postbox-no-h3">
            <?php echo $draft = FrmProFormsHelper::get_draft_button($form, 'button-secondary');
            if ( empty($draft) ) { ?>
            <p class="howto"><?php _e( 'Complete the form and save the entry', 'formidable' ); ?></p>
            <?php
            } ?>
        </div>
        <div id="major-publishing-actions">
    	    <div id="delete-action">
				<a href="javascript:void(0)" class="submitdelete deletion" onclick="history.back(-1)" title="<?php esc_attr_e( 'Cancel', 'formidable' ) ?>"><?php _e( 'Cancel', 'formidable' ) ?></a>
    	    </div>
    	    <div id="publishing-action">
				<?php submit_button( $submit, 'primary', '', false ); ?>
            </div>
            <div class="clear"></div>
        </div>
        </div>
    </div>
    </div>
</div>