<?php

class FrmProFormsController {

	/**
	 * Stars need the formidablepro.js
	 * @since 3.0
	 */
	public static function load_builder_scripts() {
		$pro_js = FrmProAppController::get_pro_js_files();
		$js_key = 'formidablepro';
		$js = $pro_js[ $js_key ];
		wp_enqueue_script( $js_key, FrmProAppHelper::plugin_url() . $js['file'], $js['requires'], $js['version'], true );
	}

	public static function admin_js() {
		$frm_settings = FrmAppHelper::get_settings();

		add_filter( 'manage_' . sanitize_title( $frm_settings->menu ) . '_page_formidable-entries_columns', 'FrmProEntriesController::manage_columns', 25 );

		$version = FrmAppHelper::plugin_version();
		wp_register_style( 'formidable-dropzone', FrmProAppHelper::plugin_url() . '/css/dropzone.css', array(), $version );
		wp_register_style( 'formidable-pro-fields', admin_url( 'admin-ajax.php?action=pro_fields_css' ), array(), $version );

		if ( FrmAppHelper::is_admin_page() ) {
			wp_enqueue_style( 'formidable-pro-fields' );
		}

		$page = FrmAppHelper::simple_get( 'page', 'sanitize_title' );
		if ( $page !== 'formidable-entries' ) {
			return;
		}

		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_style( 'formidable-pro-fields' );

		self::maybe_load_accordion_scripts( $frm_settings );

		$theme_css = FrmStylesController::get_style_val( 'theme_css' );
		if ( $theme_css == -1 ) {
			return;
		}

		wp_enqueue_style( $theme_css, FrmStylesHelper::jquery_css_url( $theme_css ) );
	}

	public static function enqueue_footer_js() {
		global $frm_vars, $frm_input_masks;

		if ( empty( $frm_vars['forms_loaded'] ) ) {
			return;
		}

		FrmProAppController::register_scripts();

		if ( ! FrmAppHelper::doing_ajax() ) {
			wp_enqueue_script( 'formidable' );
			wp_enqueue_script( 'formidablepro' );
			FrmAppHelper::localize_script( 'front' );
		}

		if ( isset( $frm_vars['tinymce_loaded'] ) && $frm_vars['tinymce_loaded'] ) {
			_WP_Editors::enqueue_scripts();
		}

		// trigger jQuery UI to be loaded on every page
		self::add_js();

		if ( isset( $frm_vars['datepicker_loaded'] ) && ! empty( $frm_vars['datepicker_loaded'] ) ) {
			if ( is_array( $frm_vars['datepicker_loaded'] ) ) {
				foreach ( $frm_vars['datepicker_loaded'] as $fid => $o ) {
					if ( ! $o ) {
						unset( $frm_vars['datepicker_loaded'][ $fid ] );
					}
					unset( $fid, $o );
				}
			}

			if ( ! empty( $frm_vars['datepicker_loaded'] ) ) {
				wp_enqueue_script( 'jquery-ui-datepicker' );
				FrmStylesHelper::enqueue_jquery_css();
			}
		}

		if ( isset( $frm_vars['chosen_loaded'] ) && $frm_vars['chosen_loaded'] ) {
			wp_enqueue_script('jquery-chosen');
		}

		if ( isset( $frm_vars['dropzone_loaded'] ) && ! empty( $frm_vars['dropzone_loaded'] ) ) {
			wp_enqueue_script( 'dropzone' );
		}

		$frm_input_masks = apply_filters( 'frm_input_masks', $frm_input_masks, $frm_vars['forms_loaded'] );
		foreach ( (array) $frm_input_masks as $fid => $o ) {
			if ( ! $o ) {
				unset( $frm_input_masks[ $fid ] );
			}
			unset( $fid, $o );
		}

		if ( ! empty( $frm_input_masks ) ) {
			wp_enqueue_script( 'jquery-maskedinput' );
		}

		if ( isset( $frm_vars['google_graphs'] ) && ! empty( $frm_vars['google_graphs'] ) ) {
			wp_enqueue_script( 'google_jsapi', 'https://www.google.com/jsapi' );
		}
	}

	public static function footer_js() {
		global $frm_vars;

		$frm_vars['footer_loaded'] = true;

		if ( empty( $frm_vars['forms_loaded'] ) ) {
			return;
		}

		include( FrmProAppHelper::plugin_path() . '/classes/views/frmpro-entries/footer_js.php' );

		/**
		* Add custom scripts after the form scripts are done loading
		* @since 2.0.6
		*/
		do_action( 'frm_footer_scripts', $frm_vars['forms_loaded'] );
	}

	public static function add_js() {
		if ( FrmAppHelper::is_admin() ) {
			return;
		}

		$frm_settings = FrmAppHelper::get_settings();

		global $frm_vars;
		if ( $frm_settings->jquery_css ) {
			$frm_vars['datepicker_loaded'][] = true;
		}

		self::maybe_load_accordion_scripts( $frm_settings );
	}

	public static function print_ajax_scripts( $keep = '' ) {
		self::enqueue_footer_js();

		if ( $keep !== 'all' ) {
			if ( $keep === 'none' ) {
				$keep_scripts = $keep_styles = array();
			} else {
				$keep_scripts = array(
					'recaptcha-api', 'jquery-frm-rating', 'jquery-chosen',
					'google_jsapi', 'dropzone', 'jquery-maskedinput',
				);
				$keep_styles = array( 'dashicons', 'jquery-theme' );

				if ( is_array( $keep ) ) {
					$keep_scripts = array_merge( $keep_scripts, $keep );
				}
			}

			global $wp_scripts, $wp_styles;
			$keep_scripts = apply_filters( 'frm_ajax_load_scripts', $keep_scripts );
			$registered_scripts = (array) $wp_scripts->registered;
			$registered_scripts = array_diff( array_keys( $registered_scripts ), $keep_scripts );
			self::mark_scripts_as_loaded( $registered_scripts );

			$keep_styles = apply_filters( 'frm_ajax_load_styles', $keep_styles );
			$registered_styles = (array) $wp_styles->registered;
			$registered_styles = array_diff( array_keys( $registered_styles ), $keep_styles );
			if ( ! empty( $registered_styles ) ) {
				$wp_styles->done = array_merge( $wp_styles->done, $registered_styles );
			}
		}

		wp_print_footer_scripts();
	}

	/**
	 * Used during ajax when we know jQuery has already been loaded
	 * Used when a form is loaded for edit-in-place
	 *
	 * @since 2.05
	 */
	public static function mark_jquery_as_loaded() {
		$mark_complete = array( 'jquery-core', 'jquery-migrate', 'jquery' );
		self::mark_scripts_as_loaded( $mark_complete );
	}

	/**
	 * @since 2.05
	 */
	private static function mark_scripts_as_loaded( $scripts ) {
		global $wp_scripts;
		$wp_scripts->done = array_merge( $wp_scripts->done, $scripts );
	}

	/**
	 * Check if the form is loaded after the wp_footer hook.
	 * If it is, we'll need to make sure the scripts are loaded.
	 */
	public static function after_footer_loaded() {
		global $frm_vars;

		if ( ! isset( $frm_vars['footer_loaded'] ) || ! $frm_vars['footer_loaded'] ) {
			wp_enqueue_script( 'formidablepro' );
			return;
		}

		self::enqueue_footer_js();

		print_late_styles();
		print_footer_scripts();

		self::footer_js();
	}

	private static function maybe_load_accordion_scripts( $frm_settings ) {
		if ( $frm_settings->accordion_js ) {
			_deprecated_function( 'Load accordion script in the Formidable Global Settings', '3.0', 'wp_enqueue_script("jquery-ui-widget") and wp_enqueue_script("jquery-ui-accordion")' );
			wp_enqueue_script( 'jquery-ui-widget' );
			wp_enqueue_script( 'jquery-ui-accordion' );
		}
	}

	/**
	 * Used for hiding the form on page load
	 * @since 2.3
	 */
	public static function head() {
		echo '<script type="text/javascript">document.documentElement.className += " js";</script>' . "\r\n";
	}

	public static function add_form_options( $values ) {
        global $frm_vars;

        $post_types = FrmProAppHelper::get_custom_post_types();
		$has_file_field = FrmField::get_all_types_in_form( $values['id'], 'file', 2, 'include' );

        require(FrmProAppHelper::plugin_path() . '/classes/views/frmpro-forms/add_form_options.php');
    }

	public static function add_form_page_options( $values ) {
		$page_fields = FrmField::get_all_types_in_form( $values['id'], 'break' );
		if ( $page_fields ) {
			$hide_rootline_class = empty( $values['rootline'] ) ? 'frm_hidden' : '';
			$hide_rootline_title_class = empty( $values['rootline_titles_on'] ) ? 'frm_hidden' : '';
			$i = 1;
			require( FrmProAppHelper::plugin_path() . '/classes/views/frmpro-forms/form_page_options.php' );
		}
	}

	public static function add_form_ajax_options( $values ) {
        global $frm_vars;

        $post_types = FrmProAppHelper::get_custom_post_types();

        require(FrmProAppHelper::plugin_path() . '/classes/views/frmpro-forms/add_form_ajax_options.php');
    }

    /**
     * Remove the noallow class on pro fields
     * @return string
     */
    public static function noallow_class() {
        return '';
    }

	public static function add_form_button_options( $values ) {
        global $frm_vars;

        $page_field = FrmProFormsHelper::has_field('break', $values['id'], true);

        $post_types = FrmProAppHelper::get_custom_post_types();

        $submit_conditions = $values['submit_conditions'];

        require(FrmProAppHelper::plugin_path() . '/classes/views/frmpro-forms/add_form_button_options.php');
    }

	public static function add_form_msg_options( $values ) {
        global $frm_vars;

        $post_types = FrmProAppHelper::get_custom_post_types();

        require(FrmProAppHelper::plugin_path() . '/classes/views/frmpro-forms/add_form_msg_options.php');
    }

	public static function instruction_tabs() {
        include(FrmProAppHelper::plugin_path() . '/classes/views/frmpro-forms/instruction_tabs.php');
    }

	public static function instructions() {
		$tags = array(
			'date'         => __( 'Current Date', 'formidable-pro' ),
			'time'         => __( 'Current Time', 'formidable-pro' ),
			'email'        => __( 'Email', 'formidable-pro' ),
			'login'        => __( 'Login', 'formidable-pro' ),
			'display_name' => __( 'Display Name', 'formidable-pro' ),
			'first_name'   => __( 'First Name', 'formidable-pro' ),
			'last_name'    => __( 'Last Name', 'formidable-pro' ),
			'user_id'      => __( 'User ID', 'formidable-pro' ),
			'user_meta key=whatever' => __( 'User Meta', 'formidable-pro' ),
			'user_role'    => __( 'User Role', 'formidable-pro' ),
			'post_id'      => __( 'Post ID', 'formidable-pro' ),
			'post_title'   => __( 'Post Title', 'formidable-pro' ),
			'post_author_email' => __( 'Author Email', 'formidable-pro' ),
			'post_meta key=whatever' => __( 'Post Meta', 'formidable-pro' ),
			'ip'           => __( 'IP Address', 'formidable-pro' ),
			'auto_id start=1' => __( 'Increment', 'formidable-pro' ),
			'get param=whatever' => array(
				'label' => __( 'GET/POST', 'formidable-pro' ),
				'title' => __( 'A variable from the URL or value posted from previous page.', 'formidable-pro' ) . ' ' . __( 'Replace \'whatever\' with the parameter name. In url.com?product=form, the variable is \'product\'. You would use [get param=product] in your field.', 'formidable-pro' ),
			),
			'server param=whatever' => array(
				'label' => __( 'SERVER', 'formidable-pro' ),
				'title' => __( 'A variable from the PHP SERVER array.', 'formidable-pro' ) . ' ' . __( 'Replace \'whatever\' with the parameter name. To get the url of the current page, use [server param="REQUEST_URI"] in your field.', 'formidable-pro' ),
			),
		);

		self::maybe_remove_ip( $tags );

		include( FrmProAppHelper::plugin_path() . '/classes/views/frmpro-forms/instructions.php' );
    }

	private static function maybe_remove_ip( &$tags ) {
		if ( ! FrmAppHelper::ips_saved() ) {
			unset( $tags['ip'] );
		}
	}

	public static function add_field_link( $field_type ) {
		return '<a href="#" class="frm_add_field">' . $field_type . '</a>';
    }

	public static function drag_field_class() {
		_deprecated_function( __METHOD__, '3.0' );
        return ' class="field_type_list"';
    }

	public static function formidable_shortcode_atts( $atts, $all_atts ) {
        global $frm_vars, $wpdb;

        // reset globals
        $frm_vars['readonly'] = $atts['readonly'];
        $frm_vars['editing_entry'] = false;
        $frm_vars['show_fields'] = array();

        if ( ! is_array($atts['fields']) ) {
            $frm_vars['show_fields'] = explode(',', $atts['fields']);
        }

        if ( ! empty($atts['exclude_fields']) ) {
            if ( ! is_array( $atts['exclude_fields'] ) ) {
                $atts['exclude_fields'] = explode(',', $atts['exclude_fields']);
            }

            $query = array(
                'form_id' => (int) $atts['id'],
                'id NOT' => $atts['exclude_fields'],
                'field_key NOT' => $atts['exclude_fields'],
            );

			$frm_vars['show_fields'] = FrmDb::get_col( $wpdb->prefix . 'frm_fields', $query );
        }

        if ( $atts['entry_id'] && $atts['entry_id'] == 'last' ) {
            $user_ID = get_current_user_id();
            if ( $user_ID ) {
				$frm_vars['editing_entry'] = FrmDb::get_var( $wpdb->prefix . 'frm_items', array( 'form_id' => $atts['id'], 'user_id' => $user_ID ), 'id', array( 'order_by' => 'created_at DESC' ) );
            }
        } else if ( $atts['entry_id'] ) {
            $frm_vars['editing_entry'] = $atts['entry_id'];
        }

        foreach ( $atts as $unset => $val ) {
			if ( is_array( $all_atts ) && isset( $all_atts[ $unset ] ) ) {
				unset( $all_atts[ $unset ] );
			}
            unset($unset, $val);
        }

        if ( is_array($all_atts) ) {
            foreach ( $all_atts as $att => $val ) {
                $_GET[ $att ] = $val;
                unset($att, $val);
            }
        }
    }

	public static function add_form_classes( $form ) {
		echo ' frm_pro_form ';

		if ( FrmProForm::is_ajax_on( $form ) ) {
			echo ' frm_ajax_submit ';
		}

		self::maybe_add_hide_class( $form );

		if ( current_user_can( 'activate_plugins' ) && current_user_can( 'frm_edit_forms' ) ) {
			echo ' frm-admin-viewing ';
		}
	}

	private static function maybe_add_hide_class( $form ) {
		$frm_settings = FrmAppHelper::get_settings();
		if ( $frm_settings->fade_form && FrmProForm::has_fields_with_conditional_logic( $form ) ) {
			echo ' frm_logic_form ';
		}
	}

	public static function form_fields_class( $class ) {
        global $frm_page_num;
        if ( $frm_page_num ) {
			$class .= ' frm_page_num_' . $frm_page_num;
        }

        return $class;
    }

	public static function form_hidden_fields( $form ) {
        if ( is_user_logged_in() && isset( $form->options['save_draft'] ) && $form->options['save_draft'] == 1 ) {
            echo '<input type="hidden" name="frm_saving_draft" class="frm_saving_draft" value="" />';
        }
    }

	public static function submit_button_label( $submit, $form ) {
        global $frm_vars;
		if ( ! FrmProFormsHelper::is_final_page( $form->id ) ) {
			$submit = $frm_vars['next_page'][ $form->id ];
			if ( is_object( $submit ) ) {
                $submit = $submit->name;
			}
        }
        return $submit;
    }

    public static function replace_shortcodes( $html, $form, $values = array() ) {
        preg_match_all("/\[(if )?(deletelink|back_label|back_hook|back_button|draft_label|save_draft|draft_hook)\b(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?/s", $html, $shortcodes, PREG_PATTERN_ORDER);

		if ( empty( $shortcodes[0] ) ) {
            return $html;
		}

		foreach ( $shortcodes[0] as $short_key => $tag ) {
            $replace_with = '';
			$atts = FrmShortcodeHelper::get_shortcode_attribute_array( $shortcodes[3][ $short_key ] );

			switch ( $shortcodes[2][ $short_key ] ) {
                case 'deletelink':
                    $replace_with = FrmProEntriesController::entry_delete_link($atts);
					break;
                case 'back_label':
                    $replace_with = isset($form->options['prev_value']) ? $form->options['prev_value'] : __( 'Previous', 'formidable-pro' );
					break;
                case 'back_hook':
                    $replace_with = apply_filters('frm_back_button_action', '', $form);
					break;
                case 'back_button':
                    global $frm_vars;
					if ( ! $frm_vars['prev_page'] || ! is_array( $frm_vars['prev_page'] ) || ! isset( $frm_vars['prev_page'][ $form->id ] ) || empty( $frm_vars['prev_page'][ $form->id ] ) ) {
                        unset($replace_with);
                    } else {
                        $classes = apply_filters('frm_back_button_class', array(), $form);
                        if ( ! empty( $classes ) ) {
							$html = str_replace( 'class="frm_prev_page', 'class="frm_prev_page ' . implode( ' ', $classes ), $html );
                        }

                        $html = str_replace('[/if back_button]', '', $html);
                    }
					break;
                case 'draft_label':
                    $replace_with = __( 'Save Draft', 'formidable-pro' );
					break;
                case 'save_draft':
                    if ( ! is_user_logged_in() || ! isset($form->options['save_draft']) || $form->options['save_draft'] != 1 || ( isset($values['is_draft']) && ! $values['is_draft'] ) ) {
                        //remove button if user is not logged in, drafts are not allowed, or editing an entry that is not a draft
                        unset($replace_with);
					} else {
                        $html = str_replace('[/if save_draft]', '', $html);
                    }
					break;
                case 'draft_hook':
                    $replace_with = apply_filters('frm_draft_button_action', '', $form);
            }

			if ( isset( $replace_with ) ) {
				$html = str_replace( $shortcodes[0][ $short_key ], $replace_with, $html );
			}

            unset( $short_key, $tag, $replace_with );
        }

        return $html;
    }

	public static function replace_content_shortcodes( $content, $entry, $shortcodes ) {
        remove_filter('frm_replace_content_shortcodes', 'FrmFormsController::replace_content_shortcodes', 20);
		return FrmProContent::replace_shortcodes( $content, $entry, $shortcodes );
    }

	public static function conditional_options( $options ) {
        $cond_opts = array(
            'equals="something"' => __( 'Equals', 'formidable-pro' ),
            'not_equal="something"' => __( 'Does Not Equal', 'formidable-pro' ),
            'equals=""' => __( 'Is Blank', 'formidable-pro' ),
            'not_equal=""' => __( 'Is Not Blank', 'formidable-pro' ),
            'like="something"' => __( 'Is Like', 'formidable-pro' ),
            'not_like="something"' => __( 'Is Not Like', 'formidable-pro' ),
            'greater_than="3"' => __( 'Greater Than', 'formidable-pro' ),
            'less_than="-1 month"' => __( 'Less Than', 'formidable-pro' )
        );

        $options = array_merge($options, $cond_opts);
        return $options;
    }

	public static function advanced_options( $options ) {
        $adv_opts = array(
            'clickable=1' => __( 'Clickable Links', 'formidable-pro' ),
            'links=0'   => array( 'label' => __( 'Remove Links', 'formidable-pro' ), 'title' => __( 'Removes the automatic links to category pages', 'formidable-pro' )),
            'sanitize=1' => array( 'label' => __( 'Sanitize', 'formidable-pro' ), 'title' => __( 'Replaces spaces with dashes and lowercases all. Use if adding an HTML class or ID', 'formidable-pro' )),
			'sanitize_url=1' => array(
				'label' => __( 'Sanitize URL', 'formidable-pro' ),
				'title' => __( 'Replaces all HTML entities with a URL safe string.', 'formidable-pro' ),
			),
            'truncate=40' => array( 'label' => __( 'Truncate', 'formidable-pro' ), 'title' => __( 'Truncate text with a link to view more. If using Both (dynamic), the link goes to the detail page. Otherwise, it will show in-place.', 'formidable-pro' )),
            'truncate=100 more_text="More"' => __( 'More Text', 'formidable-pro' ),
            'time_ago=1' => array( 'label' => __( 'Time Ago', 'formidable-pro' ), 'title' => __( 'How long ago a date was in minutes, hours, days, months, or years.', 'formidable-pro' )),
            'decimal=2 dec_point="." thousands_sep=","' => __( '# Format', 'formidable-pro' ),
            'show="value"' => array( 'label' => __( 'Saved Value', 'formidable-pro' ), 'title' => __( 'Show the saved value for fields with separate values.', 'formidable-pro' ) ),
            'striphtml=1' => array( 'label' => __( 'Remove HTML', 'formidable-pro' ), 'title' => __( 'Remove all HTML added into your form before display', 'formidable-pro' )),
            'keepjs=1' => array( 'label' => __( 'Keep JS', 'formidable-pro' ), 'title' => __( 'Javascript from your form entries are automatically removed. Add this option only if you trust those submitting entries.', 'formidable-pro' )),
        );

        $options = array_merge($options, $adv_opts);
        return $options;
    }

	public static function user_options( $options ) {
        $user_fields = array(
            'ID'            => __( 'User ID', 'formidable-pro' ),
            'first_name'    => __( 'First Name', 'formidable-pro' ),
            'last_name'     => __( 'Last Name', 'formidable-pro' ),
            'display_name'  => __( 'Display Name', 'formidable-pro' ),
            'user_login'    => __( 'User Login', 'formidable-pro' ),
            'user_email'    => __( 'Email', 'formidable-pro' ),
            'avatar'        => __( 'Avatar', 'formidable-pro' ),
			'author_link'   => __( 'Author Link', 'formidable-pro' ),
        );

        $options = array_merge($options, $user_fields);
        return $options;
    }


	/**
	 * Add submit conditions to $frm_vars for inclusion in Conditional Logic processing
	 * @param $atts
	 * @param $form
	 */
	public static function add_submit_conditions_to_frm_vars( $form ) {
		if ( ! isset( $form->options['submit_conditions'] ) ||
             ! isset( $form->options['submit_conditions']['hide_field'] ) ||
             empty( $form->options['submit_conditions']['hide_field'] ) ) {
			return;
		}

		$submit_field = array(
			'id'              => 'submit_' . $form->id,
			'key'             => 'submit_' . $form->id,
			'type'            => 'submit',
			'form_id'         => $form->id,
			'parent_form_id'  => $form->id,
			'form_select'     => '',
			'hide_field'      => $form->options['submit_conditions']['hide_field'],
			'hide_field_cond' => $form->options['submit_conditions']['hide_field_cond'],
			'hide_opt'        => $form->options['submit_conditions']['hide_opt'],
			'show_hide'       => $form->options['submit_conditions']['show_hide'],
			'any_all'         => $form->options['submit_conditions']['any_all'],
		);

		FrmProFieldsHelper::setup_conditional_fields( $submit_field );
	}

	/**
	 * Adds a row to Conditional Logic for the submit button
	 */
	public static function _submit_logic_row() {
		FrmAppHelper::permission_check( 'frm_edit_forms' );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		$meta_name         = FrmAppHelper::get_post_param( 'meta_name', '', 'absint' );
		$hide_field        = '';
		$form_id           = FrmAppHelper::get_param( 'form_id', '', 'get', 'absint' );
		$form_fields       = FrmField::get_all_for_form( $form_id );
		if ( ! $form_fields ) {
			wp_die();
		}

		$condition = array(
			'hide_field'      => '',
			'hide_field_cond' => '==',
		);

		$form              = FrmForm::getOne( $form_id );
		$submit_conditions = FrmForm::get_option( array(
			'form'    => $form,
			'option'  => 'submit_conditions',
			'default' => $condition,
		) );

		$form_fields       = FrmField::get_all_for_form( $form_id );
		$exclude_fields    = array_merge( FrmField::no_save_fields(), array( 'file', 'rte', 'date' ) );

		include( FrmProAppHelper::plugin_path() . '/classes/views/frmpro-forms/_submit_logic_row.php' );

		wp_die();
	}

	public static function include_logic_row( $atts ) {
        $defaults = array(
            'meta_name' => '',
            'condition' => array(
                'hide_field'        => '',
                'hide_field_cond'   => '==',
                'hide_opt'          => '',
            ),
            'key'      => '',
			'type'     => 'form',
            'form_id'  => 0,
			'id'       => '',
            'name'     => '',
			'names'    => array(),
			'showlast' => '',
			'hidelast' => '',
			'onchange' => '',
			'exclude_fields' => array_merge( FrmField::no_save_fields(), array( 'file', 'rte', 'date' ) ),
        );

        $atts = wp_parse_args($atts, $defaults);

		if ( empty( $atts['id'] ) ) {
			$atts['id'] = 'frm_logic_' . $atts['key'] . '_' . $atts['meta_name'];
		}

		if ( empty( $atts['name'] ) ) {
			$atts['name'] = 'frm_form_action[' . $atts['key'] . '][post_content][conditions][' . $atts['meta_name'] . ']';
		}

		if ( empty( $atts['names'] ) ) {
			$atts['names'] = array(
				'hide_field' => $atts['name'] . '[hide_field]',
				'hide_field_cond' => $atts['name'] . '[hide_field_cond]',
				'hide_opt' => $atts['name'] . '[hide_opt]',
			);
		}

		// TODO: get rid of this and add event binding instead
		if ( $atts['onchange'] == '' ) {
			$atts['onchange'] = "frmGetFieldValues(this.value,'" . $atts['key'] . "','" . $atts['meta_name'] . "','','" . $atts['names']['hide_opt'] . "')";
		}

		$form_fields = FrmField::get_all_for_form( $atts['form_id'] );

		extract( $atts );
        include(FrmProAppHelper::plugin_path() . '/classes/views/frmpro-forms/_logic_row.php');
    }

	public static function setup_new_vars( $values ) {
	    return FrmProFormsHelper::setup_new_vars($values);
	}

	public static function setup_edit_vars( $values ) {
	    return FrmProFormsHelper::setup_edit_vars($values);
	}

	public static function popup_shortcodes( $shortcodes ) {
	    $shortcodes['display-frm-data'] = array( 'name' => __( 'View', 'formidable-pro' ), 'label' => __( 'Insert a View', 'formidable-pro' ));
	    $shortcodes['frm-graph'] = array( 'name' => __( 'Graph', 'formidable-pro' ), 'label' => __( 'Insert a Graph', 'formidable-pro' ));
        $shortcodes['frm-search'] = array( 'name' => __( 'Search', 'formidable-pro' ), 'label' => __( 'Add a Search Form', 'formidable-pro' ));
        $shortcodes['frm-show-entry'] = array( 'name' => __( 'Single Entry', 'formidable-pro' ), 'label' => __( 'Display a Single Entry', 'formidable-pro' ));
		$shortcodes['frm-entry-links'] = array( 'name' => __( 'List of Entries', 'formidable-pro' ), 'label' => __( 'Display a List of Entries', 'formidable-pro' ) );

		/*
		To add:
			formresults, frm-entry-edit-link, frm-entry-delete-link,
			frm-entry-update-field, frm-field-value, frm-set-get?,
			frm-alt-color?
		*/
        return $shortcodes;
	}

	public static function sc_popup_opts( $opts, $shortcode ) {
		$function_name = 'popup_opts_' . str_replace( '-', '_', $shortcode );
		if ( method_exists( 'FrmProFormsController', $function_name ) ) {
			self::$function_name($opts, $shortcode);
		}
	    return $opts;
	}

	private static function popup_opts_formidable( array &$opts ) {
        //'fields' => '', 'entry_id' => 'last' or #, 'exclude_fields' => '', GET => value
        $opts['readonly'] = array( 'val' => 'disabled', 'label' => __( 'Make read-only fields editable', 'formidable-pro' ));
    }

	private static function popup_opts_display_frm_data( array &$opts, $shortcode ) {
        //'entry_id' => '',  'user_id' => false, 'order' => '',
		$displays = FrmProDisplay::getAll( array(), 'title ASC' );

?>
        <h4 for="frmsc_<?php echo esc_attr( $shortcode ) ?>_id" class="frm_left_label"><?php _e( 'Select a view:', 'formidable-pro' ) ?></h4>
        <select id="frmsc_<?php echo esc_attr( $shortcode ) ?>_id">
            <option value=""> </option>
            <?php foreach ( $displays as $display ) { ?>
            <option value="<?php echo esc_attr( $display->ID ) ?>"><?php echo esc_html( $display->post_title ) ?></option>
            <?php } ?>
        </select>
        <div class="frm_box_line"></div>
<?php
		$opts = array(
			'filter' => array( 'val' => 'limited', 'label' => __( 'Filter shortcodes within the view content', 'formidable-pro' ) ),
			'limit' => array( 'val' => '', 'label' => __( 'Limit', 'formidable-pro' ), 'type' => 'text' ),
			'page_size' => array( 'val' => '', 'label' => __( 'Page size', 'formidable-pro' ), 'type' => 'text' ),
			'order'  => array(
				'val'   => '', 'label' => __( 'Entry order', 'formidable-pro' ), 'type' => 'select',
				'opts'  => array(
					''      => __( 'Default', 'formidable-pro' ),
					'ASC'   => __( 'Ascending', 'formidable-pro' ),
					'DESC'  => __( 'Descending', 'formidable-pro' ),
				),
			),
			'drafts' => array(
				'val'   => '',
				'label' => __( 'Include draft entries', 'formidable-pro' ),
				'type'  => 'select',
				'opts'  => array(
					''     => __( 'No draft entries', 'formidable-pro' ),
					'1'    => __( 'Only draft entries', 'formidable-pro' ),
					'both' => __( 'All entries', 'formidable-pro' ),
				),
			),
		);
    }

	private static function popup_opts_frm_search( array &$opts ) {
        $opts = array(
            'style' => array( 'val' => 1, 'label' => __( 'Use Formidable styling', 'formidable-pro' )), // or custom class?
            'label' => array(
                'val' => __( 'Search', 'formidable-pro' ),
                'label' => __( 'Customize search button', 'formidable-pro' ),
                'type' => 'text',
            ),
            'post_id' => array(
                'val' => '',
                'label' => __( 'The ID of the page with the search results', 'formidable-pro' ),
                'type' => 'text',
            ),
        );
    }

	private static function popup_opts_frm_graph( array &$opts, $shortcode ) {
		$where = array(
			'status' => 'published',
			'is_template' => 0,
			array( 'or' => 1, 'parent_form_id' => null, 'parent_form_id <' => 1 ),
		);
		$form_list = FrmForm::getAll( $where, 'name' );

    ?>
		<h4 class="frm_left_label"><?php _e( 'Select a form and field:', 'formidable-pro' ) ?></h4>

		<select class="frm_get_field_selection" id="<?php echo esc_attr( $shortcode ) ?>_form">
			<option value="">&mdash; <?php _e( 'Select Form', 'formidable-pro' ) ?> &mdash;</option>
			<?php foreach ( $form_list as $form_opts ) { ?>
			<option value="<?php echo esc_attr( $form_opts->id ) ?>"><?php echo '' == $form_opts->name ? __( '(no title)', 'formidable-pro' ) : esc_html( FrmAppHelper::truncate($form_opts->name, 50) ) ?></option>
			<?php } ?>
		</select>

		<span id="<?php echo esc_attr( $shortcode ) ?>_fields_container">
		</span>

		<div class="frm_box_line"></div><?php

		$opts = array(
			'type'  => array(
				'val'   => 'default', 'label' => __( 'Graph Type', 'formidable-pro' ), 'type' => 'select',
				'opts'  => array(
					'column'    => __( 'Column', 'formidable-pro' ),
					'hbar'    => __( 'Horizontal Bar', 'formidable-pro' ),
					'pie'       => __( 'Pie', 'formidable-pro' ),
					'line'      => __( 'Line', 'formidable-pro' ),
					'area'      => __( 'Area', 'formidable-pro' ),
					'scatter'      => __( 'Scatter', 'formidable-pro' ),
					'histogram'      => __( 'Histogram', 'formidable-pro' ),
					'table'      => __( 'Table', 'formidable-pro' ),
					'stepped_area' => __( 'Stepped Area', 'formidable-pro' ),
					'geo'       => __( 'Geographical Map', 'formidable-pro' ),
				),
			),
			'data_type' => array(
				'val'   => 'count', 'label' => __( 'Data Type', 'formidable-pro' ), 'type' => 'select',
				'opts'  => array(
					'count' => __( 'The number of entries', 'formidable-pro' ),
					'total' => __( 'Add the field values together', 'formidable-pro' ),
					'average' => __( 'Average the totaled field values', 'formidable-pro' ),
				),
			),
			'height'    => array( 'val' => '', 'label' => __( 'Height', 'formidable-pro' ), 'type' => 'text'),
			'width'     => array( 'val' => '', 'label' => __( 'Width', 'formidable-pro' ), 'type' => 'text'),
			'bg_color'  => array( 'val' => '', 'label' => __( 'Background color', 'formidable-pro' ), 'type' => 'text'),
			'title'     => array( 'val' => '', 'label' => __( 'Graph title', 'formidable-pro' ), 'type' => 'text'),
			'title_size' => array( 'val' => '', 'label' => __( 'Title font size', 'formidable-pro' ), 'type' => 'text' ),
			'title_font' => array( 'val' => '', 'label' => __( 'Title font name', 'formidable-pro' ), 'type' => 'text' ),
			'is3d'      => array(
				'val'   => 1, 'label' => __( 'Turn your pie graph three-dimensional', 'formidable-pro' ),
				'show'  => array( 'type' => 'pie' ),
			),
			'include_zero' => array( 'val' => 1, 'label' => __( 'When using dates for the x_axis parameter, you can include dates with a zero value.', 'formidable-pro' )),
			'show_key' => array( 'val' => 1, 'label' => __( 'Include a legend with the graph', 'formidable-pro' )),
		);
    }

	private static function popup_opts_frm_show_entry( array &$opts, $shortcode ) {

?>
    <h4 class="frm_left_label"><?php _e( 'Insert an entry ID/key:', 'formidable-pro' ) ?></h4>

    <input type="text" value="" id="frmsc_<?php echo esc_attr( $shortcode ) ?>_id" />

    <div class="frm_box_line"></div>
<?php
        $opts = array(
            'user_info'     => array( 'val' => 1, 'label' => __( 'Include user info like browser and IP', 'formidable-pro' )),
            'include_blank' => array( 'val' => 1, 'label' => __( 'Include rows for blank fields', 'formidable-pro' )),
            'plain_text'    => array( 'val' => 1, 'label' => __( 'Do not include any HTML', 'formidable-pro' )),
            'direction'     => array( 'val' => 'rtl', 'label' => __( 'Use RTL format', 'formidable-pro' )),
            'font_size'     => array( 'val' => '', 'label' => __( 'Font size', 'formidable-pro' ), 'type' => 'text'),
            'text_color'    => array( 'val' => '', 'label' => __( 'Text color', 'formidable-pro' ), 'type' => 'text'),
            'border_width'  => array( 'val' => '', 'label' => __( 'Border width', 'formidable-pro' ), 'type' => 'text'),
            'border_color'  => array( 'val' => '', 'label' => __( 'Border color', 'formidable-pro' ), 'type' => 'text'),
            'bg_color'      => array( 'val' => '', 'label' => __( 'Background color', 'formidable-pro' ), 'type' => 'text'),
            'alt_bg_color'  => array( 'val' => '', 'label' => __( 'Alternate background color', 'formidable-pro' ), 'type' => 'text'),
        );
    }

	private static function popup_opts_frm_entry_links( array &$opts, $shortcode ) {
		$opts = array(
			'form_id'   => 'id',
			'field_key' => array(
				'val' => 'created_at', 'type' => 'text',
				'label' => __( 'Field ID/key for labels', 'formidable-pro' ),
			),
			'type'      => array(
				'val' => 'list', 'label' => __( 'Display format', 'formidable-pro' ),
				'type' => 'select', 'opts' => array(
					'list'     => __( 'List', 'formidable-pro' ),
					'select'   => __( 'Drop down', 'formidable-pro' ),
					'collapse' => __( 'Expanding archive', 'formidable-pro' ),
				),
			),
			'logged_in' => array(
				'val'   => 1, 'type' => 'select',
				'label' => __( 'Privacy', 'formidable-pro' ),
				'opts'  => array(
					1 => __( 'Only include the entries the current user created', 'formidable-pro' ),
					0 => __( 'Include all entries', 'formidable-pro' ),
				),
			),
			'page_id'   => array( 'val' => '', 'label' => __( 'The ID of the page to link to', 'formidable-pro' ), 'type' => 'text' ),
			'edit'      => array(
				'val'   => 1, 'type' => 'select',
				'label' => __( 'Link action', 'formidable-pro' ),
				'opts'  => array(
					1 => __( 'Edit if allowed', 'formidable-pro' ),
					0 => __( 'View only', 'formidable-pro' ),
				),
			),
			'show_delete' => array( 'val' => '', 'label' => __( 'Delete link label', 'formidable-pro' ), 'type' => 'text' ),
			'confirm'     => array( 'val'   => '', 'label' => __( 'Delete confirmation message', 'formidable-pro' ), 'type'  => 'text' ),
			'link_type'   => array(
				'val'   => 'page', 'type' => 'select',
				'label' => __( 'Send users to', 'formidable-pro' ),
				'opts'  => array(
					'page'   => __( 'A page', 'formidable-pro' ),
					'scroll' => __( 'An anchor on the page with id="[key]"', 'formidable-pro' ),
					'admin'  => __( 'The entry in the back-end', 'formidable-pro' ),
				),
			),
			'param_name'  => array( 'val' => 'entry', 'label' => __( 'URL parameter (?entry=5)', 'formidable-pro' ), 'type' => 'text' ),
			'param_value' => array(
				'val'   => 'key', 'type' => 'select',
				'label' => __( 'Identify the entry by', 'formidable-pro' ),
				'opts'  => array(
					'key' => __( 'Entry key', 'formidable-pro' ),
					'id'  => __( 'Entry ID', 'formidable-pro' ),
				),
			),
			'class'       => array( 'val' => '', 'label' => __( 'Add HTML classes', 'formidable-pro' ), 'type' => 'text' ),
			'blank_label' => array( 'val' => '', 'label' => __( 'Label on first option in the dropdown', 'formidable-pro' ), 'type' => 'text' ),
			'drafts'      => array( 'val' => 1, 'label' => __( 'Include draft entries', 'formidable-pro' ) ),
		);
	}

	/**
	 * Add Pro field helpers to Customization Panel
	 *
	 * @since 2.0.22
	 * @param array $entry_shortcodes
	 * @param bool $settings_tab
	 * @return array
	 */
	public static function add_pro_field_helpers( $entry_shortcodes, $settings_tab ) {
		if ( ! $settings_tab ) {
			$entry_shortcodes['detaillink'] = __( 'Detail Link', 'formidable-pro' );
			$entry_shortcodes['editlink location="front" label="Edit" page_id=x'] = __( 'Edit Entry Link', 'formidable-pro' );
			$entry_shortcodes['entry_count'] = __( 'Entry Count', 'formidable-pro' );
			$entry_shortcodes['entry_position'] = __( 'Entry Postion', 'formidable-pro' );
			$entry_shortcodes['evenodd'] = __( 'Even/Odd', 'formidable-pro' );
			$entry_shortcodes['is_draft'] = __( 'Draft status', 'formidable-pro' );
			$entry_shortcodes['event_date format="Y-m-d"'] = __( 'Calendar Date', 'formidable-pro' );
		}

		return $entry_shortcodes;
	}

	public static function setup_form_data_for_editing_entry( $entry, &$values ) {
		$form = $entry->form_id;
		FrmForm::maybe_get_form( $form );

		if ( ! $form || ! is_array( $form->options ) ) {
			return;
		}

		$values['form_name'] = $form->name;
		$values['parent_form_id'] = $form->parent_form_id;

		if ( ! is_array($form->options) ) {
			return;
		}

		foreach ( $form->options as $opt => $value ) {
			$values[ $opt ] = $value;
		}

		$form_defaults = FrmFormsHelper::get_default_opts();

		foreach ( $form_defaults as $opt => $default ) {
			if ( ! isset( $values[ $opt ] ) || $values[ $opt ] == '' ) {
				$values[ $opt ] = $default;
			}
		}
		unset($opt, $defaut);

		$post_values = stripslashes_deep( $_POST );
		if ( ! isset( $values['custom_style'] ) ) {
			$values['custom_style'] = FrmAppHelper::custom_style_value( $post_values );
		}

		foreach ( array( 'before', 'after', 'submit' ) as $h ) {
			if ( ! isset( $values[ $h . '_html' ] ) ) {
				$values[ $h . '_html' ] = ( isset( $post_values['options'][ $h . '_html' ] ) ? $post_values['options'][ $h . '_html' ] : FrmFormsHelper::get_default_html( $h ) );
			}
		}
		unset($h);
	}

	/* Trigger model actions */
	public static function update_options( $options, $values ) {
        return FrmProForm::update_options($options, $values);
    }

	public static function save_wppost_actions( $settings, $action ) {
        return FrmProForm::save_wppost_actions($settings, $action);
    }

	public static function update_form_field_options( $field_options, $field ) {
        return FrmProForm::update_form_field_options($field_options, $field);
    }

	public static function update( $id, $values ) {
        FrmProForm::update($id, $values);
    }

	public static function after_duplicate( $new_opts ) {
        return FrmProForm::after_duplicate($new_opts);
    }

	public static function validate( $errors, $values ) {
        return FrmProForm::validate( $errors, $values );
    }

	public static function add_form_row() {
		_deprecated_function( __FUNCTION__, '2.05', 'FrmProNestedFormsController::ajax_add_repeat_row' );
		FrmProNestedFormsController::ajax_add_repeat_row();
	}
}
