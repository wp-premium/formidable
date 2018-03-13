<?php

/**
 * @since 3.0
 */
class FrmProFieldRte extends FrmFieldType {

	/**
	 * @var string
	 * @since 3.0
	 */
	protected $type = 'rte';

	protected $is_tall = true;

	protected function include_form_builder_file() {
		return FrmProAppHelper::plugin_path() . '/classes/views/frmpro-fields/back-end/field-' . $this->type . '.php';
	}

	protected function field_settings_for_type() {
		$settings = array(
			'size'          => true,
			'unique'        => true,
			'default_blank' => false,
		);

		FrmProFieldsHelper::fill_default_field_display( $settings );
		return $settings;
	}

	protected function extra_field_opts() {
		return array(
			'max' => 7,
		);
	}

	protected function prepare_display_value( $value, $atts ) {
		FrmFieldsHelper::run_wpautop( $atts, $value );
		return $value;
	}

	protected function include_front_form_file() {
		return FrmProAppHelper::plugin_path() . '/classes/views/frmpro-fields/front-end/rte.php';
	}

	/**
	 * If submitting with Ajax or on preview page and tinymce is not loaded yet, load it now
	 */
	protected function load_field_scripts( $args ) {
		if ( ! FrmAppHelper::is_admin() ) {
			global $frm_vars;
			$load_scripts = ( FrmAppHelper::doing_ajax() || FrmAppHelper::is_preview_page() ) && ( ! isset( $frm_vars['tinymce_loaded'] ) || ! $frm_vars['tinymce_loaded'] );
			if ( $load_scripts ) {
				add_action( 'wp_print_footer_scripts', '_WP_Editors::editor_js', 50 );
				add_action( 'wp_print_footer_scripts', '_WP_Editors::enqueue_scripts', 1 );
				$frm_vars['tinymce_loaded'] = true;
			}
		}
	}
}
