<form class="search-result-contact">
   <div class="search-result-contact-row">
       <input type="text" class="input-text" placeholder="ваше имя" ng-model='tutor.request.name'>
       <input type="text" class="input-text phone-field" placeholder="ваш телефон" ng-phone ng-model='tutor.request.phone'>
   </div>

   <textarea placeholder='краткое сообщение репетитору' class="textarea" rows="0" cols="0" ng-model='tutor.request.comment'></textarea>

   <div class="search-result-contact-button">
       <button class="button-green" ng-click='request()'>
           Отправить
       </button>
   </div>

   <div class="error-message" ng-show='tutor.request_error'>
       В настоящий момент заявка не может быть отправлена. Оставьте заявку по телефону +7 495 646-10-80
   </div>
</form>
