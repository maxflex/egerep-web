var isMobile = true

$(document).ready(function() {
	$('.header-btn__menu__svg').click(function() {
        openModal('menu')
	})

    // document.documentElement.addEventListener('touchstart', function (event) {
    //     if (event.touches.length > 1) {
    //         event.preventDefault();
    //       }
    //     }, false);
})


function bindToggle()
{
    $('.accordions .accordions__title:not(.locked):not(.toggle-bound)').addClass('toggle-bound').on('click').click(function() {
		var $parent = $(this).parent('li');
		var $toggleBlock = $parent.children('.accordions__content');
		$parent.toggleClass('arrow-active');
        $parent.find('.show-hide').text($parent.hasClass('arrow-active') ? 'свернуть' : 'показать')
		$toggleBlock.stop();
		$toggleBlock.slideToggle();
	})
}

function hideCard(el) {
    $(el).closest('li').children().first().click()
}
