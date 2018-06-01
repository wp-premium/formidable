<?php

/**
 * @since 3.0
 */
class FrmProFieldFile extends FrmFieldType {

	/**
	 * @var string
	 * @since 3.0
	 */
	protected $type = 'file';

	protected $is_tall = true;

	protected function include_form_builder_file() {
		return FrmProAppHelper::plugin_path() . '/classes/views/frmpro-fields/back-end/field-' . $this->type . '.php';
	}

	protected function field_settings_for_type() {
		$settings = array(
			'default_value' => true,
			'invalid'       => true,
			'read_only'     => true,
		);

		FrmProFieldsHelper::fill_default_field_display( $settings );
		return $settings;
	}

	protected function extra_field_opts() {
		return array(
			'ftypes' => array(),
			'attach' => false,
			'delete' => false,
			'restrict' => 0,
			'resize'     => false,
			'new_size'  => '600',
			'resize_dir' => 'width',
		);
	}

	/**
	 * @since 3.01.01
	 */
	public function show_options( $field, $display, $values ) {
		$mimes = $this->get_mime_options( $field );
		include( FrmProAppHelper::plugin_path() . '/classes/views/frmpro-fields/back-end/file-options.php' );

		parent::show_options( $field, $display, $values );
	}

	/**
	 * @since 3.01.01
	 */
	private function get_mime_options( $field ) {
		$mimes = get_allowed_mime_types();
		$selected_mimes = $field['ftypes'];

		$ordered = array();
		foreach ( (array) $selected_mimes as $mime ) {
			$key = array_search( $mime, $mimes );
			if ( $key !== false ) {
				$ordered[ $key ] = $mimes[ $key ];
				unset( $mimes[ $key ] );
			}
		}

		$mimes = $ordered + $mimes;
		return $mimes;
	}

	public function validate( $args ) {
		return FrmProFileField::no_js_validate( array(), $this->field, $args['value'], $args );
	}

	/**
	 * Upload new files, delete removed files
	 *
	 * @since 3.0
	 * @param array|string $value (the posted value)
	 * @param array $atts
	 *
	 * @return array|string $value
	 */
	public function get_value_to_save( $value, $atts ) {
		// Upload files and get new meta value for file upload fields
		$value = FrmProFileField::prepare_file_upload_meta( $value, $this->field, $atts['entry_id'] );

		if ( is_array( $value ) ) {
			$value = array_map( 'intval', array_filter( $value ) );
		}

		return $value;
	}

	protected function prepare_display_value( $value, $atts ) {
		if ( ! is_numeric( $value ) && ! is_array( $value ) ) {
			return $value;
		}

		$showing_image = ( isset( $atts['html'] ) && $atts['html'] ) || ( isset( $atts['show_image'] ) && $atts['show_image'] );
		$default_sep = $showing_image ? ' ' : ', ';
		$atts['sep'] = isset( $atts['sep'] ) ? $atts['sep'] : $default_sep;

		$this->get_file_html_from_atts( $atts, $value );

		$return_array = isset( $atts['return_array'] ) && $atts['return_array'];
		if ( is_array( $value ) && ! $return_array ) {
			$value = implode( $atts['sep'], $value );

			if ( $showing_image ) {
				$value = '<div class="frm_file_container">' . $value . '</div>';
			}
		}

		return $value;
	}

	protected function fill_default_atts( &$atts ) {
		// don't add default separator
	}

	/**
	 * Get the HTML for a file upload field depending on the $atts
	 *
	 * @since 3.0
	 *
	 * @param array $atts
	 * @param string|array|int $replace_with
	 */
	private function get_file_html_from_atts( $atts, &$replace_with ) {
		$show_id = isset( $atts['show'] ) && $atts['show'] == 'id';
		if ( ! $show_id && ! empty( $replace_with ) ) {
			//size options are thumbnail, medium, large, or full
			$size = $this->set_size( $atts );

			$new_atts = array(
				'show_filename' => ( isset( $atts['show_filename'] ) && $atts['show_filename'] ) ? true : false,
				'show_image' => ( isset( $atts['show_image'] ) && $atts['show_image'] ) ? true : false,
				'add_link' => ( isset( $atts['add_link'] ) && $atts['add_link'] ) ? true : false,
				'new_tab' => ( isset( $atts['new_tab'] ) && $atts['new_tab'] ) ? true : false,
			);

			$this->modify_atts_for_reverse_compatibility( $atts, $new_atts );

			$ids = (array) $replace_with;
			$replace_with = $this->get_displayed_file_html( $ids, $size, $new_atts );
		}

		if ( is_array( $replace_with ) ) {
			$replace_with = array_filter( $replace_with );
		}
	}

	/**
	 * Check the 'size' first, and fallback to 'show' for reverse compatibility
	 * Set the default size for showing images
	 *
	 * @since 3.0
	 */
	private function set_size( $atts ) {
		if ( isset( $atts['size'] ) ) {
			$size = $atts['size'];
		} elseif ( isset( $atts['show'] ) ) {
			$size = $atts['show'];
		} elseif ( isset( $atts['source'] ) && $atts['source'] == 'entry_formatter' ) {
			$size = 'full';
		} else {
			$size = 'thumbnail';
		}
		return $size;
	}

	/**
	 * Maintain reverse compatibility for html=1, links=1, and show=label
	 *
	 * @since 3.0
	 *
	 * @param array $atts
	 * @param array $new_atts
	 */
	private function modify_atts_for_reverse_compatibility( $atts, &$new_atts ) {
		// For show=label
		if ( ! $new_atts['show_filename'] && isset( $atts['show'] ) && $atts['show'] == 'label' ) {
			$new_atts['show_filename'] = true;
		}

		// For html=1
		$inc_html = ( isset( $atts['html'] ) && $atts['html'] );
		if ( $inc_html && ! $new_atts['show_image'] ) {

			if ( $new_atts['show_filename'] ) {
				// For show_filename with html=1
				$new_atts['show_image'] = false;
				$new_atts['add_link'] = true;
			} else {
				// html=1 without show_filename=1
				$new_atts['show_image'] = true;
				$new_atts['add_link_for_non_image'] = true;
			}
		}

		// For links=1
		$show_links = ( isset( $atts['links'] ) && $atts['links'] );
		if ( $show_links && ! $new_atts['add_link'] ) {
			$new_atts['add_link'] = true;
		}
	}

	/**
	 * Get HTML for a file upload field depending on atts and file type
	 *
	 * @since 3.0
	 *
	 * @param array $ids
	 * @param string $size
	 * @param array $atts
	 * @return array|string
	 */
	public function get_displayed_file_html( $ids, $size = 'thumbnail', $atts = array() ) {
		$defaults = array(
			'show_filename' => false,
			'show_image' => false,
			'add_link' => false,
			'add_link_for_non_image' => false,
		);
		$atts = wp_parse_args( $atts, $defaults );
		$atts['size'] = $size;

		$img_html = array();
		foreach ( (array) $ids as $id ) {
			if ( ! is_numeric( $id ) ) {
				if ( ! empty( $id ) ) {
					// If a custom value was set with a hook, don't remove it
					$img_html[] = $id;
				}
				continue;
			}

			$img = $this->get_file_display( $id, $atts );

			if ( isset( $img ) ) {
				$img_html[] = $img;
			}
		}
		unset( $img, $id );

		if ( count( $img_html ) == 1 ) {
			$img_html = reset( $img_html );
		}

		return $img_html;
	}

	/**
	 * Get the HTML to display an uploaded in a File Upload field
	 *
	 * @since 3.0
	 *
	 * @param int $id
	 * @param array $atts
	 * @return string $img_html
	 */
	private function get_file_display( $id, $atts ) {
		if ( empty( $id ) || ! $this->file_exists_by_id( $id ) ) {
			return '';
		}

		$img_html = $image_url = '';
		$image = wp_get_attachment_image_src( $id, $atts['size'], false );
		$is_non_image = ! wp_attachment_is_image( $id );

		if ( $atts['show_image'] ) {
			$img_html = wp_get_attachment_image( $id, $atts['size'], $is_non_image );
		}

		// If show_filename=1 is included
		if ( $atts['show_filename'] ) {
			$label = $this->get_single_file_name( $id );
			if ( $atts['show_image'] ) {
				$img_html .= ' <span id="frm_media_' . absint( $id ) . '" class="frm_upload_label">' . $label . '</span>';
			} else {
				$img_html .= $label;
			}
		}

		// If neither show_image or show_filename are included, get file URL
		if ( empty( $img_html ) ) {
			if ( $is_non_image ) {
				$img_html = $image_url = wp_get_attachment_url( $id );
			} else {
				$img_html = $image['0'];
			}
		}

		// If add_link=1 is included
		if ( $atts['add_link'] || ( $is_non_image && $atts['add_link_for_non_image'] ) ) {

			$target = '';
			if ( isset( $atts['new_tab'] ) && $atts['new_tab'] ) {
				$target = ' target="_blank"';
			}

			if ( empty( $image_url ) ) {
				$image_url = wp_get_attachment_url( $id );
			}

			$img_html = '<a href="' . esc_url( $image_url ) . '" class="frm_file_link"' . $target . '>' . $img_html . '</a>';
		}

		$atts['media_id'] = $id;
		return apply_filters( 'frm_image_html_array', $img_html, $atts );
	}

	/**
	 * Check if a file exists on the site
	 *
	 * @since 3.0
	 * @param $id
	 *
	 * @return bool
	 */
	private function file_exists_by_id( $id ) {
		global $wpdb;

		$query = $wpdb->prepare( 'SELECT post_type FROM ' . $wpdb->posts . ' WHERE ID=%d', $id );
		$type = $wpdb->get_var( $query );

		return ( $type === 'attachment' );
	}

	/**
	 * Get the file name for a single media ID
	 *
	 * @since 3.0
	 *
	 * @param int $id
	 * @return boolean|string $filename
	 */
	private function get_single_file_name( $id ) {
		$attachment = get_post( $id );
		if ( ! $attachment ) {
			$filename = false;
		} else {
			$filename = basename( $attachment->guid );
		}
		return $filename;
	}

	public function front_field_input( $args, $shortcode_atts ) {
		$field = $this->field;
		$html_id = $args['html_id'];
		$field_name = $args['field_name'];

		$file_name = str_replace( 'item_meta[' . $field['id'] . ']', 'file' . $field['id'], $field_name );
		if ( $file_name == $field_name ) {
			// this is a repeating field
			$repeat_meta = explode( '-', $html_id );
			$repeat_meta = end( $repeat_meta );
			$file_name = 'file' . $field['id'] . '-' . $repeat_meta;
			unset( $repeat_meta );
		}

		ob_start();
		include( FrmProAppHelper::plugin_path() . '/classes/views/frmpro-fields/front-end/file.php' );
		$input_html = ob_get_contents();
		ob_end_clean();

		return $input_html;
	}

	/**
	 * Add extra classes on front-end input
	 *
	 * @since 3.01.04
	 */
	protected function get_input_class() {
		$class = '';
		if ( FrmField::is_option_true( $this->field, 'multiple' ) ) {
			$class = 'frm_multiple_file';
		}

		// Hide the "No files selected" text if files are selected
		if ( ! FrmField::is_option_empty( $this->field, 'value' ) ) {
			$class .= ' frm_transparent';
		}

		return $class;
	}

	protected function prepare_import_value( $value, $atts ) {
		$value = $this->get_file_id( $value );
		// If single file upload field, reset array
		if ( ! FrmField::is_option_true( $this->field, 'multiple' ) ) {
			$value = reset( $value );
		}
		return $value;
	}

	public function get_file_id( $value ) {
		global $wpdb;

		if ( ! is_array($value ) ) {
			$value = explode(',', $value);
		}

		foreach ( (array) $value as $pos => $m ) {
			$m = trim( $m );
			if ( empty( $m ) ) {
				continue;
			}

			if ( ! is_numeric( $m ) ) {
				//get the ID from the URL if on this site
				$m = FrmDb::get_col( $wpdb->posts, array( 'guid' => $m ), 'ID' );
			}

			if ( ! is_numeric( $m ) ) {
				unset( $value[ $pos ] );
			} else {
				$value[ $pos ] = $m;
			}

			unset( $pos, $m );
		}

		return $value;
	}
}
