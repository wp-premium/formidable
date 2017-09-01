
.frm_rootline_group{
	margin: 20px 0 30px;
}
			
ul.frm_page_bar{
	list-style-type: none;
	margin: 0 !important;
	padding: 0;
	width: 100%;
	float: left;
	display: table;
	display: -webkit-flex;
	display: flex;
	box-sizing: border-box;
	-moz-box-sizing: border-box;
	-webkit-box-sizing: border-box;
}

ul.frm_page_bar li{
	display: inline-block;
	-ms-flex: 1;
	flex: 1;
}

.frm_forms .frm_page_bar input,
.frm_forms .frm_page_bar input:disabled{
	transition: background-color 0.1s ease;
	color: <?php echo esc_html( $defaults['progress_color'] ) ?>;
	background-color: <?php echo esc_html( $defaults['progress_bg_color'] ) ?>;
	font-size: 18px;
	border-width: <?php echo esc_html( $defaults['progress_border_size'] ) ?>;
	border-style: solid;
	border-color: <?php echo esc_html( $defaults['progress_border_color'] ) ?>;
}

.frm_forms .frm_page_bar input:focus{
	outline: none;
}

.frm_forms .frm_progress_line input.frm_page_back{
	background-color: <?php echo esc_html( $defaults['progress_active_bg_color'] ) ?>;
}

.frm_forms .frm_page_bar .frm_current_page input[type="button"]{
	background-color: <?php echo esc_html( $defaults['progress_bg_color'] ) ?>;
	border-color: <?php echo esc_html( $defaults['progress_border_color'] ) ?>;
}
	
.frm_rootline_single{
	text-align: center;
	margin: 0;
	padding: 0;
}

.frm_current_page .frm_rootline_title{
	color: <?php echo esc_html( $defaults['progress_active_bg_color'] ) ?>;
}

.frm_rootline_title,
.frm_pages_complete
.frm_percent_complete{
	font-size:14px;
	padding:4px;
}

.frm_pages_complete {
	float: right;
	margin-right:13px;
}

.frm_percent_complete {
	float: left;
	margin-left:13px;
}

.frm_forms .frm_progress_line input,
.frm_forms .frm_progress_line input:disabled {
	width: 100%;
	border: none;
	border-top: 1px solid <?php echo esc_html( $defaults['progress_border_color'] ) ?>;
	border-bottom: 1px solid <?php echo esc_html( $defaults['progress_border_color'] ) ?>;
	box-shadow: inset 0px 10px 20px -15px #aaa;
	margin: 5px 0;
	padding: 6px;
	border-radius:0;
}

.frm_forms .frm_progress_line.frm_show_lines input {
	border-left: 1px solid <?php echo esc_html( $defaults['progress_color'] ) ?>;
	border-right: 1px solid <?php echo esc_html( $defaults['progress_color'] ) ?>;
}

.frm_forms .frm_progress_line li:first-of-type input {
	border-top-left-radius: 15px;
	border-bottom-left-radius: 15px;
	border-left: 1px solid <?php echo esc_html( $defaults['progress_active_bg_color'] ) ?>;
}

.frm_forms .frm_progress_line li:last-of-type input {
	border-top-right-radius: 15px;
	border-bottom-right-radius: 15px;
	border-right: 1px solid <?php echo esc_html( $defaults['progress_active_bg_color'] ) ?>;
}

.frm_forms .frm_progress_line li:last-of-type input.frm_page_skip {
	border-right: 1px solid <?php echo esc_html( $defaults['progress_border_color'] ) ?>;
}

.frm_forms .frm_progress_line .frm_current_page input[type="button"] {
	border-left: 1px solid <?php echo esc_html( $defaults['progress_border_color'] ) ?>;
}

.frm_forms .frm_progress_line.frm_show_lines .frm_current_page input[type="button"] {
	border-right: 1px solid <?php echo esc_html( $defaults['progress_color'] ) ?>;
}

.frm_forms .frm_progress_line input.frm_page_back {
	border-color: <?php echo esc_html( $defaults['progress_active_bg_color'] ) ?>;
}

.frm_forms .frm_progress_line.frm_show_lines input.frm_page_back{
	border-left-color: <?php echo esc_html( $defaults['progress_active_bg_color'] ) ?>;
	border-right-color: <?php echo esc_html( $defaults['progress_color'] ) ?>;
}

.frm_rootline.frm_show_lines:before {
    border-top-width: <?php echo esc_html( $defaults['progress_border_size'] ) ?>;
	border-top-style: solid;
	border-top-color: <?php echo esc_html( $defaults['progress_border_color'] ) ?>;
    content: "";
    margin: 0 auto;
    position: absolute;
    top: <?php echo esc_html( absint( $defaults['progress_size'] ) / 2 ) ?>px;
    left: 0;
    right: 0;
    bottom: 0;
    width: 100%;
    z-index: -1;
}

.frm_rootline.frm_show_lines{
	position: relative;
    z-index: 1;
}

.frm_rootline.frm_show_lines span{
	display:block;
}

.frm_forms .frm_rootline input {
	width: <?php echo esc_html( $defaults['progress_size'] ) ?>;
	height: <?php echo esc_html( $defaults['progress_size'] ) ?>;
	border-radius: <?php echo esc_html( $defaults['progress_size'] ) ?>;
	padding:0;
}

.frm_forms .frm_rootline input:focus {
	border-color: <?php echo esc_html( $defaults['progress_active_bg_color'] ) ?>;
}

.frm_forms .frm_rootline .frm_current_page input[type="button"] {
	border-color: <?php echo esc_html( FrmStylesHelper::adjust_brightness( $defaults['progress_active_bg_color'], -20 ) ) ?>;
	background-color: <?php echo esc_html( $defaults['progress_active_bg_color'] ) ?>;
	color: <?php echo esc_html( $defaults['progress_active_color'] ) ?>;
}

.frm_forms .frm_progress_line input,
.frm_forms .frm_progress_line input:disabled,
.frm_forms .frm_progress_line .frm_current_page input[type="button"],
.frm_forms .frm_rootline.frm_no_numbers input,
.frm_forms .frm_rootline.frm_no_numbers .frm_current_page input[type="button"] {
	color: transparent !important;
}

@media only screen and (max-width: 700px) {
	.frm_progress span.frm_rootline_title,
	.frm_rootline.frm_rootline_10 span.frm_rootline_title,
	.frm_rootline.frm_rootline_9 span.frm_rootline_title,
	.frm_rootline.frm_rootline_8 span.frm_rootline_title,
	.frm_rootline.frm_rootline_7 span.frm_rootline_title,
	.frm_rootline.frm_rootline_6 span.frm_rootline_title,
	.frm_rootline.frm_rootline_5 span.frm_rootline_title{
		display:none;
	}
}

@media only screen and (max-width: 500px) {
	.frm_rootline.frm_rootline_4 span.frm_rootline_title,
	.frm_rootline.frm_rootline_3 span.frm_rootline_title{
		display:none;
	}
}
