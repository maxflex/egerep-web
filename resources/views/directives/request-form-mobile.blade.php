<form class='contacts-form'>
    <div ng-hide='tutor.request_sent && !fixedHeight'>
        <div class="form-row">
            <input class="input-text" type="text" placeholder="ваше имя" name='name' maxlength='60'
                ng-model='tutor.request.name'>
        </div>
        <div class="form-row">
            <div class='input-group-custom'>
                <span>+7</span>
                <input class="input-text" type="tel" placeholder="телефон" name='phone'
                    ng-model='tutor.request.phone' maxlength='25'>
            </div>
        </div>
        <div class="form-row">
            <textarea ng-model='tutor.request.comment' class="textarea" placeholder="сообщение" maxlength='500'></textarea>
        </div>
        <div class="align-center">
            <button class="btn btn-gradient btn-large btn-request" ng-click='request()'>
                <i class="fa fa-envelope" aria-hidden="true"></i>
                <span>отправить</span>
            </button>
        </div>
        <div class="error-message" ng-show='tutor.request_error'>
            Отправка сообщения временно недоступна<br>
            Оставьте заявку по телефону +7 495 646-10-80
        </div>
    </div>
    <div class="request-form-sent" ng-show='tutor.request_sent && !fixedHeight'>
        <h2>Спасибо!</h2>
        <img class="request-sent-svg" src="/img/svg/letter.svg">
        <div>Ваше сообщение отправлено</div>
    </div>
</form>
