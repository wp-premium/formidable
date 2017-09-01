<?php

for ( $i = $week_begins; $i < ( $maxday+$startday ); $i++ ) {
    $pos = $i % 7;
    $end_tr = false;
	if ( $pos == $week_begins ) {
		echo "<tr>\n";
	}

    $day = $i - $startday + 1;

    //add classes for the day
    $day_class = '';

    //check for today
	if ( isset( $today ) && $day == $today ) {
        $day_class .= ' frmcal-today';
	}

	if ( $pos == 0 || $pos == 6 ) {
        $day_class .= ' frmcal-week-end';
	}

?>
<td<?php echo ( ! empty( $day_class ) ) ? ' class="' . esc_attr( $day_class ) . '"' : ''; ?>><div class="frmcal_date">
		<div class="frmcal_day_name"><?php
			echo isset( $day_names[$i] ) ? $day_names[$i] .' ' : '';
		?></div><?php
	unset($day_class);

	if ( $i >= $startday ) {
        ?><div class="frmcal_num"><?php echo esc_html( $day ) ?></div></div> <div class="frmcal-content">
<?php
        if ( isset($daily_entries[$i]) && ! empty($daily_entries[$i]) ) {
            foreach ( $daily_entries[$i] as $entry ) {
                //Set up current entry date for [event_date] shortcode
                $current_entry_date = $year . '-' . $month . '-' . ( $day < 10 ? '0' . $day : $day );

                if ( isset($used_entries[$entry->id]) ) {
                    $this_content = FrmProContent::replace_calendar_date_shortcode($used_entries[$entry->id], $current_entry_date);
                    echo '<div class="frm_cal_multi_'. $entry->id .'">'. $this_content .'</div>';
                } else {
                    // switch [event_date] to [calendar_date] so it can be replaced on each individual date instead of each entry
                    $new_content = str_replace( array( '[event_date]', '[event_date '), array( '[calendar_date]', '[calendar_date '), $new_content);
                    $this_content = apply_filters('frm_display_entry_content', $new_content, $entry, $shortcodes, $view, 'all', '', array( 'event_date' => $current_entry_date));

                    $used_entries[$entry->id] = $this_content;
                    $this_content = FrmProContent::replace_calendar_date_shortcode($this_content, $current_entry_date);
                    echo $this_content;

                    unset($this_content);
                }
            }
        }
    }
    ?></div>
</td>
<?php
	if ( $pos == $week_ends ) {
        $end_tr = true;
        echo "</tr>\n";
    }
}

$pos++;
if ( $pos == 7 ) {
    $pos = 0;
}
if ( $pos != $week_begins ) {
	if ( $pos > $week_begins ) {
		$week_begins = $week_begins + 7;
	}
	for ( $e = $pos; $e < $week_begins; $e++ ) {
		$day_class = '';
		if ( $e == 6 || $e == 7 ) {
			$day_class = ' class="frmcal-week-end"';
		}
		echo "<td" . $day_class . "></td>\n";
	}
}

if ( ! $end_tr ) {
    echo '</tr>';
}
