<?php
if ( empty( $values['fields'] ) ) {
    return;
}

foreach ( $values['fields'] as $fo_key => $fo ) {
    // don't include repeatable fields
	if ( ( isset( $post_field ) && ! in_array( $fo['type'], $post_field ) ) || FrmField::is_no_save_field( $fo['type'] ) || $fo['type'] == 'form' || $fo['form_id'] != $values['id'] ) {
        continue;
    }

    if ( $fo['post_field'] == $post_key ) {
		$values[ $post_key ] = $fo['id'];
    }
    ?>
	<option value="<?php echo esc_attr( $fo['id'] ) ?>" <?php selected( $form_action->post_content[ $post_key ], $fo['id'] ) ?>>
		<?php echo esc_html( FrmAppHelper::truncate( $fo['name'], 50 ) ); ?>
	</option>
<?php
	unset( $fo );
}
