<?php

class FrmProAppController{

	/**
	 * Use in-plugin translations instead of WP.org
	 * @since 2.2.8
	 */
	public static function load_translation( $mo_file, $domain ) {
		if ( 'formidable' === $domain ) {
			$user_local = ( FrmAppHelper::is_admin() && function_exists('get_user_locale') ) ? get_user_locale() : get_locale();
			$file = FrmAppHelper::plugin_path() . '/languages/formidable-' . $user_local . '.mo';
			if ( file_exists( $file ) ) {
				$mo_file = $file;
			}
		}
		return $mo_file;
	}

    public static function create_taxonomies() {
        register_taxonomy( 'frm_tag', 'formidable', array(
            'hierarchical' => false,
            'labels' => array(
                'name' => __( 'Formidable Tags', 'formidable' ),
                'singular_name' => __( 'Formidable Tag', 'formidable' ),
            ),
            'public' => true,
            'show_ui' => true,
        ) );
    }

	/**
	 * @since 2.05.07
	 */
	public static function admin_bar_configure() {
		if ( is_admin() || ! current_user_can( 'frm_edit_forms' ) ) {
			return;
		}

		$actions = array();

		self::add_views_to_admin_bar( $actions );
		self::add_entry_to_admin_bar( $actions );

		if ( empty( $actions ) ) {
			return;
		}

		self::maybe_add_parent_admin_bar();

		global $wp_admin_bar;

		foreach ( $actions as $id => $action ) {
			$wp_admin_bar->add_node( array(
				'parent' => 'frm-forms',
				'title'  => $action['name'],
				'href'   => $action['url'],
				'id'     => 'edit_' . $id,
			) );
		}
	}

	/**
	 * @since 2.05.07
	 */
	private static function maybe_add_parent_admin_bar() {
		global $wp_admin_bar;
		$has_node = $wp_admin_bar->get_node( 'frm-forms' );
		if ( ! $has_node ) {
			FrmFormsController::add_menu_to_admin_bar();
		}
	}

	/**
	 * @since 2.05.07
	 */
	private static function add_views_to_admin_bar( &$actions ) {
		global $frm_vars;

		if ( isset( $frm_vars['views_loaded'] ) && ! empty( $frm_vars['views_loaded'] ) ) {
			foreach ( $frm_vars['views_loaded'] as $id => $name ) {
				$actions[ 'view_' . $id ] = array(
					'name' => sprintf( __( '%s View', 'formidable' ), $name ),
					'url'  => admin_url( 'post.php?post=' . intval( $id ) . '&action=edit' ),
				);
			}

			asort( $actions );
		}
	}

	/**
	 * @since 2.05.07
	 */
	private static function add_entry_to_admin_bar( &$actions ) {
		global $post;

		if ( is_singular() && ! empty( $post ) ) {
			$entry_id = FrmDb::get_var( 'frm_items', array( 'post_id' => $post->ID ), 'id' );
			if ( ! empty( $entry_id ) ) {
				$actions[ 'entry_' . $entry_id ] = array(
					'name' => __( 'Edit Entry', 'formidable' ),
					'url'  => admin_url( 'admin.php?page=formidable-entries&frm_action=edit&id=' . intval( $entry_id ) ),
				);
			}
		}
	}

	public static function form_nav( $nav, $atts ) {
		$form_id = $atts['form_id'];

		$nav[] = array(
			'link'    => admin_url( 'edit.php?post_type=frm_display&form='. absint( $form_id ) .'&show_nav=1' ),
			'label'   => __( 'Views', 'formidable' ),
			'current' => array(),
			'page'    => 'frm_display',
			'permission' => 'frm_edit_displays',
		);

		$nav[] = array(
			'link'    => admin_url( 'admin.php?page=formidable&frm_action=reports&form=' . absint( $form_id ) . '&show_nav=1' ),
			'label'   => __( 'Reports', 'formidable' ),
			'current' => array( 'reports' ),
			'page'    => 'formidable',
			'permission' => 'frm_view_reports',
		);

		return $nav;
	}

    public static function drop_tables( $tables ) {
        global $wpdb;
        $tables[] = $wpdb->prefix .'frm_display';
        return $tables;
    }

	public static function set_get( $atts ) {
		if ( empty( $atts ) ) {
			return;
		}

		foreach ( $atts as $att => $val ) {
            $_GET[$att] = $val;
            unset($att, $val);
        }
    }

	public static function load_genesis() {
        //trigger Genesis hooks for integration
        FrmProAppHelper::load_genesis();
    }

}
