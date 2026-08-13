{{-- Modern packing slip (print-friendly) --}}
@php
	$lines = $receipt_details->lines ?? [];
	$itemCount = count($lines);
	$totalQty = 0;
	foreach ($lines as $line) {
		$totalQty += (float) str_replace(',', '', (string) ($line['quantity'] ?? 0));
	}

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

	$shipName = $receipt_details->delivered_to
		?? $receipt_details->customer_name
		?? '';
	$shipAddr = $fmtAddress($receipt_details->shipping_address ?? '');
	$billAddr = $fmtAddress($receipt_details->customer_info ?? '');
@endphp

<style type="text/css">
	.ps-wrap {
		font-family: "Segoe UI", "Helvetica Neue", Arial, sans-serif;
		color: #111827;
		font-size: 12px;
		line-height: 1.45;
		width: 100%;
		max-width: 100%;
		box-sizing: border-box;
	}
	.ps-wrap * { box-sizing: border-box; }
	.ps-header {
		display: table;
		width: 100%;
		border-bottom: 2px solid #111827;
		padding-bottom: 14px;
		margin-bottom: 18px;
	}
	.ps-header__left,
	.ps-header__right {
		display: table-cell;
		vertical-align: top;
		width: 50%;
	}
	.ps-header__right { text-align: right; }
	.ps-logo {
		max-height: 64px;
		width: auto;
		margin-bottom: 8px;
	}
	.ps-brand {
		font-size: 20px;
		font-weight: 700;
		letter-spacing: -0.02em;
		margin: 0 0 4px;
		color: #0f172a;
	}
	.ps-muted {
		color: #64748b;
		font-size: 11px;
		margin: 0;
		line-height: 1.5;
	}
	.ps-doc-title {
		font-size: 22px;
		font-weight: 800;
		letter-spacing: 0.04em;
		text-transform: uppercase;
		margin: 0 0 10px;
		color: #0f172a;
	}
	.ps-meta {
		display: inline-block;
		text-align: left;
		background: #f8fafc;
		border: 1px solid #e2e8f0;
		border-radius: 8px;
		padding: 10px 14px;
		min-width: 210px;
	}
	.ps-meta-row {
		display: table;
		width: 100%;
		margin: 0 0 4px;
	}
	.ps-meta-row:last-child { margin-bottom: 0; }
	.ps-meta-label,
	.ps-meta-value {
		display: table-cell;
		vertical-align: top;
	}
	.ps-meta-label {
		color: #64748b;
		font-size: 10px;
		text-transform: uppercase;
		letter-spacing: 0.05em;
		width: 42%;
		padding-right: 8px;
	}
	.ps-meta-value {
		font-weight: 600;
		color: #0f172a;
		text-align: right;
	}
	.ps-parties {
		display: table;
		width: 100%;
		margin-bottom: 18px;
		border-spacing: 0;
	}
	.ps-party {
		display: table-cell;
		width: 50%;
		vertical-align: top;
		padding: 12px 14px;
		border: 1px solid #e2e8f0;
		border-radius: 8px;
		background: #fff;
	}
	.ps-party + .ps-party {
		padding-left: 14px;
	}
	.ps-party-spacer {
		display: table-cell;
		width: 12px;
	}
	.ps-party-title {
		font-size: 10px;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: 0.08em;
		color: #64748b;
		margin: 0 0 8px;
		padding-bottom: 6px;
		border-bottom: 1px solid #e2e8f0;
	}
	.ps-party-name {
		font-size: 13px;
		font-weight: 700;
		margin: 0 0 4px;
		color: #0f172a;
	}
	.ps-party-body {
		margin: 0;
		color: #334155;
		font-size: 12px;
		line-height: 1.5;
	}
	.ps-table {
		width: 100%;
		border-collapse: collapse;
		margin-bottom: 16px;
	}
	.ps-table thead th {
		background: #0f172a;
		color: #fff;
		font-size: 10px;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: 0.06em;
		padding: 10px 12px;
		text-align: left;
		border: none;
	}
	.ps-table thead th.ps-col-num,
	.ps-table thead th.ps-col-qty,
	.ps-table tbody td.ps-col-num,
	.ps-table tbody td.ps-col-qty {
		text-align: center;
	}
	.ps-table thead th.ps-col-num { width: 8%; }
	.ps-table thead th.ps-col-qty { width: 18%; }
	.ps-table tbody td {
		padding: 11px 12px;
		border-bottom: 1px solid #e2e8f0;
		vertical-align: top;
		color: #1e293b;
	}
	.ps-table tbody tr:nth-child(even) td {
		background: #f8fafc;
	}
	.ps-item-name {
		font-weight: 600;
		color: #0f172a;
		display: block;
		margin-bottom: 2px;
	}
	.ps-item-meta {
		font-size: 11px;
		color: #64748b;
	}
	.ps-summary {
		display: table;
		width: 100%;
		margin-bottom: 22px;
	}
	.ps-summary__left,
	.ps-summary__right {
		display: table-cell;
		vertical-align: top;
		width: 50%;
	}
	.ps-summary__right { text-align: right; }
	.ps-chip {
		display: inline-block;
		background: #f1f5f9;
		border: 1px solid #e2e8f0;
		border-radius: 6px;
		padding: 8px 12px;
		margin-right: 8px;
		margin-bottom: 6px;
		font-size: 11px;
	}
	.ps-chip strong {
		display: block;
		font-size: 14px;
		color: #0f172a;
		margin-top: 2px;
	}
	.ps-chip span {
		color: #64748b;
		text-transform: uppercase;
		letter-spacing: 0.05em;
		font-size: 9px;
		font-weight: 700;
	}
	.ps-signs {
		display: table;
		width: 100%;
		margin-top: 28px;
		page-break-inside: avoid;
	}
	.ps-sign {
		display: table-cell;
		width: 48%;
		vertical-align: bottom;
		padding-top: 36px;
	}
	.ps-sign + .ps-sign {
		padding-left: 4%;
	}
	.ps-sign-line {
		border-top: 1px solid #94a3b8;
		padding-top: 6px;
		font-size: 11px;
		color: #64748b;
		text-transform: uppercase;
		letter-spacing: 0.05em;
	}
	.ps-footer {
		margin-top: 18px;
		padding-top: 12px;
		border-top: 1px solid #e2e8f0;
		color: #64748b;
		font-size: 11px;
	}
	.ps-barcode {
		text-align: center;
		margin-top: 16px;
	}
	@media print {
		.ps-wrap { color: #000 !important; }
		.ps-table thead th {
			background: #0f172a !important;
			color: #fff !important;
			-webkit-print-color-adjust: exact;
			print-color-adjust: exact;
		}
		.ps-table tbody tr:nth-child(even) td {
			background: #f8fafc !important;
			-webkit-print-color-adjust: exact;
			print-color-adjust: exact;
		}
		.ps-meta, .ps-chip {
			-webkit-print-color-adjust: exact;
			print-color-adjust: exact;
		}
	}
</style>

<div class="ps-wrap">
	<div class="ps-header">
		<div class="ps-header__left">
			@if(!empty($receipt_details->logo))
				<img src="{{ $receipt_details->logo }}" alt="" class="ps-logo">
			@endif
			@if(!empty($receipt_details->display_name))
				<p class="ps-brand">{{ $receipt_details->display_name }}</p>
			@endif
			<p class="ps-muted">
				@if(!empty($receipt_details->address))
					{!! $fmtAddress($receipt_details->address) !!}
				@endif
				@if(!empty($receipt_details->contact))
					<br>{!! strip_tags($receipt_details->contact, '<br>') !!}
				@endif
				@if(!empty($receipt_details->website))
					<br>{{ $receipt_details->website }}
				@endif
				@if(!empty($receipt_details->tax_info1))
					<br>{{ $receipt_details->tax_label1 }} {{ $receipt_details->tax_info1 }}
				@endif
				@if(!empty($receipt_details->tax_info2))
					<br>{{ $receipt_details->tax_label2 }} {{ $receipt_details->tax_info2 }}
				@endif
			</p>
		</div>
		<div class="ps-header__right">
			<p class="ps-doc-title">@lang('lang_v1.packing_slip')</p>
			<div class="ps-meta">
				<div class="ps-meta-row">
					<span class="ps-meta-label">{{ !empty($receipt_details->invoice_no_prefix) ? strip_tags($receipt_details->invoice_no_prefix) : __('sale.invoice_no') }}</span>
					<span class="ps-meta-value">{{ $receipt_details->invoice_no }}</span>
				</div>
				@if(!empty($receipt_details->date_label))
					<div class="ps-meta-row">
						<span class="ps-meta-label">{{ $receipt_details->date_label }}</span>
						<span class="ps-meta-value">{{ $receipt_details->invoice_date }}</span>
					</div>
				@endif
				@if(!empty($receipt_details->shipping_status))
					<div class="ps-meta-row">
						<span class="ps-meta-label">@lang('lang_v1.shipping_status')</span>
						<span class="ps-meta-value">{{ ucfirst($receipt_details->shipping_status) }}</span>
					</div>
				@endif
			</div>
		</div>
	</div>

	<div class="ps-parties">
		<div class="ps-party">
			<p class="ps-party-title">{{ $receipt_details->customer_label ?? __('contact.customer') }}</p>
			@if(!empty($receipt_details->customer_name))
				<p class="ps-party-name">{{ $receipt_details->customer_name }}</p>
			@endif
			<p class="ps-party-body">
				@if(!empty($billAddr))
					{!! $billAddr !!}
				@endif
				@if(!empty($receipt_details->client_id_label) && !empty($receipt_details->client_id))
					<br><strong>{{ $receipt_details->client_id_label }}</strong> {{ $receipt_details->client_id }}
				@endif
				@if(!empty($receipt_details->customer_tax_label) && !empty($receipt_details->customer_tax_number))
					<br><strong>{{ $receipt_details->customer_tax_label }}</strong> {{ $receipt_details->customer_tax_number }}
				@endif
				@if(!empty($receipt_details->sales_person_label) && !empty($receipt_details->sales_person))
					<br><strong>{{ $receipt_details->sales_person_label }}</strong> {{ $receipt_details->sales_person }}
				@endif
			</p>
		</div>
		<div class="ps-party-spacer"></div>
		<div class="ps-party">
			<p class="ps-party-title">@lang('lang_v1.shipping_address')</p>
			@if(!empty($shipName))
				<p class="ps-party-name">{{ $shipName }}</p>
			@endif
			<p class="ps-party-body">
				@if(!empty($shipAddr))
					{!! $shipAddr !!}
				@endif
				@if(!empty($receipt_details->shipping_custom_field_1_label))
					<br><strong>{!! strip_tags($receipt_details->shipping_custom_field_1_label) !!}:</strong> {{ $receipt_details->shipping_custom_field_1_value ?? '' }}
				@endif
				@if(!empty($receipt_details->shipping_custom_field_2_label))
					<br><strong>{!! strip_tags($receipt_details->shipping_custom_field_2_label) !!}:</strong> {{ $receipt_details->shipping_custom_field_2_value ?? '' }}
				@endif
				@if(!empty($receipt_details->shipping_custom_field_3_label))
					<br><strong>{!! strip_tags($receipt_details->shipping_custom_field_3_label) !!}:</strong> {{ $receipt_details->shipping_custom_field_3_value ?? '' }}
				@endif
				@if(!empty($receipt_details->shipping_custom_field_4_label))
					<br><strong>{!! strip_tags($receipt_details->shipping_custom_field_4_label) !!}:</strong> {{ $receipt_details->shipping_custom_field_4_value ?? '' }}
				@endif
				@if(!empty($receipt_details->shipping_custom_field_5_label))
					<br><strong>{!! strip_tags($receipt_details->shipping_custom_field_5_label) !!}:</strong> {{ $receipt_details->shipping_custom_field_5_value ?? '' }}
				@endif
			</p>
		</div>
	</div>

	<table class="ps-table">
		<thead>
			<tr>
				<th class="ps-col-num">#</th>
				<th>{{ $receipt_details->table_product_label ?? __('sale.product') }}</th>
				<th class="ps-col-qty">{{ $receipt_details->table_qty_label ?? __('lang_v1.quantity') }}</th>
			</tr>
		</thead>
		<tbody>
			@forelse($lines as $line)
				<tr>
					<td class="ps-col-num">{{ $loop->iteration }}</td>
					<td>
						<span class="ps-item-name">
							{{ $line['name'] }}
							@if(!empty($line['product_variation']) || !empty($line['variation']))
								{{ trim(($line['product_variation'] ?? '').' '.($line['variation'] ?? '')) }}
							@endif
						</span>
						<span class="ps-item-meta">
							@if(!empty($line['sub_sku']))SKU: {{ $line['sub_sku'] }}@endif
							@if(!empty($line['brand']))@if(!empty($line['sub_sku'])) · @endif{{ $line['brand'] }}@endif
							@if(!empty($line['product_custom_fields']))@if(!empty($line['sub_sku']) || !empty($line['brand'])) · @endif{{ $line['product_custom_fields'] }}@endif
							@if(!empty($line['sell_line_note']))<br>{!! $line['sell_line_note'] !!}@endif
							@if(!empty($line['lot_number']))<br>{{ $line['lot_number_label'] }}: {{ $line['lot_number'] }}@endif
							@if(!empty($line['product_expiry'])) · {{ $line['product_expiry_label'] }}: {{ $line['product_expiry'] }}@endif
						</span>
					</td>
					<td class="ps-col-qty">
						<strong>{{ $line['quantity'] }}</strong>
						@if(!empty($line['units'])) {{ $line['units'] }}@endif
					</td>
				</tr>
				@if(!empty($line['modifiers']))
					@foreach($line['modifiers'] as $modifier)
						<tr>
							<td class="ps-col-num"></td>
							<td>
								<span class="ps-item-meta">
									+ {{ $modifier['name'] }} {{ $modifier['variation'] ?? '' }}
									@if(!empty($modifier['sub_sku'])) ({{ $modifier['sub_sku'] }})@endif
								</span>
							</td>
							<td class="ps-col-qty">
								{{ $modifier['quantity'] }}
								@if(!empty($modifier['units'])) {{ $modifier['units'] }}@endif
							</td>
						</tr>
					@endforeach
				@endif
			@empty
				<tr>
					<td colspan="3" style="text-align:center; color:#64748b; padding:24px;">
						@lang('lang_v1.no_order_items')
					</td>
				</tr>
			@endforelse
		</tbody>
	</table>

	<div class="ps-summary">
		<div class="ps-summary__left">
			<div class="ps-chip">
				<span>@lang('lang_v1.total_items')</span>
				<strong>{{ @num_format($itemCount) }}</strong>
			</div>
			<div class="ps-chip">
				<span>{{ $receipt_details->table_qty_label ?? __('lang_v1.quantity') }}</span>
				<strong>{{ @num_format($totalQty) }}</strong>
			</div>
		</div>
	</div>

	<div class="ps-signs">
		<div class="ps-sign">
			<div class="ps-sign-line">@lang('lang_v1.authorized_signatory')</div>
		</div>
		<div class="ps-sign">
			<div class="ps-sign-line">@lang('lang_v1.received_by')</div>
		</div>
	</div>

	@if(!empty($receipt_details->show_barcode))
		<div class="ps-barcode">
			<img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($receipt_details->invoice_no, 'C128', 2, 40, [15, 23, 42], true) }}" alt="{{ $receipt_details->invoice_no }}">
		</div>
	@endif

	@if(!empty($receipt_details->footer_text))
		<div class="ps-footer">
			{!! $receipt_details->footer_text !!}
		</div>
	@endif
</div>
