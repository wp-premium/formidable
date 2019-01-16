<?php

class FrmProFormActionsController {

	public static function register_actions( $actions ) {
        $actions['wppost'] = 'FrmProPostAction';

        include_once(FrmProAppHelper::plugin_path() . '/classes/views/frmpro-form-actions/post_action.php');

        return $actions;
    }

	public static function email_action_control( $settings ) {
		$settings['event'] = array_unique(
			array_merge( $settings['event'],
			array( 'draft', 'create', 'update', 'delete', 'import' )
		) );
	    $settings['priority'] = 41;

	    return $settings;
	}

	public static function form_action_settings( $form_action, $atts ) {
        global $wpdb;
        extract($atts);

        $show_logic = self::has_valid_conditions( $form_action->post_content['conditions'] );

        // Text for different actions
        if ( $form_action->post_excerpt == 'email' ) {
            $send = __( 'Send', 'formidable-pro' );
            $stop = __( 'Stop', 'formidable-pro' );
            $this_action_if = __( 'this notification if', 'formidable-pro' );
        } if ( $form_action->post_excerpt == 'wppost' ) {
            $send = __( 'Create', 'formidable-pro' );
            $stop = __( 'Don\'t create', 'formidable-pro' );
            $this_action_if = __( 'this post if', 'formidable-pro' );
        } else if ( $form_action->post_excerpt == 'register' ) {
            $send = __( 'Register', 'formidable-pro' );
            $stop = __( 'Don\'t register', 'formidable-pro' );
            $this_action_if = __( 'user if', 'formidable-pro' );
        } else {
            $send = __( 'Do', 'formidable-pro' );
            $stop = __( 'Don\'t do', 'formidable-pro' );
            $this_action_if = __( 'this action if', 'formidable-pro' );
        }

        $form_fields = $atts['values']['fields'];
        unset($atts['values']['fields']);
        include(FrmProAppHelper::plugin_path() . '/classes/views/frmpro-form-actions/_form_action.php');
    }

	public static function _logic_row() {
		FrmAppHelper::permission_check('frm_edit_forms');
        check_ajax_referer( 'frm_ajax', 'nonce' );

		$meta_name = FrmAppHelper::get_param( 'meta_name', '', 'get', 'sanitize_title' );
		$form_id = FrmAppHelper::get_param( 'form_id', '', 'get', 'absint' );
		$key = FrmAppHelper::get_param( 'email_id', '', 'get', 'sanitize_title' );
		$type = FrmAppHelper::get_param( 'type', '', 'get', 'sanitize_title' );

        $form = FrmForm::getOne($form_id);

		FrmProFormsController::include_logic_row( array(
			'form_id'   => $form->id,
			'form'      => $form,
			'meta_name' => $meta_name,
			'condition' => array( 'hide_field_cond' => '==', 'hide_field' => '' ),
			'key'       => $key,
			'name'      => 'frm_' . $type . '_action[' . $key . '][post_content][conditions][' . $meta_name . ']',
			'hidelast'  => '#frm_logic_rows_' . $key,
			'showlast'  => '#logic_link_' . $key,
		) );

        wp_die();
	}

	/**
	 * Before the form action is saved, check for logic that
	 * needs to be removed.
	 *
	 * @since 3.0
	 */
	public static function remove_incomplete_logic( $settings ) {
		if ( isset( $settings['post_content']['conditions'] ) ) {
			self::remove_logic_without_field( $settings['post_content']['conditions'] );
		}

		return $settings;
	}

	/**
	 * If a condition doesn't include a selected field, remove it
	 *
	 * @since 3.0
	 */
	private static function remove_logic_without_field( &$conditions ) {
		if ( empty( $conditions ) ) {
			return;
		}

		foreach ( $conditions as $k => $condition ) {
			if ( ! is_numeric( $k ) ) {
				continue;
			}

			if ( empty( $condition['hide_field'] ) ) {
				unset( $conditions[ $k ] );
			}
		}
	}

	/**
	 * If logic includes rows with a field selected, it is value
	 *
	 * @since 3.0
	 *
	 * @return bool
	 */
	private static function has_valid_conditions( $conditions ) {
		self::remove_logic_without_field( $conditions );
		return count( $conditions ) > 2;
	}

	public static function fill_action_options( $action, $type ) {
        if ( 'wppost' == $type ) {

            $default_values = array(
                'post_type'     => 'post',
                'post_category' => array(),
                'post_content'  => '',
                'post_excerpt'  => '',
                'post_title'    => '',
                'post_name'     => '',
                'post_date'     => '',
                'post_status'   => '',
                'post_custom_fields' => array(),
                'post_password' => '',
            );

            $action->post_content = array_merge($default_values, (array) $action->post_content);
        }

        return $action;
    }

	/**
	 * @since 2.0.23
	 */
	public static function maybe_trigger_draft_actions( $event, $args ) {
		if ( isset( $args['entry_id'] ) && FrmProEntry::is_draft( $args['entry_id'] ) ) {
			$event = 'draft';
		}
		return $event;
	}

	public static function trigger_draft_actions( $entry_id, $form_id ) {
		FrmFormActionsController::trigger_actions( 'draft', $form_id, $entry_id );
	}

	public static function trigger_update_actions( $entry_id, $form_id ) {
		$event = apply_filters( 'frm_trigger_update_action', 'update', array( 'entry_id' => $entry_id ) );
		FrmFormActionsController::trigger_actions( $event, $form_id, $entry_id );
	}

	public static function trigger_delete_actions( $entry_id, $entry = false ) {
		if ( empty( $entry ) ) {
			$entry = FrmEntry::getOne( $entry_id );
		}
        FrmFormActionsController::trigger_actions('delete', $entry->form_id, $entry);
    }

	public static function _postmeta_row() {
		FrmAppHelper::permission_check('frm_edit_forms');
        check_ajax_referer( 'frm_ajax', 'nonce' );

        $custom_data = array( 'meta_name' => $_POST['meta_name'], 'field_id' => '');
        $action_key = absint( $_POST['action_key'] );
        $action_control = FrmFormActionsController::get_form_actions( 'wppost' );
        $action_control->_set($action_key);

        $values = array();

        if ( isset($_POST['form_id']) ) {
			$values['fields'] = FrmField::getAll( array( 'fi.form_id' => absint( $_POST['form_id'] ), 'fi.type not' => FrmField::no_save_fields() ), 'field_order');
        }
        $echo = false;

		$cf_keys = self::get_post_meta_keys();

        include(FrmProAppHelper::plugin_path() . '/classes/views/frmpro-form-actions/_custom_field_row.php');
        wp_die();
    }

	private static function get_post_meta_keys() {
		global $wpdb;

		$post_type = FrmAppHelper::get_param( 'post_type', 'post', 'post', 'sanitize_text_field' );
		$limit = (int) apply_filters( 'postmeta_form_limit', 50 );
		$sql = "SELECT DISTINCT meta_key
			FROM $wpdb->postmeta pm
			LEFT JOIN $wpdb->posts p
			ON (p.ID = pm.post_ID)
			WHERE p.post_type = %s
			ORDER BY meta_key
			LIMIT %d";
		$cf_keys = $wpdb->get_col( $wpdb->prepare( $sql, $post_type, $limit ) );

		if ( ! is_array( $cf_keys ) ) {
			$cf_keys = array();
		}

		if ( 'post' == $post_type && ! in_array( '_thumbnail_id', $cf_keys ) ) {
			$cf_keys[] = '_thumbnail_id';
		}

		if ( ! empty( $cf_keys ) ) {
			natcasesort( $cf_keys );
		}

		return $cf_keys;
	}

	public static function _posttax_row() {
		FrmAppHelper::permission_check('frm_edit_forms');
        check_ajax_referer( 'frm_ajax', 'nonce' );

        if ( isset($_POST['field_id']) ) {
            $field_vars = array(
                'meta_name'     => $_POST['meta_name'],
                'field_id'      => $_POST['field_id'],
                'show_exclude'  => (int) $_POST['show_exclude'],
                'exclude_cat'   => ( (int) $_POST['show_exclude'] ) ? '-1' : 0
            );
        } else {
            $field_vars = array( 'meta_name' => '', 'field_id' => '', 'show_exclude' => 0, 'exclude_cat' => 0);
        }

        $tax_meta = (int) $_POST['tax_key'];
        $post_type = sanitize_text_field( $_POST['post_type'] );
        $action_key = (int) $_POST['action_key'];
        $action_control = FrmFormActionsController::get_form_actions( 'wppost' );
        $action_control->_set($action_key);

        if ( $post_type ) {
            $taxonomies = get_object_taxonomies($post_type);
        }

        $values = array();

        if ( isset($_POST['form_id']) ) {
			$values['fields'] = FrmField::getAll( array( 'fi.form_id' => (int) $_POST['form_id'], 'fi.type' => array( 'checkbox', 'radio', 'select', 'tag', 'data' ) ), 'field_order' );
            $values['id'] = (int) $_POST['form_id'];
        }

        $echo = false;
        include(FrmProAppHelper::plugin_path() . '/classes/views/frmpro-form-actions/_post_taxonomy_row.php');
        wp_die();
    }

	public static function _replace_posttax_options() {
		FrmAppHelper::permission_check('frm_edit_forms');
        check_ajax_referer( 'frm_ajax', 'nonce' );

        // Get the post type, and all taxonomies for that post type
        $post_type = sanitize_text_field( $_POST['post_type'] );
        $taxonomies = get_object_taxonomies($post_type);

        // Get the HTML for the options
        include(FrmProAppHelper::plugin_path() . '/classes/views/frmpro-form-actions/_post_taxonomy_select.php');
        wp_die();
    }

	/**
	 * Display the taxonomy checkboxes for a specific taxonomy in a Create Post action
	 *
	 * @since 2.01.0
	 * @param array $args (MUST include taxonomy, form_id, field_name, and value)
	 */
	public static function display_taxonomy_checkboxes_for_post_action( $args ) {
		if ( ! $args['taxonomy'] ) {
			return;
		}

		$args['level'] = 1;

		$args['post_type'] = FrmProFormsHelper::post_type( $args['form_id'] );

		$children = get_categories( array(
			'hide_empty' => false,
			'parent' => 0,
			'type' => $args['post_type'],
			'taxonomy' => $args['taxonomy'],
		) );

		foreach ( $children as $key => $cat ) {
			$args['cat'] = $cat; ?>
			<div class="frm_catlevel_1"><?php
				self::display_taxonomy_checkbox_group( $args );
				?>
			</div><?php
		}
	}

	/**
	 * Display a single taxonomy checkbox and its children
	 *
	 * @since 2.01.0
	 * @param array $args (MUST include cat, value, field_name, post_type, taxonomy, and level)
	 */
	private static function display_taxonomy_checkbox_group( $args ) {
		if ( ! is_object($args['cat']) ) {
			return;
		}

		if ( is_array($args['value']) ) {
			$checked = ( in_array($args['cat']->cat_ID, $args['value'] ) ) ? ' checked="checked" ' : '';
		} else {
			$checked = checked( $args['value'], $args['cat']->cat_ID, false );
		}

		?>
		<div class="frm_checkbox">
			<label><input type="checkbox" name="<?php echo esc_attr( $args['field_name'] ) ?>" value="<?php
			echo esc_attr( $args['cat']->cat_ID );
			?>"<?php
			echo $checked;
			?> /><?php echo esc_html( $args['cat']->cat_name ) ?></label><?php

		$children = get_categories( array(
			'type' => $args['post_type'],
			'hide_empty' => false,
			'parent' => $args['cat']->cat_ID,
			'taxonomy' => $args['taxonomy'],
		));

		if ( $children ) {
			$args['level']++;
			foreach ( $children as $key => $cat ) {
				$args['cat'] = $cat;
				?>
		<div class="frm_catlevel_<?php echo esc_attr( $args['level'] ) ?>"><?php self::display_taxonomy_checkbox_group( $args ); ?></div>
<?php
			}
		}
		echo '</div>';
	}
}
