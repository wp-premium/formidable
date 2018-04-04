<div class="postbox" id="frm_comment_list">
	<h3 class="hndle"><span><?php esc_html_e( 'Comments/Notes', 'formidable-pro' ) ?></span></h3>
    <div class="inside">
        <table class="form-table"><tbody>
		<?php
		foreach ( $comments as $comment ) {
            $meta = $comment->meta_value;
            if ( ! isset($meta['comment']) ) {
                continue;
            }
        ?>
			<tr class="frm_comment_block" id="frmcomment<?php echo esc_attr( $comment->id ) ?>">
				<th scope="row">
					<p><strong><?php echo FrmAppHelper::kses( FrmFieldsHelper::get_user_display_name( $meta['user_id'], 'display_name', array( 'link' => true ) ) ); ?></strong><br/>
					<?php echo FrmAppHelper::kses( FrmAppHelper::get_formatted_time( $comment->created_at, $date_format, $time_format ) ); ?></p>
                </th>
				<td><div class="frm_comment"><?php echo wpautop( FrmAppHelper::kses( $meta['comment'] ) ); ?></div></td>
            </tr>
        <?php } ?>
        </table>
		<a href="#" class="button-secondary alignright frm_show_comment" data-frmtoggle="#frm_comment_form">+ <?php esc_html_e( 'Add Note/Comment', 'formidable-pro' ) ?></a>
        <div class="clear"></div>

        <form action="<?php echo esc_url( '?page=formidable-entries&frm_action=show&id=' . absint( $entry->id ) . '#frm_comment_form' ) ?>" name="frm_comment_form" id="frm_comment_form" method="post" class="frm_hidden frm_no_print">
            <input type="hidden" name="frm_action" value="show" />
            <input type="hidden" name="field_id" value="0" />
			<input type="hidden" name="item_id" value="<?php echo absint( $entry->id ) ?>" />
            <?php wp_nonce_field('add-option'); ?>

            <table class="form-table"><tbody>
                <tr>
					<th scope="row"><?php esc_html_e( 'Comment/Note', 'formidable-pro' ) ?>:</th>
                    <td><textarea name="frm_comment" id="frm_comment" cols="50" rows="5" class="large-text"> </textarea>
                        <p class="submit">
							<input class="button-primary" type="submit" value="<?php esc_attr_e( 'Submit', 'formidable-pro' ) ?>" />
                        </p>
                    </td>
                </tr>

            </tbody></table>
        </form>
    </div>
</div>
