@extends('layouts.app')
@section('title', __('crm::lang.leads'))
@section('content')
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold">
        @lang('crm::lang.leads')
        @if(auth()->user()->can('crm.lead.create') || auth()->user()->can('crm.access') || auth()->user()->can('superadmin'))
            <a href="{{ route('crm.leads.create') }}" class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full tw-dw-btn-sm">
                @lang('crm::lang.add_lead')
            </a>
        @endif
    </h1>
</section>
<section class="content">
    <div class="box box-solid">
        <div class="box-body">
            <table class="table table-bordered table-striped" id="leads_table" style="width:100%">
                <thead>
                    <tr>
                        <th>@lang('crm::lang.name')</th>
                        <th>@lang('crm::lang.mobile')</th>
                        <th>@lang('crm::lang.email')</th>
                        <th>@lang('crm::lang.source')</th>
                        <th>@lang('crm::lang.life_stage')</th>
                        <th>@lang('crm::lang.assigned_to')</th>
                        <th>@lang('crm::lang.actions')</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
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
