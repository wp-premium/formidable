
<div id="frm_loading" style="display:none;background:url(<?php echo FrmProAppHelper::plugin_url() ?>/images/grey_bg.png);">
<div id="frm_loading_content">
<?php echo apply_filters( 'frm_uploading_files', '<h3>' . __( 'Uploading Files. Please Wait.', 'formidable-pro' ) . '</h3>' ); ?>
<div class="progress progress-striped active">
    <div class="progress-bar" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
</div>
</div>
</div>
