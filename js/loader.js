/*! PS Maps - v2.9.4
 * https://cp-psource.github.io/ps-maps/
 * Copyright (c) 2017; * Licensed GPLv2+ */
/*! Google Maps - v2.9.07
 * https://cp-psource.github.io/ps-maps/
 * Copyright (c) 2015; * Licensed GPLv2+ */
/*global window:false */
/*global document:false */
/*global _agm:false */
/*global jQuery:false */

/**
 * Asynchrounously load Google Maps API.
 */


/**
 * Global API loaded flag.
 */
window._agmMapIsLoaded = false;


/**
 * Callback - triggers loaded flag setting.
 */
function agmInitialize () {
	window._agmMapIsLoaded = true;
	if ( undefined !== window.google.maps.Map._agm_get_markers ) {
		return true;
	}

	window.google.maps.Map.prototype._agm_markers = [];
	window.google.maps.Map.prototype._agm_get_markers = function () { return this._agm_markers; };
	window.google.maps.Map.prototype._agm_clear_markers = function () { this._agm_markers = []; };
	window.google.maps.Map.prototype._agm_add_marker = function (mrk) { this._agm_markers.push(mrk); };
	window.google.maps.Map.prototype._agm_remove_marker = function (idx) { this._agm_markers.splice(idx, 1); };
}

/**
 * Handles the actual loading of Google Maps API.
 */
function loadGoogleMaps () {
	if ( typeof window.google === 'object' &&
		typeof window.google.maps === 'object'
	) {
		// We're loaded and ready - albeit from a different source.
		return agmInitialize();
	}

	var language = '',
		src 	= 'https://maps.googleapis.com/maps/api/js?v=3',
		script = document.createElement("script"),
		libs = (_agm.libraries || []),
		filtered_libs = [],
		libraries = '',
		ii = 0,
		api_key = ((window || {})._agm || {}).maps_api_key || false
	;

	if ( window._agmLanguage !== undefined ) {
		try { language = '&language=' + window._agmLanguage; }
		catch (ex) { language = ''; }
	}
	script.type = "text/javascript";
	script.async = true;
	script.defer = true;

	if (api_key) {
		src += "&key=" + api_key;
	}

	for ( ii = 0; ii < libs.length; ii += 1 ) {
		if ( libs[ii] && libs[ii] !== 'panoramio' ) {
			filtered_libs.push( libs[ii] );
		}
	}
	libraries = filtered_libs.join(",");
	if ( libraries ) {
		src += "&libraries=" + libraries;
	}

	script.src = src +
		language +
		"&loading=async&callback=agmInitialize";
	document.body.appendChild(script);
}

jQuery( window ).on( 'load', loadGoogleMaps );
