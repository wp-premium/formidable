<?php

global $frm_update;
$frm_update  = new FrmUpdatesController();

$frmedd_update  = new FrmProEddController();

// load the license form
if ( FrmAppHelper::is_admin_page('formidable-settings') ) {
    add_action('frm_before_settings', 'FrmProSettingsController::license_box', 1);
}

if ( ! $frm_vars['pro_is_authorized'] ) {
    return;
}

$frm_vars['next_page'] = $frm_vars['prev_page'] = array();
$frm_vars['pro_is_installed'] = 'deprecated';
add_filter('frm_pro_installed', '__return_true');

add_filter('frm_load_controllers', 'frmpro_load_controllers');
function frmpro_load_controllers( $controllers ) {
    $controllers[] = 'FrmProHooksController';
    return $controllers;
}

