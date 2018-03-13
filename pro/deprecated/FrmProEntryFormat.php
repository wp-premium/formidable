<?php

class FrmProEntryFormat {

	/***********************************************************************
	 * Deprecated Functions
	 ************************************************************************/

	/**
	 * @deprecated 2.04
	 */
	public static function default_email_shortcodes( $row ) {
		_deprecated_function( __FUNCTION__, '2.04', 'custom code' );

		return $row;
	}

	/**
	 * @deprecated 2.04
	 */
	public static function prepare_entry_content( $entry, $atts ) {
		_deprecated_function( __FUNCTION__, '2.04', 'custom code' );

		$atts['include_blank'] = true;
		FrmProEntryMeta::add_post_value_to_entry( $atts['field'], $entry );
		self::add_sub_array_to_entry( $atts['field'], $entry, $atts );
		return $entry;
	}

	/**
	 * Add each linked entry as an array
	 *
	 * @deprecated 2.04
	 */
	public static function add_sub_array_to_entry( $field, &$entry, $atts = array() ) {
		_deprecated_function( __FUNCTION__, '2.04', 'custom code' );

		if ( $entry->form_id != $field->form_id ) {
			if ( ! isset( $entry->sub_entries ) ) {
				$entry->sub_entries = array();
			}
			$section_id = $field->field_options['in_section'];
			if ( $section_id && isset( $entry->metas[ $section_id ] ) ) {
				$sub_entry_ids = $entry->metas[ $section_id ];
				$child_entries = FrmEntry::getAll( array( 'parent_item_id' => $entry->id, 'id' => $sub_entry_ids ), '', '', true, false );
			} else {
				// get entry ids linked through repeat field or embeded form
				$child_entries = FrmProEntry::get_sub_entries( $entry->id, true );
			}

			foreach ( $child_entries as $child_entry ) {
				if ( ! isset( $entry->sub_entries[ $child_entry->id ] ) ) {
					$entry->sub_entries[ $child_entry->id ] = array();
				}
				$entry->sub_entries[ $child_entry->id ][ $field->id ] = FrmProEntryMetaHelper::get_post_or_meta_value( $child_entry, $field, $atts );
				$entry->sub_entries[ $child_entry->id ]['section_id'] = $section_id;
			}
		} else {
			// get values linked through a dynamic field
			$val = '';
			FrmProEntriesHelper::get_dynamic_list_values( $field, $entry, $val );
			$entry->metas[ $field->id ] = $val;
		}
	}

	/**
	 * Used for the frm-show-entry shortcode and default emails
	 * @since 2.03
	 *
	 * @deprecated 2.04
	 */
	public static function prepare_entry_array( $values, $atts ) {
		_deprecated_function( __FUNCTION__, '2.04', 'instance of FrmEntryValues or FrmProEntryValues' );

		$field = $atts['field'];
		$in_child_form = $field->form_id != $atts['form_id'];

		if ( isset( $atts['entry']->sub_entries ) && $in_child_form ) {
			if ( ! isset( $values[ $field->field_options['in_section'] ] ) ) {
				$values[ $field->field_options['in_section'] ] = array( 'label' => '', 'val' => '', 'type' => 'divider' );
			}

			foreach ( $atts['entry']->sub_entries as $sub_id => $sub_entry ) {
				$is_blank = ( ! $atts['include_blank'] && isset( $sub_entry[ $field->id ] ) && $sub_entry[ $field->id ] == '' );
				$entry_in_section = $sub_entry['section_id'] == $field->field_options['in_section'];
				if ( $is_blank || ! $entry_in_section ) {
					continue;
				}
				if ( ! isset( $values[ $field->field_options['in_section'] ]['entries'][ $sub_id ] ) ) {
					$values[ $field->field_options['in_section'] ]['entries'][ $sub_id ] = array();
				}

				$val = $sub_entry[ $field->id ];
				self::get_field_value( $atts, $val );

				$values[ $field->field_options['in_section'] ]['entries'][ $sub_id ][ $field->id ] = array(
					'label' => $field->name,
					'val'   => $val,
					'type'  => $field->type,
				);
			}
		}

		return $values;
	}

	/**
	 * @deprecated 2.04
	 */
	private static function get_field_value( $atts, &$val ) {
		_deprecated_function( __FUNCTION__, '2.04', 'instance of FrmEntryValues or FrmProEntryValues' );

		$field = $atts['field'];
		if ( $atts['entry'] ) {
			$meta = array(
				'item_id' => $atts['id'], 'field_id' => $field->id,
				'meta_value' => $val, 'field_type' => $field->type,
			);

			$filter_value = ( ! isset( $atts['filter'] ) || $atts['filter'] !== false );
			if ( $filter_value ) {
				$val = apply_filters( 'frm_email_value', $val, (object) $meta, $atts['entry'], compact( 'field' ) );
			}

			FrmEntryFormat::prepare_field_output( $atts, $val );
		}
	}

	/**
	 * @deprecated 2.04
	 */
	public static function single_plain_text_row( $row ) {
		_deprecated_function( __FUNCTION__, '2.04', 'custom code' );

		return $row;
	}

	/**
	 * @deprecated 2.04
	 */
	public static function single_html_row( $row ) {
		_deprecated_function( __FUNCTION__, '2.04', 'custom code' );

		return $row;
	}

}
