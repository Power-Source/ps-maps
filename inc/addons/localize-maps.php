<?php
/*
Plugin Name: Erzwinge Lokalisierung für Karten
Description: Standardmäßig werden Deine Karten entsprechend der bevorzugten Browsersprache Deiner Besucher angezeigt. Wenn Du dieses Add-on aktivierst, werden Deine Karten in der Sprache angezeigt, die Du in den Plugin-Einstellungen auswählst.
Plugin URI:  https://cp-psource.github.io/ps-maps/
Version:     1.0
Author:      PSOURCE
*/

class Agm_Locale_AdminPages {

	private function __construct() {}

	public static function serve() {
		$me = new Agm_Locale_AdminPages();
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
			'agm_google_maps_forced_l10n',
			__( 'Lokalisierung', AGM_LANG ),
			'__return_false',
			'agm_google_maps_options_page'
		);
		add_settings_field(
			'agm_google_maps_l10n_languages',
			__( 'Sprachen', AGM_LANG ),
			array( $this, 'create_languages_box' ),
			'agm_google_maps_options_page',
			'agm_google_maps_forced_l10n'
		);
	}

	public function create_languages_box() {
		$language = $this->_get_options( 'language' );
		?>
		<label for="agm-locale-select_language">
			<?php _e( 'Wähle Deine Sprache', AGM_LANG ); ?>:
		</label>
		<select id="agm-locale-select_language" name="agm_google_maps[locale-language]">
			<option value=""><?php _e( 'Browser erkennen (Standard)', AGM_LANG ); ?></option>
			<?php foreach ( Agm_Locale_PublicPages::get_supported_languages() as $key => $lang ) : ?>
			<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $language ); ?>>
				<?php echo esc_html( $lang ); ?>
			</option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	private function _get_options( $key = 'language' ) {
		$opts = apply_filters(
			'agm_google_maps-options-locale',
			get_option( 'agm_google_maps' )
		);
		return @$opts['locale-' . $key];
	}
}

class Agm_Locale_PublicPages {

	private function __construct() {}

	public static function serve() {
		$me = new Agm_Locale_PublicPages();
		$me->_add_hooks();
	}

	public static function get_supported_languages() {
		return array(
			'ar' => __( 'Arabisch', AGM_LANG ),
			'eu' => __( 'Baskisch', AGM_LANG ),
			'bg' => __( 'Bulgarisch', AGM_LANG ),
			'bn' => __( 'Bengalisch', AGM_LANG ),
			'ca' => __( 'Katalanisch', AGM_LANG ),
			'cs' => __( 'Tschechisch', AGM_LANG ),
			'da' => __( 'änisch', AGM_LANG ),
			'de' => __( 'Deutsch', AGM_LANG ),
			'el' => __( 'Griechisch', AGM_LANG ),
			'en' => __( 'Englisch', AGM_LANG ),
			'en-AU' => __( 'Englisch (Australien)', AGM_LANG ),
			'en-GB' => __( 'Englisch (Großbritannien)', AGM_LANG ),
			'es' => __( 'Spanisch', AGM_LANG ),
			'eu' => __( 'Baskisch', AGM_LANG ),
			'fa' => __( 'Farsi', AGM_LANG ),
			'fi' => __( 'Finnisch', AGM_LANG ),
			'fil' => __( 'Filipino', AGM_LANG ),
			'fr' => __( 'Französisch', AGM_LANG ),
			'gl' => __( 'Galicisch', AGM_LANG ),
			'gu' => __( 'Gujarati', AGM_LANG ),
			'hi' => __( 'Hindi', AGM_LANG ),
			'hr' => __( 'Kroatisch', AGM_LANG ),
			'hu' => __( 'Ungarisch', AGM_LANG ),
			'id' => __( 'Indonesisch', AGM_LANG ),
			'it' => __( 'Italienisch', AGM_LANG ),
			'iw' => __( 'Hebräisch', AGM_LANG ),
			'ja' => __( 'Japanisch', AGM_LANG ),
			'kn' => __( 'Kannada', AGM_LANG ),
			'ko' => __( 'Koreanisch', AGM_LANG ),
			'lt' => __( 'Litauisch', AGM_LANG ),
			'lv' => __( 'Lettisch', AGM_LANG ),
			'ml' => __( 'Malayalam', AGM_LANG ),
			'mr' => __( 'Marathi', AGM_LANG ),
			'nl' => __( 'Niederländisch', AGM_LANG ),
			'no' => __( 'Norwegisch', AGM_LANG ),
			'pl' => __( 'Polnisch', AGM_LANG ),
			'pt' => __( 'Portugiesisch', AGM_LANG ),
			'pt-BR' => __( 'Portugiesisch (Brasilien)', AGM_LANG ),
			'pt-PT' => __( 'Portugiesisch (Portugal)', AGM_LANG ),
			'ro' => __( 'Rumänisch', AGM_LANG ),
			'ru' => __( 'Russisch', AGM_LANG ),
			'sk' => __( 'Slowakisch', AGM_LANG ),
			'sl' => __( 'Slowenisch', AGM_LANG ),
			'sr' => __( 'Serbisch', AGM_LANG ),
			'sv' => __( 'Schwedisch', AGM_LANG ),
			'tl' => __( 'Tagalog', AGM_LANG ),
			'ta' => __( 'Tamil', AGM_LANG ),
			'te' => __( 'Telugu', AGM_LANG ),
			'th' => __( 'Thai', AGM_LANG ),
			'tr' => __( 'Türkisch', AGM_LANG ),
			'uk' => __( 'Ukrainisch', AGM_LANG ),
			'vi' => __( 'Vietnamesisch', AGM_LANG ),
			'zh-CN' => __( 'Chinesisch (vereinfacht)', AGM_LANG ),
			'zh-TW' => __( 'Chinesisch (traditionell)', AGM_LANG ),
		);
	}

	private function _get_options( $key = 'language' ) {
		$opts = apply_filters(
			'agm_google_maps-options-locale',
			get_option( 'agm_google_maps' )
		);
		return @$opts['locale-' . $key];
	}

	private function _add_hooks() {
		add_action( 'agm_google_maps-add_javascript_data', array($this, 'add_language_data' ) );
	}

	public function add_language_data() {
		$language = $this->_get_options( 'language' );
		$all_languages = array_keys( self::get_supported_languages() );
		if ( ! in_array( $language, $all_languages ) ) { return false; }
		printf(
			'<script type="text/javascript">if (typeof(_agmLanguage) == "undefined") _agmLanguage="%s";</script>',
			$language
		);
	}
}

if ( is_admin() ) {
	Agm_Locale_AdminPages::serve();
} else {
	Agm_Locale_PublicPages::serve();
}