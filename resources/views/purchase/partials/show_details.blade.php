@php
  $is_po = ($purchase->type ?? '') === 'purchase_order';
  $title = $is_po ? __('lang_v1.purchase_order') : __('purchase.purchases');
  $custom_labels = json_decode(session('business.custom_labels'), true);
  $logo = !empty($purchase->business->logo)
    ? asset('uploads/business_logos/'.$purchase->business->logo)
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
    $purchase->location->landmark ?? null,
    $purchase->location->city ?? null,
    $purchase->location->state ?? null,
    $purchase->location->country ?? null,
    $purchase->location->zip_code ?? null,
  ]);
  $supplierAddr = $fmtAddress($purchase->contact->contact_address ?? '');
  $total_before_tax = 0;
@endphp

<style type="text/css">
  .pu-wrap {
    font-family: "Segoe UI", "Helvetica Neue", Arial, sans-serif;
    color: #111827;
    font-size: 12px;
    line-height: 1.45;
    width: 100%;
    box-sizing: border-box;
  }
  .pu-wrap * { box-sizing: border-box; }
  .pu-header {
    display: table;
    width: 100%;
    border-bottom: 2px solid #111827;
    padding-bottom: 14px;
    margin-bottom: 16px;
  }
  .pu-header__left, .pu-header__right {
    display: table-cell;
    vertical-align: top;
    width: 50%;
  }
  .pu-header__right { text-align: right; }
  .pu-logo { max-height: 58px; width: auto; margin-bottom: 8px; }
  .pu-brand {
    font-size: 18px;
    font-weight: 700;
    margin: 0 0 4px;
    color: #0f172a;
  }
  .pu-muted { color: #64748b; font-size: 11px; margin: 0; line-height: 1.5; }
  .pu-doc-title {
    font-size: 20px;
    font-weight: 800;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    margin: 0 0 10px;
    color: #0f172a;
  }
  .pu-meta {
    display: inline-block;
    text-align: left;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 10px 14px;
    min-width: 230px;
  }
  .pu-meta-row { display: table; width: 100%; margin: 0 0 4px; }
  .pu-meta-row:last-child { margin-bottom: 0; }
  .pu-meta-label, .pu-meta-value { display: table-cell; vertical-align: top; }
  .pu-meta-label {
    color: #64748b;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    width: 46%;
    padding-right: 8px;
  }
  .pu-meta-value { font-weight: 600; color: #0f172a; text-align: right; }
  .pu-parties {
    display: table;
    width: 100%;
    margin-bottom: 16px;
  }
  .pu-party {
    display: table-cell;
    width: 50%;
    vertical-align: top;
    padding: 12px 14px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #fff;
  }
  .pu-party-spacer { display: table-cell; width: 12px; }
  .pu-party-title {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #64748b;
    margin: 0 0 8px;
    padding-bottom: 6px;
    border-bottom: 1px solid #e2e8f0;
  }
  .pu-party-name {
    font-size: 13px;
    font-weight: 700;
    margin: 0 0 4px;
    color: #0f172a;
  }
  .pu-party-body { margin: 0; color: #334155; font-size: 12px; line-height: 1.5; }
  .pu-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 14px;
  }
  .pu-table thead th {
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
  .pu-table thead th.num, .pu-table tbody td.num { text-align: right; }
  .pu-table tbody td {
    padding: 9px 10px;
    border-bottom: 1px solid #e2e8f0;
    vertical-align: top;
    color: #1e293b;
  }
  .pu-table tbody tr:nth-child(even) td { background: #f8fafc; }
  .pu-item-name { font-weight: 600; color: #0f172a; display: block; }
  .pu-item-meta { font-size: 11px; color: #64748b; }
  .pu-bottom {
    display: table;
    width: 100%;
    margin-top: 8px;
  }
  .pu-bottom__left, .pu-bottom__right {
    display: table-cell;
    vertical-align: top;
    width: 50%;
  }
  .pu-pay {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 12px;
  }
  .pu-pay th {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #64748b;
    padding: 6px 8px;
    border-bottom: 1px solid #e2e8f0;
    text-align: left;
  }
  .pu-pay td {
    padding: 6px 8px;
    border-bottom: 1px solid #f1f5f9;
  }
  .pu-totals {
    width: 100%;
    border-collapse: collapse;
    margin-left: auto;
  }
  .pu-totals th, .pu-totals td {
    padding: 6px 0;
    border-bottom: 1px solid #e2e8f0;
  }
  .pu-totals th { text-align: left; color: #475569; font-weight: 600; }
  .pu-totals td { text-align: right; font-weight: 600; }
  .pu-totals tr.grand th, .pu-totals tr.grand td {
    border-bottom: none;
    padding-top: 10px;
    font-size: 14px;
    color: #0f172a;
  }
  .pu-note {
    margin-top: 10px;
    padding: 10px 12px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #f8fafc;
    min-height: 52px;
  }
  .pu-note strong {
    display: block;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #64748b;
    margin-bottom: 4px;
  }
  .pu-signs {
    display: table;
    width: 100%;
    margin-top: 28px;
    page-break-inside: avoid;
  }
  .pu-sign {
    display: table-cell;
    width: 32%;
    vertical-align: bottom;
    padding-top: 36px;
  }
  .pu-sign + .pu-sign { padding-left: 2%; }
  .pu-sign-line {
    border-top: 1px solid #94a3b8;
    padding-top: 6px;
    font-size: 11px;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }
  .pu-barcode { text-align: center; margin-top: 16px; }
  @media print {
    .pu-wrap { color: #000 !important; }
    .pu-table thead th {
      background: #0f172a !important;
      color: #fff !important;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }
    .pu-table tbody tr:nth-child(even) td,
    .pu-meta, .pu-note {
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }
    .pu-table tbody tr:nth-child(even) td { background: #f8fafc !important; }
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
  <h4 class="modal-title">{{ $is_po ? __('lang_v1.purchase_order_details') : __('purchase.purchase_details') }}
    (<b>@lang('purchase.ref_no'):</b> #{{ e($purchase->ref_no) }})
  </h4>
</div>
@endunless

<div class="modal-body">
  <div class="pu-wrap">
    <div class="pu-header">
      <div class="pu-header__left">
        @if($logo)
          <img src="{{ $logo }}" alt="" class="pu-logo">
        @endif
        <p class="pu-brand">{{ $purchase->business->name }}</p>
        <p class="pu-muted">
          {{ $purchase->location->name }}
          @if(!empty($locParts))
            <br>{{ implode(', ', $locParts) }}
          @endif
          @if(!empty($purchase->business->tax_number_1))
            <br>{{ $purchase->business->tax_label_1 }}: {{ $purchase->business->tax_number_1 }}
          @endif
          @if(!empty($purchase->business->tax_number_2))
            <br>{{ $purchase->business->tax_label_2 }}: {{ $purchase->business->tax_number_2 }}
          @endif
          @if(!empty($purchase->location->mobile))
            <br>@lang('contact.mobile'): {{ $purchase->location->mobile }}
          @endif
          @if(!empty($purchase->location->email))
            <br>{{ $purchase->location->email }}
          @endif
        </p>
      </div>
      <div class="pu-header__right">
        <p class="pu-doc-title">{{ $title }}</p>
        <div class="pu-meta">
          <div class="pu-meta-row">
            <span class="pu-meta-label">@lang('purchase.ref_no')</span>
            <span class="pu-meta-value">#{{ $purchase->ref_no }}</span>
          </div>
          <div class="pu-meta-row">
            <span class="pu-meta-label">@lang('messages.date')</span>
            <span class="pu-meta-value">{{ @format_date($purchase->transaction_date) }}</span>
          </div>
          @if(!empty($purchase->status))
            <div class="pu-meta-row">
              <span class="pu-meta-label">@lang('purchase.purchase_status')</span>
              <span class="pu-meta-value">
                @if($is_po)
                  {{ $po_statuses[$purchase->status]['label'] ?? ucfirst($purchase->status) }}
                @else
                  {{ __('lang_v1.'.$purchase->status) }}
                @endif
              </span>
            </div>
          @endif
          @if(!empty($purchase->payment_status) && !$is_po)
            <div class="pu-meta-row">
              <span class="pu-meta-label">@lang('purchase.payment_status')</span>
              <span class="pu-meta-value">{{ __('lang_v1.'.$purchase->payment_status) }}</span>
            </div>
          @endif
          @if(!empty($purchase_order_nos))
            <div class="pu-meta-row">
              <span class="pu-meta-label">@lang('restaurant.order_no')</span>
              <span class="pu-meta-value">{{ $purchase_order_nos }}</span>
            </div>
          @endif
        </div>
      </div>
    </div>

    <div class="pu-parties">
      <div class="pu-party">
        <p class="pu-party-title">@lang('purchase.supplier')</p>
        <p class="pu-party-name">{{ optional($purchase->contact)->supplier_business_name ?: optional($purchase->contact)->name }}</p>
        <p class="pu-party-body">
          @if(!empty(optional($purchase->contact)->supplier_business_name) && !empty(optional($purchase->contact)->name))
            {{ $purchase->contact->name }}<br>
          @endif
          @if($supplierAddr)
            {!! $supplierAddr !!}
          @endif
          @if(!empty($purchase->contact->tax_number))
            <br><strong>@lang('contact.tax_no'):</strong> {{ $purchase->contact->tax_number }}
          @endif
          @if(!empty($purchase->contact->mobile))
            <br><strong>@lang('contact.mobile'):</strong> {{ $purchase->contact->mobile }}
          @endif
          @if(!empty($purchase->contact->email))
            <br>{{ $purchase->contact->email }}
          @endif
        </p>
        @if($purchase->document_path)
          <a href="{{ $purchase->document_path }}" download="{{ $purchase->document_name }}" class="tw-dw-btn tw-dw-btn-success tw-text-white tw-dw-btn-xs no-print" style="margin-top:8px;">
            <i class="fa fa-download"></i> {{ __('purchase.download_document') }}
          </a>
        @endif
      </div>
      <div class="pu-party-spacer"></div>
      <div class="pu-party">
        <p class="pu-party-title">@lang('business.business') / @lang('lang_v1.delivery_at')</p>
        <p class="pu-party-name">{{ $purchase->location->name }}</p>
        <p class="pu-party-body">
          @if(!empty($locParts))
            {{ implode(', ', $locParts) }}
          @endif
          @if($is_po && !empty($purchase->shipping_address()))
            <br>{{ $purchase->shipping_address() }}
          @elseif(!empty($purchase->shipping_details))
            <br>{{ $purchase->shipping_details }}
          @endif
          @if(!empty($purchase->delivered_to))
            <br><strong>@lang('lang_v1.delivered_to'):</strong> {{ $purchase->delivered_to }}
          @endif
          @if(!empty($custom_labels['purchase']['custom_field_1']) && !empty($purchase->custom_field_1))
            <br><strong>{{ $custom_labels['purchase']['custom_field_1'] }}:</strong> {{ $purchase->custom_field_1 }}
          @endif
          @if(!empty($custom_labels['purchase']['custom_field_2']) && !empty($purchase->custom_field_2))
            <br><strong>{{ $custom_labels['purchase']['custom_field_2'] }}:</strong> {{ $purchase->custom_field_2 }}
          @endif
        </p>
      </div>
    </div>

    <table class="pu-table">
      <thead>
        <tr>
          <th style="width:4%;">#</th>
          <th>@lang('product.product_name')</th>
          <th>@lang('product.sku')</th>
          @if($is_po)
            <th class="num">@lang('lang_v1.quantity_remaining')</th>
          @endif
          <th class="num">{{ $is_po ? __('lang_v1.order_quantity') : __('purchase.purchase_quantity') }}</th>
          <th class="num">@lang('lang_v1.unit_cost_before_discount')</th>
          <th class="num">@lang('lang_v1.discount_percent')</th>
          <th class="num">@lang('purchase.unit_cost_after_tax')</th>
          @if(!$is_po && session('business.enable_lot_number'))
            <th>@lang('lang_v1.lot_number')</th>
          @endif
          <th class="num">@lang('sale.subtotal')</th>
        </tr>
      </thead>
      <tbody>
        @forelse($purchase->purchase_lines as $purchase_line)
            @php
            $line_sub = $purchase_line->purchase_price_inc_tax * $purchase_line->quantity;
            $total_before_tax += ($purchase_line->quantity * $purchase_line->purchase_price);
            $unit_name = '';
            if (!empty($purchase_line->sub_unit)) {
                $unit_name = $purchase_line->sub_unit->actual_name;
            } elseif (!empty(optional($purchase_line->product)->unit)) {
                $unit_name = $purchase_line->product->unit->actual_name;
            }
            $sku = optional($purchase_line->product)->type === 'variable'
              ? optional($purchase_line->variations)->sub_sku
              : optional($purchase_line->product)->sku;
          @endphp
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>
              <span class="pu-item-name">
                {{ optional($purchase_line->product)->name }}
                @if(optional($purchase_line->product)->type === 'variable')
                  {{ optional(optional($purchase_line->variations)->product_variation)->name }}
                  {{ optional($purchase_line->variations)->name }}
                @endif
              </span>
            </td>
            <td class="pu-item-meta">{{ $sku }}</td>
            @if($is_po)
              <td class="num">
                <span class="display_currency" data-is_quantity="true" data-currency_symbol="false">{{ $purchase_line->quantity - $purchase_line->po_quantity_purchased }}</span>
              </td>
            @endif
            <td class="num">
              <span class="display_currency" data-is_quantity="true" data-currency_symbol="false">{{ $purchase_line->quantity }}</span>
              {{ $unit_name }}
            </td>
            <td class="num"><span class="display_currency" data-currency_symbol="true">{{ $purchase_line->pp_without_discount }}</span></td>
            <td class="num">{{ @num_format($purchase_line->discount_percent) }}%</td>
            <td class="num"><span class="display_currency" data-currency_symbol="true">{{ $purchase_line->purchase_price_inc_tax }}</span></td>
            @if(!$is_po && session('business.enable_lot_number'))
              <td>{{ $purchase_line->lot_number }}</td>
            @endif
            <td class="num"><span class="display_currency" data-currency_symbol="true">{{ $line_sub }}</span></td>
          </tr>
        @empty
          <tr>
            <td colspan="9" style="text-align:center;color:#64748b;padding:20px;">—</td>
          </tr>
        @endforelse
      </tbody>
    </table>

    <div class="pu-bottom">
      <div class="pu-bottom__left" style="padding-right:16px;">
        @if(!$is_po)
          <strong style="font-size:10px;text-transform:uppercase;letter-spacing:.06em;color:#64748b;">@lang('sale.payment_info')</strong>
          <table class="pu-pay">
            <thead>
              <tr>
                <th>@lang('messages.date')</th>
                <th>@lang('purchase.ref_no')</th>
                <th class="num">@lang('sale.amount')</th>
                <th>@lang('sale.payment_mode')</th>
              </tr>
            </thead>
            <tbody>
              @forelse($purchase->payment_lines as $payment_line)
                <tr>
                  <td>{{ @format_date($payment_line->paid_on) }}</td>
                  <td>{{ $payment_line->payment_ref_no ?: '—' }}</td>
                  <td class="num"><span class="display_currency" data-currency_symbol="true">{{ $payment_line->amount }}</span></td>
                  <td>{{ $payment_methods[$payment_line->method] ?? $payment_line->method }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" style="color:#94a3b8;">@lang('purchase.no_payments')</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        @endif
        <div class="pu-note">
          <strong>@lang('purchase.additional_notes')</strong>
          {{ $purchase->additional_notes ?: '—' }}
        </div>
      </div>
      <div class="pu-bottom__right">
        <table class="pu-totals">
          <tr>
            <th>@lang('purchase.net_total_amount')</th>
            <td><span class="display_currency" data-currency_symbol="true">{{ $total_before_tax }}</span></td>
          </tr>
          <tr>
            <th>@lang('purchase.discount')
              @if($purchase->discount_type == 'percentage')
                ({{ $purchase->discount_amount }}%)
              @endif
            </th>
            <td>
              <span class="display_currency" data-currency_symbol="true">
                @if($purchase->discount_type == 'percentage')
                  {{ $purchase->discount_amount * $total_before_tax / 100 }}
                @else
                  {{ $purchase->discount_amount }}
                @endif
              </span>
            </td>
          </tr>
          @if(!empty($purchase_taxes))
            @foreach($purchase_taxes as $k => $v)
              <tr>
                <th>@lang('purchase.purchase_tax') ({{ $k }})</th>
                <td><span class="display_currency" data-currency_symbol="true">{{ $v }}</span></td>
              </tr>
            @endforeach
          @endif
          @if(!empty($purchase->shipping_charges))
            <tr>
              <th>@lang('purchase.additional_shipping_charges')</th>
              <td><span class="display_currency" data-currency_symbol="true">{{ $purchase->shipping_charges }}</span></td>
            </tr>
          @endif
          @if(!empty($purchase->additional_expense_value_1) && !empty($purchase->additional_expense_key_1))
            <tr>
              <th>{{ $purchase->additional_expense_key_1 }}</th>
              <td><span class="display_currency" data-currency_symbol="true">{{ $purchase->additional_expense_value_1 }}</span></td>
            </tr>
          @endif
          @if(!empty($purchase->additional_expense_value_2) && !empty($purchase->additional_expense_key_2))
            <tr>
              <th>{{ $purchase->additional_expense_key_2 }}</th>
              <td><span class="display_currency" data-currency_symbol="true">{{ $purchase->additional_expense_value_2 }}</span></td>
            </tr>
          @endif
          <tr class="grand">
            <th>@lang('purchase.purchase_total')</th>
            <td><span class="display_currency" data-currency_symbol="true">{{ $purchase->final_total }}</span></td>
          </tr>
        </table>
      </div>
    </div>

    <div class="pu-signs">
      <div class="pu-sign"><div class="pu-sign-line">@lang('lang_v1.prepared_by')</div></div>
      <div class="pu-sign"><div class="pu-sign-line">@lang('lang_v1.checked_by')</div></div>
      <div class="pu-sign"><div class="pu-sign-line">@lang('lang_v1.authorized_signatory')</div></div>
    </div>

    <div class="pu-barcode">
      <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($purchase->ref_no, 'C128', 2, 36, [15, 23, 42], true) }}" alt="{{ $purchase->ref_no }}">
    </div>
  </div>

  @if(!empty($activities))
    <div class="row no-print" style="margin-top:16px;">
      <div class="col-md-12">
        <strong>{{ __('lang_v1.activities') }}:</strong><br>
        @includeIf('activity_log.activities', ['activity_type' => 'purchase'])
      </div>
    </div>
  @endif
</div>
