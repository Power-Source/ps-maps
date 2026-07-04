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
		add_filter(
			'agm-load-options',
			array( $this, 'sanitize_map_options' ),
			10,
			2
		);
		add_filter(
			'agm-shortcode-defaults',
			array( $this, 'attributes_defaults' )
		);
		add_filter(
			'agm-shortcode-overrides',
			array( $this, 'overrides_process' ),
			10,
			2
		);
	}

	public function load_scripts() {
		lib3()->ui->add( AGM_PLUGIN_URL . 'js/external/markerclusterer_packed.js', 'front' );
		lib3()->ui->add( AGM_PLUGIN_URL . 'js/user/marker-cluster.js', 'front' );
	}

	public function sanitize_map_options( $options, $raw ) {
		if ( isset( $raw['cluster_grid_size'] ) && '' !== $raw['cluster_grid_size'] ) {
			$options['cluster_grid_size'] = max( 2, min( 120, absint( $raw['cluster_grid_size'] ) ) );
		}

		if ( array_key_exists( 'cluster_max_zoom', $raw ) ) {
			if ( '' === (string) $raw['cluster_max_zoom'] ) {
				$options['cluster_max_zoom'] = '';
			} else {
				$options['cluster_max_zoom'] = max( 1, min( 21, absint( $raw['cluster_max_zoom'] ) ) );
			}
		}

		return $options;
	}

	public function attributes_defaults( $defaults ) {
		$defaults['cluster_grid_size'] = null;
		$defaults['cluster_max_zoom'] = null;
		return $defaults;
	}

	public function overrides_process( $overrides, $atts ) {
		if ( isset( $atts['cluster_grid_size'] ) && null !== $atts['cluster_grid_size'] && '' !== $atts['cluster_grid_size'] ) {
			$overrides['cluster_grid_size'] = max( 2, min( 120, absint( $atts['cluster_grid_size'] ) ) );
		}

		if ( isset( $atts['cluster_max_zoom'] ) && null !== $atts['cluster_max_zoom'] ) {
			if ( '' === (string) $atts['cluster_max_zoom'] ) {
				$overrides['cluster_max_zoom'] = '';
			} else {
				$overrides['cluster_max_zoom'] = max( 1, min( 21, absint( $atts['cluster_max_zoom'] ) ) );
			}
		}

		return $overrides;
	}
};

class Agm_Mc_AdminPages {

	private function __construct() {}

	public static function serve() {
		$me = new Agm_Mc_AdminPages();
		$me->_add_hooks();
	}

	private function _add_hooks() {
		add_action(
			'agm-admin-scripts',
			array( $this, 'load_scripts' )
		);
		add_filter(
			'agm-save-options',
			array( $this, 'sanitize_map_options' ),
			10,
			2
		);
		add_filter(
			'agm-load-options',
			array( $this, 'sanitize_map_options' ),
			10,
			2
		);
	}

	public function load_scripts() {
		$data = array(
			'lang' => array(
				'title' => __( 'Markercluster', AGM_LANG ),
				'grid_size' => __( 'Cluster-Raster (Pixel)', AGM_LANG ),
				'max_zoom' => __( 'Maximaler Cluster-Zoom', AGM_LANG ),
				'max_zoom_help' => __( 'Leer lassen = automatische Berechnung', AGM_LANG ),
			),
		);

		lib3()->ui->data( '_agmMc', $data );
		lib3()->ui->add( AGM_PLUGIN_URL . 'js/admin/marker-cluster.min.js' );
	}

	public function sanitize_map_options( $options, $raw ) {
		if ( isset( $raw['cluster_grid_size'] ) && '' !== $raw['cluster_grid_size'] ) {
			$options['cluster_grid_size'] = max( 2, min( 120, absint( $raw['cluster_grid_size'] ) ) );
		}

		if ( array_key_exists( 'cluster_max_zoom', $raw ) ) {
			if ( '' === (string) $raw['cluster_max_zoom'] ) {
				$options['cluster_max_zoom'] = '';
			} else {
				$options['cluster_max_zoom'] = max( 1, min( 21, absint( $raw['cluster_max_zoom'] ) ) );
			}
		}

		return $options;
	}
}

if ( is_admin() ) {
	Agm_Mc_AdminPages::serve();
} else {
	Agm_Mc_UserPages::serve();
}