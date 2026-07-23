@extends('layouts.app')
@section('title', 'Install AI Assistance')
@section('content')
<section class="content-header"><h1>Install AI Assistance</h1></section>
<section class="content">
    <div class="box box-solid">
        <div class="box-body">
            @if($is_installed)
                <div class="alert alert-success">Installed (version: {{ $version }})</div>
            @else
                <form method="POST" action="{{ url('ai/install') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary">Install</button>
                </form>
            @endif
        </div>
    </div>
</section>
@endsection
