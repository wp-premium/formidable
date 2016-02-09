<div class="misc-pub-section">
    <span class="misc-pub-revisions frm_icon_font frm_email_icon">
    <?php printf( __( 'Emails: %1$s', 'formidable' ), FrmProEntriesHelper::resend_email_links($entry->id, $entry->form_id, array( 'label' => __( 'Resend', 'formidable' ), 'echo' => false) ) ); ?>
    </span>
</div>