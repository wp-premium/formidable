<?php

class FrmProCopiesController {

	public static function install() {
		if ( is_multisite() ) {
			FrmProCopy::install();
		}
	}

    public static function activation_install() {
        // make sure post type exists before creating any views
        FrmProDisplaysController::register_post_types();
        self::install();
    }

	/**
	 * Importing default templates is happening before the tables are installed
	 *
	 * @since 2.05.09
	 */
	public static function maybe_install_import() {
		global $frm_vars;

		$importing = defined( 'WP_IMPORTING' ) && WP_IMPORTING;
		$upgrading = isset( $frm_vars['doing_upgrade'] ) ? $frm_vars['doing_upgrade'] : false;

		if ( $importing || $upgrading ) {
			$install_complete = get_option( 'frmpro_db_version' );
			if ( ! $install_complete ) {
				self::install();
			}
		}
	}

	/**
	 * @since 2.05.09
	 */
	public static function copy_forms() {
		FrmProCopy::copy_forms();
	}

	public static function save_copied_display( $id, $values ) {
		global $wpdb, $blog_id;

		self::maybe_install_import();

		$wpdb->delete( FrmProCopy::table_name(), array(
			'form_id' => $id,
			'type'    => 'display',
			'blog_id' => $blog_id,
		) );

		if ( isset( $values['options']['copy'] ) && $values['options']['copy'] ) {
			FrmProCopy::create( array(
				'form_id' => $id,
				'type'    => 'display',
			) );
		}
	}

	public static function save_copied_form( $id, $values ) {
		global $blog_id, $wpdb;

		self::maybe_install_import();

		$form_key = FrmForm::get_key_by_id( $id );
		if ( 'contact' === $form_key ) {
			// don't copy the form that is already autocreated
			return;
		}

		if ( isset( $values['options']['copy'] ) && $values['options']['copy'] ) {
			FrmProCopy::create( array(
				'form_id' => $id,
				'type'    => 'form',
			) );
		} else {
			$wpdb->delete( FrmProCopy::table_name(), array(
				'type'    => 'form',
				'form_id' => $id,
				'blog_id' => $blog_id,
			) );
		}
	}

	public static function destroy_copied_display( $id ) {
        global $blog_id, $wpdb;
        $copies = FrmProCopy::getAll( array( 'blog_id' => $blog_id, 'form_id' => $id, 'type' => 'display'));
		foreach ( $copies as $copy ) {
            FrmProCopy::destroy($copy->id);
            unset($copy);
        }
    }

	public static function destroy_copied_form( $id ) {
        global $blog_id, $wpdb;
        $copies = FrmProCopy::getAll( array( 'blog_id' => $blog_id, 'form_id' => $id, 'type' => 'form'));
		foreach ( $copies as $copy ) {
			FrmProCopy::destroy( $copy->id );
		}
    }

	public static function delete_copy_rows( $blog_id, $drop ) {
        $blog_id = (int) $blog_id;
        if ( ! $drop || ! $blog_id ) {
            return;
        }

		$copies = FrmProCopy::getAll( array( 'blog_id' => $blog_id ) );
		foreach ( $copies as $copy ) {
			FrmProCopy::destroy( $copy->id );
			unset( $copy );
        }
    }
}
