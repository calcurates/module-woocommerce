jQuery(document).ready(function () {
    // setup
    setup_shipping();

    jQuery(document.body).on('updated_checkout updated_cart_totals', function () {
        // setup
        setup_shipping();
    });

    function setup_shipping() {
        const $root = jQuery('.woocommerce-shipping-totals');

        // setup classes
        $root.find('.calcurates-checkout__shipping-rate-description').each(function () {
            let $that = jQuery(this);
            let $liElem = $that.closest('li');
            $liElem.addClass('calcurates-checkout__shipping-rate');

            if ($that.hasClass('calcurates-checkout__shipping-rate-description_has-error')) {
                $liElem.addClass('calcurates-checkout__shipping-rate_disabled');
                $liElem.find('input[name^="shipping_method"]').prop('disabled', true);
            }

            $liElem.find('label').addClass('calcurates-checkout__shipping-rate-label');
        });

        // cart option check if available
        let $shippingMethods = $root.find('input[name^="shipping_method"]');
        let $currentMethod = $shippingMethods.filter(':checked');
        if ($currentMethod.prop('disabled')) {
            // remove checked
            $currentMethod.prop('checked', false);

            // check first not disabled
            $shippingMethods.not($currentMethod).first().prop('checked', true).trigger('change');
        }
    }
});
