function bindToggle(){$(".accordions .accordions__title:not(.locked):not(.toggle-bound)").addClass("toggle-bound").on("click").click(function(){var $parent=$(this).parent("li"),$toggleBlock=$parent.children(".accordions__content");$parent.toggleClass("arrow-active"),$parent.find(".show-hide").text($parent.hasClass("arrow-active")?"свернуть":"подробнее"),$toggleBlock.stop(),$toggleBlock.slideToggle()})}function hideCard(el){$(el).closest("li").children().first().click()}function beforeCloseModal(){bodyScrollLock.clearAllBodyScrollLocks()}function beforeOpenModal(id){bodyScrollLock.disableBodyScroll(document.querySelector("#modal-"+id+" .modal-content"))}var isMobile=!0,isIphone4=window.screen&&480==window.screen.height;$(document).ready(function(){$(".header-btn__menu__svg").click(function(){openModal("menu")}),isMobile&&isIphone4&&$("body").addClass("iphone4fix")});