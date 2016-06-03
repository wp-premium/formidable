<?php if ( ! empty($atts['style']) ) {
?><div class="<?php echo esc_attr( $atts['style'] ) ?>"><?php
}
?><form action="<?php echo esc_url( $action_link ) ?>" id="frm_search_form" method="get" class="searchform"><?php
    if ( preg_match("/[?]/", $action_link) ) {
?><input type="hidden" name="p" value="<?php echo esc_attr( $atts['post_id'] ) ?>" /><?php
    }
    if ( ! empty( $atts['views'] ) ) {
        ?><input type="hidden" name="frm_search_views" value="<?php echo esc_attr( $atts['views'] ) ?>" /><?php
    }
?><input type="search" name="frm_search" id="frm_search" class="s" value="<?php echo esc_attr( FrmAppHelper::get_param( 'frm_search', '', 'get', 'sanitize_text_field' ) ); ?>" /><input type="submit" value="<?php echo esc_attr( $atts['label'] ) ?>" class="searchsubmit" /></form><?php
if ( ! empty($atts['style']) ) {
?></div><?php
}