<div id="form_reports_page" class="frm_wrap frm_charts">
	<?php
	FrmAppHelper::get_admin_header( array(
		'label' => __( 'Reports', 'formidable-pro' ),
		'form'  => $form,
	) );

	$class = 'odd';
	?>
	<div class="wrap">

		<div class="postbox">
			<h3 class="hndle" style="padding:5px 21px;"><?php esc_html_e( 'Statistics', 'formidable-pro' ); ?></h3>
			<div class="inside">
				<div class="misc-pub-section">
					<?php esc_html_e( 'Total Entries', 'formidable-pro' ); ?>:
					<b><?php echo count( $entries ); ?></b>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=formidable-entries&frm_action=list&form=' . $form->id ) ); ?>">
						<?php esc_html_e( 'Browse', 'formidable-pro' ); ?>
					</a>
				</div>
				<?php if ( isset( $submitted_user_ids ) ) { ?>
					<div class="misc-pub-section">
						<?php esc_html_e( 'Users Submitted', 'formidable-pro' ); ?>:
						<b><?php echo count( $submitted_user_ids ); ?> (<?php echo round( ( count( $submitted_user_ids ) / count( $user_ids ) ) * 100, 2 ); ?>%)</b>
					</div>
				<?php } ?>
			</div>
		</div>

        <?php
        if ( isset($data['time']) ) {
            echo $data['time'];
        }

        foreach ( $fields as $field ) {
			if ( ! isset( $data[ $field->id ] ) ) {
                continue;
            }

            $total = FrmProStatisticsController::stats_shortcode( array( 'id' => $field->id, 'type' => 'count' ) );
            if ( ! $total ) {
                continue;
            }
            ?>
			<div style="margin-top:25px;" class="pg_<?php echo esc_attr( $class ); ?>">
            <div class="alignleft"><?php echo $data[ $field->id ] ?></div>
            <div style="padding:10px; margin-top:40px;">
                <p>
					<?php esc_html_e( 'Response Count', 'formidable-pro' ); ?>:
					<?php echo esc_html( $total ); ?>
				</p>
            <?php if ( in_array( $field->type, array( 'number', 'hidden' ) ) ) { ?>
			<p>
				<?php esc_html_e( 'Total', 'formidable-pro' ); ?>:
				<?php echo esc_html( $total ); ?>
			</p>
			<p>
				<?php esc_html_e( 'Average', 'formidable-pro' ); ?>:
				<?php
				echo FrmProStatisticsController::stats_shortcode( array(
					'id' => $field->id,
					'type' => 'average',
				) );
				?>
			</p>
			<p>
				<?php esc_html_e( 'Median', 'formidable-pro' ); ?>:
				<?php
				echo FrmProStatisticsController::stats_shortcode( array(
					'id' => $field->id,
					'type' => 'median',
				) );
				?>
			</p>
			<?php
			} else if ( $field->type == 'user_id' ) {
				$submitted_user_ids = FrmEntryMeta::get_entry_metas_for_field( $field->id, '', '', array( 'unique' => true ) );
				?>
				<p>
					<?php esc_html_e( 'Percent of users submitted', 'formidable-pro' ); ?>:
					<?php echo round( ( count( $submitted_user_ids ) / count( $user_ids ) ) * 100, 2 ); ?>%
				</p>
            <?php } ?>
            </div>
            <div class="clear"></div>
            </div>
        <?php
			$class = ( $class == 'odd' ) ? 'even' : 'odd';
            unset($field);
        }

        if ( isset($data['month']) ) {
            echo $data['month'];
        }
?>
	</div>
</div>
