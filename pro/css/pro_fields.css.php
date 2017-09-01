.js .frm_logic_form:not(.frm_no_hide) {
    display:none;
}

.with_frm_style select.frm_time_select{
	white-space:pre;
	display:inline;
}

.with_frm_style .frm_repeat_sec{
    margin-bottom:20px;
    margin-top:20px;
}

.with_frm_style .frm_repeat_inline{
	clear:both;
}

.frm_form_field .frm_repeat_sec .frm_add_form_row{
    opacity:0;
	display:none;
	*display:inline;
	display:inline\0/; /* For IE 8-9 */
	-moz-transition: opacity .15s ease-in-out;
	-webkit-transition: opacity .15s ease-in-out;
	transition: opacity .15s ease-in-out;
    pointer-events:none;
}

.frm_section_heading div.frm_repeat_sec:last-child .frm_add_form_row{
    opacity:100;
	display:inline;
    pointer-events:auto;
}

.frm_form_field .frm_repeat_grid .frm_form_field label.frm_primary_label{
    display:none !important;
}

.frm_form_field .frm_repeat_grid.frm_first_repeat .frm_form_field label.frm_primary_label{
    display:inherit !important;
}


#ui-datepicker-div{
    display:none;
    z-index:999999 !important;
}

.frm_form_fields div.rating-cancel{
    display:none !important;
}

.frm_form_fields div.rating-cancel,
.frm_form_fields div.star-rating{
    float:left;
    width:17px;
    height:17px;
	font-size:16px;
    line-height:normal;
    cursor:pointer;
    display:block;
    background:transparent;
    overflow:hidden;
	clear:none;
}

.frm_form_fields div.rating-cancel a:before{
    font:16px/1 'dashicons';
    content:'\f460';
    color:#CDCDCD;
}

.frm_form_fields div.star-rating:before,
.frm_form_fields div.star-rating a:before{
    font:16px/1 'dashicons';
    content:'\f154';
    color:#F0AD4E;
}

.frm_form_fields div.rating-cancel a,
.frm_form_fields div.star-rating a{
    display:block;
    width:16px;
    height:100%;
    border:0;
}

.frm_form_fields div.star-rating-on:before,
.frm_form_fields div.star-rating-on a:before{
    content:'\f155';
}

.frm_form_fields div.star-rating-hover:before,
.frm_form_fields div.star-rating-hover a:before{
    content:'\f155';
}

.frm_form_fields div.frm_half_star:before,
.frm_form_fields div.frm_half_star a:before{
    content:'\f459';
}

.frm_form_fields div.rating-cancel.star-rating-hover a:before{
    color:#B63E3F;
}

.frm_form_fields div.star-rating-readonly,
.frm_form_fields div.star-rating-readonly a{
    cursor:default !important;
}

.frm_form_fields div.star-rating{
    overflow:hidden!important;
}

.frm_pagination_cont ul.frm_pagination{
    display:inline-block;
    list-style:none;
    margin-left:0 !important;
}

.frm_pagination_cont ul.frm_pagination > li{
    display:inline;
    list-style:none;
    margin:2px;
    background-image:none;
}

ul.frm_pagination > li.active a{
	text-decoration:none;
}

.frm_pagination_cont ul.frm_pagination > li:first-child{
    margin-left:0;
}

.archive-pagination.frm_pagination_cont ul.frm_pagination > li{
    margin:0;
}

/* Calendar Styling */
.frmcal{
    padding-top:30px;
}

.frmcal-title{
    font-size:116%;
}

.frmcal table.frmcal-calendar{
    border-collapse:collapse;
    margin-top:20px;
    color:<?php echo esc_html( $defaults['text_color'] ) ?>;
}

.frmcal table.frmcal-calendar,
.frmcal table.frmcal-calendar tbody tr td{
    border:1px solid <?php echo esc_html( $defaults['border_color'] ) ?>;
}

.frmcal table.frmcal-calendar,
.frmcal,
.frmcal-header{
    width:100%;
}

.frmcal-header{
    text-align:center;
}

.frmcal-prev{
    margin-right:10px;
}

.frmcal-prev,
.frmcal-dropdown{
    float:left;
}

.frmcal-dropdown{
    margin-left:5px;
}

.frmcal-next{
    float:right;
}

.frmcal table.frmcal-calendar thead tr th{
    text-align:center;
    padding:2px 4px;
}

.frmcal table.frmcal-calendar tbody tr td{
    height:110px;
    width:14.28%;
    vertical-align:top;
    padding:0 !important;
    color:<?php echo esc_attr( $defaults['text_color'] ) ?>;
    font-size:12px;
}

table.frmcal-calendar .frmcal_date{
    background-color:<?php echo esc_html( empty( $defaults['bg_color'] ) ? 'transparent' : $defaults['bg_color'] ); ?>;
    padding:0 5px;
    text-align:right;
    -moz-box-shadow:0 2px 5px <?php echo esc_html( $defaults['border_color'] ) ?>;
    -webkit-box-shadow:0 2px 5px <?php echo esc_html( $defaults['border_color'] ) ?>;
    box-shadow:0 2px 5px <?php echo esc_html( $defaults['border_color'] ) ?>;
}

table.frmcal-calendar .frmcal-today .frmcal_date{
    background-color:<?php echo esc_html( $defaults['bg_color_active'] ) ?>;
    padding:0 5px;
    text-align:right;
    -moz-box-shadow:0 2px 5px #<?php echo esc_html( $defaults['border_color_active'] ) ?>;
    -webkit-box-shadow:0 2px 5px #<?php echo esc_html( $defaults['border_color_active'] ) ?>;
    box-shadow:0 2px 5px #<?php echo esc_html( $defaults['border_color_active'] ) ?>;
}

.frmcal_day_name,
.frmcal_num{
    display:inline;
}

.frmcal-content{
    padding:2px 4px;
}
/* End Calendar Styling */