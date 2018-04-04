<div class="manage-menus">
	<span class="add-edit-menu-action">
		<?php
		if ( count( $styles ) < 2 && ! empty( $style->ID ) ) {
			printf(
				__( 'Edit your style below, or %1$screate a new style%2$s or %3$sduplicate the current style%4$s.', 'formidable-pro' ),
				'<a href="?page=formidable-styles&frm_action=new_style">', '</a>',
				'<a href="?page=formidable-styles&frm_action=duplicate&style_id=' . absint( $style->ID ) . '">', '</a>'
			);
		} else { ?>
			<form method="get">
				<input type="hidden" name="page" value="<?php echo esc_attr( FrmAppHelper::simple_get( 'page', 'sanitize_title' ) ) ?>"/>
				<input type="hidden" name="frm_action" value="edit" />
				<label class="selected-menu"><?php _e( 'Select a style to edit:', 'formidable-pro' ); ?></label>
				<select name="id">
					<option value=""><?php _e( '&mdash; Select &mdash;') ?></option>
					<?php foreach ( $styles as $s ) { ?>
					<option value="<?php echo esc_attr( $s->ID ) ?>" <?php selected( $s->ID, $style->ID ); ?>><?php echo esc_html( $s->post_title . ( empty( $s->menu_order ) ? '' : ' (' . __( 'default', 'formidable-pro' ) . ')' ) ) ?></option>
					<?php } ?>
				</select>
				<span class="submit-btn">
					<input type="submit" class="button-secondary" value="<?php esc_attr_e( 'Select', 'formidable-pro' ) ?>" />
				</span>
			</form>
			<span class="add-new-menu-action">
				<?php
				if ( empty( $style->ID ) ) {
					printf(
						__( 'or %1$screate a new style%2$s', 'formidable-pro' ),
						'<a href="?page=formidable-styles&frm_action=new_style">', '</a>'
					);
				} else {
					printf(
						__( 'or %1$screate a new style%2$s or %3$sduplicate the current style%4$s.', 'formidable-pro' ),
						'<a href="?page=formidable-styles&frm_action=new_style">', '</a>',
						'<a href="?page=formidable-styles&frm_action=duplicate&style_id=' . absint( $style->ID ) . '">', '</a>'
					);
				}
				?>
			</span>
<?php
		}
		?>
	</span>
</div><!-- /manage-menus -->
