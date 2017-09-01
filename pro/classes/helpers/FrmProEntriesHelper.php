<?php

class FrmProEntriesHelper{

    // check if form should automatically be in edit mode (limited to one, has draft)
    public static function allow_form_edit($action, $form) {
        if ( $action != 'new' ) {
            // make sure there is an entry id in the url if the action is being set in the url
			$entry_id = FrmAppHelper::simple_get( 'entry', 'sanitize_title', 0 );
            if ( empty($entry_id) && ( ! $_POST || ! isset($_POST['frm_action']) ) ) {
                $action = 'new';
            }
        }

        $user_ID = get_current_user_id();
        if ( ! $form || ! $user_ID ) {
            return $action;
        }

        if ( ! $form->editable ) {
            $action = 'new';
        }

        $is_draft = false;
        if($action == 'destroy')
            return $action;

        global $wpdb;
		if ( ( $form->editable && ( isset( $form->options['single_entry'] ) && $form->options['single_entry'] && $form->options['single_entry_type'] == 'user' ) || ( isset( $form->options['save_draft'] ) && $form->options['save_draft'] ) ) ) {
			if ( $action == 'update' && $form->id == FrmAppHelper::get_param( 'form_id', '', 'get', 'absint' ) ) {
                //don't change the action is this is the wrong form
            }else{
                $checking_drafts = isset($form->options['save_draft']) && $form->options['save_draft'] && ( ! $form->editable || ! isset($form->options['single_entry']) || ! $form->options['single_entry'] || $form->options['single_entry_type'] != 'user' );
                $meta = self::check_for_user_entry($user_ID, $form, $checking_drafts);

                if ( $meta ) {
                    if ( $checking_drafts ) {
                        $is_draft = true;
                    }

                    $action = 'edit';
                }
            }
        }

        //do not allow editing if user does not have permission
        if ( $action != 'edit' || $is_draft ) {
            return $action;
        }

        $entry = FrmAppHelper::get_param('entry', 0);

        if ( ! self::user_can_edit($entry, $form) ) {
            $action = 'new';
        }

        return $action;
    }

    /**
     * Check if the current user already has an entry
     * @since 2.0
     */
    public static function check_for_user_entry( $user_ID, $form, $is_draft ) {
        $query = array( 'user_id' => $user_ID, 'form_id' => $form->id);
        if ( $is_draft ) {
            $query['is_draft'] = 1;
        }

		return FrmDb::get_col( 'frm_items', $query );
    }

    public static function user_can_edit( $entry, $form = false ) {
        if ( empty($form) ) {
			FrmEntry::maybe_get_entry( $entry );

            if ( is_object($entry) ) {
                $form = $entry->form_id;
            }
        }

		FrmForm::maybe_get_form( $form );

		self::maybe_get_parent_form_and_entry( $form, $entry );

        $allowed = self::user_can_edit_check($entry, $form);
        return apply_filters('frm_user_can_edit', $allowed, compact('entry', 'form'));
    }

	/**
	* If a form is a child form, get the parent form. Then if the entry is a child entry, get the parent entry.
	*
	* @since 2.0.13
	* @param int|object $form - pass by reference
	* @param int|object $entry - pass by reference
	*/
	private static function maybe_get_parent_form_and_entry( &$form, &$entry ) {
		// If form is a child form, refer to parent form's settings
		if ( $form && $form->parent_form_id ) {
			$form = FrmForm::getOne( $form->parent_form_id );

			// Make sure we're also checking the parent entry's permissions
			FrmEntry::maybe_get_entry( $entry );
			if ( $entry->parent_item_id ) {
				$entry = FrmEntry::getOne( $entry->parent_item_id );
			}
		}
	}

    public static function user_can_edit_check($entry, $form) {
        $user_ID = get_current_user_id();

        if ( ! $user_ID || empty($form) || ( is_object($entry) && $entry->form_id != $form->id ) ) {
            return false;
        }

        if ( is_object($entry) ) {
            if ( ( $entry->is_draft && $entry->user_id == $user_ID ) || self::user_can_edit_others( $form ) ) {
                //if editable and user can edit this entry
                return true;
            }
        }

		$where = array( 'fr.id' => $form->id );

        if ( self::user_can_only_edit_draft($form) ) {
            //only allow editing of drafts
			$where['user_id'] = $user_ID;
			$where['is_draft'] = 1;
        }

        if ( ! self::user_can_edit_others( $form ) ) {
			$where['user_id'] = $user_ID;

            if ( is_object($entry) && $entry->user_id != $user_ID ) {
                return false;
            }

			// Check if open_editable_role and editable_role is set for reverse compatibility
			if ( $form->editable && isset( $form->options['open_editable_role'] ) && ! FrmAppHelper::user_has_permission( $form->options['open_editable_role'] ) && isset( $form->options['editable_role'] ) && ! FrmAppHelper::user_has_permission( $form->options['editable_role'] ) ) {
                // make sure user cannot edit their own entry, even if a higher user role can unless it's a draft
                if ( is_object($entry) && ! $entry->is_draft ) {
                    return false;
                } else if ( ! is_object($entry) ) {
					$where['is_draft'] = 1;
                }
            }
        } else if ( $form->editable && $user_ID && empty($entry) ) {
            // make sure user is editing their own draft by default, even if they have permission to edit others' entries
		   $where['user_id'] = $user_ID;
        }

        if ( ! $form->editable ) {
			$where['is_draft'] = 1;

            if ( is_object($entry) && ! $entry->is_draft ) {
                return false;
            }
        }

        // If entry object, and we made it this far, then don't do another db call
        if ( is_object($entry) ) {
            return true;
        }

		if ( ! empty($entry) ) {
			$where_key = is_numeric($entry) ? 'it.id' : 'item_key';
			$where[ $where_key ] = $entry;
		}

        return FrmEntry::getAll( $where, ' ORDER BY created_at DESC', 1, true);
    }

    /**
     * check if this user can edit entry from another user
     * @return boolean True if user can edit
     */
    public static function user_can_edit_others( $form ) {
        if ( ! $form->editable || ! isset($form->options['open_editable_role']) || ! FrmAppHelper::user_has_permission($form->options['open_editable_role']) ) {
            return false;
        }

        return ( ! isset($form->options['open_editable']) || $form->options['open_editable'] );
    }

    /**
     * only allow editing of drafts
     * @return boolean
     */
    public static function user_can_only_edit_draft($form) {
        if ( ! $form->editable || empty($form->options['editable_role']) || FrmAppHelper::user_has_permission($form->options['editable_role']) ) {
            return false;
        }

        if ( isset($form->options['open_editable_role']) && $form->options['open_editable_role'] != '-1' ) {
            return false;
        }

        return ! self::user_can_edit_others( $form );
    }

    public static function user_can_delete($entry) {
		FrmEntry::maybe_get_entry( $entry );
        if ( ! $entry ) {
            return false;
        }

        if ( current_user_can('frm_delete_entries') ) {
            $allowed = true;
        } else {
            $allowed = self::user_can_edit($entry);
            if ( !empty($allowed) ) {
                $allowed = true;
            }
        }

        return apply_filters('frm_allow_delete', $allowed, $entry);
    }

    public static function show_new_entry_button($form) {
        echo self::new_entry_button($form);
    }

    public static function new_entry_button($form) {
        if ( ! current_user_can('frm_create_entries') ) {
            return;
        }

        $link = '<a href="?page=formidable-entries&frm_action=new';
        if ( $form ) {
            $form_id = is_numeric($form) ? $form : $form->id;
            $link .= '&form='. $form_id;
        }
        $link .= '" class="add-new-h2">'. __( 'Add New', 'formidable' ) .'</a>';

        return $link;
    }

    public static function show_duplicate_link($entry) {
        echo self::duplicate_link($entry);
    }

    public static function duplicate_link($entry) {
        if ( current_user_can('frm_create_entries') ) {
            $link = '<a href="?page=formidable-entries&frm_action=duplicate&form='. $entry->form_id .'&id='. $entry->id .'" class="button-secondary alignright">'. __( 'Duplicate', 'formidable' ) .'</a>';
            return $link;
        }
    }

    public static function edit_button() {
        if ( ! current_user_can('frm_edit_entries') ) {
            return;
        }
?>
	    <div id="publishing-action">
			<a href="<?php echo esc_url( add_query_arg( 'frm_action', 'edit' ) ) ?>" class="button-primary"><?php _e( 'Edit', 'formidable' ) ?></a>
        </div>
<?php
    }

    public static function resend_email_links($entry_id, $form_id, $args = array()) {
        $defaults = array(
            'label' => __( 'Resend Email Notifications', 'formidable' ),
            'echo' => true,
        );

        $args = wp_parse_args($args, $defaults);

		$link = '<a href="#" data-eid="' . esc_attr( $entry_id ) . '" data-fid="' . esc_attr( $form_id ) . '" id="frm_resend_email" title="' . esc_attr( $args['label'] ) . '">' . $args['label'] . '</a>';
        if ( $args['echo'] ) {
            echo $link;
        }
        return $link;
    }

    public static function before_table( $footer, $form_id = false ) {
		if ( FrmAppHelper::simple_get( 'page', 'sanitize_title' ) != 'formidable-entries' ) {
            return;
        }

        if ( $footer && $form_id ) {
            if ( apply_filters('frm_show_delete_all', current_user_can('frm_edit_entries'), $form_id) ) {
            ?><div class="frm_uninstall alignleft actions"><a href="?page=formidable-entries&amp;frm_action=destroy_all<?php echo esc_attr( $form_id ? '&form=' . absint( $form_id ) : '' ); ?>" class="button" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to permanently delete ALL the entries in this form?', 'formidable' ) ?>')"><?php _e( 'Delete ALL Entries', 'formidable' ) ?></a></div>
<?php
            }
            return;
        }

        $page_params = array( 'frm_action' => 0, 'action' => 'frm_entries_csv', 'form' => $form_id);

        if ( !empty( $_REQUEST['s'] ) )
            $page_params['s'] = sanitize_text_field( $_REQUEST['s'] );

        if ( !empty( $_REQUEST['search'] ) )
            $page_params['search'] = sanitize_text_field( $_REQUEST['search'] );

    	if ( !empty( $_REQUEST['fid'] ) )
    	    $page_params['fid'] = (int) $_REQUEST['fid'];

		if ( $form_id ) {
        ?>
		<div class="alignleft actions">
			<a href="<?php echo esc_url( add_query_arg( $page_params, admin_url( 'admin-ajax.php' ) ) ) ?>" class="button">
				<?php _e( 'Download CSV', 'formidable' ); ?>
			</a>
		</div>
        <?php
		}
    }

    // check if entry being updated just switched draft status
    public static function is_new_entry($entry) {
		FrmEntry::maybe_get_entry( $entry );

        // this function will only be correct if the entry has already gone through FrmProEntriesController::check_draft_status
        return ( $entry->created_at == $entry->updated_at );
    }

    public static function get_field($field = 'is_draft', $id) {
        $entry = FrmAppHelper::check_cache( $id, 'frm_entry' );
        if ( $entry && isset($entry->$field) ) {
            return $entry->{$field};
        }

		$var = FrmDb::get_var( 'frm_items', array( 'id' => $id ), $field );

        return $var;
    }

	public static function get_dfe_values( $field, $entry, &$field_value ) {
		_deprecated_function( __FUNCTION__, '2.0.08', 'FrmProEntriesHelper::get_dynamic_list_values' );
		FrmProEntriesHelper::get_dynamic_list_values( $field, $entry, $field_value );
	}

	/**
	* Get the values for Dynamic List fields based on the conditional logic settings
	*
	* @since 2.0.08
	* @param object $field
	* @param object $entry
	* @param string|array|int $field_value, pass by reference
	*/
	public static function get_dynamic_list_values( $field, $entry, &$field_value ) {
		// Exit now if a value is already set, field type is not Dynamic List, or conditional logic is not set
		if ( $field_value || $field->type != 'data' || ! FrmProField::is_list_field( $field ) || ! isset( $field->field_options['hide_field'] ) ) {
			return;
		}

		$field_value = array();
		foreach ( (array) $field->field_options['hide_field'] as $hfield ) {
			if ( isset( $entry->metas[ $hfield ] ) ) {
				// Check if field in conditional logic is a Dynamic field
				$cl_field_type = FrmField::get_type( $hfield );
				if ( $cl_field_type == 'data' ) {
					$cl_field_val = maybe_unserialize( $entry->metas[ $hfield ] );
					if ( is_array( $cl_field_val ) ) {
						$field_value += $cl_field_val;
					} else {
						$field_value[] = $cl_field_val;
					}
				}
			}
		}
	}

	public static function get_search_str( $where_clause = '', $search_str, $form_id = 0, $fid = 0 ) {
		if ( ! is_array( $search_str ) ) {
			$search_str = str_replace( array( ', ', ',' ), array( ' ', ' ' ), $search_str );
			$search_str = explode( ' ', trim( $search_str ) );
		}

		$add_where = self::get_where_clause_for_entries_search( $fid, $form_id, $search_str );

		self::add_where_to_query( $add_where, $where_clause );

		return $where_clause;
	}

	/**
	 * Generate the where clause for an entry search - used in back-end entries tab
	 *
	 * @since 2.02.01
	 * @param int $fid
	 * @param int $form_id
	 * @param array $search_param
	 * @return array
	 */
	private static function get_where_clause_for_entries_search( $fid, $form_id, $search_param ) {
		if ( empty( $fid ) ) {
			// General query submitted
			$where = self::get_where_arguments_for_general_entry_query( $form_id, $search_param );
		} else if ( is_numeric( $fid ) ) {
			// Specific field searched
			$where = self::get_where_arguments_for_specific_field_query( $fid, $search_param );
		} else {
			// Specific frm_items column searched
			$where = self::get_where_arguments_for_frm_items_column( $fid, $search_param );
		}

		return $where;
	}

	/**
	 * Set up the where arguments for a general entry query in the back-end Entries tab
	 *
	 * @since 2.02.01
	 * @param int $form_id
	 * @param array $search_param
	 * @return array
	 */
	private static function get_where_arguments_for_general_entry_query( $form_id, $search_param ) {
		$where = array(
			'or' => 1,
			'it.name like'        => $search_param,
			'it.ip like'          => $search_param,
			'it.item_key like'    => $search_param,
			'it.description like' => $search_param,
			'it.created_at like'  => implode( ' ', $search_param ),
		);

		$ids_in_search_param = array_filter( $search_param, 'is_numeric' );

		$ids_from_field_searches = self::search_entry_metas_for_value( $form_id, $search_param );

		$where['it.id'] = array_merge( $ids_in_search_param, $ids_from_field_searches );

		self::append_entry_ids_for_matching_posts( $form_id, $search_param, $where );

		if ( empty( $where['it.id'] ) ) {
			$where['it.id'] = 0;
		}

		return $where;
	}

	/**
	 * Search the whole entry metas table for a matching value and return entry IDs
	 *
	 * @since 2.02.01
	 * @param int $form_id
	 * @param array $search_param
	 * @return array
	 */
	private static function search_entry_metas_for_value( $form_id, $search_param ) {
		$where_args = array(
			'fi.form_id' => $form_id,
			'meta_value like' => $search_param,
		);

		self::add_linked_field_query( '', $form_id, $search_param, $where_args );

		return FrmEntryMeta::getEntryIds( $where_args, '', '', true, array( 'is_draft' => 'both' ) );
	}

	/**
	 * Check linked entries for the search query
	 */
	private static function add_linked_field_query( $fid, $form_id, $search_param, &$where_entries_array ) {
		$data_fields = FrmProFormsHelper::has_field( 'data', $form_id, false );
		if ( empty ( $data_fields ) ) {
			// this form has no linked fields
			return;
		}

		$df_form_ids = array();

		//search the joined entry too
		foreach ( (array) $data_fields as $df ) {
			//don't check if a different field is selected
			if ( is_numeric( $fid ) && (int) $fid != $df->id ) {
				continue;
			}

			FrmProFieldsHelper::get_subform_ids( $df_form_ids, $df );

			unset( $df );
		}
		unset( $data_fields );

		if ( empty( $df_form_ids ) ) {
			return;
		}

		$data_form_ids = FrmDb::get_col( 'frm_fields', array( 'id' => $df_form_ids), 'form_id' );

		if ( $data_form_ids ) {
			$data_entry_ids = FrmEntryMeta::getEntryIds( array( 'fi.form_id' => $data_form_ids, 'meta_value LIKE' => $search_param ),  '', '', true, array( 'is_draft' => 'both' ) );
			if ( ! empty( $data_entry_ids ) ) {
				if ( count($data_entry_ids) == 1 ) {
					$where_entries_array['meta_value like'] = reset( $data_entry_ids );
				} else {
					$where_entries_array['meta_value'] = $data_entry_ids;
				}
			}
		}
	}

	/**
	 * Search connected posts when a general search is submitted
	 * @param $form_id
	 * @param $search_param
	 * @param $where
	 */
	private static function append_entry_ids_for_matching_posts( $form_id, $search_param, &$where ) {
		// Check if form has a post action
		$post_action = FrmFormAction::get_action_for_form( $form_id, 'wppost' );
		if( ! $post_action ) {
			return;
		}

		// Search all posts on site
		$post_query = array(
			'post_title LIKE' => $search_param,
			'post_content LIKE' => $search_param,
			'or' => 1,
		);
		$matching_posts = FrmDb::get_col( 'posts', $post_query, 'ID' );

		// If there are any posts matching the query, retrieve entry IDs for those posts
		if ( $matching_posts ) {
			$entry_ids = FrmDb::get_col( 'frm_items', array( 'post_id' => $matching_posts, 'form_id' => $form_id ) );
			if ( $entry_ids ) {
				$where['it.id'] = array_merge( $where['it.id'], $entry_ids );
			}
		}
	}

	/**
	 * Set up the it.id argument for the WHERE clause when searching for a specific field value
	 *
	 * @since 2.02.01
	 * @param int $fid
	 * @param array $search_param
	 * @return array
	 */
	private static function get_where_arguments_for_specific_field_query( $fid, $search_param ) {
		$field = FrmField::getOne( $fid );
		$args = array( 'comparison_type' => 'like', 'is_draft' => 'both' );

		if ( $field->type == 'data' && is_numeric( $field->field_options['form_select'] ) ) {
			$linked_field = FrmField::getOne( $field->field_options['form_select'] );
			$linked_entry_ids = FrmProEntryMeta::get_entry_ids_for_field_and_value( $linked_field, $search_param, $args );
			$search_param = array_merge( $search_param, $linked_entry_ids );
		}

		$entry_ids = FrmProEntryMeta::get_entry_ids_for_field_and_value( $field, $search_param, $args );

		if ( empty( $entry_ids ) ) {
			$entry_ids = 0;
		}

		return array( 'it.id' => $entry_ids );
	}

	/**
	 * Get the where argument for the specific frm_items column that was searched on back-end Entries tab
	 *
	 * @since 2.02.01
	 * @param int $fid
	 * @param array $search_param
	 * @return array
	 */
	private static function get_where_arguments_for_frm_items_column( $fid, $search_param ) {
		if ( 'user_id' == $fid ) {
			$search_param = self::replace_search_param_with_user_ids( $search_param );
			$where = array( 'it.' . $fid => $search_param );
		} else if ( 'created_at' == $fid || 'updated_at' == $fid ) {
			$search_param = implode( ' ', $search_param );
			$where = array( 'it.' . $fid . ' like' => $search_param );
		} else {
			$where = array( 'it.' . $fid => $search_param );
		}

		return $where;
	}

	/**
	 * Create an array of user IDs from an array of search parameters
	 *
	 * @since 2.02.01
	 * @param array $search_param
	 * @return array $user_ids
	 */
	private static function replace_search_param_with_user_ids( $search_param ) {
		$user_ids = array();

		foreach ( $search_param as $single_value ) {
			if ( is_numeric( $single_value ) ) {
				$user_ids[] = $single_value;
			} else {
				$user_id = FrmAppHelper::get_user_id_param( $single_value );
				if ( $user_id ) {
					$user_ids[] = $user_id;
				}
			}
		}

		return $user_ids;
	}

	/**
	 * @since 2.0.8
	 */
	private static function add_where_to_query( $add_where, &$where_clause ) {
		if ( is_array( $where_clause ) ) {
			$where_clause[] = $add_where;
		} else {
			global $wpdb;
			$where = '';
			$values = array();
			FrmDb::parse_where_from_array( $add_where, '', $where, $values );
			FrmDb::get_where_clause_and_values( $add_where );
			$where_clause .= ' AND ('. $wpdb->prepare( $where, $values ) .')';
		}
	}

	public static function get_search_ids( $s, $form_id, $args = array() ) {
        global $wpdb;

		if ( empty( $s ) ) {
			return false;
		}

		preg_match_all('/".*?("|$)|((?<=[\\s",+])|^)[^\\s",+]+/', $s, $matches);
		$search_terms = array_map('trim', $matches[0]);

        $spaces = '';
		$e_ids = $p_search = $search = array();
		$and_or = apply_filters( 'frm_search_any_terms', true, $s );
		if ( $and_or ) {
			$search['or'] = 1;
		}

        $data_field = FrmProFormsHelper::has_field('data', $form_id, false);

		foreach ( (array) $search_terms as $term ) {
			$p_search[] = array(
				$spaces . $wpdb->posts . '.post_title like' => $term,
				$spaces . $wpdb->posts . '.post_content like' => $term,
				'or' => 1, // search with an OR
			);

			$search[ $spaces . 'meta_value like' ] = $term;
			$spaces .= ' '; // add a space to keep the array keys unique

			if ( is_numeric( $term ) ) {
                $e_ids[] = (int) $term;
			}

			if ( $data_field ) {
                $df_form_ids = array();

                //search the joined entry too
                foreach ( (array) $data_field as $df ) {
                    FrmProFieldsHelper::get_subform_ids($df_form_ids, $df);

                    unset($df);
                }

                $data_form_ids = FrmDb::get_col( $wpdb->prefix .'frm_fields', array( 'id' => $df_form_ids), 'form_id' );
                unset($df_form_ids);

				if ( $data_form_ids ) {
					$data_entry_ids = FrmEntryMeta::getEntryIds( array( 'fi.form_id' => $data_form_ids, 'meta_value like' => $term ) );
					if ( $data_entry_ids ) {
						if ( ! isset( $search['meta_value'] ) ) {
							$search['meta_value'] = array();
						}
						$search['meta_value'] = array_merge( $search['meta_value'], $data_entry_ids );
					}
                }

                unset($data_form_ids);
            }
		}

		$matching_posts = FrmDb::get_col( $wpdb->posts, $p_search, 'ID' );
		$p_ids = array( $search, 'or' => 1 );
		if ( $matching_posts ) {
			$post_ids = FrmDb::get_col( $wpdb->prefix .'frm_items', array( 'post_id' => $matching_posts, 'form_id' => (int) $form_id) );
			if ( $post_ids ) {
				$p_ids['item_id'] = $post_ids;
			}
		}

		if ( ! empty( $e_ids ) ) {
			$p_ids['item_id'] = $e_ids;
		}

		$query = array( 'fi.form_id' => $form_id );
		$query[] = $p_ids;

		return FrmEntryMeta::getEntryIds( $query, '', '', true, $args );
    }

	public static function generate_csv( $form, $entry_ids, $form_cols ) {
		_deprecated_function( __FUNCTION__, '2.0.8', 'FrmCSVExportHelper::generate_csv');
		FrmCSVExportHelper::generate_csv( compact( 'form', 'entry_ids', 'form_cols' ) );
	}

	public static function csv_headings() {
		_deprecated_function( __FUNCTION__, '2.0.8' );
	}

	public static function add_repeat_field_values_to_csv() {
		_deprecated_function( __FUNCTION__, '2.0.8' );
	}

	public static function add_field_values_to_csv() {
		_deprecated_function( __FUNCTION__, '2.0.8' );
	}

	public static function add_comments_to_csv() {
		_deprecated_function( __FUNCTION__, '2.0.8' );
	}

	public static function add_entry_data_to_csv() {
		_deprecated_function( __FUNCTION__, '2.0.8' );
	}

	public static function print_csv_row() {
		_deprecated_function( __FUNCTION__, '2.0.8' );
	}

	public static function encode_value( $line ) {
		_deprecated_function( __FUNCTION__, '2.0.8', 'FrmCSVExportHelper::encode_value');
		return FrmCSVExportHelper::encode_value( $line );
	}

	public static function escape_csv( $value ) {
		_deprecated_function( __FUNCTION__, '2.0.8', 'FrmCSVExportHelper::escape_csv');
		return FrmCSVExportHelper::escape_csv( $value );
	}
}
