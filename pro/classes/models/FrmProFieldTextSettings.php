<?php

/**
 * @since 2.03.05
 */
class FrmProFieldTextSettings extends FrmProFieldSettings {

	public function __construct( array $field_options ) {
		parent::__construct( $field_options );
	}

	/**
	 * Set the use_key property for a hidden field
	 *
	 * @since 2.03.05
	 */
	protected function set_use_key() {
		$this->use_key = false;
	}

}