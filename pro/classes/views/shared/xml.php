<script type="text/javascript">
frmAdminBuild.downloadXML('<?php echo esc_attr($controller) ?>', '<?php echo esc_attr($ids); ?>'<?php
if ( isset( $is_template ) ) {
	echo "'," . esc_attr( $is_template ) . "'";
}
?>);
</script>
