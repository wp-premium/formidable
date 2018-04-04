<?php

class FrmProPost {
	public static function save_post( $action, $entry, $form ) {
		if ( $entry->post_id ) {
			$post = get_post( $entry->post_id, ARRAY_A );
			unset( $post['post_content'] );
			$new_post = self::setup_post($action, $entry, $form );
			self::insert_post( $entry, $new_post, $post, $form, $action );
		} else {
			self::create_post( $entry, $form, $action );
		}
	}

	public static function create_post( $entry, $form, $action = false ) {
		global $wpdb;

		$entry_id = is_object($entry) ? $entry->id : $entry;
		$form_id = is_object($form) ? $form->id : $form;

		if ( ! $action ) {
			$action = FrmFormAction::get_action_for_form( $form_id, 'wppost', 1 );

			if ( ! $action ) {
				return;
			}
		}

		$post = self::setup_post($action, $entry, $form);
		$post['post_type'] = $action->post_content['post_type'];

		$status = ( isset($post['post_status']) && ! empty($post['post_status']) ) ? true : false;

		if ( ! $status && $action && in_array( $action->post_content['post_status'], array( 'pending', 'publish' ) ) ) {
			$post['post_status'] = $action->post_content['post_status'];
		}

		if ( isset( $action->post_content['display_id'] ) && $action->post_content['display_id'] ) {
			$post['post_custom']['frm_display_id'] = $action->post_content['display_id'];
		} else if ( ! is_numeric( $action->post_content['post_content'] ) ) {
			// Do not set frm_display_id if the content is mapped to a single field

			//check for auto view and set frm_display_id - for reverse compatibility
			$display = FrmProDisplay::get_auto_custom_display( compact('form_id', 'entry_id') );
			if ( $display ) {
				$post['post_custom']['frm_display_id'] = $display->ID;
			}
		}

		$post_id = self::insert_post( $entry, $post, array(), $form, $action );
		return $post_id;
	}

	public static function insert_post( $entry, $new_post, $post, $form = false, $action = false ) {
		if ( ! $action ) {
			$action = FrmFormAction::get_action_for_form( $form->id, 'wppost', 1 );

			if ( ! $action ) {
				return;
			}
		}

		$post_fields = self::get_post_fields( $new_post, 'insert_post' );

		$editing = true;
		if ( empty($post) ) {
			$editing = false;
			$post = array();
		}

		foreach ( $post_fields as $post_field ) {
			if ( isset( $new_post[ $post_field ] ) ) {
				$post[ $post_field ] = $new_post[ $post_field ];
			}
			unset($post_field);
		}
		unset($post_fields);

		$dyn_content = '';
		self::post_value_overrides( $post, $new_post, $editing, $form, $entry, $dyn_content );

		$post = apply_filters( 'frm_before_create_post', $post, array( 'form' => $form, 'entry' => $entry ) );

		$post_ID = wp_insert_post( $post );

		if ( is_wp_error( $post_ID ) || empty($post_ID) ) {
			return;
		}

		self::save_taxonomies( $new_post, $post_ID );
		self::link_post_attachments( $post_ID, $editing );
		self::save_post_meta( $new_post, $post_ID );
		self::save_post_id_to_entry($post_ID, $entry, $editing);
		// Make sure save_post_id_to_entry stays above save_dynamic_content because
		// save_dynamic_content needs updated entry object from save_post_id_to_entry
		self::save_dynamic_content( $post, $post_ID, $dyn_content, $form, $entry );
		self::delete_duplicated_meta( $action, $entry );

		return $post_ID;
	}

	public static function destroy_post( $entry_id, $entry = false ) {
		global $wpdb;

		if ( $entry ) {
			$post_id = $entry->post_id;
		} else {
			$post_id = FrmDb::get_var( $wpdb->prefix . 'frm_items', array( 'id' => $entry_id ), 'post_id' );
		}

		// delete child entries
		$child_entries = FrmDb::get_col( $wpdb->prefix . 'frm_items', array( 'parent_item_id' => $entry_id ) );
		foreach ( $child_entries as $child_entry ) {
			FrmEntry::destroy( $child_entry );
		}

		// Remove hook to make things consistent
		// Due to a WP bug, this hook won't be used for parent entry when there are child entries
		remove_action( 'frm_before_destroy_entry', 'FrmProFormActionsController::trigger_delete_actions', 20, 2 );

		// Trigger delete actions for parent entry
		FrmProFormActionsController::trigger_delete_actions( $entry_id, $entry );

		if ( $post_id ) {
			wp_delete_post( $post_id );
		}
	}

	/**
	 * Insert all post variables into the post array
	 * @return array
	 */
	public static function setup_post( $action, $entry, $form ) {
		$temp_fields = FrmField::get_all_for_form($form->id);
		$fields = array();
		foreach ( $temp_fields as $f ) {
			$fields[ $f->id ] = $f;
			unset($f);
		}
		unset($temp_fields);

		$new_post = array(
			'post_custom' => array(),
			'taxonomies'    => array(),
			'post_category' => array(),
		);

		self::populate_post_author( $new_post );
		self::populate_post_fields( $action, $entry, $new_post );
		self::populate_custom_fields( $action, $entry, $fields, $new_post );
		self::populate_taxonomies( $action, $entry, $fields, $new_post );

		$new_post = apply_filters('frm_new_post', $new_post, compact('form', 'action', 'entry'));

		return $new_post;
	}

	private static function populate_post_author( &$post ) {
		$new_author = FrmAppHelper::get_post_param( 'frm_user_id', 0, 'absint' );
		if ( ! isset( $post['post_author'] ) && $new_author ) {
			$post['post_author'] = $new_author;
		}
	}

	private static function populate_post_fields( $action, $entry, &$new_post ) {
		$post_fields = self::get_post_fields( $new_post, 'post_fields' );

		foreach ( $post_fields as $setting_name ) {
			if ( ! is_numeric( $action->post_content[ $setting_name ] ) ) {
				continue;
			}

			$new_post[ $setting_name ] = isset( $entry->metas[ $action->post_content[ $setting_name ] ] ) ? $entry->metas[ $action->post_content[ $setting_name ] ] : '';

			if ( 'post_date' == $setting_name ) {
				$new_post[ $setting_name ] = FrmProAppHelper::maybe_convert_to_db_date( $new_post[ $setting_name ], 'Y-m-d H:i:s' );
			}

			unset( $setting_name );
		}
	}

	/**
	 * Make sure all post fields get included in the new post.
	 * Add the fields dynamically if they are included in the post.
	 *
	 * @since 2.0.2
	 */
	private static function get_post_fields( $new_post, $function ) {
		$post_fields = array(
			'post_content', 'post_excerpt', 'post_title',
			'post_name', 'post_date', 'post_status',
			'post_password',
		);

		if ( $function == 'insert_post' ) {
			$post_fields = array_merge( $post_fields, array( 'post_author', 'post_type', 'post_category', 'post_parent' ) );
			$extra_fields = array_keys( $new_post );
			$exclude_fields = array( 'post_custom', 'taxonomies', 'post_category' );
			$extra_fields = array_diff( $extra_fields, $exclude_fields, $post_fields );
			$post_fields = array_merge( $post_fields, $extra_fields );
		}

		return $post_fields;
	}

	/**
	 * Add custom fields to the post array
	 */
	private static function populate_custom_fields( $action, $entry, $fields, &$new_post ) {
		// populate custom fields
		foreach ( $action->post_content['post_custom_fields'] as $custom_field ) {
			if ( empty( $custom_field['field_id'] ) || empty( $custom_field['meta_name'] ) || ! isset( $fields[ $custom_field['field_id'] ] ) ) {
				continue;
			}

			$value = isset( $entry->metas[ $custom_field['field_id'] ] ) ? $entry->metas[ $custom_field['field_id'] ] : '';

			if ( $fields[ $custom_field['field_id'] ]->type == 'date' ) {
				$value = FrmProAppHelper::maybe_convert_to_db_date($value);
			}

			if ( isset( $new_post['post_custom'][ $custom_field['meta_name'] ] ) ) {
				$new_post['post_custom'][ $custom_field['meta_name'] ] = (array) $new_post['post_custom'][ $custom_field['meta_name'] ];
				$new_post['post_custom'][ $custom_field['meta_name'] ][] = $value;
			} else {
				$new_post['post_custom'][ $custom_field['meta_name'] ] = $value;
			}

			unset($value);
		}
	}

	private static function populate_taxonomies( $action, $entry, $fields, &$new_post ) {
		foreach ( $action->post_content['post_category'] as $taxonomy ) {
			if ( empty($taxonomy['field_id']) || empty($taxonomy['meta_name']) ) {
				continue;
			}

			$tax_type = ( isset($taxonomy['meta_name']) && ! empty($taxonomy['meta_name']) ) ? $taxonomy['meta_name'] : 'frm_tag';
			$value = isset( $entry->metas[ $taxonomy['field_id'] ] ) ? $entry->metas[ $taxonomy['field_id'] ] : '';

			if ( isset( $fields[ $taxonomy['field_id'] ] ) && $fields[ $taxonomy['field_id'] ]->type == 'tag' ) {
				$value = trim($value);
				$value = array_map('trim', explode(',', $value));

				if ( is_taxonomy_hierarchical($tax_type) ) {
					//create the term or check to see if it exists
					$terms = array();
					foreach ( $value as $v ) {
						$term_id = term_exists($v, $tax_type);

						// create new terms if they don't exist
						if ( ! $term_id ) {
							$term_id = wp_insert_term($v, $tax_type);
						}

						if ( $term_id && is_array( $term_id ) ) {
							$term_id = $term_id['term_id'];
						}

						if ( is_numeric($term_id) ) {
							$terms[ $term_id ] = $v;
						}

						unset($term_id, $v);
					}

					$value = $terms;
					unset($terms);
				}

				if ( isset( $new_post['taxonomies'][ $tax_type ] ) ) {
					$new_post['taxonomies'][ $tax_type ] += (array) $value;
				} else {
					$new_post['taxonomies'][ $tax_type ] = (array) $value;
				}
			} else {
				$value = (array) $value;

				// change text to numeric ids while importing
				if ( defined('WP_IMPORTING') ) {
					foreach ( $value as $k => $val ) {
						if ( empty($val) ) {
							continue;
						}

						$term = term_exists( $val, $fields[ $taxonomy['field_id'] ]->field_options['taxonomy']);
						if ( $term ) {
							$value[ $k ] = is_array( $term ) ? $term['term_id'] : $term;
						}

						unset($k, $val, $term);
					}
				}

				if ( 'category' == $tax_type ) {
					if ( ! empty($value) ) {
						$new_post['post_category'] = array_merge( $new_post['post_category'], $value );
					}
				} else {
					$new_value = array();
					foreach ( $value as $val ) {
						if ( $val == 0 ) {
							continue;
						}

						$new_value[ $val ] = self::get_taxonomy_term_name_from_id( $val, $fields[ $taxonomy['field_id'] ]->field_options['taxonomy'] );
					}

					self::fill_taxonomies($new_post['taxonomies'], $tax_type, $new_value);
				}
			}
		}
	}

	/**
	 * Get the taxonomy name from the ID
	 * If no term is retrieved, the ID will be returned
	 *
	 * @since 2.02.06
	 * @param int|string $term_id
	 * @param string $taxonomy
	 * @return string
	 */
	public static function get_taxonomy_term_name_from_id( $term_id, $taxonomy ) {
		$term = get_term( $term_id, $taxonomy );

		if ( $term && ! isset( $term->errors ) ) {
			$value = $term->name;
		} else {
			$value = $term_id;
		}

		return $value;
	}

	private static function fill_taxonomies( &$taxonomies, $tax_type, $new_value ) {
		if ( isset( $taxonomies[ $tax_type ] ) ) {
			foreach ( (array) $new_value as $new_key => $new_name ) {
				$taxonomies[ $tax_type ][ $new_key ] = $new_name;
			}
		} else {
			$taxonomies[ $tax_type ] = $new_value;
		}
	}

    /**
     * Override the post content and date format
     */
    private static function post_value_overrides( &$post, $new_post, $editing, $form, $entry, &$dyn_content ) {
        //if empty post content and auto display, then save compiled post content
		$default_display = isset( $new_post['post_custom']['frm_display_id'] ) ? $new_post['post_custom']['frm_display_id'] : 0;
		$display_id = ( $editing ) ? get_post_meta( $post['ID'], 'frm_display_id', true ) : $default_display;

        if ( ! isset($post['post_content']) && $display_id ) {
            $display = FrmProDisplay::getOne( $display_id, false, true);
			if ( $display ) {
				$dyn_content = ( 'one' == $display->frm_show_count ) ? $display->post_content : $display->frm_dyncontent;
				$post['post_content'] = apply_filters( 'frm_content', $dyn_content, $form, $entry );
			}
        }

		if ( isset( $post['post_date'] ) && ! empty( $post['post_date'] ) ) {
			// set post date gmt if post date is set
			$post['post_date_gmt'] = get_gmt_from_date( $post['post_date'] );
		}
    }

    /**
     * Add taxonomies after save in case user doesn't have permissions
     */
    private static function save_taxonomies( $new_post, $post_ID ) {
    	foreach ( $new_post['taxonomies'] as $taxonomy => $tags ) {
			// If setting hierarchical taxonomy or post_format, use IDs
			if ( is_taxonomy_hierarchical($taxonomy) || $taxonomy == 'post_format' ) {
    			$tags = array_keys($tags);
    		}

            wp_set_post_terms( $post_ID, $tags, $taxonomy );

    		unset($taxonomy, $tags);
        }
    }

	private static function link_post_attachments( $post_ID, $editing ) {
		global $frm_vars, $wpdb;

		$exclude_attached = array();
		if ( isset($frm_vars['media_id']) && ! empty($frm_vars['media_id']) ) {

			foreach ( (array) $frm_vars['media_id'] as $media_id ) {
				$exclude_attached = array_merge($exclude_attached, (array) $media_id);

				if ( is_array($media_id) ) {
					$attach_string = array_filter( $media_id );
					if ( ! empty($attach_string) ) {
						$where = array( 'post_type' => 'attachment', 'ID' => $attach_string );
						FrmDb::get_where_clause_and_values( $where );
						array_unshift( $where['values'], $post_ID );

						$wpdb->query( $wpdb->prepare( 'UPDATE ' . $wpdb->posts . ' SET post_parent = %d' . $where['where'], $where['values'] ) );

						foreach ( $media_id as $m ) {
							delete_post_meta( $m, '_frm_file' );
							clean_attachment_cache( $m );
							unset($m);
						}
					}
				} else {
					$wpdb->update( $wpdb->posts, array( 'post_parent' => $post_ID ), array( 'ID' => $media_id, 'post_type' => 'attachment' ) );
					delete_post_meta( $media_id, '_frm_file' );
					clean_attachment_cache( $media_id );
				}
			}
		}

		self::unlink_post_attachments($post_ID, $editing, $exclude_attached);
	}

	private static function unlink_post_attachments( $post_ID, $editing, $exclude_attached ) {
		if ( ! $editing ) {
			return;
		}

		$args = array(
			'post_type' => 'attachment', 'numberposts' => -1,
			'post_status' => null, 'post_parent' => $post_ID,
			'exclude' => $exclude_attached,
		);

		global $wpdb;

		$attachments = get_posts( $args );
		foreach ( $attachments as $attachment ) {
			$wpdb->update( $wpdb->posts, array( 'post_parent' => null ), array( 'ID' => $attachment->ID ) );
			update_post_meta( $attachment->ID, '_frm_file', 1 );
		}
	}

	private static function save_post_meta( $new_post, $post_ID ) {
		foreach ( $new_post['post_custom'] as $post_data => $value ) {
			if ( $value == '' ) {
				delete_post_meta($post_ID, $post_data);
			} else {
				update_post_meta($post_ID, $post_data, $value);
			}

			unset($post_data, $value);
		}

		global $user_ID;
		update_post_meta( $post_ID, '_edit_last', $user_ID );
	}

	/**
	 * save post_id with the entry
	 * If entry was updated, get updated entry object
	 */
	private static function save_post_id_to_entry( $post_ID, &$entry, $editing ) {
		if ( $editing ) {
			return;
		}

		global $wpdb;
		$updated = $wpdb->update( $wpdb->prefix . 'frm_items', array( 'post_id' => $post_ID ), array( 'id' => $entry->id ) );
		if ( $updated ) {
			wp_cache_delete( $entry->id, 'frm_entry' );
			wp_cache_delete( $entry->id . '_nometa', 'frm_entry' );
			// Save new post ID for later use
			$entry->post_id = $post_ID;
		}
	}

	/**
	 * update dynamic content after all post fields are updated
	 */
	private static function save_dynamic_content( $post, $post_ID, $dyn_content, $form, $entry ) {
		if ( $dyn_content == '' ) {
			return;
		}

		$new_content = apply_filters( 'frm_content', $dyn_content, $form, $entry );
		if ( $new_content != $post['post_content'] ) {
			global $wpdb;
			$wpdb->update( $wpdb->posts, array( 'post_content' => $new_content ), array( 'ID' => $post_ID ) );
		}
	}

	/**
	 * Delete entry meta so it won't be duplicated
	 *
	 * @param object $action
	 * @param object $entry
	 */
	private static function delete_duplicated_meta( $action, $entry ) {
		global $wpdb;

		$filtered_settings = self::get_post_field_settings( $action->post_content );

		$field_ids = array();
		self::get_post_field_ids_from_settings( $filtered_settings, $field_ids );

		if ( ! empty($field_ids) ) {
			$where = array( 'item_id' => $entry->id, 'field_id' => $field_ids );
			FrmDb::get_where_clause_and_values( $where );

			$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . 'frm_item_metas' . $where['where'], $where['values'] ) );
		}
	}

	/**
	 * Get the post field settings from a post action
	 *
	 * @since 2.2.11
	 *
	 * @param array $settings
	 * @return array $filtered
	 */
	private static function get_post_field_settings( $settings ) {
		$filtered = $settings;
		foreach ( $settings as $name => $value ) {
			if ( strpos( $name, 'post' ) !== 0 ) {
				unset( $filtered[ $name ] );
			}
		}

		return $filtered;
	}

	/**
	 * Get the field IDs from the post field settings
	 *
	 * @since 2.2.11
	 *
	 * @param array $settings
	 * @param array $field_ids
	 */
	private static function get_post_field_ids_from_settings( $settings, &$field_ids ) {
		foreach ( $settings as $name => $value ) {

			if ( is_numeric( $value ) ) {
				$field_ids[] = $value;
			} else if ( is_array( $value ) ) {
				if ( isset( $value['field_id'] ) && is_numeric( $value['field_id'] ) ) {
					$field_ids[] = $value['field_id'];
				} else {
					self::get_post_field_ids_from_settings( $value, $field_ids );
				}
			}
			unset( $name, $value );
		}
	}

	/**
	 * Get a category dropdown (for form builder, logic, or front-end)
	 *
	 * @since 2.02.07
	 * @param array (not multi-dimensional) $field
	 * @param array $args - must include 'name', 'id', and 'location'
	 * @return string
	 */
	public static function get_category_dropdown( $field, $args ) {
		if ( ! $field || ! isset( $args['location'] ) ) {
			return '';
		}

		$category_args = self::get_category_args( $field, $args );

		if ( empty( $category_args ) ) {
			return '';
		}

		$dropdown = wp_dropdown_categories( $category_args );

		if ( 'front' == $args['location'] ) {
			self::format_category_dropdown_for_front_end( $field, $args, $dropdown );
		} else if ( 'form_builder' == $args['location'] ) {
			self::format_category_dropdown_for_form_builder( $field, $args, $dropdown );
		} else if ( 'form_actions' == $args['location'] || 'field_logic' == $args['location'] ) {
			self::format_category_dropdown_for_logic( $args, $dropdown );
		}

		return $dropdown;
	}

	/**
	 * Format a category dropdown for a front-end form
	 *
	 * @since 2.02.07
	 * @param array $field
	 * @param array $args - must include placeholder_class, name, and id
	 * @param string $dropdown
	 */
	private static function format_category_dropdown_for_front_end( $field, $args, &$dropdown ) {
		// Add input HTML
		$add_html = FrmFieldsController::input_html( $field, false ) . FrmProFieldsController::input_html( $field, false );
		$dropdown = str_replace( " class='placeholder_class'", $add_html, $dropdown );

		// Set up hidden fields for read-only dropdown
		if ( FrmField::is_read_only( $field ) ) {
			$dropdown = str_replace( "name='" . $args['name'] . "'", '', $dropdown );
			$dropdown = str_replace( "id='" . $args['id'] . "'", '', $dropdown );
		}

		self::select_saved_values_in_category_dropdown( $field, $dropdown );
	}

	/**
	 * Format a category dropdown form the form builder page
	 *
	 * @since 2.02.07
	 * @param array $field
	 * @param array $args - must include placeholder_class and id
	 * @param string $dropdown
	 */
	private static function format_category_dropdown_for_form_builder( $field, $args, &$dropdown ) {
		// Remove placeholder class
		$dropdown = str_replace( " class='placeholder_class'", '', $dropdown );

		// Remove id
		$dropdown = str_replace( "id='" . $args['id'] . "'", '', $dropdown );

		self::select_saved_values_in_category_dropdown( $field, $dropdown );
	}

	/**
	 * Format a category dropdown for logic (in field or action)
	 *
	 * @since 2.02.07
	 * @param array $args - must include id and placeholder_class
	 * @param string $dropdown
	 */
	private static function format_category_dropdown_for_logic( $args, &$dropdown ) {
		// Remove placeholder id
		$dropdown = str_replace( "id='" . $args['id'] . "'", '', $dropdown );

		// Remove placeholder class
		$dropdown = str_replace( " class='placeholder_class'", '', $dropdown );

		// Set first value in category dropdown to empty string instead of 0
		$dropdown = str_replace( "value='0'", 'value=""', $dropdown );
	}

	/**
	 * Make sure all saved values are selected, not just the first
	 * This is necessary because only one value can be passed into the wp_dropdown_categories() function
	 *
	 * @since 2.02.07
	 * @param array $field
	 * @param string $dropdown
	 */
	private static function select_saved_values_in_category_dropdown( $field, &$dropdown ) {
		if ( is_array( $field['value'] ) ) {
			$skip = true;
			foreach ( $field['value'] as $v ) {
				if ( $skip ) {
					$skip = false;
					continue;
				}
				$dropdown = str_replace( ' value="' . esc_attr( $v ) . '"', ' value="' . esc_attr( $v ) . '" selected="selected"', $dropdown );
				unset( $v );
			}
		}
	}

	/**
	 * Get the arguments that will be passed into the wp_dropdown_categories function
	 *
	 * @since 2.02.07
	 * @param array $field
	 * @param array $args - must include 'name', 'id', 'location', and 'placeholder_class'
	 * @return array
	 */
	private static function get_category_args( $field, $args ) {
		$show_option_all = isset( $args['show_option_all'] ) ? $args['show_option_all'] : ' ';

		$exclude = ( is_array( $field['exclude_cat'] ) ) ? implode( ',', $field['exclude_cat'] ) : $field['exclude_cat'];
		$exclude = apply_filters( 'frm_exclude_cats', $exclude, $field );

		if ( is_array( $field['value'] ) ) {
			if ( ! empty( $exclude ) ) {
				$field['value'] = array_diff( $field['value'], explode( ',', $exclude ) );
			}
			$selected = reset( $field['value'] );
		} else {
			$selected = $field['value'];
		}

		$tax_atts = array(
			'show_option_all' => $show_option_all,
			'hierarchical' => 1,
			'name' => $args['name'],
			'id' => $args['id'],
			'exclude' => $exclude,
			'class' => 'placeholder_class',
			'selected' => $selected,
			'hide_empty' => false,
			'echo' => 0,
			'orderby' => 'name',
		);

		$tax_atts = apply_filters( 'frm_dropdown_cat', $tax_atts, $field );

		$post_type = FrmProFormsHelper::post_type( $field['form_id'] );
		$tax_atts['taxonomy'] = FrmProAppHelper::get_custom_taxonomy( $post_type, $field );
		if ( ! $tax_atts['taxonomy'] ) {
			return array();
		}

		// If field type is dropdown (not Dynamic), exclude children when parent is excluded
		if ( $field['type'] != 'data' && is_taxonomy_hierarchical( $tax_atts['taxonomy'] ) ) {
			$tax_atts['exclude_tree'] = $exclude;
		}

		return $tax_atts;
	}
}
