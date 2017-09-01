<?php
if ( isset($field->field_options) && isset($field->field_options['post_field']) && $field->field_options['post_field'] == 'post_status' ) {
	$options = FrmProFieldsHelper::get_status_options( $field, $field->options ); ?>
	<select name="options[where_val][<?php echo esc_attr( $where_key ); ?>]">
		<?php foreach ( $options as $opt_key => $opt ) {

			if ( is_array( $opt ) ){
				$opt_key = isset( $opt['value'] ) ? $opt['value'] : ( isset( $opt['label'] ) ? $opt['label'] : reset( $opt ) );
				$opt = isset( $opt['label'] ) ? $opt['label'] : reset( $opt );
			} ?>
			<option
				value="<?php echo esc_attr( $opt_key ) ?>" <?php selected( $where_val, $opt_key ) ?>><?php echo esc_html( $opt ) ?></option>
		<?php } ?>
	</select>
	<?php
} else if ( $field_id == 'is_draft' ) { ?>
		<select name="options[where_val][<?php echo esc_attr( $where_key ); ?>]">
			<option value="both" <?php selected( $where_val, 'both' ) ?>><?php _e( 'Draft or complete entry', 'formidable' ) ?></option>
			<option value="1" <?php selected( $where_val, '1' ) ?>><?php _e( 'Draft', 'formidable' ) ?></option>
			<option value="0" <?php selected( $where_val, '0' ) ?>><?php _e( 'Complete entry', 'formidable' ) ?></option>
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