<?php

class FrmProStylesController extends FrmStylesController{

    public static function load_pro_hooks() {
        if ( FrmAppHelper::is_admin_page('formidable-styles') ) {
            add_filter('frm_style_head', 'FrmProStylesController::maybe_new_style');
            add_filter('frm_style_action_route', 'FrmProStylesController::pro_route');
        }
    }

	public static function add_style_boxes( $boxes ) {
		$boxes['section-fields'] = __( 'Section Fields', 'formidable' );
		$boxes['date-fields']   = __( 'Date Fields', 'formidable' );
		$boxes['progress-bars'] = __( 'Progress Bars &amp; Rootline', 'formidable' );

		add_filter( 'frm_style_settings_progress-bars', 'FrmProStylesController::progress_settings_file' );
		add_filter( 'frm_style_settings_date-fields', 'FrmProStylesController::date_settings_file' );
		add_filter( 'frm_style_settings_section-fields', 'FrmProStylesController::section_fields_file' );

		return $boxes;
	}

	public static function section_fields_file() {
		return self::view_folder() . '/_section-fields.php';
	}

	public static function date_settings_file() {
		return self::view_folder() . '/_date-fields.php';
	}

	public static function progress_settings_file() {
		return self::view_folder() . '/_progress-bars.php';
	}

	public static function get_datepicker_names( $jquery_themes ) {
		$alt_img_name = array(
			'ui-lightness'  => 'ui_light',
			'ui-darkness'   => 'ui_dark',
			'start'         => 'start_menu',
			'redmond'       => 'windoze',
			'vader'         => 'black_matte',
			'mint-choc'     => 'mint_choco',
		);

		$theme_names = array_keys( $jquery_themes );
		$theme_names = array_combine( $theme_names, $theme_names );

		foreach ( $theme_names as $k => $v ) {
			$theme_names[ $k ] = str_replace( '-', '_', $v );
			unset($k, $v);
		}

		$alt_img_name = array_merge( $theme_names, $alt_img_name );
		$alt_img_name['-1'] = '';

		return $alt_img_name;
	}

	public static function append_style_form( $atts ) {
		$style = $atts['style'];
		$pos_class = $atts['pos_class'];
		include( self::view_folder() . '/_sample_form.php' );
	}

	public static function style_switcher( $style, $styles ) {
		include( self::view_folder() . '/_style_switcher.php' );
	}

    public static function maybe_new_style($style) {
		$action = FrmAppHelper::get_param( 'frm_action', '', 'get', 'sanitize_title' );
    	if ( 'new_style' == $action ) {
            $style = self::new_style('style');
    	} else if ( 'duplicate' == $action ) {
    		$style = self::duplicate('style');
    	}
        return $style;
    }

    public static function new_style($return = '') {
        $frm_style = new FrmStyle();
        $style = $frm_style->get_new();

        if ( 'style' == $return ) {
            // return style object for header css link
            return $style;
        }

        self::load_styler($style);
    }

	public static function duplicate( $return = '' ) {
		$style_id = FrmAppHelper::get_param( 'style_id', 0, 'get', 'absint' );

		if ( ! $style_id ) {
			self::new_style( $return );
			return;
		}

		$frm_style = new FrmProStyle();
		$style = $frm_style->duplicate( $style_id );

		if ( 'style' == $return ) {
			// return style object for header css link
			return $style;
		}

		self::load_styler( $style );
	}

    public static function destroy() {
		$id = FrmAppHelper::simple_get( 'id', 'absint' );

        $frm_style = new FrmStyle();
        $frm_style->destroy($id);

        $message = __( 'Your styling settings have been deleted.', 'formidable' );

        self::edit('default', $message);
    }

    public static function pro_route($action) {
        switch ( $action ) {
            case 'new_style':
            case 'duplicate':
            case 'destroy':
                add_filter('frm_style_stop_action_route', '__return_true');
				return self::$action();
        }
    }

	public static function include_front_css( $args ) {
		$defaults = $args['defaults'];
		include( FrmAppHelper::plugin_path() . '/pro/css/pro_fields.css.php' );
		include( FrmAppHelper::plugin_path() . '/pro/css/chosen.css.php' );
		include( FrmAppHelper::plugin_path() . '/pro/css/dropzone.css' );
		include( FrmAppHelper::plugin_path() . '/pro/css/progress.css.php' );
	}

	private static function view_folder() {
		return FrmAppHelper::plugin_path() . '/pro/classes/views/styles';
	}
}
