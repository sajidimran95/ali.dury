<script type="text/javascript">
(function () {
  var barcodeUrl = @json(url('/users/barcode-image'));
  var savedCode = @json($user->barcode_switch_id ?? '');

  function notify(msg, ok) {
    if (typeof toastr !== 'undefined') {
      var silent = { onShown: function () {}, timeOut: 2500, extendedTimeOut: 800 };
      if (ok) toastr.success(msg, '', silent);
      else toastr.error(msg, '', silent);
      return;
    }
    alert(msg);
  }

  function makeSwitchId() {
    var hex = '';
    if (window.crypto && crypto.getRandomValues) {
      var buf = new Uint8Array(6);
      crypto.getRandomValues(buf);
      for (var i = 0; i < buf.length; i++) {
        hex += ('0' + buf[i].toString(16)).slice(-2);
      }
    } else {
      hex = Math.random().toString(16).slice(2, 14);
    }
    return 'usi:' + hex.slice(0, 12);
  }

  function barcodeEnabled() {
    var el = document.getElementById('barcode_login');
    if (!el) return false;
    if (el.checked) return true;
    var $el = $('#barcode_login');
    return $el.parent().hasClass('checked') || $el.closest('.icheckbox_square-blue').hasClass('checked');
  }

  function currentSwitchId() {
    return ($('#barcode_switch_id').val() || $('#ls_barcode_id').text() || savedCode || '').toString().trim();
  }

  function refreshUserBarcode() {
    var $wrap = $('#ls_user_barcode_wrap');
    if (!$wrap.length) return;
    var on = barcodeEnabled();
    var always = String($wrap.attr('data-always') || '') === '1';
    var hasToggle = !!document.getElementById('barcode_login');
    if (!on && !always) {
      $wrap.hide();
      return;
    }
    var code = currentSwitchId();
    if (!code && hasToggle) {
      code = makeSwitchId();
      $('#barcode_switch_id').val(code);
      savedCode = code;
    }
    if (!code) {
      $wrap.hide();
      return;
    }
    $wrap.attr('data-code', code).show();
    $('#ls_barcode_id').text(code);
    var next = barcodeUrl + '?text=' + encodeURIComponent(code);
    var $img = $('#ls_barcode_img');
    var cur = ($img.attr('src') || '').split('&t=')[0];
    if (cur.indexOf(encodeURIComponent(code)) === -1) {
      $img.attr('src', next + '&t=' + Date.now());
    }
  }

  function copyId() {
    var code = currentSwitchId();
    if (!code) {
      notify('No ID to copy', false);
      return;
    }
    var done = function () {
      notify('ID copied: ' + code, true);
    };
    var fail = function () {
      try {
        var ta = document.createElement('textarea');
        ta.value = code;
        ta.setAttribute('readonly', '');
        ta.style.position = 'fixed';
        ta.style.top = '0';
        ta.style.left = '0';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.focus();
        ta.select();
        ta.setSelectionRange(0, code.length);
        var ok = document.execCommand('copy');
        document.body.removeChild(ta);
        if (ok) done();
        else notify('Could not copy ID', false);
      } catch (err) {
        notify('Could not copy ID', false);
      }
    };
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(code).then(done).catch(fail);
    } else {
      fail();
    }
  }

  function printBarcode() {
    var code = currentSwitchId();
    var img = document.getElementById('ls_barcode_img');
    if (!code) {
      notify('No barcode ID to print', false);
      return;
    }
    if (!img || !img.getAttribute('src')) {
      notify('Barcode image is not ready', false);
      return;
    }
    var src = img.src;
    var html =
      '<!doctype html><html><head><title>' + code + '</title>' +
      '<style>body{font-family:Arial,sans-serif;padding:28px;color:#111}' +
      'h1{font-size:14px;font-weight:600;color:#6b7280;margin:0 0 8px}' +
      'p{font-size:16px;margin:0 0 16px}' +
      'img{border:2px solid #111;padding:10px;height:90px;background:#fff}' +
      '</style></head><body>' +
      '<h1>Your quick switch ID</h1><p>' + code + '</p>' +
      '<img id="b" src="' + src + '" alt="' + code + '">' +
      '</body></html>';

    var iframe = document.getElementById('ls_barcode_print_frame');
    if (!iframe) {
      iframe = document.createElement('iframe');
      iframe.id = 'ls_barcode_print_frame';
      iframe.setAttribute('aria-hidden', 'true');
      iframe.style.cssText = 'position:fixed;left:-10000px;top:0;width:800px;height:600px;border:0;';
      document.body.appendChild(iframe);
    }
    var win = iframe.contentWindow;
    var doc = win.document;
    doc.open();
    doc.write(html);
    doc.close();

    var printed = false;
    function goPrint() {
      if (printed) return;
      printed = true;
      try {
        win.focus();
        win.print();
        notify('Print dialog opened', true);
      } catch (err) {
        notify('Print failed. Allow pop-ups and try again.', false);
      }
    }
    var pimg = doc.getElementById('b');
    if (pimg) {
      if (pimg.complete) {
        setTimeout(goPrint, 150);
      } else {
        pimg.onload = function () { setTimeout(goPrint, 150); };
        pimg.onerror = function () { notify('Barcode image failed to load', false); };
        setTimeout(goPrint, 1200);
      }
    } else {
      setTimeout(goPrint, 200);
    }
  }

  $(document).off('click.lsBarcodeCopy').on('click.lsBarcodeCopy', '#lsCopyId', function (e) {
    e.preventDefault();
    e.stopImmediatePropagation();
    copyId();
    return false;
  });

  $(document).off('click.lsBarcodePrint').on('click.lsBarcodePrint', '#lsPrintBarcode', function (e) {
    e.preventDefault();
    e.stopImmediatePropagation();
    printBarcode();
    return false;
  });

  $(document).on('ifChecked ifUnchecked', '#barcode_login', refreshUserBarcode);
  $(document).on('change click', '#barcode_login', refreshUserBarcode);
  $(document).ready(function () {
    setTimeout(refreshUserBarcode, 0);
    setTimeout(refreshUserBarcode, 250);
  });
})();
</script>
