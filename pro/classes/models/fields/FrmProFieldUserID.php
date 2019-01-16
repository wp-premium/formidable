<?php

/**
 * @since 3.0
 */
class FrmProFieldUserID extends FrmFieldUserID {

	protected function field_settings_for_type() {
		$settings = parent::field_settings_for_type();

		$settings['autopopulate'] = true;
		$settings['visibility'] = false;
		$settings['default_value'] = true;
		$settings['logic'] = false;

		FrmProFieldsHelper::fill_default_field_display( $settings );
		return $settings;
	}

	public function prepare_front_field( $values, $atts ) {
		$show_admin_field = FrmAppHelper::is_admin() && current_user_can('frm_edit_entries') && ! FrmAppHelper::is_admin_page('formidable' );
		if ( $show_admin_field && FrmProFieldsHelper::field_on_current_page( $this->field ) ) {
			$values['type'] = 'select';
			$values['options'] = $this->get_options( $values );
			$values['use_key'] = true;
			$values['custom_html'] = FrmFieldsHelper::get_default_html('select');
		}
		return $values;
	}

	public function get_options( $values ) {
		$users = get_users( array(
			'fields' => array( 'ID', 'user_login', 'display_name' ),
			'blog_id' => $GLOBALS['blog_id'],
			'orderby' => 'display_name',
		) );

		$options = array( '' => '' );
		foreach ( $users as $user ) {
			$options[ $user->ID ] = ( ! empty( $user->display_name ) ? $user->display_name : $user->user_login );
		}
		return $options;
	}
}
