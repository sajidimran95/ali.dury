{{-- QuickBooks-style wholesale invoice --}}
@php
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

	$partyLines = function ($name, $extra) use ($fmtHtml) {
		$lines = [];
		$name = trim((string) $name);
		if ($name !== '') {
			$lines[] = $name;
		}
		foreach ($fmtHtml($extra) as $line) {
			if (strcasecmp($line, $name) === 0 || stripos($line, 'mobile') !== false) {
				continue;
			}
			$lines[] = $line;
		}

		return $lines;
	};

	$billName = trim(strip_tags((string) ($receipt_details->customer_name ?? '')));
	if ($billName === '') {
		$billName = trim(strip_tags((string) ($receipt_details->contact_name ?? '')));
	}
	$billLines = $partyLines($billName, $receipt_details->customer_info_address ?? ($receipt_details->customer_info ?? ''));
	if ($billLines === []) {
		$billLines = $partyLines($receipt_details->delivered_to ?? '', $receipt_details->shipping_address ?? '');
	}
	$shipSource = $receipt_details->shipping_address ?? '';
	if ($shipSource === '') {
		$shipSource = $receipt_details->customer_info_address ?? ($receipt_details->customer_info ?? '');
	}
	$shipName = $receipt_details->delivered_to ?? ($receipt_details->customer_name ?? '');
	$shipLines = $partyLines($shipName, $shipSource);

	$po = trim(strip_tags((string) ($receipt_details->sell_custom_field_1_value ?? '')));
	$terms = trim(strip_tags((string) ($receipt_details->pay_term ?? '')));
	$rep = trim(strip_tags((string) ($receipt_details->sales_person ?? '')));
	if ($rep === '' && ! empty($receipt_details->commission_agent)) {
		$rep = trim(strip_tags((string) $receipt_details->commission_agent));
	}
	$via = trim(strip_tags((string) ($receipt_details->shipping_method ?? '')));

	$footer = trim(strip_tags((string) ($receipt_details->footer_text ?? '')));
	if ($footer === '') {
		$footer = 'WISCONSIN TOBACCO/VAPOR PRODUCT EXCISE TAX INCLUDED SALE*';
	}

	$lines = $receipt_details->lines ?? [];
	$totalRaw = $receipt_details->total_unformatted ?? ($receipt_details->total ?? 0);

	$layoutDesign = $receipt_details->design ?? 'us_letter';
	$pageSizeCss = 'letter portrait';
	$pageMin = '10.15in';
	if ($layoutDesign === 'slim') {
		$pageSizeCss = '80mm auto';
		$pageMin = 'auto';
	} elseif ($layoutDesign === 'slim2') {
		$pageSizeCss = '58mm auto';
		$pageMin = 'auto';
	}
@endphp
<style type="text/css">
	@media print {
		@page { size: {{ $pageSizeCss }}; margin: 0.4in 0.45in; }
		html, body { margin: 0 !important; padding: 0 !important; background: #fff !important; }
		#receipt_section, .print_section, .invoice.print_section {
			height: auto !important; max-height: none !important; overflow: visible !important;
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
		padding: 0 0 0.4in;
		min-height: {{ $pageMin }};
		display: flex;
		flex-direction: column;
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
	.qb-parties { display: flex; gap: 22px; margin-top: 18px; }
	.qb-box { border: 1px solid #000; background: #fff; }
	.qb-parties .qb-box { flex: 1; min-height: 132px; }
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
		flex: 1 1 auto;
		display: flex;
		flex-direction: column;
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
	.qb-items th, .qb-items td {
		border: 1px solid #000;
		padding: 3px 6px;
		font-size: 11px;
		font-weight: 400;
		vertical-align: top;
		background: #fff;
	}
	.qb-items thead th { font-weight: 700; text-align: center; }
	.qb-items tbody td { border-top: 0; border-bottom: 0; }
	.qb-items tr.qb-total td { border-top: 1px solid #000; border-bottom: 1px solid #000; }
	.qb-items .c { text-align: center; }
	.qb-items .r { text-align: right; white-space: nowrap; }
	.qb-items .desc { text-align: left; }
	.qb-items tfoot td { vertical-align: middle; height: 28px; }
	.qb-items .note { text-align: left; font-size: 10px; letter-spacing: 0.1px; }
	.qb-items .totlab { text-align: center; font-weight: 700; font-size: 13px; }
	.qb-items .totamt { text-align: right; font-size: 12px; }
</style>

<div class="qb-wrap">
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

	<div class="qb-parties">
		<div class="qb-box">
			<div class="lbl">Bill To</div>
			<div class="body">
				@foreach($billLines as $line)
					<div>{{ $line }}</div>
				@endforeach
			</div>
		</div>
		<div class="qb-box">
			<div class="lbl">Ship To</div>
			<div class="body">
				@foreach($shipLines as $line)
					<div>{{ $line }}</div>
				@endforeach
			</div>
		</div>
	</div>

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

	<div class="qb-items-sheet">
	<table class="qb-items">
		<colgroup>
			<col style="width:11%"><col style="width:18%"><col style="width:41%"><col style="width:15%"><col style="width:15%">
		</colgroup>
		<thead>
			<tr>
				<th style="width:11%;">Quantity</th>
				<th style="width:18%;">Item Code</th>
				<th style="width:41%;">Description</th>
				<th style="width:15%;">Price Each</th>
				<th style="width:15%;">Amount</th>
			</tr>
		</thead>
		<tbody>
			@foreach($lines as $line)
				@php
					$code = $line['sub_sku'] ?? '';
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
				@endphp
				<tr>
					<td class="c">{{ $qtyText($line) }}</td>
					<td>{{ $code }}</td>
					<td class="desc">{{ $desc }}</td>
					<td class="r">{{ $money($priceEach) }}</td>
					<td class="r">{{ $money($amount) }}</td>
				</tr>
			@endforeach
		</tbody>
	</table>
	<div class="qb-items-fill" aria-hidden="true"><span></span><span></span><span></span><span></span><span></span></div>
	<table class="qb-items">
		<colgroup>
			<col style="width:11%"><col style="width:18%"><col style="width:41%"><col style="width:15%"><col style="width:15%">
		</colgroup>
		<tbody>
			<tr class="qb-total">
				<td class="note" colspan="3">{{ $footer }}</td>
				<td class="totlab">Total</td>
				<td class="totamt">${{ $money($totalRaw) }}</td>
			</tr>
		</tbody>
	</table>
	</div>
</div>
