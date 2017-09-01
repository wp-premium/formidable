
<h3><?php _e( 'Pagination', 'formidable' ) ?></h3>
<table class="form-table">
	<tr>
		<td>
			<select name="options[rootline]" id="frm_rootline_opt" data-toggleclass="hide_rootline">
				<option value=""><?php esc_html_e( 'Hide Progress bar and Rootline', 'formidable' ) ?></option>
				<option value="progress" <?php selected( $values['rootline'], 'progress' ) ?>>
					<?php esc_html_e( 'Show Progress bar', 'formidable' ) ?>
				</option>
				<option value="rootline" <?php selected( $values['rootline'], 'rootline' ) ?>>
					<?php esc_html_e( 'Show Rootline', 'formidable' ) ?>
				</option>
			</select>
		</td>
	</tr>
	<tr class="hide_rootline <?php echo esc_attr( $hide_rootline_class ) ?>">
		<td>
			<label>
				<input type="checkbox" value="1" name="options[rootline_titles_on]" <?php checked( $values['rootline_titles_on'], 1 ) ?> data-toggleclass="hide_rootline_titles" />
				<?php esc_html_e( 'Show page titles with steps', 'formidable' ); ?>
			</label>

			<div class="frm_indent_opt hide_rootline_titles <?php echo esc_attr( $hide_rootline_title_class ) ?>">
				<p>
					<label class="screen-reader-text" for="page_title_<?php echo esc_attr( $i ) ?>">
						<?php esc_html( sprintf( __( 'Page %d title', 'formidable' ), $i ) ) ?>
					</label>
					<input type="text" value="<?php echo esc_attr( isset( $values['rootline_titles'][0] ) ? $values['rootline_titles'][0] : sprintf( __( 'Page %d', 'formidable' ), 1 ) ) ?>" name="options[rootline_titles][0]" class="large-text" placeholder="<?php echo esc_attr( sprintf( __( 'Page %d title', 'formidable' ), $i ) ) ?>" id="page_title_<?php echo esc_attr( $i ) ?>" />
				</p>
				<?php foreach ( $page_fields as $page_field ) {
					$i++; ?>
					<p>
						<label class="screen-reader-text" for="page_title_<?php echo esc_attr( $i ) ?>"></label>
						<input type="text" value="<?php echo esc_attr( isset( $values['rootline_titles'][ $page_field->id ] ) ? $values['rootline_titles'][ $page_field->id ] : $page_field->name ) ?>" name="options[rootline_titles][<?php echo esc_attr( $page_field->id ) ?>]" class="large-text" placeholder="<?php echo esc_attr( sprintf( __( 'Page %d title', 'formidable' ), $i ) ) ?>" id="page_title_<?php echo esc_attr( $i ) ?>" />
					</p>
				<?php } ?>
			</div>
		</td>
	</tr>
	<tr class="hide_rootline <?php echo esc_attr( $hide_rootline_class ) ?>">
		<td>
			<label>
				<input type="checkbox" value="1" name="options[rootline_numbers_off]" <?php checked( $values['rootline_numbers_off'], 1 ) ?> />
				<?php esc_html_e( 'Hide the page numbers', 'formidable' ); ?>
			</label>
		</td>
	</tr>
	<tr class="hide_rootline <?php echo esc_attr( $hide_rootline_class ) ?>">
		<td>
			<label>
				<input type="checkbox" value="1" name="options[rootline_lines_off]" <?php checked( $values['rootline_lines_off'], 1 ) ?> />
				<?php esc_html_e( 'Hide lines in the rootline or progress bar', 'formidable' ); ?>
			</label>
		</td>
	</tr>
</table>
