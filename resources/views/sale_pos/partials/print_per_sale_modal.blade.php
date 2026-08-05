@php
    $saved_print_copies = (int) ($business_details->print_per_sale_copies ?? 1);
    if (! in_array($saved_print_copies, [1, 2, 3], true)) {
        $saved_print_copies = 1;
    }
    $saved_print_printer = (string) ($business_details->print_per_sale_printer_id ?? '');
@endphp
<div class="modal fade no-print" id="pos_print_per_sale_modal" tabindex="-1" role="dialog"
    aria-labelledby="posPrintPerSaleTitle"
    data-saved-copies="{{ $saved_print_copies }}"
    data-saved-printer="{{ e($saved_print_printer) }}">
    <div class="modal-dialog" role="document" style="max-width: 400px;">
        <div class="modal-content pos-print-sale-modal">
            <div class="modal-header pos-print-sale-modal__header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <div class="pos-print-sale-modal__hero">
                    <span class="pos-print-sale-modal__icon" aria-hidden="true">
                        <i class="fas fa-print"></i>
                    </span>
                    <div class="pos-print-sale-modal__hero-text">
                        <h4 class="modal-title" id="posPrintPerSaleTitle">Print Per Sale</h4>
                        <p class="pos-print-sale-modal__hint">Choose how many copies print after each sale</p>
                    </div>
                </div>
            </div>
            <div class="modal-body">
                <div class="pos-print-sale-modal__options" role="group" aria-label="Print copies">
                    <button type="button" class="pos-print-sale-modal__option @if($saved_print_copies === 1) is-active @endif" data-copies="1">
                        <span class="pos-print-sale-modal__radio" aria-hidden="true"></span>
                        <span class="pos-print-sale-modal__option-body">
                            <span class="pos-print-sale-modal__option-label">Original</span>
                            <span class="pos-print-sale-modal__option-meta">1 copy · default</span>
                        </span>
                        <span class="pos-print-sale-modal__badge">1×</span>
                    </button>
                    <button type="button" class="pos-print-sale-modal__option @if($saved_print_copies === 2) is-active @endif" data-copies="2">
                        <span class="pos-print-sale-modal__radio" aria-hidden="true"></span>
                        <span class="pos-print-sale-modal__option-body">
                            <span class="pos-print-sale-modal__option-label">Duplicate</span>
                            <span class="pos-print-sale-modal__option-meta">2 copies</span>
                        </span>
                        <span class="pos-print-sale-modal__badge">2×</span>
                    </button>
                    <button type="button" class="pos-print-sale-modal__option @if($saved_print_copies === 3) is-active @endif" data-copies="3">
                        <span class="pos-print-sale-modal__radio" aria-hidden="true"></span>
                        <span class="pos-print-sale-modal__option-body">
                            <span class="pos-print-sale-modal__option-label">Triplicate</span>
                            <span class="pos-print-sale-modal__option-meta">3 copies</span>
                        </span>
                        <span class="pos-print-sale-modal__badge">3×</span>
                    </button>
                </div>
                @if (!empty($pos_printers) && count($pos_printers))
                    <div class="pos-print-sale-modal__printer-wrap">
                        <label class="pos-print-sale-modal__printer-label" for="pos_print_printer_select">
                            <i class="fas fa-print"></i> Printer
                        </label>
                        <select id="pos_print_printer_select" class="form-control pos-print-sale-modal__printer" aria-label="Printer">
                            <option value="" @if($saved_print_printer === '') selected @endif>Default</option>
                            @foreach ($pos_printers as $printer_id => $printer_name)
                                <option value="{{ $printer_id }}" @if((string) $saved_print_printer === (string) $printer_id) selected @endif>{{ $printer_name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <p class="pos-print-sale-modal__status" id="pos_print_settings_status" hidden></p>
            </div>
            <div class="modal-footer pos-print-sale-modal__footer">
                <button type="button" class="btn pos-print-sale-modal__done" data-dismiss="modal">Done</button>
            </div>
        </div>
    </div>
</div>
<script>
(function () {
    if (window.__posPrintPerSaleBound) {
        return;
    }
    window.__posPrintPerSaleBound = true;

    var modalEl = document.getElementById('pos_print_per_sale_modal');
    var saveTimer = null;

    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function saveUrl() {
        var el = document.getElementById('pos_print_settings_url');
        return el ? el.value : '';
    }

    function dbCopies() {
        if (!modalEl) return 1;
        var c = parseInt(modalEl.getAttribute('data-saved-copies') || '1', 10);
        return (c === 1 || c === 2 || c === 3) ? c : 1;
    }

    function dbPrinter() {
        if (!modalEl) return '';
        return modalEl.getAttribute('data-saved-printer') || '';
    }

    function optionButtons() {
        return document.querySelectorAll('#pos_print_per_sale_modal .pos-print-sale-modal__option');
    }

    function syncFields(copies, printer) {
        var copiesField = document.getElementById('pos_print_copies');
        var printerField = document.getElementById('pos_print_printer');
        if (copiesField) copiesField.value = String(copies);
        if (printerField) printerField.value = String(printer || '');
    }

    function setStatus(msg, isError) {
        var el = document.getElementById('pos_print_settings_status');
        if (!el) return;
        if (!msg) {
            el.hidden = true;
            el.textContent = '';
            return;
        }
        el.hidden = false;
        el.textContent = msg;
        el.classList.toggle('is-error', !!isError);
        el.classList.toggle('is-ok', !isError);
    }

    function setCopiesUI(n) {
        n = parseInt(n, 10);
        if (n !== 1 && n !== 2 && n !== 3) n = 1;
        var btns = optionButtons();
        for (var i = 0; i < btns.length; i++) {
            var btn = btns[i];
            var c = parseInt(btn.getAttribute('data-copies'), 10);
            if (c === n) {
                btn.classList.add('is-active');
            } else {
                btn.classList.remove('is-active');
            }
        }
        if (modalEl) {
            modalEl.setAttribute('data-saved-copies', String(n));
        }
        syncFields(n, currentPrinter());
        return n;
    }

    function currentPrinter() {
        var sel = document.getElementById('pos_print_printer_select');
        if (sel) return sel.value || '';
        return dbPrinter();
    }

    function setPrinterUI(val) {
        val = val || '';
        var sel = document.getElementById('pos_print_printer_select');
        if (sel) {
            var found = false;
            for (var i = 0; i < sel.options.length; i++) {
                if (sel.options[i].value === val) {
                    found = true;
                    break;
                }
            }
            sel.value = found ? val : '';
            val = sel.value;
        }
        if (modalEl) {
            modalEl.setAttribute('data-saved-printer', val);
        }
        syncFields(dbCopies(), val);
        return val;
    }

    function saveToDatabase(copies, printer) {
        var url = saveUrl();
        if (!url) {
            setStatus('Save URL missing', true);
            return;
        }
        setStatus('Saving…', false);

        var body = new FormData();
        body.append('print_copies', String(copies));
        body.append('print_printer', String(printer || ''));
        body.append('_token', csrfToken());

        fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': csrfToken(),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: body
        })
            .then(function (res) {
                if (!res.ok) throw new Error('save failed');
                return res.json();
            })
            .then(function (data) {
                if (!data || !data.success) throw new Error('save failed');
                if (modalEl) {
                    modalEl.setAttribute('data-saved-copies', String(data.print_copies));
                    modalEl.setAttribute('data-saved-printer', String(data.print_printer || ''));
                }
                syncFields(data.print_copies, data.print_printer || '');
                setStatus('Saved to database', false);
                setTimeout(function () { setStatus('', false); }, 1600);
            })
            .catch(function () {
                setStatus('Could not save — try again', true);
            });
    }

    function queueSave() {
        if (saveTimer) clearTimeout(saveTimer);
        saveTimer = setTimeout(function () {
            saveToDatabase(dbCopies(), currentPrinter());
        }, 180);
    }

    function applyFromDatabase() {
        setCopiesUI(dbCopies());
        setPrinterUI(dbPrinter());
        syncFields(dbCopies(), currentPrinter());
    }

    function bindOptions() {
        var btns = optionButtons();
        for (var i = 0; i < btns.length; i++) {
            (function (btn) {
                if (btn.getAttribute('data-bound') === '1') return;
                btn.setAttribute('data-bound', '1');
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    setCopiesUI(btn.getAttribute('data-copies'));
                    queueSave();
                });
            })(btns[i]);
        }
        var printerSelect = document.getElementById('pos_print_printer_select');
        if (printerSelect && printerSelect.getAttribute('data-bound') !== '1') {
            printerSelect.setAttribute('data-bound', '1');
            printerSelect.addEventListener('change', function () {
                setPrinterUI(printerSelect.value);
                queueSave();
            });
        }
    }

    bindOptions();
    applyFromDatabase();

    if (typeof jQuery !== 'undefined') {
        jQuery(document)
            .off('shown.bs.modal.posPrintSale')
            .on('shown.bs.modal.posPrintSale', '#pos_print_per_sale_modal', function () {
                bindOptions();
                applyFromDatabase();
            });
    }
})();
</script>
