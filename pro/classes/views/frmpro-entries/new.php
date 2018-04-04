<div id="form_entries_page" class="frm_wrap frm_single_entry_page">
    <div class="frm_forms<?php echo FrmFormsHelper::get_form_style_class( $form ); ?>" id="frm_form_<?php echo (int) $form->id ?>_container">

        <form enctype="multipart/form-data" method="post" id="form_<?php echo esc_attr( $form->form_key ) ?>" class="frm-show-form">

        <div id="poststuff" class="frm_page_container">

            <div id="post-body" class="metabox-holder columns-2">
            <div id="post-body-content">

			<?php
			FrmAppHelper::get_admin_header( array(
				'label' => __( 'Add New Entry', 'formidable-pro' ),
				'form'  => $form,
			) );
			?>

    <?php if ( empty( $values ) ) { ?>
			<p class="frm_error_style frm_form_fields">
				<strong><?php _e( 'Oops!', 'formidable-pro' ) ?></strong>
				<?php printf( __( 'You did not add any fields to your form. %1$sGo back%2$s and add some.', 'formidable-pro' ), '<br/><a href="' . esc_url( admin_url('?page=formidable&frm_action=edit&id=' . $form->id ) ) . '">', '</a>') ?>
			</p>
    <?php
        } else {

			include( FrmAppHelper::plugin_path() . '/classes/views/frm-entries/errors.php' );

			$form_action = 'create';
			require( FrmAppHelper::plugin_path() . '/classes/views/frm-entries/form.php' );
			?>

            <p>
                <?php echo FrmProFormsHelper::get_prev_button($form, 'button-secondary'); ?>
                <input class="button-primary" type="submit" value="<?php echo esc_attr($submit) ?>" <?php do_action('frm_submit_button_action', $form, $form_action); ?> />
                <?php echo FrmProFormsHelper::get_draft_link($form); ?>
            </p>
		<?php
		}
		?>
			</div>
            <?php include( FrmProAppHelper::plugin_path() . '/classes/views/frmpro-entries/sidebar-new.php' ); ?>
            </div>
            </div>
        </form>
    </div>
</div>
