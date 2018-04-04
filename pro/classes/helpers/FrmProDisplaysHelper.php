<?php

class FrmProDisplaysHelper {

	public static function setup_new_vars() {
        $values = array();
        $defaults = self::get_default_opts();
		foreach ( $defaults as $var => $default ) {
			$sanitize = self::sanitize_option( $var );
			$values[ $var ] = FrmAppHelper::get_param( $var, $default, 'post', $sanitize );
		}

        return $values;
    }

    public static function setup_edit_vars( $post, $check_post = true ) {
        if ( ! $post ) {
            return false;
        }

        $values = (object) $post;

		foreach ( array( 'form_id', 'entry_id', 'dyncontent', 'param', 'type', 'show_count' ) as $var ) {
			$values->{'frm_' . $var} = get_post_meta( $post->ID, 'frm_' . $var, true );
            if ( $check_post ) {
				$sanitize = self::sanitize_option( $var );
				$values->{'frm_' . $var} = FrmAppHelper::get_param( $var, $values->{'frm_' . $var}, 'post', $sanitize );
            }
        }

		$defaults = self::get_default_opts();
        $options = get_post_meta($post->ID, 'frm_options', true);
		foreach ( $defaults as $var => $default ) {
			if ( ! isset( $values->{'frm_' . $var} ) ) {
				$values->{'frm_' . $var} = isset( $options[ $var ] ) ? $options[ $var ] : $default;
                if ( $check_post ) {
					$sanitize = self::sanitize_option( $var );
					$values->{'frm_' . $var} = FrmAppHelper::get_post_param( 'options[' . $var . ']', $values->{'frm_' . $var}, $sanitize );
                }
			} elseif ( $var == 'param' && empty( $values->{'frm_' . $var} ) ) {
				$values->{'frm_' . $var} = $default;
            }
        }

	    $values->frm_form_id = (int) $values->frm_form_id;
		$values->frm_order_by = empty($values->frm_order_by) ? array() : (array) $values->frm_order_by;
		$values->frm_order = empty($values->frm_order) ? array() : (array) $values->frm_order;

        return $values;
    }

	/**
	 * Allow script and style tags in content boxes,
	 * but remove them from other settings
	 *
	 * @since 2.05.05
	 */
	private static function sanitize_option( $name ) {
		$allow_code = array( 'before_content', 'content', 'after_content', 'dyncontent', 'empty_msg', 'where_is' );
		return in_array( $name, $allow_code ) ? '' : 'sanitize_text_field';
	}

	public static function get_default_opts() {

        return array(
            'name' => '', 'description' => '', 'display_key' => '',
            'form_id' => 0, 'date_field_id' => '', 'edate_field_id' => '',
			'repeat_event_field_id' => '', 'repeat_edate_field_id' => '', 'entry_id' => '',
			'before_content' => '', 'content' => '',
            'after_content' => '', 'dyncontent' => '', 'param' => 'entry',
			'type' => '', 'show_count' => 'all', 'no_rt' => 0,
            'order_by' => array(), 'order' => array(), 'limit' => '', 'page_size' => '',
            'empty_msg' => __( 'No Entries Found', 'formidable-pro' ), 'copy' => 0,
			'where' => array(), 'where_is' => array(), 'where_val' => array(),
			'group_by' => array(),
        );
    }

    public static function is_edit_view_page() {
        global $pagenow;
		$post_type = FrmAppHelper::simple_get( 'post_type', 'sanitize_title' );
		return is_admin() && $pagenow == 'edit.php' && $post_type == FrmProDisplaysController::$post_type;
    }

    public static function prepare_duplicate_view( &$post ) {
        $post = self::get_current_view($post);
        $post = self::setup_edit_vars($post);
    }

    /**
    * Check if a View has been duplicated. If it has, get the View object to be duplicated. If it has not been duplicated, just get the new post object.
    *
    * @param object $post
    * @return object - the View to be copied or the View that is being created (if it is not being duplicated)
    */
    public static function get_current_view( $post ) {
        if ( $post->post_type == FrmProDisplaysController::$post_type && isset($_GET['copy_id']) ) {
            global $copy_display;
            return $copy_display;
        }
        return $post;
    }

	public static function get_shortcodes( $content, $form_id ) {
		if ( empty( $form_id ) || strpos( $content, '[' ) === false ) {
			// don't continue if there are no shortcodes to check
			return array( array() );
        }

        $tagregexp = array(
            'deletelink', 'detaillink',
            'evenodd', 'get', 'entry_count', 'event_date',
			'is[-|_]draft',
        );

        $form_id = (int) $form_id;
		$form_ids = self::linked_form_ids( $form_id );

		$field_query = array(
			'form_id' => $form_ids,
			'or'      => 1,
		);
		$field_keys = FrmDb::get_col( 'frm_fields', $field_query, 'field_key' );

		$tagregexp = array_merge( $tagregexp, $field_keys );
		$tagregexp = implode( '|', $tagregexp ) . '|';
		$tagregexp .= FrmFieldsHelper::allowed_shortcodes();

		self::maybe_increase_regex_limit();

		preg_match_all( "/\[(if |foreach )?(\d+|$tagregexp)\b(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?/s", $content, $matches, PREG_PATTERN_ORDER );

		$matches[0] = self::organize_and_filter_shortcodes( $matches[0] );

        return $matches;
    }

	/**
	 * Get the ids of any child forms (repeat or embedded)
	 *
	 * @since 3.0
	 */
	private static function linked_form_ids( $form_id ) {
		$linked_field_query = array(
			'form_id' => $form_id,
			'type'    => array( 'divider', 'form' ),
		);
		$fields = FrmDb::get_col( 'frm_fields', $linked_field_query, 'field_options' );

		$form_ids = array( $form_id );
		foreach ( $fields as $field_options ) {
			$field_options = maybe_unserialize( $field_options );
			if ( isset( $field_options['form_select'] ) && ! empty( $field_options['form_select'] ) ) {
				$form_ids[] = $field_options['form_select'];
			}
			unset( $field_options );
		}

		return $form_ids;
	}

	/**
	 * make sure the backtrack limit is as least at the default
	 * @since 3.0
	 */
	private static function maybe_increase_regex_limit() {
	    $backtrack_limit = ini_get( 'pcre.backtrack_limit' );
	    if ( $backtrack_limit < 1000000 ) {
		    ini_set( 'pcre.backtrack_limit', 1000000 );
	    }
	}

	/**
	 * Put conditionals and foreach first
	 * Remove duplicate conditional and foreach tags
	 *
	 * @since 2.01.03
	 * @param array $shortcodes
	 * @return array $shortcodes
	 */
	private static function organize_and_filter_shortcodes( $shortcodes ) {
		$move_up = array();

		foreach ( $shortcodes as $short_key => $tag ) {
			$conditional = preg_match( '/^\[if/s', $shortcodes[ $short_key ] ) ? true : false;

			$foreach = preg_match( '/^\[foreach/s', $shortcodes[ $short_key ] ) ? true : false;

			if ( $conditional || $foreach ) {
				if ( ! in_array( $tag, $move_up ) ) {
					$move_up[ $short_key ] = $tag;
				}
				unset( $shortcodes[ $short_key ] );
			}
		}

		if ( ! empty( $move_up ) ) {
			$shortcodes = $move_up + $shortcodes;
		}

		return $shortcodes;
	}

	public static function where_is_options() {
		return array(
			'='               => __( 'equal to', 'formidable-pro' ),
			'!='              => __( 'NOT equal to', 'formidable-pro' ),
			'>'               => __( 'greater than', 'formidable-pro' ),
			'<'               => __( 'less than', 'formidable-pro' ),
			'>='              => __( 'greater than or equal to', 'formidable-pro' ),
			'<='              => __( 'less than or equal to', 'formidable-pro' ),
			'LIKE'            => __( 'like', 'formidable-pro' ),
			'not LIKE'        => __( 'NOT like', 'formidable-pro' ),
			'LIKE%'           => __( 'starts with', 'formidable-pro' ),
			'%LIKE'           => __( 'ends with', 'formidable-pro' ),
			'group_by'        => __( 'unique (get oldest entries)', 'formidable-pro' ),
			'group_by_newest' => __( 'unique (get newest entries)', 'formidable-pro' ),
		);
	}
}
