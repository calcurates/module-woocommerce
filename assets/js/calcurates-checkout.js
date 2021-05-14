jQuery(document).ready(function () {
    jQuery('body').on('updated_checkout updated_cart_totals', function () {

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
    });
});
