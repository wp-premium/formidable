<?php

class FrmProComment {
	public static function create_comment( $entry_id, $form_id ) {
		$comment_post_ID = isset($_POST['comment_post_ID']) ? (int) $_POST['comment_post_ID'] : 0;

		$post = get_post($comment_post_ID);

		if ( empty($post->comment_status) )
			return;

		// get_post_status() will get the parent status for attachments.
		$status = get_post_status($post);

		$status_obj = get_post_status_object($status);

		if ( ! comments_open( $comment_post_ID ) ) {
			do_action('comment_closed', $comment_post_ID);
			//wp_die( __( 'Sorry, comments are closed for this item.') );
			return;
		} else if ( 'trash' == $status ) {
			do_action('comment_on_trash', $comment_post_ID);
			return;
		} else if ( ! $status_obj->public && ! $status_obj->private ) {
			do_action('comment_on_draft', $comment_post_ID);
			return;
		} else if ( post_password_required($comment_post_ID) ) {
			do_action('comment_on_password_protected', $comment_post_ID);
			return;
		} else {
			do_action('pre_comment_on_post', $comment_post_ID);
		}

		$comment_content      = ( isset($_POST['comment']) ) ? trim($_POST['comment']) : '';

		// If the user is logged in
		$user_ID = get_current_user_id();
		if ( $user_ID ) {
			global $current_user;

			$display_name = ( ! empty( $current_user->display_name ) ) ? $current_user->display_name : $current_user->user_login;
			$comment_author       = $display_name;
			$comment_author_email = ''; //get email from field
			$comment_author_url   = $current_user->user_url;
		}else{
			$comment_author       = ( isset($_POST['author']) )  ? trim(strip_tags($_POST['author'])) : '';
			$comment_author_email = ( isset($_POST['email']) )   ? trim($_POST['email']) : '';
			$comment_author_url   = ( isset($_POST['url']) )     ? trim($_POST['url']) : '';
		}

		$comment_type = '';

		if ( ! $user_ID && get_option( 'require_name_email' ) && ( 6 > strlen($comment_author_email) || $comment_author == '' ) ) {
			return;
		}

		if ( $comment_content == '' ) {
			return;
		}


		$commentdata = compact('comment_post_ID', 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_content', 'comment_type', 'user_ID');

		wp_new_comment( $commentdata );

	}
}