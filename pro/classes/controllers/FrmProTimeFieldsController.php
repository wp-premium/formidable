<?php

/**
 * @since 2.03.08
 */
class FrmProTimeFieldsController {

	/**
	 * Disable used times in a single dropdown Time field when a Date is selected
	 *
	 * @since 2.03.08
	 */
	public static function ajax_time_options() {
		$values = array(
			'time_field' => FrmAppHelper::get_post_param( 'time_field', '', 'sanitize_text_field' ),
			'date_field' => FrmAppHelper::get_post_param( 'date_field', '', 'sanitize_text_field' ),
			'date'       => FrmAppHelper::get_post_param( 'date', '', 'sanitize_text_field' ),
			'entry_id'   => FrmAppHelper::get_post_param( 'entry_id', 0, 'absint' ),
		);
		$values['time_key'] = str_replace( 'field_', '', $values['time_field'] );
		$values['date_key'] = str_replace( 'field_', '', $values['date_field'] );

		$remove = array();

		$field_obj = FrmFieldFactory::get_field_type( 'time', $values );
		$field_obj->get_disallowed_times( $values, $remove );

		foreach ( $remove as $key => $time_to_remove ) {
			$remove[] = FrmProAppHelper::format_time( $time_to_remove, 'g:i A' );
		}

		$remove = array_values( $remove );

		echo json_encode( $remove );

		wp_die();
	}

	/**
	 * Load the unique timepicker JS
	 *
	 * @since 2.03.08
	 *
	 * @param string $datepicker
	 */
	public static function load_timepicker_js( $datepicker ) {
		global $frm_vars;

		if ( ! isset( $frm_vars['timepicker_loaded'] ) || empty( $frm_vars['timepicker_loaded'] ) || ! $datepicker ) {
			return;
		}

		$unique_time_fields = array();
		foreach ( $frm_vars['timepicker_loaded'] as $time_field_id => $options ) {
			if ( ! $options ) {
				continue;
			}

			$unique_time_fields[] = array( 'dateID' => $datepicker, 'timeID' => $time_field_id );
		}

		if ( ! empty( $unique_time_fields ) ) {
			echo '__frmUniqueTimes=' . json_encode( $unique_time_fields ) . ';';
		}
	}
}
