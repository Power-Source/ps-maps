/*! PS Maps - v2.9.4
 * https://cp-psource.github.io/ps-maps/
 * Copyright (c) 2017; * Licensed GPLv2+ */
/*global window:false */
/*global document:false */
/*global _agm:false */
/*global _agmMc:false */
/*global navigator:false */

jQuery(function init_cluster_options() {
	var doc = jQuery(document);
	var lang = (window._agmMc && _agmMc.lang) ? _agmMc.lang : {
		title: 'Markercluster',
		grid_size: 'Cluster-Raster (Pixel)',
		max_zoom: 'Maximaler Cluster-Zoom',
		max_zoom_help: 'Leer lassen = automatische Berechnung'
	};

	function toInt(value, fallback) {
		var parsed = parseInt(value, 10);
		return isNaN(parsed) ? fallback : parsed;
	}

	function init_options(ev, options, data) {
		var gridSize = 10;
		var maxZoom = '';

		try {
			gridSize = data.cluster_grid_size !== undefined && data.cluster_grid_size !== ''
				? toInt(data.cluster_grid_size, gridSize)
				: gridSize;
			maxZoom = data.cluster_max_zoom !== undefined && data.cluster_max_zoom !== ''
				? toInt(data.cluster_max_zoom, '')
				: '';
		} catch (ignore) {}

		options.append(
			'<fieldset id="agm-marker_cluster-box">' +
				'<legend>' + lang.title + '</legend>' +
				'<label for="agm-cluster_grid_size">' + lang.grid_size + '</label> ' +
				'<input type="number" min="2" max="120" step="1" id="agm-cluster_grid_size" size="4" value="' + gridSize + '" />' +
				'<br />' +
				'<label for="agm-cluster_max_zoom">' + lang.max_zoom + '</label> ' +
				'<input type="number" min="1" max="21" step="1" id="agm-cluster_max_zoom" size="4" value="' + maxZoom + '" placeholder="auto" />' +
				'<br /><small>' + lang.max_zoom_help + '</small>' +
			'</fieldset>'
		);
	}

	function sanitize_options(ev, request) {
		var gridSize = toInt(jQuery('#agm-cluster_grid_size').val(), 10);
		var maxZoomRaw = jQuery('#agm-cluster_max_zoom').val();

		request.cluster_grid_size = Math.max(2, Math.min(120, gridSize));

		if ( '' === jQuery.trim(maxZoomRaw) ) {
			request.cluster_max_zoom = '';
		} else {
			var maxZoom = toInt(maxZoomRaw, 13);
			request.cluster_max_zoom = Math.max(1, Math.min(21, maxZoom));
		}
	}

	doc.on('agm_google_maps-admin-options_initialized', init_options);
	doc.on('agm_google_maps-admin-save_request', sanitize_options);
});
