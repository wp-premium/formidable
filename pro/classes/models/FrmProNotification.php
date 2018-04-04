<?php
class FrmProNotification {

	public static function add_attachments( $attachments, $form, $args ) {
        $defaults = array(
            'entry'     => false,
            'email_key' => '',
        );
        $args = wp_parse_args( $args, $defaults );

        $file_fields = FrmField::get_all_types_in_form( $form->id, 'file', '', 'include' );

        foreach ( $file_fields as $file_field ) {
            $file_options = $file_field->field_options;

            //Only go through code if file is supposed to be attached to email
            if ( ! isset($file_options['attach']) || ! $file_options['attach'] ) {
                continue;
            }

			$field_value = new FrmProFieldValue( $file_field, $args['entry'] );
			$file_ids = $field_value->get_saved_value();

            //Only proceed if there is actually an uploaded file
            if ( empty($file_ids) ) {
                continue;
            }

            // Get each file in this field
            foreach ( (array) $file_ids as $file_id ) {
                if ( empty($file_id) ) {
                    continue;
                }

				// For multi-file upload fields in repeating sections
				if ( is_array( $file_id ) ) {
					foreach ( $file_id as $f_id ) {
						// Add attachments
						self::add_to_attachments( $attachments, $f_id );
					}
					continue;
				}

				// Add the attachments now
				self::add_to_attachments( $attachments, $file_id );
            }
        }

        return $attachments;
    }

	/**
	* Add to email attachments
	*
	* @since 2.0
	* Called by add_attachments in FrmProNotification
	*/
	private static function add_to_attachments( &$attachments, $file_id ) {
		if ( empty( $file_id ) ) {
			return;
		}
		// Get the file
		$file = get_post_meta( $file_id, '_wp_attached_file', true);
		if ( $file ) {
			$uploads = wp_upload_dir();
			$attachments[] = $uploads['basedir'] . '/' . $file;
		}
	}

	/**
	 * @deprecated 2.03.04
	 */
	public static function entry_created( $entry_id, $form_id ) {
	    $new_function = 'FrmFormActionsController::trigger_actions("create", ' . $form_id . ', ' . $entry_id . ', "email")';
	    _deprecated_function( __FUNCTION__, '2.03.04', $new_function );
	    FrmFormActionsController::trigger_actions( 'create', $form_id, $entry_id, 'email' );
    }
}
