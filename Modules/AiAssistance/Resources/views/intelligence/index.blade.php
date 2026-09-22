@extends('layouts.app')
@section('title', 'JapsAI Intelligence')
@section('content')
@php
    $s = $symbol;
    $sec = $section;
@endphp
<section class="content" style="padding-top:16px;">
    <div class="box box-solid">
        <div class="box-header with-border">
            <h3 class="box-title">JapsAI Intelligence <small>brief modules — drafts only, existing POS screens unchanged</small></h3>
        </div>
        <div class="box-body">
            <p class="text-muted">{{ $data['basis'] }} · as of {{ $data['as_of'] }} ({{ $data['timezone'] }})</p>
            <p>
                @foreach(['overview','forecast','collections','match','slow','credit','cash','cross','churn','vendors','prices','compliance','anomalies','digest'] as $k)
                    <a class="btn btn-xs {{ $sec === $k ? 'btn-primary' : 'btn-default' }}" href="{{ route('ai.intelligence.index', ['section' => $k]) }}">{{ ucfirst($k) }}</a>
                @endforeach
            </p>
            @include('aiassistance::intelligence.report')
        </div>
    </div>
</section>
@endsection
