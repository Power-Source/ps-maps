<?php
/*
Plugin Name: Wo bin ich?
Description: Fügt den Standort des Besuchers als Marker zur Karte in unterstützten Browsern hinzu, automatisch oder über ein Shortcode-Attribut.
Plugin URI:  https://cp-psource.github.io/ps-maps/
Version:     1.0
Author:      PSOURCE
*/

class Agm_Wmi_AdminPages {

	private function __construct() {
		$this->_add_hooks();
	}

	public static function serve() {
		$me = new Agm_Wmi_AdminPages();
	}

	private function _add_hooks() {
		add_action(
			'agm_google_maps-options-plugins_options',
			array( $this, 'register_settings' )
		);
	}

	public function register_settings() {
		add_settings_section(
			'agm_google_wmi_fields',
			__( 'Wo bin ich?', AGM_LANG ),
			'__return_false',
			'agm_google_maps_options_page'
		);
		add_settings_field(
			'agm_google_maps_wmi_auto',
			__( 'Verhalten', AGM_LANG ),
			array( $this, 'create_auto_add_box' ),
			'agm_google_maps_options_page',
			'agm_google_wmi_fields'
		);
		add_settings_field(
			'agm_google_maps_wmi_marker',
			__( 'Aussehen', AGM_LANG ),
			array( $this, 'create_marker_options_box' ),
			'agm_google_maps_options_page',
			'agm_google_wmi_fields'
		);
	}

	public function create_auto_add_box() {
		$shortcode_only = $this->_get_options( 'shortcode_only' );
		$no = $shortcode_only ? 'checked="checked"' : false;
		$yes = $shortcode_only ? false : 'checked="checked"';

		echo '<input type="radio" id="agm-wmi-auto-yes" name="agm_google_maps[wmi-shortcode_only]" value="" ' . $yes . ' />' .
			'&nbsp' .
			'<label for="agm-wmi-auto-yes">' . __( 'Ich möchte den Standort des Besuchers automatisch auf allen meinen Karten anzeigen', AGM_LANG ) . '</label>' .
			'<div><small>' . __( 'Der Standort des Besuchers wird automatisch zu allen Deinen Karten hinzugefügt', AGM_LANG ) . '</small></div>' .
		'<br />';
		echo '<input type="radio" id="agm-wmi-auto-no" name="agm_google_maps[wmi-shortcode_only]" value="1" ' . $no . ' />' .
			'&nbsp;' .
			'<label for="agm-wmi-auto-no">' . __( 'Ich möchte angeben, welche Karten den Standort des Besuchers mit einem Shortcode-Attribut anzeigen', AGM_LANG ) . '</label>' .
			'<div><small>' . __( 'Du kannst den Standort des Besuchers auf Deinen Karten anzeigen, indem Du <code>visitor_location=&quot;yes&quot;</code> zu Deinen Shortcodes hinzufügst', AGM_LANG ) . '</small></div>' .
		'<br />';

		$center = $this->_get_options( 'auto_center' );
		$center = $center ? 'checked="checked"' : '';
		echo '<br />';
		echo '<input type="hidden" name="agm_google_maps[wmi-auto_center]" value="" />' .
			'<input type="checkbox" id="agm-wmi-auto_center" name="agm_google_maps[wmi-auto_center]" value="1" ' . $center . ' />' .
			'&nbsp;' .
			'<label for="agm-wmi-auto_center">' . __( 'Karte automatisch auf den Standort des Besuchers zentrieren', AGM_LANG ) . '</label>' .
		'<br />';

		$marker = $this->_get_options( 'marker' );
		$marker = $marker ? 'checked="checked"' : '';
		echo '<input type="hidden" name="agm_google_maps[wmi-marker]" value="" />' .
			'<input type="checkbox" id="agm-wmi-marker" name="agm_google_maps[wmi-marker]" value="1" ' . $marker . ' />' .
			'&nbsp;' .
			'<label for="agm-wmi-marker">' . __( 'Standort des Besuchers automatisch zur Markerliste meiner Karte hinzufügen', AGM_LANG ) . '</label>' .
			'<div><small>' . __( 'Standardmäßig wird der Standort des Besuchers nur zur Karte hinzugefügt. Aktiviere diese Option, wenn Du ihn auch in der Markerliste anzeigen möchtest.', AGM_LANG ) . '</small></div>' .
		'<br />';
	}

	public function create_marker_options_box() {
		$label = $this->_get_options( 'label' );
		$label = $label ? $label : __( 'Das bist du', AGM_LANG );
		echo '<label for="agm-wmi-label">' . __( 'Besuchermarkierung', AGM_LANG ) . '</label>' .
			'&nbsp;' .
			'<input type="text" class="widefat" id="agm-wmi-label" name="agm_google_maps[wmi-label]" value="' . esc_attr( $label ) . '" />' .
		'<br />';

		$icon = $this->_get_options( 'icon' );
		echo '<label for="agm-wmi-icon">' . __( 'Besuchermarker-Symbol', AGM_LANG ) . '</label>' .
			$this->_create_icons_box() .
			'<input type="text" class="widefat" id="agm-wmi-icon" name="agm_google_maps[wmi-icon]" value="' . esc_attr( $icon ) . '" />' .
			'<div><small>' . __( 'Leer lassen, um das Standardsymbol zu verwenden', AGM_LANG ) . '</small></div>' .
		'<br />';
	}

	private function _create_icons_box() {
		$out = '';
		$icons = AgmAdminMaps::list_icons();
		foreach ( $icons as $icon ) {
			$out .= "<a href='#select'><img src='{$icon}' class='marker-icon-32' /></a> ";
		}
		$out = '<div id="agm_google_maps-wmi-preset_icons">' . $out . '</div>';
		ob_start();
		?>
		<script type="text/javascript">
		jQuery(function () {
			jQuery( '#agm_google_maps-wmi-preset_icons a' ).on('click', function () {
				jQuery( '#agm-wmi-icon' ).val( jQuery(this).find('img').attr('src') );
				return false;
			});
		});
		</script>
		<?php
		$out .= ob_get_clean();
		return $out;
	}

	private function _get_options( $key = 'shortcode_only' ) {
		$opts = apply_filters(
			'agm_google_maps-options-wmi',
			get_option( 'agm_google_maps' )
		);
		return @$opts['wmi-' . $key];
	}
}

class Agm_Wmi_UserPages {

	private function __construct() {
		$this->_add_hooks();
	}

	public static function serve() {
		$me = new Agm_Wmi_UserPages();
	}

	private function _add_hooks() {
		add_action(
			'agm-user-scripts',
			array( $this, 'load_scripts' )
		);
		add_action(
			'agm_google_maps-add_javascript_data',
			array( $this, 'add_wmi_data' )
		);

		// Shortcode attribute
		add_filter(
			'agm-shortcode-defaults',
			array( $this, 'attributes_defaults' )
		);
		add_filter(
			'agm-shortcode-overrides',
			array( $this, 'overrides_process' ),
			10, 2
		);
	}

	public function attributes_defaults( $defaults ) {
		$defaults['visitor_location'] = false;
		return $defaults;
	}

	public function overrides_process( $overrides, $atts ) {
		if ( @$atts['visitor_location'] ) {
			$overrides['visitor_location'] = $atts['visitor_location'];
		}
		return $overrides;
	}

	public function load_scripts() {
		lib3()->ui->add( AGM_PLUGIN_URL . 'js/user/where-am-i.min.js', 'front' );
	}

	public function add_wmi_data() {
		$label = $this->_get_options( 'label' );
		$label = $label ? $label : __( 'Das bist du', AGM_LANG );
		printf(
			'<script type="text/javascript">if ( window._agmWmi === undefined) _agmWmi = {
				"add_marker": %d,
				"shortcode_only": %d,
				"auto_center": %d,
				"marker_label": "%s",
				"icon": "%s"
			};</script>',
			(int)$this->_get_options( 'marker' ),
			(int)$this->_get_options( 'shortcode_only' ),
			(int)$this->_get_options( 'auto_center' ),
			esc_js( $label ),
			esc_js( $this->_get_options( 'icon' ) )
		);
	}

	private function _get_options( $key = 'shortcode_only' ) {
		$opts = apply_filters( 'agm_google_maps-options-wmi', get_option( 'agm_google_maps' ) );
		return @$opts['wmi-' . $key];
	}
}

if ( is_admin() ) {
	Agm_Wmi_AdminPages::serve();
} else {
	Agm_Wmi_UserPages::serve();
}