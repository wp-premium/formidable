<?php

class FrmProDynamicFieldsController {

	/**
	 * Add options for a Dynamic field
	 *
	 * @since 2.01.0
	 * @param object $field
	 * @param array $values
	 */
	public static function add_options_for_dynamic_field( $field, &$values, $atts = array() ) {
		if ( self::is_field_independent( $values ) ) {
			$entry_id = isset( $atts['entry_id'] ) ? $atts['entry_id'] : 0;
			$values['options'] = self::get_independent_options( $values, $field, $entry_id );
		} else if ( is_numeric( $values['value'] ) ) {
			$values['options'] = array();
			if ( $field->field_options['data_type'] == 'select' ) {
				// add blank option for dropdown
				$values['options'][''] = '';
			}
			$values['options'][ $values['value'] ] = FrmEntryMeta::get_entry_meta_by_field( $values['value'], $values['form_select'] );
		}
	}

	/**
	 * Check if Dynamic field is independent of other Dynamic fields
	 *
	 * @since 2.01.0
	 * @param array $values
	 * @return bool
	 */
	private static function is_field_independent( $values ) {
		$independent = true;
		if ( ! empty( $values['hide_field'] ) && ( ! empty( $values['hide_opt'] ) || ! empty( $values['form_select'] ) ) ) {
			foreach ( $values['hide_field'] as $hkey => $f ) {
				if ( ! empty( $values['hide_opt'][ $hkey ] ) ) {
					continue;
				}
				$f = FrmField::getOne( $f );
				if ( $f && $f->type == 'data' ) {
					$independent = false;
					break;
				}
				unset( $f, $hkey );
			}
		}

		return $independent;
	}

	/**
	 * Get the options for an independent Dynamic field
	 *
	 * @param array $values
	 * @param object $field
	 * @param bool|int $entry_id
	 * @return array
	 */
	public static function get_independent_options( $values, $field, $entry_id = false ) {
		global $user_ID, $wpdb;

		$metas = array();
		$selected_field = FrmField::getOne( $values['form_select'] );

		if ( ! $selected_field ) {
			return array();
		}

		$linked_is_post_field = FrmField::get_option( $selected_field, 'post_field' );
		$linked_posts = $linked_is_post_field && $linked_is_post_field != '';

		$post_ids = array();

		if ( is_numeric( $values['hide_field'] ) && empty( $values['hide_opt'] ) ) {
			if ( isset( $_POST ) && isset( $_POST['item_meta'] ) ) {
				$observed_field_val = ( isset( $_POST['item_meta'][ $values['hide_field'] ] ) ) ? $_POST['item_meta'][ $values['hide_field'] ] : '';
			} else if ( $entry_id ) {
				$observed_field_val = FrmEntryMeta::get_entry_meta_by_field( $entry_id, $values['hide_field'] );
			} else {
				$observed_field_val = '';
			}

			$observed_field_val = maybe_unserialize( $observed_field_val );

			$metas = array();
			FrmProEntryMetaHelper::meta_through_join( $values['hide_field'], $selected_field, $observed_field_val, false, $metas );

		} else if ( $values['restrict'] && $user_ID ) {
			$entry_user = FrmProEntryMetaHelper::user_for_dynamic_opts( $user_ID, compact( 'entry_id', 'field' ) );

			if ( isset( $selected_field->form_id ) ) {
				$linked_where = array( 'form_id' => $selected_field->form_id, 'user_id' => $entry_user );
				if ( $linked_posts ) {
					$post_ids = FrmDb::get_results( 'frm_items', $linked_where, 'id, post_id' );
				} else {
					$entry_ids = FrmDb::get_col( $wpdb->prefix . 'frm_items', $linked_where, 'id' );
				}
				unset( $linked_where );
			}

			if ( isset( $entry_ids ) && ! empty( $entry_ids ) ) {
				$metas = FrmEntryMeta::getAll( array( 'it.item_id' => $entry_ids, 'field_id' => (int) $values['form_select'] ), ' ORDER BY meta_value', '' );
			}
		} else {
			$limit = '';
			if ( FrmAppHelper::is_admin_page( 'formidable' ) ) {
				$limit = 500;
			}

			$metas = FrmDb::get_results( 'frm_item_metas', array( 'field_id' => $values['form_select'] ), 'item_id, meta_value', array( 'order_by' => 'meta_value', 'limit' => $limit ) );
			$post_ids = FrmDb::get_results( 'frm_items', array( 'form_id' => $selected_field->form_id ), 'id, post_id', array( 'limit' => $limit ) );
		}

		if ( $linked_posts && ! empty( $post_ids ) ) {
			foreach ( $post_ids as $entry ) {
				$meta_value = FrmProEntryMetaHelper::get_post_value( $entry->post_id, $selected_field->field_options['post_field'], $selected_field->field_options['custom_field'], array( 'type' => $selected_field->type, 'form_id' => $selected_field->form_id, 'field' => $selected_field ) );
				$metas[] = array( 'meta_value' => $meta_value, 'item_id' => $entry->id );
			}
		}

		$options = array();
		foreach ( $metas as $meta ) {
			$meta = (array) $meta;
			if ( $meta['meta_value'] == '' ) {
				continue;
			}

			$new_value = FrmEntriesHelper::display_value( $meta['meta_value'], $selected_field, array( 'type' => $selected_field->type, 'show_icon' => true, 'show_filename' => false ) );
			if ( $field->field_options['data_type'] == 'select' || FrmAppHelper::is_admin_page('formidable') ) {
				$new_value = strip_tags( $new_value );
			}

			$options[ $meta['item_id'] ] = $new_value;

			unset( $meta );
		}

		$options = apply_filters( 'frm_data_sort', $options, array( 'metas' => $metas, 'field' => $selected_field, 'dynamic_field' => $values ) );
		unset( $metas );

		if ( self::include_blank_option( $options, $field ) ) {
			$options = array( '' => '' ) + (array) $options;
		}

		return stripslashes_deep( $options );
	}

	/**
	 * A dropdown field should include a blank option if it is not multiselect
	 * unless it autocomplete is also enabled
	 *
	 * @since 2.0
	 * @return boolean
	 */
	public static function include_blank_option( $options, $field ) {
		if ( empty( $options ) || $field->type != 'data' ) {
			return false;
		}

		if ( ! isset( $field->field_options['data_type'] ) || $field->field_options['data_type'] != 'select' ) {
			return false;
		}

		return ( ! FrmField::is_multiple_select( $field ) || FrmField::is_option_true( $field, 'autocom' ) );
	}
}
