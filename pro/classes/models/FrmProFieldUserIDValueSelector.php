<?php

/**
 * @since 2.03.05
 */
class FrmProFieldUserIDValueSelector extends FrmProFieldValueSelector {

	public function __construct( $field_id, $args ) {
		parent::__construct( $field_id, $args );
	}

	/**
	 * Set the options property
	 *
	 * @since 2.03.05
	 */
	protected function set_options() {
		$this->options = FrmProFieldsHelper::get_user_options();

		$this->trigger_options_filter();
	}

	/**
	 * Display the field value selector
	 *
	 * @since 2.03.05
	 */
	public function display() {
		echo '<select name="' . esc_attr( $this->html_name ) . '">';
		echo '<option value=""></option>';
		echo '<option value="current_user" ' . selected( $this->value, 'current_user', false ) . '>';
		echo __( 'Current User', 'formidable' );
		echo '</option>';

		if ( $this->has_options() ) {
			foreach ( $this->options as $user_id => $user_login ) {
				if ( empty( $user_id ) ) {
					continue;
				}

				echo '<option value="' . esc_attr( $user_id ) . '" ' . selected( $this->value, $user_id, false ) . '>';
				echo esc_html( $user_login );
				echo '</option>';

			}
		}

		echo '</select>';
	}

}