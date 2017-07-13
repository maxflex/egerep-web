    active_modal = false

    $(document).ready(function() {
        $('.custom-select').customSelect()
        moment.locale('ru-RU')
        $('.footer-search-input')
            .on('keydown', function(event) {
                if (event.keyCode == 13) {
                    goTutor()
                }
            })
            .on('focusout', function(){
                this.blur();
            })
        $('div:has(>a[href^="https://www.google.com/maps"])').remove()
    })

    $(document).on('keyup', function(event) {
        if (event.keyCode == 27) {
            closeModal()
        }
    })

    angular.element(document).ready(function() {
		setTimeout(function() {
			setScope()
		}, 50)
	})

    // настройки оповещения
    notify_options = {
        hideAnimation: 'fadeOut',
        showDuration: 0,
        hideDuration: 400,
        autoHideDelay: 3000
    }

    function closeModal() {
        $('.modal').removeClass('active')
        $('body').removeClass('modal-open')
		$("body").addClass('open-modal-' + active_modal); active_modal = false
        $('.container').off('touchmove');
        if(window.location.hash == "#modal") {
            window.history.back()
        }
    }

    function openModal(id) {
        $(".modal#modal-" + id).addClass('active')
        $('#menu-overlay').height('95%').scrollTop(); // iphone5-safari fix
        $("body").addClass('modal-open open-modal-' + id); active_modal = id
        $('.container').on('touchmove', function(e){e.preventDefault();});
        window.location.hash = '#modal'
    }

    // close modal on «back» button
    $(window).on('hashchange', function() {
        if(window.location.hash != "#modal") {
            closeModal()
        }
    });

	/**
	 * Remove by id
	 */
	function removeById(object, id) {
		return _.without(object, _.findWhere(object, {id: id}))
	}

	/**
	 * Find by id
	 */
	function findById(object, id) {
		return _.findWhere(object, {id: parseInt(id)})
	}

	/**
	 * Svg map debug function.
	 * @todo: delete this when done debugging
	 */
	function clickSt(id) {
		$('iframe').contents().find('#st' + id).click();
	}
	function selectLine(id) {
		return $('iframe').contents().find('#line' + id);
	}

	/**
	 * Helper function for recording pagination history
	 */
	function paginate(entity, page) {
		window.history.pushState('', '', entity + '?page=' + page)
	}

	/**
	 * Helper funciton for selectpicker
	 */
	function sp(id, placeholder, multipleSeparator) {
		setTimeout(function() {
			$('#sp-' + id).selectpicker({
				noneSelectedText: placeholder,
				multipleSeparator: multipleSeparator === undefined ? ', ' : multipleSeparator,
			})
		}, 50)
	}

	function spRefresh(id) {
		setTimeout(function() {
			$('#sp-' + id).selectpicker('refresh')
		}, 50)
	}

	function spe(element, placeholder) {
		setTimeout(function() {
			$(element).selectpicker({
				noneSelectedText: placeholder
			})
		}, 50)
	}

	function speRefresh(element) {
		setTimeout(function() {
			$(element).selectpicker('refresh')
		}, 50)
	}

	/**
	 * Helper functions to start/stop ajax requests
	 */
	function ajaxStart() {
		NProgress.start()
	}

	function ajaxEnd() {
		NProgress.done()
	}

	/**
	 * Биндит аргументы контроллера ангуляра в $scope
	 */
	function bindArguments(scope, arguments) {
		function_arguments = getArguments(arguments.callee)

		for (i = 1; i < arguments.length; i++) {
			function_name = function_arguments[i]
			if (function_name[0] === '$') {
				continue
			}
			scope[function_name] = arguments[i]
		}
	}

	/**
	 * Получить аргументы функции в виде строки
	 * @link: http://stackoverflow.com/a/9924463/2274406
	 */
	var STRIP_COMMENTS = /((\/\/.*$)|(\/\*[\s\S]*?\*\/))/mg;
	var ARGUMENT_NAMES = /([^\s,]+)/g;
	function getArguments(func) {
	  var fnStr = func.toString().replace(STRIP_COMMENTS, '');
	  var result = fnStr.slice(fnStr.indexOf('(')+1, fnStr.indexOf(')')).match(ARGUMENT_NAMES);
	  if(result === null)
	     result = [];
	  return result;
	}

	/**
	 * Стандартная дата
	 */
	function convertDate(date) {
		date = date.split(".")
		date = date.reverse()
		date = date.join("-")
		return date
	}

	/**
	 * Configure plugins
	 */
	function configurePlugins() {

	}

	/**
	 * Переназначает маски для всех элементов, включая новые
	 *
	 */
	function rebindMasks(delay) {
		// Немного ждем, чтобы новые элементы успели добавиться в DOM
		setTimeout(function() {
			$('.sp').selectpicker()

			// Дата
			$('.bs-date').datepicker({
				language	: 'ru',
				orientation	: 'top left',
				autoclose	: true
			})

			// Дата, начиная с нынчашнего дня
			$('.bs-date-now').datepicker({
				language	: 'ru',
				orientation	: 'top left',
				startDate: '-0d',
				autoclose	: true
			})

			// Дата вверху
			$(".bs-date-top").datepicker({
				language	: 'ru',
				autoclose	: true,
				orientation	: 'bottom auto',
			})

			// С очисткой даты
			$(".bs-date-clear").datepicker({
				language	: 'ru',
				autoclose	: true,
				orientation	: 'bottom auto',
				clearBtn    : true
			})

			// $(".passport-number").inputmask("Regex", {regex: "[a-zA-Z0-9]{0,12}"});
			// $(".digits-year").inputmask("Regex", {regex: "[0-9]{0,4}"});
			//
			// // REGEX для полей типа "число" и "1-5"
			// $(".digits-only-float").inputmask("Regex", {regex: "[0-9]*[.]?[0-9]+"});
			// $(".digits-only-minus").inputmask("Regex", {regex: "[-]?[0-9]*"});
			// $(".digits-only").inputmask("Regex", {regex: "[0-9]*"});
			//
			// $.mask.definitions['H'] = "[0-2]";
		    // $.mask.definitions['h'] = "[0-9]";
		    // $.mask.definitions['M'] = "[0-5]";
		    // $.mask.definitions['m'] = "[0-9]";
			// $(".timemask").mask("Hh:Mm", {clearIfNotMatch: true});
			//
			// // Маска телефонов
			// $(".phone-masked")
			// 	.mask("+7 (999) 999-99-99", { autoclear: false })
		}, (delay === undefined ? 100 : delay)  )
	}

	/**
	 * Нотифай с сообщением об ошибке.
	 *
	 */
	function notifyError(message) {
		$.notify({'message': message, icon: "glyphicon glyphicon-remove"}, {
			type : "danger",
			allow_dismiss : false,
			placement: {
				from: "top",
			}
		});
	}

	/**
	 * Нотифай с сообщением об успехе.
	 *
	 */
	function notifySuccess(message) {
		$.notify({'message': message, icon: "glyphicon glyphicon-ok"}, {
			type : "success",
			allow_dismiss : false,
			placement: {
				from: "top",
			}
		});
	}

	// Установить scope
	function setScope() {
		scope = angular.element('[ng-app=Egerep]').scope()
	}


/**
 * Инициализировать array перед push, если он не установлен, чтобы не было ошибки.
 *
 */
function initIfNotSet(arr) {
	if (!arr) {
		arr = []
	}
	return arr
}

/**
 * Инициализировать array перед push, если он не установлен, чтобы не было ошибки.
 *
 */
function initIfNotSetObject(obj) {
	if (!obj) {
		obj = {}
	}
	return obj
}


/**
 * Анимация аякса.
 *
 */
function frontendLoadingStart()
{
	$("#frontend-loading").fadeIn(300)
}
function frontendLoadingEnd()
{
	$("#frontend-loading").hide()
}

/**
 * Печать дива.
 *
 */
function printDiv(id_div) {
	var contents = document.getElementById(id_div).innerHTML;
	var frame1 = document.createElement('iframe');
	frame1.name = "frame1";
	frame1.style.position = "absolute";
	frame1.style.top = "-1000000px";

	document.body.appendChild(frame1);
	var frameDoc = frame1.contentWindow ? frame1.contentWindow : frame1.contentDocument.document ? frame1.contentDocument.document : frame1.contentDocument;
	frameDoc.document.open();
	frameDoc.document.write('<html><head><title>ЕГЭ Центр</title>');
	frameDoc.document.write("<style type='text/css'>\
		h4 {text-align: center}\
		p {text-indent: 50px; margin: 0}\
	  </style>"
	);
	frameDoc.document.write('</head><body>');
	frameDoc.document.write(contents);
	frameDoc.document.write('</body></html>');
	frameDoc.document.close();
	setTimeout(function () {
		window.frames["frame1"].focus();
		window.frames["frame1"].print();
		document.body.removeChild(frame1);
	}, 500);
	return false;
}

function redirect(url) {
	window.location.href = url
}

function chunk(arr, size) {
  var newArr = [];
  for (var i=0; i<arr.length; i+=size) {
    newArr.push(arr.slice(i, i+size));
  }
  return newArr;
}

function toArray(object) {
    arr = []
    $.each(object, function(i, v) {
        arr.push(v)
    })
    return arr
}

function goTutor() {
    tutor_id = $('.footer-search-input').val()
    tutor_id = parseInt(tutor_id)
    if (tutor_id > 0) {
        window.location = '/' + tutor_id
    }
}

function googleClientId() {
    if (ga && ga.getAll) {
        return ga.getAll()[0].get('clientId')
    } else {
        return ''
    }
}

function dataLayerPush(object) {
    window.dataLayer = window.dataLayer || []
    window.dataLayer.push(object)
}

function streamLink(url, action, type, additional) {
    if (additional === undefined) {
        additional = {}
    }
    // в tel: тоже не подставлять
    if (url[0] != '/' && url[0] != 't') {
        url = '/' + url
    }
    scope.StreamService.run('go_' + action, type, additional).then(function() {
        window.location = url
    })
}

function getSubdomain() {
    return window.location.host.split('.')[0]
}

function getUrlParam(param) {
    url = new URL(window.location.href)
    if (url.searchParams !== undefined) {
        return url.searchParams.get(param)
    } else {
        return null
    }
}