<?php

class FrmProEddController extends FrmAddon {

	public $plugin_file;
	public $plugin_name = 'Formidable Pro';
	public $download_id = 93790;
	private $pro_cred_store  = 'frmpro-credentials';
	private $pro_auth_store  = 'frmpro-authorized';
	public $pro_wpmu_store  = 'frmpro-wpmu-sitewide';
	private $pro_wpmu = false;
	protected $get_beta = false;

	public function __construct() {
		$this->version = FrmProDb::$plug_version;
		$this->set_download();

        if ( $this->get_license() && is_multisite() && get_site_option( $this->pro_wpmu_store ) ) {
            $this->pro_wpmu = true;
        }

		global $frm_vars;
		$frm_vars['pro_is_authorized'] = $this->pro_is_authorized();

		parent::__construct();
	}

	public static function load_hooks() {
		// don't use the addons page
	}

	/**
	 * This isn't really beta, but we need to serve two different downloads
	 * "beta" is the nested version with formidable/pro that we will be phasing out
	 * @since 3.0
	 */
	private function set_download() {
		$path = FrmProAppHelper::plugin_path();
		if ( substr( $path, -4 ) === '/pro' ) {
			// this is nested
			$this->plugin_file = FrmAppHelper::plugin_path() . '/formidable.php';
			$this->get_beta = true;
		} else {
			$this->plugin_file = $path . '/formidable-pro.php';
		}
	}

	public function set_license( $license ) {
		update_option( $this->pro_cred_store, array( 'license' => $license ) );
	}

	public function get_license() {
		if ( is_multisite() && get_site_option( $this->pro_wpmu_store ) ) {
			$creds = get_site_option( $this->pro_cred_store );
		} else {
			$creds = get_option( $this->pro_cred_store );
		}

		$license = '';
		if ( $creds && is_array( $creds ) && isset( $creds['license'] ) ) {
			$license = $creds['license'];
			if ( strpos( $license, '-' ) ) {
				// this is a fix for licenses saved in the past
				$license = strtoupper( $license );
			}
		}

		if ( empty( $license ) ) {
			$license = $this->activate_defined_license();
		}

		return $license;
	}

	public function get_defined_license() {
		return defined( 'FRM_PRO_LICENSE' ) ? FRM_PRO_LICENSE : false;
	}

	public function clear_license() {
        delete_option( $this->pro_cred_store );
        delete_option( $this->pro_auth_store );
        delete_site_option( $this->pro_cred_store );
        delete_site_option( $this->pro_auth_store );
		parent::clear_license();
	}

	public function set_active( $is_active ) {
		$is_active = ( $is_active == 'valid' );
		$creds = $this->get_pro_cred_form_vals();

        if ( is_multisite() ) {
            update_site_option( $this->pro_wpmu_store, $creds['wpmu'] );
		}

        if ( $creds['wpmu'] ) {
            update_site_option( $this->pro_cred_store, $creds );
            update_site_option( $this->pro_auth_store, $is_active );
        } else {
            update_option( $this->pro_auth_store, $is_active );
        }

		// update style sheet to make sure pro css is included
		$frm_style = new FrmStyle();
		$frm_style->update( 'default' );

		FrmAppHelper::save_combined_js();
	}

	private function get_pro_cred_form_vals() {
		$license = isset( $_POST['license'] ) ? sanitize_text_field( $_POST['license'] ) : $this->get_license();
		$wpmu = isset( $_POST['wpmu'] ) ? absint( $_POST['wpmu'] ) : $this->pro_wpmu;

		return compact('license', 'wpmu');
	}

	public function show_license_message( $file, $plugin ) {
		$wp_list_table = _get_list_table( 'WP_Plugins_List_Table' );
		echo '<tr class="plugin-update-tr active"><td colspan="' . esc_attr( $wp_list_table->get_column_count() ) . '" class="plugin-update colspanchange"><div class="update-message">';
		echo sprintf( esc_html__( 'Your %1$s license key is missing. Please add it on the %2$sGlobal Settings page%3$s.', 'formidable-pro' ), esc_html( $this->plugin_name ), '<a href="' . esc_url( admin_url('admin.php?page=formidable-settings' ) ) . '">', '</a>' );
		$id = sanitize_title( $plugin['Name'] );
		echo '<script type="text/javascript">var d = document.getElementById("' . esc_attr( $id ) . '");if ( d !== null ){ d.className = d.className + " update"; }</script>';
		echo '</div></td></tr>';
	}

	function pro_is_authorized() {
		$license = $this->get_license();
		if ( empty( $license ) ) {
			return false;
		}

		if ( is_multisite() && $this->pro_wpmu ) {
			$authorized = get_site_option( $this->pro_auth_store );
		} else {
			$authorized = get_option( $this->pro_auth_store );
		}

		return $authorized;
	}

	function pro_is_installed_and_authorized() {
		return $this->pro_is_authorized();
	}

    public function pro_cred_form() {
        global $frm_vars;
		$config_license = $this->get_defined_license();

?>
<div id="frm_license_bottom" class="<?php echo esc_attr( $frm_vars['pro_is_authorized'] ? '' : 'frm_hidden' ) ?>">
<div class="frm_pro_installed">
<div>
	<p>
		<strong>
			<?php esc_html_e( 'Formidable Pro is Installed', 'formidable-pro' ); ?>
		</strong>
		<?php if ( ! $config_license ) { ?>
			<a href="#" id="frm_deauthorize_link" class="alignright" data-plugin="<?php echo esc_attr( $this->plugin_slug ) ?>">
				<?php esc_html_e( 'Deauthorize this site', 'formidable-pro' ) ?>
			</a>
		<?php } ?>
		<div class="spinner"></div>
	</p>
</div>
</div>
</div>

<div id="frm_license_top">
	<?php
	$this->display_form();

	if ( ! $frm_vars['pro_is_authorized'] ) {
		?>
    <p>Already signed up? <a href="https://formidableforms.com/account/licenses/?utm_source=WordPress&utm_medium=settings-license&utm_campaign=proplugin" target="_blank"><?php esc_html_e( 'Get your license number', 'formidable-pro' ) ?></a>.</p>
    <?php } ?>
</div>

<div class="frm_pro_license_msg frm_hidden"></div>
<div class="clear"></div>

<?php
    }

	/**
	 * this is the view for the license form
	 */
	function display_form() {
		global $frm_vars;

		if ( $frm_vars['pro_is_authorized'] ) {
			$placeholder = __( 'Verify a different license key', 'formidable-pro' );
			$value = '•••••••••••••••••••';
		} else {
			$placeholder = __( 'Enter your license key here', 'formidable-pro' );
			$value = '';
		}
		?>
<div id="pro_cred_form">

	<input type="text" name="proplug-license" value="<?php echo esc_attr( $value ); ?>" class="frm_full" placeholder="<?php echo esc_attr( $placeholder ); ?>" id="edd_<?php echo esc_attr( $this->plugin_slug ); ?>_license_key" />

	<?php
	if ( is_multisite() ) {
		$creds = $this->get_pro_cred_form_vals();
		?>
		<br/>
		<label for="proplug-wpmu">
			<input type="checkbox" value="1" name="proplug-wpmu" id="proplug-wpmu" <?php checked( $creds['wpmu'], 1 ); ?> />
			<?php esc_html_e( 'Use this license to enable Formidable Pro site-wide', 'formidable-pro' ); ?>
		</label>
	<?php } ?>
	<p>
		<input class="button-secondary frm_authorize_link" type="button" data-plugin="<?php echo esc_attr( $this->plugin_slug ); ?>" value="<?php esc_attr_e( 'Save License', 'formidable-pro' ); ?>" />
	</p>
</div>
<?php
    }
}
