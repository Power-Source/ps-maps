/*! PS Maps - v2.9.4
 * https://cp-psource.github.io/ps-maps/
 * Copyright (c) 2017; * Licensed GPLv2+ */
/*global window:false */
/*global document:false */
/*global _agm:false */
/*global navigator:false */


// Load selected Places
jQuery(document).on("agm_google_maps-user-map_initialized", function (e, map, data, markers) {
	function initialize_all_markers_places (map, show, distance, types) {
		var show_places = show,
			places_radius = distance,
			place_types = types,
			max_origins = data && data.places_max_origins ? parseInt(data.places_max_origins, 10) : 10;

		if ( ! show_places ) { return false; }
		if ( isNaN(max_origins) || max_origins < 1 ) { max_origins = 10; }

		var service = new window.google.maps.places.PlacesService( map ),
			markers = map._agm_get_markers(),
			bounds = map.getBounds ? map.getBounds() : null,
			cache = map._agmPlacesCache || {},
			processed = 0,
			request = {
				"radius": places_radius
			};

		map._agmPlacesCache = cache;

		if ( place_types ) { request.types = place_types; }

		jQuery.each(markers, function () {
			if ( processed >= max_origins ) { return false; }

			var marker = this;
			if ( bounds && ! bounds.contains(marker.getPosition()) ) {
				return true;
			}

			request.location = marker.getPosition();
			processed += 1;

			var point = marker.getPosition();
			var cache_key = [
				point.lat().toFixed(4),
				point.lng().toFixed(4),
				places_radius,
				(place_types || []).join('|')
			].join(':');

			if ( cache[cache_key] ) {
				update_marker_places(map, marker, cache[cache_key]);
				return true;
			}

			service.search(request, function (response) {
				cache[cache_key] = response || [];
				update_marker_places(map, marker, response);
			});
		});
	}

	function update_marker_places (map, marker, places) {
		var pos = marker.getPosition().toString();
		jQuery.each(places, function () {
			var place = this,
				place_icon = new window.google.maps.MarkerImage(
					place.icon.toString(),
					null, null, null, new window.google.maps.Size(32, 32)
				),
				place_marker = window._agmCreateMapMarker({
					"title": place.name,
					"map": map,
					"icon": place_icon,
					"draggable": false,
					"clickable": true,
					"position": place.geometry.location
				}),
				info = new window.google.maps.InfoWindow({
					"content": '<b>' + place.name + '</b><br />' + '<p>' + place.vicinity + '</p>',
					"maxWidth": 400
				});

			place_marker.addListener('click', function() {
				window._agmOpenInfoWindow(info, map, place_marker);
			});
		});
	}

	if ( typeof window.google.maps.places !== 'object' ) { return false; }

	var show = data.show_places ? parseInt( data.show_places ) : false,
		distance = data.places_radius || 1000,
		place_types = data.place_types;

	initialize_all_markers_places( map, show, distance, place_types );
});