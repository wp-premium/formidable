<div class="general_settings metabox-holder tabs-panel" style="min-height:0px;border-bottom:none;display:<?php echo ( $a == 'general_settings' ) ? 'block' : 'none'; ?>;">
<?php if ( $show_creds_form ) { ?>
    <div class="postbox">
		<h3 class="hndle manage-menus"><span class="dashicons dashicons-post-status"></span>
			<?php _e( 'Formidable Forms License', 'formidable' ) ?>
		</h3>
        <div class="inside">
            <?php $edd_update->pro_cred_form(); ?>
        </div>
    </div>
<?php } ?>
</div>