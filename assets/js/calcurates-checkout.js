jQuery(document).ready(function () {
    jQuery('body').on('updated_checkout updated_cart_totals', function () {

        jQuery('.calcurates-checkout__shipping-rate-description').each(function () {
            var liElem = jQuery(this).closest('li');
            liElem.addClass('calcurates-checkout__shipping-rate');

            if (jQuery(this).hasClass('calcurates-checkout__shipping-rate-description_has-error')) {
                liElem.addClass('calcurates-checkout__shipping-rate_disabled');
                liElem.find('input').attr('disabled', 'disabled');
            }

            liElem.find('label').addClass('calcurates-checkout__shipping-rate-label');
        });
    });
});
