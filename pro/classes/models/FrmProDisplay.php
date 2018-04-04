<?php
class FrmProDisplay {

    public static function duplicate( $id, $copy_keys = false, $blog_id = false ) {
        global $wpdb;

        $values = self::getOne( $id, $blog_id, true );

        if ( ! $values || ! is_numeric($values->frm_form_id) ) {
            return false;
        }

        $new_values = array();
		foreach ( array( 'post_name', 'post_title', 'post_excerpt', 'post_content', 'post_status', 'post_type' ) as $k ) {
			$new_values[ $k ] = $values->{$k};
            unset($k);
        }

        $meta = array();
		foreach ( array( 'form_id', 'entry_id', 'dyncontent', 'param', 'type', 'show_count' ) as $k ) {
			$meta[ $k ] = $values->{'frm_' . $k};
			unset( $k );
        }

        $default = FrmProDisplaysHelper::get_default_opts();
        $meta['options'] = array();
		foreach ( $default as $k => $v ) {
			if ( isset( $meta[ $k ] ) ) {
				continue;
			}

			$meta['options'][ $k ] = $values->{'frm_' . $k};
            unset($k, $v);
        }
        $meta['options']['copy'] = false;

		if ( $blog_id ) {
            $old_form = FrmForm::getOne($values->frm_form_id, $blog_id);
            $new_form = FrmForm::getOne($old_form->form_key);
            $meta['form_id'] = $new_form->id;
		} else {
            $meta['form_id'] = $values->form_id;
        }

        $post_ID = wp_insert_post( $new_values );

		$new_values = array_merge( (array) $new_values, $meta );

        self::update($post_ID, $new_values);

        return $post_ID;
    }

	public static function update( $id, $values ) {
        $new_values = array();
        $new_values['frm_param'] = isset($values['param']) ? sanitize_title_with_dashes($values['param']) : '';

		$fields = array( 'dyncontent', 'type', 'show_count', 'form_id', 'entry_id' );
		foreach ( $fields as $field ) {
            if ( isset( $values[ $field ] ) ) {
				$new_values[ 'frm_' . $field ] = $values[ $field ];
			}
        }

		if ( isset( $values['options'] ) ) {
            $new_values['frm_options'] = array();
			foreach ( $values['options'] as $key => $value ) {
				$new_values['frm_options'][ $key ] = $value;
			}
        }

		foreach ( $new_values as $key => $val ) {
			if ( $key == 'frm_param' ) {
				$last_param = get_post_meta( $id, $key, true );
				if ( $last_param != $val ) {
					update_post_meta( $id, $key, $val );
					add_rewrite_endpoint( $val, EP_PERMALINK | EP_PAGES );
					flush_rewrite_rules();
				}
			} else {
				update_post_meta( $id, $key, $val );
			}

			unset( $key, $val );
        }
    }

    public static function getOne( $id, $blog_id = false, $get_meta = false, $atts = array() ) {
        global $wpdb;

		if ( $blog_id && is_multisite() ) {
			switch_to_blog( $blog_id );
		}

		$id = sanitize_title( $id );
        if ( ! is_numeric($id) ) {
            $id = FrmDb::get_var( $wpdb->posts, array( 'post_name' => $id, 'post_type' => 'frm_display', 'post_status !' => 'trash'), 'ID' );

            if ( is_multisite() && empty($id) ) {
                return false;
            }
        }

		if ( empty( $id ) ) {
			// don't let it get the current page
			return false;
		}

        $post = get_post($id);
        if ( ! $post || $post->post_type != 'frm_display' || $post->post_status == 'trash' ) {
            $args = array(
                'post_type' => 'frm_display',
                'meta_key' => 'frm_old_id',
                'meta_value' => $id,
                'numberposts' => 1,
                'post_status' => 'publish'
            );
            $posts = get_posts($args);

            if ( $posts ) {
                $post = reset($posts);
            }
        }

        if ( $post && $post->post_status == 'trash' ) {
            return false;
        }

        if ( $post && $get_meta ) {
            $check_post = isset($atts['check_post']) ? $atts['check_post'] : false;
            $post = FrmProDisplaysHelper::setup_edit_vars($post, $check_post);
        }

        if ( $blog_id && is_multisite() ) {
            restore_current_blog();
        }

        return $post;
    }

	public static function getAll( $where = array(), $order_by = 'post_date', $limit = 99 ) {
		if ( ! is_numeric($limit) ) {
			$limit = (int) $limit;
		}

		$order = 'DESC';
		if ( strpos( $order_by, ' ' ) ) {
			list( $order_by, $order ) = explode( ' ', $order_by );
		}

		$query = array(
			'numberposts'   => $limit,
			'orderby'       => $order_by,
			'order'         => $order,
			'post_type'     => 'frm_display',
			'post_status'   => array( 'publish', 'private' ),
		);
		$query = array_merge( (array) $where, $query );

		$results = get_posts($query);
		return $results;
	}

    /**
     * Check for a qualified view.
     * Qualified:   1. set to show calendar or dynamic
     *              2. published
     *              3. form has posts/entry is linked to a post
     */
	public static function get_auto_custom_display( $args ) {
        $defaults = array( 'post_id' => false, 'form_id' => false, 'entry_id' => false);
        $args = wp_parse_args( $args, $defaults );

        global $wpdb;

        if ( $args['form_id'] ) {
            $display_ids = self::get_display_ids_by_form( $args['form_id'] );

            if ( ! $display_ids ) {
                return false;
            }

            if ( ! $args['post_id'] && ! $args['entry_id'] ) {
                //does form have posts?
				$args['entry_id'] = FrmDb::get_var( 'frm_items', array( 'form_id' => $args['form_id'] ), 'post_id' );
            }
        }

        if ( $args['post_id'] && ! $args['entry_id'] ) {
            //is post linked to an entry?
			$args['entry_id'] = FrmDb::get_var( $wpdb->prefix . 'frm_items', array( 'post_id' => $args['post_id'] ) );
        }

        //this post does not have an auto display
        if ( ! $args['entry_id'] ) {
            return false;
        }

		$query = array(
			'pm.meta_key'   => 'frm_show_count',
			'post_type'     => 'frm_display',
			'pm.meta_value' => array( 'dynamic', 'calendar', 'one' ),
			'p.post_status' => 'publish',
		);

        if ( isset($display_ids) ) {
            $query['p.ID'] = $display_ids;
        }

		$display = FrmDb::get_row( $wpdb->posts . ' p LEFT JOIN ' . $wpdb->postmeta . ' pm ON (p.ID = pm.post_ID)', $query, 'p.*', array( 'order_by' => 'p.ID ASC' ) );

        return $display;
    }

	public static function get_display_ids_by_form( $form_id ) {
		global $wpdb;
		return FrmDb::get_col( $wpdb->postmeta, array( 'meta_key' => 'frm_form_id', 'meta_value' => $form_id ), 'post_ID' );
	}

	public static function get_form_custom_display( $form_id ) {
        global $wpdb;

        $display_ids = self::get_display_ids_by_form( $form_id );

        if ( ! $display_ids ) {
            return false;
        }

        $display = FrmDb::get_row(
			$wpdb->posts . ' p LEFT JOIN ' . $wpdb->postmeta . ' pm ON (p.ID = pm.post_ID)',
			array(
				'pm.meta_key' => 'frm_show_count',
				'post_type' => 'frm_display',
				'p.ID' => $display_ids,
				'pm.meta_value' => array( 'dynamic', 'calendar', 'one' ),
				'p.post_status' => 'publish',
			),
            'p.*', array( 'order_by' => 'p.ID ASC' )
        );

        return $display;
    }

	public static function validate( $values ) {
        $errors = array();

		if ( $values['post_title'] == '' ) {
			$errors[] = __( 'Name cannot be blank', 'formidable-pro' );
		}

        if ( $values['excerpt'] == __( 'This is not displayed anywhere, but is just for your reference. (optional)', 'formidable-pro' ) ) {
			$_POST['excerpt'] = '';
		}

		if ( $values['content'] == '' ) {
			$errors[] = __( 'Content cannot be blank', 'formidable-pro' );
		}

        if ( ! empty($values['options']['limit']) && ! is_numeric($values['options']['limit']) ) {
            $errors[] = __( 'Limit must be a number', 'formidable-pro' );
        }

		if ( $values['show_count'] == 'dynamic' ) {
			if ( $values['dyncontent'] == '' ) {
                $errors[] = __( 'Dynamic Content cannot be blank', 'formidable-pro' );
			}
        }

		if ( isset( $values['options']['where'] ) ) {
            $_POST['options']['where'] = FrmProAppHelper::reset_keys($values['options']['where']);
            $_POST['options']['where_is'] = FrmProAppHelper::reset_keys($values['options']['where_is']);
            $_POST['options']['where_val'] = FrmProAppHelper::reset_keys($values['options']['where_val']);
        }

        return $errors;
    }

	/**
	 * Get the ID of a View using the key
	 *
	 * @since 2.0.21
	 * @param $key
	 * @return int
	 */
	public static function get_id_by_key( $key ) {
        $id = FrmDb::get_var( 'posts', array( 'post_name' => sanitize_title( $key ) ) );
        return $id;
	}
}
