//This file contains all common functionality for the application

// Make required-field asterisks (*) red on all create/edit forms and modals
window.highlightRequiredAsterisks = function (context) {
    var $root = context ? $(context) : $(document);
    $root.find('label, .control-label, th, legend, .box-title, h1, h2, h3, h4').each(function () {
        var $el = $(this);
        if ($el.data('req-star-done')) {
            return;
        }
        var html = $el.html();
        if (!html || html.indexOf('*') === -1 || html.indexOf('required-asterisk') !== -1) {
            if (html && html.indexOf('required-asterisk') !== -1) {
                $el.data('req-star-done', 1);
            }
            return;
        }
        // Only wrap plain * used as required markers (e.g. "Name:*" / "Name *")
        var updated = html
            .replace(/:(\s*)\*(?![^<]*>)/g, ':$1<span class="required-asterisk">*</span>')
            .replace(/(\S)\s+\*(?=\s*<|$)/g, '$1 <span class="required-asterisk">*</span>');
        if (updated !== html) {
            $el.html(updated);
            $el.data('req-star-done', 1);
        }
    });
};

// Bootstrap 3 wasn't built for stacked/nested modals — it shares state across ALL modal
// instances instead of scoping it per-instance. The fixes below patch that, but ONLY for
// the specific modal pairs in this app that are actually opened stacked (JapsAI's two
// scan modals + the quick-add-product popup they can open on top of themselves, and the
// contact modal with the same exposure). Do NOT widen this to a bare '.modal' selector —
// that previously also touched single, non-stacked modals elsewhere (e.g. POS's payment
// modal) and could leave a backdrop mis-stacked above a modal that was never nested to
// begin with, making it look open but unclickable.
var STACKED_MODALS_SELECTOR = '#ai_invoice_modal, #ai_product_scan_modal, .quick_add_product_modal, .contact_modal, #global_contact_modal';

// Closing ANY of the above unconditionally tears down state shared across all modal
// instances — the `modal-open` class on <body>, the document-level focus-trap handler,
// and the window resize handler that keeps a modal's layout in sync. None of that gets
// restored for a modal from this set left open behind it, which is what breaks its
// scrolling. Put it back.
$(document).on('hidden.bs.modal', STACKED_MODALS_SELECTOR, function () {
    var $stillOpen = $(STACKED_MODALS_SELECTOR).filter('.in');
    if (!$stillOpen.length) {
        return;
    }
    $('body').addClass('modal-open');
    $stillOpen.each(function () {
        var modal = $(this).data('bs.modal');
        if (modal && modal.isShown) {
            if (typeof modal.enforceFocus === 'function') {
                modal.enforceFocus();
            }
            if (typeof modal.resize === 'function') {
                modal.resize();
            }
        }
    });
});

// These modals can be opened stacked (e.g. JapsAI's scan modal with "Add new product"
// opened on top of it for a missing item) and need the later-opened one to paint above
// the earlier one. Admin theme pins `.modal` at z-index 1100; POS theme uses ~10600.
// Assign a z-index based on how many of these are already open when one shows, so open
// order decides the stack; `setProperty(..., 'important')` is required to outrank the
// theme's `!important` rule. Scoped to this specific set so it can't touch any other
// modal's normal (non-stacked) z-index.
$(document).on('show.bs.modal', STACKED_MODALS_SELECTOR, function () {
    var depth = $(STACKED_MODALS_SELECTOR).filter('.in').length;
    var base = document.body.classList.contains('pos-sale-theme') ? 10600 : 1100;
    this.style.setProperty('z-index', base + depth * 20, 'important');
});
$(document).on('shown.bs.modal', STACKED_MODALS_SELECTOR, function () {
    var base = document.body.classList.contains('pos-sale-theme') ? 10600 : 1100;
    var zIndex = parseInt(this.style.zIndex, 10) || base;
    var backdrops = document.querySelectorAll('.modal-backdrop');
    var backdrop = backdrops.length ? backdrops[backdrops.length - 1] : null;
    if (backdrop) {
        backdrop.style.setProperty('z-index', String(zIndex - 1), 'important');
    }
});

$(document).on('submit', 'form', function (e) {
    if (!__is_online()) {
        e.preventDefault();
        toastr.error(LANG.not_connected_to_a_network);
        return false;
    }

    $(this).find('button[type="submit"]').attr('disabled', true);
});
$(document).ready(function () {
    window.addEventListener('online', updateOnlineStatus);
    window.addEventListener('offline', updateOnlineStatus);

    // Red * for required fields on all pages
    if (typeof window.highlightRequiredAsterisks === 'function') {
        window.highlightRequiredAsterisks();
    }
    $(document).on('shown.bs.modal', '.modal', function () {
        window.highlightRequiredAsterisks(this);
    });
    $(document).ajaxComplete(function () {
        window.highlightRequiredAsterisks();
    });

    $.ajaxSetup({
        beforeSend: function (jqXHR, settings) {
            if (!__is_online()) {
                toastr.error(LANG.not_connected_to_a_network);
                return false;
            }
            if (settings.url.indexOf('http') === -1) {
                settings.url = base_path + settings.url;
            }
        },
    });

    update_font_size();

    // Must be set before the first toast: the container keeps the position it was created with
    toastr.options.preventDuplicates = true;
    toastr.options.positionClass = 'toast-top-center';
    toastr.options.timeOut = '3000';
    toastr.options.extendedTimeOut = '800';
    toastr.options.closeButton = true;
    toastr.options.newestOnTop = true;

    if ($('#status_span').length) {
        var status = $('#status_span').attr('data-status');
        var status_msg = $('#status_span').attr('data-msg');
        // Deferred until the page's own ready handlers (DataTables etc.) finish; otherwise the
        // main thread is blocked while the toast's timer runs and it vanishes before it's painted
        setTimeout(function () {
            if (status === '1') {
                toastr.success(status_msg, '', { timeOut: 4000 });
            } else if (status == '' || status === '0') {
                toastr.error(status_msg, '', { timeOut: 5000 });
            }
        }, 300);
    }

    //Default setting for select2
    $.fn.select2.defaults.set('minimumResultsForSearch', 6);
    if ($('html').attr('dir') == 'rtl') {
        $.fn.select2.defaults.set('dir', 'rtl');
    }
    $.fn.datepicker.defaults.todayHighlight = true;
    $.fn.datepicker.defaults.autoclose = true;
    $.fn.datepicker.defaults.format = datepicker_date_format;

    //Play notification sound on success, error and warning
    toastr.options.onShown = function () {
        if ($(this).hasClass('toast-success')) {
            var audio = $('#success-audio')[0];
            if (audio !== undefined) {
                audio.play();
            }
        } else if ($(this).hasClass('toast-error')) {
            var audio = $('#error-audio')[0];
            if (audio !== undefined) {
                audio.play();
            }
        } else if ($(this).hasClass('toast-warning')) {
            var audio = $('#warning-audio')[0];
            if (audio !== undefined) {
                audio.play();
            }
        }
    };

    //Default setting for jQuey validator
    jQuery.validator.setDefaults({
        errorPlacement: function (error, element) {
            if (element.hasClass('select2') && element.parent().hasClass('input-group')) {
                error.insertAfter(element.parent());
            } else if (element.hasClass('select2')) {
                error.insertAfter(element.next('span.select2-container'));
            } else if (element.parent().hasClass('input-group')) {
                error.insertAfter(element.parent());
            } else if (element.parent().hasClass('multi-input')) {
                error.insertAfter(element.closest('.multi-input'));
            } else if (element.parent().hasClass('input_inline')) {
                error.insertAfter(element.parent());
            } else if (element.hasClass('upload-element')) {
                error.insertAfter(element.closest('.input-group'));
            } else {
                error.insertAfter(element);
            }
        },

        invalidHandler: function () {
            toastr.error(LANG.some_error_in_input_field);
        },
    });

    jQuery.validator.addMethod(
        'max-value',
        function (value, element, param) {
            var is_draft = false;
            if (
                $(element).hasClass('pos_quantity') &&
                $('select#status').length &&
                $('select#status').val() !== 'final'
            ) {
                is_draft = true;
            }
            return is_draft || this.optional(element) || !(param < __number_uf(value));
        },
        function (params, element) {
            return $(element).data('msg-max-value');
        }
    );

    jQuery.validator.addMethod('abs_digit', function (value, element) {
        return this.optional(element) || Number.isInteger(Math.abs(__number_uf(value)));
    });

    //Set global currency to be used in the application
    __currency_symbol = $('input#__symbol').val();
    __currency_thousand_separator = $('input#__thousand').val();
    __currency_decimal_separator = $('input#__decimal').val();
    __currency_symbol_placement = $('input#__symbol_placement').val();
    if ($('input#__precision').length > 0) {
        __currency_precision = $('input#__precision').val();
    } else {
        __currency_precision = 2;
    }

    if ($('input#__quantity_precision').length > 0) {
        __quantity_precision = $('input#__quantity_precision').val();
    } else {
        __quantity_precision = 2;
    }

    //Set page level currency to be used for some pages. (Purchase page)
    if ($('input#p_symbol').length > 0) {
        __p_currency_symbol = $('input#p_symbol').val();
        __p_currency_thousand_separator = $('input#p_thousand').val();
        __p_currency_decimal_separator = $('input#p_decimal').val();
    }

    __currency_convert_recursively($(document), $('input#p_symbol').length);

    // Simple function to remove currency symbol and HTML tags from string
    function __remove_currency_symbol(str) {
        // DataTables can pass numbers, objects, null - convert all to string
        if (typeof str !== 'string') {
            str = String(str);
        }
        
        // HTML REMOVAL: Simple regex to remove HTML tags
        str = str.replace(/<[^>]*>/g, ''); 
        
        // Check 1: Variable exists, Check 2: Has value, Check 3: Symbol present in string
        if (typeof __currency_symbol !== 'undefined' && __currency_symbol && str.includes(__currency_symbol)) {
            // SIMPLE REPLACEMENT: Replace all occurrences of currency symbol with empty string
            str = str.split(__currency_symbol).join('');  
        }
        
        return str.trim();
    }

    var buttons = [
        // {
        //     extend: 'copy',
        //     text: '<i class="fa fa-files-o" aria-hidden="true"></i> ' + LANG.copy,
        //     className: 'btn-sm',
        //     exportOptions: {
        //         columns: ':visible',
        //     },
        //     footer: true,
        // },
        {
            extend: 'csv',
            text: '<i class="fa fa-file-csv" aria-hidden="true"></i> ' + LANG.export_to_csv,
            className: 'tw-dw-btn-xs  tw-dw-btn tw-dw-btn-outline tw-my-2',
            exportOptions: {
                columns: ':visible',
                format: {
                    body: function(data, row, column, node) {
                        // Check if the node or its children have data-is_quantity="true"
                        var $node = $(node);
                        var $quantityElement = $node.find('[data-is_quantity="true"]');
                        
                        if ($quantityElement.length > 0) {
                            return $quantityElement.attr('data-orig-value');
                        }
                        // Remove currency symbol from the cell data
                        return __remove_currency_symbol(data);
                    },
                    footer: function(data, row, column, node) {
                        // Remove currency symbol from the footer data
                        return __remove_currency_symbol(data);
                    }
                }
            },
            footer: true,
            // Tables marked `hide-footer` (e.g. product list) skip the footer in CSV.
            action: function(e, dt, button, config) {
                if ($(dt.table().node()).hasClass('hide-footer')) config.footer = false;
                $.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, button, config);
            },
        },
        {
            extend: 'excel',
            text: '<i class="fa fa-file-excel" aria-hidden="true"></i> ' + LANG.export_to_excel,
            className: 'tw-dw-btn-xs  tw-dw-btn tw-dw-btn-outline tw-my-2',
            exportOptions: {
                columns: ':visible',
                format: {
                    body: function(data, row, column, node) {
                        // Check if the node or its children have data-is_quantity="true"
                        var $node = $(node);
                        var $quantityElement = $node.find('[data-is_quantity="true"]');
                        if ($quantityElement.length > 0) {
                            return $quantityElement.attr('data-orig-value');
                        }
                        // Remove currency symbol from the cell data
                        return __remove_currency_symbol(data);
                    },
                    footer: function(data, row, column, node) {
                        // Remove currency symbol from the footer data
                        return __remove_currency_symbol(data);
                    }
                }
            },
            footer: true,
            // Tables marked `hide-footer` (e.g. product list) skip the footer in Excel.
            action: function(e, dt, button, config) {
                if ($(dt.table().node()).hasClass('hide-footer')) config.footer = false;
                $.fn.dataTable.ext.buttons.excelHtml5.action.call(this, e, dt, button, config);
            },
        },
        {
            extend: 'print',
            text: '<i class="fa fa-print" aria-hidden="true"></i> ' + LANG.print,
            className: 'tw-dw-btn-xs  tw-dw-btn tw-dw-btn-outline tw-my-2',
            exportOptions: {
                columns: ':visible',
                stripHtml: true,
            },
            footer: true,
            customize: function (win) {
                if ($('.print_table_part').length > 0) {
                    $($('.print_table_part').html()).insertBefore(
                        $(win.document.body).find('table')
                    );
                }
                if ($(win.document.body).find('table.hide-footer').length) {
                    $(win.document.body).find('table.hide-footer tfoot').remove();
                }
                __currency_convert_recursively($(win.document.body).find('table'));
            },
        },
        {
            extend: 'colvis',
            text: '<i class="fa fa-columns" aria-hidden="true"></i> ' + LANG.col_vis,
            className: 'tw-dw-btn-xs  tw-dw-btn tw-dw-btn-outline tw-my-2',
        },
    ];

    // PDF export button (portrait). To show images in PDF, add `data-pdf-include` to the column's <th>.
    var pdf_btn = {
        extend: 'pdf',
        text: '<i class="fa fa-file-pdf" aria-hidden="true"></i> ' + LANG.export_to_pdf,
        className: 'tw-dw-btn-xs  tw-dw-btn tw-dw-btn-outline tw-my-2',
        exportOptions: {
            // Skip hidden columns. Skip "not-export" columns unless they have data-pdf-include.
            columns: function (idx, data, node) {
                return $(node).is(':visible') && (!$(node).hasClass('not-export') || $(node).data('pdfInclude'));
            },
            format: {
                // Use the cached image for image cells, plain text for everything else.
                body: function(data, row, column, node) {
                    var img = $(node).find('img')[0];
                    var cached = img && window._pdfImageCache && window._pdfImageCache[img.src];
                    return cached || $(node).text().trim();
                }
            }
        },
        footer: true,
        // Tables marked `hide-footer` (e.g. product list) skip the footer in PDF.
        action: function(e, dt, button, config) {
            if ($(dt.table().node()).hasClass('hide-footer')) config.footer = false;
            $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button, config);
        },
        // Turn the image data into real images in the PDF.
        customize: function(doc) {
            // Smaller font + tighter margins so wide tables fit on the page.
            doc.defaultStyle.fontSize = 8;
            doc.pageMargins = [20, 20, 20, 20];
            doc.content.forEach(function(b) {
                if (!b.table) return;
                b.table.widths = b.table.body[0].map(function() { return '*'; });
                b.table.body.forEach(function(row) {
                    row.forEach(function(c, j) {
                        var v = typeof c === 'string' ? c : (c && c.text);
                        if (v && v.indexOf('data:image') === 0) row[j] = { image: v, width: 40, height: 40 };
                    });
                });
            });
        }
    };

    // PDF dropdown — Portrait / Landscape choices, styled to match the toolbar's outline button.
    if (!document.getElementById('pdf-dropdown-style')) {
        var pdfDropdownStyle = document.createElement('style');
        pdfDropdownStyle.id = 'pdf-dropdown-style';
        pdfDropdownStyle.textContent =
            // Panel — vertical stack, white background (kills AdminLTE's #00c0ef cyan).
            '.dt-button-collection.pdf-orient-collection{' +
            'display:flex !important;flex-direction:column !important;gap:6px !important;padding:6px !important;' +
            'background:#fff !important;border:1px solid rgba(0,0,0,.15) !important;border-radius:.375rem !important;' +
            'box-shadow:0 4px 6px -1px rgba(0,0,0,.1) !important;min-width:160px !important;' +
            'margin:0 !important;list-style:none !important;column-count:1 !important}' +
            '.pdf-orient-collection,.pdf-orient-collection *{outline:0 !important}' +
            '.pdf-orient-collection>li{all:unset !important;display:block !important}' +
            // Items — only direct <a>/<button> of <li>, never the <li> itself.
            '.pdf-orient-collection>li>a,.pdf-orient-collection>li>button{' +
            'all:unset !important;box-sizing:border-box !important;display:flex !important;align-items:center !important;' +
            'width:100% !important;height:1.5rem !important;padding:0 .5rem !important;' +
            'font-size:.75rem !important;font-weight:600 !important;color:#000 !important;' +
            'background:#fff !important;border:1px solid #000 !important;border-radius:.375rem !important;' +
            'cursor:pointer !important;text-align:left !important}' +
            '.pdf-orient-collection>li>a:hover,.pdf-orient-collection>li>a:focus,' +
            '.pdf-orient-collection>li>a:active,.pdf-orient-collection>li>a.active,' +
            '.pdf-orient-collection>li>button:hover,.pdf-orient-collection>li>button:focus,' +
            '.pdf-orient-collection>li>button:active,.pdf-orient-collection>li>button.active{' +
            'background:#1f2937 !important;color:#fff !important;border-color:#1f2937 !important;' +
            'box-shadow:none !important;text-shadow:none !important}' +
            '.pdf-orient-collection>li>a>*,.pdf-orient-collection>li>button>*{' +
            'background:transparent !important;color:inherit !important;box-shadow:none !important}';
        document.head.appendChild(pdfDropdownStyle);
    }

    var pdf_item_class = 'tw-dw-btn-xs tw-dw-btn tw-dw-btn-outline';

    var pdf_dropdown = {
        extend: 'collection',
        text: '<i class="fa fa-file-pdf" aria-hidden="true"></i> ' + LANG.export_to_pdf + ' <i class="fa fa-caret-down" aria-hidden="true"></i>',
        className: 'tw-dw-btn-xs tw-dw-btn tw-dw-btn-outline tw-my-2',
        collectionLayout: 'pdf-orient-collection',
        autoClose: true,
        buttons: [
            $.extend(true, {}, pdf_btn, {
                text: LANG.portrait,
                className: pdf_item_class
            }),
            $.extend(true, {}, pdf_btn, {
                text: LANG.landscape,
                className: pdf_item_class,
                orientation: 'landscape',
                pageSize: 'A4',
            })
        ]
    };

    if (non_utf8_languages.indexOf(app_locale) == -1) {
        buttons.push(pdf_dropdown);
    }

    if ($('#view_export_buttons').length < 1) {
        buttons = [];
    }
    //Datables
    jQuery.extend($.fn.dataTable.defaults, {
        //Uncomment below line to enable save state of datatable.
        //stateSave: true,
        fixedHeader: true,
        // Toolbar: length + export left, search right (same row on laptop)
        dom: '<"row margin-bottom-20 dt-toolbar"<"dt-toolbar__left"lB><"dt-toolbar__right"f>r>tip',
        buttons: buttons,
        aLengthMenu: [
            [25, 50, 100, 200],
            [25, 50, 100, 200],
        ],
        iDisplayLength: Math.min(parseInt(__default_datatable_page_entries, 10) || 25, 200),
        language: {
            searchPlaceholder: LANG.search + ' ...',
            search: '',
            lengthMenu: LANG.show + ' _MENU_ ' + LANG.entries,
            emptyTable: LANG.table_emptyTable,
            info: LANG.table_info,
            infoEmpty: LANG.table_infoEmpty,
            loadingRecords: LANG.table_loadingRecords,
            processing: LANG.table_processing,
            zeroRecords: LANG.table_zeroRecords,
            paginate: {
                first: LANG.first,
                last: LANG.last,
                next: LANG.next,
                previous: LANG.previous,
            },
        },
    });

    // Icon-only export buttons on laptop still need hover labels
    $(document).on('init.dt', function (e, settings) {
        var $wrap = $(settings.nTableWrapper);
        $wrap.find('.dt-buttons .btn, .dt-buttons .tw-dw-btn').each(function () {
            if (!this.getAttribute('title')) {
                var label = ($(this).text() || '').replace(/\s+/g, ' ').trim();
                if (label) this.setAttribute('title', label);
            }
        });
    });

    // After first DataTable draw, reveal the scroll container to prevent layout shift
    $(document).on('draw.dt', function (e) {
        var $scroll = $(e.target).closest('.dataTables_scroll');
        if ($scroll.length && !$scroll.hasClass('dt-loaded')) {
            $scroll.addClass('dt-loaded');
        }
    });

    if ($('input#iraqi_selling_price_adjustment').length > 0) {
        iraqi_selling_price_adjustment = true;
    } else {
        iraqi_selling_price_adjustment = false;
    }

    //Input number
    $(document).on(
        'click',
        '.input-number .quantity-up, .input-number .quantity-down',
        function () {
            var input = $(this).closest('.input-number').find('input');
            var qty = __read_number(input);
            var step = 1;
            if (input.data('step')) {
                step = input.data('step');
            }
            var min = parseFloat(input.data('min'));
            var max = parseFloat(input.data('max'));

            if ($(this).hasClass('quantity-up')) {
                //if max reached return false
                if (typeof max != 'undefined' && qty + step > max) {
                    return false;
                }

                __write_number(input, qty + step);
                input.change();
            } else if ($(this).hasClass('quantity-down')) {
                //if max reached return false
                if (typeof min != 'undefined' && qty - step < min) {
                    return false;
                }

                __write_number(input, qty - step);
                input.change();
            }
        }
    );

    $('div.pos-tab-menu>div.list-group>a').click(function (e) {
        e.preventDefault();
        $(this).siblings('a.active').removeClass('active');
        $(this).addClass('active');
        var index = $(this).index();
        $('div.pos-tab>div.pos-tab-content').removeClass('active');
        var $tab = $('div.pos-tab>div.pos-tab-content').eq(index);
        $tab.addClass('active');

        // Lazy-init Select2 / iCheck when a settings tab is first opened
        if ($('.business-settings-page').length && !$tab.data('widgets-ready')) {
            $tab.data('widgets-ready', true);
            var $selects = $tab.find('select.select2').filter(function () {
                return !$(this).hasClass('select2-hidden-accessible');
            });
            if ($selects.length && typeof __select2 === 'function') {
                __select2($selects);
            }
            $tab.find('input[type="checkbox"].input-icheck, input[type="radio"].input-icheck')
                .filter(function () {
                    return !$(this).parent().hasClass('icheckbox_square-blue')
                        && !$(this).parent().hasClass('iradio_square-blue');
                })
                .iCheck({
                    checkboxClass: 'icheckbox_square-blue',
                    radioClass: 'iradio_square-blue',
                });
        }

        // Display Screen tab: TinyMCE + carousel file inputs
        if ($('.business-settings-page').length && typeof window.__initBusinessSettingsDisplayTab === 'function') {
            if ($tab.find('#display_screen_heading, .carousel_image').length) {
                window.__initBusinessSettingsDisplayTab();
            }
        }
    });

    $('.scroll-top-bottom').each(function () {
        $(this).topScrollbar();
    });

    $('.datetimepicker').datetimepicker({
        format: moment_date_format + ' ' + moment_time_format,
        ignoreReadonly: true,
    });
});

//Default settings for daterangePicker
var ranges = {};
ranges[LANG.today] = [moment(), moment()];
ranges[LANG.yesterday] = [moment().subtract(1, 'days'), moment().subtract(1, 'days')];
ranges[LANG.last_7_days] = [moment().subtract(6, 'days'), moment()];
ranges[LANG.last_30_days] = [moment().subtract(29, 'days'), moment()];
ranges[LANG.this_month] = [moment().startOf('month'), moment().endOf('month')];
ranges[LANG.last_month] = [
    moment().subtract(1, 'month').startOf('month'),
    moment().subtract(1, 'month').endOf('month'),
];
ranges[LANG.this_month_last_year] = [
    moment().subtract(1, 'year').startOf('month'),
    moment().subtract(1, 'year').endOf('month'),
];
ranges[LANG.this_year] = [moment().startOf('year'), moment().endOf('year')];
ranges[LANG.last_year] = [
    moment().startOf('year').subtract(1, 'year'),
    moment().endOf('year').subtract(1, 'year'),
];
ranges[LANG.this_financial_year] = [financial_year.start, financial_year.end];
ranges[LANG.last_financial_year] = [
    moment(financial_year.start._i).subtract(1, 'year'),
    moment(financial_year.end._i).subtract(1, 'year'),
];

var dateRangeSettings = {
    showDropdowns : true,
    linkedCalendars : false,
    ranges: ranges,
    startDate: financial_year.start,
    endDate: financial_year.end,
    locale: {
        cancelLabel: LANG.clear,
        applyLabel: LANG.apply,
        customRangeLabel: LANG.custom_range,
        format: moment_date_format,
        toLabel: '~',
    },
};

//Check for number string in input field, if data-decimal is 0 then don't allow decimal symbol and if no_neg then don't allow  negative value
$(document).on('keypress', 'input.input_number', function (event) {
    var is_decimal = $(this).data('decimal');

    if (is_decimal == 0) {
        if (__currency_decimal_separator == '.') {
            var regex = new RegExp(/^[0-9,-]+$/);
        } else {
            var regex = new RegExp(/^[0-9.-]+$/);
        }
    } else {
        var regex = new RegExp(/^[0-9.,-]+$/);
    }

    // Check for no negative values
    if(is_decimal == 'no_neg'){
        var regex = new RegExp(/^[0-9.,]+$/);
    }

    var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
    if (!regex.test(key)) {
        event.preventDefault();
        return false;
    }
});

//Select all input values on click
$(document).on('click', 'input', function (event) {
    $(this).select();
});

$(document).on('click', '.toggle-font-size', function (event) {
    localStorage.setItem('upos_font_size', $(this).data('size'));
    update_font_size();
});
$(document).on('click', '.sidebar-toggle', function () {
    if ($('body').hasClass('sidebar-collapse')) {
        localStorage.setItem('upos_sidebar_collapse', 'false');
    } else {
        localStorage.setItem('upos_sidebar_collapse', 'true');
    }
});

//Ask for confirmation for links
$(document).on('click', 'a.link_confirmation', function (e) {
    e.preventDefault();
    swal({
        title: LANG.sure,
        icon: 'warning',
        buttons: true,
        dangerMode: true,
    }).then((confirmed) => {
        if (confirmed) {
            window.location.href = $(this).attr('href');
        }
    });
});

//Change max quantity rule if lot number changes
$('table#stock_adjustment_product_table tbody').on('change', 'select.lot_number', function () {
    var tr = $(this).closest('tr');
    var qty_element = tr.find('input.product_quantity');
    var qty_available_el = tr.find('.qty_available_text');

    var multiplier = 1;
    var unit_name = '';
    var sub_unit_length = tr.find('select.sub_unit').length;
    if (sub_unit_length > 0) {
        var select = tr.find('select.sub_unit');
        multiplier = parseFloat(select.find(':selected').data('multiplier'));
        unit_name = select.find(':selected').data('unit_name');
    }

    if ($(this).val()) {
        var lot_qty = $('option:selected', $(this)).data('qty_available');
        var max_err_msg = $('option:selected', $(this)).data('msg-max');

        if (sub_unit_length > 0) {
            lot_qty = lot_qty / multiplier;
            var lot_qty_formated = __number_f(lot_qty, false);
            max_err_msg = __translate('lot_max_qty_error', {
                max_val: lot_qty_formated,
                unit_name: unit_name,
            });
        }

        qty_element.attr('data-rule-max-value', lot_qty);
        qty_element.attr('data-msg-max-value', max_err_msg);

        qty_element.rules('add', {
            'max-value': lot_qty,
            messages: {
                'max-value': max_err_msg,
            },
        });
        if (qty_available_el.length) {
            qty_available_el.text(__currency_trans_from_en(lot_qty, false));
        }
    } else {
        var default_qty = qty_element.data('qty_available');
        var default_err_msg = qty_element.data('msg_max_default');

        if (sub_unit_length > 0) {
            default_qty = default_qty / multiplier;
            var lot_qty_formated = __number_f(default_qty, false);
            default_err_msg = __translate('pos_max_qty_error', {
                max_val: lot_qty_formated,
                unit_name: unit_name,
            });
        }

        qty_element.attr('data-rule-max-value', default_qty);
        qty_element.attr('data-msg-max-value', default_err_msg);

        qty_element.rules('add', {
            'max-value': default_qty,
            messages: {
                'max-value': default_err_msg,
            },
        });

        if (qty_available_el.length) {
            qty_available_el.text(__currency_trans_from_en(default_qty, false));
        }
    }
    qty_element.trigger('change');
});
$('button#btnCalculator, button#return_sale').hover(function () {
    $(this).tooltip('show');
});
$('button#return_sale').click(function () {
    $(this).popover('toggle');
});
$('button#service_staff_replacement').click(function () {
    $(this).popover('toggle');
});
$(document).on('mouseleave', 'button#btnCalculator, button#return_sale', function (e) {
    $(this).tooltip('hide');
});

jQuery.validator.addMethod(
    'min-value',
    function (value, element, param) {
        return this.optional(element) || !(param > __number_uf(value));
    },
    function (params, element) {
        return $(element).data('min-value');
    }
);

$(document).on('click', '.view_uploaded_document', function (e) {
    e.preventDefault();
    var src = $(this).data('href');
    var html =
        '<div class="modal-dialog" role="document"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><div class="modal-body"><img src="' +
        src +
        '" class="img-responsive" alt="Uploaded Document"></div><div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Close</button> <a href="' +
        src +
        '" class="btn btn-success" download=""><i class="fa fa-download"></i> Download</a></div></div></div>';
    $('div.view_modal').html(html).modal('show');
});

$(document).on('click', '#accordion .box-header', function (e) {
    if (e.target.tagName == 'A' || e.target.tagName == 'I') {
        return false;
    }
    $(this).find('.box-title a').click();
});

$(document).on('shown.bs.modal', '.contains_select2, .view_modal', function () {
    $(this)
        .find('.select2')
        .each(function () {
            var $p = $(this).parent();
            $(this).select2({ dropdownParent: $p });
        });
});

//common configuration : tinyMCE editor
if (typeof tinymce !== 'undefined' && tinymce && typeof tinymce.overrideDefaults === 'function') {
    tinymce.overrideDefaults({
        height: 300,
        language: app_locale, // Set language dynamically
        language_url: base_path + '/js/lang/tiny/' + app_locale + '.js', // Dynamic URL
        theme: 'silver',
        plugins: [
            'advlist autolink link image lists charmap print preview hr anchor pagebreak',
            'searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking',
            'table template paste help',
        ],
        toolbar:
            'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify |' +
            ' bullist numlist outdent indent | link image | print preview media fullpage | ' +
            'forecolor backcolor',
        menu: {
            favs: { title: 'My Favorites', items: 'code | searchreplace' },
        },
        menubar: 'favs file edit view insert format tools table help',
    });
}

// Prevent Bootstrap dialog from blocking focusin
$(document).on('focusin', function (e) {
    if ($(e.target).closest('.tox-tinymce-aux, .moxman-window, .tam-assetmanager-root, .select2-container').length) {
        e.stopImmediatePropagation();
    }
});

//search parameter in url
function urlSearchParam(param) {
    var results = new RegExp('[?&]' + param + '=([^&#]*)').exec(window.location.href);
    if (results == null) {
        return null;
    } else {
        return results[1];
    }
}

// For dropdown hidden issue
// (function() {
//   var dropdownMenu;
//   $('table').on('show.bs.dropdown', function(e) {
//     dropdownMenu = $(e.target).find('.dropdown-menu');
//     $('body').append(dropdownMenu.detach());
//     var eOffset = $(e.target).offset();
//     if(dropdownMenu.hasClass('dropdown-menu-right')) {
//         dropdownMenu.css({
//             'display': 'block',
//             'top': eOffset.top + $(e.target).outerHeight(),
//             'left': 'auto',
//             'right': 0
//         });
//     } else {
//         dropdownMenu.css({
//             'display': 'block',
//             'top': eOffset.top + $(e.target).outerHeight(),
//             'left': eOffset.left
//         });
//     }
//   });
//   $('table').on('hide.bs.dropdown', function(e) {
//     $(e.target).append(dropdownMenu.detach());
//     dropdownMenu.hide();
//   });
// })();

function updateOnlineStatus() {
    if (!__is_online()) {
        $('#online_indicator').removeClass('text-success');
        $('#online_indicator').addClass('text-danger');
    } else {
        $('#online_indicator').removeClass('text-danger');
        $('#online_indicator').addClass('text-success');
    }
}

$(document).on('change', '.cash_denomination', function () {
    var total = 0;
    var table = $(this).closest('table');
    table.find('tbody tr').each(function () {
        var denomination = parseFloat($(this).find('.cash_denomination').attr('data-denomination'));
        var count = $(this).find('.cash_denomination').val()
            ? parseInt($(this).find('.cash_denomination').val())
            : 0;
        var subtotal = denomination * count;
        total = total + subtotal;
        $(this).find('span.denomination_subtotal').text(__currency_trans_from_en(subtotal, true));
    });

    table.find('span.denomination_total').text(__currency_trans_from_en(total, true));
    table.find('input.denomination_total_amount').val(total);
});

//autofocus select2 search input
let forceFocusFn = function () {
    // Gets the search input of the opened select2
    var searchInput = document.querySelector('.select2-container--open .select2-search__field');
    // If exists
    if (searchInput) searchInput.focus(); // focus
};

// Every time a select2 is opened
$(document).on('select2:open', () => {
    // We use a timeout because when a select2 is already opened and you open a new one, it has to wait to find the appropiate
    setTimeout(() => forceFocusFn(), 200);
});

function copyToClipboard(element_id) {
    var temp = $('<input>');
    $('body').append(temp);
    temp.val($('#' + element_id).text()).select();
    document.execCommand('copy');
    temp.remove();
    toastr.success(LANG.copied_to_clipboard);
}

// This function escapes HTML characters in a given string to prevent XSS attacks.
function escapeHtml(str) {
    if (typeof str !== 'string') return '';
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// Sidebar interactions (search, dropdowns, mobile toggle, collapse)
$(function () {
    $('.side-bar').removeAttr('style');

    // --- Sidebar menu search filter ---
    // Matches labels, title, URL path, and data-search-terms; expands matching submenus
    // (child panels stay display:none after match unless we open them — that made
    // Shipments / Shipping Methods look "missing" unless their parent was already open).
    function sidebarSearchText($el) {
        if (!$el || !$el.length) {
            return '';
        }
        var parts = [];
        var $label = $el.find('.sidebar-menu-label').first();
        if ($label.length) {
            parts.push($label.text());
        } else {
            var $span = $el.children('span').first();
            parts.push($span.length ? $span.text() : $el.text());
        }
        var title = $el.attr('title');
        if (title) {
            parts.push(title);
        }
        var href = $el.attr('href') || '';
        if (href && href !== '#') {
            try {
                href = href.replace(/^https?:\/\/[^/]+/i, '');
            } catch (e) {}
            parts.push(href.replace(/[/?#=&._-]+/g, ' '));
        }
        var terms = $el.attr('data-search-terms');
        if (terms) {
            parts.push(terms);
        }
        return parts.join(' ').replace(/\s+/g, ' ').trim().toLowerCase();
    }

    function sidebarQueryMatches(haystack, q) {
        if (!q) {
            return true;
        }
        if (!haystack) {
            return false;
        }
        // All tokens must match (so "shipping method" hits "Shipping Methods")
        return q.split(/\s+/).filter(Boolean).every(function (token) {
            return haystack.indexOf(token) !== -1;
        });
    }

    function restoreSidebarMenusAfterSearch() {
        $('#side-bar .sidebar-menu-hidden').removeClass('sidebar-menu-hidden');
        $('#side-bar > .zoho-sidebar-item').each(function () {
            var $item = $(this);
            var $panel = $item.children('.chiled');
            if (!$panel.length) {
                return;
            }
            var open = $item.children('a.drop_down').hasClass('theme-sidebar-active');
            $panel.css('display', open ? 'block' : 'none');
        });
        $('#side-bar .zoho-sidebar-nested-group').each(function () {
            var $group = $(this);
            var $panel = $group.children('.chiled');
            if (!$panel.length) {
                return;
            }
            var open = $group.children('.zoho-sidebar-nested-toggle').hasClass('theme-sidebar-child-active');
            $panel.css('display', open ? 'block' : 'none');
        });
    }

    function filterSidebar(q) {
        q = (q || '').trim().toLowerCase();
        var anyVisible = false;

        if (!q) {
            restoreSidebarMenusAfterSearch();
            $('body').removeClass('zoho-sidebar-search-active');
            $('#sidebar-no-results').addClass('tw-hidden');
            $('#sidebar-search-clear').addClass('tw-hidden');
            $('#side-bar > .zoho-sidebar-item').removeClass('zoho-sidebar-flyout-open');
            return;
        }

        $('#side-bar').children('.zoho-sidebar-item, a').each(function () {
            var $item = $(this);
            var $link = $item.is('a') ? $item : $item.children('a').first();
            var parentText = sidebarSearchText($link);
            var parentMatch = sidebarQueryMatches(parentText, q);
            var anyChildMatch = false;
            var $panel = $item.children('.chiled');

            // Direct + nested leaf links
            $item.find('.zoho-sidebar-child-link').each(function () {
                var $child = $(this);
                if ($child.hasClass('zoho-sidebar-child-add')) {
                    return;
                }
                var $row = $child.closest('.zoho-sidebar-child-row');
                var childMatch = sidebarQueryMatches(sidebarSearchText($child), q);
                var hideChild = !parentMatch && !childMatch;
                if ($row.length) {
                    $row.toggleClass('sidebar-menu-hidden', hideChild);
                } else {
                    $child.toggleClass('sidebar-menu-hidden', hideChild);
                }
                if (childMatch) {
                    anyChildMatch = true;
                }
            });

            // Nested submenu titles (e.g. "Items") also searchable / control expand
            $item.find('.zoho-sidebar-nested-group').each(function () {
                var $group = $(this);
                var $toggle = $group.children('.zoho-sidebar-nested-toggle');
                var groupTitleMatch = sidebarQueryMatches(sidebarSearchText($toggle), q);
                var groupChildMatch = false;
                $group.find('.zoho-sidebar-child-link').each(function () {
                    if (sidebarQueryMatches(sidebarSearchText($(this)), q)) {
                        groupChildMatch = true;
                        return false;
                    }
                });
                var showGroup = parentMatch || groupTitleMatch || groupChildMatch;
                $group.toggleClass('sidebar-menu-hidden', !showGroup);
                if (showGroup && (groupTitleMatch || groupChildMatch || parentMatch)) {
                    $group.children('.chiled').css('display', 'block');
                    if (groupTitleMatch || groupChildMatch) {
                        anyChildMatch = true;
                    }
                } else {
                    $group.children('.chiled').css('display', 'none');
                }
            });

            var showItem = parentMatch || anyChildMatch;
            $item.toggleClass('sidebar-menu-hidden', !showItem);
            if (showItem) {
                anyVisible = true;
                // Always expand matching branches so results are visible
                if ($panel.length) {
                    $panel.css('display', 'block');
                }
            } else if ($panel.length) {
                $panel.css('display', 'none');
            }
        });

        $('body').toggleClass('zoho-sidebar-search-active', true);
        $('#sidebar-no-results').toggleClass('tw-hidden', anyVisible);
        $('#sidebar-search-clear').toggleClass('tw-hidden', false);
    }

    function syncMenuSearch(value) {
        $('#sidebar-search').val(value);
        filterSidebar(value);
    }

    $('#sidebar-search').on('input', function () {
        syncMenuSearch($(this).val());
    });

    $('#sidebar-search-clear').on('click', function () {
        syncMenuSearch('');
        $('#sidebar-search').focus();
    });

    $(document).on('keydown', function (e) {
        if (e.key === '/' && !/input|textarea|select/i.test(document.activeElement.tagName)) {
            e.preventDefault();
            $('#sidebar-search').focus();
        }
    });

    // --- Sidebar dropdown toggle ---
    function isZohoSidebarCollapsed() {
        if (window.ZohoSidebar) {
            return window.ZohoSidebar.isCollapsed();
        }

        return $('.side-bar').hasClass('zoho-sidebar-collapsed') || $('body').hasClass('sidebar-collapse');
    }

    function setSidebarCollapsed(collapsed) {
        if (window.ZohoSidebar) {
            window.ZohoSidebar.setCollapsed(collapsed);
            return;
        }

        $('body').toggleClass('sidebar-collapse zoho-sidebar-collapsed', collapsed);
        $('.side-bar').toggleClass('zoho-sidebar-collapsed', collapsed);
        localStorage.setItem('upos_sidebar_collapse', collapsed ? 'true' : 'false');
        $('.zoho-sidebar-item').removeClass('zoho-sidebar-flyout-open');
        if (!collapsed) {
            $('#side-bar .chiled').each(function () {
                var $chiled = $(this);
                if ($chiled.closest('.zoho-sidebar-item').find('> .drop_down').hasClass('theme-sidebar-active')) {
                    $chiled.show();
                }
            });
        }
    }

    $(document).on('click', '.zoho-sidebar-child-add', function (event) {
        event.stopPropagation();
    });

    function clearCollapsedFlyoutStyles($panel) {
        if (!$panel || !$panel.length) {
            return;
        }
        $panel.css({
            position: '',
            top: '',
            left: '',
            right: '',
            bottom: '',
            maxHeight: '',
            width: '',
            display: '',
            overflowY: '',
        });
    }

    function positionCollapsedFlyout($item) {
        var $panel = $item.children('.chiled').first();
        if (!$panel.length) {
            return;
        }

        var trigger = $item.children('a').first().get(0) || $item.get(0);
        var rect = trigger.getBoundingClientRect();
        var gap = 4;
        var panelWidth = 240;
        var viewportPad = 8;
        var maxHeight = Math.max(180, window.innerHeight - viewportPad * 2);
        var left = Math.round(rect.right + gap);
        var top = Math.round(rect.top);

        if (left + panelWidth > window.innerWidth - viewportPad) {
            left = Math.max(viewportPad, Math.round(rect.left - panelWidth - gap));
        }

        // Temporarily show to measure height, then clamp into viewport
        $panel.css({
            position: 'fixed',
            top: top + 'px',
            left: left + 'px',
            right: 'auto',
            bottom: 'auto',
            width: panelWidth + 'px',
            maxHeight: maxHeight + 'px',
            overflowY: 'auto',
            display: 'block',
            zIndex: 1080,
        });

        var panelHeight = Math.min($panel.outerHeight() || 280, maxHeight);
        if (top + panelHeight > window.innerHeight - viewportPad) {
            top = Math.max(viewportPad, window.innerHeight - viewportPad - panelHeight);
        }

        $panel.css({
            top: Math.round(top) + 'px',
            maxHeight: maxHeight + 'px',
            overflowY: 'auto',
        });
    }

    function closeAllCollapsedFlyouts() {
        $('#side-bar > .zoho-sidebar-item.zoho-sidebar-flyout-open').each(function () {
            var $item = $(this);
            clearCollapsedFlyoutStyles($item.children('.chiled').first());
            $item.removeClass('zoho-sidebar-flyout-open');
        });
    }

    $(document).on('click', '.drop_down', function (event) {
        if (isZohoSidebarCollapsed()) {
            // Nested submenu inside an open flyout (e.g. Inventory > Items)
            if ($(this).hasClass('zoho-sidebar-nested-toggle')) {
                event.preventDefault();
                event.stopPropagation();
                var $nested = $(this).next('.chiled');
                $nested.slideToggle(150, function () {
                    var $openItem = $('#side-bar > .zoho-sidebar-item.zoho-sidebar-flyout-open').first();
                    if ($openItem.length) {
                        positionCollapsedFlyout($openItem);
                    }
                });
                return;
            }

            // Top-level icon: open flyout panel to the right (fixed + scrollable)
            var $item = $(this).closest('#side-bar > .zoho-sidebar-item');
            if (!$item.length) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();

            var willOpen = !$item.hasClass('zoho-sidebar-flyout-open');
            closeAllCollapsedFlyouts();

            if (willOpen) {
                $item.addClass('zoho-sidebar-flyout-open');
                positionCollapsedFlyout($item);
            }
            return;
        }

        event.preventDefault();
        event.stopPropagation();
        var $toggle = $(this);
        var $chiled = $toggle.next('.chiled');
        var $parent = $toggle.parent();

        // Close only same-level sibling panels (keeps nested Inventory > Items/Stock open)
        $parent.siblings().children('.chiled').slideUp();
        if ($parent.hasClass('zoho-sidebar-item')) {
            $('#side-bar > .zoho-sidebar-item').not($parent).children('.chiled').slideUp();
        }

        $chiled.slideToggle(function () {
            $('.sidebar-menu-chevron').each(function () {
                var $currentSvgElement = $(this);
                var $panel = $currentSvgElement.closest('.drop_down').next('.chiled');
                if ($panel.length && $panel.is(':visible')) {
                    $currentSvgElement.html(
                        '<path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M6 9l6 6l6 -6" />'
                    );
                } else {
                    $currentSvgElement.html(
                        '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 6l-6 6l6 6" />'
                    );
                }
            });
        });
    });

    $(document).on('click', function (event) {
        if (!isZohoSidebarCollapsed()) {
            return;
        }
        if (!$(event.target).closest('.zoho-sidebar-item, .chiled').length) {
            closeAllCollapsedFlyouts();
        }
    });

    $(window).on('resize scroll', function () {
        if (!isZohoSidebarCollapsed()) {
            return;
        }
        var $openItem = $('#side-bar > .zoho-sidebar-item.zoho-sidebar-flyout-open').first();
        if ($openItem.length) {
            positionCollapsedFlyout($openItem);
        }
    });

    $('#side-bar').on('scroll', function () {
        if (!isZohoSidebarCollapsed()) {
            return;
        }
        closeAllCollapsedFlyouts();
    });

    document.addEventListener('zoho-sidebar-toggled', function () {
        closeAllCollapsedFlyouts();
    });

    // --- Sidebar mobile open / overlay close ---
    $(document).on('click', '.small-view-button', function () {
        $('.side-bar').addClass('small-view-side-active');
        $('.overlay').removeClass('tw-hidden').stop(true, true).fadeIn('slow');
    });

    $(document).on('click', '.overlay', function () {
        $('.overlay').stop(true, true).fadeOut('slow', function () {
            $(this).addClass('tw-hidden').css('display', '');
        });
        $('.side-bar').removeClass('small-view-side-active');
        if (window.ZohoSidebar && window.ZohoSidebar.closeMobileSidebar) {
            window.ZohoSidebar.closeMobileSidebar();
        }
    });

    // --- Sidebar responsive resize ---
    $(window).on('resize', function () {
        if ($(window).width() >= 1024) {
            $('.overlay').stop(true, true).fadeOut(0).addClass('tw-hidden').css('display', '');
            $('.side-bar').removeClass('small-view-side-active');
            if (window.ZohoSidebar && window.ZohoSidebar.closeMobileSidebar) {
                window.ZohoSidebar.closeMobileSidebar();
            }
        }
        if ($('.side-bar').hasClass('small-view-side-active')) {
            $('.overlay').removeClass('tw-hidden').stop(true, true).fadeIn('slow');
        }
    });

    // Collapse handled by zoho-sidebar.js (capture phase). Keep fallback only.
    $(document).on('click', '.side-bar-collapse', function (event) {
        if (window.ZohoSidebar) {
            return;
        }

        event.preventDefault();
        setSidebarCollapsed(!isZohoSidebarCollapsed());
    });
});
