<?php

/**
 * @since 2.03.05
 */
class FrmProFieldValueSelector extends FrmFieldValueSelector {

	/**
	 * @var FrmProFieldSettings
	 *
	 * @since 2.03.05
	 */
	protected $field_settings = null;

	public function __construct( $field_id, $args ) {
		parent::__construct( $field_id, $args );
	}

	/**
	 * Set the field_settings property
	 *
	 * @since 2.03.05
	 */
	protected function set_field_settings() {
		$this->field_settings = FrmProFieldFactory::create_settings( $this->db_row );
	}

	/**
	 * Set the options property for a pro field
	 *
	 * @since 2.03.05
	 */
	protected function set_options() {
		parent::set_options();

		$post_field = $this->field_settings->get_post_field();
		if ( $post_field === 'post_status' ) {
			$this->options = FrmProFieldsHelper::get_post_status_options( $this->db_row->form_id, $this->options );
		}

		$this->trigger_options_filter();
	}

	/**
	 * Trigger the frm_pro_set_value_selector_options filter to allow modification of options
	 *
	 * @since 2.03.05
	 */
	protected function trigger_options_filter() {
		$field_args = array(
			'field_key' => $this->field_key,
			'field_id' => $this->field_id,
		);

		$this->options = apply_filters( 'frm_pro_value_selector_options', $this->options, $field_args );
	}

	/**
	 * Get an instance of FrmProFieldOption
	 *
	 * @since 2.03.05
	 *
	 * @param string $key
	 * @param string $value
	 *
	 * @return FrmProFieldOption
	 */
	protected function get_single_field_option( $key, $value ) {
		$args = array(
			'use_key' => $this->field_settings->get_use_key(),
			'use_separate_values' => $this->field_settings->get_has_separate_values(),
		);

		return new FrmProFieldOption( $key, $value, $args );
	}

	/**
	 * Display the field value selector
	 *
	 * @since 2.03.05
	 */
	public function display() {
		if ( $this->has_db_row() ) {
			$post_field = $this->field_settings->get_post_field();
		}

		if ( isset( $post_field ) && $post_field === 'post_category' ) {
			$this->display_post_category_value_selector();
		} else {
			parent::display();
		}
	}

	/**
	 * Display a post category value selector
	 *
	 * @since 2.03.05
	 */
	private function display_post_category_value_selector() {
		$is_settings_page = ( FrmAppHelper::simple_get( 'frm_action' ) == 'settings' );
		$first_option = ( $this->blank_option_label === '' ) ? ' ' : $this->blank_option_label;

		$temp_field = FrmField::getOne( $this->field_id );
		$temp_field = FrmProFieldsHelper::convert_field_object_to_flat_array( $temp_field );
		$temp_field['value'] = $this->value;

		$pass_args = array(
			'name' => $this->html_name,
			'id' => 'placeholder_id',
			'show_option_all' => $first_option,
			'location' => $is_settings_page ? 'form_actions' : 'field_logic',
		);

		echo FrmProPost::get_category_dropdown( $temp_field, $pass_args );
	}

}