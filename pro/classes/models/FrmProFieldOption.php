<?php

/**
 * @since 2.03.05
 */
class FrmProFieldOption extends FrmFieldOption {

	/**
	 * @var bool
	 *
	 * @since 2.03.05
	 */
	private $use_key = false;

	/**
	 * @var bool
	 *
	 * @since 2.03.05
	 */
	private $use_separate_values = false;

	/**
	 * FrmProFieldOption constructor.
	 *
	 * @param string|int $option_key
	 * @param string|array $option
	 * @param array $args
	 */
	public function __construct( $option_key, $option, $args ) {
		$this->set_use_key( $args );
		$this->set_use_separate_values( $args );

		parent::__construct( $option_key, $option, $args );
	}

	/**
	 * Set the use_key property
	 *
	 * @since 2.03.05
	 *
	 * @param array $args
	 */
	private function set_use_key( $args ) {
		if ( isset( $args['use_key'] ) ) {
			$this->use_key = (bool) $args['use_key'];
		}
	}

	/**
	 * Set the use_separate_values property
	 *
	 * @since 2.03.05
	 *
	 * @param array $args
	 */
	private function set_use_separate_values( $args ) {
		if ( isset( $args['use_separate_values'] ) ) {
			$this->use_separate_values = (bool) $args['use_separate_values'];
		}
	}

	/**
	 * Set the saved_value property
	 *
	 * @since 2.03.05
	 */
	protected function set_saved_value() {
		if ( $this->use_key ) {
			$this->saved_value = $this->option_key;
		} else if ( is_array( $this->option ) ) {
			if ( $this->use_separate_values && isset( $this->option['value'] ) ) {
				$this->saved_value = $this->option['value'];
			} else {
				$this->saved_value = isset( $this->option['label'] ) ? $this->option['label'] : reset( $this->option );
			}
		} else {
			$this->saved_value = $this->option;
		}
	}
}