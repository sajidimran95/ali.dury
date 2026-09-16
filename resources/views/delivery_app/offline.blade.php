@extends('delivery_app.layout')
@section('title', 'Offline')
@section('content')
<div class="wrap"><div class="card"><p>{{ __('delivery.offline') }}. Open {{ url('/delivery') }} when you have a connection.</p></div></div>
@endsection
