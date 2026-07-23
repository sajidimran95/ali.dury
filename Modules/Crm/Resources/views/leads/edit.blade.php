@extends('layouts.app')
@section('title', __('crm::lang.edit') . ' ' . __('crm::lang.lead'))
@section('content')
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold">@lang('crm::lang.edit') — {{ $contact->name }}</h1>
</section>
<section class="content">
    <div class="box box-solid">
        <div class="box-body">
            <form method="POST" action="{{ route('crm.leads.update', $contact->id) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="type" value="lead">

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>@lang('lang_v1.individual') / @lang('business.business')</label><br>
                            <label class="radio-inline"><input type="radio" name="contact_type_radio" value="individual" @checked($contact->contact_type !== 'business')> @lang('lang_v1.individual')</label>
                            <label class="radio-inline"><input type="radio" name="contact_type_radio" value="business" @checked($contact->contact_type === 'business')> @lang('business.business')</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>@lang('business.business_name')</label>
                            <input type="text" name="supplier_business_name" class="form-control" value="{{ $contact->supplier_business_name }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>@lang('lang_v1.contact_id')</label>
                            <input type="text" name="contact_id" class="form-control" value="{{ $contact->contact_id }}">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>@lang('business.prefix')</label>
                            <input type="text" name="prefix" class="form-control" value="{{ $contact->prefix }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>@lang('business.first_name') *</label>
                            <input type="text" name="first_name" class="form-control" value="{{ $contact->first_name }}" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>@lang('lang_v1.middle_name')</label>
                            <input type="text" name="middle_name" class="form-control" value="{{ $contact->middle_name }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>@lang('business.last_name')</label>
                            <input type="text" name="last_name" class="form-control" value="{{ $contact->last_name }}">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>@lang('crm::lang.mobile') *</label>
                            <input type="text" name="mobile" class="form-control" value="{{ $contact->mobile }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>@lang('crm::lang.email')</label>
                            <input type="email" name="email" class="form-control" value="{{ $contact->email }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>@lang('contact.landline')</label>
                            <input type="text" name="landline" class="form-control" value="{{ $contact->landline }}">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>@lang('crm::lang.source')</label>
                            <select name="crm_source" class="form-control">
                                <option value="">@lang('messages.please_select')</option>
                                @foreach($sources as $id => $name)
                                    <option value="{{ $id }}" @selected($contact->crm_source == $id)>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>@lang('crm::lang.life_stage')</label>
                            <select name="crm_life_stage" class="form-control">
                                <option value="">@lang('messages.please_select')</option>
                                @foreach($life_stages as $id => $name)
                                    <option value="{{ $id }}" @selected($contact->crm_life_stage == $id)>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>@lang('crm::lang.assigned_to')</label>
                            @php $lead_user_ids = $contact->leadUsers->pluck('id')->all(); @endphp
                            <select name="user_id[]" class="form-control select2" multiple style="width:100%">
                                @foreach($users as $id => $name)
                                    <option value="{{ $id }}" @selected(in_array($id, $lead_user_ids))>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>@lang('lang_v1.address_line_1')</label>
                            <input type="text" name="address_line_1" class="form-control" value="{{ $contact->address_line_1 }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>@lang('business.city')</label>
                            <input type="text" name="city" class="form-control" value="{{ $contact->city }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>@lang('business.state')</label>
                            <input type="text" name="state" class="form-control" value="{{ $contact->state }}">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">@lang('crm::lang.save')</button>
                <a href="{{ route('crm.leads.show', $contact->id) }}" class="btn btn-default">@lang('messages.cancel')</a>
            </form>
        </div>
    </div>
</section>
@endsection
@section('javascript')
<script>
$(function () {
    if ($.fn.select2) { $('.select2').select2(); }
});
</script>
@endsection
