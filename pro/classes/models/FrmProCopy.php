<?php

class FrmProCopy {

    public static function table_name() {
        global $wpmuBaseTablePrefix, $wpdb;
        $prefix = $wpmuBaseTablePrefix ? $wpmuBaseTablePrefix : $wpdb->base_prefix;
		return $prefix . 'frmpro_copies';
    }

	public static function create( $values ) {
		global $wpdb, $blog_id;

		$new_values = array();
		self::prepare_values( $values, $new_values );

		$id = false;

		$exists = self::getAll( array( 'blog_id' => $blog_id, 'form_id' => $new_values['form_id'], 'type' => $new_values['type'] ), '', ' LIMIT 1' );
		if ( ! $exists ) {
			$query_results = $wpdb->insert( self::table_name(), $new_values );

			if ( $query_results ) {
				$id = $wpdb->insert_id;
			}
		}
		return $id;
	}

	public static function destroy( $id ) {
		global $wpdb;
		return $wpdb->delete( self::table_name(), array( 'id' => $id ) );
	}

	public static function getAll( $where = array(), $order_by = '', $limit = '' ) {
		$method = ( $limit == ' LIMIT 1' ) ? 'row' : 'results';
		$results = FrmDb::get_var( self::table_name(), $where, '*', $args = array( 'order_by' => $order_by ), $limit, $method );

		return $results;
	}

	/**
	 * @since 2.02.10
	 * @param array $values
	 * @param array $new_values
	 */
	private static function prepare_values( $values, &$new_values ) {
		global $blog_id;
		$new_values['blog_id'] = $blog_id;
		$new_values['form_id'] = isset( $values['form_id'] ) ? (int) $values['form_id'] : null;
		$new_values['type'] = isset( $values['type'] ) ? $values['type'] : 'form'; //options here are: form, display
		if ( $new_values['type'] == 'form' ) {
			$form_copied = FrmForm::getOne( $new_values['form_id'] );
			$new_values['copy_key'] = $form_copied->form_key;
		} else {
			$form_copied = FrmProDisplay::getOne( $new_values['form_id'] );
			$new_values['copy_key'] = $form_copied->post_name;
		}
		$new_values['created_at'] = current_time('mysql', 1);
	}

    public static function install( $force = false ) {
        $db_version = 1.2; // this is the version of the database we're moving to
        $old_db_version = get_site_option('frmpro_copies_db_version');

        global $wpdb;

		if ( ( $db_version != $old_db_version ) || $force ) {
            $force = true;

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            $frmdb = new FrmMigrate();
            $charset_collate = $frmdb->collation();

            /* Create/Upgrade Display Table */
			$sql = 'CREATE TABLE ' . self::table_name() . ' (
                    id int(11) NOT NULL auto_increment,
                    type varchar(255) default NULL,
                    copy_key varchar(255) default NULL,
                    form_id int(11) default NULL,
                    blog_id int(11) default NULL,
                    created_at datetime NOT NULL,
                    PRIMARY KEY id (id),
                    KEY form_id (form_id),
                    KEY blog_id (blog_id)
			) ' . $charset_collate . ';';

            dbDelta($sql);

            update_site_option('frmpro_copies_db_version', $db_version);
        }

        self::copy_forms($force);
    }

	/**
	 * Copy forms that are set to copy from one site to another
	 */
	public static function copy_forms( $force = false ) {
		self::maybe_force( $force );
		if ( ! $force ) {
			return;
		}

		$templates = self::get_templates_to_copy();

		foreach ( $templates as $temp ) {
			if ( $temp->type == 'form' ) {
				self::copy_form( $temp );
			} else {
				self::copy_view( $temp );
			}

			//TODO: replace any ids with field keys in the display before duplicated
			unset( $temp );
		}

		update_option( 'frmpro_copies_checked', time() );
	}

	/**
	 * @since 2.02.10
	 * @param boolean $force
	 */
	private static function maybe_force( &$force ) {
		if ( ! $force ) { //don't check on every page load
			$last_checked = get_option('frmpro_copies_checked');

			if ( ! $last_checked || ( ( time() - $last_checked ) >= ( 60 * 60 ) ) ) {
				//check every hour
				$force = true;
			}
		}
	}

	/**
	 * Get all forms to be copied from global table
	 *
	 * @since 2.02.10
	 */
	private static function get_templates_to_copy() {
		global $wpdb, $blog_id;

		$query = 'SELECT c.*, p.post_name FROM ' . self::table_name() . ' c ' .
				'LEFT JOIN ' . $wpdb->prefix . 'frm_forms f ON (c.copy_key = f.form_key) ' .
				'LEFT JOIN ' . $wpdb->posts . ' p ON (c.copy_key = p.post_name) ' .
				'WHERE blog_id != %d ' .
				'AND ((type = %s AND f.form_key is NULL) OR (type = %s AND p.post_name is NULL)) ' .
				'ORDER BY type DESC';
		$query = $wpdb->prepare( $query, $blog_id, 'form', 'display' );

		return FrmDb::check_cache( 'all_templates_' . $blog_id, 'frm_copy', $query, 'get_results' );
	}

	/**
	 * @since 2.02.10
	 *
	 * @param object $template
	 */
	private static function copy_form( $template ) {
		FrmForm::duplicate( $template->form_id, false, true, $template->blog_id );
	}

	/**
	 * @since 2.02.10
	 * @param object $template
	 */
	private static function copy_view( $template ) {
		$values = FrmProDisplay::getOne( $template->form_id, $template->blog_id, true );
		if ( $values && 'trash' != $values->post_status ) {

			if ( ! self::post_slug_exists( $values ) ) {

				if ( $values->post_name != $template->copy_key ) {
					global $wpdb;
					$wpdb->update( self::table_name(), array( 'copy_key' => $values->post_name ), array( 'id' => $template->id ) );
				}

				FrmProDisplay::duplicate( $template->form_id, true, $template->blog_id );
			}
		}
	}

	/**
	 * @since 2.02.10
	 * @param object $values
	 *
	 * @return bool
	 */
	private static function post_slug_exists( $values ) {
		$post_name = wp_unique_post_slug( $values->post_name, 0, 'publish', 'frm_display', 0 );
		return ( $post_name != $values->post_name );
	}
}
