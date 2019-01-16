<?php

/**
 * @since 3.0
 */
class FrmProFieldText extends FrmFieldText {

	protected function field_settings_for_type() {
		$settings = parent::field_settings_for_type();

		$settings['autopopulate'] = true;
		$settings['calc'] = true;
		$settings['read_only'] = true;
		$settings['unique'] = true;

		FrmProFieldsHelper::fill_default_field_display( $settings );
		return $settings;
	}
}
