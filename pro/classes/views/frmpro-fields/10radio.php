<?php
if ( is_array($field['options']) ) {
    if ( ! isset($field['value']) ) {
        $field['value'] = maybe_unserialize($field['default_value']);
    }

	$star = FrmField::is_option_true( $field, 'star' );
    foreach ( $field['options'] as $opt_key => $opt ) {
        $opt = apply_filters('frm_field_label_seen', $opt, $opt_key, $field);
        $last = end($field['options']) == $opt ? ' frm_last' : '';

if ( ! $star ) {
    ?><div class="frm_scale <?php echo esc_attr( $last ) ?>"><label for="<?php echo esc_attr( $html_id ) ?>-<?php echo esc_attr( $opt_key ) ?>"><?php
} ?>
<input type="radio" name="<?php echo esc_attr( $field_name ) ?>" id="<?php echo esc_attr( $html_id . '-' . $opt_key ) ?>" value="<?php echo esc_attr( $opt ) ?>" <?php
	checked( $field['value'], $opt ) . ' ';
    do_action( 'frm_field_input_html', $field );
?> />
<?php if ( ! $star ) {
    echo esc_html( $opt ) ?></label>
</div>
<?php }

}
} ?>
<div style="clear:both;"></div>