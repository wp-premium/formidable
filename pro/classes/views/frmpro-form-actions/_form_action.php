<?php if ( ! empty($form_fields) ) { ?>
	<h3 class="frm_add_logic_link <?php echo esc_attr( $show_logic ? ' frm_hidden' : '' ); ?>" id="logic_link_<?php echo esc_attr( $action_key ) ?>">
		<a href="javascript:void(0)" class="frm_add_form_logic" data-emailkey="<?php echo esc_attr( $action_key ); ?>" id="email_logic_<?php echo esc_attr( $action_key ); ?>">
			<?php esc_html_e( 'Use Conditional Logic', 'formidable-pro' ); ?>
		</a>
	</h3>
<?php } ?>

<div class="frm_logic_rows frm_add_remove <?php echo esc_attr( $show_logic ? '' : ' frm_hidden' ); ?>" id="frm_logic_rows_<?php echo esc_attr( $action_key ) ?>">
	<h3><?php esc_html_e( 'Conditional Logic', 'formidable-pro' ); ?></h3>
    <div id="frm_logic_row_<?php echo esc_attr( $action_key ) ?>">
        <p><select name="<?php echo esc_attr( $action_control->get_field_name('conditions') ) ?>[send_stop]">
            <option value="send" <?php selected($form_action->post_content['conditions']['send_stop'], 'send') ?>><?php echo esc_html( $send ) ?></option>
            <option value="stop" <?php selected($form_action->post_content['conditions']['send_stop'], 'stop') ?>><?php echo esc_html( $stop ) ?></option>
        </select>
        <?php echo esc_html( $this_action_if ) ?>
        <select name="<?php echo esc_attr( $action_control->get_field_name('conditions') ) ?>[any_all]">
			<option value="any" <?php selected( $form_action->post_content['conditions']['any_all'], 'any' ); ?>>
				<?php esc_html_e( 'any', 'formidable-pro' ); ?>
			</option>
			<option value="all" <?php selected( $form_action->post_content['conditions']['any_all'], 'all' ); ?>>
				<?php esc_html_e( 'all', 'formidable-pro' ); ?>
			</option>
        </select>
        <?php esc_html_e( 'of the following match', 'formidable-pro' ) ?>:
        </p>

<?php

foreach ( $form_action->post_content['conditions'] as $meta_name => $condition ) {
    if ( ! is_numeric( $meta_name ) || ! isset( $condition['hide_field'] ) || empty( $condition['hide_field'] ) ) {
        continue;
    }

    FrmProFormsController::include_logic_row( array(
        'meta_name' => $meta_name,
        'condition' => $condition,
        'key'       => $action_key,
        'form_id'   => $values['id'],
		'name'      => $action_control->get_field_name( 'conditions' ) . '[' . $meta_name . ']',
		'exclude_fields' => FrmField::no_save_fields(),
		'hidelast'  => '#frm_logic_rows_' . $action_key,
		'showlast'  => '#logic_link_' . $action_key,
    ) );

    unset($meta_name, $condition);
}

?>
    </div>
</div>
