<?php
// Contains all the functions necessary to provide an update mechanism for FormidableForms!

class FrmUpdatesController{

    // Where all the vitals are defined for this plugin
    var $plugin_nicename        = 'formidable';
    var $plugin_name            = 'formidable/formidable.php';
    var $plugin_url             = 'https://formidableforms.com/';
    var $pro_mothership         = 'http://api.strategy11.com/plugin-updates/';
    var $pro_cred_store         = 'frmpro-credentials';
    var $pro_auth_store         = 'frmpro-authorized';
    var $pro_wpmu_store         = 'frmpro-wpmu-sitewide';
    var $pro_last_checked_store = 'frm_autoupdate';
    var $pro_check_interval     = 0; // Don't check. Pro updates will force over free updates
    var $timeout                = 10;
	var $update_to;

    var $pro_wpmu = false;

    var $pro_error_message_str;
    var $license        = '';

    function __construct(){
		_deprecated_function( __FUNCTION__, '2.3' );

        $this->pro_error_message_str = __( 'Your Formidable Pro License was Invalid', 'formidable' );

        // Retrieve Pro Credentials
        if (is_multisite() && get_site_option($this->pro_wpmu_store)){
            $creds = get_site_option($this->pro_cred_store);
            $this->pro_wpmu = true;
        } else {
            $creds = get_option($this->pro_cred_store);
        }

        if ( $creds && is_array($creds) ) {
            $cred_array = array( 'license' => '' );
            $creds = array_intersect_key($creds, $cred_array);
            foreach ( $creds as $k => $cred ) {
                $this->{$k} = $cred;
            }
        }
    }

    function pro_is_authorized( $force_check = false ) {
        if ( empty($this->license) ) {
			return false;
        }

        if ( is_multisite() && $this->pro_wpmu ) {
            $authorized = get_site_option($this->pro_auth_store);
        } else {
            $authorized = get_option($this->pro_auth_store);
        }

        if ( ! $force_check ) {
            return $authorized;
        }

        if ( ! empty($this->license) ) {
            $new_auth = $this->check_license();
            return $new_auth['auth'];
        }

        return false;
    }

    function pro_is_installed_and_authorized(){
        return $this->pro_is_authorized();
    }

    function get_pro_cred_form_vals(){
		$license = isset( $_POST['proplug-license'] ) ? sanitize_title( $_POST['proplug-license'] ) : $this->license;
        $wpmu = (isset($_POST['proplug-wpmu'])) ? true : $this->pro_wpmu;

        return compact('license', 'wpmu');
    }

    function check_license( $license = false ) {
        $save = true;
		if ( empty( $license ) ) {
            $license = $this->license;
            $save = false;
        }

        if ( empty( $license ) ) {
            return array( 'auth' => false, 'response' => __( 'Please enter a license number', 'formidable' ));
		}

        $domain = home_url();
        $args = compact('domain');

        $act = $this->send_mothership_request($this->plugin_nicename .'/activate/'. $license, $args);

        if($save){
            $auth = false;
            if ( is_array($act) ) {

                $auth = is_array($act) ? true : false;
                $wpmu = (isset($_POST) && isset($_POST['proplug-wpmu'])) ? true : $this->pro_wpmu;

                //save response
                if ( is_multisite() ) {
                    update_site_option($this->pro_wpmu_store, $wpmu);
                }

                if ($wpmu){
                    update_site_option($this->pro_cred_store, compact('license', 'wpmu'));
                    update_site_option($this->pro_auth_store, $auth);
                }else{
                    update_option($this->pro_cred_store, compact('license', 'wpmu'));
                    update_option($this->pro_auth_store, $auth);
                }

            }

            return array( 'auth' => $auth, 'response' => $act);
        }

        return array( 'auth' => false, 'response' => __( 'Please enter a license number', 'formidable' ));
    }

	/**
	 * Check if the transient says there is an update when the plugin has already been updated
	 *
	 * @return boolean - true if the plugin is up to date
	 * @since 2.0.5
	 */
	function is_current_version( $transient ) {
		if ( empty( $transient->checked ) || ! isset( $transient->checked[ $this->plugin_name ] ) ) {
			return false;
		}

		$response = ! isset( $transient->response ) || empty( $transient->response );
		if ( $response ) {
			return true;
		}

		return isset( $transient->response ) && isset( $transient->response[ $this->plugin_name ] ) && $transient->checked[ $this->plugin_name ] == $transient->response[ $this->plugin_name ]->new_version;
	}

	/**
	 * If the update url is for the free version, force the api check to get the pro donwload url
	 *
	 * @return boolean - true if api check should be forced
	 * @since 2.0.5
	 */
	function set_force_check( $version_info, $transient ) {
		if ( ! $version_info || ! is_array( $version_info ) || ! isset( $version_info['version'] ) || ! isset( $version_info['url'] ) ) {
			return true;
		}

		return ( ! strpos( $transient->response[ $this->plugin_name ]->url, 'formidableforms.com' ) || version_compare( $version_info['version'], FrmAppHelper::plugin_version(), '<=' ) || $version_info['url'] != $transient->response[ $this->plugin_name ]->package );
	}

	/**
	 * If the license is not active on this site, the update will not be allowed.
	 * If there is an upgrade notice, that tells us the user is not allowed.
	 *
	 * @return boolean - true if user is not allowed to update to pro
	 * @since 2.0.5
	 */
	function pro_update_disallowed( $transient ) {
		return (
			isset( $transient->response ) && isset( $transient->response[ $this->plugin_name ] ) &&
			isset( $transient->response[ $this->plugin_name ]->upgrade_notice ) &&
			! empty( $transient->response[ $this->plugin_name ]->upgrade_notice )
		);
	}

    function queue_addon_update( $transient, $plugin, $force = false, $checked = false ) {
        if ( $force !== true ) {
            // make sure another plugin isn't inserting other data
            $force = false;
        }

        if ( ! $this->pro_is_authorized() || ! is_object($transient) || $checked || ( empty($transient->checked) && ! $force ) ) {
            return $transient;
        }

		if ( ! empty( $transient->checked ) && isset( $transient->checked[ $plugin->plugin_name ] ) ) {
			$installed_version = $transient->checked[ $plugin->plugin_name ];
		} else if ( $plugin->plugin_nicename == 'formidable' ) {
			$installed_version = 1;
		} else {
			// don't continue if we don't know the current plugin version
			return $transient;
		}

        // check if we have already checked this page load
        global $frm_vars;
        if ( ! isset($frm_vars['plugins_checked']) ) {
            $frm_vars['plugins_checked'] = array();
        } else if ( isset($frm_vars['plugins_checked'][$plugin->plugin_name]) ) {
            if ( $frm_vars['plugins_checked'][$plugin->plugin_name] != 'latest' ) {
                $transient->response[$plugin->plugin_name] = $frm_vars['plugins_checked'][$plugin->plugin_name];
            }
            return $transient;
        }

		if ( $plugin->plugin_nicename == 'formidable' && isset( $transient->response[ $plugin->plugin_name ] ) ) {
			$plugin->update_to = $transient->response[ $plugin->plugin_name ]->new_version;
		}
        $version_info = $this->get_version($force, $plugin);

        if ( $version_info && isset($version_info['version']) && ( $force || version_compare($version_info['version'], $installed_version, '>') ) ) {
			if ( $plugin->plugin_nicename != 'formidable' && isset( $transient->response[ $plugin->plugin_name ] ) && $transient->response[ $plugin->plugin_name ]->new_version == $version_info['version'] ) {
                $frm_vars['plugins_checked'][$plugin->plugin_name] = $transient->response[$plugin->plugin_name];
				return $transient;
            }

			$plugin_update = new stdClass();
			$plugin_update->id = 0;
			$plugin_update->slug = $plugin->plugin_nicename;
			$plugin_update->plugin = $plugin->plugin_name;
			$plugin_update->new_version = $version_info['version'];
			$plugin_update->url = $this->plugin_url;

			if ( isset( $version_info['url'] ) ) {
				$plugin_update->package = $version_info['url'];
			} else {
                //new version available, but no permission
                $expired = isset($version_info['expired']) ? __( 'expired', 'formidable' ) : __( 'invalid', 'formidable' );
				$plugin_update->upgrade_notice = sprintf( __( 'An update is available, but your license is %s.', 'formidable' ), $expired );
				add_filter( 'frm_pro_update_msg', array( &$this, 'no_permission_msg' ) );
            }

			$transient->response[ $plugin->plugin_name ] = $plugin_update;

			// add this plugin to the checked array to prevent multiple checks per page load
			$frm_vars['plugins_checked'][ $plugin->plugin_name ] = $transient->response[ $plugin->plugin_name ];
        } else {
			$frm_vars['plugins_checked'][ $plugin->plugin_name ] = 'latest';

			if ( ! $version_info && isset( $transient->response[ $plugin->plugin_name ] ) ) {
				unset( $transient->response[ $plugin->plugin_name ] );

                // check again in 1 hour if there was an error to prevent timeout loops
                set_site_transient( $plugin->pro_last_checked_store, 'latest', 60*60 );
            }
        }

        return $transient;
    }

    function get_version($force = false, $plugin = false) {
        global $frm_vars;
        if ( $plugin && $plugin->plugin_nicename != $this->plugin_nicename ) {
            //don't check for update if pro is not installed
            if ( ! $frm_vars['pro_is_authorized'] ) {
                return false;
            }
        }

		if ( ! isset( $frm_vars['forced'] ) ) {
			$frm_vars['forced'] = array();
		}

		$version_info = false;
        if ( ! $force ) {
            // if not forced, allow version_info to be equal to 'latest'
            $version_info = get_site_transient( $plugin->pro_last_checked_store );
		} else if ( isset( $frm_vars['forced'][ $plugin->plugin_nicename ] ) ) {
			$version_info = $frm_vars['forced'][ $plugin->plugin_nicename ];
			if ( ! is_array( $version_info ) ) {
				return false;
			}
		} else if ( $plugin->plugin_nicename == 'formidable' && ! empty( $plugin->update_to ) ) {
			$version_info = get_site_transient( 'frm_update_' . $plugin->plugin_nicename . $plugin->update_to );
		}

		if ( $version_info && $version_info != 'latest' && ! is_array( $version_info ) ) {
            $version_info = false;
        }

		if ( ! $version_info ) {
            $errors = false;

            if ( ! empty($this->license) ) {
                $domain = home_url();
                $args = compact('domain');

                $version_info = $this->send_mothership_request($plugin->plugin_nicename .'/info/'. $this->license, $args);
                if ( ! is_array($version_info) ) {
                    $errors = true;
                }
            }

            if ( ! isset($version_info) || $errors ) {
                // query for the current version
                $version_info = $this->send_mothership_request($plugin->plugin_nicename .'/latest');
                $errors = ! is_array($version_info) ? true : false;
            }

            //don't force again on same page
			$frm_vars['forced'][ $plugin->plugin_nicename ] = $version_info;

			if ( $errors ) {
                return false;
			}

            // store in transient for 24 hours
			if ( $plugin->plugin_nicename == 'formidable' && isset( $version_info['version'] ) ) {
				set_site_transient( 'frm_update_' . $plugin->plugin_nicename . $version_info['version'], $version_info, 60 * 60 * 5 );
			}
            set_site_transient( $plugin->pro_last_checked_store, $version_info, $plugin->pro_check_interval );
        }

        return (array) $version_info;
    }

    function send_mothership_request( $endpoint, $args = array(), $domain = '' ) {
        if ( empty($domain) ) {
            $domain = $this->pro_mothership;
        }
        $uri = trailingslashit($domain . $endpoint);

        $arg_array = array(
            'body'      => $args,
            'timeout'   => $this->timeout,
            'sslverify' => false,
            'user-agent' => 'Formidable/'. FrmAppHelper::plugin_version() .'; '. get_bloginfo( 'url' )
        );

        $resp = wp_remote_post($uri, $arg_array);
        $body = wp_remote_retrieve_body( $resp );

        if(is_wp_error($resp)){
            $message = sprintf(__( 'You had an error communicating with the Formidable Forms API. %1$sClick here%2$s for more information.', 'formidable' ), '<a href="https://formidableforms.com/knowledgebase/why-cant-i-activate-formidable-pro/" target="_blank">', '</a>');
            if(is_wp_error($resp))
                $message .= ' '. $resp->get_error_message();
            return $message;
        }else if($body == 'error' || is_wp_error($body)){
            return __( 'You had an HTTP error connecting to the Formidable Forms API', 'formidable' );
        }else{
            $json_res = json_decode($body, true);
            if ( null !== $json_res ) {
                if ( is_array($json_res) && isset($json_res['error']) ) {
                    return $json_res['error'];
                } else {
                    return $json_res;
                }
            }else if(isset($resp['response']) && isset($resp['response']['code'])){
                return sprintf(__( 'There was a %1$s error: %2$s', 'formidable' ), $resp['response']['code'], $resp['response']['message'] .' '. $resp['body']);
            }
        }

        return __( 'Your License Key was invalid', 'formidable' );
    }

    function no_permission_msg(){
        return __( 'A Formidable Forms update is available, but your license is invalid.', 'formidable' );
    }
}
