<?php
if ( $page_count <= 1 ) {
    return; // Only show the pager bar if there is more than 1 page
}
?>
<div class="<?php echo esc_attr( apply_filters( 'frm_pagination_class', 'frm_pagination_cont' ) ); ?>">
<ul class="<?php echo esc_attr( apply_filters( 'frm_ul_pagination_class', 'frm_pagination' ) ); ?>">
<?php
    if ( ! is_numeric($current_page) ) {
        $current_page = FrmAppHelper::get_param( $page_param, '1', 'get', 'absint' );
    }

	$page_params = isset( $page_params ) ? $page_params : '';
	$s = FrmAppHelper::get_param( 'frm_search', false );
	if ( $s ) {
		$page_params .= '&frm_search=' . urlencode( $s );
	}

    if ( $current_page > 1 ) { // Only show the prev page button if the current page is not the first page ?>
<li class="<?php echo esc_attr( apply_filters( 'frm_prev_page_class', '' ) ) ?>"><a href="<?php echo esc_url( add_query_arg( array( $page_param => $current_page - 1 ) ) . $page_params ) ?>" class="prev"><?php echo apply_filters('frm_prev_page_label', '&#171;') ?></a></li> <?php
    }

    // First page is always displayed
?>
<li class="<?php echo ( $current_page == 1 ) ? 'active' : '' ?>"><a href="<?php echo esc_url( add_query_arg( array( $page_param => 1 ) ) . $page_params ) ?>">1</a></li><?php

    // If the current page is more than 2 spaces away from the first page then we put some dots in here
    if ( $current_page >= 5 ) { ?>
<li class="<?php echo esc_attr( apply_filters( 'frm_page_dots_class', 'dots disabled' ) ) ?>">...</li> <?php
    }

    // display the current page icon and the 2 pages beneath and above it
    $low_page = ($current_page >= 5) ? ($current_page-2) : 2;
    $high_page = (($current_page + 2) < ($page_count-1)) ? ($current_page+2) : ($page_count-1);
    for ( $i = $low_page; $i <= $high_page; $i++ ) { ?>
<li class="<?php echo esc_attr( $current_page == $i ? 'active' : '' ) ?>"><a href="<?php echo esc_url( add_query_arg( array( $page_param => $i ) ) . $page_params ); ?>"><?php echo absint( $i ); ?></a></li> <?php
    }
    unset($low_page, $high_page, $i);

    // If the current page is more than 2 away from the last page then show ellipsis
    if ( $current_page < ($page_count - 3) ) { ?>
<li class="<?php echo esc_attr( apply_filters( 'frm_page_dots_class', 'dots disabled' ) ) ?>">...</li> <?php
    }

    // Display the last page icon
?>
<li class="<?php echo esc_attr( $current_page == $page_count ? 'active' : '' ) ?>"><a href="<?php echo esc_url( add_query_arg( array( $page_param => $page_count ) ) . $page_params ); ?>"><?php echo absint( $page_count ); ?></a></li><?php

    // Display the next page icon if there is a next page
	if ( $current_page < $page_count ) { ?>
<li class="<?php echo esc_attr( apply_filters( 'frm_next_page_class', '' ) ) ?>"><a href="<?php echo esc_url( add_query_arg( array( $page_param => $current_page + 1 ) ) . $page_params ); ?>" class="next"><?php echo apply_filters('frm_next_page_label', '&#187;') ?></a></li><?php
    } ?>
</ul>
</div>
