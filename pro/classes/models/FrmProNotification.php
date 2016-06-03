<?php
class FrmProNotification{

    public static function add_attachments($attachments, $form, $args) {
        $defaults = array(
            'entry'     => false,
            'email_key' => '',
        );
        $args = wp_parse_args($args, $defaults);
        $entry = $args['entry'];

        // Used for getting the file ids for sub entries
        $atts = array(
            'entry'         => $entry,  'default_email' => false,
            'include_blank' => false,   'id'            => $entry->id,
            'plain_text'    => true,    'format'        => 'array',
            'filter'        => false,
        );

        $file_fields = FrmField::get_all_types_in_form($form->id, 'file', '', 'include');

        foreach ( $file_fields as $file_field ) {
            $file_options = $file_field->field_options;

            //Only go through code if file is supposed to be attached to email
            if ( ! isset($file_options['attach']) || ! $file_options['attach'] ) {
                continue;
            }

            $file_ids = array();
            //Get attachment ID for uploaded files
            if ( isset($entry->metas[$file_field->id]) ) {
                $file_ids = $entry->metas[$file_field->id];
            } else if ( $file_field->form_id != $form->id ) {
                // this is in a repeating or embedded field
                $values = array();

                FrmEntryFormat::fill_entry_values( $atts, $file_field, $values );
                if ( isset($values[$file_field->field_key]) ) {
                    $file_ids = $values[$file_field->field_key];
                }
            } else if ( isset($file_field->field_options['post_field']) && !empty($file_field->field_options['post_field']) ) {
                //get value from linked post
                $file_ids = FrmProEntryMetaHelper::get_post_or_meta_value( $entry, $file_field );
            }

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
	* Since 2.0
	* Called by add_attachments in FrmProNotification
	*/
	private static function add_to_attachments( &$attachments, $file_id ) {
		if ( empty( $file_id ) ) {
			return;
		}
		// Get the file
		$file = get_post_meta( $file_id, '_wp_attached_file', true);
		if ( $file ) {
			if ( ! isset( $uploads ) || ! isset( $uploads['basedir'] ) ) {
				$uploads = wp_upload_dir();
			}
			$attachments[] = $uploads['basedir'] . '/'. $file;
		}
	}

    public static function entry_created($entry_id, $form_id) {
        FrmNotification::entry_created($entry_id, $form_id);
    }
}
