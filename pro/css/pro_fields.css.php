.js .frm_logic_form:not(.frm_no_hide) {
    display:none;
}

.with_frm_style .frm_conf_field.frm_half label.frm_conf_label {
    overflow: hidden;
    white-space: nowrap;
}

.with_frm_style .frm_time_wrap{
	white-space:nowrap;
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

.frm_invisible_section .frm_form_field,
.frm_invisible_section{
	display:none !important;
	visibility:hidden !important;
	height:0;
	margin:0;
}

.frm_form_field .frm_repeat_sec .frm_add_form_row,
.frm_section_heading div.frm_repeat_sec:last-child .frm_add_form_row.frm_hide_add_button,
.frm_form_field div.frm_repeat_grid .frm_add_form_row.frm_hide_add_button,
.frm_form_field div.frm_repeat_inline .frm_add_form_row.frm_hide_add_button {
	-moz-transition: opacity .15s ease-in-out;
	-webkit-transition: opacity .15s ease-in-out;
	transition: opacity .15s ease-in-out;
	pointer-events: none;
}

.frm_form_field .frm_repeat_sec .frm_add_form_row,
.frm_section_heading div.frm_repeat_sec:last-child .frm_add_form_row.frm_hide_add_button {
	display: none;
}

.frm_form_field div.frm_repeat_grid .frm_add_form_row.frm_hide_add_button,
.frm_form_field div.frm_repeat_inline .frm_add_form_row.frm_hide_add_button {
	visibility: hidden;
}

.frm_form_field div.frm_repeat_grid .frm_add_form_row,
.frm_form_field div.frm_repeat_inline .frm_add_form_row,
.frm_section_heading div.frm_repeat_sec:last-child .frm_add_form_row {
	display: inline-block;
	visibility: visible;
	pointer-events: auto;
}

.frm_repeat_inline .frm_repeat_buttons a.frm_icon_font{
	vertical-align: sub;
}

.frm_repeat_inline .frm_repeat_buttons a.frm_icon_font:before{
    vertical-align: text-top;
}

.frm_repeat_grid .frm_button,
.frm_repeat_inline .frm_button,
.frm_repeat_sec .frm_button{
	display: inline-block;
	line-height:1.3;
}

.frm_repeat_sec .frm_button .frm_icon_font:before,
.frm_repeat_grid .frm_button .frm_icon_font:before,
.frm_repeat_inline .frm_button .frm_icon_font:before{
    line-height:1.3;
}

.frm_form_field .frm_repeat_grid .frm_form_field label.frm_primary_label{
    display:none !important;
}

.frm_form_field .frm_repeat_grid.frm_first_repeat .frm_form_field label.frm_primary_label{
    display:inherit !important;
}

/* Datepicker */

#ui-datepicker-div{
    display:none;
    z-index:999999 !important;
}

<?php $use_default_date = ( empty( $defaults['theme_css'] ) || 'ui-lightness' === $defaults['theme_css'] ); ?>

.ui-datepicker .ui-datepicker-title select.ui-datepicker-month,
.ui-datepicker .ui-datepicker-title select.ui-datepicker-year {
    width: <?php echo esc_html( $use_default_date ? '33' : '45' ); ?>%;
	background-color:#fff;
	float:none;
}

.ui-datepicker select.ui-datepicker-month{
	margin-right: 3px;
}

.ui-datepicker-month, .ui-datepicker-year{
	max-width:100%;
	max-height:2em;
	padding:6px 10px;
	-webkit-box-sizing:border-box;
	-moz-box-sizing:border-box;
	box-sizing:border-box;
}

<?php if ( $use_default_date ) { ?>
.ui-datepicker .ui-widget-header,
.ui-datepicker .ui-datepicker-header {
    background: <?php echo esc_html( $defaults['date_head_bg_color'] ); ?> !important;
	color: <?php echo esc_html( $defaults['date_head_color'] ); ?> !important;
}

.ui-datepicker td.ui-datepicker-today{
	background: rgba(<?php echo esc_html( FrmStylesHelper::hex2rgb( $defaults['date_band_color'] ) . ',0.15)' ); ?> !important;
}

.ui-datepicker td.ui-datepicker-current-day,
.ui-datepicker td .ui-state-hover,
.ui-datepicker thead {
    background: <?php echo esc_html( $defaults['date_band_color'] ); ?> !important;
	color: <?php echo esc_html( $defaults['date_head_color'] ); ?> !important;
}

.ui-datepicker td.ui-datepicker-current-day .ui-state-default{
	color: <?php echo esc_html( $defaults['date_head_color'] ); ?> !important;
}
<?php } ?>

/* Radio Scale */

.with_frm_style .frm_scale{
	margin-right:10px;
	text-align:center;
	float:left;
}

.with_frm_style .frm_scale input{
	display:block;
	margin:0;
}

/* Star ratings */

.frm-star-group input {
	display: none !important;
}

.frm-star-group .star-rating,
.frm-star-group input + label {
	float:left;
	width:20px;
	height:20px;
	font-size:20px;
	line-height:1.4em;
	cursor:pointer;
	display:block;
	background:transparent;
	overflow:hidden !important;
	clear:none;
	font-style:normal;
}

.frm-star-group input + label:before,
.frm-star-group .star-rating:before{
	font-family:'s11-fp';
	content:'\e9d7';
	color:#F0AD4E;
	display: inline-block;
}

.frm-star-group input[type=radio]:checked + label:before,
.frm-star-group:not(.frm-star-hovered) input[type=radio]:checked + label:before{
	color:#F0AD4E;
}

.frm-star-group:not(.frm-star-hovered) input[type=radio]:checked + label:before,
.frm-star-group input + label:hover:before,
.frm-star-group:hover input + label:hover:before,
.frm-star-group .star-rating-on:before,
.frm-star-group .star-rating-hover:before{
	content:'\e9d9';
	color:#F0AD4E;
}

.frm-star-group .frm_half_star:before{
	content:'\e9d8';
}

.frm-star-group .star-rating-readonly{
	cursor:default !important;
}

/* Other input */
.with_frm_style .frm_other_input.frm_other_full{
	margin-top:10px;
}

.frm_left_container .frm_other_input{
	grid-column:2;
}

.frm_inline_container.frm_other_container .frm_other_input,
.frm_left_container.frm_other_container .frm_other_input{
	margin-left:5px;
}

.frm_right_container.frm_other_container .frm_other_input{
	margin-right:5px;
}

.frm_inline_container.frm_other_container select ~ .frm_other_input,
.frm_right_container.frm_other_container select ~ .frm_other_input,
.frm_left_container.frm_other_container select ~ .frm_other_input{
	margin:0;
}

/* Pagination */
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
    -moz-box-shadow:0 2px 5px <?php echo esc_html( $defaults['border_color_active'] ) ?>;
    -webkit-box-shadow:0 2px 5px <?php echo esc_html( $defaults['border_color_active'] ) ?>;
    box-shadow:0 2px 5px <?php echo esc_html( $defaults['border_color_active'] ) ?>;
}

.frmcal_day_name,
.frmcal_num{
    display:inline;
}

.frmcal-content{
    padding:2px 4px;
}
/* End Calendar Styling */

/* Start Toggle Styling */
.frm_switch_opt {
	padding:0 8px 0 0;
	white-space:normal;
	display:inline;
	vertical-align: middle;
}

.frm_on_label{
	color: #008ec2;
	padding:0 0 0 8px;
}

.frm_switch {
	position: relative;
	display: inline-block;
	width: 40px;
	height: 25px;
	vertical-align: middle;
}

.frm_switch input {
	display:none !important;
}

.frm_slider {
	position: absolute;
	cursor: pointer;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background-color: #ccc;
	transition: .4s;
	border-radius: 30px;
}

.frm_slider:before {
	border-radius: 50%;
	position: absolute;
	content: "";
	height: 23px;
	width: 23px;
	left: 1px;
	bottom: 1px;
	background-color: white;
	transition: .4s;
	box-shadow:0 2px 5px #999;
}

input:checked + .frm_slider {
	background-color: #008ec2;
}

input:focus + .frm_slider {
	box-shadow: 0 0 1px #008ec2;
}

input:checked + .frm_slider:before {
	transform: translateX(15px);
}

/* Range slider */

<?php
$bg_color = '#ccc' . $important;
$thumb_color = '#008ec2' . $important;
$text_color = '#ffffff' . $important;
?>
.with_frm_style .frm_range_value{
	display:inline-block;
}

.with_frm_style input[type=range] {
	-webkit-appearance: none;
	box-shadow:none !important;
	border:none !important;
	cursor: pointer;
	padding:0 <?php echo esc_html( $important ) ?>;
	background:transparent !important;
	display: block;
	width: 100%;
	margin: 7px 0 15px;
	font-size:14px;
}

.with_frm_style input[type=range]:active,
.with_frm_style input[type=range]:focus {
	outline: none;
	box-shadow:none !important;
	background:transparent !important;
}

.with_frm_style .frm_range_container{
	text-align:center;
}

.with_frm_style input[type=range]::-webkit-slider-runnable-track {
	<?php
	echo $border = 'border-radius: 0;
	border: none;
	height: 4px;
	background-color: ' . esc_html( $bg_color ) . ';';
	echo $track = 'animate: 0.2s;';
	?>
}
.with_frm_style input[type=range]::-moz-range-track {
	<?php echo $border . $track ?>
	border-color: transparent;
	border-width: 39px 0;
	color: transparent;
}
.with_frm_style input[type=range]::-ms-fill-lower {
	<?php echo $border . $track ?>
}
.with_frm_style input[type=range]::-ms-fill-upper {
	<?php echo $border . $track ?>
}

.with_frm_style input[type=range]::-webkit-slider-thumb {
	-webkit-appearance: none;
	-webkit-border-radius: 20px;
	<?php
	echo $thumb_size = 'height: 2em;
	width: 2em;';
	echo $thumb = 'border-radius: 20px;
	border: 1px solid rgba(' . esc_html( FrmStylesHelper::hex2rgb( '#008ec2' ) ) . ',0.6);
	color:' . esc_html( $text_color ) . ';
	background-color: ' . esc_html( $thumb_color ) . ';
	cursor: pointer;';
	?>
	margin-top: -.9em;
}

.with_frm_style input[type=range]::-moz-range-thumb {
	<?php echo $thumb_size . $thumb; ?>
	-moz-border-radius: 20px;
}

.with_frm_style input[type=range]::-ms-thumb {
	<?php echo $thumb_size . $thumb; ?>
}

/**
 * Password strength meter CSS
 */

@media screen and (max-width: 768px) {
    .frm-pass-req, .frm-pass-verified {
        width: 50% !important;
        white-space: nowrap;
    }
}

.frm-pass-req, .frm-pass-verified {
    float: left;
    width: 20%;
    line-height: 20px;
    font-size: 12px;
    padding-top: 4px;
    min-width: 175px;
}

.frm-pass-req:before, .frm-pass-verified:before {
    padding-right: 4px;
    font-size: 12px !important;
    vertical-align: middle !important;
}

span.frm-pass-verified::before {
    content: '\e606';
}

span.frm-pass-req::before {
    content: '\e608';
}

div.frm-password-strength {
    width: 100%;
    float: left;
}
