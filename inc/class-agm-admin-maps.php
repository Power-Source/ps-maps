<?php

/**
 * Handles admin maps interface.
 */
class AgmAdminMaps {

	private $model;

	/**
	 * Entry method.
	 *
	 * Creates and handles the Admin interface for the Plugin.
	 *
	 * @access public
	 * @static
	 */
	static function serve() {
		$me = new AgmAdminMaps();
		$me->model = new AgmMapModel();
		$me->add_hooks();
	}

	/**
	 * Registers settings.
	 * This function also displays the addon settings in the end.
	 */
	public function register_settings() {
		register_setting( 'agm_google_maps', 'agm_google_maps', array( $this, 'sanitize_settings' ) );
		$form = new Agm_AdminFormRenderer();

		// Overview
		add_settings_section(
			'agm_google_maps_overview',
			__( 'Übersicht', AGM_LANG ),
			'__return_false',
			'agm_google_maps_options_page'
		);
		add_settings_field(
			'agm_google_maps_default_height',
			'',
			array( $form, 'create_overview_box' ),
			'agm_google_maps_options_page',
			'agm_google_maps_overview'
		);

		// Options
		add_settings_section(
			'agm_google_maps',
			__( 'Optionen', AGM_LANG ),
			'__return_false',
			'agm_google_maps_options_page'
		);

		add_settings_field(
			'agm_google_maps_default_map_api_key',
			__( 'Google Maps API-Schlüssel', AGM_LANG ),
			array( $form, 'create_map_api_key_box' ),
			'agm_google_maps_options_page',
			'agm_google_maps'
		);

		add_settings_field(
			'agm_google_maps_default_height',
			__( 'Standard-Kartenhöhe', AGM_LANG ),
			array( $form, 'create_height_box' ),
			'agm_google_maps_options_page',
			'agm_google_maps'
		);
		add_settings_field(
			'agm_google_maps_default_width',
			__( 'Standard-Kartenbreite', AGM_LANG ),
			array( $form, 'create_width_box' ),
			'agm_google_maps_options_page',
			'agm_google_maps'
		);
		add_settings_field(
			'agm_google_maps_default_map_type',
			__( 'Standard-Kartentyp', AGM_LANG ),
			array( $form, 'create_map_type_box' ),
			'agm_google_maps_options_page',
			'agm_google_maps'
		);

		add_settings_field(
			'agm_google_maps_default_map_zoom',
			__( 'Standard-Kartenzoom', AGM_LANG ),
			array( $form, 'create_map_zoom_box' ),
			'agm_google_maps_options_page',
			'agm_google_maps'
		);

		add_settings_field(
			'agm_google_maps_default_map_units',
			__( 'Standard-Karteneinheiten', AGM_LANG ),
			array( $form, 'create_map_units_box' ),
			'agm_google_maps_options_page',
			'agm_google_maps'
		);
		add_settings_field(
			'agm_google_maps_default_image_size',
			__( 'Standard-Bildgröße', AGM_LANG ),
			array( $form, 'create_image_size_box' ),
			'agm_google_maps_options_page',
			'agm_google_maps'
		);
		add_settings_field(
			'agm_google_maps_default_image_limit',
			__( 'Standard-Bildlimit', AGM_LANG ),
			array( $form, 'create_image_limit_box' ),
			'agm_google_maps_options_page',
			'agm_google_maps'
		);
		add_settings_field(
			'agm_google_maps_default_map_alignment',
			__( 'Standard-Kartenausrichtung', AGM_LANG ),
			array( $form, 'create_alignment_box' ),
			'agm_google_maps_options_page',
			'agm_google_maps'
		);
		add_settings_field(
			'agm_google_maps_snapping',
			__( 'Snapping', AGM_LANG ),
			array( $form, 'create_snapping_box' ),
			'agm_google_maps_options_page',
			'agm_google_maps'
		);
		add_settings_field(
			'agm_google_maps_directions_snapping',
			__( 'Directions Snapping', AGM_LANG ),
			array( $form, 'create_directions_snapping_box' ),
			'agm_google_maps_options_page',
			'agm_google_maps'
		);
		add_settings_field(
			'agm_google_maps_custom_css',
			__( 'Zusätzliches CSS', AGM_LANG ),
			array( $form, 'create_custom_css_box' ),
			'agm_google_maps_options_page',
			'agm_google_maps'
		);
		add_settings_field(
			'agm_google_maps_shortcode',
			__( 'Ändere Shortcode', AGM_LANG ),
			array( $form, 'create_alt_shortcode_box' ),
			'agm_google_maps_options_page',
			'agm_google_maps'
		);

		// Section
		add_settings_section(
			'agm_google_maps_fields',
			__( 'Benutzerdefinierte Felder', AGM_LANG ),
			'__return_false',
			'agm_google_maps_options_page'
		);
		add_settings_field(
			'agm_google_maps_use_custom_fields',
			__( 'Benutzerdefinierte Post-Meta-Felder verwenden', AGM_LANG ),
			array( $form, 'create_use_custom_fields_box' ),
			'agm_google_maps_options_page',
			'agm_google_maps_fields'
		);
		add_settings_field(
			'agm_google_maps_custom_fields_map',
			__( 'Benutzerdefinierte Kartenfelder', AGM_LANG ),
			array( $form, 'create_custom_fields_map_box' ),
			'agm_google_maps_options_page',
			'agm_google_maps_fields'
		);
		add_settings_field(
			'agm_google_maps_custom_fields_options',
			__( 'Wenn diese Felder gefunden werden, möchte ich', AGM_LANG ),
			array( $form, 'create_custom_fields_options_box' ),
			'agm_google_maps_options_page',
			'agm_google_maps_fields'
		);

		// The addons are not always displayed...
		if (
			( ! is_multisite() && current_user_can( 'manage_options' ) ) ||
			(is_multisite() && current_user_can( 'manage_network_options' ))
			) { // On multisite, plugins are available only to network admins

			add_settings_section(
				'agm_google_maps_plugins',
				__( 'Add-ons', AGM_LANG ),
				'__return_false',
				'agm_google_maps_options_page'
			);
			add_settings_field(
				'agm_google_maps_all_plugins',
				'',
				array( $form, 'create_plugins_box' ),
				'agm_google_maps_options_page', 'agm_google_maps_plugins'
			);
		}

		add_settings_section(
			'agm_google_maps_sep1',
			'-',
			'__return_false',
			'agm_google_maps_options_page'
		);

		// give the addons an easy possibility to display their options.
		// Note: Options are displayed in order in which addons were activated!
		do_action( 'agm_google_maps-options-plugins_options', $form );
	}

	/**
	 * Sanitize the options before they are written to the database.
	 *
	 * @since  2.8.2
	 * @param  array $raw_settings
	 * @return array Sanitized settings
	 */
	public function sanitize_settings( $raw_settings ) {
		$raw_settings['additional_css'] = $this->sanitize_css( $raw_settings['additional_css'] );
		return $raw_settings;
	}

	/**
	 * Creates Admin menu entry.
	 */
	public function create_admin_menu_entry() {
		// Show branding for singular installs.
		$title = is_multisite() ? __( 'PS Maps', AGM_LANG ) : 'PS Maps';

		// Register our google maps options page.
		$hook = add_options_page(
			$title,
			$title,
			'manage_options',
			'agm_google_maps',
			array( $this, 'create_admin_page' )
		);

		lib3()->ui->add( TheLib_Ui::MODULE_VNAV, $hook );
	}

	/**
	 * Creates Admin menu page.
	 */
	public function create_admin_page() {
		include AGM_VIEWS_DIR . 'plugin_settings.php';
	}

	/**
	 * Hooks to 'current_screen' and enqueues scripts used on post-edior screen.
	 *
	 * @since  2.9
	 */
	public function load_scripts( $screen ) {
		if ( 'post' == @$screen->base || 'widgets' == @$screen->base ) {
			lib3()->ui->add( TheLib_Ui::MODULE_CORE );
			lib3()->ui->add( TheLib_Ui::MODULE_SELECT );
		} elseif ( 'settings_page_agm_google_maps' == $screen->base ) {
			lib3()->ui->add( AGM_PLUGIN_URL . 'css/google_maps_admin.min.css' );
		}
	}

	/**
	 * Hook Scripts to post editor.
	 */
	private function shared_scripts() {
		$opt = apply_filters( 'agm_google_maps-options', get_option( 'agm_google_maps' ) );
		$defaults = array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'root_url' => AGM_PLUGIN_URL,
			'is_multisite' => (int)is_multisite(),
			'libraries' => array(),
			'maps_api_key' => !empty($opt['map_api_key']) ? $opt['map_api_key'] : '',
			'nonce' => wp_create_nonce( 'agm_google_maps' ),
		);

		$vars = apply_filters(
			'agm_google_maps-javascript-data_object',
			apply_filters( 'agm_google_maps-javascript-data_object-admin', $defaults )
		);

		lib3()->ui->data( '_agm', $vars );
		lib3()->ui->data( '_agm_root_url', AGM_PLUGIN_URL );

		lib3()->ui->add( AGM_PLUGIN_URL . 'js/loader.js' );
		lib3()->ui->add( AGM_PLUGIN_URL . 'js/admin/google-maps.js' );
	}

	/**
	 * Adds an editor button to WordPress editor and handle Editor interface.
	 */
	public function js_editor_button() {
		$agm_map = 'agm_map' == AgmMapModel::get_config( 'shortcode_map' );

		lib3()->ui->data(
			'l10nEditor',
			array(
				'loading' => __( 'Karten werden geladen... Bitte warten.', AGM_LANG ),
				'use_this_map' => __( 'Diese Karte einfügen', AGM_LANG ),
				'preview_or_edit' => __( 'Vorschau/Bearbeiten', AGM_LANG ),
				'delete_map' => __( 'Löschen', AGM_LANG ),
				'add_map' => __( 'Karte hinzufügen', AGM_LANG ),
				'google_maps' => __( 'PS Maps', AGM_LANG ),
				'load_next_maps' => __( 'Weiter &raquo;', AGM_LANG ),
				'load_prev_maps' => __( '&laquo; Zurück', AGM_LANG ),
				'existing_map' => __( 'Vorhandene Karten', AGM_LANG ),
				'edit_map' => __( 'Karte anzeigen und bearbeiten', AGM_LANG ),
				'no_existing_maps' => __( 'Keine vorhandenen Karten', AGM_LANG ),
				'new_map' => __( 'Neue Karte', AGM_LANG ),
				'advanced' => __( 'Erweiterter Modus', AGM_LANG ),
				'advanced_mode_activate_help' => __( 'Erweiterter Modus: Mehrere Karten zu einer neuen Karte zusammenführen oder mehrere Karten löschen', AGM_LANG ),
				'advanced_mode_help' => __( 'Erstellt eine neue Karte, die alle Marker der ausgewählten Karten enthält', AGM_LANG ),
				'advanced_off' => __( 'Erweiterter Modus beenden', AGM_LANG ),
				'merge_locations' => __( 'Zusammenführen', AGM_LANG ),
				'batch_delete' => __( 'Löschen', AGM_LANG ),
				'new_map_intro' => __( 'Erstellt eine neue Karte, die in diesen Beitrag oder diese Seite eingefügt werden kann', AGM_LANG ),
				'no_maps' => __( 'Sie haben noch keine Karten erstellt.', AGM_LANG ),
				'delete_confirmation' => __( 'Möchten Sie diese Karte dauerhaft löschen?', AGM_LANG ),
				'batch_delete_confirmation' => __( 'Möchten Sie alle ausgewählten Karten dauerhaft löschen?', AGM_LANG ),
				'confirm_delete' => __( 'Löschen', AGM_LANG ),
				'confirm_cancel' => __( 'Abbrechen', AGM_LANG ),
			)
		);

		lib3()->ui->data(
			'_agmConfig',
			array(
				'shortcode' => ($agm_map ? 'agm_map' : 'map'),
			)
		);

		$this->shared_scripts();
		lib3()->ui->add( AGM_PLUGIN_URL . 'js/admin/editor.js' );
	}

	public function js_widget_editor() {
		lib3()->ui->data(
			'l10nEditor',
			array(
				'add_map' => __( 'Karte hinzufügen', AGM_LANG ),
				'new_map' => __( 'Neue Karte erstellen', AGM_LANG ),
			)
		);

		$this->shared_scripts();
		lib3()->ui->add( AGM_PLUGIN_URL . 'js/admin/widget-editor.js' );
	}

	/**
	 * Include Google Maps dependencies.
	 */
	public function js_google_maps_api() {
		lib3()->ui->data(
			'l10nStrings',
			array(
				'geocoding_error' => __( 'Beim Geokodieren Deines Standorts ist ein Fehler aufgetreten. Überprüfe die Adresse und versuche es erneut', AGM_LANG ),
				'type_in_location' => __( 'Bitte gib den Ort ein', AGM_LANG ),
				'add' => __( 'Marker hinzufügen', AGM_LANG ),
				'title' => __( 'Titel', AGM_LANG ),
				'body' => __( 'Text', AGM_LANG ),
				'delete_item' => __( 'Löschen', AGM_LANG ),
				'save' => __( 'Änderungen speichern', AGM_LANG ),
				'saved' => __( 'Alle Änderungen gespeichert!', AGM_LANG ),
				'saving' => __( 'Daten werden gesendet...', AGM_LANG ),
				'insert' => __( 'Diese Karte einfügen', AGM_LANG ),
				'map_not_saved' => __( 'Karte nicht gespeichert', AGM_LANG ),
				'map_name_missing' => __( 'Bitte gib dieser Karte einen Namen', AGM_LANG ),
				'please_save_map' => __( 'Bitte speichere die Karte zuerst', AGM_LANG ),
				'go_back' => __( 'Zurück', AGM_LANG ),
				'map_title' => __( 'Gib hier den Kartentitel ein', AGM_LANG ),
				'options' => __( 'Kartenoptionen', AGM_LANG ),
				'options_help' => __( 'Verwende Kartenoptionen, um Größe, Erscheinungsbild, Ausrichtung und Bildleiste der Karte zu ändern', AGM_LANG ),
				'drop_marker' => __( 'Marker setzen', AGM_LANG ),
				'zoom_in_help' => __( 'Tipp: ür beste Kartenqualität <strong>zoome hinein</strong>, um Deine Marker vor dem Speichern zu platzieren', AGM_LANG ),
				'map_associate' => __( 'Karte mit diesem Beitrag verknüpfen', AGM_LANG ),
				'already_associated_width' => __( 'Diese Karte ist bereits mit diesen verknüpft', AGM_LANG ),
				'association_help' => __( 'Das Verknüpfen einer Karte mit einem Beitrag ermöglicht die erweiterte Nutzung der Karte - z.B. dynamische Anzeige im Sidebar-Widget oder in einem erweiterten Mashup', AGM_LANG ),
				'map_size' => __( 'Kartengröße', AGM_LANG ),
				'use_default_size' => __( 'Standardgröße verwenden', AGM_LANG ),
				'map_appearance' => __( 'Kartenerscheinungsbild', AGM_LANG ),
				'map_alignment' => __( 'Kartenausrichtung', AGM_LANG ),
				'map_alignment_left' => __( 'Links', AGM_LANG ),
				'map_alignment_center' => __( 'Zentriert', AGM_LANG ),
				'map_alignment_right' => __( 'Rechts', AGM_LANG ),
				'show_map' => __( 'Karte anzeigen', AGM_LANG ),
				'show_posts' => __( 'Beiträge anzeigen', AGM_LANG ),
				'show_markers' => __( 'Markerliste anzeigen', AGM_LANG ),
				'images_strip' => __( 'Bildleisten-Einstellungen', AGM_LANG ),
				'show_images' => __( 'Bildleiste anzeigen', AGM_LANG ),
				'image_size' => __( 'Bildgröße verwenden', AGM_LANG ),
				'image_limit' => __( 'Diese Anzahl von Bildern anzeigen', AGM_LANG ),
				'add_location' => __( 'Ort hinzufügen:', AGM_LANG ),
				'apply_settings' => __( 'Anwenden', AGM_LANG ),
			)
		);

		$this->shared_scripts();
		do_action( 'agm-admin-scripts' );
	}

	/**
	 * Includes required styles.
	 */
	public function css_load_styles() {
		lib3()->ui->add( AGM_PLUGIN_URL . 'css/google_maps_admin.min.css' );
	}

	/**
	 * Handles map listing requests.
	 */
	public function json_list_maps() {
		check_ajax_referer( 'agm_google_maps', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}
		
		$increment = ! empty( $_POST['increment'] ) ? absint( $_POST['increment'] ) : false;
		$maps = $this->model->get_maps( $increment );
		$total = $this->model->get_maps_total();
		header( 'Content-type: application/json' );
		echo json_encode(
			array(
				'maps' => $maps,
				'total' => $total,
			)
		);
		exit();
	}

	/**
	 * Handles loading a particular map requests.
	 */
	public function json_load_map() {
		check_ajax_referer( 'agm_google_maps', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}
		
		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$map = $this->model->get_map( $id );
		header( 'Content-type: application/json' );
		echo json_encode( $map );
		exit();
	}

	/**
	 * Handles maps creation requests.
	 * Loads defaults and such.
	 */
	public function json_new_map() {
		check_ajax_referer( 'agm_google_maps', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}
		
		$defaults = $this->model->get_map_defaults();
		header( 'Content-type: application/json' );
		echo json_encode(
			array( 'defaults' => $defaults )
		);
		exit();
	}

	/**
	 * Handles map save requests.
	 */
	public function json_save_map() {
		check_ajax_referer( 'agm_google_maps', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}
		
		$id = $this->model->save_map( $_POST );
		header( 'Content-type: application/json' );
		echo json_encode(
			array(
				'status' => $id ? 1 : 0,
				'id' => $id,
			)
		);
		exit();
	}

	/**
	 * Handles map delete requests.
	 */
	public function json_delete_map() {
		check_ajax_referer( 'agm_google_maps', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}
		
		$id = $this->model->delete_map( $_POST );
		header( 'Content-type: application/json' );
		echo json_encode(
			array(
				'status' => $id ? 1 : 0,
				'id' => $id,
			)
		);
		exit();
	}

	/**
	 * Returns an array with all icons
	 */
	public static function list_icons() {
		$icons = glob( AGM_IMG_DIR . '*.png' );
		foreach ( $icons as $k => $v ) {
			$icons[ $k ] = AGM_PLUGIN_URL . 'img/' . basename( $v );
		}
		$icons = apply_filters( 'agm_google_maps-custom_icons', $icons );
		return $icons;
	}

	/**
	 * Handles icons list requests.
	 */
	public function json_list_icons() {
		check_ajax_referer( 'agm_google_maps', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}
		
		// glob() will return filenames based on a path-pattern.
		// Here: all *.png images in the plugin "img/" directory will be found.
		$icons = self::list_icons();
		header( 'Content-type: application/json' );
		echo json_encode( $icons );
		exit();
	}

	/**
	 * Loads associated post titles.
	 */
	public function json_get_post_titles() {
		// Verify nonce for security
		check_ajax_referer( 'agm_google_maps', 'nonce' );
		
		// Check if user has permission (no nopriv access)
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}
		
		$post_ids = isset( $_POST['post_ids'] ) ? array_map( 'absint', (array) $_POST['post_ids'] ) : array();
		$titles = $this->model->get_post_titles( $post_ids );
		$titles = apply_filters( 'agm_google_maps-json_post_titles', $titles );
		header( 'Content-type: application/json' );
		echo json_encode(
			array( 'posts' => $titles )
		);
		exit();
	}

	/**
	 * Merges selected maps into one and echo the resulting data.
	 */
	public function json_merge_maps() {
		check_ajax_referer( 'agm_google_maps', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}
		
		$ids = isset( $_POST['ids'] ) ? array_map( 'absint', (array) $_POST['ids'] ) : array();

		$maps = $this->model->get_maps_by_ids( $ids );
		$map = $this->model->merge_markers( $maps );
		$map['debug'] = $maps;

		$map['id'] = '';
		header( 'Content-type: application/json' );
		echo json_encode( $map );
		exit();
	}

	/**
	 * Handles batch delete requests.
	 */
	public function json_batch_delete() {
		check_ajax_referer( 'agm_google_maps', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}
		
		$ids = isset( $_POST['ids'] ) ? array_map( 'absint', (array) $_POST['ids'] ) : array();
		$count = $this->model->batch_delete_maps( $ids );

		header( 'Content-type: application/json' );
		echo json_encode(
			array( 'count' => $count )
		);

		exit();
	}

	/**
	 * Processes post meta fields and creates a map, if needed.
	 */
	public function process_post_meta( $post_id ) {
		if ( ! $post_id ) {
			return false;
		}

		$post = get_post( $post_id );
		if ( 'publish' != $post->post_status) return false; // Draft, auto-save or something else we don't want

		$opts = apply_filters( 'agm_google_maps-options', get_option( 'agm_google_maps' ) );
		$fields = !empty($opts['custom_fields_map'])
			? $opts['custom_fields_map']
			: array()
		;
		$latitude = $longitude = $address = false;

		if ( !empty($fields['latitude_field']) ) {
			$latitude = get_post_meta( $post_id, $fields['latitude_field'], true );
		}
		if ( !empty($fields['longitude_field']) ) {
			$longitude = get_post_meta( $post_id, $fields['longitude_field'], true );
		}
		if ( !empty($fields['address_field']) ) {
			/*
			 * We allow the address-field to contain a list of field names
			 * @since 2.9.0.5
			 */
			$address = '';
			$address_fields = explode( ',', $fields['address_field'] );
			foreach ( $address_fields as $address_field ) {
				$address_field = trim( $address_field );
				$field_value = get_post_meta( $post_id, $address_field, true );
				$address .= $field_value . ' ';
			}
		}

		$latitude = apply_filters( 'agm_google_maps-post_meta-latitude', $latitude );
		$longitude = apply_filters( 'agm_google_maps-post_meta-longitude', $longitude );
		$address = apply_filters( 'agm_google_maps-post_meta-address', $address );

		if ( ! $latitude && ! $longitude && ! $address ) {
			// Coordinates "0/0" will be interpreted as "not defined"
			return false; // Nothing to process
		}

		$map_id = get_post_meta( $post_id, 'agm_map_created', true );

		if ( $map_id ) {
			$map = $this->model->get_map( $map_id );
			if ( $address ) {
				if ( $address == $map['markers'][0]['title'] ) {
					// Already have a map, nothing to do
					return true;
				} else if ( isset( $fields['discard_old'] ) && $fields['discard_old'] ) {
					// Discaring old map
					$this->model->delete_map( array( 'id' => $map_id ) );
				}
			} else if ( $latitude && $longitude ) {
				if ( $latitude == $map['markers'][0]['position'][0] && $longitude == $map['markers'][0]['position'][1] ) {
					// Already have a map, nothing to do
					return true;
				} else if ( isset( $fields['discard_old'] ) && $fields['discard_old'] ) {
					// Discaring old map
					$this->model->delete_map( array( 'id' => $map_id) );
				}
			}
		}

		return $this->model->autocreate_map(
			$post_id,
			$latitude,
			$longitude,
			$address
		);
	}

	/**
	 * Ajax handler that activates an addon.
	 */
	public function json_activate_plugin() {
		check_ajax_referer( 'agm_google_maps', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}
		
		$plugin = isset( $_POST['plugin'] ) ? sanitize_text_field( $_POST['plugin'] ) : '';
		$status = AgmPluginsHandler::activate_plugin( $plugin );
		echo json_encode(
			array( 'status' => $status ? 1 : 0 )
		);
		exit();
	}

	/**
	 * Ajax handler to deactivate an addon.
	 */
	public function json_deactivate_plugin() {
		check_ajax_referer( 'agm_google_maps', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}
		
		$plugin = isset( $_POST['plugin'] ) ? sanitize_text_field( $_POST['plugin'] ) : '';
		$status = AgmPluginsHandler::deactivate_plugin( $plugin );
		echo json_encode(
			array( 'status' => $status ? 1 : 0 )
		);
		exit();
	}

	/**
	 * Adds needed hooks.
	 *
	 * @access private
	 */
	private function add_hooks() {
		// Step0: Register options and menu
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_menu', array( $this, 'create_admin_menu_entry' ) );

		// Step1a: Add plugin script core requirements and editor interface
		add_action( 'current_screen', array( $this, 'load_scripts' ) );

		add_action( 'admin_print_scripts-post.php', array( $this, 'js_editor_button' ) );
		add_action( 'admin_print_scripts-post-new.php', array( $this, 'js_editor_button' ) );
		add_action( 'admin_print_scripts-widgets.php', array( $this, 'js_widget_editor' ) );

		add_action( 'admin_print_styles-post.php', array( $this, 'css_load_styles' ) );
		add_action( 'admin_print_styles-post-new.php', array( $this, 'css_load_styles' ) );
		add_action( 'admin_print_styles-widgets.php', array( $this, 'css_load_styles' ) );

		// Register post saving handlers
		$opts = apply_filters( 'agm_google_maps-options', get_option( 'agm_google_maps' ) );
		if ( @$opts['use_custom_fields'] ) {
			add_action( 'post_updated', array( $this, 'process_post_meta' ), 1 ); // Note the order
		}

		// Step1b: Add Google Maps dependencies
		add_action( 'admin_print_scripts-post.php', array( $this, 'js_google_maps_api' ) );
		add_action( 'admin_print_scripts-post-new.php', array( $this, 'js_google_maps_api' ) );
		add_action( 'admin_print_scripts-widgets.php', array( $this, 'js_google_maps_api' ) );

		// Step2: Add AJAX request handlers
		add_action( 'wp_ajax_agm_list_maps', array( $this, 'json_list_maps' ) );
		add_action( 'wp_ajax_agm_load_map', array( $this, 'json_load_map' ) );
		add_action( 'wp_ajax_agm_new_map', array( $this, 'json_new_map' ) );
		add_action( 'wp_ajax_agm_save_map', array( $this, 'json_save_map' ) );
		add_action( 'wp_ajax_agm_delete_map', array( $this, 'json_delete_map' ) );
		add_action( 'wp_ajax_agm_list_icons', array( $this, 'json_list_icons' ) );
		add_action( 'wp_ajax_agm_get_post_titles', array( $this, 'json_get_post_titles' ) );
		add_action( 'wp_ajax_agm_merge_maps', array( $this, 'json_merge_maps' ) );
		add_action( 'wp_ajax_agm_batch_delete', array( $this, 'json_batch_delete' ) );

		// AJAX plugin handlers
		add_action( 'wp_ajax_agm_activate_plugin', array( $this, 'json_activate_plugin' ) );
		add_action( 'wp_ajax_agm_deactivate_plugin', array( $this, 'json_deactivate_plugin' ) );
	}

	/**
	 * Removes malicious code from CSS string.
	 *
	 * @since  2.8.2
	 */
	public function sanitize_css( $raw_css ) {
		$css = '';
		if ( is_string( $raw_css ) ) {
			$css = str_replace(
				array( '&' ),
				array( '&amp;' ),
				$raw_css
			);

			/*
			 * Remove all <...> tags from the code.
			 * CSS cannot contain the '<' character, unless inside "content":
			 * "content: '<script>'" will output the TEXT "<script>"
			 */
			$css = preg_replace(
				array(
					// Allowed:
					'/(content\s*:\s*"[^"]*)<(.*?)>/',
					'/(content\s*:\s*\'[^\']*)<(.*?)>/',
					// Not allowed:
					'/<.*?>/',
				),
				array(
					'\\1&lt;\\2&gt;',
					'\\1&lt;\\2&gt;',
					'',
				),
				$css
			);

			$css = str_replace(
				array( '&lt;', '&gt;', '&amp;' ),
				array( '<', '>', '&' ),
				$css
			);
		}

		return $css;
	}
}