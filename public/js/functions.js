//This file contains all functions used in the app.

// Defaults — common.js overwrites these on document.ready from #__precision etc.
var __currency_symbol = '';
var __currency_thousand_separator = ',';
var __currency_decimal_separator = '.';
var __currency_symbol_placement = 'before';
var __currency_precision = 2;
var __quantity_precision = 2;

function __calculate_amount(calculation_type, calculation_amount, amount) {
    var calculation_amount = parseFloat(calculation_amount);
    calculation_amount = isNaN(calculation_amount) ? 0 : calculation_amount;

    var amount = parseFloat(amount);
    amount = isNaN(amount) ? 0 : amount;

    switch (calculation_type) {
        case 'fixed':
            return parseFloat(calculation_amount);
        case 'percentage':
        case 'percent':
                var div = Decimal.div(calculation_amount, 100).toNumber();
            return Decimal.mul(div, amount).toNumber();
        default:
            return 0;
    }
}

//Add specified percentage to the input amount.
function __add_percent(amount, percentage = 0) {
    var amount = parseFloat(amount);
    var percentage = isNaN(percentage) ? 0 : parseFloat(percentage);

    var div = Decimal.div(percentage, 100).toNumber();
    var mul = Decimal.mul(div, amount).toNumber();
    return Decimal.add(amount, mul).toNumber();
}

//Substract specified percentage to the input amount.
function __substract_percent(amount, percentage = 0) {
    var amount = parseFloat(amount);
    var percentage = isNaN(percentage) ? 0 : parseFloat(percentage);

    var div = Decimal.div(percentage, 100).toNumber();
    var mul = Decimal.mul(div, amount).toNumber();
    return Decimal.sub(amount, mul).toNumber();
}

//Returns the principle amount for the calculated amount and percentage
function __get_principle(amount, percentage = 0, minus = false) {
    var amount = parseFloat(amount);
    var percentage = isNaN(percentage) ? 0 : parseFloat(percentage);
    var mul = Decimal.mul(100, amount).toNumber();
    var sum = 1;
    if (minus) {
        sum = Decimal.sub(100, percentage).toNumber();
    } else {
        sum = Decimal.add(100, percentage).toNumber();
    }
    return Decimal.div(mul, sum).toNumber();
}

//Returns the rate at which amount is calculated from principal
function __get_rate(principal, amount) {
    var principal = isNaN(principal) ? 0 : parseFloat(principal);
    var amount = isNaN(amount) ? 0 : parseFloat(amount);
    var interest = Decimal.sub(amount, principal).toNumber();
    var div = Decimal.div(interest, principal).toNumber();
    return Decimal.mul(div, 100).toNumber();
}

function __tab_key_up(e) {
    if (e.keyCode == 9) {
        return true;
    }
}

function __currency_trans_from_en(
    input,
    show_symbol = true,
    use_page_currency = false,
    precision = __currency_precision,
    is_quantity = false
) {
    if (use_page_currency && __p_currency_symbol) {
        var s = __p_currency_symbol;
        var thousand = __p_currency_thousand_separator;
        var decimal = __p_currency_decimal_separator;
    } else {
        var s = __currency_symbol;
        var thousand = __currency_thousand_separator;
        var decimal = __currency_decimal_separator;
    }

    symbol = '';
    var format = '%s%v';
    if (show_symbol) {
        symbol = s;
        format = '%s %v';
        if (__currency_symbol_placement == 'after') {
            format = '%v %s';
        }
    }

    if (is_quantity) {
        precision = __quantity_precision;
    }

    return accounting.formatMoney(input, symbol, precision, thousand, decimal, format);
}

function __currency_convert_recursively(element, use_page_currency = false) {
    element.find('.display_currency').each(function() {
        var value = $(this).data('orig-value') !== undefined ? $(this).data('orig-value') : $(this).text();

        var show_symbol = $(this).data('currency_symbol');
        if (show_symbol == undefined || show_symbol != true) {
            show_symbol = false;
        }

        //If data-use_page_currency is present in the element use_page_currency 
        //value will be over written 
        if (typeof $(this).data('use_page_currency') !== 'undefined') {
            use_page_currency = $(this).data('use_page_currency');
        }

        var highlight = $(this).data('highlight');
        if (highlight == true) {
            __highlight(value, $(this));
        }

        var is_quantity = $(this).data('is_quantity');
        if (is_quantity == undefined || is_quantity != true) {
            is_quantity = false;
        }

        if (is_quantity) {
            show_symbol = false;
        }

        $(this).text(__currency_trans_from_en(value, show_symbol, use_page_currency, __currency_precision, is_quantity));
        $(this).addClass('currency-converted');
    });
}

function __translate(str, obj = []) {
    var trans = LANG[str];
    $.each(obj, function(key, value) {
        trans = trans.replace(':' + key, value);
    });
    if (trans) {
        return trans;
    } else {
        return str;
    }
}

//If the value is positive, text-success class will be applied else text-danger
function __highlight(value, obj) {
    obj.removeClass('text-success').removeClass('text-danger');
    if (value > 0) {
        obj.addClass('text-success');
    } else if (value < 0) {
        obj.addClass('text-danger');
    }
}

//Unformats the currency/number
function __number_uf(input, use_page_currency = false) {
    if (use_page_currency && __currency_decimal_separator) {
        var decimal = __p_currency_decimal_separator;
    } else {
        var decimal = __currency_decimal_separator;
    }

    return accounting.unformat(input, decimal);
}

//Alias of currency format, formats number
function __number_f(
    input,
    show_symbol = false,
    use_page_currency = false,
    precision = __currency_precision
) {
    return __currency_trans_from_en(input, show_symbol, use_page_currency, precision);
}

//Read input and convert it into natural number
function __read_number(input_element, use_page_currency = false) {
    return __number_uf(input_element.val(), use_page_currency);
}

//Write input by converting to formatted number
function __write_number(
    input_element,
    value,
    use_page_currency = false,
    precision = __currency_precision
) {
    if(input_element.hasClass('input_quantity')) {
        precision = __quantity_precision;
    }

    input_element.val(__number_f(value, false, use_page_currency, precision));
}

//Return the font-awesome html based on class value
function __fa_awesome($class = 'fa-sync fa-spin fa-fw ') {
    return '<i class="fa ' + $class + '"></i>';
}

//Converts standard dates (YYYY-MM-DD) to human readable dates
function __show_date_diff_for_human(element) {
    moment.locale(app_locale);
    element.find('.time-to-now').each(function() {
        var string = $(this).text();
        $(this).text(moment(string).toNow(true));
    });

    element.find('.time-from-now').each(function() {
        var string = $(this).text();
        $(this).text(moment(string).from(moment()));
    });
}

//Rounds a number to Iraqi dinnar
function round_to_iraqi_dinnar(value) {
    //Adjsustment
    var remaining = value % 250;
    if (remaining >= 125) {
        value += 250 - remaining;
    } else {
        value -= remaining;
    }

    return value;
}

function __select2(selector) {
    if ($('html').attr('dir') == 'rtl') selector.select2({ dir: 'rtl' });
    else selector.select2();
}

function update_font_size() {
    var font_size = localStorage.getItem('upos_font_size');
    var font_size_array = [];
    font_size_array['s'] = ' - 3px';
    font_size_array['m'] = '';
    font_size_array['l'] = ' + 3px';
    font_size_array['xl'] = ' + 6px';
    if (typeof font_size !== 'undefined') {
        $('header').css('font-size', 'calc(100% ' + font_size_array[font_size] + ')');
        $('footer').css('font-size', 'calc(100% ' + font_size_array[font_size] + ')');
        $('section').each(function() {
            if (!$(this).hasClass('print_section')) {
                $(this).css('font-size', 'calc(100% ' + font_size_array[font_size] + ')');
            }
        });
        $('div.modal').css('font-size', 'calc(100% ' + font_size_array[font_size] + ')');
    }
}

function sum_table_col(table, class_name) {
    var sum = 0;
    table
        .find('tbody')
        .find('tr')
        .each(function() {
            if (
                parseFloat(
                    $(this)
                        .find('.' + class_name)
                        .data('orig-value')
                )
            ) {
                sum += parseFloat(
                    $(this)
                        .find('.' + class_name)
                        .data('orig-value')
                );
            }
        });

    return sum;
}

function __count_status(data, key) {
    var statuses = [];
    for (var r in data){
        var element = $(data[r][key]);
        if (element.data('orig-value')) {
            var status_name = element.data('orig-value');
            if (!(status_name in statuses)) {
                statuses[status_name] = [];
                statuses[status_name]['count'] = 1;
                statuses[status_name]['display_name'] = element.data('status-name');
            } else {
                statuses[status_name]['count'] += 1;
            }
        }
    }

    //generate html
    var html = '<p class="text-left"><small>';
    for (var key in statuses) {
        html +=
            statuses[key]['display_name'] + ' - ' + statuses[key]['count'] + '</br>';
    }

    html += '</small></p>';

    return html;
}

function __sum_status(table, class_name) {
    var statuses = [];
    var status_html = [];
    table
        .find('tbody')
        .find('tr')
        .each(function() {
            element = $(this).find('.' + class_name);
            if (element.data('orig-value')) {
                var status_name = element.data('orig-value');
                if (!(status_name in statuses)) {
                    statuses[status_name] = [];
                    statuses[status_name]['count'] = 1;
                    statuses[status_name]['display_name'] = element.data('status-name');
                } else {
                    statuses[status_name]['count'] += 1;
                }
            }
        });

    return statuses;
}

function __sum_status_html(table, class_name) {
    var statuses_sum = __sum_status(table, class_name);
    var status_html = '<p class="text-left"><small>';
    for (var key in statuses_sum) {
        status_html +=
            statuses_sum[key]['display_name'] + ' - ' + statuses_sum[key]['count'] + '</br>';
    }

    status_html += '</small></p>';

    return status_html;
}

function __sum_stock(table, class_name, label_direction = 'right') {
    var stocks = [];
    table
        .find('tbody')
        .find('tr')
        .each(function() {
            element = $(this).find('.' + class_name);
            if (element.data('orig-value')) {
                var unit_name = element.data('unit');
                if (!(unit_name in stocks)) {
                    stocks[unit_name] = parseFloat(element.data('orig-value'));
                } else {
                    stocks[unit_name] += parseFloat(element.data('orig-value'));
                }
            }
        });
    var stock_html = '<p class="text-left"><small>';

    for (var key in stocks) {
        if (label_direction == 'left') {
            stock_html +=
                key +
                ' : <span class="display_currency" data-is_quantity="true">' +
                stocks[key] +
                '</span> ' +
                '</br>';
        } else {
            stock_html +=
                '<span class="display_currency" data-is_quantity="true">' +
                stocks[key] +
                '</span> ' +
                key +
                '</br>';
        }
    }

    stock_html += '</small></p>';

    return stock_html;
}

/**
 * Print invoice (HTML multipage — reliable content; PDF path left blank white in Chrome).
 * pdf_url kept for callers; optional use via __print_invoice_pdf only.
 */
function __print_receipt(section_id, pdf_url) {
    // Always multipage HTML so invoice content shows. 0×0 PDF iframe was blank preview.
    __print_receipt_html(section_id || 'receipt_section');
}

/**
 * Optional: fetch invoice PDF and print (letter size frame required — 0×0 prints white).
 */
function __print_invoice_pdf(pdf_url) {
    if (!pdf_url) {
        __print_receipt_html('receipt_section');
        return;
    }
    var prevTitle = document.title;
    document.title = '\u200B';

    fetch(pdf_url, {
        method: 'GET',
        credentials: 'same-origin',
        headers: { Accept: 'application/pdf' },
    })
        .then(function (res) {
            if (!res.ok) {
                throw new Error('pdf ' + res.status);
            }
            return res.blob();
        })
        .then(function (blob) {
            var type = (blob.type || '').toLowerCase();
            // Empty / HTML error / too-small PDF → HTML multipage
            if ((type && type.indexOf('pdf') === -1) || !blob.size || blob.size < 800) {
                throw new Error('not pdf');
            }
            var url = URL.createObjectURL(blob);
            var iframe = document.getElementById('__pos_pdf_print_frame');
            if (!iframe) {
                iframe = document.createElement('iframe');
                iframe.id = '__pos_pdf_print_frame';
                iframe.setAttribute('aria-hidden', 'true');
                document.body.appendChild(iframe);
            }
            // Non-zero size required — width/height 0 yields blank white print in Chrome
            iframe.setAttribute(
                'style',
                'position:fixed;left:-10000px;top:0;width:8.5in;height:11in;border:0;' +
                    'opacity:0;pointer-events:none;z-index:-1;'
            );

            var printed = false;
            var cleanup = function () {
                try {
                    URL.revokeObjectURL(url);
                } catch (e) {}
                document.title = prevTitle;
            };

            var doPrint = function () {
                if (printed) {
                    return;
                }
                printed = true;
                setTimeout(function () {
                    try {
                        iframe.contentWindow.focus();
                        iframe.contentWindow.print();
                        setTimeout(cleanup, 3000);
                    } catch (err) {
                        cleanup();
                        __print_receipt_html('receipt_section');
                    }
                }, 500);
            };

            iframe.onload = doPrint;
            iframe.src = url;
            // Blob PDF viewers sometimes skip onload
            setTimeout(doPrint, 1400);
        })
        .catch(function () {
            document.title = prevTitle;
            __print_receipt_html('receipt_section');
        });
}

/**
 * HTML multipage print (legacy fallback when PDF unavailable).
 * Keep the browser URL on the POS page (e.g. /pos/create) — never navigate or replaceState to "/".
 */
function __print_receipt_html(section_id) {
    var section = section_id ? document.getElementById(section_id) : null;
    var prevTitle = document.title;
    var restored = false;

    var restore = function () {
        if (restored) {
            return;
        }
        restored = true;
        try {
            document.title = prevTitle;
            document.body.classList.remove('pos-is-printing');
        } catch (e) {}
        window.removeEventListener('afterprint', restore);
        window.removeEventListener('focus', restore);
    };
    window.addEventListener('afterprint', restore);
    // Chrome print preview can leave path unsettled; ensure UI restores on focus/cancel
    window.addEventListener('focus', restore);

    // Blank title only for print header — do not change location/history
    document.title = '\u200B';
    document.body.classList.add('pos-is-printing');

    var printParent = function () {
        setTimeout(function () {
            try {
                window.focus();
                window.print();
            } catch (e) {}
            setTimeout(restore, 2500);
        }, 200);
    };

    if (!section || !section.innerHTML) {
        printParent();
        return;
    }

    var iframe = document.getElementById('__pos_print_frame');
    if (!iframe) {
        iframe = document.createElement('iframe');
        iframe.id = '__pos_print_frame';
        iframe.setAttribute('aria-hidden', 'true');
        document.body.appendChild(iframe);
    }
    iframe.setAttribute(
        'style',
        'position:fixed;left:0;top:0;width:8.5in;height:1200px;border:0;' +
        'opacity:0;pointer-events:none;z-index:-1;'
    );

    var content = section.innerHTML;
    var invoicePageCss = (content.indexOf('qb-letterhead') !== -1)
        ? '@page{size:letter portrait;margin:0.32in 0.4in 0.42in 0.4in;}'
        : '@page{size:letter portrait;margin:0;}';
    var base = '';
    try {
        base = document.getElementsByTagName('base')[0]
            ? document.getElementsByTagName('base')[0].href
            : (window.location.origin + '/');
    } catch (e2) {
        base = window.location.origin + '/';
    }

    var multipageCss =
        invoicePageCss +
        'html,body{margin:0!important;padding:0!important;background:#fff!important;' +
        'height:auto!important;min-height:0!important;max-height:none!important;' +
        'overflow:visible!important;}' +
        'body{-webkit-print-color-adjust:exact;print-color-adjust:exact;}' +
        '*{box-sizing:border-box;}' +
        'img{max-width:100%;}' +
        'table{page-break-inside:auto;border-collapse:collapse;}' +
        'thead{display:table-header-group!important;}' +
        'tfoot{display:table-footer-group!important;}' +
        'tr{page-break-inside:avoid;break-inside:avoid;page-break-after:auto;}' +
        '.usl-wrap{display:block!important;height:auto!important;min-height:0!important;' +
        'max-height:none!important;overflow:visible!important;width:100%!important;' +
        'max-width:none!important;padding:0!important;margin:0!important;}' +
        '.usl-items{page-break-inside:auto!important;break-inside:auto!important;width:100%;}' +
        '.usl-items thead{display:table-header-group!important;}' +
        '.qb-wrap{display:block!important;height:auto!important;min-height:0!important;' +
        'max-height:none!important;overflow:visible!important;width:100%!important;' +
        'max-width:none!important;padding:0!important;margin:0!important;}' +
        '.qb-items-sheet{display:block!important;min-height:0!important;height:auto!important;}' +
        '.qb-items-fill{display:none!important;}' +
        '.qb-items{page-break-inside:auto!important;break-inside:auto!important;width:100%;}' +
        '.qb-items thead{display:table-header-group!important;}' +
        '.qb-items tbody{display:table-row-group!important;}' +
        '.qb-items tbody tr{page-break-inside:avoid!important;break-inside:avoid!important;}' +
        '.qb-letterhead{page-break-inside:avoid!important;break-inside:avoid!important;}' +
        '.qb-sheet{position:relative;width:100%;height:9.9in;box-sizing:border-box;' +
        'page-break-after:always;break-after:page;}' +
        '.qb-sheet:last-child{page-break-after:auto;break-after:auto;}' +
        '.qb-sheet-footer{position:absolute;left:0;right:0;bottom:0;text-align:center;' +
        'font-family:Arial,Helvetica,sans-serif;font-size:10px;line-height:1.2;color:#000;}' +
        '.usl-items tbody{display:table-row-group!important;}' +
        '.usl-items tbody tr{page-break-inside:avoid!important;break-inside:avoid!important;}' +
        '.usl-endblock{page-break-inside:auto!important;break-inside:auto!important;}' +
        '.usl-lower,.usl-tots{page-break-inside:avoid!important;break-inside:avoid!important;}' +
        '.usl-ship-tbl{border-collapse:collapse;width:auto;max-width:360px;border:0!important;}' +
        '.usl-ship-tbl td{padding:4px 8px;font-size:11px;border:0!important;}' +
        '.usl-ship-tbl td.k{background:#e8e8e8!important;font-weight:700;white-space:nowrap;' +
        '-webkit-print-color-adjust:exact;print-color-adjust:exact;}' +
        '.usl-ship-tbl td.v{background:transparent!important;font-weight:400;}' +
        '.usl-pay-block{margin-top:12px;margin-bottom:6px;font-size:11.5px;}' +
        '.usl-pay{width:auto!important;table-layout:auto;}' +
        '.usl-pay td{padding:2px 14px 2px 0;white-space:nowrap;width:auto;}' +
        '.usl-pay tr.usl-pay-return td,.usl-pay td.usl-pay-return-amt{color:#c62828;font-weight:600;}';

    var htmlDoc =
        '<!DOCTYPE html><html><head><meta charset="utf-8">' +
        '<base href="' + base.replace(/"/g, '') + '">' +
        '<title></title>' +
        '<style type="text/css">' + multipageCss + '</style>' +
        '</head><body class="pos-print-doc">' + content + '</body></html>';

    var idoc = iframe.contentDocument || iframe.contentWindow.document;
    idoc.open();
    idoc.write(htmlDoc);
    idoc.close();
    try {
        idoc.title = '';
    } catch (e3) {}

    var expandFrame = function () {
        try {
            var h = Math.max(
                idoc.body.scrollHeight || 0,
                idoc.documentElement.scrollHeight || 0,
                1100
            );
            iframe.style.height = Math.min(Math.max(h + 40, 1100), 50000) + 'px';
        } catch (e4) {}
    };

    var triggerIframePrint = function () {
        expandFrame();
        setTimeout(function () {
            expandFrame();
            try {
                if (iframe.contentWindow && iframe.contentWindow.__qbPaginateInvoice) {
                    iframe.contentWindow.__qbPaginateInvoice();
                }
            } catch (ePag) {}
            try {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
            } catch (err) {
                printParent();
                return;
            }
            setTimeout(restore, 2500);
        }, 100);
    };

    var imgs = idoc.images ? Array.prototype.slice.call(idoc.images) : [];
    if (!imgs.length) {
        setTimeout(triggerIframePrint, 250);
        return;
    }
    var left = imgs.length;
    var one = function () {
        left -= 1;
        if (left <= 0) {
            setTimeout(triggerIframePrint, 150);
        }
    };
    imgs.forEach(function (img) {
        if (img.complete) {
            one();
        } else {
            img.addEventListener('load', one, { once: true });
            img.addEventListener('error', one, { once: true });
        }
    });
    setTimeout(function () {
        if (left > 0) {
            left = 0;
            triggerIframePrint();
        }
    }, 2500);
}

function __getUnitMultiplier(row){
    multiplier = row.find('select.sub_unit').find(':selected').data('multiplier');
    if(multiplier == undefined){
        return 1;
    } else {
        return parseFloat(multiplier);
    }
}

//Rounds a number to the nearest given multiple
function __round(number, multiple = 0){

    rounded_number = number;
    if(multiple > 0) {
        // Whole number: always round UP (35.20 → 36). Decimals: nearest multiple.
        if (Number(multiple) === 1) {
            var n = parseFloat(number) || 0;
            rounded_number = (Math.abs(n - Math.round(n)) < 0.0000001)
                ? Math.round(n)
                : Math.ceil(n - 0.0000001);
        } else {
            x = new Decimal(number)
            rounded_number = x.toNearest(multiple);
        }
    }

    var output = {
        number: rounded_number,
        diff: rounded_number - number
    }
    
    return output;
}

//This method removes unwanted get parameter from the data.
function __datatable_ajax_callback(data){
    for (var i = 0, len = data.columns.length; i < len; i++) {
        if (! data.columns[i].search.value) delete data.columns[i].search;
        if (data.columns[i].searchable === true) delete data.columns[i].searchable;
        if (data.columns[i].orderable === true) delete data.columns[i].orderable;
        if (data.columns[i].data === data.columns[i].name) delete data.columns[i].name;
    }
    delete data.search.regex;

    return data;
}

//Confirmation before page load.
function __page_leave_confirmation(form) {
    var form_obj = $(form);
    var orig_form_data = form_obj.serialize();

    setTimeout(function(){ orig_form_data = form_obj.serialize(); }, 1000);
    
    $(document).on("submit", "form", function(event){
        window.onbeforeunload = null;
    });
    window.onbeforeunload = function() {
        if (form_obj.serialize() != orig_form_data) {
            return LANG.sure;
        }
    }
}

//initialize tinyMCE editor for invoice template
function init_tinymce(editor_id) {
    if (typeof tinymce === 'undefined' || !tinymce || typeof tinymce.init !== 'function') {
        return;
    }
    tinymce.init({
        selector: 'textarea#' + editor_id,
        plugins: [
        'advlist autolink link image lists charmap print preview hr anchor pagebreak',
        'searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking',
        'table template paste help'
        ],
        toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify |' +
          ' bullist numlist outdent indent | link image | print preview fullpage | ' +
          'forecolor backcolor',
        menu: {
          favs: {title: 'My Favorites', items: 'code | searchreplace'}
        },
        menubar: 'favs file edit view insert format tools table help'
    });
}

function getSelectedRows() {
    var selected_rows = [];
    var i = 0;
    $('.row-select:checked').each(function () {
        selected_rows[i++] = $(this).val();
    });

    return selected_rows; 
}

function __is_online() {
    return true;
    
    //if localhost always return true
    if ($('#__is_localhost').length > 0) {
        return true;
    }

    return window.navigator.onLine;
}

function __disable_submit_button(element) {
    if (__is_online()) {
        element.attr('disable', true);
    }
}

function __current_datetime() {
    return moment().format(moment_date_format + ' ' + moment_time_format);
}

//Converts formatted number string to float (similar to PHP num_uf function)
function __num_uf(input_number, use_page_currency = false) {
    var thousand_separator = '';
    var decimal_separator = '';

    if (use_page_currency && __p_currency_thousand_separator) {
        thousand_separator = __p_currency_thousand_separator;
        decimal_separator = __p_currency_decimal_separator;
    } else {
        thousand_separator = __currency_thousand_separator;
        decimal_separator = __currency_decimal_separator;
    }

    // Remove thousand separators
    var num = String(input_number || '0').replace(new RegExp('\\' + thousand_separator, 'g'), '');
    // Replace decimal separator with dot
    num = num.replace(decimal_separator, '.');

    return parseFloat(num) || 0;
}