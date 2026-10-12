@extends('ecommerce::front.layouts.store')
@section('title', $page['title'])
@section('content')
<div class="max-w-3xl mx-auto px-4 py-12">
    <p class="text-xs text-[#8b95b8] mb-2"><a href="{{ url('/') }}">Home</a> › {{ $page['title'] }}</p>
    <h1 class="text-4xl font-extrabold mb-6">{{ $page['title'] }}</h1>
    <div class="wn-panel p-6 md:p-8 text-sm leading-relaxed text-[#c5cce6] whitespace-pre-line">{{ $page['body'] }}</div>
</div>
@endsection
