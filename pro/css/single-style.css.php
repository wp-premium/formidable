.<?php echo esc_html( $settings['style_class'] ); ?> .frm_button .frm_icon_font:before{
    font-size:<?php echo esc_html( $settings['submit_font_size'] . $important ); ?>;
}

/* Dropzone */
.<?php echo esc_html( $settings['style_class'] ); ?> .frm_dropzone{
	border-color: <?php echo esc_html( $settings['border_color'] . $important ); ?>;
	border-radius:<?php echo esc_html( $settings['border_radius'] . $important ); ?>;
	color: <?php echo esc_html( $settings['text_color'] . $important ); ?>;
	background-color:<?php echo esc_html( $settings['bg_color'] . $important ); ?>;
}

.<?php echo esc_html( $settings['style_class'] ); ?> .frm_dropzone .frm_upload_icon:before,
.<?php echo esc_html( $settings['style_class'] ); ?> .frm_dropzone .dz-remove{
	color: <?php echo esc_html( $settings['text_color'] . $important ); ?>;
}

.<?php echo esc_html( $settings['style_class'] ); ?> .frm_blank_field .frm_dropzone{
	border-color:<?php echo esc_html( $settings['border_color_error'] . $important ); ?>;
	color:<?php echo esc_html( $settings['text_color_error'] . $important ); ?>;
	background-color:<?php echo esc_html( $settings['bg_color_error'] . $important ); ?>;
}

/* File Upload */

.<?php echo esc_html( $settings['style_class'] ); ?> input[type=file]::-webkit-file-upload-button{
	color:<?php echo esc_html( $settings['text_color'] . $important ); ?>;
	background-color:<?php echo esc_html( $settings['bg_color'] . $important ); ?>;
	padding:<?php echo esc_html( $settings['field_pad'] . $important ); ?>;
	border-radius:<?php echo esc_html( $settings['border_radius'] . $important ); ?>;
	border-color: <?php echo esc_html( $settings['border_color'] . $important ); ?>;
	border-width:<?php echo esc_html( $settings['field_border_width'] . $important ); ?>;
	border-style:<?php echo esc_html( $settings['field_border_style'] . $important ); ?>;
}

/* Start Chosen */
.<?php echo esc_html( $settings['style_class'] ); ?> .chosen-container{
	font-size:<?php echo esc_html( $settings['field_font_size'] . $important ) ?>;
}

.<?php echo esc_html( $settings['style_class'] ) ?> .chosen-container .chosen-results li,
.<?php echo esc_html( $settings['style_class'] ) ?> .chosen-container .chosen-results li span{
	color:<?php echo esc_html( $settings['text_color'] . $important ) ?>;
}

.<?php echo esc_html( $settings['style_class'] ) ?> .chosen-container-single .chosen-single{
	padding-top:0 <?php esc_html( $important ); ?>;
<?php if ( $settings['field_height'] != 'auto' && $settings['field_height'] != '' ) { ?>
	height:<?php echo esc_html( $settings['field_height'] . $important ); ?>;
	line-height:<?php echo esc_html( $settings['field_height'] . $important ) ?>;
<?php } ?>
}

<?php if ( is_numeric( $top_margin ) && $pad_unit === 'px' ) { ?>
.<?php echo esc_html( $settings['style_class'] ) ?> .chosen-container-single .chosen-single abbr{
    top:<?php echo esc_attr( 6 + (int) $top_margin ); ?>px <?php esc_html( $important ); ?>;
}

.<?php echo esc_html( $settings['style_class'] ) ?> .chosen-container-single .chosen-single div{
	top:<?php echo esc_html( $top_margin . $pad_unit . $important ) ?>;
}
<?php } ?>

.<?php echo esc_html( $settings['style_class'] ) ?> .chosen-container-single .chosen-search input[type="text"]{
	height:<?php echo esc_html( ( $settings['field_height'] == 'auto' || $settings['field_height'] == '' ) ? 'auto' : $settings['field_height'] ) ?>;
}

.<?php echo esc_html( $settings['style_class'] ) ?> .chosen-container-multi .chosen-choices li.search-field input[type="text"]{
	height:15px<?php echo esc_html( $important ) ?>;
}
/* End Chosen */

<?php if ( isset( $settings['progress_color'] ) ) { ?>
/* Progress Bars */
.<?php echo esc_html( $settings['style_class'] ) ?> .frm_page_bar input,
.<?php echo esc_html( $settings['style_class'] ) ?> .frm_page_bar input:disabled{
	color: <?php echo esc_html( $settings['progress_color'] . $important ) ?>;
	background-color: <?php echo esc_html( $settings['progress_bg_color'] . $important ) ?>;
	border-color: <?php echo esc_html( $settings['progress_border_color'] . $important ) ?>;
	border-width: <?php echo esc_html( $settings['progress_border_size'] . $important ) ?>;
}

.<?php echo esc_html( $settings['style_class'] ) ?> .frm_progress_line input.frm_page_back{
	background-color: <?php echo esc_html( $settings['progress_active_bg_color'] . $important ) ?>;
}

.<?php echo esc_html( $settings['style_class'] ) ?> .frm_page_bar .frm_current_page input[type="button"]{
	background-color: <?php echo esc_html( $settings['progress_bg_color'] . $important ) ?>;
	border-color: <?php echo esc_html( $settings['progress_border_color'] . $important ) ?>;
	opacity:1<?php echo esc_html( $important ) ?>;
}

.<?php echo esc_html( $settings['style_class'] ) ?> .frm_current_page .frm_rootline_title{
	color: <?php echo esc_html( $settings['progress_active_bg_color'] . $important ) ?>;
}

.<?php echo esc_html( $settings['style_class'] ) ?> .frm_rootline_title,
.<?php echo esc_html( $settings['style_class'] ) ?> .frm_pages_complete,
.<?php echo esc_html( $settings['style_class'] ) ?> .frm_percent_complete{
	color: <?php echo esc_html( $settings['description_color'] . $important ) ?>;
}

.<?php echo esc_html( $settings['style_class'] ) ?> .frm_progress_line input,
.<?php echo esc_html( $settings['style_class'] ) ?> .frm_progress_line input:disabled {
	border-color: <?php echo esc_html( $settings['progress_border_color'] . $important ) ?>;
}

.<?php echo esc_html( $settings['style_class'] ) ?> .frm_progress_line.frm_show_lines input {
	border-left-color: <?php echo esc_html( $settings['progress_color'] . $important ) ?>;
	border-right-color: <?php echo esc_html( $settings['progress_color'] . $important ) ?>;
	border-left-width: 1px <?php echo esc_html( $important ) ?>;
	border-right-width: 1px <?php echo esc_html( $important ) ?>;
}

.<?php echo esc_html( $settings['style_class'] ) ?> .frm_progress_line li:first-of-type input {
	border-left-color: <?php echo esc_html( $settings['progress_active_bg_color'] . $important ) ?>;
}

.<?php echo esc_html( $settings['style_class'] ) ?> .frm_progress_line li:last-of-type input {
	border-right-color: <?php echo esc_html( $settings['progress_active_bg_color'] . $important ) ?>;
}

.<?php echo esc_html( $settings['style_class'] ) ?> .frm_progress_line li:last-of-type input.frm_page_skip {
	border-right-color: <?php echo esc_html( $settings['progress_border_color'] . $important ) ?>;
}

.<?php echo esc_html( $settings['style_class'] ) ?> .frm_progress_line .frm_current_page input[type="button"] {
	border-left-color: <?php echo esc_html( $settings['progress_border_color'] . $important ) ?>;
}

.<?php echo esc_html( $settings['style_class'] ) ?> .frm_progress_line.frm_show_lines .frm_current_page input[type="button"] {
	border-right-color: <?php echo esc_html( $settings['progress_color'] . $important ) ?>;
}

.<?php echo esc_html( $settings['style_class'] ) ?> .frm_progress_line input.frm_page_back {
	border-color: <?php echo esc_html( $settings['progress_active_bg_color'] . $important ) ?>;
}

.<?php echo esc_html( $settings['style_class'] ) ?> .frm_progress_line.frm_show_lines input.frm_page_back{
	border-left-color: <?php echo esc_html( $settings['progress_active_bg_color'] . $important ) ?>;
	border-right-color: <?php echo esc_html( $settings['progress_color'] . $important ) ?>;
}

.<?php echo esc_html( $settings['style_class'] ) ?> .frm_rootline.frm_show_lines:before {
	border-color: <?php echo esc_html( $settings['progress_border_color'] . $important ) ?>;
	border-top-width: <?php echo esc_html( $settings['progress_border_size'] . $important ) ?>;
	top: <?php echo esc_html( absint( $settings['progress_size'] ) / 2 ) ?>px;
}

.<?php echo esc_html( $settings['style_class'] ) ?> .frm_rootline input,
.<?php echo esc_html( $settings['style_class'] ) ?> .frm_rootline input:hover {
	width: <?php echo esc_html( $settings['progress_size'] . $important ) ?>;
	height: <?php echo esc_html( $settings['progress_size'] . $important ) ?>;
	border-radius: <?php echo esc_html( $settings['progress_size'] . $important ) ?>;
	padding: 0<?php echo esc_html( $important ) ?>;
}

.<?php echo esc_html( $settings['style_class'] ) ?> .frm_rootline input:focus {
	border-color: <?php echo esc_html( $settings['progress_active_bg_color'] . $important ) ?>;
}

.<?php echo esc_html( $settings['style_class'] ) ?> .frm_rootline .frm_current_page input[type="button"] {
	border-color: <?php echo esc_html( FrmStylesHelper::adjust_brightness( $settings['progress_active_bg_color'], -20 ) . $important ); ?>;
	background-color: <?php echo esc_html( $settings['progress_active_bg_color'] . $important ) ?>;
	color: <?php echo esc_html( $settings['progress_active_color'] . $important ) ?>;
}

.<?php echo esc_html( $settings['style_class'] ) ?> .frm_progress_line input,
.<?php echo esc_html( $settings['style_class'] ) ?> .frm_progress_line input:disabled,
.<?php echo esc_html( $settings['style_class'] ) ?> .frm_progress_line .frm_current_page input[type="button"],
.<?php echo esc_html( $settings['style_class'] ) ?> .frm_rootline.frm_no_numbers input,
.<?php echo esc_html( $settings['style_class'] ) ?> .frm_rootline.frm_no_numbers .frm_current_page input[type="button"] {
	color: transparent !important;
}
<?php } ?>

/* Start Range slider */

.<?php echo esc_html( $settings['style_class'] ) ?> .frm_range_value{
	font-size:<?php echo esc_html( $settings['slider_font_size'] ) ?>;
}

.<?php echo esc_html( $settings['style_class'] ) ?> .form-field input[type=range],
.<?php echo esc_html( $settings['style_class'] ) ?> .form-field input[type=range]:focus {
	padding:0 <?php echo esc_html( $important ) ?>;
	background:transparent !important;
}

.<?php echo esc_html( $settings['style_class'] ) ?> input[type=range]::-webkit-slider-runnable-track {
	background-color:<?php echo esc_html( $settings['slider_bar_color'] . $important ) ?>;
	border-radius:<?php echo esc_html( $settings['border_radius'] . $important ) ?>;
}

.<?php echo esc_html( $settings['style_class'] ) ?> input[type=range]::-webkit-slider-thumb {
	<?php
	$thumb_color = $settings['slider_color'] . $important;
	echo $thumb = 'border: 1px solid rgba(' . esc_html( FrmStylesHelper::hex2rgb( $thumb_color ) ) . ',0.6);
	color:' . esc_html( $settings['progress_active_color'] . $important ) . ';
	background-color: ' . esc_html( $thumb_color ) . ';';
	?>
}

.<?php echo esc_html( $settings['style_class'] ) ?> input[type=range]::-moz-range-thumb {
	<?php echo $thumb; ?>
}

.<?php echo esc_html( $settings['style_class'] ) ?> input[type=range]::-ms-thumb {
	<?php echo $thumb; ?>
}

/* End Range Slider */

/* Start Toggle */
.<?php echo esc_html( $settings['style_class'] ) ?> .frm_switch_opt{
	font-size:<?php echo esc_html( $settings['toggle_font_size'] . $important ) ?>;
	font-weight:<?php echo esc_html( $settings['check_weight'] . $important ) ?>;
}

.<?php echo esc_html( $settings['style_class'] ) ?> .frm_on_label{
	color:<?php echo esc_html( $settings['toggle_on_color'] . $important ) ?>;
}

.<?php echo esc_html( $settings['style_class'] ) ?> .frm_off_label{
	color:<?php echo esc_html( $settings['check_label_color'] . $important ) ?>;
}

.<?php echo esc_html( $settings['style_class'] ) ?> .frm_slider {
	background-color:<?php echo esc_html( $settings['toggle_off_color'] . $important ) ?>;
}

.<?php echo esc_html( $settings['style_class'] ) ?> input:checked + .frm_slider {
	background-color:<?php echo esc_html( $settings['toggle_on_color'] . $important ) ?>;
}

/* End Toggle */

/* Start other fields */
.<?php echo esc_html( $settings['style_class'] ); ?> input.frm_other_input:not(.frm_other_full){
    width:auto <?php echo esc_html( $important ) ?>;
}

.<?php echo esc_html( $settings['style_class'] ); ?> .frm_checkbox input.frm_other_input:not(.frm_other_full),
.<?php echo esc_html( $settings['style_class'] ); ?> .frm_radio input.frm_other_input:not(.frm_other_full){
	margin-left:5px;
}

.<?php echo esc_html( $settings['style_class'] ); ?> .horizontal_radio input.frm_other_input:not(.frm_other_full):not(.frm_pos_none) {
	display:inline-block<?php echo esc_html( $important ); ?>;
}

.<?php echo esc_html( $settings['style_class'] ); ?> .frm_full input.frm_other_input:not(.frm_other_full){
    margin-left:0 <?php echo esc_html( $important ) ?>;
    margin-top:8px;
}
/* End other */
