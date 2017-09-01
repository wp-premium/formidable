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
