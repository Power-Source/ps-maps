/*! PS Maps - v2.9.4
 * https://cp-psource.github.io/ps-maps/
 * Copyright (c) 2017; * Licensed GPLv2+ */
/*global window:false */
/*global document:false */
/*global _agm:false */
/*global navigator:false */

jQuery(function () {

function toInt(value, fallback) {
	var parsed = parseInt(value, 10);
	return isNaN(parsed) ? fallback : parsed;
}

function getClusterOptions(map, data) {
	var mapTypeId = map && map.getMapTypeId ? map.getMapTypeId() : null;
	var mapType = mapTypeId && map && map.mapTypes ? map.mapTypes[mapTypeId] : null;
	var mapTypeMaxZoom = mapType ? toInt(mapType.maxZoom, null) : null;
	var configuredMaxZoom = data && data.cluster_max_zoom !== undefined ? toInt(data.cluster_max_zoom, null) : null;
	var configuredGridSize = data && data.cluster_grid_size !== undefined ? toInt(data.cluster_grid_size, null) : null;
	var maxZoom = configuredMaxZoom;

	if ( null === maxZoom ) {
		maxZoom = null !== mapTypeMaxZoom ? Math.max(1, mapTypeMaxZoom - 3) : 15;
	}

	return {
		zoomOnClick: false,
		gridSize: null !== configuredGridSize ? Math.max(10, configuredGridSize) : 20,
		maxZoom: maxZoom,
		minimumClusterSize: 2,
		imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m'
	};
}

jQuery(document).on("agm_google_maps-user-map_initialized", function (e, map, data, markers) {
	if ( ! markers || ! markers.length ) { return; }
	var markerCluster = new window.MarkerClusterer(
		map,
		markers,
		getClusterOptions(map, data)
	);

	window.google.maps.event.addListener(markerCluster, "clusterclick", function (c) {
		var clustered = c.getMarkers();
		var contents = '';

		jQuery.each(clustered, function () {
			if ( '_agmInfo' in this ) {
				contents += this._agmInfo.getContent();
				contents += "<hr style='clear:both' />";
			}
		});

		var info = new window.google.maps.InfoWindow({
			content: contents
		});

		window._agmOpenInfoWindow(info, map, clustered[0]);
	});
});

});
