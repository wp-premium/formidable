<?php

/**
 * @since 2.03.05
 */
class FrmProFieldSettings {

	/**
	 * @since 2.03.05
	 * @var string
	 */
	protected $post_field = '';

	/**
	 * @since 2.03.05
	 * @var bool
	 */
	protected $has_separate_values = false;

	/**
	 * @since 2.03.05
	 * @var bool
	 */
	protected $use_key = false;

	/**
	 * @var array
	 * @since 2.03.05
	 */
	protected $field_options = array();

	/**
	 * FrmProFieldSettings constructor.
	 *
	 * @param array $field_options
	 */
	public function __construct( $field_options ) {
		$this->field_options = $field_options;

		if ( ! $this->has_field_options() ) {
			return;
		}

		$this->set_post_field();
		$this->set_has_separate_values();
		$this->set_use_key();
	}

	/**
	 * Set the post_field property
	 *
	 * @since 2.03.05
	 */
	private function set_post_field() {
		if ( isset( $this->field_options['post_field'] ) && $this->field_options['post_field'] ) {
			$this->post_field = $this->field_options['post_field'];
		}
	}

	/**
	 * @since 2.03.05
	 * @return string
	 */
	public function get_post_field() {
		return $this->post_field;
	}

	/**
	 * Set the has_separate_values property
	 *
	 * @since 2.03.05
	 */
	private function set_has_separate_values() {
		if ( isset( $this->field_options['separate_value'] ) && $this->field_options['separate_value'] ) {
			$this->has_separate_values = true;
		}
	}

	/**
	 * Get the has_separate_values property
	 *
	 * @since 2.03.05
	 *
	 * @return bool
	 */
	public function get_has_separate_values() {
		return $this->has_separate_values;
	}

	/**
	 * Set the use_key property
	 *
	 * @since 2.03.05
	 */
	protected function set_use_key() {
		if ( isset( $this->field_options['use_key'] ) && $this->field_options['use_key'] ) {
			$this->use_key = true;
		} elseif ( $this->post_field === 'post_category' ) {
			$this->use_key = true;
		} elseif ( $this->post_field === 'post_status' ) {
			$this->use_key = true;
		}
	}

	/**
	 * Get the use_key property
	 *
	 * @since 2.03.05
	 *
	 * @return bool
	 */
	public function get_use_key() {
		return $this->use_key;
	}

	/*
	 * Check if a field has any field_options from database
	 *
	 * @since 2.03.05
	 *
	 * @return bool
	 */
	final private function has_field_options() {
		return ! empty( $this->field_options );
	}

}
