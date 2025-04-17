jQuery(document).ready(function () {
    // setup
    setupShipping();

    watchForCompanyInputChange();

    jQuery(document.body).on('updated_checkout updated_cart_totals', function () {
        // setup
        setupShipping();
        setupDatePicker();
    });
});

function setupShipping() {
    const $root = jQuery('.woocommerce-shipping-totals');
    const $shippingRateTexts = $root.find('.calcurates-checkout__shipping-rate-text');

    // setup classes
    $shippingRateTexts.each(function () {
        const $that = jQuery(this);
        const $liElem = $that.closest('li').addClass('calcurates-checkout__shipping-rate');
        const $input = $liElem.find('input[name^="shipping_method"]');
        const $datepicker = $liElem.find('.calcurates-checkout__shipping-rate-date-select');
        const $originalUtcDate = $liElem.find('.calcurates-checkout__shipping-rate-date-original-utc');

        if ($that.hasClass('calcurates-checkout__shipping-rate-text_has-error')) {
            $liElem.addClass('calcurates-checkout__shipping-rate_disabled');
            $input.prop('disabled', true);
        }

        // set max-width exclude radio size
        $that.closest('label').css('box-sizing', 'border-box').css('max-width', 'calc(100% - ' + $input.outerWidth() + 'px)');

        if (!$datepicker) {
            return;
        }

        if ($shippingRateTexts.length > 1) {
            $datepicker.prop('disabled', !$input.prop('checked'));
            $originalUtcDate.prop('disabled', !$input.prop('checked'));
        }

        if ($shippingRateTexts.length === 1 || $input.prop('checked')) {
            $datepicker.closest('.calcurates-checkout__shipping-rate-date-select-label').show();
        } else {
            $datepicker.closest('.calcurates-checkout__shipping-rate-date-select-label').hide();
        }
    });

    // cart option check if available
    const $shippingMethods = $root.find('input[name^="shipping_method"]');
    const $currentMethod = $shippingMethods.filter(':checked');

    if ($currentMethod.prop('disabled')) {
        // remove checked
        $currentMethod.prop('checked', false);

        // check first not disabled
        $shippingMethods.not(':disabled').first().prop('checked', true).trigger('change');
    }
}

function watchForCompanyInputChange() {
    let debounce = null;

    jQuery("#billing_company, #shipping_company").on('input', function () {
        clearTimeout(debounce);

        debounce = setTimeout(function () {
            jQuery(document.body).trigger("update_checkout");
        }, 300);
    });
}

// datepicker setup
function setupDatePicker() {
    const timePattern = /\d\d:\d\d:\d\d/;

    jQuery.getScript(CALCURATES_GLOBAL.pluginDir + '/assets/lib/air-datepicker/locale/' + CALCURATES_GLOBAL.lang + '.js', function () {
        jQuery('input[id^="calcurates-datepicker"]').each(function () {
            const $datepicker = jQuery(this);
            const $originalUtcDate = $datepicker.parent().find('.calcurates-checkout__shipping-rate-date-original-utc');

            const timeSlots = cloneFull($datepicker.data('time-slots'));
            if (!timeSlots || timeSlots.length === 0) {
                return;
            }

            const timeSlotDateRequired = $datepicker.data('time-slot-date-required') ? ('1' === $datepicker.data('time-slot-date-required') || 1 === $datepicker.data('time-slot-date-required')) : false;
            const timeSlotTimeRequired = $datepicker.data('time-slot-time-required') ? ('1' === $datepicker.data('time-slot-time-required') || 1 === $datepicker.data('time-slot-time-required')) : false;
            const id = "#" + $datepicker.attr('id');

            // normalize
            timeSlots.forEach(function (item, index) {
                const baseDate = item['date'];

                timeSlots[index]['date'] = new Date(baseDate).toISOString();

                timeSlots[index]['time'].forEach(function (time, timeIndex) {
                    if (time.from) {
                        time.from = new Date(baseDate.replace(timePattern, time.from)).toISOString();
                    }

                    if (time.to) {
                        time.to = new Date(baseDate.replace(timePattern, time.to)).toISOString();
                    }

                    timeSlots[index]['time'][timeIndex] = time;
                })
            });

            const deliveryDateFrom = new Date(new Date(timeSlots[0]['date']).toISOString().replace(timePattern, '00:00:00'));
            const deliveryDateTo = new Date(new Date(timeSlots[timeSlots.length - 1]['date']).toISOString().replace(timePattern, '00:00:00'));

            const options = {
                startDate: deliveryDateFrom,
                locale: DATEPICKER_LANG,
                autoClose: true,
                onSelect(data) {
                    const normalizedDate = normalizeDatepickerDateToZeroUTC(data.date);
                    //find time
                    const result = timeSlots.find(function (item) {
                        $originalUtcDate.val(item['date']);

                        return item['date'].replace(timePattern, '00:00:00') === normalizedDate;
                    });

                    if (result) {
                        createTimeSlotSelect($datepicker, result['time'], timeSlotTimeRequired);
                    } else {
                        removeTimeSelect($datepicker);
                    }
                },
                onRenderCell: function (data) {
                    if (data.cellType === 'day') {
                        const normalizedDate = normalizeDatepickerDateToZeroUTC(data.date);
                        const isDisabled = timeSlots.find(function (item) {
                            return item['date'].replace(timePattern, '00:00:00') === normalizedDate;
                        }) === undefined;

                        return {
                            disabled: isDisabled
                        }
                    }
                },
                dateFormat(date) {
                    const fmt = new DateFormatter();

                    if (!CALCURATES_GLOBAL.dateFormat) {
                        return fmt.formatDate(date, 'F j, Y');
                    }

                    return fmt.formatDate(date, CALCURATES_GLOBAL.dateFormat);
                }
            };

            if (deliveryDateFrom) {
                options['minDate'] = deliveryDateFrom
            }
            if (deliveryDateTo) {
                options['maxDate'] = deliveryDateTo;
            }
            if (timeSlotDateRequired) {
                options['toggleSelected'] = !timeSlotDateRequired;
            }

            const picker = new AirDatepicker(id, options);

            if (timeSlotDateRequired && timeSlots.length > 0) {
                picker.selectDate(new Date(deliveryDateFrom));
            }
        });
    });
}

/**
 * @param {jQuery} $datepicker
 * @param {Array} time
 * @param {boolean} required
 */
function createTimeSlotSelect($datepicker, time, required) {
    removeTimeSelect($datepicker);

    if (time.length === 0) {
        return;
    }
    if ($datepicker.prop('disabled')) {
        return;
    }

    const $select = jQuery('<select class="calcurates-checkout__shipping-rate-time-select" name="selected_delivery_time">').appendTo($datepicker);
    time.forEach(function (item) {
        $select.append(new Option(
            formatToWordpressTime(item['from']) + ' - ' + formatToWordpressTime(item['to']),
            JSON.stringify({from: item['from'], to: item['to']})
        ));
    });

    if (!required) {
        $select.prepend(jQuery('<option selected="selected">Select time slot</option>'));
    }

    $datepicker.closest('.calcurates-checkout__shipping-rate-date-select-label').after(jQuery('<div class="calcurates-checkout__shipping-rate-time-select-label">').append('Delivery time ').append($select));
}

/**
 * @param {jQuery} $elem
 */
function removeTimeSelect($elem) {
    $elem.closest('.calcurates-checkout__shipping-rate-dates').find('.calcurates-checkout__shipping-rate-time-select-label').remove();
}

function cloneFull(obj) {
    return JSON.parse(JSON.stringify(obj));
}

/**
 * @param {Date} date
 * @return {string}
 */
function normalizeDatepickerDateToZeroUTC(date) {
    let day = date.getDate();
    let month = date.getMonth() + 1;
    const year = date.getFullYear();

    if (day < 10) {
        day = '0' + day;
    }

    if (month < 10) {
        month = '0' + month;
    }

    return year + '-' + month + '-' + day + 'T00:00:00.000Z';
}

/**
 * @param {string} dateTime
 * @return {string}
 */
function formatToWordpressTime(dateTime) {
    const localDate = new Date(dateTime);
    const localTime = localDate.getTime();
    const localOffset = localDate.getTimezoneOffset() * 60000;
    const utc = localTime + localOffset;
    const wpTime = utc + (1000 * +CALCURATES_GLOBAL.wpTimeZoneOffsetSeconds);
    const newDate = new Date(wpTime);

    const fmt = new DateFormatter();

    if (!CALCURATES_GLOBAL.timeFormat) {
        return fmt.formatDate(newDate, 'H:i:s');
    }

    return fmt.formatDate(newDate, CALCURATES_GLOBAL.timeFormat);
}
