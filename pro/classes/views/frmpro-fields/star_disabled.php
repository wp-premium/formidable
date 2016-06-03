<div class="frm_form_fields">
<?php
global $frm_vars;
if ( ! isset($frm_vars['star_loaded']) || ! is_array($frm_vars['star_loaded']) ) {
    $frm_vars['star_loaded'] = array();
}
if ( ! $frm_vars['forms_loaded'] || empty($frm_vars['forms_loaded']) ) {
    $frm_vars['forms_loaded'][] = true;
}

$rand = FrmProAppHelper::get_rand(3);
$name = $field->id . $rand;
if ( in_array($name, $frm_vars['star_loaded']) ) {
    $rand = FrmProAppHelper::get_rand(3);
    $name = $field->id . $rand;
}
$frm_vars['star_loaded'][] = $name;

$field->options = maybe_unserialize($field->options);
$max = max($field->options);

$d = 0;
if ( $stat != floor( $stat ) ) {
    $stat = round($stat, 2);
    list($n, $d) = explode('.', $stat);
    if ( strlen($d) == 1 ) {
        // make sure there are two digits after the decimal
        $d = $d * 10;
    }
    if ($d < 25) {
        $d = 0;
    } else if ( $d < 75 ) {
        $d = 5;
    } else {
        $d = 0;
        $n++;
    }

    $stat = (float) ($n .'.'. $d);
}

for ( $i = 1; $i <= $max; $i++ ) {
    // check if this is a half
    $class = ( $d && ($i-1) == $n ) ? ' frm_half_star' : '';

	?><input type="radio" name="item_meta[<?php echo esc_attr( $name ) ?>]" value="<?php echo esc_attr( $i ); ?>" <?php checked( round( $stat ), $i ) ?> class="star<?php echo esc_attr( $class ) ?>" disabled="disabled" style="display:none;"/><?php
} ?>
</div>