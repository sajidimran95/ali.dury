{{-- QuickBooks-style wholesale invoice --}}
@php
	$forPdf = ! empty($for_pdf ?? null);
	$fmtHtml = function ($text) {
		if ($text === null || $text === '') {
			return [];
		}
		$text = html_entity_decode((string) $text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
		$text = preg_replace('/<\s*br\s*\/?\s*>/i', "\n", $text);
		$text = strip_tags($text);
		$lines = array_values(array_filter(array_map(function ($line) {
			return trim($line, " \t,");
		}, preg_split("/\r\n|\r|\n/", $text)), function ($line) {
			return $line !== '';
		}));
		$clean = [];
		foreach ($lines as $line) {
			if (end($clean) !== $line) {
				$clean[] = $line;
			}
		}

		return $clean;
	};

	$fmtDate = function ($value) {
		if ($value === null || $value === '') {
			return '';
		}
		try {
			return \Carbon\Carbon::parse($value)->format('n/j/Y');
		} catch (\Throwable $e) {
			$raw = trim(strip_tags((string) $value));
			if (preg_match('/^(\d{1,2}\/\d{1,2}\/\d{2,4})/', $raw, $m)) {
				return $m[1];
			}

			return $raw;
		}
	};

	$num = function ($value) {
		if ($value === null || $value === '') {
			return 0.0;
		}
		if (is_numeric($value)) {
			return (float) $value;
		}

		return (float) preg_replace('/[^0-9.\-]/', '', (string) $value);
	};

	$qtyText = function ($line) use ($num) {
		$n = array_key_exists('quantity_uf', $line) ? $num($line['quantity_uf']) : $num($line['quantity'] ?? 0);
		if (abs($n - round($n)) < 0.0001) {
			return (string) (int) round($n);
		}

		return rtrim(rtrim(number_format($n, 2, '.', ''), '0'), '.');
	};

	$money = function ($value) use ($num) {
		return number_format($num($value), 2);
	};

	$bizName = trim((string) ($receipt_details->business_name ?? ''));
	if ($bizName === '') {
		$bizName = trim((string) ($receipt_details->display_name ?? ''));
		if (str_contains($bizName, ',')) {
			$bizName = trim(explode(',', $bizName)[0]);
		}
	}

	$addrParts = $fmtHtml($receipt_details->address ?? '');
	if (count($addrParts) === 1 && str_contains($addrParts[0], ',')) {
		$addrParts = array_values(array_filter(array_map('trim', explode(',', $addrParts[0]))));
	}
	$street = $addrParts[0] ?? '';
	$cityStateZip = '';
	if (count($addrParts) >= 3) {
		$zip = array_pop($addrParts);
		$state = array_pop($addrParts);
		$street = array_shift($addrParts);
		$city = trim(implode(' ', $addrParts));
		$cityStateZip = trim($city.' '.$state.' '.$zip);
	} elseif (count($addrParts) === 2) {
		$street = $addrParts[0];
		$cityStateZip = $addrParts[1];
	}

	$phone = trim(strip_tags((string) ($receipt_details->location_mobile ?? '')));
	if ($phone === '' && ! empty($receipt_details->contact)) {
		$contactLines = $fmtHtml($receipt_details->contact);
		$phone = $contactLines[0] ?? '';
	}

	$invoiceDate = $fmtDate($receipt_details->invoice_date ?? '');
	$shipDate = $fmtDate($receipt_details->shipping_date ?? '');
	if ($shipDate === '') {
		$shipDate = $invoiceDate;
	}

	// QB Bill To / Ship To: 1) person name  2) business name  3) street address
	$qbPartyLines = function ($person, $business, $addressHtml) use ($fmtHtml) {
		$lines = [];
		$seen = [];
		$add = function ($line) use (&$lines, &$seen) {
			$line = trim((string) $line);
			if ($line === '') {
				return;
			}
			$key = strtolower($line);
			if (isset($seen[$key])) {
				return;
			}
			$seen[$key] = true;
			$lines[] = $line;
		};

		$add($person);
		$add($business);
		foreach ($fmtHtml($addressHtml) as $line) {
			if (stripos($line, 'mobile') !== false) {
				continue;
			}
			$add($line);
		}

		return $lines;
	};

	$billPerson = trim(strip_tags((string) ($receipt_details->customer_person_name ?? '')));
	if ($billPerson === '') {
		$billPerson = trim(strip_tags((string) ($receipt_details->customer_name ?? '')));
	}
	if ($billPerson === '') {
		$billPerson = trim(strip_tags((string) ($receipt_details->contact_name ?? '')));
	}
	$billBusiness = trim(strip_tags((string) ($receipt_details->customer_business_name ?? '')));
	$billAddress = $receipt_details->customer_info_address ?? '';
	$billLines = $qbPartyLines($billPerson, $billBusiness, $billAddress);
	if ($billLines === []) {
		$billLines = $qbPartyLines(
			trim(strip_tags((string) ($receipt_details->delivered_to ?? ''))),
			'',
			$receipt_details->shipping_address ?? ''
		);
	}

	$shipPerson = trim(strip_tags((string) ($receipt_details->delivered_to ?? '')));
	if ($shipPerson === '') {
		$shipPerson = $billPerson;
	}
	$shipBusiness = $billBusiness;
	$shipAddress = $receipt_details->shipping_address ?? '';
	if (trim(strip_tags((string) $shipAddress)) === '') {
		$shipAddress = $billAddress;
	}
	$shipLines = $qbPartyLines($shipPerson, $shipBusiness, $shipAddress);

	$po = trim(strip_tags((string) ($receipt_details->sell_custom_field_1_value ?? '')));
	$terms = trim(strip_tags((string) ($receipt_details->pay_term ?? '')));
	$rep = trim(strip_tags((string) ($receipt_details->sales_person ?? '')));
	if ($rep === '' && ! empty($receipt_details->commission_agent)) {
		$rep = trim(strip_tags((string) $receipt_details->commission_agent));
	}
	$via = trim(strip_tags((string) ($receipt_details->shipping_method ?? '')));

	// japspos2 only: hide Bill To / Ship To on printed invoices. Other users keep them.
	$hideBillShip = auth()->check()
		&& strcasecmp((string) (auth()->user()->username ?? ''), 'japspos2') === 0;
	// japspos2 only: manual name typed at POS is printed as Bill To (no address / phone).
	$manualBillName = $hideBillShip ? trim(strip_tags((string) ($receipt_details->invoice_bill_name ?? ''))) : '';

	$footer = trim(strip_tags((string) ($receipt_details->footer_text ?? '')));
	if ($footer === '') {
		$footer = 'WISCONSIN TOBACCO/VAPOR PRODUCT EXCISE TAX INCLUDED SALE*';
	}

	$lines = $receipt_details->lines ?? [];
	$totalRaw = $receipt_details->total_unformatted ?? ($receipt_details->total ?? 0);
	$paidRaw = $receipt_details->total_paid ?? 0;
	if (! is_numeric($paidRaw)) {
		$paidRaw = $num($paidRaw);
	}
	$dueRaw = $receipt_details->total_due ?? null;
	if ($dueRaw === null || $dueRaw === '') {
		$dueRaw = $num($totalRaw) - $num($paidRaw);
	} elseif (! is_numeric($dueRaw)) {
		$dueRaw = $num($dueRaw);
	}
	$showDue = $num($dueRaw) > 0.009;
	$qtyTotal = 0.0;
	foreach ($lines as $line) {
		$qtyTotal += array_key_exists('quantity_uf', $line) ? $num($line['quantity_uf']) : $num($line['quantity'] ?? 0);
	}
	$qtyTotalText = abs($qtyTotal - round($qtyTotal)) < 0.0001
		? (string) (int) round($qtyTotal)
		: rtrim(rtrim(number_format($qtyTotal, 2, '.', ''), '0'), '.');

	$layoutDesign = $receipt_details->design ?? 'us_letter';
	$pageSizeCss = 'letter portrait';
	$pageMin = '11in';
	$pagePad = '0.4in 0.45in 0.8in';
	$letterPrint = true;
	if ($layoutDesign === 'slim') {
		$pageSizeCss = '80mm auto';
		$pageMin = 'auto';
		$pagePad = '2mm';
		$letterPrint = false;
	} elseif ($layoutDesign === 'slim2') {
		$pageSizeCss = '58mm auto';
		$pageMin = 'auto';
		$pagePad = '2mm';
		$letterPrint = false;
	}
@endphp
<style type="text/css">
	@media print {
		@if($letterPrint && ! $forPdf)
		@page {
			size: {{ $pageSizeCss }};
			margin: 0.32in 0.4in 0.42in 0.4in;
		}
		.qb-sheet {
			position: relative;
			width: 100%;
			height: 9.9in;
			box-sizing: border-box;
			page-break-after: always;
			break-after: page;
		}
		.qb-sheet:last-child {
			page-break-after: auto;
			break-after: auto;
		}
		.qb-sheet:last-child {
			display: flex;
			flex-direction: column;
			padding-bottom: 16px;
		}
		.qb-sheet:last-child .qb-items-fill {
			display: grid !important;
			flex: 1 1 auto;
			min-height: 8px;
		}
		.qb-sheet:last-child .qb-items-totals {
			position: static;
			margin: 0;
		}
		.qb-sheet-footer {
			position: absolute;
			left: 0;
			right: 0;
			bottom: 0;
			text-align: center;
			font-family: Arial, Helvetica, sans-serif;
			font-size: 10px;
			line-height: 1.2;
			color: #000;
		}
		@elseif($letterPrint)
		{{-- mPDF sets the Letter page size. A CSS size here collapses later pages. --}}
		.qb-items > tbody > tr.qb-grid-fill > td {
			border-top: 1px solid #000 !important;
			border-bottom: none !important;
			padding: 0 !important;
		}
		@else
		@page { size: {{ $pageSizeCss }}; margin: 0; }
		@endif
		html, body { margin: 0 !important; padding: 0 !important; background: #fff !important; }
		#receipt_section, .print_section, .invoice.print_section {
			height: auto !important; max-height: none !important; overflow: visible !important;
		}
		.qb-wrap {
			display: block !important;
			min-height: 0 !important;
			height: auto !important;
			max-width: none !important;
			padding: 0 !important;
		}
		.qb-items-sheet { display: block !important; min-height: 0 !important; }
		.qb-items-fill { display: none !important; }
		.qb-items { page-break-inside: auto !important; break-inside: auto !important; }
		.qb-items-totals { page-break-before: avoid; break-before: avoid; }
		.qb-items thead { display: table-header-group !important; }
		.qb-items > thead > tr.qb-letterhead { page-break-inside: avoid; break-inside: avoid; }
		.qb-items > thead > tr.qb-letterhead > td {
			border: none !important;
			padding: 0 0 8px !important;
			text-align: left !important;
			font-weight: 400 !important;
			background: #fff !important;
		}
	}
	.qb-wrap {
		color: #000;
		background: #fff;
		font-family: Arial, Helvetica, sans-serif;
		font-size: 11px;
		line-height: 1.25;
		width: 100%;
		max-width: 8.2in;
		margin: 0 auto;
		box-sizing: border-box;
		padding: {{ $pagePad }};
		min-height: 0;
		display: block;
	}
	.qb-wrap table { width: 100%; border-collapse: collapse; }
	.qb-hdr td { vertical-align: top; padding: 0; }
	.qb-biz { width: 58%; font-size: 12px; line-height: 1.35; padding-top: 8px; }
	.qb-biz .nm { margin-bottom: 10px; }
	.qb-inv { width: 42%; text-align: right; }
	.qb-inv .title { font-size: 26px; font-weight: 700; margin: 0 0 10px; letter-spacing: 0; }
	.qb-id {
		width: auto;
		margin-left: auto;
		border-collapse: collapse;
	}
	.qb-id th, .qb-id td {
		border: 1px solid #000;
		padding: 3px 14px;
		font-size: 11px;
		font-weight: 400;
		text-align: center;
		background: #fff;
	}
	.qb-id th { font-weight: 700; }
	.qb-wrap table.qb-parties { width: 100%; margin-top: 18px; border-collapse: separate; border-spacing: 0; }
	.qb-box { border: 1px solid #000; background: #fff; }
	.qb-parties td.qb-box { width: 49%; vertical-align: top; }
	.qb-parties td.qb-parties-gap { width: 22px; border: none; padding: 0; background: transparent; }
	.qb-box .lbl {
		border-bottom: 1px solid #000;
		padding: 3px 6px;
		font-size: 11px;
	}
	.qb-box .body { padding: 4px 6px 8px; font-size: 11px; line-height: 1.35; min-height: 88px; }
	.qb-meta { margin-top: 12px; }
	.qb-meta th, .qb-meta td {
		border: 1px solid #000;
		padding: 4px 6px;
		font-size: 11px;
		font-weight: 400;
		text-align: center;
		height: 22px;
	}
	.qb-meta th { font-weight: 700; }
	.qb-items-sheet {
		display: block;
		min-height: 0;
	}
	.qb-items { margin-top: 0; table-layout: fixed; }
	.qb-items-fill {
		flex: 1 1 auto;
		display: grid;
		grid-template-columns: 11% 18% 41% 15% 15%;
		border-left: 1px solid #000;
		border-right: 1px solid #000;
		min-height: 0;
	}
	.qb-items-fill span { border-right: 1px solid #000; }
	.qb-items-fill span:last-child { border-right: none; }
	.qb-items > thead > tr > th,
	.qb-items > tbody > tr > td {
		border: 1px solid #000;
		padding: 3px 6px;
		font-size: 11px;
		font-weight: 400;
		vertical-align: top;
		background: #fff;
	}
	.qb-items > thead > tr > th { font-weight: 700; text-align: center; }
	.qb-items > thead { display: table-header-group; }
	.qb-letterhead-block { padding-bottom: 8px; }
	.qb-items > thead > tr.qb-letterhead > td {
		border: none;
		padding: 0 0 8px;
		text-align: left;
		font-weight: 400;
		background: #fff;
	}
	.qb-items > tbody > tr > td { border-top: 0; border-bottom: 0; }
	.qb-sheet:not(:last-child) .qb-items:not(.qb-items-totals) > tbody > tr:last-child > td {
		border-bottom: 1px solid #000;
	}
	.qb-items > tbody > tr.qb-total > td { border-top: 1px solid #000; border-bottom: 1px solid #000; }
	.qb-items.qb-items-totals > tbody > tr:last-child > td {
		border-top: 1px solid #000 !important;
		border-bottom: 1px solid #000 !important;
	}
	.qb-items .c { text-align: center; }
	.qb-items .r { text-align: right; white-space: nowrap; }
	.qb-items .desc { text-align: left; }
	.qb-items tfoot td { vertical-align: middle; height: 28px; }
	.qb-items .note { text-align: left; font-size: 10px; letter-spacing: 0.1px; }
	.qb-items .qb-qty-total { text-align: center; font-weight: 700; vertical-align: middle; font-size: 11px; white-space: nowrap; }
	.qb-items .totlab { text-align: center; font-weight: 700; font-size: 13px; }
	.qb-items .totamt { text-align: right; font-size: 12px; }
	.qb-wrap.qe-on .qe-scanbar { margin: 10px 0; }
	.qb-wrap.qe-on .qe-scanbar label { display: block; font-size: 10px; letter-spacing: 0.06em; text-transform: uppercase; margin-bottom: 3px; }
	.qb-wrap.qe-on #qe_scan,
	.qb-wrap.qe-on #qe_sale_note { width: 100%; border: 1px solid #000; padding: 6px 8px; font-size: 12px; background: #fff; color: #111; }
	.qb-wrap.qe-on .qe-scan-msg { min-height: 14px; font-size: 11px; color: #9a3412; margin-top: 3px; }
	.qb-wrap.qe-on .qe-picks { list-style: none; margin: 4px 0 0; padding: 0; border: 1px solid #000; display: none; max-height: 160px; overflow: auto; background: #fff; }
	.qb-wrap.qe-on .qe-picks li { padding: 6px 8px; cursor: pointer; border-bottom: 1px solid #ddd; }
	.qb-wrap.qe-on .qe-picks li:hover { background: #f3f3f3; }
	.qb-wrap.qe-on .qe-stepper { display: flex; align-items: center; justify-content: center; gap: 2px; }
	.qb-wrap.qe-on .qe-qty,
	.qb-wrap.qe-on .qe-unit,
	.qb-wrap.qe-on .qe-damount,
	.qb-wrap.qe-on #qe_shipping,
	.qb-wrap.qe-on #qe_order_discount_amount {
		width: 64px; height: 22px; border: 1px solid #000; background: #fff; color: #111;
		text-align: right; font-size: 11px; padding: 0 4px; margin: 0;
	}
	.qb-wrap.qe-on .qe-qty { width: 36px; text-align: center; }
	.qb-wrap.qe-on .qe-stepper button,
	.qb-wrap.qe-on .qe-remove {
		width: 18px; height: 18px; padding: 0; border: 1px solid #000; background: #f7f7f7; color: #111; cursor: pointer; line-height: 16px;
	}
	.qb-wrap.qe-on .qe-remove { float: right; }
	.qb-wrap.qe-on .qe-discount { display: flex; gap: 4px; margin-top: 4px; align-items: center; }
	.qb-wrap.qe-on .qe-dtype,
	.qb-wrap.qe-on #qe_order_discount_type { height: 22px; border: 1px solid #000; background: #fff; color: #111; font-size: 11px; }
	.qb-wrap.qe-on .qe-note { margin-top: 10px; }
	.qb-wrap.qe-on .qe-note label { display: block; font-size: 11px; font-weight: 700; margin-bottom: 3px; }
</style>

@php ob_start(); @endphp
					<table class="qb-hdr">
						<tr>
							<td class="qb-biz">
								@if($bizName !== '')
									<div class="nm">{{ $bizName }}</div>
								@endif
								@if($street !== '')
									<div>{{ $street }}</div>
								@endif
								@if($cityStateZip !== '')
									<div>{{ $cityStateZip }}</div>
								@endif
								@if($phone !== '')
									<div>{{ $phone }}</div>
								@endif
							</td>
							<td class="qb-inv">
								<div class="title">{{ !empty($receipt_details->invoice_heading) ? strip_tags($receipt_details->invoice_heading) : 'Invoice' }}</div>
								<table class="qb-id">
									<tr>
										<th>Date</th>
										<th>Invoice #</th>
									</tr>
									<tr>
										<td>{{ $invoiceDate }}</td>
										<td>{{ $receipt_details->invoice_no ?? '' }}</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>

					@if(empty($hideBillShip))
					<table class="qb-parties">
						<tr>
							<td class="qb-box">
								<div class="lbl">Bill To</div>
								<div class="body">
									@foreach($billLines as $line)
										<div>{{ $line }}</div>
									@endforeach
								</div>
							</td>
							<td class="qb-parties-gap"></td>
							<td class="qb-box">
								<div class="lbl">Ship To</div>
								<div class="body">
									@foreach($shipLines as $line)
										<div>{{ $line }}</div>
									@endforeach
								</div>
							</td>
						</tr>
					</table>
					@elseif($manualBillName !== '')
					<table class="qb-parties">
						<tr>
							<td class="qb-box">
								<div class="lbl">Bill To</div>
								<div class="body">
									<div>{{ $manualBillName }}</div>
								</div>
							</td>
							<td class="qb-parties-gap"></td>
							<td></td>
						</tr>
					</table>
					@endif

					<table class="qb-meta">
						<tr>
							<th style="width:16%;">P.O. Number</th>
							<th style="width:14%;">Terms</th>
							<th style="width:12%;">Rep</th>
							<th style="width:14%;">Ship</th>
							<th style="width:12%;">Via</th>
							<th style="width:14%;">F.O.B.</th>
							<th style="width:18%;">Project</th>
						</tr>
						<tr>
							<td>{{ $po }}</td>
							<td>{{ $terms }}</td>
							<td>{{ $rep }}</td>
							<td>{{ $shipDate }}</td>
							<td>{{ $via }}</td>
							<td></td>
							<td></td>
						</tr>
					</table>

					@if(!empty($quick_edit))
						<div class="qe-scanbar">
							<label for="qe_scan">Scan item</label>
							<input type="text" id="qe_scan" autocomplete="off" placeholder="Scan barcode or type SKU / name, then Enter">
							<div id="qe_scan_msg" class="qe-scan-msg"></div>
							<ul id="qe_scan_picks" class="qe-picks"></ul>
						</div>
					@endif
	@php $qbLetterhead = ob_get_clean(); @endphp
	@if($forPdf)
		<htmlpageheader name="qbhead">{!! $qbLetterhead !!}</htmlpageheader>
		<htmlpagefooter name="qbfoot">
			<div style="text-align:center;font-family:DejaVu Sans,Arial,sans-serif;font-size:10px;">Page {PAGENO} of {nbpg}</div>
		</htmlpagefooter>
		<sethtmlpageheader name="qbhead" value="on" show-this-page="1" />
		<sethtmlpagefooter name="qbfoot" value="on" />
	@endif
<div class="qb-wrap @if(!empty($quick_edit)) qe-on @endif" @if(! $forPdf) style="min-height: {{ $pageMin }}; display: flex; flex-direction: column;" @endif>
	@if(! $forPdf)
		<div class="qb-letterhead qb-letterhead-block">{!! $qbLetterhead !!}</div>
	@endif
	<div class="qb-items-sheet" @if(! $forPdf) style="flex: 1 1 auto; display: flex; flex-direction: column; min-height: 0;" @endif>
	<table class="qb-items">
		<colgroup>
			<col style="width:{{ !empty($quick_edit) ? '18%' : '11%' }}"><col style="width:{{ !empty($quick_edit) ? '16%' : '18%' }}"><col style="width:{{ !empty($quick_edit) ? '36%' : '41%' }}"><col style="width:15%"><col style="width:15%">
		</colgroup>
		<thead>
			<tr>
				<th style="width:{{ !empty($quick_edit) ? '18%' : '11%' }};">Quantity</th>
				<th style="width:{{ !empty($quick_edit) ? '16%' : '18%' }};">Item Code</th>
				<th style="width:{{ !empty($quick_edit) ? '36%' : '41%' }};">Description</th>
				<th style="width:15%;">Price Each</th>
				<th style="width:15%;">Amount</th>
			</tr>
		</thead>
		<tbody @if(!empty($quick_edit)) id="qe_lines" @endif>
			@foreach($lines as $line)
				@php
					$code = !empty($quick_edit)
						? ($line['sku_code'] ?? ($line['sub_sku'] ?? ''))
						: ($line['sub_sku'] ?? '');
					if ($code === '' && !empty($line['cat_code'])) {
						$code = $line['cat_code'];
					}
					$desc = trim((string) ($line['name'] ?? ''));
					if (!empty($line['product_variation']) && $line['product_variation'] !== 'DUMMY') {
						$desc = trim($desc.' '.$line['product_variation']);
					}
					if (!empty($line['variation']) && $line['variation'] !== 'DUMMY') {
						$desc = trim($desc.' '.$line['variation']);
					}
					$priceEach = $line['unit_price_inc_tax_uf'] ?? ($line['unit_price_inc_tax'] ?? ($line['unit_price_before_discount'] ?? 0));
					$amount = $line['line_total_uf'] ?? ($line['line_total'] ?? 0);
					$qeIdx = $loop->index;
					$qeUnitAfter = (float) ($line['unit_price_uf'] ?? 0);
					$qeTaxRate = $qeUnitAfter > 0 ? ((float) ($line['tax_unformatted'] ?? 0) / $qeUnitAfter) : 0;
					$qeQty = (float) ($line['quantity_uf'] ?? 0);
					$qePrice = (float) ($line['unit_price_before_discount_uf'] ?? $qeUnitAfter);
					$qeDiscType = $line['line_discount_type_uf'] ?? '';
					$qeDiscAmt = (float) ($line['line_discount_amount_uf'] ?? 0);
				@endphp
				<tr @if(!empty($quick_edit)) data-variation-id="{{ $line['variation_id'] ?? '' }}" @endif>
					<td class="c">
						@if(!empty($quick_edit))
							<div class="qe-stepper">
								<button type="button" class="qe-minus" aria-label="Decrease">−</button>
								<input type="text" class="qe-qty" name="lines[{{ $qeIdx }}][quantity]" value="{{ rtrim(rtrim(number_format($qeQty, 4, '.', ''), '0'), '.') }}">
								<button type="button" class="qe-plus" aria-label="Increase">+</button>
							</div>
						@else
							{{ $qtyText($line) }}
						@endif
					</td>
					<td class="qe-sku">{{ $code }}</td>
					<td class="desc">
						@if(!empty($quick_edit))
							<button type="button" class="qe-remove" title="Remove">&times;</button>
						@endif
						<span class="prod qe-name">{{ $desc }}</span>
						@if(!empty($quick_edit))
							<input type="hidden" name="lines[{{ $qeIdx }}][transaction_sell_lines_id]" value="{{ $line['sell_line_id'] ?? '' }}">
							<input type="hidden" name="lines[{{ $qeIdx }}][product_id]" value="{{ $line['product_id'] ?? '' }}">
							<input type="hidden" name="lines[{{ $qeIdx }}][variation_id]" value="{{ $line['variation_id'] ?? '' }}">
							<input type="hidden" class="qe-tax-rate" name="lines[{{ $qeIdx }}][tax_rate]" value="{{ $qeTaxRate }}">
							<div class="qe-discount">
								<select class="qe-dtype" name="lines[{{ $qeIdx }}][line_discount_type]">
									<option value="" @if($qeDiscType === '' || $qeDiscType === null) selected @endif>No discount</option>
									<option value="fixed" @if($qeDiscType === 'fixed') selected @endif>Fixed</option>
									<option value="percentage" @if($qeDiscType === 'percentage') selected @endif>%</option>
								</select>
								<input type="text" class="qe-damount" name="lines[{{ $qeIdx }}][line_discount_amount]" value="{{ number_format($qeDiscAmt, 2, '.', '') }}">
							</div>
						@endif
					</td>
					<td class="r">
						@if(!empty($quick_edit))
							<input type="text" class="qe-unit" name="lines[{{ $qeIdx }}][unit_price]" value="{{ number_format($qePrice, 2, '.', '') }}">
						@else
							{{ $money($priceEach) }}
						@endif
					</td>
					<td class="r qe-line-total">{{ $money($amount) }}</td>
				</tr>
			@endforeach
		</tbody>
	</table>
	@if($forPdf)
	<table style="width:100%; border-collapse:collapse; margin:0; padding:0;">
		<!--QB_GRID_FILL-->
		@php $qbBottom = $showDue ? '0.6pt solid #000' : '1.2pt solid #000'; @endphp
		<tr>
			<td style="width:11%; border-left:0.6pt solid #000; border-right:0.6pt solid #000; border-top:0.6pt solid #000; border-bottom:{{ $qbBottom }}; text-align:center; font-weight:bold; font-size:9pt; padding:3px 4px;">QTY {{ $qtyTotalText }}</td>
			<td colspan="2" style="width:59%; border-left:0.6pt solid #000; border-right:0.6pt solid #000; border-top:0.6pt solid #000; border-bottom:{{ $qbBottom }}; text-align:left; font-size:7.5pt; padding:3px 4px;">{{ $footer }}</td>
			<td style="width:15%; border-left:0.6pt solid #000; border-right:0.6pt solid #000; border-top:0.6pt solid #000; border-bottom:{{ $qbBottom }}; text-align:center; font-weight:bold; font-size:10pt; padding:3px 4px;">Total</td>
			<td style="width:15%; border-left:0.6pt solid #000; border-right:0.6pt solid #000; border-top:0.6pt solid #000; border-bottom:{{ $qbBottom }}; text-align:right; font-size:9pt; padding:3px 4px;">${{ $money($totalRaw) }}</td>
		</tr>
		@if($showDue)
		<tr>
			<td style="width:11%; border-left:0.6pt solid #000; border-right:0.6pt solid #000; border-top:0.6pt solid #000; border-bottom:1.2pt solid #000; padding:3px 4px;">&nbsp;</td>
			<td colspan="2" style="width:59%; border-left:0.6pt solid #000; border-right:0.6pt solid #000; border-top:0.6pt solid #000; border-bottom:1.2pt solid #000; padding:3px 4px;">&nbsp;</td>
			<td style="width:15%; border-left:0.6pt solid #000; border-right:0.6pt solid #000; border-top:0.6pt solid #000; border-bottom:1.2pt solid #000; text-align:center; font-weight:bold; font-size:9pt; padding:3px 4px;">Balance Due</td>
			<td style="width:15%; border-left:0.6pt solid #000; border-right:0.6pt solid #000; border-top:0.6pt solid #000; border-bottom:1.2pt solid #000; text-align:right; font-size:9pt; padding:3px 4px;">${{ $money($dueRaw) }}</td>
		</tr>
		@endif
	</table>
	@endif
	<div class="qb-items-fill" aria-hidden="true"><span></span><span></span><span></span><span></span><span></span></div>
	@if(! $forPdf)
	<table class="qb-items qb-items-totals">
		<colgroup>
			<col style="width:{{ !empty($quick_edit) ? '18%' : '11%' }}"><col style="width:{{ !empty($quick_edit) ? '16%' : '18%' }}"><col style="width:{{ !empty($quick_edit) ? '36%' : '41%' }}"><col style="width:15%"><col style="width:15%">
		</colgroup>
		<tbody>
			@if(!empty($quick_edit))
				<tr>
					<td class="note" colspan="3"></td>
					<td class="totlab">Shipping</td>
					<td class="totamt"><input type="text" id="qe_shipping" name="shipping_charges" value="{{ number_format((float) ($quick_edit['shipping_charges'] ?? 0), 2, '.', '') }}"></td>
				</tr>
				<tr>
					<td class="note" colspan="3"></td>
					<td class="totlab">Discount</td>
					<td class="totamt">
						<select id="qe_order_discount_type" name="discount_type">
							<option value="fixed" @if(($quick_edit['discount_type'] ?? 'fixed') !== 'percentage') selected @endif>Fixed</option>
							<option value="percentage" @if(($quick_edit['discount_type'] ?? '') === 'percentage') selected @endif>%</option>
						</select>
						<input type="text" id="qe_order_discount_amount" name="discount_amount" value="{{ number_format((float) ($quick_edit['discount_amount'] ?? 0), 2, '.', '') }}">
					</td>
				</tr>
			@endif
			<tr class="qb-total">
				<td class="qb-qty-total" @if(!empty($quick_edit)) id="qe_qty_total" @endif>QTY {{ $qtyTotalText }}</td>
				<td class="note" colspan="2">{{ $footer }}</td>
				<td class="totlab">Total</td>
				<td class="totamt" @if(!empty($quick_edit)) id="qe_grand" @endif>${{ $money($totalRaw) }}</td>
			</tr>
			@if($showDue || !empty($quick_edit))
				<tr class="qb-balance-due">
					<td class="note" colspan="3"></td>
					<td class="totlab">Balance Due</td>
					<td class="totamt" @if(!empty($quick_edit)) id="qe_due" @endif>${{ $money($showDue ? $dueRaw : $totalRaw) }}</td>
				</tr>
			@endif
		</tbody>
	</table>
	@endif
	</div>
	@if(!empty($quick_edit))
		<template id="qe_row_template">
			<tr data-variation-id="">
				<td class="c">
					<div class="qe-stepper">
						<button type="button" class="qe-minus" aria-label="Decrease">−</button>
						<input type="text" class="qe-qty" data-name="quantity" value="1">
						<button type="button" class="qe-plus" aria-label="Increase">+</button>
					</div>
				</td>
				<td class="qe-sku"></td>
				<td class="desc">
					<button type="button" class="qe-remove" title="Remove">&times;</button>
					<span class="prod qe-name"></span>
					<input type="hidden" data-name="product_id" value="">
					<input type="hidden" data-name="variation_id" value="">
					<input type="hidden" class="qe-tax-rate" data-name="tax_rate" value="0">
					<div class="qe-discount">
						<select class="qe-dtype" data-name="line_discount_type">
							<option value="">No discount</option>
							<option value="fixed">Fixed</option>
							<option value="percentage">%</option>
						</select>
						<input type="text" class="qe-damount" data-name="line_discount_amount" value="0.00">
					</div>
				</td>
				<td class="r"><input type="text" class="qe-unit" data-name="unit_price" value="0.00"></td>
				<td class="r qe-line-total">0.00</td>
			</tr>
		</template>
		<div class="qe-note">
			<label for="qe_sale_note">Note</label>
			<textarea name="sale_note" id="qe_sale_note" rows="3">{{ $quick_edit['sale_note'] ?? '' }}</textarea>
		</div>
	@endif
</div>
@if(! $forPdf && $letterPrint)
<script>
(function () {
	var PAGE_H = 9.9 * 96;
	var FOOTER_H = 36;
	var SLACK = 28;

	function hidden(el) {
		var node = el;
		while (node && node !== document.documentElement) {
			var st = window.getComputedStyle(node);
			if (st.display === 'none' || st.visibility === 'hidden') {
				return true;
			}
			node = node.parentElement;
		}
		return false;
	}

	function rowHeight(tr) {
		return Math.max(18, Math.ceil(tr.getBoundingClientRect().height || tr.offsetHeight || 0));
	}

	window.__qbRestoreInvoice = function () {
		var roots = document.querySelectorAll('.qb-wrap[data-qb-paged="1"]');
		Array.prototype.forEach.call(roots, function (root) {
			if (typeof root._qbSavedHtml === 'string') {
				root.innerHTML = root._qbSavedHtml;
			}
			if (root._qbSavedStyle) {
				root.setAttribute('style', root._qbSavedStyle);
			} else {
				root.removeAttribute('style');
			}
			root.removeAttribute('data-qb-paged');
		});
	};

	window.__qbPaginateInvoice = function () {
		var roots = document.querySelectorAll('.qb-wrap');
		Array.prototype.forEach.call(roots, function (root) {
			if (!root || root.getAttribute('data-qb-paged') === '1' || root.classList.contains('qe-on') || hidden(root)) {
				return;
			}
			var letter = root.querySelector('.qb-letterhead-block');
			var table = root.querySelector('table.qb-items:not(.qb-items-totals)');
			if (!letter || !table) {
				return;
			}
			var totals = root.querySelector('table.qb-items-totals');
			var thead = table.querySelector('thead');
			var colgroup = table.querySelector('colgroup');
			var rows = Array.prototype.slice.call(table.querySelectorAll('tbody > tr'));

			var sandbox = document.createElement('div');
			sandbox.style.cssText = 'position:fixed;left:0;top:0;width:7.7in;visibility:hidden;pointer-events:none;z-index:-1;';
			var clone = root.cloneNode(true);
			clone.style.minHeight = '0';
			clone.style.height = 'auto';
			clone.style.display = 'block';
			clone.style.padding = '0';
			clone.style.maxWidth = 'none';
			clone.style.width = '7.7in';
			sandbox.appendChild(clone);
			document.body.appendChild(sandbox);

			var cLetter = clone.querySelector('.qb-letterhead-block');
			var cTable = clone.querySelector('table.qb-items:not(.qb-items-totals)');
			var cThead = cTable ? cTable.querySelector('thead') : null;
			var cRows = cTable ? Array.prototype.slice.call(cTable.querySelectorAll('tbody > tr')) : [];
			var cTotals = clone.querySelector('table.qb-items-totals');
			var headerH = cLetter ? Math.ceil(cLetter.getBoundingClientRect().height) : 0;
			var theadH = cThead ? Math.ceil(cThead.getBoundingClientRect().height) : 0;
			var totalsH = cTotals ? Math.ceil(cTotals.getBoundingClientRect().height) : 0;
			var heights = cRows.map(rowHeight);
			document.body.removeChild(sandbox);

			var budget = PAGE_H - FOOTER_H - headerH - theadH - SLACK;
			if (budget < 60) {
				budget = 60;
			}

			var pages = [[]];
			var used = 0;
			for (var i = 0; i < rows.length; i++) {
				var h = heights[i] || 22;
				var need = h + (i === rows.length - 1 ? totalsH : 0);
				if (pages[pages.length - 1].length && used + need > budget) {
					pages.push([]);
					used = 0;
				}
				pages[pages.length - 1].push(rows[i].outerHTML);
				used += h;
			}

			var savedHtml = root.innerHTML;
			var savedStyle = root.getAttribute('style');
			var letterHtml = letter.outerHTML;
			var theadHtml = thead ? thead.outerHTML : '';
			var colHtml = colgroup ? colgroup.outerHTML : '';
			var totalsHtml = totals ? totals.outerHTML : '';
			var n = pages.length;
			var html = '';
			for (var p = 0; p < n; p++) {
				var fillHtml = '<div class="qb-items-fill" aria-hidden="true"><span></span><span></span><span></span><span></span><span></span></div>';
				html += '<section class="qb-sheet">' + letterHtml +
					'<table class="qb-items">' + colHtml + theadHtml +
					'<tbody>' + pages[p].join('') + '</tbody></table>' +
					(p === n - 1 ? fillHtml + totalsHtml : '') +
					'<div class="qb-sheet-footer">Page ' + (p + 1) + ' of ' + n + '</div></section>';
			}
			root.innerHTML = html;
			root._qbSavedHtml = savedHtml;
			root._qbSavedStyle = savedStyle;
			root.setAttribute('data-qb-paged', '1');
			root.setAttribute('style', 'min-height:0;display:block;height:auto;max-width:none;padding:0;');
		});
	};

	if (!window.__qbInvoicePrintBound) {
		window.__qbInvoicePrintBound = true;
		window.addEventListener('beforeprint', function () {
			if (window.__qbPaginateInvoice) {
				window.__qbPaginateInvoice();
			}
		});
		window.addEventListener('afterprint', function () {
			if (window.__qbRestoreInvoice) {
				window.__qbRestoreInvoice();
			}
		});
	}
})();
</script>
@endif
