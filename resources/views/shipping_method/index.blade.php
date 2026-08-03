@extends('layouts.app')
@section('title', __('lang_v1.shipping_methods'))

@section('content')
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('lang_v1.shipping_methods')
        <small class="tw-text-sm tw-text-gray-600">@lang('lang_v1.manage_shipping_methods')</small>
    </h1>
</section>

<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => __('lang_v1.all_shipping_methods')])
        @slot('tool')
            <div class="box-tools">
                @can('shipping_method.create')
                    <button type="button" class="tw-dw-btn tw-dw-btn-primary tw-text-white btn-modal"
                        data-href="{{action([\App\Http\Controllers\ShippingMethodController::class, 'create'])}}"
                        data-container=".shipping_method_modal">
                        <i class="fa fa-plus"></i> @lang('messages.add')
                    </button>
                @endcan
            </div>
        @endslot
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
