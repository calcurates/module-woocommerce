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

    // setup classes
    $root.find('.calcurates-checkout__shipping-rate-text').each(function () {
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

        $datepicker.prop('disabled', !$input.prop('checked'));
        $originalUtcDate.prop('disabled', !$input.prop('checked'));

        if ($input.prop('checked')) {
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

    jQuery.getScript(CALCURATES_GLOBAL.pluginDir + "/assets/lib/air-datepicker/locale/" + CALCURATES_GLOBAL.lang + ".js", function () {
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

            const deliveryDatFrom = new Date(new Date(timeSlots[0]['date']).toISOString().replace(timePattern, '00:00:00'));
            const deliveryDatTo = new Date(new Date(timeSlots[timeSlots.length - 1]['date']).toISOString().replace(timePattern, '00:00:00'));

            const options = {
                locale: exports.default,
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
                }
            };

            if (deliveryDatFrom) {
                options['minDate'] = deliveryDatFrom
            }
            if (deliveryDatTo) {
                options['maxDate'] = deliveryDatTo;
            }
            if (timeSlotDateRequired) {
                options['toggleSelected'] = !timeSlotDateRequired;
            }

            const picker = new AirDatepicker(id, options);

            if (timeSlotDateRequired && timeSlots.length > 0) {
                picker.selectDate(new Date(deliveryDatFrom));
            }
        });
    });
}

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
        const fromLocalDate = new Date(item['from']);
        const toLocalDate = new Date(item['to']);

        $select.append(new Option(
            convertSlotsToWpTime(fromLocalDate) + ' - ' + convertSlotsToWpTime(toLocalDate),
            JSON.stringify({from: item['from'], to: item['to']})
        ));
    });

    if (!required) {
        $select.prepend(jQuery('<option selected="selected">Select time slot</option>'));
    }

    $datepicker.closest('.calcurates-checkout__shipping-rate-date-select-label').after(jQuery('<div class="calcurates-checkout__shipping-rate-time-select-label">').append('Delivery time ').append($select));
}

function removeTimeSelect($elem) {
    $elem.closest('.calcurates-checkout__shipping-rate-dates').find('.calcurates-checkout__shipping-rate-time-select-label').remove();
}

function cloneFull(obj) {
    return JSON.parse(JSON.stringify(obj));
}

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

function convertSlotsToWpTime(date) {
    const localTime = date.getTime();
    const localOffset = date.getTimezoneOffset() * 60000;
    const utc = localTime + localOffset;
    const wpTime = utc + (1000* +CALCURATES_GLOBAL.wpTimeZoneOffsetSeconds);
    const newDate = new Date(wpTime);

    return newDate.toLocaleTimeString().slice(0, -3);
}
