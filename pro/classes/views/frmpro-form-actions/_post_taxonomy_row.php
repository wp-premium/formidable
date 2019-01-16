<?php
if ( ! isset($tax_meta) ) {
    $tax_meta = $field_vars['meta_name'] . $tax_key;
}

$selected_type = '';
?>
<div id="frm_posttax_<?php echo esc_attr( $tax_meta ) ?>" class="frm_posttax_row">
    <?php if ( isset($taxonomies) && $taxonomies ) { ?>
       <select name="<?php echo esc_attr( $action_control->get_field_name('post_category') ) ?>[<?php echo esc_attr( $tax_meta ) ?>][meta_name]" class="frm_tax_selector">
		   <option value=""><?php esc_html_e( '&mdash; Select a Taxonomy &mdash;', 'formidable-pro' ); ?></option>
		<?php foreach ( $taxonomies as $taxonomy ) { ?>
           <option value="<?php echo esc_attr( $taxonomy ) ?>" <?php selected( $field_vars['meta_name'], $taxonomy ) ?>>
			   <?php echo esc_html( str_replace( array( '_', '-' ), ' ', ucfirst( $taxonomy ) ) ); ?>
		   </option>
		<?php
			unset( $taxonomy );
		}
		?>
       </select>
    <?php } ?>

    <select name="<?php echo esc_attr( $action_control->get_field_name('post_category') ) ?>[<?php echo esc_attr( $tax_meta ) ?>][field_id]" class="frm_single_post_field">
		<option value=""><?php esc_html_e( '&mdash; Select a Field &mdash;', 'formidable-pro' ); ?></option>
		<option value="checkbox" <?php selected( $field_vars['field_id'], 'checkbox' ); ?>>
			<?php esc_html_e( 'A New Checkbox Field', 'formidable-pro' ); ?>
		</option>
        <?php
        if ( ! empty( $values['fields'] ) ) {
		foreach ( $values['fields'] as $fo ) {
			if ( is_object( $fo ) ) {
                $fo->field_options = maybe_unserialize($fo->field_options);
                if ( isset($fo->field_options['form_select']) ) {
                    $fo->form_select = $fo->field_options['form_select'];
                }
                $fo = (array) $fo;
            }

			if ( in_array( $fo['type'], array( 'checkbox', 'radio', 'select', 'tag' ) ) || ( $fo['type'] == 'data' && isset( $fo['form_select'] ) && $fo['form_select'] == 'taxonomy' ) ) {
				?>
				<option value="<?php echo esc_attr( $fo['id'] ) ?>" <?php selected( $field_vars['field_id'], $fo['id'] ) ?>>
					<?php echo FrmAppHelper::truncate($fo['name'], 50) ?>
				</option>
				<?php

                if ( $field_vars['field_id'] == $fo['id'] ) {
                    $selected_type = $fo['type'];
                }
            }
            unset($fo);
        }
        }
        ?>
    </select>

	<span class="alignright">
    	<a href="javascript:void(0)" class="frm_remove_tag frm_icon_font" data-removeid="frm_posttax_<?php echo esc_attr( $tax_meta ) ?>" data-showlast=".frm_add_posttax_row.button"></a>
    	<a href="javascript:void(0)" class="frm_add_tag frm_icon_font frm_add_posttax_row"></a>
	</span>

<?php if ( $selected_type != 'tag' ) { ?>
	<br/>
	<label for="<?php echo esc_attr( $tax_meta ) ?>_show_exclude">
		<input type="checkbox" value="1" name="<?php echo esc_attr( $action_control->get_field_name('post_category') ) ?>[<?php echo esc_attr( $tax_meta ) ?>][show_exclude]" id="<?php echo esc_attr( $tax_meta ) ?>_show_exclude" <?php echo ( isset( $field_vars['exclude_cat'] ) && $field_vars['exclude_cat'] && ! empty( $field_vars['exclude_cat'] ) ) ? 'checked="checked"' : ''; ?> onchange="frm_show_div('frm_exclude_cat_list_<?php echo esc_attr( $tax_meta ) ?>',this.checked,1,'#')" class="frm_show_exclude" />
		<?php esc_html_e( 'Exclude options', 'formidable-pro' ); ?>
	</label>

    <div class="frm_exclude_cat_<?php echo esc_attr( $tax_meta ) ?> with_frm_style">
        <div id="frm_exclude_cat_list_<?php echo esc_attr( $tax_meta ) ?>" class="frm_exclude_cat_list <?php echo esc_attr( isset( $field_vars['exclude_cat'] ) && $field_vars['exclude_cat'] && ! empty( $field_vars['exclude_cat'] ) ? '' : 'frm_hidden' ); ?>" style="margin:5px 10px 10px 0;">
        <div class="frm_posttax_opt_list">
            <div class="frm_border_bottom">
			<label for="check_all_<?php echo esc_attr( $tax_meta ) ?>">
				<input type="checkbox" id="check_all_<?php echo esc_attr( $tax_meta ) ?>" onclick="frmCheckAll(this.checked,'<?php echo esc_js( $action_control->get_field_name('post_category') ) ?>[<?php echo esc_attr( $tax_meta ) ?>][exclude_cat]')" />
				<span class="howto frm_no_float"><?php esc_html_e( 'Check All', 'formidable-pro' ); ?></span>
			</label>

            <?php
			// Get the selected category/taxonomy depth, and then show the correct number of checkboxes
            $depth = FrmProFieldsHelper::get_category_depth( $field_vars['meta_name'] );
			?>
            <?php for ( $i = 1; $i <= $depth; $i++ ) { ?>
				<label for="check_lev<?php echo esc_attr( $i . '_' . $tax_meta ) ?>" class="check_lev<?php echo absint( $i ) ?>_label">
					<input type="checkbox" id="check_lev<?php echo absint( $i ); ?>_<?php echo esc_attr( $tax_meta ); ?>" class="frm_check_all" value="0" name="<?php echo esc_attr( $action_control->get_field_name( 'exclude_cat_' . $tax_meta ) ); ?>[]" onclick="frmCheckAllLevel(this.checked,'<?php echo esc_js( $action_control->get_field_name('post_category') ); ?>[<?php echo esc_attr( $tax_meta ); ?>][exclude_cat]',<?php echo absint( $i ); ?>)" />
					<span class="howto frm_no_float"><?php printf(__( 'Check All Level %d', 'formidable-pro' ), $i); ?></span>
				</label>
            <?php } ?>
            </div>
            <?php
			if ( ! empty( $selected_type ) && $selected_type != 'data' ) {
			?>
			<p class="howto check_lev1_label frm_hidden">
				<?php esc_html_e( 'NOTE: if the parent is excluded, child categories will be automatically excluded.', 'formidable-pro' ); ?>
			</p>
            <?php
            }

			FrmProFormActionsController::display_taxonomy_checkboxes_for_post_action( array(
                'form_id' => $values['id'],
				'taxonomy' => $field_vars['meta_name'],
				'field_name' => $action_control->get_field_name( 'post_category' ) . '[' . $tax_meta . '][exclude_cat][]',
				'value' => ( isset( $field_vars['exclude_cat'] ) ? $field_vars['exclude_cat'] : 0 ),
			) );
           ?>
        </div>
        </div>
    </div>
<?php
    unset($selected_type);
}
unset($tax_meta);
?>
</div>
