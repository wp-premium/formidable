<div class="frmcal" id="frmcal-<?php echo esc_attr( $display->ID ) ?>">
<div class="frmcal-header"><a href="<?php echo esc_url( add_query_arg( array( 'frmcal-month' => $prev_month, 'frmcal-year' => $prev_year) ) ) ?>#frmcal-<?php echo esc_attr( $display->ID ) ?>" class="frmcal-prev" title="<?php echo esc_attr( $month_names[ $prev_month ] ) ?>">&larr; <?php echo $month_names[ $prev_month ] ?></a><select class="frmcal-dropdown" onchange="window.location='<?php echo esc_url( remove_query_arg( 'frmcal-month', add_query_arg( array( 'frmcal-year' => $year ) ) ) ) ?>&amp;frmcal-month='+this.value+'#frmcal-<?php echo esc_attr( $display->ID ) ?>';"><?php

foreach ( $month_names as $mkey => $mname ) {
    echo '<option value="'. esc_attr( $mkey ) .'"'. ( $mkey == $month ? ' selected="selected"' : '' ) .'>'. $mname .'</option>';
    unset($mkey, $mname);
}

?></select> <select class="frmcal-dropdown" onchange="window.location='<?php echo esc_url( remove_query_arg( 'frmcal-year', add_query_arg( array( 'frmcal-month' => $month ) ) ) ) ?>&amp;frmcal-year='+this.value+'#frmcal-<?php echo esc_attr( $display->ID ) ?>';"><?php
for ( $i = ( $year - 5 ); $i <= ( $year + 5 ); $i++ ) {
	echo '<option value="' . esc_attr( $i ) . '"' . ( $i == $year ? ' selected="selected"' : '' ) . '>' . $i . '</option>';
}
unset($i);
?></select> <a href="<?php echo esc_url( add_query_arg( array( 'frmcal-month' => $next_month, 'frmcal-year' => $next_year) ) ) ?>#frmcal-<?php echo esc_attr( $display->ID ) ?>" class="frmcal-next" title="<?php echo esc_attr( $month_names[ $next_month ] ) ?>"><?php echo $month_names[ $next_month ] ?> &rarr;</a><div class="frmcal-title"><span class="frmcal-month"><?php echo $month_names[ $month ] ?></span> <span class="frmcal-year"><?php echo $year ?></span></div></div>
<table class="frmcal-calendar"><tbody>