<?php

class FrmProSettingsController{

    public static function license_box(){
		$edd_update = new FrmProEddController();
		$a = FrmAppHelper::simple_get( 't', 'sanitize_title', 'general_settings' );
        remove_action('frm_before_settings', 'FrmSettingsController::license_box');
		$show_creds_form = self::show_license_form();
        include(FrmAppHelper::plugin_path() .'/pro/classes/views/settings/license_box.php');
    }

	public static function standalone_license_box() {
		$edd_update = new FrmProEddController();
		if ( self::show_license_form() ) {
			include(FrmAppHelper::plugin_path() .'/pro/classes/views/settings/standalone_license_box.php');
		}
	}

	private static function show_license_form() {
		return ( ! is_multisite() || current_user_can( 'setup_network' ) || ! get_site_option( $edd_update->pro_wpmu_store ) );
	}

    public static function general_style_settings($frm_settings){
        include(FrmAppHelper::plugin_path() .'/pro/classes/views/settings/general_style.php');
    }

    public static function more_settings($frm_settings){
        $frmpro_settings = new FrmProSettings();
        require(FrmAppHelper::plugin_path() .'/pro/classes/views/settings/form.php');
    }

    public static function update($params){
        global $frmpro_settings;
        $frmpro_settings = new FrmProSettings();
        $frmpro_settings->update($params);
    }

    public static function store(){
        global $frmpro_settings;
        $frmpro_settings->store();
    }

}
