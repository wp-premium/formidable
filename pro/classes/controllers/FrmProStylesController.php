<?php

class FrmProStylesController extends FrmStylesController {

    public static function load_pro_hooks() {
        if ( FrmAppHelper::is_admin_page('formidable-styles') ) {
            add_filter('frm_style_head', 'FrmProStylesController::maybe_new_style');
            add_filter('frm_style_action_route', 'FrmProStylesController::pro_route');
        }
    }

	public static function add_style_boxes( $boxes ) {
		$add_boxes = array(
			'section-fields' => __( 'Section Fields', 'formidable-pro' ),
			'date-fields'    => __( 'Date Fields', 'formidable-pro' ),
			'toggle-fields'  => __( 'Toggle Fields', 'formidable-pro' ),
			'slider-fields'  => __( 'Slider Fields', 'formidable-pro' ),
			'progress-bars'  => __( 'Progress Bars &amp; Rootline', 'formidable-pro' ),
		);
		$boxes = array_merge( $boxes, $add_boxes );

		foreach ( $add_boxes as $label => $name ) {
			add_filter( 'frm_style_settings_' . $label, 'FrmProStylesController::style_box_file' );
		}

		return $boxes;
	}

	/**
	 * @since 3.01.01
	 */
	public static function style_box_file( $f ) {
		$path = explode( '/views/styles/', $f );
		return self::view_folder() . '/' . $path[1];
	}

	public static function section_fields_file() {
		_deprecated_function( __METHOD__, '3.01.01', 'FrmProStylesController::style_box_file' );
		return self::view_folder() . '/_section-fields.php';
	}

	public static function date_settings_file() {
		_deprecated_function( __METHOD__, '3.01.01', 'FrmProStylesController::style_box_file' );
		return self::view_folder() . '/_date-fields.php';
	}

	public static function progress_settings_file() {
		_deprecated_function( __METHOD__, '3.01.01', 'FrmProStylesController::style_box_file' );
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

	public static function maybe_new_style( $style ) {
		$action = FrmAppHelper::get_param( 'frm_action', '', 'get', 'sanitize_title' );
    	if ( 'new_style' == $action ) {
            $style = self::new_style('style');
    	} else if ( 'duplicate' == $action ) {
    		$style = self::duplicate('style');
    	}
        return $style;
    }

	public static function new_style( $return = '' ) {
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

        $message = __( 'Your styling settings have been deleted.', 'formidable-pro' );

        self::edit('default', $message);
    }

	public static function pro_route( $action ) {
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
		$important = self::is_important( $defaults );

		include( FrmProAppHelper::plugin_path() . '/css/pro_fields.css.php' );
		include( FrmProAppHelper::plugin_path() . '/css/chosen.css.php' );
		include( FrmProAppHelper::plugin_path() . '/css/dropzone.css' );
		include( FrmProAppHelper::plugin_path() . '/css/progress.css.php' );
	}

	/**
	 * @since 3.01.01
	 */
	public static function add_defaults( $settings ) {
		self::set_toggle_slider_colors( $settings );

		return $settings;
	}

	/**
	 * @since 3.01.01
	 */
	public static function override_defaults( $settings ) {
		if ( ! isset( $settings['toggle_on_color'] ) && isset( $settings['progress_active_bg_color'] ) ) {
			self::set_toggle_slider_colors( $settings );
		}

		return $settings;
	}

	/**
	 * @since 3.01.01
	 */
	private static function set_toggle_slider_colors( &$settings ) {
		$settings['toggle_font_size'] = $settings['font_size'];
		$settings['toggle_on_color']  = $settings['progress_active_bg_color'];
		$settings['toggle_off_color'] = $settings['border_color'];

		$settings['slider_font_size'] = $settings['field_font_size'];
		$settings['slider_color']     = $settings['progress_active_bg_color'];
		$settings['slider_bar_color'] = $settings['border_color'];
	}

	/**
	 * @since 3.0
	 */
	public static function include_pro_fields_ajax_css() {
		header('Content-type: text/css');

		$frm_style = new FrmStyle();
		$defaults = $frm_style->get_defaults();
		$important = self::is_important( $defaults );

		include( FrmProAppHelper::plugin_path() . '/css/pro_fields.css.php' );
	}

	public static function output_single_style( $settings ) {
		$important = empty( $settings['important_style'] ) ? '' : ' !important';

		// calculate the top position based on field padding
		$top_pad = explode( ' ', $settings['field_pad'] );
		$top_pad = reset( $top_pad ); // the top padding is listed first
		$pad_unit = preg_replace( '/[0-9]+/', '', $top_pad ); //px, em, rem...
		$top_margin = (int) str_replace( $pad_unit, '', $top_pad ) / 2;

		include( FrmProAppHelper::plugin_path() . '/css/single-style.css.php' );
	}

	private static function view_folder() {
		return FrmProAppHelper::plugin_path() . '/classes/views/styles';
	}

	private static function is_important( $defaults ) {
		return ( isset( $defaults['important_style'] ) && ! empty( $defaults['important_style'] ) ) ? ' !important' : '';
	}
}
