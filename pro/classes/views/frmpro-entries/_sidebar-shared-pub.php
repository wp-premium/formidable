<div class="misc-pub-section frm_no_print">
    <span class="misc-pub-revisions frm_icon_font frm_email_icon"></span>
    <?php printf( __( 'Emails: %1$s', 'formidable-pro' ), FrmProEntriesHelper::resend_email_links($entry->id, $entry->form_id, array( 'label' => __( 'Resend', 'formidable-pro' ), 'echo' => false) ) ); ?>
</div>
