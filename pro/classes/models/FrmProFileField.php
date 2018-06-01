<?php

class FrmProFileField {

	/**
	 * @param array $field (no array for field options)
	 * @param array $atts
	 */
	public static function setup_dropzone( $field, $atts ) {
		global $frm_vars;

		$is_multiple = FrmField::is_option_true( $field, 'multiple' );

		if ( ! isset( $frm_vars['dropzone_loaded'] ) || ! is_array( $frm_vars['dropzone_loaded'] ) ) {
			$frm_vars['dropzone_loaded'] = array();
		}

		$the_id = $atts['file_name'];
		if ( ! isset( $frm_vars['dropzone_loaded'][ $the_id ] ) ) {
			if ( $is_multiple ) {
				$max = empty( $field['max'] ) ? 99 : absint( $field['max'] );
			} else {
				$max = 1;
			}

			$file_size = self::get_max_file_size( $field['size'] );

			$frm_vars['dropzone_loaded'][ $the_id ] = array(
				'maxFilesize' => round( $file_size, 2 ),
				'maxFiles'    => $max,
				//'acceptedFiles' => '', //cover this in the php to minimize differences in mime types
				'htmlID'      => $the_id,
				'uploadMultiple' => $is_multiple,
				'fieldID'     => $field['id'],
				'formID'            => $field['form_id'],
				'parentFormID'      => isset( $field['parent_form_id'] ) ? $field['parent_form_id'] : $field['form_id'],
				'fieldName'   => $atts['field_name'],
				'mockFiles'   => array(),
				'defaultMessage' => __( 'Drop files here to upload', 'formidable-pro' ),
				'fallbackMessage' => __( 'Your browser does not support drag and drop file uploads.', 'formidable-pro' ),
				'fallbackText' => __( 'Please use the fallback form below to upload your files like in the olden days.', 'formidable-pro' ),
				'fileTooBig' => sprintf( __( 'That file is too big. It must be less than %sMB.', 'formidable-pro' ), '{{maxFilesize}}' ),
				'invalidFileType'  => self::get_invalid_file_type_message( $field['name'], $field['invalid'] ),
				'responseError'    => sprintf( __( 'Server responded with %s code.', 'formidable-pro' ), '{{statusCode}}' ),
				'cancel'           => __( 'Cancel upload', 'formidable-pro' ),
				'cancelConfirm'    => __( 'Are you sure you want to cancel this upload?', 'formidable-pro' ),
				'remove'           => __( 'Remove file', 'formidable-pro' ),
				'maxFilesExceeded' => sprintf( __( 'You have uploaded too many files. You may only include %d file(s).', 'formidable-pro' ), $max ),
				'resizeHeight'     => null,
				'resizeWidth'      => null,
				'timeout'          => self::get_timeout(),
			);

			if ( $field['resize'] && ! empty( $field['new_size'] ) ) {
				$setting_name = 'resize' . ucfirst( $field['resize_dir'] );
				$frm_vars['dropzone_loaded'][ $the_id ][ $setting_name ] = $field['new_size'];
			}

			if ( strpos( $the_id, '-i' ) ) {
				// we are editing, so get the base settings added too
				$id_parts = explode( '-i', $the_id );
				$base_id = $id_parts[0] . '-0';
				$base_settings = $frm_vars['dropzone_loaded'][ $the_id ];
				if ( ! isset( $frm_vars['dropzone_loaded'][ $base_id ] ) && strpos( $base_settings['fieldName'], '[i' . $id_parts[1] . ']' ) ) {
					$base_settings['htmlID'] = $base_id;
					$base_settings['fieldName'] = str_replace( '[i' . $id_parts[1] . ']', '[0]', $base_settings['fieldName'] );
					$frm_vars['dropzone_loaded'][ $base_id ] = $base_settings;
				}
			}

			self::add_mock_files( $field['value'], $frm_vars['dropzone_loaded'][ $the_id ]['mockFiles'] );
		}
	}

	/**
	 * Increase the default timeout from 30 based on server limits
	 *
	 * @since 3.01.02
	 */
	private static function get_timeout() {
		$timeout = absint( ini_get( 'max_execution_time' ) );
		if ( $timeout <= 1 ) {
			// allow for -1 or 0 for unlimited
			$timeout = 5000 * 1000;
		} elseif ( $timeout > 30 ) {
			$timeout = $timeout * 1000;
		} else {
			$timeout = 30000;
		}
		return $timeout;
	}

	private static function add_mock_files( $media_ids, &$mock_files ) {
		$media_ids = maybe_unserialize( $media_ids );
		if ( ! empty( $media_ids ) ) {
			foreach ( (array) $media_ids as $media_id ) {
				$file = self::get_mock_file( $media_id );
				if ( ! empty( $file ) ) {
					$mock_files[] = $file;
				}
			}
		}
	}

	private static function get_mock_file( $media_id ) {
		$file = array();
		$image = get_attached_file( $media_id );
		if ( file_exists( $image ) ) {
			$file_url = wp_get_attachment_url( $media_id );
			$url = wp_get_attachment_thumb_url( $media_id );
			if ( ! $url ) {
				$url = wp_get_attachment_image_src( $media_id, 'thumbnail', true );
				if ( $url ) {
					$url = reset( $url );
				}
			}
			$label = basename( $image );
			$size = filesize( $image );

			$file = array(
				'name' => $label, 'size' => $size,
				'url' => $url, 'id' => $media_id,
				'file_url' => $file_url,
			);
		}

		return $file;
	}

	/**
	 * Always hide the temp files from queries.
	 * Hide all unattached form uploads from those without permission.
	 *
	 * @param WP_Query $query
	 */
	public static function filter_media_library( $query ) {
		if ( 'attachment' == $query->get('post_type') ) {
			if ( current_user_can('frm_edit_entries') ) {
				$show = FrmAppHelper::get_param( 'frm-attachment-filter', '', 'get', 'absint' );
			} else {
				$show = false;
			}

			$meta_query = $query->get('meta_query');
			if ( ! is_array( $meta_query ) ) {
				$meta_query = array();
			}

			$meta_query[] = array(
				'key'     => '_frm_temporary',
				'compare' => 'NOT EXISTS',
			);

			$meta_query[] = array(
				'key'     => '_frm_file',
				'compare' => $show ? 'EXISTS' : 'NOT EXISTS',
			);

			$query->set( 'meta_query', $meta_query );
		}
	}

	/**
	 * Validate a file upload field if file was not uploaded with Ajax
	 *
	 * @since 2.03.08
	 *
	 * @param array $errors
	 * @param stdClass $field
	 * @param array $values
	 * @param array $args
	 *
	 * @return array
	 */
	public static function no_js_validate( $errors, $field, $values, $args ) {
		$field->temp_id = $args['id'];

		$args['file_name'] = self::get_file_name( $field, $args );

		if ( isset( $_FILES[ $args['file_name'] ] ) ) {
			self::validate_file_upload( $errors, $field, $args, $values );
			self::add_file_fields_to_global_variable( $field, $args );
		}

		return $errors;
	}

	/**
	 * @since 3.0
	 *
	 * @param object $field
	 * @param array $args
	 */
	private static function get_file_name( $field, $args ) {
		$file_name = 'file' . $field->id;
		if ( isset( $args['key_pointer'] ) && ( $args['key_pointer'] || $args['key_pointer'] === 0 ) ) {
			$file_name .= '-' . $args['key_pointer'];
		}
		return $file_name;
	}

	/**
	 * Add file upload field information to global variable
	 *
	 * @since 2.03.08
	 *
	 * @param stdClass $field
	 * @param array $args
	 */
	private static function add_file_fields_to_global_variable( $field, $args ) {
		global $frm_vars;
		if ( ! isset( $frm_vars['file_fields'] ) ) {
			$frm_vars['file_fields'] = array();
		}

		$frm_vars['file_fields'][ $field->temp_id ]               = $args;
		$frm_vars['file_fields'][ $field->temp_id ]['field_id'] = $field->id;
	}

	/**
	 * Upload files the uploaded files when no JS on page
	 *
	 * @since 2.03.08
	 *
	 * @param array $errors
	 *
	 * @return array
	 */
	public static function upload_files_no_js( $errors ) {
		if ( ! empty( $errors ) ) {
			return $errors;
		}

		global $frm_vars;

		if ( isset( $frm_vars['file_fields'] ) ) {
			foreach ( $frm_vars['file_fields'] as $unique_file_id => $file_args ) {

				if ( isset( $_FILES[ $file_args['file_name'] ] ) ) {
					$file_field = FrmField::getOne( $file_args['field_id'] );
					self::maybe_upload_temp_file( $errors, $file_field, $file_args );
				}
			}
		}

		return $errors;
	}

	/**
	 * If blank errors are set, remove them if a file was uploaded in the field.
	 * It still needs some checks in case there are multiple file fields
	 *
	 * @since 3.0.03
	 */
	public static function remove_error_message( $errors, $field, $value, $args ) {
		if ( ! isset( $errors[ 'field' . $field->temp_id ] ) || $errors[ 'field' . $field->temp_id ] != FrmFieldsHelper::get_error_msg( $field, 'blank' ) ) {
			return $errors;
		}

		$file_name = self::get_file_name( $field, $args );
		$file_uploads = $_FILES[ $file_name ];
		if ( self::file_was_selected( $file_uploads ) ) {
			unset( $errors[ 'field' . $field->temp_id ] );
		}

		return $errors;
	}

	public static function validate_file_upload( &$errors, $field, $args, $values = array() ) {
		$file_uploads = $_FILES[ $args['file_name'] ];

		if ( self::file_was_selected( $file_uploads ) ) {

			add_filter( 'frm_validate_file_field_entry', 'FrmProFileField::remove_error_message', 10, 4 );

			self::validate_file_size( $errors, $field, $args );
			self::validate_file_count( $errors, $field, $args, $values );
			self::validate_file_type( $errors, $field, $args );
			$errors = apply_filters( 'frm_validate_file', $errors, $field, $args );

		} elseif ( empty( $values ) ) {
			$skip_required = FrmProEntryMeta::skip_required_validation( $field );
			if ( $field->required && ! $skip_required ) {
				$errors[ 'field' . $field->temp_id ] = FrmFieldsHelper::get_error_msg( $field, 'blank' );
			}
		}
	}

	private static function file_was_selected( $file_uploads ) {
		//if the field is a file upload, check for a file
		if ( empty( $file_uploads['name'] ) ) {
			return false;
		}

		$filled = true;
		if ( is_array( $file_uploads['name'] ) ) {
			$filled = false;
			foreach ( $file_uploads['name'] as $n ) {
				if ( ! empty( $n ) ) {
					$filled = true;
				}
			}
		}
		return $filled;
	}

	/**
	 * @since 2.02
	 */
	public static function validate_file_size( &$errors, $field, $args ) {
		$mb_limit = FrmField::get_option( $field, 'size' );
		$size_limit = self::get_max_file_size( $mb_limit );
		$file_uploads = (array) $_FILES[ $args['file_name'] ];

		foreach ( (array) $file_uploads['name'] as $k => $name ) {

			// check allowed file size
			if ( ! empty( $file_uploads['error'] ) && in_array( 1, (array) $file_uploads['error'] ) ) {
				$errors[ 'field' . $field->temp_id ] = __( 'That file is too big. It must be less than %sMB.', 'formidable-pro' );
			}

			if ( empty( $name ) ) {
				continue;
			}

			$this_file_size = is_array( $file_uploads['size'] ) ? $file_uploads['size'][ $k ] : $file_uploads['size'];
			$this_file_size = $this_file_size / 1000000; // compare in MB

			if ( $this_file_size > $size_limit ) {
				$errors[ 'field' . $field->temp_id ] = sprintf( __( 'That file is too big. It must be less than %sMB.', 'formidable-pro' ), $size_limit );
			}

			unset( $name );
		}
	}

	public static function get_max_file_size( $mb_limit = 256 ) {
		if ( empty( $mb_limit ) || ! is_numeric( $mb_limit ) ) {
			$mb_limit = 516;
		}
		$mb_limit = (float) $mb_limit;

		$upload_max = wp_max_upload_size() / 1000000;

		return round( min( $upload_max, $mb_limit ), 3 );
	}

	/**
	 * @since 2.02
	 */
	private static function validate_file_count( &$errors, $field, $args, $values ) {
		$multiple_files_allowed = FrmField::get_option( $field, 'multiple' );
		$file_count_limit = (int) FrmField::get_option( $field, 'max' );
		if ( ! $multiple_files_allowed || empty( $file_count_limit ) ) {
			return;
		}

		$total_upload_count = self::get_new_and_old_file_count( $field, $args, $values );
		if ( $total_upload_count > $file_count_limit ) {
			$errors[ 'field' . $field->temp_id ] = sprintf( __( 'You have uploaded too many files. You may only include %d file(s).', 'formidable-pro' ), $file_count_limit );
		}
	}

	/**
	 * Count the number of new files uploaded
	 * along with any previously uploaded files
	 *
	 * @since 2.02
	 */
	private static function get_new_and_old_file_count( $field, $args, $values ) {
		$file_uploads = (array) $_FILES[ $args['file_name'] ];
		$uploaded_count = count( array_filter( $file_uploads['tmp_name'] ) );

		$previous_uploads = (array) self::get_file_posted_vals( $field->id, $args );
		$previous_upload_count = count( array_filter( $previous_uploads ) );

		$total_upload_count = $uploaded_count + $previous_upload_count;
		return $total_upload_count;
	}

	/**
	 * @since 2.02
	 */
	public static function validate_file_type( &$errors, $field, $args ) {
		if ( isset( $errors[ 'field' . $field->temp_id ] ) ) {
			return;
		}

		$mimes = self::get_allowed_mimes( $field );

		$file_uploads = $_FILES[ $args['file_name'] ];
		foreach ( (array) $file_uploads['name'] as $name ) {
			if ( empty( $name ) ) {
				continue;
			}

			//check allowed mime types for this field
			$file_type = wp_check_filetype( $name, $mimes );
			unset($name);

			if ( ! $file_type['ext'] ) {
				break;
			}
		}

        if ( isset( $file_type ) && ! $file_type['ext'] ) {
			$errors[ 'field' . $field->temp_id ] = self::get_invalid_file_type_message( $field->name, $field->field_options['invalid'] );
        }
	}

	private static function get_allowed_mimes( $field ) {
		$mimes = FrmField::get_option( $field, 'ftypes' );
		$restrict = FrmField::is_option_true( $field, 'restrict' ) && ! empty( $mimes );
		if ( ! $restrict ) {
			$mimes = null;
		}
		return $mimes;
	}

	/**
	 * @param string $field_name
	 * @param string $field_invalid_msg
	 * @return string
	 */
	private static function get_invalid_file_type_message( $field_name, $field_invalid_msg ) {
		$default_invalid_messages = array( '' );
		$default_invalid_messages[] = __( 'This field is invalid', 'formidable-pro' );
		$default_invalid_messages[] = $field_name . ' ' . __( 'is invalid', 'formidable-pro' );
		$is_default_message = in_array( $field_invalid_msg, $default_invalid_messages );

		$invalid_type = __( 'Sorry, this file type is not permitted.', 'formidable-pro' );
		$invalid_message = $is_default_message ? $invalid_type : $field_invalid_msg;

		return $invalid_message;
	}

    /**
     * Upload new files, delete removed files
     *
     * @since 2.0
     * @param array|string $meta_value (the posted value)
     * @param int $field_id
     * @param int $entry_id
     * @return array|string $meta_value
     *
     */
	public static function prepare_data_before_db( $meta_value, $field_id, $entry_id, $atts ) {
		_deprecated_function( __FUNCTION__, '3.0', 'FrmFieldType::get_value_to_save' );
		$atts['field_id'] = $field_id;
		$atts['entry_id'] = $entry_id;
		$field_obj = FrmFieldFactory::get_field_object( $atts['field'] );
		return $field_obj->get_value_to_save( $meta_value, $atts );
    }

	/**
	* Get media ID(s) to be saved to database and set global media ID values
	*
	* @since 2.0
	* @param array|string $prev_value (posted value)
	* @param object $field
	* @param integer $entry_id
	* @return array|string $meta_value
	*/
	public static function prepare_file_upload_meta( $prev_value, $field, $entry_id ) {
		// remove temp tag on uploads
		self::remove_meta_from_media( $prev_value );

		$last_saved_value = self::get_previous_file_ids( $field, $entry_id );
		self::delete_removed_files( $last_saved_value, $prev_value, $field );
		return $prev_value;
	}

	private static function maybe_upload_temp_file( &$errors, $field, $args ) {
		$file_uploads = $_FILES[ $args['file_name'] ];

		if ( self::file_was_selected( $file_uploads ) ) {
			$response = array( 'errors' => array(), 'media_ids' => array() );
			self::upload_temp_files( $args['file_name'], $response );

			if ( ! empty( $response['media_ids'] ) ) {
				$previous_value = self::get_file_posted_vals( $field->id, $args );
				$new_value = self::set_new_file_upload_meta_value( $field, $response['media_ids'], $previous_value );
				self::set_file_posted_vals( $field->id, $new_value, $args );
			}

			if ( ! empty( $response['errors'] ) ) {
				$errors[ 'field' . $field->temp_id ] = implode( ' ', $response['errors'] );
			}
		}
	}

	public static function ajax_upload() {
		$response = array( 'errors' => array(), 'media_ids' => array() );
		if ( ! empty( $_FILES ) ) {
			$field_id = FrmAppHelper::get_param( 'field_id', '', 'post', 'absint' );
			if ( $field_id ) {
				$field = FrmField::getOne( $field_id );
				$field->temp_id = $field->id;

				foreach ( $_FILES as $file_name => $file ) {
					$args = array( 'file_name' => $file_name );
					self::validate_file_type( $response['errors'], $field, $args );
					self::validate_file_size( $response['errors'], $field, $args );
					$response['errors'] = apply_filters( 'frm_validate_file', $response['errors'], $field, $args );

					if ( empty( $response['errors'] ) ) {
						self::upload_temp_files( $file_name, $response );
					}
				}
				$response = apply_filters( 'frm_response_after_upload', $response, $field );
			}
		}

		return $response;
	}

	private static function upload_temp_files( $file_name, &$response ) {
		$new_media_ids = self::upload_file( $file_name );

		if ( empty( $new_media_ids ) ) {
			$response['errors'][] = __( 'File upload failed', 'formidable-pro' );
		} else {
			self::add_meta_to_media( $new_media_ids, 'temporary' );
			$response['media_ids'] = $response['media_ids'] + (array) $new_media_ids;
			self::sort_errors_from_ids( $response );
		}
	}

    /**
     * Let WordPress process the uploads
     * @param int $field_id
     * @param bool $sideload
     */
	public static function upload_file( $field_id, $sideload = false ) {
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );

		$response = array( 'media_ids' => array(), 'errors' => array() );
		add_filter( 'upload_dir', array( 'FrmProFileField', 'upload_dir' ) );

		if ( ! $sideload && is_array( $_FILES[ $field_id ]['name'] ) ) {
			foreach ( $_FILES[ $field_id ]['name'] as $k => $n ) {
				if ( empty( $n ) ) {
					continue;
				}

				$f_id = $field_id . $k;
				$_FILES[ $f_id ] = array(
					'name'  => $n,
					'type'  => $_FILES[ $field_id ]['type'][ $k ],
					'tmp_name' => $_FILES[ $field_id ]['tmp_name'][ $k ],
					'error' => $_FILES[ $field_id ]['error'][ $k ],
					'size'  => $_FILES[ $field_id ]['size'][ $k ]
				);

				unset( $k, $n );

				self::handle_upload( $f_id, $response );
			}
		} else {
			self::handle_upload( $field_id, $response, $sideload );
		}

		remove_filter( 'upload_dir', array( 'FrmProFileField', 'upload_dir' ) );

		self::prepare_upload_response( $response );

		return $response;
	}

	private static function handle_upload( $field_id, &$response, $sideload = false ) {
		add_filter( 'wp_insert_attachment_data', 'FrmProFileField::change_attachment_slug' );
		$media_id = $sideload ? media_handle_sideload( $field_id, 0 ) : media_handle_upload( $field_id, 0 );
		remove_filter( 'wp_insert_attachment_data', 'FrmProFileField::change_attachment_slug' );

		if ( is_numeric( $media_id ) ) {
			$response['media_ids'][] = $media_id;
			self::add_meta_to_media( $media_id, 'file' );
		} else {
			$response['errors'][] = $media_id;
		}
	}

	/**
	 * Prevent attachments from using valuable top-level slug names
	 */
	public static function change_attachment_slug( $data ) {
		$data['post_name'] = sanitize_title( 'frm-' . $data['post_name'] );
		return $data;
	}

	private static function prepare_upload_response( &$response ) {
		if ( empty( $response['media_ids'] ) ) {
			$response = $response['errors'];
		} else {
			$response = $response['media_ids'];
			if ( count( $response ) == 1 ) {
				$response = reset( $response );
			}
		}
	}

	/**
	* Get the final media IDs
	*
	* @since 2.0
	* @param array|string $media_ids
	* @return array $mids
	*/
	private static function sort_errors_from_ids( &$response ) {
        $mids = array();
        foreach ( (array) $response['media_ids'] as $media_id ) {
            if ( is_numeric( $media_id ) ) {
               $mids[] = $media_id;
            } else {
                foreach ( $media_id->errors as $error ) {
                    if ( ! is_array( $error[0] ) ) {
                        $response['errors'][] = $error[0];
                    }
                    unset( $error );
                }
            }
            unset( $media_id );
        }

		$response['media_ids'] = array_filter( $mids );
	}

	/**
	 * Set _frm_temporary and _frm_file metas
	 * to use for media library filtering
	 */
	private static function add_meta_to_media( $media_ids, $type = 'temporary' ) {
		foreach ( (array) $media_ids as $media_id ) {
			if ( is_numeric( $media_id ) ) {
				update_post_meta( $media_id, '_frm_' . $type, 1 );
			}
		}
	}

	/**
	 * When an entry is saved, remove the temp flag
	 */
	private static function remove_meta_from_media( $media_ids ) {
		foreach ( (array) $media_ids as $media_id ) {
			if ( is_numeric( $media_id ) ) {
				delete_post_meta( $media_id, '_frm_temporary' );
			}
		}
	}

	/**
	 * Upload files into "formidable" subdirectory
	 */
	public static function upload_dir( $uploads ) {
		$form_id = FrmAppHelper::get_post_param( 'form_id', 0, 'absint' );
		if ( ! $form_id ) {
			$form_id = FrmAppHelper::simple_get( 'form', 'absint', 0 );
		}

		$relative_path = self::get_upload_dir_for_form( $form_id );

		if ( ! empty( $relative_path ) ) {
			$uploads['path'] = $uploads['basedir'] . '/' . $relative_path;
			$uploads['url'] = $uploads['baseurl'] . '/' . $relative_path;
			$uploads['subdir'] = '/' . $relative_path;
		}

		return $uploads;
	}

	public static function get_upload_dir_for_form( $form_id ) {
		$base = 'formidable';
		if ( $form_id ) {
			$base .= '/' . $form_id;
		}

		$relative_path = apply_filters( 'frm_upload_folder', $base, compact( 'form_id' ) );
		$relative_path = untrailingslashit( $relative_path );

		return $relative_path;
	}

	/**
	 * Automatically delete files when an entry is deleted.
	 * If the "Delete all entries" button is used, entries will not be deleted
	 * @since 2.0.22
	 */
	public static function delete_files_with_entry( $entry_id, $entry = false ) {
		if ( empty( $entry ) ) {
			return;
		}

		$upload_fields = FrmField::getAll( array( 'fi.type' => 'file', 'fi.form_id' => $entry->form_id ) );
		foreach ( $upload_fields as $field ) {
			self::delete_files_from_field( $field, $entry );
			unset( $field );
		}
	}

	/**
	 * @since 2.0.22
	 */
	public static function delete_files_from_field( $field, $entry ) {
		if ( self::should_delete_files( $field ) ) {
			$media_ids = self::get_previous_file_ids( $field, $entry );
			self::delete_files_now( $media_ids );
		}
	}

	private static function should_delete_files( $field ) {
		$auto_delete = FrmField::get_option_in_object( $field, 'delete' );
		return ! empty( $auto_delete );
	}

	/**
	 * @since 2.0.22
	 */
	private static function get_previous_file_ids( $field, $entry_id ) {
		return FrmProEntryMetaHelper::get_post_or_meta_value( $entry_id, $field );
	}

	private static function delete_removed_files( $old_value, $new_value, $field ) {
		if ( self::should_delete_files( $field ) ) {
			$media_ids = self::get_removed_file_ids( $old_value, $new_value );
			self::delete_files_now( $media_ids );
		}
	}

	/**
	 * @since 2.0.22
	 */
	private static function get_removed_file_ids( $old_value, $new_value ) {
		$media_ids = array_diff( (array) $old_value, (array) $new_value );
		return $media_ids;
	}

	/**
	 * @since 2.0.22
	 */
	private static function delete_files_now( $media_ids ) {
		if ( empty( $media_ids ) ) {
			return;
		}

		$media_ids = maybe_unserialize( $media_ids );
		foreach ( (array) $media_ids as $m ) {
			if ( is_numeric( $m ) ) {
				wp_delete_attachment( $m, true );
			}
		}
	}

	/**
	 * @since 2.02
	 */
	private static function get_file_posted_vals( $field_id, $args ) {
		if ( self::is_field_repeating( $field_id, $args ) ) {
			$value = $_POST['item_meta'][ $args['parent_field_id'] ][ $args['key_pointer'] ][ $field_id ];
		} else {
			$value = $_POST['item_meta'][ $field_id ];
		}
		return $value;
	}

    /**
    *
    * @since 2.0
    * @param int $field_id
    * @param $new_value to set
    * @param array $args array with repeating, key_pointer, and parent_field
    */
    private static function set_file_posted_vals( $field_id, $new_value, $args ) {
        if ( self::is_field_repeating( $field_id, $args ) ) {
            $_POST['item_meta'][ $args['parent_field_id'] ][ $args['key_pointer'] ][ $field_id ] = $new_value;
        } else {
            $_POST['item_meta'][ $field_id ] = $new_value;
        }
    }

	/**
	* Get the final value for a file upload field
	*
	* @since 2.0.19
	*
	* @param object $field
	* @param array $new_mids
	* @param array|string $prev_value
	* @return array|string $new_value
	*/
	private static function set_new_file_upload_meta_value( $field, $new_mids, $prev_value ) {
		// If no media IDs to upload, end now
		if ( empty( $new_mids ) ) {
			$new_value = $prev_value;
		} else {

			if ( FrmField::is_option_true( $field, 'multiple' ) ) {
				// Multi-file upload fields

				if ( ! empty( $prev_value ) ) {
					$new_value = array_merge( (array) $prev_value, $new_mids );
				} else {
					$new_value = $new_mids;
				}
			} else {
				// Single file upload fields
				$new_value = reset( $new_mids );
			}
		}

		return $new_value;
	}

	private static function is_field_repeating( $field_id, $args ) {
		// Assume this field is not repeating
		$repeating = false;

		if ( isset( $args['parent_field_id'] ) && $args['parent_field_id'] && isset( $args['key_pointer'] ) ) {
			// Check if the current field is inside of the parent/pointer
			if ( isset( $_POST['item_meta'][ $args['parent_field_id'] ][ $args['key_pointer'] ][ $field_id ] ) ) {
				$repeating = true;
			}
		}

		return $repeating;
	}

	/**
	 * @since 3.01.03
	 */
	public static function duplicate_files_with_entry( $entry_id, $form_id, $args ) {
		$old_entry_id  = ! empty( $args['old_id'] ) ? $args['old_id'] : 0;
		$upload_fields = FrmField::getAll( array( 'fi.type' => 'file', 'fi.form_id' => $form_id ) );

		if ( ! $old_entry_id || ! $upload_fields ) {
			return;
		}

		foreach ( $upload_fields as $field ) {
			$attachments = maybe_unserialize( self::get_previous_file_ids( $field, $old_entry_id ) );
			if ( empty( $attachments ) ) {
				continue;
			}

			$new_media_ids = array();

			foreach ( (array) $attachments as $attachment_id ) {
				$orig_path = get_attached_file( $attachment_id );

				if ( ! file_exists( $orig_path ) ) {
					continue;
				}

				// Copy path to a temp location because wp_handle_sideload() deletes the original.
				$tmp_path = wp_tempnam();
				if ( ! $tmp_path ) {
					continue;
				}

				$read_file = new FrmCreateFile( array(
				    'new_file_path' => dirname( $orig_path ),
				    'file_name' => basename( $orig_path ),
				) );
				$file_contents = $read_file->get_file_contents();

				if ( ! $file_contents || false === file_put_contents( $tmp_path, $file_contents ) ) { // phpcs:ignore WordPress.VIP.FileSystemWritesDisallow.file_ops_file_put_contents,
					@unlink( $tmp_path );
					continue;
				}

				$file_arr = array(
					'name'     => basename( $orig_path ),
					'size'     => @filesize( $tmp_path ),
					'tmp_name' => $tmp_path,
					'error'    => 0
				);
				$response = self::upload_file( $file_arr, true );

				foreach ( (array) $response as $r ) {
					if ( is_numeric( $r ) ) {
						$new_media_ids[] = $r;
					}
				}
			}

			if ( 1 === count( $new_media_ids ) ) {
				$new_meta = reset( $new_media_ids );
			} else {
				$new_meta = $new_media_ids;
			}

			FrmEntryMeta::update_entry_meta( $entry_id, $field->id, null, $new_meta );
		}
	}


	/**
	 * @deprecated 2.03.08
	 */
	public static function validate( $errors, $field, $values, $args ) {
		_deprecated_function( __FUNCTION__, '2.03.08', 'FrmProFileField::no_js_validate' );

		return self::no_js_validate( $errors, $field, $values, $args );
	}
}

