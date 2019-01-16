<?php

/**
 * @since 3.0
 */
class FrmProInstallPlugin {

	protected $plugin_file; // format: folder/filename.php
	protected $plugin_slug;

	public function __construct( $atts ) {
		$this->plugin_file = $atts['plugin_file'];
		list( $slug, $file ) = explode( '/', $this->plugin_file );
		$this->plugin_slug = $slug;
	}

	public function maybe_install_and_activate() {
		if ( $this->is_installed() && $this->is_active() ) {
			return;
		}

		if ( $this->is_installed() ) {
			$this->activate_plugin();
		} else {
			$this->install_plugin();
		}
	}

	public function is_installed() {
		return is_dir( WP_PLUGIN_DIR . '/' . $this->plugin_slug );
	}

	public function is_active() {
		return is_plugin_active( $this->plugin_file );
	}

	protected function install_url() {
		return wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=' . $this->plugin_slug ), 'install-plugin_' . $this->plugin_slug );
	}

	protected function activate_url() {
		return wp_nonce_url( self_admin_url( 'plugins.php?action=activate&plugin=' . $this->plugin_file ), 'activate-plugin_' . $this->plugin_file );
	}

	protected function install_plugin() {
		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
		include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );

		$api = plugins_api( 'plugin_information', array(
			'slug'   => $this->plugin_slug,
			'fields' => array(
				'short_description' => false,
				'sections'          => false,
				'requires'          => false,
				'rating'            => false,
				'ratings'           => false,
				'downloaded'        => false,
				'last_updated'      => false,
				'added'             => false,
				'tags'              => false,
				'compatibility'     => false,
				'homepage'          => false,
				'donate_link'       => false,
			),
		) );

		if ( is_wp_error( $api ) ) {
			return;
		}

		$skin     = new WP_Ajax_Upgrader_Skin();
		$upgrader = new Plugin_Upgrader( $skin );
		$result   = $upgrader->install( $api->download_link );

		if ( is_wp_error( $result ) || is_wp_error( $skin->result ) || is_null( $result ) ) {
			return;
		}

		$this->activate_plugin();
	}

	public function activate_plugin() {
		if ( ! $this->is_active() ) {
			$active = get_option( 'active_plugins', array() );
			$active[] = $this->plugin_file;
			sort( $active );
			update_option( 'active_plugins', $active );
		}
	}
}
