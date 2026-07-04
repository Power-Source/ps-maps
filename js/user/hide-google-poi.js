/*! PS Maps - v2.9.4
 * https://cp-psource.github.io/ps-maps/
 * Copyright (c) 2017; * Licensed GPLv2+ */
/*global window:false */
/*global document:false */
/*global _agm:false */
/*global navigator:false */

jQuery(document).on('agm_google_maps-user-map_initialized', function (e, map) {
	if ( ! map || ! map.setOptions || ! window._agm || ! _agm.hide_google_poi ) {
		return;
	}

	var options = _agm.hide_google_poi;
	var mapOptions = {};
	var styles = [];

	if ( options.disable_clickable_icons ) {
		mapOptions.clickableIcons = false;
	}
	if ( options.hide_poi ) {
		styles.push({
			featureType: 'poi',
			stylers: [{ visibility: 'off' }]
		});
	}
	if ( options.hide_business_labels ) {
		styles.push({
			featureType: 'poi.business',
			elementType: 'labels',
			stylers: [{ visibility: 'off' }]
		});
	}
	if ( options.hide_transit ) {
		styles.push({
			featureType: 'transit',
			stylers: [{ visibility: 'off' }]
		});
	}

	if ( styles.length ) {
		mapOptions.styles = styles;
	}

	if ( mapOptions.clickableIcons || mapOptions.styles ) {
		map.setOptions(mapOptions);

		// Fallback: For some mapId/vector setups, local styles can be ignored.
		// A styled map type restores style control at runtime on roadmap maps.
		if (
			styles.length &&
			window.google && window.google.maps && window.google.maps.StyledMapType &&
			map.mapTypes && map.mapTypes.set &&
			map.getMapTypeId && map.setMapTypeId
		) {
			var currentType = map.getMapTypeId();
			if ( ! currentType || 'roadmap' === currentType || 'terrain' === currentType ) {
				var styledTypeId = 'agm_hide_google_poi';
				map.mapTypes.set(styledTypeId, new window.google.maps.StyledMapType(styles));
				map.setMapTypeId(styledTypeId);
			}
		}
	}
});
