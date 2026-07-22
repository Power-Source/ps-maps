/*! PS Maps - v2.9.4
 * https://cp-psource.github.io/ps-maps/
 * Copyright (c) 2017; * Licensed GPLv2+ */
/*global window:false */
/*global document:false */
/*global _agm:false */
/*global navigator:false */

jQuery(document).on("agm_google_maps-user-map_initialized", function (e, map, data) {
	if ( ! data.is_responsive ) {
		return false; // Short out
	}

	var el = jQuery(map.getDiv()),
		container = el.parents(".agm_google_maps"),
		parent = container.parent(),
		center = map.getCenter(),
		total_width = parent.width(),
		map_width = el.width(),
		width_is_percentage = -1 !== (String( data.width || '' ).indexOf('%'))
	;

	container.addClass('agm-responsive-map').css({
		width: '100%',
		maxWidth: '100%'
	});
	el.css({
		width: '100%',
		maxWidth: '100%'
	});

	jQuery(window).on('resize', function () {
		var width = parent.width();

		if ( data.responsive_respect_width && width_is_percentage ) {
			width = (width / total_width) * map_width;
		}

		container.width(width);
		el.width(width);
		window.google.maps.event.trigger(map, 'resize');
		map.setCenter(center);
	}).triggerHandler('resize');
});