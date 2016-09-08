<div id="form_reports_page" class="wrap frm_charts">
	<h1><?php _e( 'Reports', 'formidable' ) ?></h1>

	<div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
        <div id="post-body-content">
        <?php
            FrmAppController::get_form_nav($form, true);
            $class = 'odd';
        ?>
        <div class="clear"></div>

        <?php
        if ( isset($data['time']) ) {
            echo $data['time'];
        }

        foreach ( $fields as $field ) {
            if ( ! isset($data[$field->id]) ) {
                continue;
            }

            $total = FrmProStatisticsController::stats_shortcode( array( 'id' => $field->id, 'type' => 'count' ) );
            if ( ! $total ) {
                continue;
            }
            ?>
            <div style="margin-top:25px;" class="pg_<?php echo esc_attr( $class ) ?>">
            <div class="alignleft"><?php echo $data[ $field->id ] ?></div>
            <div style="padding:10px; margin-top:40px;">
                <p><?php _e( 'Response Count', 'formidable' ) ?>: <?php echo $total ?></p>
            <?php if ( in_array( $field->type, array( 'number', 'hidden' ) ) ) { ?>
            <p><?php _e( 'Total', 'formidable' ) ?>: <?php echo esc_html( $total ); ?></p>
            <p><?php _e( 'Average', 'formidable' ) ?>: <?php echo FrmProStatisticsController::stats_shortcode( array( 'id' => $field->id, 'type' => 'average' ) ); ?></p>
            <p><?php _e( 'Median', 'formidable' ) ?>: <?php echo FrmProStatisticsController::stats_shortcode( array( 'id' => $field->id, 'type' => 'median' ) ); ?></p>
            <?php } else if ( $field->type == 'user_id' ) {
                $user_ids = FrmDb::get_col( $wpdb->users, array(), 'ID', 'display_name ASC' );
                $submitted_user_ids = FrmEntryMeta::get_entry_metas_for_field($field->id, '', '', array( 'unique' => true));
                $not_submitted = array_diff($user_ids, $submitted_user_ids); ?>
            <p><?php _e( 'Percent of users submitted', 'formidable' ) ?>: <?php echo round((count($submitted_user_ids) / count($user_ids)) *100, 2) ?>%</p>
			<form action="<?php echo esc_url( admin_url( 'user-edit.php' ) ) ?>" method="get">
            <p><?php esc_html_e( 'Users with no entry:', 'formidable' ) ?><br/>
				<?php wp_dropdown_users( array( 'include' => $not_submitted, 'name' => 'user_id')) ?>
				<input type="submit" value="<?php esc_attr_e( 'View Profile', 'formidable' ) ?>" class="button-secondary" />
			</p>
            </form>
            <?php } ?>
            </div>
            <div class="clear"></div>
            </div>
        <?php
            $class = ($class == 'odd') ? 'even' : 'odd';
            unset($field);
        }

        if ( isset($data['month']) ) {
            echo $data['month'];
        }
?>
        </div>
        <div id="postbox-container-1" class="postbox-container">
            <div class="postbox ">
            <div class="handlediv"><br/></div><h3 class="hndle"><span><?php _e( 'Statistics', 'formidable' ) ?></span></h3>
            <div class="inside">
                <div class="misc-pub-section">
                    <?php _e( 'Entries', 'formidable' ) ?>:
                    <b><?php echo count($entries); ?></b>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=formidable-entries&frm_action=list&form='. $form->id ) ) ?>"><?php _e( 'Browse', 'formidable' ) ?></a>
                </div>
                <?php if (isset($submitted_user_ids) ) { ?>
                <div class="misc-pub-section">
                    <?php _e( 'Users Submitted', 'formidable' ) ?>: <b><?php echo count($submitted_user_ids) ?> (<?php echo round((count($submitted_user_ids) / count($user_ids)) *100, 2) ?>%)</b>
                </div>
                <?php } ?>
            </div>
            </div>
        </div>
        </div>
    </div>
</div>
