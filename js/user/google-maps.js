/*! PS Maps - v2.9.4
 * https://cp-psource.github.io/ps-maps/
 * Copyright (c) 2017; * Licensed GPLv2+ */
/*global window:false */
/*global document:false */
/*global _agm:false */
/*global _agmMaps:false */
/*global l10nStrings:false */
/*global navigator:false */

/**
 * Global handler object variable.
 * Initiated as global, will be bound as object on document.load.
 */
window.AgmMapHandler = null;
window._agm = window._agm || {};
_agm.initialized = false;

jQuery(function() {
_agm.initialized = false;

var normalizeMarkerPosition = function (position) {
	if ( ! position ) {
		return position;
	}

	if ( 'function' === typeof position.lat && 'function' === typeof position.lng ) {
		return position;
	}

	if ( undefined !== position.lat && undefined !== position.lng ) {
		return new window.google.maps.LatLng(position.lat, position.lng);
	}

	return position;
};

var extractMarkerIcon = function (icon) {
	if ( ! icon ) {
		return '';
	}

	if ( 'string' === typeof icon ) {
		return icon;
	}

	if ( 'object' === typeof icon && icon.url ) {
		return icon.url;
	}

	return icon.toString ? icon.toString() : '';
};

var createMarkerImageContent = function (icon, title) {
	var image = document.createElement('img');
	image.className = 'marker-icon-32 agm-advanced-marker';
	image.src = icon;
	image.alt = title || '';
	image.title = title || '';
	image.style.width = '32px';
	image.style.height = '32px';
	return image;
};

var createLegacyMarker = function (options) {
	return new window.google.maps.Marker(options);
};

var createAdvancedMarkerWrapper = function (options) {
	var position = normalizeMarkerPosition(options.position);
	var icon = extractMarkerIcon(options.icon);
	var content = icon ? createMarkerImageContent(icon, options.title) : null;
	var markerView = new window.google.maps.marker.AdvancedMarkerElement({
		map: options.map,
		position: position,
		title: options.title || '',
		content: content || undefined,
		gmpDraggable: !!options.draggable,
		zIndex: options.zIndex
	});
	var visible = true;
	var advancedClickListeners = [];

	var getAdvancedClickTargets = function () {
		var targets = [];

		if ( markerView.addEventListener ) {
			targets.push(markerView);
		}

		if ( markerView.element && markerView.element.addEventListener ) {
			if ( -1 === targets.indexOf(markerView.element) ) {
				targets.push(markerView.element);
			}
		}

		return targets;
	};

	var bindAdvancedClickListener = function (listener) {
		var targets = getAdvancedClickTargets();
		if ( ! targets.length ) {
			return false;
		}

		for ( var i = 0; i < targets.length; i += 1 ) {
			targets[i].addEventListener('gmp-click', listener.wrapped);

			if ( markerView.element && targets[i] === markerView.element ) {
				targets[i].addEventListener('click', listener.wrapped);
			}
		}

		listener.targets = targets;
		return true;
	};

	var rebindAdvancedClickListeners = function (retry) {
		retry = retry || 0;

		if ( ! advancedClickListeners.length ) {
			return;
		}

		if ( ! getAdvancedClickTargets().length ) {
			if ( retry < 5 ) {
				window.setTimeout(function () {
					rebindAdvancedClickListeners(retry + 1);
				}, 40);
			}
			return;
		}

		for ( var i = 0; i < advancedClickListeners.length; i += 1 ) {
			var listener = advancedClickListeners[i];
			for ( var j = 0; j < (listener.targets || []).length; j += 1 ) {
				if ( listener.targets[j] && listener.targets[j].removeEventListener ) {
					listener.targets[j].removeEventListener('gmp-click', listener.wrapped);
				}
			}
			listener.targets = [];
			bindAdvancedClickListener(listener);
		}
	};

	var wrapper = {
		_agmAdvancedMarker: markerView,
		_agmMarkerContent: content,
		draggable: !!options.draggable,
		clickable: options.clickable !== false,
		show_info: false,
		getPosition: function () {
			return normalizeMarkerPosition(markerView.position);
		},
		setPosition: function (newPosition) {
			markerView.position = normalizeMarkerPosition(newPosition);
		},
		getMap: function () {
			return markerView.map || null;
		},
		setMap: function (map) {
			markerView.map = map || null;
			rebindAdvancedClickListeners();
		},
		getTitle: function () {
			return markerView.title || '';
		},
		setTitle: function (title) {
			markerView.title = title || '';
			if ( wrapper._agmMarkerContent ) {
				wrapper._agmMarkerContent.alt = title || '';
				wrapper._agmMarkerContent.title = title || '';
			}
		},
		getIcon: function () {
			return icon;
		},
		setIcon: function (newIcon) {
			icon = extractMarkerIcon(newIcon);
			if ( ! wrapper._agmMarkerContent ) {
				wrapper._agmMarkerContent = createMarkerImageContent(icon, wrapper.getTitle());
				markerView.content = wrapper._agmMarkerContent;
				return;
			}

			wrapper._agmMarkerContent.src = icon;
		},
		setVisible: function (state) {
			visible = !!state;
			if ( wrapper._agmMarkerContent ) {
				wrapper._agmMarkerContent.style.display = visible ? '' : 'none';
			}
			if ( markerView.element ) {
				markerView.element.style.display = visible ? '' : 'none';
			}
		},
		getVisible: function () {
			return visible;
		},
		addListener: function (eventName, handler) {
			if ( 'click' === eventName ) {
				var clickListener = {
					targets: [],
					lastTriggerAt: 0,
					wrapped: function (event) {
						var now = Date.now();
						if ( now - clickListener.lastTriggerAt < 25 ) {
							return;
						}
						clickListener.lastTriggerAt = now;
						handler(event);
					}
				};
				var fallbackHandle = null;

				advancedClickListeners.push(clickListener);

				if ( ! bindAdvancedClickListener(clickListener) ) {
					fallbackHandle = markerView.addListener(eventName, handler);
				}

				return {
					remove: function () {
						for ( var i = advancedClickListeners.length - 1; i >= 0; i -= 1 ) {
							if ( advancedClickListeners[i] === clickListener ) {
								advancedClickListeners.splice(i, 1);
								break;
							}
						}

						for ( var j = 0; j < clickListener.targets.length; j += 1 ) {
							if ( clickListener.targets[j] && clickListener.targets[j].removeEventListener ) {
								clickListener.targets[j].removeEventListener('gmp-click', clickListener.wrapped);
								if ( markerView.element && clickListener.targets[j] === markerView.element ) {
									clickListener.targets[j].removeEventListener('click', clickListener.wrapped);
								}
							}
						}
						clickListener.targets = [];

						if ( fallbackHandle && fallbackHandle.remove ) {
							fallbackHandle.remove();
						}
					}
				};
			}

			return markerView.addListener(eventName, handler);
		}
		};

	return wrapper;
};

window._agmCreateMapMarker = function (options) {
	if (
		options && options.map && options.map.__agmSupportsAdvancedMarkers &&
		window.google && window.google.maps && window.google.maps.marker &&
		window.google.maps.marker.AdvancedMarkerElement
	) {
		return createAdvancedMarkerWrapper(options);
	}

	return createLegacyMarker(options);
};

window._agmOpenInfoWindow = function (infoWindow, map, marker) {
	if ( marker && marker._agmAdvancedMarker ) {
		infoWindow.open({
			map: map || marker.getMap(),
			anchor: marker._agmAdvancedMarker
		});
		return;
	}

	infoWindow.open(map, marker);
};

/**
 * Public side map handler.
 * Responsible for rendering maps on public facing pages.
 *
 * @param selector Container element selector string.
 * @param data Map data object.
 */
window.AgmMapHandler = function (selector, data) {
	var map;
	var directionsDisplay;
	var directionsService;
	var travelType;
	var directionsPolylines = [];
	var mapId = 'map_' + Math.floor(Math.random()* new Date().getTime()) + '_preview';
	var container = jQuery(selector);
	var alignmentContainer;
	var _markers = []; // Detail information on each marker
	var _list_changed = false;
	var _sort_function = null;

	var closeDirections = function () {
		jQuery(selector + ' .agm_mh_directions_container').remove();
		return false;
	};

	var canUseRoutesApi = function () {
		return !! (
			window.google &&
			window.google.maps &&
			window.google.maps.routes &&
			window.google.maps.routes.Route &&
			typeof window.google.maps.routes.Route.computeRoutes === 'function'
		);
	};

	var getResolvedMapId = function () {
		if ( data.map_id ) { return data.map_id; }
		if ( data.defaults && data.defaults.map_id ) { return data.defaults.map_id; }
		if ( _agm.map_id ) { return _agm.map_id; }
		return 'DEMO_MAP_ID';
	};

	var clearDirectionsPolylines = function () {
		jQuery.each(directionsPolylines, function () {
			if ( this && typeof this.setMap === 'function' ) {
				this.setMap(null);
			}
		});
		directionsPolylines = [];
	};

	var clearDirectionsPanel = function () {
		jQuery(selector + ' .agm_mh_directions_panel').empty();
	};

	var getRouteValue = function (value, fallback) {
		if ( undefined === value || null === value ) {
			return fallback || '';
		}

		if ( 'string' === typeof value ) {
			return value;
		}

		if ( 'object' === typeof value && undefined !== value.text ) {
			return value.text;
		}

		return value.toString ? value.toString() : (fallback || '');
	};

	var fitRoutePath = function (path) {
		var bounds;

		if ( ! path || ! path.length ) {
			return;
		}

		bounds = new window.google.maps.LatLngBounds();
		jQuery.each(path, function () {
			bounds.extend(this);
		});

		map.fitBounds(bounds);
	};

	var renderRoutePanel = function (route) {
		var panel = jQuery(selector + ' .agm_mh_directions_panel');

		clearDirectionsPanel();

		if ( ! route || ! route.legs || ! route.legs.length ) {
			panel.text( l10nStrings.oops_no_directions );
			return;
		}

		jQuery.each(route.legs, function (legIndex, leg) {
			var legWrap = jQuery('<div class="agm_mh_route_leg"></div>');
			var legTitle = jQuery('<div class="agm_mh_route_leg_title"></div>');
			var stepsList = jQuery('<ol class="agm_mh_route_steps"></ol>');
			var legDistance = getRouteValue(
				leg.localizedValues && leg.localizedValues.distance,
				''
			);
			var legDuration = getRouteValue(
				leg.localizedValues && (leg.localizedValues.duration || leg.localizedValues.staticDuration),
				''
			);

			legTitle.text('Leg ' + (legIndex + 1) + (legDistance || legDuration ? ': ' + jQuery.trim((legDistance + ' ' + legDuration).replace(/\s+/g, ' ')) : ''));
			legWrap.append(legTitle);

			jQuery.each(leg.steps || [], function (stepIndex, step) {
				var item = jQuery('<li class="agm_mh_route_step"></li>');
				var instruction = getRouteValue(step.instructions, '');
				var distance = getRouteValue(
					step.localizedValues && step.localizedValues.distance,
					''
				);
				var duration = getRouteValue(
					step.localizedValues && (step.localizedValues.duration || step.localizedValues.staticDuration),
					''
				);
				var meta = jQuery.trim((distance + ' ' + duration).replace(/\s+/g, ' '));

				if ( instruction ) {
					item.append(jQuery('<div class="agm_mh_route_instruction"></div>').text(instruction));
				}

				if ( meta ) {
					item.append(jQuery('<div class="agm_mh_route_step_meta"></div>').text(meta));
				}

				if ( ! instruction && ! meta ) {
					item.text('Step ' + (stepIndex + 1));
				}

				stepsList.append(item);
			});

			legWrap.append(stepsList);
			panel.append(legWrap);
		});
	};

	var renderRouteOnMap = function (route) {
		clearDirectionsPolylines();

		if ( ! route ) {
			return;
		}

		directionsPolylines = route.createPolylines() || [];
		jQuery.each(directionsPolylines, function () {
			this.setMap(map);
		});

		fitRoutePath(route.path || []);
	};

	var ensureLegacyDirectionsObjects = function () {
		if ( ! directionsDisplay ) {
			directionsDisplay = new window.google.maps.DirectionsRenderer({
				"draggable": true
			});
			directionsDisplay.setMap(map);
		}

		if ( ! directionsService ) {
			directionsService = new window.google.maps.DirectionsService();
		}
	};

	var getRouteRequest = function (origin, destination, unitSystem, routeMode) {
		var request = {
			origin: origin,
			destination: destination,
			travelMode: routeMode,
			fields: ['legs', 'path']
		};

		if ( undefined !== unitSystem && null !== unitSystem ) {
			request.units = unitSystem;
		}

		return request;
	};

	var requestLegacyDirections = function (request, onSuccess, onError) {
		ensureLegacyDirectionsObjects();
		directionsService.route(
			{
				origin: request.origin,
				destination: request.destination,
				unitSystem: request.units,
				travelMode: request.travelMode
			},
			function (result, status) {
				if (status === window.google.maps.DirectionsStatus.OK) {
					onSuccess(result);
				} else if ( typeof onError === 'function' ) {
					onError(status);
				}
			}
		);
	};

	var requestRenderedRoute = function (request, onSuccess, onError) {
		if ( ! canUseRoutesApi() ) {
			requestLegacyDirections(
				request,
				function (legacyResult) {
					onSuccess({ type: 'legacy', result: legacyResult });
				},
				onError
			);
			return;
		}

		window.google.maps.routes.Route.computeRoutes(request)
			.then(function (response) {
				if ( response && response.routes && response.routes.length ) {
					onSuccess({ type: 'routes', result: response.routes[0] });
					return;
				}

				if ( typeof onError === 'function' ) {
					onError('NO_ROUTES');
				}
			})
			.catch(function () {
				requestLegacyDirections(
					request,
					function (legacyResult) {
						onSuccess({ type: 'legacy', result: legacyResult });
					},
					onError
				);
			});
	};

	var createDirectionsMarkup = function () {
		var html = '<div class="agm_mh_directions_container agm_mh_container">';
		html += '<div style="width:300px">' +
					'<span style="float:right"><input type="button" class="agm_mh_close_directions" value="' + l10nStrings.close + '" /> </span>' +
					'<div>' +
						'<a href="#" class="agm_mh_travel_type"><img src="' + _agm.root_url + '/img/system/car_on.png"></a>' +
						'<a href="#" class="agm_mh_travel_type"><img src="' + _agm.root_url + '/img/system/bike_off.png"></a>' +
						'<a href="#" class="agm_mh_travel_type"><img src="' + _agm.root_url + '/img/system/walk_off.png"></a>' +
						'<a href="#" class="agm_mh_travel_type"><img src="' + _agm.root_url + '/img/system/bus_off.png"></a>' +
					'</div>' +
				'</div>' +
			'<div>' +
				'<img src="' + _agm.root_url + '/img/system/a.png">' +
				'&nbsp;' +
				'<input size="32" type="text" class="agm_waypoint_a" />' +
			'</div>' +
			'<div><a href="#" class="agm_mh_swap_direction_waypoints"><img src="' + _agm.root_url + '/img/system/swap.png"></a></div>' +
			'<div>' +
				'<img src="' + _agm.root_url + '/img/system/b.png">' +
				'&nbsp;' +
				'<input size="32" type="text"  class="agm_waypoint_b" />' +
			'</div>' +
			'<div>' +
				'<input type="button" class="agm_mh_get_directions" value="' + l10nStrings.get_directions + '" />' +
			'</div>' +
			'<div class="agm_mh_directions_panel agm_mh_container">' +
			'</div>'
		;
		html += '</div>';
		container.append(html);
	};

	var switchTravelType = function () {
		var me = jQuery(this);
		var meImg = me.find('img');
		var allImg = jQuery(selector + ' .agm_mh_travel_type img');
		allImg.each(function () {
			jQuery(this).attr('src', jQuery(this).attr('src').replace(/_on\./, '_off.'));
		});
		if (meImg.attr('src').match(/car_off\.png/)) {
			travelType = 'DRIVING';
		} else if (meImg.attr('src').match(/bike_off\.png/)) {
			travelType = 'BICYCLING';
		} else if (meImg.attr('src').match(/walk_off\.png/)) {
			travelType = 'WALKING';
		} else if (meImg.attr('src').match(/bus_off\.png/)) {
			travelType = 'TRANSIT';
		}
		meImg.attr('src', meImg.attr('src').replace(/_off\./, '_on.'));
		return false;
	};

	var setDirectionWaypoint = function () {
		if ( ! jQuery(selector + ' .agm_mh_directions_container').is(':visible') ) {
			createDirectionsMarkup();
		}

		var id = extractMarkerId(jQuery(this).attr('href'));
		var marker = _markers[id];

		if (data.defaults && data.defaults.directions_snapping) {
			var geocoder = new window.google.maps.Geocoder();
			geocoder.geocode({'latLng': marker.getPosition()}, function (results, status) {
				if (status === window.google.maps.GeocoderStatus.OK) {
					jQuery(selector + ' .agm_waypoint_b').val(results[0].formatted_address);

					jQuery(document).trigger("agm_google_maps-user-directions-waypoint_populated", [results[0].formatted_address, marker, container]);  // old style
					jQuery(document).trigger("agm:dir_waypoint", [results[0].formatted_address, marker, container]);
				}
				else {
					window.alert(l10nStrings.geocoding_error);
				}
			});
		} else {
			var position = marker.getPosition();
			jQuery(selector + ' .agm_waypoint_b').val(position);

			jQuery(document).trigger("agm_google_maps-user-directions-waypoint_populated", [position, marker, container]); // old style
			jQuery(document).trigger("agm:dir_waypoint", [position, marker, container]);
		}
		return false;
	};

	var swapWaypoints = function () {
		var tmpA = jQuery(selector + ' .agm_waypoint_a').val();
		jQuery(selector + ' .agm_waypoint_a').val(jQuery(selector + ' .agm_waypoint_b').val());
		jQuery(selector + ' .agm_waypoint_b').val(tmpA);
		return false;
	};

	var getDirections = function () {
		var loc_a = jQuery(selector + ' .agm_waypoint_a').val();
		var loc_b = jQuery(selector + ' .agm_waypoint_b').val();

		if ( ! loc_a || ! loc_b ) {
			window.alert( l10nStrings.missing_waypoint );
			return false;
		}

		var unit_system = 0;
		if ( 'defaults' in data && 'units' in data.defaults ) {
			unit_system = data.defaults.units;
		} else if ( 'units' in data ) {
			unit_system = data.units;
		}

		var request = {
			origin: loc_a,
			destination: loc_b,
			units: unit_system,
			travelMode: travelType
		};

		requestRenderedRoute(
			request,
			function (response) {
				if ( 'routes' === response.type ) {
					renderRouteOnMap(response.result);
					renderRoutePanel(response.result);
					return;
				}

				ensureLegacyDirectionsObjects();
				clearDirectionsPolylines();
				directionsDisplay.setPanel(jQuery(selector + ' .agm_mh_directions_panel').get(0));
				directionsDisplay.setDirections(response.result);
			},
			function () {
				window.alert( l10nStrings.oops_no_directions );
			}
		);
		return false;
	};

	var plotRoutes = function () {
		var old = false;

		jQuery.each(_markers, function (idx, mark) {
			if ( ! old ) {
				old = mark;
				return true; // Skip if no previous marker
			}
			requestRenderedRoute(
				getRouteRequest(old.getPosition(), mark.getPosition(), null, 'DRIVING'),
				function (response) {
					if ( 'routes' === response.type ) {
						var polylines = response.result.createPolylines() || [];
						jQuery.each(polylines, function () {
							this.setMap(map);
							directionsPolylines.push(this);
						});
						return;
					}

					var dir_rend = new window.google.maps.DirectionsRenderer({
						"draggable": true,
						"suppressMarkers": true
					});
					dir_rend.setMap(map);
					dir_rend.setDirections(response.result);
				},
				function () {}
			);
			old = mark;
		});
	};

	/**
	 * Adds a new marker to the map and marker list (blue list below the map).
	 * The marker list is updated by the function {@see updateMarkersListDisplay}
	 */
	var addNewMarker = function (title, pos, body, icon, original, idx) {
		idx = (undefined === idx || isNaN( idx ) ? _markers.length : idx);
		icon = (icon.match(/^https?:\/\//) ? icon : _agm.root_url + '/img/' + icon);

		//var iconImg = new window.google.maps.MarkerImage(icon, new window.google.maps.Size(32, 32), null, null, new window.google.maps.Size(32, 32));

		var identifier = '';
		if ( undefined !== original && undefined !== original.identifier ) {
			identifier = 'agm-id-' + original.identifier;
		}

		map.setCenter(pos);

		var marker = window._agmCreateMapMarker({
			title: title,
            map: map,
            icon: icon,
            draggable: false,
            clickable: true,
			position: pos
        });
		var infoContent = '<div class="agm_mh_info_content">' +
			'<div class="agm_mh_info_title">' + title + '</div>' +
			'<img class="agm_mh_info_icon marker-icon-32" src="' + icon + '" />' +
			'<div class="agm_mh_info_body">' + body + '</div>' +
			createDirectionsLink(idx) +
			createLinksToPostsMarkup(idx,1) +
		'</div>';
		var info = new window.google.maps.InfoWindow({
			content: infoContent
		});

		marker.addListener('click', function() {
			marker.show_info = true;

			jQuery(document).trigger("agm_google_maps-user-marker_click", [idx, title, body, marker, map]); // old style
			jQuery(document).trigger("agm:marker_click", [idx, title, body, marker, map]);

			if ( marker.show_info === true ) {
				window._agmOpenInfoWindow(info, map, marker);
			}
		});

		marker._agmBody = body;
		marker._agmInfo = info;
		marker._identifier = identifier;
		_markers[idx] = marker;
		_list_changed = true;

		jQuery(document).trigger("agm_google_maps-user-adding_marker", [marker, idx, map, original]); // old style
		jQuery(document).trigger("agm:add_marker", [marker, idx, map, original]);

		map._agm_add_marker(marker);
		updateMarkersListDisplay();
	};

	var addMarkers = function () {
		if ( ! data.markers ) { return; }

		jQuery.each(data.markers, function (idx, marker) {
			addNewMarker(
				marker.title,
				new window.google.maps.LatLng(marker.position[0], marker.position[1]),
				marker.body,
				marker.icon,
				marker
			);
		});
	};

	var extractMarkerId = function (href) {
		var id = href.replace( /[^0-9]+/, '' );

		return parseInt( id, 10 );
	};

	var centerToMarker = function () {
		var me = jQuery(this);
		var id = extractMarkerId(me.attr('href'));

		var sorted_markers = jQuery.extend( [], _markers );

		if ( typeof _sort_function === 'function' ) {
			sorted_markers.sort( _sort_function );
		}

		var m = sorted_markers[id];

		map.setCenter(m.getPosition());

		if ( parseInt(data.street_view, 10) ) {
			var panorama = map.getStreetView();
			panorama.setPosition(map.getCenter());
			panorama.setVisible(true);
		}

		jQuery(document).trigger("agm_google_maps-user-map_centered_to_marker", [m, map]); // old style
		jQuery(document).trigger("agm:centered", [m, map]);

		return false;
	};

	var createDirectionsLink = function (idx) {
		return '<a href="#agm_mh_marker-' +
			idx +
			'" class="agm_mh_marker_item_directions">' +
			l10nStrings.directions +
			'</a>';
	};

	var createLinksToPostsMarkup = function (idx,i) {
		if ( ! ("show_posts" in data) ||
			! data.show_posts ||
			! parseInt( data.show_posts, 10 )
		) {
			return '';
		}

		return '<div class="agm_post_links_container"><input type="hidden" value="' + idx + '" /></div>';
	};

	/**
	 * This function re-creates the marker list below the google map.
	 * It will completely erase the current list and create the HTML from scatch.
	 * The markers are stored in the private variable _markers.
	 */
	var updateMarkersListDisplay = function () {
		if ( !  data.show_markers || ! parseInt( data.show_markers, 10 ) ) {
			return false;
		}

		// We only refresh the list when we know that is has changed since last refresh.
		if ( ! _list_changed ) {
			return false;
		}

		/*
		 * Refresh the marker list after 10ms, in case there are many markers this
		 * ensures that the marker list is created at the end, and not after each
		 * single marker was added to the list.
		 */
		var now = new Date().getTime(), delay = 10;
		if ( isNaN( data.update_list_timer ) ) {
			data.update_list_at = now + delay;
			data.update_list_timer = window.setTimeout( updateMarkersListDisplay, delay + 1 );
		}
		if ( now < data.update_list_at ) {
			return false;
		}
		// End of the refresh-marker-optimization.

		// Custom sorting of the markers.
		var prepared_markers = jQuery.extend( [], _markers );
		if ( typeof _sort_function === 'function' ) {
			prepared_markers.sort( _sort_function );
		}

		// Display the markers in the defined order
		var idx, mark, item, icon, html = '<ul class="agm_mh_marker_list">';
		for ( idx = 0; idx < prepared_markers.length; idx += 1 ) {
			mark = prepared_markers[idx];

			//  Markers can be deleted, which could interfere with this loop.
			if ( undefined === mark ) {
				return false;
			}

			icon = mark.getIcon();
			try { icon = icon.url ? icon.url : icon; }
			catch (e) { icon = mark.getIcon(); }

			item = '<li style="clear:both" class="' + mark._identifier + '">' +
				'<a href="#agm_mh_marker-' + idx + '" class="agm_mh_marker_item">' +
					'<img src="' + icon + '" class="marker-icon-32" />' +
					'<div class="agm_mh_marker_item_content">' +
						'<div><b>' + mark.getTitle() + '</b></div>' +
						'<div>' + mark._agmBody + '</div>' +
					'</div>' +
				'</a>' +
				createDirectionsLink(idx) +
				createLinksToPostsMarkup(idx) +
				'<div style="clear:both"></div>' +
			'</li>';
			html += item;
		}

		html += '</ul>';
		jQuery( '#agm_mh_markers_' + mapId ).html( html );
		_list_changed = false;
	};

	/**
	 * Refresh the details of a single marker on map + marker list.
	 * This will either update OR create a new marker.
	 * Usage: map.trigger('agm:update_marker', [identifier, marker]);
	 *
	 * @since  2.8
	 */
	var refreshMarker = function refreshMarker( ev, identifier, marker ) {
		var ind, pos, idx;

		if ( ! identifier.length ) { return; }

		identifier = 'agm-id-' + identifier;
		for ( ind = _markers.length - 1; ind >= 0; ind -= 1 ) {
			//  Markers can be deleted, which could interfere with this loop.
			if ( undefined === _markers[ ind ] ) { continue; }

			if ( _markers[ ind ]._identifier === identifier ) {
				// Remove current marker from the map.
				_markers[ ind ].setMap( null );
				idx = ind;
				break;
			}
		}

		// This will either update the marker, when the identier was found,
		// or create a new marker with the identifier.
		pos = new window.google.maps.LatLng( marker.position[0], marker.position[1] );
		addNewMarker( marker.title, pos, marker.body, marker.icon, marker, idx );
	};

	/**
	 * Removes a single marker from the map + marker-listing.
	 * Usage: map.trigger('agm:remove_marker', [identifier]);
	 *
	 * @since  2.8
	 */
	var removeMarker = function removeMarker( ev, identifier ) {
		var ind, pos;

		if ( ! identifier.length ) { return; }

		identifier = 'agm-id-' + identifier;
		for ( ind = _markers.length - 1; ind >= 0; ind -= 1 ) {
			if ( _markers[ ind ]._identifier === identifier ) {
				// Remove current marker from the map.
				_markers[ ind ].setMap( null );
				_markers.splice( ind, 1 );
				_list_changed = true;

				updateMarkersListDisplay();
				break;
			}
		}
	};

	/**
	 * Changes the sort-order of markers in the marker listing.
	 *
	 * @since 2.8.3
	 */
	var setSortFunction = function setSortFunction( ev, new_sort_function ) {
		if ( typeof ev === 'function' && undefined === new_sort_function ) {
			new_sort_function = ev;
		}

		// Only use the new sort function if:
		// - the new callback is actually a function AND
		// - the current function is different to the new function
		if ( typeof new_sort_function === 'function' ) {
			if ( typeof _sort_function !== 'function' ||
				_sort_function.toString() !== new_sort_function.toString()
			) {
				_sort_function = new_sort_function;
				_list_changed = true;
			}
		}
	};

	var populateLinksToPostsMarkup = function () {
		if ( ! ("show_posts" in data) ||
			! data.show_posts ||
			! parseInt( data.show_posts, 10 )
		) {
			return false;
		}

		jQuery(selector + ' .agm_post_links_container').each(function () {
			var me = jQuery(this);
			var mid = me.find('input:hidden').val();

			if (!mid) { return true; }
			var marker = data.markers[mid];

			if ( ! marker ) { return true; }
			var post_ids = false;

			if ( ! ( "post_ids" in marker ) ||
				! marker.post_ids ||
				! marker.post_ids.length
			) {
				post_ids = data.post_ids;
			} else {
				post_ids = marker.post_ids;
			}

			if ( ! post_ids ) { return true; }

			jQuery.post(
				_agm.ajax_url,
				{
					"action": "agm_get_post_titles",
					"post_ids": post_ids
				},
				function (data) {
					if ( ! data.posts ) { return true; }

					var html = '<div class="agm_associated_posts_list_title">' + l10nStrings.posts + '</div>';
					html += '<ul class="agm_associated_posts_list_items">';
					var style = '';

					jQuery.each(data.posts, function (idx, post) {
						if (idx > 2) { style = 'style="display:none;"'; }
						html += '<li ' + style + '><a href="' + post.permalink + '">' + post.post_title + '</a></li>';
					});
					html += '</ul>';
					if ( style ) {
						html += '<a href="#" class="agm_toggle_hidden_post_links">' + l10nStrings.showAll + '</a>';
					}

					// Update marker in the list
					me.html(html);

					// Update Info Popup
					var mapMarker = _markers[mid];
					var old = jQuery(mapMarker._agmInfo.getContent());
					old.find('.agm_post_links_container').html(html);
					var markup = '<div class="agm_mh_info_content">' + old.html() + '</div>';
					mapMarker._agmInfo.setContent(markup);

					jQuery('body').on('click', '.agm_toggle_hidden_post_links', function () {
						var me = jQuery(this).parents('.agm_post_links_container').first();
						if (me.find('.agm_associated_posts_list_items li:hidden').length) {
							me.find('.agm_associated_posts_list_items li:hidden').show();
							jQuery(this).text(l10nStrings.hide);
						} else {
							me.find('.agm_associated_posts_list_items li:gt(2)').hide();
							jQuery(this).text(l10nStrings.showAll);
						}
						return false;
					});

					jQuery(document).trigger("agm_google_maps-user-post_titles_loaded", [mapMarker, data, map]); // old style
					jQuery(document).trigger("agm:post_titles", [mapMarker, data, map]);
				}
			);
		});
	};

	var init = function () {
		var width, height, tmr_init, is_loaded = false;

		try {
			width = (parseInt(data.width, 10) > 0) ? data.width : data.defaults.width;
			height = (parseInt(data.height, 10) > 0) ? data.height : data.defaults.height;
		} catch (e) {
			width = (parseInt(data.width, 10) > 0) ? data.width : 200;
			height = (parseInt(data.height, 10) > 0) ? data.height : 200;
		}

		width = (width.toString().indexOf('%')) ? width : parseInt(width, 10); // Support percentages
		try {
			if ("units" in data.defaults) {
				data.defaults.units = window.google.maps.UnitSystem[data.defaults.units];
			} else {
				data.defaults.units = window.google.maps.UnitSystem.METRIC;
			}
		} catch (e) {
			data.units = window.google.maps.UnitSystem.METRIC;
		}

		data.zoom = parseInt(data.zoom, 10) ? parseInt(data.zoom, 10) : 1;
		data.map_type = (data.map_type) ? data.map_type : 'ROADMAP';
		data.map_alignment = data.map_alignment || data.defaults.map_alignment;
		data.image_size = data.image_size || data.defaults.image_size;
		data.image_limit = data.image_limit || data.defaults.image_limit;

		data.street_view = ("street_view" in data) ? data.street_view : 0;

		container.wrap('<div id="map_' + mapId + '_alignment_container"></div>');
		alignmentContainer = jQuery('#map_' + mapId + '_alignment_container');

		container.html('<div id="' + mapId + '"></div>');
		jQuery('#' + mapId)
			.width(width)
			.height(parseInt(height, 10));

		container
			.width(width);

		if ( ! data.show_map || ! parseInt(data.show_map, 10) ) {
			jQuery('#' + mapId).css({
				"position": "absolute",
				"left": "-120000px"
			});
		}

		var mapOptions = {
			"zoom": parseInt(data.zoom, 10) ? parseInt(data.zoom, 10) : 1,
			"minZoom": 1,
			"center": new window.google.maps.LatLng(40.7171, -74.0039), // New York
			"mapTypeId": window.google.maps.MapTypeId[data.map_type]
		};
		var resolvedMapId = getResolvedMapId();

		if ( resolvedMapId ) {
			mapOptions.mapId = resolvedMapId;
		}

		map = new window.google.maps.Map(
			jQuery('#' + mapId).get(0),
			mapOptions
		);
		map.__agmSupportsAdvancedMarkers = !!resolvedMapId;
		map.__agmMapId = resolvedMapId;

		travelType = 'DRIVING';

		container.append(
			'<div id="agm_mh_footer" class="agm_mh_footer">' +
				'<div class="agm_mh_container" id="agm_mh_markers_' + mapId + '">' +
				'</div>' +
			'</div>'
		);

		addMarkers();
		window.setTimeout( function() { populateLinksToPostsMarkup(); }, 20 );

		if ( parseInt(data.street_view, 10) ) {
			var panorama = map.getStreetView();
			var pos = null;

			if ( data.street_view_pos ) {
				pos = new window.google.maps.LatLng(data.street_view_pos[0], data.street_view_pos[1]);
			} else {
				pos = map.getCenter();
			}

			panorama.setPosition(pos);
			if ( data.street_view_pov ) {
				panorama.setPov({
					"heading": parseInt(data.street_view_pov.heading, 10),
					"pitch": parseInt(data.street_view_pov.pitch, 10),
					"zoom": parseInt(data.street_view_pov.zoom, 10)
				});
			}
			panorama.setVisible(true);
		}

		var plot_routes = false;
		try { if (data.plot_routes) { plot_routes = true; } }
		catch (e) {}
		if ( plot_routes ) { plotRoutes(); }

		jQuery(document).trigger("agm_google_maps-user-map_initialized", [map, data, _markers]); // old style
		jQuery(document).trigger("agm:init", [map, data, _markers]);

		jQuery(document).trigger("agm_google_maps-user-map_postprocess_markers", [data, _markers, addNewMarker]); // old style
		jQuery(document).trigger("agm:postprocess_markers", [data, _markers, addNewMarker]);

		jQuery(document).trigger("agm_google_maps-user-sort_function", [map, data, setSortFunction]); // old style
		jQuery(document).trigger("agm:sort", [map, data, setSortFunction]);

		// Set alignment
		switch ( data.map_alignment ) {
			case "right":
				container.css({"float": "right"});
				break;

			case "center":
				container.css({"margin": "0 auto"});
				break;

			default:
				container.css({"float": "left"});
				break;
		}
		alignmentContainer.append( '<div style="clear:both"></div>' );

		jQuery('body').on('click', selector + ' .agm_mh_travel_type', switchTravelType);
		jQuery('body').on('click', selector + ' .agm_mh_swap_direction_waypoints', swapWaypoints);
		jQuery('body').on('click', selector + ' .agm_mh_close_directions', closeDirections);
		jQuery('body').on('click', selector + ' .agm_mh_get_directions', getDirections);
		jQuery('body').on('click', selector + ' .agm_mh_marker_item', centerToMarker);
		jQuery('body').on('click', selector + ' .agm_mh_marker_item_directions', setDirectionWaypoint);

		container.on('agm:update_marker', refreshMarker);
		container.on('agm:remove_marker', removeMarker);
		container.on('agm:sort_function', setSortFunction);

		// Fix for initially hidden maps
		tmr_init = window.setInterval(function () {
			if ( is_loaded && container.is( ':visible' ) ) {
				// Re-Center the map on current position once it is loaded + visible.
				var center = map.getCenter();
				window.google.maps.event.trigger(map, 'resize');
				map.setCenter(center);

				// Only do this once.
				jQuery(document).trigger("agm:after_init", [map, data, _markers]);
				window.clearInterval( tmr_init );
			}
		}, 300);

		// Idle event fires once there is nothing more to load.
		window.google.maps.event.addListenerOnce(map, 'idle', function(){
			is_loaded = true;
		});
	};

	init();

};

/**
 * Uses global _agmMaps array to create the needed map objects.
 * Deferres AgmMapHandler creation until Google Maps API is available.
 */
function createMaps () {
	if ( ! window._agmMapIsLoaded ) {
		window.setTimeout(createMaps, 100);
	} else {
		jQuery.each(_agmMaps, function (idx, map) {
			new window.AgmMapHandler( map.selector, map.data );
		});

		// Flag: All maps are fully loaded.
		_agm.initialized = true;
	}
}

// Create map objects on document.load,
// or as soon as we're able to
createMaps();

var init_popups = function init_popups() {
	var show_map_popup = function show_map_popup( event ) {
		event.preventDefault();
		event.stopPropagation();

		var margin_left, margin_top, map,
			id = jQuery(this).attr('id') + '-container',
			body = jQuery('body'),
			html = jQuery('html'),
			map_box = jQuery('#' + id),
			map_modal = jQuery('<div class="agm-modal-back"></div>').appendTo(body),
			body_overflow = body.css('overflow'),
			html_overflow = html.css('overflow'),
			width = map_box.data('width'),
			height = map_box.data('height'),
			map_el = map_box.find('.agm_google_maps'),
			agm_selector = '#' + map_el.attr('id');

		for (var ind = _agmMaps.length - 1; ind >= 0; ind--) {
			if (_agmMaps[ind].selector === agm_selector) {
				map = _agmMaps[ind];
				break;
			}
		}

		map_box.show();
		map_modal.show();

		if ( ! width.toString().length || isNaN( width ) ) { width = map.data.defaults.width; }
		if ( ! height.toString().length || isNaN( height ) ) { height = map.data.defaults.height; }

		if ( width.toString().length && !isNaN( width ) ) {
			margin_left = (width / 2) * -1;
			map_box.css({'width': width, 'margin-left': margin_left});
		}
		if ( height.toString().length && !isNaN( height ) ) {
			height = parseInt(height);
			if ( parseInt( map.data.show_markers ) ) {
				var marker_box = jQuery('#agm_mh_footer', map_box),
					marker_count = 2;

				if ( map.data.markers.length > 2 ) {
					if ( map.data.markers.length < 5 ) {
						marker_count = map.data.markers.length;
					} else {
						marker_count = 5;
					}
				}

				// Position the marker-list right below the map
				marker_box.css({'top': height, 'bottom': 0});
				// Increase the popup height to show the marker list
				height += 68 * marker_count;
			}
			margin_top = (height / 2) * -1;
			map_box.css({'height': height, 'margin-top': margin_top});
		}

		body.css('overflow', 'hidden');
		html.css('overflow', 'hidden');

		// one: this event can be fired only once.
		map_box.one('agm:close', function() {
			body.css('overflow', body_overflow);
			html.css('overflow', html_overflow);

			map_box.hide();
			map_modal.remove();
		});

		map_modal.click( function hide_map() {
			map_box.trigger( 'agm:close' );
		});

		return false;
	};

	jQuery('body').on('click', '.agm-map-popup', show_map_popup);
};

init_popups();

});
