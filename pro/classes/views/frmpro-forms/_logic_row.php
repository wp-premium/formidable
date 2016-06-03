<div id="<?php echo esc_attr( $id ) ?>" class="frm_logic_row frm_logic_row_<?php echo esc_attr( $key ) ?>">
<select name="<?php echo esc_attr( $names['hide_field'] ) ?>" <?php if ( ! empty( $onchange ) ) { ?>onchange="<?php echo $onchange ?>"<?php } ?>>
    <option value=""><?php _e( '&mdash; Select &mdash;' ) ?></option>
    <?php
    foreach ( $form_fields as $ff ) {
        if ( is_array($ff) ) {
            $ff = (object) $ff;
        }

		if ( in_array( $ff->type, $exclude_fields ) || FrmProField::is_list_field( $ff ) ) {
            continue;
        }

        $selected = ( isset($condition['hide_field']) && $ff->id == $condition['hide_field'] ) ? ' selected="selected"' : ''; ?>
	<option value="<?php echo esc_attr( $ff->id ) ?>"<?php echo $selected ?>><?php echo FrmAppHelper::truncate($ff->name, 25); ?></option>
    <?php
        unset($ff);
        } ?>
</select>
<?php _e( 'is', 'formidable' ); ?>

<select name="<?php echo esc_attr( $names['hide_field_cond'] ) ?>">
    <option value="==" <?php selected($condition['hide_field_cond'], '==') ?>><?php _e( 'equal to', 'formidable' ) ?></option>
    <option value="!=" <?php selected($condition['hide_field_cond'], '!=') ?>><?php _e( 'NOT equal to', 'formidable' ) ?> &nbsp;</option>
    <option value=">" <?php selected($condition['hide_field_cond'], '>') ?>><?php _e( 'greater than', 'formidable' ) ?></option>
    <option value="<" <?php selected($condition['hide_field_cond'], '<') ?>><?php _e( 'less than', 'formidable' ) ?></option>
    <option value="LIKE" <?php selected($condition['hide_field_cond'], 'LIKE') ?>><?php _e( 'like', 'formidable' ) ?></option>
    <option value="not LIKE" <?php selected($condition['hide_field_cond'], 'not LIKE') ?>><?php _e( 'not like', 'formidable' ) ?> &nbsp;</option>
</select>

<span id="frm_show_selected_values_<?php echo esc_attr( $key . '_' . $meta_name ) ?>">
<?php
    if ( $condition['hide_field'] && is_numeric($condition['hide_field']) ) {
        $new_field = FrmField::getOne($condition['hide_field']);
    }

    $val = isset($condition['hide_opt']) ? $condition['hide_opt'] : '';
    if ( ! isset($field) ) {
        $field = array( 'hide_opt' => array($meta_name => $val));
    }
    $field_name = $names['hide_opt'];

    require(FrmAppHelper::plugin_path() .'/pro/classes/views/frmpro-fields/field-values.php');
?>
</span>
<a href="javascript:void(0)" class="frm_remove_tag frm_icon_font" data-removeid="<?php echo esc_attr( $id ) ?>" <?php echo ! empty( $showlast ) ? 'data-showlast="' . esc_attr( $showlast ) . '"' : ''; ?>></a>
<a href="javascript:void(0)" class="frm_add_tag frm_icon_font frm_add_<?php echo esc_attr( $type ) ?>_logic" data-emailkey="<?php echo esc_attr( $key ) ?>"></a>
</div>