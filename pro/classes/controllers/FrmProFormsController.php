<?php

class FrmProFormsController{

	/**
	 * Used for hiding the form on page load
	 * @since 2.3
	 */
	public static function head() {
		echo '<script type="text/javascript">document.documentElement.className += " js";</script>' . "\r\n";
	}

    public static function add_form_options($values){
        global $frm_vars;

        $post_types = FrmProAppHelper::get_custom_post_types();
		$has_file_field = FrmField::get_all_types_in_form( $values['id'], 'file', 2, 'include' );

        require(FrmAppHelper::plugin_path() .'/pro/classes/views/frmpro-forms/add_form_options.php');
    }

	public static function add_form_page_options( $values ) {
		$page_fields = FrmField::get_all_types_in_form( $values['id'], 'break' );
		if ( $page_fields ) {
			$hide_rootline_class = empty( $values['rootline'] ) ? 'frm_hidden' : '';
			$hide_rootline_title_class = empty( $values['rootline_titles_on'] ) ? 'frm_hidden' : '';
			$i = 1;
			require( FrmAppHelper::plugin_path() .'/pro/classes/views/frmpro-forms/form_page_options.php' );
		}
	}

    public static function add_form_ajax_options($values){
        global $frm_vars;

        $post_types = FrmProAppHelper::get_custom_post_types();

        require(FrmAppHelper::plugin_path() .'/pro/classes/views/frmpro-forms/add_form_ajax_options.php');
    }

    /**
     * Remove the noallow class on pro fields
     * @return string
     */
    public static function noallow_class() {
        return '';
    }

    public static function add_form_button_options($values){
        global $frm_vars;

        $page_field = FrmProFormsHelper::has_field('break', $values['id'], true);

        $post_types = FrmProAppHelper::get_custom_post_types();

        require(FrmAppHelper::plugin_path() .'/pro/classes/views/frmpro-forms/add_form_button_options.php');
    }

    public static function add_form_msg_options($values){
        global $frm_vars;

        $post_types = FrmProAppHelper::get_custom_post_types();

        require(FrmAppHelper::plugin_path() .'/pro/classes/views/frmpro-forms/add_form_msg_options.php');
    }

    public static function instruction_tabs(){
        include(FrmAppHelper::plugin_path() .'/pro/classes/views/frmpro-forms/instruction_tabs.php');
    }

    public static function instructions(){
		$tags = array(
			'date'         => __( 'Current Date', 'formidable' ),
			'time'         => __( 'Current Time', 'formidable' ),
			'email'        => __( 'Email', 'formidable' ),
			'login'        => __( 'Login', 'formidable' ),
			'display_name' => __( 'Display Name', 'formidable' ),
			'first_name'   => __( 'First Name', 'formidable' ),
			'last_name'    => __( 'Last Name', 'formidable' ),
			'user_id'      => __( 'User ID', 'formidable' ),
			'user_meta key=whatever' => __( 'User Meta', 'formidable' ),
			'user_role'    => __( 'User Role', 'formidable' ),
			'post_id'      => __( 'Post ID', 'formidable' ),
			'post_title'   => __( 'Post Title', 'formidable' ),
			'post_author_email' => __( 'Author Email', 'formidable' ),
			'post_meta key=whatever' => __( 'Post Meta', 'formidable' ),
			'ip'           => __( 'IP Address', 'formidable' ),
			'auto_id start=1' => __( 'Increment', 'formidable' ),
			'get param=whatever' => array( 'label' => __( 'GET/POST', 'formidable' ), 'title' => __( 'A variable from the URL or value posted from previous page.', 'formidable' ) .' '. __( 'Replace \'whatever\' with the parameter name. In url.com?product=form, the variable is \'product\'. You would use [get param=product] in your field.', 'formidable' )),
			'server param=whatever' => array( 'label' => __( 'SERVER', 'formidable' ), 'title' => __( 'A variable from the PHP SERVER array.', 'formidable' ) .' '. __( 'Replace \'whatever\' with the parameter name. To get the url of the current page, use [server param="REQUEST_URI"] in your field.', 'formidable' )),
		);
        include(FrmAppHelper::plugin_path() .'/pro/classes/views/frmpro-forms/instructions.php');
    }

    public static function add_field_link($field_type) {
        return '<a href="#" class="frm_add_field">'. $field_type .'</a>';
    }

    public static function drag_field_class(){
        return ' class="field_type_list"';
    }

    public static function formidable_shortcode_atts($atts, $all_atts){
        global $frm_vars, $wpdb;

        // reset globals
        $frm_vars['readonly'] = $atts['readonly'];
        $frm_vars['editing_entry'] = false;
        $frm_vars['show_fields'] = array();

        if ( ! is_array($atts['fields']) ) {
            $frm_vars['show_fields'] = explode(',', $atts['fields']);
        }

        if ( ! empty($atts['exclude_fields']) ) {
            if ( ! is_array( $atts['exclude_fields'] ) ) {
                $atts['exclude_fields'] = explode(',', $atts['exclude_fields']);
            }

            $query = array(
                'form_id' => (int) $atts['id'],
                'id NOT' => $atts['exclude_fields'],
                'field_key NOT' => $atts['exclude_fields'],
            );

            $frm_vars['show_fields'] = FrmDb::get_col($wpdb->prefix .'frm_fields', $query);
        }

        if ( $atts['entry_id'] && $atts['entry_id'] == 'last' ) {
            $user_ID = get_current_user_id();
            if ( $user_ID ) {
                $frm_vars['editing_entry'] = FrmDb::get_var( $wpdb->prefix .'frm_items', array( 'form_id' => $atts['id'], 'user_id' => $user_ID), 'id', array( 'order_by' => 'created_at DESC') );
            }
        } else if ( $atts['entry_id'] ) {
            $frm_vars['editing_entry'] = $atts['entry_id'];
        }

        foreach ( $atts as $unset => $val ) {
            if ( is_array($all_atts) && isset($all_atts[$unset]) ) {
                unset($all_atts[$unset]);
            }
            unset($unset, $val);
        }

        if ( is_array($all_atts) ) {
            foreach ( $all_atts as $att => $val ) {
                $_GET[ $att ] = $val;
                unset($att, $val);
            }
        }
    }

	public static function add_form_classes( $form ) {
		echo ' frm_pro_form ';
		if ( isset( $form->options['js_validate'] ) && $form->options['js_validate'] ) {
			echo ' frm_js_validate ';
		}

		if ( FrmProForm::is_ajax_on( $form ) ) {
			echo ' frm_ajax_submit ';
		}

		self::maybe_add_hide_class( $form );
	}

	private static function maybe_add_hide_class( $form ) {
		$frm_settings = FrmAppHelper::get_settings();
		if ( $frm_settings->fade_form && FrmProForm::has_fields_with_conditional_logic( $form ) ) {
			echo ' frm_logic_form ';
		}
	}

    public static function form_fields_class($class){
        global $frm_page_num;
        if ( $frm_page_num ) {
            $class .= ' frm_page_num_'. $frm_page_num;
        }

        return $class;
    }

    public static function form_hidden_fields($form){
        if ( is_user_logged_in() && isset( $form->options['save_draft'] ) && $form->options['save_draft'] == 1 ) {
            echo '<input type="hidden" name="frm_saving_draft" class="frm_saving_draft" value="" />';
        }
    }

    public static function submit_button_label($submit, $form){
        global $frm_vars;
		if ( ! FrmProFormsHelper::is_final_page( $form->id ) ) {
			$submit = $frm_vars['next_page'][ $form->id ];
			if ( is_object( $submit ) ) {
                $submit = $submit->name;
			}
        }
        return $submit;
    }

    public static function replace_shortcodes( $html, $form, $values = array() ) {
        preg_match_all("/\[(if )?(deletelink|back_label|back_hook|back_button|draft_label|save_draft|draft_hook)\b(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?/s", $html, $shortcodes, PREG_PATTERN_ORDER);

		if ( empty( $shortcodes[0] ) ) {
            return $html;
		}

		foreach ( $shortcodes[0] as $short_key => $tag ) {
            $replace_with = '';
            $atts = FrmShortcodeHelper::get_shortcode_attribute_array( $shortcodes[3][$short_key] );

			switch ( $shortcodes[2][ $short_key ] ) {
                case 'deletelink':
                    $replace_with = FrmProEntriesController::entry_delete_link($atts);
                break;
                case 'back_label':
                    $replace_with = isset($form->options['prev_value']) ? $form->options['prev_value'] : __( 'Previous', 'formidable' );
                break;
                case 'back_hook':
                    $replace_with = apply_filters('frm_back_button_action', '', $form);
                break;
                case 'back_button':
                    global $frm_vars;
                    if ( ! $frm_vars['prev_page'] || ! is_array($frm_vars['prev_page']) || ! isset($frm_vars['prev_page'][$form->id]) || empty($frm_vars['prev_page'][$form->id]) ) {
                        unset($replace_with);
                    } else {
                        $classes = apply_filters('frm_back_button_class', array(), $form);
                        if ( ! empty( $classes ) ) {
                            $html = str_replace('class="frm_prev_page', 'class="frm_prev_page '. implode(' ', $classes), $html);
                        }

                        $html = str_replace('[/if back_button]', '', $html);
                    }
                break;
                case 'draft_label':
                    $replace_with = __( 'Save Draft', 'formidable' );
                break;
                case 'save_draft':
                    if ( ! is_user_logged_in() || ! isset($form->options['save_draft']) || $form->options['save_draft'] != 1 || ( isset($values['is_draft']) && ! $values['is_draft'] ) ) {
                        //remove button if user is not logged in, drafts are not allowed, or editing an entry that is not a draft
                        unset($replace_with);
                    }else{
                        $html = str_replace('[/if save_draft]', '', $html);
                    }
                break;
                case 'draft_hook':
                    $replace_with = apply_filters('frm_draft_button_action', '', $form);
                break;
            }

			if ( isset( $replace_with ) ) {
				$html = str_replace( $shortcodes[0][ $short_key ], $replace_with, $html );
			}

            unset( $short_key, $tag, $replace_with );
        }

        return $html;
    }

    public static function replace_content_shortcodes($content, $entry, $shortcodes) {
        remove_filter('frm_replace_content_shortcodes', 'FrmFormsController::replace_content_shortcodes', 20);
		return FrmProContent::replace_shortcodes( $content, $entry, $shortcodes );
    }

    public static function conditional_options($options) {
        $cond_opts = array(
            'equals="something"' => __( 'Equals', 'formidable' ),
            'not_equal="something"' => __( 'Does Not Equal', 'formidable' ),
            'equals=""' => __( 'Is Blank', 'formidable' ),
            'not_equal=""' => __( 'Is Not Blank', 'formidable' ),
            'like="something"' => __( 'Is Like', 'formidable' ),
            'not_like="something"' => __( 'Is Not Like', 'formidable' ),
            'greater_than="3"' => __( 'Greater Than', 'formidable' ),
            'less_than="-1 month"' => __( 'Less Than', 'formidable' )
        );

        $options = array_merge($options, $cond_opts);
        return $options;
    }

    public static function advanced_options($options) {
        $adv_opts = array(
            'clickable=1' => __( 'Clickable Links', 'formidable' ),
            'links=0'   => array( 'label' => __( 'Remove Links', 'formidable' ), 'title' => __( 'Removes the automatic links to category pages', 'formidable' )),
            'sanitize=1' => array( 'label' => __( 'Sanitize', 'formidable' ), 'title' => __( 'Replaces spaces with dashes and lowercases all. Use if adding an HTML class or ID', 'formidable' )),
            'sanitize_url=1' => array( 'label' => __( 'Sanitize URL', 'formidable' ), 'title' =>  __( 'Replaces all HTML entities with a URL safe string.', 'formidable' )),
            'truncate=40' => array( 'label' => __( 'Truncate', 'formidable' ), 'title' => __( 'Truncate text with a link to view more. If using Both (dynamic), the link goes to the detail page. Otherwise, it will show in-place.', 'formidable' )),
            'truncate=100 more_text="More"' => __( 'More Text', 'formidable' ),
            'time_ago=1' => array( 'label' => __( 'Time Ago', 'formidable' ), 'title' => __( 'How long ago a date was in minutes, hours, days, months, or years.', 'formidable' )),
            'decimal=2 dec_point="." thousands_sep=","' => __( '# Format', 'formidable' ),
            'show="value"' => array( 'label' => __( 'Saved Value', 'formidable' ), 'title' => __( 'Show the saved value for fields with separate values.', 'formidable' ) ),
            'striphtml=1' => array( 'label' => __( 'Remove HTML', 'formidable' ), 'title' => __( 'Remove all HTML added into your form before display', 'formidable' )),
            'keepjs=1' => array( 'label' => __( 'Keep JS', 'formidable' ), 'title' => __( 'Javascript from your form entries are automatically removed. Add this option only if you trust those submitting entries.', 'formidable' )),
        );

        $options = array_merge($options, $adv_opts);
        return $options;
    }

    public static function user_options($options) {
        $user_fields = array(
            'ID'            => __( 'User ID', 'formidable' ),
            'first_name'    => __( 'First Name', 'formidable' ),
            'last_name'     => __( 'Last Name', 'formidable' ),
            'display_name'  => __( 'Display Name', 'formidable' ),
            'user_login'    => __( 'User Login', 'formidable' ),
            'user_email'    => __( 'Email', 'formidable' ),
            'avatar'        => __( 'Avatar', 'formidable' ),
			'author_link'   => __( 'Author Link', 'formidable' ),
        );

        $options = array_merge($options, $user_fields);
        return $options;
    }

    public static function include_logic_row($atts) {
        $defaults = array(
            'meta_name' => '',
            'condition' => array(
                'hide_field'        => '',
                'hide_field_cond'   => '==',
                'hide_opt'          => '',
            ),
            'key' => '', 'type' => 'form',
            'form_id' => 0, 'id' => '' ,
            'name' => '', 'names' => array(),
            'showlast' => '', 'onchange' => '',
			'exclude_fields' => array_merge( FrmField::no_save_fields(), array( 'file', 'rte', 'date') ),
        );

        $atts = wp_parse_args($atts, $defaults);

		if ( empty( $atts['id'] ) ) {
			$atts['id'] = 'frm_logic_' . $atts['key'] . '_' . $atts['meta_name'];
		}

		if ( empty( $atts['name'] ) ) {
			$atts['name'] = 'frm_form_action[' . $atts['key'] . '][post_content][conditions][' . $atts['meta_name'] . ']';
		}

		if ( empty( $atts['names'] ) ) {
			$atts['names'] = array(
				'hide_field' => $atts['name'] . '[hide_field]',
				'hide_field_cond' => $atts['name'] .'[hide_field_cond]',
				'hide_opt' => $atts['name'] . '[hide_opt]',
			);
		}

		// TODO: get rid of this and add event binding instead
		if ( $atts['onchange'] == '' ) {
			$atts['onchange'] = "frmGetFieldValues(this.value,'". $atts['key'] . "','" . $atts['meta_name'] . "','','" . $atts['names']['hide_opt'] . "')";
		}

		$form_fields = FrmField::get_all_for_form( $atts['form_id'] );

		extract( $atts );
        include(FrmAppHelper::plugin_path() .'/pro/classes/views/frmpro-forms/_logic_row.php');
    }

	public static function setup_new_vars($values) {
	    return FrmProFormsHelper::setup_new_vars($values);
	}

	public static function setup_edit_vars($values) {
	    return FrmProFormsHelper::setup_edit_vars($values);
	}

	public static function popup_shortcodes($shortcodes) {
	    $shortcodes['display-frm-data'] = array( 'name' => __( 'View', 'formidable' ), 'label' => __( 'Insert a View', 'formidable' ));
	    $shortcodes['frm-graph'] = array( 'name' => __( 'Graph', 'formidable' ), 'label' => __( 'Insert a Graph', 'formidable' ));
        $shortcodes['frm-search'] = array( 'name' => __( 'Search', 'formidable' ), 'label' => __( 'Add a Search Form', 'formidable' ));
        $shortcodes['frm-show-entry'] = array( 'name' => __( 'Single Entry', 'formidable' ), 'label' => __( 'Display a Single Entry', 'formidable' ));
		$shortcodes['frm-entry-links'] = array( 'name' => __( 'List of Entries', 'formidable' ), 'label' => __( 'Display a List of Entries', 'formidable' ) );

		/*
		To add:
			formresults, frm-entry-edit-link, frm-entry-delete-link,
			frm-entry-update-field, frm-field-value, frm-set-get?,
			frm-alt-color?
		*/
        return $shortcodes;
	}

	public static function sc_popup_opts($opts, $shortcode) {
	    $function_name = 'popup_opts_'. str_replace('-', '_', $shortcode);
		if ( method_exists( 'FrmProFormsController', $function_name ) ) {
			self::$function_name($opts, $shortcode);
		}
	    return $opts;
	}

    private static function popup_opts_formidable(array &$opts) {
        //'fields' => '', 'entry_id' => 'last' or #, 'exclude_fields' => '', GET => value
        $opts['readonly'] = array( 'val' => 'disabled', 'label' => __( 'Make read-only fields editable', 'formidable' ));
    }

    private static function popup_opts_display_frm_data(array &$opts, $shortcode) {
        //'entry_id' => '',  'user_id' => false, 'order' => '',
        $displays = FrmProDisplay::getAll( array(), 'post_title');

?>
        <h4 for="frmsc_<?php echo esc_attr( $shortcode ) ?>_id" class="frm_left_label"><?php _e( 'Select a view:', 'formidable' ) ?></h4>
        <select id="frmsc_<?php echo esc_attr( $shortcode ) ?>_id">
            <option value=""> </option>
            <?php foreach ( $displays as $display ) { ?>
            <option value="<?php echo esc_attr( $display->ID ) ?>"><?php echo esc_html( $display->post_title ) ?></option>
            <?php } ?>
        </select>
        <div class="frm_box_line"></div>
<?php
        $opts = array(
            'filter' => array( 'val' => 1, 'label' => __( 'Filter shortcodes within the view content', 'formidable' )),
            'limit' => array( 'val' => '', 'label' => __( 'Limit', 'formidable' ), 'type' => 'text'),
            'page_size' => array( 'val' => '', 'label' => __( 'Page size', 'formidable' ), 'type' => 'text'),
			'order'  => array(
                'val'   => '', 'label' => __( 'Entry order', 'formidable' ), 'type' => 'select',
                'opts'  => array(
                    ''      => __( 'Default', 'formidable' ),
                    'ASC'   => __( 'Ascending', 'formidable' ),
                    'DESC'  => __( 'Descending', 'formidable' ),
                ),
            ),
        );
    }

    private static function popup_opts_frm_search(array &$opts) {
        $opts = array(
            'style' => array( 'val' => 1, 'label' => __( 'Use Formidable styling', 'formidable' )), // or custom class?
            'label' => array(
                'val' => __( 'Search', 'formidable' ),
                'label' => __( 'Customize search button', 'formidable' ),
                'type' => 'text',
            ),
            'post_id' => array(
                'val' => '',
                'label' => __( 'The ID of the page with the search results', 'formidable' ),
                'type' => 'text',
            ),
        );
    }

    private static function popup_opts_frm_graph(array &$opts, $shortcode) {
		$where = array(
			'status' => 'published',
			'is_template' => 0,
			array( 'or' => 1, 'parent_form_id' => null, 'parent_form_id <' => 1 ),
		);
		$form_list = FrmForm::getAll( $where, 'name' );

    ?>
		<h4 class="frm_left_label"><?php _e( 'Select a form and field:', 'formidable' ) ?></h4>

		<select class="frm_get_field_selection" id="<?php echo esc_attr( $shortcode ) ?>_form">
			<option value="">&mdash; <?php _e( 'Select Form', 'formidable' ) ?> &mdash;</option>
			<?php foreach ( $form_list as $form_opts ) { ?>
			<option value="<?php echo esc_attr( $form_opts->id ) ?>"><?php echo '' == $form_opts->name ? __( '(no title)', 'formidable' ) : esc_html( FrmAppHelper::truncate($form_opts->name, 50) ) ?></option>
			<?php } ?>
		</select>

		<span id="<?php echo esc_attr( $shortcode ) ?>_fields_container">
		</span>

		<div class="frm_box_line"></div><?php

		$opts = array(
			'type'  => array(
				'val'   => 'default', 'label' => __( 'Graph Type', 'formidable' ), 'type' => 'select',
				'opts'  => array(
					'column'    => __( 'Column', 'formidable' ),
					'hbar'    => __( 'Horizontal Bar', 'formidable' ),
					'pie'       => __( 'Pie', 'formidable' ),
					'line'      => __( 'Line', 'formidable' ),
					'area'      => __( 'Area', 'formidable' ),
					'scatter'      => __( 'Scatter', 'formidable' ),
					'histogram'      => __( 'Histogram', 'formidable' ),
					'table'      => __( 'Table', 'formidable' ),
					'stepped_area' => __( 'Stepped Area', 'formidable' ),
					'geo'       => __( 'Geographical Map', 'formidable' ),
				),
			),
			'data_type' => array(
				'val'   => 'count', 'label' => __( 'Data Type', 'formidable' ), 'type' => 'select',
				'opts'  => array(
					'count' => __( 'The number of entries', 'formidable' ),
					'total' => __( 'Add the field values together', 'formidable' ),
					'average' => __( 'Average the totaled field values', 'formidable' ),
				),
			),
			'height'    => array( 'val' => '', 'label' => __( 'Height', 'formidable' ), 'type' => 'text'),
			'width'     => array( 'val' => '', 'label' => __( 'Width', 'formidable' ), 'type' => 'text'),
			'bg_color'  => array( 'val' => '', 'label' => __( 'Background color', 'formidable' ), 'type' => 'text'),
			'title'     => array( 'val' => '', 'label' => __( 'Graph title', 'formidable' ), 'type' => 'text'),
			'title_size'=> array( 'val' => '', 'label' => __( 'Title font size', 'formidable' ), 'type' => 'text'),
			'title_font'=> array( 'val' => '', 'label' => __( 'Title font name', 'formidable' ), 'type' => 'text'),
			'is3d'      => array(
				'val'   => 1, 'label' => __( 'Turn your pie graph three-dimensional', 'formidable' ),
				'show'  => array( 'type' => 'pie'),
			),
			'include_zero' => array( 'val' => 1, 'label' => __( 'When using dates for the x_axis parameter, you can include dates with a zero value.', 'formidable' )),
			'show_key' => array( 'val' => 1, 'label' => __( 'Include a legend with the graph', 'formidable' )),
		);
    }

    private static function popup_opts_frm_show_entry(array &$opts, $shortcode) {

?>
    <h4 class="frm_left_label"><?php _e( 'Insert an entry ID/key:', 'formidable' ) ?></h4>

    <input type="text" value="" id="frmsc_<?php echo esc_attr( $shortcode ) ?>_id" />

    <div class="frm_box_line"></div>
<?php
        $opts = array(
            'user_info'     => array( 'val' => 1, 'label' => __( 'Include user info like browser and IP', 'formidable' )),
            'include_blank' => array( 'val' => 1, 'label' => __( 'Include rows for blank fields', 'formidable' )),
            'plain_text'    => array( 'val' => 1, 'label' => __( 'Do not include any HTML', 'formidable' )),
            'direction'     => array( 'val' => 'rtl', 'label' => __( 'Use RTL format', 'formidable' )),
            'font_size'     => array( 'val' => '', 'label' => __( 'Font size', 'formidable' ), 'type' => 'text'),
            'text_color'    => array( 'val' => '', 'label' => __( 'Text color', 'formidable' ), 'type' => 'text'),
            'border_width'  => array( 'val' => '', 'label' => __( 'Border width', 'formidable' ), 'type' => 'text'),
            'border_color'  => array( 'val' => '', 'label' => __( 'Border color', 'formidable' ), 'type' => 'text'),
            'bg_color'      => array( 'val' => '', 'label' => __( 'Background color', 'formidable' ), 'type' => 'text'),
            'alt_bg_color'  => array( 'val' => '', 'label' => __( 'Alternate background color', 'formidable' ), 'type' => 'text'),
        );
    }

	private static function popup_opts_frm_entry_links( array &$opts, $shortcode ) {
		$opts = array(
			'form_id'   => 'id',
			'field_key' => array(
				'val' => 'created_at', 'type' => 'text',
				'label' => __( 'Field ID/key for labels', 'formidable' ),
			),
			'type'      => array(
				'val' => 'list', 'label' => __( 'Display format', 'formidable' ),
				'type' => 'select', 'opts' => array(
					'list'     => __( 'List', 'formidable' ),
					'select'   => __( 'Drop down', 'formidable' ),
					'collapse' => __( 'Expanding archive', 'formidable' ),
				),
			),
			'logged_in' => array(
				'val'   => 1, 'type' => 'select',
				'label' => __( 'Privacy', 'formidable' ),
				'opts'  => array(
					1 => __( 'Only include the entries the current user created', 'formidable' ),
					0 => __( 'Include all entries', 'formidable' ),
				),
			),
			'page_id'   => array( 'val' => '', 'label' => __( 'The ID of the page to link to', 'formidable' ), 'type' => 'text' ),
			'edit'      => array(
				'val'   => 1, 'type' => 'select',
				'label' => __( 'Link action', 'formidable' ),
				'opts'  => array(
					1 => __( 'Edit if allowed', 'formidable' ),
					0 => __( 'View only', 'formidable' ),
				),
			),
			'show_delete' => array( 'val' => '', 'label' => __( 'Delete link label', 'formidable' ), 'type' => 'text' ),
			'confirm'     => array( 'val'   => '', 'label' => __( 'Delete confirmation message', 'formidable' ), 'type'  => 'text' ),
			'link_type'   => array(
				'val'   => 'page', 'type' => 'select',
				'label' => __( 'Send users to', 'formidable' ),
				'opts'  => array(
					'page'   => __( 'A page', 'formidable' ),
					'scroll' => __( 'An anchor on the page with id="[key]"', 'formidable' ),
					'admin'  => __( 'The entry in the back-end', 'formidable' ),
				),
			),
			'param_name'  => array( 'val' => 'entry', 'label' => __( 'URL parameter (?entry=5)', 'formidable' ), 'type' => 'text' ),
			'param_value' => array(
				'val'   => 'key', 'type' => 'select',
				'label' => __( 'Identify the entry by', 'formidable' ),
				'opts'  => array(
					'key' => __( 'Entry key', 'formidable' ),
					'id'  => __( 'Entry ID', 'formidable' ),
				),
			),
			'class'       => array( 'val' => '', 'label' => __( 'Add HTML classes', 'formidable' ), 'type' => 'text' ),
			'blank_label' => array( 'val' => '', 'label' => __( 'Label on first option in the dropdown', 'formidable' ), 'type' => 'text' ),
			'drafts'      => array( 'val' => 1, 'label' => __( 'Include draft entries', 'formidable' ) ),
		);
	}

	/**
	 * Add Pro field helpers to Customization Panel
	 *
	 * @since 2.0.22
	 * @param array $entry_shortcodes
	 * @param bool $settings_tab
	 * @return array
	 */
	public static function add_pro_field_helpers( $entry_shortcodes, $settings_tab ){
		if ( ! $settings_tab ) {
			$entry_shortcodes['is_draft'] = __( 'Draft status', 'formidable' );
		}

		return $entry_shortcodes;
	}

	public static function setup_form_data_for_editing_entry( $entry, &$values ) {
		$form = $entry->form_id;
		FrmForm::maybe_get_form( $form );

		if ( ! $form || ! is_array( $form->options ) ) {
			return;
		}

		$values['form_name'] = $form->name;
		$values['parent_form_id'] = $form->parent_form_id;

		if ( ! is_array($form->options) ) {
			return;
		}

		foreach ( $form->options as $opt => $value ) {
			$values[ $opt ] = $value;
		}

		$form_defaults = FrmFormsHelper::get_default_opts();

		foreach ( $form_defaults as $opt => $default ) {
			if ( ! isset( $values[ $opt ] ) || $values[ $opt ] == '' ) {
				$values[ $opt ] = $default;
			}
		}
		unset($opt, $defaut);

		$post_values = stripslashes_deep( $_POST );
		if ( ! isset( $values['custom_style'] ) ) {
			$values['custom_style'] = FrmAppHelper::custom_style_value( $post_values );
		}

		foreach ( array( 'before', 'after', 'submit' ) as $h ) {
			if ( ! isset( $values[ $h .'_html' ] ) ) {
				$values[ $h .'_html' ] = ( isset( $post_values['options'][ $h .'_html' ] ) ? $post_values['options'][ $h .'_html' ] : FrmFormsHelper::get_default_html( $h ) );
			}
		}
		unset($h);
	}

	/* Trigger model actions */
	public static function update_options($options, $values){
        return FrmProForm::update_options($options, $values);
    }

    public static function save_wppost_actions($settings, $action) {
        return FrmProForm::save_wppost_actions($settings, $action);
    }

    public static function update_form_field_options($field_options, $field){
        return FrmProForm::update_form_field_options($field_options, $field);
    }

    public static function update($id, $values){
        FrmProForm::update($id, $values);
    }

    public static function after_duplicate($new_opts) {
        return FrmProForm::after_duplicate($new_opts);
    }

    public static function validate( $errors, $values ){
        return FrmProForm::validate( $errors, $values );
    }

	public static function add_form_row( ) {
		FrmProNestedFormsController::ajax_add_repeat_row();
	}
}
