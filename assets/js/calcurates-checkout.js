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

            let deliveryDateFrom = null;
            let deliveryDateTo = null;

            // normalize
            timeSlots.forEach(function (item, index) {
                const parsedDate = parseDate(item['date']);
                const baseDate = new Date(); // skip timezone
                baseDate.setFullYear(parsedDate.year, parsedDate.month, parsedDate.date);
                baseDate.setHours(parsedDate.hours, parsedDate.minutes, parsedDate.seconds, 0);

                timeSlots[index]['date'] = formatParsedDateAsIsoDate(parsedDate);

                timeSlots[index]['time'].forEach(function (time, timeIndex) {
                    if (time.from) {
                        time.from = timeSlots[index]['date'].replace(timePattern, time.from);
                    }

                    if (time.to) {
                        time.to = timeSlots[index]['date'].replace(timePattern, time.to);
                    }

                    timeSlots[index]['time'][timeIndex] = time;
                });

                if (0 === index) {
                    deliveryDateFrom = baseDate;
                }
                if ((timeSlots.length - 1) === index) {
                    deliveryDateTo = baseDate;
                }
            });

            const options = {
                minDate: deliveryDateFrom,
                maxDate: deliveryDateTo,
                toggleSelected: !timeSlotDateRequired,
                locale: DATEPICKER_LANG,
                autoClose: true,
                onSelect(data) {
                    if (!data.date) {
                        removeTimeSelect($datepicker);
                        return;
                    }

                    //find time
                    const result = timeSlots.find(function (item) {
                        $originalUtcDate.val(item['date']);

                        return isSameDates(item['date'], data.date);
                    });

                    if (result) {
                        createTimeSlotSelect($datepicker, result['time'], timeSlotTimeRequired);
                    } else {
                        removeTimeSelect($datepicker);
                    }
                },
                onRenderCell: function (data) {
                    if (data.cellType === 'day') {
                        const isDisabled = timeSlots.find(function (item) {
                            return isSameDates(item['date'], data.date);
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

            const picker = new AirDatepicker(id, options);

            if (timeSlotDateRequired) {
                picker.selectDate(new Date(deliveryDateFrom));
            }
        });
    });
}

/**
 * @param {{year: number, month: number, date: number, hours: number, minutes: number, seconds: number}} parsedDate
 * @returns string
 */
function formatParsedDateAsIsoDate(parsedDate) {
    const year = parsedDate.year;
    let month = parsedDate.month + 1;
    let date = parsedDate.date;
    let hours = parsedDate.hours;
    let minutes = parsedDate.minutes;
    let seconds = parsedDate.seconds;

    if (date < 10) {
        date = '0' + date;
    }
    if (month < 10) {
        month = '0' + month;
    }
    if (hours < 10) {
        hours = '0' + hours;
    }
    if (minutes < 10) {
        minutes = '0' + minutes;
    }
    if (seconds < 10) {
        seconds = '0' + seconds;
    }

    return year + '-' + month + '-' + date + 'T' + hours + ':' + minutes + ':' + seconds + '.000Z';
}

/**
 * same dates ignore time and timezone
 * @param {string} dateStr
 * @param {Date} dateObj
 * @returns boolean
 */
function isSameDates(dateStr, dateObj) {
    const obj = parseDate(dateStr);

    return obj.year === dateObj.getFullYear() &&
        obj.month === dateObj.getMonth() &&
        obj.date === dateObj.getDate();
}

/**
 * parse st date, month: 0- 11
 *
 * @param {string} date
 * @returns {{year: number, month: number, date: number, hours: number, minutes: number, seconds: number}}
 */
function parseDate(date) {
    const datetimePattern = /(\d{4})-(\d{2})-(\d{2})[T ](\d{2}):(\d{2}):(\d{2})/;

    const matches = date.match(datetimePattern);
    return {
        year: Number(matches[1]).valueOf(),
        month: Number(matches[2] - 1).valueOf(),
        date: Number(matches[3]).valueOf(),
        hours: Number(matches[4]).valueOf(),
        minutes: Number(matches[5]).valueOf(),
        seconds: Number(matches[6]).valueOf(),
    };
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
 * @param {string} date
 * @return {string}
 */
function formatToWordpressTime(date) {
    const parsedDate = parseDate(date);
    const newDate = new Date();
    newDate.setFullYear(parsedDate.year, parsedDate.month, parsedDate.date);
    newDate.setHours(parsedDate.hours, parsedDate.minutes, parsedDate.seconds, 0);

    const fmt = new DateFormatter();

    if (!CALCURATES_GLOBAL.timeFormat) {
        return fmt.formatDate(newDaate, 'H:i:s');
    }

    return fmt.formatDate(newDate, CALCURATES_GLOBAL.timeFormat);
}
