@php
    $saved_layout = strtolower((string) ($business_details->pos_screen_layout ?? 'ltr'));
    if (! in_array($saved_layout, ['ltr', 'rtl'], true)) {
        $saved_layout = 'ltr';
    }
@endphp
<div class="modal fade no-print" id="pos_screen_setting_modal" tabindex="-1" role="dialog"
    aria-labelledby="posScreenSettingTitle"
    data-saved-layout="{{ $saved_layout }}">
    <div class="modal-dialog" role="document" style="max-width: 400px;">
        <div class="modal-content pos-print-sale-modal pos-screen-setting-modal">
            <div class="modal-header pos-print-sale-modal__header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <div class="pos-print-sale-modal__hero">
                    <span class="pos-print-sale-modal__icon" aria-hidden="true">
                        <i class="fas fa-columns"></i>
                    </span>
                    <div class="pos-print-sale-modal__hero-text">
                        <h4 class="modal-title" id="posScreenSettingTitle">POS Screen Setting</h4>
                        <p class="pos-print-sale-modal__hint">Choose product &amp; cart panel layout</p>
                    </div>
                </div>
            </div>
            <div class="modal-body">
                <div class="pos-print-sale-modal__options" role="group" aria-label="Screen layout">
                    <button type="button" class="pos-print-sale-modal__option pos-screen-layout-opt @if($saved_layout === 'ltr') is-active @endif" data-layout="ltr">
                        <span class="pos-print-sale-modal__radio" aria-hidden="true"></span>
                        <span class="pos-print-sale-modal__option-body">
                            <span class="pos-print-sale-modal__option-label">Left to Right</span>
                            <span class="pos-print-sale-modal__option-meta">Products left · Cart right (default)</span>
                        </span>
                        <span class="pos-screen-layout-preview pos-screen-layout-preview--ltr" aria-hidden="true">
                            <span class="pos-screen-layout-preview__prod"></span>
                            <span class="pos-screen-layout-preview__cart"></span>
                        </span>
                    </button>
                    <button type="button" class="pos-print-sale-modal__option pos-screen-layout-opt @if($saved_layout === 'rtl') is-active @endif" data-layout="rtl">
                        <span class="pos-print-sale-modal__radio" aria-hidden="true"></span>
                        <span class="pos-print-sale-modal__option-body">
                            <span class="pos-print-sale-modal__option-label">Right to Left</span>
                            <span class="pos-print-sale-modal__option-meta">Cart left · Products right</span>
                        </span>
                        <span class="pos-screen-layout-preview pos-screen-layout-preview--rtl" aria-hidden="true">
                            <span class="pos-screen-layout-preview__cart"></span>
                            <span class="pos-screen-layout-preview__prod"></span>
                        </span>
                    </button>
                </div>
                <p class="pos-print-sale-modal__status" id="pos_screen_settings_status" hidden></p>
            </div>
            <div class="modal-footer pos-print-sale-modal__footer">
                <button type="button" class="btn pos-print-sale-modal__done" data-dismiss="modal">Done</button>
            </div>
        </div>
    </div>
</div>
<script>
(function () {
    if (window.__posScreenSettingBound) {
        return;
    }
    window.__posScreenSettingBound = true;

    var modalEl = document.getElementById('pos_screen_setting_modal');
    var saveTimer = null;

    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function saveUrl() {
        var el = document.getElementById('pos_screen_settings_url');
        if (el && el.value) {
            return el.value;
        }
        var base = (typeof window.base_path !== 'undefined' && window.base_path) ? window.base_path : '';
        return base + '/sells/pos/screen-settings';
    }

    function dbLayout() {
        if (!modalEl) return 'ltr';
        var v = (modalEl.getAttribute('data-saved-layout') || 'ltr').toLowerCase();
        return (v === 'rtl') ? 'rtl' : 'ltr';
    }

    function optionButtons() {
        return document.querySelectorAll('#pos_screen_setting_modal .pos-screen-layout-opt');
    }

    function applyLayoutClass(layout) {
        layout = layout === 'rtl' ? 'rtl' : 'ltr';
        document.body.classList.toggle('pos-layout-rtl', layout === 'rtl');
        document.body.classList.toggle('pos-layout-ltr', layout === 'ltr');
        var shell = document.querySelector('.pos-sale-shell');
        if (shell) {
            shell.classList.toggle('pos-layout-rtl', layout === 'rtl');
            shell.classList.toggle('pos-layout-ltr', layout === 'ltr');
        }
    }

    function setStatus(msg, isError) {
        var el = document.getElementById('pos_screen_settings_status');
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

    function setLayoutUI(layout) {
        layout = layout === 'rtl' ? 'rtl' : 'ltr';
        var btns = optionButtons();
        for (var i = 0; i < btns.length; i++) {
            var btn = btns[i];
            if ((btn.getAttribute('data-layout') || '') === layout) {
                btn.classList.add('is-active');
            } else {
                btn.classList.remove('is-active');
            }
        }
        if (modalEl) {
            modalEl.setAttribute('data-saved-layout', layout);
        }
        var hidden = document.getElementById('pos_screen_layout');
        if (hidden) {
            hidden.value = layout;
        }
        applyLayoutClass(layout);
        return layout;
    }

    function saveToDatabase(layout) {
        var url = saveUrl();
        if (!url) {
            setStatus('Save URL missing', true);
            return;
        }
        setStatus('Saving…', false);

        var body = new FormData();
        body.append('layout', layout);
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
                var layout = data.layout === 'rtl' ? 'rtl' : 'ltr';
                setLayoutUI(layout);
                setStatus('Success', false);
                setTimeout(function () { setStatus('', false); }, 1600);
            })
            .catch(function () {
                setStatus('Could not save — try again', true);
            });
    }

    function queueSave(layout) {
        if (saveTimer) clearTimeout(saveTimer);
        saveTimer = setTimeout(function () {
            saveToDatabase(layout);
        }, 120);
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
                    var layout = setLayoutUI(btn.getAttribute('data-layout'));
                    queueSave(layout);
                });
            })(btns[i]);
        }
    }

    bindOptions();
    setLayoutUI(dbLayout());

    if (typeof jQuery !== 'undefined') {
        jQuery(document)
            .off('shown.bs.modal.posScreenSetting')
            .on('shown.bs.modal.posScreenSetting', '#pos_screen_setting_modal', function () {
                bindOptions();
                setLayoutUI(dbLayout());
            });
    }
})();
</script>
