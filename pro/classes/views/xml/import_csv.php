<div class="wrap">
    <h2><?php _e( 'Import/Export', 'formidable-pro' ); ?></h2>

	<?php include( FrmAppHelper::plugin_path() . '/classes/views/shared/errors.php' ); ?>
    <div id="poststuff" class="metabox-holder">
    <div id="post-body">
    <div id="post-body-content">

    <div class="postbox ">
    <h3 class="hndle"><span><?php _e( 'Importing CSV', 'formidable-pro' ) ?></span></h3>
    <div class="inside">
        <div class="with_frm_style" id="frm_import_message">
			<span class="frm_message" style="padding:7px;">
				<?php printf( __( '%1$s entries are importing', 'formidable-pro' ), '<span class="frm_csv_remaining">' . $left . '</span>' ); ?>
			</span>
		</div>

        <div class="frm_admin_progress">
          <div class="frm_admin_progress_bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="<?php echo esc_attr( $left ) ?>" style="width:0%;">
          </div>
        </div>


<script type="text/javascript">
/*<![CDATA[*/
__FRMURLVARS="<?php echo $url_vars ?>";
jQuery(document).ready(function(){frmImportCsv(<?php echo (int) $form_id ?>);});
/*]]>*/
</script>
    </div>
    </div>
    </div>
    </div>
    </div>
</div>
