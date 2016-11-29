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

    document.documentElement.addEventListener('touchstart', function (event) {
        if (event.touches.length > 1) {
            event.preventDefault();
          }
        }, false);
})


function bindToggle()
{
	// $('.toggle-widget__title:not(.locked):not(.toggle-bound)').addClass('toggle-bound').on('click').click(function() {
	// 	var $parent = $(this).parent('.toggle-widget');
	// 	var $toggleBlock = $parent.children('.toggle-widget__inner');
	// 	$parent.toggleClass('arrow-active');
    //     debugger
	// 	$toggleBlock.stop();
	// 	$toggleBlock.slideToggle();
	// });
    $('.accordions .accordions__title:not(.locked):not(.toggle-bound)').addClass('toggle-bound').on('click').click(function() {
		var $parent = $(this).parent('li');
		var $toggleBlock = $parent.children('.accordions__content');
		$parent.toggleClass('arrow-active');
		$toggleBlock.stop();
		$toggleBlock.slideToggle();
	}).parent('li').find('.arrow').on('click', function() {
        $(this).parent().children().first().click()
    });
}

function hideCard(el) {
    $(el).closest('li').children().first().click()
}

function closeModal(el) {
    $('.modal').removeClass('active')
    $('body').removeClass('modal-open')
}
