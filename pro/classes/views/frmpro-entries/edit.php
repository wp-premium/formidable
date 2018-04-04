<div id="form_entries_page" class="frm_wrap frm_single_entry_page">
	<div class="frm_forms<?php echo FrmFormsHelper::get_form_style_class($values); ?>" id="frm_form_<?php echo (int) $form->id ?>_container">
        <form enctype="multipart/form-data" method="post" id="form_<?php echo esc_attr( $form->form_key ) ?>" class="frm-show-form">

        <div id="poststuff" class="frm_page_container">
        <div id="post-body" class="metabox-holder columns-2">
        <div id="post-body-content">
		<?php
			FrmAppHelper::get_admin_header( array(
				'label' => __( 'Edit Entry', 'formidable-pro' ),
				'link_hook' => array( 'hook' => 'frm_entry_inside_h2', 'param' => $form ),
				'form'  => $form,
			) );

        	include( FrmAppHelper::plugin_path() . '/classes/views/frm-entries/errors.php' );

            $form_action = 'update';
			require( FrmAppHelper::plugin_path() . '/classes/views/frm-entries/form.php' );
		?>

        <p>
        <?php echo FrmProFormsHelper::get_prev_button($form, 'button-secondary'); ?>
        <input class="button-primary" type="submit" value="<?php echo esc_attr($submit) ?>" <?php do_action('frm_submit_button_action', $form, $form_action); ?> />
        </p>
        </div>

        <?php require(FrmProAppHelper::plugin_path() . '/classes/views/frmpro-entries/sidebar-edit.php'); ?>
        </div>
        </div>
        </form>
	</div>
</div>
