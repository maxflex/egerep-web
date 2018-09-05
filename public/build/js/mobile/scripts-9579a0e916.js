var isMobile = true
var isIphone4 = window.screen && (window.screen.height == (960 / 2));

$(document).ready(function() {
	$('.header-btn__menu__svg').click(function() {
        openModal('menu')
	})


	document.addEventListener("touchstart", function(e) {
		console.log('touch start...')
		e.preventDefault()
	})

	if (isMobile && isIphone4) $('body').addClass('iphone4fix');
})

function bindToggle()
{
    $('.accordions .accordions__title:not(.locked):not(.toggle-bound)').addClass('toggle-bound').on('click').click(function() {
		var $parent = $(this).parent('li');
		var $toggleBlock = $parent.children('.accordions__content');
		$parent.toggleClass('arrow-active');
        $parent.find('.show-hide').text($parent.hasClass('arrow-active') ? 'свернуть' : 'подробнее')
		$toggleBlock.stop();
		$toggleBlock.slideToggle();
	})
}

function hideCard(el) {
    $(el).closest('li').children().first().click()
}

function beforeCloseModal() {
	bodyScrollLock.clearAllBodyScrollLocks()
}

function beforeOpenModal(id) {
	bodyScrollLock.disableBodyScroll(document.querySelector('#modal-' + id + ' .modal-content'))
}

//# sourceMappingURL=scripts.js.map
