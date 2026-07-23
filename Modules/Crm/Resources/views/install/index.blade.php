@extends('layouts.app')
@section('title', __('crm::lang.install_crm'))
@section('content')
<section class="content-header">
    <h1>@lang('crm::lang.install_crm')</h1>
</section>
<section class="content">
    <div class="box box-solid">
        <div class="box-body">
            @if($is_installed)
                <div class="alert alert-success">CRM installed (version: {{ $version }})</div>
            @else
                <form method="POST" action="{{ route('crm.install') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary">@lang('crm::lang.install_crm')</button>
                </form>
            @endif
        </div>
    </div>
</section>
@endsection
