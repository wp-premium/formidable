<div id="form_entries_page" class="wrap">
    <h2><?php _e( 'Edit Entry', 'formidable' ) ?>
        <?php do_action('frm_entry_inside_h2', $form); ?>
    </h2>

	<div class="frm_forms<?php echo FrmFormsHelper::get_form_style_class($values); ?>" id="frm_form_<?php echo (int) $form->id ?>_container">
        <form enctype="multipart/form-data" method="post" id="form_<?php echo esc_attr( $form->form_key ) ?>" class="frm-show-form">

        <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
        <div id="post-body-content">
            <?php
            if ( $form ) {
                FrmAppController::get_form_nav($form->id, true);
            } ?>
        	<div class="clear"></div>
        	<?php

        	include(FrmAppHelper::plugin_path() .'/classes/views/frm-entries/errors.php');

            $form_action = 'update';
            require(FrmAppHelper::plugin_path() .'/classes/views/frm-entries/form.php');
		?>

        <p>
        <?php echo FrmProFormsHelper::get_prev_button($form, 'button-secondary'); ?>
        <input class="button-primary" type="submit" value="<?php echo esc_attr($submit) ?>" <?php do_action('frm_submit_button_action', $form, $form_action); ?> />
        </p>
        </div>

        <?php require(FrmAppHelper::plugin_path() .'/pro/classes/views/frmpro-entries/sidebar-edit.php'); ?>
        </div>
        </div>
        </form>

        </div>
    </div>