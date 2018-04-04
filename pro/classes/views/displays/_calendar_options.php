
<label class="frm_left_label"><?php _e( 'Event Date', 'formidable-pro' ); ?></label>
<select id="date_field_id" name="options[date_field_id]">
    <option value="created_at" <?php selected($post->frm_date_field_id, 'created_at') ?>><?php _e( 'Entry creation date', 'formidable-pro' ) ?></option>
    <option value="updated_at" <?php selected($post->frm_date_field_id, 'updated_at') ?>><?php _e( 'Entry update date', 'formidable-pro' ) ?></option>
    <?php
	if ( is_numeric( $post->frm_form_id ) && ! empty( $post->frm_form_id ) ) {
		FrmProFieldsHelper::get_field_options( $post->frm_form_id, $post->frm_date_field_id, '', array( 'date' ) );
	}
	?>
</select>
<br/>

<label class="frm_left_label"><?php _e( 'End Date or Day Count', 'formidable-pro' ); ?></label>
<select id="edate_field_id" name="options[edate_field_id]">
    <option value=""><?php _e( 'No multi-day events', 'formidable-pro' ) ?></option>
    <option value="created_at" <?php selected($post->frm_edate_field_id, 'created_at') ?>><?php _e( 'Entry creation date', 'formidable-pro' ) ?></option>
    <option value="updated_at" <?php selected($post->frm_edate_field_id, 'updated_at') ?>><?php _e( 'Entry update date', 'formidable-pro' ) ?></option>
    <?php
	if ( is_numeric( $post->frm_form_id ) && ! empty( $post->frm_form_id ) ) {
		FrmProFieldsHelper::get_field_options( $post->frm_form_id, $post->frm_edate_field_id, '', array( 'date', 'number', 'select', 'radio', 'scale', 'star' ) );
	}
	?>
</select>
<br/>

<label class="frm_left_label"><?php _e( 'Repeat', 'formidable-pro' ); ?> <span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php printf(__( 'Select a field from your form that contains values like 1 week, 2 weeks, 1 year, etc. This will set the repeat period for each event.', 'formidable-pro' ), FrmAppHelper::site_url()) ?>" ></span> </label>
<select id="repeat_event_field_id" name="options[repeat_event_field_id]">
    <option value=""><?php _e( 'No repeating events', 'formidable-pro' ) ?></option>
    <?php
	if ( is_numeric( $post->frm_form_id ) && ! empty( $post->frm_form_id ) ) {
		FrmProFieldsHelper::get_field_options( $post->frm_form_id, $post->frm_repeat_event_field_id, '', array( 'radio', 'select' ) );
	}
	?>
</select>
<br/>

<label class="frm_left_label"><?php _e( 'End Repeat', 'formidable-pro' ); ?></label>
<select id="repeat_edate_field_id" name="options[repeat_edate_field_id]">
    <option value=""><?php _e( 'Never', 'formidable-pro' ) ?></option>
    <?php
	if ( is_numeric( $post->frm_form_id ) && ! empty( $post->frm_form_id ) ) {
		FrmProFieldsHelper::get_field_options( $post->frm_form_id, $post->frm_repeat_edate_field_id, '', array( 'date' ) );
	}
	?>
</select>
