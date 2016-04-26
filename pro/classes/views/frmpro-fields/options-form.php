<?php
if ( $display['type'] == 'radio' || $display['type'] == 'checkbox' || ( $display['type'] == 'data' && in_array( $display['field_data']['data_type'], array( 'radio', 'checkbox' ) ) ) ) {
	include( FrmAppHelper::plugin_path() . '/pro/classes/views/frmpro-fields/back-end/alignment.php' );
}

if ( in_array( $display['type'], array( 'radio', 'checkbox', 'select' ) ) && ( ! isset( $field['post_field'] ) || ( $field['post_field'] != 'post_category' ) ) ) {
    include( FrmAppHelper::plugin_path() . '/pro/classes/views/frmpro-fields/back-end/separate-values.php' );
}

if ( $field['type'] == 'data' ) {
    include( FrmAppHelper::plugin_path() . '/pro/classes/views/frmpro-fields/back-end/dynamic-field.php' );
}

if ( $display['type'] == 'select' || $field['type'] == 'data' ) {
    include( FrmAppHelper::plugin_path() . '/pro/classes/views/frmpro-fields/back-end/multi-select.php' );

} else if ( $field['type'] == 'divider' ) {
    include( FrmAppHelper::plugin_path() . '/pro/classes/views/frmpro-fields/back-end/repeat-options.php' );

} else if ( $field['type'] == 'end_divider' ) {
    include( FrmAppHelper::plugin_path() . '/pro/classes/views/frmpro-fields/back-end/repeat-buttons.php' );

} else if ( $field['type'] == 'date' ) {
    include( FrmAppHelper::plugin_path() . '/pro/classes/views/frmpro-fields/back-end/calendar.php' );

} else if ( $field['type'] == 'time' ) {
    include( FrmAppHelper::plugin_path() . '/pro/classes/views/frmpro-fields/back-end/clock-settings.php' );

} else if ( $field['type'] == 'file' ) {
    include( FrmAppHelper::plugin_path() . '/pro/classes/views/frmpro-fields/back-end/file-options.php' );

} else if ( $field['type'] == 'number' && $frm_settings->use_html ) {
    include( FrmAppHelper::plugin_path() . '/pro/classes/views/frmpro-fields/back-end/number-range.php' );

} else if ( $field['type'] == 'scale' ) {
    include( FrmAppHelper::plugin_path() . '/pro/classes/views/frmpro-fields/back-end/scale-options.php' );

} else if ( $field['type'] == 'html' ) {
    include( FrmAppHelper::plugin_path() . '/pro/classes/views/frmpro-fields/back-end/html-content.php' );

} else if ( $field['type'] == 'form' ) {
    include( FrmAppHelper::plugin_path() . '/pro/classes/views/frmpro-fields/back-end/insert-form.php' );

} else if ( $field['type'] == 'phone' ) {
    include( FrmAppHelper::plugin_path() . '/pro/classes/views/frmpro-fields/back-end/phone-format.php' );
}

if ( $display['visibility'] ) {
    include( FrmAppHelper::plugin_path() . '/pro/classes/views/frmpro-fields/back-end/visibility.php' );
}

if ( $display['conf_field'] ) {
    include( FrmAppHelper::plugin_path() . '/pro/classes/views/frmpro-fields/back-end/confirmation.php' );
}

if ( $display['logic'] ) {
    include( FrmAppHelper::plugin_path() . '/pro/classes/views/frmpro-fields/back-end/logic.php' );
}

if ( $display['default_value'] || $display['calc'] || $display['autopopulate'] ) {
    include( FrmAppHelper::plugin_path() . '/pro/classes/views/frmpro-fields/back-end/dynamic-values.php' );
}