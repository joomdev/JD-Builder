(function ($) {
	$(document).keydown(function (e) {
		if (e.keyCode == 70 && e.ctrlKey && e.shiftKey && !e.metaKey) {
			e.preventDefault();
			$('#jdfinderUnderlay').addClass('visible');
			$('#jdfindersearch').focus();
		}
	});
	$(document).on('click', '.jdfinder-close', function (e) {
		$('#jdfinderUnderlay').removeClass('visible');
	});
	document.onkeydown = function (e) {
		if (e.keyCode == 27) {
			$('#jdfinderUnderlay').removeClass('visible');
		}
	}
	$(document).on("keyup", '.search', function () {
		var srchInput = $('.search').val();
		$('#jdfindersearch').addClass('searchajax');
		$.getJSON('index.php?option=com_ajax&group=system&plugin=jdfinder&method=onsearchtitles&q=&format=json', {
			'q': srchInput
		}, function (searchdata) {
			$('.jdfinder_results').html(searchdata.data);
			$('#jdfindersearch').removeClass('searchajax');
		});
	});
})(jQuery);