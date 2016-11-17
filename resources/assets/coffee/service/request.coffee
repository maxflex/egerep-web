angular.module 'Egerep'
    .service 'RequestService', (Request) ->
        # отправить заявку
        this.request = (tutor, element) ->
            tutor.request = {} if tutor.request is undefined
            tutor.request.tutor_id = tutor.id
            Request.save tutor.request, ->
                tutor.request_sent = true
                trackDataLayer()
            , (response) ->
                if response.status is 422
                    angular.forEach response.data, (errors, field) ->
                        selector = "[ng-model$='#{field}']"
                        $(element).find("input#{selector}, textarea#{selector}").focus().notify errors[0], notify_options
                else
                    tutor.request_error = true

        trackDataLayer = (tutor) ->
            window.dataLayer = window.dataLayer || []
            window.dataLayer.push
                event: 'purchase'
                ecommerce:
                    currencyCode: 'RUR'
                    purchase:
                        actionField:
                            id: tutor.id
                            affilaction: 'serp' # @todo: profile|request
                            revenue: tutor.public_price
                        products: [
                            id: tutor.id
                            price: tutor.public_price
                            brand: tutor.subjects
                            category: tutor.markers
                            quantity: 1
                        ]

        this
