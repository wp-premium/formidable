<div id="frm-dynamic-values" class="tabs-panel frm_hidden" style="max-height:none;">
	<ol class="howto">
		<li><?php esc_html_e( 'Click inside the "Dynamic default value" field setting or directly on the input field.', 'formidable-pro' ); ?></li>
		<li><?php esc_html_e( 'This box will activate and you can click to insert dynamic default values as default text to fields in your form.', 'formidable-pro' ); ?></li>
	</ol>

    <ul class="frm_code_list" style="margin-bottom:0;">
        <?php
        $col = 'one';
		foreach ( $tags as $tag => $label ) {
			$title = '';
			if ( is_array( $label ) ) {
				$title = isset( $label['title'] ) ? $label['title'] : '';
				$label = isset( $label['label'] ) ? $label['label'] : reset( $label );
            }

        ?>
            <li class="frm_col_<?php echo esc_attr( $col ) ?>">
                <a href="javascript:void(0)" data-code="<?php echo esc_attr($tag) ?>" class="frmbutton button show_dyn_default_value frm_insert_code<?php
                if ( ! empty($title) ) {
					echo ' frm_help" title="' . esc_attr( $title );
				}
				?>"><?php echo esc_html( $label ) ?></a>
            </li>
        <?php
			$col = ( $col == 'one' ) ? 'two' : 'one';
            unset($tag, $label);
		}
		?>
    </ul>
</div>
