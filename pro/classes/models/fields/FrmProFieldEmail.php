<?php

/**
 * @since 3.0
 */
class FrmProFieldEmail extends FrmFieldEmail {

	protected function field_settings_for_type() {
		$settings = parent::field_settings_for_type();

		$settings['autopopulate'] = true;
		$settings['conf_field'] = true;
		$settings['unique'] = true;
		$settings['read_only'] = true;

		FrmProFieldsHelper::fill_default_field_display( $settings );
		return $settings;
	}
}
