/* Start Chosen */
.with_frm_style .chosen-container{
    font-size:<?php echo esc_html( $defaults['field_font_size'] ) ?>;
    position:relative;
    display:inline-block;
    zoom:1;
    vertical-align:middle;
	width:100% !important;
    -webkit-user-select:none;
    -moz-user-select:none;
    user-select:none;
    *display:inline;
}

.with_frm_style .chosen-container .chosen-drop{
    background:#fff;
    border:1px solid #aaa;
    border-top:0;
    position:absolute;
    top:100%;
    left:-9999px;
    box-shadow:0 4px 5px rgba(0,0,0,.15);
    z-index:1010;
    -webkit-box-sizing:border-box;
    -moz-box-sizing:border-box;
    box-sizing:border-box;
    width:100%;
}

.with_frm_style .chosen-container.chosen-with-drop .chosen-drop{
    left:0;
}

.with_frm_style .chosen-container a{
    cursor:pointer;
}

.with_frm_style .chosen-container-single .chosen-single{
    position:relative;
    display:block;
    overflow:hidden;
    padding:0 0 0 8px;
    height:25px;
    background:-webkit-gradient(linear, 50% 0%, 50% 100%, color-stop(20%, #ffffff), color-stop(50%, #f6f6f6), color-stop(52%, #eeeeee), color-stop(100%, #f4f4f4));
    background:-webkit-linear-gradient(top, #ffffff 20%, #f6f6f6 50%, #eeeeee 52%, #f4f4f4 100%);
    background:-moz-linear-gradient(top, #ffffff 20%, #f6f6f6 50%, #eeeeee 52%, #f4f4f4 100%);
    background:-o-linear-gradient(top, #ffffff 20%, #f6f6f6 50%, #eeeeee 52%, #f4f4f4 100%);
    background:linear-gradient(top, #ffffff 20%, #f6f6f6 50%, #eeeeee 52%, #f4f4f4 100%);
    background-clip:padding-box;
    box-shadow:0 0 3px white inset, 0 1px 1px rgba(0, 0, 0, 0.1);
    text-decoration:none;
    white-space:nowrap;
    line-height:24px;
}

.with_frm_style .chosen-container-single .chosen-single span{
    margin-right:26px;
    display:block;
    overflow:hidden;
    white-space:nowrap;
    text-overflow:ellipsis;
}

.with_frm_style .chosen-container-single .chosen-single-with-deselect span{
    margin-right:38px;
}

.with_frm_style .chosen-container-single .chosen-single abbr{
    display:block;
    position:absolute;
    right:26px;
    top:6px;
    width:12px;
    height:12px;
    font-size:1px;
    background:url('<?php echo FrmAppHelper::relative_plugin_url() ?>/pro/images/chosen-sprite.png') -42px 1px no-repeat;
}

.with_frm_style .chosen-container-single .chosen-single abbr:hover{
    background-position:-42px -10px;
}

.with_frm_style .chosen-container-single.chosen-disabled .chosen-single abbr:hover{
    background-position:-42px -10px;
}

.with_frm_style .chosen-container-single .chosen-single div{
    position:absolute;
    right:0;
    top:0;
    display:block;
    height:100%;
    width:18px;
}

.with_frm_style .chosen-container-single .chosen-single div b{
    background:url('<?php echo FrmAppHelper::relative_plugin_url() ?>/pro/images/chosen-sprite.png') no-repeat 0 2px;
    display:block;
    width:100%;
    height:100%;
}

.with_frm_style .chosen-container-single .chosen-search{
    padding:3px 4px;
    position:relative;
    margin:0;
    white-space:nowrap;
    z-index:1010;
}

.with_frm_style .chosen-container-single .chosen-search input[type="text"]{
    -webkit-box-sizing:border-box;
    -moz-box-sizing:border-box;
    box-sizing:border-box;
    width:100% !important;
    max-width:100% !important;
    height:auto;
    background:white url('<?php echo FrmAppHelper::relative_plugin_url() ?>/pro/images/chosen-sprite.png') no-repeat 100% -20px;
    background:url('<?php echo FrmAppHelper::relative_plugin_url() ?>/pro/images/chosen-sprite.png') no-repeat 100% -20px;
    font-size:1em;
    font-family:sans-serif;
    line-height:normal;
    border-radius:0;
}

.with_frm_style .chosen-container-single .chosen-drop{
    margin-top:-1px;
    border-radius:0 0 4px 4px;
    background-clip:padding-box;
}

.with_frm_style .chosen-container-single.chosen-container-single-nosearch .chosen-search{
    position:absolute;
    left:-9999px;
}

.with_frm_style .chosen-container .chosen-results{
    cursor:text;
    overflow-x:hidden;
    overflow-y:auto;
    position:relative;
    margin:0 4px 4px 0;
    padding:0 0 0 4px;
    max-height:240px;
    word-wrap:break-word;
    -webkit-overflow-scrolling:touch;
}

.with_frm_style .chosen-container .chosen-results li:before{
	background:none;
}

.with_frm_style .chosen-container .chosen-results li{
    display:none;
    margin:0;
    padding:5px 6px;
    list-style:none;
    line-height:15px;
    -webkit-touch-callout:none;
}

.with_frm_style .chosen-container .chosen-results li.active-result{
    display:list-item;
    cursor:pointer;
}

.with_frm_style .chosen-container .chosen-results li.disabled-result{
    display:list-item;
    color:#ccc;
    cursor:default;
}

.with_frm_style .chosen-container .chosen-results li.highlighted{
    background-color:#3875d7;
    color:#fff;
}

.with_frm_style .chosen-container .chosen-results li.no-results{
    display:list-item;
    background:#f4f4f4;
}

.with_frm_style .chosen-container .chosen-results li.group-result{
    display:list-item;
    font-weight:bold;
    cursor:default;
}

.with_frm_style .chosen-container .chosen-results li.group-option{
    padding-left:15px;
}

.with_frm_style .chosen-container .chosen-results li em{
    font-style:normal;
    text-decoration:underline;
}

.with_frm_style .chosen-container-multi .chosen-choices{
    position:relative;
    overflow:hidden;
    -webkit-box-sizing:border-box;
    -moz-box-sizing:border-box;
    box-sizing:border-box;
    margin:0;
    padding:0 5px;
    width:100%;
    height:auto !important;
    height:1%;
    cursor:text;
}

.with_frm_style .chosen-container-multi .chosen-choices li{
    float:left;
    list-style:none;
}

.with_frm_style .chosen-container-multi .chosen-choices li.search-field{
    margin:0;
    padding:0;
    white-space:nowrap;
}

.with_frm_style .chosen-container-multi .chosen-choices li.search-field input[type="text"]{
    margin:1px 0;
    padding:0 !important;
    height:25px;
    outline:0;
    border:0 !important;
    background:transparent !important;
    box-shadow:none;
    color:#666;
    font-size:100%;
    font-family:sans-serif;
    line-height:normal;
    border-radius:0;
}

.with_frm_style .chosen-container-multi .chosen-choices li.search-choice{
    position:relative;
    margin:3px 5px 3px 0;
    padding:3px 20px 3px 5px;
    border:1px solid #aaa;
    max-width:100%;
    border-radius:3px;
    background-color:#eee;
    background-size:100% 19px;
    background-repeat:repeat-x;
    background-clip:padding-box;
    box-shadow:0 0 2px white inset, 0 1px 0 rgba(0, 0, 0, 0.05);
    color:#333;
    line-height:13px;
    cursor:default;
}

.with_frm_style .chosen-container-multi .chosen-choices li.search-choice .search-choice-close{
    position:absolute;
    top:4px;
    right:3px;
    display:block;
    width:12px;
    height:12px;
    background:url('<?php echo FrmAppHelper::relative_plugin_url() ?>/pro/images/chosen-sprite.png') -42px 1px no-repeat;
    font-size:1px;
}

.with_frm_style .chosen-container-multi .chosen-choices li.search-choice .search-choice-close:hover{
    background-position:-42px -10px;
}

.with_frm_style .chosen-container-multi .chosen-choices li.search-choice-disabled{
    padding-right:5px;
    border:1px solid #ccc;
    background-color:#e4e4e4;
    color:#666;
}

.with_frm_style .chosen-container-multi .chosen-choices li.search-choice-focus{
    background:#d4d4d4;
}

.with_frm_style .chosen-container-multi .chosen-choices li.search-choice-focus .search-choice-close{
    background-position:-42px -10px;
}

.with_frm_style .chosen-container-multi .chosen-results{
    margin:0;
    padding:0;
}

.with_frm_style .chosen-container-multi .chosen-drop .result-selected{
    display:list-item;
    color:#ccc;
    cursor:default;
}

.with_frm_style .chosen-container-active .chosen-single{
    border:1px solid #5897fb;
    box-shadow:0 0 5px rgba(0, 0, 0, 0.3);
}

.with_frm_style .chosen-container-active.chosen-with-drop .chosen-single{
    border:1px solid #aaa;
    -moz-border-radius-bottomright:0;
    border-bottom-right-radius:0;
    -moz-border-radius-bottomleft:0;
    border-bottom-left-radius:0;
    box-shadow:0 1px 0 #fff inset;
}

.with_frm_style .chosen-container-active.chosen-with-drop .chosen-single div{
    border-left:none;
    background:transparent;
}

.with_frm_style .chosen-container-active.chosen-with-drop .chosen-single div b{
    background-position:-18px 2px;
}

.with_frm_style .chosen-container-active .chosen-choices li.search-field input[type="text"]{
    color:#111 !important;
}

.with_frm_style .chosen-disabled{
    opacity:0.5 !important;
    cursor:default;
}

.with_frm_style .chosen-disabled .chosen-single{
    cursor:default;
}

.with_frm_style .chosen-disabled .chosen-choices .search-choice .search-choice-close{
    cursor:default;
}

.with_frm_style .chosen-rtl{
    text-align:right;
}

.with_frm_style .chosen-rtl .chosen-single{
    overflow:visible;
    padding:0 8px 0 0;
}

.with_frm_style .chosen-rtl .chosen-single span{
    margin-right:0;
    margin-left:26px;
    direction:rtl;
}

.with_frm_style .chosen-rtl .chosen-single-with-deselect span{
    margin-left:38px;
}

.with_frm_style .chosen-rtl .chosen-single div{
    right:auto;
    left:3px;
}

.with_frm_style .chosen-rtl .chosen-single abbr{
    right:auto;
    left:26px;
}

.with_frm_style .chosen-rtl .chosen-choices li{
    float:right;
}

.with_frm_style .chosen-rtl .chosen-choices li.search-field input[type="text"]{
    direction:rtl;
}

.with_frm_style .chosen-rtl .chosen-choices li.search-choice{
    margin:3px 5px 3px 0;
    padding:3px 5px 3px 19px;
}

.with_frm_style .chosen-rtl .chosen-choices li.search-choice .search-choice-close{
    right:auto;left:4px;
}

.with_frm_style .chosen-rtl.chosen-container-single-nosearch .chosen-search, .with_frm_style .chosen-rtl .chosen-drop{
    left:9999px;
}

.with_frm_style .chosen-rtl.chosen-container-single .chosen-results{
    margin:0 0 4px 4px;
    padding:0 4px 0 0;
}

.with_frm_style .chosen-rtl .chosen-results li.group-option{
    padding-right:15px;
    padding-left:0;
}

.with_frm_style .chosen-rtl.chosen-container-active.chosen-with-drop .chosen-single div{
    border-right:none;
}

.with_frm_style .chosen-rtl .chosen-search input[type="text"]{
    padding:4px 5px 4px 20px;
    background:white url('<?php echo FrmAppHelper::relative_plugin_url() ?>/pro/images/chosen-sprite.png') no-repeat -30px -20px;background:url('<?php echo FrmAppHelper::relative_plugin_url() ?>/pro/images/chosen-sprite.png') no-repeat -30px -20px;
    direction:rtl;
}

.with_frm_style .chosen-rtl.chosen-container-single .chosen-single div b{
    background-position:6px 2px;
}

.with_frm_style .chosen-rtl.chosen-container-single.chosen-with-drop .chosen-single div b{
    background-position:-12px 2px;
}
/* End Chosen */

@media only screen and (-webkit-min-device-pixel-ratio: 2), only screen and (min-resolution: 144dpi){
    .with_frm_style .chosen-rtl .chosen-search input[type="text"],
    .with_frm_style .chosen-container-single .chosen-single abbr,
    .with_frm_style .chosen-container-single .chosen-single div b,
    .with_frm_style .chosen-container-single .chosen-search input[type="text"],
    .with_frm_style .chosen-container-multi .chosen-choices .search-choice .search-choice-close,
    .with_frm_style .chosen-container .chosen-results-scroll-down span,
    .with_frm_style .chosen-container .chosen-results-scroll-up span{
        background-image:url('<?php echo FrmAppHelper::relative_plugin_url() ?>/pro/images/chosen-sprite2x.png') !important;
        background-size:52px 37px !important;
        background-repeat:no-repeat !important;
    }
}