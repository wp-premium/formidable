<?php wp_nonce_field('frm_save_display_nonce', 'frm_save_display'); ?>

<table class="form-table frm-no-margin">
    <tr class="limit_container <?php echo ( $post->frm_show_count == 'calendar' || $post->frm_show_count == 'one' ) ? 'frm_hidden' : ''; ?>">
        <td class="frm_left_label">
			<label><?php esc_html_e( 'Limit', 'formidable-pro' ); ?>
				<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'If you don’t want all your entries displayed, you can insert the number limit here. Leave blank if you’d like all entries shown.', 'formidable-pro' ) ?>"></span>
			</label>
        </td>
        <td>
            <input type="text" id="limit" name="options[limit]" value="<?php echo esc_attr($post->frm_limit) ?>" size="4" />
        </td>
    </tr>

    <tr class="limit_container <?php echo ( $post->frm_show_count == 'calendar' || $post->frm_show_count == 'one' ) ? 'frm_hidden' : ''; ?>">
        <td>
			<label><?php esc_html_e( 'Page Size', 'formidable-pro' ); ?>
				<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'The number of entries to show per page. Leave blank to not use pagination.', 'formidable-pro' ) ?>"></span>
			</label>
        </td>
        <td>
            <input type="text" id="limit" name="options[page_size]" value="<?php echo esc_attr($post->frm_page_size) ?>" size="4" />
        </td>
    </tr>
</table>

<h3><?php esc_html_e( 'Sort & Filter', 'formidable-pro' ); ?></h3>
<table class="form-table frm-no-margin">
    <tr class="form-field" id="order_by_container">
		<td class="frm_left_label"><?php esc_html_e( 'Order', 'formidable-pro' ); ?></td>
        <td>
            <div id="frm_order_options" class="frm_repeat_rows" style="padding-bottom:8px;">
				<a href="javascript:void(0)" class="frm_add_order_row button <?php echo esc_attr( empty( $post->frm_order_by ) ? '' : 'frm_hidden' ); ?>">+ <?php esc_html_e( 'Add', 'formidable-pro' ); ?></a>
                <div class="frm_logic_rows frm_add_remove">
            <?php
			foreach ( $post->frm_order_by as $order_key => $order_by_field ) {
				if ( isset( $post->frm_order[ $order_key ] ) && isset( $post->frm_order_by[ $order_key ] ) ) {
                	FrmProDisplaysController::add_order_row( $order_key, $post->frm_form_id, $order_by_field, $post->frm_order[ $order_key ] );
				}
			}
            ?>
                </div>
            </div>
        </td>
    </tr>

    <tr class="form-field" id="where_container">
		<td><?php esc_html_e( 'Filter Entries', 'formidable-pro' ); ?>
            <span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Narrow down which entries will be used. The Unique options uses SQL GROUP BY to make sure only one entry is shown for each value in the selected field(s).', 'formidable-pro' ) ?>"></span>
        </td>
        <td>
            <div id="frm_where_options" class="frm_repeat_rows">
				<a href="javascript:void(0)" class="frm_add_where_row button <?php echo esc_attr( empty( $post->frm_where ) ? '' : 'frm_hidden' ); ?>">+ <?php esc_html_e( 'Add', 'formidable-pro' ); ?></a>
                <div class="frm_logic_rows frm_add_remove">
            <?php
				foreach ( $post->frm_where as $where_key => $where_field ) {
					if ( isset( $post->frm_where_is[ $where_key ] ) && isset( $post->frm_where_val[ $where_key ] ) ) {
						FrmProDisplaysController::add_where_row( $where_key, $post->frm_form_id, $where_field, $post->frm_where_is[ $where_key ], $post->frm_where_val[ $where_key ] );
					}
                }
            ?>
                </div>
            </div>
        </td>
    </tr>

    <tr class="form-field">
		<td><?php esc_html_e( 'No Entries Message', 'formidable-pro' ); ?></td>
        <td>
            <textarea id="empty_msg" name="options[empty_msg]" class="frm_98_width"><?php echo FrmAppHelper::esc_textarea($post->frm_empty_msg) ?></textarea>
        </td>
    </tr>
</table>

<?php if ( is_multisite() && current_user_can( 'setup_network' ) ) { ?>
<h3><?php esc_html_e( 'Advanced', 'formidable-pro' ); ?></h3>
<?php } ?>

<table class="form-table frm-no-margin">
    <tr class="hide_dyncontent <?php echo in_array( $post->frm_show_count, array( 'dynamic', 'calendar' ) ) ? '' : 'frm_hidden'; ?>">
        <td><?php esc_html_e( 'Detail Page Slug', 'formidable-pro' ); ?>
			<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php printf( esc_html__( 'Example: If parameter name is \'contact\', the url would be like %1$s/selected-page?contact=2. If this entry is linked to a post, the post permalink will be used instead.', 'formidable-pro' ), esc_html( FrmAppHelper::site_url() ) ); ?>" ></span>
		</td>
        <td>
                <input type="text" id="param" name="param" value="<?php echo esc_attr($post->frm_param) ?>">

				<?php esc_html_e( 'Parameter Value', 'formidable-pro' ); ?>:
                <select id="type" name="type">
					<option value="id" <?php selected( $post->frm_type, 'id' ); ?>>
						<?php esc_html_e( 'ID', 'formidable-pro' ); ?>
					</option>
					<option value="display_key" <?php selected( $post->frm_type, 'display_key' ); ?>>
						<?php esc_html_e( 'Key', 'formidable-pro' ); ?>
					</option>
                </select>
            <?php //} ?>
        </td>
    </tr>

    <?php
	if ( is_multisite() ) {
		if ( current_user_can( 'setup_network' ) ) {
		?>
        <tr class="form-field">
			<td><?php esc_html_e( 'Copy', 'formidable-pro' ); ?></td>
            <td>
				<label for="copy">
					<input type="checkbox" id="copy" name="options[copy]" value="1" <?php checked( $post->frm_copy, 1 ); ?> />
					<?php esc_html_e( 'Copy these display settings to other blogs when Formidable Pro is activated. <br/>Note: Use only field keys in the content box(es) above.', 'formidable-pro' ); ?>
				</label>
            </td>
        </tr>
		<?php } else if ( $post->frm_copy ) { ?>
        <input type="hidden" id="copy" name="options[copy]" value="1" />
		<?php
        }
	}
	?>

</table>
