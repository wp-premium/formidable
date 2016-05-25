<?php

class FrmProGraphsController {

	public static function graph_shortcode( $atts ) {

		$defaults = array(
			'id' => false,
			'ids' => array(),
			'colors' => '',
			'grid_color' => '#CCC',
			'is3d' => false,
			'height' => 400,
			'width' => 400,
			'truncate_label' => 7,
			'bg_color' => '#FFFFFF',
			'truncate' => 40,
			'response_count' => 10,
			'user_id' => false,
			'entry_id' => false,
			'title' => '',
			'type' => 'default',
			'x_axis' => false,
			'data_type' => 'count',
			'limit' => '',
			'show_key' => false,
			'min' => '',
			'max' => '',
			'y_title' => '',
			'x_title' => '',
			'include_zero' => false,
			'field' => false,
			'title_size' => '',
			'title_font' => '',
			'tooltip_label' => '',
			'start_date' => '',
			'end_date' => '',
			'x_start' => '',
			'x_end' => '',
			'group_by' => '',
			'x_order' => 'default',
			'atts' => false,
		);

		// TODO: Remove limit from docs, add x_order='desc' and x_order='field_options'
		// TODO: Remove id from docs. Just use ids to simplify.
		// TODO: Remove either x start or start_date from docs
		// TODO: Remove either x_end or end_date from docs
		// TODO: Make sure x_order is set up to work with abc

		// If no id, stop now
		if ( ! $atts || ! $atts[ 'id' ] ) {
			$html = __( 'You must include a field id or key in your graph shortcode.', 'formidable' );
			return $html;
		}

		if ( isset( $atts[ 'type' ] ) && $atts[ 'type' ] == 'geo' ) {
			$defaults[ 'truncate_label' ] = 100;
			$defaults[ 'width' ] = 600;
		}

		if ( isset( $atts[ 'include_js' ] ) ) {
			unset( $atts[ 'include_js' ] );
		}

		// Set up array for filtering fields
		// TODO: Ask about simpler way
		$temp_atts = $atts;
		foreach ( $defaults as $unset => $val ) {
			unset( $temp_atts[ $unset ], $unset, $val );
		}
		foreach ( $temp_atts as $unset => $val ) {
			unset( $atts[ $unset ] );
			$atts[ 'atts' ][ $unset ] = $val;
			unset( $unset, $val );
		}

		// User's values should override default values
		$atts = array_merge( $defaults, $atts );

		global $wpdb;

		//x_start and start_date do the same thing
		// Reverse compatibility for x_start
		if ( $atts[ 'start_date' ] || $atts[ 'x_start' ] ) {
			if ( $atts[ 'x_start' ] ) {
				$atts[ 'start_date' ] = $atts[ 'x_start' ];
				unset( $atts[ 'x_start' ] );
			}
			$atts[ 'start_date' ] = FrmAppHelper::replace_quotes( $atts[ 'start_date' ] );
		}

		//x_end and end_date do the same thing
		// Reverse compatibility for x_end
		if ( $atts[ 'end_date' ] || $atts[ 'x_end' ] ) {
			if ( $atts[ 'x_end' ] ) {
				$atts[ 'end_date' ] = $atts[ 'x_end' ];
				unset( $atts[ 'x_end' ] );
			}
			$atts[ 'end_date' ] = FrmAppHelper::replace_quotes( $atts[ 'end_date' ] );
		}

		// Reverse compatibility for x_order=0
		if ( ! $atts[ 'x_order' ] ) {
			$atts[ 'x_order' ] = 'field_opts';
		}

		// If limit is set, get only the top results
		if ( $atts[ 'limit' ] ) {
			$atts[ 'x_order' ] = 'desc';
		}

		$atts[ 'user_id' ] = FrmAppHelper::get_user_id_param( $atts[ 'user_id' ] );

		if ( $atts[ 'entry_id' ] ) {
			$atts[ 'entry_id' ] = explode( ',', $atts[ 'entry_id' ] );

			//make sure all values are numeric
			//TODO: Make this work with entry keys
			$atts[ 'entry_id' ] = array_filter( $atts[ 'entry_id' ], 'is_numeric' );
			if ( empty( $atts[ 'entry_id' ] ) ) {
				// don't continue if there are no entry ids
				return '';
			}
			$atts[ 'entry_id' ] = implode( ',', $atts[ 'entry_id' ] );
		}
		// Switch to entry_ids for easier reference
		$atts[ 'entry_ids' ] = $atts[ 'entry_id' ];
		unset( $atts[ 'entry_id' ] );

		//Convert $tooltip_label to array
		if ( $atts[ 'tooltip_label' ] ) {
			$atts[ 'tooltip_label' ] = explode( ',', $atts[ 'tooltip_label' ] );
		}

		// This will only be an object when coming from show()
		if ( is_object( $atts[ 'field' ] ) ) {
			$fields = array( $atts[ 'field' ] );

			// If creating multiple graphs with one shortcode
		} else {
			$atts[ 'id' ] = explode( ',', $atts[ 'id' ] );

			foreach ( $atts[ 'id' ] as $key => $id ) {
				//If using field keys, retrieve the field IDs
				if ( ! is_numeric( $id ) ) {
					$atts[ 'id' ][ $key ] = FrmDb::get_var( $wpdb->prefix . 'frm_fields', array( 'field_key' => $id ) );
				}
				unset( $key, $id );
			}
			//make sure all field IDs are numeric - TODO: ask Steph if this is redundant
			$atts[ 'id' ] = array_filter( $atts[ 'id' ], 'is_numeric' );
			if ( empty( $atts[ 'id' ] ) ) {
				// don't continue if there is nothing to graph
				return '';
			}

			$fields = FrmField::getAll( array( 'fi.id' => $atts[ 'id' ] ) );

			// No longer needed
			unset( $atts[ 'id' ] );
		}

		if ( ! empty( $atts[ 'colors' ] ) ) {
			$atts[ 'colors' ] = explode( ',', $atts[ 'colors' ] );
		}

		// Trigger js load
		global $frm_vars;
		$frm_vars[ 'forms_loaded' ][] = true;

		$html = '';
		foreach ( $fields as $field ) {
			$data = self::get_google_graph( $field, $atts );

			if ( empty( $data ) ) {
				$html .= apply_filters( 'frm_no_data_graph', '<div class="frm_no_data_graph">' . __( 'No Data', 'formidable' ) . '</div>' );
				continue;
			}

			$html .= '<div id="chart_' . $data[ 'graph_id' ] . '" style="height:' . $atts[ 'height' ] . ';width:' . $atts[ 'width' ] . '"></div>';
		}

		return $html;
	}

	private static function get_google_graph($field, $args){
		$defaults = array( 'allowed_col_types' => array( 'string', 'number'));

		$args = wp_parse_args($args, $defaults);
		$vals = self::get_graph_values($field, $args);

		if ( empty($vals) ) {
			return '';
		}

		$pie = ( $args['type'] == 'default' ) ? $vals['pie'] : ( $args['type'] == 'pie' ? true : false );
		if ( $pie ) {
			$args['type'] = 'pie';

			//map each array position in rows array
			$vals['cols'] = array( 'Field' => array( 'type' => 'string'), 'Entries' => array( 'type' => 'number'));

			foreach ( $vals['values'] as $val_key => $val ) {
				if ( $val ) {
					$vals['rows'][] = array($vals['labels'][$val_key], $val);
				}
			}
		} else {
			if ( ! isset($vals['options']['hAxis']) ) {
				$vals['options']['hAxis'] = array();
			}

			$vals['options']['vAxis'] = array( 'gridlines' => array( 'color' => $args['grid_color']));
			if ( $vals['combine_dates'] && ! strpos($args['width'], '%') && ( ( count($vals['labels']) * 50 ) > (int) $args['width'] ) ) {
				$vals['options']['hAxis']['showTextEvery'] = ceil(( count($vals['labels']) * 50 ) / (int) $args['width'] );
			}

			$vals['options']['hAxis']['slantedText'] = true;
			$vals['options']['hAxis']['slantedTextAngle'] = 20;

			$rn_order = array();
			foreach ( $vals['labels'] as $lkey => $l ) {
				if ( isset($x_field) && $x_field && $x_field->type == 'number' ) {
					$l = (float) $l;
					$rn_order[] = $l;
				}

				$row = array($l, $vals['values'][$lkey]);
				foreach ( $vals['f_values'] as $f_id => $f_vals ) {
					$row[] = isset($f_vals[$lkey]) ? $f_vals[$lkey] : 0;
				}

				// add an untruncated tooltip
				if ( isset($vals['tooltips'][$lkey]) ) {
					$row['tooltip'] = wordwrap( $vals['tooltips'][ $lkey ] . ': ' . $row[1], 50, "\r\n" );
				}

				$vals['rows'][] = $row;
				unset($lkey, $l);
			}

			if ( isset($args['max']) && $args['max'] != '' ) {
				$vals['options']['vAxis']['viewWindow']['max'] = $args['max'];
			}

			if ( $args['min'] != '' ) {
				$vals['options']['vAxis']['viewWindow']['min'] = $args['min'];
			}

			if ( isset($args['y_title']) && ! empty($args['y_title']) ) {
				$vals['options']['vAxis']['title'] = $args['y_title'];
			}

			if ( isset($args['x_title']) && ! empty($args['x_title']) ) {
				$vals['options']['hAxis']['title'] = $args['x_title'];
			}
		}

		if ( isset( $rn_order ) && ! empty( $rn_order ) ) {
			asort($rn_order);
			$sorted_rows = array();
			foreach ( $rn_order as $rk => $rv ) {
				$sorted_rows[] = $vals['rows'][$rk];
			}

			$vals['rows'] = $sorted_rows;
		}

		$vals['options']['backgroundColor'] = $args['bg_color'];
		$vals['options']['is3D'] = $args['is3d'] ? true : false;

		if ( in_array($args['type'], array( 'bar', 'bar_flat', 'bar_glass')) ) {
			$args['type'] = 'column';
		} else if ( $args['type'] == 'hbar' ) {
			$args['type'] = 'bar';
		}

		$allowed_types = array( 'pie', 'line', 'column', 'area', 'SteppedArea', 'geo', 'bar');
		if ( ! in_array($args['type'], $allowed_types) ) {
			$args['type'] = 'column';
		}

		$vals['options'] = apply_filters( 'frm_google_chart', $vals['options'], array(
			'rows' => $vals['rows'], 'cols' => $vals['cols'], 'type' => $args['type'], 'atts' => $args['atts'],
		) );
		return self::convert_to_google($vals['rows'], $vals['cols'], $vals['options'], $args['type']);
	}

	private static function get_graph_values( $field, $args ) {
		// These are variables that will be returned at the end
		$values = $labels = $tooltips = $f_values = $rows = $cols = $options = $x_inputs = array();
		$pie = $combine_dates = false;

		// Get fields and ids array
		$fields_and_ids = self::get_fields( $field, $args['ids'] );
		$args['ids'] = $fields_and_ids['ids'];
		$args['fields'] = $fields_and_ids['fields'];

		// Get x field
		self::get_x_field( $args );

		// Get columns
		self::get_graph_cols( $cols, $field, $args );

		// Get options
		self::get_graph_options( $options, $field, $args );

		// Get entry IDs
		self::get_entry_ids( $field, $args );

		// If there are no matching entry IDs for filtering values, end now
		if ( $args['atts'] && ! $args['entry_ids'] ) {
			return array();
		}

		// Get values when x axis is set
		if ( $args['x_axis'] ) {
			self::get_x_axis_values( $values, $f_values, $labels, $tooltips, $x_inputs, $field, $args );

			if ( ! $values ) {
				return array();
			}

			self::combine_dates( $combine_dates, $values, $labels, $tooltips, $f_values, $args);

			// Graph by month or quarter
			self::graph_by_period( $values, $f_values, $labels, $tooltips, $args );

			// Get values by field if no x axis is set and multiple fields are being graphed
		} else if ( $args['ids'] ) {
			self::get_multiple_id_values( $values, $labels, $tooltips, $args );

			// Get values for posts and non-posts
		} else {
			// TODO: Make sure this works with truncate_label
			self::get_count_values( $values, $labels, $tooltips, $pie, $field, $args );

			if ( empty( $values ) ) {
				return array();
			}
		}

		// Reset keys for labels, values, and tooltips
		$labels = FrmProAppHelper::reset_keys($labels);
		$values = FrmProAppHelper::reset_keys($values);
		$tooltips = FrmProAppHelper::reset_keys($tooltips);
		foreach ( $f_values as $f_field_id => $f_value ) {
			$f_values[ $f_field_id ] = FrmProAppHelper::reset_keys( $f_value );
			unset( $f_field_id, $f_value );
		}

		// Filter hooks
		$values = apply_filters('frm_graph_values', $values, $args, $field);
		$labels = apply_filters('frm_graph_labels', $labels, $args, $field);
		$tooltips = apply_filters('frm_graph_tooltips', $tooltips, $args, $field);

		// Return values
		$return = array(
			'f_values' => $f_values,
			'labels' => $labels,
			'values' => $values,
			'pie' => $pie,
			'combine_dates' => $combine_dates,
			'ids' => $args['ids'],
			'cols' => $cols,
			'rows' => $rows,
			'options' => $options,
			'fields' => $args['fields'],
			'tooltips' => $tooltips,
			'x_inputs' => $x_inputs,
		);

		// Allow complete customization with this hook:
		$return = apply_filters( 'frm_final_graph_values', $return, $args, $field );

		return $return;
	}

	private static function convert_to_google($rows, $cols, $options, $type) {
		$num_col = array();

		global $frm_vars;
		$frm_vars['forms_loaded'][] = true;
		if ( ! isset($frm_vars['google_graphs']) ) {
			$frm_vars['google_graphs'] = array();
		}

		$graph_type = ($type == 'geo' ) ? 'geochart' : 'corechart';
		if ( ! isset($frm_vars['google_graphs'][$graph_type]) ) {
			$frm_vars['google_graphs'][$graph_type] = array();
		}

		$graph = array(
			'data'      => array(),
			'options'   => $options,
			'type'      => $type,
			'graph_id'  => '_frm_'. strtolower($type) . ( count($frm_vars['google_graphs'][$graph_type]) +1 ),
		);

		$new_cols = array();
		if ( ! empty($cols) ) {
			$pos = 0;
			foreach ( (array) $cols as $col_name => $col ) {
				$new_cols[] = array( 'type' => $col['type'], 'name' => $col_name);

				// save the number cols so we can make sure they are formatted correctly below
				if ( 'number' == $col['type'] ) {
					$num_col[] = $pos;
				}
				$pos++;

				unset($col_name, $col);
			}
		}

		if ( ! empty($rows) && ! empty($num_col) ) {
			// make sure number fields are displayed as numbers
			foreach ( $rows as $row_k => $row ) {
				foreach ( $num_col as $k ) {
					$rows[$row_k][$k] = (float) $rows[$row_k][$k];
					unset($k);
				}

				unset($row_k, $row);
			}
		}

		$graph['rows'] = $rows;
		$graph['cols'] = $new_cols;

		$frm_vars['google_graphs'][$graph_type][] = $graph;

		return $graph;
	}

	/**
	 * Get fields and ids arrays
	 *
	 * @since 2.0
	 *
	 * @param object $field
	 * @param array $ids additional field ids
	 * @return array $return - multidimensional array of fields and ids
	 */
	private static function get_fields( $field, $ids ) {;
		$fields = array();
		$fields[$field->id] = $field;

		// If multiple fields are being graphed
		if ( $ids ) {
			$ids = explode(',', $ids);

			foreach ( $ids as $id_key => $f ) {
				$ids[$id_key] = $f = trim($f);
				if ( ! $f ) {
					unset( $ids[$id_key] );
					continue;
				}

				if ( $add_field = FrmField::getOne( $f ) ) {
					$fields[$add_field->id] = $add_field;
					$ids[$id_key] = $add_field->id;
				}
			}
			unset( $f, $id_key );
		}
		$return = compact( 'fields', 'ids' );
		return $return;
	}

	/**
	 * Get all posts for this form
	 *
	 * @since 2.0
	 *
	 * @param object $field
	 * @param array $args
	 * @return array $form_posts - posts for form
	 */
	private static function get_form_posts( $field, $args ) {
		global $wpdb;
		if ( $args['entry_ids'] && is_array( $args['entry_ids'] ) ) {
			$args['entry_ids'] = implode( ', ', $args['entry_ids'] );
		}

		$query = array( 'form_id' => $field->form_id, 'post_id >' => 1);
		if ( $args['user_id'] !== false ) {
			$query['user_id'] = $args['user_id'];
		}
		if ( $args['entry_ids'] ) {
			$query['id'] = $args['entry_ids'];
		}

		$form_posts = FrmDb::get_results( $wpdb->prefix .'frm_items', $query, 'id, post_id' );

		return $form_posts;
	}

	/**
	 * Get entry IDs array for graph - only when entry_id is set or filtering by another field
	 *
	 * @since 2.0
	 *
	 * @param object $field
	 * @param array $args
	 */
	private static function get_entry_ids( $field, &$args ) {
		if ( ! $args['entry_ids'] && ! $args['atts'] ) {
			return;
		}

		// Check if form creates posts
		$form_posts = self::get_form_posts( $field, $args );

		$entry_ids = array();

		// If entry ID is set in shortcode
		if ( $args['entry_ids'] ) {
			$entry_ids = explode( ',', $args['entry_ids'] );
		}

		//If filtering by other fields
		if ( $args['atts'] ) {
			//Get the entry IDs for fields being used to filter graph data
			$after_where = false;
			foreach ( $args['atts'] as $orig_f => $val ) {
				$entry_ids = FrmProFieldsHelper::get_field_matches( array(
					'entry_ids' => $entry_ids, 'orig_f' => $orig_f, 'val' => $val,
					'id' => $field->id, 'atts' => $args['atts'], 'field' => $field,
					'form_posts' => $form_posts, 'after_where' => $after_where ,
					'drafts' => false,
				));
				$after_where = true;
			}
		}
		$args['entry_ids'] = $entry_ids;
	}

	/**
	 * Get x_field
	 *
	 * @since 2.0
	 *
	 * @param args (array), passed by reference
	 */
	private static function get_x_field( &$args ) {
		// Assume there is no x field
		$args['x_field'] = false;

		// If there is an x_axis and it is a field ID or key
		if ( $args['x_axis'] && ! in_array( $args['x_axis'], array( 'created_at', 'updated_at' ) ) ) {
			$args['x_field'] = FrmField::getOne( $args['x_axis'] );
		}
	}

	/**
	 * Get inputs, x_inputs, and f_inputs for graph when x_axis is set
	 * TODO: Clean this function
	 *
	 * @since 2.0
	 *
	 * @param array $inputs - inputs for main field
	 * @param array $f_inputs - multi-dimensional array for additional field inputs
	 * @param array $x_inputs - x-axis inputs
	 * @param object $field
	 * @param array $args
	 */
	private static function get_x_axis_inputs( &$inputs, &$f_inputs, &$x_inputs, $field, $args ) {
		global $wpdb;

		// Set up both queries
		$query = $x_query = 'SELECT em.meta_value, em.item_id FROM ' . $wpdb->prefix . 'frm_item_metas em';

		// Join regular query with items table
		$query .= ' LEFT JOIN '. $wpdb->prefix . 'frm_items e ON (e.id=em.item_id)';

		// If x axis is a field
		if ( $args['x_field'] ) {
			$x_query .= ' LEFT JOIN '. $wpdb->prefix . 'frm_items e ON (e.id=em.item_id)';
			$x_query .= " WHERE em.field_id=" . $args['x_field']->id;

			// If x-axis is created_at or updated_at
		} else {
			$x_query = 'SELECT id, '. $args['x_axis'] .' FROM '. $wpdb->prefix . 'frm_items e';
			$x_query .= " WHERE form_id=". $field->form_id;
		}

		// Will be used when graphing multiple fields
		$select_query = $query;
		$and_query = '';

		// Add where to regular query
		$query .= " WHERE em.field_id=". (int) $field->id;

		self::add_to_x_axis_queries( $query, $x_query, $and_query, $args);

		// If graphing multiple fields, set up multiple queries
		$q = array();
		foreach ( $args['fields'] as $f_id => $f ) {
			if ( $f_id != $field->id ) {
				$q[$f_id] = $select_query . " WHERE em.field_id=". (int) $f_id;
				if ( $args['user_id'] !== false ) {
					$select_query .= ' AND user_id=' . $args['user_id'];
				}
				$q[$f_id] .= $and_query;
			}
			unset($f, $f_id);
		}
		if ( empty($q) ) {
			$f_inputs = array();
		}

		//Set up $x_query for data from entries fields.
		if ( $args['x_field'] && $args['x_field']->type == 'data' ) {
			$linked_field = $args['x_field']->field_options['form_select'];
			$x_query = str_replace('SELECT em.meta_value, em.item_id', 'SELECT dfe.meta_value, em.item_id', $x_query);
			$x_query = str_replace($wpdb->prefix . 'frm_item_metas em', $wpdb->prefix . 'frm_item_metas dfe, ' . $wpdb->prefix . 'frm_item_metas em', $x_query);
			$x_query = str_replace('WHERE', 'WHERE dfe.item_id=em.meta_value AND dfe.field_id=' . $linked_field . ' AND', $x_query);
		}

		// Get inputs
		$query = apply_filters('frm_graph_query', $query, $field, $args);
		$inputs = $wpdb->get_results($query, ARRAY_A); // TODO: Check for prepare

		// Get x inputs
		$x_query = apply_filters('frm_graph_xquery', $x_query, $field, $args);
		$x_inputs = $wpdb->get_results($x_query, ARRAY_A); // TODO: Check for prepare

		unset( $query, $x_query );

		// Get all entry IDs from x_inputs
		$x_entries = array();
		foreach ( $x_inputs as $x_input ) {
			$x_entries[] = (  isset( $x_input['item_id'] ) ? $x_input['item_id'] : $x_input['id'] );
			unset( $x_input );
		}

		//If there are multiple fields being graphed
		foreach ( $q as $f_id => $query ) {
			$f_inputs[$f_id] = $wpdb->get_results($query, ARRAY_A); // TODO: Check for prepare
			self::clean_inputs( $f_inputs[$f_id], $field, $args, $x_entries );
			unset($f_id, $query);
		}

		// Clean up inputs
		self::clean_inputs( $inputs, $field, $args, $x_entries );
		self::clean_inputs( $x_inputs, $field, $args);
	}

	/**
	 * Get graph columns
	 *
	 * @since 2.0
	 *
	 * @param array $cols
	 * @param object $field
	 * @param array $args
	 */
	private static function get_graph_cols( &$cols, $field, $args ) {
		// Set default x-axis type
		$cols['xaxis'] = array( 'type' => 'string');

		if ( $args['x_field'] ) {
			$cols['xaxis'] = array( 'type' => ( in_array( $args['x_field']->type, $args['allowed_col_types']) ? $args['x_field']->type : 'string' ), 'id' => $args['x_field']->id );
		}

		// If x axis is not set, only set up cols as if there were one field
		if ( ! $args['x_axis'] ) {
			$args['fields'] = array( $field->id => $field );
		}

		//add columns for each field
		$count = 0;
		$tooltip_label = $args['tooltip_label'];
		foreach ( $args['fields'] as $f_id => $f ) {
			$type = in_array( $f->type, $args['allowed_col_types'] ) ? $f->type : 'number';
			// If custom tooltip label is set, change the tooltip label to match user-defined text
			if ( isset( $tooltip_label[ $count ] ) && ! empty( $tooltip_label[ $count ] ) ) {
				$cols[ $tooltip_label[ $count ] ] = array( 'type' => $type, 'id' => $f->id );
				$count++;

				// If tooltip label is not set by user, use the field name
			} else {
				$cols[ $f->name ] = array( 'type' => $type, 'id' => $f->id );
			}
			unset($f, $f_id);
		}
	}

	/**
	 * Get options for graph
	 *
	 * @since 2.0
	 *
	 * @param array $options
	 * @param object $field
	 * @param array $args
	 */
	private static function get_graph_options( &$options, $field, $args ) {
		// Set up defaults
		$options = array( 'width' => $args['width'], 'height' => $args['height'], 'legend' => 'none' );

		if ( $args['colors'] ) {
			$options['colors'] = $args['colors'];
		}

		if ( $args['title'] ) {
			$options['title'] = $args['title'];
		} else {
			$options['title'] = preg_replace("/&#?[a-z0-9]{2,8};/i", "", FrmAppHelper::truncate($field->name, $args['truncate'], 0));
		}

		if ( $args['title_size'] ) {
			$options['titleTextStyle']['fontSize'] = $args['title_size'];
		}

		if ( $args['title_font'] ) {
			$options['titleTextStyle']['fontName'] = $args['title_font'];
		}

		if ( $args['show_key'] ) {
			// Make sure show_key isn't too small
			if ( $args['show_key'] < 5 ) {
				$args['show_key'] = 10;
			}
			$options['legend'] = array( 'position' => 'right', 'textStyle' => array( 'fontSize' => $args['show_key'] ) );
		}

		if ( $args['x_field'] ) {
			$options['hAxis'] = array( 'title' => $args['x_field']->name);
		}
	}


	/**
	 * Add to queries when x axis is set
	 *
	 * @since 2.0
	 */
	private static function add_to_x_axis_queries( &$query, &$x_query, &$and_query, $args ) {
		global $wpdb;

		/// If start date is set
		if ( $args['start_date'] ) {
			$start_date = $wpdb->prepare('%s', date('Y-m-d', strtotime( $args['start_date'] ) ) );

			self::add_start_end_date_where( '>', $start_date, $args, $x_query );
		}

		// If end date is set
		if ( $args['end_date'] ) {
			$end_date = $wpdb->prepare('%s', date('Y-m-d 23:59:59', strtotime( $args['end_date'] )));

			self::add_start_end_date_where( '<', $end_date, $args, $x_query );
		}

		//If user_id is set
		if ( $args['user_id'] !== false ) {
			$query .= $wpdb->prepare(' AND user_id=%d', $args['user_id']);
			$x_query .= $wpdb->prepare(' AND user_id=%d', $args['user_id']);
			$and_query .= $wpdb->prepare(' AND user_id=%d', $args['user_id']);
		}

		//If entry_ids is set
		if ( $args['entry_ids'] ) {
			$query .= " AND e.id in (" . implode( ',', $args['entry_ids'] ) . ")";
			$x_query .= " AND e.id in (" . implode( ',', $args['entry_ids'] ) . ")";
			$and_query .= " AND e.id in (" . implode( ',', $args['entry_ids'] ) . ")";
		}

		// Don't include drafts
		$query .= ' AND e.is_draft=0';
		$x_query .= ' AND e.is_draft=0';
		$and_query .= ' AND e.is_draft=0';
	}

	/**
	 * Add start_date/end_date to x_axis WHERE query
	 *
	 * @since 2.0.12
	 *
	 * @param string $operator
	 * @param string $date
	 * @param array $args
	 * @param string $x_query - pass by reference
	 *
	 * TODO: Check if it's more efficient to add to the $query and $and_query at this time as well
	 */
	private static function add_start_end_date_where( $operator, $date, $args, &$x_query ){
		if ( $args['x_field'] ) {
			if ( $args['x_field']->type == 'date' ) {
				// If the x axis is a date field, make sure the dates comes after start_date
				$x_query .= ' AND meta_value ' . $operator . '=' . $date;
			} else {
				// If the x axis is NOT a date field, filter by creation date
				$x_query .= ' and e.created_at' . $operator . '=' . $date;
			}
		} else {
			// x_axis is created_at or updated_at
			$x_query .= ' and e.' . $args['x_axis'] . $operator . '=' . $date;
		}
	}

	/**
	 * Strip slashes and get rid of multi-dimensional arrays in inputs
	 *
	 * @since 2.0
	 *
	 * @param array $inputs
	 * @param object $field
	 * @param array $args
	 * @return array $inputs - cleaned inputs array
	 */
	private static function clean_inputs( &$inputs, $field, $args, $x_entries = array() ) {
		if ( ! $inputs ) {
			return false;
		}

		//Break out any inner arrays (for checkbox or multi-select fields) and add them to the end of the $inputs array
		if ( ! $args['x_axis'] && FrmField::is_field_with_multiple_values( $field ) ) {
			$count = 0;
			foreach ( $inputs as $k => $i ) {
				$i = maybe_unserialize($i);
				if ( ! is_array( $i ) ) {
					unset($k, $i);
					continue;
				}

				unset($inputs[$k]);
				$count++;
				foreach ( $i as $i_key => $item ) {
					// If this is an "other" option, keep key
					if ( strpos( $i_key, 'other' ) !== false ) {
						$inputs[] = $i_key;
					} else {
						$inputs[] = $item;
					}
					unset($item, $i_key);
				}
			}
			unset( $k, $i, $count);
		}

		if ( $x_entries ) {
			// Get rid of inputs if there is no match in x_inputs
			foreach ( $inputs as $key => $input ) {
				if ( ! in_array ( $input['item_id'], $x_entries ) ) {
					unset( $inputs[$key] );
				}
			}
			unset( $key, $input );
		}

		//Strip slashes from inputs
		$inputs = stripslashes_deep($inputs);

		return $inputs;
	}

	/**
	 * Modify post values (only applies to x-axis graphs)
	 *
	 * @since 2.0
	 *
	 * @param array $inputs
	 * @param array $field_options
	 * @param object $field
	 * @param array $form_posts - posts
	 * @param array $args
	 */
	private static function mod_post_inputs( &$inputs, &$field_options, $field, $form_posts, $args ) {
		if ( ! $form_posts ) {
			return;
		}

		//Declare $field_options variable.
		$field_options = $field->options;

		//if ( $skip_posts_code ) {return}//TODO: Check if this is necessary

		// If field is not a post field, return
		if ( isset( $field->field_options['post_field']) && $field->field_options['post_field'] != '' ) {
			$post_field_type = $field->field_options['post_field'];
		} else {
			return;
		}
		global $wpdb;

		// If category field
		if ( $post_field_type == 'post_category' ) {
			$field_options = FrmProFieldsHelper::get_category_options( $field );

			// If field is a custom field
		} else if ( $post_field_type == 'post_custom' && $field->field_options['custom_field'] != '' ) {
			foreach ( $form_posts as $form_post ) {
				$meta_value = get_post_meta( $form_post->post_id, $field->field_options['custom_field'], true );
				if ( $meta_value) {
					if ( $args['x_axis'] ) {
						$inputs[] = array( 'meta_value' => $meta_value, 'item_id' => $form_post->id);
					} else {
						$inputs[] = $meta_value;
					}
				}
			}
			// If regular post field
		} else{
			if ( $post_field_type == 'post_status') {
				$field_options = FrmProFieldsHelper::get_status_options( $field );
			}
			foreach ( $form_posts as $form_post ) {
				$post_value = FrmDb::get_var( $wpdb->posts, array( 'ID' => $form_post->post_id), $post_field_type );
				if ( $post_value ) {
					if ( $args['x_axis'] ) {
						$inputs[] = array( 'meta_value' => $post_value, 'item_id' => $form_post->id);
					} else {
						$inputs[] = $post_value;
					}
				}
			}
		}
	}

	/**
	 * Modify inputs for x-axis
	 * TODO: Clean this function
	 *
	 * @since 2.0
	 *
	 * @param array $x_inputs - x inputs
	 * @param array $inputs
	 * @param array $f_values - additional field values
	 * @param array $args
	 */
	private static function mod_x_inputs( &$x_inputs, &$inputs, &$f_values, $args ) {
		if ( $x_inputs ) {
			$x_temp = array();
			foreach ( $x_inputs as $x_input ) {
				if ( ! $x_input ) {
					continue;
				}

				if ( $args['x_field'] ) {
					$x_temp[$x_input['item_id']] = $x_input['meta_value'];
				} else {
					$x_temp[$x_input['id']] = $x_input[$args['x_axis']];
				}
			}
			$x_inputs = apply_filters('frm_graph_value', $x_temp, ($args['x_field'] ? $args['x_field'] : $args['x_axis']), $args);
			unset($x_temp, $x_input);
		}

		if ( $args['x_axis'] ){
			$y_temp = array();
			foreach ( $inputs as $input ) {
				$y_temp[$input['item_id']] = $input['meta_value'];
			}
			foreach ( $args['ids'] as $f_id ) {
				if ( ! isset( $f_values[ $f_id ] ) ) {
					$f_values[$f_id] = array();
				}
				$f_values[$f_id][key($y_temp)] = 0;
				unset($f_id);
			}
			$inputs = $y_temp;
			unset($y_temp, $input);
		}
	}

	/**
	 * Format additional field inputs
	 *
	 * @since 2.0
	 *
	 * @param array $f_inputs
	 * @param array $f_values
	 * @param array $args
	 */
	private static function format_f_inputs( &$f_inputs, &$f_values, $args ) {
		if ( ! $f_inputs ) {
			return;
		}
		foreach ( $f_inputs as $f_id => $f ) {
			$temp = array();
			foreach ( $f as $input ) {
				if ( is_array( $input ) ){
					$temp[$input['item_id']] = $input['meta_value'];

					foreach ( $args['ids'] as $d ) {
						if ( ! isset( $f_values[ $d ][ $input['item_id'] ] ) ) {
							$f_values[$d][$input['item_id']] = 0;
						}

						unset($d);
					}
				} else {
					$temp[] = $input;
				}
				unset($input);
			}

			$f_inputs[$f_id] = apply_filters('frm_graph_value', $temp, $args['fields'][$f_id], $args);

			unset($temp, $input, $f);
		}
	}

	/**
	 * Get values for user ID graph
	 *
	 * @since 2.0
	 *
	 * @param array $values
	 * @param array $labels
	 * @param array $tooltips
	 * @param boolean $pie - boolean for pie graph
	 * @param array $temp_values - temporary values
	 * @param object $field
	 */
	private static function get_user_id_values( &$values, &$labels, &$tooltips, &$pie, $temp_values, $field ) {
		global $wpdb;

		// Get form options
		$form = FrmDb::get_row( $wpdb->prefix .'frm_forms', array( 'id' => $field->form_id) );
		$form_options = maybe_unserialize( $form->options );

		// Remove deleted users from values and show display name instead of user ID number
		foreach ( $temp_values as $user_id => $count ) {
			$user_info = get_userdata( $user_id );
			if ( ! $user_info ) {
				unset( $temp_values[$user_id] );
				continue;
			}
			$labels[] = ($user_info) ? $user_info->display_name : __( 'Deleted User', 'formidable' );
			$values[] = $count;
		}

		// If only one response per user, do a pie chart of users who have submitted the form
		if ( isset( $form_options['single_entry'] ) && $form_options['single_entry'] && isset( $form_options['single_entry_type'] ) && $form_options['single_entry_type'] == 'user' ) {

			// Get number of users on site
			$total_users = count( get_users() );

			// Get number of users that have completed entries
			$id_count = count( $values );

			// Get the difference
			$not_completed = (int) $total_users - (int) $id_count;
			$labels = array( __( 'Completed', 'formidable' ), __( 'Not Completed', 'formidable' ) );
			$temp_values = array( $id_count, $not_completed );
			$pie = true;

		} else {
			if ( count( $labels ) < 10 ) {
				$pie = true;
			}
		}
		$values = $temp_values;
	}

	/**
	 * Get final x-axis values
	 * TODO: Clean this up, try to think of a cleaner way to get these values
	 *
	 * @since 2.0
	 *
	 * @param array $values
	 * @param array $f_values - additional field values
	 * @param array $labels
	 * @param array $tooltips
	 * @param array $inputs
	 * @param array $x_inputs - x inputs
	 * @param array $f_inputs - f inputs
	 * @param array $args - arguments
	 */
	private static function get_final_x_axis_values( &$values, &$f_values, &$labels, &$tooltips, $inputs, $x_inputs, $f_inputs, $args ){
		if ( ! isset( $x_inputs ) || ! $x_inputs ) {
			return;
		}
		$calc_array = array();

		//TODO: CHECK IF other option works with x axis
		foreach ( $inputs as $entry_id => $in ) {
			$entry_id = (int) $entry_id;
			if ( ! isset( $values[$entry_id] ) ) {
				$values[$entry_id] = 0;
			}

			$labels[$entry_id] = ( isset( $x_inputs[$entry_id] ) ) ? $x_inputs[$entry_id] : '';

			if ( ! isset( $calc_array[ $entry_id ] ) ) {
				$calc_array[$entry_id] = array( 'count' => 0);
			}

			if ( $args['data_type'] == 'total' || $args['data_type'] == 'average' ) {
				$values[$entry_id] += (float) $in;
				$calc_array[$entry_id]['total'] = $values[$entry_id];
				$calc_array[$entry_id]['count']++;
			} else {
				$values[$entry_id]++;
			}

			unset( $entry_id, $in );
		}

		//TODO: Does this even work?
		if ( $args['data_type'] == 'average' ) {
			foreach ( $calc_array as $entry_id => $calc ) {
				$values[$entry_id] = ($calc['total'] / $calc['count']);
				unset( $entry_id, $calc );
			}
		}

		$calc_array = array();
		foreach ( $f_inputs as $f_id => $f ) {
			if ( ! isset( $calc_array[$f_id] ) ) {
				$calc_array[$f_id] = array();
			}

			foreach ( $f as $entry_id => $in ) {
				$entry_id = (int) $entry_id;
				if ( ! isset( $labels[ $entry_id ] ) ) {
					$labels[$entry_id] = (isset($x_inputs[$entry_id])) ? $x_inputs[$entry_id] : '';
					$values[$entry_id] = 0;
				}

				if ( ! isset( $calc_array[ $f_id ][ $entry_id ] ) ) {
					$calc_array[$f_id][$entry_id] = array( 'count' => 0);
				}

				if ( ! isset( $f_values[ $f_id ][ $entry_id ] ) ) {
					$f_values[$f_id][$entry_id] = 0;
				}

				if ( $args['data_type'] == 'total' || $args['data_type'] == 'average' ) {
					$f_values[$f_id][$entry_id] += (float) $in;
					$calc_array[$f_id][$entry_id]['total'] = $f_values[$f_id][$entry_id];
					$calc_array[$f_id][$entry_id]['count']++;
				}else{
					$f_values[$f_id][$entry_id]++;
				}

				unset( $entry_id, $in );
			}

			unset( $f_id, $f );
		}

		if ( $args['data_type'] == 'average' ) {
			foreach ( $calc_array as $f_id => $calc ) {
				foreach ( $calc as $entry_id => $c ) {
					$f_values[$f_id][$entry_id] = ($c['total'] / $c['count']);
					unset( $entry_id, $c );
				}
				unset( $calc, $f_id );
			}
		}
		unset($calc_array);

		//TODO: Is this duplicate code?
		$used_vals = $calc_array = array();
		foreach ( $labels as $l_key => $label ) {
			if ( empty( $label ) && ( ! empty( $start_date ) || ! empty( $end_date ) ) ) {
				unset( $values[ $l_key ], $labels[ $l_key ] );
				if ( isset( $tooltips[ $l_key ] ) ) {
					unset( $tooltips[ $l_key ] );
				}
				continue;
			}

			if ( in_array( $args['x_axis'], array( 'created_at', 'updated_at' ) ) ) {
				if ( $args['type'] == 'pie' ) {
					$labels[$l_key] = $label = $inputs[$l_key];
				} else {
					$labels[$l_key] = $label = date('Y-m-d', strtotime($label));
				}
			}

			if ( isset( $used_vals[ $label ] ) ) {
				$values[ $l_key ] += $values[ $used_vals[ $label ] ];
				unset( $values[ $used_vals[ $label ] ] );

				foreach ( $args['ids'] as $f_id ) {
					if ( ! isset($f_values[ $f_id ][ $l_key ]) ) {
						$f_values[ $f_id ][ $l_key ] = 0;
					}
					if ( ! isset( $f_values[ $f_id ][ $used_vals[ $label ] ] ) ) {
						$f_values[ $f_id ][ $used_vals[ $label ] ] = 0;
					}

					$f_values[ $f_id ][ $l_key ] += $f_values[ $f_id ][ $used_vals[ $label ] ];
					unset( $f_values[ $f_id ][ $used_vals[ $label ] ], $f_id );
				}

				unset( $labels[ $used_vals[ $label ] ] );
			}
			$used_vals[$label] = $l_key;

			if ( $args['data_type'] == 'average' ) {
				if ( ! isset( $calc_array[ $label ] ) ) {
					$calc_array[ $label ] = 0;
				}
				$calc_array[ $label ] ++;
			}

			unset( $label, $l_key);
		}

		if ( ! empty( $calc_array ) ) {
			foreach ( $calc_array as $label => $calc ) {
				if ( isset( $used_vals[ $label ] ) ) {
					$values[ $used_vals[ $label ] ] = ( $values[ $used_vals[ $label ] ] / $calc);

					foreach ( $args['ids'] as $f_id ) {
						$f_values[ $f_id ][ $used_vals[ $label ] ] = ( $f_values[ $f_id ][ $used_vals[ $label ] ] / $calc );
						unset($f_id);
					}
				}

				unset( $label, $calc );
			}
		}
	}

	/**
	 * Combine dates when using created-at, updated-at, or date field on x-axis
	 *
	 * @since 2.0
	 *
	 * @param boolean $combine_dates - will be true if combining dates
	 * @param array $values
	 * @param array $labels
	 * @param array $tooltips
	 * @param array $f_values - additional field values
	 * @param array $args - arguments
	 */
	private static function combine_dates( &$combine_dates, &$values, &$labels, &$tooltips, &$f_values, $args ){
		if ( (isset( $args['x_field']) && $args['x_field'] && $args['x_field']->type == 'date') || in_array( $args['x_axis'], array( 'created_at', 'updated_at' ) ) ) {
			$combine_dates = apply_filters('frm_combine_dates', true, $args['x_field']);
		}
		if ( $combine_dates === false ) {
			return;
		}

		if ( $args['include_zero'] ) {
			$start_timestamp = empty( $args['start_date'] ) ? strtotime( '-1 month') : strtotime( $args['start_date'] );
			$end_timestamp = empty( $args['end_date'] ) ? time() : strtotime( $args['end_date'] );
			$dates_array = array();

			// Get the dates array
			for($e = $start_timestamp; $e <= $end_timestamp; $e += 60*60*24)
				$dates_array[] = date('Y-m-d', $e);

			unset($e);

			// Add the zero count days
			foreach ( $dates_array as $date_str ) {
				if ( ! in_array($date_str, $labels) ) {
					$labels[$date_str] = $date_str;
					$values[$date_str] = 0;
					foreach ( $args['ids'] as $f_id ) {
						if ( ! isset( $f_values[ $f_id ][ $date_str ] ) ) {
							$f_values[$f_id][$date_str] = 0;
						}
					}
				}
			}

			unset($dates_array, $start_timestamp, $end_timestamp);
		}

		asort($labels);

		foreach ( $labels as $l_key => $l ) {
			if ( ( ( isset( $args['x_field'] ) && $args['x_field'] && $args['x_field']->type == 'date') || in_array( $args['x_axis'], array( 'created_at', 'updated_at') ) ) && ! $args['group_by'] ) {
				if ( $args['type'] != 'pie' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $l) ) {
					$frmpro_settings = new FrmProSettings();
					$labels[$l_key] = FrmProAppHelper::convert_date($l, 'Y-m-d', $frmpro_settings->date_format);
				}
			}
			unset( $l_key, $l );
		}

		$values = FrmProAppHelper::sort_by_array($values, array_keys($labels));
		$tooltips = FrmProAppHelper::sort_by_array($tooltips, array_keys($labels));

		foreach ( $args['ids'] as $f_id ) {
			$f_values[$f_id] = FrmProAppHelper::sort_by_array($f_values[$f_id], array_keys($labels));
			$f_values[$f_id] = FrmProAppHelper::reset_keys($f_values[$f_id]);
			ksort($f_values[$f_id]);
			unset($f_id);
		}
	}

	/**
	 * Group entries by month or quarter
	 *
	 * @since 2.0
	 *
	 * @param array $values
	 * @param array $f_values - additional field values
	 * @param array $labels
	 * @param array $tooltips
	 * @param array $args - arguments
	 */
	private static function graph_by_period( &$values, &$f_values, &$labels, &$tooltips, $args ) {
		if ( ! isset( $args['group_by'] ) || ! in_array( $args['group_by'], array( 'month','quarter' ) ) ) {
			return;
		}

		$labels = FrmProAppHelper::reset_keys( $labels );
		$values = FrmProAppHelper::reset_keys( $values );

		// Loop through labels and change labels to month or quarter
		foreach ( $labels as $key => $label ) {
			if ( $args['group_by'] == 'month' ) {
				$labels[$key] = date( 'F Y', strtotime( $label ) );
			} else if ( $args['group_by'] == 'quarter' ) {
				//Convert date to Y-m-d format
				$label = date( 'Y-m-d', strtotime( $label ) );
				if ( preg_match('/-(01|02|03)-/', $label) ) {
					$labels[$key] = 'Q1 ' . date('Y', strtotime($label));
				} else if ( preg_match('/-(04|05|06)-/', $label) ) {
					$labels[$key] = 'Q2 ' . date('Y', strtotime($label));
				} else if ( preg_match('/-(07|08|09)-/', $label) ) {
					$labels[$key] = 'Q3 ' . date('Y', strtotime($label));
				} else if ( preg_match('/-(10|11|12)-/', $label) ) {
					$labels[$key] = 'Q4 ' . date('Y', strtotime($label));
				}
			}
		}

		// Combine identical labels and values
		$count = count( $labels ) - 1;
		for ( $i=0; $i<$count; $i++ ) {
			if ( $labels[$i] == $labels[$i+1] ) {
				unset($labels[$i]);
				$values[$i+1] = $values[$i] + $values[$i+1];
				unset($values[$i]);

				//Group additional field values
				foreach ( $args['ids'] as $field_id ) {
					$f_values[$field_id][$i+1] = $f_values[$field_id][$i] + $f_values[$field_id][$i+1];
					unset( $f_values[$field_id][$i], $field_id );
				}
			}
		}

		// Reset keys for additional field values
		foreach ( $args['ids'] as $field_id ) {
			$f_values[$field_id] = FrmProAppHelper::reset_keys( $f_values[$field_id] );
		}
	}

	/**
	 * Get values, labels, and tooltips for graph when multiple fields are graphed and no x axis is set
	 *
	 * @since 2.0
	 *
	 * @param array $values pass by reference
	 * @param array $labels pass by reference
	 * @param array $tooltips pass by reference
	 * @param array $args
	 */
	private static function get_multiple_id_values( &$values, &$labels, &$tooltips, $args ) {
		$type = $args['data_type'] ? $args['data_type'] : 'count';

		// Set up arguments for stats shortcode
		$stats_args = array( 'type' => $type );
		if ( $args['start_date'] ) {
			if ( $args['end_date'] ) {
				$stats_args[] = $args['start_date'] . '<created_at<' . $args['end_date'];
			} else {
				$stats_args[] = 'created_at>' . $args['start_date'];
			}
		}
		if ( $args['user_id'] !== false ) {
			$stats_args['user_id'] = $args['user_id'];
		}
		if ( $args['entry_ids'] ) {
			// frm-stats only accepts one entry ID at the moment
			$stats_args['entry_id'] = reset( $args['entry_ids'] );
		}

		//Get count/total for each field
		$count = 0;
		foreach ( $args['fields'] as $f_id => $f_data ) {
			$stats_args['id'] = $f_id;
			$values[] = FrmProStatisticsController::stats_shortcode( $stats_args );
			$labels[] = isset( $args['tooltip_label'][$count] ) ? $args['tooltip_label'][$count] : $f_data->name;
			$count++;
			unset( $f_id, $f_data );
		}

		//Make tooltips match labels
		$tooltips = $labels;
	}

	/**
	 * Get values for x-axis graph
	 *
	 * @since 2.0
	 *
	 * @param array $values
	 * @param array $f_values - values if multiple fields are graphed
	 * @param array $labels
	 * @param array $tooltips
	 * @param array $x_inputs - inputs for x-axis
	 * @param object $field
	 * @param array $args
	 */
	private static function get_x_axis_values( &$values, &$f_values, &$labels, &$tooltips, &$x_inputs, $field, $args ){
		// Get form posts. This will return empty if the form does not create posts.
		$form_posts = self::get_form_posts( $field, $args );

		// Get all inputs
		$inputs = $f_inputs = $x_inputs = $f_values = array();
		self::get_x_axis_inputs( $inputs, $f_inputs, $x_inputs, $field, $args );

		// There is no data, so don't graph
		if ( ! $inputs || ! $x_inputs ) {
			return;
		}

		// Modify post inputs
		$field_options = '';
		if ( ! $args['atts'] ) {
			self::mod_post_inputs( $inputs, $field_options, $field, $form_posts, $args );
		}

		// Modify x inputs and set up f_values - TODO: what does this really dO?
		self::mod_x_inputs( $x_inputs, $inputs, $f_values, $args );

		// Format f_inputs
		self::format_f_inputs( $f_inputs, $f_values, $args );

		// Get final x_axis values
		self::get_final_x_axis_values( $values, $f_values, $labels, $tooltips, $inputs, $x_inputs, $f_inputs, $args );
	}

	/**
	 * Get values for graph with only one field and no x-axis
	 *
	 * @since 2.0
	 *
	 * @param array $values
	 * @param array $labels
	 * @param array $tooltips
	 * @param boolean $pie - for pie graph
	 * @param object $field
	 * @param array $args
	 */
	private static function get_count_values( &$values, &$labels, &$tooltips, &$pie, $field, $args ) {
		// Get all inputs for this field
		$inputs = self::get_generic_inputs( $field, $args );

		if ( ! $inputs ) {
			return;
		}

		// Get counts for each value
		$temp_values = array_count_values( array_map( 'strtolower', $inputs ) );

		// Get displayed values ( for DFE, separate values, or Other val )
		if ( $field->type == 'data' || $field->field_options['separate_value'] || FrmField::is_option_true( $field, 'other' ) ) {
			self::get_displayed_values( $temp_values, $field );
		} else if ( $field->type == 'user_id' ) {
			self::get_user_id_values($values, $labels, $tooltips, $pie, $temp_values, $field );
			return;
		}

		// Sort values by order of field options
		if ( $args['x_order'] == 'field_opts' && in_array( $field->type, array( 'radio', 'checkbox', 'select', 'data' ) ) ) {
			self::field_opt_order_vals( $temp_values, $field );

			// Sort by descending count if x_order is set to 'desc'
		} else if ( $args['x_order'] == 'desc' ) {
			arsort( $temp_values );

			// Sort alphabetically by default
		} else {
			ksort( $temp_values );
		}

		// Get slice of array
		if ( $args['limit'] ) {
			$temp_values = array_slice( $temp_values, 0, $args['limit'] );
		}

		// Capitalize the first letter of each value
		foreach ( $temp_values as $val => $count ) {
			$new_val = ucwords( $val );
			$labels[] = $new_val;
			$values[] = $count;
		}
	}

	/**
	 * Get inputs for graph (when no x-axis is set and only one field is graphed)
	 *
	 * @since 2.0
	 *
	 * @param object $field
	 * @param array $args
	 * @return array $inputs all values for field
	 */
	private static function get_generic_inputs( $field, $args ) {
		$pass_args = array( 'entry_ids', 'user_id', 'start_date', 'end_date' );
		$meta_args = array();
		foreach ( $pass_args as $arg_key ) {
			if ( $args[ $arg_key ] !== false && $args[ $arg_key ] !== '' ) {
				$meta_args[ $arg_key ] = $args[ $arg_key ];
			}
		}
		unset( $arg_key, $pass_args );

		// Get the metas
		$inputs = FrmProEntryMeta::get_all_metas_for_field( $field, $meta_args );

		// Clean up multi-dimensional array
		self::clean_inputs( $inputs, $field, $args );

		return $inputs;
	}

	/**
	 * Order values so they match the field options order
	 *
	 * @since 2.0
	 *
	 * @param array $temp_values
	 * @param object $field
	 */
	private static function field_opt_order_vals( &$temp_values, $field ) {
		$reorder_vals = array();
		foreach ( $field->options as $opt ) {
			if ( ! $opt ) {
				continue;
			}
			if ( is_array( $opt ) ) {
				if ( ! isset( $opt['label'] ) || ! $opt['label'] ) {
					continue;
				}
				$opt = strtolower( $opt['label'] );
			} else {
				$opt = strtolower( $opt );
			}
			if ( ! isset( $temp_values[$opt] ) ) {
				continue;
			}
			$reorder_vals[$opt] = $temp_values[$opt];
		}
		$temp_values = $reorder_vals;
	}

	/**
	 * Get displayed values for separate values, data from entries, and other option.
	 * Capitalizes first letter of each option
	 *
	 * @since 2.0
	 *
	 * @param array $temp_values
	 * @param object $field
	 */
	private static function get_displayed_values( &$temp_values, $field ) {
		$temp_array = array();

		// If data from entries field
		if ( $field->type == 'data' ) {

			// Get DFE text
			foreach ( $temp_values as $entry_id => $total ) {
				$linked_field = $field->field_options['form_select'];
				$text_val = FrmProEntriesController::get_field_value_shortcode( array( 'field_id' => $linked_field, 'entry_id' => $entry_id));
				$temp_array[$text_val] = $total;
				unset( $entry_id, $total, $linked_field, $text_val );
			}
		} else {
			$other_label = false;
			foreach ( $field->options as $opt_key => $opt ) {
				if ( ! $opt ) {
					continue;
				}
				// If field option is "other" option
				if ( FrmFieldsHelper::is_other_opt( $opt_key ) ) {

					// For radio button field, combine all extra counts/totals into one "Other" count/total
					if ( $field->type == 'radio' || $field->type == 'select' ) {
						$other_label = strtolower( $opt );
						continue;

						// For checkbox fields, set value and label
					} else {
						$opt_value = strtolower( $opt_key );
						$opt_label = strtolower( $opt );
					}

					// If using separate values
				} else if ( is_array( $opt) ) {
					$opt_label = strtolower( $opt['label'] );
					$opt_value = strtolower( $opt['value'] );
					if ( ! $opt_value || ! $opt_label ) {
						continue;
					}
				} else {
					$opt_label = $opt_value = strtolower( $opt );
				}

				// Set displayed value total in new array, unset original value in old array
				if ( isset( $temp_values[$opt_value] ) ) {
					$temp_array[$opt_label] = $temp_values[$opt_value];
					unset( $temp_values[$opt_value] );
				}
				unset( $opt_key, $opt, $opt_label, $opt_value );
			}
			// Applies to radio buttons only (with other option)
			// Combines all extra counts/totals into one "Other" count/total
			if ( $other_label ) {
				$temp_array[$other_label] = array_sum( $temp_values );
			}
		}

		// Copy new array
		$temp_values = $temp_array;
	}

	public static function show_reports() {
		add_filter( 'frm_form_stop_action_reports', '__return_true' );
		FrmAppHelper::permission_check( 'frm_view_reports' );

		global $wpdb;

		$form = false;
		if ( isset($_REQUEST['form'] ) ) {
			$form = FrmForm::getOne($_REQUEST['form']);
		}

		if ( ! $form ) {
			require(FrmAppHelper::plugin_path() .'/pro/classes/views/frmpro-statistics/select.php');
			return;
		}

		$exclude_types = FrmField::no_save_fields();
		$exclude_types = array_merge($exclude_types, array(
			'rte', 'textarea', 'file', 'grid',
			'signature', 'form', 'table',
		) );

		$fields = FrmField::getAll( array( 'fi.form_id' => (int) $form->id, 'fi.type not' => $exclude_types), 'field_order');

		$js = '';
		$data = array();
		$colors = '#21759B,#EF8C08,#C6C6C6';

		$data['time'] = self::get_daily_entries($form, array(
			'is3d' => true, 'colors' => $colors, 'bg_color' => 'transparent',
		));
		$data['month'] = self::get_daily_entries($form, array(
			'is3d' => true, 'colors' => $colors, 'bg_color' => 'transparent',
			'width' => '100%',
		), 'MONTH');

		foreach ( $fields as $field ) {

			$this_data = self::graph_shortcode( array(
				'id' => $field->id, 'field' => $field, 'is3d' => true, 'min' => 0,
				'colors' => $colors, 'width' => 650, 'bg_color' => 'transparent',
			));

			if ( strpos($this_data, 'frm_no_data_graph') === false ) {
				$data[$field->id] = $this_data;
			}

			unset($field, $this_data);
		}

		$entries = FrmDb::get_col( $wpdb->prefix .'frm_items', array( 'form_id' => $form->id), 'created_at' );

		// trigger the scripts to load
		global $frm_vars;
		$frm_vars['forms_loaded'][] = true;

		include(FrmAppHelper::plugin_path() .'/pro/classes/views/frmpro-statistics/show.php');
	}

	private static function get_daily_entries( $form, $opts = array(), $type = 'DATE' ) {
		global $wpdb;

		$options = array();
		if ( isset( $opts['colors'] ) ) {
			$options['colors'] = explode(',', $opts['colors']);
		}

		if ( isset( $opts['bg_color'] ) ) {
			$options['backgroundColor'] = $opts['bg_color'];
		}

		$type = strtoupper($type);
		$end_timestamp = time();

		//Chart for Entries Submitted
		if ( $type == 'HOUR' ) {
			$start_timestamp = strtotime('-48 hours');
			$title = __( 'Hourly Entries', 'formidable' );
		} else if ( $type == 'MONTH' ) {
			$start_timestamp = strtotime('-1 year');
			$end_timestamp = strtotime( '+1 month');
			$title = __( 'Monthly Entries', 'formidable' );
		} else if ( $type == 'YEAR' ) {
			$start_timestamp = strtotime('-10 years');
			$title = __( 'Yearly Entries', 'formidable' );
		} else {
			$start_timestamp = strtotime('-1 month');
			$title = __( 'Daily Entries', 'formidable' );
		}

		$query = array(
			'form_id' => $form->id,
			'is_draft' => 0,
		);
		$args = array();
		if ( $type == 'HOUR' ) {
			$field = 'created_at';
			$query['created_at >'] = date( 'Y-m-d H', $start_timestamp ) . ':00:00';
		} else {
			$field = 'DATE(created_at)';
			$query['created_at >'] = date( 'Y-m-d', $start_timestamp ) . ' 00:00:00';
			$args['group_by'] = $type . '(created_at)';
		}

		$entries_array = FrmDb::get_results( 'frm_items', $query, $field .' as endate, COUNT(*) as encount', $args );

		$temp_array = $counts_array = $dates_array = array();

		// Refactor Array for use later on
		foreach ( $entries_array as $e ) {
			$e_key = $e->endate;
			if ( $type == 'HOUR' ) {
				$e_key = date('Y-m-d H', strtotime($e->endate)) .':00:00';
			} else if ( $type == 'MONTH' ) {
				$e_key = date('Y-m', strtotime($e->endate)) .'-01';
			} else if ( $type == 'YEAR' ) {
				$e_key = date('Y', strtotime($e->endate)) .'-01-01';
			}
			$temp_array[ $e_key ] = $e->encount;
		}

		// Get the dates array
		if ( $type == 'HOUR' ) {
			for ( $e = $start_timestamp; $e <= $end_timestamp; $e += 60*60 ) {
				if ( ! in_array(date('Y-m-d H', $e) .':00:00' , $dates_array) ) {
					$dates_array[] = date('Y-m-d H', $e) .':00:00';
				}
			}

			$date_format = get_option('time_format');
		} else if ( $type == 'MONTH' ) {
			for ( $e = $start_timestamp; $e <= $end_timestamp; $e += 60*60*24*25 ) {
				if ( ! in_array(date('Y-m', $e) .'-01', $dates_array) ) {
					$dates_array[] = date('Y-m', $e) .'-01';
				}
			}

			$date_format = 'F Y';
		} else if ( $type == 'YEAR' ) {
			for ( $e = $start_timestamp; $e <= $end_timestamp; $e += 60*60*24*364 ) {
				if ( ! in_array( date('Y', $e) .'-01-01', $dates_array ) ) {
					$dates_array[] = date('Y', $e) .'-01-01';
				}
			}

			$date_format = 'Y';
		} else {
			for ( $e = $start_timestamp; $e <= $end_timestamp; $e += 60*60*24 ) {
				$dates_array[] = date( 'Y-m-d', $e );
			}

			$date_format = get_option('date_format');
		}

		if ( empty($dates_array) ) {
			return;
		}

		// Make sure counts array is in order and includes zero click days
		foreach ( $dates_array as $date_str ) {
			if ( isset( $temp_array[ $date_str ] ) ) {
				$counts_array[ $date_str ] = $temp_array[ $date_str ];
			} else {
				$counts_array[ $date_str ] = 0;
			}
		}

		$rows = array();
		$max = 3;
		foreach ( $counts_array as $date => $count ) {
			$rows[] = array( date_i18n($date_format, strtotime($date)), (int) $count );
			if ( (int) $count > $max ) {
				$max = $count + 1;
			}
			unset( $date, $count );
		}

		$options['title'] = $title;
		$options['legend'] = 'none';
		$cols = array( 'xaxis' => array( 'type' => 'string'), __( 'Count', 'formidable' ) => array( 'type' => 'number'));

		$options['vAxis'] = array( 'maxValue' => $max, 'minValue' => 0);
		$options['hAxis'] = array( 'slantedText' => true, 'slantedTextAngle' => 20);

		$height = 400;
		$width = '100%';

		$options['height'] = $height;
		$options['width'] = $width;

		$graph = self::convert_to_google($rows, $cols, $options, 'line');

		$html = '<div id="chart_'. $graph['graph_id'] .'" style="height:'. $height .';width:'. $width .'"></div>';

		return $html;
	}
}