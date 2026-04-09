<?php

class Agm_AdminHelp {

	const SETTINGS_SCREEN_ID = 'settings_page_agm_google_maps';

	private $_help;

	static public function instance() {
		static $Instance = null;

		if ( ! $Instance instanceof Agm_AdminHelp ) {
			$Instance = new Agm_AdminHelp();
			$Instance->_add_hooks();
		}
		return $Instance;
	}

	private function __construct() {
		if ( ! class_exists( 'WpmuDev_ContextualHelp' ) ) {
			require_once AGM_INC_DIR . 'external/class_wd_contextual_help.php';
		}
		$this->_help = new WpmuDev_ContextualHelp();
	}

	private function _get_default_tabs() {
		return array(
			array(
				'id'      => 'agm_google_maps-options',
				'title'   => __( 'Optionen', AGM_LANG ),
				'content' => '' .
					'<p>' . __(
						'Hier kannst Du PS Maps einrichten. ' .
						'Standard-Einstellungen.', AGM_LANG
					) . '</p>'
			),
			array(
				'id'      => 'agm_google_maps-custom_fields',
				'title'   => __( 'Benutzerdefinierte Felder', AGM_LANG ),
				'content' => '' .
					'<p>' . __(
						'Hier kannst Du die automatische Erstellung von ' .
						'neuen Google Maps einrichten, ausgelöst durch Deine vorhandenen ' .
						'benutzerdefinierten Standortfelder.', AGM_LANG
					) . '</p>'
			),
			array(
				'id'      => 'agm_google_maps-addons',
				'title'   => __( 'Add-ons', AGM_LANG ),
				'content' => '' .
					'<p>' . __(
						'Dies sind die optionalen Erweiterungen für Deine PS ' .
						'Maps. Aktiviere oder deaktiviere sie nach Bedarf.', AGM_LANG
					) . '</p>'
			),

		);
	}

	private function _get_default_sidebar() {
		return '' .
			'<h4>' . __( 'PS Maps', AGM_LANG ) . '</h4>' .
			'<ul>' .
				'<li>' .
					'<a href="https://cp-psource.github.io/ps-maps/" target="_blank">' .
						__( 'Project page', AGM_LANG ) .
					'</a>' .
				'</li>' .
				'<li>' .
					'<a href="https://psource.eimen.net/ps-maps/wiki" target="_blank">' .
						__( 'Installation and instructions page', AGM_LANG ) .
					'</a>' .
				'</li>' .
				'<li>' .
					'<a href="https://psource.eimen.net/ps-maps/discussions" target="_blank">' .
						__( 'Support forum', AGM_LANG ) .
					'</a>' .
				'</li>' .
				'<li>' .
					'<a href="https://psource.eimen.net/ps-maps/issues" target="_blank">' .
						__( 'Bug Report', AGM_LANG ) .
					'</a>' .
				'</li>' .
			'</ul>' .
		'';
	}

	private function _add_hooks() {
		add_action(
			'admin_init',
			array( $this, 'initialize' )
		);

		add_filter(
			'plugin_action_links_' . AGM_PLUGIN,
			array( $this, 'add_settings_link' )
		);
	}

	public function initialize() {
		$this->_help->add_page(
			self::SETTINGS_SCREEN_ID,
			$this->_get_default_tabs(),
			$this->_get_default_sidebar()
		);
		$this->_help->initialize();
	}

	public function add_tab( $id, $title, $content ) {
		$this->_help->add_tab(
			self::SETTINGS_SCREEN_ID,
			array(
				'id'      => $id,
				'title'   => $title,
				'content' => $content,
			)
		);
	}

	/**
	 * Add quick link to plugin settings page.
	 *
	 * @param $links Links array.
	 *
	 * @return array
	 */
	public function add_settings_link( $links ) {

		$plugin_link = array(
			'<a href="' . admin_url( 'options-general.php?page=agm_google_maps' ) . '">' . __( "Settings", AGM_LANG ) . '</a>',
		);

		return array_merge( $plugin_link, $links );
	}
};

Agm_AdminHelp::instance();