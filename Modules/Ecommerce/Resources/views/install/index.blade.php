@extends('layouts.app')
@section('title', 'Install Ecommerce')
@section('content')
<section class="content-header"><h1>Ecommerce Module</h1></section>
<section class="content">
<div class="box"><div class="box-body">
@if($is_installed)
<p>Installed version: <strong>{{ $version }}</strong></p>
@else
<p>Module not installed in system table.</p>
<form method="post" action="{{ url('ecommerce/install') }}">@csrf
<button class="btn btn-success">Install</button>
</form>
@endif
</div></div>
</section>
@endsection
