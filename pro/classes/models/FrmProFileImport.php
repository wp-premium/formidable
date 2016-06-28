<?php

class FrmProFileImport {

	public static function import_attachment( $val, $field ) {
		if ( $field->type != 'file' || is_numeric( $val ) || empty( $val ) ) {
			return $val;
		}

		$should_import_files = FrmAppHelper::get_param( 'csv_files', '', 'REQUEST', 'absint' );
		if ( ! $should_import_files ) {
			return $val;
		}

		// Set up global vars to track uploaded files
		self::setup_global_media_import_vars( $field );

		global $wpdb, $frm_vars;
    
		$vals = self::convert_to_array( $val );

		$new_val = array();
		foreach ( (array) $vals as $v ) {
			$v = trim( $v );

			//check to see if the attachment already exists on this site
			$exists = $wpdb->get_var( $wpdb->prepare( 'SELECT ID FROM '. $wpdb->posts .' WHERE guid = %s', $v ) );
			if ( $exists ) {
				$new_val[] = $exists;
			} else {
				// Get media ID for newly uploaded image
				$mid = self::curl_image( $v );
				$new_val[] = $mid;
				if ( is_numeric( $mid ) ) {
					// Add newly uploaded images to the global media IDs for this field.
					$frm_vars['media_id'][ $field->id ][] = $mid;
				}
			}
			unset( $v );
		}

		$val = self::convert_to_string( $new_val );
    
		return $val;
	}

	/**
	 * Set up global media_id vars. This will be used for post fields.
	 */
	private static function setup_global_media_import_vars( $field ) {
		global $frm_vars;

		// If it hasn't been set yet, set it now
		if ( ! isset( $frm_vars['media_id'] ) ) {
			$frm_vars['media_id'] = array();
		}
		
		// Clear out old values
		$frm_vars['media_id'][ $field->id ] = array();
	}

	private static function convert_to_array( $val ) {
		if ( is_array( $val ) ) {
			$vals = $val;
		} else {
			$vals = str_replace( '<br/>', ',', $val );
			$vals = explode( ',', $vals );
		}
		return $vals;
	}

	private static function convert_to_string( $val ) {
		if ( count( $val ) == 1 ) {
			$val = reset( $val );
		} else {
			$val = implode( ',', $val );
		}
		return $val;
	}

	private static function curl_image( $img_url ) {
		$ch = curl_init( str_replace( array(' '), array( '%20' ), $img_url ) );
		$uploads = wp_upload_dir();
		$filename = wp_unique_filename( $uploads['path'], basename( $img_url ) );
		$path =  trailingslashit( $uploads['path'] ); // dirname(__FILE__) . '/screenshots/';
		$fp = fopen( $path . $filename, 'wb' );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
		curl_setopt( $ch, CURLOPT_FILE, $fp );
		curl_setopt( $ch, CURLOPT_HEADER, 0 );
		$result = curl_exec( $ch );
		curl_close( $ch );
		fclose( $fp );
		if ( $result ) {
			$img_url = self::attach_existing_image( $filename );
		} else {
			unlink( $path . $filename );
			//echo "<p>Failed to download image $img_url";
		}
		return $img_url;
	}

	private static function attach_existing_image( $filename ) {
		$attachment = array();
		self::prepare_attachment( $filename, $attachment );

		$uploads = wp_upload_dir();
		$file = $uploads['path'] . '/' . $filename;

		$id = wp_insert_attachment( $attachment, $file );
    
		if ( ! function_exists('wp_generate_attachment_metadata') ) {
			require_once( ABSPATH .'wp-admin/includes/image.php' );
		}
    
		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $file ) );

		return $id;
	}

	/**
	 * Construct the attachment array
	 */
	private static function prepare_attachment( $filename, &$attachment ) {
		$uploads = wp_upload_dir();
		$attachment = array(
			'guid'           => $uploads['url'] . '/' . $filename,
			'post_content'   => '',
		);

		$file = $uploads['path'] . '/' . $filename;

		self::get_mime_type( $file, $attachment );
		self::get_attachment_name( $file, $attachment );
	}

	private static function get_mime_type( $file, &$attachment ) {
		if ( function_exists('finfo_file') ) {
			$finfo = finfo_open( FILEINFO_MIME_TYPE ); // return mime type ala mimetype extension
			$type = finfo_file( $finfo, $file );
			finfo_close( $finfo );
			unset( $finfo );
		} else {
			$type = mime_content_type( $file );
		}
		$attachment['post_mime_type'] = $type;
	}

	private static function get_attachment_name( $file, &$attachment ) {
		$name_parts = pathinfo( $file ) ;
		$name = trim( substr( $name_parts['basename'], 0, - ( 1 + strlen( $name_parts['extension'] ) ) ) );
		$attachment['post_title'] = $name;
	}
}
