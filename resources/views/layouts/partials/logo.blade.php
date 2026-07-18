<div class="row text-center">
	@php
		$auth_logo = getAuthLogoUrl();
		$default_logo = asset('img/logo-small.png');
		$legacy_logo = file_exists(public_path('uploads/logo.png')) ? asset('uploads/logo.png') : null;
		$logo_src = $auth_logo !== $default_logo ? $auth_logo : ($legacy_logo ?: null);
	@endphp
	@if($logo_src)
		<div class="col-xs-12">
			<img src="{{ $logo_src }}" class="img-rounded" alt="Logo" width="150" style="margin-bottom: 30px;">
		</div>
	@else
    	<h1 class="text-center page-header">{{ config('app.name', 'JAPS POS') }}</h1>
    @endif
</div>
