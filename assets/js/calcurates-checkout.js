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
            const $that = jQuery(this);
            const $liElem = $that.closest('li').addClass('calcurates-checkout__shipping-rate');
            const $input = $liElem.find('input[name^="shipping_method"]');
            const $label = $liElem.find('label').addClass('calcurates-checkout__shipping-rate-label');

            if ($that.hasClass('calcurates-checkout__shipping-rate-description_has-error')) {
                $liElem.addClass('calcurates-checkout__shipping-rate_disabled');
                $input.prop('disabled', true);
            }

            // create layout to wrap input and label with shipping cost
            const $controlLayout = jQuery('<div/>', {
                'class': 'calcurates-checkout__shipping-rate-control-layout'
            }).prependTo($liElem);

            // wrap
            $label.prependTo($controlLayout);
            $input.prependTo($controlLayout);

        });

        // cart option check if available
        const $shippingMethods = $root.find('input[name^="shipping_method"]');
        const $currentMethod = $shippingMethods.filter(':checked');

        if ($currentMethod.prop('disabled')) {
            // remove checked
            $currentMethod.prop('checked', false);

            // check first not disabled
            $shippingMethods.not($currentMethod).first().prop('checked', true).trigger('change');
        }
    }
});
