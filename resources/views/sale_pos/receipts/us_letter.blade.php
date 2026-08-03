{{-- US Letter wholesale invoice — structure matched to reference (no paper color) --}}
@php
	$fmtHtml = function ($text) {
		if ($text === null || $text === '') {
			return '';
		}
		$text = (string) $text;
		// Decode accidental double-escaped breaks, then normalize <br>
		$text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
		if (strip_tags($text) !== $text) {
			// Already HTML (trusted system fields / contact address builder)
			$text = preg_replace('/<\s*br\s*\/?\s*>/i', "\n", $text);
			$text = strip_tags($text);
		}
		$text = preg_replace("/\r\n|\r|\n/", "\n", $text);
		// Collapse "Name, \nName" style noise
		$lines = array_values(array_filter(array_map('trim', explode("\n", $text)), function ($l) {
			return $l !== '' && $l !== ',';
		}));
		// Drop leading comma junk from implode patterns
		$lines = array_map(function ($l) {
			return trim($l, " \t,");
		}, $lines);
		$lines = array_values(array_filter($lines));
		// Unique consecutive duplicates
		$clean = [];
		foreach ($lines as $line) {
			if (end($clean) === $line) {
				continue;
			}
			// skip if only a comma-joined copy of previous
			$clean[] = $line;
		}
		return implode("\n", $clean);
	};

	$soldToBody = '';
	if (!empty($receipt_details->customer_name)) {
		$soldToBody = $receipt_details->customer_name;
	}
	$addrExtra = $fmtHtml($receipt_details->customer_info_address ?? '');
	// customer_info_address already includes name sometimes — rebuild carefully
	if (empty($addrExtra) && !empty($receipt_details->customer_info)) {
		$addrExtra = $fmtHtml($receipt_details->customer_info);
	}
	// Remove name line if already at top
	if ($soldToBody !== '' && $addrExtra !== '') {
		$addrLines = explode("\n", $addrExtra);
		$addrLines = array_values(array_filter($addrLines, function ($l) use ($soldToBody) {
			return strcasecmp(trim($l), trim($soldToBody)) !== 0
				&& stripos($l, 'mobile') === false; // mobile shown separately
		}));
		// Allow mobile later from field
		$addrExtra = implode("\n", $addrLines);
	}
	// Prefer structured mobile
	$mobileLine = !empty($receipt_details->customer_mobile) ? $receipt_details->customer_mobile : '';
	// From customer_info, re-extract non-duplicate lines if soldTo empty
	if ($soldToBody === '' && $addrExtra !== '') {
		$parts = explode("\n", $addrExtra);
		$soldToBody = array_shift($parts) ?: '';
		$addrExtra = implode("\n", $parts);
	}

	$shipToBody = '';
	if (!empty($receipt_details->delivered_to)) {
		$shipToBody = $receipt_details->delivered_to;
	} elseif (!empty($receipt_details->customer_name)) {
		$shipToBody = $receipt_details->customer_name;
	}
	$shipExtra = '';
	if (!empty($receipt_details->shipping_address)) {
		$shipExtra = $fmtHtml($receipt_details->shipping_address);
	} elseif (!empty($receipt_details->customer_info_address)) {
		$shipExtra = $fmtHtml($receipt_details->customer_info_address);
	} elseif (!empty($receipt_details->customer_info)) {
		$shipExtra = $fmtHtml($receipt_details->customer_info);
	}
	if ($shipToBody !== '' && $shipExtra !== '') {
		$shipLines = explode("\n", $shipExtra);
		$shipLines = array_values(array_filter($shipLines, function ($l) use ($shipToBody) {
			return strcasecmp(trim($l), trim($shipToBody)) !== 0
				&& stripos($l, 'mobile') === false;
		}));
		$shipExtra = implode("\n", $shipLines);
	}

	$due_date_label = !empty($receipt_details->due_date_label) ? $receipt_details->due_date_label : 'Due Date';
	$prev_bal_label = !empty($receipt_details->total_previous_due_label)
		? trim(str_replace(':', '', $receipt_details->total_previous_due_label))
		: 'Previous Due Balance';
	$total_bal_label = !empty($receipt_details->all_bal_label)
		? trim(str_replace(':', '', $receipt_details->all_bal_label))
		: 'Total Due Balance';

	// Layout labels for totals (keep same design / always show discount rows)
	$lbl = function ($text, $fallback = '') {
		if ($text === null || $text === '' || $text === false) {
			return $fallback;
		}
		return trim(str_replace(':', '', strip_tags((string) $text)));
	};

	// Product table — fixed US Letter columns
	$prod_label = $lbl($receipt_details->table_product_label ?? null, 'Product Name / Description');
	$qty_label = $lbl($receipt_details->table_qty_label ?? null, 'Qty');
	$price_label = $lbl($receipt_details->table_unit_price_label ?? null, 'Price');
	$amt_label = $lbl($receipt_details->table_subtotal_label ?? null, 'Amount');
	$show_item_disc_col = true;
	$item_disc_label = $lbl($receipt_details->item_discount_label ?? null, 'Discount');
	$show_sold_price = true;
	$sold_price_label = $lbl($receipt_details->discounted_unit_price_label ?? null, 'Sold Price');

	// Totals labels — fixed line order for US Letter bottom totals
	$qty_total_label = 'Total Quantity';
	$subtotal_label = $lbl($receipt_details->subtotal_label ?? null, 'Subtotal');
	$ship_label = 'Shipping Charges';
	$line_discount_label = 'Line Item (D/C)';
	// Discount label keeps % when percentage (from receipt details)
	$discount_label_html = !empty($receipt_details->discount_label)
		? $receipt_details->discount_label
		: 'Discount';
	// Tax label keeps rate name e.g. Tax (VAT@10%)
	$tax_total_label_html = !empty($receipt_details->tax_label)
		? $receipt_details->tax_label
		: 'Tax:';
	$tax_col_label = $lbl($receipt_details->tax_label ?? null, 'Tax');
	if ($tax_col_label === '' || $tax_col_label === '(') {
		$tax_col_label = 'Tax';
	}
	$show_tax_column = !empty($receipt_details->enable_inline_tax);
	$total_label = $lbl($receipt_details->total_label ?? null, 'Total');
	$paid_label = $lbl($receipt_details->total_paid_label ?? null, 'Total Paid');
	$due_label = $lbl($receipt_details->total_due_label ?? null, 'Total Due');

	$sym = $receipt_details->currency_symbol ?? '';
	$zero_money = $sym !== '' ? ($sym.' 0.00') : '0.00';
@endphp
<style type="text/css">
	@media print {
		/* Zero margins hide browser chrome headers (date / title) & footers when possible */
		@page {
			size: letter portrait;
			margin: 0;
		}
		html, body {
			margin: 0 !important;
			padding: 0 !important;
			background: #fff !important;
		}
		.usl-wrap {
			width: 100% !important;
			max-width: none !important;
			min-height: 11in;
			box-sizing: border-box !important;
			padding: 0.4in 0.45in 0.5in 0.45in !important;
			display: flex !important;
			flex-direction: column !important;
			position: relative;
		}
		/* Footer thank-you + page number at physical page bottom */
		.usl-foot {
			margin-top: auto !important;
			padding-top: 12px !important;
		}
		.usl-page {
			margin-top: 10px !important;
			margin-bottom: 0 !important;
			padding-top: 8px !important;
			border-top: 1px solid #ccc !important;
			position: relative;
			bottom: 0;
		}
	}
	.usl-wrap {
		color: #111 !important;
		background: #fff !important;
		font-family: Arial, Helvetica, sans-serif;
		font-size: 10.5px;
		line-height: 1.32;
		width: 100%;
		max-width: 8.5in;
		margin: 0 auto;
		display: flex;
		flex-direction: column;
		min-height: 10.5in;
		box-sizing: border-box;
		padding: 0;
	}
	.usl-wrap > *:not(.usl-foot):not(.usl-page) {
		flex-shrink: 0;
	}
	.usl-wrap table { width: 100%; border-collapse: collapse; }
	.usl-wrap td, .usl-wrap th { vertical-align: top; }
	/* Header */
	.usl-hdr td { padding: 0; }
	.usl-biz {
		width: 48%;
		font-size: 11px;
		line-height: 1.4;
		color: #111;
	}
	.usl-biz .logo {
		max-height: 48px;
		max-width: 160px;
		width: auto;
		display: block;
		margin-bottom: 3px;
	}
	.usl-biz .name { font-weight: 700; font-size: 12px; }
	.usl-inv {
		width: 52%;
		text-align: right;
	}
	.usl-inv .title {
		font-size: 22px;
		font-weight: 700;
		letter-spacing: 0.4px;
		text-transform: uppercase;
		color: #222;
		margin: 0 0 3px 0;
		line-height: 1.15;
	}
	.usl-inv .barcode {
		margin: 2px 0 6px 0;
		text-align: right;
	}
	.usl-inv .barcode img {
		display: inline-block;
		height: 34px;
		max-width: 200px;
	}
	.usl-inv .meta {
		font-size: 11px;
		line-height: 1.45;
		color: #111;
	}
	/* Parties — SOLD TO left, SHIP TO right */
	.usl-parties {
		margin-top: 12px;
		margin-bottom: 8px;
		width: 100%;
		table-layout: fixed;
	}
	.usl-parties > tbody > tr > td {
		width: 50%;
		vertical-align: top;
		padding: 0;
	}
	.usl-parties .sold {
		text-align: left;
		padding-right: 16px;
	}
	.usl-parties .ship {
		text-align: left;
		padding-left: 16px;
	}
	.usl-parties .lbl {
		font-weight: 700;
		text-transform: uppercase;
		font-size: 11px;
		margin-bottom: 3px;
		color: #111;
	}
	.usl-parties .body {
		font-size: 11px;
		line-height: 1.4;
	}
	.usl-parties .body .nm { font-weight: 600; }
	/* Meta strip — label row same grey bg as item header */
	.usl-meta {
		width: 100%;
		border-collapse: collapse;
		border: none !important;
		margin: 8px 0 12px 0;
		table-layout: fixed;
	}
	.usl-meta td {
		padding: 0;
		border: none !important;
		font-size: 10px;
		vertical-align: top;
		background: transparent !important;
	}
	.usl-meta tr.usl-meta-labels td {
		background: #e6e6e6 !important;
		border-top: 1px solid #666 !important;
		border-bottom: 1px solid #666 !important;
		padding: 5px 6px;
		font-weight: 700;
		text-transform: uppercase;
		font-size: 9px;
		color: #222;
		-webkit-print-color-adjust: exact;
		print-color-adjust: exact;
	}
	.usl-meta tr.usl-meta-values td {
		padding: 4px 6px 2px 6px;
		font-size: 11px;
		color: #111;
		word-break: break-word;
		background: transparent !important;
		border: none !important;
	}
	/* Items table — header bar only; product rows without cell box lines (match reference open lines) */
	.usl-items {
		width: 100%;
		margin-top: 2px;
		border-collapse: collapse;
		table-layout: fixed;
		border: 0;
	}
	.usl-items thead th {
		background: #e6e6e6 !important;
		border: 0;
		border-top: 1px solid #666;
		border-bottom: 1px solid #666;
		padding: 6px 4px;
		font-size: 10px;
		font-weight: 700;
		text-transform: none;
		text-align: left;
		-webkit-print-color-adjust: exact;
		print-color-adjust: exact;
	}
	.usl-items thead th.r { text-align: right; }
	.usl-items tbody td {
		border: 0;
		border-bottom: 1px solid #ddd;
		padding: 7px 5px;
		font-size: 11px;
		vertical-align: top;
		background: #fff;
	}
	.usl-items tbody tr:last-child td {
		border-bottom: 1px solid #666;
	}
	.usl-items .r { text-align: right; white-space: nowrap; }
	.usl-items .prod { font-weight: 600; }
	.usl-items .muted { font-weight: 400; color: #333; }
	/* Lower split */
	.usl-lower { margin-top: 12px; width: 100%; border-collapse: collapse; }
	.usl-lower .ship {
		width: 48%;
		padding-right: 16px;
		font-size: 11px;
		line-height: 1.55;
		vertical-align: top;
	}
	.usl-lower .tots {
		width: 52%;
		padding-left: 8px;
		vertical-align: top;
	}
	/* Totals — each row has a bottom line (ref wholesale style) */
	.usl-tots {
		width: 100%;
		max-width: 300px;
		margin-left: auto;
		border-collapse: collapse;
		border: 0;
	}
	.usl-tots tr.tot-row td {
		padding: 5px 4px;
		font-size: 11.5px;
		line-height: 1.3;
		border: 0;
		border-bottom: 1px solid #111;
		background: transparent;
		vertical-align: middle;
	}
	.usl-tots tr.tot-row:last-child td {
		border-bottom: 1px solid #111;
	}
	.usl-tots .lab {
		text-align: left;
		padding-right: 12px;
		white-space: nowrap;
		color: #111;
		width: 62%;
	}
	.usl-tots .val {
		text-align: right;
		white-space: nowrap;
		color: #111;
		width: 38%;
		font-variant-numeric: tabular-nums;
	}
	/* Stronger rule before grand total block (ref) */
	.usl-tots tr.tot-rule td {
		padding: 0;
		height: 0;
		border: 0;
		border-bottom: 1.5px solid #111;
		line-height: 0;
		font-size: 0;
	}
	.usl-tots tr.grand td {
		font-weight: 700;
		font-size: 12.5px;
		padding-top: 6px;
		padding-bottom: 6px;
		border-bottom: 1px solid #111;
	}
	.usl-tots tr.grand:first-of-type td {
		border-top: 1.5px solid #111;
	}
	.usl-tots .words td {
		font-size: 10px;
		font-weight: 400;
		color: #333;
		padding-top: 1px;
		padding-bottom: 4px;
		text-align: right;
		border: 0;
	}
	.usl-amount-words {
		text-align: left;
		font-size: 11px;
		color: #333;
		margin-top: 6px;
		font-style: italic;
	}
	.usl-credit {
		text-align: left;
		margin-top: 10px;
		font-size: 11px;
	}
	/* Dark balance bar */
	.usl-bal {
		width: 100%;
		background: #333 !important;
		margin-top: 6px;
		-webkit-print-color-adjust: exact;
		print-color-adjust: exact;
	}
	.usl-bal td {
		padding: 8px 12px;
		font-size: 12px;
		font-weight: 700;
		color: #fff !important;
		background: #333 !important;
		-webkit-print-color-adjust: exact;
		print-color-adjust: exact;
	}
	.usl-bal .l { text-align: left; }
	.usl-bal .r { text-align: right; }
	/* Footer */
	/* Signature stays with invoice body (not pushed to bottom) */
	.usl-sign-row {
		width: 100%;
		margin-top: 16px;
		flex-shrink: 0;
	}
	.usl-sign-row td { font-size: 10px; vertical-align: bottom; }
	.usl-sign-row .sig { width: 100%; text-align: right; }
	.usl-sign-row .sig-line {
		border-bottom: 1px solid #000;
		height: 26px;
		margin: 0 0 3px auto;
		width: 28%;
	}
	.usl-sign-row .sig-lbl {
		font-size: 10px;
		text-align: center;
		width: 28%;
		margin-left: auto;
	}
	/* Thank-you footer text only — pushed to page bottom */
	.usl-foot {
		width: 100%;
		margin-top: auto;
		padding-top: 16px;
		flex-shrink: 0;
	}
	.usl-foot td { font-size: 12px; vertical-align: middle; }
	.usl-foot .thx {
		width: 100%;
		text-align: center;
		font-size: 12px;
		font-weight: 600;
		padding-bottom: 4px;
		line-height: 1.4;
	}
	.usl-page {
		margin-top: 10px;
		padding-top: 8px;
		border-top: 1px solid #ccc;
		font-size: 10px;
		color: #333;
		width: 100%;
		flex-shrink: 0;
	}
	.usl-page td {
		vertical-align: middle;
		padding: 4px 0 0 0;
	}
	.usl-page .pg-left { text-align: left; font-weight: 600; }
	.usl-page .pg-right { text-align: right; }
	.usl-pay { margin-top: 4px; font-size: 10.5px; }
	.usl-pay td { padding: 1px 6px 1px 0; }
	.usl-notes { margin-top: 8px; font-size: 10px; white-space: pre-line; }
</style>

<div class="usl-wrap">
	{{-- ========== HEADER ========== --}}
	<table class="usl-hdr">
		<tr>
			<td class="usl-biz">
				@if(empty($receipt_details->letter_head))
					@if(!empty($receipt_details->logo))
						<img class="logo" src="{{ $receipt_details->logo }}" alt="">
					@endif
					@if(!empty($receipt_details->header_text))
						<div>{!! $receipt_details->header_text !!}</div>
					@endif
					@if(!empty($receipt_details->display_name))
						<div class="name">{{ $receipt_details->display_name }}</div>
					@endif
					@if(!empty($receipt_details->address))
						@php $bizAddr = $fmtHtml($receipt_details->address); @endphp
						@if($bizAddr !== '')
							<div style="white-space:pre-line;">{{ $bizAddr }}</div>
						@endif
					@endif
					@if(!empty($receipt_details->location_mobile))
						<div>{{ $receipt_details->location_mobile }}</div>
					@elseif(!empty($receipt_details->contact))
						@php $c = $fmtHtml($receipt_details->contact); @endphp
						@if($c !== '')
							<div style="white-space:pre-line;">{{ $c }}</div>
						@endif
					@endif
					@if(!empty($receipt_details->location_alternate_number))
						<div>{{ $receipt_details->location_alternate_number }}</div>
					@endif
					@if(!empty($receipt_details->location_email))
						<div>{{ $receipt_details->location_email }}</div>
					@endif
					@if(!empty($receipt_details->website))
						<div>{{ $receipt_details->website }}</div>
					@endif
					@if(!empty($receipt_details->location_custom_fields))
						<div>{{ $receipt_details->location_custom_fields }}</div>
					@endif
					@foreach([1,2,3,4,5] as $n)
						@php $sh = 'sub_heading_line'.$n; @endphp
						@if(!empty($receipt_details->$sh))
							<div>{{ $receipt_details->$sh }}</div>
						@endif
					@endforeach
					@if(!empty($receipt_details->tax_info1) || !empty($receipt_details->tax_info2))
						{{-- tax ids also appear in meta bar when present --}}
					@endif
				@else
					<img style="width:100%;max-height:80px;object-fit:contain;object-position:left;" src="{{ $receipt_details->letter_head }}" alt="">
				@endif
			</td>
			<td class="usl-inv">
				<div class="title">
					@if(!empty($receipt_details->invoice_heading))
						{!! strip_tags($receipt_details->invoice_heading) !!}
					@else
						INVOICE
					@endif
					@if(!empty($receipt_details->invoice_no))
						: {{ $receipt_details->invoice_no }}
					@endif
				</div>
				@if(!empty($receipt_details->show_barcode))
					<div class="barcode">
						<img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($receipt_details->invoice_no, 'C128', 2, 40, array(0, 0, 0), false) }}" alt="barcode">
					</div>
				@endif
				<div class="meta">
					@if(!empty($receipt_details->date_label))
						<div><strong>{{ $receipt_details->date_label }}:</strong> {{ $receipt_details->invoice_date }}</div>
					@endif
					@if(!empty($receipt_details->due_date) || !empty($receipt_details->due_date_label))
						<div><strong>{{ $due_date_label }}:</strong> {{ $receipt_details->due_date ?? $receipt_details->invoice_date }}</div>
					@elseif(!empty($receipt_details->invoice_date))
						{{-- Reference always shows due date line; fall back to invoice date when due date empty --}}
						<div><strong>Due Date:</strong> {{ $receipt_details->invoice_date }}</div>
					@endif
					@if(!empty($receipt_details->parent_invoice_no))
						<div>
							<strong>{!! $receipt_details->parent_invoice_no_prefix ?? '' !!}</strong>
							{{ $receipt_details->parent_invoice_no }}
						</div>
					@endif
				</div>
			</td>
		</tr>
	</table>

	{{-- ========== SOLD TO (left) / SHIP TO (right) ========== --}}
	<table class="usl-parties">
		<tr>
			<td class="sold">
				<div class="lbl">SOLD TO:</div>
				<div class="body">
					@if($soldToBody !== '')
						<div class="nm">{{ $soldToBody }}</div>
					@endif
					@foreach(array_filter(explode("\n", $addrExtra)) as $line)
						<div>{{ $line }}</div>
					@endforeach
					@if($mobileLine !== '')
						<div>{{ $mobileLine }}</div>
					@endif
					@if(!empty($receipt_details->client_id_label) && !empty($receipt_details->client_id))
						<div><strong>{{ $receipt_details->client_id_label }}</strong> {{ $receipt_details->client_id }}</div>
					@endif
					@if(!empty($receipt_details->customer_tax_label) && !empty($receipt_details->customer_tax_number))
						<div><strong>{{ $receipt_details->customer_tax_label }}</strong> {{ $receipt_details->customer_tax_number }}</div>
					@endif
					@if(!empty($receipt_details->customer_custom_fields))
						@foreach(array_filter(explode("\n", $fmtHtml($receipt_details->customer_custom_fields))) as $line)
							<div>{{ $line }}</div>
						@endforeach
					@endif
				</div>
			</td>
			<td class="ship">
				<div class="lbl">SHIP TO:</div>
				<div class="body">
					@if($shipToBody !== '')
						<div class="nm">{{ $shipToBody }}</div>
					@endif
					@foreach(array_filter(explode("\n", $shipExtra)) as $line)
						<div>{{ $line }}</div>
					@endforeach
					@if($mobileLine !== '' && empty($receipt_details->shipping_address))
						<div>{{ $mobileLine }}</div>
					@endif
				</div>
			</td>
		</tr>
	</table>

	{{-- ========== META BAR (same columns as reference; values from layout/settings) ========== --}}
	@php
		// Fixed 6-column strip like reference; empty → "-"
		$dash = function ($v) {
			$v = is_string($v) ? trim(strip_tags($v)) : $v;
			return ($v === null || $v === '' || $v === false) ? '-' : $v;
		};

		$termsVal = $dash($receipt_details->pay_term ?? null);
		$statusVal = $dash($receipt_details->payment_status ?? null);
		$invoicedByVal = $dash($receipt_details->added_by ?? null);

		// Rep: sales person (if enabled) → commission agent → fallback "-"
		$repVal = '-';
		if (!empty($receipt_details->sales_person)) {
			$repVal = $receipt_details->sales_person;
		} elseif (!empty($receipt_details->commission_agent)) {
			$repVal = $receipt_details->commission_agent;
		}
		$repLabel = !empty($receipt_details->sales_person_label)
			? rtrim($receipt_details->sales_person_label, ':')
			: (!empty($receipt_details->commission_agent_label)
				? rtrim($receipt_details->commission_agent_label, ':')
				: 'Rep');

		// Tax ID / Hemp — left: business tax 1 (show_tax_1), right: business code 1 or sell CF1
		$taxIdLeft = '-';
		if (isset($receipt_details->tax_info1) && $receipt_details->tax_info1 !== '') {
			$taxIdLeft = $dash(strip_tags($receipt_details->tax_info1));
		}
		$hempRight = '-';
		if (!empty($receipt_details->code_1)) {
			$hempRight = $dash($receipt_details->code_1);
		} elseif (!empty($receipt_details->sell_custom_field_1_value)) {
			$hempRight = $dash($receipt_details->sell_custom_field_1_value);
		}
		$taxIdHempLabel = 'Tax ID/Hemp';

		// Tobacco / Sales Tax — tax 2 (show_tax_2) + code 2 or sell CF2
		$tobLeft = '-';
		if (!empty($receipt_details->tax_info2)) {
			$tobLeft = $dash(strip_tags($receipt_details->tax_info2));
		} elseif (!empty($receipt_details->customer_tax_number)) {
			$tobLeft = $dash($receipt_details->customer_tax_number);
		}
		$tobRight = '-';
		if (!empty($receipt_details->code_2)) {
			$tobRight = $dash($receipt_details->code_2);
		} elseif (!empty($receipt_details->sell_custom_field_2_value)) {
			$tobRight = $dash($receipt_details->sell_custom_field_2_value);
		}
		$tobLabel = 'Tobacco/Sales Tax';

		$meta = [
			['Terms', $termsVal],
			['Status', $statusVal],
			['Invoiced By', $invoicedByVal],
			[$repLabel, $repVal],
			[$taxIdHempLabel, $taxIdLeft.' / '.$hempRight],
			[$tobLabel, $tobLeft.'/'.$tobRight],
		];
		$metaW = round(100 / count($meta), 2);
	@endphp
	<table class="usl-meta">
		<tr class="usl-meta-labels">
			@foreach($meta as $cell)
				<td style="width:{{ $metaW }}%;">{{ $cell[0] }}</td>
			@endforeach
		</tr>
		<tr class="usl-meta-values">
			@foreach($meta as $cell)
				<td style="width:{{ $metaW }}%;">{{ $cell[1] }}</td>
			@endforeach
		</tr>
	</table>

	@includeIf('sale_pos.receipts.partial.common_repair_invoice')

	{{-- ========== LINE ITEMS (labels from Invoice Layout) ========== --}}
	@php
		$colCount = 3; // code + product + qty
		if (empty($receipt_details->hide_price)) {
			$colCount += 1; // price
			if ($show_sold_price) { $colCount += 1; }
			if ($show_item_disc_col) { $colCount += 1; }
			if ($show_tax_column) { $colCount += 1; }
			$colCount += 1; // amount
		}
	@endphp
	<table class="usl-items">
		<thead>
			<tr>
				<th style="width:12%;">@lang('product.sku')</th>
				<th style="width:{{ $show_sold_price || $show_item_disc_col || $show_tax_column ? '28%' : '40%' }};">{{ $prod_label }}</th>
				<th class="r" style="width:8%;">{{ $qty_label }}</th>
				@if(empty($receipt_details->hide_price))
					<th class="r" style="width:10%;">{{ $price_label }}</th>
					@if($show_sold_price)
						<th class="r" style="width:10%;">{{ $sold_price_label }}</th>
					@endif
					@if($show_item_disc_col)
						<th class="r" style="width:9%;">{{ $item_disc_label }}</th>
					@endif
					@if($show_tax_column)
						<th class="r" style="width:9%;">{{ $tax_col_label }}</th>
					@endif
					<th class="r" style="width:12%;">{{ $amt_label }}</th>
				@endif
			</tr>
		</thead>
		<tbody>
			@forelse($receipt_details->lines as $line)
				<tr>
					<td>
						@php
							$codeCell = $line['sub_sku'] ?? '';
							if ($codeCell === '' && !empty($line['cat_code'])) {
								$codeCell = $line['cat_code'];
							}
						@endphp
						{{ $codeCell }}
					</td>
					<td>
						@if(!empty($line['image']))
							<img src="{{ $line['image'] }}" alt="" width="36" style="float:left;margin:0 6px 2px 0;">
						@endif
						<span class="prod">
							{{ $line['name'] }}
							@if(!empty($line['product_variation']) || !empty($line['variation']))
								{{ $line['product_variation'] }} {{ $line['variation'] }}
							@endif
						</span>
						@if(!empty($line['brand']))
							<span class="muted">, {{ $line['brand'] }}</span>
						@endif
						@if(!empty($line['cat_code']) && !empty($line['sub_sku']) && $line['cat_code'] !== $line['sub_sku'])
							<span class="muted">, {{ $line['cat_code'] }}</span>
						@endif
						@if(!empty($line['product_custom_fields']))
							<span class="muted">, {{ $line['product_custom_fields'] }}</span>
						@endif
						@if(!empty($line['product_description']))
							<div class="muted">{!! $line['product_description'] !!}</div>
						@endif
						@if(!empty($line['sell_line_note']))
							<div class="muted">{!! $line['sell_line_note'] !!}</div>
						@endif
						@if(!empty($line['lot_number']))
							<div class="muted">{{ $line['lot_number_label'] }}: {{ $line['lot_number'] }}</div>
						@endif
						@if(!empty($line['product_expiry']))
							<div class="muted">{{ $line['product_expiry_label'] }}: {{ $line['product_expiry'] }}</div>
						@endif
					</td>
					<td class="r">{{ $line['quantity'] }}</td>
					@if(empty($receipt_details->hide_price))
						<td class="r">{{ $line['unit_price_before_discount'] }}</td>
						@if($show_sold_price)
							<td class="r">{{ $line['unit_price'] ?? $line['unit_price_inc_tax'] }}</td>
						@endif
						@if($show_item_disc_col)
							<td class="r">
								@php
									$lineDiscShow = $line['total_line_discount'] ?? null;
									$lineDiscUf = (float) ($line['line_discount_uf'] ?? 0);
									if ($lineDiscUf <= 0 && !empty($lineDiscShow) && $lineDiscShow !== '0' && $lineDiscShow !== '0.00') {
										$lineDiscUf = (float) preg_replace('/[^0-9.\-]/', '', (string) $lineDiscShow);
									}
								@endphp
								@if($lineDiscUf > 0)
									{{ $lineDiscShow }}
									@if(!empty($line['line_discount_percent']))
										({{ $line['line_discount_percent'] }}%)
									@endif
								@else
									0.00
								@endif
							</td>
						@endif
						@if($show_tax_column)
							<td class="r">{{ $line['tax'] ?? '0.00' }}</td>
						@endif
						<td class="r">{{ $line['line_total'] }}</td>
					@endif
				</tr>
				@if(!empty($line['modifiers']))
					@foreach($line['modifiers'] as $modifier)
						<tr>
							<td>{{ $modifier['sub_sku'] ?? '' }}</td>
							<td>{{ $modifier['name'] }} {{ $modifier['variation'] ?? '' }}</td>
							<td class="r">{{ $modifier['quantity'] }}</td>
							@if(empty($receipt_details->hide_price))
								<td class="r">{{ $modifier['unit_price_inc_tax'] }}</td>
								@if($show_sold_price)
									<td class="r">{{ $modifier['unit_price_inc_tax'] }}</td>
								@endif
								@if($show_item_disc_col)
									<td class="r">0.00</td>
								@endif
								@if($show_tax_column)
									<td class="r">0.00</td>
								@endif
								<td class="r">{{ $modifier['line_total'] }}</td>
							@endif
						</tr>
					@endforeach
				@endif
			@empty
				<tr>
					<td colspan="{{ $colCount }}">&nbsp;</td>
				</tr>
			@endforelse
		</tbody>
	</table>

	{{-- ========== SHIPPING (left) + TOTALS line-wise (right) ========== --}}
	@php
		$moneyOrZero = function ($val) use ($zero_money) {
			if ($val === null || $val === '' || $val === 0 || $val === '0' || $val === '0.00') {
				return $zero_money;
			}
			return $val;
		};
		$shipAmt = $moneyOrZero($receipt_details->shipping_charges ?? 0);
		$paidAmt = isset($receipt_details->total_paid) ? $moneyOrZero($receipt_details->total_paid) : $zero_money;
		$dueAmt = isset($receipt_details->total_due) ? $moneyOrZero($receipt_details->total_due) : $zero_money;
		$qtyDisp = $receipt_details->total_quantity ?? '0.00';
	@endphp
	<table class="usl-lower">
		<tr>
			<td class="ship">
				@if(!empty($receipt_details->shipping_details))
					<div><strong>Ship Method:</strong> {{ $receipt_details->shipping_details }}</div>
				@endif
				@if(!empty($receipt_details->shipping_method))
					<div><strong>{{ $receipt_details->shipping_method_label ?? 'Shipping Method' }}:</strong> {{ $receipt_details->shipping_method }}</div>
				@endif
				@if(!empty($receipt_details->shipping_date))
					<div><strong>{{ $receipt_details->shipping_date_label ?? 'Shipping Date' }}:</strong> {{ $receipt_details->shipping_date }}</div>
				@endif
				@if(!empty($receipt_details->shipping_tracking_no))
					<div><strong>{{ $receipt_details->shipping_tracking_no_label ?? 'Tracking No' }}:</strong> {{ $receipt_details->shipping_tracking_no }}</div>
				@endif
				@if(!empty($receipt_details->no_of_boxes))
					<div><strong>{{ $receipt_details->no_of_boxes_label ?? 'No. of Boxes' }}:</strong> {{ $receipt_details->no_of_boxes }}</div>
				@endif
				@if(!empty($receipt_details->shipping_status))
					<div><strong>Ship Status:</strong> {{ $receipt_details->shipping_status }}</div>
				@endif
				@if(!empty($receipt_details->shipping_custom_field_1_label))
					<div><strong>{!! strip_tags($receipt_details->shipping_custom_field_1_label) !!}:</strong> {!! $receipt_details->shipping_custom_field_1_value ?? '' !!}</div>
				@endif
				@if(!empty($receipt_details->shipping_custom_field_2_label))
					<div><strong>{!! strip_tags($receipt_details->shipping_custom_field_2_label) !!}:</strong> {!! $receipt_details->shipping_custom_field_2_value ?? '' !!}</div>
				@endif
				@if(!empty($receipt_details->shipping_custom_field_3_label))
					<div><strong>{!! strip_tags($receipt_details->shipping_custom_field_3_label) !!}:</strong> {!! $receipt_details->shipping_custom_field_3_value ?? '' !!}</div>
				@endif
				@if(!empty($receipt_details->shipping_custom_field_4_label))
					<div><strong>{!! strip_tags($receipt_details->shipping_custom_field_4_label) !!}:</strong> {!! $receipt_details->shipping_custom_field_4_value ?? '' !!}</div>
				@endif
				@if(!empty($receipt_details->shipping_custom_field_5_label))
					<div><strong>{!! strip_tags($receipt_details->shipping_custom_field_5_label) !!}:</strong> {!! $receipt_details->shipping_custom_field_5_value ?? '' !!}</div>
				@endif
				@if(!empty($receipt_details->payments))
					<table class="usl-pay">
						@foreach($receipt_details->payments as $payment)
							<tr>
								<td>{{ $payment['method'] }}</td>
								<td>{{ $payment['amount'] }}</td>
								<td>{{ $payment['date'] }}</td>
							</tr>
						@endforeach
					</table>
				@endif
			</td>
			<td class="tots">
				@if(empty($receipt_details->hide_price))
				@php
					// 4. Line Item (D/C) total
					$lineDcUf = (float) ($receipt_details->total_line_discount_uf ?? 0);
					if ($lineDcUf <= 0 && !empty($receipt_details->total_line_discount) && $receipt_details->total_line_discount !== 0 && $receipt_details->total_line_discount !== '0') {
						$lineDcUf = (float) preg_replace('/[^0-9.\-]/', '', (string) $receipt_details->total_line_discount);
					}
					// 5. Order discount (POS % / fixed)
					$invDisc = (float) ($receipt_details->discount_amount_unformatted ?? 0);
					if ($invDisc <= 0 && !empty($receipt_details->discount) && $receipt_details->discount !== 0 && $receipt_details->discount !== '0') {
						$invDisc = (float) preg_replace('/[^0-9.\-]/', '', (string) $receipt_details->discount);
					}
					// 3. Shipping
					$shipShow = !empty($receipt_details->shipping_charges) ? $receipt_details->shipping_charges : $shipAmt;
					// 6. Tax — order tax only (not product/item line tax)
					$taxUf = (float) ($receipt_details->tax_amount_uf ?? 0);
					// Never fall back to summing line taxes; blank/zero order tax = $0.00
					$taxShow = ($taxUf != 0 && !empty($receipt_details->tax) && $receipt_details->tax !== 0 && $receipt_details->tax !== '0')
						? $receipt_details->tax
						: $zero_money;
				@endphp
				{{-- Fixed bottom line order + horizontal underlines (ref style) --}}
				<table class="usl-tots">
					{{-- 1. Total Quantity --}}
					<tr class="tot-row">
						<td class="lab">Total Quantity</td>
						<td class="val">{{ $qtyDisp }}</td>
					</tr>
					{{-- 2. Subtotal --}}
					<tr class="tot-row">
						<td class="lab">Subtotal</td>
						<td class="val">{{ $receipt_details->subtotal }}</td>
					</tr>
					{{-- 3. Shipping Charges --}}
					<tr class="tot-row">
						<td class="lab">Shipping Charges</td>
						<td class="val">{{ $shipShow }}</td>
					</tr>
					{{-- 4. Line Item (D/C) --}}
					<tr class="tot-row">
						<td class="lab">Line Item (D/C)</td>
						<td class="val">
							@if($lineDcUf > 0)
								{{ $receipt_details->total_line_discount }}
							@else
								{{ $zero_money }}
							@endif
						</td>
					</tr>
					{{-- 5. Discount (2.00% when %) / amount --}}
					<tr class="tot-row">
						<td class="lab">{!! $discount_label_html !!}</td>
						<td class="val">
							@if($invDisc > 0)
								{{ $receipt_details->discount }}
							@else
								{{ $zero_money }}
							@endif
						</td>
					</tr>
					{{-- 6. Tax (order tax only) --}}
					<tr class="tot-row">
						<td class="lab">{!! $tax_total_label_html !!}</td>
						<td class="val">{{ $taxShow }}</td>
					</tr>
					{{-- Rule before Total --}}
					<tr class="tot-rule"><td colspan="2"></td></tr>
					{{-- 7. Total --}}
					<tr class="grand">
						<td class="lab">Total</td>
						<td class="val">{{ $receipt_details->total }}</td>
					</tr>
					{{-- 8. Total Paid --}}
					<tr class="grand">
						<td class="lab">Total Paid</td>
						<td class="val">{{ $paidAmt }}</td>
					</tr>
					{{-- 9. Total Due --}}
					<tr class="grand">
						<td class="lab">Total Due</td>
						<td class="val">{{ $dueAmt }}</td>
					</tr>
				</table>
				@endif
			</td>
		</tr>
	</table>

	<div class="usl-credit">
		@php
			// Prefer advance balance (store credit); fall back only if old field was set
			$storeCreditDisp = $zero_money;
			if (isset($receipt_details->customer_store_credit_uf)) {
				$storeCreditDisp = ((float) $receipt_details->customer_store_credit_uf > 0)
					? $receipt_details->customer_store_credit
					: $zero_money;
			} elseif (!empty($receipt_details->customer_store_credit)) {
				$storeCreditDisp = $receipt_details->customer_store_credit;
			}
			$creditLabel = !empty($receipt_details->customer_store_credit_label)
				? $receipt_details->customer_store_credit_label
				: 'Available Store Credit';
			// Prefer fixed wholesale label for this design
			$creditLabel = 'Available Store Credit';
		@endphp
		{{ $creditLabel }}:
		{{ $storeCreditDisp }}
	</div>

	@php
		$prevBal = $zero_money;
		if (isset($receipt_details->total_previous_due) && $receipt_details->total_previous_due !== '' && $receipt_details->total_previous_due !== null && $receipt_details->total_previous_due !== 0 && $receipt_details->total_previous_due !== '0') {
			$prevBal = $receipt_details->total_previous_due;
		}
		$totalBal = $zero_money;
		if (!empty($receipt_details->all_due)) {
			$totalBal = $receipt_details->all_due;
		} elseif (isset($receipt_details->total_due)) {
			$totalBal = $moneyOrZero($receipt_details->total_due);
		}
	@endphp
	<table class="usl-bal">
		<tr>
			<td class="l">{{ $prev_bal_label }}: {{ $prevBal }}</td>
			<td class="r">{{ $total_bal_label }}: {{ $totalBal }}</td>
		</tr>
	</table>

	{{-- Amount in letters — after previous payment / balance line --}}
	@if(!empty($receipt_details->total_in_words))
		<div class="usl-amount-words">({{ $receipt_details->total_in_words }})</div>
	@endif

	@if(empty($receipt_details->hide_price) && !empty($receipt_details->tax_summary_label) && !empty($receipt_details->taxes))
		<table class="usl-items" style="margin-top:8px;width:48%;margin-left:auto;">
			<thead>
				<tr><th colspan="2" style="text-align:center;">{{ $receipt_details->tax_summary_label }}</th></tr>
			</thead>
			<tbody>
				@foreach($receipt_details->taxes as $key => $val)
					<tr>
						<td style="text-align:center;"><b>{{ $key }}</b></td>
						<td style="text-align:center;">{{ $val }}</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	@endif

	@if(!empty($receipt_details->additional_notes))
		<div class="usl-notes">{{ $fmtHtml($receipt_details->additional_notes) }}</div>
	@endif

	@if(!empty($receipt_details->show_qr_code) && !empty($receipt_details->qr_code_text))
		<div style="text-align:center;margin-top:8px;">
			<img src="data:image/png;base64,{{ DNS2D::getBarcodePNG($receipt_details->qr_code_text, 'QRCODE', 3, 3, [0, 0, 0]) }}" alt="qr">
		</div>
	@endif

	{{-- Signature stays with body content (not bottom-pushed) --}}
	<table class="usl-sign-row">
		<tr>
			<td class="sig">
				<div class="sig-line"></div>
				<div class="sig-lbl">Date &amp; Signature</div>
			</td>
		</tr>
	</table>

	{{-- Footer thank-you text only — bottom of page --}}
	<table class="usl-foot">
		<tr>
			<td class="thx">
				@if(!empty($receipt_details->footer_text))
					{!! $receipt_details->footer_text !!}
				@endif
			</td>
		</tr>
	</table>

	<table class="usl-page">
		<tr>
			<td class="pg-left">Page 1 of 1</td>
			<td class="pg-right">&nbsp;</td>
		</tr>
	</table>
</div>
