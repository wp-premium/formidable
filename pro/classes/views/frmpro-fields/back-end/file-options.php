<tr><td><label><?php _e( 'Multiple files', 'formidable' ) ?></label></td>
	<td><label for="multiple_<?php echo esc_attr( $field['id'] ) ?>"><input type="checkbox" name="field_options[multiple_<?php echo esc_attr( $field['id'] ) ?>]" id="multiple_<?php echo esc_attr( $field['id'] ) ?>" value="1" <?php echo ( isset( $field['multiple'] ) && $field['multiple'] ) ? 'checked="checked"' : ''; ?> />
			<?php _e( 'allow multiple files to be uploaded to this field', 'formidable' ) ?></label></td>
</tr>
<tr><td><label><?php _e( 'Delete files', 'formidable' ) ?></label></td>
	<td><label for="delete_<?php echo esc_attr( $field['id'] ) ?>"><input type="checkbox" name="field_options[delete_<?php echo esc_attr( $field['id'] ) ?>]" id="delete_<?php echo esc_attr( $field['id'] ) ?>" value="1" <?php echo ( isset( $field['delete'] ) && $field['delete'] ) ? 'checked="checked"' : ''; ?> />
			<?php _e( 'permanently delete old files when replaced or when the entry is deleted', 'formidable' ) ?></label></td>
</tr>
<tr><td><label><?php _e( 'Email Attachment', 'formidable' ) ?></label></td>
	<td><label for="attach_<?php echo esc_attr( $field['id'] ) ?>"><input type="checkbox" id="attach_<?php echo esc_attr( $field['id'] ) ?>" name="field_options[attach_<?php echo esc_attr( $field['id'] ) ?>]" value="1" <?php echo ( isset( $field['attach'] ) && $field['attach'] ) ? 'checked="checked"' : ''; ?> /> <?php _e( 'attach this file to the email notification', 'formidable' ) ?></label></td>
</tr>
<?php if ( $mimes ) { ?>
	<tr><td><label><?php _e( 'Allowed file types', 'formidable' ) ?></label></td>
		<td>
			<label for="restrict_<?php echo $field['id'] ?>_0"><input type="radio" name="field_options[restrict_<?php echo $field['id'] ?>]" id="restrict_<?php echo $field['id'] ?>_0" value="0" <?php FrmAppHelper::checked($field['restrict'], 0); ?> onclick="frm_show_div('restrict_box_<?php echo $field['id'] ?>',this.value,1,'.')" /> <?php _e( 'All types', 'formidable' ) ?></label>
			<label for="restrict_<?php echo $field['id'] ?>_1"><input type="radio" name="field_options[restrict_<?php echo $field['id'] ?>]" id="restrict_<?php echo $field['id'] ?>_1" value="1" <?php FrmAppHelper::checked($field['restrict'], 1); ?> onclick="frm_show_div('restrict_box_<?php echo $field['id'] ?>',this.value,1,'.')" /> <?php _e( 'Specify allowed types', 'formidable' ) ?></label>
			<label for="check_all_ftypes_<?php echo $field['id'] ?>" class="restrict_box_<?php echo $field['id'] ?> <?php echo ($field['restrict'] == 1) ? '' : 'frm_hidden'; ?>"><input type="checkbox" id="check_all_ftypes_<?php echo $field['id'] ?>" onclick="frmCheckAll(this.checked,'field_options[ftypes_<?php echo $field['id'] ?>]')" /> <span class="howto"><?php _e( 'Check All', 'formidable' ) ?></span></label>

			<div class="restrict_box_<?php echo $field['id'] . ($field['restrict'] == 1 ? '' : ' frm_hidden'); ?>">
				<div class="frm_field_opts_list" style="width:100%;">
					<div class="alignleft" style="width:33% !important">
						<?php
						$mcount = count($mimes);
						$third = ceil($mcount/3);
						$c = 0;
						if ( ! isset($field['ftypes']) ) {
							$field['ftypes'] = array();
						}

						foreach ( $mimes as $ext_preg => $mime ) {
						if ( $c == $third || ( ( $c/2 ) == $third ) ) { ?>
					</div>
					<div class="alignleft" style="width:33% !important">
						<?php } ?>
						<label for="ftypes_<?php echo $field['id'] ?>_<?php echo sanitize_key($ext_preg) ?>"><input type="checkbox" id="ftypes_<?php echo $field['id'] ?>_<?php echo sanitize_key($ext_preg) ?>" name="field_options[ftypes_<?php echo $field['id'] ?>][<?php echo $ext_preg ?>]" value="<?php echo $mime ?>" <?php FrmAppHelper::checked($field['ftypes'], $mime); ?> /> <span class="howto"><?php echo str_replace('|', ', ', $ext_preg); ?></span></label><br/>
						<?php
						$c++;
						unset($ext_preg, $mime);
						}
						unset( $c, $mcount, $third );
						?>
					</div>
				</div>
			</div>
		</td>
	</tr>
<?php }