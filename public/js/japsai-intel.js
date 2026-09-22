(function (w, d) {
    if (w.__japsAiIntelV2) {
        return;
    }
    w.__japsAiIntelV2 = true;

    function reportBox() {
        var modal = d.getElementById('ai-modal');
        var box = (modal && modal.querySelector('#ai-intel-report')) || d.getElementById('ai-intel-report');
        if (box) {
            return box;
        }
        var pane = (modal && modal.querySelector('#ai-pane-intel')) || d.getElementById('ai-pane-intel');
        if (!pane) {
            return null;
        }
        box = d.createElement('div');
        box.id = 'ai-intel-report';
        box.className = 'ai-intel-report';
        pane.appendChild(box);
        return box;
    }

    function urlFrom(el) {
        if (!el) {
            return '';
        }
        var form = el.tagName === 'FORM' ? el : (el.closest ? el.closest('form.japs-intel-form') : null);
        if (form) {
            var action = form.getAttribute('action') || '';
            var section = '';
            var inp = form.querySelector('input[name="section"]');
            if (inp) {
                section = inp.value;
            }
            var sep = action.indexOf('?') >= 0 ? '&' : '?';
            return action + sep + 'embed=1&section=' + encodeURIComponent(section);
        }
        return el.getAttribute('data-intel-url') || el.getAttribute('href') || '';
    }

    w.japsAiShowIntel = function (url) {
        var box = reportBox();
        if (!box || !url) {
            return false;
        }
        box.innerHTML = '<p class="text-muted" style="padding:12px 0;">Loading report…</p>';
        var xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'text/html');
        xhr.onreadystatechange = function () {
            if (xhr.readyState !== 4) {
                return;
            }
            var html = xhr.responseText || '';
            if (xhr.status >= 400 && !html) {
                box.innerHTML = '<p class="text-danger">Could not load this report (' + xhr.status + ').</p>';
                return;
            }
            var wrap = d.createElement('div');
            wrap.innerHTML = html;
            var body = wrap.querySelector('body');
            box.innerHTML = (body ? body.innerHTML : html) || '<p class="text-muted">No rows for this report.</p>';
        };
        xhr.send();
        return false;
    };

    function handle(el, e) {
        var url = urlFrom(el);
        if (!url) {
            return;
        }
        if (e) {
            e.preventDefault();
            e.stopPropagation();
            if (e.stopImmediatePropagation) {
                e.stopImmediatePropagation();
            }
        }
        w.japsAiShowIntel(url);
    }

    d.addEventListener('click', function (e) {
        var btn = e.target && e.target.closest && e.target.closest('.japs-intel-go, .ai-intel-btn, .ai-intel-link');
        if (!btn) {
            return;
        }
        handle(btn, e);
    }, true);

    d.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form || !form.classList || !form.classList.contains('japs-intel-form')) {
            return;
        }
        handle(form, e);
    }, true);
})(window, document);
