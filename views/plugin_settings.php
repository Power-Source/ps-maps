<div class="wrap">
	<h2><?php _e( 'PS Maps Optionen', AGM_LANG ); ?></h2>

	<?php $options = apply_filters( 'agm_google_maps-settings_form_options', '' ); ?>
	<form action="options.php" <?php echo $options; ?> method="post">
		<?php settings_fields( 'agm_google_maps' ); ?>
		<div class="vnav">
		<?php do_settings_sections( 'agm_google_maps_options_page' ); ?>
		</div>
		<p class="submit">
			<button name="Submit" class="button-primary"><?php _e( 'Änderungen speichern', AGM_LANG ); ?></button>
		</p>
	</form>
</div>