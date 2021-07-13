define([
    'jquery',
    'Magento_Customer/js/customer-data'
], function ($, customerData) {
    'use strict';

    var _this;

    $.widget('dintero.productAddToCart', {
        options: {
            addToCartForm: '#product_addtocart_form'
        },

        productAdded: false,

        /**
         * Create triggers
         */
        _create: function () {
            _this = this;
            this.setupTriggers();
        },

        setupTriggers: function () {
            this.cart = customerData.get('cart');
            // setup binds for click
            $('.dintero-addToCart').on('click', function () {
                if ($(_this.options.addToCartForm).valid()) {
                    $(_this.options.addToCartForm).submit();
                    _this.productAdded = true;
                }
            });
            $(document).on('ajax:addToCart', _this.checkAndRedirect);
        },
        checkAndRedirect: function (event) {
            if (_this.productAdded) {
                document.location.href = _this.options.checkoutUrl;
            }
        }
    });

    return $.dintero.productAddToCart;
});
