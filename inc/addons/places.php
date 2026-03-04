<?php
/*
Plugin Name: Google Places-Unterstützung
Description: Ermöglicht die Anzeige von nahegelegenen Orten - neue Optionen werden im Kartenoptionsdialog verfügbar sein.
Plugin URI:  https://cp-psource.github.io/ps-maps/
Version:     1.0
Author:      PSOURCE
*/

class Agm_PlacesAdminPages {

	private function __construct() {}

	public static function serve() {
		$me = new Agm_PlacesAdminPages();
		$me->_add_hooks();
	}

	private function _add_hooks() {
		// UI
		add_action(
			'agm-admin-scripts',
			array( $this, 'load_scripts' )
		);
		add_filter(
			'agm-save-options',
			array( $this, 'prepare_for_save' ),
			10, 2
		);
		add_filter(
			'agm-load-options',
			array( $this, 'prepare_for_load' ),
			10, 2
		);

		// Adding in map defaults
		add_action(
			'agm_google_maps-options',
			array( $this, 'inject_default_location_types' )
		);
	}

	public function load_scripts() {
		global $hook_suffix;
		lib3()->ui->add( AGM_PLUGIN_URL . 'js/admin/places.min.js', $hook_suffix );
	}

	public function prepare_for_save( $options, $raw ) {
		$options['show_places'] = isset( $raw['show_places']) ? $raw['show_places'] : 0;
		$options['places_radius'] = isset( $raw['places_radius']) ? $raw['places_radius'] : 1000;
		$options['place_types'] = isset( $raw['place_types']) ? $raw['place_types'] : array();
		return $options;
	}

	public function prepare_for_load( $options, $raw ) {
		$options['show_places'] = isset( $raw['show_places']) ? $raw['show_places'] : 0;
		$options['places_radius'] = isset( $raw['places_radius']) ? $raw['places_radius'] : 1000;
		$options['place_types'] = isset( $raw['place_types']) ? $raw['place_types'] : array();
		return $options;
	}

	public function inject_default_location_types( $options ) {
		$options['place_types'] = array(
			'accounting' => __( 'Buchhaltung', AGM_LANG ),
			'airport' => __( 'Flughafen', AGM_LANG ),
			'amusement_park' => __( 'Vergnügungspark', AGM_LANG ),
			'aquarium' => __( 'Aquarium', AGM_LANG ),
			'art_gallery' => __( 'Kunstgalerie', AGM_LANG ),
			'atm' => __( 'Geldautomat', AGM_LANG ),
			'bakery' => __( 'Bäckerei', AGM_LANG ),
			'bank' => __( 'Bank', AGM_LANG ),
			'bar' => __( 'Bar', AGM_LANG ),
			'beauty_salon' => __( 'Kosmetikstudio', AGM_LANG ),
			'bicycle_store' => __( 'Fahrradladen', AGM_LANG ),
			'book_store' => __( 'Buchhandlung', AGM_LANG ),
			'bowling_alley' => __( 'Bowlingbahn', AGM_LANG ),
			'bus_station' => __( 'Bushaltestelle', AGM_LANG ),
			'cafe' => __( 'Café', AGM_LANG ),
			'campground' => __( 'Campingplatz', AGM_LANG ),
			'car_dealer' => __( 'Autohändler', AGM_LANG ),
			'car_rental' => __( 'Autovermietung', AGM_LANG ),
			'car_repair' => __( 'Autoreparatur', AGM_LANG ),
			'car_wash' => __( 'Autowäsche', AGM_LANG ),
			'casino' => __( 'Casino', AGM_LANG ),
			'cemetery' => __( 'Friedhof', AGM_LANG ),
			'church' => __( 'Kirche', AGM_LANG ),
			'city_hall' => __( 'Rathaus', AGM_LANG ),
			'clothing_store' => __( 'Bekleidungsgeschäft', AGM_LANG ),
			'convenience_store' => __( 'Lebensmittelgeschäft', AGM_LANG ),
			'courthouse' => __( 'Gericht', AGM_LANG ),
			'dentist' => __( 'Zahnarzt', AGM_LANG ),
			'department_store' => __( 'Kaufhaus', AGM_LANG ),
			'doctor' => __( 'Arzt', AGM_LANG ),
			'electrician' => __( 'Elektriker', AGM_LANG ),
			'electronics_store' => __( 'Elektronikgeschäft', AGM_LANG ),
			'embassy' => __( 'Botschaft', AGM_LANG ),
			'establishment' => __( 'Einrichtung', AGM_LANG ),
			'finance' => __( 'Finanzen', AGM_LANG ),
			'fire_station' => __( 'Feuerwache', AGM_LANG ),
			'florist' => __( 'Florist', AGM_LANG ),
			'food' => __( 'Lebensmittel', AGM_LANG ),
			'funeral_home' => __( 'Bestattungsinstitut', AGM_LANG ),
			'furniture_store' => __( 'Möbelgeschäft', AGM_LANG ),
			'gas_station' => __( 'Tankstelle', AGM_LANG ),
			'general_contractor' => __( 'Generalunternehmer', AGM_LANG ),
			'grocery_or_supermarket' => __( 'Lebensmittelgeschäft oder Supermarkt', AGM_LANG ),
			'gym' => __( 'Fitnessstudio', AGM_LANG ),
			'hair_care' => __( 'Friseursalon', AGM_LANG ),
			'hardware_store' => __( 'Baumarkt', AGM_LANG ),
			'health' => __( 'Gesundheit', AGM_LANG ),
			'hindu_temple' => __( 'Hindu-Tempel', AGM_LANG ),
			'home_goods_store' => __( 'Haushaltswarenladen', AGM_LANG ),
			'hospital' => __( 'Krankenhaus', AGM_LANG ),
			'insurance_agency' => __( 'Versicherungsagentur', AGM_LANG ),
			'jewelry_store' => __( 'Juweliergeschäft', AGM_LANG ),
			'laundry' => __( 'Wäscherei', AGM_LANG ),
			'lawyer' => __( 'Rechtsanwalt', AGM_LANG ),
			'library' => __( 'Bibliothek', AGM_LANG ),
			'liquor_store' => __( 'Spirituosengeschäft', AGM_LANG ),
			'local_government_office' => __( 'Kommunalverwaltung', AGM_LANG ),
			'locksmith' => __( 'Schlüsseldienst', AGM_LANG ),
			'lodging' => __( 'Unterkunft', AGM_LANG ),
			'meal_delivery' => __( 'Essenslieferung', AGM_LANG ),
			'meal_takeaway' => __( 'Essen zum Mitnehmen', AGM_LANG ),
			'mosque' => __( 'Moschee', AGM_LANG ),
			'movie_rental' => __( 'Videoverleih', AGM_LANG ),
			'movie_theater' => __( 'Kino', AGM_LANG ),
			'moving_company' => __( 'Umzugsunternehmen', AGM_LANG ),
			'museum' => __( 'Museum', AGM_LANG ),
			'night_club' => __( 'NNachtclub', AGM_LANG ),
			'painter' => __( 'Maler', AGM_LANG ),
			'park' => __( 'Park', AGM_LANG ),
			'parking' => __( 'Parkplatz', AGM_LANG ),
			'pet_store' => __( 'Zoohandlung', AGM_LANG ),
			'pharmacy' => __( 'Apotheke', AGM_LANG ),
			'physiotherapist' => __( 'Physiotherapeut', AGM_LANG ),
			'place_of_worship' => __( 'Gebetsstätte', AGM_LANG ),
			'plumber' => __( 'Klempner', AGM_LANG ),
			'police' => __( 'Polizei', AGM_LANG ),
			'post_office' => __( 'Postamt', AGM_LANG ),
			'real_estate_agency' => __( 'Immobilienagentur', AGM_LANG ),
			'restaurant' => __( 'Restaurant', AGM_LANG ),
			'roofing_contractor' => __( 'Dachdecker', AGM_LANG ),
			'rv_park' => __( 'Wohnmobilpark', AGM_LANG ),
			'school' => __( 'Schule', AGM_LANG ),
			'shoe_store' => __( 'Schuhgeschäft', AGM_LANG ),
			'shopping_mall' => __( 'Einkaufszentrum', AGM_LANG ),
			'spa' => __( 'Spa', AGM_LANG ),
			'stadium' => __( 'Stadion', AGM_LANG ),
			'storage' => __( 'Lager', AGM_LANG ),
			'store' => __( 'Geschäft', AGM_LANG ),
			'subway_station' => __( 'U-Bahn-Station', AGM_LANG ),
			'synagogue' => __( 'Synagoge', AGM_LANG ),
			'taxi_stand' => __( 'Taxistand', AGM_LANG ),
			'train_station' => __( 'Bahnhof', AGM_LANG ),
			'travel_agency' => __( 'Reisebüro', AGM_LANG ),
			'university' => __( 'Universität', AGM_LANG ),
			'veterinary_care' => __( 'Tierarztpraxis', AGM_LANG ),
			'zoo' => __( 'Zoo', AGM_LANG ),
		);
		return $options;
	}
}


class Agm_PlacesUserPages {
	private function __construct() {}

	public static function serve() {
		$me = new Agm_PlacesUserPages();
		$me->_add_hooks();
	}

	private function _add_hooks() {
		// UI
		add_action(
			'agm-user-scripts',
			array( $this, 'load_scripts' )
		);
		add_filter(
			'agm-load-options',
			array( $this, 'prepare_for_load' ),
			10, 2
		);
	}

	public function load_scripts() {
		lib3()->ui->add( AGM_PLUGIN_URL . 'js/user/places.min.js', 'front' );
	}

	public function prepare_for_load( $options, $raw ) {
		$options['show_places'] = isset( $raw['show_places']) ? $raw['show_places'] : 0;
		$options['places_radius'] = isset( $raw['places_radius']) ? $raw['places_radius'] : 1000;
		$options['place_types'] = isset( $raw['place_types']) ? $raw['place_types'] : array();
		return $options;
	}
}

function _agm_places_add_library_support( $data ) {
	$data['libraries'] = $data['libraries'] ? $data['libraries'] : array();
	$data['libraries'][] = 'places';
	return $data;
}
add_filter( 'agm_google_maps-javascript-data_object', '_agm_places_add_library_support' );

if ( is_admin() ) {
	Agm_PlacesAdminPages::serve();
} else {
	Agm_PlacesUserPages::serve();
}