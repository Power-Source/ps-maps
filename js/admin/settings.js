jQuery(function ($) {
	var settings = window._agmSettings || {};

	$(document).on('click', '[data-agm_contextual_trigger]', function () {
		var $trigger = $(this),
			$target = $($trigger.attr('data-agm_contextual_trigger'));

		if ( ! $target.length ) {
			return false;
		}

		$('#contextual-help-link').trigger('click');
		$target.find('a').trigger('click');
		$(window).scrollTop(0);
		return false;
	});

	$(document).on('click', '#agm-advanced_zoom-toggler', function () {
		$('#agm-zoom-basic-container').addClass('is-hidden').find('select').prop('disabled', true);
		$('#agm-zoom-advanced-container').removeClass('is-hidden').find('#agm-zoom-advanced').prop('disabled', false);
		return false;
	});

	$(document).on('click', '#agm-basic_zoom-toggler', function () {
		$('#agm-zoom-advanced-container').addClass('is-hidden').find('#agm-zoom-advanced').prop('disabled', true);
		$('#agm-zoom-basic-container').removeClass('is-hidden').find('select').prop('disabled', false);
		return false;
	});

	$(document).on('change', '.agm_shortcode_map', function () {
		var $group = $(this).closest('.agm-shortcode-switch'),
			original = $group.data('agm-original-shortcode');

		if ( $(this).val() !== original ) {
			$group.find('.alt-hint').removeClass('is-hidden');
		} else {
			$group.find('.alt-hint').addClass('is-hidden');
		}
	});

	$(document).on('click', '.agm_plugin', function (ev) {
		var $me = $(this);

		ev.preventDefault();

		$.post(
			window.ajaxurl,
			{
				action: $me.data('action'),
				plugin: $me.data('plugin'),
				nonce: settings.nonce || ''
			},
			function () {
				window.location.reload();
			}
		);

		return false;
	});

	$(document).on('click', '.agm-addons-all', function () {
		var $list = $('.plugins');

		$('.subsubsub > a').removeClass('current');
		$(this).addClass('current');
		$list.find('tr.agm-add-on').show();
		return false;
	});

	$(document).on('click', '.agm-addons-active', function () {
		var $list = $('.plugins');

		$('.subsubsub > a').removeClass('current');
		$(this).addClass('current');
		$list.find('tr.agm-add-on.inactive').hide();
		$list.find('tr.agm-add-on.active').show();
		return false;
	});

	$(document).on('click', '.agm-addons-inactive', function () {
		var $list = $('.plugins');

		$('.subsubsub > a').removeClass('current');
		$(this).addClass('current');
		$list.find('tr.agm-add-on.inactive').show();
		$list.find('tr.agm-add-on.active').hide();
		return false;
	});
});