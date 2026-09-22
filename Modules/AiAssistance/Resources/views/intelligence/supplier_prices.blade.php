@extends('layouts.app')
@section('title', 'Cheaper supplier prices')
@section('content')
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold">Cheaper supplier prices
        <small>Which supplier gives the better unit cost (last 12 months)</small>
    </h1>
</section>
<section class="content">
    <div class="box box-solid">
        <div class="box-body">
            @include('aiassistance::intelligence.supplier_prices_embed')
        </div>
    </div>
</section>
@endsection
