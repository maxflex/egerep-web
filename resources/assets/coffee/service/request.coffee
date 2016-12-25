angular.module 'Egerep'
    .service 'RequestService', (Request, Sources, $rootScope) ->
        # отправить заявку
        this.request = (tutor, element, index = null, StreamService) ->
            tutor.request = {} if tutor.request is undefined
            tutor.request.tutor_id = tutor.id
            Request.save tutor.request, ->
                tutor.request_sent = true
                StreamService.run(identifySource(tutor, index), index)
                trackDataLayer(tutor)
            , (response) ->
                if response.status is 422
                    angular.forEach response.data, (errors, field) ->
                        selector = "[ng-model$='#{field}']"
                        $(element).find("input#{selector}, textarea#{selector}").focus().notify errors[0], notify_options
                else
                    tutor.request_error = true

        identifySource = (tutor, index) ->
            if tutor.id
                if index then Sources.SERP_REQUEST else Sources.PROFILE_REQUEST
            else
                Sources.HELP_REQUEST

        trackDataLayer = (tutor) ->
            window.dataLayer = window.dataLayer || []
            window.dataLayer.push
                event: 'purchase'
                ecommerce:
                    currencyCode: 'RUR'
                    purchase:
                        actionField:
                            id: googleClientId()
                            affiliation: 'serp' # @todo: profile|request
                            revenue: tutor.public_price
                        products: [
                            id: tutor.id
                            price: tutor.public_price
                            brand: tutor.subjects
                            category: tutor.gender + '_' + $rootScope.yearsPassed(tutor.birth_year) # пол_возраст
                            quantity: 1
                        ]

        this
