@extends('layouts.app')
@section('title', __('crm::lang.leads'))

@section('css')
    <link rel="stylesheet" href="{{ asset('css/product-list-metrics.css?v=' . ($asset_v ?? '') . '&plm=4') }}">
@endsection

@section('content')
<section class="content-header product-list-header">
    <div class="product-list-header__row">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black product-list-header__title">
            @lang('crm::lang.leads')
        </h1>
        <div class="product-list-header__actions">
            @if(auth()->user()->can('crm.lead.create') || auth()->user()->can('crm.access') || auth()->user()->can('superadmin'))
                <a href="{{ route('crm.leads.create') }}" class="product-list-header__btn product-list-header__btn--primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 5l0 14" />
                        <path d="M5 12l14 0" />
                    </svg>
                    @lang('crm::lang.add_lead')
                </a>
            @endif
        </div>
    </div>
</section>

<section class="content">
    @if (!empty($lead_stats))
        @include('crm::leads.partials.list_metrics')
    @endif

    @component('components.widget', ['class' => 'box-primary', 'title' => __('crm::lang.leads')])
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="leads_table" style="width:100%">
                <thead>
                    <tr>
                        <th>@lang('crm::lang.name')</th>
                        <th>@lang('crm::lang.mobile')</th>
                        <th>@lang('crm::lang.email')</th>
                        <th>@lang('crm::lang.source')</th>
                        <th>@lang('crm::lang.life_stage')</th>
                        <th>@lang('crm::lang.assigned_to')</th>
                        <th class="not-export">@lang('crm::lang.actions')</th>
                    </tr>
                </thead>
            </table>
        </div>
    @endcomponent
</section>
@endsection

@section('javascript')
<script>
$(document).ready(function () {
    var table = $('#leads_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('crm.leads.index') }}',
        columns: [
            { data: 'name', name: 'name' },
            { data: 'mobile', name: 'mobile' },
            { data: 'email', name: 'email' },
            { data: 'crm_source', name: 'crm_source' },
            { data: 'crm_life_stage', name: 'crm_life_stage' },
            { data: 'assigned', name: 'assigned', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });

    $(document).on('click', '.delete-lead', function (e) {
        e.preventDefault();
        var href = $(this).data('href');
        swal({
            title: LANG.sure,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then(function (willDelete) {
            if (willDelete) {
                $.ajax({
                    method: 'DELETE',
                    url: href,
                    dataType: 'json',
                    success: function (result) {
                        if (result.success) {
                            toastr.success(result.msg);
                            table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });
            }
        });
    });
});
</script>
@endsection
