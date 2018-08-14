<?php
if ( isset($taxonomies) && $taxonomies ) { ?>
	<option value=""><?php esc_html_e( '&mdash; Select a Taxonomy &mdash;', 'formidable-pro' ); ?></option>
    <?php foreach ( $taxonomies as $taxonomy ) { ?>
		<option value="<?php echo esc_attr( $taxonomy ) ?>">
			<?php echo esc_html( str_replace( array( '_', '-' ), ' ', ucfirst( $taxonomy ) ) ); ?>
		</option>
        <?php
        unset($taxonomy);
    }
} else {
?>
	<option value=""><?php esc_html_e( 'No taxonomies available', 'formidable-pro' ); ?></option>
<?php
}
?>
