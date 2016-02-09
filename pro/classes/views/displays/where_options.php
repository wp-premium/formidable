<?php
if ( isset($field) && $field->type == 'user_id' ) { ?>
<select name="options[where_val][<?php echo esc_attr( $where_key ); ?>]">
<option value="current_user"><?php _e( 'Current User', 'formidable' ) ?></option>
<?php
$users = FrmProFieldsHelper::get_user_options();
foreach ( $users as $user_id => $user_login ) {
	if ( empty($user_id) ) {
		continue;
	}
?>
<option value="<?php echo esc_attr( $user_id ) ?>" <?php selected( $where_val, $user_id ) ?>><?php echo esc_html( $user_login ) ?></option>
<?php } ?>
</select>
<?php
} else if ( isset($field->field_options) && isset($field->field_options['post_field']) && $field->field_options['post_field'] == 'post_status' ) {
$options = FrmProFieldsHelper::get_status_options($field); ?>
<select name="options[where_val][<?php echo esc_attr( $where_key ); ?>]">
    <?php foreach ( $options as $opt_key => $opt ) { ?>
	<option value="<?php echo esc_attr( $opt_key ) ?>" <?php selected( $where_val, $opt_key ) ?>><?php echo esc_html( $opt ) ?></option>
    <?php } ?>
</select>
<?php
} else {
    if ( isset($field) && $field->type == 'date' ) {
    ?><span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Date options: \'NOW\' or a date in yyyy-mm-dd format.', 'formidable' ) ?>" ></span> <?php
    }
?>
<input type="text" value="<?php echo esc_attr( $where_val ) ?>" name="options[where_val][<?php echo esc_attr( $where_key ); ?>]"/>
<?php
}