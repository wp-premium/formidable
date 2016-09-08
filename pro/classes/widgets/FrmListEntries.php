<?php

class FrmListEntries extends WP_Widget {

	function __construct() {
		$widget_ops = array( 'description' => __( 'Display a list of Formidable entries', 'formidable' ) );
		parent::__construct( 'frm_list_items', __( 'Formidable Entries List', 'formidable' ), $widget_ops );
	}

	function widget( $args, $instance ) {
        global $wpdb;

        $display = FrmProDisplay::getOne($instance['display_id'], false, true);

		$title = apply_filters( 'widget_title', ( empty( $instance['title'] ) && $display ) ? $display->post_title : $instance['title'] );
        $limit = empty($instance['limit']) ? ' LIMIT 100' : " LIMIT {$instance['limit']}";
        $post_id = $instance['post_id'];
        $page_url = get_permalink($post_id);

        $order_by = '';
        $cat_field = false;

        if ( $display && is_numeric($display->frm_form_id) && ! empty($display->frm_form_id) ) {

				//Set up order for Entries List Widget
                if ( isset($display->frm_order_by) && ! empty($display->frm_order_by) ) {
					//Get only the first order field and order
					$order_field = reset($display->frm_order_by);
					$order = reset($display->frm_order);
					FrmAppHelper::esc_order_by( $order );

					if ( $order_field == 'rand' ) {
						//If random is set, set the order to random
					    $order_by = ' RAND()';
					} else if ( is_numeric( $order_field ) ) {
						//If ordering by a field

						//Get all post IDs for this form
                        $posts = FrmDb::get_results( $wpdb->prefix .'frm_items', array( 'form_id' => $display->frm_form_id, 'post_id >' => 1, 'is_draft' => 0), 'id, post_id' );
			            $linked_posts = array();
			           	foreach ( $posts as $post_meta ) {
			            	$linked_posts[ $post_meta->post_id ] = $post_meta->id;
			            }

						//Get all field information
						$o_field = FrmField::getOne($order_field);
						$query = 'SELECT m.id FROM ' . $wpdb->prefix . 'frm_items m INNER JOIN ';
						$where = array();

						//create query with ordered values
						//if field is some type of post field
						if ( isset( $o_field->field_options['post_field'] ) && $o_field->field_options['post_field'] ) {

							if ( $o_field->field_options['post_field'] == 'post_custom' && ! empty($linked_posts) ) {
								//if field is custom field
								$where['pm.post_id'] = array_keys( $linked_posts );
								FrmDb::get_where_clause_and_values( $where );
								array_unshift( $where['values'], $o_field->field_options['custom_field'] );

								$query .= $wpdb->postmeta . ' pm ON pm.post_id=m.post_id AND pm.meta_key=%s ' . $where['where'] . ' ORDER BY CASE when pm.meta_value IS NULL THEN 1 ELSE 0 END, pm.meta_value ' . $order;
							} else if ( $o_field->field_options['post_field'] != 'post_category' && ! empty($linked_posts) ) {
								//if field is a non-category post field
								$where['p.ID'] = array_keys( $linked_posts );
								FrmDb::get_where_clause_and_values( $where );

								$query .= $wpdb->posts . ' p ON p.ID=m.post_id ' . $where['where'] . ' ORDER BY CASE p.' . sanitize_title( $o_field->field_options['post_field'] ) . ' WHEN "" THEN 1 ELSE 0 END, p.' . sanitize_title( $o_field->field_options['post_field'] ) . ' ' . $order;
							}
						} else {
						    //if field is a normal, non-post field
							$where['em.field_id'] = $o_field->id;
							FrmDb::get_where_clause_and_values( $where );

							$query .= $wpdb->prefix . 'frm_item_metas em ON em.item_id=m.id ' . $where['where'] . ' ORDER BY CASE when em.meta_value IS NULL THEN 1 ELSE 0 END, em.meta_value' . ( $o_field->type == 'number' ? ' +0 ' : '' ) . ' '. $order;
						}

						//Get ordered values
						if ( ! empty( $where ) ) {
							$metas = $wpdb->get_results( $wpdb->prepare( $query, $where['values'] ) );
						} else {
						    $metas = false;
						}
						unset( $query, $where );

                        if ( ! empty($metas) ) {
							$order_by_array = array();
							foreach ( $metas as $meta ) {
								$order_by_array[] = $wpdb->prepare( 'it.id=%d DESC', $meta->id );
							}

							$order_by = implode( ', ', $order_by_array );
							unset( $order_by_array );
                        } else {
                            $order_by .= 'it.created_at '. $order;
						}
						unset( $metas );
					} else if ( ! empty( $order_field ) ) {
						//If ordering by created_at or updated_at
						$order_by = 'it.' . sanitize_title( $order_field ) . ' ' . $order;
					}

					if ( ! empty( $order_by ) ) {
                        $order_by = ' ORDER BY '. $order_by;
                    }
                }

                if ( isset($instance['cat_list']) && (int) $instance['cat_list'] == 1 && is_numeric($instance['cat_id']) ) {
                    if ($cat_field = FrmField::getOne($instance['cat_id']))
                        $categories = maybe_unserialize($cat_field->options);
                }

        }

		echo $args['before_widget'];
		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

        echo '<ul id="frm_entry_list'. ( $display ? $display->frm_form_id : '' ) .'">'. "\n";

		//if Listing entries by category
        if ( isset($instance['cat_list']) && (int) $instance['cat_list'] == 1 && isset($categories) && is_array($categories) ) {
			foreach ( $categories as $cat_order => $cat ) {
				if ( $cat == '' ) {
					continue;
				}
                echo '<li>';

                if ( isset($instance['cat_name']) && (int) $instance['cat_name'] == 1 && $cat_field ) {
					echo '<a href="' . esc_url( add_query_arg( array( 'frm_cat' => $cat_field->field_key, 'frm_cat_id' => $cat_order ), $page_url ) ) . '">';
                }

                echo esc_html( $cat );

                if ( isset($instance['cat_count']) && (int) $instance['cat_count'] == 1 ) {
					echo ' ('. FrmProStatisticsController::stats_shortcode( array( 'id' => $instance['cat_id'], 'type' => 'count', 'value' => $cat ) ) .')';
                }

                if ( isset($instance['cat_name']) && (int) $instance['cat_name'] == 1 ) {
                    echo '</a>';
                }else{
                    $entry_ids = FrmEntryMeta::getEntryIds( array('meta_value like' => $cat, 'fi.id' => $instance['cat_id'] ) );
                    $items = false;
					if ( $entry_ids ) {
						$items = FrmEntry::getAll( array( 'it.id' => $entry_ids, 'it.form_id' => (int) $display->frm_form_id ), $order_by, $limit );
					}

					if ( $items ) {
                        echo '<ul>';
						foreach ( $items as $item ) {
                            $url_id = $display->frm_type == 'id' ? $item->id : $item->item_key;
                            $current = ( FrmAppHelper::simple_get( $display->frm_param ) == $url_id ) ? ' class="current_page"' : '';

							if ( $item->post_id ) {
								$entry_link = get_permalink( $item->post_id );
							} else {
								$entry_link = add_query_arg( array( $display->frm_param => $url_id ), $page_url );
							}

							echo '<li' . $current . '><a href="' . esc_url( $entry_link ) . '">' . FrmAppHelper::kses( $item->name ) . '</a></li>' . "\n";
                        }
                        echo '</ul>';
                    }
                }
                echo '</li>';
             }
		} else { // if not listing entries by category
			if ( $display ) {
				$items = FrmEntry::getAll( array( 'it.form_id' => $display->frm_form_id, 'is_draft' => '0' ), $order_by, $limit );
			} else {
				$items = array();
			}

			foreach ( $items as $item ) {
				$url_id = $display->frm_type == 'id' ? $item->id : $item->item_key;
				$current = ( FrmAppHelper::simple_get( $display->frm_param ) == $url_id ) ? ' class="current_page"' : '';

				echo '<li' . $current . '><a href="' . esc_url( add_query_arg( array( $display->frm_param => $url_id ), $page_url ) ) . '">' . FrmAppHelper::kses( $item->name ) . '</a></li>' . "\n";
              }
		  }

		  echo "</ul>\n";

		  echo $args['after_widget'];
	  }

	  function update( $new_instance, $old_instance ) {
		  return $new_instance;
	  }

	  function form( $instance ) {
		  $pages = get_posts( array( 'post_type' => 'page', 'post_status' => 'publish', 'numberposts' => 999, 'order_by' => 'post_title', 'order' => 'ASC' ) );

		  $displays = FrmProDisplay::getAll( array( 'meta_key' => 'frm_show_count', 'meta_value' => 'dynamic' ) );

		  //Defaults
		  $instance = wp_parse_args( (array) $instance, array( 'title' => false, 'display_id' => false, 'post_id' => false, 'title_id' => false, 'cat_list' => false, 'cat_name' => false, 'cat_count' => false, 'cat_id' => false, 'limit' => false ) );

		  if ( $instance['display_id'] ) {
			  $selected_display = FrmProDisplay::getOne( $instance['display_id'] );
			  if ( $selected_display ) {
				  $selected_form_id = get_post_meta( $selected_display->ID, 'frm_form_id', true );

				  $title_opts = FrmField::getAll( array( 'fi.form_id' => (int) $selected_form_id, 'type not' => FrmField::no_save_fields() ), 'field_order' );
				  $instance['display_id'] = $selected_display->ID;
			  }
		  }
?>
	<p><label for="<?php echo esc_attr( $this->get_field_id('title') ); ?>"><?php _e( 'Title', 'formidable' ) ?>:</label>
	<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>" name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" value="<?php echo esc_attr( stripslashes($instance['title']) ); ?>" /></p>

	<p><label for="<?php echo esc_attr( $this->get_field_id('display_id') ); ?>"><?php _e( 'Use Settings from View', 'formidable' ) ?>:</label>
	    <select name="<?php echo esc_attr( $this->get_field_name('display_id') ); ?>" id="<?php echo esc_attr( $this->get_field_id('display_id') ); ?>" class="widefat frm_list_items_display_id">
	        <option value=""> </option>
            <?php
			foreach ( $displays as $display ) {
				echo '<option value="' . esc_attr( $display->ID ) . '" ' . selected( $instance['display_id'], $display->ID, false ) . '>' . esc_html( $display->post_title ) . '</option>';
			}
            ?>
        </select>
	</p>
	<p class="description"><?php _e( 'Views with a "Both (Dynamic)" format will show here.', 'formidable' ) ?></p>

	<p><label for="<?php echo esc_attr( $this->get_field_id('post_id') ); ?>"><?php _e( 'Page', 'formidable' ) ?>:</label>
        <select name="<?php echo esc_attr( $this->get_field_name('post_id') ); ?>" id="<?php echo esc_attr( $this->get_field_id('post_id') ); ?>" class="widefat">
	        <option value=""> </option>
            <?php
			foreach ( $pages as $page ) {
				echo '<option value="' . esc_attr( $page->ID ) . '" ' . selected( $instance['post_id'], $page->ID, false ) . '>' . esc_html( $page->post_title ) . '</option>';
			}
            ?>
        </select>
    </p>

    <p><label for="<?php echo esc_attr( $this->get_field_id('title_id') ); ?>"><?php _e( 'Title Field', 'formidable' ) ?>:</label>
        <select name="<?php echo esc_attr( $this->get_field_name('title_id') ); ?>" id="<?php echo esc_attr( $this->get_field_id('title_id') ); ?>" class="widefat frm_list_items_title_id">
	        <option value=""> </option>
            <?php
            if ( isset($title_opts) && $title_opts ) {
                foreach ( $title_opts as $title_opt ) {
                    if ( $title_opt->type != 'checkbox' ) { ?>
                        <option value="<?php echo absint( $title_opt->id ) ?>" <?php selected( $instance['title_id'], $title_opt->id ) ?>><?php echo esc_html( $title_opt->name ) ?></option>
                        <?php
                    }
                }
            }
            ?>
        </select>
	</p>

    <p><label for="<?php echo esc_attr( $this->get_field_id('cat_list') ); ?>"><input class="checkbox frm_list_items_cat_list" type="checkbox" <?php checked($instance['cat_list'], true) ?> id="<?php echo esc_attr( $this->get_field_id('cat_list') ); ?>" name="<?php echo esc_attr( $this->get_field_name('cat_list') ); ?>" value="1" />
	<?php _e( 'List Entries by Category', 'formidable' ) ?></label></p>

    <div id="<?php echo esc_attr( $this->get_field_id('hide_cat_opts') ); ?>" class="frm_list_items_hide_cat_opts <?php echo ( $instance['cat_list'] ) ? '' : 'frm_hidden'; ?>">
    <p><label for="<?php echo esc_attr( $this->get_field_id('cat_id') ); ?>"><?php _e( 'Category Field', 'formidable' ) ?>:</label>
	    <select name="<?php echo esc_attr( $this->get_field_name('cat_id') ); ?>" id="<?php echo esc_attr( $this->get_field_id('cat_id') ); ?>" class="widefat frm_list_items_cat_id">
	        <option value=""> </option>
	        <?php
            if ( isset($title_opts) && $title_opts ) {
				foreach ( $title_opts as $title_opt ) {
					if ( in_array( $title_opt->type, array( 'select', 'radio', 'checkbox' ) ) ) {
						echo '<option value="' . esc_attr( $title_opt->id ) . '"' . selected( $instance['cat_id'], $title_opt->id, false ) . '>' . FrmAppHelper::kses( $title_opt->name ) . '</option>';
					}
                }
            }
            ?>
        </select>
	</p>

	<p><label for="<?php echo esc_attr( $this->get_field_id('cat_count') ); ?>"><input class="checkbox" type="checkbox" <?php checked($instance['cat_count'], true) ?> id="<?php echo esc_attr( $this->get_field_id('cat_count') ); ?>" name="<?php echo esc_attr( $this->get_field_name('cat_count') ); ?>" value="1" />
	<?php _e( 'Show Entry Counts', 'formidable' ) ?></label></p>

	<p><input class="checkbox" type="radio" <?php checked($instance['cat_name'], 1) ?> id="<?php echo esc_attr( $this->get_field_id('cat_name') ); ?>" name="<?php echo esc_attr( $this->get_field_name('cat_name') ); ?>" value="1" />
	<label for="<?php echo esc_attr( $this->get_field_id('cat_name') ); ?>"><?php _e( 'Show Only Category Name', 'formidable' ) ?></label><br/>

	<input class="checkbox" type="radio" <?php checked($instance['cat_name'], 0) ?> id="<?php echo esc_attr( $this->get_field_id('cat_name') ); ?>" name="<?php echo esc_attr( $this->get_field_name('cat_name') ); ?>" value="0" />
	<label for="<?php echo esc_attr( $this->get_field_id('cat_name') ); ?>"><?php _e( 'Show Entries Beneath Categories', 'formidable' ) ?></label></p>
	</div>

	<p><label for="<?php echo esc_attr( $this->get_field_id('limit') ); ?>"><?php _e( 'Entry Limit (leave blank to list all)', 'formidable' ) ?>:</label>
	<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id('limit') ); ?>" name="<?php echo esc_attr( $this->get_field_name('limit') ); ?>" value="<?php echo esc_attr( $instance['limit'] ); ?>" /></p>

<?php
	}
}
