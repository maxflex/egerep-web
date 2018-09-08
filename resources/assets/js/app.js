(function(){var indexOf=[].indexOf||function(item){for(var i=0,l=this.length;i<l;i++)if(i in this&&this[i]===item)return i;return-1};angular.module("Egerep",["ngResource","angularFileUpload","angular-toArrayFilter","svgmap","ngSanitize","angucomplete-alt"]).config(["$compileProvider",function($compileProvider){return $compileProvider.aHrefSanitizationWhitelist(/^\s*(https?|ftp|mailto|chrome-extension|sip):/)}]).filter("cut",function(){return function(value,wordwise,max,nothing,tail){var lastspace;return null==nothing&&(nothing=""),value?(max=parseInt(max,10))?value.length<=max?value:(value=value.substr(0,max),wordwise&&(lastspace=value.lastIndexOf(" "),lastspace!==-1&&("."!==value.charAt(lastspace-1)&&","!==value.charAt(lastspace-1)||(lastspace-=1),value=value.substr(0,lastspace))),value+(tail||"…")):value:nothing}}).filter("hideZero",function(){return function(item){return item>0?item:null}}).run(function($rootScope,$q){return $rootScope.dataLoaded=$q.defer(),$rootScope.frontendStop=function(rebind_masks){if(null==rebind_masks&&(rebind_masks=!0),$rootScope.frontend_loading=!1,$rootScope.dataLoaded.resolve(!0),rebind_masks)return rebindMasks()},$rootScope.withTailingDot=function(text){var char;return text=text.trim(),char=text[text.length-1],["!","."].indexOf(char)===-1&&(text+="."),text},$rootScope.range=function(min,max,step){var i,input;for(step=step||1,input=[],i=min;i<=max;)input.push(i),i+=step;return input},$rootScope.yearsPassed=function(year){return moment().format("YYYY")-year},$rootScope.toggleEnum=function(ngModel,status,ngEnum,skip_values,allowed_user_ids,recursion){var ref,ref1,ref2,status_id,statuses;if(null==skip_values&&(skip_values=[]),null==allowed_user_ids&&(allowed_user_ids=[]),null==recursion&&(recursion=!1),!(!recursion&&(ref=parseInt(ngModel[status]),indexOf.call(skip_values,ref)>=0)&&(ref1=$rootScope.$$childHead.user.id,indexOf.call(allowed_user_ids,ref1)<0)))return statuses=Object.keys(ngEnum),status_id=statuses.indexOf(ngModel[status].toString()),status_id++,status_id>statuses.length-1&&(status_id=0),ngModel[status]=statuses[status_id],indexOf.call(skip_values,status_id)>=0&&(ref2=$rootScope.$$childHead.user.id,indexOf.call(allowed_user_ids,ref2)<0)?$rootScope.toggleEnum(ngModel,status,ngEnum,skip_values,allowed_user_ids,!0):void 0},$rootScope.toggleEnumServer=function(ngModel,status,ngEnum,Resource){var status_id,statuses,update_data;return statuses=Object.keys(ngEnum),status_id=statuses.indexOf(ngModel[status].toString()),status_id++,status_id>statuses.length-1&&(status_id=0),update_data={id:ngModel.id},update_data[status]=status_id,Resource.update(update_data,function(){return ngModel[status]=statuses[status_id]})},$rootScope.formatDateTime=function(date){return moment(date).format("DD.MM.YY в HH:mm")},$rootScope.formatDate=function(date,full_year){return null==full_year&&(full_year=!1),date?moment(date).format("DD.MM.YY"+(full_year?"YY":"")):""},$rootScope.formatDateFull=function(date){return moment(date).format("D MMMM YYYY")},$rootScope.dialog=function(id){$("#"+id).modal("show")},$rootScope.closeDialog=function(id){$("#"+id).modal("hide")},$rootScope.onEnter=function(event,fun,prevent_default){if(null==prevent_default&&(prevent_default=!0),prevent_default&&event.preventDefault(),13===event.keyCode)return fun()},$rootScope.ajaxStart=function(){return ajaxStart(),$rootScope.saving=!0},$rootScope.ajaxEnd=function(){return ajaxEnd(),$rootScope.saving=!1},$rootScope.findById=function(object,id){return _.findWhere(object,{id:parseInt(id)})},$rootScope.total=function(array,prop,prop2){var sum;return null==prop2&&(prop2=!1),sum=0,$.each(array,function(index,value){var v;return v=value[prop],prop2&&(v=v[prop2]),sum+=v}),sum},$rootScope.deny=function(ngModel,prop){return ngModel[prop]=+!ngModel[prop]},$rootScope.closestMetro=function(markers){var closest_metro;return closest_metro=markers[0].metros[0],markers.forEach(function(marker){return marker.metros.forEach(function(metro){if(metro.meters<closest_metro.meters)return closest_metro=metro})}),closest_metro.station.title},$rootScope.closestMetros=function(markers){var closest_metros;return closest_metros=[],markers&&markers.forEach(function(marker,index){return closest_metros[index]=marker.metros[0],closest_metros[index].comment=marker.comment,marker.metros.forEach(function(metro){if(metro.meters<closest_metros[index].meters)return closest_metros[index]=metro,closest_metros[index]=marker.comment})}),closest_metros},$rootScope.photoUrl=function(tutor){return tutor&&tutor.photo_exists?"https://lk.ege-repetitor.ru/img/tutors/"+tutor.id+"."+tutor.photo_extension:"https://lk.ege-repetitor.ru/img/tutors/no-profile-img.gif"},$rootScope.objectLength=function(obj){return Object.keys(obj).length},$rootScope.formatBytes=function(bytes){return bytes<1024?bytes+" Bytes":bytes<1048576?(bytes/1024).toFixed(1)+" KB":bytes<1073741824?(bytes/1048576).toFixed(1)+" MB":(bytes/1073741824).toFixed(1)+" GB"}})}).call(this),function(){angular.module("Egerep").value("Genders",{male:"мужской",female:"женский"}).value("Sources",{LANDING:"landing",LANDING_PROFILE:"landing_profile",LANDING_HELP:"landing_help",FILTER:"filter",PROFILE_REQUEST:"profilerequest",SERP_REQUEST:"serprequest",HELP_REQUEST:"helprequest",MORE_TUTORS:"more_tutors"})}.call(this),function(){}.call(this),function(){angular.module("Egerep").controller("Cv",function($scope,$timeout,Tutor,FileUploader,Cv,PhoneService,StreamService){return bindArguments($scope,arguments),$scope.error_text="ошибка: максимальная длина текста – 1000 символов",$timeout(function(){},1e3),$scope.application={agree_to_publish:1},FileUploader.FileSelect.prototype.isEmptyAfterSelection=function(){return!0},$scope.uploader=new FileUploader({url:"api/cv/uploadPhoto",alias:"photo",autoUpload:!0,method:"post",removeAfterUpload:!0,onProgressItem:function(i,progress){return $scope.percentage=progress},onCompleteItem:function(i,response,status){return $scope.percentage=void 0,200===status?($scope.application.filename=response.filename,$scope.application.filesize=response.size):($scope.upload_error=!0,$scope.application.filename=void 0)}}),$scope.clearFile=function(){return $scope.upload_error=!1,$scope.application.filename=void 0},$scope.upload=function(e){return e.preventDefault(),$scope.upload_error=!1,$("#upload-button").trigger("click"),!1},$scope.sendApplication=function(){return Cv.save($scope.application,function(){return $scope.application.sent=!0},function(response){return 422===response.status?($scope.errors={},angular.forEach(response.data,function(errors,field){var selector;return $scope.errors[field]=errors,selector="[ng-model$='"+field+"']",$("input"+selector+", textarea"+selector).focus(),$("html,body").animate({scrollTop:$("input"+selector+", textarea"+selector).first().offset().top},0)})):$scope.application.error=!0})}})}.call(this),function(){angular.module("Egerep").controller("Empty",function($scope,StreamService){return bindArguments($scope,arguments)})}.call(this),function(){angular.module("Egerep").controller("Index",function($scope,$timeout,$http,Tutor,StreamService){var searchReviews;return $timeout(function(){return StreamService.run("landing","main"),$scope.has_more_reviews=!0,$scope.reviews_page=0,$scope.reviews=[],searchReviews()}),$scope.nextReviewsPage=function(){return $scope.reviews_page++,searchReviews()},searchReviews=function(){return $scope.searching_reviews=!0,$http.get("/api/reviews?page="+$scope.reviews_page).then(function(response){return $scope.searching_reviews=!1,$scope.reviews=$scope.reviews.concat(response.data.reviews),$scope.has_more_reviews=response.data.has_more_reviews})},bindArguments($scope,arguments),$scope.selected_subject="1",$scope.refreshSelect=function(){return $timeout(function(){return $(".custom-select-sort").trigger("render")})},$scope.goSubject=function(where){return streamLink($scope.subject_routes[$scope.selected_subject],"serp_"+where,$scope.findById($scope.subjects,$scope.selected_subject).eng)},$scope.onWebsite=function(tutor,type){var attachment_month,attachment_year,current_month,current_year,month_diff,year_diff;if(null==type&&(type="month"),tutor)return current_year=parseInt(moment().format("YYYY")),attachment_year=parseInt(moment(tutor.created_at).format("YYYY")),current_month=parseInt(moment().format("M")),attachment_month=parseInt(moment(tutor.created_at).format("M")),month_diff=current_month-attachment_month,year_diff=current_year-attachment_year,month_diff<0&&(month_diff=12+month_diff,year_diff--),"month"===type?month_diff:year_diff},$scope.randomReview=function(){return $scope.loading_review=!0,$http.get("api/reviews/random").then(function(response){return $scope.random_review=response.data,$scope.loading_review=!1})}})}.call(this),function(){angular.module("Egerep").controller("LoginCtrl",function($scope,$timeout,Sms,Tutor,StreamService){var login;return bindArguments($scope,arguments),login=function(){return Tutor.login({},function(response){return $scope.tutor=response},function(){return $scope.tutor=null})},login(),$scope.sendCode=function(){return $scope.error_message=!1,Sms.save({phone:$scope.phone},function(){return $scope.code_sent=!0,$timeout(function(){return $("#code-input").focus()})},function(){return $scope.error_message="неверный номер телефона"})},$scope.checkCode=function(){return $scope.error_message=!1,Sms.get({code:$scope.code},function(){return login()},function(response){return 403===response.status&&redirect("/"),$scope.error_message="код введен неверно"})}})}.call(this),function(){angular.module("Egerep").constant("REVIEWS_PER_PAGE",5).controller("Tutors",function($scope,$http,$timeout,Tutor,SubjectService,REVIEWS_PER_PAGE,Genders,Request,StreamService,Sources,StepperService){var bindWatchers,bindWatchersDev,filter,filter_used,handleScrollDesktop,handleScrollMobile,highlight,search,search_count;return bindArguments($scope,arguments),search_count=0,$scope.popups={},$scope.station_ids={},$scope.paramsCount=function(){var count;return count=0,$scope.search.hasOwnProperty("subjects")&&Object.keys($scope.search.subjects).length&&count++,$scope.search.hasOwnProperty("sort")&&$scope.search.sort&&count++,$scope.search.hasOwnProperty("place")&&$scope.search.place&&count++,count},$scope.filterPopup=function(popup){var data;return Object.keys($scope.popups).length?void($scope.popups={}):($scope.popups[popup]=!0,$scope.mobile&&openModal("filter-"+popup),StreamService.run("filter_open",popup),"all"===popup&&$scope.hasOwnProperty("is_first_visit")?(data={event:"configuration",eventCategory:$scope.is_first_visit?"ex:open-stepper-first":"ex:open-stepper"},void dataLayerPush(data)):void 0)},$scope.getStarRating=function(rating){var segment;return segment=2*Math.floor(rating/2)+1,10*(segment-.6*(segment-rating))},$scope.getIndex=function(index){return null==index&&(index=null),null!==index?parseInt(index)+1:$scope.index_from_hash||null},$scope.streamLink=streamLink,$scope.profileLink=function(tutor,index,async,event_name){var link;return null==async&&(async=!0),null==event_name&&(event_name="tutor_profile"),index=$scope.getIndex(index),link=""+tutor.id,index&&(link+="#"+index),async&&window.open(link,"_blank"),StreamService.run("go_"+event_name,StreamService.identifySource(tutor),{position:index,tutor_id:tutor.id}).then(function(){if(!async)return window.location=link})},$scope.profilePage=function(){return RegExp(/^\/[\d]+$/).test(window.location.pathname)},bindWatchers=function(){return $scope.$watchCollection("search.subjects",function(newVal,oldVal){var data;newVal!==oldVal&&(data={event:"configuration",eventCategory:"subjects",eventAction:$scope.SubjectService.getSelected().map(function(subject_id){return $scope.subjects[subject_id].eng}).join(",")},dataLayerPush(data))}),$scope.$watchCollection("search.place",function(newVal,oldVal){var data;newVal!==oldVal&&(data={event:"configuration",eventCategory:"place",eventAction:$scope.search.place},dataLayerPush(data))}),$scope.$watchCollection("search.sort",function(newVal,oldVal){var data;newVal!==oldVal&&(data={event:"configuration",eventCategory:"sort",eventAction:$scope.search.sort},dataLayerPush(data))}),$scope.$watchCollection("search.station_id",function(newVal,oldVal){var data;newVal!==oldVal&&(data={event:"configuration",eventCategory:"station",eventAction:$scope.search.station_id?$scope.stations[$scope.search.station_id].title:""},dataLayerPush(data))})},bindWatchersDev=function(){return $scope.$watchCollection("search.subjects",function(newVal,oldVal){var data;newVal!==oldVal&&(data={event:"configuration",eventCategory:"ex:subjects",eventAction:$scope.SubjectService.getSelected().map(function(subject_id){return $scope.subjects[subject_id].eng}).join(",")},dataLayerPush(data))}),$scope.$watch("search.place",function(newVal,oldVal){var data;newVal!==oldVal&&(data={event:"configuration",eventCategory:"ex:place",eventAction:$scope.search.place},dataLayerPush(data))}),$scope.$watch("search.sort",function(newVal,oldVal){var data;newVal!==oldVal&&(data={event:"configuration",eventCategory:"ex:sort",eventAction:$scope.search.sort},dataLayerPush(data))}),$scope.$watch("search.station_id",function(newVal,oldVal){var data;newVal!==oldVal&&(data={event:"configuration",eventCategory:"ex:station",eventAction:$scope.search.station_id?$scope.stations[$scope.search.station_id].title:""},dataLayerPush(data))}),$scope.$watch("search.grade",function(newVal,oldVal){var data;newVal!==oldVal&&(data={event:"configuration",eventCategory:"ex:grade",eventAction:$scope.search.grade},dataLayerPush(data))}),$scope.$watch("search.preparation_direction",function(newVal,oldVal){var data;newVal!==oldVal&&(data={event:"configuration",eventCategory:"ex:preparation_direction",eventAction:$scope.search.preparation_direction},dataLayerPush(data))})},handleScrollDesktop=function(){var sticky,wrapper;if(!$scope.hasOwnProperty("is_first_visit"))return wrapper=$(".filter-groups"),sticky=wrapper.position().top-1,$(window).on("scroll",function(){return window.pageYOffset>sticky?$("body").addClass("sticky"):$("body").removeClass("sticky")})},handleScrollMobile=function(){},$timeout(function(){return $timeout($scope.hasOwnProperty("is_first_visit")?bindWatchersDev:bindWatchers,500),$scope.profilePage()||"/request"===window.location.pathname?($scope.index_from_hash=window.location.hash.substring(1),StreamService.run("landing",StreamService.identifySource(),$scope.profilePage()?{tutor_id:$scope.tutor.id,position:$scope.getIndex()}:{})):($scope.selected_subjects&&$scope.selected_subjects.split(",").forEach(function(subject_id){return $scope.search.subjects[subject_id]=!0}),SubjectService.init($scope.search.subjects),StreamService.run("landing","serp"),$scope.hasOwnProperty("is_first_visit")&&$scope.is_first_visit?void 0:$scope.filter())}),$scope.pairs=[[1,2],[3,4],[6,7],[8,9]],$scope.viewed_tutors=[],$scope.dateToText=function(date){var text_date;return text_date=moment(date).format("DD MMMM YYYY"),text_date.substr(3)},$scope.onWebsite=function(tutor,type){var attachment_month,attachment_year,current_month,current_year,month_diff,year_diff;if(null==type&&(type="month"),tutor)return current_year=parseInt(moment().format("YYYY")),attachment_year=parseInt(moment(tutor.created_at).format("YYYY")),current_month=parseInt(moment().format("M")),attachment_month=parseInt(moment(tutor.created_at).format("M")),month_diff=current_month-attachment_month,year_diff=current_year-attachment_year,month_diff<0&&(month_diff=12+month_diff,year_diff--),"month"===type?month_diff:year_diff},$scope.requestSent=function(tutor){return tutor.request_sent||$scope.sent_ids.indexOf(tutor.id)!==-1},$scope.gmap=function(tutor,index){return tutor.map_initialized=!0,$timeout(function(){var bounds,extendPoint1,extendPoint2,map;return map=new google.maps.Map(document.getElementById("gmap-"+tutor.id),{center:MAP_CENTER,scrollwheel:!1,zoom:8,disableDefaultUI:!0,clickableLabels:!1,clickableIcons:!1,zoomControl:!0,zoomControlOptions:{position:google.maps.ControlPosition.LEFT_BOTTOM},scaleControl:!0}),bounds=new google.maps.LatLngBounds,tutor.markers.forEach(function(marker){var marker_location,new_marker;return marker_location=new google.maps.LatLng(marker.lat,marker.lng),bounds.extend(marker_location),new_marker=newMarker(marker_location,map)}),bounds.getNorthEast().equals(bounds.getSouthWest())&&(extendPoint1=new google.maps.LatLng(bounds.getNorthEast().lat()+.005,bounds.getNorthEast().lng()+.005),extendPoint2=new google.maps.LatLng(bounds.getNorthEast().lat()-.005,bounds.getNorthEast().lng()-.005),bounds.extend(extendPoint1),bounds.extend(extendPoint2)),map.fitBounds(bounds),map.panToBounds(bounds),map.setZoom(tutor.markers.length>1?9:14),google.maps.event.addListenerOnce(map,"idle",function(){return $('div:has(>a[href^="https://www.google.com/maps"])').remove()})}),$scope.toggleShow(tutor,"map_shown","google_map",index)},$scope.getMetros=function(tutor){return _.chain(tutor.markers).pluck("metros").flatten().value()},$scope.loadReviews=function(tutor){if(void 0===tutor.all_reviews)return tutor.reviews_loading=!0,tutor.all_reviews=Tutor.reviews({id:tutor.id},function(response){return tutor.reviews_loading=!1,$scope.showMoreReviews(tutor)})},$scope.reviews=function(tutor,index){return StreamService.run("reviews",StreamService.identifySource(tutor),{position:$scope.getIndex(index),tutor_id:tutor.id}),void 0===tutor.all_reviews&&(tutor.all_reviews=Tutor.reviews({id:tutor.id},function(response){return $scope.showMoreReviews(tutor)})),$scope.toggleShow(tutor,"show_reviews","reviews",!1)},$scope.showMoreReviews=function(tutor,index){var from,to;return void 0===tutor.all_reviews?void $scope.loadReviews(tutor):(tutor.reviews_page&&StreamService.run("reviews_more",StreamService.identifySource(tutor),{position:$scope.getIndex(index),tutor_id:tutor.id,depth:(tutor.reviews_page+1)*REVIEWS_PER_PAGE+(1===tutor.reviews_page?2:0)}),tutor.reviews_page=tutor.reviews_page?tutor.reviews_page+1:1,from=(tutor.reviews_page-1)*REVIEWS_PER_PAGE+2,to=from+REVIEWS_PER_PAGE,tutor.displayed_reviews=tutor.all_reviews.slice(0,to),highlight("search-result-reviews-text"))},$scope.reviewsLeft=function(tutor){var reviews_left;return reviews_left=tutor.reviews_count-tutor.displayed_reviews.length,reviews_left>REVIEWS_PER_PAGE?REVIEWS_PER_PAGE:reviews_left},$scope.stepperFilter=function(){var data;closeStepper(),$scope.filter(),$scope.is_first_visit=!1,data={event:"configuration",eventCategory:"ex:show-tutors"},dataLayerPush(data)},filter_used=!1,$scope.filter=function(type){var params;return null==type&&(type=null),$scope.popups={},closeModal(),$scope.tutors=[],$scope.page=1,filter_used?(StreamService.updateCookie({search:StreamService.cookie.search+1}),params={search:StreamService.cookie.search,subjects:$scope.SubjectService.getSelected().join(","),station_id:$scope.search.station_id,sort:$scope.search.sort,place:$scope.search.place},StreamService.run("filter",type,params).then(function(){return filter()})):(filter(),filter_used=!0)},filter=function(){return search(),$scope.search.hidden_filter&&search_count&&delete $scope.search.hidden_filter,$.cookie("search",JSON.stringify($scope.search))},$scope.nextPage=function(){return $scope.page++,StreamService.run("load_more_tutors",null,{page:$scope.page}),search()},$scope.$watch("page",function(newVal,oldVal){if(void 0!==newVal)return $.cookie("page",$scope.page)}),$scope.isLastPage=function(){if($scope.data)return $scope.data.current_page>=$scope.data.last_page},$scope.unselectSubjects=function(subject_id){return angular.forEach($scope.search.subjects,function(enabled,id){var pair;return subject_id?(pair=_.filter(scope.pairs,function(p){return p.indexOf(parseInt(subject_id))!==-1}),pair.length||pair.push([subject_id]),pair[0].indexOf(parseInt(id))===-1?$scope.search.subjects[id]=!1:void 0):$scope.search.subjects[id]=!1})},search=function(){return $scope.searching=!0,Tutor.search({filter_used:filter_used,tutor_id:getUrlParam("tutor_id"),page:$scope.page,search:$scope.search},function(response){return search_count++,$scope.searching=!1,1===search_count&&$timeout(function(){return $scope.mobile?handleScrollMobile():handleScrollDesktop()},500),response.hasOwnProperty("url")?redirect(response.url):($scope.data=response,$scope.tutors=$scope.tutors.concat(response.data),angular.forEach($scope.tutors,function(tutor){if("string"==typeof tutor.svg_map)return tutor.svg_map=_.filter(tutor.svg_map.split(","))}),highlight("search-result-text"),$scope.mobile&&$timeout(function(){return bindToggle()}),$timeout(function(){if(window.dispatchEvent(new Event("scroll")),1===$scope.page)return $("html, body").scrollTop(0)}))})},highlight=function(className){if($scope.search&&$scope.search.hidden_filter)return $timeout(function(){return $.each($scope.search.hidden_filter,function(index,phrase){return $("."+className).mark(phrase,{separateWordSearch:!0,accuracy:{value:"exactly",limiters:["!","@","#","&","*","(",")","-","–","—","+","=","[","]","{","}","|",":",";","'",'"',"‘","’","“","”",",",".","<",">","/","?"]}})})})},$scope.showSvg=function(tutor,index){return $scope.toggleShow(tutor,"show_svg","metro_map",index)},$scope.toggleShow=function(tutor,prop,iteraction_type,index){return null==index&&(index=null),tutor[prop]?$timeout(function(){return tutor[prop]=!1},$scope.mobile?400:0):(tutor[prop]=!0,index!==!1?StreamService.run(iteraction_type,StreamService.identifySource(tutor),{position:$scope.getIndex(index),tutor_id:tutor.id}):void 0)},$scope.shortenGrades=function(tutor){var a,combo_end,combo_start,i,j,limit,pairs;if(a=tutor.grades,!(a.length<1)){for(limit=a.length-1,combo_end=-1,pairs=[],i=0;i<=limit;)if(combo_start=parseInt(a[i]),combo_start>11)i++,combo_end=-1,pairs.push($scope.grades[combo_start].title);else if(combo_start<=combo_end)i++;else{for(j=i;j<=limit&&(combo_end=parseInt(a[j]),!(combo_end>=11))&&!(parseInt(a[j+1])-combo_end>1);)j++;combo_start!==combo_end?pairs.push(combo_start+"–"+combo_end+" классы"):pairs.push(combo_start+" класс"),i++}return pairs.join(", ")}},$scope.roundRating=function(review){return Math.round(review.score/2)},$scope.popup=function(id,tutor,fn,index){return null==tutor&&(tutor=null),null==fn&&(fn=null),null==index&&(index=null),openModal(id),null!==tutor&&($scope.popup_tutor=tutor),null!==fn&&$timeout(function(){return $scope[fn](tutor,index)}),$scope.index=$scope.getIndex(index)},$scope.request=function(tutor,index){return StreamService.run("contact_tutor_button",StreamService.identifySource(tutor),{position:$scope.getIndex(index),tutor_id:tutor.id})},$scope.expandMoreInfo=function(tutor,index){return tutor.expand_info=!0,StreamService.run("expand_more_tutor_info",StreamService.identifySource(tutor),{position:$scope.getIndex(index),tutor_id:tutor.id})},$scope.expand=function(tutor,index){var event_name;return tutor.is_expanded=!tutor.is_expanded,tutor.map_initialized||$scope.gmap(tutor,index),event_name=tutor.is_expanded?"expand_tutor_info":"shrink_tutor_info",StreamService.run(event_name,StreamService.identifySource(tutor),{position:$scope.getIndex(index),tutor_id:tutor.id})},$scope.tutorPopup=function(tutor,index){return $("#modal-tutor .modal-content").scrollTop(0),$scope.gmap(tutor,index)},$scope.changeFilter=function(param,value){return null==value&&(value=null),null!==value&&($scope.search[param]=value),$scope.overlay[param]=!1,$scope.filter()},$scope.hasSelectedStation=function(tutor){return!(!$scope.search||5!==$scope.search.sort)&&tutor.svg_map.indexOf(parseInt($scope.search.station_id))!==-1},$scope.departsEverywhere=function(tutor){return!!tutor.svg_map&&("string"==typeof tutor.svg_map&&(tutor.svg_map=tutor.svg_map.split(",")),tutor.svg_map.length>=214)},$scope.sendRequest=function(){return void 0===$scope.sending_tutor.request&&($scope.sending_tutor.request={}),$scope.sending_tutor.request.tutor_id=$scope.sending_tutor.id,Request.save($scope.sending_tutor.request,function(){return $scope.sending_tutor.request_sent=!0},function(response){return 422===response.status?angular.forEach(response.data,function(errors,field){var selector;return selector="[ng-model$='"+field+"']",$(".request-overlay").find("input"+selector+", textarea"+selector).focus().notify(errors[0],notify_options)}):$scope.sending_tutor.request_error=!0})},angular.element(document).ready(function(){if($scope.mobile)return $timeout(function(){return bindToggle()})})})}.call(this),function(){var apiPath,countable,updatable;angular.module("Egerep").factory("Tutor",function($resource){return $resource(apiPath("tutors"),{id:"@id",type:"@type"},{search:{method:"POST",url:apiPath("tutors","search")},reviews:{method:"GET",isArray:!0,url:apiPath("tutors","reviews")},login:{method:"GET",url:apiPath("tutors","login")}})}).factory("Request",function($resource){return $resource(apiPath("requests"),{id:"@id"},updatable())}).factory("Sms",function($resource){return $resource(apiPath("sms"),{id:"@id"},updatable())}).factory("Cv",function($resource){return $resource(apiPath("cv"),{id:"@id"})}).factory("Stream",function($resource){return $resource(apiPath("stream"),{id:"@id"})}),apiPath=function(entity,additional){return null==additional&&(additional=""),"/api/"+entity+"/"+(additional?additional+"/":"")+":id"},updatable=function(){return{update:{method:"PUT"}}},countable=function(){return{count:{method:"GET"}}}}.call(this),function(){angular.module("Egerep").service("PhoneService",function(){var isFull;return this.checkForm=function(element){var phone_element,phone_number;return phone_element=$(element).find(".phone-field"),isFull(phone_element.val())?(phone_number=phone_element.val().match(/\d/g).join(""),"4"===phone_number[1]||"9"===phone_number[1]||(phone_element.focus().notify("номер должен начинаться с 9 или 4",notify_options),!1)):(phone_element.focus().notify("номер телефона не заполнен полностью",notify_options),!1)},isFull=function(number){return void 0!==number&&""!==number&&!number.match(/_/)},this})}.call(this),function(){angular.module("Egerep").service("StepperService",function(){return this.questions=["В каком классе учится ребенок?","Укажите предмет","Выберите направление подготовки","Укажите место для занятий",["Укажите ближайшее к Вам метро","Укажите метро, куда должен выезжать репетитор"],"Укажите сортировку анкет"],this.current_step=0,this.max_step=0,this.startStepper=function(){return $.get("/open-stepper"),"undefined"==typeof isMobile?openStepper():scope.filterPopup("all")},this.next=function(){var data;return this.current_step===this.questions.length-1?void scope.stepperFilter():(this.current_step++,this.max_step<this.current_step&&(this.max_step=this.current_step),data={event:"configuration",eventCategory:"ex:step",eventAction:"forward-"+(this.current_step+1)},void dataLayerPush(data))},this.back=function(){var data;this.current_step--,data={event:"configuration",eventCategory:"ex:step",eventAction:"back-"+(this.current_step+1)},dataLayerPush(data)},this.goToStep=function(index){var data;if(index<=this.max_step)return this.current_step=index,data={event:"configuration",eventCategory:"ex:step",eventAction:"bar-"+(this.current_step+1)},void dataLayerPush(data)},this.nextStepDisabled=function(step){return null==step&&(step=null),"undefined"!=typeof scope&&(null===step&&(step=this.current_step),0===step&&!scope.search.grade||(1===step&&!scope.SubjectService.getSelected().length||(2===step&&!scope.search.preparation_direction||(3===step&&!scope.search.place||(4===step&&!scope.search.station_id||(5===step&&!scope.search.sort||void 0))))))},this.grades=["1 класс","2 класс","3 класс","4 класс","5 класс","6 класс","7 класс","8 класс","9 класс","10 класс","11 класс","студент колледжа","студент вуза","взрослый"],this.places=[{value:"tutor",title:"дома у репетитора"},{value:"home",title:"дома у ученика"}],this.sort=[{value:"most-popular",title:"сначала самые востребованные по Москве"},{value:"nearest-metro",title:"ближайшие к метро"}],this.preparation_directions=["подготовка к ЕГЭ","подготовка к ОГЭ","подготовка к ДВИ","улучшение успеваемости в школе"],this})}.call(this),function(){angular.module("Egerep").service("StreamService",function($http,$timeout,Stream,SubjectService,Sources){return this.identifySource=function(tutor){return null==tutor&&(tutor=void 0),void 0!==tutor&&tutor.is_similar?"similar":RegExp(/^\/[\d]+$/).test(window.location.pathname)?"tutor":"/request"===window.location.pathname?"help":"/"===window.location.pathname?"main":"serp"},this.generateEventString=function(params){var parts,search;return search=$.cookie("search"),void 0!==search&&$.each(JSON.parse(search),function(key,value){if(!params.hasOwnProperty(key))return params[key]=value}),parts=[],$.each(params,function(key,value){if("action"!==key&&"type"!==key&&"google_id"!==key&&"yandex_id"!==key&&"id"!==key&&"hidden_filter"!==key&&"sort"!==key&&"place"!==key&&"gender"!==key&&value){switch(key){case"priority":switch(parseInt(value)){case 2:value="tutor";break;case 3:value="client";break;case 4:value="maxprice";break;case 5:value="minprice";break;case 6:value="rating";break;default:value="pop"}break;case"subjects":"object"==typeof value&&(value=SubjectService.getSelected(value).join(","))}return parts.push(key+"="+value)}}),parts.join("_")},this.updateCookie=function(params){return void 0===this.cookie&&(this.cookie={}),$.each(params,function(_this){return function(key,value){return _this.cookie[key]=value}}(this)),$.cookie("stream",JSON.stringify(this.cookie),{expires:365,path:"/"})},this.initCookie=function(){return void 0!==$.cookie("stream")?this.cookie=JSON.parse($.cookie("stream")):this.updateCookie({step:0,search:0})},this.run=function(action,type,additional){return null==additional&&(additional={}),void 0===this.cookie&&this.initCookie(),this.initialized?this._run(action,type,additional):$timeout(function(_this){return function(){return _this._run(action,type,additional)}}(this),1e3)},this._run=function(action,type,additional){var params;return null==additional&&(additional={}),this.updateCookie({step:this.cookie.step+1}),params={action:action,type:type,step:this.cookie.step,google_id:googleClientId(),yandex_id:yaCounter1411783?yaCounter1411783.getClientID():"",mobile:"undefined"==typeof isMobile?"0":"1"},$.each(additional,function(_this){return function(key,value){return params[key]=value}}(this)),"view"!==action&&dataLayerPush({event:"configuration",eventCategory:"action="+action+(type?"_type="+type:""),eventAction:this.generateEventString(angular.copy(params))}),Stream.save(params).$promise},this})}.call(this),function(){angular.module("Egerep").service("SubjectService",function(){return this.pairs=[[1,2],[3,4],[6,7],[8,9]],this.init=function(subjects){return this.subjects=subjects},this.pairsControl=function(subject_id){if(subject_id)return angular.forEach(this.subjects,function(_this){return function(enabled,id){var pair;if(pair=_.filter(_this.pairs,function(p){return p.indexOf(parseInt(subject_id))!==-1}),pair.length||pair.push([subject_id]),pair[0].indexOf(parseInt(id))===-1)return _this.subjects[id]=!1}}(this))},this.selected=function(subject_id){return void 0!==this.subjects&&void 0!==this.subjects[subject_id]&&this.subjects[subject_id]},this.select=function(subject_id){return this.subjects[subject_id]=!this.subjects[subject_id]||!this.subjects[subject_id],this.pairsControl(subject_id)},this.getSelected=function(subjects){var ids;return null==subjects&&(subjects=null),ids=[],angular.forEach(subjects||this.subjects,function(enabled,id){if(enabled)return ids.push(id)}),ids},this.opacityControl=function(id){var pair,selected_id;return!!this.getSelected().length&&(selected_id=parseInt(this.getSelected()[0]),pair=_.filter(this.pairs,function(p){return p.indexOf(selected_id)!==-1}),pair.length?pair[0].indexOf(parseInt(id))===-1:selected_id!==id)},this})}.call(this),function(){angular.module("Egerep").directive("ngAge",function(){return{restrict:"A",require:"ngModel",scope:{ngModel:"=",prefix:"@"},link:function(scope,element,attrs,ngModel){var updateModel;return updateModel=function(value){
return ngModel.$setViewValue(value),ngModel.$render()},$(element).on("blur",function(){var age_from,age_to;if($(".age-field").removeClass("has-error"),age_to=scope.$parent.search.age_to,age_from=scope.$parent.search.age_from,age_to&&age_from&&getNumber(age_from)>getNumber(age_to)&&$(".age-field").addClass("has-error"),scope.ngModel)return updateModel(scope.prefix+" "+scope.ngModel+" лет")}).on("keyup",function(){return updateModel(getNumber(scope.ngModel))}).on("focus",function(){return updateModel(getNumber(scope.ngModel))})}}})}.call(this),function(){}.call(this),function(){}.call(this),function(){angular.module("Egerep").directive("errors",function(){return{restrict:"E",templateUrl:"directives/errors",scope:{model:"@"},controller:function($scope,$element,$attrs){return $scope.only_first=$attrs.hasOwnProperty("onlyFirst"),$scope.getErrors=function(){var errors;if(void 0!==$scope.$parent.errors&&(errors=$scope.$parent.errors[$scope.model]))return $scope.only_first?[errors[0]]:errors}}}})}.call(this),function(){angular.module("Egerep").directive("hideLoopedLink",function(){return{restrict:"A",link:function($scope,$element){if(window.location.pathname===$element.attr("href"))return"/"===window.location.pathname?$element.removeAttr("href"):$element.parent().remove()}}})}.call(this),function(){angular.module("Egerep").directive("icheck",function($timeout,$parse){return{require:"ngModel",link:function($scope,element,$attrs,ngModel){return $timeout(function(){var value;return value=void 0,value=$attrs.value,$scope.$watch($attrs.ngModel,function(newValue){$(element).iCheck("update")}),$(element).iCheck({checkboxClass:"custom-checkbox",radioClass:"custom-radio",checkedClass:"checked",cursor:!0}).on("ifChanged",function(event){if("checkbox"===$(element).attr("type")&&$attrs.ngModel&&$scope.$apply(function(){return ngModel.$setViewValue(event.target.checked)}),"radio"===$(element).attr("type")&&$attrs.ngModel)return $scope.$apply(function(){return ngModel.$setViewValue(value)})})})}}})}.call(this),function(){angular.module("Egerep").directive("inView",function(){return{restrict:"A",scope:{tutor:"=tutor",index:"=index"},link:function($scope,$element,$attrs){return $($element).on("inview",function(event,isInView){if(isInView&&$scope.$parent.viewed_tutors.indexOf($scope.tutor.id)===-1)return $scope.$parent.viewed_tutors.push($scope.tutor.id),$scope.$parent.StreamService.run("view",$scope.$parent.StreamService.identifySource($scope.tutor),{tutor_id:$scope.tutor.id,position:$scope.index||null}),$($element).off("inview")})}}})}.call(this),function(){}.call(this),function(){}.call(this),function(){}.call(this),function(){angular.module("Egerep").directive("ngPhone",function(){return{restrict:"A",link:function($scope,element){return $(element).inputmask("+7 (999) 999-99-99",{autoclear:!1,showMaskOnHover:!1})}}})}.call(this),function(){}.call(this),function(){angular.module("Egerep").directive("plural",function(){return{restrict:"E",scope:{count:"=",type:"@",noneText:"@"},templateUrl:"/directives/plural",controller:function($scope,$element,$attrs,$timeout){return $scope.textOnly=$attrs.hasOwnProperty("textOnly"),$scope.hideZero=$attrs.hasOwnProperty("hideZero"),$scope.when={age:["год","года","лет"],month:["месяц","месяца","месяцев"],student:["ученик","ученика","учеников"],minute:["минуту","минуты","минут"],hour:["час","часа","часов"],day:["день","дня","дней"],meeting:["встреча","встречи","встреч"],score:["балл","балла","баллов"],rubbles:["рубль","рубля","рублей"],lesson:["занятие","занятия","занятий"],client:["клиент","клиента","клиентов"],mark:["оценки","оценок","оценок"],marks:["оценка","оценки","оценок"],review:["отзыв","отзыва","отзывов"],request:["заявка","заявки","заявок"],station:["станцию","станции","станций"],tutor:["репетитор","репетитора","репетиторов"],profile:["анкета","анкеты","анкет"],schooler:["школьник нашел","школьника нашли","школьников нашли"],taught:["подготовлен","подготовлено","подготовлено"],address:["адрес","адреса","адресов"]}}}})}.call(this),function(){}.call(this),function(){angular.module("Egerep").directive("requestForm",function(){return{replace:!0,scope:{tutor:"=",sentIds:"=",index:"=",source:"@"},templateUrl:function(elem,attrs){return attrs.hasOwnProperty("mobile")?"directives/request-form-mobile":"directives/request-form"},controller:function($scope,$element,$attrs,$timeout,$rootScope,Request,Sources){var trackDataLayer;return $scope.fixedHeight=$attrs.hasOwnProperty("fixedHeight"),$scope.request=function(){return $scope.tutor||($scope.tutor={}),void 0===$scope.tutor.request&&($scope.tutor.request={}),$scope.tutor.request.tutor_id=$scope.tutor.id,Request.save($scope.tutor.request,function(){return $scope.tutor.request_sent=!0,$scope.$parent.StreamService.run("request",$scope.source||$scope.$parent.StreamService.identifySource($scope.tutor),{position:$scope.index||$scope.$parent.index,tutor_id:$scope.tutor.id}),trackDataLayer()},function(response){return 422===response.status?angular.forEach(response.data,function(errors,field){var selector;return selector="[ng-model$='"+field+"']",$($element).find("input"+selector+", textarea"+selector).focus().notify(errors[0],notify_options)}):$scope.tutor.request_error=!0})},trackDataLayer=function(){return dataLayerPush({event:"purchase",ecommerce:{currencyCode:"RUB",purchase:{actionField:{id:googleClientId(),affiliation:$scope.$parent.StreamService.identifySource(),revenue:$scope.tutor.public_price},products:[{id:$scope.tutor.id,price:$scope.tutor.public_price,brand:$scope.tutor.subjects?$scope.tutor.subjects.join(","):null,category:$scope.tutor.gender+"_"+$scope.tutor.age,quantity:1}]}}})}}}})}.call(this),function(){}.call(this),function(){angular.module("Egerep").directive("subjectList",function(){return{restrict:"E",scope:{subjects:"=",subjectIds:"=","case":"@"},templateUrl:"/directives/subject-list",controller:function($scope,$element,$attrs,$rootScope){return $scope.byId=void 0!==$attrs.byId,void 0===$scope["case"]&&($scope["case"]="dative"),$scope.findById=$rootScope.findById}}})}.call(this),function(){angular.module("Egerep").directive("tutorName",function(){return{restrict:"E",scope:{tutor:"="},templateUrl:"/directives/tutor-name"}})}.call(this),function(){}.call(this),function(){}.call(this),function(){}.call(this),function(){angular.module("Egerep").directive("widgetLoadable",function($q,$timeout){return{restrict:"A",scope:{field:"="},link:function($scope,$element,$attrs){var $toggleBlock,q;return q=$q.defer(),$toggleBlock=$($element).children(".toggle-widget__inner"),$($element).find(".widget-loadable__title").on("click").click(function(){var text;return text=$(this).find("span").text(),q.promise.$$state.status?$(this).find("span").text(text):$(this).find("span").html("<span class='loading loading-inside-widget'>загрузка</span>"),q.promise.then(function(_this){return function(){return $(_this).parent().toggleClass("arrow-active"),$(_this).find("span").text(text),$toggleBlock.stop(),$toggleBlock.slideToggle()}}(this))}),$scope.$watch("field",function(newVal,oldVal){if(void 0!==newVal)return q.resolve(!0)})}}})}.call(this);