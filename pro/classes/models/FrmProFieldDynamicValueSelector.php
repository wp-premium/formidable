<?php

/**
 * @since 2.03.05
 */
class FrmProFieldDynamicValueSelector extends FrmProFieldValueSelector {

	/**
	 * @var FrmProFieldDynamicSettings
	 * @since 2.03.05
	 */
	protected $field_settings = null;

	public function __construct( $field_id, $args ) {
		parent::__construct( $field_id, $args );

		$this->set_blank_option_label();
	}

	/**
	 * Set the options property
	 *
	 * @since 2.03.05
	 */
	protected function set_options() {
		if ( $this->field_settings->get_linked_field_id() > 0 ) {
			$where = array( 'it.field_id' => $this->field_settings->get_linked_field_id() );
			$linked_entries = FrmEntryMeta::getAll( $where, '', ' LIMIT 300', true );

			if ( ! empty( $linked_entries ) ) {
				foreach ( $linked_entries as $entry ) {
					$this->options[ $entry->item_id ] = $entry->meta_value;
				}
			}
		}

		$this->trigger_options_filter();
	}

	/**
	 * Set the blank_option_label property
	 *
	 * @since 2.03.05
	 */
	private function set_blank_option_label() {
		$this->blank_option_label = $this->source === 'data' ? __( 'Anything', 'formidable' ) : '';
	}
}