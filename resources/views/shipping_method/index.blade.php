@extends('layouts.app')
@section('title', __('lang_v1.shipping_methods'))

@section('css')
    <link rel="stylesheet" href="{{ asset('css/product-list-metrics.css?v=' . ($asset_v ?? '') . '&plm=4') }}">
@endsection

@section('content')
<section class="content-header product-list-header">
    <div class="product-list-header__row">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black product-list-header__title">
            @lang('lang_v1.shipping_methods')
            <small class="tw-text-sm md:tw-text-base tw-text-gray-700 tw-font-semibold">@lang('lang_v1.manage_shipping_methods')</small>
        </h1>
        <div class="product-list-header__actions">
            @can('shipping_method.create')
                <a class="product-list-header__btn product-list-header__btn--primary btn-modal"
                    data-href="{{ action([\App\Http\Controllers\ShippingMethodController::class, 'create']) }}"
                    data-container=".shipping_method_modal"
                    href="javascript:void(0);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 5l0 14" />
                        <path d="M5 12l14 0" />
                    </svg>
                    @lang('messages.add')
                </a>
            @endcan
        </div>
    </div>
</section>

<section class="content">
    @if (!empty($shipping_stats))
        @include('shipping_method.partials.list_metrics')
    @endif

    @component('components.widget', ['class' => 'box-primary', 'title' => __('lang_v1.all_shipping_methods')])
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="shipping_methods_table">
                <thead>
                    <tr>
                        <th>@lang('lang_v1.name')</th>
                        <th>@lang('lang_v1.description')</th>
                        <th>@lang('sale.status')</th>
                        <th>@lang('messages.action')</th>
                    </tr>
                </thead>
            </table>
        </div>
    @endcomponent

    <div class="modal fade shipping_method_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
</section>
@endsection

@section('javascript')
<script type="text/javascript">
$(document).ready(function(){
    var table = $('#shipping_methods_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ action([\App\Http\Controllers\ShippingMethodController::class, 'index']) }}',
        columnDefs: [{ targets: [2, 3], orderable: false, searchable: false }],
        columns: [
            { data: 'name', name: 'name' },
            { data: 'description', name: 'description' },
            { data: 'status', name: 'is_active' },
            { data: 'action', name: 'action' }
        ]
    });

    $(document).on('submit', 'form#shipping_method_add_form', function(e) {
        e.preventDefault();
        var form = $(this);
        var data = form.serialize();
        $.ajax({
            method: 'POST',
            url: form.attr('action'),
            dataType: 'json',
            data: data,
            success: function(result) {
                if (result.success) {
                    $('div.shipping_method_modal').modal('hide');
                    toastr.success(result.msg);
                    table.ajax.reload();
                } else {
                    toastr.error(result.msg);
                }
            }
        });
    });

    $(document).on('click', 'button.edit_shipping_method_button', function() {
        $('div.shipping_method_modal').load($(this).data('href'), function() {
            $(this).modal('show');
            $('form#shipping_method_edit_form').submit(function(e) {
                e.preventDefault();
                var form = $(this);
                $.ajax({
                    method: 'POST',
                    url: form.attr('action'),
                    dataType: 'json',
                    data: form.serialize(),
                    success: function(result) {
                        if (result.success) {
                            $('div.shipping_method_modal').modal('hide');
                            toastr.success(result.msg);
                            table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });
            });
        });
    });

    $(document).on('click', 'button.delete_shipping_method_button', function() {
        swal({
            title: LANG.sure,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                var href = $(this).data('href');
                $.ajax({
                    method: 'DELETE',
                    url: href,
                    dataType: 'json',
                    success: function(result) {
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
