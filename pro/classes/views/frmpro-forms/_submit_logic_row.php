<div id="frm_logic_submit_<?php echo esc_attr( $meta_name ); ?>" class="frm_logic_row frm_logic_row_submit">
    <select name="options[submit_conditions][hide_field][]" class="frm_logic_field_opts frm_submit_logic_field_opts"
            data-type="submit" data-row="<?php echo esc_attr( $meta_name ) ?>">
        <option value=""><?php esc_html_e( '&mdash; Select &mdash;' ) ?></option>
		<?php
		foreach ( $form_fields as $ff ) {
			if ( is_array( $ff ) ) {
				$ff = (object) $ff;
			}

			if ( in_array( $ff->type, $exclude_fields ) || FrmProField::is_list_field( $ff ) ) {
				continue;
			}
			$selected = ( isset( $submit_conditions['hide_field'][ $meta_name ] ) && $ff->id == $submit_conditions['hide_field'][ $meta_name ] ) ? 'selected="selected"' : '';
			?>
            <option value="<?php echo esc_attr( $ff->id ) ?>"<?php echo $selected ?>>
				<?php echo FrmAppHelper::truncate( $ff->name, 25 ); ?>
			</option>
			<?php
			unset( $ff );
		}
		?>
    </select>
	<?php
	esc_html_e( 'is', 'formidable-pro' );

	if ( ! isset( $submit_conditions['hide_field_cond'][ $meta_name ] ) ) {
		$submit_conditions['hide_field_cond'][ $meta_name ] = '';
	}
	$submit_conditions['hide_field_cond'][ $meta_name ] = htmlspecialchars_decode( $submit_conditions['hide_field_cond'][ $meta_name ] );
	?>
    <select name="options[submit_conditions][hide_field_cond][]">
        <option value="==" <?php selected( $submit_conditions['hide_field_cond'][ $meta_name ], '==' ) ?>>
			<?php esc_html_e( 'equal to', 'formidable-pro' ); ?>
		</option>
        <option value="!=" <?php selected( $submit_conditions['hide_field_cond'][ $meta_name ], '!=' ) ?>>
			<?php esc_html_e( 'NOT equal to', 'formidable-pro' ); ?>
            &nbsp;
        </option>
        <option value=">" <?php selected( $submit_conditions['hide_field_cond'][ $meta_name ], '>' ) ?>>
			<?php esc_html_e( 'greater than', 'formidable-pro' ); ?>
		</option>
        <option value=">=" <?php selected( $submit_conditions['hide_field_cond'][ $meta_name ], '>=' ) ?>>
			<?php esc_html_e( 'greater than or equal to', 'formidable-pro' ); ?>
		</option>
        <option value="<" <?php selected( $submit_conditions['hide_field_cond'][ $meta_name ], '<' ) ?>>
			<?php esc_html_e( 'less than', 'formidable-pro' ); ?>
		</option>
        <option value="<=" <?php selected( $submit_conditions['hide_field_cond'][ $meta_name ], '<=' ) ?>>
			<?php esc_html_e( 'less than or equal to', 'formidable-pro' ); ?>
		</option>
        <option value="LIKE" <?php selected( $submit_conditions['hide_field_cond'][ $meta_name ], 'LIKE' ) ?>>
			<?php esc_html_e( 'like', 'formidable-pro' ); ?>
		</option>
        <option value="not LIKE" <?php selected( $submit_conditions['hide_field_cond'][ $meta_name ], 'not LIKE' ) ?>>
			<?php esc_html_e( 'not like', 'formidable-pro' ); ?>
            &nbsp;
        </option>
    </select>

    <span id="frm_show_selected_values_submit_<?php echo esc_attr( $meta_name ) ?>">
<?php
$selector_field_id = ( isset( $submit_conditions['hide_field'][ $meta_name ] ) && is_numeric( $submit_conditions['hide_field'][ $meta_name ] ) ) ? (int) $submit_conditions['hide_field'][ $meta_name ] : 0;
$selector_args     = array(
	'html_name' => 'options[submit_conditions][hide_opt][]',
	'value'     => isset( $submit_conditions['hide_opt'][ $meta_name ] ) ? $submit_conditions['hide_opt'][ $meta_name ] : '',
	'source'    => 'submit',
);

FrmFieldsHelper::display_field_value_selector( $selector_field_id, $selector_args );
?>
</span>
    <a href="javascript:void(0)" class="frm_remove_tag frm_icon_font"
       data-removeid="frm_logic_submit_<?php echo esc_attr( $meta_name ) ?>" data-showlast="#logic_link_submit"
       data-hidelast="#frm_submit_logic_rows"></a>
    <a href="javascript:void(0)" class="frm_add_tag frm_icon_font frm_add_submit_logic"></a>
</div>
