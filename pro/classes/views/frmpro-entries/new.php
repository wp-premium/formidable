<div id="form_entries_page" class="wrap">
    <h2 style="height:34px;"><?php _e( 'Add New Entry', 'formidable' ); ?></h2>

    <?php if ( empty( $values ) ) { ?>
    <div class="frm_forms <?php echo FrmFormsHelper::get_form_style_class($form); ?>" id="frm_form_<?php echo (int) $form->id ?>_container">
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
            <div id="post-body-content">
            <?php FrmAppController::get_form_nav($form, true); ?>
			<p class="clear frm_error_style"><strong><?php _e( 'Oops!', 'formidable' ) ?></strong> <?php printf( __( 'You did not add any fields to your form. %1$sGo back%2$s and add some.', 'formidable' ), '<br/><a href="' . esc_url( admin_url('?page=formidable&frm_action=edit&id=' . $form->id ) ) . '">', '</a>') ?></p>
            </div>
            <?php include(FrmAppHelper::plugin_path() .'/pro/classes/views/frmpro-entries/sidebar-new.php'); ?>
            </div>
        </div>
    </div>
    <?php
            return;
        } ?>
    <div class="frm_forms<?php echo FrmFormsHelper::get_form_style_class($values); ?>" id="frm_form_<?php echo (int) $form->id ?>_container">

        <form enctype="multipart/form-data" method="post" id="form_<?php echo esc_attr( $form->form_key ) ?>" class="frm-show-form">

        <div id="poststuff">

            <div id="post-body" class="metabox-holder columns-2">
            <div id="post-body-content">
            <?php
            FrmAppController::get_form_nav($form, true);
    		include(FrmAppHelper::plugin_path() .'/classes/views/frm-entries/errors.php');

			$form_action = 'create';
			require(FrmAppHelper::plugin_path() .'/classes/views/frm-entries/form.php');
			?>

            <p>
                <?php echo FrmProFormsHelper::get_prev_button($form, 'button-secondary'); ?>
                <input class="button-primary" type="submit" value="<?php echo esc_attr($submit) ?>" <?php do_action('frm_submit_button_action', $form, $form_action); ?> />
                <?php echo FrmProFormsHelper::get_draft_link($form); ?>
            </p>
            </div>
            <?php include(FrmAppHelper::plugin_path() .'/pro/classes/views/frmpro-entries/sidebar-new.php'); ?>
            </div>
            </div>
        </form>
    </div>

</div>