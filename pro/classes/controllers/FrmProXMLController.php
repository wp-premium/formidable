<?php

class FrmProXMLController{

    public static function import_default_templates($files) {
        $files[] = FrmAppHelper::plugin_path() .'/pro/classes/views/xml/default-templates.xml';
        return $files;
    }

    public static function route($continue, $action) {
        if ( $action == 'import_csv' ) {
            self::import_csv();
            $continue = false;
        }
        return $continue;
    }

    public static function importing_xml($imported, $xml) {
        if ( ! isset($xml->view) && ! isset($xml->item) ) {
            return $imported;
        }

        $append = array(
            'items' => 0,
        );
        $imported['updated'] = array_merge($imported['updated'], $append);
        $imported['imported'] = array_merge($imported['imported'], $append);
        unset($append);

	    // get entries
	    if ( isset($xml->item) ) {
            $imported = FrmProXMLHelper::import_xml_entries($xml->item, $imported);
	        unset($xml->item);
	    }

        return $imported;
    }

    public static function csv_instructions_1(){
        return __( 'Upload your Formidable XML or CSV file to import forms, entries, and views into this site. <br/><strong>Note: If your imported form/entry/view key and creation date match an item on your site, that item will be updated. You cannot undo this action.</strong>', 'formidable' );
    }

    public static function csv_instructions_2(){
        return __( 'Choose a Formidable XML or any CSV file', 'formidable' );
    }

	/**
	 * @param array|object $forms
	 */
    public static function csv_opts($forms) {
		$csv_del = FrmAppHelper::get_param( 'csv_del', ',', 'get', 'sanitize_text_field' );
		$form_id = FrmAppHelper::get_param( 'form_id', '', 'get', 'absint' );
		$csv_files = FrmAppHelper::get_param( 'csv_files', '', 'get', 'absint' );

		if ( 'object' == gettype( $forms ) ) {
			// do_action resets an array with a single object in it
			$forms = array( $forms );
		}

        include(FrmAppHelper::plugin_path() .'/pro/classes/views/xml/csv_opts.php');
    }

    public static function xml_export_types($types) {
        $types['posts'] = __( 'Views', 'formidable' );
        $types['styles'] = __( 'Styles', 'formidable' );

        return $types;
    }

    public static function export_formats($formats) {
		$formats['csv'] = array( 'name' => 'CSV', 'support' => 'items', 'count' => 'single' );
		$formats['xml']['support'] = 'forms|items|posts|styles';

        return $formats;
    }

	public static function csv_filter( $query, $atts ) {
		if ( ! empty( $atts['search'] ) && ! $atts['item_id'] ) {
			$query = FrmProEntriesHelper::get_search_str( $query, $atts['search'], $atts['form_id'], $atts['fid'] );
		}
		return $query;
	}

	public static function csv_row( $row, $atts ) {
		$row['user_id'] = FrmProFieldsHelper::get_display_name( $atts['entry']->user_id, 'user_login' );
		$row['updated_by'] = FrmProFieldsHelper::get_display_name( $atts['entry']->updated_by, 'user_login' );
		self::add_comments_to_csv( $row, $atts );
		return $row;
	}

	private static function add_comments_to_csv( &$row, $atts ) {
		if ( ! $atts['comment_count'] ) {
			// don't continue if we already know there are no comments
			return;
		}

	    $comments = FrmEntryMeta::getAll( array( 'item_id' => (int) $atts['entry']->id, 'field_id' => 0 ), ' ORDER BY it.created_at ASC' );

		$i = 0;
		if ( $comments ) {
			foreach ( $comments as $comment ) {
				$c = maybe_unserialize( $comment->meta_value );
				if ( ! isset( $c['comment'] ) ) {
					continue;
				}

				$row[ 'comment' . $i ] = $c['comment'];
				unset( $co );

				$row[ 'comment_user_id' . $i ] = FrmProFieldsHelper::get_display_name( $c['user_id'], 'user_login' );
				unset( $c );

				$row[ 'comment_created_at' . $i ] = FrmAppHelper::get_formatted_time( $comment->created_at, $atts['date_format'], ' ');
				unset( $v, $comment );
				$i++;
	        }
		}

		for ( $i; $i <= $atts['comment_count']; $i++ ) {
			$row[ 'comment' . $i ] = '';
			$row[ 'comment_user_id' . $i ] = '';
			$row[ 'comment_created_at' . $i ] = '';
		}
	}

	public static function csv_field_value( $field_value, $atts ) {
		// Post values need to be retrieved differently
		if ( $atts['entry']->post_id && ( $atts['field']->type == 'tag' || ( isset( $atts['field']->field_options['post_field'] ) && $atts['field']->field_options['post_field'] ) ) ) {
			$field_value = FrmProEntryMetaHelper::get_post_value(
				$atts['entry']->post_id, $atts['field']->field_options['post_field'],
				$atts['field']->field_options['custom_field'],
				array(
					'truncate' => ( ( $atts['field']->field_options['post_field'] == 'post_category' ) ? true : false ),
					'form_id' => $atts['entry']->form_id, 'field' => $atts['field'], 'type' => $atts['field']->type,
					'exclude_cat' => ( isset( $atts['field']->field_options['exclude_cat'] ) ? $atts['field']->field_options['exclude_cat'] : 0 ),
					'sep' => $atts['separator'],
				)
			);
		}

		if ( in_array( $atts['field']->type, array( 'user_id', 'file', 'date', 'data' ) ) ) {
			$field_value = FrmProFieldsHelper::get_export_val( $field_value, $atts['field'], $atts['entry'] );
		}

		return $field_value;
	}

	public static function export_csv( $atts ) {
		_deprecated_function( __METHOD__, '2.0.19', 'FrmXMLController::' . __FUNCTION__ );
		FrmXMLController::generate_csv( $atts );
	}

    // map fields from csv
    public static function map_csv_fields() {
        $name = 'frm_import_file';

        if ( ! isset($_FILES) || ! isset($_FILES[$name]) || empty($_FILES[$name]['name']) || (int) $_FILES[$name]['size'] < 1) {
            return;
        }

        $file = $_FILES[$name]['tmp_name'];

        // check if file was uploaded
        if ( ! is_uploaded_file($file) ) {
            return;
        }

        if ( empty($_POST['form_id']) ) {
            $errors = array(__( 'All Fields are required', 'formidable' ));
            FrmXMLController::form($errors);
            return;
        }

        //upload
        $media_id = ( isset( $_POST[ $name ] ) && ! empty( $_POST[ $name ] ) && is_numeric( $_POST[ $name ] ) ) ? $_POST[ $name ] : FrmProFileField::upload_file( $name );
        if ( $media_id && ! is_wp_error( $media_id ) ) {
            $filename = get_attached_file($media_id);
        }

        if ( empty($filename) ) {
            $errors = array(__( 'That CSV was not uploaded. Are CSV files allowed on your site?', 'formidable' ));
            FrmXMLController::form($errors);
            return;
        }

        $headers = $example = '';
		$csv_del = FrmAppHelper::get_param( 'csv_del', ',', 'get', 'sanitize_text_field' );
		$csv_files = FrmAppHelper::get_param( 'csv_files', ',', 'get', 'absint' );
		$form_id = FrmAppHelper::get_param( 'form_id', '', 'get', 'absint' );

        setlocale(LC_ALL, get_locale());
        if ( ( $f = fopen($filename, 'r') ) !== false ) {
            $row = 0;
            while ( ( $data = fgetcsv($f, 100000, $csv_del) ) !== false ) {
            //while (($raw_data = fgets($f, 100000))){
                $row++;
				if ( $row == 1 ) {
                    $headers = $data;
                } else if ( $row == 2 ) {
                    $example = $data;
				} else {
                    continue;
				}
            }
            fclose($f);
        } else {
            $errors = array(__( 'CSV cannot be opened.', 'formidable' ));
            FrmXMLController::form($errors);
            return;
        }

        $fields = FrmField::get_all_for_form($form_id);

        include(FrmAppHelper::plugin_path() .'/pro/classes/views/xml/map_csv_fields.php');
    }

    public static function import_csv() {
        //Import csv to entries
        $import_count = 250;
		$media_id = FrmAppHelper::get_param( 'frm_import_file', '', 'get', 'absint' );
        $current_path = get_attached_file($media_id);
		$row = FrmAppHelper::get_param('row', 0, 'get', 'absint' );
		$csv_del = FrmAppHelper::get_param( 'csv_del', ',', 'get', 'sanitize_text_field' );
		$csv_files = FrmAppHelper::get_param( 'csv_files', ',', 'get', 'absint' );
		$form_id = FrmAppHelper::get_param( 'form_id', 0, 'get', 'absint' );

        $opts = get_option('frm_import_options');

        $left = ( $opts && isset($opts[$media_id]) ) ? ( (int) $row - (int) $opts[$media_id]['imported'] - 1 ) : ( $row - 1 );
        if ( $row < 300 && ( ! isset($opts[$media_id]) || $opts[$media_id]['imported'] < 300 ) ) {
            // if the total number of rows is less than 250
            $import_count = ceil($left/2);
        }

        if ( $import_count > $left ) {
            $import_count = $left;
        }

        $mapping = FrmAppHelper::get_param('data_array');
        $url_vars = "&csv_del=". urlencode($csv_del) ."&form_id={$form_id}&frm_import_file={$media_id}&row={$row}&max={$import_count}";
		$url_vars .= "&csv_files=" . $csv_files;

        foreach ( $mapping as $mkey => $map ) {
            $url_vars .= "&data_array[$mkey]=$map";
		}

        include(FrmAppHelper::plugin_path() .'/pro/classes/views/xml/import_csv.php');
    }

    public static function import_csv_entries() {
        check_ajax_referer( 'frm_ajax', 'nonce' );
        FrmAppHelper::permission_check('frm_create_entries');

        $opts = get_option('frm_import_options');
        if ( ! $opts ) {
            $opts = array();
        }

        $vars = $_POST;
        $file_id = $vars['frm_import_file'];
        $current_path = get_attached_file($file_id);
        $start_row = isset($opts[$file_id]) ? $opts[$file_id]['imported'] : 1;

        $imported = FrmProXMLHelper::import_csv($current_path, $vars['form_id'], $vars['data_array'], 0, $start_row+1, $vars['csv_del'], $vars['max']);

        $opts[$file_id] = array( 'row' => $vars['row'], 'imported' => $imported);
        $remaining = ( (int) $vars['row'] - (int) $imported );
		echo (int) $remaining;

        // check if the import is complete
        if ( ! $remaining ) {
            unset($opts[$file_id]);

            // since we are finished with this csv, delete it
            wp_delete_attachment($file_id, true);
        }

        update_option('frm_import_options', $opts);

        wp_die();
    }

}