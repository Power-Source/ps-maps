<?php
/*
Plugin Name: Markercluster
Description: Bereinigt Deine Karten, indem nahe beieinander liegende Marker zu Clustern zusammengefasst werden. Dies wirkt sich automatisch auf alle Karten aus, wenn aktiviert.
Plugin URI:  https://cp-psource.github.io/ps-maps/
Version:     1.0
Author:      PSOURCE
*/

class Agm_Mc_UserPages {

	private function __construct() {}

	public static function serve() {
		$me = new Agm_Mc_UserPages();
		$me->_add_hooks();
	}

	private function _add_hooks() {
		add_action(
			'agm-user-scripts',
			array( $this, 'load_scripts' )
		);
	}

	public function load_scripts() {
		lib3()->ui->add( AGM_PLUGIN_URL . 'js/external/markerclusterer_packed.min.js', 'front' );
		lib3()->ui->add( AGM_PLUGIN_URL . 'js/user/marker-cluster.min.js', 'front' );
	}
};

if ( ! is_admin() ) {
	Agm_Mc_UserPages::serve();
}