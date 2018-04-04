<?php

/**
 * @since 2.03.05
 */
class FrmProFieldDynamicSettings extends FrmProFieldSettings {

	/**
	 * @var int
	 * @since 2.03.05
	 */
	private $linked_field_id = 0;

	public function __construct( array $field_options ) {
		parent::__construct( $field_options );

		$this->set_linked_field_id();
	}

	/**
	 * Set the use_key property
	 *
	 * @since 2.03.05
	 */
	protected function set_use_key() {
		$this->use_key = true;
	}

	/**
	 * Set the linked_field_id property
	 *
	 * @since 2.03.05
	 */
	private function set_linked_field_id() {
		if ( isset( $this->field_options['form_select'] ) && is_numeric( $this->field_options['form_select'] ) ) {
			$this->linked_field_id = (int) $this->field_options['form_select'];
		}
	}

	/**
	 * Get the linked_field_id property
	 *
	 * @since 2.03.05
	 *
	 * @return int
	 */
	public function get_linked_field_id() {
		return $this->linked_field_id;
	}
}
