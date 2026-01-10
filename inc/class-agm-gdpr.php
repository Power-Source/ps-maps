<?php

class AgmGdpr {

	private function __construct() {}
	private function __clone() {}

	public static function serve() {
		$me = new AgmGdpr;
		$me->_add_hooks();
	}

	private function _add_hooks() {
		add_action( 'admin_init', array( $this, 'add_privacy_copy' ) );

		add_filter(
			'wp_privacy_personal_data_exporters',
			array( $this, 'register_data_exporter' )
		);
		add_filter(
			'wp_privacy_personal_data_erasers',
			array( $this, 'register_data_eraser' )
		);
	}

	/**
	 * Adds privacy body copy text
	 */
	public function add_privacy_copy() {
		if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
			return false;
		}
		wp_add_privacy_policy_content(
			__( 'PS-Maps', 'psmaps' ),
			$this->get_policy_content()
		);
	}

	/**
	 * Registers data exporters for maps
	 *
	 * @param array $exporters Exporters this far.
	 *
	 * @return array
	 */
	public function register_data_exporter( $exporters ) {
		$exporters['agm_google_maps-autocreated'] = array(
			'exporter_friendly_name' => __( 'Von PS Maps automatisch erstellte Karten', 'psmaps' ),
			'callback' => array( $this, 'export_autocreated_maps' ),
		);
		$exporters['agm_google_maps-associated'] = array(
			'exporter_friendly_name' => __( 'Mit PS Maps verknüpfte Karten', 'psmaps' ),
			'callback' => array( $this, 'export_associated_maps' ),
		);
		return $exporters;
	}

	/**
	 * Registers data erasers for maps
	 *
	 * @param array $erasers erasers this far.
	 *
	 * @return array
	 */
	public function register_data_eraser( $erasers ) {
		$erasers['agm_google_maps-autocreated'] = array(
			'eraser_friendly_name' => __( 'Von PS Maps automatisch erstellte Karten', 'psmaps' ),
			'callback' => array( $this, 'erase_autocreated_maps' ),
		);
		$erasers['agm_google_maps-associated'] = array(
			'eraser_friendly_name' => __( 'Mit PS Maps verknüpfte Karten', 'psmaps' ),
			'callback' => array( $this, 'erase_associated_maps' ),
		);
		return $erasers;
	}

	/**
	 * Exports associated maps for the plugin
	 *
	 * @param string $email User email.
	 * @param int    $page Page data.
	 *
	 * @return array
	 */
	public function export_associated_maps( $email, $page = 1 ) {
		$user = get_user_by( 'email', $email );
		$maps = $this->get_associated_maps( $user->ID );

		return $this->get_exported_maps_data(
			$maps,
			'associated',
			__( 'Zugehörige Karten', 'psmaps' )
		);
	}

	/**
	 * Erases associated maps for the plugin
	 *
	 * @param string $email User email.
	 * @param int    $page Page data.
	 *
	 * @return array
	 */
	public function erase_associated_maps( $email, $page = 1 ) {
		$user = get_user_by( 'email', $email );
		$maps = $this->get_associated_maps( $user->ID );

		return $this->erase_maps_data( $maps );
	}

	/**
	 * Exports autocreated maps for the plugin
	 *
	 * @param string $email User email.
	 * @param int    $page Page data.
	 *
	 * @return array
	 */
	public function export_autocreated_maps( $email, $page = 1 ) {
		$user = get_user_by( 'email', $email );
		$maps = $this->get_autocreated_maps( $user->ID );

		return $this->get_exported_maps_data(
			$maps,
			'autocreated',
			__( 'Automatisch erstellte Karten', 'psmaps' )
		);
	}

	/**
	 * Erases autocreated maps for the plugin
	 *
	 * @param string $email User email.
	 * @param int    $page Page data.
	 *
	 * @return array
	 */
	public function erase_autocreated_maps( $email, $page = 1 ) {
		$user = get_user_by( 'email', $email );
		$maps = $this->get_autocreated_maps( $user->ID );

		return $this->erase_maps_data( $maps );
	}

	/**
	 * Packs up maps data into exportable format
	 *
	 * @param array  $maps Maps to export.
	 * @param string $group Group ID.
	 * @param string $label Group label.
	 *
	 * @return array
	 */
	public function get_exported_maps_data( $maps, $group, $label ) {
		$result = array(
			'data' => array(),
			'done' => true,
		);
		if ( empty( $maps ) ) {
			return $result;
		}
		$exports = array();
		foreach ( $maps as $map ) {
			$exports[] = array(
				'item_id' => 'map-' . md5( serialize( $map ) ),
				'group_id' => 'agm_google_maps-' . $group,
				'group_label' => $label,
				'data' => array(
					array(
						'name' => __( 'Map', 'psmaps' ),
						'value' => wp_json_encode( $map ),
					),
				),
			);
		}

		$result['data'] = $exports;
		return $result;
	}

	/**
	 * Actually erases the maps data
	 *
	 * @param array $maps A list of map hashes to remove.
	 *
	 * @return array Response hash
	 */
	public function erase_maps_data( $maps ) {
		$map_ids = wp_list_pluck( $maps, 'id' );
		$response = array(
			'items_removed' => 0,
			'items_retained' => false,
			'messages' => array(),
			'done' => true,
		);

		if ( empty( $map_ids ) ) {
			return $response;
		}

		$model = new AgmMapModel;
		$status = $model->batch_delete_maps( $map_ids );

		$response['items_retained'] = ! $status;
		$response['items_removed'] = count( $map_ids );

		return $response;
	}

	/**
	 * Gets maps associated with posts written by author
	 *
	 * @param int $author_id Post author ID.
	 *
	 * @return array
	 */
	public function get_associated_maps( $author_id ) {
		$model = new AgmMapModel;
		return $model->get_custom_maps(array(
			'post_type' => 'any',
			'post_status' => 'any',
			'author' => $author_id,
			'limit' => 500,
		));
	}

	/**
	 * Gets auto-created maps table IDs by post author
	 *
	 * @param int $author_id Post author ID.
	 *
	 * @return array
	 */
	public function get_autocreated_map_ids( $author_id ) {
		$map_ids = array();

		$post_ids = get_posts(array(
			'post_type' => 'any',
			'post_status' => 'any',
			'author' => $author_id,
			'meta_key' => 'agm_map_created',
			'meta_compare' => 'EXISTS',
			'fields' => 'ids',
			'limit' => 500,
		));
		if ( ! is_array( $post_ids ) ) {
			return $map_ids;
		}

		foreach ( $post_ids as $pid ) {
			$map_id = (int) get_post_meta( (int) $pid, 'agm_map_created', true );
			if ( empty( $map_id ) ) {
				continue;
			}
			$map_ids[] = $map_id;
		}

		return $map_ids;
	}

	/**
	 * Gets actual auto-created maps by post author
	 *
	 * @param int $author_id Post author ID.
	 *
	 * @return array
	 */
	public function get_autocreated_maps( $author_id ) {
		$maps = array();
		$map_ids = $this->get_autocreated_map_ids( $author_id );
		if ( empty( $map_ids ) ) {
			return $maps;
		}

		$model = new AgmMapModel;

		return $model->get_maps_by_ids( $map_ids );
	}

	public function get_policy_content() {
		return '' .
			'<h3>' . __( 'Dritte', 'psmaps' ) . '</h3>' .
			'<p>' . __( 'Diese Webseite verfolgt Deine (anonymen) Standortdaten mithilfe Deiner Browser-API und gibt sie an den Google Maps-API-Dienst weiter.', 'psmaps' ) . '</p>' .
			'<p>' . __( 'Diese Webseite enthält auch Ressourcen von Drittanbietern aus der Google Maps-API, die möglicherweise selbst Cookies setzen.', 'psmaps' ) . '</p>' .
			'<h3>' . __( 'Check-ins', 'psmaps' ) . '</h3>' .
			'<p>' . __( 'Diese Webseite verfolgt möglicherweise Deinen Standort (mit Deiner Zustimmung) in Form eines anonymen oder benutzerbezogenen Check-ins. Diese Informationen können exportiert und entfernt werden.', 'psmaps' ) . '</p>' .
			'<h3>' . __( 'Für Seiten-Mitglieder', 'psmaps' ) . '</h3>' .
			'<p>' . __( 'Diese Webseite verwendet möglicherweise Deine angegebenen Adressinformationen (falls vorhanden), um sie auf einer Karte anzuzeigen und sie somit für die Google Maps-API freizugeben. Diese Informationen können entfernt werden.', 'psmaps' ) . '</p>' .
			'<h3>' . __( 'Für Inhaltsersteller', 'psmaps' ) . '</h3>' .
			'<p>' . __( 'Diese Seite erstellt möglicherweise automatisch Karten anhand der bereitgestellten Standortdaten und/oder verknüpft sie mit den von Dir erstellten Inhalten, z.B. Beiträge und BuddyPress-Aktivitätsaktualisierungen. Dieser Inhalt kann exportiert und entfernt werden.', 'psmaps' ) . '</p>' .
		'';
	}

}