<?php

class FrmProDisplaysController{
    public static $post_type = 'frm_display';

    public static function trigger_load_view_hooks() {
        FrmHooksController::trigger_load_hook( 'load_view_hooks' );
    }

	public static function register_post_types() {
        register_post_type(self::$post_type, array(
            'label' => __( 'Views', 'formidable' ),
            'description' => '',
            'public' => apply_filters('frm_public_views', true),
            'show_ui' => true,
            'exclude_from_search' => true,
            'show_in_nav_menus' => false,
            'show_in_menu' => false,
            'menu_icon' => admin_url('images/icons32.png'),
			'capability_type' => 'page',
			'capabilities' => array(
				'edit_post'		=> 'frm_edit_displays',
				'edit_posts'	=> 'frm_edit_displays',
				'edit_others_posts' => 'frm_edit_displays',
				'publish_posts' => 'frm_edit_displays',
				'delete_post'	=> 'frm_edit_displays',
				'delete_posts'	=> 'frm_edit_displays',
				'read_post'		=> 'frm_edit_displays', // Needed to view revisions
			),
            'supports' => array(
                'title', 'revisions',
            ),
            'has_archive' => false,
            'labels' => array(
                'name' => __( 'Views', 'formidable' ),
                'singular_name' => __( 'View', 'formidable' ),
                'menu_name' => __( 'View', 'formidable' ),
                'edit' => __( 'Edit'),
                'search_items' => __( 'Search Views', 'formidable' ),
                'not_found' => __( 'No Views Found.', 'formidable' ),
                'add_new_item' => __( 'Add New View', 'formidable' ),
                'edit_item' => __( 'Edit View', 'formidable' )
            )
        ) );
    }

	public static function menu() {
		FrmAppHelper::force_capability( 'frm_edit_displays' );

        add_submenu_page('formidable', 'Formidable | '. __( 'Views', 'formidable' ), __( 'Views', 'formidable' ), 'frm_edit_displays', 'edit.php?post_type=frm_display');
    }

	public static function highlight_menu() {
        FrmAppHelper::maybe_highlight_menu(self::$post_type);
    }

	public static function switch_form_box() {
        global $post_type_object;
        if ( ! $post_type_object || $post_type_object->name != self::$post_type ) {
            return;
        }
		$form_id = FrmAppHelper::simple_get( 'form', 'absint' );
        echo FrmFormsHelper::forms_dropdown( 'form', $form_id, array( 'blank' => __( 'View all forms', 'formidable' )) );
    }

	public static function filter_forms( $query ) {
        if ( ! FrmProDisplaysHelper::is_edit_view_page() ) {
            return $query;
        }

        if ( isset($_REQUEST['form']) && is_numeric($_REQUEST['form']) && isset($query->query_vars['post_type']) && self::$post_type == $query->query_vars['post_type'] ) {
            $query->query_vars['meta_key'] = 'frm_form_id';
            $query->query_vars['meta_value'] = (int) $_REQUEST['form'];
        }

        return $query;
    }

	public static function add_form_nav( $views ) {
        if ( ! FrmProDisplaysHelper::is_edit_view_page() ) {
            return $views;
        }

        $form = (isset($_REQUEST['form']) && is_numeric($_REQUEST['form'])) ? $_REQUEST['form'] : false;
        if ( ! $form ) {
            return $views;
        }

        $form = FrmForm::getOne($form);
        if ( ! $form ) {
            return $views;
        }

        echo '<div id="poststuff">';
        echo '<div id="post-body" class="metabox-holder columns-2">';
        echo '<div id="post-body-content">';
		FrmAppController::get_form_nav($form, true, 'hide');
		echo '</div>';
		echo '<div class="clear"></div>';
		echo '</div>';
		echo '<div id="titlediv"><input id="title" type="text" value="'. esc_attr($form->name == '' ? __( '(no title)') : $form->name) .'" readonly="readonly" disabled="disabled" /></div>';
        echo '</div>';

		echo '<style type="text/css">p.search-box{margin-top:-91px;}</style>';

        return $views;

    }

	public static function post_row_actions( $actions, $post ) {
        if ( $post->post_type == self::$post_type ) {
			$actions['duplicate'] = '<a href="' . esc_url( admin_url( 'post-new.php?post_type=frm_display&copy_id=' . $post->ID ) ) . '" title="' . esc_attr( __( 'Duplicate', 'formidable' ) ) . '">' . __( 'Duplicate', 'formidable' ) . '</a>';
        }
        return $actions;
    }

	public static function create_from_template( $path ) {
        $templates = glob( $path .'/*.php' );

		for ( $i = count( $templates ) - 1; $i >= 0; $i-- ) {
            $filename = str_replace('.php', '', str_replace($path.'/', '', $templates[$i]));
            $display = get_page_by_path($filename, OBJECT, self::$post_type);

            $values = FrmProDisplaysHelper::setup_new_vars();
            $values['display_key'] = $filename;

            include($templates[$i]);
        }
    }

	public static function manage_columns( $columns ) {
		unset( $columns['title'], $columns['date'] );

        $columns['id'] = 'ID';
        $columns['title'] = __( 'View Title', 'formidable' );
        $columns['description'] = __( 'Description');
        $columns['form_id'] = __( 'Form', 'formidable' );
        $columns['show_count'] = __( 'Entry', 'formidable' );
        $columns['content'] = __( 'Content', 'formidable' );
        $columns['dyncontent'] = __( 'Dynamic Content', 'formidable' );
        $columns['date'] = __( 'Date', 'formidable' );
        $columns['name'] = __( 'Key', 'formidable' );
        $columns['old_id'] = __( 'Former ID', 'formidable' );
        $columns['shortcode'] = __( 'Shortcode', 'formidable' );

        return $columns;
    }

	public static function sortable_columns( $columns ) {
        $columns['name'] = 'name';
        $columns['shortcode'] = 'ID';

        //$columns['description'] = 'excerpt';
        //$columns['content'] = 'content';

        return $columns;
    }

	public static function hidden_columns( $result ) {
        $return = false;
        foreach ( (array) $result as $r ) {
            if ( ! empty( $r ) ) {
                $return = true;
                break;
            }
        }

		if ( 'excerpt' != FrmAppHelper::simple_get( 'mode', 'sanitize_title' ) ) {
            $result[] = 'description';
        }

		if ( $return ) {
            return $result;
		}

        $result[] = 'content';
        $result[] = 'dyncontent';
        $result[] = 'old_id';

        return $result;
    }

	public static function manage_custom_columns( $column_name, $id ) {
        switch ( $column_name ) {
			case 'id':
			    $val = $id;
			    break;
			case 'old_id':
			    $old_id = get_post_meta($id, 'frm_old_id', true);
			    $val = ($old_id) ? $old_id : __( 'N/A', 'formidable' );
			    break;
			case 'name':
			case 'content':
			    $post = get_post($id);
			    $val = FrmAppHelper::truncate(strip_tags($post->{"post_$column_name"}), 100);
			    break;
			case 'description':
			    $post = get_post($id);
			    $val = FrmAppHelper::truncate(strip_tags($post->post_excerpt), 100);
		        break;
			case 'show_count':
			    $val = ucwords(get_post_meta($id, 'frm_'. $column_name, true));
			    break;
			case 'dyncontent':
			    $val = FrmAppHelper::truncate(strip_tags(get_post_meta($id, 'frm_'. $column_name, true)), 100);
			    break;
			case 'form_id':
			    $form_id = get_post_meta($id, 'frm_'. $column_name, true);
			    $val = FrmFormsHelper::edit_form_link($form_id);
				break;
			case 'shortcode':
			    $code = '[display-frm-data id='. $id .' filter=1]';

				$val = '<input type="text" readonly="readonly" class="frm_select_box" value="' . esc_attr( $code ) . '" />';
		        break;
			default:
			    $val = $column_name;
			break;
		}

        echo $val;
    }

	public static function submitbox_actions() {
        global $post;
        if ( $post->post_type != self::$post_type ) {
            return;
        }

        include(FrmAppHelper::plugin_path() .'/pro/classes/views/displays/submitbox_actions.php');
    }

	public static function default_content( $content, $post ) {
		$copy_id = FrmAppHelper::simple_get( 'copy_id', 'sanitize_title' );
		if ( $post->post_type != self::$post_type || empty( $copy_id ) ) {
            return $content;
        }

        global $copy_display;
		$copy_display = FrmProDisplay::getOne( $copy_id, false, false, array( 'check_post' => true ) );
        if ( $copy_display ) {
            $content = $copy_display->post_content;
            // Copy title and excerpt over to duplicated View
            add_filter('default_title',   'FrmProDisplaysController::default_title', 10, 2 );
            add_filter('default_excerpt', 'FrmProDisplaysController::default_excerpt', 10, 2 );
        }

        return $content;
    }

    /**
    *
    * Get the title for a View when it is duplicated
    *
    * @return string $title
    */
	public static function default_title( $title, $post ) {
        $copy_display = FrmProDisplaysHelper::get_current_view($post);
        if ( $copy_display ) {
            $title = $copy_display->post_title;
        }
        return $title;
    }

    /**
    *
    * Get the excerpt for a View when it is duplicated
    *
    * @return string $excerpt
    */
	public static function default_excerpt( $excerpt, $post ) {
        $copy_display = FrmProDisplaysHelper::get_current_view($post);
        if ( $copy_display ) {
            $excerpt = $copy_display->post_excerpt;
        }
        return $excerpt;
    }

	public static function add_meta_boxes( $post_type ) {
        if ( $post_type != self::$post_type ) {
            return;
        }

        add_meta_box('frm_form_disp_type', __( 'Basic Settings', 'formidable' ), 'FrmProDisplaysController::mb_form_disp_type', self::$post_type, 'normal', 'high');
        add_meta_box('frm_dyncontent', __( 'Content', 'formidable' ), 'FrmProDisplaysController::mb_dyncontent', self::$post_type, 'normal', 'high');
        add_meta_box('frm_excerpt', __( 'Description'), 'FrmProDisplaysController::mb_excerpt', self::$post_type, 'normal', 'high');
        add_meta_box('frm_advanced', __( 'Advanced Settings', 'formidable' ), 'FrmProDisplaysController::mb_advanced', self::$post_type, 'advanced');

        add_meta_box('frm_adv_info', __( 'Customization', 'formidable' ), 'FrmProDisplaysController::mb_adv_info', self::$post_type, 'side', 'low');
    }

	public static function save_post( $post_id ) {
        //Verify nonce
        if ( empty($_POST) || ( isset($_POST['frm_save_display']) && ! wp_verify_nonce($_POST['frm_save_display'], 'frm_save_display_nonce') ) || ! isset($_POST['post_type']) || $_POST['post_type'] != self::$post_type || ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) || ! current_user_can('edit_post', $post_id) ) {
            return;
        }

        $post = get_post($post_id);
		if ( $post->post_status == 'inherit' ) {
            return;
		}

        FrmProDisplay::update( $post_id, $_POST );
        do_action('frm_create_display', $post_id, $_POST);
    }

	public static function before_delete_post( $post_id ) {
        $post = get_post($post_id);
        if ( $post->post_type != self::$post_type ) {
            return;
        }

        global $wpdb;

        do_action('frm_destroy_display', $post_id);

		$used_by = FrmDb::get_col( $wpdb->postmeta, array( 'meta_key' => 'frm_display_id', 'meta_value' => $post_id ), 'post_ID' );
        if ( ! $used_by ) {
            return;
        }

        $form_id = get_post_meta($post_id, 'frm_form_id', true);

        $next_display = FrmProDisplay::get_auto_custom_display(compact('form_id'));
        if ( $next_display && $next_display->ID ) {
            $wpdb->update($wpdb->postmeta,
                array( 'meta_value' => $next_display->ID),
                array( 'meta_key' => 'frm_display_id',  'meta_value' => $post_id)
            );
        }else{
            $wpdb->delete($wpdb->postmeta, array( 'meta_key' => 'frm_display_id', 'meta_value' => $post_id));
        }
    }

	/**
	 * @since 2.0.8
	 */
	public static function delete_views_for_form( $form_id ) {
		$display_ids = FrmProDisplay::get_display_ids_by_form( $form_id );
		foreach ( $display_ids as $display_id ) {
			wp_delete_post( $display_id );
		}
	}

    /* META BOXES */
	public static function mb_dyncontent( $post ) {
        FrmProDisplaysHelper::prepare_duplicate_view($post);

        $editor_args = array();
		if ( $post->frm_no_rt ) {
            $editor_args['teeny'] = true;
            $editor_args['tinymce'] = false;
        }

        include(FrmAppHelper::plugin_path() .'/pro/classes/views/displays/mb_dyncontent.php');
    }

	public static function mb_excerpt( $post ) {
        include(FrmAppHelper::plugin_path() .'/pro/classes/views/displays/mb_excerpt.php');

        //add form nav via javascript
        $form = get_post_meta($post->ID, 'frm_form_id', true);
		if ( $form ) {
            echo '<div id="frm_nav_container" style="display:none;margin-top:-10px">';
            FrmAppController::get_form_nav($form, true, 'hide');
			echo '<div class="clear"></div>';
            echo '</div>';
        }
    }

	public static function mb_form_disp_type( $post ) {
        FrmProDisplaysHelper::prepare_duplicate_view($post);
        include(FrmAppHelper::plugin_path() .'/pro/classes/views/displays/mb_form_disp_type.php');
    }

	public static function mb_advanced( $post ) {
        FrmProDisplaysHelper::prepare_duplicate_view($post);
        include(FrmAppHelper::plugin_path() .'/pro/classes/views/displays/mb_advanced.php');
    }

	public static function mb_adv_info( $post ) {
        FrmProDisplaysHelper::prepare_duplicate_view($post);
        FrmFormsController::mb_tags_box($post->frm_form_id);
    }

	public static function get_tags_box() {
		FrmAppHelper::permission_check('frm_view_forms');
        check_ajax_referer( 'frm_ajax', 'nonce' );
        FrmFormsController::mb_tags_box( (int) $_POST['form_id'], 'frm_doing_ajax' );
        wp_die();
    }

    /* FRONT END */

	public static function get_content( $content ) {
        global $post;
        if ( ! $post ) {
            return $content;
        }

        $entry_id = false;
        $filter = apply_filters('frm_filter_auto_content', true);

        if ( $post->post_type == self::$post_type && in_the_loop() ) {
            global $frm_displayed;
            if ( ! $frm_displayed ) {
                $frm_displayed = array();
            }

			if ( in_array( $post->ID, $frm_displayed ) ) {
                return $content;
			}

            $frm_displayed[] = $post->ID;

            return self::get_display_data($post, $content, false, compact('filter'));
        }

        if ( is_singular() && post_password_required() ) {
            return $content;
        }

        $display_id = get_post_meta($post->ID, 'frm_display_id', true);

        if ( ! $display_id || ( ! is_single() && ! is_page() ) ) {
            return $content;
        }

        $display = FrmProDisplay::getOne($display_id);
        if ( ! $display ) {
            return $content;
        }

        global $frm_displayed;

        if ( $post->post_type != self::$post_type ) {
            $display = FrmProDisplaysHelper::setup_edit_vars($display, false);
        }

        if ( ! $frm_displayed ) {
            $frm_displayed = array();
        }

        //make sure this isn't loaded multiple times but still works with themes and plugins that call the_content multiple times
        if ( ! in_the_loop() || in_array($display->ID, (array) $frm_displayed) ) {
            return $content;
        }

        global $wpdb;

        //get the entry linked to this post
        if ( ( is_single() || is_page() ) && $post->post_type != self::$post_type ) {

            $entry = FrmDb::get_row( 'frm_items', array( 'post_id' => $post->ID ), 'id, item_key' );
            if ( ! $entry ) {
                return $content;
            }

            $entry_id = $entry->id;

            if ( in_array($display->frm_show_count, array( 'dynamic', 'calendar')) && $display->frm_type == 'display_key' ) {
                $entry_id = $entry->item_key;
            }
        }

        $frm_displayed[] = $display->ID;
        $content = self::get_display_data($display, $content, $entry_id, array(
            'filter' => $filter, 'auto_id' => $entry_id,
        ) );

        return $content;
    }

	public static function get_order_row() {
		FrmAppHelper::permission_check('frm_edit_displays');
        check_ajax_referer( 'frm_ajax', 'nonce' );
		self::add_order_row( absint( $_POST['order_key'] ), absint( $_POST['form_id'] ) );
        wp_die();
    }

    public static function add_order_row( $order_key = '', $form_id = '', $order_by = '', $order = '' ) {
        $order_key = (int) $order_key;
        require(FrmAppHelper::plugin_path() .'/pro/classes/views/displays/order_row.php');
    }

	public static function get_where_row() {
		FrmAppHelper::permission_check('frm_edit_displays');
        check_ajax_referer( 'frm_ajax', 'nonce' );
        self::add_where_row( absint( $_POST['where_key'] ), absint( $_POST['form_id'] ) );
        wp_die();
    }

    public static function add_where_row( $where_key = '', $form_id = '', $where_field = '', $where_is = '', $where_val = '' ) {
        $where_key = (int) $where_key;
        require(FrmAppHelper::plugin_path() .'/pro/classes/views/displays/where_row.php');
    }

	public static function get_where_options() {
		FrmAppHelper::permission_check('frm_edit_displays');
        check_ajax_referer( 'frm_ajax', 'nonce' );
		self::add_where_options( absint( $_POST['field_id'] ), absint( $_POST['where_key'] ) );
        wp_die();
    }

    public static function add_where_options( $field_id, $where_key, $where_val = '' ) {
        if ( is_numeric($field_id) ) {
            $field = FrmField::getOne($field_id);
        }

        require(FrmAppHelper::plugin_path() .'/pro/classes/views/displays/where_options.php');
    }

	public static function calendar_header( $content, $display, $show = 'one' ) {
        if ( $display->frm_show_count != 'calendar' || $show == 'one' ) {
            return $content;
        }

        global $frm_vars, $wp_locale;
        $frm_vars['load_css'] = true;

		//4 digit year
		$year = FrmAppHelper::get_param( 'frmcal-year', date_i18n( 'Y' ), 'get', 'absint' );

		//Numeric month with leading zeros
		$month = FrmAppHelper::get_param( 'frmcal-month', date_i18n( 'm' ), 'get', 'sanitize_title' );

        $month_names = $wp_locale->month;

        $this_time = strtotime($year .'-'. $month .'-01');
        $prev_month = date('m', strtotime('-1 month', $this_time));
        $prev_year = date('Y', strtotime('-1 month', $this_time));

        $next_month = date('m', strtotime('+1 month', $this_time));
        $next_year = date('Y', strtotime('+1 month', $this_time));

        ob_start();
        include(FrmAppHelper::plugin_path() .'/pro/classes/views/displays/calendar-header.php');
        $content .= ob_get_contents();
        ob_end_clean();
        return $content;
    }

    /**
     * @return string
     */
	public static function build_calendar( $new_content, $entry_ids, $shortcodes, $display, $show = 'one' ) {
        if ( ! $display || $display->frm_show_count != 'calendar' || $show == 'one') {
            return $new_content;
        }

        global $wp_locale;

        $current_year = date_i18n('Y');
        $current_month = date_i18n('m');

		//4 digit year
		$year = FrmAppHelper::get_param( 'frmcal-year', date( 'Y' ), 'get', 'absint' );

		//Numeric month with leading zeros
		$month = FrmAppHelper::get_param( 'frmcal-month', $current_month, 'get', 'sanitize_title' );

        $timestamp = mktime(0, 0, 0, $month, 1, $year);
        $maxday = date('t', $timestamp); //Number of days in the given month
        $this_month = getdate($timestamp);
        $startday = $this_month['wday'];
        unset($this_month);

        // week_begins = 0 stands for Sunday
		$week_begins = apply_filters( 'frm_cal_week_begins', absint( get_option( 'start_of_week' ) ), $display );
    	if ( $week_begins > $startday ) {
            $startday = $startday + 7;
        }

        $week_ends = 6 + (int) $week_begins;
        if ( $week_ends > 6 ) {
            $week_ends = (int) $week_ends - 7;
        }

        $efield = $field = false;
        if ( is_numeric($display->frm_date_field_id) ) {
            $field = FrmField::getOne($display->frm_date_field_id);
        }

        if ( is_numeric($display->frm_edate_field_id) ) {
            $efield = FrmField::getOne($display->frm_edate_field_id);
        }

        $daily_entries = array();
		while ( $next_set = array_splice( $entry_ids, 0, 30 ) ) {
			$entries = FrmEntry::getAll( array( 'id' => $next_set ), ' ORDER BY FIELD(it.id,' . implode( ',', $next_set ) . ')', '', true, false );
			foreach ( $entries as $entry ) {
				self::calendar_daily_entries( $entry, $display, compact('startday', 'maxday', 'year', 'month', 'field', 'efield'), $daily_entries );
			}
		}

		$locale_day_names = apply_filters( 'frm_calendar_day_names', 'weekday_abbrev', array( 'display' => $display ) );
		$day_names = FrmProAppHelper::reset_keys($wp_locale->{$locale_day_names}); //switch keys to order

        if ( $week_begins ) {
            for ( $i = $week_begins; $i < ( $week_begins + 7 ); $i++ ) {
                if ( ! isset($day_names[$i]) ) {
                    $day_names[$i] = $day_names[$i - 7];
                }
            }
            unset($i);
        }

        if ( $current_year == $year && $current_month == $month ) {
            $today = date_i18n('j');
        }

        $used_entries = array();

        ob_start();
        include(FrmAppHelper::plugin_path() .'/pro/classes/views/displays/calendar.php');
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

	private static function calendar_daily_entries( $entry, $display, $args, array &$daily_entries ) {
        $i18n = false;

        if ( is_numeric($display->frm_date_field_id) ) {
			$date = FrmEntryMeta::get_meta_value( $entry, $display->frm_date_field_id );

            if ( $entry->post_id && ! $date && $args['field'] &&
                isset($args['field']->field_options['post_field']) && $args['field']->field_options['post_field'] ) {

                $date = FrmProEntryMetaHelper::get_post_value($entry->post_id, $args['field']->field_options['post_field'], $args['field']->field_options['custom_field'], array(
                    'form_id' => $display->frm_form_id, 'type' => $args['field']->type,
                    'field' => $args['field']
                ) );

            }
        } else {
			$date = $display->frm_date_field_id == 'updated_at' ? $entry->updated_at : $entry->created_at;
            $i18n = true;
        }

        if ( empty($date) ) {
            return;
        }

        if ( $i18n ) {
			$date = FrmAppHelper::get_localized_date( 'Y-m-d', $date );
        } else {
            $date = date('Y-m-d', strtotime($date));
        }

        unset($i18n);
        $dates = array($date);

        if ( ! empty($display->frm_edate_field_id) ) {
            if ( is_numeric($display->frm_edate_field_id) && $args['efield'] ) {
                $edate = FrmProEntryMetaHelper::get_post_or_meta_value($entry, $args['efield']);

                if ( $args['efield'] && $args['efield']->type == 'number' && is_numeric($edate) ) {
                    $edate = date('Y-m-d', strtotime('+'. ($edate - 1) .' days', strtotime($date)));
                }
            } else if ( $display->frm_edate_field_id == 'updated_at' ) {
				$edate = FrmAppHelper::get_localized_date( 'Y-m-d', $entry->updated_at );
            } else {
				$edate = FrmAppHelper::get_localized_date( 'Y-m-d', $entry->created_at );
            }

            if ( $edate && ! empty($edate) ) {
                $from_date = strtotime($date);
                $to_date = strtotime($edate);

                if ( ! empty($from_date) && $from_date < $to_date ) {
                    for ( $current_ts = $from_date; $current_ts <= $to_date; $current_ts += (60*60*24) ) {
                        $dates[] = date('Y-m-d', $current_ts);
                    }
                    unset($current_ts);
                }

                unset($from_date, $to_date);
            }
            unset($edate);
        }
        unset($date);

        self::get_repeating_dates($entry, $display, $args, $dates);

        $dates = apply_filters('frm_show_entry_dates', $dates, $entry);

        for ( $i=0; $i < ( $args['maxday'] + $args['startday'] ); $i++ ) {
            $day = $i - $args['startday'] + 1;

            if ( in_array(date('Y-m-d', strtotime($args['year'] .'-'. $args['month'] .'-'. $day)), $dates) ) {
                $daily_entries[$i][] = $entry;
            }

            unset($day);
        }
    }

	private static function get_repeating_dates( $entry, $display, $args, array &$dates ) {
        if ( ! is_numeric($display->frm_repeat_event_field_id) ) {
            return;
        }

        //Get meta values for repeat field and end repeat field
        if ( isset($entry->metas[$display->frm_repeat_event_field_id]) ) {
            $repeat_period = $entry->metas[$display->frm_repeat_event_field_id];
        } else {
            $repeat_field = FrmField::getOne($display->frm_repeat_event_field_id);
            $repeat_period = FrmProEntryMetaHelper::get_post_or_meta_value($entry->id, $repeat_field);
            unset($repeat_field);
        }

        if ( isset($entry->metas[$display->frm_repeat_edate_field_id]) ) {
            $stop_repeat = $entry->metas[$display->frm_repeat_edate_field_id];
        } else {
            $stop_field = FrmField::getOne($display->frm_repeat_edate_field_id);
            $stop_repeat = FrmProEntryMetaHelper::get_post_or_meta_value($entry->id, $stop_field);
            unset($stop_field);
        }

		//If site is not set to English, convert day(s), week(s), month(s), and year(s) (in repeat_period string) to English
		//Check for a few common repeat periods like daily, weekly, monthly, and yearly as well
		$t_strings = array(__( 'day', 'formidable' ), __( 'days', 'formidable' ), __( 'daily', 'formidable' ),__( 'week', 'formidable' ), __( 'weeks', 'formidable' ), __( 'weekly', 'formidable' ), __( 'month', 'formidable' ), __( 'months', 'formidable' ), __( 'monthly', 'formidable' ), __( 'year', 'formidable' ), __( 'years', 'formidable' ), __( 'yearly', 'formidable' ));
		$t_strings = apply_filters('frm_recurring_strings', $t_strings, $display);
		$e_strings = array( 'day', 'days', '1 day', 'week', 'weeks', '1 week', 'month', 'months', '1 month', 'year', 'years', '1 year');
		if ( $t_strings != $e_strings ) {
			$repeat_period = str_ireplace($t_strings, $e_strings, $repeat_period);
		}
		unset($t_strings, $e_strings);

		//Switch [frmcal-date] for current calendar date (for use in "Third Wednesday of [frmcal-date]")
		$repeat_period = str_replace('[frmcal-date]', $args['year'] . '-' . $args['month'] . '-01', $repeat_period);

		//Filter for repeat_period
		$repeat_period = apply_filters('frm_repeat_period', $repeat_period, $display);

		//If repeat period is set and is valid
		if ( empty($repeat_period) || ! is_numeric(strtotime($repeat_period)) ) {
		    return;
		}

		//Set up end date to minimize dates array - allow for no end repeat field set, nothing selected for end, or any date

		if ( ! empty($stop_repeat) ) {
		    //If field is selected for recurring end date and the date is not empty
			$maybe_stop_repeat = strtotime($stop_repeat);
		}

		//Repeat until next viewable month
		$cal_date = $args['year'] . '-' . $args['month'] . '-01';
		$stop_repeat = strtotime('+1 month', strtotime($cal_date));

		//If the repeat should end before $stop_repeat (+1 month), use $maybe_stop_repeat
		if ( isset($maybe_stop_repeat) && $maybe_stop_repeat < $stop_repeat ) {
		    $stop_repeat = $maybe_stop_repeat;
		    unset($maybe_stop_repeat);
		}

		$temp_dates = array();

		foreach ( $dates as $d ) {
			$last_i = 0;
			for ($i = strtotime($d); $i <= $stop_repeat; $i = strtotime($repeat_period, $i)) {
				//Break endless loop
				if ( $i == $last_i ) {
					break;
				}
				$last_i = $i;

				//Add to dates array
				$temp_dates[] = date('Y-m-d', $i);
			}
			unset($last_i, $d);
		}
		$dates = $temp_dates;
    }

	public static function calendar_footer( $content, $display, $show = 'one' ) {
        if ( $display->frm_show_count != 'calendar' || $show == 'one' ) {
            return $content;
        }

        ob_start();
        include(FrmAppHelper::plugin_path() .'/pro/classes/views/displays/calendar-footer.php');
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

	public static function get_date_field_select() {
		FrmAppHelper::permission_check('frm_edit_displays');
        check_ajax_referer( 'frm_ajax', 'nonce' );

		if ( is_numeric($_POST['form_id']) ) {
		    $post = new stdClass();
		    $post->frm_form_id = (int) $_POST['form_id'];
		    $post->frm_edate_field_id = $post->frm_date_field_id = '';
		    $post->frm_repeat_event_field_id = $post->frm_repeat_edate_field_id = '';
		    include(FrmAppHelper::plugin_path() .'/pro/classes/views/displays/_calendar_options.php');
		}

        wp_die();
    }

    /* Shortcodes */
	public static function get_shortcode( $atts ) {
        $defaults = array(
            'id' => '', 'entry_id' => '', 'filter' => false,
            'user_id' => false, 'limit' => '', 'page_size' => '',
            'order_by' => '', 'order' => '', 'get' => '', 'get_value' => '',
            'drafts' => false,
        );

        $sc_atts = shortcode_atts($defaults, $atts);
		$atts = array_merge( (array) $atts, (array) $sc_atts );

        $display = FrmProDisplay::getOne($atts['id'], false, true);
        $user_id = FrmAppHelper::get_user_id_param($atts['user_id']);

        if ( ! empty($atts['get']) ) {
            $_GET[$atts['get']] = urlencode($atts['get_value']);
        }

        $get_atts = $atts;
        foreach ( $defaults as $unset => $val ) {
            unset($get_atts[$unset], $unset, $val);
        }

        foreach ( $get_atts as $att => $val ) {
            $_GET[$att] = urlencode($val);
            unset($att, $val);
        }

        if ( ! $display ) {
            return __( 'There are no views with that ID', 'formidable' );
        }

        return self::get_display_data($display, '', $atts['entry_id'], array(
            'filter' => $atts['filter'], 'user_id' => $user_id,
            'limit' => $atts['limit'], 'page_size' => $atts['page_size'],
            'order_by' => $atts['order_by'], 'order' => $atts['order'],
            'drafts' => $atts['drafts'],
        ) );
    }

	public static function custom_display( $id ) {
        if ($display = FrmProDisplay::getOne($id, false, false, array( 'check_post' => true)))
            return self::get_display_data($display);
    }

    public static function get_display_data( $display, $content = '', $entry_id = false, $extra_atts = array() ) {
		if ( post_password_required( $display ) ) {
			return get_the_password_form( $display );
		}

        add_action('frm_load_view_hooks', 'FrmProDisplaysController::trigger_load_view_hooks');
        FrmAppHelper::trigger_hook_load( 'view', $display );

        global $frm_vars, $post;

        $frm_vars['forms_loaded'][] = true;

        if ( ! isset($display->frm_empty_msg) ) {
            $display = FrmProDisplaysHelper::setup_edit_vars($display, false);
        }

        if ( ! isset($display->frm_form_id) || empty($display->frm_form_id) ) {
            return $content;
        }

        //for backwards compatability
        $display->id = $display->frm_old_id;
        $display->display_key = $display->post_name;

        $defaults = array(
        	'filter' => false, 'user_id' => '', 'limit' => '',
        	'page_size' => '', 'order_by' => '', 'order' => '',
        	'drafts' => false, 'auto_id' => '',
        );

        $extra_atts = wp_parse_args( $extra_atts, $defaults );
        extract($extra_atts);

        //if (FrmProAppHelper::rewriting_on() && $frmpro_settings->permalinks )
        //    self::parse_pretty_entry_url();

        if ( $display->frm_show_count == 'one' && is_numeric($display->frm_entry_id) && $display->frm_entry_id > 0 && ! $entry_id ) {
            $entry_id = $display->frm_entry_id;
        }

        $entry = false;
        $show = 'all';

		// Don't filter with $entry_ids by default because the query gets too long.
		// Only filter with $entry_ids when showing one entry
		$use_ids = false;

        global $wpdb;

		$where = array( 'it.form_id' => $display->frm_form_id );

		if ( in_array( $display->frm_show_count, array( 'dynamic', 'calendar', 'one' ) ) ) {
			$one_param = FrmAppHelper::simple_get( 'entry', 'sanitize_title', $extra_atts['auto_id'] );
			$get_param = FrmAppHelper::simple_get( $display->frm_param, 'sanitize_title', ( $display->frm_show_count == 'one' ? $one_param : $extra_atts['auto_id'] ) );
			unset($one_param);

			if ( $get_param ) {
                if ( ($display->frm_type == 'id' || $display->frm_show_count == 'one') && is_numeric($get_param) ) {
					$where['it.id'] = $get_param;

                } else {
					$where['it.item_key'] = $get_param;
                }

				$entry = FrmEntry::getAll( $where, '', 1, 0 );
				if ( $entry ) {
					$entry = reset( $entry );
				}

                if ( $entry && $entry->post_id ) {
                    //redirect to single post page if this entry is a post
                    if ( in_the_loop() && $display->frm_show_count != 'one' && ! is_single($entry->post_id) && $post->ID != $entry->post_id ) {
                        $this_post = get_post($entry->post_id);
                        if ( in_array($this_post->post_status, array( 'publish', 'private')) ) {
                            die(FrmAppHelper::js_redirect(get_permalink($entry->post_id)));
                        }
                    }
                }
            }
            unset($get_param);
        }

        if ( $entry && in_array($display->frm_show_count, array( 'dynamic', 'calendar')) ) {
            $new_content = $display->frm_dyncontent;
            $show = 'one';
        }else{
            $new_content = $display->post_content;
        }

        $show = ($display->frm_show_count == 'one') ? 'one' : $show;
        $shortcodes = FrmProDisplaysHelper::get_shortcodes($new_content, $display->frm_form_id);

        //don't let page size and limit override single entry displays
		if ( $display->frm_show_count == 'one' ) {
            $display->frm_page_size = $display->frm_limit = '';
		}

        $pagination = '';

        $form_query = array( 'form_id' => $display->frm_form_id, 'post_id >' => 1 );

        if ( $extra_atts['drafts'] != 'both' ) {
			$is_draft = empty( $extra_atts['drafts'] ) ? 0 : 1;
			$form_query['is_draft'] = $is_draft;
		} else {
			$is_draft = 'both';
		}

		if ( $entry && $entry->form_id == $display->frm_form_id ) {
			$form_query['id'] = $entry->id;
        }

		$form_posts = FrmDb::get_results( 'frm_items', $form_query, 'id, post_id' );
		unset( $form_query );

		$getting_entries = ( ! $entry || ! $post || empty( $extra_atts['auto_id'] ) );
		$check_filter_opts = ( ! empty( $display->frm_where ) && $getting_entries );

        if ( $entry && $entry->form_id == $display->frm_form_id ) {
            $entry_ids = array($entry->id);
			// Filter by this entry ID to make query faster
			$use_ids = true;
		} else if ( $check_filter_opts || isset( $_GET['frm_search'] ) ) {
            //Only get $entry_ids if filters are set or if frm_search parameter is set
			$entry_query = array( 'form_id' => $display->frm_form_id );

			if ( $extra_atts['drafts'] != 'both' ) {
				$entry_query['is_draft'] = $is_draft;
            }

			$entry_ids = FrmDb::get_col( 'frm_items', $entry_query );
			unset( $entry_query );
        }

		$empty_msg = ( isset($display->frm_empty_msg) && ! empty($display->frm_empty_msg) ) ? '<div class="frm_no_entries">' . FrmProFieldsHelper::get_default_value($display->frm_empty_msg, false) . '</div>' : '';
		$empty_msg = apply_filters( 'frm_no_entries_message', $empty_msg, array( 'display' => $display ) );

        if ( isset( $message ) ) {
            // if an entry was deleted above, show a message
            $empty_msg = $message . $empty_msg;
        }

        $after_where = false;

        $user_id = $extra_atts['user_id'];
        if ( ! empty($user_id) ) {
            $user_id = FrmAppHelper::get_user_id_param($user_id);
            $uid_used = false;
        }

		self::add_group_by_filter( $display, $getting_entries );
		unset( $getting_entries );

		if ( $check_filter_opts ) {
			$display->frm_where = apply_filters( 'frm_custom_where_opt', $display->frm_where, array( 'display' => $display, 'entry' => $entry ) );
                $continue = false;
				foreach ( $display->frm_where as $where_key => $where_opt ) {
                    $where_val = isset($display->frm_where_val[$where_key]) ? $display->frm_where_val[$where_key] : '';

					if ( preg_match("/\[(get|get-(.?))\b(.*?)(?:(\/))?\]/s", $where_val ) ) {
                        $where_val = FrmProFieldsHelper::get_default_value($where_val, false, true, true);
                        //if this param doesn't exist, then don't include it
						if ( $where_val == '' ) {
                            if ( ! $after_where ) {
                                $continue = true;
                            }

                            continue;
                        }
                    }else{
                        $where_val = FrmProFieldsHelper::get_default_value($where_val, false, true, true);
                    }

                    $continue = false;

					if ( $where_val == 'current_user' ) {
						if ( $user_id && is_numeric( $user_id ) ) {
                            $where_val = $user_id;
                            $uid_used = true;
						} else {
                            $where_val = get_current_user_id();
                        }
					} else if ( ! is_array( $where_val ) ) {
                        $where_val = do_shortcode($where_val);
                    }

                    if ( in_array( $where_opt, array( 'id', 'item_key', 'post_id') ) && ! is_array( $where_val ) && strpos( $where_val, ',' ) ) {
                        $where_val = explode(',', $where_val);
						$where_val = array_filter( $where_val );
                    }

                    if ( is_array($where_val) && ! empty($where_val) ) {
                        if ( strpos($display->frm_where_is[$where_key], '!') === false && strpos($display->frm_where_is[$where_key], 'not') === false ) {
                            $display->frm_where_is[$where_key] = ' in ';
                        } else {
                            $display->frm_where_is[$where_key] = 'not in';
                        }
                    }

					if ( is_numeric( $where_opt ) ) {
                        $filter_opts = apply_filters('frm_display_filter_opt', array(
                            'where_opt' => $where_opt, 'where_is' => $display->frm_where_is[$where_key],
                            'where_val' => $where_val, 'form_id' => $display->frm_form_id, 'form_posts' => $form_posts,
							'after_where' => $after_where, 'display' => $display, 'drafts' => $is_draft,
							'use_ids' => $use_ids,
						));

						$entry_ids = FrmProAppHelper::filter_where($entry_ids, $filter_opts);

                        unset($filter_opts);
                        $after_where = true;
                        $continue = false;

                        if ( empty( $entry_ids ) ) {
                            break;
						}
                    } else if ( in_array($where_opt, array( 'created_at', 'updated_at')) ) {
                        if ( $where_val == 'NOW' ) {
                            $where_val = current_time('mysql', 1);
                        }

                        if ( strpos($display->frm_where_is[$where_key], 'LIKE') === false ) {
                            $where_val = date('Y-m-d H:i:s', strtotime($where_val));

							// If using less than or equal to, set the time to the end of the day
							if ( $display->frm_where_is[ $where_key ] == '<=' ) {
								$where_val = str_replace( '00:00:00', '23:59:59', $where_val );
							}

							// Convert date to GMT since that is the format in the DB
							$where_val = get_gmt_from_date( $where_val );
                        }

						$where[ 'it.' . sanitize_title( $where_opt ) . FrmDb::append_where_is( $display->frm_where_is[$where_key] ) ] = $where_val;

                        $continue = true;
					} else if ( in_array($where_opt, array( 'id', 'item_key', 'post_id', 'ip', 'parent_item_id' ) ) ) {
						$where[ 'it.' . sanitize_title( $where_opt ) . FrmDb::append_where_is( $display->frm_where_is[$where_key] ) ] = $where_val;

						// Update entry IDs if the entry ID filter is set to "equal to"
						if ( $where_opt == 'id' && in_array( $display->frm_where_is[$where_key], array( '=', ' in ' ) ) ) {
							$entry_ids = $where_val;
						}

                        $continue = true;
                    }

                }

                if ( ! $continue && empty($entry_ids) ) {
					
					if ( $filter ) {
						$empty_msg = apply_filters( 'the_content', $empty_msg );
					}

					if ( $post && $post->post_type == self::$post_type && in_the_loop() ) {
						$content = '';
					}

					$content .= $empty_msg;

                    return $content;
                }
            }

            if ( $user_id && is_numeric( $user_id ) && ! $uid_used ) {
				$where['it.user_id'] = $user_id;
            }

			$s = FrmAppHelper::get_param( 'frm_search', false, 'get', 'sanitize_text_field' );
			if ( $s ) {
                $new_ids = FrmProEntriesHelper::get_search_ids( $s, $display->frm_form_id, array( 'is_draft' => $extra_atts['drafts'] ) );

                if ( $after_where && isset($entry_ids) && ! empty($entry_ids) ) {
                    $entry_ids = array_intersect($new_ids, $entry_ids);
                } else {
                    $entry_ids = $new_ids;
                }

				if ( empty( $entry_ids ) ) {
                    if ( $post->post_type == self::$post_type && in_the_loop() ) {
                        $content = '';
                    }

                    return $content . ' '. $empty_msg;
                }
            }

            if ( isset( $entry_ids ) && ! empty( $entry_ids ) ) {
				$where['it.id'] = $entry_ids;
            }

			self::maybe_add_entry_query( $entry_id, $where );

            if ( $extra_atts['drafts'] != 'both' ) {
				$where['is_draft'] = $is_draft;
    		}
    		unset($is_draft);

			if ( $show == 'one' ) {
				$limit = ' LIMIT 1';
			} else {
				self::maybe_add_cat_query( $where );
			}

			if ( ! empty($limit) && is_numeric($limit) ) {
                $display->frm_limit = (int) $limit;
            }

			if ( is_numeric($display->frm_limit) ) {
                $num_limit = (int) $display->frm_limit;
                $limit = ' LIMIT '. $display->frm_limit;
			}

			if ( ! empty( $order_by ) ) {
            	$display->frm_order_by = explode(',', $order_by);
			}

            if ( ! empty( $order ) ) {
				$display->frm_order = explode( ',', $order );
				if ( ! isset( $display->frm_order_by[0] ) ) {
					$display->frm_order_by = FrmProAppHelper::reset_keys( $display->frm_order_by );
				}
			}

			unset($order);


            if ( ! empty( $page_size ) && is_numeric( $page_size ) ) {
                $display->frm_page_size = (int) $page_size;
            }

            // if limit is lower than page size, ignore the page size
            if ( isset($num_limit) && $display->frm_page_size > $num_limit ) {
                $display->frm_page_size = '';
            }

			$display_page_query = array(
				'order_by_array' => $display->frm_order_by, 'order_array' => $display->frm_order,
				'posts' => $form_posts, 'display' => $display,
			);
            if ( isset($display->frm_page_size) && is_numeric($display->frm_page_size) ) {
                $page_param = ( $_GET && isset($_GET['frm-page-'. $display->ID]) ) ? 'frm-page-'. $display->ID : 'frm-page';
				$current_page = FrmAppHelper::simple_get( $page_param, 'absint', 1 );

                $record_count = FrmEntry::getRecordCount( $where );
                if ( isset($num_limit) && ( $record_count > (int) $num_limit ) ) {
                    $record_count = (int) $num_limit;
                }

                $page_count = FrmEntry::getPageCount($display->frm_page_size, $record_count);
				$entry_ids = FrmProEntry::get_view_page( $current_page, $display->frm_page_size, $where, $display_page_query );

                $page_last_record = FrmAppHelper::get_last_record_num( $record_count, $current_page, $display->frm_page_size );
                $page_first_record = FrmAppHelper::get_first_record_num( $record_count, $current_page, $display->frm_page_size );
				if ( $page_count > 1 ) {
                    $page_param = 'frm-page-'. $display->ID;
                    $pagination = FrmAppHelper::get_file_contents(FrmAppHelper::plugin_path() .'/pro/classes/views/displays/pagination.php', compact('current_page', 'record_count', 'page_count', 'page_last_record', 'page_first_record', 'page_param'));
                }
            }else{
				$display_page_query['limit'] = $limit;
				//Get all entries
				$entry_ids = FrmProEntry::get_view_results( $where, $display_page_query );
            }

			$total_count = count( $entry_ids );
            $sc_atts = array();
			if ( isset( $record_count ) ) {
                $sc_atts['record_count'] = $record_count;
			} else {
                $sc_atts['record_count'] = $total_count;
			}

            $display_content = '';
            if ( isset( $message ) ) {
                // if an entry was deleted above, show a message
                $display_content .= $message;
            }

			if ( $show == 'all' ) {
                $display_content .= isset($display->frm_before_content) ? $display->frm_before_content : '';
			}

            add_filter('frm_before_display_content', 'FrmProDisplaysController::calendar_header', 10, 3);
            add_filter('frm_before_display_content', 'FrmProDisplaysController::filter_after_content', 10, 4);

            $display_content = apply_filters('frm_before_display_content', $display_content, $display, $show, array( 'total_count' => $total_count, 'record_count' => $sc_atts['record_count'], 'entry_ids' => $entry_ids));

            add_filter('frm_display_entries_content', 'FrmProDisplaysController::build_calendar', 10, 5);
			$filtered_content = apply_filters( 'frm_display_entries_content', $new_content, $entry_ids, $shortcodes, $display, $show, $sc_atts );

			if ( $filtered_content != $new_content ) {
                $display_content .= $filtered_content;
			} else {
                $odd = 'odd';
                $count = 0;
				if ( ! empty( $entry_ids ) ) {
					$loop_entry_ids = $entry_ids;
					while ( $next_set = array_splice( $loop_entry_ids, 0, 30 ) ) {
						$entries = FrmEntry::getAll( array( 'id' => $next_set ), ' ORDER BY FIELD(it.id,' . implode( ',', $next_set ) . ')', '', true, false );
						foreach ( $entries as $entry ) {
							$count++; //TODO: use the count with conditionals
							$display_content .= apply_filters( 'frm_display_entry_content', $new_content, $entry, $shortcodes, $display, $show, $odd, array( 'count' => $count, 'total_count' => $total_count, 'record_count' => $sc_atts['record_count'], 'pagination' => $pagination, 'entry_ids' => $entry_ids ) );
							$odd = ( $odd == 'odd' ) ? 'even' : 'odd';
							unset( $entry );
						}
						unset( $entries );
					}
					unset( $loop_entry_ids, $count );
                }else{
                    if ( $post && $post->post_type == self::$post_type && in_the_loop() ) {
                        $display_content = '';
                    }

					if ( ! isset( $message ) || FrmAppHelper::get_param( 'frm_action', '', 'get', 'sanitize_title' ) != 'destroy' ) {
                        $display_content .= $empty_msg;
                    }
                }
            }

        if ( isset( $message ) ) {
            unset( $message );
        }

        if ( $show == 'all' && isset($display->frm_after_content) ) {
            add_filter('frm_after_content', 'FrmProDisplaysController::filter_after_content', 10, 4);
            $display_content .= apply_filters('frm_after_content', $display->frm_after_content, $display, $show, array( 'total_count' => $total_count, 'record_count' => $sc_atts['record_count'], 'entry_ids' => $entry_ids));
        }

        if ( ! isset($sc_atts) ) {
            $sc_atts = array( 'record_count' => 0);
        }

        if ( ! isset($total_count) ) {
            $total_count = 0;
        }

        $pagination = self::calendar_footer($pagination, $display, $show);
        $display_content .= apply_filters('frm_after_display_content', $pagination, $display, $show, array( 'total_count' => $total_count, 'record_count' => $sc_atts['record_count'], 'entry_ids' => $entry_ids ));
        unset($sc_atts);
        $display_content = FrmProFieldsHelper::get_default_value($display_content, false, true, false);

		if ( $filter ) {
			$display_content = apply_filters( 'the_content', $display_content );
		}
		$content = $display_content;

        // load the styling for css classes and pagination
        FrmStylesController::enqueue_style();

        return $content;
    }

	/**
	 * @param string $entry_id
	 * @param array $where
	 * @since 2.0.6
	 */
	private static function maybe_add_entry_query( $entry_id, &$where ) {
		if ( empty( $entry_id ) ) {
			return;
		}

		$entry_id_array = explode( ',', $entry_id );

		//Get IDs (if there are any)
		$numeric_entry_ids = array_filter( $entry_id_array, 'is_numeric' );

		//If there are entry keys, use esc_sql
		if ( empty( $numeric_entry_ids ) ) {
			$entry_id_array = array_filter( $entry_id_array, 'esc_sql' );
		}

		if ( empty( $numeric_entry_ids ) ) {
			$where['it.item_key'] = $entry_id_array;
		} else {
			$where['it.id'] = $numeric_entry_ids;
		}
	}

	/**
	 * Get fields with specified field value 'frm_cat' = field key/id,
	 * 'frm_cat_id' = order position of selected option
	 * @since 2.0.6
	 */
	private static function maybe_add_cat_query( &$where ) {
		$frm_cat = FrmAppHelper::simple_get( 'frm_cat', 'sanitize_title' );
		$frm_cat_id = FrmAppHelper::simple_get( 'frm_cat_id', 'sanitize_title' );

		if ( ! $frm_cat || ! isset( $_GET['frm_cat_id'] ) ) {
			return;
		}

		$cat_field = FrmField::getOne( $frm_cat );
		if ( ! $cat_field ) {
			return;
		}

		$categories = maybe_unserialize( $cat_field->options );

		if ( isset( $categories[ $frm_cat_id ] ) ) {
			$cat_entry_ids = FrmEntryMeta::getEntryIds( array( 'meta_value' => $categories[ $frm_cat_id ], 'fi.field_key' => $frm_cat ) );
			if ( $cat_entry_ids ) {
				$where['it.id'] = $cat_entry_ids;
			} else {
				$where['it.id'] = 0;
			}
		}
	}

	/**
	 * Change the group by options to "Field name != _____"
	 */
	private static function add_group_by_filter( &$display, $getting_entries ) {
		if ( empty( $display->frm_where_is ) || ! $getting_entries ) {
			return;
		}

		if ( ! is_array( $display->frm_group_by ) ) {
			$display->frm_group_by = (array) $display->frm_group_by;
		}

		foreach ( $display->frm_where_is as $k => $where_is ) {
			if ( $where_is != 'group_by' ) {
				continue;
			}

			// Add the the frm_group_by array for later use
			$display->frm_group_by[] = $display->frm_where[ $k ];

			// Change the query to != ''
			$display->frm_where_is[ $k ] = '!=';
			$display->frm_where_val[ $k ] = '';
		}
	}

	/*
	public static function parse_pretty_entry_url() {
        global $wpdb, $post;

        $post_url = get_permalink($post->ID);
        $request_uri = FrmProAppHelper::current_url();

        $match_str = '#^'.$post_url.'(.*?)([\?/].*?)?$#';

		if ( preg_match( $match_str, $request_uri, $match_val ) ) {
            // match short slugs (most common)
            if ( isset($match_val[1]) && ! empty($match_val[1]) && FrmEntry::exists($match_val[1]) ) {
                // Artificially set the GET variable
                $_GET['entry'] = $match_val[1];
            }
        }
    }
	*/

	public static function get_pagination_file( $filename, $atts ) {
        _deprecated_function( __FUNCTION__, '2.0', 'FrmAppHelper::get_file_contents' );
        return FrmAppHelper::get_file_contents($filename, $atts);
    }

	public static function filter_after_content( $content, $display, $show, $atts ) {
        $content = str_replace('[entry_count]', $atts['record_count'], $content);
        return $content;
    }

    public static function get_post_content() {
		FrmAppHelper::permission_check('frm_edit_forms');
        check_ajax_referer( 'frm_ajax', 'nonce' );

		$id = absint( $_POST['id'] );

        $display = FrmProDisplay::getOne( $id, false, true );
        if ( 'one' == $display->frm_show_count ) {
            echo $display->post_content;
        } else {
            echo $display->frm_dyncontent;
        }

        wp_die();
    }
}
