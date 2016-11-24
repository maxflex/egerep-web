$(document).ready(function() {
	$('.header-btn__menu__svg').click(function() {
		$('#menu-overlay').addClass('open').removeClass('pseudo-hidden')
		$('body, html').addClass('body-crop')
	})

	$('#menu-overlay img').click(function() {
		$('#menu-overlay').removeClass('open')
		setTimeout(function() {
			$('#menu-overlay').addClass('pseudo-hidden')
		}, 400)
		$('body, html').removeClass('body-crop')
	})

	$('#search-tutor-button').click(function() {
		search_tutor_id = $('#search-tutor-id').val()
		if (search_tutor_id != '') {
			location.href = '/' + search_tutor_id
		}
	})
})


function bindToggle()
{
	$('.toggle-widget__title:not(.locked):not(.toggle-bound)').addClass('toggle-bound').on('click').click(function() {
		var $parent = $(this).parent('.toggle-widget');
		var $toggleBlock = $parent.children('.toggle-widget__inner');
		$(this).toggleClass('active');
		$toggleBlock.stop();
		$toggleBlock.slideToggle();
	});
    $('.accordions .accordions__title:not(.locked):not(.toggle-bound)').addClass('toggle-bound').on('click').click(function() {
		var $parent = $(this).parent('li');
		var $toggleBlock = $parent.children('.accordions__content');
		$(this).toggleClass('active');
		$toggleBlock.stop();
		$toggleBlock.slideToggle();
	});
}
