@extends('layouts.app')
@section('title', __('aiassistance::lang.settings'))
@section('content')
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold">@lang('aiassistance::lang.ai_assistant') — @lang('aiassistance::lang.settings')</h1>
</section>
<section class="content">
    <div class="box box-solid" style="max-width:640px;">
        <div class="box-body">
            <form method="POST" action="{{ route('ai.settings.update') }}">
                @csrf
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="is_enabled" value="1" @checked($settings->is_enabled)>
                        @lang('aiassistance::lang.enable_ai')
                    </label>
                </div>
                <div class="form-group">
                    <label>@lang('aiassistance::lang.openai_api_key')</label>
                    <input type="password" name="openai_api_key" class="form-control"
                           value="{{ $settings->openai_api_key ? '********' : '' }}"
                           placeholder="sk-...">
                    <p class="help-block">@lang('aiassistance::lang.openai_api_key_help')</p>
                </div>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="clear_api_key" value="1">
                        Clear saved API key
                    </label>
                </div>
                <div class="form-group">
                    <label>@lang('aiassistance::lang.openai_model')</label>
                    <input type="text" name="openai_model" class="form-control"
                           value="{{ $settings->openai_model ?: config('aiassistance.openai_model') }}">
                </div>
                <div class="form-group">
                    <label>3-way match tolerance %</label>
                    <input type="number" step="0.1" name="match_tolerance_pct" class="form-control"
                           value="{{ $settings->match_tolerance_pct ?? 2 }}">
                    <p class="help-block">Invoices within this % qty/price variance are auto-approved for review skip. Exceptions still need a person.</p>
                </div>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="digest_enabled" value="1" @checked($settings->digest_enabled ?? false)>
                        Send daily digest email at 7:00 (server cron: php artisan schedule:run)
                    </label>
                </div>
                <div class="form-group">
                    <label>Digest email</label>
                    <input type="email" name="digest_email" class="form-control" value="{{ $settings->digest_email ?? '' }}">
                </div>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="alert_email_enabled" value="1" @checked($settings->alert_email_enabled ?? true)>
                        Email admin alerts (PO cost changes, invoice uploads that need review) to business admins and the digest email
                    </label>
                    <p class="help-block">Product price edits always appear in JapsAI → Insights → Admin alerts.</p>
                </div>
                <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                <a href="{{ route('ai.dashboard') }}" class="btn btn-default">@lang('aiassistance::lang.ai_assistant')</a>
            </form>
        </div>
    </div>
</section>
@endsection
