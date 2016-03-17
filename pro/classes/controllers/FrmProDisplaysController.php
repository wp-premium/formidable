<?php

class FrmProDisplaysController {
	public static $post_type = 'frm_display';

	public static function trigger_load_view_hooks() {
		FrmHooksController::trigger_load_hook( 'load_view_hooks' );
	}

	public static function register_post_types() {
		register_post_type( self::$post_type, array(
			'label' => __( 'Views', 'formidable' ),
			'description' => '',
			'public' => apply_filters( 'frm_public_views', true ),
			'show_ui' => true,
			'exclude_from_search' => true,
			'show_in_nav_menus' => false,
			'show_in_menu' => false,
			'menu_icon' => admin_url( 'images/icons32.png' ),
			'capability_type' => 'page',
			'capabilities' => array(
				'edit_post' => 'frm_edit_displays',
				'edit_posts' => 'frm_edit_displays',
				'edit_others_posts' => 'frm_edit_displays',
				'publish_posts' => 'frm_edit_displays',
				'delete_post' => 'frm_edit_displays',
				'delete_posts' => 'frm_edit_displays',
				'read_post' => 'frm_edit_displays', // Needed to view revisions
			),
			'supports' => array(
				'title', 'revisions',
			),
			'has_archive' => false,
			'labels' => array(
				'name' => __( 'Views', 'formidable' ),
				'singular_name' => __( 'View', 'formidable' ),
				'menu_name' => __( 'View', 'formidable' ),
				'edit' => __( 'Edit' ),
				'search_items' => __( 'Search Views', 'formidable' ),
				'not_found' => __( 'No Views Found.', 'formidable' ),
				'add_new_item' => __( 'Add New View', 'formidable' ),
				'edit_item' => __( 'Edit View', 'formidable' )
			)
		) );
	}

	public static function menu() {
		FrmAppHelper::force_capability( 'frm_edit_displays' );

		add_submenu_page( 'formidable', 'Formidable | ' . __( 'Views', 'formidable' ), __( 'Views', 'formidable' ), 'frm_edit_displays', 'edit.php?post_type=frm_display' );
	}

	public static function highlight_menu() {
		FrmAppHelper::maybe_highlight_menu( self::$post_type );
	}

	public static function switch_form_box() {
		global $post_type_object;
		if ( !$post_type_object || $post_type_object->name != self::$post_type ) {
			return;
		}
		$form_id = FrmAppHelper::simple_get( 'form', 'absint' );
		echo FrmFormsHelper::forms_dropdown( 'form', $form_id, array( 'blank' => __( 'View all forms', 'formidable' ) ) );
	}

	public static function filter_forms( $query ) {
		if ( !FrmProDisplaysHelper::is_edit_view_page() ) {
			return $query;
		}

		if ( isset( $_REQUEST['form'] ) && is_numeric( $_REQUEST['form'] ) && isset( $query->query_vars['post_type'] ) && self::$post_type == $query->query_vars['post_type'] ) {
			$query->query_vars['meta_key'] = 'frm_form_id';
			$query->query_vars['meta_value'] = (int)$_REQUEST['form'];
		}

		return $query;
	}

	public static function add_form_nav( $views ) {
		if ( !FrmProDisplaysHelper::is_edit_view_page() ) {
			return $views;
		}

		$form = ( isset( $_REQUEST['form'] ) && is_numeric( $_REQUEST['form'] ) ) ? $_REQUEST['form'] : false;
		if ( !$form ) {
			return $views;
		}

		$form = FrmForm::getOne( $form );
		if ( !$form ) {
			return $views;
		}

		echo '<div id="poststuff">';
		echo '<div id="post-body" class="metabox-holder columns-2">';
		echo '<div id="post-body-content">';
		FrmAppController::get_form_nav( $form, true, 'hide' );
		echo '</div>';
		echo '<div class="clear"></div>';
		echo '</div>';
		echo '<div id="titlediv"><input id="title" type="text" value="' . esc_attr( $form->name == '' ? __( '(no title)' ) : $form->name ) . '" readonly="readonly" disabled="disabled" /></div>';
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
		$templates = glob( $path . '/*.php' );

		for ( $i = count( $templates ) - 1; $i >= 0; $i-- ) {
			$filename = str_replace( '.php', '', str_replace( $path . '/', '', $templates[ $i ] ) );
			$display = get_page_by_path( $filename, OBJECT, self::$post_type );

			$values = FrmProDisplaysHelper::setup_new_vars();
			$values['display_key'] = $filename;

			include( $templates[ $i ] );
		}
	}

	public static function manage_columns( $columns ) {
		unset( $columns['title'], $columns['date'] );

		$columns['id'] = 'ID';
		$columns['title'] = __( 'View Title', 'formidable' );
		$columns['description'] = __( 'Description' );
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
		foreach ( (array)$result as $r ) {
			if ( !empty( $r ) ) {
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
				$old_id = get_post_meta( $id, 'frm_old_id', true );
				$val = ( $old_id ) ? $old_id : __( 'N/A', 'formidable' );
				break;
			case 'name':
			case 'content':
				$post = get_post( $id );
				$val = FrmAppHelper::truncate( strip_tags( $post->{"post_$column_name"} ), 100 );
				break;
			case 'description':
				$post = get_post( $id );
				$val = FrmAppHelper::truncate( strip_tags( $post->post_excerpt ), 100 );
				break;
			case 'show_count':
				$val = ucwords( get_post_meta( $id, 'frm_' . $column_name, true ) );
				break;
			case 'dyncontent':
				$val = FrmAppHelper::truncate( strip_tags( get_post_meta( $id, 'frm_' . $column_name, true ) ), 100 );
				break;
			case 'form_id':
				$form_id = get_post_meta( $id, 'frm_' . $column_name, true );
				$val = FrmFormsHelper::edit_form_link( $form_id );
				break;
			case 'shortcode':
				$code = '[display-frm-data id=' . $id . ' filter=1]';

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

		include( FrmAppHelper::plugin_path() . '/pro/classes/views/displays/submitbox_actions.php' );
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
			add_filter( 'default_title', 'FrmProDisplaysController::default_title', 10, 2 );
			add_filter( 'default_excerpt', 'FrmProDisplaysController::default_excerpt', 10, 2 );
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
		$copy_display = FrmProDisplaysHelper::get_current_view( $post );
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
		$copy_display = FrmProDisplaysHelper::get_current_view( $post );
		if ( $copy_display ) {
			$excerpt = $copy_display->post_excerpt;
		}
		return $excerpt;
	}

	public static function add_meta_boxes( $post_type ) {
		if ( $post_type != self::$post_type ) {
			return;
		}

		add_meta_box( 'frm_form_disp_type', __( 'Basic Settings', 'formidable' ), 'FrmProDisplaysController::mb_form_disp_type', self::$post_type, 'normal', 'high' );
		add_meta_box( 'frm_dyncontent', __( 'Content', 'formidable' ), 'FrmProDisplaysController::mb_dyncontent', self::$post_type, 'normal', 'high' );
		add_meta_box( 'frm_excerpt', __( 'Description' ), 'FrmProDisplaysController::mb_excerpt', self::$post_type, 'normal', 'high' );
		add_meta_box( 'frm_advanced', __( 'Advanced Settings', 'formidable' ), 'FrmProDisplaysController::mb_advanced', self::$post_type, 'advanced' );

		add_meta_box( 'frm_adv_info', __( 'Customization', 'formidable' ), 'FrmProDisplaysController::mb_adv_info', self::$post_type, 'side', 'low' );
	}

	public static function save_post( $post_id ) {
		//Verify nonce
		if ( empty( $_POST ) || ( isset( $_POST['frm_save_display'] ) && !wp_verify_nonce( $_POST['frm_save_display'], 'frm_save_display_nonce' ) ) || !isset( $_POST['post_type'] ) || $_POST['post_type'] != self::$post_type || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || !current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$post = get_post( $post_id );
		if ( $post->post_status == 'inherit' ) {
			return;
		}

		FrmProDisplay::update( $post_id, $_POST );
		do_action( 'frm_create_display', $post_id, $_POST );
	}

	public static function before_delete_post( $post_id ) {
		$post = get_post( $post_id );
		if ( $post->post_type != self::$post_type ) {
			return;
		}

		global $wpdb;

		do_action( 'frm_destroy_display', $post_id );

		$used_by = FrmDb::get_col( $wpdb->postmeta, array( 'meta_key' => 'frm_display_id', 'meta_value' => $post_id ), 'post_ID' );
		if ( !$used_by ) {
			return;
		}

		$form_id = get_post_meta( $post_id, 'frm_form_id', true );

		$next_display = FrmProDisplay::get_auto_custom_display( compact( 'form_id' ) );
		if ( $next_display && $next_display->ID ) {
			$wpdb->update( $wpdb->postmeta,
				array( 'meta_value' => $next_display->ID ),
				array( 'meta_key' => 'frm_display_id', 'meta_value' => $post_id )
			);
		} else {
			$wpdb->delete( $wpdb->postmeta, array( 'meta_key' => 'frm_display_id', 'meta_value' => $post_id ) );
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
		FrmProDisplaysHelper::prepare_duplicate_view( $post );

		$editor_args = array();
		if ( $post->frm_no_rt ) {
			$editor_args['teeny'] = true;
			$editor_args['tinymce'] = false;
		}

		include( FrmAppHelper::plugin_path() . '/pro/classes/views/displays/mb_dyncontent.php' );
	}

	public static function mb_excerpt( $post ) {
		include( FrmAppHelper::plugin_path() . '/pro/classes/views/displays/mb_excerpt.php' );

		//add form nav via javascript
		$form = get_post_meta( $post->ID, 'frm_form_id', true );
		if ( $form ) {
			echo '<div id="frm_nav_container" style="display:none;margin-top:-10px">';
			FrmAppController::get_form_nav( $form, true, 'hide' );
			echo '<div class="clear"></div>';
			echo '</div>';
		}
	}

	public static function mb_form_disp_type( $post ) {
		FrmProDisplaysHelper::prepare_duplicate_view( $post );
		include( FrmAppHelper::plugin_path() . '/pro/classes/views/displays/mb_form_disp_type.php' );
	}

	public static function mb_advanced( $post ) {
		FrmProDisplaysHelper::prepare_duplicate_view( $post );
		include( FrmAppHelper::plugin_path() . '/pro/classes/views/displays/mb_advanced.php' );
	}

	public static function mb_adv_info( $post ) {
		FrmProDisplaysHelper::prepare_duplicate_view( $post );
		FrmFormsController::mb_tags_box( $post->frm_form_id );
	}

	public static function get_tags_box() {
		FrmAppHelper::permission_check('frm_view_forms');
		check_ajax_referer( 'frm_ajax', 'nonce' );
		FrmFormsController::mb_tags_box( (int)$_POST['form_id'], 'frm_doing_ajax' );
		wp_die();
	}

	/* FRONT END */

	public static function get_content( $content ) {
		global $post;
		if ( !$post ) {
			return $content;
		}

		$entry_id = false;
		$filter = apply_filters( 'frm_filter_auto_content', true );

		if ( $post->post_type == self::$post_type && in_the_loop() ) {
			global $frm_displayed;
			if ( !$frm_displayed ) {
				$frm_displayed = array();
			}

			if ( in_array( $post->ID, $frm_displayed ) ) {
				return $content;
			}

			$frm_displayed[] = $post->ID;

			return self::get_display_data( $post, $content, false, compact( 'filter' ) );
		}

		if ( is_singular() && post_password_required() ) {
			return $content;
		}

		$display_id = get_post_meta( $post->ID, 'frm_display_id', true );

		if ( !$display_id || ( !is_single() && !is_page() ) ) {
			return $content;
		}

		$display = FrmProDisplay::getOne( $display_id );
		if ( !$display ) {
			return $content;
		}

		global $frm_displayed;

		if ( $post->post_type != self::$post_type ) {
			$display = FrmProDisplaysHelper::setup_edit_vars( $display, false );
		}

		if ( !$frm_displayed ) {
			$frm_displayed = array();
		}

		//make sure this isn't loaded multiple times but still works with themes and plugins that call the_content multiple times
		if ( !in_the_loop() || in_array( $display->ID, (array)$frm_displayed ) ) {
			return $content;
		}

		//get the entry linked to this post
		if ( ( is_single() || is_page() ) && $post->post_type != self::$post_type ) {

			$entry = FrmDb::get_row( 'frm_items', array( 'post_id' => $post->ID ), 'id, item_key' );
			if ( !$entry ) {
				return $content;
			}

			$entry_id = $entry->id;

			if ( in_array( $display->frm_show_count, array( 'dynamic', 'calendar' ) ) && $display->frm_type == 'display_key' ) {
				$entry_id = $entry->item_key;
			}
		}

		$frm_displayed[] = $display->ID;
		$content = self::get_display_data( $display, $content, $entry_id, array(
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
		$order_key = (int)$order_key;
		require( FrmAppHelper::plugin_path() . '/pro/classes/views/displays/order_row.php' );
	}

	public static function get_where_row() {
		FrmAppHelper::permission_check('frm_edit_displays');
		check_ajax_referer( 'frm_ajax', 'nonce' );
		self::add_where_row( absint( $_POST['where_key'] ), absint( $_POST['form_id'] ) );
		wp_die();
	}

	public static function add_where_row( $where_key = '', $form_id = '', $where_field = '', $where_is = '', $where_val = '' ) {
		$where_key = (int)$where_key;
		require( FrmAppHelper::plugin_path() . '/pro/classes/views/displays/where_row.php' );
	}

	public static function get_where_options() {
		FrmAppHelper::permission_check('frm_edit_displays');
		check_ajax_referer( 'frm_ajax', 'nonce' );
		self::add_where_options( sanitize_title( $_POST['field_id'] ), absint( $_POST['where_key'] ), '', true );
		wp_die();
	}

	public static function add_where_options( $field_id, $where_key, $where_val = '', $new = false ) {
		if ( is_numeric( $field_id ) ) {
			$field = FrmField::getOne( $field_id );

			// If a new UserID filter is being added, set "current_user" as the default value
			if ( $new == true && $field->type == 'user_id' ) {
				$where_val = 'current_user';
			}
		}

		require( FrmAppHelper::plugin_path() . '/pro/classes/views/displays/where_options.php' );
	}

	/**
	 * Add the header for a calendar listing View
	 *
	 * @param string $content
	 * @param object $view
	 * @return string
	 */
	public static function calendar_header( $content, $view ) {
		if ( $view->frm_show_count != 'calendar' ) {
			return $content;
		}

		global $frm_vars, $wp_locale;
		$frm_vars['load_css'] = true;

		//4 digit year
		$year = FrmAppHelper::get_param( 'frmcal-year', date_i18n( 'Y' ), 'get', 'absint' );

		//Numeric month with leading zeros
		$month = FrmAppHelper::get_param( 'frmcal-month', date_i18n( 'm' ), 'get', 'sanitize_title' );

		$month_names = $wp_locale->month;

		$this_time = strtotime( $year . '-' . $month . '-01' );
		$prev_month = date( 'm', strtotime( '-1 month', $this_time ) );
		$prev_year = date( 'Y', strtotime( '-1 month', $this_time ) );

		$next_month = date( 'm', strtotime( '+1 month', $this_time ) );
		$next_year = date( 'Y', strtotime( '+1 month', $this_time ) );

		ob_start();
		include( FrmAppHelper::plugin_path() . '/pro/classes/views/displays/calendar-header.php' );
		$content .= ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * Get the inner content for a Calendar View
	 *
	 * @param string $new_content
	 * @param array $entry_ids
	 * @param array $shortcodes
	 * @param object $view
	 * @return string
	 */
	public static function build_calendar( $new_content, $entry_ids, $shortcodes, $view ) {
		if ( ! $view || $view->frm_show_count != 'calendar' ) {
			return $new_content;
		}

		global $wp_locale;

		$current_year = date_i18n( 'Y' );
		$current_month = date_i18n( 'm' );

		//4 digit year
		$year = FrmAppHelper::get_param( 'frmcal-year', date( 'Y' ), 'get', 'absint' );

		//Numeric month with leading zeros
		$month = FrmAppHelper::get_param( 'frmcal-month', $current_month, 'get', 'sanitize_title' );

		$timestamp = mktime( 0, 0, 0, $month, 1, $year );
		$maxday = date( 't', $timestamp ); //Number of days in the given month
		$this_month = getdate( $timestamp );
		$startday = $this_month['wday'];
		unset( $this_month );

		// week_begins = 0 stands for Sunday
		$week_begins = apply_filters( 'frm_cal_week_begins', absint( get_option( 'start_of_week' ) ), $view );
		if ( $week_begins > $startday ) {
			$startday = $startday + 7;
		}

		$week_ends = 6 + (int)$week_begins;
		if ( $week_ends > 6 ) {
			$week_ends = (int)$week_ends - 7;
		}

		$efield = $field = false;
		if ( is_numeric( $view->frm_date_field_id ) ) {
			$field = FrmField::getOne( $view->frm_date_field_id );
		}

		if ( is_numeric( $view->frm_edate_field_id ) ) {
			$efield = FrmField::getOne( $view->frm_edate_field_id );
		}

		$daily_entries = array();
		while ( $next_set = array_splice( $entry_ids, 0, 30 ) ) {
			$entries = FrmEntry::getAll( array( 'id' => $next_set ), ' ORDER BY FIELD(it.id,' . implode( ',', $next_set ) . ')', '', true, false );
			foreach ( $entries as $entry ) {
				self::calendar_daily_entries( $entry, $view, compact( 'startday', 'maxday', 'year', 'month', 'field', 'efield' ), $daily_entries );
			}
		}

		$locale_day_names = apply_filters( 'frm_calendar_day_names', 'weekday_abbrev', array( 'display' => $view ) );
		$day_names = FrmProAppHelper::reset_keys( $wp_locale->{$locale_day_names} ); //switch keys to order

		if ( $week_begins ) {
			for ( $i = $week_begins; $i < ( $week_begins + 7 ); $i++ ) {
				if ( !isset( $day_names[ $i ] ) ) {
					$day_names[ $i ] = $day_names[ $i - 7 ];
				}
			}
			unset( $i );
		}

		if ( $current_year == $year && $current_month == $month ) {
			$today = date_i18n( 'j' );
		}

		$used_entries = array();

		ob_start();
		include( FrmAppHelper::plugin_path() . '/pro/classes/views/displays/calendar.php' );
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	private static function calendar_daily_entries( $entry, $display, $args, array &$daily_entries ) {
		$i18n = false;

		if ( is_numeric( $display->frm_date_field_id ) ) {
			$date = FrmEntryMeta::get_meta_value( $entry, $display->frm_date_field_id );

			if ( $entry->post_id && !$date && $args['field'] &&
				isset( $args['field']->field_options['post_field'] ) && $args['field']->field_options['post_field']
			) {

				$date = FrmProEntryMetaHelper::get_post_value( $entry->post_id, $args['field']->field_options['post_field'], $args['field']->field_options['custom_field'], array(
					'form_id' => $display->frm_form_id, 'type' => $args['field']->type,
					'field' => $args['field']
				) );

			}
		} else {
			$date = $display->frm_date_field_id == 'updated_at' ? $entry->updated_at : $entry->created_at;
			$i18n = true;
		}

		if ( empty( $date ) ) {
			return;
		}

		if ( $i18n ) {
			$date = FrmAppHelper::get_localized_date( 'Y-m-d', $date );
		} else {
			$date = date( 'Y-m-d', strtotime( $date ) );
		}

		unset( $i18n );
		$dates = array( $date );

		if ( !empty( $display->frm_edate_field_id ) ) {
			if ( is_numeric( $display->frm_edate_field_id ) && $args['efield'] ) {
				$edate = FrmProEntryMetaHelper::get_post_or_meta_value( $entry, $args['efield'] );

				if ( $args['efield'] && $args['efield']->type == 'number' && is_numeric( $edate ) ) {
					$edate = date( 'Y-m-d', strtotime( '+' . ( $edate - 1 ) . ' days', strtotime( $date ) ) );
				}
			} else if ( $display->frm_edate_field_id == 'updated_at' ) {
				$edate = FrmAppHelper::get_localized_date( 'Y-m-d', $entry->updated_at );
			} else {
				$edate = FrmAppHelper::get_localized_date( 'Y-m-d', $entry->created_at );
			}

			if ( $edate && !empty( $edate ) ) {
				$from_date = strtotime( $date );
				$to_date = strtotime( $edate );

				if ( !empty( $from_date ) && $from_date < $to_date ) {
					for ( $current_ts = $from_date; $current_ts <= $to_date; $current_ts += ( 60 * 60 * 24 ) ) {
						$dates[] = date( 'Y-m-d', $current_ts );
					}
					unset( $current_ts );
				}

				unset( $from_date, $to_date );
			}
			unset( $edate );
		}
		unset( $date );

		self::get_repeating_dates( $entry, $display, $args, $dates );

		$dates = apply_filters( 'frm_show_entry_dates', $dates, $entry );

		for ( $i = 0; $i < ( $args['maxday'] + $args['startday'] ); $i++ ) {
			$day = $i - $args['startday'] + 1;

			if ( in_array( date( 'Y-m-d', strtotime( $args['year'] . '-' . $args['month'] . '-' . $day ) ), $dates ) ) {
				$daily_entries[ $i ][] = $entry;
			}

			unset( $day );
		}
	}

	private static function get_repeating_dates( $entry, $display, $args, array &$dates ) {
		if ( !is_numeric( $display->frm_repeat_event_field_id ) ) {
			return;
		}

		//Get meta values for repeat field and end repeat field
		if ( isset( $entry->metas[ $display->frm_repeat_event_field_id ] ) ) {
			$repeat_period = $entry->metas[ $display->frm_repeat_event_field_id ];
		} else {
			$repeat_field = FrmField::getOne( $display->frm_repeat_event_field_id );
			$repeat_period = FrmProEntryMetaHelper::get_post_or_meta_value( $entry->id, $repeat_field );
			unset( $repeat_field );
		}

		if ( isset( $entry->metas[ $display->frm_repeat_edate_field_id ] ) ) {
			$stop_repeat = $entry->metas[ $display->frm_repeat_edate_field_id ];
		} else {
			$stop_field = FrmField::getOne( $display->frm_repeat_edate_field_id );
			$stop_repeat = FrmProEntryMetaHelper::get_post_or_meta_value( $entry->id, $stop_field );
			unset( $stop_field );
		}

		//If site is not set to English, convert day(s), week(s), month(s), and year(s) (in repeat_period string) to English
		//Check for a few common repeat periods like daily, weekly, monthly, and yearly as well
		$t_strings = array( __( 'day', 'formidable' ), __( 'days', 'formidable' ), __( 'daily', 'formidable' ), __( 'week', 'formidable' ), __( 'weeks', 'formidable' ), __( 'weekly', 'formidable' ), __( 'month', 'formidable' ), __( 'months', 'formidable' ), __( 'monthly', 'formidable' ), __( 'year', 'formidable' ), __( 'years', 'formidable' ), __( 'yearly', 'formidable' ) );
		$t_strings = apply_filters( 'frm_recurring_strings', $t_strings, $display );
		$e_strings = array( 'day', 'days', '1 day', 'week', 'weeks', '1 week', 'month', 'months', '1 month', 'year', 'years', '1 year' );
		if ( $t_strings != $e_strings ) {
			$repeat_period = str_ireplace( $t_strings, $e_strings, $repeat_period );
		}
		unset( $t_strings, $e_strings );

		//Switch [frmcal-date] for current calendar date (for use in "Third Wednesday of [frmcal-date]")
		$repeat_period = str_replace( '[frmcal-date]', $args['year'] . '-' . $args['month'] . '-01', $repeat_period );

		//Filter for repeat_period
		$repeat_period = apply_filters( 'frm_repeat_period', $repeat_period, $display );

		//If repeat period is set and is valid
		if ( empty( $repeat_period ) || !is_numeric( strtotime( $repeat_period ) ) ) {
			return;
		}

		//Set up end date to minimize dates array - allow for no end repeat field set, nothing selected for end, or any date

		if ( !empty( $stop_repeat ) ) {
			//If field is selected for recurring end date and the date is not empty
			$maybe_stop_repeat = strtotime( $stop_repeat );
		}

		//Repeat until next viewable month
		$cal_date = $args['year'] . '-' . $args['month'] . '-01';
		$stop_repeat = strtotime( '+1 month', strtotime( $cal_date ) );

		//If the repeat should end before $stop_repeat (+1 month), use $maybe_stop_repeat
		if ( isset( $maybe_stop_repeat ) && $maybe_stop_repeat < $stop_repeat ) {
			$stop_repeat = $maybe_stop_repeat;
			unset( $maybe_stop_repeat );
		}

		$temp_dates = array();

		foreach ( $dates as $d ) {
			$last_i = 0;
			for ( $i = strtotime( $d ); $i <= $stop_repeat; $i = strtotime( $repeat_period, $i ) ) {
				//Break endless loop
				if ( $i == $last_i ) {
					break;
				}
				$last_i = $i;

				//Add to dates array
				$temp_dates[] = date( 'Y-m-d', $i );
			}
			unset( $last_i, $d );
		}
		$dates = $temp_dates;
	}

	/**
	 * Get the footer for a Calendar View
	 *
	 * @param string $content
	 * @param object $view
	 * @return string
	 */
	public static function calendar_footer( $content, $view ) {
		if ( $view->frm_show_count != 'calendar' ) {
			return $content;
		}

		ob_start();
		include( FrmAppHelper::plugin_path() . '/pro/classes/views/displays/calendar-footer.php' );
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	public static function get_date_field_select() {
		FrmAppHelper::permission_check('frm_edit_displays');
		check_ajax_referer( 'frm_ajax', 'nonce' );

		if ( is_numeric( $_POST['form_id'] ) ) {
			$post = new stdClass();
			$post->frm_form_id = (int)$_POST['form_id'];
			$post->frm_edate_field_id = $post->frm_date_field_id = '';
			$post->frm_repeat_event_field_id = $post->frm_repeat_edate_field_id = '';
			include( FrmAppHelper::plugin_path() . '/pro/classes/views/displays/_calendar_options.php' );
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

		$sc_atts = shortcode_atts( $defaults, $atts );
		$atts = array_merge( (array)$atts, (array)$sc_atts );

		$display = FrmProDisplay::getOne( $atts['id'], false, true );
		$user_id = FrmAppHelper::get_user_id_param( $atts['user_id'] );

		if ( !empty( $atts['get'] ) ) {
			$_GET[ $atts['get'] ] = urlencode( $atts['get_value'] );
		}

		$get_atts = $atts;
		foreach ( $defaults as $unset => $val ) {
			unset( $get_atts[ $unset ], $unset, $val );
		}

		foreach ( $get_atts as $att => $val ) {
			$_GET[ $att ] = urlencode( $val );
			unset( $att, $val );
		}

		if ( !$display ) {
			return __( 'There are no views with that ID', 'formidable' );
		}

		return self::get_display_data( $display, '', $atts['entry_id'], array(
			'filter' => $atts['filter'], 'user_id' => $user_id,
			'limit' => $atts['limit'], 'page_size' => $atts['page_size'],
			'order_by' => $atts['order_by'], 'order' => $atts['order'],
			'drafts' => $atts['drafts'],
		) );
	}

	public static function custom_display( $id ) {
		if ( $display = FrmProDisplay::getOne( $id, false, false, array( 'check_post' => true ) ) )
			return self::get_display_data( $display );
	}

	// TODO: Maybe make this function deprecated?
	public static function get_display_data( $view, $content = '', $entry_id = false, $extra_atts = array() ) {
		$extra_atts['entry_id'] = $entry_id;
		return self::get_view_data( $view, $content, $extra_atts );
	}

	/**
	 * Get the content for a View
	 *
	 * @since 2.0.23
	 * @param object $view
	 * @param string $content
	 * @param array $atts
	 * @return string
	 */
	private static function get_view_data( $view, $content, $atts ) {
		if ( post_password_required( $view ) ) {
			return get_the_password_form( $view );
		}

		if ( self::check_the_view_object( $view ) === false ) {
			return $content;
		}

		self::load_view_hooks( $view );
		self::add_to_forms_loaded_vars();

		$atts = self::get_atts_for_view( $atts, $view );

		if ( self::is_listing_page_displayed( $view, $atts ) ) {
			$new_content = self::get_listing_page_content( $view, $atts );
		} else {
			$new_content = self::get_detail_page_content( $view, $atts );
		}

		// load the styling for css classes and pagination
		FrmStylesController::enqueue_style();

		return $new_content;
	}

	/**
	 * Make sure the View object has the necessary properties set
	 *
	 * @since 2.0.23
	 *
	 * @param object $view
	 * @return bool
	 */
	private static function check_the_view_object( &$view ) {
		if ( !isset( $view->frm_empty_msg ) ) {
			$view = FrmProDisplaysHelper::setup_edit_vars( $view, false );
		}

		if ( !isset( $view->frm_form_id ) || empty( $view->frm_form_id ) ) {
			return false;
		}

		//for backwards compatability
		$view->id = $view->frm_old_id;
		$view->display_key = $view->post_name;

		return true;
	}

	/**
	 * Load the necessary hooks for a View
	 *
	 * @since 2.0.23
	 * @param object $view
	 */
	private static function load_view_hooks( $view ) {
		add_action( 'frm_load_view_hooks', 'FrmProDisplaysController::trigger_load_view_hooks' );
		FrmAppHelper::trigger_hook_load( 'view', $view );
	}

	/**
	 * Add to the forms_loaded array in the global $frm_vars variable
	 *
	 * @since 2.0.23
	 */
	private static function add_to_forms_loaded_vars() {
		global $frm_vars;
		$frm_vars['forms_loaded'][] = true;
	}

	/**
	 * Set up the default attributes for a View
	 *
	 * @since 2.0.23
	 *
	 * @param array $atts
	 * @param object $view
	 * @return array
	 */
	private static function get_atts_for_view( $atts, $view ) {
		// If old entry ID is set, save it as an att (for reverse compatibility)
		if ( $view->frm_show_count == 'one' && is_numeric( $view->frm_entry_id ) && $view->frm_entry_id > 0 && !$atts['entry_id'] ) {
			$atts['entry_id'] = $view->frm_entry_id;
		}

		$defaults = array(
			'filter' => false,
			'user_id' => '',
			'limit' => '',
			'page_size' => '',
			'order_by' => '',
			'order' => '',
			'drafts' => 'default',
			'auto_id' => '',
			'form_posts' => array(),
			'pagination' => '',
		);

		return wp_parse_args( $atts, $defaults );
	}

	/**
	 * Check if the listing page is being displayed in a View
	 *
	 * @since 2.0.23
	 *
	 * @param object $view
	 * @param array $atts
	 * @return bool
	 */
	private static function is_listing_page_displayed( $view, $atts ) {
		$listing_page = true;

		if ( in_array( $view->frm_show_count, array( 'dynamic', 'calendar' ) ) ) {
			// If calendar/Dynamic View, show the detail page if entry parameter is set (or post is showing)
			if ( self::get_detail_param( $view, $atts ) ) {
				$listing_page = false;
			}
		} else if ( 'one' == $view->frm_show_count ) {
			$listing_page = false;
		}

		return $listing_page;
	}

	/**
	 * Get the content for the listing page of a View
	 *
	 * @since 2.0.23
	 *
	 * @param object $view
	 * @param array $atts
	 * @return string
	 */
	private static function get_listing_page_content( $view, $atts ) {
		$entry_ids = self::get_entry_ids_for_view_listing_page( $view, $atts );

		if ( ! $entry_ids || empty( $entry_ids ) ) {
			return self::get_no_entries_content_for_listing_page( $view, $atts );
		}

		$entry_ids_on_current_page = self::order_entry_ids_for_view( $view, $atts, $entry_ids );

		$args = self::package_args_for_view_hooks( count( $entry_ids ), $entry_ids_on_current_page, $view );

		if ( empty( $entry_ids_on_current_page ) ) {
			return self::get_no_entries_message_with_pagination( $view, $args, $atts );
		}

		$before_and_after_content = self::get_before_and_after_content_for_listing_page( $view, $args );
		$inner_content = self::get_inner_content_for_listing_page( $view, $args );

		$view_content = $before_and_after_content[0] . $inner_content . $before_and_after_content[1];

		self::maybe_apply_the_content_filter( $atts, $view_content );

		return $view_content;
	}

	/**
	 * Get the content for a View's Detail Page
	 *
	 * @since 2.0.23
	 * @param object $view
	 * @param array $atts
	 * @return string
	 */
	private static function get_detail_page_content( $view, $atts ) {
		$atts['limit'] = 1;

		$entry_ids = self::get_entry_ids_for_view( $view, $atts );

		// If no entry IDs, stop here
		if ( ! $entry_ids || empty( $entry_ids ) ) {
			return self::get_no_entries_message( $view, $atts );
		}

		$entry_ids = self::order_entry_ids_for_view( $view, $atts, $entry_ids );

		$entry_id = reset( $entry_ids );

		$before_content = self::get_before_content_for_detail_page( $view );
		$inner_content = self::get_inner_content_for_detail_page( $view, $entry_id );
		$after_content = self::get_after_content_for_detail_page( $view );

		$view_content = $before_content . $inner_content . $after_content;

		self::maybe_apply_the_content_filter( $atts, $view_content );

		return $view_content;
	}

	/**
	 * Get the entry IDs on a View listing page
	 *
	 * @since 2.0.23
	 *
	 * @param object $view
	 * @param array $atts
	 * @return array
	 */
	private static function get_entry_ids_for_view_listing_page( $view, &$atts ) {
		$entry_ids = self::get_entry_ids_for_view( $view, $atts );

		self::check_unique_filters( $view, $entry_ids );

		return $entry_ids;
	}

	/**
	 * Get the entry IDs that should be displayed in a View
	 *
	 * @since 2.0.23
	 *
	 * @param object $view
	 * @param array $atts
	 * @return array
	 */
	private static function get_entry_ids_for_view( $view, &$atts ) {
		$entry_ids = self::get_unfiltered_entry_ids_for_view( $view, $atts );

		if ( self::return_view_entry_ids_now( $atts, $entry_ids ) === true ) {
			return $entry_ids;
		}

		$atts['form_posts'] = self::get_form_posts_for_view( $view, $entry_ids );

		self::move_drafts_param_to_filter( $atts, $view );
		self::move_user_id_param_to_filter( $atts, $view );
		self::check_view_filters( $view, $atts, $entry_ids );

		self::check_frm_search( $view, $entry_ids );

		return $entry_ids;
	}

	/**
	 * Get the entry IDs for a View prior to checking filters
	 *
	 * @since 2.0.23
	 *
	 * @param object $view
	 * @param array $atts
	 * @return array
	 */
	private static function get_unfiltered_entry_ids_for_view( $view, &$atts ) {
		if ( $atts['auto_id'] ) {
			// single post is being shown
			$entry_ids = self::get_entry_id_for_post( $atts );

		} else if ( $atts['entry_id'] ) {
			// entry_id parameter is set, overrides all filters and other parameters
			$entry_ids = self::convert_entry_param_to_numeric_ids( $atts['entry_id'] );

		} else if ( in_array( $view->frm_show_count, array( 'dynamic', 'calendar' ) ) && self::get_detail_param( $view, $atts ) ) {
			// Dynamic/Calendar View with detail page parameter set
			$entry_ids = self::get_entry_id_for_detail_page( $view, $atts );

		} else {
			// get all entry IDs
			$entry_ids = self::get_all_entry_ids_for_view( $view );
		}

		return $entry_ids;
	}

	/**
	 * Get the entry ID linked to the single post being displayed
	 * auto_id could have the entry ID or key
	 *
	 * @since 2.0.23
	 * @param array $atts
	 * @return array
	 */
	private static function get_entry_id_for_post( $atts ) {
		if ( is_numeric( $atts['auto_id'] ) ) {
			$entry_id = $atts['auto_id'];
		} else {
			$entry_id = FrmEntry::get_id_by_key( $atts['auto_id'] );
		}

		// Convert to array
		if ( $entry_id ) {
			$entry_ids = array( $entry_id );
		} else {
			$entry_ids = array();
		}

		return $entry_ids;
	}

	/**
	 * Convert an entry parameter, set by user, to a numeric entry ID
	 *
	 * @since 2.0.23
	 * @param mixed $entry_id_att
	 * @return array
	 */
	private static function convert_entry_param_to_numeric_ids( $entry_id_att ) {
		if ( is_array( $entry_id_att ) ) {
			$entry_ids = array();
			foreach ( $entry_id_att as $e_id ) {
				$entry_ids[] = self::convert_single_entry_to_numeric_id( $e_id );
			}
		} else {
			$entry_ids = array( self::convert_single_entry_to_numeric_id( $entry_id_att ) );
		}

		$entry_ids = array_filter( $entry_ids );

		return $entry_ids;
	}

	/**
	 * Convert entry key or ID to an ID
	 *
	 * @since 2.0.23
	 * @param string $e_id
	 * @return int
	 */
	private static function convert_single_entry_to_numeric_id( $e_id ) {
		if ( is_numeric( $e_id ) ) {
			// keep it as is
		} else {
			$e_id = FrmEntry::get_id_by_key( $e_id );
		}

		return $e_id;
	}

	/**
	 * Get the entry ID for a View's detail page
	 *
	 * @since 2.0.23
	 * @param object $view
	 * @param array $atts
	 * @return array
	 */
	private static function get_entry_id_for_detail_page( $view, $atts ) {
		$detail_param = self::get_detail_param( $view, $atts );

		if ( $view->frm_type == 'id' && is_numeric( $detail_param ) ) {
			// If using entry ID for detail page
			$entry_id = $detail_param;
		} else {
			// If using entry key for detail page
			$entry_id = FrmEntry::get_id_by_key( $detail_param );
		}

		// Convert to array
		if ( $entry_id ) {
			$entry_ids = array( $entry_id );
		} else {
			$entry_ids = array();
		}

		return $entry_ids;
	}

	/**
	 * Get all the entry IDs for a View's form ID
	 *
	 * @since 2.0.23
	 * @param object $view
	 * @return array
	 */
	private static function get_all_entry_ids_for_view( $view ) {
		$table = 'frm_items';
		$where = array( 'form_id' => $view->frm_form_id );

		return FrmDb::get_col( $table, $where, 'id' );
	}

	/**
	 * Get the detail page parameter value
	 *
	 * @since 2.0.23
	 * @param object $view
	 * @param array $atts
	 * @return string
	 */
	private static function get_detail_param( $view, $atts ) {
		return FrmAppHelper::simple_get( $view->frm_param, 'sanitize_title', $atts['auto_id'] );
	}

	/**
	 * Return the View's entry IDs now is a single post is displayed or the entry_id parameter is set
	 * These are the only situations where View filters should be ignored
	 *
	 * @since 2.0.23
	 * @param array $atts
	 * @param array $entry_ids
	 * @return bool
	 */
	private static function return_view_entry_ids_now( $atts, &$entry_ids ) {
		$return_now = false;

		if ( empty( $entry_ids ) ) {
			$return_now = true;
		}

		// If single post is displayed, ignore View filters
		global $post;
		if ( !empty( $atts['auto_id'] ) && count( $entry_ids ) == 1 && $post ) {
			$return_now = true;
		}

		// If entry_id parameter is set, skip all other filters
		if ( !empty( $atts['entry_id'] ) ) {
			$return_now = true;
		}

		return $return_now;
	}

	/**
	 * Get the entry IDs and linked post IDs for a particular View
	 *
	 * @since 2.0.23
	 * @param object $view
	 * @param array $entry_ids
	 * @return mixed
	 */
	private static function get_form_posts_for_view( $view, $entry_ids ) {
		$form_query = array(
			'form_id' => $view->frm_form_id,
			'post_id >' => 1
		);

		if ( count( $entry_ids ) == 1 ) {
			$form_query['id'] = reset( $entry_ids );
		}

		return FrmDb::get_results( 'frm_items', $form_query, 'id, post_id' );
	}

	/**
	 * Loop through a View's filters and cut down the entry IDs
	 *
	 * @since 2.0.23
	 * @param object $view
	 * @param array $atts
	 * @param array $entry_ids
	 */
	private static function check_view_filters( $view, $atts, &$entry_ids ) {
		if ( ! empty( $view->frm_where ) ) {
			$frm_items_where_clause = array();
			foreach ( $view->frm_where as $i => $where_field ) {

				// If no entry IDs, don't keep checking filters
				if ( empty( $entry_ids ) ) {
					break;
				}

				// Prepare where val
				if ( self::prepare_where_val( $i, $view ) === false ) {
					continue;
				}

				// Prepare where is
				self::prepare_where_is( $i, $view );

				if ( is_numeric( $where_field ) ) {
					// Filter by a field value
					self::update_entry_ids_with_field_filter( $view, $i, $atts, $entry_ids );

				} else {
					// Filter by a standard frm_items database column
					self::add_to_frm_items_query( $view, $i, $frm_items_where_clause );
				}
			}

			self::check_frm_items_query( $frm_items_where_clause, $view, $entry_ids );
		}
	}

	/**
	 * Move the drafts parameter to a View filter
	 *
	 * @since 2.0.23
	 * @param array $atts
	 * @param object $view
	 */
	private static function move_drafts_param_to_filter( $atts, &$view ) {
		if ( !isset( $view->frm_where ) ) {
			$view->frm_where = array();
		}

		if ( in_array( 'is_draft', $view->frm_where ) && $atts['drafts'] === 'default' ) {
			// Don't modify the View filters if a drafts filter is already set and no user-defined drafts parameter is set
		} else {
			$draft_value = self::get_the_drafts_where_value( $atts['drafts'] );
			self::add_or_update_filter( 'is_draft', $draft_value, $view );
		}

		// Remove filter if it sets drafts = both
		$key = array_search( 'is_draft', $view->frm_where );
		if ( $view->frm_where_val[ $key ] === 'both' ) {
			unset( $view->frm_where[ $key ] );
			unset( $view->frm_where_is[ $key ] );
			unset( $view->frm_where_val[ $key ] );
		}
	}

	/**
	 * Get the where_value for the drafts filter
	 *
	 * @since 2.0.23
	 * @param mixed $drafts_param
	 * @return string
	 */
	private static function get_the_drafts_where_value( $drafts_param ) {
		// Get the is_draft value
		if ( $drafts_param === 'both' ) {
			$draft_value = 'both';
		} else if ( $drafts_param === 'default' ) {
			$draft_value = '0';
		} else if ( $drafts_param ) {
			$draft_value = '1';
		} else {
			$draft_value = '0';
		}

		return $draft_value;
	}

	/**
	 * Move the user_id parameter to a View filter
	 *
	 * @since 2.0.23
	 * @param array $atts
	 * @param object $view
	 */
	private static function move_user_id_param_to_filter( $atts, $view ) {
		if ( $atts['user_id'] != '' ) {

			// Get the userID field in the form
			$user_id_fields = FrmField::get_all_types_in_form( $view->frm_form_id, 'user_id' );
			$user_id_field = reset( $user_id_fields );

			// Get the user value
			if ( $atts['user_id'] == 'current' ) {
				$user_val = get_current_user_id();
			} else {
				$user_val = $atts['user_id'];
			}

			// Replace userID filter or add a new one
			self::add_or_update_filter( $user_id_field->id, $user_val, $view );
		}
	}

	/**
	 * Update a View filter if it already exists, otherwise add it
	 *
	 * @since 2.0.23
	 * @param string $filter_col
	 * @param string $filter_value
	 * @param object $view
	 */
	private static function add_or_update_filter( $filter_col, $filter_value, &$view ) {
		if ( in_array( $filter_col, $view->frm_where ) ) {
			// Update existing filter
			$key = array_search( $filter_col, $view->frm_where );
			$view->frm_where_is[ $key ] = '=';
			$view->frm_where_val[ $key ] = $filter_value;
		} else {
			// Add new filter
			$view->frm_where[] = $filter_col;
			$view->frm_where_is[] = '=';
			$view->frm_where_val[] = $filter_value;
		}
	}

	/**
	 * Prepare the where value in a View filter
	 *
	 * @since 2.0.23
	 * @param int $i
	 * @param object $view
	 * @return bool
	 */
	private static function prepare_where_val( $i, &$view ) {
		if ( !isset( $view->frm_where_val[ $i ] ) ) {
			$view->frm_where_val[ $i ] = '';
		}

		$orig_where_val = $view->frm_where_val[ $i ];

		$view->frm_where_val[ $i ] = FrmProFieldsHelper::get_default_value( $orig_where_val, false, true, true );

		if ( preg_match( "/\[(get|get-(.?))\b(.*?)(?:(\/))?\]/s", $orig_where_val ) && $view->frm_where_val[ $i ] == '' ) {
			// If where_val contains [get] or [get-param] shortcode and the param isn't set, ignore this filter
			return false;
		}

		if ( true == self::ignore_entry_id_filter( $orig_where_val, $i, $view ) ) {
			return false;
		}

		self::convert_current_user_val_to_current_user_id( $view->frm_where_val[ $i ] );

		self::do_shortcode_in_where_val( $view->frm_where_val[ $i ] );

		self::prepare_where_val_for_date_columns( $view, $i, $view->frm_where_val[ $i ] );

		self::prepare_where_val_for_id_and_key_columns( $view, $i, $view->frm_where_val[ $i ] );

		return true;
	}

	/**
	 * For stinking reverse compatibility
	 * Ignore an "Entry ID is equal to [get param=entry old_filter=1]" filter in a single entry View
	 * if the retrieved entry doesn't exist in the current form
	 *
	 * @since 2.0.23
	 * @param object $view
	 * @param int $i
	 * @param string $orig_where_val
	 * @return bool
	 */
	private static function ignore_entry_id_filter( $orig_where_val, $i, &$view ) {
		$ignore = false;

		if ( 'one' == $view->frm_show_count && 'id' == $view->frm_where[ $i ] && '[get param=entry old_filter=1]' === $orig_where_val ) {
			$where = array( 'form_id' => $view->frm_form_id );
			if ( ! is_numeric( $view->frm_where_val[ $i ] ) ) {
				$where['item_key'] = $view->frm_where_val[ $i ];
				$entry_id_in_form = FrmDb::get_var( 'frm_items', $where );
				if ( $entry_id_in_form ) {
					$view->frm_where_val[ $i ] = $entry_id_in_form;
				}
			} else {
				$where['id'] = $view->frm_where_val[ $i ];
				$entry_id_in_form = FrmDb::get_var( 'frm_items', $where );
				if ( ! $entry_id_in_form ) {
					$ignore = true;
				}
			}
		}

		return $ignore;
	}

	/**
	 * Convert current_user to the current user's ID
	 *
	 * @since 2.0.23
	 * @param string|array $where_val
	 */
	private static function convert_current_user_val_to_current_user_id( &$where_val ) {
		if ( $where_val == 'current_user' ) {
			$where_val = get_current_user_id();
		}
	}

	/**
	 * Do shortcodes in the where value
	 *
	 * @since 2.0.23
	 * @param string|array $where_val
	 */
	private static function do_shortcode_in_where_val( &$where_val ) {
		if ( !is_array( $where_val ) ) {
			$where_val = do_shortcode( $where_val );
		}
	}

	/**
	 * Prepare the where value for date columns
	 *
	 * @since 2.0.23
	 * @param object $view
	 * @param int $i
	 * @param string $where_val
	 */
	private static function prepare_where_val_for_date_columns( $view, $i, &$where_val ) {
		if ( !in_array( $view->frm_where[ $i ], array( 'created_at', 'updated_at' ) ) ) {
			return;
		}

		if ( $where_val == 'NOW' ) {
			$where_val = current_time( 'mysql', 1 );
		}

		if ( strpos( $view->frm_where_is[ $i ], 'LIKE' ) === false ) {
			$where_val = date( 'Y-m-d H:i:s', strtotime( $where_val ) );

			// If using less than or equal to, set the time to the end of the day
			if ( $view->frm_where_is[ $i ] == '<=' ) {
				$where_val = str_replace( '00:00:00', '23:59:59', $where_val );
			}

			// Convert date to GMT since that is the format in the DB
			$where_val = get_gmt_from_date( $where_val );
		}
	}

	/**
	 * Prepare the where value for id, item_key, and post_id columns
	 *
	 * @since 2.0.23
	 * @param object $view
	 * @param int $i
	 * @param string|array $where_val
	 */
	private static function prepare_where_val_for_id_and_key_columns( $view, $i, &$where_val ) {
		if ( in_array( $view->frm_where[ $i ], array( 'id', 'item_key', 'post_id' ) ) && !is_array( $where_val ) && strpos( $where_val, ',' ) ) {
			$where_val = explode( ',', $where_val );
			$where_val = array_filter( $where_val );
		}
	}

	/**
	 * Prepare the where_is value for a View filter
	 *
	 * @since 2.0.23
	 * @param int $i
	 * @param object $view
	 */
	private static function prepare_where_is( $i, &$view ) {
		$where_is = $view->frm_where_is[ $i ];

		if ( is_array( $view->frm_where_val[ $i ] ) && !empty( $view->frm_where_val[ $i ] ) ) {
			if ( strpos( $where_is, '!' ) === false && strpos( $where_is, 'not' ) === false ) {
				$where_is = ' in ';
			} else {
				$where_is = 'not in';
			}
		}

		$view->frm_where_is[ $i ] = $where_is;
	}

	/**
	 * Check if frm_search parameter is set and filter entry IDs accordingly
	 *
	 * @since 2.0.23
	 * @param object $view
	 * @param array $entry_ids
	 */
	private static function check_frm_search( $view, &$entry_ids ) {
		$s = FrmAppHelper::get_param( 'frm_search', false, 'get', 'sanitize_text_field' );
		if ( $s && !empty( $entry_ids ) ) {
			$new_ids = FrmProEntriesHelper::get_search_ids( $s, $view->frm_form_id, array( 'is_draft' => 'both' ) );

			$entry_ids = array_intersect( $new_ids, $entry_ids );
		}
	}

	/**
	 * Filter down entry IDs with a View field filter
	 *
	 * @since 2.0.23
	 * @param object $view
	 * @param int $i
	 * @param array $atts
	 * @param array $entry_ids
	 */
	private static function update_entry_ids_with_field_filter( $view, $i, $atts, &$entry_ids ) {
		// Don't run the unique filters here
		if ( $view->frm_where_is[ $i ] == 'group_by' ) {
			return;
		}

		$args = array(
			'where_opt' => $view->frm_where[ $i ],
			'where_is' => $view->frm_where_is[ $i ],
			'where_val' => $view->frm_where_val[ $i ],
			'form_id' => $view->frm_form_id,
			'form_posts' => $atts['form_posts'],
			'after_where' => true,
			'display' => $view,
			'drafts' => 'both',
			'use_ids' => false,
		);

		if ( count( $entry_ids ) < 100 ) {
			// Only use the entry IDs in DB calls if it won't make the query too long
			$args['use_ids'] = true;
		}

		$filter_opts = apply_filters( 'frm_display_filter_opt', $args );

		$entry_ids = FrmProAppHelper::filter_where( $entry_ids, $filter_opts );
	}

	/**
	 * Add a standard frm_items column filter to the where array
	 *
	 * @since 2.0.23
	 * @param object $view
	 * @param int $i
	 * @param array $frm_items_where_clause
	 */
	private static function add_to_frm_items_query( $view, $i, &$frm_items_where_clause ) {
		$array_key = sanitize_title( $view->frm_where[ $i ] ) . FrmDb::append_where_is( $view->frm_where_is[ $i ] );

		if ( isset( $frm_items_where_clause[ $array_key ] ) ) {
			$array_key .= ' ';
		}

		$frm_items_where_clause[ $array_key ] = $view->frm_where_val[ $i ];
	}

	/**
	 * Run the query to check all frm_items column filters
	 *
	 * @since 2.0.23
	 * @param array $where
	 * @param object $view
	 * @param array $entry_ids
	 */
	private static function check_frm_items_query( $where, $view, &$entry_ids ) {
		if ( ! empty( $where ) && ! empty( $entry_ids ) ) {
			$table = 'frm_items';
			$where['form_id'] = $view->frm_form_id;

			if ( ! isset( $where['id'] ) && count( $entry_ids ) < 25000 ) {
				$where['id'] = $entry_ids;
				$entry_ids = FrmDb::get_col( $table, $where, 'id' );
			} else {
				$new_entry_ids = FrmDb::get_col( $table, $where, 'id' );
				$entry_ids = array_intersect( $new_entry_ids, $entry_ids );
			}
		}
	}

	/**
	 * Check the unique filters on a View and filter entry IDs accordingly
	 *
	 * @since 2.0.23
	 * @param object $view
	 * @param array $entry_ids
	 */
	private static function check_unique_filters( $view, &$entry_ids ){
		if ( !$entry_ids || empty( $entry_ids ) ) {
			return;
		}

		if ( isset( $view->frm_where_is ) && !empty( $view->frm_where_is ) && in_array( 'group_by', $view->frm_where_is ) ) {
			foreach ( $view->frm_where as $i => $filter_field ) {
				if ( $view->frm_where_is[ $i ] != 'group_by' ) {
					continue;
				}

				if ( is_numeric( $view->frm_where[ $i ] ) ) {
					self::check_unique_field_filter( $view, $i, $entry_ids );
				} else {
					$results = self::check_unique_frm_items_filter( $view, $i);
					$entry_ids = self::get_the_entry_ids_for_a_unique_filter( $results, $entry_ids );
				}
			}
		}
	}

	/**
	 * Check a unique field filter
	 *
	 * @since 2.0.23
	 * @param object $view
	 * @param int $i
	 * @param array $entry_ids
	 */
	private static function check_unique_field_filter( $view, $i, &$entry_ids ){
		$unique_field = FrmField::getOne( $view->frm_where[ $i ] );

		if ( FrmField::is_repeating_field( $unique_field ) || $unique_field->type == 'form' ) {
			// TODO: Add embedded field functionality
			return;
		}

		if ( FrmField::is_option_value_in_object( $unique_field, 'post_field' ) ) {
			$results = self::get_post_values_and_entry_ids_for_unique_fields( $unique_field, $view->frm_form_id );
		} else {
			$results = self::get_values_and_item_ids_for_unique_fields( $unique_field->id );
		}

		$entry_ids = self::get_the_entry_ids_for_a_unique_filter( $results, $entry_ids );
	}

	/**
	 * Get the post values and entry IDs for a unique field filter
	 *
	 * @since 2.0.23
	 * @param object $unique_field
	 * @param int $form_id
	 * @return array
	 */
	private static function get_post_values_and_entry_ids_for_unique_fields( $unique_field, $form_id ) {
		if ( $unique_field->field_options['post_field'] == 'post_custom' ) {
			// If field is a custom field
			$results = self::get_results_for_custom_fields( $unique_field, $form_id );

		} else if ( $unique_field->field_options['post_field'] == 'post_category' ) {
			// If field is a category field
			$results = self::get_results_for_category_fields( $unique_field, $form_id );

		} else {
			// If field is a non-category post field
			$results = self::get_results_for_post_fields( $unique_field, $form_id );

		}

		return $results;
	}

	/**
	 * Get the results for custom fields (for a unique filter)
	 *
	 * @since 2.0.23
	 * @param object $unique_field
	 * @param int $form_id
	 * @return array
	 */
	private static function get_results_for_custom_fields( $unique_field, $form_id ) {
		global $wpdb;
		$raw_query = '
				SELECT
					entries.id,
					postmeta.meta_value meta_value
				FROM
					' . $wpdb->prefix . 'frm_items entries
				INNER JOIN
					' . $wpdb->postmeta . ' postmeta
				ON
					entries.post_id=postmeta.post_id
				WHERE
					postmeta.meta_key=%s AND
					entries.form_id=%d';
		$query = $wpdb->prepare( $raw_query, $unique_field->field_options['custom_field'], $form_id );

		return $wpdb->get_results( $query, OBJECT_K );
	}

	/**
	 * Get the results for category fields (for a unique filter)
	 *
	 * @since 2.0.23
	 * @param object $unique_field
	 * @param int $form_id
	 * @return array
	 */
	private static function get_results_for_category_fields( $unique_field, $form_id ){
		global $wpdb;
		$raw_query = '
				SELECT
					entries.id,
					term_taxonomy.term_id meta_value
				FROM
					' . $wpdb->prefix . 'frm_items entries
				INNER JOIN
					' . $wpdb->term_relationships . ' term_relationships
					ON
						entries.post_id=term_relationships.object_id
				INNER JOIN
					' . $wpdb->term_taxonomy . ' term_taxonomy
					ON
						term_taxonomy.term_taxonomy_id=term_relationships.term_taxonomy_id
				WHERE
					term_taxonomy.taxonomy=%s AND
					entries.form_id=%d';
		$query = $wpdb->prepare( $raw_query, $unique_field->field_options['taxonomy'], $form_id );

		return $wpdb->get_results( $query, OBJECT_K );
	}

	/**
	 * Get the results for post fields (for a unique filter)
	 *
	 * @since 2.0.23
	 * @param object $unique_field
	 * @param int $form_id
	 * @return array|null|object
	 */
	private static function get_results_for_post_fields( $unique_field, $form_id ) {
		global $wpdb;
		$raw_query = '
				SELECT
					entries.id,
					posts.' . $unique_field->field_options['post_field'] . ' meta_value
				FROM
					' . $wpdb->prefix . 'frm_items entries
				INNER JOIN
					' . $wpdb->posts . ' posts
				ON
					entries.post_id=posts.ID
				WHERE
					entries.form_id=%d';
		$query = $wpdb->prepare( $raw_query, $form_id );

		return $wpdb->get_results( $query, OBJECT_K );
	}

	/**
	 * Get the meta_values and item_ids for the unique field filter
	 *
	 * @since 2.0.23
	 * @param int $filter_field
	 * @return array
	 */
	private static function get_values_and_item_ids_for_unique_fields( $filter_field ) {
		global $wpdb;
		$raw_query = '
				SELECT
					item_id,
					meta_value
				FROM
					' . $wpdb->prefix . 'frm_item_metas
				WHERE
					field_id=%d';
		$query = $wpdb->prepare( $raw_query, $filter_field );

		return $wpdb->get_results( $query, OBJECT_K );
	}

	/**
	 * Check a unique filter for a column in frm_items
	 *
	 * @since 2.0.23
	 * @param object $view
	 * @param int $i
	 * @return array
	 */
	private static function check_unique_frm_items_filter( $view, $i ) {
		global $wpdb;
		$raw_query = '
				SELECT
					id,
					' . $view->frm_where[ $i ] . ' meta_value
				FROM
					' . $wpdb->prefix . 'frm_items
				WHERE
					form_id=%d';
		$query = $wpdb->prepare( $raw_query, $view->frm_form_id );

		return $wpdb->get_results( $query, OBJECT_K );
	}

	/**
	 * Get the entry IDs for a unique filter
	 * @param $results
	 * @param $entry_ids
	 * @return array
	 */
	private static function get_the_entry_ids_for_a_unique_filter( $results, $entry_ids ) {
		$unique_meta_values = $new_entry_ids = array();

		foreach ( $entry_ids as $e_id ) {
			// If field value is empty/blank, entry will not be shown in View
			if ( ! isset( $results[ $e_id ] ) ) {
				continue;
			}

			$meta_value = $results[ $e_id ]->meta_value;

			// Add the entry ID to the $new_entry_ids array if the value doesn't already exist in the $unique_meta_values array
			if ( ! isset( $unique_meta_values[ $meta_value ] ) ) {
				$unique_meta_values[ $meta_value ] = $e_id;
				$new_entry_ids[] = $e_id;
			}
		}

		return $new_entry_ids;
	}

	/**
	 * Order the entry IDs for a View
	 *
	 * @since 2.0.23
	 * @param object $view
	 * @param array $atts
	 * @param array $entry_ids
	 * @return array
	 */
	private static function order_entry_ids_for_view( $view, $atts, $entry_ids ) {
		if ( count( $entry_ids ) == 1 ) {
			return $entry_ids;
		}

		self::maybe_update_view_order( $atts, $view );

		$display_page_query = array(
			'order_by_array' => $view->frm_order_by,
			'order_array' => $view->frm_order,
			'posts' => $atts['form_posts'],
			'display' => $view,
		);
		self::maybe_add_limit_to_query( $view, $atts, $display_page_query );

		self::get_page_size_for_view( $atts, $view );

		$where = self::set_where_for_ordering_view_entries( $view, $entry_ids );
		//self::maybe_add_cat_query( $where );

		if ( $view->frm_page_size ) {
			$page_param = ( $_GET && isset( $_GET['frm-page-'. $view->ID] ) ) ? 'frm-page-'. $view->ID : 'frm-page';
			$current_page = FrmAppHelper::simple_get( $page_param, 'absint', 1 );
			$entry_ids = FrmProEntry::get_view_page( $current_page, $view->frm_page_size, $where, $display_page_query );
		} else {
			$entry_ids = FrmProEntry::get_view_results( $where, $display_page_query );
		}

		return $entry_ids;
	}

	/**
	 * Allow order and order_by parameters to override order/order_by settings
	 *
	 * @since 2.0.23
	 * @param array $atts
	 * @param object $view
	 */
	private static function maybe_update_view_order( $atts, &$view ) {
		if ( ! empty( $atts['order_by'] ) ) {
			$view->frm_order_by = explode( ',', $atts['order_by'] );

			if ( ! empty( $atts['order'] ) ) {
				$view->frm_order = explode( ',', $atts['order'] );
			} else {
				$view->frm_order = array( 'DESC' );
			}

			foreach ( $view->frm_order_by as $i => $order ) {
				if ( ! isset( $view->frm_order[ $i ] ) ) {
					$view->frm_order[ $i ] = 'DESC';
				}
			}


		}
	}

	/**
	 * Set the where for ordering View entries
	 *
	 * @since 2.0.23
	 * @param object $view
	 * @param array $entry_ids
	 * @return array
	 */
	private static function set_where_for_ordering_view_entries( $view, $entry_ids ) {
		$where = array(
			'it.form_id' => $view->frm_form_id,
			'it.id' => $entry_ids,
		);

		return $where;
	}

	/**
	 * Get the limit for a View
	 *
	 * @since 2.0.23
	 * @param object $view
	 * @param array $atts
	 * @return int|string
	 */
	private static function get_view_limit( $view, $atts ) {
		$limit = '';

		if ( is_numeric( $atts['limit'] ) ) {
			$view->frm_limit = (int)$atts['limit'];
		}

		if ( is_numeric( $view->frm_limit ) ) {
			$limit = $view->frm_limit;
		}

		// Ignore limit on calendar Views since it doesn't appear as an option
		if ( $view->frm_show_count == 'calendar' ) {
			$limit = '';
		}

		return $limit;
	}

	/**
	 * Add the View limit to a query
	 *
	 * @since 2.0.23
	 * @param object $view
	 * @param array $atts
	 * @param array $display_page_query
	 */
	private static function maybe_add_limit_to_query( $view, $atts, &$display_page_query ){
		$limit = self::get_view_limit( $view, $atts );

		if ( is_numeric( $limit ) ) {
			$display_page_query['limit'] = ' LIMIT ' . $limit;
		}
	}

	/**
	 * Get the pagination HTML for a paginated View
	 *
	 * @since 2.0.23
	 * @param object $view
	 * @param int $total_count
	 * @return string
	 */
	private static function setup_pagination( $view, $total_count ) {
		$pagination = '';

		if ( is_int( $view->frm_page_size ) ) {
			$page_param = ( $_GET && isset( $_GET['frm-page-'. $view->ID] ) ) ? 'frm-page-'. $view->ID : 'frm-page';
			$current_page = FrmAppHelper::simple_get( $page_param, 'absint', 1 );

			$pagination = self::get_pagination( $view, $total_count, $current_page );
		}

		return $pagination;
	}

	/**
	 * Get the page size for a View
	 * Make sure page_size parameter overrides Page Size setting
	 *
	 * @since 2.0.23
	 * @param array $atts
	 * @param object $view
	 */
	private static function get_page_size_for_view( $atts, &$view ) {
		if ( is_numeric( $atts['page_size'] ) ) {
			// page_size parameter overrides page size setting
			$view->frm_page_size = (int) $atts['page_size'];
		} else if ( is_numeric( $view->frm_page_size ) ) {
			$view->frm_page_size = (int) $view->frm_page_size;
		} else {
			$view->frm_page_size = '';
		}

		// If limit is lower than page size, ignore the page size
		if ( is_numeric( $view->frm_page_size ) && is_numeric( $view->frm_limit ) && $view->frm_limit < $view->frm_page_size ) {
			$view->frm_page_size = '';
		}
	}

	/**
	 * Package the arguments for all the View hooks
	 *
	 * @since 2.0.23
	 * @param int $total_entry_count
	 * @param array $entry_ids_on_current_page
	 * @param object $view
	 * @return array
	 */
	private static function package_args_for_view_hooks( $total_entry_count, $entry_ids_on_current_page, $view ) {
		$args = array(
			'entry_ids' => $entry_ids_on_current_page,
			'total_count' => count( $entry_ids_on_current_page ),
			'record_count' => $total_entry_count,
			'pagination' => self::setup_pagination( $view, $total_entry_count ),
		);

		return $args;
	}

	/**
	 * Get a listing View's Before and After Content
	 *
	 * @since 2.0.23
	 * @param object $view
	 * @param array $args
	 * @return array
	 */
	private static function get_before_and_after_content_for_listing_page( $view, $args ) {
		$before_content = self::get_before_content_for_listing_page( $view, $args );
		$after_content = self::get_after_content_for_listing_page( $view, $args );

		$before_and_after_content = $before_content . 'frm_inner_content_placeholder' . $after_content;
		$before_and_after_content = FrmProFieldsHelper::get_default_value( $before_and_after_content, false, true, false );

		$before_and_after_pieces = explode( 'frm_inner_content_placeholder', $before_and_after_content );

		return $before_and_after_pieces;
	}

	/**
	 * Get the Before Content for a View's Listing Page
	 *
	 * @since 2.0.23
	 * @param object $view
	 * @param array $args
	 * @return string
	 */
	private static function get_before_content_for_listing_page( $view, $args ) {
		$before_content = isset( $view->frm_before_content ) ? $view->frm_before_content : '';

		$before_content = self::calendar_header( $before_content, $view );

		self::replace_entry_count_shortcode( $args, $before_content );

		$before_content = apply_filters( 'frm_before_display_content', $before_content, $view, 'all', $args );

		return $before_content;
	}

	/**
	 * Get the inner content for a View's Listing Page
	 *
	 * @since 2.0.23
	 * @param object $view
	 * @param array $args
	 * @return string
	 */
	private static function get_inner_content_for_listing_page( $view, $args ) {
		$inner_content = '';

		// Replace shortcodes in the content
		$unfiltered_content = $view->post_content;
		$shortcodes = FrmProDisplaysHelper::get_shortcodes( $unfiltered_content, $view->frm_form_id );

		$filtered_content = self::build_calendar( $unfiltered_content, $args['entry_ids'], $shortcodes, $view );
		$filtered_content = apply_filters( 'frm_display_entries_content', $filtered_content, $args['entry_ids'], $shortcodes, $view, 'all', $args );

		if ( $filtered_content != $unfiltered_content ) {
			$inner_content = $filtered_content;
			$inner_content = FrmProFieldsHelper::get_default_value( $inner_content, false, true, false );
		} else {
			$odd = 'odd';
			$count = 0;
			$loop_entry_ids = $args['entry_ids'];
			while ( $next_set = array_splice( $loop_entry_ids, 0, 30 ) ) {
				$entries = FrmEntry::getAll( array( 'id' => $next_set ), ' ORDER BY FIELD(it.id,' . implode( ',', $next_set ) . ')', '', true, false );
				foreach ( $entries as $entry ) {
					$count++;
					$args['count'] = $count;

					$new_content = apply_filters( 'frm_display_entry_content', $unfiltered_content, $entry, $shortcodes, $view, 'all', $odd, $args );
					$new_content = FrmProFieldsHelper::get_default_value( $new_content, false, true, false );
					$inner_content .= $new_content;

					$odd = ( $odd == 'odd' ) ? 'even' : 'odd';
				}
				unset( $entry, $entries );
			}
		}

		return $inner_content;
	}

	/**
	 * Get the After Content for a View's Listing Page
	 *
	 * @since 2.0.23
	 * @param object $view
	 * @param array $args
	 * @return string
	 */
	private static function get_after_content_for_listing_page( $view, $args ) {
		$after_content = '';

		if ( isset( $view->frm_after_content ) ) {
			$after_content = $view->frm_after_content;

			self::replace_entry_count_shortcode( $args, $after_content );

			// TODO: Remove this hook after a few versions have passed
			$after_content = apply_filters( 'frm_after_content', $after_content, $view, 'all', $args );
			if ( has_filter( 'frm_after_content' ) ) {
				_deprecated_function( 'The frm_after_content filter', '2.0.23', 'the frm_after_display_content filter' );
			}
		}

		if ( 'calendar' == $view->frm_show_count ) {
			$calendar_footer = self::calendar_footer( '', $view );
			$after_content = $calendar_footer . $after_content;
		} else if ( $args['pagination'] ) {
			$after_content .= $args['pagination'];
		}

		$after_content = apply_filters( 'frm_after_display_content', $after_content, $view, 'all', $args );

		return $after_content;
	}

	/**
	 * Get the Before Content for a View's Detail Page
	 *
	 * @since 2.0.23
	 * @param object $view
	 * @return string
	 */
	private static function get_before_content_for_detail_page( $view ){
		$before_content = apply_filters('frm_before_display_content', '', $view, 'one', array() );

		return $before_content;
	}

	/**
	 * Get the inner cntent for a View's Detail Page
	 *
	 * @since 2.0.23
	 * @param object $view
	 * @param int $entry_id
	 * @return string
	 */
	private static function get_inner_content_for_detail_page( $view, $entry_id ){
		if ( $view->frm_show_count == 'one' ) {
			$new_content = $view->post_content;
		} else {
			$new_content = $view->frm_dyncontent;
		}

		$shortcodes = FrmProDisplaysHelper::get_shortcodes( $new_content, $view->frm_form_id );

		$entry = FrmEntry::getOne( $entry_id );

		$detail_content = apply_filters( 'frm_display_entry_content', $new_content, $entry, $shortcodes, $view, 'one', 'odd', array() );

		$detail_content = FrmProFieldsHelper::get_default_value( $detail_content, false, true, false );

		return $detail_content;
	}

	/**
	 * Get the after content for a View's Detail Page
	 *
	 * @since 2.0.23
	 * @param object $view
	 * @return string
	 */
	private static function get_after_content_for_detail_page( $view ){
		$after_content = apply_filters( 'frm_after_display_content', '', $view, 'one', array() );

		return $after_content;
	}

	/**
	 * Get View pagination
	 *
	 * @since 2.0.23
	 * @param object $view
	 * @param int $record_count
	 * @param int $current_page
	 * @return string
	 */
	private static function get_pagination( $view, $record_count, $current_page ) {
		$pagination = '';

		$page_count = FrmEntry::getPageCount( $view->frm_page_size, $record_count );

		if ( $page_count > 1 ) {
			$page_last_record = FrmAppHelper::get_last_record_num( $record_count, $current_page, $view->frm_page_size );
			$page_first_record = FrmAppHelper::get_first_record_num( $record_count, $current_page, $view->frm_page_size );
			$page_param = 'frm-page-'. $view->ID;
			$args = compact( 'current_page', 'record_count', 'page_count', 'page_last_record', 'page_first_record', 'page_param' );
			$pagination = FrmAppHelper::get_file_contents( FrmAppHelper::plugin_path() .'/pro/classes/views/displays/pagination.php', $args );
		}

		return $pagination;
	}

	/**
	 * Get the content for a listing page with no entries
	 *
	 * @since 2.0.23
	 * @param object $view
	 * @param array $atts
	 * @return string
	 */
	private static function get_no_entries_content_for_listing_page( $view, $atts ){
		if ( 'calendar' == $view->frm_show_count ) {
			// Show empty calendar
			$view_content = self::calendar_header( '', $view );
			$view_content .= self::build_calendar( $view_content, array(), array(), $view );
			$view_content .= self::calendar_footer( $view_content, $view );
		} else {
			// Get no entries message
			$view_content = self::get_no_entries_message( $view, $atts );
		}

		return $view_content;
	}

	/**
	 * Get the no entries message for a view
	 *
	 * @since 2.0.23
	 * @param object $view
	 * @return string
	 */
	private static function get_no_entries_message( $view, $atts ){
		$empty_msg = '';

		if ( isset( $view->frm_empty_msg ) && ! empty( $view->frm_empty_msg ) ) {
			$empty_msg = '<div class="frm_no_entries">' . FrmProFieldsHelper::get_default_value( $view->frm_empty_msg, false ) . '</div>';
		}

		$empty_msg = apply_filters( 'frm_no_entries_message', $empty_msg, array( 'display' => $view ) );

		self::maybe_apply_the_content_filter( $atts, $empty_msg );

		return $empty_msg;
	}

	/**
	 * Get the no entries message with the pagination below it
	 *
	 * @since 2.0.23
	 * @param object $view
	 * @param array $args
	 * @param array $atts
	 * @return string
	 */
	private static function get_no_entries_message_with_pagination( $view, $args, $atts ) {
		$content = self::get_no_entries_message( $view, $atts );

		// Add pagination and filter it
		$pagination = self::calendar_footer( $args['pagination'], $view );
		$content .= apply_filters( 'frm_after_display_content', $pagination, $view, 'all', $args );

		return $content;
	}

	/**
	 * Apply the content filter if filter=1 is set
	 *
	 * @since 2.0.23
	 * @param string $content
	 * @param array $atts
	 * @return string
	 */
	private static function maybe_apply_the_content_filter( $atts, &$content ) {
		if ( $atts['filter'] ) {
			$content = apply_filters( 'the_content', $content );
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
	 * Replace the [entry_count] shortcode in a View's before and after content
	 *
	 * @since 2.0.23
	 * @param array $atts
	 * @param string $content
	 */
	private static function replace_entry_count_shortcode( $atts, &$content ) {
		$content = str_replace( '[entry_count]', $atts['record_count'], $content );
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

	public static function get_pagination_file( $filename, $atts ) {
		_deprecated_function( __FUNCTION__, '2.0', 'FrmAppHelper::get_file_contents' );
		return FrmAppHelper::get_file_contents($filename, $atts);
	}

	public static function filter_after_content( $content, $display, $show, $atts ) {
		_deprecated_function( __FUNCTION__, '2.0.23', 'FrmProDisplaysController::replace_entry_count_shortcode()' );
		self::replace_entry_count_shortcode( $atts, $content );
		return $content;
	}
}
