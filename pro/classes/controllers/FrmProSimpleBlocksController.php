<?php

class FrmProSimpleBlocksController {

	/**
	 * Adds View values to info sent to block JS
	 *
	 * @param $script_vars
	 *
	 * @return mixed
	 */
	public static function block_editor_assets() {
		$version = FrmAppHelper::plugin_version();

		wp_register_script(
			'formidable-view-selector',
			FrmProAppHelper::plugin_url() . '/js/frm_blocks.js',
			array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-components', 'wp-editor' ),
			$version,
			true
		);

		$script_vars = array(
			'views'        => self::get_views_options(),
			'show_counts'  => FrmProDisplaysHelper::get_show_counts(),
			'view_options' => FrmProDisplaysHelper::get_frm_options_for_views(),
			'name'         => FrmAppHelper::get_menu_name() . ' ' . __( 'Views', 'formidable-pro' ),
		);

		wp_localize_script( 'formidable-view-selector', 'formidable_view_selector', $script_vars );
		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'formidable-view-selector', 'formidable-pro', FrmProAppHelper::plugin_path() . '/languages' );
		}
	}

	/**
	 * Returns an array of Views options with name as the label and the id as the value, sorted by label
	 *
	 * @return array
	 */
	private static function get_views_options() {
		$views         = FrmProDisplay::getAll( array(), 'post_title' );
		$views_options = array_map( 'self::set_view_options', $views );
		$views_options = array_reverse( $views_options );

		return $views_options;
	}

	/**
	 * For a View, returns an array with the title as label and id as value
	 *
	 * @param $view
	 *
	 * @return array
	 */
	private static function set_view_options( $view ) {
		return array(
			'label' => $view->post_title,
			'value' => $view->ID,
		);
	}

	/**
	 * Registers simple View block
	 */
	public static function register_simple_view_block() {
		if ( ! is_callable( 'register_block_type' ) ) {
			return;
		}

		if ( is_admin() ) {
			// register back-end scripts
			add_action( 'enqueue_block_editor_assets', 'FrmProSimpleBlocksController::block_editor_assets' );
		}

		register_block_type(
			'formidable/simple-view',
			array(
				'attributes'      => array(
					'viewId'          => array(
						'type' => 'string',
					),
					'filter'          => array(
						'type' => 'string',
						'default' => 'limited',
					),
					'useDefaultLimit' => array(
						'type'    => 'boolean',
						'default' => false,
					),
				),
				'editor_style'    => 'formidable',
				'editor_script'   => 'formidable-view-selector',
				'render_callback' => 'FrmProSimpleBlocksController::simple_view_render',
			)
		);
	}

	/**
	 * Renders a View given the specified attributes.
	 *
	 * @param $attributes
	 *
	 * @return string
	 */
	public static function simple_view_render( $attributes ) {
		if ( ! isset( $attributes['viewId'] ) ) {
			return '';
		}

		$params = array_filter( $attributes );

		if ( isset( $params['useDefaultLimit'] ) && ( $params['useDefaultLimit'] ) ) {
			$params['limit'] = 20;
		}
		unset( $params['useDefaultLimit'] );

		$params['id'] = $params['viewId'];
		unset( $params['viewId'] );

		return FrmProDisplaysController::get_shortcode( $params );
	}
}
