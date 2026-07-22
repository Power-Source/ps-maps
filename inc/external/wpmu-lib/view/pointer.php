<?php
/**
 * Modern contextual popup for lib3()->html->pointer().
 *
 * Keeps the public API intact, but renders via wpmUi.popup instead of the
 * deprecated wp-pointer jQuery plugin.
 */

$title_text = trim( wp_strip_all_tags( (string) $title ) );
$body_html = (string) $body;
$body_html = str_replace( array( "\r", "\n" ), '', $body_html );

?>
<script>
	jQuery(document).ready(function() {
		var target = jQuery( '<?php echo $html_el; ?>' );

		if ( ! target.length || 'function' !== typeof wpmUi.popup ) {
			return;
		}

		target = target.first();

		var popup = wpmUi.popup();
		popup.set_class( 'wpmui-pointer-popup' )
			.modal( <?php echo $modal ? 'true' : 'false'; ?> )
			.content(
				'<div class="wpmui-pointer-popup-content">' +
					<?php if ( ! empty( $title_text ) ) : ?>
					'<h3><?php echo esc_js( $title_text ); ?></h3>' +
					<?php endif; ?>
					'<div class="wpmui-pointer-popup-body"><?php echo esc_js( wp_kses_post( $body_html ) ); ?></div>' +
				'</div>'
			)
			.show();
	});
</script>