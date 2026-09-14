{{-- Sell Return / Credit Note receipt — print-friendly --}}
<style>
	.sr-receipt {
		font-family: Arial, Helvetica, sans-serif;
		font-size: 12px;
		color: #111;
		line-height: 1.35;
		width: 100%;
		max-width: 800px;
		margin: 0 auto;
		box-sizing: border-box;
	}
	.sr-receipt * { box-sizing: border-box; }
	.sr-receipt img {
		max-width: 160px !important;
		max-height: 70px !important;
		width: auto !important;
		height: auto !important;
		object-fit: contain;
	}
	.sr-receipt .sr-header {
		display: table;
		width: 100%;
		border-bottom: 2px solid #1f2937;
		padding-bottom: 10px;
		margin-bottom: 12px;
	}
	.sr-receipt .sr-header-left,
	.sr-receipt .sr-header-right {
		display: table-cell;
		vertical-align: top;
		width: 50%;
	}
	.sr-receipt .sr-header-right { text-align: right; }
	.sr-receipt .sr-biz-name {
		font-size: 16px;
		font-weight: 700;
		margin: 4px 0 2px;
	}
	.sr-receipt .sr-title {
		font-size: 18px;
		font-weight: 700;
		letter-spacing: 0.02em;
		margin: 0 0 4px;
		text-transform: uppercase;
	}
	.sr-receipt .sr-meta {
		margin: 0;
		font-size: 12px;
	}
	.sr-receipt .sr-meta strong { font-weight: 700; }
	.sr-receipt .sr-parties {
		display: table;
		width: 100%;
		margin: 0 0 14px;
	}
	.sr-receipt .sr-party {
		display: table-cell;
		vertical-align: top;
		width: 50%;
		padding-right: 12px;
	}
	.sr-receipt .sr-party:last-child {
		padding-right: 0;
		padding-left: 12px;
	}
	.sr-receipt .sr-party-label {
		font-size: 11px;
		font-weight: 700;
		text-transform: uppercase;
		color: #374151;
		margin: 0 0 4px;
		border-bottom: 1px solid #d1d5db;
		padding-bottom: 3px;
	}
	.sr-receipt .sr-table {
		width: 100%;
		border-collapse: collapse;
		margin: 0 0 12px;
	}
	.sr-receipt .sr-table th {
		background: #1f2937;
		color: #fff;
		font-size: 11px;
		text-transform: uppercase;
		padding: 7px 8px;
		border: 1px solid #1f2937;
		text-align: left;
	}
	.sr-receipt .sr-table td {
		padding: 6px 8px;
		border: 1px solid #d1d5db;
		vertical-align: top;
	}
	.sr-receipt .sr-table .num { text-align: center; width: 36px; }
	.sr-receipt .sr-table .qty,
	.sr-receipt .sr-table .money { text-align: right; white-space: nowrap; }
	.sr-receipt .sr-totals-wrap {
		display: table;
		width: 100%;
		page-break-inside: avoid;
	}
	.sr-receipt .sr-sign {
		display: table-cell;
		width: 48%;
		vertical-align: bottom;
		padding-top: 28px;
	}
	.sr-receipt .sr-sign-line {
		border-top: 1px solid #9ca3af;
		width: 200px;
		margin-top: 40px;
		padding-top: 4px;
		font-size: 11px;
	}
	.sr-receipt .sr-totals {
		display: table-cell;
		width: 52%;
		vertical-align: top;
	}
	.sr-receipt .sr-totals table {
		width: 100%;
		border-collapse: collapse;
		margin-left: auto;
	}
	.sr-receipt .sr-totals td {
		padding: 5px 8px;
		border: 1px solid #d1d5db;
	}
	.sr-receipt .sr-totals .sr-total-row td {
		background: #1f2937;
		color: #fff;
		font-weight: 700;
		font-size: 13px;
	}
	.sr-receipt .sr-footer {
		margin-top: 14px;
		font-size: 11px;
		color: #374151;
		border-top: 1px solid #e5e7eb;
		padding-top: 8px;
	}
	.sr-receipt .sr-barcode { text-align: center; margin-top: 10px; }
	.sr-receipt .sr-barcode img {
		max-width: 220px !important;
		max-height: 50px !important;
	}
	@media print {
		.sr-receipt { max-width: none; }
	}
</style>

<div class="sr-receipt">
	@if(!empty($receipt_details->header_text))
		<div class="sr-header-text" style="margin-bottom:8px;font-size:11px;">
			{!! $receipt_details->header_text !!}
		</div>
	@endif

	<div class="sr-header">
		<div class="sr-header-left">
			@if(!empty($receipt_details->logo))
				<img src="{{ $receipt_details->logo }}" alt="">
			@endif
			@if(!empty($receipt_details->display_name))
				<div class="sr-biz-name">{{ $receipt_details->display_name }}</div>
			@endif
			<div>
				@if(!empty($receipt_details->address))
					{!! $receipt_details->address !!}
				@endif
				@if(!empty($receipt_details->contact))
					<br>{!! $receipt_details->contact !!}
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
				@if(!empty($receipt_details->location_custom_fields))
					<br>{!! $receipt_details->location_custom_fields !!}
				@endif
			</div>
		</div>
		<div class="sr-header-right">
			<div class="sr-title">
				@if(!empty($receipt_details->invoice_heading))
					{!! $receipt_details->invoice_heading !!}
				@else
					Credit Note
				@endif
			</div>
			<p class="sr-meta">
				@if(!empty($receipt_details->invoice_no_prefix))
					{!! $receipt_details->invoice_no_prefix !!}
				@endif
				<strong>{{ $receipt_details->invoice_no }}</strong>
			</p>
			@if(!empty($receipt_details->parent_invoice_no))
				<p class="sr-meta">
					@if(!empty($receipt_details->parent_invoice_no_prefix))
						{!! $receipt_details->parent_invoice_no_prefix !!}
					@endif
					{{ $receipt_details->parent_invoice_no }}
				</p>
			@endif
			@if(!empty($receipt_details->date_label))
				<p class="sr-meta">
					{{ $receipt_details->date_label }}
					<strong>{{ $receipt_details->invoice_date }}</strong>
				</p>
			@endif
			@if(!empty($receipt_details->total_paid))
				<p class="sr-meta">
					{!! $receipt_details->total_paid_label !!}
					<strong>{{ $receipt_details->total_paid }}</strong>
				</p>
			@endif
			@if(!empty($receipt_details->total_due))
				<p class="sr-meta">
					{!! $receipt_details->total_due_label !!}
					<strong>{{ $receipt_details->total_due }}</strong>
				</p>
			@endif
		</div>
	</div>

	<div class="sr-parties">
		<div class="sr-party">
			<div class="sr-party-label">{{ $receipt_details->customer_label ?? __('contact.customer') }}</div>
			@if(!empty($receipt_details->customer_name))
				<div><strong>{!! $receipt_details->customer_name !!}</strong></div>
			@endif
			@if(!empty($receipt_details->customer_info))
				<div>{!! $receipt_details->customer_info !!}</div>
			@endif
			@if(!empty($receipt_details->client_id_label))
				<div>{{ $receipt_details->client_id_label }} {{ $receipt_details->client_id }}</div>
			@endif
			@if(!empty($receipt_details->customer_tax_label))
				<div>{{ $receipt_details->customer_tax_label }} {{ $receipt_details->customer_tax_number }}</div>
			@endif
			@if(!empty($receipt_details->customer_custom_fields))
				<div>{!! $receipt_details->customer_custom_fields !!}</div>
			@endif
		</div>
		<div class="sr-party">
			@if(!empty($receipt_details->sub_heading_line1) || !empty($receipt_details->table) || !empty($receipt_details->waiter))
				<div class="sr-party-label">Details</div>
			@endif
			@if(!empty($receipt_details->sub_heading_line1)){{ $receipt_details->sub_heading_line1 }}<br>@endif
			@if(!empty($receipt_details->sub_heading_line2)){{ $receipt_details->sub_heading_line2 }}<br>@endif
			@if(!empty($receipt_details->sub_heading_line3)){{ $receipt_details->sub_heading_line3 }}<br>@endif
			@if(!empty($receipt_details->sub_heading_line4)){{ $receipt_details->sub_heading_line4 }}<br>@endif
			@if(!empty($receipt_details->sub_heading_line5)){{ $receipt_details->sub_heading_line5 }}<br>@endif
			@if(!empty($receipt_details->table_label) || !empty($receipt_details->table))
				{!! $receipt_details->table_label !!} {{ $receipt_details->table }}<br>
			@endif
			@if(!empty($receipt_details->waiter_label) || !empty($receipt_details->waiter))
				{!! $receipt_details->waiter_label !!} {{ $receipt_details->waiter }}
			@endif
		</div>
	</div>

	<table class="sr-table">
		<thead>
			<tr>
				<th class="num">#</th>
				<th>{{ $receipt_details->table_product_label }}</th>
				@if($receipt_details->show_cat_code == 1)
					<th>{{ $receipt_details->cat_code_label }}</th>
				@endif
				<th class="qty">{{ $receipt_details->table_qty_label }}</th>
				<th class="money">{{ $receipt_details->table_unit_price_label }}</th>
				<th class="money">{{ $receipt_details->table_subtotal_label }}</th>
			</tr>
		</thead>
		<tbody>
			@forelse($receipt_details->lines as $line)
				<tr>
					<td class="num">{{ $loop->iteration }}</td>
					<td>
						{{ $line['name'] }} {{ $line['variation'] }}
						@if(!empty($line['sub_sku'])), {{ $line['sub_sku'] }}@endif
						@if(!empty($line['brand'])), {{ $line['brand'] }}@endif
						@if(!empty($line['sell_line_note']))
							<br><small>{{ $line['sell_line_note'] }}</small>
						@endif
					</td>
					@if($receipt_details->show_cat_code == 1)
						<td>{{ $line['cat_code'] ?? '' }}</td>
					@endif
					<td class="qty">{{ $line['quantity'] }} {{ $line['units'] }}</td>
					<td class="money">{{ $line['unit_price_exc_tax'] }}</td>
					<td class="money">{{ $line['line_total'] }}</td>
				</tr>
			@empty
				<tr>
					<td colspan="{{ $receipt_details->show_cat_code == 1 ? 6 : 5 }}" style="text-align:center;padding:16px;">
						—
					</td>
				</tr>
			@endforelse
		</tbody>
	</table>

	<div class="sr-totals-wrap">
		<div class="sr-sign">
			<div class="sr-sign-line">Authorized Signatory</div>
		</div>
		<div class="sr-totals">
			<table>
				<tr>
					<td>{!! $receipt_details->subtotal_label !!}</td>
					<td class="money" style="text-align:right;">{{ $receipt_details->subtotal }}</td>
				</tr>
				@if(!empty($receipt_details->taxes))
					@foreach($receipt_details->taxes as $k => $v)
						<tr>
							<td>{{ $k }}</td>
							<td style="text-align:right;">{{ $v }}</td>
						</tr>
					@endforeach
				@endif
				@if(!empty($receipt_details->discount))
					<tr>
						<td>{!! $receipt_details->discount_label !!}</td>
						<td style="text-align:right;">(-) {{ $receipt_details->discount }}</td>
					</tr>
				@endif
				@if(!empty($receipt_details->group_tax_details))
					@foreach($receipt_details->group_tax_details as $key => $value)
						<tr>
							<td>{!! $key !!}</td>
							<td style="text-align:right;">(+) {{ $value }}</td>
						</tr>
					@endforeach
				@elseif(!empty($receipt_details->tax))
					<tr>
						<td>{!! $receipt_details->tax_label !!}</td>
						<td style="text-align:right;">(+) {{ $receipt_details->tax }}</td>
					</tr>
				@endif
				<tr class="sr-total-row">
					<td>{!! $receipt_details->total_label !!}</td>
					<td style="text-align:right;">{{ $receipt_details->total }}</td>
				</tr>
			</table>
		</div>
	</div>

	@if(!empty($receipt_details->additional_notes))
		<div class="sr-footer">{{ $receipt_details->additional_notes }}</div>
	@endif

	@if(!empty($receipt_details->show_barcode))
		<div class="sr-barcode">
			<img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($receipt_details->invoice_no, 'C128', 2, 30, [39, 48, 54], true) }}" alt="{{ $receipt_details->invoice_no }}">
		</div>
	@endif

	@if(!empty($receipt_details->show_qr_code) && !empty($receipt_details->qr_code_text))
		<div class="sr-barcode">
			<img src="data:image/png;base64,{{ DNS2D::getBarcodePNG($receipt_details->qr_code_text, 'QRCODE', 3, 3, [39, 48, 54]) }}" alt="QR" style="max-height:90px !important;max-width:90px !important;">
		</div>
	@endif

	@if(!empty($receipt_details->footer_text))
		<div class="sr-footer">{!! $receipt_details->footer_text !!}</div>
	@endif
</div>
