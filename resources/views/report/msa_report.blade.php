@extends('layouts.app')
@section('title', __('report.msa_tobacco_report'))

@section('content')
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
        @lang('report.msa_tobacco_report')
        <small class="tw-text-sm md:tw-text-base tw-text-gray-700 tw-font-semibold">
            @lang('report.msa_tobacco_report_msg')
        </small>
    </h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            @component('components.widget')
                @slot('title')
                    @lang('report.msa_export_settings')
                @endslot

                {!! Form::open([
                    'url' => action([\App\Http\Controllers\ReportController::class, 'exportMsaReport']),
                    'method' => 'post',
                    'id' => 'msa_export_form',
                ]) !!}
                    @csrf

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('location_id', __('purchase.business_location') . ':*') !!}
                                {!! Form::select('location_id', $business_locations, null, [
                                    'class' => 'form-control select2',
                                    'required',
                                    'style' => 'width:100%',
                                    'placeholder' => __('messages.please_select'),
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('week_ending', __('report.msa_week_ending') . ':*') !!}
                                {!! Form::text('week_ending', $week_ending, [
                                    'class' => 'form-control',
                                    'id' => 'msa_week_ending',
                                    'required',
                                    'readonly',
                                    'placeholder' => __('report.msa_week_ending_help'),
                                ]) !!}
                                <p class="help-block">@lang('report.msa_week_ending_help')</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('category_id', __('category.category') . ':') !!}
                                {!! Form::select('category_id[]', $categories, $default_category_ids ?? [], [
                                    'class' => 'form-control select2',
                                    'id' => 'msa_category_id',
                                    'multiple' => 'multiple',
                                    'style' => 'width:100%',
                                    'data-placeholder' => __('report.msa_category_placeholder'),
                                ]) !!}
                                <p class="help-block">@lang('report.msa_category_help')</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div class="checkbox">
                                    <label>
                                        {!! Form::checkbox('is_test', 1, true, ['id' => 'is_test']) !!}
                                        @lang('report.msa_test_file')
                                    </label>
                                </div>
                                <p class="help-block">@lang('report.msa_test_file_help')</p>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h4>@lang('report.msa_distributor_header')</h4>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('distributor_id', __('report.msa_distributor_id') . ':*') !!}
                                {!! Form::text('distributor_id', $common_settings['msa_distributor_id'] ?? '', [
                                    'class' => 'form-control',
                                    'required',
                                    'maxlength' => 8,
                                    'placeholder' => '17033752',
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('state_tax', __('report.msa_state_tax') . ':') !!}
                                {!! Form::text('state_tax', $common_settings['msa_state_tax'] ?? 'TN', [
                                    'class' => 'form-control',
                                    'maxlength' => 2,
                                    'placeholder' => 'TN',
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('contact_last', __('report.msa_contact_last') . ':') !!}
                                {!! Form::text('contact_last', $common_settings['msa_contact_last'] ?? '', [
                                    'class' => 'form-control',
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('contact_first', __('report.msa_contact_first') . ':') !!}
                                {!! Form::text('contact_first', $common_settings['msa_contact_first'] ?? '', [
                                    'class' => 'form-control',
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('contact_phone', __('report.msa_contact_phone') . ':') !!}
                                {!! Form::text('contact_phone', $common_settings['msa_contact_phone'] ?? '', [
                                    'class' => 'form-control',
                                    'placeholder' => '6151234567',
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('contact_email', __('report.msa_contact_email') . ':') !!}
                                {!! Form::email('contact_email', $common_settings['msa_contact_email'] ?? '', [
                                    'class' => 'form-control',
                                ]) !!}
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white">
                                <i class="fa fa-download"></i> @lang('report.msa_download_txt')
                            </button>
                        </div>
                    </div>
                {!! Form::close() !!}

                <hr>
                <div class="callout callout-info" style="margin-bottom:0;">
                    <h4>@lang('report.msa_format_notes_title')</h4>
                    <ul class="tw-mb-0">
                        <li>@lang('report.msa_format_note_1')</li>
                        <li>@lang('report.msa_format_note_2')</li>
                        <li>@lang('report.msa_format_note_3')</li>
                        <li>@lang('report.msa_format_note_4')</li>
                        <li>@lang('report.msa_format_note_5')</li>
                        <li>@lang('report.msa_format_note_6')</li>
                    </ul>
                </div>
            @endcomponent
        </div>
    </div>
</section>
@endsection

@section('javascript')
<script type="text/javascript">
    $(document).ready(function () {
        $('#msa_week_ending').datepicker({
            autoclose: true,
            format: datepicker_date_format,
            daysOfWeekDisabled: [0,1,2,3,4,5],
            endDate: '0d'
        });

        if ($('#msa_category_id').length) {
            $('#msa_category_id').select2({
                width: '100%',
                allowClear: true,
                placeholder: @json(__('report.msa_category_placeholder'))
            });
        }
    });
</script>
@endsection
