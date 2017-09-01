
<div class="frm_form_field frm_section_heading form-field frm_first  frm_half">
    <h3 class="frm_pos_top frm_section_spacing"><?php _e( 'Repeatable Section', 'formidable' ) ?></h3>
    <div>
        <div class="frm_repeat_sec">

			<div class="frm_form_field form-field frm_full <?php echo esc_attr( $pos_class ) ?>">
				<label class="frm_primary_label"><?php _e( 'Text Area', 'formidable' ) ?></label>
				<textarea></textarea>
				<div class="frm_description"><?php _e( 'Another field with a description', 'formidable' ) ?></div>
			</div>

			<div class="frm_form_field form-field frm_first frm_half <?php echo esc_attr( $pos_class ) ?>">
				<label class="frm_primary_label"><?php _e( 'Radio Buttons', 'formidable' ) ?></label>
				<div class="frm_opt_container">
					<div class="frm_radio"><input type="radio" /><label><?php _e( 'Option 1', 'formidable' ) ?></label></div>
					<div class="frm_radio"><input type="radio" /><label><?php _e( 'Option 2', 'formidable' ) ?></label></div>
				</div>
			</div>

			<div class="frm_form_field form-field frm_half <?php echo esc_attr( $pos_class ) ?>">
				<label class="frm_primary_label"><?php _e( 'Check Boxes', 'formidable' ) ?></label>
				<div class="frm_opt_container">
					<div class="frm_checkbox"><label><input type="checkbox" /><?php _e( 'Option 1', 'formidable' ) ?></label></div>
					<div class="frm_checkbox"><label><input type="checkbox" /><?php _e( 'Option 2', 'formidable' ) ?></label></div>
				</div>
			</div>
            <div class="frm_form_field frm_hidden_container">
                <a href="javascript:void(0)" class="frm_button"><i class="frm_icon_font frm_minus_icon"> </i> <?php _e( 'Remove', 'formidable' ) ?></a>
                <a href="javascript:void(0)" class="frm_button"><i class="frm_icon_font frm_plus_icon"> </i> <?php _e( 'Add', 'formidable' ) ?></a>
            </div>
        </div>
    </div>
</div>

<div class="frm_form_field frm_section_heading form-field frm_half">
    <h3 class="frm_pos_top frm_trigger active frm_section_spacing"><i class="frm_icon_font frm_arrow_icon frm_before_collapse"></i><?php _e( 'Collapsible Section', 'formidable' ) ?><i class="frm_icon_font frm_arrow_icon frm_after_collapse"></i></h3>
    <div class="frm_toggle_container">

		<div class="frm_form_field form-field">
			<div id="datepicker_sample" style="margin-bottom:<?php echo esc_attr( $style->post_content['field_margin'] ) ?>;"></div>
		</div>

    </div>
</div>

<div class="frm_form_field frm_section_heading form-field">
    <h3 class="frm_pos_top frm_section_spacing"> </h3>
</div>

<div class="frm_rootline_group">
	<ul class="frm_page_bar frm_progress_line frm_show_lines">
		<li class="frm_rootline_single">
			<span class="frm_rootline_title">Step 1</span>
			<input type="button" value="1" class="frm_page_back" disabled="disabled"  />
		</li>
		<li class="frm_rootline_single frm_current_page">
			<span class="frm_rootline_title">Step 2</span>
			<input type="button" value="2" class="frm_page_skip" disabled="disabled"  />
		</li>
		<li class="frm_rootline_single">
			<span class="frm_rootline_title">Step 3</span>
			<input type="button" value="3" class="frm_page_skip" disabled="disabled" />
		</li>
	</ul>
	<div class="frm_percent_complete"><?php echo esc_html( sprintf( __( '%s Complete', 'formidable' ), '33%' ) ) ?></div>
	<div class="frm_pages_complete"><?php echo esc_html( sprintf( __( '%1$d of %2$d', 'formidable' ), 2, 3 ) ) ?></div>
	<div class="frm_clearfix"></div>
</div>

<div class="frm_rootline_group">
	<ul class="frm_page_bar frm_rootline frm_show_lines">
		<li class="frm_rootline_single">
			<input type="button" value="1" class="frm_page_back" disabled="disabled"  />
			<span class="frm_rootline_title">Step 1</span>
		</li>
		<li class="frm_rootline_single frm_current_page">
			<input type="button" value="2" class="frm_page_skip" disabled="disabled"  />
			<span class="frm_rootline_title">Step 2</span>
		</li>
		<li class="frm_rootline_single">
			<input type="button" value="3" class="frm_page_skip" disabled="disabled" />
			<span class="frm_rootline_title">Step 3</span>
		</li>
	</ul>
	<div class="frm_clearfix"></div>
</div>
