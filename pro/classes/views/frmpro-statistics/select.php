<div id="form_reports_page" class="wrap frm_charts">
	<h2><?php esc_html_e( 'Reports', 'formidable-pro' ); ?></h2>

    <div id="menu-management" class="nav-menus-php frm-menu-boxes">
        <div class="menu-edit">
            <div id="nav-menu-header"><div class="major-publishing-actions" style="padding:8px 0;">
				<div style="font-size:15px;background:transparent;" class="search">
					<?php esc_html_e( 'Go to Report', 'formidable-pro' ); ?>
				</div>
            </div></div>

            <form method="get">
                <div id="post-body">
				<p><?php esc_html_e( 'Select a report to view.', 'formidable-pro' ); ?></p>
                <input type="hidden" name="frm_action" value="reports" />
                <input type="hidden" name="page" value="formidable" />
				<?php FrmFormsHelper::forms_dropdown( 'form', '', array( 'blank' => false ) ); ?><br/>
                </div>
                <div id="nav-menu-footer">
					<div class="major-publishing-actions">
						<input type="submit" class="button-primary" value="<?php esc_attr_e( 'Go', 'formidable-pro' ) ?>" />
					</div>

                <div class="clear"></div>
                </div>
            </form>
        </div>

    </div>
    <div class="clear"></div>
    </div>
</div>
