<div class="frm-star-group">
<?php

for ( $i = 1; $i <= $max; $i++ ) {

	$class = 'star-rating-readonly star-rating';
	if ( $i <= $numbers['value'] ) {
		$class .= ' star-rating-on';
	} elseif ( $numbers['decimal'] && ( $i - 1 ) == $numbers['digit'] ) {
		$class .= ' frm_half_star';
	}

	?><i class="<?php echo esc_attr( $class ) ?>"></i><?php
}
?>
</div>
<div class="frm_clear"></div>
