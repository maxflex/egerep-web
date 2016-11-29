<form class='contacts-form'>
    <div ng-hide='tutor.request_sent'>
        <div class="form-row">
            <input class="input-text" type="text" placeholder="ваше имя" name='name' maxlength='60'
                ng-model='tutor.request.name'>
        </div>
        <div class="form-row">
            <input ng-phone class="input-text phone-input" type="tel" placeholder="телефон" name='phone'
                ng-model='tutor.request.phone'>
        </div>
        <div class="form-row">
            <textarea ng-model='tutor.request.comment' class="textarea" placeholder="сообщение" maxlength='500'></textarea>
        </div>
        <div class="align-center">
            <button class="btn btn-gradient btn-large" ng-click='request()'><span>отправить</span></button>
        </div>
        <div class="error-message" ng-show='tutor.request_error'>
            Отправка сообщения временно недоступна<br>
            Оставьте заявку по телефону +7 495 646-10-80
        </div>
    </div>
    <div class="request-form-sent" ng-show='tutor.request_sent'>
        <h2>Спасибо!</h2>
        <span>Ваше сообщение отправлено</span>
    </div>
</form>
