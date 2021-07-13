define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/place-order',
        'Dintero_Checkout/js/action/set-payment-method',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/customer-data',
        'ko',
        'Magento_Checkout/js/model/full-screen-loader',
        'dinteroSdk',
        'Magento_Checkout/js/model/url-builder',
        'mage/storage'
    ],
    function ($, Component, placeOrderAction, setPaymentMethodAction, additionalValidators, quote, customerData, ko, fullScreenLoader, dintero, urlBuilder, storage) {
        'use strict';

        let dinteroTemplate = window.checkoutConfig.payment.dintero.isEmbedded ? 'Dintero_Checkout/payment/dintero-embedded' : 'Dintero_Checkout/payment/dintero';
        return Component.extend({
            defaults: {
                template: dinteroTemplate
            },
            redirectAfterPlaceOrder: false,
            isVisible: ko.observable(true),
            showButton: ko.observable(true),
            initElement: function() {
                this._super();
                if (window.checkoutConfig.payment.dintero.isEmbedded) {
                    const serviceUrl = urlBuilder.createUrl('/dintero/checkout/session-init', {}),
                        payload = {cartId: quote.getQuoteId()};
                    storage.post(serviceUrl, JSON.stringify(payload), true, 'application/json').success(function(session) {
                        dintero.embed({
                            container: $('#dintero-embedded-checkout-container').get(0),
                            sid: session.id,
                            language: window.checkoutConfig.payment.dintero.language,
                            onPaymentError: function(event, checkout) {
                                alert($.mage.__('Unable to place the order'));
                                checkout.destroy();
                            }
                        });
                    });
                }
                return this;
            },
            getLogoUrl: function() {
                return window.checkoutConfig.payment.dintero.logoUrl;
            },
            continueToDintero: function () {
                if (additionalValidators.validate()) {
                    this.selectPaymentMethod();
                    setPaymentMethodAction(this.messageContainer).done(this.placeOrder);
                    return false;
                }
            },
            placeOrder: function () {
                customerData.invalidate(['cart']);
                $.ajax({
                    url: window.checkoutConfig.payment.dintero.placeOrderUrl,
                    type: 'post',
                    context: this,
                    dataType: 'json',
                    beforeSend: function () {
                        fullScreenLoader.startLoader();
                    },
                    success: function (response) {
                        var preparedData,
                            msg,

                            /**
                             * {Function}
                             */
                            alertActionHandler = function () {
                                // default action
                            };

                        if (response.url) {
                            $.mage.redirect(response.url);
                        } else {
                            fullScreenLoader.stopLoader(true);

                            msg = response['error'];
                            if (typeof msg === 'object') {
                                msg = msg.join('\n');
                            }

                            if (msg) {
                                alert(
                                    {
                                        content: msg,
                                        actions: {

                                            /**
                                             * {Function}
                                             */
                                            always: alertActionHandler
                                        }
                                    }
                                );
                            }
                        }
                    }
                });
            }
        });
    }
);
