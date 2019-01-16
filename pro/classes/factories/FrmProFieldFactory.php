<?php

/**
 * @since 2.03.05
 */
class FrmProFieldFactory {

	/**
	 * Create an instance of an FrmProFieldValueSelector object
	 *
	 * @since 2.03.05
	 *
	 * @param null $null
	 * @param int $field_id
	 * @param array $selector_args
	 *
	 * @return FrmFieldValueSelector|mixed|void
	 */
	public static function create_field_value_selector( $null, $field_id, $selector_args ) {
		$type = FrmField::get_type( $field_id );

		switch ( $type ) {
			case 'data':
				$selector = new FrmProFieldDynamicValueSelector( $field_id, $selector_args );
				break;
			case 'user_id':
				$selector = new FrmProFieldUserIDValueSelector( $field_id, $selector_args );
				break;
			default:
				$selector = new FrmProFieldValueSelector( $field_id, $selector_args );
		}

		return $selector;
	}

	/**
	 * Retrieves a pro field settings object, depending on the field type
	 *
	 * @since 2.03.05
	 *
	 * @return FrmProFieldSettings
	 */
	public static function create_settings( $db_row ) {
		$type = $db_row->type;
		$field_options = maybe_unserialize( $db_row->field_options );

		switch ( $type ) {
			case 'text':
				$settings = new FrmProFieldTextSettings( $field_options );
				break;
			case 'hidden':
				$settings = new FrmProFieldHiddenSettings( $field_options );
				break;
			case 'data':
				$settings = new FrmProFieldDynamicSettings( $field_options );
				break;
			default:
				$settings = new FrmProFieldSettings( $field_options );
		}

		return $settings;
	}

	/**
	 * @since 3.0
	 */
	public static function get_field_type_class( $class, $field_type ) {
		if ( empty( $class ) ) {
			$type_classes = array(
				'address'  => 'FrmProFieldAddress',
				'break'    => 'FrmProFieldBreak',
				'credit_card' => 'FrmProFieldCreditCard',
				'data'     => 'FrmProFieldData',
				'date'     => 'FrmProFieldDate',
				'divider'  => 'FrmProFieldDivider',
				'end_divider' => 'FrmProFieldEndDivider',
				'file'     => 'FrmProFieldFile',
				'form'     => 'FrmProFieldForm',
				'lookup'   => 'FrmProFieldLookup',
				'password' => 'FrmProFieldPassword',
				'range'    => 'FrmProFieldRange',
				'rte'      => 'FrmProFieldRte',
				'scale'    => 'FrmProFieldScale',
				'star'     => 'FrmProFieldStar',
				'tag'      => 'FrmProFieldTag',
				'time'     => 'FrmProFieldTime',
				'toggle'   => 'FrmProFieldToggle',
			);

			$class = isset( $type_classes[ $field_type ] ) ? $type_classes[ $field_type ] : '';
		} elseif ( strpos( $class, 'FrmField' ) === 0 ) {
			$new_class = str_replace( 'FrmField', 'FrmProField', $class );
			if ( class_exists( $new_class ) ) {
				$class = $new_class;
			}
		}

		return $class;
	}
}
