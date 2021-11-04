jQuery(document).ready(function() {
    // setup
    setup_shipping();

    jQuery(document.body).on('updated_checkout updated_cart_totals', function() {
        // setup
        setup_shipping();
    });

    function setup_shipping() {
        const $root = jQuery('.woocommerce-shipping-totals');

        // setup classes
        $root.find('.calcurates-checkout__shipping-rate-text').each(function() {
            const $that = jQuery(this);
            const $liElem = $that.closest('li').addClass('calcurates-checkout__shipping-rate');
            const $input = $liElem.find('input[name^="shipping_method"]');

            if ($that.hasClass('calcurates-checkout__shipping-rate-text_has-error')) {
                $liElem.addClass('calcurates-checkout__shipping-rate_disabled');
                $input.prop('disabled', true);
            }

            // set max-width exclude radio size
            $that.closest('label').css('box-sizing', 'border-box').css('max-width', 'calc(100% - ' + $input.outerWidth() + 'px)')

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