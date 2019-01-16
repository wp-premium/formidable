<h3>
	<?php esc_html_e( 'Form Scheduling', 'formidable-pro' ); ?>
	<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Prevent the form from showing when submissions should not be accepted. Close it now, or schedule the form to open and/or close later.', 'formidable-pro' ); ?>"></span>
</h3>

<table class="form-table">
	<tr>
		<td class="frm_left_label">
			<label for="frm_open_status">
				<?php esc_html_e( 'Form Status', 'formidable' ); ?>
			</label>
		</td>
		<td>
			<select name="options[open_status]" id="frm_open_status" data-toggleclass="hide_form_status">
				<option value="" <?php selected( $values['open_status'], '' ); ?>>
					<?php esc_html_e( 'Open', 'formidable' ); ?>
				</option>
				<option value="closed" <?php selected( $values['open_status'], 'closed' ); ?>>
					<?php esc_html_e( 'Closed', 'formidable' ); ?>
				</option>
				<option value="schedule" <?php selected( $values['open_status'], 'schedule' ); ?>>
					<?php esc_html_e( 'Schedule', 'formidable' ); ?>
				</option>
				<option value="limit" <?php selected( $values['open_status'], 'limit' ); ?>>
					<?php esc_html_e( 'Limit Entries', 'formidable' ); ?>
				</option>
				<option value="schedule-limit" <?php selected( $values['open_status'], 'schedule-limit' ); ?>>
					<?php esc_html_e( 'Schedule and Limit Entries', 'formidable' ); ?>
				</option>
			</select>
		</td>
	</tr>
	<tr class="hide_form_status hide_hide_form_status_closed hide_hide_form_status_limit<?php echo ( strpos( $values['open_status'], 'schedule' ) === false ) ? ' frm_hidden' : ''; ?>">
		<td class="frm_left_label">
			<label for="frm_open_date">
				<?php esc_html_e( 'Open Form on', 'formidable' ); ?>
			</label>
		</td>
		<td>
			<input type="text" name="options[open_date]" id="frm_open_date" class="frm_date" value="<?php echo esc_attr( $values['open_date'] ); ?>" />
		</td>
	</tr>
	<tr class="hide_form_status hide_hide_form_status_closed hide_hide_form_status_limit<?php echo ( strpos( $values['open_status'], 'schedule' ) === false ) ? ' frm_hidden' : ''; ?>">
		<td class="frm_left_label">
			<label for="frm_close_date">
				<?php esc_html_e( 'Close Form on', 'formidable' ); ?>
			</label>
		</td>
		<td>
			<input type="text" name="options[close_date]" id="frm_close_date" class="frm_date" value="<?php echo esc_attr( $values['close_date'] ); ?>" />
		</td>
	</tr>
	<tr class="hide_form_status hide_hide_form_status_closed hide_hide_form_status_schedule<?php echo ( strpos( $values['open_status'], 'limit' ) === false ) ? ' frm_hidden' : ''; ?>">
		<td class="frm_left_label">
			<?php esc_html_e( 'Entry Limit', 'formidable' ); ?>
			<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Close the form after a specific number of entries have been received.', 'formidable-pro' ); ?>"></span>
		</td>
		<td>
			<label>
				<?php
				/* translators: %1$s: a text input box */
				printf(
					esc_html__( 'Close this form after %1$s entries', 'formidable-pro' ),
					'<input type="text" name="options[max_entries]" id="frm_max_entries" size="4" value="' . esc_attr( $values['max_entries'] ) . '" />'
				);
				?>
			</label>
		</td>
	</tr>
	<tr class="hide_form_status<?php echo ( empty( $values['open_status'] ) ) ? ' frm_hidden' : ''; ?>">
		<td class="frm_left_label">
			<label for="frm_closed_msg">
				<?php esc_html_e( 'Form Closed Message', 'formidable' ); ?>
				<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'This message is shown when a form is closed for new entries.', 'formidable-pro' ); ?>"></span>
			</label>
		</td>
		<td>
			<div class="form_status_closed_box">
				<textarea name="options[closed_msg]" id="frm_closed_msg" class="frm_long_input" rows="3"><?php echo FrmAppHelper::esc_textarea( $values['closed_msg'] ); ?></textarea>
			</div>
		</td>
	</tr>
</table>

<script>
	jQuery( function() {
		jQuery('.frm_date').datepicker({changeMonth:true,changeYear:true,dateFormat:'yy-mm-dd',onSelect: function(val){
			var d = new Date();

			var h = d.getHours();
			h = (h < 10) ? ('0' + h) : h ;

			var m = d.getMinutes();
			m = (m < 10) ? ('0' + m) : m ;

			val = val + ' ' + h + ':' + m;

			jQuery(this).val(val);
		}});
	});
</script>
