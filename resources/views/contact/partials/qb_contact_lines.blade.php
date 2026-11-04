@php
	$personName = trim((string) ($contact->full_name ?? ''));
	if ($personName === '') {
		$legacyName = trim((string) ($contact->name ?? ''));
		$bizTmp = trim((string) ($contact->supplier_business_name ?? ''));
		if ($legacyName !== '' && strcasecmp($legacyName, $bizTmp) !== 0) {
			$personName = $legacyName;
		}
	}
	$businessName = trim((string) ($contact->supplier_business_name ?? ''));
	if ($businessName !== '' && strcasecmp($businessName, $personName) === 0) {
		$businessName = '';
	}
	$addressParts = array_values(array_filter(array_map('trim', $contact->contact_address_array ?? []), function ($p) {
		return $p !== '';
	}));
	$addressOneLine = implode(', ', $addressParts);
@endphp
@if($personName !== '')
	<div>{{ $personName }}</div>
@endif
@if($businessName !== '')
	<div>{{ $businessName }}</div>
@endif
@if($addressOneLine !== '')
	<div>{{ $addressOneLine }}</div>
@endif
@if(!empty($show_email) && !empty($contact->email))
	<div>{{ $contact->email }}</div>
@endif
@if(!empty($show_mobile) && !empty($contact->mobile) && $contact->mobile !== 'N/A')
	<div>{{ $contact->mobile }}</div>
@endif
