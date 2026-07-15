const DATEPICKER_INPUT_SELECTOR = 'input[id^="calcurates-datepicker"]';
const TIME_PATTERN = /\d\d:\d\d:\d\d/;

jQuery(document).ready(function () {
    // setup
    setupShipping();
    setupDatePicker();

    watchForCompanyInputChange();

    jQuery(document.body).on('updated_checkout updated_cart_totals', function () {
        // setup
        setupShipping();
        setupDatePicker();
    });

    observeShippingRates();
});

// Some themes/plugins re-render only the order-review table footer (shipping rows)
// without firing WooCommerce's `updated_checkout`/`updated_cart_totals` events. That
// swaps the datepicker input for a fresh, uninitialized node, so `setupDatePicker()`
// never re-runs and the field stops opening. Watch the checkout/cart DOM and
// re-initialize whenever an un-bound datepicker input appears.
function observeShippingRates() {
    if (typeof MutationObserver === 'undefined') {
        return;
    }

    const container = document.querySelector('form.checkout, .woocommerce-cart-form, .woocommerce') || document.body;
    if (!container) {
        return;
    }

    let scheduled = false;
    const observer = new MutationObserver(function () {
        if (scheduled) {
            return;
        }
        scheduled = true;

        setTimeout(function () {
            scheduled = false;

            if (hasUnboundDatepicker()) {
                setupShipping();
                setupDatePicker();
            }
        }, 50);
    });

    observer.observe(container, {childList: true, subtree: true});
}

function hasUnboundDatepicker() {
    return Array.prototype.some.call(
        document.querySelectorAll(DATEPICKER_INPUT_SELECTOR),
        function (el) {
            return !el._calcAirDatepicker;
        }
    );
}

function setupShipping() {
    const $root = jQuery('.woocommerce-shipping-totals');
    const $shippingRateTexts = $root.find('.calcurates-checkout__shipping-rate-text');

    // setup classes
    $shippingRateTexts.each(function () {
        const $that = jQuery(this);
        const $liElem = $that.closest('li').addClass('calcurates-checkout__shipping-rate');
        const $input = $liElem.find('input[name^="shipping_method"]');
        const $datepicker = $liElem.find('.calcurates-checkout__shipping-rate-date-select');
        const $originalDate = $liElem.find('.calcurates-checkout__shipping-rate-date-original');

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
            $originalDate.prop('disabled', !$input.prop('checked'));
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
    if (typeof AirDatepicker === 'undefined') {
        return;
    }

    jQuery(DATEPICKER_INPUT_SELECTOR).each(function () {
        initDatepicker(this);
    });
}

/**
 * Initialize (or re-initialize) the AirDatepicker instance for a single input.
 *
 * @param {HTMLElement} el
 */
function initDatepicker(el) {
    const $datepicker = jQuery(el);

    // WooCommerce replaces the order-review DOM (and re-fires updated_checkout)
    // repeatedly. Destroy any existing instance so the input is never
    // double-initialized, which breaks click handling.
    destroyDatepicker(el);

    const timeSlots = cloneFull($datepicker.data('time-slots'));
    if (!timeSlots || timeSlots.length === 0) {
        return;
    }

    const range = normalizeTimeSlots(timeSlots);
    const options = buildDatepickerOptions($datepicker, timeSlots, range);

    const picker = new AirDatepicker("#" + $datepicker.attr('id'), options);
    el._calcAirDatepicker = picker;

    if (parseBoolData($datepicker.data('time-slot-date-required'))) {
        picker.selectDate(new Date(range.from));
    }
}

/**
 * @param {HTMLElement} el
 */
function destroyDatepicker(el) {
    if (el._calcAirDatepicker) {
        el._calcAirDatepicker.destroy();
        el._calcAirDatepicker = null;
    }
}

/**
 * Normalize time slot dates/times in place to ISO strings and return the
 * available delivery range.
 *
 * @param {Array} timeSlots
 * @returns {{from: (Date|null), to: (Date|null)}}
 */
function normalizeTimeSlots(timeSlots) {
    let from = null;
    let to = null;

    timeSlots.forEach(function (item, index) {
        const parsedDate = parseDate(item['date']);
        const baseDate = new Date(); // skip timezone
        baseDate.setFullYear(parsedDate.year, parsedDate.month, parsedDate.date);
        baseDate.setHours(parsedDate.hours, parsedDate.minutes, parsedDate.seconds, 0);

        item['date'] = formatParsedDateAsIsoDate(parsedDate);

        item['time'].forEach(function (time) {
            if (time.from) {
                time.from = item['date'].replace(TIME_PATTERN, time.from);
            }
            if (time.to) {
                time.to = item['date'].replace(TIME_PATTERN, time.to);
            }
        });

        if (index === 0) {
            from = baseDate;
        }
        if (index === timeSlots.length - 1) {
            to = baseDate;
        }
    });

    return {from: from, to: to};
}

/**
 * @param {jQuery} $datepicker
 * @param {Array} timeSlots
 * @param {{from: (Date|null), to: (Date|null)}} range
 * @returns {Object}
 */
function buildDatepickerOptions($datepicker, timeSlots, range) {
    const $originalDate = $datepicker.parent().find('.calcurates-checkout__shipping-rate-date-original');
    const timeSlotDateRequired = parseBoolData($datepicker.data('time-slot-date-required'));
    const timeSlotTimeRequired = parseBoolData($datepicker.data('time-slot-time-required'));

    return {
        minDate: range.from,
        maxDate: range.to,
        toggleSelected: !timeSlotDateRequired,
        locale: typeof DATEPICKER_LANG !== 'undefined' ? DATEPICKER_LANG : void 0,
        autoClose: true,
        onSelect(data) {
            if (!data.date) {
                removeTimeSelect($datepicker);
                return;
            }

            //find time
            const result = timeSlots.find(function (item) {
                $originalDate.val(item['date']);

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

            return fmt.formatDate(date, CALCURATES_GLOBAL.dateFormat || 'F j, Y');
        }
    };
}

/**
 * Parse a WooCommerce boolean-ish data attribute ("1"/1 => true).
 *
 * @param {*} value
 * @returns boolean
 */
function parseBoolData(value) {
    return value === '1' || value === 1;
}

/**
 * @param {number} value
 * @returns string
 */
function pad2(value) {
    return value < 10 ? '0' + value : '' + value;
}

/**
 * @param {{year: number, month: number, date: number, hours: number, minutes: number, seconds: number}} parsedDate
 * @returns string
 */
function formatParsedDateAsIsoDate(parsedDate) {
    const year = parsedDate.year;
    const month = pad2(parsedDate.month + 1);
    const date = pad2(parsedDate.date);
    const hours = pad2(parsedDate.hours);
    const minutes = pad2(parsedDate.minutes);
    const seconds = pad2(parsedDate.seconds);

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
        return fmt.formatDate(newDate, 'H:i:s');
    }

    return fmt.formatDate(newDate, CALCURATES_GLOBAL.timeFormat);
}
