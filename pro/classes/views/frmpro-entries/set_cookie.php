<script type="text/javascript">
jQuery(document).ready(function($){
jQuery.ajax({type:'POST',url:'<?php echo esc_url_raw( admin_url( 'admin-ajax.php' ) ); ?>',
data:"action=frm_entries_ajax_set_cookie&entry_id=<?php echo (int) $entry_id; ?>&form_id=<?php echo (int) $form_id; ?>"
});
});
</script>
