<?php
/*
Plugin Name: Google POI ausblenden
Description: Blendet optional native Google-Basiskarten-POIs und Transit-Labels aus und deaktiviert klickbare Google-Orte global.
Plugin URI:  https://cp-psource.github.io/ps-maps/
Version:     1.0
Author:      PSOURCE
*/

class Agm_Hgp_AdminPages {

	private function __construct() {}

	public static function serve() {
		$me = new Agm_Hgp_AdminPages();
		$me->_add_hooks();
	}

	private function _add_hooks() {
		add_action(
			'agm_google_maps-options-plugins_options',
			array( $this, 'register_settings' )
		);
	}

	public function register_settings() {
		add_settings_section(
			'agm_google_maps_hgp',
			__( 'Google Karten-POI Verhalten', AGM_LANG ),
			'__return_false',
			'agm_google_maps_options_page'
		);
		add_settings_field(
			'agm_google_maps_hide_google_poi',
			__( 'Google Basiskarte', AGM_LANG ),
			array( $this, 'create_options_box' ),
			'agm_google_maps_options_page',
			'agm_google_maps_hgp'
		);
	}

	public function create_options_box() {
		$opts = $this->_get_options();
		?>
		<label for="agm_google_maps-hgp-disable_clickable_icons">
			<input type="hidden" value="" name="agm_google_maps[hide_google_poi][disable_clickable_icons]" />
			<input type="checkbox"
				value="1"
				id="agm_google_maps-hgp-disable_clickable_icons"
				name="agm_google_maps[hide_google_poi][disable_clickable_icons]"
				<?php checked( $opts['disable_clickable_icons'], true ); ?> />
			&nbsp;
			<?php _e( 'Klickbare Google-Ortssymbole deaktivieren', AGM_LANG ); ?>
		</label>
		<br />

		<label for="agm_google_maps-hgp-hide_poi">
			<input type="hidden" value="" name="agm_google_maps[hide_google_poi][hide_poi]" />
			<input type="checkbox"
				value="1"
				id="agm_google_maps-hgp-hide_poi"
				name="agm_google_maps[hide_google_poi][hide_poi]"
				<?php checked( $opts['hide_poi'], true ); ?> />
			&nbsp;
			<?php _e( 'POI-Symbole der Basiskarte ausblenden', AGM_LANG ); ?>
		</label>
		<br />

		<label for="agm_google_maps-hgp-hide_business_labels">
			<input type="hidden" value="" name="agm_google_maps[hide_google_poi][hide_business_labels]" />
			<input type="checkbox"
				value="1"
				id="agm_google_maps-hgp-hide_business_labels"
				name="agm_google_maps[hide_google_poi][hide_business_labels]"
				<?php checked( $opts['hide_business_labels'], true ); ?> />
			&nbsp;
			<?php _e( 'Business-Labels ausblenden', AGM_LANG ); ?>
		</label>
		<br />

		<label for="agm_google_maps-hgp-hide_transit">
			<input type="hidden" value="" name="agm_google_maps[hide_google_poi][hide_transit]" />
			<input type="checkbox"
				value="1"
				id="agm_google_maps-hgp-hide_transit"
				name="agm_google_maps[hide_google_poi][hide_transit]"
				<?php checked( $opts['hide_transit'], true ); ?> />
			&nbsp;
			<?php _e( 'Transit-Symbole und Labels ausblenden', AGM_LANG ); ?>
		</label>
		<p>
			<em><?php _e( 'Hinweis: Bei Nutzung von Google Map ID kann Cloud-Styling Vorrang vor lokalen Styles haben.', AGM_LANG ); ?></em>
		</p>
		<?php
	}

	private function _get_options() {
		$opt = apply_filters( 'agm_google_maps-options', get_option( 'agm_google_maps' ) );
		$opt = isset( $opt['hide_google_poi'] ) && is_array( $opt['hide_google_poi'] )
			? $opt['hide_google_poi']
			: array();

		return wp_parse_args(
			$opt,
			array(
				'disable_clickable_icons' => false,
				'hide_poi' => false,
				'hide_business_labels' => false,
				'hide_transit' => false,
			)
		);
	}
}

class Agm_Hgp_UserPages {

	private function __construct() {}

	public static function serve() {
		$me = new Agm_Hgp_UserPages();
		$me->_add_hooks();
	}

	private function _add_hooks() {
		add_action(
			'agm-user-scripts',
			array( $this, 'load_scripts' )
		);
		add_filter(
			'agm_google_maps-javascript-data_object-user',
			array( $this, 'add_data' )
		);
	}

	public function load_scripts() {
		lib3()->ui->add( AGM_PLUGIN_URL . 'js/user/hide-google-poi.min.js', 'front' );
	}

	public function add_data( $data ) {
		$data['hide_google_poi'] = $this->_get_options();
		return $data;
	}

	private function _get_options() {
		$opt = apply_filters( 'agm_google_maps-options', get_option( 'agm_google_maps' ) );
		$opt = isset( $opt['hide_google_poi'] ) && is_array( $opt['hide_google_poi'] )
			? $opt['hide_google_poi']
			: array();

		return array(
			'disable_clickable_icons' => ! empty( $opt['disable_clickable_icons'] ),
			'hide_poi' => ! empty( $opt['hide_poi'] ),
			'hide_business_labels' => ! empty( $opt['hide_business_labels'] ),
			'hide_transit' => ! empty( $opt['hide_transit'] ),
		);
	}
}

if ( is_admin() ) {
	Agm_Hgp_AdminPages::serve();
} else {
	Agm_Hgp_UserPages::serve();
}
