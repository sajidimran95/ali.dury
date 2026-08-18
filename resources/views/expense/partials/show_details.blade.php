@php
  $is_refund = ($expense->type ?? '') === 'expense_refund';
  $title = $is_refund ? __('expense.expense_refund_voucher') : __('expense.expense_invoice');
  $logo = !empty($expense->business->logo)
    ? asset('uploads/business_logos/'.$expense->business->logo)
    : null;

  $fmtAddress = function ($text) {
    if ($text === null || $text === '') {
      return '';
    }
    $text = html_entity_decode((string) $text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = preg_replace('/<\s*br\s*\/?\s*>/i', "\n", $text);
    $text = strip_tags($text);
    $parts = array_values(array_filter(array_map(function ($l) {
      return trim($l, " \t,");
    }, preg_split("/\r\n|\r|\n/", $text)), function ($l) {
      return $l !== '';
    }));
    $clean = [];
    foreach ($parts as $line) {
      if (end($clean) === $line) {
        continue;
      }
      $clean[] = $line;
    }

    return implode('<br>', array_map('e', $clean));
  };

  $locParts = array_filter([
    $expense->location->landmark ?? null,
    $expense->location->city ?? null,
    $expense->location->state ?? null,
    $expense->location->country ?? null,
    $expense->location->zip_code ?? null,
  ]);
  $payeeAddr = $fmtAddress(optional($expense->contact)->contact_address ?? '');
  $payeeName = optional($expense->contact)->supplier_business_name
    ?: optional($expense->contact)->name;
  $expenseForName = optional($expense->expenseFor)->user_full_name;
  $categoryName = optional($expense->expenseCategory)->name;
  $subCategoryName = optional($expense->expenseSubCategory)->name;
  $lineDescription = trim((string) ($expense->additional_notes ?: ''));
  if ($lineDescription === '') {
    $lineDescription = $categoryName ?: $title;
  }
  $total_before_tax = $expense->total_before_tax ?: ($expense->final_total - ($expense->tax_amount ?: 0));
  $sign = $is_refund ? -1 : 1;
@endphp

<style type="text/css">
  .ev-wrap {
    font-family: "Segoe UI", "Helvetica Neue", Arial, sans-serif;
    color: #111827;
    font-size: 12px;
    line-height: 1.45;
    width: 100%;
    box-sizing: border-box;
  }
  .ev-wrap * { box-sizing: border-box; }
  .ev-header {
    display: table;
    width: 100%;
    border-bottom: 2px solid #111827;
    padding-bottom: 14px;
    margin-bottom: 16px;
  }
  .ev-header__left, .ev-header__right {
    display: table-cell;
    vertical-align: top;
    width: 50%;
  }
  .ev-header__right { text-align: right; }
  .ev-logo { max-height: 58px; width: auto; margin-bottom: 8px; }
  .ev-brand {
    font-size: 18px;
    font-weight: 700;
    margin: 0 0 4px;
    color: #0f172a;
  }
  .ev-muted { color: #64748b; font-size: 11px; margin: 0; line-height: 1.5; }
  .ev-doc-title {
    font-size: 20px;
    font-weight: 800;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    margin: 0 0 10px;
    color: #0f172a;
  }
  .ev-meta {
    display: inline-block;
    text-align: left;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 10px 14px;
    min-width: 230px;
  }
  .ev-meta-row { display: table; width: 100%; margin: 0 0 4px; }
  .ev-meta-row:last-child { margin-bottom: 0; }
  .ev-meta-label, .ev-meta-value { display: table-cell; vertical-align: top; }
  .ev-meta-label {
    color: #64748b;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    width: 46%;
    padding-right: 8px;
  }
  .ev-meta-value { font-weight: 600; color: #0f172a; text-align: right; }
  .ev-parties {
    display: table;
    width: 100%;
    margin-bottom: 16px;
  }
  .ev-party {
    display: table-cell;
    width: 50%;
    vertical-align: top;
    padding: 12px 14px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #fff;
  }
  .ev-party-spacer { display: table-cell; width: 12px; }
  .ev-party-title {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #64748b;
    margin: 0 0 8px;
    padding-bottom: 6px;
    border-bottom: 1px solid #e2e8f0;
  }
  .ev-party-name {
    font-size: 13px;
    font-weight: 700;
    margin: 0 0 4px;
    color: #0f172a;
  }
  .ev-party-body { margin: 0; color: #334155; font-size: 12px; line-height: 1.5; }
  .ev-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 14px;
  }
  .ev-table thead th {
    background: #0f172a;
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    padding: 9px 10px;
    text-align: left;
    border: none;
  }
  .ev-table thead th.num, .ev-table tbody td.num { text-align: right; }
  .ev-table tbody td {
    padding: 9px 10px;
    border-bottom: 1px solid #e2e8f0;
    vertical-align: top;
    color: #1e293b;
  }
  .ev-table tbody tr:nth-child(even) td { background: #f8fafc; }
  .ev-item-name { font-weight: 600; color: #0f172a; display: block; }
  .ev-item-meta { font-size: 11px; color: #64748b; }
  .ev-bottom {
    display: table;
    width: 100%;
    margin-top: 8px;
  }
  .ev-bottom__left, .ev-bottom__right {
    display: table-cell;
    vertical-align: top;
    width: 50%;
  }
  .ev-pay {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 12px;
  }
  .ev-pay th {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #64748b;
    padding: 6px 8px;
    border-bottom: 1px solid #e2e8f0;
    text-align: left;
  }
  .ev-pay td {
    padding: 6px 8px;
    border-bottom: 1px solid #f1f5f9;
  }
  .ev-pay th.num, .ev-pay td.num { text-align: right; }
  .ev-totals {
    width: 100%;
    border-collapse: collapse;
    margin-left: auto;
  }
  .ev-totals th, .ev-totals td {
    padding: 6px 0;
    border-bottom: 1px solid #e2e8f0;
  }
  .ev-totals th { text-align: left; color: #475569; font-weight: 600; }
  .ev-totals td { text-align: right; font-weight: 600; }
  .ev-totals tr.grand th, .ev-totals tr.grand td {
    border-bottom: none;
    padding-top: 10px;
    font-size: 14px;
    color: #0f172a;
  }
  .ev-note {
    margin-top: 10px;
    padding: 10px 12px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #f8fafc;
    min-height: 52px;
  }
  .ev-note strong {
    display: block;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #64748b;
    margin-bottom: 4px;
  }
  .ev-signs {
    display: table;
    width: 100%;
    margin-top: 28px;
    page-break-inside: avoid;
  }
  .ev-sign {
    display: table-cell;
    width: 32%;
    vertical-align: bottom;
    padding-top: 36px;
  }
  .ev-sign + .ev-sign { padding-left: 2%; }
  .ev-sign-line {
    border-top: 1px solid #94a3b8;
    padding-top: 6px;
    font-size: 11px;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }
  .ev-barcode { text-align: center; margin-top: 16px; }
  @media print {
    .ev-wrap { color: #000 !important; }
    .ev-table thead th {
      background: #0f172a !important;
      color: #fff !important;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }
    .ev-table tbody tr:nth-child(even) td,
    .ev-meta, .ev-note {
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }
    .ev-table tbody tr:nth-child(even) td { background: #f8fafc !important; }
    .modal-header, button.close, .close { display: none !important; }
  }
  #receipt_section .modal-header,
  #receipt_section button.close,
  #receipt_section .close {
    display: none !important;
  }
</style>

@unless(!empty($for_print))
<div class="modal-header no-print">
  <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
  <h4 class="modal-title">{{ $title }}
    (<b>@lang('purchase.ref_no'):</b> #{{ e($expense->ref_no) }})
  </h4>
</div>
@endunless

<div class="modal-body">
  <div class="ev-wrap">
    <div class="ev-header">
      <div class="ev-header__left">
        @if($logo)
          <img src="{{ $logo }}" alt="" class="ev-logo">
        @endif
        <p class="ev-brand">{{ $expense->business->name }}</p>
        <p class="ev-muted">
          {{ $expense->location->name }}
          @if(!empty($locParts))
            <br>{{ implode(', ', $locParts) }}
          @endif
          @if(!empty($expense->business->tax_number_1))
            <br>{{ $expense->business->tax_label_1 }}: {{ $expense->business->tax_number_1 }}
          @endif
          @if(!empty($expense->business->tax_number_2))
            <br>{{ $expense->business->tax_label_2 }}: {{ $expense->business->tax_number_2 }}
          @endif
          @if(!empty($expense->location->mobile))
            <br>@lang('contact.mobile'): {{ $expense->location->mobile }}
          @endif
          @if(!empty($expense->location->email))
            <br>{{ $expense->location->email }}
          @endif
        </p>
      </div>
      <div class="ev-header__right">
        <p class="ev-doc-title">{{ $title }}</p>
        <div class="ev-meta">
          <div class="ev-meta-row">
            <span class="ev-meta-label">@lang('purchase.ref_no')</span>
            <span class="ev-meta-value">#{{ $expense->ref_no }}</span>
          </div>
          <div class="ev-meta-row">
            <span class="ev-meta-label">@lang('messages.date')</span>
            <span class="ev-meta-value">{{ @format_date($expense->transaction_date) }}</span>
          </div>
          @if(!empty($expense->payment_status))
            <div class="ev-meta-row">
              <span class="ev-meta-label">@lang('purchase.payment_status')</span>
              <span class="ev-meta-value">{{ __('lang_v1.'.$expense->payment_status) }}</span>
            </div>
          @endif
          @if($is_refund)
            <div class="ev-meta-row">
              <span class="ev-meta-label">@lang('lang_v1.refund')</span>
              <span class="ev-meta-value">@lang('messages.yes')</span>
            </div>
          @endif
        </div>
      </div>
    </div>

    <div class="ev-parties">
      <div class="ev-party">
        <p class="ev-party-title">@lang('expense.payee')</p>
        @if($payeeName)
          <p class="ev-party-name">{{ $payeeName }}</p>
          <p class="ev-party-body">
            @if(!empty(optional($expense->contact)->supplier_business_name) && !empty(optional($expense->contact)->name))
              {{ $expense->contact->name }}<br>
            @endif
            @if($payeeAddr)
              {!! $payeeAddr !!}
            @endif
            @if(!empty($expense->contact->tax_number))
              <br><strong>@lang('contact.tax_no'):</strong> {{ $expense->contact->tax_number }}
            @endif
            @if(!empty($expense->contact->mobile))
              <br><strong>@lang('contact.mobile'):</strong> {{ $expense->contact->mobile }}
            @endif
            @if(!empty($expense->contact->email))
              <br>{{ $expense->contact->email }}
            @endif
          </p>
        @elseif($expenseForName)
          <p class="ev-party-name">{{ $expenseForName }}</p>
          <p class="ev-party-body">@lang('expense.expense_for')</p>
        @else
          <p class="ev-party-name">—</p>
        @endif
        @if($expense->document_path)
          <a href="{{ $expense->document_path }}" download="{{ $expense->document_name }}" class="tw-dw-btn tw-dw-btn-success tw-text-white tw-dw-btn-xs no-print" style="margin-top:8px;">
            <i class="fa fa-download"></i> {{ __('purchase.download_document') }}
          </a>
        @endif
      </div>
      <div class="ev-party-spacer"></div>
      <div class="ev-party">
        <p class="ev-party-title">@lang('business.location')</p>
        <p class="ev-party-name">{{ $expense->location->name }}</p>
        <p class="ev-party-body">
          @if(!empty($locParts))
            {{ implode(', ', $locParts) }}
          @endif
          @if($expenseForName && $payeeName)
            <br><strong>@lang('expense.expense_for'):</strong> {{ $expenseForName }}
          @endif
          @if($categoryName)
            <br><strong>@lang('expense.expense_category'):</strong> {{ $categoryName }}
            @if($subCategoryName)
              / {{ $subCategoryName }}
            @endif
          @endif
          @if(!empty(optional($expense->sales_person)->user_full_name))
            <br><strong>@lang('lang_v1.added_by'):</strong> {{ $expense->sales_person->user_full_name }}
          @endif
        </p>
      </div>
    </div>

    <table class="ev-table">
      <thead>
        <tr>
          <th style="width:4%;">#</th>
          <th>@lang('expense.particulars')</th>
          <th>@lang('expense.expense_category')</th>
          <th class="num">@lang('purchase.net_total_amount')</th>
          <th class="num">@lang('product.tax')</th>
          <th class="num">@lang('sale.total_amount')</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>1</td>
          <td>
            <span class="ev-item-name">{{ $lineDescription }}</span>
          </td>
          <td>
            {{ $categoryName ?: '—' }}
            @if($subCategoryName)
              <span class="ev-item-meta"><br>{{ $subCategoryName }}</span>
            @endif
          </td>
          <td class="num"><span class="display_currency" data-currency_symbol="true">{{ $sign * $total_before_tax }}</span></td>
          <td class="num"><span class="display_currency" data-currency_symbol="true">{{ $sign * ($expense->tax_amount ?: 0) }}</span></td>
          <td class="num"><span class="display_currency" data-currency_symbol="true">{{ $sign * $expense->final_total }}</span></td>
        </tr>
      </tbody>
    </table>

    <div class="ev-bottom">
      <div class="ev-bottom__left" style="padding-right:16px;">
        <strong style="font-size:10px;text-transform:uppercase;letter-spacing:.06em;color:#64748b;">@lang('sale.payment_info')</strong>
        <table class="ev-pay">
          <thead>
            <tr>
              <th>@lang('messages.date')</th>
              <th>@lang('purchase.ref_no')</th>
              <th class="num">@lang('sale.amount')</th>
              <th>@lang('sale.payment_mode')</th>
            </tr>
          </thead>
          <tbody>
            @forelse($expense->payment_lines as $payment_line)
              <tr>
                <td>{{ @format_date($payment_line->paid_on) }}</td>
                <td>{{ $payment_line->payment_ref_no ?: '—' }}</td>
                <td class="num"><span class="display_currency" data-currency_symbol="true">{{ $sign * $payment_line->amount }}</span></td>
                <td>{{ $payment_methods[$payment_line->method] ?? $payment_line->method }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="4" style="color:#94a3b8;">@lang('purchase.no_payments')</td>
              </tr>
            @endforelse
          </tbody>
        </table>
        <div class="ev-note">
          <strong>@lang('expense.expense_note')</strong>
          {{ $expense->additional_notes ?: '—' }}
        </div>
      </div>
      <div class="ev-bottom__right">
        <table class="ev-totals">
          <tr>
            <th>@lang('purchase.net_total_amount')</th>
            <td><span class="display_currency" data-currency_symbol="true">{{ $sign * $total_before_tax }}</span></td>
          </tr>
          @if(!empty($expense_taxes))
            @foreach($expense_taxes as $k => $v)
              <tr>
                <th>@lang('product.tax') ({{ $k }})</th>
                <td><span class="display_currency" data-currency_symbol="true">{{ $sign * $v }}</span></td>
              </tr>
            @endforeach
          @elseif(!empty($expense->tax_amount))
            <tr>
              <th>@lang('product.tax')</th>
              <td><span class="display_currency" data-currency_symbol="true">{{ $sign * $expense->tax_amount }}</span></td>
            </tr>
          @endif
          <tr class="grand">
            <th>@lang('sale.total_amount')</th>
            <td><span class="display_currency" data-currency_symbol="true">{{ $sign * $expense->final_total }}</span></td>
          </tr>
          <tr>
            <th>@lang('sale.total_paid')</th>
            <td><span class="display_currency" data-currency_symbol="true">{{ $sign * $amount_paid }}</span></td>
          </tr>
          <tr>
            <th>@lang('purchase.payment_due')</th>
            <td><span class="display_currency" data-currency_symbol="true">{{ $sign * $payment_due }}</span></td>
          </tr>
        </table>
      </div>
    </div>

    <div class="ev-signs">
      <div class="ev-sign"><div class="ev-sign-line">@lang('lang_v1.prepared_by')</div></div>
      <div class="ev-sign"><div class="ev-sign-line">@lang('lang_v1.checked_by')</div></div>
      <div class="ev-sign"><div class="ev-sign-line">@lang('lang_v1.authorized_signatory')</div></div>
    </div>

    @if(!empty($expense->ref_no))
      <div class="ev-barcode">
        <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($expense->ref_no, 'C128', 2, 36, [15, 23, 42], true) }}" alt="{{ $expense->ref_no }}">
      </div>
    @endif
  </div>
</div>
