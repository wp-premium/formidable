<table class="form-table frm-no-margin">
    <tr>
        <th>
			<label><?php esc_html_e( 'Post Type', 'formidable-pro' ); ?></label>
			<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'To setup a new custom post type, install and setup a plugin like \'Custom Post Type UI\', then return to this page to select your new custom post type.', 'formidable-pro' ) ?>" ></span>
        </th>
        <td>
            <select class="frm_post_type" name="<?php echo esc_attr( $this->get_field_name('post_type') ) ?>">
				<?php
				foreach ( $post_types as $post_key => $post_type ) {
					if ( in_array( $post_key, array( 'frm_display', 'frm_form_actions', 'frm_styles' ) ) ) {
						continue;
					}
					$expected_post_key = sanitize_title_with_dashes( $post_type->label );
					$hide_key = ( $post_type->_builtin || $expected_post_key == $post_key || $expected_post_key == $post_key . 's' );
				?>
					<option value="<?php echo esc_attr( $post_key ) ?>" <?php selected( $form_action->post_content['post_type'], $post_key ) ?>>
						<?php echo esc_html( $post_type->label . ( $hide_key ? '' : ' (' . $post_key . ')' ) ); ?>
					</option>
<?php
					unset( $post_type );
				}

				unset( $post_types );
				?>
            </select>
        </td>
    </tr>
        <?php
        if ( empty($form_action->post_content['post_category']) && ! empty($values['fields']) ) {
            foreach ( $values['fields'] as $fo_key => $fo ) {
				if ( $fo['post_field'] == 'post_category' ) {
                    if ( ! isset($fo['taxonomy']) || $fo['taxonomy'] == '' ) {
                        $fo['taxonomy'] = 'post_category';
                    }

                    $tax_count = FrmProFormsHelper::get_taxonomy_count($fo['taxonomy'], $form_action->post_content['post_category']);

					$form_action->post_content['post_category'][ $fo['taxonomy'] . $tax_count ] = array(
						'field_id'    => $fo['id'],
						'exclude_cat' => $fo['exclude_cat'],
						'meta_name'   => $fo['taxonomy'],
					);
                    unset($tax_count);
                } else if ( $fo['post_field'] == 'post_custom' && ! in_array( $fo['custom_field'], $custom_fields ) ) {
					$form_action->post_content['post_custom_fields'][ $fo['custom_field'] ] = array(
						'field_id'  => $fo['id'],
						'meta_name' => $fo['custom_field'],
					);
                }
                unset($fo_key, $fo);
            }
        }
        ?>
        <tr>
            <th>
				<label><?php esc_html_e( 'Post Title', 'formidable-pro' ); ?> <span class="frm_required">*</span></label>
            </th>
            <td><select name="<?php echo esc_attr( $this->get_field_name('post_title') ) ?>" class="frm_single_post_field">
				<option value=""><?php esc_html_e( '&mdash; Select &mdash;' ); ?></option>
				<?php
				$post_key = 'post_title';
				$post_field = array( 'text', 'email', 'url', 'radio', 'checkbox', 'select', 'scale', 'star', 'number', 'phone', 'time', 'hidden' );
				include( dirname( __FILE__ ) . '/_post_field_options.php' );
				unset( $post_field );
				?>
                </select>
            </td>
        </tr>

        <tr>
            <th>
				<label><?php esc_html_e( 'Post Content', 'formidable-pro' ); ?></label>
            </th>
            <td>
                <select class="frm_toggle_post_content">
					<option value=""><?php esc_html_e( '&mdash; Select &mdash;' ); ?></option>
					<option value="post_content" <?php echo is_numeric( $form_action->post_content['post_content'] ) ? 'selected="selected"' : ''; ?>>
						<?php esc_html_e( 'Use a single field', 'formidable-pro' ); ?>
					</option>
					<option value="dyncontent" <?php echo ( $display ? 'selected="selected"' : '' ); ?>>
						<?php esc_html_e( 'Customize post content', 'formidable-pro' ); ?>
					</option>
                </select>

                <select name="<?php echo esc_attr( $this->get_field_name('post_content') ) ?>" class="frm_post_content_opt frm_single_post_field <?php echo esc_attr( $display || empty($form_action->post_content['post_content']) ) ? 'frm_hidden' : ''; ?>">
					<option value=""><?php esc_html_e( '&mdash; Select &mdash;' ); ?></option>
                    <?php
                    $post_key = 'post_content';
					include( dirname( __FILE__ ) . '/_post_field_options.php' );
                    ?>
                </select>

                <select name="<?php echo esc_attr( $this->get_field_name('display_id') ) ?>" class="frm_dyncontent_opt <?php echo ( $display ? '' : 'frm_hidden' ); ?>">
					<option value=""><?php esc_html_e( '&mdash; Select &mdash;' ); ?></option>
					<option value="new"><?php esc_html_e( 'Create new view', 'formidable-pro' ); ?></option>
                    <?php foreach ( $displays as $d ) { ?>
					<option value="<?php echo absint( $d->ID ) ?>" <?php
						if ( $display ) {
							selected( $d->ID, $display->ID );
						}
						?>>
						<?php echo esc_html( stripslashes( $d->post_title ) ) ?>
					</option>
                    <?php } ?>
                </select>
            </td>
        </tr>
        <tr class="frm_dyncontent_opt <?php echo esc_attr( $display ? '' : 'frm_hidden' ); ?>">
            <td colspan="2">
				<label><?php esc_html_e( 'Customize Content', 'formidable-pro' ); ?></label>
				<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'The content shown on your single post page. If nothing is entered here, the regular post content will be used.', 'formidable-pro' ) ?>" ></span><br/>
				<textarea id="frm_dyncontent" placeholder="<?php esc_attr_e( 'Add text, HTML, and fields from your form to build your post content.', 'formidable-pro' ) ?>" name="dyncontent" rows="10" class="frm_not_email_message large-text"><?php
                if ( $display ) {
                    echo FrmAppHelper::esc_textarea($display->frm_show_count == 'one' ? $display->post_content : $display->frm_dyncontent);
                }
                ?></textarea>
				<p class="howto"><?php esc_html_e( 'Editing this box will update your existing view or create a new one.', 'formidable-pro' ); ?></p>
            </td>
        </tr>

        <tr>
            <th>
				<label><?php esc_html_e( 'Excerpt', 'formidable-pro' ); ?></label>
            </th>
            <td><select name="<?php echo esc_attr( $this->get_field_name('post_excerpt') ) ?>" class="frm_single_post_field">
				<option value=""><?php esc_html_e( 'None', 'formidable-pro' ); ?></option>
				<?php
				$post_key = 'post_excerpt';
				include( dirname( __FILE__ ) . '/_post_field_options.php' );
				?>
                </select>
            </td>
        </tr>

        <tr>
			<td>
				<label><?php esc_html_e( 'Post Password', 'formidable-pro' ); ?></label>
			</td>
            <td><select name="<?php echo esc_attr( $this->get_field_name('post_password') ) ?>" class="frm_single_post_field">
				<option value=""><?php echo esc_html_e( 'None', 'formidable-pro' ); ?></option>
				<?php
				$post_key = 'post_password';
				include( dirname( __FILE__ ) . '/_post_field_options.php' );
				?>
                </select>
            </td>
        </tr>

        <tr>
			<td><label><?php esc_html_e( 'Slug', 'formidable-pro' ); ?></label></td>
            <td><select name="<?php echo esc_attr( $this->get_field_name('post_name') ) ?>" class="frm_single_post_field">
				<option value=""><?php esc_html_e( 'Automatically Generate from Post Title', 'formidable-pro' ); ?></option>
				<?php
				$post_key = 'post_name';
                include( dirname( __FILE__ ) . '/_post_field_options.php' );
				?>
                </select>
            </td>
        </tr>

        <tr>
			<td><label><?php esc_html_e( 'Post Date', 'formidable-pro' ); ?></label></td>
            <td><select name="<?php echo esc_attr( $this->get_field_name('post_date') ) ?>" class="frm_single_post_field">
				<option value=""><?php esc_html_e( 'Date of entry submission', 'formidable-pro' ); ?></option>
				<?php
				$post_key = 'post_date';
				$post_field = array( 'date' );
				include( dirname( __FILE__ ) . '/_post_field_options.php' );
				?>
                </select>
            </td>
        </tr>

        <tr>
			<td><label><?php esc_html_e( 'Post Status', 'formidable-pro' ); ?></label></td>
            <td><select name="<?php echo esc_attr( $this->get_field_name('post_status') ) ?>" class="frm_single_post_field">
				<option value=""><?php echo esc_html_e( 'Create Draft', 'formidable-pro' ); ?></option>
				<option value="pending" <?php selected( $form_action->post_content['post_status'], 'pending' ) ?>>
					<?php esc_html_e( 'Pending', 'formidable-pro' ); ?>
				</option>
				<option value="publish" <?php selected( $form_action->post_content['post_status'], 'publish' ); ?>>
					<?php esc_html_e( 'Automatically Publish', 'formidable-pro' ); ?>
				</option>
				<option value="dropdown">
					<?php echo esc_html_e( 'Create New Dropdown Field', 'formidable-pro' ); ?>
				</option>
                <?php
				$post_key = 'post_status';
				$post_field = array( 'select', 'radio', 'hidden' );
				include( dirname( __FILE__ ) . '/_post_field_options.php' );
				?>
                </select>
            </td>
        </tr>

        <?php
        unset($post_field, $post_key);
        ?>


        <tr>
            <td colspan="2">
				<h3>
					<?php esc_html_e( 'Taxonomies/Categories', 'formidable-pro' ); ?>
					<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Select the field(s) from your form that you would like to populate with your categories, tags, or other taxonomies.', 'formidable-pro' ); ?>"></span>
				</h3>
                <div id="frm_posttax_rows" class="frm_add_remove" style="padding-bottom:8px;">
                <?php
                $tax_key = 0;
                foreach ( $form_action->post_content['post_category'] as $field_vars ) {
					include( dirname( __FILE__ ) . '/_post_taxonomy_row.php' );
                    $tax_key++;
                    unset($field_vars);
                }
                ?>
                </div>

                <p><a href="javascript:void(0)" class="frm_add_posttax_row button <?php echo esc_attr( empty( $form_action->post_content['post_category'] ) ? '' : 'frm_hidden' ) ?>">+ <?php esc_html_e( 'Add' ); ?></a></p>

				<h3>
					<?php esc_html_e( 'Custom Fields', 'formidable-pro' ); ?>
					<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'To set the featured image, use \'_thumbnail_id\' as the custom field name.', 'formidable-pro' ); ?>"></span>
				</h3>
                <div id="postcustomstuff" class="frm_add_remove frm_name_value<?php echo empty($form_action->post_content['post_custom_fields']) ? ' frm_hidden' : ''; ?>">
                <table id="list-table">
                    <thead>
                    <tr>
						<th class="left"><?php esc_html_e( 'Name', 'formidable-pro' ); ?></th>
						<th><?php esc_html_e( 'Value', 'formidable-pro' ); ?></th>
						<th style="width:35px;"></th>
                    </tr>
                    </thead>

                    <tbody id="frm_postmeta_rows" data-wp-lists="list:meta">

                <?php
                foreach ( $form_action->post_content['post_custom_fields'] as $custom_data ) {
					if ( isset( $custom_data['meta_name'] ) && ! empty( $custom_data['meta_name'] ) ) {
						include( dirname( __FILE__ ) . '/_custom_field_row.php' );
					}
                    unset($custom_data);
                }
                ?>
                    </tbody>
                </table>
                </div>

				<p><a href="javascript:void(0)" class="frm_add_postmeta_row button <?php echo esc_attr( empty( $form_action->post_content['post_custom_fields'] ) ? '' : 'frm_hidden' ) ?>">+ <?php esc_html_e( 'Add' ); ?></a></p>
            </td>
        </tr>
</table>
