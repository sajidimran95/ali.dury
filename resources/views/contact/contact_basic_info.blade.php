@include('contact.partials.qb_contact_lines', ['contact' => $contact, 'show_email' => false, 'show_mobile' => true])
@if($contact->landline)
	<div>{{ $contact->landline }}</div>
@endif
@if($contact->alternate_number)
	<div>{{ $contact->alternate_number }}</div>
@endif
@if($contact->dob)
	<div>{{ @format_date($contact->dob) }}</div>
@endif
