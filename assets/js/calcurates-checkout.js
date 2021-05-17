jQuery(document).ready(function () {
    // setup
    setup_shipping();

    jQuery('body').on('updated_checkout updated_cart_totals', function () {

        // setup
        setup_shipping();
    });

    function setup_shipping() {
        // setup classes
        jQuery('.calcurates-checkout__shipping-rate-description').each(function () {
            var that = jQuery(this);
            var liElem = that.closest('li');
            liElem.addClass('calcurates-checkout__shipping-rate');

            if (that.hasClass('calcurates-checkout__shipping-rate-description_has-error')) {
                liElem.addClass('calcurates-checkout__shipping-rate_disabled');
                liElem.find('input').prop('disabled', true);
            }

            liElem.find('label').addClass('calcurates-checkout__shipping-rate-label');
        });

        // cart option check if available
        var current_method = jQuery(':input[name^=shipping_method]:checked');
        if (current_method.is(':disabled')) {
            // remove checked
            current_method.prop('checked', false);

            jQuery('li:not(.calcurates-checkout__shipping-rate_disabled) :input[name^=shipping_method]:first').prop('checked', true).trigger('change');
        }
    }
});
